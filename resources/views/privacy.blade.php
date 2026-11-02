@extends('layouts.app')

@section('title', 'Kebijakan Privasi - KSM Multimedia')

@section('content')
    <main class="policy-page">
        <div class="policy-container privacy-policy-container">
            <header class="policy-header">
                <span class="policy-eyebrow">Informasi Layanan</span>
                <h1>Kebijakan Privasi</h1>
                <p>Informasi tentang data yang digunakan dalam layanan Sistem Informasi KSM Multimedia.</p>
                <small>Terakhir diperbarui: 2 November 2026</small>
            </header>

            <article class="policy-content">
                <section>
                    <span class="policy-section-number" aria-hidden="true">01</span>
                    <div>
                        <h2>Informasi yang digunakan</h2>
                    <p>Layanan dapat menggunakan informasi akun seperti nama dan alamat email, informasi karya yang dikirimkan, keanggotaan tim, serta catatan proses mentoring, review, dan publikasi. Data teknis sesi juga digunakan agar fitur login dan layanan berjalan dengan baik.</p>
                    </div>
                </section>
                <section>
                    <span class="policy-section-number" aria-hidden="true">02</span>
                    <div>
                        <h2>Tujuan penggunaan</h2>
                    <p>Informasi digunakan untuk mengelola akun, mendukung proses mentoring dan evaluasi karya, mengelola publikasi di Showcase, serta menjaga keamanan dan kelancaran layanan.</p>
                    </div>
                </section>
                <section>
                    <span class="policy-section-number" aria-hidden="true">03</span>
                    <div>
                        <h2>Informasi yang tampil di Showcase</h2>
                    <p>Jika sebuah project dipublikasikan, informasi karya seperti judul, deskripsi, bidang, periode, thumbnail, nama anggota tim dan perannya, serta tautan project dapat dilihat oleh pengunjung Showcase, termasuk pengunjung yang belum login. Hindari mengirimkan informasi pribadi yang tidak diperlukan di deskripsi atau berkas project.</p>
                    </div>
                </section>
                <section>
                    <span class="policy-section-number" aria-hidden="true">04</span>
                    <div>
                        <h2>Akses dan penyimpanan</h2>
                    <p>Informasi akun dan project yang belum dipublikasikan hanya tersedia untuk kebutuhan layanan dan pihak berwenang sesuai perannya. Data disimpan selama diperlukan untuk operasional dan dokumentasi kegiatan KSM Multimedia, dengan memperhatikan kebutuhan pengelolaan layanan.</p>
                    </div>
                </section>
                <section>
                    <span class="policy-section-number" aria-hidden="true">05</span>
                    <div>
                        <h2>Pengelolaan informasi akun</h2>
                    <p>Untuk meminta akses, koreksi, atau bantuan terkait informasi akun, hubungi pengurus KSM Multimedia melalui kanal komunikasi resmi organisasi. Permintaan dapat memerlukan verifikasi dan mempertimbangkan kebutuhan pencatatan akademik maupun operasional.</p>
                    </div>
                </section>
                <section>
                    <span class="policy-section-number" aria-hidden="true">06</span>
                    <div>
                        <h2>Perubahan kebijakan</h2>
                    <p>Kebijakan ini dapat diperbarui apabila layanan atau cara pengelolaan informasi berubah. Versi terbaru akan tersedia pada halaman ini.</p>
                    </div>
                </section>
            </article>

            <p class="policy-related-link">Baca juga <a href="{{ route('terms') }}">Syarat &amp; Ketentuan</a>.</p>
        </div>
    </main>
@endsection
