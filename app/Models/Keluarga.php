<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keluarga extends Model
{
    protected $fillable = ['rt_id', 'no_kk', 'kepala_keluarga', 'alamat', 'dusun', 'rt', 'rw'];

    public function penduduks()
    {
        return $this->hasMany(Penduduk::class);
    }
}
