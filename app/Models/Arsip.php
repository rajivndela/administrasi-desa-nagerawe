<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arsip extends Model
{
    protected $fillable = ['surat_id', 'user_id', 'no_dokumen', 'tanggal', 'perihal', 'file', 'status'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }
}
