<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifikasiSurat extends Model
{
    protected $fillable = ['pengajuan_id', 'user_id', 'status_verifikasi', 'catatan', 'tgl_verifikasi'];
}
