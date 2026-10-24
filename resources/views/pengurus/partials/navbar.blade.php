<nav class="border-b border-gray-200 py-4 sticky top-0 z-40" style="background-color: var(--color-navbar-bg);">
    <div class="mx-auto px-8 lg:px-12 flex items-center justify-between">
        <a href="{{ route('landing') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/logo-ksm.png') }}" alt="Logo KSM Multimedia" class="w-8 h-8 object-contain">
            <span class="font-bold text-xl tracking-tight text-ksm">KSM Multimedia</span>
            <span class="bg-red-50 text-ksm text-xs font-bold px-2 py-1 rounded border border-red-100 ml-2">PORTAL PENGURUS</span>
        </a>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-3 text-right">
                <div class="w-10 h-10 rounded-full bg-ksm text-white flex items-center justify-center font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="hidden md:block">
                    <p class="text-sm font-bold text-gray-800">Halo, {{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500">Pengurus KSM</p>
                </div>
            </div>
            <div class="h-8 w-px bg-gray-200 mx-2"></div>
            <a href="{{ route('landing') }}" class="text-sm font-medium text-gray-600 hover:text-ksm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Kembali ke Beranda
            </a>
        </div>
    </div>
</nav>
