<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rt extends Model
{
    protected $table = 'rts';

    protected $fillable = ['dusun_id', 'no_rt', 'nama_ketua_rt', 'no_hp'];

    public function dusun()
    {
        return $this->belongsTo(Dusun::class);
    }
}
