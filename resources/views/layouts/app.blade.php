<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} | Desa Nagerawe</title>
    <link rel="stylesheet" href="{{ asset('css/desa.css') }}">
</head>
<body>
    @php
        $user = auth()->user();
        $rolePrefix = match ($user->role) {
            'admin' => 'admin',
            'kepala_desa' => 'kepala-desa',
            default => 'penduduk',
        };
        $isAdmin = $user->role === 'admin';
        $isHeadman = $user->role === 'kepala_desa';
        $isResident = $user->role === 'penduduk';
        $unreadNotifications = $user->notifikasis()->where('status_baca', false)->count();
        $serviceModuleLabel = $isAdmin ? 'Proses Pengajuan Surat' : ($isHeadman ? 'Persetujuan Surat' : 'Ajukan Surat');
        $complaintModuleLabel = $isResident ? 'Pengaduan Saya' : 'Pengaduan Warga';
        $navigation = [
            ['section' => 'Ringkasan', 'label' => 'Dashboard', 'route' => $rolePrefix . '.dashboard', 'href' => route($rolePrefix . '.dashboard'), 'show' => true, 'mark' => 'D'],
            ['section' => $isAdmin ? 'Pelayanan' : 'Layanan warga', 'label' => $serviceModuleLabel, 'route' => $rolePrefix . '.modules.index', 'module' => 'surat', 'href' => route($rolePrefix . '.modules.index', 'surat'), 'show' => true, 'mark' => 'S'],
            ['section' => $isAdmin ? 'Pelayanan' : 'Layanan warga', 'label' => $complaintModuleLabel, 'route' => $rolePrefix . '.modules.index', 'module' => 'pengaduan', 'href' => route($rolePrefix . '.modules.index', 'pengaduan'), 'show' => true, 'mark' => 'A'],
            ['section' => $isAdmin ? 'Pelayanan' : 'Layanan warga', 'label' => $isResident ? 'Arsip Surat Saya' : 'Arsip Surat', 'route' => $rolePrefix . '.modules.index', 'module' => 'arsip', 'href' => route($rolePrefix . '.modules.index', 'arsip'), 'show' => true, 'mark' => 'R'],
            ['section' => 'Data master', 'label' => 'Data Penduduk', 'route' => $rolePrefix . '.modules.index', 'module' => 'penduduk', 'href' => route($rolePrefix . '.modules.index', 'penduduk'), 'show' => $isAdmin, 'mark' => 'P'],
            ['section' => 'Data master', 'label' => 'Data Keluarga', 'route' => $rolePrefix . '.modules.index', 'module' => 'keluarga', 'href' => route($rolePrefix . '.modules.index', 'keluarga'), 'show' => $isAdmin, 'mark' => 'K'],
            ['section' => 'Data master', 'label' => 'Wilayah Dusun & RT', 'route' => $rolePrefix . '.modules.index', 'module' => 'wilayah', 'href' => route($rolePrefix . '.modules.index', 'wilayah'), 'show' => $isAdmin, 'mark' => 'W'],
            ['section' => 'Data master', 'label' => 'Jenis Layanan Surat', 'route' => $rolePrefix . '.modules.index', 'module' => 'jenis-surat', 'href' => route($rolePrefix . '.modules.index', 'jenis-surat'), 'show' => $isAdmin, 'mark' => 'L'],
            ['section' => 'Pengaturan', 'label' => 'Pengguna Sistem', 'route' => $rolePrefix . '.modules.index', 'module' => 'pengguna', 'href' => route($rolePrefix . '.modules.index', 'pengguna'), 'show' => $isAdmin, 'mark' => 'U'],
            ['section' => 'Pemantauan', 'label' => 'Laporan', 'route' => $rolePrefix . '.reports.index', 'href' => $isAdmin || $isHeadman ? route($rolePrefix . '.reports.index') : '#', 'show' => $isAdmin || $isHeadman, 'mark' => '↗'],
            ['section' => 'Pemantauan', 'label' => 'Notifikasi', 'route' => $rolePrefix . '.modules.index', 'module' => 'notifikasi', 'href' => route($rolePrefix . '.modules.index', 'notifikasi'), 'show' => true, 'mark' => 'N'],
        ];
    @endphp
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <a class="brand" href="{{ route($rolePrefix . '.dashboard') }}">
                <span class="brand-emblem">N</span>
                <span><strong>Desa Nagerawe</strong><small>LAYANAN DIGITAL</small></span>
            </a>
            <nav class="side-nav" aria-label="Navigasi utama">
                @php
                    $currentSection = null;
                @endphp
                @foreach ($navigation as $item)
                    @if ($item['show'])
                        @if ($currentSection !== $item['section'])
                            <div class="nav-caption">{{ $item['section'] }}</div>
                            @php
                                $currentSection = $item['section'];
                            @endphp
                        @endif
                        @php
                            $active = isset($item['module'])
                                ? request()->routeIs($item['route']) && request()->route('module') === $item['module']
                                : request()->routeIs($item['route']);
                        @endphp
                        <a class="nav-link {{ $active ? 'active' : '' }}" href="{{ $item['href'] }}">
                            <span class="nav-mark">{{ $item['mark'] }}</span>
                            <span>{{ $item['label'] }}</span>
                            @if ($item['label'] === 'Notifikasi' && $unreadNotifications > 0)
                                <span class="nav-count">{{ $unreadNotifications }}</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </nav>
            <div class="sidebar-bottom">
                <div class="village-note"><span class="status-dot"></span><span><strong>Sistem aktif</strong><small>Pelayanan Desa Nagerawe</small></span></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit"><span class="nav-mark">↪</span> Keluar akun</button>
                </form>
            </div>
        </aside>

        <main class="main-area">
            <header class="topbar">
                <button class="mobile-menu" type="button" aria-label="Buka navigasi" onclick="document.body.classList.toggle('nav-open')">☰</button>
                <div class="breadcrumb"><span>Desa Nagerawe</span><span class="crumb-divider">/</span><strong>{{ $title ?? 'Dashboard' }}</strong></div>
                <div class="topbar-user">
                    <a class="top-notification" href="{{ route($rolePrefix . '.modules.index', 'notifikasi') }}" aria-label="Notifikasi">♧ @if($unreadNotifications > 0)<i></i>@endif</a>
                    <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    <div class="topbar-identity"><strong>{{ $user->name }}</strong><small>{{ $user->role === 'admin' ? 'Administrator' : ($user->role === 'kepala_desa' ? 'Kepala Desa' : 'Penduduk') }}</small></div>
                </div>
            </header>

            <section class="page-content">
                @if (session('success'))
                    <div class="alert alert-success" role="status">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-error" role="alert">
                        <strong>Periksa kembali data yang dimasukkan.</strong>
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </section>
            <footer class="page-footer">Pemerintah Desa Nagerawe <span>•</span> Sistem Administrasi Desa</footer>
        </main>
    </div>
</body>
</html>
