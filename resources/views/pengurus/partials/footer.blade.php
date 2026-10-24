<footer class="mt-auto border-t border-gray-700 py-8 bg-gray-900">
    <div class="mx-auto px-8 lg:px-12 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-gray-400">
        <div class="flex items-center gap-2 font-bold text-white">
            KSM Multimedia
            <span class="font-normal text-gray-400 ml-2">&copy; {{ date('Y') }} KSM Multimedia. Hak Cipta Dilindungi Undang-Undang.</span>
        </div>
        <div class="flex gap-6 font-medium">
            <a href="{{ route('landing') }}" class="text-gray-400 hover:text-white transition">Beranda</a>
            <a href="{{ route('landing') }}#showcase" class="text-gray-400 hover:text-white transition">Showcase</a>
            <a href="{{ route('landing') }}#mentoring" class="text-gray-400 hover:text-white transition">Mentoring</a>
            <a href="#" class="text-gray-400 hover:text-white transition">Kebijakan Privasi</a>
            <a href="#" class="text-gray-400 hover:text-white transition">Syarat &amp; Ketentuan</a>
        </div>
    </div>
</footer>
