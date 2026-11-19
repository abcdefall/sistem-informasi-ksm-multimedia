<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectVersion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectArchiveController extends Controller
{
    public function index(): View
    {
        $this->authorizePengurus();

        return view('pengurus.archives.index', [
            'projects' => Project::query()
                ->with(['mentoringField', 'period', 'creator', 'archivedBy', 'members.user'])
                ->where('status', 'Archived')
                ->orderByDesc('archived_at')
                ->orderBy('title')
                ->paginate(15),
            ...$this->sidebarData('archives'),
        ]);
    }

    public function show(string $projectId): View
    {
        $this->authorizePengurus();

        $project = Project::query()
            ->with([
                'mentoringField',
                'period',
                'creator',
                'archivedBy',
                'members.user',
                'versions' => fn ($query) => $query
                    ->with(['submitter', 'approvedBy', 'files', 'reviews.reviewer'])
                    ->orderBy('created_at')
                    ->orderBy('version_id'),
            ])
            ->where('status', 'Archived')
            ->findOrFail($projectId);
        $project->setRelation('versions', ProjectVersion::sortChronologically($project->versions));

        return view('pengurus.archives.show', [
            'project' => $project,
            ...$this->sidebarData('archives'),
        ]);
    }

    public function archive(string $projectId): RedirectResponse
    {
        $this->authorizePengurus();

        DB::transaction(function () use ($projectId): void {
            $project = Project::query()
                ->whereKey($projectId)
                ->lockForUpdate()
                ->firstOrFail();
            abort_if($project->status === 'Archived', 409, 'Project ini sudah diarsipkan.');

            $previousStatus = $project->status;
            $archivedAt = now();

            $project->forceFill([
                'status' => 'Archived',
                'archived_from_status' => $previousStatus,
                'archived_at' => $archivedAt,
                'archived_by' => auth()->id(),
            ])->save();

            $this->recordAudit(
                'PROJECT_ARCHIVED',
                'Project "'.$project->title.'" diarsipkan dari status '.$previousStatus.'.',
                $project->project_id,
            );
        }, 5);

        return redirect()
            ->route('dashboard.pengurus.archives')
            ->with('status', 'Project berhasil diarsipkan tanpa menghapus data atau file.');
    }

    public function restore(string $projectId): RedirectResponse
    {
        $this->authorizePengurus();

        DB::transaction(function () use ($projectId): void {
            $project = Project::query()
                ->whereKey($projectId)
                ->lockForUpdate()
                ->firstOrFail();
            abort_unless($project->status === 'Archived', 409, 'Project ini tidak sedang diarsipkan.');

            $previousStatus = $project->archived_from_status;
            $knownStatuses = ['Draft', 'Submitted', 'In Review', 'Revision Required', 'Resubmitted', 'Approved', 'Published'];
            $wasPublished = $previousStatus === 'Published'
                || (! in_array($previousStatus, $knownStatuses, true) && $project->published_at !== null);
            $restoredStatus = match ($previousStatus) {
                'Draft', 'Submitted', 'In Review', 'Revision Required', 'Resubmitted', 'Approved' => $previousStatus,
                'Published' => 'Approved',
                default => $project->published_at !== null ? 'Approved' : 'Submitted',
            };

            $project->forceFill([
                'status' => $restoredStatus,
                'publication_approved_by' => $wasPublished ? null : $project->publication_approved_by,
                'publication_approved_at' => $wasPublished ? null : $project->publication_approved_at,
                'published_version_id' => $wasPublished ? null : $project->published_version_id,
            ])->save();

            $this->recordAudit(
                'PROJECT_RESTORED',
                'Project "'.$project->title.'" dipulihkan ke status '.$restoredStatus.
                    ($wasPublished ? ' dan memerlukan approval serta publication ulang.' : '.'),
                $project->project_id,
            );
        }, 5);

        return redirect()
            ->route('dashboard.pengurus.archives')
            ->with('status', 'Project berhasil dipulihkan ke workflow yang sesuai.');
    }

    private function authorizePengurus(): void
    {
        abort_unless(auth()->user()->role?->role_name === 'Pengurus KSM', 403, 'Fitur ini khusus pengurus.');
    }

    private function sidebarData(string $activeMenu): array
    {
        return [
            'activeMenu' => $activeMenu,
            'activePeriod' => DB::table('mentoring_periods')
                ->where('is_active', true)
                ->orderByDesc('start_date')
                ->value('period_name'),
            'pendingApprovalCount' => Project::query()->where('status', 'Approved')->count(),
        ];
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
}
