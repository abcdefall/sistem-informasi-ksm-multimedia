<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectMediaController extends Controller
{
    public function show(Request $request, string $path): StreamedResponse
    {
        abort_unless(
            str_starts_with($path, 'projects/')
                && ! str_contains($path, '\\')
                && ! preg_match('#(^|/)\.\.?(/|$)#', $path),
            404,
        );

        $segments = explode('/', $path);
        abort_unless(count($segments) >= 3 && $segments[1] !== '', 404);

        $project = Project::query()->find($segments[1]);
        abort_unless($project, 404);
        $mediaDisk = config('filesystems.project_media_disk', 'public');
        abort_unless(Storage::disk($mediaDisk)->exists($path), 404);

        $isPublishedThumbnail = $project->status === 'Published'
            && $project->thumbnail_url === $path;
        $user = $request->user();

        $isThumbnail = $project->thumbnail_url === $path;
        $isPublished = $project->status === 'Published';
        $isSubmitted = $project->status !== 'Draft';
        $isPrivileged = $user !== null
            && in_array($user->role?->role_name, ['Mentor', 'Pengurus KSM'], true);
        $isProjectMember = $user !== null && (
            $project->created_by === $user->user_id
            || $project->members()->where('user_id', $user->user_id)->exists()
        );

        if ($user === null) {
            abort_unless($isPublishedThumbnail, 404);
        }

        $canViewThumbnail = $isThumbnail
            && ($isPublished || ($user !== null && ($isSubmitted || $isPrivileged || $isProjectMember)));
        $canViewOtherFiles = ! $isThumbnail
            && $user !== null
            && ($isPublished || $isPrivileged || $isProjectMember);

        abort_unless($canViewThumbnail || $canViewOtherFiles, 403);

        $response = Storage::disk($mediaDisk)->response($path);
        $response->headers->set(
            'Cache-Control',
            $isPublishedThumbnail ? 'public, max-age=3600' : 'private, no-store',
        );

        return $response;
    }
}
