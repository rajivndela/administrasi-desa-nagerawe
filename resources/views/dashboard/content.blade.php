    <div class="welcome-card">
        <div class="welcome-row">
            <div>
                <span class="eyebrow">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                <h1>Selamat datang, {{ auth()->user()->name }} <span class="wave">👋</span></h1>
                <p class="muted">{{ auth()->user()->role === 'penduduk' ? 'Pantau layanan desa dan aktivitas pengajuan Anda.' : 'Berikut ringkasan pelayanan dan administrasi desa hari ini.' }}</p>
            </div>
            @if (auth()->user()->role === 'penduduk')
                <a class="button button-primary" href="{{ route($rolePrefix . '.modules.index', 'surat') }}">+ Ajukan surat</a>
            @endif
        </div>
    </div>

    <div class="stats-grid">
        @foreach ($cards as $card)
            <article class="stat-card">
                <div class="stat-top"><span>{{ $card['label'] }}</span><span class="stat-icon tone-{{ $card['tone'] }}">{{ $card['icon'] }}</span></div>
                <strong class="stat-value">{{ number_format($card['value'], 0, ',', '.') }}</strong>
                <span class="stat-foot">{{ auth()->user()->role === 'penduduk' ? 'Ringkasan akun Anda' : 'Data tercatat dalam sistem' }}</span>
            </article>
        @endforeach
    </div>

    <div class="dashboard-grid">
        <section class="panel">
            <div class="panel-heading">
                <div><h2>{{ auth()->user()->role === 'penduduk' ? 'Pengajuan surat saya' : 'Pengajuan terbaru' }}</h2><p>Perkembangan layanan surat</p></div>
                <a class="text-link" href="{{ route($rolePrefix . '.modules.index', 'surat') }}">Lihat semua <span>→</span></a>
            </div>
            @if ($requests->isEmpty())
                <div class="empty-state"><span class="empty-icon">S</span><strong>Belum ada pengajuan surat</strong><p>Pengajuan layanan baru akan muncul di sini.</p></div>
            @else
                <div class="activity-list">
                    @foreach ($requests as $item)
                        <div class="activity-row">
                            <span class="activity-symbol symbol-blue">S</span>
                            <div class="activity-copy"><strong>{{ $item->jenisSurat?->nama_surat ?? 'Layanan surat' }}</strong><small>{{ $item->penduduk?->nama }} <span>•</span> {{ $item->created_at->locale('id')->translatedFormat('d M Y') }}</small></div>
                            <span class="badge badge-{{ $item->status }}">{{ ucfirst($item->status) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="panel quick-panel">
            <div class="panel-heading"><div><h2>Akses cepat</h2><p>Menu yang sering digunakan</p></div></div>
            <div class="quick-links">
                @if (auth()->user()->role === 'penduduk')
                    <a href="{{ route($rolePrefix . '.modules.index', 'surat') }}"><span class="quick-mark tone-blue">S</span><span><strong>Ajukan surat</strong><small>Ajukan layanan administrasi</small></span><b>→</b></a>
                    <a href="{{ route($rolePrefix . '.modules.index', 'pengaduan') }}"><span class="quick-mark tone-purple">A</span><span><strong>Kirim pengaduan</strong><small>Sampaikan aspirasi atau keluhan</small></span><b>→</b></a>
                    <a href="{{ route($rolePrefix . '.modules.index', 'notifikasi') }}"><span class="quick-mark tone-amber">N</span><span><strong>Notifikasi</strong><small>{{ auth()->user()->notifikasis()->where('status_baca', false)->count() }} informasi belum dibaca</small></span><b>→</b></a>
                @else
                    <a href="{{ route($rolePrefix . '.modules.index', 'surat') }}"><span class="quick-mark tone-blue">S</span><span><strong>{{ auth()->user()->role === 'admin' ? 'Periksa pengajuan surat' : 'Persetujuan surat' }}</strong><small>{{ $pendingWorkflowCount }} {{ auth()->user()->role === 'admin' ? 'menunggu pemeriksaan' : 'menunggu persetujuan' }}</small></span><b>→</b></a>
                    <a href="{{ route($rolePrefix . '.modules.index', 'pengaduan') }}"><span class="quick-mark tone-purple">A</span><span><strong>Tindak lanjut pengaduan</strong><small>Kelola pesan dari masyarakat</small></span><b>→</b></a>
                    <a href="{{ route($rolePrefix . '.reports.index') }}"><span class="quick-mark tone-green">L</span><span><strong>Lihat laporan</strong><small>Ringkasan administrasi desa</small></span><b>→</b></a>
                @endif
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading"><div><h2>Pengaduan terbaru</h2><p>Suara dan aspirasi masyarakat</p></div><a class="text-link" href="{{ route($rolePrefix . '.modules.index', 'pengaduan') }}">Lihat semua <span>→</span></a></div>
            @if ($complaints->isEmpty())
                <div class="empty-state"><span class="empty-icon">A</span><strong>Belum ada pengaduan</strong><p>Pengaduan baru akan ditampilkan di sini.</p></div>
            @else
                <div class="activity-list">
                    @foreach ($complaints as $item)
                        <div class="activity-row">
                            <span class="activity-symbol symbol-purple">A</span>
                            <div class="activity-copy"><strong>{{ \Illuminate\Support\Str::limit($item->isi_pengaduan, 58) }}</strong><small>{{ $item->penduduk?->nama }} <span>•</span> {{ $item->tanggal?->locale('id')->translatedFormat('d M Y') }}</small></div>
                            <span class="badge badge-{{ $item->status }}">{{ ucfirst($item->status) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="panel overview-panel">
            <div class="panel-heading"><div><h2>Informasi layanan</h2><p>Desa Nagerawe</p></div><span class="overview-mark">N</span></div>
            <div class="overview-stats"><span>Jenis surat aktif</span><strong>{{ $activeTypes }}</strong></div>
            <div class="overview-stats"><span>Dokumen arsip</span><strong>{{ $archiveCount }}</strong></div>
            @if (auth()->user()->role !== 'penduduk')
                <div class="overview-stats"><span>Pengguna terdaftar</span><strong>{{ $userCount }}</strong></div>
            @endif
            <div class="village-address"><span class="location-mark">⌖</span><span><strong>Kantor Desa Nagerawe</strong><small>Pelayanan administrasi masyarakat</small></span></div>
        </section>
    </div>
