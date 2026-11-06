    <div class="page-heading">
        <div><span class="eyebrow">RINGKASAN ADMINISTRASI</span><h1>Laporan Desa</h1><p class="muted">Gambaran data administrasi Desa Nagerawe.</p></div>
        <button class="button button-outline" type="button" onclick="window.print()">Cetak laporan <span>↗</span></button>
    </div>

    <div class="stats-grid report-stats">
        @foreach ($stats as $label => $value)
            <article class="stat-card"><div class="stat-top"><span>{{ $label }}</span><span class="stat-icon tone-blue">↗</span></div><strong class="stat-value">{{ number_format($value, 0, ',', '.') }}</strong><span class="stat-foot">Data saat ini</span></article>
        @endforeach
    </div>

    <section class="panel report-panel">
        <div class="panel-heading"><div><h2>Permohonan surat tahun {{ now()->year }}</h2><p>Jumlah pengajuan per bulan</p></div><span class="report-total">{{ array_sum($monthly) }} pengajuan</span></div>
        <div class="chart-list">
            @foreach (range(1, 12) as $month)
                @php
                    $value = $monthly[$month] ?? 0;
                    $maxValue = max(1, max($monthly));
                    $width = $value > 0 ? max(4, (int) round($value / $maxValue * 100)) : 0;
                @endphp
                <div class="chart-row"><span>{{ \Illuminate\Support\Carbon::create()->locale('id')->month($month)->translatedFormat('F') }}</span><div class="chart-track"><i style="width: {{ $width }}%"></i></div><strong>{{ $value }}</strong></div>
            @endforeach
        </div>
    </section>
    <p class="report-note">Laporan ini merangkum data yang tersimpan di sistem. Gunakan tombol cetak untuk menyimpan atau mencetak halaman.</p>
