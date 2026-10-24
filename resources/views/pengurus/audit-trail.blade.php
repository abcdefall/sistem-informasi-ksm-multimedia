<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Audit Trail - KSM Multimedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">
    @include('pengurus.partials.navbar')

    <div class="participant-layout pengurus-layout">
        @include('pengurus.partials.sidebar')

        <main class="participant-content mentor-content pengurus-content">
            <header class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <div>
                    <a href="{{ route('dashboard.pengurus') }}" class="text-xs font-semibold text-ksm hover:underline">← Dashboard Monitoring</a>
                    <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Log Audit Trail Aktivitas Kurasi</h1>
                    <p class="mt-1 max-w-3xl text-sm text-slate-500">Rekam jejak digital seluruh aksi, revisi, penilaian, dan publikasi capstone secara kronologis.</p>
                </div>
                <span class="rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs font-semibold text-slate-700">Database: Audit Logs</span>
            </header>

            <form method="GET" action="{{ route('dashboard.pengurus.audit') }}" class="mentor-filter-bar mb-4">
                <label class="mentor-search">
                    <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="m16 16 4 4"/></svg>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari berdasarkan ID log, nama aktor, atau detail" aria-label="Cari log audit">
                </label>
                <div class="pengurus-filter-form">
                    <label>
                        <span class="sr-only">Filter jenis aktivitas</span>
                        <select name="action" class="mentor-status-select">
                            <option value="">Semua Aksi</option>
                            @foreach ($actions as $action)
                                <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="mentor-filter-button" aria-label="Terapkan filter" title="Terapkan filter">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="M4 6h16M7 12h10m-7 6h4"/></svg>
                    </button>
                </div>
            </form>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="mentor-review-table pengurus-audit-table w-full min-w-[760px] text-left">
                        <thead>
                            <tr>
                                <th>Waktu &amp; Tanggal</th>
                                <th>Aktor</th>
                                <th>Jenis Aktivitas</th>
                                <th>Detail Project</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="whitespace-nowrap">
                                        <p class="font-semibold text-slate-700">{{ \Illuminate\Support\Carbon::parse($log->created_at)->format('d M Y') }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($log->created_at)->format('H:i') }}</p>
                                    </td>
                                    <td>
                                        <p class="font-semibold text-slate-800">{{ $log->actor_name ?: 'Akun dihapus' }}</p>
                                        <p class="mt-1 text-[0.65rem] text-slate-500">{{ $log->actor_email ?: 'Tidak tersedia' }}</p>
                                    </td>
                                    <td><span class="pengurus-audit-action">{{ $log->action }}</span></td>
                                    <td>
                                        <p class="font-semibold text-slate-700">{{ $log->project_title ?: '—' }}</p>
                                        <p class="mt-1 font-mono text-[0.6rem] text-slate-500">{{ $log->project_id ?: $log->log_id }}</p>
                                    </td>
                                    <td class="max-w-sm whitespace-normal text-xs leading-relaxed text-slate-600">{{ $log->description ?: 'Tidak ada detail tambahan.' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada aktivitas audit yang tercatat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col justify-between gap-3 border-t border-slate-100 px-4 py-3 text-xs text-slate-500 sm:flex-row sm:items-center">
                    <span>Menampilkan {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} aktivitas tercatat</span>
                    {{ $logs->links() }}
                </div>
            </section>
        </main>
    </div>

    @include('pengurus.partials.footer')
</body>
</html>
