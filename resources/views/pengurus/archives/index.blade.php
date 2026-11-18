<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archive Project - KSM Multimedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">
    @include('pengurus.partials.navbar')

    <div class="participant-layout pengurus-layout">
        @include('pengurus.partials.sidebar', ['activeMenu' => 'archives'])

        <main class="participant-content mentor-content pengurus-content">
            <header class="mb-6 flex flex-col gap-3 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-ksm">Administrasi Project</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Archive Project</h1>
                    <p class="mt-1 max-w-3xl text-sm text-slate-500">Project diarsipkan tanpa menghapus submission, review, file, anggota tim, atau riwayat audit.</p>
                </div>
                <a href="{{ route('dashboard.pengurus.version-history') }}" class="pengurus-secondary-action">Buka Version History</a>
            </header>

            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
            @endif

            @if ($projects->isEmpty())
                <section class="mentor-review-card text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-xl text-slate-500" aria-hidden="true">▤</div>
                    <h2 class="mt-3 text-lg font-bold text-slate-800">Belum ada project Archived</h2>
                    <p class="mt-1 text-sm text-slate-500">Project yang diarsipkan akan muncul di halaman ini dan dapat dipulihkan oleh Pengurus.</p>
                </section>
            @else
                <section class="space-y-4" aria-label="Daftar project Archived">
                    @foreach ($projects as $project)
                        <article class="mentor-review-card">
                            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="text-lg font-bold text-slate-900">{{ $project->title }}</h2>
                                        <span class="mentor-status-badge bg-slate-100 text-slate-700">Archived</span>
                                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">Sebelumnya: {{ $project->archived_from_status ?: 'Status legacy tidak tercatat' }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">ID {{ $project->project_id }} · {{ $project->mentoringField?->field_name ?? 'Bidang tidak tersedia' }} · {{ $project->period?->period_name ?? 'Periode tidak tersedia' }}</p>
                                    <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 xl:grid-cols-4">
                                        <div>
                                            <dt class="text-xs font-semibold uppercase text-slate-400">Peserta / Tim</dt>
                                            <dd class="mt-1 text-slate-700">{{ $project->members->pluck('user.name')->filter()->join(', ') ?: ($project->creator?->name ?? 'Tidak tersedia') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase text-slate-400">Dipublikasikan sebelumnya</dt>
                                            <dd class="mt-1 text-slate-700">{{ $project->published_at?->format('d M Y, H:i') ?? 'Belum pernah dipublikasikan' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase text-slate-400">Waktu archive</dt>
                                            <dd class="mt-1 text-slate-700">{{ $project->archived_at?->format('d M Y, H:i') ?? 'Data archive lama belum tercatat' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase text-slate-400">Diarsipkan oleh</dt>
                                            <dd class="mt-1 text-slate-700">{{ $project->archivedBy?->name ?? 'Tidak tersedia' }}</dd>
                                        </div>
                                    </dl>
                                </div>
                                <div class="flex flex-wrap gap-2 lg:justify-end">
                                    <a href="{{ route('dashboard.pengurus.archives.show', $project->project_id) }}" class="pengurus-secondary-action">Detail</a>
                                    <a href="{{ route('dashboard.pengurus.version-history', ['project_id' => $project->project_id]) }}" class="pengurus-secondary-action">Riwayat Versi</a>
                                    <form method="POST" action="{{ route('dashboard.pengurus.archives.restore', $project->project_id) }}" onsubmit="return confirm('Pulihkan project ini ke workflow yang sesuai? Project yang sebelumnya Published akan kembali ke tahap approval, bukan langsung ke Showcase.')">
                                        @csrf
                                        <button type="submit" class="pengurus-primary-action">Restore</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>

                @if ($projects->hasPages())
                    <nav class="mt-5" aria-label="Navigasi daftar archive">{{ $projects->links() }}</nav>
                @endif
            @endif
        </main>
    </div>

    @include('pengurus.partials.footer')
</body>
</html>
