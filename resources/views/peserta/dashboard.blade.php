<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Peserta - KSM Multimedia</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Memanggil CSS Landing (untuk tombol standar) dan CSS Dashboard -->
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">

    <!-- Navbar (Mirip Landing Page tapi versi Login) -->
    <nav class="border-b border-gray-200 py-4 sticky top-0 z-40" style="background-color: var(--color-navbar-bg);">
        <div class="mx-auto px-8 lg:px-12 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo-ksm.png') }}" alt="Logo KSM Multimedia" class="w-8 h-8 object-contain">
                <span class="font-bold text-xl tracking-tight text-ksm">KSM Multimedia</span>
                <span class="bg-red-50 text-ksm text-xs font-bold px-2 py-1 rounded border border-red-100 ml-2">PORTAL PESERTA</span>
            </a>
            
            <!-- User Profile Menu -->
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

    <!-- Layout Utama Dashboard -->
    <div class="participant-layout">
        
        <!-- Sidebar Kiri -->
        <aside class="participant-sidebar">
            <div class="participant-sidebar-shell">
                <nav class="sidebar-menu">
                    <a href="#dashboard" class="sidebar-link active">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('dashboard.peserta.project.create') }}" class="sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Ajukan Project Baru
                    </a>
                    <a href="{{ route('dashboard.peserta.projects.index') }}" class="sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        Project
                    </a>
                    <a href="#" class="sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        Jadwal Mentoring
                    </a>
                    <a href="#" class="sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Panduan & Template
                    </a>
                    <a href="#" class="sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21a8 8 0 0116 0"></path></svg>
                        Profil
                    </a>
                </nav>

                <div class="mt-8 mb-2 px-3 text-xs font-bold text-gray-400 tracking-wider">BANTUAN</div>
                <nav class="sidebar-menu">
                    <a href="#" class="sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        Panduan Capstone
                    </a>
                </nav>
                @include('peserta.partials.mentor-card')

            </div>
        </aside>

        <!-- Konten Kanan -->
        <main class="participant-content">
            
            <!-- Header Dashboard -->
            <div class="mb-8">
                <h1 id="dashboard" class="text-3xl font-extrabold text-gray-900 mb-1">Dashboard Peserta</h1>
                <p class="text-gray-500">Kelola progres pengajuan capstone project dan tinjau evaluasi berkala mentor.</p>
            </div>
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Statistic Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Card 1 -->
                <div class="stat-card">
                    <div>
                        <p class="text-sm font-semibold text-gray-500 mb-1">Total Pengajuan</p>
                        <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $totalProjects }} <span class="text-sm font-normal text-gray-500">Project</span></h3>
                        <p class="text-xs text-gray-400">Total project yang Anda buat</p>
                    </div>
                    <div class="w-10 h-10 bg-gray-100 text-gray-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="stat-card">
                    <div>
                        <p class="text-sm font-semibold text-gray-500 mb-1">Butuh Revisi</p>
                        <h3 class="text-3xl font-bold text-gray-900 mb-2">{{ $revisionProjects }} <span class="text-sm font-normal text-gray-500">Project</span></h3>
                        <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-full">Perlu Tindakan</span>
                    </div>
                    <div class="w-10 h-10 bg-red-50 text-red-500 border border-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="stat-card">
                    <div>
                        <p class="text-sm font-semibold text-gray-500 mb-1">Siap / Published</p>
                        <h3 class="text-3xl font-bold text-gray-900 mb-2">{{ $publishedProjects }} <span class="text-sm font-normal text-gray-500">Project</span></h3>
                        <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-full">Terverifikasi</span>
                    </div>
                    <div class="w-10 h-10 bg-green-50 text-green-500 border border-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Tabel Daftar Project -->
            <div id="projects" class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <!-- Table Header -->
                <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Daftar Project</h2>
                        <p class="text-sm text-gray-500">Daftar arsip dan status moderasi capstone yang telah diajukan.</p>
                    </div>
                    <span class="text-sm font-semibold text-gray-600">{{ $totalProjects }} project</span>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100 bg-gray-50">
                                <th class="p-4">Judul Project</th>
                                <th class="p-4">Bidang Mentoring</th>
                                <th class="p-4">Tanggal Pengajuan</th>
                                <th class="p-4">Status Evaluasi</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($projects as $project)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4">
                                        <p class="font-bold text-gray-900">{{ $project->title }}</p>
                                        <p class="text-xs text-gray-500">{{ $project->project_id }}</p>
                                    </td>
                                    <td class="p-4 text-sm font-medium text-gray-700">{{ $project->mentoringField?->field_name ?? 'Belum ditentukan' }}</td>
                                    <td class="p-4 text-sm text-gray-600">{{ ($project->submitted_at ?? $project->created_at)?->format('d M Y') ?? '-' }}</td>
                                    <td class="p-4">
                                        @php
                                            $statusClass = match ($project->status) {
                                                'Published', 'Approved' => 'status-published',
                                                'Revision Required' => 'status-revision',
                                                default => 'status-draft',
                                            };
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}">{{ $project->status }}</span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <a href="{{ route('dashboard.peserta.project.show', $project->project_id) }}" class="text-ksm border border-ksm hover:bg-red-50 font-medium text-xs px-3 py-1.5 rounded whitespace-nowrap">
                                            Lihat Detail &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-sm text-gray-500">
                                        Belum ada project yang Anda ajukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div class="p-4 border-t border-gray-100 text-sm text-gray-500">
                    Menampilkan {{ $projects->count() }} project
                </div>
            </div>

        </main>
    </div>

    <!-- Footer Konsisten -->
    <footer class="border-t border-gray-700 py-8 bg-gray-900 mt-auto">
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
                <a href="#" class="text-gray-400 hover:text-white transition">Syarat & Ketentuan</a>
            </div>
        </div>
    </footer>

</body>
</html>