<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SiswaExport implements FromCollection, WithHeadings, WithMapping
{
    protected $kelas_id;

    public function __construct($kelas_id = null)
    {
        $this->kelas_id = $kelas_id;
    }

    public function collection()
    {
        $query = Siswa::with('kelas');
        if ($this->kelas_id) {
            $query->where('kelas_id', $this->kelas_id);
        }
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Nama',
            'NIS',
            'Kelas',
            'Jenis Kelamin',
        ];
    }

    public function map($siswa): array
    {
        return [
            $siswa->nama,
            $siswa->nis,
            $siswa->kelas->nama_kelas,
            $siswa->jenis_kelamin,
        ];
    }
}
