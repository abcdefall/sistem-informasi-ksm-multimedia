<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'KSM Multimedia')</title>

    <!-- Menggunakan Tailwind CDN sementara untuk layouting -->
    <script src="https://cdn.tailwindcss.com"></script>

    @if(request()->routeIs('mentoring'))
        @vite(['resources/css/mentoring.css'])
    @elseif(request()->routeIs('showcase', 'showcase.show'))
        @vite(['resources/css/showcase.css'])
    @elseif(request()->routeIs('privacy'))
        @vite(['resources/css/landing.css', 'resources/css/privacy.css'])
    @elseif(request()->routeIs('terms'))
        @vite(['resources/css/landing.css', 'resources/css/terms.css'])
    @else
        @vite(['resources/css/landing.css'])
    @endif
</head>
<body class="font-sans antialiased text-gray-800" style="background-color: var(--color-body-bg);">
    @php($authenticatedRole = auth()->user()?->role?->role_name)

    <!-- Navbar -->
    <nav class="sticky top-0 z-50 border-b border-gray-200 py-4" style="background-color: var(--color-navbar-bg);">
        <div class="mx-auto px-8 lg:px-12 flex items-center justify-between">
            <a href="{{ $authenticatedRole ? route('landing') : route('home') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/logo-ksm.png') }}" alt="Logo KSM Multimedia" class="w-8 h-8 object-contain">
                <span class="font-bold text-xl tracking-tight">KSM Multimedia</span>
            </a>
            <div class="hidden md:flex items-center gap-8">
                <a href="{{ $authenticatedRole ? route('landing') : route('home') }}" class="nav-link {{ request()->routeIs('home', 'landing') ? 'active' : '' }}">Beranda</a>
                <a href="{{ route('showcase') }}" class="nav-link {{ request()->routeIs('showcase', 'showcase.show') ? 'active' : '' }}">Showcase</a>
                <a href="{{ route('mentoring') }}" class="nav-link {{ request()->routeIs('mentoring') ? 'active' : '' }}">Mentoring</a>
            </div>
            <div class="flex items-center gap-3">
                @if(auth()->check())
                    @if($authenticatedRole === 'Peserta')
                        <a href="{{ route('dashboard.peserta') }}" class="btn btn-outline btn-sm">Dashboard Peserta</a>
                    @elseif($authenticatedRole === 'Mentor')
                        <a href="{{ route('dashboard.mentor') }}" class="btn btn-outline btn-sm">Dashboard Mentor</a>
                    @elseif($authenticatedRole === 'Pengurus KSM')
                        <a href="{{ route('dashboard.pengurus') }}" class="btn btn-outline btn-sm">Dashboard Pengurus</a>
                    @endif
                    <span class="text-sm font-medium text-gray-700">
                        {{ auth()->user()->name }}
                        <span class="text-gray-500">({{ $authenticatedRole }})</span>
                    </span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Masuk</a>
                @endif
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    @yield('content')

    <!-- Footer -->
    <footer class="border-t border-gray-700 py-8 bg-gray-900">
        <div class="mx-auto px-8 lg:px-12 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-gray-400">
            <div class="flex items-center gap-2 font-bold text-white">
                KSM Multimedia
                <span class="font-normal text-gray-400 ml-2">&copy; {{ date('Y') }} KSM Multimedia. Hak Cipta Dilindungi Undang-Undang.</span>
            </div>
            <div class="flex gap-6 font-medium">
                <a href="{{ $authenticatedRole ? route('landing') : route('home') }}" class="text-gray-400 hover:text-white transition">Beranda</a>
                <a href="{{ route('showcase') }}" class="text-gray-400 hover:text-white transition">Showcase</a>
                <a href="{{ route('mentoring') }}" class="text-gray-400 hover:text-white transition">Mentoring</a>
                <a href="{{ route('privacy') }}" class="text-gray-400 hover:text-white transition">Kebijakan Privasi</a>
                <a href="{{ route('terms') }}" class="text-gray-400 hover:text-white transition">Syarat & Ketentuan</a>
            </div>
        </div>
    </footer>

</body>
</html>
