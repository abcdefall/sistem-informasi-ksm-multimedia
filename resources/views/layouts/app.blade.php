<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'KSM Multimedia')</title>

    <!-- Menggunakan Tailwind CDN sementara untuk layouting -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- MEMANGGIL CSS DARI FOLDER RESOURCES MENGGUNAKAN VITE -->
    @vite(['resources/css/landing.css'])
</head>
<body class="font-sans antialiased text-gray-800" style="background-color: var(--color-body-bg);">

    <!-- Navbar -->
    <nav class="sticky top-0 z-50 border-b border-gray-200 py-4" style="background-color: var(--color-navbar-bg);">
        <div class="mx-auto px-8 lg:px-12 flex items-center justify-between">
            <a href="{{ $dashboardRole ? route('landing') : route('home') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/logo-ksm.png') }}" alt="Logo KSM Multimedia" class="w-8 h-8 object-contain">
                <span class="font-bold text-xl tracking-tight">KSM Multimedia</span>
            </a>
            <div class="hidden md:flex items-center gap-8">
                <a href="{{ $dashboardRole ? route('landing') : route('home') }}" class="nav-link active">Beranda</a>
                <a href="#showcase" class="nav-link">Showcase</a>
                <a href="#mentoring" class="nav-link">Mentoring</a>
            </div>
            <div class="flex items-center gap-3">
                @if($dashboardRole)
                    @if($dashboardRole === 'Peserta')
                        <a href="{{ route('dashboard.peserta') }}" class="btn btn-outline btn-sm">Dashboard Peserta</a>
                    @elseif($dashboardRole === 'Mentor')
                        <a href="{{ route('dashboard.mentor') }}" class="btn btn-outline btn-sm">Dashboard Mentor</a>
                    @elseif($dashboardRole === 'Pengurus KSM')
                        <a href="{{ route('dashboard.pengurus') }}" class="btn btn-outline btn-sm">Dashboard Pengurus</a>
                    @endif
                    <span class="text-sm font-medium text-gray-700">
                        {{ auth()->user()->name }}
                        <span class="text-gray-500">({{ $dashboardRole }})</span>
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
                <a href="{{ $dashboardRole ? route('landing') : route('home') }}" class="text-gray-400 hover:text-white transition">Beranda</a>
                <a href="#showcase" class="text-gray-400 hover:text-white transition">Showcase</a>
                <a href="#mentoring" class="text-gray-400 hover:text-white transition">Mentoring</a>
                <a href="#" class="text-gray-400 hover:text-white transition">Kebijakan Privasi</a>
                <a href="#" class="text-gray-400 hover:text-white transition">Syarat & Ketentuan</a>
            </div>
        </div>
    </footer>

</body>
</html>
