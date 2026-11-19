<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail {{ $version->version_number }} - Version History</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">
    @include('pengurus.partials.navbar')

    <div class="participant-layout pengurus-layout">
        @include('pengurus.partials.sidebar', ['activeMenu' => 'versions'])

        <main class="participant-content mentor-content pengurus-content">
            <a href="{{ route('dashboard.pengurus.version-history.project', $version->project_id) }}" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ksm hover:underline">← Kembali ke Riwayat Versi Project</a>

            <header class="mb-5 flex flex-col gap-3 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-ksm">Snapshot Versi Project</p>
                    <h1 class="mt-1 break-words text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $version->title_snapshot ?? 'Judul snapshot tidak tercatat' }}</h1>
                    <p class="mt-1 text-sm text-slate-500">Project {{ $version->project_id }} · Versi {{ $version->version_number }} · ID {{ $version->version_id }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-800">{{ $version->history_status }}</span>
                    @if ($version->is_published)
                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Pernah dipublikasikan</span>
                    @endif
                    @if ($version->is_legacy_snapshot)
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Snapshot legacy mungkin tidak lengkap</span>
                    @endif
                </div>
            </header>

            @if ($version->is_legacy_snapshot)
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="note">
                    Snapshot ini berasal dari data legacy dan mungkin tidak memiliki seluruh informasi historis. Informasi yang tidak tercatat tidak diganti dengan data project terbaru.
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-[1.2fr_0.8fr]">
                <div class="space-y-4">
                    <section class="mentor-review-card">
                        <h2 class="mentor-review-section-title">Snapshot Submission</h2>
                        <dl class="mt-4 space-y-4 text-sm">
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">Deskripsi project</dt>
                                <dd class="mt-1 whitespace-pre-line leading-relaxed text-slate-700">{{ $version->description_snapshot ?? 'Snapshot deskripsi tidak tercatat.' }}</dd>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs font-semibold uppercase text-slate-400">Bidang</dt>
                                    <dd class="mt-1 text-slate-700">{{ $version->field_name_snapshot ?? 'Tidak tercatat' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase text-slate-400">Periode mentoring</dt>
                                    <dd class="mt-1 text-slate-700">{{ $version->period_name_snapshot ?? 'Tidak tercatat' }}</dd>
                                </div>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">Project link saat submission</dt>
                                <dd class="mt-1 break-all">
                                    @if ($projectLinkUrl)
                                        <a href="{{ $projectLinkUrl }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-ksm hover:underline">{{ $version->project_link_snapshot }} ↗</a>
                                    @else
                                        <span class="text-slate-500">{{ $version->project_link_snapshot ?: 'Tidak tercatat' }}</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">Anggota tim pada submission</dt>
                                @if (is_array($version->team_snapshot) && count($version->team_snapshot) > 0)
                                    <dd class="mt-2">
                                        <ul class="space-y-2">
                                            @foreach ($version->team_snapshot as $member)
                                                <li class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                                    <span class="font-semibold text-slate-800">{{ $member['name'] ?? 'Nama tidak tercatat' }}</span>
                                                    @if (!empty($member['member_role']))
                                                        <span class="text-slate-500"> · {{ $member['member_role'] }}</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </dd>
                                @else
                                    <dd class="mt-1 text-slate-500">Snapshot tim tidak tersedia.</dd>
                                @endif
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">Tools pada submission</dt>
                                <dd class="mt-2 flex flex-wrap gap-2">
                                    @forelse ($version->tools_snapshot ?? [] as $tool)
                                        <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-800">{{ $tool }}</span>
                                    @empty
                                        <span class="text-slate-500">Tidak tercatat.</span>
                                    @endforelse
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">Changelog</dt>
                                <dd class="mt-1 whitespace-pre-line leading-relaxed text-slate-700">{{ $version->changelog ?: 'Pengajuan awal atau catatan perubahan tidak tersedia.' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="mentor-review-card">
                        <h2 class="mentor-review-section-title">Thumbnail dan File Versi</h2>
                        @if ($thumbnailUrl)
                            <a href="{{ $thumbnailUrl }}" target="_blank" rel="noopener noreferrer" class="mt-4 block overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                <img src="{{ $thumbnailUrl }}" alt="Thumbnail snapshot {{ $version->version_number }}" class="max-h-80 w-full object-contain">
                            </a>
                        @else
                            <p class="mt-3 text-sm text-slate-500">Thumbnail snapshot tidak tersedia.</p>
                        @endif

                        <h3 class="mt-5 text-xs font-semibold uppercase text-slate-400">File submission</h3>
                        @if ($version->files->isNotEmpty())
                            <ul class="mt-2 space-y-2">
                                @foreach ($version->files as $file)
                                    <li>
                                        <a href="{{ route('media.show', ['path' => $file->file_path]) }}" target="_blank" rel="noopener noreferrer" class="break-all text-sm font-semibold text-ksm hover:underline">{{ $file->file_name_snapshot }} ↗</a>
                                    </li>
                                @endforeach
                            </ul>
                        @elseif ($version->file_path)
                            <a href="{{ route('media.show', ['path' => $version->file_path]) }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex break-all text-sm font-semibold text-ksm hover:underline">{{ basename($version->file_path) }} ↗</a>
                        @else
                            <p class="mt-2 text-sm text-slate-500">Tidak ada referensi file pada versi ini.</p>
                        @endif
                    </section>
                </div>

                <div class="space-y-4">
                    <section class="mentor-review-card">
                        <h2 class="mentor-review-section-title">Informasi Versi</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">Nomor versi</dt>
                                <dd class="mt-1 font-semibold text-slate-800">{{ $version->version_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">Waktu submission</dt>
                                <dd class="mt-1 text-slate-700">{{ $version->submitted_at?->format('d M Y, H:i') ?? 'Tidak tercatat' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">Pengirim</dt>
                                <dd class="mt-1 text-slate-700">{{ $version->submitter?->name ?? 'Tidak tercatat' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">Dibuat</dt>
                                <dd class="mt-1 text-slate-700">{{ $version->created_at?->format('d M Y, H:i') ?? 'Tidak tercatat' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">Approval</dt>
                                <dd class="mt-1 text-slate-700">
                                    @if ($version->approved_at)
                                        Disetujui {{ $version->approved_at->format('d M Y, H:i') }} oleh {{ $version->approvedBy?->name ?? 'Pengurus tidak tercatat' }}
                                    @else
                                        Belum tercatat atau belum disetujui.
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">Publikasi</dt>
                                <dd class="mt-1 text-slate-700">
                                    @if ($version->is_published)
                                        Versi ini pernah dipublikasikan{{ $version->published_at ? ' pada '.$version->published_at->format('d M Y, H:i') : '' }}.
                                    @else
                                        Versi ini belum tercatat sebagai versi publikasi.
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="mentor-review-card">
                        <h2 class="mentor-review-section-title">Review dan Feedback</h2>
                        @forelse ($version->reviews as $review)
                            <article class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <p class="text-sm font-semibold text-slate-800">{{ $review->reviewer?->name ?? 'Reviewer tidak tersedia' }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $review->decision ?? 'Keputusan tidak tercatat' }}
                                    @if ($review->score_out_of_100 !== null) · Nilai {{ $review->score_out_of_100 }}/100 @endif
                                    · {{ $review->created_at?->format('d M Y, H:i') ?? 'Waktu tidak tercatat' }}
                                </p>
                                <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $review->comment }}</p>
                            </article>
                        @empty
                            <p class="mt-3 text-sm text-slate-500">Belum ada review yang ditautkan ke versi ini.</p>
                        @endforelse
                    </section>
                </div>
            </div>
        </main>
    </div>

    @include('pengurus.partials.footer')
</body>
</html>
