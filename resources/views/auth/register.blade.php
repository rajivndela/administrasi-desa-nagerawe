<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Penduduk | Desa Nagerawe</title>
    <link rel="stylesheet" href="{{ asset('css/desa.css') }}">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-aside">
            <a class="brand brand-light" href="{{ route('home') }}"><span class="brand-emblem">N</span><span><strong>Desa Nagerawe</strong><small>LAYANAN DIGITAL</small></span></a>
            <div class="auth-message">
                <span class="eyebrow">AKSES LAYANAN WARGA</span>
                <h1>Satu akun untuk layanan desa.</h1>
                <p>Daftarkan diri untuk mengajukan surat dan menyampaikan pengaduan langsung kepada pemerintah desa.</p>
            </div>
            <div class="auth-aside-foot">Data Anda digunakan untuk keperluan pelayanan desa.</div>
        </section>
        <section class="auth-main">
            <div class="auth-card auth-card-wide">
                <a class="auth-mobile-brand" href="{{ route('home') }}"><span class="brand-emblem">N</span> Desa Nagerawe</a>
                <span class="eyebrow">PENDAFTARAN WARGA</span>
                <h2>Buat akun penduduk</h2>
                <p class="muted">Lengkapi identitas dasar untuk memulai.</p>
                @if ($errors->any())
                    <div class="alert alert-error" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <form method="POST" action="{{ route('register.store') }}" class="stack-form">
                    @csrf
                    <label>Nama lengkap<input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required></label>
                    <label>NIK (16 digit)<input type="text" name="nik" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" value="{{ old('nik') }}" required></label>
                    <label>Email<input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required></label>
                    <label>Nomor telepon <span class="optional">(opsional)</span><input type="tel" name="no_hp" value="{{ old('no_hp') }}" autocomplete="tel"></label>
                    <label>Alamat domisili<input type="text" name="alamat" value="{{ old('alamat') }}" required></label>
                    <div class="form-grid">
                        <label>Kata sandi<input type="password" name="password" autocomplete="new-password" minlength="8" required></label>
                        <label>Ulangi kata sandi<input type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required></label>
                    </div>
                    <button class="button button-primary button-wide" type="submit">Daftar sekarang <span>→</span></button>
                </form>
                <p class="auth-switch">Sudah memiliki akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
            </div>
        </section>
    </main>
</body>
</html>
