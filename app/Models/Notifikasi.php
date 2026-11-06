<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $fillable = ['user_id', 'pesan', 'status_baca', 'tanggal'];

    protected function casts(): array
    {
        return ['status_baca' => 'boolean', 'tanggal' => 'datetime'];
    }
}
