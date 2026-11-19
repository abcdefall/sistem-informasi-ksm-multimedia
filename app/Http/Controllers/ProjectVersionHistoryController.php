<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectVersion;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectVersionHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizePengurus();

        $filters = $request->validate([
            'project_id' => ['nullable', 'string', 'exists:projects,project_id'],
        ]);

        $projects = Project::query()
            ->with([
                'period',
                'creator',
                'members.user',
            ])
            ->withCount('versions')
            ->withMax('versions', 'created_at')
            ->whereHas('versions')
            ->when(
                $filters['project_id'] ?? null,
                fn (Builder $query, string $projectId) => $query->where('project_id', $projectId),
            )
            ->orderByDesc('versions_max_created_at')
            ->orderBy('title')
            ->paginate(10)
            ->withQueryString();

        return view('pengurus.version-history.index', [
            'projects' => $projects,
            'filters' => $filters,
            'activeMenu' => 'versions',
            'activePeriod' => DB::table('mentoring_periods')
                ->where('is_active', true)
                ->orderByDesc('start_date')
                ->value('period_name'),
            'pendingApprovalCount' => Project::query()->where('status', 'Approved')->count(),
        ]);
    }

    public function showProject(string $projectId): View
    {
        $this->authorizePengurus();

        $project = Project::query()
            ->with([
                'period',
                'creator',
                'members.user',
                'versions' => fn ($query) => $query->with('submitter')
                    ->orderBy('created_at')
                    ->orderBy('version_id'),
            ])
            ->whereHas('versions')
            ->findOrFail($projectId);
        $versions = ProjectVersion::sortChronologically($project->versions);
        $versions->each(fn (ProjectVersion $version) => $version->setAttribute('history_status', $this->versionStatus($version)));
        $project->setRelation('versions', $versions);

        return view('pengurus.version-history.project', [
            'project' => $project,
            'activeMenu' => 'versions',
            'activePeriod' => DB::table('mentoring_periods')
                ->where('is_active', true)
                ->orderByDesc('start_date')
                ->value('period_name'),
            'pendingApprovalCount' => Project::query()->where('status', 'Approved')->count(),
        ]);
    }

    public function show(string $projectId, string $versionId): View
    {
        $this->authorizePengurus();

        $version = ProjectVersion::query()
            ->with(['submitter', 'approvedBy', 'files', 'reviews.reviewer'])
            ->where('project_id', $projectId)
            ->where('version_id', $versionId)
            ->firstOrFail();
        $version->setAttribute('history_status', $this->versionStatus($version));

        $thumbnailUrl = null;
        if (is_string($version->thumbnail_path_snapshot) && $version->thumbnail_path_snapshot !== '') {
            $thumbnailUrl = $this->externalHttpUrl($version->thumbnail_path_snapshot)
                ?? route('media.show', ['path' => $version->thumbnail_path_snapshot]);
        }

        return view('pengurus.version-history.show', [
            'version' => $version,
            'thumbnailUrl' => $thumbnailUrl,
            'projectLinkUrl' => is_string($version->project_link_snapshot)
                ? $this->externalHttpUrl($version->project_link_snapshot)
                : null,
            'activeMenu' => 'versions',
            'activePeriod' => DB::table('mentoring_periods')
                ->where('is_active', true)
                ->orderByDesc('start_date')
                ->value('period_name'),
            'pendingApprovalCount' => Project::query()->where('status', 'Approved')->count(),
        ]);
    }

    private function versionStatus(ProjectVersion $version): string
    {
        if ($version->is_published) {
            return 'Published';
        }

        if ($version->approved_at !== null) {
            return 'Approved';
        }

        if ($version->submitted_at !== null) {
            return 'Submitted';
        }

        return $version->is_legacy_snapshot ? 'Status tidak tercatat' : 'Draft';
    }

    private function externalHttpUrl(string $url): ?string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true) ? $url : null;
    }

    private function authorizePengurus(): void
    {
        abort_unless(auth()->user()->role?->role_name === 'Pengurus KSM', 403, 'Fitur ini khusus pengurus.');
    }
}
