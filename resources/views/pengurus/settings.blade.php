<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - KSM Multimedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">
    @include('pengurus.partials.navbar')

    <div class="participant-layout pengurus-layout">
        @include('pengurus.partials.sidebar')

        <main class="participant-content mentor-content pengurus-content">
            <header class="mb-5">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Pengaturan</h1>
                <p class="mt-1 max-w-3xl text-sm text-slate-500">Pengaturan dasar untuk menentukan periode mentoring yang digunakan sistem.</p>
            </header>

            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
            @endif

            <section class="mentor-review-card">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                    <div>
                        <h2 class="mentor-review-section-title">Periode Mentoring Aktif</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">Periode aktif akan digunakan sebagai pilihan periode saat peserta membuat atau mengajukan project baru. Project yang sudah tersimpan tidak dipindahkan.</p>
                    </div>
                    <details class="w-full sm:w-auto" @if($errors->getBag('periodCreate')->any()) open @endif>
                        <summary class="pengurus-approval-button cursor-pointer list-none text-center">Tambah Periode Baru</summary>
                        <form method="POST" action="{{ route('dashboard.pengurus.settings.periods.store') }}" class="mt-4 w-full rounded-xl border border-slate-200 bg-slate-50 p-4 sm:min-w-[22rem]">
                            @csrf
                            <h3 class="text-sm font-bold text-slate-800">Data Periode Baru</h3>
                            @if($errors->getBag('periodCreate')->any())
                                <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">
                                    @foreach($errors->getBag('periodCreate')->all() as $error)<p>{{ $error }}</p>@endforeach
                                </div>
                            @endif
                            <label for="new-period-name" class="mt-4 block text-sm font-medium text-slate-700">Nama periode</label>
                            <input id="new-period-name" name="period_name" value="{{ old('period_name') }}" maxlength="255" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Contoh: Semester Ganjil 2027/2028">
                            <label for="new-period-start" class="mt-3 block text-sm font-medium text-slate-700">Tanggal mulai</label>
                            <input id="new-period-start" type="date" name="start_date" value="{{ old('start_date') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <label for="new-period-end" class="mt-3 block text-sm font-medium text-slate-700">Tanggal selesai</label>
                            <input id="new-period-end" type="date" name="end_date" value="{{ old('end_date') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <button type="submit" class="pengurus-approval-button mt-4 w-full">Simpan Periode</button>
                        </form>
                    </details>
                </div>

                @error('period_id')
                    <p class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">{{ $message }}</p>
                @enderror

                @if($periods->isEmpty())
                    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Belum ada data periode mentoring. Tambahkan periode terlebih dahulu sebelum mengatur periode aktif.
                    </div>
                @else
                    <form method="POST" action="{{ route('dashboard.pengurus.settings.period') }}" class="mt-5">
                        @csrf
                        @method('PUT')
                        <fieldset class="space-y-3">
                            <legend class="mb-3 text-sm font-semibold text-slate-700">Pilih satu periode untuk diaktifkan</legend>
                            @foreach($periods as $period)
                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 hover:border-rose-200 hover:bg-rose-50/30">
                                    <input type="radio" name="period_id" value="{{ $period->period_id }}" @checked($period->is_active) required class="mt-1 accent-rose-800">
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center gap-2 font-semibold text-slate-800">
                                            {{ $period->period_name }}
                                            @if($period->is_active)
                                                <span class="mentor-status-badge mentor-status-submitted">Aktif saat ini</span>
                                            @else
                                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">Tidak aktif</span>
                                            @endif
                                        </span>
                                        <span class="mt-1 block text-xs text-slate-500">
                                            {{ \Illuminate\Support\Carbon::parse($period->start_date)->format('d M Y') }}
                                            &ndash;
                                            {{ \Illuminate\Support\Carbon::parse($period->end_date)->format('d M Y') }}
                                        </span>
                                        <span class="mt-1 block text-xs text-slate-400">ID: {{ $period->period_id }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </fieldset>
                        <button type="submit" class="pengurus-approval-button mt-5" onclick="return confirm('Aktifkan periode yang dipilih? Periode aktif sebelumnya akan dinonaktifkan.')">Simpan Periode Aktif</button>
                    </form>
                @endif

                @if($periods->isNotEmpty())
                    <section class="mt-8 border-t border-slate-200 pt-6" aria-labelledby="edit-periods-heading">
                        <h3 id="edit-periods-heading" class="text-base font-bold text-slate-800">Kelola Detail Periode</h3>
                        <p class="mt-1 text-sm text-slate-500">Perubahan nama dan tanggal akan tercermin pada project yang menggunakan periode tersebut.</p>
                        <div class="mt-4 space-y-3">
                            @foreach($periods as $period)
                                @php($periodEditErrors = $errors->getBag('periodEdit_'.$period->period_id))
                                <details id="edit-{{ $period->period_id }}" class="rounded-lg border border-slate-200 bg-white p-4" @if($periodEditErrors->any()) open @endif>
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3">
                                        <span class="min-w-0">
                                            <span class="block truncate font-semibold text-slate-800">{{ $period->period_name }}</span>
                                            <span class="mt-1 block text-xs text-slate-500">
                                                {{ \Illuminate\Support\Carbon::parse($period->start_date)->format('d M Y') }}
                                                &ndash;
                                                {{ \Illuminate\Support\Carbon::parse($period->end_date)->format('d M Y') }}
                                            </span>
                                        </span>
                                        <span class="shrink-0 rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold text-slate-700">Edit</span>
                                    </summary>
                                    <form method="POST" action="{{ route('dashboard.pengurus.settings.periods.update', $period->period_id) }}" class="mt-4 border-t border-slate-100 pt-4">
                                        @csrf
                                        @method('PUT')
                                        @if($periodEditErrors->any())
                                            <div class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">
                                                @foreach($periodEditErrors->all() as $error)<p>{{ $error }}</p>@endforeach
                                            </div>
                                        @endif
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                            <div>
                                                <label for="period-name-{{ $period->period_id }}" class="block text-sm font-medium text-slate-700">Nama periode</label>
                                                <input id="period-name-{{ $period->period_id }}" name="period_name" value="{{ $periodEditErrors->any() ? old('period_name', $period->period_name) : $period->period_name }}" maxlength="255" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label for="period-start-{{ $period->period_id }}" class="block text-sm font-medium text-slate-700">Tanggal mulai</label>
                                                <input id="period-start-{{ $period->period_id }}" type="date" name="start_date" value="{{ $periodEditErrors->any() ? old('start_date', $period->start_date) : $period->start_date }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label for="period-end-{{ $period->period_id }}" class="block text-sm font-medium text-slate-700">Tanggal selesai</label>
                                                <input id="period-end-{{ $period->period_id }}" type="date" name="end_date" value="{{ $periodEditErrors->any() ? old('end_date', $period->end_date) : $period->end_date }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                            </div>
                                        </div>
                                        <button type="submit" class="pengurus-approval-button mt-4">Simpan Perubahan</button>
                                    </form>
                                </details>
                            @endforeach
                        </div>
                    </section>
                @endif
            </section>
        </main>
    </div>

    @include('pengurus.partials.footer')
</body>
</html>
