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
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <section class="mentor-review-card">
                <h2 class="mentor-review-section-title">Periode Mentoring Aktif</h2>
                <p class="mt-3 text-sm leading-relaxed text-slate-600">Periode aktif akan digunakan sebagai pilihan periode saat peserta membuat atau mengajukan project baru. Project yang sudah tersimpan tidak dipindahkan.</p>

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
                                            @endif
                                        </span>
                                        <span class="mt-1 block text-xs text-slate-500">
                                            {{ \Illuminate\Support\Carbon::parse($period->start_date)->format('d M Y') }}
                                            &ndash;
                                            {{ \Illuminate\Support\Carbon::parse($period->end_date)->format('d M Y') }}
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </fieldset>
                        <button type="submit" class="pengurus-approval-button mt-5">Simpan Periode Aktif</button>
                    </form>
                @endif
            </section>
        </main>
    </div>

    @include('pengurus.partials.footer')
</body>
</html>
