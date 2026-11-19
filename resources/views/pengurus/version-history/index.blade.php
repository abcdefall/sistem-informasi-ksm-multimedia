<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Version History - KSM Multimedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">
    @include('pengurus.partials.navbar')

    <div class="participant-layout pengurus-layout">
        @include('pengurus.partials.sidebar', ['activeMenu' => 'versions'])

        <main class="participant-content mentor-content pengurus-content">
            <header class="mb-6 flex flex-col gap-3 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-ksm">Submission Immutable</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Version History</h1>
                    <p class="mt-1 max-w-3xl text-sm text-slate-500">Pilih project untuk melihat riwayat versi dan snapshot submission. Riwayat tidak dapat diedit atau dihapus dari halaman ini.</p>
                </div>
                <a href="{{ route('dashboard.pengurus.archives') }}" class="pengurus-secondary-action">Buka Archive Project</a>
            </header>

            <form method="GET" action="{{ route('dashboard.pengurus.version-history') }}" class="mb-5 flex flex-col gap-2 sm:flex-row">
                <label class="sr-only" for="history-project-id">Filter project</label>
                <input id="history-project-id" name="project_id" value="{{ $filters['project_id'] ?? '' }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm sm:max-w-sm" placeholder="Filter dengan Project ID">
                <button type="submit" class="pengurus-primary-action">Filter</button>
                @if (!empty($filters['project_id']))<a href="{{ route('dashboard.pengurus.version-history') }}" class="pengurus-secondary-action">Hapus filter</a>@endif
            </form>

            @forelse ($projects as $project)
                <section class="mentor-review-card mb-4" aria-labelledby="project-{{ $project->project_id }}">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 id="project-{{ $project->project_id }}" class="text-lg font-bold text-slate-900">{{ $project->title }}</h2>
                                <span class="mentor-status-badge {{ $project->status === 'Archived' ? 'bg-slate-100 text-slate-700' : 'bg-indigo-100 text-indigo-800' }}">{{ $project->status }}</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ $project->project_id }} · {{ $project->period?->period_name ?? 'Periode tidak tersedia' }}</p>
                            <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 xl:grid-cols-4">
                                <div>
                                    <dt class="text-xs font-semibold uppercase text-slate-400">Tim</dt>
                                    <dd class="mt-1 text-slate-700">{{ $project->members->pluck('user.name')->filter()->join(', ') ?: ($project->creator?->name ?? 'Tidak tersedia') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase text-slate-400">Submitter</dt>
                                    <dd class="mt-1 text-slate-700">{{ $project->creator?->name ?? 'Tidak tersedia' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase text-slate-400">Jumlah versi</dt>
                                    <dd class="mt-1 font-semibold text-slate-800">{{ $project->versions_count }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase text-slate-400">Aktivitas versi terakhir</dt>
                                    <dd class="mt-1 text-slate-700">{{ $project->versions_max_created_at ? \Illuminate\Support\Carbon::parse($project->versions_max_created_at)->format('d M Y, H:i') : 'Tidak tersedia' }}</dd>
                                </div>
                            </dl>
                        </div>
                        <div class="flex shrink-0 flex-wrap gap-2">
                            <a href="{{ route('dashboard.pengurus.version-history.project', $project->project_id) }}" class="pengurus-primary-action">Lihat Riwayat Versi</a>
                            @if ($project->status === 'Archived')
                                <a href="{{ route('dashboard.pengurus.archives.show', $project->project_id) }}" class="pengurus-secondary-action">Detail archive</a>
                            @else
                                <form method="POST" action="{{ route('dashboard.pengurus.projects.archive', $project->project_id) }}" onsubmit="return confirm('Arsipkan project ini? Data, file, review, dan versinya tidak akan dihapus.')">
                                    @csrf
                                    <button type="submit" class="pengurus-secondary-action">Archive Project</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </section>
            @empty
                <section class="mentor-review-card text-center">
                    <h2 class="font-bold text-slate-800">Project tidak ditemukan</h2>
                    <p class="mt-1 text-sm text-slate-500">Belum ada project yang cocok dengan filter.</p>
                </section>
            @endforelse

            @if ($projects->hasPages())
                <nav class="mt-5" aria-label="Navigasi riwayat versi">{{ $projects->links() }}</nav>
            @endif
        </main>
    </div>

    @include('pengurus.partials.footer')
</body>
</html>
