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
    @include('mentor.partials.topbar')

    <div class="participant-layout">
        <aside class="participant-sidebar">
            <div class="participant-sidebar-shell">
                @include('mentor.partials.sidebar-menu', ['activeMenu' => 'queue'])
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
                <a href="{{ route('showcase') }}" class="text-gray-400 hover:text-white transition">Showcase</a>
                <a href="{{ route('mentoring') }}" class="text-gray-400 hover:text-white transition">Mentoring</a>
                <a href="{{ route('privacy') }}" class="text-gray-400 hover:text-white transition">Kebijakan Privasi</a>
                <a href="{{ route('terms') }}" class="text-gray-400 hover:text-white transition">Syarat &amp; Ketentuan</a>
            </div>
        </div>
    </footer>
</body>
</html>
