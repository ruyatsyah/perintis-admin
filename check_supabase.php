<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['users', 'jurusan', 'kelas', 'siswa', 'absensi', 'absensi_detail', 'hari_liburs'];
foreach ($tables as $table) {
    try {
        $count = DB::connection('pgsql')->table($table)->count();
        echo "Table $table in Supabase has $count records.\n";
    } catch (\Exception $e) {
        echo "Error checking table $table in Supabase: " . $e->getMessage() . "\n";
    }
}
