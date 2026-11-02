@extends('layouts.app')

@section('title', 'Syarat & Ketentuan - KSM Multimedia')

@section('content')
    <main class="policy-page">
        <div class="policy-container terms-policy-container">
            <header class="policy-header">
                <span class="policy-eyebrow">Informasi Layanan</span>
                <h1>Syarat &amp; Ketentuan</h1>
                <p>Ketentuan dasar penggunaan Sistem Informasi KSM Multimedia.</p>
                <small>Terakhir diperbarui: 2 November 2026</small>
            </header>

            <article class="policy-content">
                <section>
                    <span class="policy-section-number" aria-hidden="true">01</span>
                    <div>
                        <h2>Penggunaan layanan</h2>
                    <p>Gunakan layanan sesuai peran dan akses akun Anda untuk mendukung kegiatan mentoring, pengelolaan project, dan publikasi karya KSM Multimedia. Informasi yang dimasukkan harus benar, relevan, dan tidak melanggar hak orang lain.</p>
                    </div>
                </section>
                <section>
                    <span class="policy-section-number" aria-hidden="true">02</span>
                    <div>
                        <h2>Akun dan keamanan</h2>
                    <p>Jaga kerahasiaan kredensial akun dan jangan menggunakan akun milik orang lain. Segera hubungi pengurus KSM Multimedia apabila mengetahui atau mencurigai penggunaan akun tanpa izin.</p>
                    </div>
                </section>
                <section>
                    <span class="policy-section-number" aria-hidden="true">03</span>
                    <div>
                        <h2>Pengiriman dan kepemilikan karya</h2>
                    <p>Pengirim bertanggung jawab memastikan karya, deskripsi, thumbnail, berkas, dan tautan yang disertakan layak dibagikan serta tidak melanggar hak cipta, privasi, atau ketentuan pihak lain. Pastikan tim mengetahui informasi yang dicantumkan pada project.</p>
                    </div>
                </section>
                <section>
                    <span class="policy-section-number" aria-hidden="true">04</span>
                    <div>
                        <h2>Review dan publikasi</h2>
                    <p>Project dapat melalui proses review dan persetujuan sebelum dipublikasikan. Pengurus dapat meminta perbaikan atau menolak publikasi berdasarkan proses kurasi. Informasi project yang telah dipublikasikan dapat dilihat oleh pengunjung Showcase tanpa login.</p>
                    </div>
                </section>
                <section>
                    <span class="policy-section-number" aria-hidden="true">05</span>
                    <div>
                        <h2>Penggunaan yang tidak diperbolehkan</h2>
                    <p>Jangan mengunggah konten ilegal, menyesatkan, berbahaya, melanggar hak pihak lain, atau menggunakan layanan untuk mengganggu keamanan maupun operasional sistem. Pengurus dapat membatasi akses atau menindaklanjuti konten yang tidak sesuai.</p>
                    </div>
                </section>
                <section>
                    <span class="policy-section-number" aria-hidden="true">06</span>
                    <div>
                        <h2>Ketersediaan layanan dan perubahan</h2>
                    <p>Fitur dan ketersediaan layanan dapat berubah untuk pemeliharaan atau pengembangan. Ketentuan ini dapat diperbarui; penggunaan layanan setelah perubahan berarti Anda diminta membaca ketentuan terbaru yang tersedia di halaman ini.</p>
                    </div>
                </section>
            </article>

            <p class="policy-related-link">Baca juga <a href="{{ route('privacy') }}">Kebijakan Privasi</a>.</p>
        </div>
    </main>
@endsection
