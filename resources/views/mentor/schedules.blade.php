<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Mentoring - KSM Multimedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">
    @include('mentor.partials.topbar')

    <div class="participant-layout">
        <aside class="participant-sidebar">
            <div class="participant-sidebar-shell">
                @include('mentor.partials.sidebar-menu', ['activeMenu' => 'schedules'])
            </div>
        </aside>
        <main class="participant-content mentor-content">
            <header class="mb-5">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Jadwal Mentoring</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">Buat dan kelola sesi mentoring untuk project mahasiswa.</p>
            </header>

            @if(session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <section class="mentor-review-card mb-5">
                <h2 class="mentor-review-section-title">Buat Jadwal Baru</h2>
                @if($projects->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">Belum ada project aktif yang dapat dijadwalkan. Project yang masih draft atau sudah diarsipkan tidak ditampilkan.</p>
                @else
                    <form method="POST" action="{{ route('dashboard.mentor.schedules.store') }}" class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        @csrf
                        <label class="text-sm font-semibold text-slate-700">
                            Project
                            <select name="project_id" required class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                <option value="">Pilih project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->project_id }}" @selected(old('project_id') === $project->project_id)>{{ $project->title }} ({{ $project->project_id }})</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="text-sm font-semibold text-slate-700">
                            Tanggal dan waktu
                            <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required min="{{ now()->format('Y-m-d\TH:i') }}" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                        </label>
                        <label class="text-sm font-semibold text-slate-700">
                            Durasi (menit)
                            <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" min="15" max="240" step="15" required class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                        </label>
                        <label class="text-sm font-semibold text-slate-700">
                            Lokasi (opsional jika ada tautan)
                            <input type="text" name="location" value="{{ old('location') }}" maxlength="255" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm" placeholder="Contoh: Ruang Multimedia">
                        </label>
                        <label class="text-sm font-semibold text-slate-700 md:col-span-2">
                            Tautan pertemuan (opsional jika ada lokasi)
                            <input type="url" name="meeting_url" value="{{ old('meeting_url') }}" maxlength="2048" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm" placeholder="https://...">
                        </label>
                        <label class="text-sm font-semibold text-slate-700 md:col-span-2">
                            Catatan untuk peserta (opsional)
                            <textarea name="notes" rows="3" maxlength="1000" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">{{ old('notes') }}</textarea>
                        </label>
                        <div class="md:col-span-2">
                            <button type="submit" class="mentor-review-button">Simpan Jadwal</button>
                        </div>
                    </form>
                @endif
            </section>

            <section class="space-y-3" aria-label="Daftar jadwal mentoring">
                @forelse($schedules as $schedule)
                    @php($scheduleIsUpcoming = $schedule->status === 'Scheduled' && \Illuminate\Support\Carbon::parse($schedule->starts_at)->isFuture())
                    <article class="mentor-review-card">
                        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                            <div>
                                <p class="text-xs font-semibold text-slate-500">{{ $schedule->project_id }}</p>
                                <h2 class="mt-1 text-lg font-bold text-slate-900">{{ $schedule->project_title }}</h2>
                                <p class="mt-1 text-sm text-slate-600">
                                    {{ \Illuminate\Support\Carbon::parse($schedule->starts_at)->format('d M Y, H:i') }}
                                    &middot; {{ $schedule->duration_minutes }} menit
                                </p>
                            </div>
                            <span class="mentor-status-badge {{ $schedule->status === 'Canceled' ? 'mentor-status-resubmitted' : ($scheduleIsUpcoming ? 'mentor-status-submitted' : 'mentor-status-in-review') }}">
                                {{ $schedule->status === 'Canceled' ? 'Dibatalkan' : ($scheduleIsUpcoming ? 'Akan Datang' : 'Selesai') }}
                            </span>
                        </div>
                        @if($schedule->location)
                            <p class="mt-3 text-sm text-slate-600">Lokasi: {{ $schedule->location }}</p>
                        @endif
                        @if($schedule->meeting_url)
                            <p class="mt-2 text-sm"><a class="font-semibold text-ksm underline" href="{{ $schedule->meeting_url }}" target="_blank" rel="noopener noreferrer">Buka tautan pertemuan</a></p>
                        @endif
                        @if($schedule->notes)
                            <p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $schedule->notes }}</p>
                        @endif

                        @if($scheduleIsUpcoming)
                            <details class="mt-4 border-t border-slate-100 pt-3">
                                <summary class="cursor-pointer text-sm font-semibold text-ksm">Ubah jadwal</summary>
                                <form method="POST" action="{{ route('dashboard.mentor.schedules.update', $schedule->schedule_id) }}" class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2">
                                    @csrf
                                    @method('PUT')
                                    <label class="text-xs font-semibold text-slate-700">
                                        Tanggal dan waktu
                                        <input type="datetime-local" name="starts_at" value="{{ \Illuminate\Support\Carbon::parse($schedule->starts_at)->format('Y-m-d\TH:i') }}" required min="{{ now()->format('Y-m-d\TH:i') }}" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                    </label>
                                    <label class="text-xs font-semibold text-slate-700">
                                        Durasi (menit)
                                        <input type="number" name="duration_minutes" value="{{ $schedule->duration_minutes }}" min="15" max="240" step="15" required class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                    </label>
                                    <label class="text-xs font-semibold text-slate-700">
                                        Lokasi
                                        <input type="text" name="location" value="{{ $schedule->location }}" maxlength="255" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                    </label>
                                    <label class="text-xs font-semibold text-slate-700">
                                        Tautan pertemuan
                                        <input type="url" name="meeting_url" value="{{ $schedule->meeting_url }}" maxlength="2048" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                    </label>
                                    <label class="text-xs font-semibold text-slate-700 md:col-span-2">
                                        Catatan
                                        <textarea name="notes" rows="2" maxlength="1000" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">{{ $schedule->notes }}</textarea>
                                    </label>
                                    <div class="flex flex-wrap gap-2 md:col-span-2">
                                        <button type="submit" class="mentor-review-button">Simpan Perubahan</button>
                                        <button type="submit" form="cancel-schedule-{{ $schedule->schedule_id }}" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">Batalkan Jadwal</button>
                                    </div>
                                </form>
                                <form id="cancel-schedule-{{ $schedule->schedule_id }}" method="POST" action="{{ route('dashboard.mentor.schedules.cancel', $schedule->schedule_id) }}" class="hidden">
                                    @csrf
                                    @method('PATCH')
                                </form>
                            </details>
                        @endif
                    </article>
                @empty
                    <div class="mentor-review-card py-12 text-center">
                        <h2 class="font-bold text-slate-800">Belum ada jadwal mentoring</h2>
                        <p class="mt-1 text-sm text-slate-500">Jadwal yang Anda buat akan muncul di sini.</p>
                    </div>
                @endforelse
            </section>
            @if($schedules->hasPages())
                <div class="mt-5">{{ $schedules->links() }}</div>
            @endif
        </main>
    </div>

    <footer class="mt-auto border-t border-gray-700 py-8 bg-gray-900">
        <div class="mx-auto px-8 lg:px-12 text-sm text-gray-400">
            <span class="font-bold text-white">KSM Multimedia</span>
            <span class="ml-2">&copy; {{ date('Y') }} KSM Multimedia. Hak Cipta Dilindungi Undang-Undang.</span>
        </div>
    </footer>
</body>
</html>
