<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Portal layanan digital administrasi Desa Nagerawe. Ajukan surat, sampaikan aspirasi, dan pantau pelayanan desa secara mudah.">
    <title>Desa Nagerawe | Layanan Warga</title>
    <link rel="stylesheet" href="{{ asset('css/desa.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>
<body class="home-page">
    <header class="public-header">
        <a class="brand public-brand" href="{{ route('home') }}">
            <span class="brand-emblem">N</span>
            <span><strong>Desa Nagerawe</strong><small>LAYANAN DIGITAL</small></span>
        </a>
        <nav class="public-nav" aria-label="Navigasi halaman utama">
            <a href="#layanan">Layanan</a>
            <a href="#profil-desa">Profil Desa</a>
            <a href="#lokasi">Lokasi</a>
            <a href="#alur">Cara mengajukan</a>
            <a href="#tentang">Tentang portal</a>
        </nav>
        <div class="public-actions">
            <a class="public-login" href="{{ route('login') }}">Masuk</a>
            <a class="button button-primary public-register" href="{{ route('register') }}">Buat akun <span>→</span></a>
        </div>
    </header>

    <main>
        <section class="home-hero">
            <div class="home-hero-inner">
                <div class="hero-copy">
                    <span class="hero-kicker"><i></i> PORTAL RESMI LAYANAN WARGA</span>
                    <h1>Urus kebutuhan desa, <em>lebih mudah.</em></h1>
                    <p>Pengajuan surat dan aspirasi warga kini dapat dimulai dari satu tempat. Praktis, transparan, dan bisa dipantau.</p>
                    <div class="hero-actions">
                        <a class="button hero-primary" href="{{ route('register') }}">Mulai layanan <span>→</span></a>
                        <a class="hero-secondary" href="#alur"><span class="play-mark">↓</span> Lihat cara kerja</a>
                    </div>
                    <div class="hero-trust"><span class="trust-avatars"><i>N</i><i>W</i><i>✓</i></span><span>Pelayanan administrasi <strong>lebih dekat untuk warga</strong></span></div>
                </div>
                <div class="hero-visual" aria-label="Ilustrasi alur pelayanan surat desa">
                    <div class="hero-orbit orbit-one"></div>
                    <div class="hero-orbit orbit-two"></div>
                    <div class="village-sun"></div>
                    <div class="hero-hill hill-back"></div>
                    <div class="hero-hill hill-front"></div>
                    <div class="village-building">
                        <div class="building-roof"></div>
                        <div class="building-body">
                            <span class="building-sign">KANTOR DESA</span>
                            <div class="building-columns"><i></i><i></i><i></i></div>
                            <div class="building-door"></div>
                        </div>
                    </div>
                    <div class="floating-card service-float">
                        <span class="float-icon">✓</span>
                        <span><strong>Surat diproses</strong><small>Alur layanan terpantau</small></span>
                    </div>
                    <div class="floating-card warga-float">
                        <span class="float-avatar">N</span>
                        <span><strong>Layanan warga</strong><small>Semua dalam satu portal</small></span>
                    </div>
                    <span class="visual-caption">DESA NAGERAWE <i>•</i> MELAYANI DENGAN HATI</span>
                </div>
            </div>
            <div class="hero-bottom-line"><span></span><span>PEMERINTAH DESA NAGERAWE</span><span></span></div>
        </section>

        <section class="village-profile" id="profil-desa">
            <div class="section-wrap profile-grid">
                <div class="profile-history">
                    <span class="eyebrow">PROFIL DESA</span>
                    <h2>Desa yang tumbuh bersama warga.</h2>
                    <p>Desa Nagerawe terletak di Kecamatan Boawae, Kabupaten Nagekeo, Provinsi Nusa Tenggara Timur. Desa ini terus mengembangkan pelayanan yang transparan, mudah dijangkau, dan berorientasi pada kebutuhan masyarakat.</p>
                    <div class="profile-meta">
                        <span><strong>Nagerawe</strong><small>Nama Desa</small></span>
                        <span><strong>{{ $villageProfile['administrative_code'] }}</strong><small>Kode Wilayah</small></span>
                    </div>
                </div>
                <div class="profile-stats">
                    <article><span>▦</span><strong>{{ number_format($villageProfile['area_km2'], 2, ',', '.') }} km²</strong><small>Luas wilayah</small></article>
                    <article><span>◎</span><strong>{{ number_format($villageProfile['population'], 0, ',', '.') }} jiwa</strong><small>Penduduk {{ $villageProfile['data_year'] }}</small></article>
                    <article><span>♂</span><strong>{{ $villageProfile['male'] }} laki-laki</strong><small>{{ $villageProfile['female'] }} perempuan</small></article>
                    <article><span>◷</span><strong>{{ $villageProfile['density'] }} jiwa/km²</strong><small>Kepadatan penduduk</small></article>
                </div>
            </div>
        </section>

        <section class="village-location section-wrap" id="lokasi">
            <div class="location-map" aria-label="Peta lokasi Desa Nagerawe">
                <div class="map-grid"></div>
                <span class="map-road road-one"></span>
                <span class="map-road road-two"></span>
                <div class="map-pin"><i></i><strong>Desa Nagerawe</strong></div>
                <a href="{{ route('register') }}">Layanan digital desa</a>
            </div>
            <div class="location-copy">
                <span class="eyebrow">LOKASI DAN PEMERINTAHAN</span>
                <h2>Pemerintah Desa Nagerawe</h2>
                <p>Temukan informasi desa, ajukan layanan administrasi, dan pantau perkembangan pengajuan melalui portal resmi yang terhubung langsung ke sistem desa.</p>
                <div class="location-details">
                    <p><strong>{{ $villageProfile['district'] }}, {{ $villageProfile['regency'] }}</strong><br>{{ $villageProfile['province'] }}</p>
                    <p><strong>{{ $villageProfile['distance_to_district_km'] }} km</strong><br>Jarak ke pusat Kecamatan Boawae</p>
                    <p><strong>{{ $villageProfile['latitude'] }}, {{ $villageProfile['longitude'] }}</strong><br>Koordinat desa</p>
                </div>
                <p class="data-source">Sumber data: Wikipedia dan publikasi BPS Kecamatan Boawae Dalam Angka 2024.</p>
                <a class="button button-primary" href="{{ route('login') }}">Buka portal layanan <span>→</span></a>
            </div>
        </section>

        <section class="home-services section-wrap" id="layanan">
            <div class="section-heading">
                <div><span class="eyebrow">LAYANAN UNTUK WARGA</span><h2>Keperluan desa, satu pintu.</h2><p>Pilih layanan yang Anda butuhkan. Pengajuan bisa dipantau tanpa bingung.</p></div>
                <a class="section-link" href="{{ route('register') }}">Lihat cara memulai <span>→</span></a>
            </div>
            <div class="service-grid">
                <article class="service-card">
                    <div class="service-icon service-icon-green"><span>▤</span></div>
                    <span class="service-number">01 / SURAT</span>
                    <h3>Pengajuan surat</h3>
                    <p>Ajukan surat administrasi dan lihat perkembangannya hingga selesai diterbitkan.</p>
                    <a href="{{ route('register') }}">Ajukan setelah masuk <span>↗</span></a>
                </article>
                <article class="service-card">
                    <div class="service-icon service-icon-orange"><span>✳</span></div>
                    <span class="service-number">02 / ASPIRASI</span>
                    <h3>Pengaduan warga</h3>
                    <p>Sampaikan pertanyaan, aspirasi, atau keluhan untuk ditindaklanjuti pemerintah desa.</p>
                    <a href="{{ route('register') }}">Sampaikan aspirasi <span>↗</span></a>
                </article>
                <article class="service-card">
                    <div class="service-icon service-icon-blue"><span>▣</span></div>
                    <span class="service-number">03 / DOKUMEN</span>
                    <h3>Arsip dan status</h3>
                    <p>Periksa notifikasi serta temukan kembali surat yang sudah selesai diproses.</p>
                    <a href="{{ route('login') }}">Masuk ke akun <span>↗</span></a>
                </article>
            </div>
            <div class="available-services">
                <div class="available-heading"><div><span class="eyebrow">JENIS SURAT</span><strong>Layanan yang tersedia</strong></div><span class="available-count">{{ $activeLetterCount }} layanan aktif</span></div>
                <div class="service-chips">
                    @forelse ($letterTypes as $type)
                        <span class="service-chip"><i></i>{{ $type->nama_surat }} <small>{{ $type->standar_waktu_layanan }} hari kerja</small></span>
                    @empty
                        <span class="service-chip"><i></i>Surat keterangan domisili</span>
                        <span class="service-chip"><i></i>Surat keterangan usaha</span>
                        <span class="service-chip"><i></i>Surat pengantar</span>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="home-flow" id="alur">
            <div class="section-wrap">
                <div class="section-heading flow-heading">
                    <div><span class="eyebrow">TANPA LANGKAH YANG RUMIT</span><h2>Dari pengajuan sampai selesai.</h2><p>Alur yang jelas membantu Anda tahu siapa yang sedang memproses layanan.</p></div>
                    <span class="flow-stamp"><strong>01—04</strong><small>LANGKAH MUDAH</small></span>
                </div>
                <div class="flow-grid">
                    <article class="flow-card"><span class="flow-index">01</span><div class="flow-icon">↗</div><h3>Buat akun warga</h3><p>Daftar menggunakan NIK dan identitas dasar Anda.</p><span class="flow-connector"></span></article>
                    <article class="flow-card"><span class="flow-index">02</span><div class="flow-icon">▤</div><h3>Kirim pengajuan</h3><p>Pilih jenis surat atau tulis aspirasi yang ingin disampaikan.</p><span class="flow-connector"></span></article>
                    <article class="flow-card"><span class="flow-index">03</span><div class="flow-icon">◎</div><h3>Desa meninjau</h3><p>Petugas memeriksa dan Kepala Desa memberi persetujuan.</p><span class="flow-connector"></span></article>
                    <article class="flow-card"><span class="flow-index">04</span><div class="flow-icon">✓</div><h3>Pantau hasilnya</h3><p>Terima notifikasi dan lihat dokumen dari akun Anda.</p></article>
                </div>
            </div>
        </section>

        <section class="home-cta section-wrap" id="tentang">
            <div class="cta-emblem">N</div>
            <div><span class="eyebrow">PEMERINTAH DESA NAGERAWE</span><h2>Pelayanan desa yang dekat dan terbuka.</h2><p>Mulai dari kebutuhan administrasi hingga aspirasi masyarakat, kami hadir untuk melayani.</p></div>
            <a class="button button-primary" href="{{ route('register') }}">Daftar sebagai warga <span>→</span></a>
        </section>
    </main>

    <footer class="public-footer">
        <a class="brand public-brand" href="{{ route('home') }}"><span class="brand-emblem">N</span><span><strong>Desa Nagerawe</strong><small>LAYANAN DIGITAL</small></span></a>
        <p>Portal layanan administrasi Pemerintah Desa Nagerawe.</p>
        <span>© {{ now()->year }} Desa Nagerawe</span>
    </footer>
</body>
</html>
