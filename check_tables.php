<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['users', 'jurusan', 'kelas', 'siswa', 'absensi', 'absensi_detail', 'hari_liburs'];
foreach ($tables as $table) {
    try {
        $count = DB::connection('sqlite')->table($table)->count();
        echo "Table $table exists and has $count records.\n";
    } catch (\Exception $e) {
        echo "Table $table does NOT exist or error: " . $e->getMessage() . "\n";
    }
}
