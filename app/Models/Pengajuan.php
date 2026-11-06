<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    protected $fillable = [
        'penduduk_id', 'jenis_surat_id', 'status', 'keterangan', 'file_pendukung',
        'waktu_mulai_proses', 'waktu_selesai',
    ];

    protected function casts(): array
    {
        return ['waktu_mulai_proses' => 'datetime', 'waktu_selesai' => 'datetime'];
    }

    public function penduduk()
    {
        return $this->belongsTo(Penduduk::class);
    }

    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class, 'jenis_surat_id');
    }

    public function surat()
    {
        return $this->hasOne(Surat::class);
    }
}
