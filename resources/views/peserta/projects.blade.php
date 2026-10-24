<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project - KSM Multimedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">
    <nav class="sticky top-0 z-40 border-b border-gray-200 py-4" style="background-color: var(--color-navbar-bg);">
        <div class="mx-auto px-8 lg:px-12 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo-ksm.png') }}" alt="Logo KSM Multimedia" class="w-8 h-8 object-contain">
                <span class="font-bold text-xl tracking-tight text-ksm">KSM Multimedia</span>
                <span class="bg-red-50 text-ksm text-xs font-bold px-2 py-1 rounded border border-red-100 ml-2">PORTAL PESERTA</span>
            </a>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3 text-right">
                    <div class="w-10 h-10 rounded-full bg-ksm text-white flex items-center justify-center font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="hidden md:block">
                        <p class="text-sm font-bold text-gray-800">Halo, {{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">Peserta</p>
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
                <nav class="sidebar-menu">
                    <a href="{{ route('dashboard.peserta') }}" class="sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('dashboard.peserta.project.create') }}" class="sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Ajukan Project Baru
                    </a>
                    <a href="{{ route('dashboard.peserta.projects.index') }}" class="sidebar-link active" aria-current="page">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        Project
                    </a>
                    <a href="#" class="sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        Jadwal Mentoring
                    </a>
                    <a href="#" class="sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a2 2 0 01.293.707V19a2 2 0 01-2-2z"></path></svg>
                        Panduan &amp; Template
                    </a>
                    <a href="#" class="sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21a8 8 0 0116 0"></path></svg>
                        Profil
                    </a>
                </nav>
                <div class="mt-8 mb-2 px-3 text-xs font-bold text-gray-400 tracking-wider">BANTUAN</div>
                <nav class="sidebar-menu">
                    <a href="#" class="sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.523 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        Panduan Capstone
                    </a>
                </nav>
                @include('peserta.partials.mentor-card')
            </div>
        </aside>

        <main class="participant-content">
            <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <p class="section-label">Portal Peserta</p>
                    <h1 class="text-3xl font-extrabold text-gray-900">Project</h1>
                    <p class="mt-2 text-sm text-gray-500">Pantau status pengajuan, lihat feedback mentor, dan buka detail project Anda.</p>
                </div>
                <a href="{{ route('dashboard.peserta.project.create') }}" class="btn btn-primary text-sm">+ Ajukan Project</a>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="status">{{ session('status') }}</div>
            @endif

            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="stat-card"><div><p class="text-sm font-semibold text-gray-500">Total Project</p><p class="mt-1 text-2xl font-bold text-gray-900">{{ $projects->count() }}</p></div><span class="text-2xl text-ksm">▤</span></div>
                <div class="stat-card"><div><p class="text-sm font-semibold text-gray-500">Perlu Revisi</p><p class="mt-1 text-2xl font-bold text-gray-900">{{ $projects->where('status', 'Revision Required')->count() }}</p></div><span class="text-2xl text-red-600">!</span></div>
                <div class="stat-card"><div><p class="text-sm font-semibold text-gray-500">Tayang Showcase</p><p class="mt-1 text-2xl font-bold text-gray-900">{{ $projects->where('status', 'Published')->count() }}</p></div><span class="text-2xl text-green-600">✓</span></div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <h2 class="text-lg font-bold text-gray-900">Daftar Pengajuan</h2>
                    <p class="mt-1 text-sm text-gray-500">Pilih project untuk melihat detail dan riwayat evaluasi.</p>
                </div>
                @if ($projects->isNotEmpty())
                    <div class="divide-y divide-gray-100">
                        @foreach ($projects as $project)
                            @php
                                $statusClass = match ($project->status) {
                                    'Published', 'Approved' => 'status-published',
                                    'Revision Required' => 'status-revision',
                                    'Submitted', 'In Review', 'Resubmitted' => 'status-submitted',
                                    default => 'status-draft',
                                };
                                $review = $latestReviews->get($project->project_id);
                            @endphp
                            <article class="p-5 transition hover:bg-gray-50 sm:p-6">
                                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                                    <div class="min-w-0">
                                        <div class="mb-2 flex flex-wrap items-center gap-2">
                                            <span class="text-xs font-semibold text-gray-400">ID: {{ $project->project_id }}</span>
                                            <span class="status-badge {{ $statusClass }}">{{ $project->status }}</span>
                                        </div>
                                        <h3 class="text-lg font-bold text-gray-900">{{ $project->title }}</h3>
                                        <p class="mt-1 text-sm text-gray-500">{{ $project->mentoringField?->field_name ?? 'Bidang belum ditentukan' }}</p>
                                    </div>
                                    <a href="{{ route('dashboard.peserta.project.show', $project->project_id) }}" class="inline-flex shrink-0 items-center justify-center rounded-lg border border-ksm px-4 py-2 text-sm font-semibold text-ksm transition hover:bg-red-50">
                                        Lihat Detail <span class="ml-2">&rarr;</span>
                                    </a>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-xs text-gray-500">
                                    <span>Diajukan: {{ ($project->submitted_at ?? $project->created_at)?->format('d M Y') ?? '-' }}</span>
                                    <span>{{ $project->project_link ? 'Bukti project tersedia' : 'Belum ada link project' }}</span>
                                    @if ($review)
                                        <span>Feedback terbaru: {{ \Illuminate\Support\Carbon::parse($review->latest_review_at)->format('d M Y') }}</span>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-16 text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-2xl text-ksm">▤</div>
                        <h3 class="text-lg font-bold text-gray-900">Belum ada project</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm text-gray-500">Project yang Anda ajukan atau yang mencantumkan Anda sebagai anggota akan muncul di halaman ini.</p>
                        <a href="{{ route('dashboard.peserta.project.create') }}" class="btn btn-primary mt-5 text-sm">Ajukan Project Pertama</a>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <footer class="mt-auto border-t border-gray-700 bg-gray-900 py-8">
        <div class="mx-auto px-8 lg:px-12 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-gray-400">
            <div class="flex items-center gap-2 font-bold text-white">KSM Multimedia <span class="font-normal text-gray-400 ml-2">&copy; {{ date('Y') }} KSM Multimedia. Hak Cipta Dilindungi Undang-Undang.</span></div>
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
