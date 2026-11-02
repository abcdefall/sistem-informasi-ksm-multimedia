<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Evaluasi - KSM Multimedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">
    @include('mentor.partials.topbar')

    <div class="participant-layout">
        <aside class="participant-sidebar">
            <div class="participant-sidebar-shell">
                @include('mentor.partials.sidebar-menu', ['activeMenu' => 'evaluations'])
            </div>
        </aside>
        <main class="participant-content mentor-content">
            <header class="mb-5">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Riwayat Evaluasi</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">Daftar evaluasi project yang pernah Anda kirimkan.</p>
            </header>

            <section class="space-y-3" aria-label="Daftar riwayat evaluasi">
                @forelse($evaluations as $evaluation)
                    <article class="mentor-review-card">
                        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                            <div>
                                <p class="text-xs font-semibold text-slate-500">{{ $evaluation->project_id }}@if($evaluation->field_name) &middot; {{ $evaluation->field_name }} @endif</p>
                                <h2 class="mt-1 text-lg font-bold text-slate-900">{{ $evaluation->project_title }}</h2>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="mentor-field-badge">Nilai {{ $evaluation->rating ?? '—' }}/5</span>
                                <time class="text-xs text-slate-500" datetime="{{ $evaluation->created_at }}">{{ \Illuminate\Support\Carbon::parse($evaluation->created_at)->format('d M Y, H:i') }}</time>
                            </div>
                        </div>
                        <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-600">{{ $evaluation->comment }}</p>
                    </article>
                @empty
                    <div class="mentor-review-card py-12 text-center">
                        <h2 class="font-bold text-slate-800">Belum ada evaluasi</h2>
                        <p class="mt-1 text-sm text-slate-500">Evaluasi yang sudah dikirim akan tercatat di halaman ini.</p>
                        <a href="{{ route('dashboard.mentor') }}" class="mentor-review-button mt-4">Buka Antrian Review</a>
                    </div>
                @endforelse
            </section>

            @if($evaluations->hasPages())
                <div class="mt-5">{{ $evaluations->links() }}</div>
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
