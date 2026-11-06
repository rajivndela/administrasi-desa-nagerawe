<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    protected $fillable = ['penduduk_id', 'isi_pengaduan', 'status', 'tanggapan', 'ditanggapi_oleh', 'tanggal'];

    protected function casts(): array
    {
        return ['tanggal' => 'datetime'];
    }

    public function penduduk()
    {
        return $this->belongsTo(Penduduk::class);
    }
}
