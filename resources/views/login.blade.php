<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Akun - KSM Multimedia</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Vite CSS Call (Landing & Auth) -->
    @vite(['resources/css/landing.css', 'resources/css/auth.css'])
</head>
<body class="font-sans antialiased text-gray-800 bg-slate-50 min-h-screen flex flex-col justify-between">

    <!-- Navbar Minimalis Konsisten -->
    <nav class="sticky top-0 z-50 border-b border-gray-200 py-4" style="background-color: var(--color-navbar-bg);">
        <div class="mx-auto px-8 lg:px-12 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/logo-ksm.png') }}" alt="Logo KSM Multimedia" class="w-8 h-8 object-contain">
                <span class="font-bold text-xl tracking-tight">KSM Multimedia</span>
            </div>
            <div>
                <a href="{{ route('home') }}" class="text-sm font-medium text-gray-600 hover:text-ksm transition flex items-center gap-1.5">
                    &larr; Kembali ke Beranda
                </a>
            </div>
        </div>
    </nav>

    <!-- Content Utama Form Login -->
    <main class="auth-container my-auto">
        <div class="w-full max-w-md">
            
            <!-- Card Container -->
            <div class="auth-card mx-auto">
                
                <!-- Switcher Atas: Masuk / Daftar Akun
                <div class="tab-switcher grid-cols-2 mb-6">
                    <a href="{{ route('login') }}" class="tab-btn active">Masuk</a>
                    <a href="#" class="tab-btn">Daftar Akun</a>
                </div> -->

                <!-- Header Icon Gembok & Judul -->
                <div class="text-center mb-6">
                    <div class="w-12 h-12 bg-red-50 text-ksm rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Masuk ke Akun</h1>
                </div>

                <!-- Form Login -->
                <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Masuk Sebagai</label>
                        <input type="hidden" name="role" id="selectedRole" value="{{ old('role', 'Peserta') }}">
                        <div class="tab-switcher grid-cols-3">
                            <button type="button" onclick="selectRole('Peserta')" id="role-Peserta" class="tab-btn tab-btn-ksm {{ old('role', 'Peserta') === 'Peserta' ? 'active' : '' }}">Peserta</button>
                            <button type="button" onclick="selectRole('Mentor')" id="role-Mentor" class="tab-btn tab-btn-ksm {{ old('role') === 'Mentor' ? 'active' : '' }}">Mentor</button>
                            <button type="button" onclick="selectRole('Pengurus KSM')" id="role-Pengurus" class="tab-btn tab-btn-ksm {{ old('role') === 'Pengurus KSM' ? 'active' : '' }}">Pengurus</button>
                        </div>
                        @error('role')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Input Alamat Email -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-700 mb-1">
                            Alamat Email <span class="text-red-500">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-icon-left">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com" class="form-control">
                        </div>
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Input Kata Sandi -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">
                            Kata Sandi <span class="text-red-500">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-icon-left">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input type="password" id="password" name="password" required placeholder="Masukkan kata sandi" class="form-control pr-10">
                            <button type="button" onclick="togglePasswordVisibility()" class="input-icon-right focus:outline-none" title="Tampilkan kata sandi">
                                <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Lupa Kata Sandi -->
                    <div class="flex items-center justify-between text-xs pt-1">
                        <a href="#" class="text-ksm font-semibold hover:underline">Lupa kata sandi?</a>
                    </div>

                    <!-- Tombol Submit -->
                    <button type="submit" class="btn btn-primary w-full py-3 mt-2 text-sm font-semibold flex items-center justify-center gap-2">
                        Masuk Sekarang &rarr;
                    </button>
                </form>

                <!-- Pemisah/Divider
                <div class="divider-text">ATAU</div>-->

                <!-- Tombol Google Login
                <a href="#" class="btn-social">
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                    Masuk dengan Google
                </a> -->

                <!-- Link Pendaftaran
                <p class="text-xs text-center text-gray-500 mt-6">
                    Belum memiliki akun? <a href="#" class="text-ksm font-bold hover:underline">Daftar Sekarang</a>
                </p> -->

            </div>

            <!-- Catatan Keamanan TLS -->
            <div class="text-center mt-6 text-xs text-gray-400 flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Enkripsi TLS 256-bit standar akademik KSM Multimedia</span>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-700 py-8 bg-gray-900">
        <div class="mx-auto px-8 lg:px-12 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-gray-400">
            <div class="flex items-center gap-2 font-bold text-white">
                KSM Multimedia
                <span class="font-normal text-gray-400 ml-2">&copy; {{ date('Y') }} KSM Multimedia. Hak Cipta Dilindungi Undang-Undang.</span>
            </div>
            <div class="flex gap-6 font-medium">
                <a href="#" class="text-gray-400 hover:text-white transition">Beranda</a>
                <a href="#showcase" class="text-gray-400 hover:text-white transition">Showcase</a>
                <a href="#mentoring" class="text-gray-400 hover:text-white transition">Mentoring</a>
                <a href="#" class="text-gray-400 hover:text-white transition">Kebijakan Privasi</a>
                <a href="#" class="text-gray-400 hover:text-white transition">Syarat & Ketentuan</a>
            </div>
        </div>
    </footer>

    <!-- Script Interaktif JS -->
    <script>
        function selectRole(roleName) {
            document.getElementById('selectedRole').value = roleName;
            ['Peserta', 'Mentor', 'Pengurus'].forEach(role => {
                document.getElementById('role-' + role).classList.toggle(
                    'active',
                    role === (roleName === 'Pengurus KSM' ? 'Pengurus' : roleName)
                );
            });
        }

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.style.color = '#8A1538';
            } else {
                passwordInput.type = 'password';
                eyeIcon.style.color = '#9ca3af';
            }
        }
    </script>
</body>
</html>