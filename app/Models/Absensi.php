<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $fillable = ['tanggal', 'kelas_id', 'petugas_id'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function details()
    {
        return $this->hasMany(AbsensiDetail::class);
    }
}
