<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiDetail extends Model
{
    protected $table = 'absensi_detail';
    protected $fillable = ['absensi_id', 'siswa_id', 'status'];

    public function absensi()
    {
        return $this->belongsTo(Absensi::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
