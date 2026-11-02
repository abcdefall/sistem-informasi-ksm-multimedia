<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - KSM Multimedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">
    @php($roleName = auth()->user()->role?->role_name)

    @if($roleName === 'Mentor')
        @include('mentor.partials.topbar')
    @else
        @include('peserta.partials.topbar')
    @endif

    <div class="participant-layout">
        <aside class="participant-sidebar">
            <div class="participant-sidebar-shell">
                @if($roleName === 'Mentor')
                    @include('mentor.partials.sidebar-menu', [
                        'activeMenu' => 'profile',
                        'waitingReviewCount' => $waitingReviewCount,
                        'revisionIncomingCount' => $revisionIncomingCount,
                    ])
                @else
                    @include('peserta.partials.sidebar-menu', ['activeMenu' => 'profile'])
                @endif
            </div>
        </aside>

        <main class="participant-content mentor-content">
            <header class="mb-5">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Profil Saya</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">Kelola nama tampilan dan bio singkat Anda. Email dan akses akun dikelola oleh pengurus KSM.</p>
            </header>

            @if(session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <section class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="profile-form-title">
                <div class="mb-6 border-b border-slate-100 pb-5">
                    <h2 id="profile-form-title" class="text-lg font-bold text-slate-900">Informasi dasar</h2>
                    <p class="mt-1 text-sm text-slate-500">Informasi ini membantu tim dan mentor mengenal Anda.</p>
                </div>

                <form method="POST" action="{{ route('dashboard.profile.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <label class="block text-sm font-semibold text-slate-700" for="name">
                        Nama
                        <input id="name" name="name" type="text" value="{{ old('name', auth()->user()->name) }}" required maxlength="255" autocomplete="name" class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-normal text-slate-800 focus:border-ksm focus:outline-none focus:ring-2 focus:ring-red-100">
                    </label>

                    <label class="block text-sm font-semibold text-slate-700" for="bio">
                        Bio singkat <span class="font-normal text-slate-400">(opsional)</span>
                        <textarea id="bio" name="bio" rows="4" maxlength="500" placeholder="Ceritakan minat atau bidang yang sedang Anda tekuni..." class="mt-1.5 block w-full resize-y rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-normal text-slate-800 focus:border-ksm focus:outline-none focus:ring-2 focus:ring-red-100">{{ old('bio', auth()->user()->profile_data['bio'] ?? '') }}</textarea>
                        <span class="mt-1 block text-xs font-normal text-slate-400">Maksimal 500 karakter.</span>
                    </label>

                    <div class="grid gap-4 border-t border-slate-100 pt-5 sm:grid-cols-2">
                        <div>
                            <span class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Email akun</span>
                            <p class="mt-1 break-all text-sm text-slate-700">{{ auth()->user()->email }}</p>
                            <p class="mt-1 text-xs text-slate-400">Perubahan email dibantu pengurus KSM.</p>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Peran</span>
                            <p class="mt-1 text-sm text-slate-700">{{ $roleName }}</p>
                            <p class="mt-1 text-xs text-slate-400">Peran akun dikelola pengurus KSM.</p>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-slate-100 pt-5">
                        <button type="submit" class="rounded-lg bg-ksm px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#70102c] focus:outline-none focus:ring-2 focus:ring-red-200 focus:ring-offset-2">Simpan Profil</button>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
