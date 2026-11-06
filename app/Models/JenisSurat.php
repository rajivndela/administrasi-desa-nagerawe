<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSurat extends Model
{
    protected $fillable = ['nama_surat', 'deskripsi', 'standar_waktu_layanan', 'template_file', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class, 'jenis_surat_id');
    }
}
