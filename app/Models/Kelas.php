<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $fillable = ['tingkat', 'nama_kelas', 'jurusan_id'];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }

    public function petugas()
    {
        return $this->hasMany(User::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }
}
