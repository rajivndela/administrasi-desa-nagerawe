<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penduduk extends Model
{
    protected $fillable = [
        'user_id', 'keluarga_id', 'nik', 'nama', 'jenis_kelamin', 'tempat_lahir',
        'tanggal_lahir', 'agama', 'alamat', 'dusun', 'rt', 'rw', 'pekerjaan',
        'status_perkawinan', 'status',
    ];

    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function keluarga()
    {
        return $this->belongsTo(Keluarga::class);
    }

    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class);
    }

    public function pengaduans()
    {
        return $this->hasMany(Pengaduan::class);
    }
}
