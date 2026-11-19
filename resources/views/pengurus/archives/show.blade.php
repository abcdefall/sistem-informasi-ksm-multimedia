<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Archive - {{ $project->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">
    @include('pengurus.partials.navbar')

    <div class="participant-layout pengurus-layout">
        @include('pengurus.partials.sidebar', ['activeMenu' => 'archives'])

        <main class="participant-content mentor-content pengurus-content">
            <a href="{{ route('dashboard.pengurus.archives') }}" class="mb-4 inline-flex text-sm font-semibold text-ksm hover:underline">← Kembali ke Archive Project</a>
            <header class="mb-5 flex flex-col justify-between gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-start">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-ksm">Detail Project Archived</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $project->title }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ $project->project_id }} · Status sebelumnya: {{ $project->archived_from_status ?: 'Tidak tercatat (legacy)' }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('dashboard.pengurus.version-history', ['project_id' => $project->project_id]) }}" class="pengurus-secondary-action">Riwayat Versi</a>
                    <form method="POST" action="{{ route('dashboard.pengurus.archives.restore', $project->project_id) }}" onsubmit="return confirm('Pulihkan project ini ke workflow yang sesuai? Project yang sebelumnya Published akan kembali ke tahap approval.')">
                        @csrf
                        <button type="submit" class="pengurus-primary-action">Restore Project</button>
                    </form>
                </div>
            </header>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-[1fr_1.3fr]">
                <section class="mentor-review-card">
                    <h2 class="mentor-review-section-title">Informasi Archive</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div><dt class="text-xs font-semibold uppercase text-slate-400">Bidang</dt><dd class="mt-1 text-slate-800">{{ $project->mentoringField?->field_name ?? 'Tidak tersedia' }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase text-slate-400">Periode</dt><dd class="mt-1 text-slate-800">{{ $project->period?->period_name ?? 'Tidak tersedia' }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase text-slate-400">Tim</dt><dd class="mt-1 text-slate-800">{{ $project->members->pluck('user.name')->filter()->join(', ') ?: ($project->creator?->name ?? 'Tidak tersedia') }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase text-slate-400">Tanggal publikasi sebelumnya</dt><dd class="mt-1 text-slate-800">{{ $project->published_at?->format('d M Y, H:i') ?? 'Belum pernah dipublikasikan' }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase text-slate-400">Tanggal archive</dt><dd class="mt-1 text-slate-800">{{ $project->archived_at?->format('d M Y, H:i') ?? 'Data archive lama belum tercatat' }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase text-slate-400">Admin</dt><dd class="mt-1 text-slate-800">{{ $project->archivedBy?->name ?? 'Tidak tersedia' }}</dd></div>
                    </dl>
                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <h3 class="text-sm font-bold text-slate-800">Deskripsi project saat ini</h3>
                        <p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $project->description ?: 'Tidak ada deskripsi.' }}</p>
                    </div>
                </section>

                <section class="mentor-review-card">
                    <h2 class="mentor-review-section-title">Versi Project</h2>
                    @forelse ($project->versions as $version)
                        <article class="border-b border-slate-100 py-4 last:border-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-bold text-slate-900">{{ $version->version_number }}</h3>
                                @if ($version->is_published)<span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Pernah dipublikasikan</span>@endif
                                @if ($version->is_legacy_snapshot)<span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Snapshot legacy terbatas</span>@endif
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ $version->submitted_at?->format('d M Y, H:i') ?? $version->created_at?->format('d M Y, H:i') }} · {{ $version->submitter?->name ?? 'Pengirim tidak tersedia' }}</p>
                            <p class="mt-2 text-sm font-semibold text-slate-800">{{ $version->title_snapshot ?? $project->title }}</p>
                            @if ($version->changelog)<p class="mt-1 whitespace-pre-line text-sm text-slate-600">{{ $version->changelog }}</p>@endif
                            @if ($version->project_link_snapshot)
                                <a href="{{ $version->project_link_snapshot }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex text-sm font-semibold text-ksm hover:underline">Buka link project versi ini ↗</a>
                            @endif
                            @foreach ($version->files as $file)
                                <p class="mt-2"><a href="{{ route('media.show', ['path' => $file->file_path]) }}" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-ksm hover:underline">{{ $file->file_name_snapshot }} ↗</a></p>
                            @endforeach
                            @foreach ($version->reviews as $review)
                                <div class="mt-3 rounded-lg bg-slate-50 p-3">
                                    <p class="text-xs font-semibold text-slate-700">{{ $review->reviewer?->name ?? 'Reviewer tidak tersedia' }} · {{ $review->decision ?? 'Keputusan legacy tidak tercatat' }}@if ($review->score_out_of_100 !== null) · Nilai {{ $review->score_out_of_100 }}/100 @endif</p>
                                    <p class="mt-1 whitespace-pre-line text-sm text-slate-600">{{ $review->comment }}</p>
                                </div>
                            @endforeach
                        </article>
                    @empty
                        <p class="mt-3 text-sm text-slate-500">Belum ada riwayat versi.</p>
                    @endforelse
                </section>
            </div>
        </main>
    </div>

    @include('pengurus.partials.footer')
</body>
</html>
