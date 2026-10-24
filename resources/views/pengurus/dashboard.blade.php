<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengurus - KSM Multimedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">
    @include('pengurus.partials.navbar')

    <div class="participant-layout pengurus-layout">
        <aside class="participant-sidebar pengurus-sidebar">
            <div class="participant-sidebar-shell pengurus-sidebar-shell">
                <nav class="sidebar-menu" aria-label="Navigasi pengurus">
                    <a href="{{ route('dashboard.pengurus') }}" class="sidebar-link active" aria-current="page">
                        <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1" stroke-width="2"/></svg>
                        <span>Dashboard &amp; Monitoring</span>
                    </a>
                    <a href="{{ route('dashboard.pengurus.approvals') }}" class="sidebar-link">
                        <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11l2 2 4-4m5-5v6a8 8 0 11-4-6.9"/></svg>
                        <span>Approval Publikasi</span>
                        @if ($pendingApprovalCount > 0)
                            <span class="mentor-menu-count">{{ $pendingApprovalCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('dashboard.pengurus.audit') }}" class="sidebar-link">
                        <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                        <span>Log Audit Trail</span>
                    </a>
                    <span class="sidebar-link" aria-disabled="true" title="Pengelolaan pengguna belum tersedia">
                        <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="9" cy="8" r="3" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 20v-1a6 6 0 0112 0v1M16 11a3 3 0 100-6m2 9a5 5 0 013 4v2"/></svg>
                        <span>Kelola Pengguna</span>
                    </span>
                    <span class="sidebar-link" aria-disabled="true" title="Pengaturan belum tersedia">
                        <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.4 15a1.7 1.7 0 00.2 1.9l.1.1-1.7 2.9-.2-.1a1.7 1.7 0 00-1.9.2l-.1.1h-3.4l-.1-.2a1.7 1.7 0 00-1.6-1 1.7 1.7 0 00-.9.2l-.2.1-2.9-1.7.1-.2a1.7 1.7 0 00-.2-1.9l-.1-.1v-3.4l.2-.1a1.7 1.7 0 001-1.6 1.7 1.7 0 00-.2-.9l-.1-.2 1.7-2.9.2.1a1.7 1.7 0 001.9-.2l.1-.1h3.4l.1.2a1.7 1.7 0 001.6 1 1.7 1.7 0 00.9-.2l.2-.1 2.9 1.7-.1.2a1.7 1.7 0 00.2 1.9l.1.1z"/></svg>
                        <span>Pengaturan</span>
                    </span>
                </nav>

                <section class="pengurus-system-card" aria-label="Informasi dashboard">
                    <p class="text-xs font-bold text-slate-700">Periode Akademik</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $activePeriod ?: 'Belum ada periode aktif' }}</p>
                    <div class="mt-3 flex items-center justify-between border-t border-slate-200 pt-3 text-[0.65rem]">
                        <span class="text-slate-500">Persetujuan tertunda</span>
                        <strong class="text-ksm">{{ $pendingApprovalCount }} project</strong>
                    </div>
                </section>
            </div>
        </aside>

        <main class="participant-content mentor-content pengurus-content">
            <header class="mb-6 flex flex-col justify-between gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-center">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Dashboard Monitoring &amp; Kurasi Capstone</h1>
                    <p class="mt-1 max-w-3xl text-sm text-slate-500">Pemantauan real-time siklus karya akhir mahasiswa, validasi rubrik mentor, dan antrean rilis publik.</p>
                </div>
                <div class="flex shrink-0 gap-2">
                    <button type="button" class="pengurus-secondary-action" disabled title="Ekspor rekap akan tersedia pada tahap berikutnya">
                        <svg class="h-4 w-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M5 17v3h14v-3"/></svg>
                        Ekspor Rekap
                    </button>
                    <button type="button" class="pengurus-primary-action" disabled title="Sinkronisasi status akan tersedia pada tahap berikutnya">
                        <svg class="h-4 w-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7v5h-5M4 17v-5h5m10-1a7 7 0 00-12-4L4 8m16 8l-3 1a7 7 0 01-12-4"/></svg>
                        Sinkronkan Status
                    </button>
                </div>
            </header>

            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <section class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan kurasi">
                <article class="mentor-stat-card pengurus-stat-card">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Menunggu Persetujuan</p>
                        <p class="mt-1 text-2xl font-bold text-ksm">{{ $pendingApprovalCount }}</p>
                        <p class="text-[0.65rem] text-slate-500">Siap ditinjau pengurus</p>
                    </div>
                    <span class="mentor-stat-icon bg-red-100 text-ksm" aria-hidden="true">▣</span>
                    <span class="pengurus-stat-progress pengurus-progress-red"></span>
                </article>
                <article class="mentor-stat-card pengurus-stat-card">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Sedang Direview Mentor</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $reviewingProjectCount }}</p>
                        <p class="text-[0.65rem] text-slate-500">Proses asistensi &amp; evaluasi rubrik</p>
                    </div>
                    <span class="mentor-stat-icon bg-indigo-100 text-indigo-700" aria-hidden="true">▧</span>
                    <span class="pengurus-stat-progress pengurus-progress-slate"></span>
                </article>
                <article class="mentor-stat-card pengurus-stat-card">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Total Karya Terpublikasi</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $publishedProjectCount }}</p>
                        <p class="text-[0.65rem] text-slate-500">Arsip terbuka KSM Multimedia</p>
                    </div>
                    <span class="mentor-stat-icon bg-indigo-100 text-indigo-700" aria-hidden="true">▤</span>
                    <span class="pengurus-stat-progress pengurus-progress-blue"></span>
                </article>
                <article class="mentor-stat-card pengurus-stat-card">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Tingkat Kelulusan Kurasi</p>
                        @if ($curationScore !== null)
                            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format((float) $curationScore * 20, 1) }}%</p>
                            <p class="text-[0.65rem] text-slate-500">Rata-rata skor {{ number_format((float) $curationScore, 2) }}/5</p>
                        @else
                            <p class="mt-1 text-2xl font-bold text-slate-900">—</p>
                            <p class="text-[0.65rem] text-slate-500">Belum ada nilai review</p>
                        @endif
                    </div>
                    <span class="mentor-stat-icon bg-emerald-100 text-emerald-700" aria-hidden="true">◎</span>
                    <span class="pengurus-stat-progress pengurus-progress-green"></span>
                </article>
            </section>

            <section id="approval-queue" class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col justify-between gap-3 border-b border-slate-100 p-4 lg:flex-row lg:items-center">
                    <div>
                        <h2 class="flex items-center gap-2 text-base font-bold text-slate-900"><span class="h-2 w-2 rounded-full bg-ksm" aria-hidden="true"></span> Antrean Perlu Tindakan Segera</h2>
                        <p class="mt-1 text-xs text-slate-500">Karya yang telah diverifikasi mentor dan membutuhkan izin rilis ke galeri umum.</p>
                    </div>
                    <form method="GET" action="{{ route('dashboard.pengurus') }}" class="pengurus-filter-form">
                        <label>
                            <span class="sr-only">Filter bidang</span>
                            <select name="field" class="mentor-status-select">
                                <option value="">Semua Bidang</option>
                                @foreach ($fields as $field)
                                    <option value="{{ $field->field_id }}" @selected(($filters['field'] ?? '') === $field->field_id)>{{ $field->field_name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button type="submit" class="mentor-filter-button" aria-label="Terapkan filter" title="Terapkan filter">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="M4 6h16M7 12h10m-7 6h4"/></svg>
                        </button>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="mentor-review-table pengurus-approval-table w-full min-w-[850px] text-left">
                        <thead>
                            <tr>
                                <th>Judul &amp; Identitas Capstone</th>
                                <th>Bidang</th>
                                <th>Mentor &amp; Rekomendasi</th>
                                <th>Status Kurasi</th>
                                <th class="text-right">Aksi Persetujuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projects as $project)
                                @php
                                    $latestReview = $latestReviewsByProject->get($project->project_id, collect())->first();
                                    $members = $membersByProject->get($project->project_id, collect());
                                    $statusLabel = $project->status === 'Approved' ? 'Lolos Review' : $project->status;
                                    $statusClass = 'pengurus-status-approved';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2.5">
                                            @if ($project->thumbnail_url)
                                                <img src="{{ $project->thumbnail_url }}" alt="" class="h-10 w-10 shrink-0 rounded-md border border-slate-200 object-cover">
                                            @else
                                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-slate-100 text-slate-400" aria-hidden="true">▧</span>
                                            @endif
                                            <div>
                                                <p class="max-w-[17rem] font-bold leading-snug text-slate-900">{{ $project->title }}</p>
                                                <p class="mt-0.5 text-[0.63rem] text-slate-500">ID: {{ $project->project_id }} • Tim {{ $members->pluck('name')->join(', ') ?: $project->creator?->name }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="mentor-field-badge">{{ $project->mentoringField?->field_name ?? 'Belum ditentukan' }}</span></td>
                                    <td>
                                        <p class="font-semibold text-slate-800">{{ $latestReview?->reviewer_name ?? 'Belum ada data mentor' }}</p>
                                        @if ($latestReview)
                                            <p class="mt-0.5 text-[0.65rem] font-medium text-emerald-700">✓ Rekomendasi mentor</p>
                                            <p class="text-[0.62rem] text-slate-500">Skor: {{ $latestReview->rating ? $latestReview->rating . '/5' : '—' }}</p>
                                        @else
                                            <p class="mt-0.5 text-[0.65rem] text-slate-500">Belum ada rekomendasi</p>
                                        @endif
                                    </td>
                                    <td><span class="mentor-status-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                    <td class="text-right">
                                        @if ($project->status === 'Approved')
                                            <a href="{{ route('dashboard.pengurus.approval.show', $project->project_id) }}" class="pengurus-approval-button">
                                                <span aria-hidden="true">♙</span> Review &amp; Approval
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <p class="font-semibold text-slate-700">Belum ada karya yang menunggu approval.</p>
                                        <p class="mt-1 text-sm text-slate-500">Project yang direkomendasikan mentor akan muncul di antrean persetujuan publikasi.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col justify-between gap-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-500 sm:flex-row sm:items-center">
                    <span>Menampilkan {{ $projects->count() }} dari {{ $pendingApprovalCount }} project menunggu approval</span>
                    <a href="{{ route('dashboard.pengurus.approvals') }}" class="font-semibold text-ksm hover:underline">Lihat semua approval <span aria-hidden="true">→</span></a>
                </div>
            </section>

            <section id="activities" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Riwayat &amp; Log Aktivitas Terkini</h2>
                        <p class="mt-1 text-xs text-slate-500">Aktivitas audit dan evaluasi project terbaru yang tersimpan di sistem.</p>
                    </div>
                    <svg class="mt-1 h-5 w-5 shrink-0 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H5a2 2 0 00-2 2v13a2 2 0 002 2h14a2 2 0 002-2v-3M8 4a2 2 0 012-2h4a2 2 0 012 2v2H8V4zm8 6h5m-2.5-2.5V13M7 13h6m-6 4h10"/></svg>
                </div>
                <div class="space-y-2.5">
                    @forelse ($activities as $activity)
                        <article class="pengurus-activity">
                            <span class="pengurus-activity-icon {{ $activity['type'] === 'review' ? 'pengurus-activity-review' : 'pengurus-activity-audit' }}" aria-hidden="true">{{ $activity['type'] === 'review' ? '▧' : '↻' }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold leading-relaxed text-slate-800">{{ $activity['title'] }}</p>
                                <p class="mt-1 text-[0.65rem] text-slate-500">{{ $activity['detail'] }}</p>
                            </div>
                            <time class="shrink-0 text-[0.6rem] text-slate-500" datetime="{{ $activity['created_at'] }}">{{ \Illuminate\Support\Carbon::parse($activity['created_at'])->diffForHumans() }}</time>
                        </article>
                    @empty
                        <p class="rounded-lg bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">Belum ada aktivitas audit atau evaluasi yang tercatat.</p>
                    @endforelse
                </div>
                <div class="mt-4 border-t border-slate-100 pt-3 text-right">
                    <a href="{{ route('dashboard.pengurus.audit') }}" class="text-xs font-semibold text-ksm hover:underline">Lihat semua log audit <span aria-hidden="true">→</span></a>
                </div>
            </section>
        </main>
    </div>

    @include('pengurus.partials.footer')
</body>
</html>