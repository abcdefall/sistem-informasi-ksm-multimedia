<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\MentoringField;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HomeController extends Controller
{
    public function index()
    {
        return $this->renderPage('landing');
    }

    public function landingRole()
    {
        $roleName = auth()->user()->role?->role_name;

        $landingRoleViews = [
            'Peserta' => 'landing-role.peserta',
            'Mentor' => 'landing-role.mentor',
            'Pengurus KSM' => 'landing-role.pengurus',
        ];

        abort_unless(isset($landingRoleViews[$roleName]), 403, 'Role akun tidak memiliki dashboard.');

        return $this->renderPage($landingRoleViews[$roleName], $roleName);
    }

    public function dashboardPeserta()
    {
        abort_unless(auth()->user()->role?->role_name === 'Peserta', 403, 'Dashboard ini khusus peserta.');

        $projects = auth()->user()->createdProjects()
            ->with('mentoringField')
            ->latest('created_at')
            ->get();

        return response()
            ->view('peserta.dashboard', [
                'projects' => $projects,
                'totalProjects' => $projects->count(),
                'revisionProjects' => $projects->where('status', 'Revision Required')->count(),
                'publishedProjects' => $projects->whereIn('status', ['Approved', 'Published'])->count(),
                'mentor' => $this->participantMentor(),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function dashboardMentor(Request $request)
    {
        abort_unless(auth()->user()->role?->role_name === 'Mentor', 403, 'Dashboard ini khusus mentor.');

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['Submitted', 'In Review', 'Resubmitted'])],
        ]);

        $queueQuery = Project::query()
            ->with(['mentoringField', 'creator'])
            ->whereIn('status', ['Submitted', 'In Review', 'Resubmitted']);

        if (!empty($filters['q'])) {
            $search = $filters['q'];
            $queueQuery->where(function ($query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhereHas('creator', fn ($creatorQuery) => $creatorQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['status'])) {
            $queueQuery->where('status', $filters['status']);
        }

        $projects = $queueQuery
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->get();
        $projectIds = $projects->pluck('project_id');
        $membersByProject = DB::table('project_members')
            ->join('users', 'users.user_id', '=', 'project_members.user_id')
            ->whereIn('project_members.project_id', $projectIds)
            ->orderBy('users.name')
            ->get(['project_members.project_id', 'users.name'])
            ->groupBy('project_id');
        $activePeriod = DB::table('mentoring_periods')
            ->where('is_active', true)
            ->orderByDesc('start_date')
            ->value('period_name');

        return $this->privateView('mentor.dashboard', [
            'projects' => $projects,
            'membersByProject' => $membersByProject,
            'activePeriod' => $activePeriod,
            'waitingReviewCount' => Project::whereIn('status', ['Submitted', 'In Review'])->count(),
            'revisionIncomingCount' => Project::where('status', 'Resubmitted')->count(),
            'reviewedProjectCount' => DB::table('reviews')
                ->where('reviewer_id', auth()->id())
                ->distinct('project_id')
                ->count('project_id'),
            'filters' => $filters,
        ]);
    }

    public function dashboardPengurus(Request $request)
    {
        abort_unless(auth()->user()->role?->role_name === 'Pengurus KSM', 403, 'Dashboard ini khusus pengurus.');

        $filters = $request->validate([
            'field' => ['nullable', 'string', 'max:20'],
        ]);

        $queueQuery = Project::query()
            ->with(['mentoringField', 'creator'])
            ->where('status', 'Approved');

        if (!empty($filters['field'])) {
            $queueQuery->where('field_id', $filters['field']);
        }

        $projects = $queueQuery
            ->orderByRaw("CASE WHEN status = 'Approved' THEN 0 WHEN status = 'Published' THEN 1 ELSE 2 END")
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();
        $projectIds = $projects->pluck('project_id');
        $membersByProject = DB::table('project_members')
            ->join('users', 'users.user_id', '=', 'project_members.user_id')
            ->whereIn('project_members.project_id', $projectIds)
            ->orderBy('users.name')
            ->get(['project_members.project_id', 'users.name'])
            ->groupBy('project_id');
        $latestReviewsByProject = DB::table('reviews')
            ->join('users', 'users.user_id', '=', 'reviews.reviewer_id')
            ->whereIn('reviews.project_id', $projectIds)
            ->orderByDesc('reviews.created_at')
            ->get([
                'reviews.project_id',
                'reviews.rating',
                'reviews.comment',
                'reviews.created_at',
                'users.name as reviewer_name',
            ])
            ->groupBy('project_id');

        $activities = collect();
        foreach (DB::table('audit_logs')
            ->leftJoin('users', 'users.user_id', '=', 'audit_logs.user_id')
            ->leftJoin('projects', 'projects.project_id', '=', 'audit_logs.project_id')
            ->orderByDesc('audit_logs.created_at')
            ->limit(10)
            ->get([
                'audit_logs.action',
                'audit_logs.description',
                'audit_logs.created_at',
                'users.name as user_name',
                'projects.title as project_title',
            ]) as $activity) {
            $activities->push([
                'type' => 'audit',
                'title' => $activity->description ?: $activity->action,
                'detail' => $activity->action,
                'user' => $activity->user_name ?: 'Pengguna',
                'project' => $activity->project_title,
                'created_at' => $activity->created_at,
            ]);
        }

        foreach (DB::table('reviews')
            ->join('users', 'users.user_id', '=', 'reviews.reviewer_id')
            ->join('projects', 'projects.project_id', '=', 'reviews.project_id')
            ->orderByDesc('reviews.created_at')
            ->limit(10)
            ->get([
                'reviews.rating',
                'reviews.created_at',
                'users.name as reviewer_name',
                'projects.title as project_title',
            ]) as $review) {
            $activities->push([
                'type' => 'review',
                'title' => $review->reviewer_name . ' menyelesaikan evaluasi mentor untuk "' . $review->project_title . '".',
                'detail' => $review->rating ? 'Skor rubrik: ' . $review->rating . '/5' : 'Review tersimpan',
                'user' => $review->reviewer_name,
                'created_at' => $review->created_at,
            ]);
        }

        $activities = $activities
            ->sortByDesc(fn (array $activity) => strtotime($activity['created_at']))
            ->take(5)
            ->values();

        return $this->privateView('pengurus.dashboard', [
            'projects' => $projects,
            'membersByProject' => $membersByProject,
            'latestReviewsByProject' => $latestReviewsByProject,
            'activities' => $activities,
            'fields' => MentoringField::query()->orderBy('field_name')->get(['field_id', 'field_name']),
            'filters' => $filters,
            'activeMenu' => 'dashboard',
            'pendingApprovalCount' => Project::where('status', 'Approved')->count(),
            'reviewingProjectCount' => Project::whereIn('status', ['Submitted', 'In Review', 'Resubmitted'])->count(),
            'publishedProjectCount' => Project::where('status', 'Published')->count(),
            'curationScore' => DB::table('reviews')->whereNotNull('rating')->avg('rating'),
            'activePeriod' => DB::table('mentoring_periods')
                ->where('is_active', true)
                ->orderByDesc('start_date')
                ->value('period_name'),
        ]);
    }

    public function pengurusApprovals(Request $request)
    {
        $this->authorizePengurus();

        $filters = $request->validate([
            'field' => ['nullable', 'string', 'max:20'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $query = Project::query()
            ->with(['mentoringField', 'creator'])
            ->where('status', 'Approved');

        if (!empty($filters['field'])) {
            $query->where('field_id', $filters['field']);
        }
        if (!empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function ($projectQuery) use ($search): void {
                $projectQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('project_id', 'like', "%{$search}%")
                    ->orWhereHas('creator', fn ($creatorQuery) => $creatorQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $projects = $query
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();
        $membersByProject = DB::table('project_members')
            ->join('users', 'users.user_id', '=', 'project_members.user_id')
            ->whereIn('project_members.project_id', $projects->getCollection()->pluck('project_id'))
            ->orderBy('users.name')
            ->get(['project_members.project_id', 'users.name'])
            ->groupBy('project_id');

        return $this->privateView('pengurus.approvals', [
            'projects' => $projects,
            'membersByProject' => $membersByProject,
            'fields' => MentoringField::query()->orderBy('field_name')->get(['field_id', 'field_name']),
            'filters' => $filters,
            'pendingApprovalCount' => Project::where('status', 'Approved')->count(),
            'activeMenu' => 'approvals',
        ]);
    }

    public function showPengurusApproval(string $projectId)
    {
        $this->authorizePengurus();

        $project = Project::query()
            ->with(['mentoringField', 'creator'])
            ->where('project_id', $projectId)
            ->whereIn('status', ['Approved', 'Published'])
            ->firstOrFail();
        $members = DB::table('project_members')
            ->join('users', 'users.user_id', '=', 'project_members.user_id')
            ->where('project_members.project_id', $projectId)
            ->orderBy('users.name')
            ->get(['users.name', 'users.email', 'project_members.member_role']);
        $tools = DB::table('project_tools')
            ->where('project_id', $projectId)
            ->orderBy('tool_name')
            ->pluck('tool_name');
        $files = DB::table('project_files')
            ->where('project_id', $projectId)
            ->latest('created_at')
            ->get();
        $period = DB::table('mentoring_periods')->where('period_id', $project->period_id)->first();
        $latestReview = DB::table('reviews')
            ->join('users', 'users.user_id', '=', 'reviews.reviewer_id')
            ->where('reviews.project_id', $projectId)
            ->orderByDesc('reviews.created_at')
            ->first([
                'reviews.comment',
                'reviews.rating',
                'reviews.rubric_scores',
                'reviews.created_at',
                'users.name as reviewer_name',
            ]);
        $publicationApprover = $project->publication_approved_by
            ? User::query()->find($project->publication_approved_by)
            : null;
        $rubricScores = $latestReview?->rubric_scores
            ? json_decode($latestReview->rubric_scores, true, flags: JSON_THROW_ON_ERROR)
            : [];

        return $this->privateView('pengurus.approval-detail', [
            'project' => $project,
            'members' => $members,
            'tools' => $tools,
            'files' => $files,
            'period' => $period,
            'latestReview' => $latestReview,
            'publicationApprover' => $publicationApprover,
            'rubricScores' => $rubricScores,
            'pendingApprovalCount' => Project::where('status', 'Approved')->count(),
            'activeMenu' => 'approvals',
        ]);
    }

    public function storePengurusApproval(Request $request, string $projectId): RedirectResponse
    {
        $this->authorizePengurus();

        $validated = $request->validate([
            'action' => ['required', Rule::in(['approve', 'publish', 'request_revision'])],
            'showcase' => ['nullable', 'boolean'],
        ]);

        $checklist = [];
        if ($validated['action'] === 'publish') {
            $checklist = $request->validate([
                'checklist.thumbnail' => ['required', 'accepted'],
                'checklist.rights' => ['required', 'accepted'],
                'checklist.demo' => ['required', 'accepted'],
                'checklist.originality' => ['required', 'accepted'],
            ])['checklist'];
        }
        if ($validated['action'] === 'request_revision') {
            $validated['comment'] = $request->validate([
                'comment' => ['required', 'string', 'max:1000'],
            ])['comment'];
        }

        DB::transaction(function () use ($projectId, $validated, $checklist): void {
            $project = Project::query()
                ->where('project_id', $projectId)
                ->whereIn('status', ['Approved', 'Published'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($validated['action'] === 'approve') {
                if ($project->status === 'Published') {
                    abort(409, 'Project ini sudah dipublikasikan.');
                }

                $project->forceFill([
                    'publication_approved_by' => auth()->id(),
                    'publication_approved_at' => now(),
                ])->save();

                $this->recordPengurusAudit(
                    'PUBLICATION_APPROVED',
                    'Pengurus memberikan approval resmi untuk project "' . $project->title . '".',
                    $project->project_id,
                );

                return;
            }

            if ($validated['action'] === 'request_revision') {
                if ($project->status === 'Published') {
                    abort(409, 'Project yang sudah dipublikasikan tidak dapat dikembalikan ke revisi dari halaman ini.');
                }

                do {
                    $reviewId = 'REV-' . Str::upper(Str::random(16));
                } while (DB::table('reviews')->where('review_id', $reviewId)->exists());

                DB::table('reviews')->insert([
                    'review_id' => $reviewId,
                    'project_id' => $project->project_id,
                    'reviewer_id' => auth()->id(),
                    'comment' => $validated['comment'],
                    'rating' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $project->forceFill(['status' => 'Revision Required'])->save();
                $this->recordPengurusAudit(
                    'REVISION_REQUESTED',
                    'Pengurus meminta revisi untuk project "' . $project->title . '".',
                    $project->project_id,
                );

                return;
            }

            if ($project->status === 'Published') {
                abort(409, 'Project ini sudah dipublikasikan.');
            }
            if (!$project->publication_approved_at) {
                abort(409, 'Berikan approval resmi pengurus sebelum mempublikasikan project.');
            }

            $project->forceFill([
                'status' => 'Published',
                'published_at' => now(),
                'is_featured' => (bool) ($validated['showcase'] ?? false),
            ])->save();

            $this->recordPengurusAudit(
                'PROJECT_PUBLISHED',
                'Project "' . $project->title . '" dipublikasikan ke showcase. ' .
                    'Visibilitas beranda: ' . ($project->is_featured ? 'aktif' : 'nonaktif') .
                    '. Checklist: ' . implode(', ', array_keys($checklist)) . '.',
                $project->project_id,
            );
        });

        return redirect()
            ->route($validated['action'] === 'request_revision'
                ? 'dashboard.pengurus.approvals'
                : 'dashboard.pengurus.approval.show', $validated['action'] === 'request_revision' ? [] : $projectId)
            ->with('status', $validated['action'] === 'approve'
                ? 'Approval resmi pengurus berhasil disimpan.'
                : ($validated['action'] === 'publish'
                    ? 'Project berhasil dipublikasikan ke showcase.'
                    : 'Catatan revisi berhasil dikirim kepada peserta.'));
    }

    public function pengurusAuditTrail(Request $request)
    {
        $this->authorizePengurus();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'max:100'],
        ]);
        $query = DB::table('audit_logs')
            ->leftJoin('users', 'users.user_id', '=', 'audit_logs.user_id')
            ->leftJoin('projects', 'projects.project_id', '=', 'audit_logs.project_id');

        if (!empty($filters['action'])) {
            $query->where('audit_logs.action', $filters['action']);
        }
        if (!empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function ($logQuery) use ($search): void {
                $logQuery->where('audit_logs.description', 'like', "%{$search}%")
                    ->orWhere('audit_logs.action', 'like', "%{$search}%")
                    ->orWhere('audit_logs.log_id', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('projects.title', 'like', "%{$search}%")
                    ->orWhere('projects.project_id', 'like', "%{$search}%");
            });
        }

        $logs = $query
            ->orderByDesc('audit_logs.created_at')
            ->select([
                'audit_logs.log_id',
                'audit_logs.action',
                'audit_logs.description',
                'audit_logs.created_at',
                'users.name as actor_name',
                'users.email as actor_email',
                'projects.project_id',
                'projects.title as project_title',
            ])
            ->paginate(10)
            ->withQueryString();

        return $this->privateView('pengurus.audit-trail', [
            'logs' => $logs,
            'filters' => $filters,
            'actions' => DB::table('audit_logs')->distinct()->orderBy('action')->pluck('action'),
            'pendingApprovalCount' => Project::where('status', 'Approved')->count(),
            'activeMenu' => 'audit',
        ]);
    }

    public function showMentorReview(string $projectId)
    {
        $this->authorizeMentor();

        $project = $this->mentorReviewQueueQuery()
            ->where('projects.project_id', $projectId)
            ->firstOrFail();

        $period = DB::table('mentoring_periods')
            ->where('period_id', $project->period_id)
            ->first();
        $members = DB::table('project_members')
            ->join('users', 'users.user_id', '=', 'project_members.user_id')
            ->where('project_members.project_id', $project->project_id)
            ->orderBy('users.name')
            ->get(['users.name', 'users.email', 'project_members.member_role']);
        $tools = DB::table('project_tools')
            ->where('project_id', $project->project_id)
            ->orderBy('tool_name')
            ->pluck('tool_name');
        $files = DB::table('project_files')
            ->where('project_id', $project->project_id)
            ->latest('created_at')
            ->get();
        $latestVersion = DB::table('project_versions')
            ->where('project_id', $project->project_id)
            ->latest('created_at')
            ->first();
        $projectUrl = filter_var($project->project_link, FILTER_VALIDATE_URL)
            && in_array(parse_url($project->project_link, PHP_URL_SCHEME), ['http', 'https'], true)
                ? $project->project_link
                : null;

        return $this->privateView('mentor.review-project', compact(
            'project',
            'period',
            'members',
            'tools',
            'files',
            'latestVersion',
            'projectUrl',
        ) + [
            'waitingReviewCount' => Project::whereIn('status', ['Submitted', 'In Review'])->count(),
            'revisionIncomingCount' => Project::where('status', 'Resubmitted')->count(),
            'activePeriod' => DB::table('mentoring_periods')
                ->where('is_active', true)
                ->orderByDesc('start_date')
                ->value('period_name'),
        ]);
    }

    public function storeMentorReview(Request $request, string $projectId): RedirectResponse
    {
        $this->authorizeMentor();

        $project = $this->mentorReviewQueueQuery()
            ->where('projects.project_id', $projectId)
            ->firstOrFail();

        $validated = $request->validate([
            'rubric.technical_quality' => ['required', Rule::in(['needs_improvement', 'meets_standard', 'exceptional'])],
            'rubric.creative_quality' => ['required', Rule::in(['needs_improvement', 'meets_standard', 'exceptional'])],
            'rubric.publication_readiness' => ['required', Rule::in(['needs_improvement', 'meets_standard', 'exceptional'])],
            'comment' => ['required', 'string', 'max:1000'],
            'decision' => ['required', Rule::in(['Revision Required', 'Approved'])],
        ]);

        $levelScores = [
            'needs_improvement' => 1,
            'meets_standard' => 3,
            'exceptional' => 5,
        ];
        $weights = [
            'technical_quality' => 40,
            'creative_quality' => 30,
            'publication_readiness' => 30,
        ];
        $rubricScores = [];
        $weightedScore = 0;

        foreach ($weights as $criterion => $weight) {
            $score = $levelScores[$validated['rubric'][$criterion]];
            $rubricScores[$criterion] = [
                'rating' => $validated['rubric'][$criterion],
                'score' => $score,
                'weight' => $weight,
            ];
            $weightedScore += $score * ($weight / 100);
        }

        DB::transaction(function () use ($project, $validated, $rubricScores, $weightedScore): void {
            $project = $this->mentorReviewQueueQuery()
                ->where('projects.project_id', $project->project_id)
                ->lockForUpdate()
                ->firstOrFail();

            do {
                $reviewId = 'REV-' . Str::upper(Str::random(16));
            } while (DB::table('reviews')->where('review_id', $reviewId)->exists());

            DB::table('reviews')->insert([
                'review_id' => $reviewId,
                'project_id' => $project->project_id,
                'reviewer_id' => auth()->id(),
                'comment' => $validated['comment'],
                'rating' => (string) round($weightedScore),
                'rubric_scores' => json_encode($rubricScores, JSON_THROW_ON_ERROR),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Project::query()
                ->where('project_id', $project->project_id)
                ->update([
                    'status' => $validated['decision'],
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->route('dashboard.mentor')
            ->with('status', 'Review project berhasil disimpan.');
    }

    public function participantProjects()
    {
        $this->authorizeParticipant();

        $projects = $this->participantProjectsQuery()
            ->with('mentoringField')
            ->latest('created_at')
            ->get();

        $latestReviews = DB::table('reviews')
            ->whereIn('project_id', $projects->pluck('project_id'))
            ->select('project_id', DB::raw('MAX(created_at) as latest_review_at'))
            ->groupBy('project_id')
            ->get()
            ->keyBy('project_id');

        return $this->privateView('peserta.projects', [
            'projects' => $projects,
            'latestReviews' => $latestReviews,
            'mentor' => $this->participantMentor(),
        ]);
    }

    public function showParticipantProject(string $projectId)
    {
        $this->authorizeParticipant();

        $project = $this->participantProjectsQuery()
            ->with('mentoringField')
            ->where('project_id', $projectId)
            ->firstOrFail();

        $period = DB::table('mentoring_periods')
            ->where('period_id', $project->period_id)
            ->first();
        $members = DB::table('project_members')
            ->join('users', 'users.user_id', '=', 'project_members.user_id')
            ->where('project_members.project_id', $project->project_id)
            ->orderBy('users.name')
            ->get(['users.name', 'project_members.member_role']);
        $tools = DB::table('project_tools')
            ->where('project_id', $project->project_id)
            ->orderBy('tool_name')
            ->pluck('tool_name');
        $reviews = DB::table('reviews')
            ->join('users', 'users.user_id', '=', 'reviews.reviewer_id')
            ->where('reviews.project_id', $project->project_id)
            ->latest('reviews.created_at')
            ->get([
                'reviews.comment',
                'reviews.rating',
                'reviews.created_at',
                'users.name as reviewer_name',
            ]);
        $versions = DB::table('project_versions')
            ->where('project_id', $project->project_id)
            ->latest('created_at')
            ->get();
        $files = DB::table('project_files')
            ->where('project_id', $project->project_id)
            ->latest('created_at')
            ->get();
        $canResubmit = $project->created_by === auth()->id();
        $mentor = $this->participantMentor();

        return $this->privateView('peserta.detail-project', compact(
            'project',
            'period',
            'members',
            'tools',
            'reviews',
            'versions',
            'files',
            'canResubmit',
            'mentor',
        ));
    }

    public function resubmitProject(Request $request, string $projectId): RedirectResponse
    {
        $this->authorizeParticipant();

        $project = Project::query()
            ->where('project_id', $projectId)
            ->where('created_by', auth()->id())
            ->firstOrFail();
        abort_unless($project->status === 'Revision Required', 403, 'Project ini tidak sedang menunggu revisi.');

        $validated = $request->validate([
            'revision_notes' => ['required', 'string', 'max:5000'],
            'project_link' => ['required', 'url', 'max:255'],
            'revision_file' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'integrity' => ['accepted'],
        ]);

        $revisionFile = $request->file('revision_file');
        $revisionFilePath = $revisionFile?->store("projects/{$project->project_id}/revisions", 'public');
        $nextVersion = 'v1.' . (DB::table('project_versions')
            ->where('project_id', $project->project_id)
            ->count() + 1);

        try {
            DB::transaction(function () use ($project, $validated, $revisionFile, $revisionFilePath, $nextVersion): void {
                $project->update([
                    'project_link' => $validated['project_link'],
                    'status' => 'Resubmitted',
                ]);

                do {
                    $versionId = 'VER-' . Str::upper(Str::random(15));
                } while (DB::table('project_versions')->where('version_id', $versionId)->exists());

                DB::table('project_versions')->insert([
                    'version_id' => $versionId,
                    'project_id' => $project->project_id,
                    'version_number' => $nextVersion,
                    'changelog' => $validated['revision_notes'],
                    'file_path' => $revisionFilePath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($revisionFile && $revisionFilePath) {
                    do {
                        $fileId = 'FILE-' . Str::upper(Str::random(15));
                    } while (DB::table('project_files')->where('file_id', $fileId)->exists());

                    DB::table('project_files')->insert([
                        'file_id' => $fileId,
                        'project_id' => $project->project_id,
                        'file_name' => Str::limit(basename($revisionFile->getClientOriginalName()), 255, ''),
                        'file_path' => $revisionFilePath,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            if ($revisionFilePath) {
                Storage::disk('public')->delete($revisionFilePath);
            }

            throw $exception;
        }

        return redirect()
            ->route('dashboard.peserta.project.show', $project->project_id)
            ->with('status', 'Project berhasil dikirim ulang untuk ditinjau.');
    }

    public function createProject()
    {
        $this->authorizeParticipant();

        return response()
            ->view('peserta.create-project', [
                'mentoringFields' => MentoringField::orderBy('field_name')->get(),
                'mentoringPeriods' => DB::table('mentoring_periods')
                    ->where('is_active', true)
                    ->orderByDesc('start_date')
                    ->get(),
                'mentor' => $this->participantMentor(),
                'participants' => DB::table('users')
                    ->where('role_id', auth()->user()->role_id)
                    ->orderBy('name')
                    ->get(['user_id', 'name']),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function storeProject(Request $request): RedirectResponse
    {
        $this->authorizeParticipant();

        $request->merge([
            'submission_action' => $request->input('submission_action', 'Submitted'),
        ]);
        $submissionAction = $request->input('submission_action');

        $validated = $request->validate([
            'submission_action' => ['required', Rule::in(['Draft', 'Submitted'])],
            'title' => ['required', 'string', 'max:255'],
            'field_id' => ['required', 'string', Rule::exists('mentoring_fields', 'field_id')],
            'period_id' => [
                'required',
                'string',
                Rule::exists('mentoring_periods', 'period_id')
                    ->where(fn (Builder $query) => $query->where('is_active', true)),
            ],
            'description' => [
                'required',
                'string',
                'max:5000',
                function (string $attribute, string $value, \Closure $fail) use ($submissionAction): void {
                    if ($submissionAction === 'Submitted' && str_word_count(strip_tags($value)) < 150) {
                        $fail('Deskripsi project harus berisi minimal 150 kata untuk dikirim.');
                    }
                },
            ],
            'members' => [
                Rule::requiredIf(fn () => $submissionAction === 'Submitted'),
                'array',
                'min:1',
                'max:10',
            ],
            'members.*.user_id' => [
                'required',
                'distinct',
                Rule::exists('users', 'user_id')
                    ->where(fn (Builder $query) => $query->where('role_id', auth()->user()->role_id)),
            ],
            'members.*.member_role' => ['required', 'string', 'max:100'],
            'tools' => [
                Rule::requiredIf(fn () => $submissionAction === 'Submitted'),
                'array',
                'min:1',
                'max:20',
            ],
            'tools.*' => ['required', 'string', 'max:255', 'distinct'],
            'thumbnail' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('submission_action') === 'Submitted'),
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'project_link' => [
                Rule::requiredIf(fn () => $submissionAction === 'Submitted'),
                'nullable',
                'url',
                'max:255',
            ],
        ]);

        $submissionAction = $validated['submission_action'];
        unset($validated['submission_action']);

        $members = $validated['members'] ?? [];
        $tools = array_map('trim', $validated['tools'] ?? []);
        unset($validated['members'], $validated['tools'], $validated['thumbnail']);

        do {
            $projectId = 'PROJ-' . Str::upper(Str::random(15));
        } while (Project::whereKey($projectId)->exists());

        $thumbnail = $request->file('thumbnail');
        $thumbnailPath = $thumbnail?->store("projects/{$projectId}", 'public');

        try {
            DB::transaction(function () use ($validated, $members, $tools, $projectId, $thumbnail, $thumbnailPath, $submissionAction): void {
                Project::create([
                    ...$validated,
                    'project_id' => $projectId,
                    'created_by' => auth()->id(),
                    'thumbnail_url' => $thumbnailPath ? Storage::disk('public')->url($thumbnailPath) : null,
                    'status' => $submissionAction,
                    'submitted_at' => $submissionAction === 'Submitted' ? now() : null,
                ]);

                foreach ($members as $member) {
                    DB::table('project_members')->insert([
                        ...$member,
                        'project_id' => $projectId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                foreach ($tools as $tool) {
                    do {
                        $toolId = 'TOOL-' . Str::upper(Str::random(15));
                    } while (DB::table('project_tools')->where('tool_id', $toolId)->exists());

                    DB::table('project_tools')->insert([
                        'tool_id' => $toolId,
                        'project_id' => $projectId,
                        'tool_name' => $tool,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if ($thumbnail && $thumbnailPath) {
                    do {
                        $fileId = 'FILE-' . Str::upper(Str::random(15));
                    } while (DB::table('project_files')->where('file_id', $fileId)->exists());

                    DB::table('project_files')->insert([
                        'file_id' => $fileId,
                        'project_id' => $projectId,
                        'file_name' => Str::limit(basename($thumbnail->getClientOriginalName()), 255, ''),
                        'file_path' => $thumbnailPath,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            if ($thumbnailPath) {
                Storage::disk('public')->delete($thumbnailPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('dashboard.peserta')
            ->with('status', $submissionAction === 'Draft'
                ? 'Draft project berhasil disimpan.'
                : 'Project berhasil diajukan.');
    }

    private function authorizeParticipant(): void
    {
        abort_unless(auth()->user()->role?->role_name === 'Peserta', 403, 'Fitur ini khusus peserta.');
    }

    private function authorizeMentor(): void
    {
        abort_unless(auth()->user()->role?->role_name === 'Mentor', 403, 'Fitur ini khusus mentor.');
    }

    private function authorizePengurus(): void
    {
        abort_unless(auth()->user()->role?->role_name === 'Pengurus KSM', 403, 'Fitur ini khusus pengurus.');
    }

    private function recordPengurusAudit(string $action, string $description, ?string $projectId = null): void
    {
        do {
            $logId = 'LOG-' . Str::upper(Str::random(16));
        } while (DB::table('audit_logs')->where('log_id', $logId)->exists());

        DB::table('audit_logs')->insert([
            'log_id' => $logId,
            'user_id' => auth()->id(),
            'project_id' => $projectId,
            'action' => $action,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function mentorReviewQueueQuery(): EloquentBuilder
    {
        return Project::query()
            ->with(['mentoringField', 'creator'])
            ->whereIn('status', ['Submitted', 'In Review', 'Resubmitted']);
    }

    private function participantMentor(): ?User
    {
        return User::query()
            ->whereHas('role', fn ($query) => $query->where('role_name', 'Mentor'))
            ->orderBy('name')
            ->first(['user_id', 'name', 'email']);
    }

    private function participantProjectsQuery()
    {
        $userId = auth()->id();

        return Project::query()
            ->where(function ($query) use ($userId): void {
                $query->where('created_by', $userId)
                    ->orWhereIn('project_id', DB::table('project_members')
                        ->select('project_id')
                        ->where('user_id', $userId));
            });
    }

    private function privateView(string $view, array $data)
    {
        return response()
            ->view($view, $data)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function renderPage(string $view, ?string $dashboardRole = null)
    {
        try {
            $mentoringFields = MentoringField::take(4)->get();
            $publishedProjects = Project::with(['mentoringField', 'creator'])
                ->where('status', 'Published')
                ->where('is_featured', true)
                ->latest('published_at')
                ->take(3)
                ->get();
        } catch (\Exception $e) {
            // Fallback berupa collection kosong jika migration / database belum diisi
            $mentoringFields = collect();
            $publishedProjects = collect();
        }

        return response()
            ->view($view, compact('mentoringFields', 'publishedProjects', 'dashboardRole'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}