<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SiswaImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $kelasStr = trim($row['kelas']);
        $kelas = Kelas::whereRaw('LOWER(TRIM(nama_kelas)) = ?', [strtolower($kelasStr)])->first();

        if (!$kelas) {
            throw new \Exception("Kelas '{$kelasStr}' tidak ditemukan. Pastikan data kelas tersebut sudah ditambahkan di sistem.");
        }

        return new Siswa([
            'nama' => $row['nama'],
            'nis' => $row['nis'],
            'kelas_id' => $kelas->id,
            'jenis_kelamin' => strtoupper($row['jenis_kelamin']),
        ]);
    }

    public function prepareForValidation($data, $index)
    {
        // Clean NIS from any unexpected formatting (like .0 from Excel numbers)
        if (isset($data['nis'])) {
            $data['nis'] = preg_replace('/[^0-9]/', '', (string)$data['nis']);
        }
        return $data;
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string',
            'nis' => 'required|numeric|digits_between:1,20|unique:siswa,nis',
            'kelas' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P,l,p',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nis.unique' => 'NIS :input sudah terdaftar.',
            'jenis_kelamin.in' => 'Jenis kelamin harus L atau P.',
        ];
    }
}
