<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project->title }} - Project Peserta</title>
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
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21a8 8 0 0116 0"></path></svg>
                        Profil
                    </a>
                </nav>
            </div>
        </aside>

        <main class="participant-content space-y-5">
            <a href="{{ route('dashboard.peserta.projects.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-ksm">&larr; Kembali ke Project</a>

            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                    <p class="font-semibold">Periksa kembali data revisi:</p>
                    <ul class="mt-2 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            @php
                $statusClass = match ($project->status) {
                    'Published', 'Approved' => 'status-published',
                    'Revision Required' => 'status-revision',
                    'Submitted', 'In Review', 'Resubmitted' => 'status-submitted',
                    default => 'status-draft',
                };
            @endphp

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm md:p-8">
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div class="min-w-0">
                        <p class="mb-2 inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-ksm">ID Project: {{ \Illuminate\Support\Str::limit($project->project_id, 13, '') }}</p>
                        <h1 class="text-2xl font-extrabold leading-tight text-gray-900 md:text-3xl">{{ $project->title }}</h1>
                        <p class="mt-2 text-sm text-gray-500">
                            Bidang: {{ $project->mentoringField?->field_name ?? 'Belum ditentukan' }}
                            <span class="mx-1">•</span> Periode: {{ $period->period_name ?? 'Belum ditentukan' }}
                            <span class="mx-1">•</span> ID: {{ \Illuminate\Support\Str::limit($project->project_id, 13, '') }}
                        </p>
                    </div>
                    <span class="status-badge {{ $statusClass }} self-start uppercase">{{ $project->status }}</span>
                </div>
            </section>

            @include('peserta.partials.progress-tracker', ['project' => $project, 'activities' => $activities])

            @forelse ($reviews as $review)
                <section class="rounded-xl border border-gray-200 border-l-4 {{ $project->status === 'Revision Required' ? 'border-l-ksm' : 'border-l-blue-500' }} bg-white p-5 shadow-sm md:p-6">
                    <div class="flex flex-col justify-between gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-start">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-sm font-bold text-ksm">{{ strtoupper(substr($review->reviewer_name, 0, 1)) }}</div>
                            <div>
                                <h2 class="font-bold text-gray-900">{{ $review->reviewer_name }}</h2>
                                <p class="text-xs text-gray-500">{{ \Illuminate\Support\Carbon::parse($review->created_at)->format('d F Y, H:i') }} WIB</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $project->status === 'Revision Required' ? 'text-ksm' : 'text-blue-700' }}">
                            {{ $project->status === 'Revision Required' ? 'Evaluasi: perlu revisi' : 'Feedback mentor' }}
                            @if ($review->rating) <span class="ml-1">• Rating {{ $review->rating }}/5</span> @endif
                        </span>
                    </div>
                    <div class="pt-4">
                        <h3 class="mb-2 font-semibold text-gray-900">Catatan Feedback Mentor</h3>
                        <p class="whitespace-pre-line text-sm leading-relaxed text-gray-700">{{ $review->comment }}</p>
                    </div>
                </section>
            @empty
                <section class="rounded-xl border border-blue-100 bg-blue-50 p-5 text-sm text-blue-800">
                    Belum ada feedback mentor untuk project ini. Status pengajuan: <strong>{{ $project->status }}</strong>.
                </section>
            @endforelse

            @if ($project->status === 'Revision Required' && $canResubmit)
                <section class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    Batas revisi dan tenggat waktu akan diinformasikan oleh mentor melalui feedback project ini.
                </section>
            @endif

            <details class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <summary class="cursor-pointer list-none px-5 py-4 text-sm font-semibold text-gray-700"><span class="mr-2">◷</span> Riwayat Pengajuan dan Revisi</summary>
                <div class="space-y-4 border-t border-gray-100 p-5">
                    <div class="flex gap-3">
                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-ksm"></span>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Pengajuan awal • {{ $project->created_at?->format('d M Y, H:i') }}</p>
                            <p class="text-xs text-gray-500">Status saat ini: {{ $project->status }}</p>
                        </div>
                    </div>
                    @foreach ($versions as $version)
                        <div class="flex gap-3">
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-blue-500"></span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-800">Versi {{ $version->version_number }} • {{ \Illuminate\Support\Carbon::parse($version->created_at)->format('d M Y, H:i') }}</p>
                                @if ($version->changelog)<p class="mt-1 whitespace-pre-line text-sm text-gray-600">{{ $version->changelog }}</p>@endif
                                @if ($version->file_path)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($version->file_path) }}" target="_blank" rel="noopener" class="mt-2 inline-flex text-xs font-semibold text-ksm hover:underline">Buka berkas versi ini</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </details>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm md:p-8">
                <h2 class="text-xl font-bold text-gray-900">Informasi Project</h2>
                <p class="mt-1 text-sm text-gray-500">Ringkasan data yang telah dikirim untuk project ini.</p>
                <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Anggota Tim</dt>
                        <dd class="mt-2 space-y-2 text-sm text-gray-700">
                            @forelse ($members as $member)<p><strong>{{ $member->name }}</strong> — {{ $member->member_role }}</p>@empty<p>Belum dicatat</p>@endforelse
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Tools &amp; Teknologi</dt>
                        <dd class="mt-2 flex flex-wrap gap-2">
                            @forelse ($tools as $tool)<span class="tag-pill">{{ $tool }}</span>@empty<span class="text-sm text-gray-500">Belum dicatat</span>@endforelse
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Deskripsi</dt>
                        <dd class="mt-2 whitespace-pre-line text-sm leading-relaxed text-gray-700">{{ $project->description ?: 'Belum ada deskripsi.' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Link Demo / Repository</dt>
                        <dd class="mt-2 text-sm">
                            @if ($project->project_link)
                                <a href="{{ $project->project_link }}" target="_blank" rel="noopener noreferrer" class="break-all font-medium text-ksm hover:underline">{{ $project->project_link }}</a>
                            @else
                                <span class="text-gray-500">Belum tersedia</span>
                            @endif
                        </dd>
                    </div>
                </dl>
                @if ($files->isNotEmpty())
                    <div class="mt-5 border-t border-gray-100 pt-5">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Berkas Project</h3>
                        <ul class="mt-2 space-y-2">
                            @foreach ($files as $file)
                                <li><a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($file->file_path) }}" target="_blank" rel="noopener" class="text-sm font-medium text-ksm hover:underline">{{ $file->file_name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>

            @if ($project->status === 'Revision Required' && $canResubmit)
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm md:p-8">
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-900">Form Pengajuan Ulang Revisi</h2>
                        <p class="mt-1 text-sm text-gray-500">Jelaskan perbaikan yang sudah dilakukan dan perbarui tautan bukti project.</p>
                    </div>
                    <form action="{{ route('dashboard.peserta.project.resubmit', $project->project_id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        <div>
                            <label for="revision_notes" class="form-label">Catatan Perubahan &amp; Penyesuaian <span class="required">*</span></label>
                            <textarea id="revision_notes" name="revision_notes" rows="5" maxlength="5000" class="form-textarea" placeholder="Jelaskan perbaikan yang telah dilakukan berdasarkan feedback mentor..." required>{{ old('revision_notes') }}</textarea>
                            @error('revision_notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="project_link" class="form-label">Tautan Build Demo Terbaru <span class="required">*</span></label>
                            <input id="project_link" type="url" name="project_link" value="{{ old('project_link', $project->project_link) }}" class="form-input" maxlength="255" placeholder="https://contoh.com/demo-terbaru" required>
                            <p class="mt-1 text-xs text-gray-500">Pastikan tautan bisa dibuka tanpa kredensial login.</p>
                            @error('project_link') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="revision_file" class="form-label">Unggah Tangkapan Layar / Patch Note <span class="text-xs font-normal text-gray-500">(opsional)</span></label>
                            <input id="revision_file" type="file" name="revision_file" accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg" class="block w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-700 file:mr-4 file:border-0 file:bg-red-50 file:px-4 file:py-3 file:font-semibold file:text-ksm">
                            <p class="mt-1 text-xs text-gray-500">Format PDF, PNG, atau JPG. Maksimal 10 MB.</p>
                            @error('revision_file') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <label class="flex items-start gap-3 text-xs leading-relaxed text-gray-600">
                            <input type="checkbox" name="integrity" value="1" class="mt-0.5 accent-red-800" required @checked(old('integrity'))>
                            <span>Saya menyatakan bahwa perbaikan telah dilakukan sesuai feedback mentor dan informasi yang dikirim benar.</span>
                        </label>
                        @error('integrity') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        <div class="flex flex-col-reverse justify-end gap-3 border-t border-gray-100 pt-5 sm:flex-row">
                            <a href="{{ route('dashboard.peserta.projects.index') }}" class="btn btn-outline text-sm">Batal / Kembali</a>
                            <button type="submit" class="btn btn-primary text-sm">Kirim Ulang Revisi &rarr;</button>
                        </div>
                    </form>
                </section>
            @endif
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