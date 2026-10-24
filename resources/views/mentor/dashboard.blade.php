<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antrian Review Mentor - KSM Multimedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">
    <nav class="border-b border-gray-200 py-4 sticky top-0 z-40" style="background-color: var(--color-navbar-bg);">
        <div class="mx-auto px-8 lg:px-12 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo-ksm.png') }}" alt="Logo KSM Multimedia" class="w-8 h-8 object-contain">
                <span class="font-bold text-xl tracking-tight text-ksm">KSM Multimedia</span>
                <span class="bg-red-50 text-ksm text-xs font-bold px-2 py-1 rounded border border-red-100 ml-2">PORTAL MENTOR</span>
            </a>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3 text-right">
                    <div class="w-10 h-10 rounded-full bg-ksm text-white flex items-center justify-center font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="hidden md:block">
                        <p class="text-sm font-bold text-gray-800">Halo, {{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">Mentor</p>
                    </div>
                </div>
                <div class="h-8 w-px bg-gray-200 mx-2"></div>
                <a href="{{ route('landing') }}" class="text-sm font-medium text-gray-600 hover:text-ksm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </nav>

    <div class="participant-layout">
        <aside class="participant-sidebar">
            <div class="participant-sidebar-shell">
                <nav class="sidebar-menu" aria-label="Navigasi mentor">
                    <a href="{{ route('dashboard.mentor') }}" class="sidebar-link active" aria-current="page">
                        <svg class="w-5 h-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 4h14a2 2 0 012 2v14l-4-2-5 2-5-2-4 2V6a2 2 0 012-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9h6M9 13h6"/></svg>
                        <span>Antrian Review</span>
                        @if ($waitingReviewCount + $revisionIncomingCount > 0)
                            <span class="mentor-menu-count">{{ $waitingReviewCount + $revisionIncomingCount }}</span>
                        @endif
                    </a>
                    <span class="sidebar-link" aria-disabled="true" title="Menu ini akan tersedia pada tahap berikutnya">
                        <svg class="w-5 h-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                        <span>Riwayat Evaluasi</span>
                    </span>
                    <span class="sidebar-link" aria-disabled="true" title="Menu ini akan tersedia pada tahap berikutnya">
                        <svg class="w-5 h-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3.5" y="5" width="17" height="16" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 3v4M16.5 3v4M4 10h16"/></svg>
                        <span>Jadwal Mentoring</span>
                    </span>
                    <span class="sidebar-link" aria-disabled="true" title="Menu ini akan tersedia pada tahap berikutnya">
                        <svg class="w-5 h-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="8" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21a8 8 0 0116 0"/></svg>
                        <span>Profil</span>
                    </span>
                </nav>

                <section class="mentor-rubric-card">
                    <div class="mb-2 flex items-center gap-2 text-ksm">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l2.5 2.5M4 12H2m20 0h-2"/></svg>
                        <h2 class="text-sm font-bold">Panduan Penilaian</h2>
                    </div>
                    <p class="mb-3 text-xs leading-relaxed text-slate-600">Pastikan setiap penilaian capstone mencakup 4 pilar utama kurasi akademik KSM Multimedia:</p>
                    <ul class="space-y-2 text-[0.68rem] leading-snug text-slate-700">
                        <li class="flex gap-2"><span class="text-ksm">✓</span> Mekanika Gameplay &amp; Kreativitas</li>
                        <li class="flex gap-2"><span class="text-ksm">✓</span> Estetika Aset Visual &amp; Audio</li>
                        <li class="flex gap-2"><span class="text-ksm">✓</span> Kerapihan Arsitektur Kode</li>
                        <li class="flex gap-2"><span class="text-ksm">✓</span> Kelengkapan Laporan Capstone</li>
                    </ul>
                    <span class="mt-3 inline-flex text-[0.68rem] font-semibold text-ksm" aria-disabled="true" title="Rubrik penilaian akan tersedia pada tahap berikutnya">Unduh Rubrik PDF (v3.2) ↗</span>
                </section>
            </div>
        </aside>

        <main class="participant-content mentor-content">
            <header class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Antrian Review Masuk</h1>
                    <p class="mt-1 max-w-2xl text-sm text-slate-500">Tinjau pengajuan dan kirim feedback akademik untuk proyek capstone mahasiswa bimbingan.</p>
                </div>
                <div class="inline-flex items-center gap-2 self-start rounded-full border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs font-medium text-slate-600 sm:self-center">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ $activePeriod ?: 'Tidak ada periode aktif' }}
                </div>
            </header>

            <section class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-3" aria-label="Ringkasan review">
                <article class="mentor-stat-card">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Menunggu Review</p>
                        <p class="mt-1 text-lg font-bold text-ksm">{{ $waitingReviewCount }} Project</p>
                        <p class="text-[0.65rem] text-ksm">Butuh tanggapan segera</p>
                    </div>
                    <span class="mentor-stat-icon bg-red-100 text-ksm" aria-hidden="true">▣</span>
                </article>
                <article class="mentor-stat-card">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Revisi Masuk</p>
                        <p class="mt-1 text-lg font-bold text-slate-900">{{ $revisionIncomingCount }} Project</p>
                        <p class="text-[0.65rem] text-slate-500">Resubmitted oleh tim</p>
                    </div>
                    <span class="mentor-stat-icon bg-purple-50 text-purple-600" aria-hidden="true">↻</span>
                </article>
                <article class="mentor-stat-card">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Selesai Ditinjau</p>
                        <p class="mt-1 text-lg font-bold text-slate-900">{{ $reviewedProjectCount }} Project</p>
                        <p class="text-[0.65rem] text-slate-500">Project yang sudah dievaluasi</p>
                    </div>
                    <span class="mentor-stat-icon bg-indigo-100 text-indigo-700" aria-hidden="true">✓</span>
                </article>
            </section>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <form method="GET" action="{{ route('dashboard.mentor') }}" class="mentor-filter-bar mb-4">
                <label class="mentor-search">
                    <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="m16 16 4 4"/></svg>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari judul proyek atau nama mahasiswa" aria-label="Cari judul project atau nama mahasiswa">
                </label>
                <div class="flex items-center gap-2">
                    <label for="status" class="sr-only">Filter status</label>
                    <select id="status" name="status" class="mentor-status-select">
                        <option value="">Semua Status</option>
                        @foreach (['Submitted' => 'Submitted', 'In Review' => 'In Review', 'Resubmitted' => 'Resubmitted'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="mentor-filter-button" aria-label="Terapkan filter" title="Terapkan filter">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="M4 6h16M7 12h10m-7 6h4"/></svg>
                    </button>
                </div>
            </form>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="mentor-review-table w-full min-w-[780px] text-left">
                        <thead>
                            <tr>
                                <th>Tanggal Submission</th>
                                <th>Judul Capstone &amp; Tim</th>
                                <th>Bidang Mentoring</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projects as $project)
                                @php
                                    $statusClass = match ($project->status) {
                                        'Resubmitted' => 'mentor-status-resubmitted',
                                        'In Review' => 'mentor-status-in-review',
                                        default => 'mentor-status-submitted',
                                    };
                                    $members = $membersByProject->get($project->project_id, collect());
                                @endphp
                                <tr>
                                    <td>
                                        <p class="whitespace-nowrap font-medium text-slate-700">{{ $project->submitted_at?->format('d M Y') ?? $project->created_at?->format('d M Y') }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $project->submitted_at?->format('H:i') ?? $project->created_at?->format('H:i') }} WIB</p>
                                    </td>
                                    <td>
                                        <p class="max-w-[15rem] font-bold leading-snug text-slate-900">{{ $project->title }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $members->pluck('name')->join(', ') ?: $project->creator?->name }}</p>
                                    </td>
                                    <td><span class="mentor-field-badge">{{ $project->mentoringField?->field_name ?? 'Belum ditentukan' }}</span></td>
                                    <td><span class="mentor-status-badge {{ $statusClass }}">{{ $project->status }}</span></td>
                                    <td class="text-right">
                                        <a href="{{ route('dashboard.mentor.project.review', $project->project_id) }}" class="mentor-review-button">
                                            Review Project <span aria-hidden="true">▣</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <p class="font-semibold text-slate-700">{{ !empty($filters['q']) || !empty($filters['status']) ? 'Tidak ada project yang sesuai filter.' : 'Belum ada project yang menunggu review.' }}</p>
                                        <p class="mt-1 text-sm text-slate-500">Pengajuan baru akan muncul di sini setelah dikirim peserta.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($projects->isNotEmpty())
                    <div class="border-t border-slate-100 px-5 py-3 text-xs text-slate-500">
                        Menampilkan {{ $projects->count() }} project pada daftar review
                    </div>
                @endif
            </section>
        </main>
    </div>

    <footer class="mt-auto border-t border-gray-700 py-8 bg-gray-900">
        <div class="mx-auto px-8 lg:px-12 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-gray-400">
            <div class="flex items-center gap-2 font-bold text-white">
                KSM Multimedia
                <span class="font-normal text-gray-400 ml-2">&copy; {{ date('Y') }} KSM Multimedia. Hak Cipta Dilindungi Undang-Undang.</span>
            </div>
            <div class="flex gap-6 font-medium">
                <a href="{{ route('landing') }}" class="text-gray-400 hover:text-white transition">Beranda</a>
                <a href="{{ route('landing') }}#showcase" class="text-gray-400 hover:text-white transition">Showcase</a>
                <a href="{{ route('landing') }}#mentoring" class="text-gray-400 hover:text-white transition">Mentoring</a>
                <a href="#" class="text-gray-400 hover:text-white transition">Kebijakan Privasi</a>
                <a href="#" class="text-gray-400 hover:text-white transition">Syarat &amp; Ketentuan</a>
            </div>
        </div>
    </footer>
</body>
</html>
