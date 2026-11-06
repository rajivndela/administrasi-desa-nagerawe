<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dusun extends Model
{
    protected $fillable = ['nama_dusun'];

    public function rts()
    {
        return $this->hasMany(Rt::class);
    }
}
