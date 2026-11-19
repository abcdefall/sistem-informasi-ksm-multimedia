<?php

namespace App\Http\Controllers;

use App\Models\MentoringField;
use App\Models\Project;
use App\Models\ProjectVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class HomeController extends Controller
{
    public function index()
    {
        return $this->renderPage('landing');
    }

    public function mentoring()
    {
        $role = auth()->user()?->role?->role_name;
        $pages = [
            'Peserta' => [
                'eyebrow' => 'Program untuk peserta',
                'heading' => 'Kembangkan ide menjadi karya nyata',
                'description' => 'Pilih jalur mentoring yang sesuai dengan minat Anda. Belajar terarah, dapatkan masukan mentor, dan bangun portofolio capstone secara bertahap.',
                'action_label' => 'Mulai Proyek',
                'action_url' => route('dashboard.peserta.project.create'),
            ],
            'Mentor' => [
                'eyebrow' => 'Program untuk mentor',
                'heading' => 'Dampingi talenta multimedia berikutnya',
                'description' => 'Kenali kurikulum dan jalur spesialisasi yang menjadi ruang pendampingan peserta KSM Multimedia.',
                'action_label' => 'Buka Dashboard Mentor',
                'action_url' => route('dashboard.mentor'),
            ],
            'Pengurus KSM' => [
                'eyebrow' => 'Program KSM Multimedia',
                'heading' => 'Kurikulum mentoring yang terarah',
                'description' => 'Jelajahi jalur kurikulum dan informasi program mentoring KSM Multimedia untuk setiap bidang spesialisasi.',
                'action_label' => 'Buka Dashboard Pengurus',
                'action_url' => route('dashboard.pengurus'),
            ],
        ];
        $context = $pages[$role] ?? [
            'eyebrow' => 'Program KSM Multimedia',
            'heading' => 'Temukan jalur mentoring Anda',
            'description' => 'Kurikulum terstruktur untuk membantu mahasiswa mengembangkan keterampilan multimedia dan menghasilkan karya capstone yang siap dipresentasikan.',
            'action_label' => 'Masuk untuk Bergabung',
            'action_url' => route('login'),
        ];

        return response()
            ->view('mentoring', [
                'mentoringFields' => MentoringField::orderBy('field_name')->get(),
                'dashboardRole' => $role,
                'mentoringContext' => $context,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function showcase(Request $request)
    {
        $showcasePages = [
            'Peserta' => [
                'view' => 'showcase-role.peserta',
                'eyebrow' => 'Inspirasi Capstone',
                'heading' => 'Temukan Inspirasi Karya',
                'description' => 'Jelajahi karya yang telah dipublikasikan dan temukan inspirasi untuk mengembangkan capstone Anda.',
                'action_label' => 'Ajukan Karya',
                'action_url' => route('dashboard.peserta.project.create'),
            ],
            'Mentor' => [
                'view' => 'showcase-role.mentor',
                'eyebrow' => 'Apresiasi Karya Mahasiswa',
                'heading' => 'Showcase Karya Mahasiswa',
                'description' => 'Jelajahi hasil capstone mahasiswa yang telah dipublikasikan sebagai bagian dari perjalanan mentoring.',
                'action_label' => 'Buka Dashboard Mentor',
                'action_url' => route('dashboard.mentor'),
            ],
            'Pengurus KSM' => [
                'view' => 'showcase-role.pengurus',
                'eyebrow' => 'Publikasi KSM Multimedia',
                'heading' => 'Showcase Karya Mahasiswa',
                'description' => 'Jelajahi dan pantau karya capstone yang telah dipublikasikan kepada sivitas akademika.',
                'action_label' => 'Buka Dashboard Pengurus',
                'action_url' => route('dashboard.pengurus'),
            ],
        ];
        $dashboardRole = auth()->user()?->role?->role_name;
        $showcaseView = $showcasePages[$dashboardRole]['view'] ?? 'showcase';
        $showcaseContext = $showcasePages[$dashboardRole] ?? [
            'eyebrow' => 'Portfolio Capstone Tahunan',
            'heading' => 'Showcase Karya Mahasiswa',
            'description' => 'Eksplorasi inovasi digital, game interaktif, riset antarmuka, dan perancangan visual terbaik hasil karya sivitas akademika KSM Multimedia.',
            'action_label' => null,
            'action_url' => null,
        ];

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'field' => ['nullable', 'string', Rule::exists('mentoring_fields', 'field_id')],
        ]);

        $projectsQuery = Project::query()
            ->with(['mentoringField', 'period', 'creator', 'publishedVersion'])
            ->where('status', 'Published');

        if (! empty($filters['q'])) {
            $search = $filters['q'];
            $projectsQuery->where(function (EloquentBuilder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('mentoringField', fn (EloquentBuilder $fieldQuery) => $fieldQuery->where('field_name', 'like', "%{$search}%"))
                    ->orWhereHas('creator', fn (EloquentBuilder $creatorQuery) => $creatorQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereExists(function (Builder $memberQuery) use ($search): void {
                        $memberQuery->selectRaw('1')
                            ->from('project_members')
                            ->join('users', 'users.user_id', '=', 'project_members.user_id')
                            ->whereColumn('project_members.project_id', 'projects.project_id')
                            ->where('users.name', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['field'])) {
            $fieldId = $filters['field'];
            $projectsQuery->where(function (EloquentBuilder $query) use ($fieldId): void {
                $query->whereHas(
                    'publishedVersion',
                    fn (EloquentBuilder $versionQuery) => $versionQuery->where('field_id_snapshot', $fieldId),
                )->orWhere(function (EloquentBuilder $legacyQuery) use ($fieldId): void {
                    $legacyQuery->where('field_id', $fieldId)
                        ->where(function (EloquentBuilder $snapshotQuery): void {
                            $snapshotQuery->whereDoesntHave('publishedVersion')
                                ->orWhereHas(
                                    'publishedVersion',
                                    fn (EloquentBuilder $versionQuery) => $versionQuery->whereNull('field_id_snapshot'),
                                );
                        });
                });
            });
        }

        $projects = $projectsQuery
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(6)
            ->withQueryString();

        $membersByProject = DB::table('project_members')
            ->join('users', 'users.user_id', '=', 'project_members.user_id')
            ->whereIn('project_members.project_id', $projects->getCollection()->pluck('project_id'))
            ->orderBy('users.name')
            ->get(['project_members.project_id', 'users.name'])
            ->groupBy('project_id')
            ->map(fn ($members) => $members->pluck('name'));

        return response()
            ->view($showcaseView, [
                'projects' => $projects,
                'mentoringFields' => MentoringField::orderBy('field_name')->get(),
                'membersByProject' => $membersByProject,
                'filters' => $filters,
                'dashboardRole' => $dashboardRole,
                'showcaseContext' => $showcaseContext,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function showcaseProject(string $projectId)
    {
        $project = Project::query()
            ->with(['mentoringField', 'period', 'creator', 'publishedVersion'])
            ->where('status', 'Published')
            ->findOrFail($projectId);

        $members = DB::table('project_members')
            ->join('users', 'users.user_id', '=', 'project_members.user_id')
            ->where('project_members.project_id', $project->project_id)
            ->orderBy('users.name')
            ->get(['users.name', 'project_members.member_role']);

        $tools = DB::table('project_tools')
            ->where('project_id', $project->project_id)
            ->orderBy('tool_name')
            ->pluck('tool_name');

        $publishedSnapshot = $project->published_snapshot;
        $projectUrl = filter_var($publishedSnapshot['project_link'], FILTER_VALIDATE_URL)
            && in_array(parse_url($publishedSnapshot['project_link'], PHP_URL_SCHEME), ['http', 'https'], true)
                ? $publishedSnapshot['project_link']
                : null;

        return response()
            ->view('showcase-detail', [
                'project' => $project,
                'publishedSnapshot' => $publishedSnapshot,
                'members' => $members,
                'tools' => $tools,
                'projectUrl' => $projectUrl,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
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

        $project = $this->participantProjectsQuery()
            ->with('mentoringField')
            ->latest('created_at')
            ->first();

        return $this->privateView('peserta.dashboard', [
            'project' => $project,
            'activities' => $project ? $this->participantActivities($project->project_id) : collect(),
        ]);
    }

    public function profile()
    {
        $roleName = auth()->user()->role?->role_name;
        abort_unless(in_array($roleName, ['Peserta', 'Mentor'], true), 403, 'Profil ini khusus peserta dan mentor.');

        $data = ['activeMenu' => 'profile'];
        if ($roleName === 'Mentor') {
            $data += [
                'waitingReviewCount' => Project::whereIn('status', ['Submitted', 'In Review'])->count(),
                'revisionIncomingCount' => Project::where('status', 'Resubmitted')->count(),
            ];
        }

        return $this->privateView('profile', $data);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $roleName = auth()->user()->role?->role_name;
        abort_unless(in_array($roleName, ['Peserta', 'Mentor'], true), 403, 'Profil ini khusus peserta dan mentor.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $profileData = $user->profile_data ?? [];
        $profileData['bio'] = filled($validated['bio'] ?? null) ? trim($validated['bio']) : null;

        $user->name = trim($validated['name']);
        $user->profile_data = $profileData;
        $user->save();

        return redirect()
            ->route('dashboard.profile')
            ->with('status', 'Profil berhasil diperbarui.');
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

        if (! empty($filters['q'])) {
            $search = $filters['q'];
            $queueQuery->where(function ($query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhereHas('creator', fn ($creatorQuery) => $creatorQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if (! empty($filters['status'])) {
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

    public function mentorEvaluationHistory()
    {
        $this->authorizeMentor();

        $evaluations = DB::table('reviews')
            ->join('projects', 'projects.project_id', '=', 'reviews.project_id')
            ->leftJoin('mentoring_fields', 'mentoring_fields.field_id', '=', 'projects.field_id')
            ->where('reviews.reviewer_id', auth()->id())
            ->orderByDesc('reviews.created_at')
            ->select([
                'reviews.review_id',
                'reviews.project_id',
                'reviews.comment',
                'reviews.score',
                'reviews.rating',
                'reviews.created_at',
                'projects.title as project_title',
                'mentoring_fields.field_name',
            ])
            ->paginate(10);

        return $this->privateView('mentor.evaluations', [
            'evaluations' => $evaluations,
            'waitingReviewCount' => Project::whereIn('status', ['Submitted', 'In Review'])->count(),
            'revisionIncomingCount' => Project::where('status', 'Resubmitted')->count(),
        ]);
    }

    public function mentorSchedules()
    {
        $this->authorizeMentor();

        $schedules = DB::table('mentoring_schedules')
            ->join('projects', 'projects.project_id', '=', 'mentoring_schedules.project_id')
            ->where('mentoring_schedules.mentor_id', auth()->id())
            ->orderBy('mentoring_schedules.starts_at')
            ->select([
                'mentoring_schedules.schedule_id',
                'mentoring_schedules.project_id',
                'mentoring_schedules.starts_at',
                'mentoring_schedules.duration_minutes',
                'mentoring_schedules.location',
                'mentoring_schedules.meeting_url',
                'mentoring_schedules.notes',
                'mentoring_schedules.status',
                'projects.title as project_title',
            ])
            ->paginate(10);

        $projects = Project::query()
            ->whereIn('status', ['Submitted', 'In Review', 'Revision Required', 'Resubmitted', 'Approved', 'Published'])
            ->orderBy('title')
            ->get(['project_id', 'title']);

        return $this->privateView('mentor.schedules', [
            'schedules' => $schedules,
            'projects' => $projects,
            'waitingReviewCount' => Project::whereIn('status', ['Submitted', 'In Review'])->count(),
            'revisionIncomingCount' => Project::where('status', 'Resubmitted')->count(),
        ]);
    }

    public function storeMentorSchedule(Request $request): RedirectResponse
    {
        $this->authorizeMentor();
        $validated = $this->validateMentorSchedule($request);

        do {
            $scheduleId = 'SCH-'.Str::upper(Str::random(16));
        } while (DB::table('mentoring_schedules')->where('schedule_id', $scheduleId)->exists());

        DB::table('mentoring_schedules')->insert([
            ...$validated,
            'schedule_id' => $scheduleId,
            'mentor_id' => auth()->id(),
            'status' => 'Scheduled',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('dashboard.mentor.schedules')
            ->with('status', 'Jadwal mentoring berhasil dibuat.');
    }

    public function updateMentorSchedule(Request $request, string $scheduleId): RedirectResponse
    {
        $this->authorizeMentor();
        $validated = $this->validateMentorSchedule($request, false);

        $updated = DB::table('mentoring_schedules')
            ->where('schedule_id', $scheduleId)
            ->where('mentor_id', auth()->id())
            ->where('status', 'Scheduled')
            ->update([...$validated, 'updated_at' => now()]);

        abort_unless($updated, 404);

        return redirect()
            ->route('dashboard.mentor.schedules')
            ->with('status', 'Jadwal mentoring berhasil diperbarui.');
    }

    public function cancelMentorSchedule(string $scheduleId): RedirectResponse
    {
        $this->authorizeMentor();

        $updated = DB::table('mentoring_schedules')
            ->where('schedule_id', $scheduleId)
            ->where('mentor_id', auth()->id())
            ->where('status', 'Scheduled')
            ->update(['status' => 'Canceled', 'updated_at' => now()]);

        abort_unless($updated, 404);

        return redirect()
            ->route('dashboard.mentor.schedules')
            ->with('status', 'Jadwal mentoring berhasil dibatalkan.');
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

        if (! empty($filters['field'])) {
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
                'reviews.score',
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
                'reviews.score',
                'reviews.rating',
                'reviews.created_at',
                'users.name as reviewer_name',
                'projects.title as project_title',
            ]) as $review) {
            $activities->push([
                'type' => 'review',
                'title' => $review->reviewer_name.' menyelesaikan evaluasi mentor untuk "'.$review->project_title.'".',
                'detail' => $review->score !== null
                    ? 'Nilai akhir: '.$review->score.'/100'
                    : ($review->rating !== null ? 'Nilai akhir legacy: '.((int) $review->rating * 20).'/100' : 'Review tersimpan'),
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
            'curationScore' => DB::table('reviews')
                ->where(fn (Builder $query) => $query->whereNotNull('score')->orWhereNotNull('rating'))
                ->avg(DB::raw('COALESCE(score, rating * 20)')),
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

        if (! empty($filters['field'])) {
            $query->where('field_id', $filters['field']);
        }
        if (! empty($filters['q'])) {
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

    public function pengurusUsers(Request $request)
    {
        $this->authorizePengurus();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => [
                'nullable',
                'string',
                Rule::exists('roles', 'role_id')->where(fn (Builder $query) => $query->whereIn('role_name', ['Peserta', 'Mentor', 'Pengurus KSM'])),
            ],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);
        $query = User::query()
            ->with('role')
            ->whereHas('role', fn (EloquentBuilder $roleQuery) => $roleQuery->whereIn('role_name', ['Peserta', 'Mentor', 'Pengurus KSM']));

        if (! empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function (EloquentBuilder $userQuery) use ($search): void {
                $userQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('user_id', 'like', "%{$search}%");
            });
        }
        if (! empty($filters['role'])) {
            $query->where('role_id', $filters['role']);
        }
        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return $this->privateView('pengurus.users', [
            'users' => $query->orderBy('name')->paginate(12)->withQueryString(),
            'roles' => DB::table('roles')->whereIn('role_name', ['Peserta', 'Mentor', 'Pengurus KSM'])->orderBy('role_name')->get(),
            'filters' => $filters,
            'activeMenu' => 'users',
            'activePeriod' => DB::table('mentoring_periods')->where('is_active', true)->orderByDesc('start_date')->value('period_name'),
            'pendingApprovalCount' => Project::where('status', 'Approved')->count(),
            'userCounts' => [
                'all' => User::whereHas('role', fn (EloquentBuilder $roleQuery) => $roleQuery->whereIn('role_name', ['Peserta', 'Mentor', 'Pengurus KSM']))->count(),
                'active' => User::where('is_active', true)->whereHas('role', fn (EloquentBuilder $roleQuery) => $roleQuery->whereIn('role_name', ['Peserta', 'Mentor', 'Pengurus KSM']))->count(),
                'inactive' => User::where('is_active', false)->whereHas('role', fn (EloquentBuilder $roleQuery) => $roleQuery->whereIn('role_name', ['Peserta', 'Mentor', 'Pengurus KSM']))->count(),
            ],
        ]);
    }

    public function updatePengurusUser(Request $request, string $userId): RedirectResponse
    {
        $this->authorizePengurus();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId, 'user_id')],
            'role_id' => [
                'required',
                'string',
                Rule::exists('roles', 'role_id')->where(fn (Builder $query) => $query->whereIn('role_name', ['Peserta', 'Mentor', 'Pengurus KSM'])),
            ],
        ]);

        DB::transaction(function () use ($userId, $validated): void {
            $user = User::query()
                ->with('role')
                ->where('user_id', $userId)
                ->whereHas('role', fn (EloquentBuilder $roleQuery) => $roleQuery->whereIn('role_name', ['Peserta', 'Mentor', 'Pengurus KSM']))
                ->lockForUpdate()
                ->firstOrFail();
            $newRole = DB::table('roles')->where('role_id', $validated['role_id'])->firstOrFail();

            if ($user->user_id === auth()->id() && $newRole->role_name !== 'Pengurus KSM') {
                abort(409, 'Anda tidak dapat mengubah role akun sendiri.');
            }
            if ($user->is_active && $user->role?->role_name === 'Pengurus KSM' && $newRole->role_name !== 'Pengurus KSM') {
                $this->ensureAnotherActivePengurusExists($user->user_id);
            }

            $oldRole = $user->role?->role_name;
            $user->forceFill($validated)->save();

            $this->recordPengurusAudit(
                'USER_UPDATED',
                'Memperbarui akun '.$user->user_id.': nama, email, atau role berubah. '.
                    'Role: '.($oldRole ?: 'Tidak diketahui').' → '.$newRole->role_name.'.',
            );
        });

        return redirect()
            ->route('dashboard.pengurus.users')
            ->with('status', 'Data pengguna berhasil diperbarui.');
    }

    public function togglePengurusUserStatus(string $userId): RedirectResponse
    {
        $this->authorizePengurus();

        DB::transaction(function () use ($userId): void {
            $user = User::query()
                ->with('role')
                ->where('user_id', $userId)
                ->whereHas('role', fn (EloquentBuilder $roleQuery) => $roleQuery->whereIn('role_name', ['Peserta', 'Mentor', 'Pengurus KSM']))
                ->lockForUpdate()
                ->firstOrFail();
            abort_if($user->user_id === auth()->id(), 409, 'Anda tidak dapat menonaktifkan akun sendiri.');

            if ($user->is_active && $user->role?->role_name === 'Pengurus KSM') {
                $this->ensureAnotherActivePengurusExists($user->user_id);
            }

            $user->forceFill(['is_active' => ! $user->is_active])->save();
            $this->recordPengurusAudit(
                $user->is_active ? 'USER_ACTIVATED' : 'USER_DEACTIVATED',
                'Mengubah status akun '.$user->user_id.' menjadi '.($user->is_active ? 'aktif' : 'nonaktif').'.',
            );
        });

        return redirect()
            ->route('dashboard.pengurus.users')
            ->with('status', 'Status akun berhasil diperbarui.');
    }

    public function pengurusSettings()
    {
        $this->authorizePengurus();

        return $this->privateView('pengurus.settings', [
            'periods' => DB::table('mentoring_periods')->orderByDesc('start_date')->get(),
            'activePeriod' => DB::table('mentoring_periods')->where('is_active', true)->orderByDesc('start_date')->value('period_name'),
            'pendingApprovalCount' => Project::where('status', 'Approved')->count(),
            'activeMenu' => 'settings',
        ]);
    }

    public function storePengurusPeriod(Request $request): RedirectResponse
    {
        $this->authorizePengurus();
        $validated = $this->validateMentoringPeriod($request, 'periodCreate');

        DB::transaction(function () use ($validated): void {
            do {
                $periodId = 'PER-'.Str::upper(Str::random(16));
            } while (DB::table('mentoring_periods')->where('period_id', $periodId)->exists());

            DB::table('mentoring_periods')->insert([
                'period_id' => $periodId,
                'period_name' => $validated['period_name'],
                'is_active' => false,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->recordPengurusAudit(
                'MENTORING_PERIOD_CREATED',
                'Menambahkan periode mentoring "'.$validated['period_name'].'" ('.$periodId.').',
            );
        });

        return redirect()
            ->route('dashboard.pengurus.settings')
            ->with('status', 'Periode mentoring baru berhasil ditambahkan.');
    }

    public function updatePengurusPeriod(Request $request, string $periodId): RedirectResponse
    {
        $this->authorizePengurus();
        $validated = $this->validateMentoringPeriod($request, 'periodEdit_'.$periodId, $periodId);

        DB::transaction(function () use ($periodId, $validated): void {
            $period = DB::table('mentoring_periods')
                ->where('period_id', $periodId)
                ->lockForUpdate()
                ->first();
            abort_unless($period, 404);

            DB::table('mentoring_periods')
                ->where('period_id', $periodId)
                ->update([
                    'period_name' => $validated['period_name'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'updated_at' => now(),
                ]);

            $this->recordPengurusAudit(
                'MENTORING_PERIOD_UPDATED',
                'Memperbarui detail periode mentoring "'.$validated['period_name'].'" ('.$periodId.').',
            );
        }, 5);

        return redirect()
            ->route('dashboard.pengurus.settings')
            ->with('status', 'Detail periode mentoring berhasil diperbarui.');
    }

    public function updatePengurusActivePeriod(Request $request): RedirectResponse
    {
        $this->authorizePengurus();
        $validated = $request->validate([
            'period_id' => ['required', 'string', Rule::exists('mentoring_periods', 'period_id')],
        ]);

        DB::transaction(function () use ($validated): void {
            $periods = DB::table('mentoring_periods')
                ->orderBy('period_id')
                ->lockForUpdate()
                ->get(['period_id', 'period_name']);
            $period = $periods->firstWhere('period_id', $validated['period_id']);
            abort_unless($period, 404);

            DB::table('mentoring_periods')->update(['is_active' => false, 'updated_at' => now()]);
            DB::table('mentoring_periods')
                ->where('period_id', $period->period_id)
                ->update(['is_active' => true, 'updated_at' => now()]);

            $this->recordPengurusAudit(
                'ACTIVE_PERIOD_CHANGED',
                'Mengatur periode mentoring aktif menjadi "'.$period->period_name.'".',
            );
        }, 5);

        return redirect()
            ->route('dashboard.pengurus.settings')
            ->with('status', 'Periode mentoring aktif berhasil diperbarui.');
    }

    private function validateMentoringPeriod(Request $request, string $errorBag, ?string $periodId = null): array
    {
        $periodName = $request->input('period_name');
        if (is_string($periodName)) {
            $request->merge(['period_name' => trim($periodName)]);
        }

        $uniqueName = Rule::unique('mentoring_periods', 'period_name');
        if ($periodId !== null) {
            $uniqueName->ignore($periodId, 'period_id');
        }

        return $request->validateWithBag($errorBag, [
            'period_name' => ['required', 'string', 'max:255', $uniqueName],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ], [
            'period_name.unique' => 'Nama periode sudah digunakan.',
            'start_date.date_format' => 'Tanggal mulai harus berupa tanggal yang valid.',
            'end_date.date_format' => 'Tanggal selesai harus berupa tanggal yang valid.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama dengan atau setelah tanggal mulai.',
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
        $period = DB::table('mentoring_periods')->where('period_id', $project->period_id)->first();
        $latestVersion = ProjectVersion::sortChronologically(DB::table('project_versions')
            ->where('project_id', $projectId)
            ->get())->last();
        $files = $latestVersion
            ? DB::table('project_version_files')
                ->where('version_id', $latestVersion->version_id)
                ->orderBy('created_at')
                ->get()
            : collect();
        if ($files->isEmpty() && $latestVersion?->title_snapshot === null) {
            $files = DB::table('project_files')
                ->where('project_id', $projectId)
                ->orderBy('created_at')
                ->get(['file_path', 'file_name as file_name_snapshot']);
        }
        $approvalSnapshot = [
            'title' => $latestVersion?->title_snapshot ?? $project->title,
            'description' => $latestVersion?->description_snapshot ?? $project->description,
            'field_name' => $latestVersion?->field_name_snapshot ?? $project->mentoringField?->field_name,
            'period_name' => $latestVersion?->period_name_snapshot ?? $period?->period_name,
            'project_link' => $latestVersion?->project_link_snapshot ?? $project->project_link,
            'thumbnail_path' => $latestVersion?->title_snapshot !== null
                ? $latestVersion->thumbnail_path_snapshot
                : $project->thumbnail_url,
        ];
        $projectUrl = filter_var($approvalSnapshot['project_link'], FILTER_VALIDATE_URL)
            && in_array(parse_url($approvalSnapshot['project_link'], PHP_URL_SCHEME), ['http', 'https'], true)
                ? $approvalSnapshot['project_link']
                : null;
        $latestReview = DB::table('reviews')
            ->join('users', 'users.user_id', '=', 'reviews.reviewer_id')
            ->leftJoin('project_versions', 'project_versions.version_id', '=', 'reviews.version_id')
            ->where('reviews.project_id', $projectId)
            ->orderByDesc('reviews.created_at')
            ->first([
                'reviews.comment',
                'reviews.score',
                'reviews.rating',
                'reviews.version_id',
                'reviews.decision',
                'reviews.created_at',
                'users.name as reviewer_name',
                'project_versions.version_number as review_version_number',
            ]);
        $versions = ProjectVersion::sortChronologically(DB::table('project_versions')
            ->where('project_id', $projectId)
            ->get());
        $publicationApprover = $project->publication_approved_by
            ? User::query()->find($project->publication_approved_by)
            : null;

        return $this->privateView('pengurus.approval-detail', [
            'project' => $project,
            'members' => $members,
            'tools' => $tools,
            'files' => $files,
            'period' => $period,
            'latestVersion' => $latestVersion,
            'approvalSnapshot' => $approvalSnapshot,
            'projectUrl' => $projectUrl,
            'latestReview' => $latestReview,
            'versions' => $versions,
            'publicationApprover' => $publicationApprover,
            'pendingApprovalCount' => Project::where('status', 'Approved')->count(),
            'activeMenu' => 'approvals',
        ]);
    }

    public function storePengurusApproval(Request $request, string $projectId): RedirectResponse
    {
        $this->authorizePengurus();

        $validated = $request->validate([
            'action' => ['required', Rule::in(['publish', 'request_revision'])],
            'showcase' => ['nullable', 'boolean'],
        ]);

        if ($validated['action'] === 'request_revision') {
            $validated['comment'] = $request->validate([
                'comment' => ['required', 'string', 'max:1000'],
            ])['comment'];
        }

        DB::transaction(function () use ($projectId, $validated): void {
            $project = Project::query()
                ->where('project_id', $projectId)
                ->whereIn('status', ['Approved', 'Published'])
                ->lockForUpdate()
                ->firstOrFail();
            $version = ProjectVersion::sortChronologically(DB::table('project_versions')
                ->where('project_id', $project->project_id)
                ->get())->last();
            abort_if(! $version, 409, 'Project ini belum memiliki versi submission yang dapat disetujui.');
            abort_if($project->status === 'Published', 409, 'Project ini sudah dipublikasikan.');

            if ($validated['action'] === 'request_revision') {
                do {
                    $reviewId = 'REV-'.Str::upper(Str::random(16));
                } while (DB::table('reviews')->where('review_id', $reviewId)->exists());

                DB::table('reviews')->insert([
                    'review_id' => $reviewId,
                    'project_id' => $project->project_id,
                    'reviewer_id' => auth()->id(),
                    'version_id' => $version->version_id,
                    'comment' => $validated['comment'],
                    'rating' => null,
                    'decision' => 'Revision Required',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $project->forceFill([
                    'status' => 'Revision Required',
                    'publication_approved_by' => null,
                    'publication_approved_at' => null,
                ])->save();
                $this->recordPengurusAudit(
                    'REVISION_REQUESTED',
                    'Pengurus meminta revisi untuk project "'.$project->title.'" versi '.
                        $version->version_number.' ('.$version->version_id.').',
                    $project->project_id,
                );

                return;
            }

            $publishedAt = now();
            $project->forceFill([
                'status' => 'Published',
                'publication_approved_by' => auth()->id(),
                'publication_approved_at' => $publishedAt,
                'published_at' => $publishedAt,
                'published_version_id' => $version->version_id,
                'is_featured' => (bool) ($validated['showcase'] ?? false),
            ])->save();
            DB::table('project_versions')
                ->where('version_id', $version->version_id)
                ->update([
                    'approved_by' => auth()->id(),
                    'approved_at' => $publishedAt,
                    'is_published' => true,
                    'published_at' => $publishedAt,
                    'updated_at' => $publishedAt,
                ]);

            $this->recordPengurusAudit(
                'PUBLICATION_APPROVED',
                'Pengurus memberikan approval resmi untuk project "'.$project->title.'" versi '.
                    $version->version_number.' ('.$version->version_id.').',
                $project->project_id,
            );
            $this->recordPengurusAudit(
                'PROJECT_PUBLISHED',
                'Project "'.$project->title.'" dipublikasikan ke showcase. '.
                    'Visibilitas beranda: '.($project->is_featured ? 'aktif' : 'nonaktif').
                    '. Versi: '.$version->version_number.' ('.$version->version_id.').',
                $project->project_id,
            );
        });

        return redirect()
            ->route($validated['action'] === 'request_revision'
                ? 'dashboard.pengurus.approvals'
                : 'dashboard.pengurus.approval.show', $validated['action'] === 'request_revision' ? [] : $projectId)
            ->with('status', $validated['action'] === 'publish'
                ? 'Project berhasil dipublikasikan ke showcase.'
                : 'Catatan revisi berhasil dikirim kepada peserta.');
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

        if (! empty($filters['action'])) {
            $query->where('audit_logs.action', $filters['action']);
        }
        if (! empty($filters['q'])) {
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
        $latestVersion = ProjectVersion::sortChronologically(DB::table('project_versions')
            ->where('project_id', $project->project_id)
            ->get())->last();
        $files = $latestVersion
            ? DB::table('project_version_files')->where('version_id', $latestVersion->version_id)->orderBy('created_at')->get()
            : collect();
        if ($files->isEmpty() && $latestVersion?->title_snapshot === null) {
            $files = DB::table('project_files')
                ->where('project_id', $project->project_id)
                ->orderBy('created_at')
                ->get(['file_path', 'file_name as file_name_snapshot']);
        }
        $reviewSnapshot = [
            'title' => $latestVersion?->title_snapshot ?? $project->title,
            'description' => $latestVersion?->description_snapshot ?? $project->description,
            'field_name' => $latestVersion?->field_name_snapshot ?? $project->mentoringField?->field_name,
            'period_name' => $latestVersion?->period_name_snapshot ?? $period?->period_name,
            'project_link' => $latestVersion?->project_link_snapshot ?? $project->project_link,
            'thumbnail_path' => $latestVersion?->title_snapshot !== null
                ? $latestVersion->thumbnail_path_snapshot
                : $project->thumbnail_url,
        ];
        $projectUrl = filter_var($reviewSnapshot['project_link'], FILTER_VALIDATE_URL)
            && in_array(parse_url($reviewSnapshot['project_link'], PHP_URL_SCHEME), ['http', 'https'], true)
                ? $reviewSnapshot['project_link']
                : null;

        return $this->privateView('mentor.review-project', compact(
            'project',
            'period',
            'members',
            'tools',
            'files',
            'latestVersion',
            'reviewSnapshot',
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
            'score' => ['required', 'integer', 'between:0,100'],
            'comment' => ['required', 'string', 'max:1000'],
            'decision' => ['required', Rule::in(['Revision Required', 'Approved'])],
        ]);

        DB::transaction(function () use ($project, $validated): void {
            $project = $this->mentorReviewQueueQuery()
                ->where('projects.project_id', $project->project_id)
                ->lockForUpdate()
                ->firstOrFail();
            $version = ProjectVersion::sortChronologically(DB::table('project_versions')
                ->where('project_id', $project->project_id)
                ->get())->last();
            abort_if(! $version, 409, 'Project ini belum memiliki versi submission yang dapat direview.');
            $reviewedAt = now();

            do {
                $reviewId = 'REV-'.Str::upper(Str::random(16));
            } while (DB::table('reviews')->where('review_id', $reviewId)->exists());

            DB::table('reviews')->insert([
                'review_id' => $reviewId,
                'project_id' => $project->project_id,
                'reviewer_id' => auth()->id(),
                'version_id' => $version->version_id,
                'comment' => $validated['comment'],
                'score' => (int) $validated['score'],
                'decision' => $validated['decision'],
                'created_at' => $reviewedAt,
                'updated_at' => $reviewedAt,
            ]);

            Project::query()
                ->where('project_id', $project->project_id)
                ->update([
                    'status' => $validated['decision'],
                    'updated_at' => now(),
                ]);

            $this->recordAudit(
                'MENTOR_REVIEW',
                'Mentor meninjau project "'.$project->title.'" versi '.
                    $version->version_number.' ('.$version->version_id.'): '.$validated['decision'].'.',
                $project->project_id,
            );
            if ($validated['decision'] === 'Revision Required') {
                $this->recordAudit(
                    'REVISION_REQUESTED',
                    'Mentor meminta revisi project "'.$project->title.'" pada versi '.
                        $version->version_number.' ('.$version->version_id.').',
                    $project->project_id,
                );
            }
        });

        return redirect()
            ->route('dashboard.mentor')
            ->with('status', 'Review project berhasil disimpan.');
    }

    public function participantProjects()
    {
        $this->authorizeParticipant();

        $project = $this->participantProjectsQuery()
            ->latest('created_at')
            ->first();

        if (! $project) {
            return redirect()->route('dashboard.peserta.project.create');
        }

        if ($project->status === 'Draft' && $project->created_by === auth()->id()) {
            return redirect()->route('dashboard.peserta.project.edit', $project->project_id);
        }

        return redirect()->route('dashboard.peserta.project.show', $project->project_id);
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
            ->leftJoin('project_versions', 'project_versions.version_id', '=', 'reviews.version_id')
            ->where('reviews.project_id', $project->project_id)
            ->latest('reviews.created_at')
            ->get([
                'reviews.comment',
                'reviews.score',
                'reviews.rating',
                'reviews.decision',
                'reviews.version_id',
                'project_versions.version_number',
                'reviews.created_at',
                'users.name as reviewer_name',
            ]);
        $versions = ProjectVersion::sortChronologically(DB::table('project_versions')
            ->where('project_id', $project->project_id)
            ->get())->reverse()->values();
        $files = DB::table('project_files')
            ->where('project_id', $project->project_id)
            ->latest('created_at')
            ->get();
        $canResubmit = $project->created_by === auth()->id();
        $activities = $this->participantActivities($project->project_id);

        return $this->privateView('peserta.detail-project', compact(
            'project',
            'period',
            'members',
            'tools',
            'reviews',
            'versions',
            'files',
            'canResubmit',
            'activities',
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
        $revisionFilePath = $revisionFile
            ? $this->storeProjectMedia(
                $revisionFile,
                "projects/{$project->project_id}/revisions",
                'revision_file',
            )
            : null;

        try {
            DB::transaction(function () use ($project, $validated, $revisionFile, $revisionFilePath): void {
                $project = Project::query()
                    ->whereKey($project->project_id)
                    ->lockForUpdate()
                    ->firstOrFail();
                abort_unless(
                    $project->created_by === auth()->id() && $project->status === 'Revision Required',
                    403,
                    'Project ini tidak sedang menunggu revisi.',
                );

                $submittedAt = now();
                $project->update([
                    'project_link' => $validated['project_link'],
                    'status' => 'Resubmitted',
                    'submitted_at' => $submittedAt,
                    'publication_approved_by' => null,
                    'publication_approved_at' => null,
                    'published_version_id' => null,
                ]);

                $versionFileIds = $project->thumbnail_url
                    ? DB::table('project_files')
                        ->where('project_id', $project->project_id)
                        ->where('file_path', $project->thumbnail_url)
                        ->pluck('file_id')
                        ->all()
                    : [];

                if ($revisionFile && $revisionFilePath) {
                    do {
                        $fileId = 'FILE-'.Str::upper(Str::random(15));
                    } while (DB::table('project_files')->where('file_id', $fileId)->exists());

                    DB::table('project_files')->insert([
                        'file_id' => $fileId,
                        'project_id' => $project->project_id,
                        'file_name' => Str::limit(basename($revisionFile->getClientOriginalName()), 255, ''),
                        'file_path' => $revisionFilePath,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $versionFileIds[] = $fileId;
                }

                $version = $this->createProjectVersion(
                    $project,
                    $validated['revision_notes'],
                    $revisionFilePath,
                    $versionFileIds,
                    $submittedAt,
                );
                $this->recordAudit(
                    'PROJECT_RESUBMITTED',
                    'Peserta mengirim ulang project "'.$project->title.'" sebagai versi '.
                        $version->version_number.' ('.$version->version_id.').',
                    $project->project_id,
                );
            });
        } catch (Throwable $exception) {
            if ($revisionFilePath) {
                Storage::disk(config('filesystems.project_media_disk', 'public'))->delete($revisionFilePath);
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

        $existingDraft = Project::query()
            ->where('created_by', auth()->id())
            ->where('status', 'Draft')
            ->latest('created_at')
            ->first();

        if ($existingDraft) {
            return redirect()->route('dashboard.peserta.project.edit', $existingDraft->project_id);
        }

        if ($this->hasProjectInvolvement(auth()->id())) {
            return redirect()
                ->route('dashboard.peserta')
                ->with('status', 'Kamu sudah memiliki project.');
        }

        return response()
            ->view('peserta.create-project', [
                'mentoringFields' => MentoringField::orderBy('field_name')->get(),
                'mentoringPeriods' => DB::table('mentoring_periods')
                    ->where('is_active', true)
                    ->orderByDesc('start_date')
                    ->get(),
                'participants' => $this->availableProjectParticipants(),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function editProject(string $projectId)
    {
        $this->authorizeParticipant();

        $project = Project::query()
            ->where('project_id', $projectId)
            ->where('created_by', auth()->id())
            ->where('status', 'Draft')
            ->firstOrFail();

        $thumbnailFile = DB::table('project_files')
            ->where('project_id', $project->project_id)
            ->get()
            ->first(fn ($file) => $file->file_path === $project->thumbnail_url
                || Storage::disk('public')->url($file->file_path) === $project->thumbnail_url);

        return $this->privateView('peserta.create-project', [
            'mentoringFields' => MentoringField::orderBy('field_name')->get(),
            'mentoringPeriods' => DB::table('mentoring_periods')
                ->where('is_active', true)
                ->orderByDesc('start_date')
                ->get(),
            'participants' => $this->availableProjectParticipants($project->project_id),
            'project' => $project,
            'members' => DB::table('project_members')
                ->where('project_id', $project->project_id)
                ->orderBy('project_member_id')
                ->get(['user_id', 'member_role'])
                ->map(fn ($member) => (array) $member)
                ->all(),
            'tools' => DB::table('project_tools')
                ->where('project_id', $project->project_id)
                ->orderBy('created_at')
                ->pluck('tool_name')
                ->all(),
            'thumbnailName' => $thumbnailFile?->file_name,
        ]);
    }

    public function storeProject(Request $request): RedirectResponse
    {
        $this->authorizeParticipant();

        $validated = $this->validateProject($request);

        $submissionAction = $validated['submission_action'];
        unset($validated['submission_action']);

        $members = $validated['members'] ?? [];
        $tools = array_map('trim', $validated['tools'] ?? []);
        unset($validated['members'], $validated['tools'], $validated['thumbnail']);

        do {
            $projectId = 'PROJ-'.Str::upper(Str::random(15));
        } while (Project::whereKey($projectId)->exists());
        $thumbnail = $request->file('thumbnail');
        $thumbnailPath = $thumbnail
            ? $this->storeProjectMedia($thumbnail, "projects/{$projectId}", 'thumbnail')
            : null;

        try {
            DB::transaction(function () use ($validated, $members, $tools, $projectId, $thumbnail, $thumbnailPath, $submissionAction): void {
                $userIds = collect([(string) auth()->id(), ...array_column($members, 'user_id')])
                    ->unique()
                    ->sort()
                    ->values();
                User::query()
                    ->whereIn('user_id', $userIds)
                    ->orderBy('user_id')
                    ->lockForUpdate()
                    ->get();

                if ($this->hasProjectInvolvement((string) auth()->id())) {
                    throw ValidationException::withMessages([
                        'project' => 'Kamu sudah memiliki project.',
                    ]);
                }

                foreach ($members as $index => $member) {
                    if ($this->hasProjectInvolvement($member['user_id'])) {
                        throw ValidationException::withMessages([
                            "members.{$index}.user_id" => 'Peserta sudah memiliki atau menjadi anggota project lain.',
                        ]);
                    }
                }

                $submittedAt = $submissionAction === 'Submitted' ? now() : null;
                $project = Project::create([
                    ...$validated,
                    'project_id' => $projectId,
                    'created_by' => auth()->id(),
                    'thumbnail_url' => $thumbnailPath,
                    'status' => $submissionAction,
                    'submitted_at' => $submittedAt,
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
                        $toolId = 'TOOL-'.Str::upper(Str::random(15));
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
                        $fileId = 'FILE-'.Str::upper(Str::random(15));
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

                if ($submissionAction === 'Submitted') {
                    $version = $this->createProjectVersion(
                        $project,
                        null,
                        null,
                        DB::table('project_files')->where('project_id', $projectId)->pluck('file_id')->all(),
                        $submittedAt,
                    );
                    $this->recordAudit(
                        'PROJECT_SUBMITTED',
                        'Peserta mengajukan project "'.$project->title.'" sebagai versi '.
                            $version->version_number.' ('.$version->version_id.').',
                        $projectId,
                    );
                }
            });
        } catch (Throwable $exception) {
            if ($thumbnailPath) {
                Storage::disk(config('filesystems.project_media_disk', 'public'))->delete($thumbnailPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('dashboard.peserta')
            ->with('status', $submissionAction === 'Draft'
                ? 'Draft project berhasil disimpan.'
                : 'Project berhasil diajukan.');
    }

    public function updateProject(Request $request, string $projectId): RedirectResponse
    {
        $this->authorizeParticipant();

        $project = Project::query()
            ->where('project_id', $projectId)
            ->first();

        abort_unless(
            $project && $project->created_by === auth()->id() && $project->status === 'Draft',
            403,
        );

        $validated = $this->validateProject($request, filled($project->thumbnail_url), $project->project_id);
        $submissionAction = $validated['submission_action'];
        unset($validated['submission_action']);

        $members = $validated['members'] ?? [];
        $tools = array_map('trim', $validated['tools'] ?? []);
        unset($validated['members'], $validated['tools'], $validated['thumbnail']);
        $thumbnail = $request->file('thumbnail');
        $thumbnailPath = $thumbnail
            ? $this->storeProjectMedia($thumbnail, "projects/{$project->project_id}", 'thumbnail')
            : null;

        try {
            $oldThumbnailPath = DB::transaction(function () use ($projectId, $validated, $members, $tools, $thumbnail, $thumbnailPath, $submissionAction): ?string {
                $project = Project::query()
                    ->where('project_id', $projectId)
                    ->lockForUpdate()
                    ->first();

                abort_unless(
                    $project && $project->created_by === auth()->id() && $project->status === 'Draft',
                    403,
                );

                $oldThumbnailFile = $project->thumbnail_url
                    ? DB::table('project_files')
                        ->where('project_id', $project->project_id)
                        ->get()
                        ->first(fn ($file) => $file->file_path === $project->thumbnail_url
                            || Storage::disk('public')->url($file->file_path) === $project->thumbnail_url)
                    : null;
                $oldThumbnailPath = $oldThumbnailFile?->file_path;

                $submittedAt = $submissionAction === 'Submitted' ? now() : $project->submitted_at;
                $project->update([
                    ...$validated,
                    'thumbnail_url' => $thumbnailPath
                        ? $thumbnailPath
                        : $project->thumbnail_url,
                    'status' => $submissionAction === 'Submitted' ? 'Submitted' : $project->status,
                    'submitted_at' => $submittedAt,
                ]);

                DB::table('project_members')->where('project_id', $project->project_id)->delete();
                foreach ($members as $member) {
                    DB::table('project_members')->insert([
                        ...$member,
                        'project_id' => $project->project_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('project_tools')->where('project_id', $project->project_id)->delete();
                foreach ($tools as $tool) {
                    do {
                        $toolId = 'TOOL-'.Str::upper(Str::random(15));
                    } while (DB::table('project_tools')->where('tool_id', $toolId)->exists());

                    DB::table('project_tools')->insert([
                        'tool_id' => $toolId,
                        'project_id' => $project->project_id,
                        'tool_name' => $tool,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if ($thumbnail && $thumbnailPath) {
                    $fileData = [
                        'file_name' => Str::limit(basename($thumbnail->getClientOriginalName()), 255, ''),
                        'file_path' => $thumbnailPath,
                        'updated_at' => now(),
                    ];

                    if ($oldThumbnailFile) {
                        DB::table('project_files')
                            ->where('file_id', $oldThumbnailFile->file_id)
                            ->update($fileData);
                    } else {
                        do {
                            $fileId = 'FILE-'.Str::upper(Str::random(15));
                        } while (DB::table('project_files')->where('file_id', $fileId)->exists());

                        DB::table('project_files')->insert([
                            ...$fileData,
                            'file_id' => $fileId,
                            'project_id' => $project->project_id,
                            'created_at' => now(),
                        ]);
                    }
                }

                if ($submissionAction === 'Submitted') {
                    $version = $this->createProjectVersion(
                        $project,
                        null,
                        null,
                        DB::table('project_files')->where('project_id', $project->project_id)->pluck('file_id')->all(),
                        $submittedAt,
                    );
                    $this->recordAudit(
                        'PROJECT_SUBMITTED',
                        'Peserta mengajukan project "'.$project->title.'" dari draft sebagai versi '.
                            $version->version_number.' ('.$version->version_id.').',
                        $project->project_id,
                    );
                } else {
                    $this->recordAudit(
                        'DRAFT_SAVED',
                        'Menyimpan draft project "'.$project->title.'".',
                        $project->project_id,
                    );
                }

                return $oldThumbnailPath;
            });
        } catch (Throwable $exception) {
            if ($thumbnailPath) {
                Storage::disk(config('filesystems.project_media_disk', 'public'))->delete($thumbnailPath);
            }

            throw $exception;
        }

        if ($oldThumbnailPath && $oldThumbnailPath !== $thumbnailPath) {
            Storage::disk(config('filesystems.project_media_disk', 'public'))->delete($oldThumbnailPath);
        }

        return redirect()
            ->route('dashboard.peserta')
            ->with('status', $submissionAction === 'Draft'
            ? 'Draft project berhasil disimpan.'
            : 'Project berhasil diajukan.');
    }

    private function storeProjectMedia(UploadedFile $file, string $directory, string $field): string
    {
        $disk = config('filesystems.project_media_disk', 'public');
        $expectedPath = trim($directory, '/').'/'.$file->hashName();

        try {
            $path = $file->store($directory, $disk);

            if (! is_string($path) || ! str_starts_with($path, 'projects/') || trim($path) === '') {
                throw new \RuntimeException('The uploaded file was not stored at a valid project path.');
            }

            return $path;
        } catch (Throwable $exception) {
            Log::error($exception->getMessage());

            $pathsToDelete = [$expectedPath];
            if (isset($path) && is_string($path) && str_starts_with($path, 'projects/') && $path !== $expectedPath) {
                $pathsToDelete[] = $path;
            }

            foreach ($pathsToDelete as $pathToDelete) {
                try {
                    Storage::disk($disk)->delete($pathToDelete);
                } catch (Throwable $cleanupException) {
                    Log::error($cleanupException->getMessage());
                }
            }

            throw ValidationException::withMessages([
                $field => 'Gagal menyimpan file, coba lagi.',
            ]);
        }
    }

    private function validateProject(Request $request, bool $hasExistingThumbnail = false, ?string $projectId = null): array
    {
        $request->merge([
            'submission_action' => $request->input('submission_action', 'Submitted'),
        ]);
        $submissionAction = $request->input('submission_action');

        return $request->validate([
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
                Rule::exists('users', 'user_id')->where(function (Builder $query) use ($projectId): void {
                    $query->where('role_id', auth()->user()->role_id)
                        ->whereNotExists(function (Builder $subquery) use ($projectId): void {
                            $subquery->selectRaw('1')
                                ->from('projects')
                                ->whereColumn('projects.created_by', 'users.user_id');

                            if ($projectId !== null) {
                                $subquery->where('projects.project_id', '<>', $projectId);
                            }
                        })
                        ->whereNotExists(function (Builder $subquery) use ($projectId): void {
                            $subquery->selectRaw('1')
                                ->from('project_members')
                                ->whereColumn('project_members.user_id', 'users.user_id');

                            if ($projectId !== null) {
                                $subquery->where('project_members.project_id', '<>', $projectId);
                            }
                        });
                }),
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
                Rule::requiredIf(fn () => $submissionAction === 'Submitted' && ! $hasExistingThumbnail),
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
    }

    private function authorizeParticipant(): void
    {
        abort_unless(auth()->user()->role?->role_name === 'Peserta', 403, 'Fitur ini khusus peserta.');
    }

    private function recordAudit(string $action, string $description, string $projectId): void
    {
        do {
            $logId = 'LOG-'.Str::upper(Str::random(16));
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

    private function createProjectVersion(
        Project $project,
        ?string $changelog,
        ?string $filePath,
        array $fileIds,
        Carbon $submittedAt,
    ): object {
        do {
            $versionId = 'VER-'.Str::upper(Str::random(15));
        } while (DB::table('project_versions')->where('version_id', $versionId)->exists());

        $field = DB::table('mentoring_fields')->where('field_id', $project->field_id)->first();
        $period = DB::table('mentoring_periods')->where('period_id', $project->period_id)->first();
        $teamSnapshot = DB::table('project_members')
            ->join('users', 'users.user_id', '=', 'project_members.user_id')
            ->where('project_members.project_id', $project->project_id)
            ->orderBy('users.name')
            ->get(['project_members.user_id', 'users.name', 'project_members.member_role'])
            ->map(fn ($member): array => [
                'user_id' => $member->user_id,
                'name' => $member->name,
                'member_role' => $member->member_role,
            ])
            ->all();
        $toolsSnapshot = DB::table('project_tools')
            ->where('project_id', $project->project_id)
            ->orderBy('tool_name')
            ->pluck('tool_name')
            ->all();

        DB::table('project_versions')->insert([
            'version_id' => $versionId,
            'project_id' => $project->project_id,
            'version_number' => $this->nextProjectVersionNumber($project->project_id),
            'changelog' => $changelog,
            'file_path' => $filePath,
            'title_snapshot' => $project->title,
            'description_snapshot' => $project->description,
            'field_id_snapshot' => $project->field_id,
            'field_name_snapshot' => $field?->field_name,
            'period_id_snapshot' => $project->period_id,
            'period_name_snapshot' => $period?->period_name,
            'project_link_snapshot' => $project->project_link,
            'thumbnail_path_snapshot' => $project->thumbnail_url,
            'team_snapshot' => json_encode($teamSnapshot, JSON_THROW_ON_ERROR),
            'tools_snapshot' => json_encode($toolsSnapshot, JSON_THROW_ON_ERROR),
            'submitted_at' => $submittedAt,
            'submitted_by' => auth()->id(),
            'is_published' => false,
            'is_legacy_snapshot' => false,
            'created_at' => $submittedAt,
            'updated_at' => $submittedAt,
        ]);

        foreach (array_unique($fileIds) as $fileId) {
            $file = DB::table('project_files')
                ->where('project_id', $project->project_id)
                ->where('file_id', $fileId)
                ->first();
            if (! $file) {
                continue;
            }

            do {
                $versionFileId = 'VF-'.Str::upper(Str::random(17));
            } while (DB::table('project_version_files')->where('version_file_id', $versionFileId)->exists());

            DB::table('project_version_files')->insert([
                'version_file_id' => $versionFileId,
                'version_id' => $versionId,
                'file_id' => $file->file_id,
                'file_name_snapshot' => $file->file_name,
                'file_path' => $file->file_path,
                'created_at' => $submittedAt,
                'updated_at' => $submittedAt,
            ]);
        }

        return DB::table('project_versions')->where('version_id', $versionId)->firstOrFail();
    }

    private function nextProjectVersionNumber(string $projectId): string
    {
        $versions = DB::table('project_versions')
            ->where('project_id', $projectId)
            ->pluck('version_number');
        $latest = null;

        foreach ($versions as $versionNumber) {
            $candidate = ProjectVersion::versionSequence((string) $versionNumber);
            if ($candidate === null) {
                continue;
            }

            if ($latest === null || $candidate[0] > $latest[0] || ($candidate[0] === $latest[0] && $candidate[1] > $latest[1])) {
                $latest = $candidate;
            }
        }

        if ($latest === null) {
            return 'v1.0';
        }

        return 'v'.$latest[0].'.'.($latest[1] + 1);
    }

    private function participantActivities(string $projectId): Collection
    {
        $auditActivities = DB::table('audit_logs')
            ->leftJoin('users as actors', 'actors.user_id', '=', 'audit_logs.user_id')
            ->where('audit_logs.project_id', $projectId)
            ->orderByDesc('audit_logs.created_at')
            ->limit(10)
            ->get([
                'audit_logs.action',
                'audit_logs.description',
                'audit_logs.created_at',
                'actors.name as actor_name',
            ]);

        $reviewActivities = DB::table('reviews')
            ->join('users as actors', 'actors.user_id', '=', 'reviews.reviewer_id')
            ->where('reviews.project_id', $projectId)
            ->orderByDesc('reviews.created_at')
            ->limit(10)
            ->get([
                'reviews.comment as description',
                'reviews.created_at',
                'actors.name as actor_name',
            ])
            ->map(fn ($review) => (object) [
                'action' => 'MENTOR_REVIEW',
                'description' => $review->description,
                'created_at' => $review->created_at,
                'actor_name' => $review->actor_name,
            ]);

        return $auditActivities
            ->concat($reviewActivities)
            ->sortByDesc(fn ($activity) => strtotime($activity->created_at))
            ->take(10)
            ->values();
    }

    private function authorizeMentor(): void
    {
        abort_unless(auth()->user()->role?->role_name === 'Mentor', 403, 'Fitur ini khusus mentor.');
    }

    private function validateMentorSchedule(Request $request, bool $includeProject = true): array
    {
        $rules = [
            'starts_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:240'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_url' => ['nullable', 'url', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($includeProject) {
            $rules = [
                'project_id' => [
                    'required',
                    'string',
                    Rule::exists('projects', 'project_id')
                        ->where(fn (Builder $query) => $query->whereIn('status', ['Submitted', 'In Review', 'Revision Required', 'Resubmitted', 'Approved', 'Published'])),
                ],
            ] + $rules;
        }

        $validated = $request->validate($rules);

        if (blank($validated['location'] ?? null) && blank($validated['meeting_url'] ?? null)) {
            throw ValidationException::withMessages([
                'location' => 'Isi lokasi atau tautan pertemuan mentoring.',
            ]);
        }

        $scheduleData = [
            'starts_at' => Carbon::createFromFormat('Y-m-d\TH:i', $validated['starts_at'])->format('Y-m-d H:i:s'),
            'duration_minutes' => $validated['duration_minutes'],
            'location' => $validated['location'] ?? null,
            'meeting_url' => $validated['meeting_url'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        if ($includeProject) {
            $scheduleData['project_id'] = $validated['project_id'];
        }

        return $scheduleData;
    }

    private function authorizePengurus(): void
    {
        abort_unless(auth()->user()->role?->role_name === 'Pengurus KSM', 403, 'Fitur ini khusus pengurus.');
    }

    private function ensureAnotherActivePengurusExists(string $exceptUserId): void
    {
        $anotherActivePengurusExists = User::query()
            ->where('is_active', true)
            ->where('user_id', '<>', $exceptUserId)
            ->whereHas('role', fn (EloquentBuilder $roleQuery) => $roleQuery->where('role_name', 'Pengurus KSM'))
            ->exists();

        abort_unless($anotherActivePengurusExists, 409, 'Sistem harus memiliki minimal satu akun pengurus aktif.');
    }

    private function recordPengurusAudit(string $action, string $description, ?string $projectId = null): void
    {
        do {
            $logId = 'LOG-'.Str::upper(Str::random(16));
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

    private function participantProjectsQuery(): EloquentBuilder
    {
        $userId = auth()->id();

        return Project::query()
            ->where(function ($query) use ($userId): void {
                $query->where('created_by', $userId)
                    ->orWhere(function ($memberQuery) use ($userId): void {
                        $memberQuery->where('status', '<>', 'Draft')
                            ->whereIn('project_id', DB::table('project_members')
                                ->select('project_id')
                                ->where('user_id', $userId));
                    });
            });
    }

    private function hasProjectInvolvement(string $userId): bool
    {
        return Project::query()
            ->where(function ($query) use ($userId): void {
                $query->where('created_by', $userId)
                    ->orWhereIn('project_id', DB::table('project_members')
                        ->select('project_id')
                        ->where('user_id', $userId));
            })
            ->exists();
    }

    private function availableProjectParticipants(?string $projectId = null): Collection
    {
        return DB::table('users')
            ->where('role_id', auth()->user()->role_id)
            ->whereNotExists(function (Builder $query) use ($projectId): void {
                $query->selectRaw('1')
                    ->from('projects')
                    ->whereColumn('projects.created_by', 'users.user_id');

                if ($projectId !== null) {
                    $query->where('projects.project_id', '<>', $projectId);
                }
            })
            ->whereNotExists(function (Builder $query) use ($projectId): void {
                $query->selectRaw('1')
                    ->from('project_members')
                    ->whereColumn('project_members.user_id', 'users.user_id');

                if ($projectId !== null) {
                    $query->where('project_members.project_id', '<>', $projectId);
                }
            })
            ->orderBy('name')
            ->get(['user_id', 'name']);
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
            $publishedProjects = Project::with(['mentoringField', 'creator', 'publishedVersion'])
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
