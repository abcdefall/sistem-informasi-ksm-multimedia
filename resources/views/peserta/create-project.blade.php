<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Project - KSM Multimedia</title>
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
                    <a href="{{ route('dashboard.peserta.project.create') }}" class="sidebar-link active" aria-current="page">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Ajukan Project Baru
                    </a>
                    <a href="{{ route('dashboard.peserta.projects.index') }}" class="sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        Project
                    </a>
                    <a href="#" class="sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        Jadwal Mentoring
                    </a>
                    <a href="#" class="sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a2 2 0 01.293.707V19a2 2 0 01-2-2z"></path></svg>
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

        <main class="participant-content">
            <div class="rounded-xl border border-red-100 bg-white p-6 shadow-sm md:p-8">
                <div class="mb-7 flex flex-col justify-between gap-4 border-b border-red-50 pb-6 md:flex-row md:items-start">
                    <div>
                        <p class="section-label">Portal Peserta</p>
                        <h1 class="text-2xl font-extrabold text-gray-900 md:text-3xl">Form Pengajuan Capstone Project</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-relaxed text-gray-500">Isi formulir dengan data project yang akurat. Pengajuan akan melalui tahapan kurasi mentor sebelum dipertimbangkan untuk showcase publik.</p>
                    </div>
                    <span class="inline-flex shrink-0 items-center gap-2 self-start rounded-full border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-bold text-ksm">
                        <span class="h-2 w-2 rounded-full bg-ksm"></span>
                        ID dibuat setelah disimpan
                    </span>
                </div>

                <div class="submission-progress mb-8" aria-label="Tahapan pengajuan project">
                    <div class="submission-step active"><span class="submission-step-bar"></span><span class="submission-step-label"><span class="submission-step-number">1</span>Informasi Utama</span></div>
                    <div class="submission-step"><span class="submission-step-bar"></span><span class="submission-step-label"><span class="submission-step-number">2</span>Berkas &amp; Aset</span></div>
                    <div class="submission-step"><span class="submission-step-bar"></span><span class="submission-step-label"><span class="submission-step-number">3</span>Review &amp; Kirim</span></div>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                        <p class="font-semibold">Periksa kembali data pengajuan:</p>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($mentoringFields->isEmpty() || $mentoringPeriods->isEmpty())
                    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800" role="status">
                        Pengajuan belum dapat dilakukan karena bidang mentoring atau periode aktif belum tersedia. Silakan hubungi pengurus.
                    </div>
                @endif

                <form action="{{ route('dashboard.peserta.project.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    <section>
                        <h2 class="form-section-title"><span class="text-ksm">01.</span> Detail Identitas Project</h2>
                        <div class="space-y-5">
                            <div>
                                <label for="title" class="form-label">Judul Project <span class="required">*</span></label>
                                <input id="title" type="text" name="title" value="{{ old('title') }}" class="form-input" maxlength="255" placeholder="Contoh: Interactive WebGL Physics Simulation" required>
                                @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label for="field_id" class="form-label">Bidang Mentoring <span class="required">*</span></label>
                                    <select id="field_id" name="field_id" class="form-select" required>
                                        <option value="">Pilih bidang mentoring</option>
                                        @foreach ($mentoringFields as $field)
                                            <option value="{{ $field->field_id }}" @selected(old('field_id') === $field->field_id)>{{ $field->field_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('field_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="period_id" class="form-label">Periode Akademik <span class="required">*</span></label>
                                    <select id="period_id" name="period_id" class="form-select" required>
                                        <option value="">Pilih periode aktif</option>
                                        @foreach ($mentoringPeriods as $period)
                                            <option value="{{ $period->period_id }}" @selected(old('period_id') === $period->period_id)>{{ $period->period_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('period_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h2 class="form-section-title"><span class="text-ksm">02.</span> Tim &amp; Teknologi</h2>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <div class="mb-2 flex items-center justify-between gap-3">
                                    <label class="form-label mb-0">Anggota Tim <span class="required">*</span></label>
                                    @if ($participants->isNotEmpty())
                                        <button type="button" id="add-member" class="text-xs font-bold text-ksm hover:underline">+ Tambah anggota</button>
                                    @endif
                                </div>
                                <p class="mb-3 text-xs text-gray-500">Wajib pilih minimal satu akun Peserta saat mengirim. Pilih diri sendiri jika Anda juga anggota tim, lalu isi peran setiap anggota. Draft boleh disimpan tanpa anggota.</p>
                                <div id="member-list" class="space-y-3">
                                    @foreach (old('members', []) as $index => $member)
                                        <div class="member-row rounded-lg border border-gray-200 p-3">
                                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                <select name="members[{{ $index }}][user_id]" class="form-select" aria-label="Anggota project" required>
                                                    <option value="">Pilih peserta</option>
                                                    @foreach ($participants as $participant)
                                                        <option value="{{ $participant->user_id }}" @selected(($member['user_id'] ?? '') === $participant->user_id)>{{ $participant->name }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="flex gap-2">
                                                    <input type="text" name="members[{{ $index }}][member_role]" value="{{ $member['member_role'] ?? '' }}" class="form-input" maxlength="100" placeholder="Peran, mis. UI Designer" required>
                                                    <button type="button" class="remove-row rounded-md px-3 text-gray-500 hover:bg-red-50 hover:text-red-700" aria-label="Hapus anggota">&times;</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @if ($participants->isEmpty())
                                    <p class="rounded-lg bg-gray-50 p-3 text-xs text-gray-500">Belum ada akun Peserta yang dapat dipilih sebagai anggota tim.</p>
                                @endif
                                @error('members') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                @error('members.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                @error('members.*.user_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                @error('members.*.member_role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <div class="mb-2 flex items-center justify-between gap-3">
                                    <label class="form-label mb-0">Tools &amp; Stack Teknologi <span class="required">*</span></label>
                                    <button type="button" id="add-tool" class="text-xs font-bold text-ksm hover:underline">+ Tambah tools</button>
                                </div>
                                <p class="mb-3 text-xs text-gray-500">Tambahkan minimal satu teknologi saat mengirim. Draft boleh disimpan tanpa tools.</p>
                                <div id="tool-list" class="space-y-3">
                                    @foreach (old('tools', []) as $index => $tool)
                                        <div class="tool-row flex gap-2">
                                            <input type="text" name="tools[{{ $index }}]" value="{{ $tool }}" class="form-input" maxlength="255" placeholder="Contoh: Laravel, Figma" required>
                                            <button type="button" class="remove-row rounded-md px-3 text-gray-500 hover:bg-red-50 hover:text-red-700" aria-label="Hapus tools">&times;</button>
                                        </div>
                                    @endforeach
                                </div>
                                @error('tools') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                @error('tools.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <section>
                        <h2 class="form-section-title"><span class="text-ksm">03.</span> Deskripsi &amp; Ringkasan Project</h2>
                        <div>
                            <div class="mb-2 flex items-end justify-between gap-3">
                                <label for="description" class="form-label mb-0">Deskripsi Karya <span class="required">*</span></label>
                                <span id="description-word-count" class="text-xs font-semibold text-gray-400">0 / 150 kata</span>
                            </div>
                            <textarea id="description" name="description" rows="6" maxlength="5000" class="form-textarea" placeholder="Jelaskan latar belakang, tujuan, inovasi, dan fitur utama karya..." required>{{ old('description') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Minimal 150 kata untuk dikirim, maksimal 5.000 karakter. Draft dapat disimpan sebelum lengkap.</p>
                            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </section>

                    <section>
                        <h2 class="form-section-title"><span class="text-ksm">04.</span> Berkas &amp; Media Display</h2>
                        <div class="space-y-5">
                            <div>
                                <label for="thumbnail" class="form-label">Media Cover / Thumbnail <span class="required">*</span></label>
                                <label for="thumbnail" class="file-drop-zone block">
                                    <span class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white text-ksm shadow-sm">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    </span>
                                    <span class="block text-sm font-semibold text-gray-700">Tarik dan lepas gambar di sini, atau <span class="text-ksm underline">pilih berkas</span></span>
                                    <span class="mt-2 block text-xs text-gray-500">JPG, PNG, atau WEBP. Maksimal 5 MB.</span>
                                    <span id="thumbnail-name" class="mt-2 block text-xs font-semibold text-ksm">{{ old('thumbnail') ? 'Pilih kembali file thumbnail setelah validasi.' : '' }}</span>
                                    <input id="thumbnail" type="file" name="thumbnail" class="sr-only" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" @required(!old('submission_action') || old('submission_action') === 'Submitted')>
                                </label>
                                @error('thumbnail') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="project_link" class="form-label">Link Demo atau Repository <span class="required">*</span></label>
                                <input id="project_link" type="url" name="project_link" value="{{ old('project_link') }}" class="form-input" maxlength="255" placeholder="https://contoh.com/project" @required(!old('submission_action') || old('submission_action') === 'Submitted')>
                                <p class="mt-1 text-xs text-gray-500">Wajib saat mengirim sebagai bukti project. Draft boleh disimpan tanpa link.</p>
                                @error('project_link') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <div class="flex items-start gap-3 rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-800">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        <div>
                            <h2 class="text-sm font-bold">Pernyataan Integritas Akademik &amp; Publikasi</h2>
                            <p class="mt-1 text-xs leading-relaxed">Dengan mengajukan project ini, Anda menyatakan bahwa karya merupakan hasil orisinalitas tim dan menyetujui penayangan pada Showcase KSM Multimedia setelah melalui persetujuan kurator.</p>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse justify-between gap-3 border-t border-gray-100 pt-6 sm:flex-row">
                        <button type="submit" name="submission_action" value="Draft" formnovalidate class="btn btn-outline w-full text-sm sm:w-auto">Simpan sebagai Draft</button>
                        <button type="submit" name="submission_action" value="Submitted" class="btn btn-primary w-full text-sm sm:w-auto" @disabled($mentoringFields->isEmpty() || $mentoringPeriods->isEmpty() || $participants->isEmpty())>
                            Kirim Submission Capstone &rarr;
                        </button>
                    </div>
                </form>

                <template id="member-template">
                    <div class="member-row rounded-lg border border-gray-200 p-3">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <select name="members[__INDEX__][user_id]" class="form-select" aria-label="Anggota project" required>
                                <option value="">Pilih peserta</option>
                                @foreach ($participants as $participant)
                                    <option value="{{ $participant->user_id }}">{{ $participant->name }}</option>
                                @endforeach
                            </select>
                            <div class="flex gap-2">
                                <input type="text" name="members[__INDEX__][member_role]" class="form-input" maxlength="100" placeholder="Peran, mis. UI Designer" required>
                                <button type="button" class="remove-row rounded-md px-3 text-gray-500 hover:bg-red-50 hover:text-red-700" aria-label="Hapus anggota">&times;</button>
                            </div>
                        </div>
                    </div>
                </template>
                <template id="tool-template">
                    <div class="tool-row flex gap-2">
                        <input type="text" name="tools[__INDEX__]" class="form-input" maxlength="255" placeholder="Contoh: Laravel, Figma" required>
                        <button type="button" class="remove-row rounded-md px-3 text-gray-500 hover:bg-red-50 hover:text-red-700" aria-label="Hapus tools">&times;</button>
                    </div>
                </template>
            </div>
        </main>
    </div>

    <footer class="mt-auto border-t border-gray-700 bg-gray-900 py-8">
        <div class="mx-auto px-8 lg:px-12 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-gray-400">
            <div class="flex items-center gap-2 font-bold text-white">
                KSM Multimedia
                <span class="font-normal text-gray-400 ml-2">&copy; {{ date('Y') }} KSM Multimedia. Hak Cipta Dilindungi Undang-Undang.</span>
            </div>
            <div class="flex gap-6 font-medium">
                <a href="{{ route('landing') }}" class="text-gray-400 hover:text-white transition">Beranda</a>
                <a href="{{ route('landing') }}#showcase" class="text-gray-400 hover:text-white transition">Showcase</a>
                <a href="{{ route('landing') }}#mentoring" class="text-gray-400 hover:text-white transition">Mentoring</a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const memberList = document.getElementById('member-list');
            const toolList = document.getElementById('tool-list');
            const addMember = document.getElementById('add-member');
            const addTool = document.getElementById('add-tool');
            const thumbnail = document.getElementById('thumbnail');
            const description = document.getElementById('description');
            const wordCount = document.getElementById('description-word-count');
            let memberIndex = {{ count(old('members', [])) }};
            let toolIndex = {{ count(old('tools', [])) }};

            const updateWordCount = () => {
                const words = description.value.trim().match(/\S+/g) ?? [];
                wordCount.textContent = `${words.length} / 150 kata`;
                wordCount.classList.toggle('text-ksm', words.length >= 150);
            };

            description?.addEventListener('input', updateWordCount);
            updateWordCount();

            addMember?.addEventListener('click', () => {
                const html = document.getElementById('member-template').innerHTML.replace(/__INDEX__/g, memberIndex++);
                memberList.insertAdjacentHTML('beforeend', html);
            });

            addTool?.addEventListener('click', () => {
                const html = document.getElementById('tool-template').innerHTML.replace(/__INDEX__/g, toolIndex++);
                toolList.insertAdjacentHTML('beforeend', html);
                toolList.lastElementChild.querySelector('input').focus();
            });

            document.addEventListener('click', (event) => {
                const removeButton = event.target.closest('.remove-row');
                if (removeButton) {
                    removeButton.closest('.member-row, .tool-row').remove();
                }
            });

            thumbnail?.addEventListener('change', () => {
                document.getElementById('thumbnail-name').textContent = thumbnail.files[0]?.name ?? '';
            });

            const dropZone = thumbnail?.closest('.file-drop-zone');
            dropZone?.addEventListener('dragover', (event) => {
                event.preventDefault();
                dropZone.classList.add('is-dragging');
            });
            dropZone?.addEventListener('dragleave', () => dropZone.classList.remove('is-dragging'));
            dropZone?.addEventListener('drop', (event) => {
                event.preventDefault();
                dropZone.classList.remove('is-dragging');
                if (event.dataTransfer.files.length) {
                    thumbnail.files = event.dataTransfer.files;
                    thumbnail.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
    </script>
</body>
</html>
