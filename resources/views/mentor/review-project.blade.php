<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Project - KSM Multimedia</title>
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
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
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
                        <h2 class="text-sm font-bold">Bobot Penilaian</h2>
                    </div>
                    <ul class="space-y-2 text-[0.7rem] leading-snug text-slate-700">
                        <li class="flex justify-between gap-2"><span>Kualitas teknis &amp; arsitektur</span><strong>40%</strong></li>
                        <li class="flex justify-between gap-2"><span>Aset &amp; estetika visual</span><strong>30%</strong></li>
                        <li class="flex justify-between gap-2"><span>Dokumentasi &amp; kesiapan publik</span><strong>30%</strong></li>
                    </ul>
                </section>
            </div>
        </aside>

        <main class="participant-content mentor-content">
            <a href="{{ route('dashboard.mentor') }}" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-ksm">
                <span aria-hidden="true">←</span> Kembali ke Antrian Review
            </a>

            <header class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-ksm">Review Project</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $project->title }}</h1>
                    <p class="mt-1 text-sm text-slate-500">Periksa kelengkapan proyek, berikan penilaian, lalu tentukan tindak lanjut.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="mentor-status-badge {{ $project->status === 'Resubmitted' ? 'mentor-status-resubmitted' : ($project->status === 'In Review' ? 'mentor-status-in-review' : 'mentor-status-submitted') }}">{{ $project->status }}</span>
                    <span class="rounded-full border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs font-medium text-slate-600">{{ $activePeriod ?: 'Tidak ada periode aktif' }}</span>
                </div>
            </header>

            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <p class="mb-1 font-bold">Periksa kembali form review:</p>
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <section class="mentor-review-card mb-4">
                <h2 class="mentor-review-section-title">
                    <span aria-hidden="true">▣</span> Ringkasan Project
                </h2>
                <div class="mentor-project-meta">
                    <div>
                        <p class="text-xs font-semibold text-slate-400">DIAJUKAN OLEH</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">{{ $project->creator?->name ?? 'Tidak diketahui' }}</p>
                        <p class="text-xs text-slate-500">{{ $project->creator?->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400">BIDANG MENTORING</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">{{ $project->mentoringField?->field_name ?? 'Belum ditentukan' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400">PERIODE</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">{{ $period?->period_name ?? 'Tidak tersedia' }}</p>
                    </div>
                </div>
                <div class="border-t border-slate-100 pt-4">
                    <h3 class="text-sm font-bold text-slate-800">Deskripsi</h3>
                    <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-slate-600">{{ $project->description ?: 'Belum ada deskripsi project.' }}</p>
                </div>
                @if ($projectUrl || $files->isNotEmpty())
                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($projectUrl)
                            <a href="{{ $projectUrl }}" target="_blank" rel="noopener noreferrer" class="mentor-review-link">Buka Demo / Repository <span aria-hidden="true">↗</span></a>
                        @endif
                        @foreach ($files as $file)
                            <a href="{{ route('media.show', ['path' => $file->file_path]) }}" target="_blank" rel="noopener noreferrer" class="mentor-review-link">{{ $file->file_name }} <span aria-hidden="true">↗</span></a>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="mentor-review-card mb-4">
                <h2 class="mentor-review-section-title"><span aria-hidden="true">♙</span> Anggota Tim</h2>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full min-w-[420px] text-left text-sm">
                        <thead class="text-xs uppercase text-slate-400">
                            <tr><th class="pb-2 pr-4 font-semibold">Nama</th><th class="pb-2 pr-4 font-semibold">Email</th><th class="pb-2 font-semibold">Peran</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($members as $member)
                                <tr>
                                    <td class="py-2 pr-4 font-semibold text-slate-700">{{ $member->name }}</td>
                                    <td class="py-2 pr-4 text-slate-500">{{ $member->email }}</td>
                                    <td class="py-2 text-slate-600">{{ $member->member_role }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-3 text-slate-500">Informasi anggota tim belum tersedia.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 border-t border-slate-100 pt-3">
                    <h3 class="text-xs font-bold uppercase text-slate-400">Tools &amp; Stack Teknologi</h3>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @forelse ($tools as $tool)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $tool }}</span>
                        @empty
                            <span class="text-sm text-slate-500">Informasi tools belum tersedia.</span>
                        @endforelse
                    </div>
                </div>
            </section>

            @if ($latestVersion?->changelog)
                <section class="mentor-review-context mb-4">
                    <h2 class="mb-1 font-bold text-slate-700">Catatan perubahan / revisi peserta</h2>
                    <p class="whitespace-pre-line">{{ $latestVersion->changelog }}</p>
                </section>
            @endif

            <form method="POST" action="{{ route('dashboard.mentor.project.review.store', $project->project_id) }}" class="space-y-4">
                @csrf
                <section class="mentor-review-card">
                    <h2 class="mentor-review-section-title"><span aria-hidden="true">✦</span> Penilaian Rubrik</h2>
                    <p class="mt-3 text-xs leading-relaxed text-slate-500">Pilih satu tingkat penilaian untuk setiap kriteria. Nilai tiap tingkat (1, 3, atau 5) dihitung sesuai bobot untuk menghasilkan rating keseluruhan 1–5.</p>
                    @php
                        $criteria = [
                            'technical_quality' => ['Kualitas teknis & arsitektur', '40%', ['Perlu Perbaikan', 'Memenuhi Standar', 'Istimewa / Melampaui']],
                            'creative_quality' => ['Aset & estetika visual', '30%', ['Cukup', 'Sangat Baik', 'Istimewa']],
                            'publication_readiness' => ['Dokumentasi & kesiapan publik', '30%', ['Belum Siap', 'Memenuhi Syarat', 'Sangat Lengkap']],
                        ];
                        $levels = ['needs_improvement', 'meets_standard', 'exceptional'];
                    @endphp
                    <div class="mt-4 space-y-3">
                        @foreach ($criteria as $key => [$label, $weight, $options])
                            <fieldset class="mentor-criterion">
                                <legend class="mb-3 flex w-full justify-between gap-3 text-sm font-bold text-slate-800">
                                    <span>{{ $label }}</span><span class="shrink-0 text-xs font-bold text-ksm">{{ $weight }}</span>
                                </legend>
                                <div class="mentor-rating-options">
                                    @foreach ($levels as $index => $level)
                                        @php($inputId = 'rubric-' . $key . '-' . $level)
                                        <div class="mentor-rating-option">
                                            <input id="{{ $inputId }}" type="radio" name="rubric[{{ $key }}]" value="{{ $level }}" @checked(old('rubric.' . $key) === $level)>
                                            <label for="{{ $inputId }}">{{ $options[$index] }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                </section>

                <section class="mentor-review-card">
                    <label for="comment" class="mentor-review-section-title"><span aria-hidden="true">☷</span> Feedback Mentor <span class="text-red-600">*</span></label>
                    <p class="mt-3 text-xs leading-relaxed text-slate-500">Tulis masukan yang jelas dan dapat ditindaklanjuti peserta (maksimal 1000 karakter).</p>
                    <textarea id="comment" name="comment" maxlength="1000" required class="mentor-review-comment mt-3" placeholder="Tuliskan feedback dan catatan perbaikan untuk project ini...">{{ old('comment') }}</textarea>
                    <p class="mt-1 text-right text-xs text-slate-400">Maks. 1000 karakter</p>
                </section>

                <section class="mentor-review-card">
                    <h2 class="mentor-review-section-title"><span aria-hidden="true">⚑</span> Keputusan Review</h2>
                    <p class="mt-3 text-xs leading-relaxed text-slate-500">Pilih tindakan yang akan diterima peserta. Rekomendasi publikasi akan diteruskan kepada pengurus untuk keputusan akhir.</p>
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <button type="submit" name="decision" value="Revision Required" class="mentor-decision-button mentor-decision-revision">
                            <span aria-hidden="true">↻</span> Minta Revisi
                        </button>
                        <button type="submit" name="decision" value="Approved" class="mentor-decision-button mentor-decision-approve">
                            <span aria-hidden="true">✓</span> Rekomendasikan ke Pengurus
                        </button>
                    </div>
                </section>
            </form>
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