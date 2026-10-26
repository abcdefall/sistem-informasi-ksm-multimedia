<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Publikasi - KSM Multimedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">
    @include('pengurus.partials.navbar')

    <div class="participant-layout pengurus-layout">
        @include('pengurus.partials.sidebar')

        <main class="participant-content mentor-content pengurus-content">
            <a href="{{ route('dashboard.pengurus.approvals') }}" class="mb-4 inline-flex items-center gap-2 text-xs font-semibold text-ksm hover:underline">← Kembali ke Daftar Approval</a>

            <header class="mb-5 flex flex-col justify-between gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Persetujuan Publikasi: {{ $project->title }}</h1>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[0.68rem] text-slate-500">
                        <span>Capstone ID: <strong class="text-slate-700">{{ $project->project_id }}</strong></span>
                        <span>Bidang: <strong class="text-slate-700">{{ $project->mentoringField?->field_name ?? 'Belum ditentukan' }}</strong></span>
                        <span>Periode: <strong class="text-slate-700">{{ $period?->period_name ?? 'Tidak tersedia' }}</strong></span>
                    </div>
                </div>
                <span class="mentor-status-badge {{ $project->status === 'Published' ? 'pengurus-status-published' : 'pengurus-status-approved' }}">
                    {{ $project->status === 'Published' ? 'Sudah Dipublikasikan' : 'Lolos Evaluasi Mentor' }}
                </span>
            </header>

            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-[1.25fr_0.9fr]">
                <div class="space-y-4">
                    <section class="mentor-review-card">
                        <div class="mentor-review-section-title">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5h16v14H4zM8 9h8M8 13h5"/></svg>
                            Ringkasan Karya &amp; Profil Pengusul
                            <span class="ml-auto rounded bg-indigo-100 px-2 py-1 text-[0.6rem] font-semibold text-indigo-700">{{ $project->mentoringField?->field_name ?? 'Bidang belum ditentukan' }}</span>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <p class="text-[0.6rem] font-bold uppercase text-slate-400">Anggota Tim Pengusul</p>
                                <p class="mt-1 text-xs font-semibold text-slate-800">{{ $members->pluck('name')->join(', ') ?: $project->creator?->name }}</p>
                                <p class="mt-1 text-[0.65rem] text-slate-500">Pengaju: {{ $project->creator?->name }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <p class="text-[0.6rem] font-bold uppercase text-slate-400">Status Akademis</p>
                                <p class="mt-1 text-xs font-semibold text-slate-800">{{ $project->status === 'Published' ? 'Terbit di Showcase' : 'Reguler - Tingkat Akhir' }}</p>
                            </div>
                        </div>

                        <div class="mt-3 overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                            @if ($project->thumbnail_url)
                                <img src="{{ $project->thumbnail_display_url }}" alt="Thumbnail {{ $project->title }}" class="max-h-[24rem] w-full object-contain">
                            @else
                                <div class="flex min-h-48 items-center justify-center text-sm text-slate-400">Thumbnail project belum tersedia</div>
                            @endif
                        </div>
                        <p class="mt-3 whitespace-pre-line text-xs leading-relaxed text-slate-600">{{ $project->description ?: 'Belum ada deskripsi project.' }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if ($project->project_link && filter_var($project->project_link, FILTER_VALIDATE_URL) && in_array(parse_url($project->project_link, PHP_URL_SCHEME), ['http', 'https'], true))
                                <a href="{{ $project->project_link }}" target="_blank" rel="noopener noreferrer" class="mentor-review-link">Buka Demo / Repository ↗</a>
                            @endif
                            @foreach ($files as $file)
                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($file->file_path) }}" target="_blank" rel="noopener noreferrer" class="mentor-review-link">{{ $file->file_name }} ↗</a>
                            @endforeach
                        </div>
                        @if ($tools->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($tools as $tool)
                                    <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[0.63rem] font-semibold text-rose-800">{{ $tool }}</span>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    @if ($latestReview)
                        <section class="mentor-review-context border-l-4 border-ksm">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h2 class="font-bold text-slate-800">Catatan Rekomendasi Mentor</h2>
                                <span class="rounded bg-emerald-100 px-2 py-1 text-[0.62rem] font-semibold text-emerald-800">✓ Direkomendasikan</span>
                            </div>
                            <p class="mt-1 text-[0.65rem] text-slate-500">{{ $latestReview->reviewer_name }}</p>
                            <p class="mt-3 whitespace-pre-line text-xs leading-relaxed text-slate-700">“{{ $latestReview->comment }}”</p>
                        </section>
                    @endif

                    <section class="mentor-review-card">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h2 class="mentor-review-section-title flex-1 border-0 pb-0">Skor Evaluasi Rubrik Akademik</h2>
                            <p class="text-sm font-extrabold text-slate-900">{{ $latestReview?->rating ? number_format((float) $latestReview->rating * 20, 0) : '—' }} <span class="text-[0.65rem] font-medium text-slate-500">/ 100 Rata-rata</span></p>
                        </div>
                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-3">
                            @foreach ([
                                'technical_quality' => 'Kualitas Teknis',
                                'creative_quality' => 'Estetika Visual',
                                'publication_readiness' => 'Dokumentasi',
                            ] as $criterion => $label)
                                @php($score = data_get($rubricScores, $criterion . '.score'))
                                <article class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                    <div class="flex items-center justify-between gap-1">
                                        <h3 class="text-[0.65rem] text-slate-500">{{ $label }}</h3>
                                        <strong class="text-xs text-ksm">{{ $score ? $score * 20 : '—' }}</strong>
                                    </div>
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200">
                                        <span class="block h-full rounded-full bg-ksm" style="width: {{ $score ? $score * 20 : 0 }}%"></span>
                                    </div>
                                    <p class="mt-1 text-[0.58rem] text-slate-500">Bobot {{ data_get($rubricScores, $criterion . '.weight', '—') }}%</p>
                                </article>
                            @endforeach
                        </div>
                    </section>
                </div>

                <div class="space-y-4">
                    <section class="mentor-review-card">
                        <h2 class="mentor-review-section-title">Checklist Standar Etalase</h2>
                        <p class="mt-3 text-xs leading-relaxed text-slate-500">Verifikasi setiap persyaratan sebelum memublikasikan karya.</p>
                        <div class="mt-3 space-y-2">
                            @foreach ([
                                'thumbnail' => ['Kelengkapan Thumbnail 16:9', 'Resolusi tinggi, jernih, tanpa watermark pihak ketiga.'],
                                'rights' => ['Bebas Pelanggaran Hak Cipta', 'Aset audio & visual berlisensi CC atau orisinal tim.'],
                                'demo' => ['Tautan Demo Akses Publik', 'Dapat dimainkan tanpa kredensial login khusus.'],
                                'originality' => ['Integritas Akademik KSM', 'Pakta keaslian karya telah ditandatangani digital.'],
                            ] as $key => [$label, $description])
                                <label class="flex gap-2.5 rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs">
                                    <input form="publication-form" type="checkbox" name="checklist[{{ $key }}]" value="1" required class="mt-0.5 accent-ksm" @checked(old('checklist.' . $key)) @disabled($project->status === 'Published')>
                                    <span><strong class="block text-slate-800">{{ $label }}</strong><span class="mt-1 block leading-relaxed text-slate-500">{{ $description }}</span></span>
                                </label>
                            @endforeach
                        </div>
                    </section>

                    <section class="mentor-review-card">
                        <h2 class="mentor-review-section-title">Pengaturan Visibilitas Etalase</h2>
                        <label class="mt-3 flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <span>
                                <strong class="block text-xs text-slate-800">Tampilkan di Beranda Utama</strong>
                                <span class="mt-1 block text-[0.65rem] text-slate-500">Sematkan sebagai capstone pilihan bulan ini.</span>
                            </span>
                            <input form="publication-form" type="checkbox" name="showcase" value="1" class="peer sr-only" @checked(old('showcase', $project->is_featured)) @disabled($project->status === 'Published')>
                            <span class="relative h-6 w-11 shrink-0 rounded-full bg-slate-300 transition peer-checked:bg-ksm peer-disabled:opacity-60 after:absolute after:left-1 after:top-1 after:h-4 after:w-4 after:rounded-full after:bg-white after:transition peer-checked:after:translate-x-5"></span>
                        </label>
                        <p class="mt-3 text-[0.65rem] font-semibold text-slate-600">Tag kurasi publik</p>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach ($tools->take(3) as $tool)
                                <span class="rounded-full bg-rose-100 px-2 py-1 text-[0.6rem] font-semibold text-rose-800">#{{ \Illuminate\Support\Str::slug($tool) }}</span>
                            @endforeach
                            @if ($project->mentoringField)
                                <span class="rounded-full bg-rose-100 px-2 py-1 text-[0.6rem] font-semibold text-rose-800">#{{ \Illuminate\Support\Str::slug($project->mentoringField->field_name) }}</span>
                            @endif
                        </div>
                    </section>
                </div>
            </div>

            <section class="mentor-review-card mt-5 border-slate-200 shadow-sm">
                <p class="text-[0.62rem] font-bold uppercase tracking-[0.12em] text-ksm">Otorisasi Resmi Pengurus</p>
                <div class="mt-1 flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Prosedur Keputusan &amp; Penerbitan Final</h2>
                        @if ($project->publication_approved_at)
                            <p class="mt-1 text-xs text-emerald-700">Approval diberikan oleh {{ $publicationApprover?->name ?? 'Pengurus' }} pada {{ $project->publication_approved_at->format('d M Y H:i') }}.</p>
                        @else
                            <p class="mt-1 text-xs text-slate-500">Approval resmi harus diberikan sebelum project dapat dipublikasikan.</p>
                        @endif
                    </div>
                    <span class="rounded border border-slate-200 bg-slate-50 px-2 py-1 text-[0.6rem] text-slate-500">Tercatat pada log audit KSM</span>
                </div>

                @if ($project->status === 'Published')
                    <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">Project telah dipublikasikan ke showcase {{ $project->is_featured ? 'dan ditampilkan di beranda utama.' : 'tanpa ditampilkan di beranda utama.' }}</div>
                @else
                    <form id="publication-form" method="POST" action="{{ route('dashboard.pengurus.approval.store', $project->project_id) }}" class="mt-4">
                        @csrf
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @if (!$project->publication_approved_at)
                                <button type="submit" name="action" value="approve" formnovalidate class="pengurus-secondary-action w-full border-ksm text-ksm hover:bg-rose-50">
                                    <span aria-hidden="true">☑</span> (1) Berikan Approval Resmi Pengurus
                                </button>
                            @else
                                <div class="flex min-h-11 items-center justify-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 text-xs font-semibold text-emerald-800">
                                    <span aria-hidden="true">✓</span> Approval Resmi Tersimpan
                                </div>
                            @endif
                            <button type="submit" name="action" value="publish" class="pengurus-primary-action w-full" @disabled(!$project->publication_approved_at)>
                                <span aria-hidden="true">⇧</span> (2) Publikasikan ke Etalase Showcase Publik ↗
                            </button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('dashboard.pengurus.approval.store', $project->project_id) }}" class="mt-4 flex flex-col gap-2 border-t border-slate-100 pt-4 sm:flex-row">
                        @csrf
                        <input type="hidden" name="action" value="request_revision">
                        <label class="sr-only" for="revision-comment">Catatan revisi</label>
                        <textarea id="revision-comment" name="comment" maxlength="1000" required class="mentor-review-comment min-h-16 flex-1" placeholder="Catatan untuk dikembalikan kepada mentor atau peserta..."></textarea>
                        <button type="submit" class="pengurus-secondary-action self-start text-ksm">Kembalikan Catatan ke Mentor / Mahasiswa</button>
                    </form>
                @endif

                <p class="mt-4 border-t border-slate-100 pt-3 text-xs leading-relaxed text-slate-500">Setelah dipublikasikan, status project berubah menjadi <strong>PUBLISHED</strong> dan karya dapat diakses secara terbuka oleh publik di halaman Showcase KSM Multimedia.</p>
            </section>
        </main>
    </div>

    @include('pengurus.partials.footer')
</body>
</html>
