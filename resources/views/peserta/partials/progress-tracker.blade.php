@php
    $steps = ['Draft', 'Diajukan', 'Review Mentor', 'Disetujui', 'Dipublikasi'];
    $activeStep = match ($project->status) {
        'Submitted', 'In Review', 'Resubmitted', 'Revision Required' => 2,
        'Approved' => 3,
        'Published', 'Archived' => 4,
        default => 0,
    };
    $isArchived = $project->status === 'Archived';
    $isPublished = $project->status === 'Published';
@endphp

<section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm" aria-labelledby="project-progress-title">
    <div>
        <h2 id="project-progress-title" class="text-lg font-bold text-gray-900">Progres Project</h2>
        <p class="mt-1 text-sm text-gray-500">{{ $project->title }}</p>
        @if ($project->status === 'Draft' && $project->created_by === auth()->id())
            <a href="{{ route('dashboard.peserta.project.edit', $project->project_id) }}" class="mt-4 inline-flex items-center rounded-lg border border-ksm px-4 py-2 text-sm font-semibold text-ksm transition hover:bg-red-50">
                Lanjutkan Draft <span class="ml-2">&rarr;</span>
            </a>
        @endif
    </div>

    <ol class="mt-6 flex flex-col gap-4 md:flex-row md:items-start md:gap-0" aria-label="Tahapan progres project">
        @foreach ($steps as $index => $step)
            @php
                $isComplete = $isArchived || $isPublished || $index < $activeStep;
                $isActive = !$isArchived && !$isPublished && $index === $activeStep;
            @endphp
            <li class="relative flex min-h-10 gap-3 md:min-h-0 md:flex-1 md:flex-col md:items-center md:gap-2 md:text-center">
                @if (!$loop->last)
                    <span class="absolute left-4 top-8 h-[calc(100%-1.5rem)] w-px bg-gray-200 md:left-1/2 md:top-4 md:h-px md:w-full {{ $isComplete ? 'md:bg-[var(--color-ksm)]' : '' }}" aria-hidden="true"></span>
                @endif
                <span @class([
                    'relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border text-sm font-bold',
                    'border-transparent text-white' => $isComplete,
                    'bg-white' => $isComplete,
                    'border-gray-300 bg-white text-gray-500' => $isActive,
                    'border-gray-200 bg-gray-100 text-gray-400' => !$isComplete && !$isActive,
                ]) @if ($isComplete) style="background-color: var(--color-ksm); border-color: var(--color-ksm);" @elseif ($isActive) style="border-color: var(--color-ksm); color: var(--color-ksm);" @endif>
                    {{ $isComplete ? '✓' : $index + 1 }}
                </span>
                <div class="relative z-10 flex flex-wrap items-center gap-2 pt-1 md:justify-center md:pt-0">
                    <span @class([
                        'text-sm font-semibold',
                        'text-gray-900' => $isComplete || $isActive,
                        'text-gray-400' => !$isComplete && !$isActive,
                    ]) @if ($isActive) aria-current="step" @endif>{{ $step }}</span>
                    @if ($step === 'Review Mentor' && $project->status === 'Revision Required')
                        <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-bold text-red-700">Perlu Revisi</span>
                    @endif
                    @if ($loop->last && $isArchived)
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-bold text-gray-600">Diarsipkan</span>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>

    <div class="mt-7 border-t border-gray-100 pt-5">
        <h3 class="text-sm font-bold text-gray-900">Riwayat Aktivitas</h3>
        @forelse ($activities as $activity)
            <div class="mt-4 flex gap-3">
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full" style="background-color: var(--color-ksm);" aria-hidden="true"></span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800">{{ \Illuminate\Support\Str::headline(strtolower($activity->action)) }}</p>
                    @if ($activity->description)
                        <p class="mt-0.5 whitespace-pre-line text-sm text-gray-600">{{ $activity->description }}</p>
                    @endif
                    <p class="mt-1 text-xs text-gray-500">
                        oleh {{ $activity->actor_name ?? 'Pengguna' }}
                        · {{ \Illuminate\Support\Carbon::parse($activity->created_at)->translatedFormat('d M Y, H:i') }}
                    </p>
                </div>
            </div>
        @empty
            <p class="mt-3 text-sm text-gray-500">Belum ada aktivitas.</p>
        @endforelse
    </div>
</section>
