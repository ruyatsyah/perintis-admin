<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $fillable = ['nama', 'nis', 'kelas_id', 'jenis_kelamin'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function absensi_details()
    {
        return $this->hasMany(AbsensiDetail::class);
    }
}
