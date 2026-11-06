<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | Desa Nagerawe</title>
    <link rel="stylesheet" href="{{ asset('css/desa.css') }}">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-aside">
            <a class="brand brand-light" href="{{ route('home') }}"><span class="brand-emblem">N</span><span><strong>Desa Nagerawe</strong><small>LAYANAN DIGITAL</small></span></a>
            <div class="auth-message">
                <span class="eyebrow">PELAYANAN DESA, LEBIH DEKAT</span>
                <h1>Urus kebutuhan desa dengan lebih mudah.</h1>
                <p>Ajukan surat, sampaikan aspirasi, dan pantau pelayanan dalam satu ruang digital.</p>
            </div>
            <div class="auth-aside-foot">Melayani dengan sepenuh hati <span>•</span> Desa Nagerawe</div>
        </section>
        <section class="auth-main">
            <div class="auth-card">
                <a class="auth-mobile-brand" href="{{ route('home') }}"><span class="brand-emblem">N</span> Desa Nagerawe</a>
                <span class="eyebrow">SELAMAT DATANG KEMBALI</span>
                <h2>Masuk ke akun</h2>
                <p class="muted">Gunakan email dan kata sandi yang telah terdaftar.</p>
                @if ($errors->any())
                    <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="{{ route('login.store') }}" class="stack-form">
                    @csrf
                    <label>Email
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email" required autofocus>
                        @error('email')<span class="field-error">{{ $message }}</span>@enderror
                    </label>
                    <label>Kata sandi
                        <input type="password" name="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required>
                        @error('password')<span class="field-error">{{ $message }}</span>@enderror
                    </label>
                    <label class="checkbox-label"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
                    <button class="button button-primary button-wide" type="submit">Masuk ke Dashboard <span>→</span></button>
                </form>
                <p class="auth-switch">Belum memiliki akun? <a href="{{ route('register') }}">Daftar sebagai penduduk</a></p>
            </div>
        </section>
    </main>
</body>
</html>
