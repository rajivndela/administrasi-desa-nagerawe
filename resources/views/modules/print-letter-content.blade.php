<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $letter->jenisSurat->nama_surat }} | Desa Nagerawe</title>
    <link rel="stylesheet" href="{{ asset('css/desa.css') }}">
    <style>
        body{background:#edf1ee;color:#222;font-family:Georgia,'Times New Roman',serif}
        .print-toolbar{max-width:820px;margin:24px auto;text-align:right}
        .letter-paper{max-width:820px;min-height:1000px;margin:0 auto 40px;padding:58px 72px;background:#fff;box-shadow:0 12px 40px rgba(0,0,0,.1)}
        .letter-head{text-align:center;border-bottom:3px double #222;padding-bottom:16px}
        .letter-head strong,.letter-head span{display:block}
        .letter-head strong{font-size:21px;text-transform:uppercase}
        .letter-head span{margin-top:5px;font-size:12px}
        .letter-number{text-align:center;margin:26px 0 32px}
        .letter-number strong,.letter-number span{display:block}
        .letter-number strong{text-decoration:underline;text-transform:uppercase;font-size:16px}
        .letter-number span{margin-top:5px;font-size:13px}
        .letter-body{font-size:14px;line-height:1.9;text-align:justify}
        .identity-table{margin:18px auto;border-collapse:collapse}
        .identity-table td{padding:3px 8px;border:0;vertical-align:top}
        .letter-signature{width:245px;margin:50px 0 0 auto;text-align:center;font-size:13px;line-height:1.8}
        .signature-space{height:70px}
        .letter-foot{margin-top:36px;color:#666;font-size:10px}
        @media print{body{background:#fff}.print-toolbar{display:none}.letter-paper{max-width:none;min-height:auto;margin:0;padding:24mm 22mm;box-shadow:none}}
        @media(max-width:600px){.letter-paper{margin:0;padding:28px 20px;min-height:100vh}.print-toolbar{text-align:center}}
    </style>
</head>
<body>
    <div class="print-toolbar"><button class="button button-primary" type="button" onclick="window.print()">Cetak surat</button></div>
    <main class="letter-paper">
        <header class="letter-head">
            <strong>Pemerintah Kabupaten</strong>
            <strong>Desa Nagerawe</strong>
            <span>Pelayanan Administrasi Masyarakat</span>
        </header>
        <div class="letter-number">
            <strong>{{ $letter->jenisSurat->nama_surat }}</strong>
            <span>Nomor: {{ $letter->surat->no_surat }}</span>
        </div>
        <div class="letter-body">
            <p>Yang bertanda tangan di bawah ini, Kepala Desa Nagerawe, menerangkan bahwa:</p>
            <table class="identity-table">
                <tr><td>Nama</td><td>:</td><td>{{ $letter->penduduk->nama }}</td></tr>
                <tr><td>NIK</td><td>:</td><td>{{ $letter->penduduk->nik }}</td></tr>
                <tr><td>Alamat</td><td>:</td><td>{{ $letter->penduduk->alamat }}</td></tr>
            </table>
            <p>Nama tersebut di atas tercatat sebagai penduduk Desa Nagerawe. Surat ini diterbitkan untuk keperluan:</p>
            <p><strong>{{ $letter->keterangan ?: $letter->jenisSurat->deskripsi ?: $letter->jenisSurat->nama_surat }}</strong></p>
            <p>Demikian surat keterangan ini dibuat dengan sebenarnya agar dapat dipergunakan sebagaimana mestinya.</p>
        </div>
        <div class="letter-signature">
            <div>Nagerawe, {{ $letter->surat->tgl_terbit->locale('id')->translatedFormat('d F Y') }}</div>
            <div>Kepala Desa Nagerawe</div>
            <div class="signature-space"></div>
            <strong><u>{{ $letter->surat->ditandatanganiOleh?->name ?? 'Kepala Desa Nagerawe' }}</u></strong>
        </div>
        <div class="letter-foot">Kode verifikasi dokumen: {{ $letter->surat->qr_code }}</div>
    </main>
</body>
</html>
