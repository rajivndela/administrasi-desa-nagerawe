<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    protected $fillable = ['pengajuan_id', 'no_surat', 'tgl_terbit', 'file_surat', 'qr_code', 'ditandatangani_oleh'];

    protected function casts(): array
    {
        return ['tgl_terbit' => 'date'];
    }

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }

    public function ditandatanganiOleh()
    {
        return $this->belongsTo(User::class, 'ditandatangani_oleh');
    }
}
