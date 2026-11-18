<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Versi - {{ $project->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">
    @include('pengurus.partials.navbar')

    <div class="participant-layout pengurus-layout">
        @include('pengurus.partials.sidebar', ['activeMenu' => 'versions'])

        <main class="participant-content mentor-content pengurus-content">
            <a href="{{ route('dashboard.pengurus.version-history') }}" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ksm hover:underline">← Kembali ke Daftar Project</a>

            <header class="mb-5 flex flex-col gap-3 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-ksm">Riwayat Versi Project</p>
                    <h1 class="mt-1 break-words text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $project->title }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ $project->project_id }} · {{ $project->period?->period_name ?? 'Periode tidak tersedia' }} · {{ $project->versions->count() }} versi</p>
                </div>
                <span class="mentor-status-badge {{ $project->status === 'Archived' ? 'bg-slate-100 text-slate-700' : 'bg-indigo-100 text-indigo-800' }}">{{ $project->status }}</span>
            </header>

            <section class="mentor-review-card">
                <h2 class="mentor-review-section-title">Daftar Versi</h2>
                <div class="mt-2">
                    @forelse ($project->versions as $version)
                        <article class="border-b border-slate-100 py-4 last:border-0">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-bold text-slate-900">{{ $version->version_number }}</h3>
                                        <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-800">{{ $version->history_status }}</span>
                                        @if ($project->published_version_id === $version->version_id && $project->status === 'Published')
                                            <span class="rounded-full bg-green-700 px-2.5 py-1 text-xs font-semibold text-white">Versi aktif di Showcase</span>
                                        @elseif ($version->is_published)
                                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Pernah dipublikasikan</span>
                                        @endif
                                        @if ($version->is_legacy_snapshot)
                                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Snapshot legacy</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 truncate text-sm font-semibold text-slate-800">{{ $version->title_snapshot ?? 'Judul snapshot tidak tercatat' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $version->submitted_at?->format('d M Y, H:i') ?? $version->created_at?->format('d M Y, H:i') ?? 'Tanggal tidak tercatat' }}
                                        · {{ $version->submitter?->name ?? 'Pengirim tidak tersedia' }}
                                    </p>
                                    @if ($version->changelog)
                                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ \Illuminate\Support\Str::limit($version->changelog, 180) }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('dashboard.pengurus.version-history.show', [$project->project_id, $version->version_id]) }}" class="pengurus-secondary-action shrink-0 self-start">Detail Versi</a>
                            </div>
                        </article>
                    @empty
                        <p class="pt-4 text-sm text-slate-500">Belum ada riwayat versi untuk project ini.</p>
                    @endforelse
                </div>
            </section>
        </main>
    </div>

    @include('pengurus.partials.footer')
</body>
</html>
