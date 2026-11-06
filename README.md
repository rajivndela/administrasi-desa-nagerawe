# Administrasi Desa Nagerawe

Aplikasi administrasi desa berbasis Laravel untuk penduduk, administrator, dan kepala desa. Alur dan entitas data mengikuti diagram ERD serta flowchart yang diberikan.

## Fitur

- Registrasi penduduk, login, logout, dan pembatasan menu berdasarkan peran.
- Dashboard ringkasan pengajuan surat dan pengaduan.
- Pengelolaan data penduduk, keluarga, dusun/RT, pengguna, dan jenis layanan surat.
- Pengajuan surat oleh penduduk; proses, persetujuan, penolakan, penerbitan, notifikasi, dan arsip surat oleh petugas.
- Pengaduan warga beserta tanggapan dan status tindak lanjut.
- Notifikasi akun, laporan administrasi, dan tampilan cetak surat.

## Menjalankan aplikasi

Persyaratan: PHP 8.2+, Composer, dan Node.js/npm hanya bila ingin membangun aset frontend lain.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

`.env.example` menggunakan SQLite. Pastikan `DB_DATABASE` menunjuk ke database yang akan digunakan. Untuk membuat ulang database lokal secara destruktif, gunakan `php artisan migrate:fresh --seed` hanya setelah memastikan data di dalamnya memang boleh dihapus.

Setelah server berjalan, buka `http://127.0.0.1:8000`. Setelah masuk, setiap peran diarahkan ke dashboard masing-masing:

- Halaman utama publik: `/`

- Administrator: `/admin/dashboard`
- Kepala Desa: `/kepala-desa/dashboard`
- Penduduk: `/penduduk/dashboard`

Menu disusun mengikuti tanggung jawab setiap peran: administrator mengelola data master dan pemeriksaan awal surat, Kepala Desa menyetujui atau menolak surat, sementara penduduk mengajukan surat dan memantau pengaduan serta arsip miliknya.

## Akun aplikasi

Seeder menyediakan akun berikut untuk penggunaan aplikasi:

| Peran | Email | Kata sandi |
|---|---|---|
| Administrator | `admin@gmail.com` | `abcd12345` |
| Kepala Desa | `kepaladesa@gmail.com` | `abcd12345` |

Penduduk dapat mendaftarkan akun melalui halaman Daftar. Ganti kredensial demo dan konfigurasi aplikasi sebelum dipakai di lingkungan produksi.

## Struktur penting

```text
app/
  Http/Controllers/       Autentikasi, dashboard, dan alur modul
  Models/                 Model pengguna, wilayah, penduduk, surat, arsip, dan notifikasi
database/
  migrations/             Skema basis data administrasi desa
  seeders/                Akun demo dan jenis layanan awal
resources/views/
  auth/                   Form masuk dan pendaftaran
  home.blade.php          Halaman utama publik dan pengenalan layanan desa
  admin/                  Dashboard dan menu administrator
  kepala-desa/            Dashboard dan menu kepala desa
  penduduk/               Dashboard dan menu penduduk
  dashboard/content.blade.php  Tampilan dashboard bersama
  layouts/                Kerangka navigasi utama
  modules/                Konten bersama modul, laporan, dan cetak surat
public/css/desa.css       Tampilan responsif aplikasi
public/css/home.css       Tampilan halaman utama publik
routes/web.php            Rute halaman dan alur autentikasi
tests/Feature/            Pengujian alur pendaftaran dan layanan surat
```

Setiap file menu berdasarkan peran menggunakan nama menu yang sama dengan URL, misalnya `resources/views/admin/dashboard.blade.php` dan `resources/views/kepala-desa/dashboard.blade.php`. File tersebut memakai komponen konten bersama di `resources/views/dashboard/` atau `resources/views/modules/` agar tampilan konsisten.

## Menjalankan pengujian

```powershell
php artisan test
```
