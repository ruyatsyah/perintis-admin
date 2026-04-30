<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin Perintis',
            'email' => 'admin@perintis.sch.id',
            'role' => 'admin',
            'password' => bcrypt('admin123'),
        ]);

        // Jurusan
        $jurusan = [
            ['nama' => 'Teknik Kendaraan Ringan', 'kode' => 'TKR'],
            ['nama' => 'Teknik Komputer dan Jaringan', 'kode' => 'TKJ'],
            ['nama' => 'Akuntansi', 'kode' => 'AKL'],
        ];

        foreach ($jurusan as $j) {
            \App\Models\Jurusan::create($j);
        }
    }
}
