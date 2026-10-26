<!-- Hero Section -->
<header class="pt-20 pb-16 text-center container mx-auto px-8 lg:px-12 max-w-5xl">
    <div class="badge-subtle mb-6">
        <span class="badge-indicator"></span>
        ARSIP AKADEMIK & SHOWCASE MULTIMEDIA
    </div>

    <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-6">
        @if($dashboardRole)
            Selamat datang, <span class="text-ksm">{{ auth()->user()->name }}</span>
        @else
            Showcase Capstone <span class="text-ksm">KSM<br>Multimedia</span>
        @endif
    </h1>

    <p class="text-gray-500 text-lg mb-10 max-w-2xl mx-auto leading-relaxed">
        Pameran karya digital terbaik mahasiswa multimedia yang memadukan keunggulan estetika interaktif, ketajaman riset, dan standar rekayasa modern.
    </p>

    <div class="flex items-center justify-center gap-4 mb-16">
        <a href="#showcase" class="btn btn-primary">Lihat Showcase &rarr;</a>
        <a href="#mentoring" class="btn btn-outline">Mentoring</a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 border-t border-gray-100 pt-10">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">120+</h3>
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider mt-1">Karya Terpublikasi</p>
        </div>
        <div>
            <h3 class="text-3xl font-bold text-gray-900">4</h3>
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider mt-1">Bidang Utama</p>
        </div>
        <div>
            <h3 class="text-3xl font-bold text-gray-900">18</h3>
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider mt-1">Mentor Industri</p>
        </div>
        <div>
            <h3 class="text-3xl font-bold text-gray-900">100%</h3>
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider mt-1">Riset Berbasis Timbal-Balik</p>
        </div>
    </div>
</header>

<section id="mentoring" class="mentoring-section py-16">
    <div class="mx-auto px-8 lg:px-12">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 gap-4">
            <div>
                <span class="section-label text-red-300">Program Kurikulum</span>
                <h2 class="section-title text-white">4 Bidang Mentoring</h2>
            </div>
            <p class="text-gray-300 text-sm max-w-sm text-left md:text-right">
                Konsultasi terstruktur dan penempatan portofolio intensif bersama mentor berpengalaman untuk tiap jalur spesialisasi.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($mentoringFields as $field)
                <div class="custom-card p-6 group">
                    <div class="card-icon-wrapper">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    </div>
                    <h3 class="font-bold text-lg mb-2">{{ $field->field_name }}</h3>
                    <p class="text-gray-500 text-sm mb-6 line-clamp-3">
                        {{ $field->description ?? 'Deskripsi bidang ' . $field->field_name . ' dalam rekayasa multimedia.' }}
                    </p>
                    <a href="#" class="text-xs font-bold text-ksm hover:underline flex items-center gap-1">
                        Kurikulum Teknis <span class="text-lg leading-none">&rsaquo;</span>
                    </a>
                </div>
            @empty
                <p class="col-span-4 text-center text-gray-500">Data bidang mentoring belum tersedia.</p>
            @endforelse
        </div>
    </div>
</section>

<section id="showcase" class="py-20">
    <div class="mx-auto px-8 lg:px-12">
        <div class="flex justify-between items-end mb-10">
            <div>
                <span class="section-label">Kurasi Terpilih</span>
                <h2 class="section-title">Karya Terbaru</h2>
            </div>
            <a href="#" class="text-sm font-bold text-ksm hover:underline flex items-center gap-1">
                Lihat Semua <span class="text-lg leading-none">&rsaquo;</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @forelse($publishedProjects as $project)
                <div class="custom-card">
                    <div class="showcase-image-wrapper">
                        @if($project->thumbnail_url)
                            <img src="{{ $project->thumbnail_display_url }}" alt="{{ $project->title }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">Tanpa Gambar</div>
                        @endif
                        <div class="showcase-badge">{{ $project->mentoringField->field_name ?? 'General' }}</div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-ksm text-xs font-bold tracking-widest uppercase">{{ $project->mentoringField->field_name ?? 'Multimedia' }}</span>
                            <span class="text-gray-400 text-xs">ID: CS-{{ date('Y') }}-{{ str_pad($project->project_id, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <h3 class="font-bold text-xl mb-1 truncate">{{ $project->title }}</h3>
                        <p class="text-gray-500 text-sm mb-6 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            {{ $project->creator->name ?? 'Tim Kreator' }}
                        </p>
                        <a href="{{ $project->project_link ?? '#' }}" class="block w-full text-center border border-gray-200 text-gray-700 hover:border-ksm hover:text-ksm py-2 rounded-md text-sm font-medium transition">
                            Buka Detail &nearr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-10 text-center border-2 border-dashed border-gray-200 rounded-xl">
                    <p class="text-gray-500">Belum ada karya Capstone Project yang dipublikasikan.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section class="pb-20 pt-10">
    <div class="mx-auto px-8 lg:px-12 max-w-6xl">
        <div class="cta-box flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="max-w-xl text-center md:text-left">
                <span class="section-label">Peluang Kolaborasi</span>
                <h2 class="section-title mt-2 mb-4 text-2xl md:text-3xl">Siap Menampilkan Karya Capstone Anda?</h2>
                <p class="text-gray-500">
                    Mahasiswa aktif KSM Multimedia dapat mendaftarkan karya capstone semester untuk ditinjau oleh dewan kurator departemen dan dipublikasikan di galeri resmi.
                </p>
            </div>
            <div>
                <a href="{{ $dashboardRole ? route('landing') : route('login') }}" class="btn btn-primary whitespace-nowrap">
                    Submit Karya Capstone
                </a>
            </div>
        </div>
    </div>
</section>
