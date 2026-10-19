# sistem-informasi-ksm-multimedia
Sistem Informasi untuk Pengelolaan dan Publikasi Capstone Project KSM Multimedia

# Sistem Informasi KSM Multimedia

Sistem informasi berbasis web untuk mendukung pengelolaan dan publikasi Capstone Project KSM Multimedia.

## Teknologi yang Digunakan

- **Backend:** Laravel (PHP)
- **Frontend:** Blade, HTML, dan CSS
- **Database:** MySQL

## Prasyarat

Pastikan perangkat sudah memiliki aplikasi berikut:

- PHP
- Composer
- Node.js dan npm
- Git

## Instalasi dan Setup

### 1. Clone Repository

Clone repository project dari GitHub:

```bash
git clone https://github.com/abcdefall/sistem-informasi-ksm-multimedia.git
```

Masuk ke folder project:

```bash
cd sistem-informasi-ksm-multimedia
```

### 2. Install Dependency PHP

Jalankan perintah berikut untuk menginstal dependency Laravel:

```bash
composer install
```

### 3. Buat File Environment

Salin file `.env.example` menjadi `.env`.

**Windows PowerShell:**

```powershell
Copy-Item .env.example .env
```

### 4. Generate Application Key

Jalankan perintah:

```bash
php artisan key:generate
```

### 5. Jalankan Aplikasi

Jalankan server development Laravel:

```bash
php artisan serve
```

Buka alamat berikut di browser:

```text
http://127.0.0.1:8000
```

Untuk menghentikan server, tekan `Ctrl + C` pada terminal.


## Catatan Pengembangan

- Gunakan repository GitHub sebagai tempat penyimpanan kode bersama.
- File `.env` berisi konfigurasi lokal dan tidak boleh diunggah ke repository.
- Folder `vendor` dan `node_modules` tidak perlu diunggah ke repository.
- Pengembangan fitur dilakukan secara bertahap sesuai pembagian tugas tim.
