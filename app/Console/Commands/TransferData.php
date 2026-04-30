<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransferData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:transfer-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer data from MySQL to PostgreSQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tables = [
            'users',
            'jurusan',
            'kelas',
            'siswa',
            'hari_liburs',
            'absensi',
            'absensi_detail'
        ];

        foreach ($tables as $table) {
            $this->info("Migrating table: $table");
            
            // Delete existing data to prevent unique constraint errors
            DB::connection('pgsql')->table($table)->delete();
            
            // Get records from old db
            $records = DB::connection('sqlite')->table($table)->get();
            
            $data = [];
            foreach ($records as $record) {
                $data[] = (array) $record;
            }
            
            if (count($data) > 0) {
                $chunks = array_chunk($data, 500);
                foreach ($chunks as $chunk) {
                    DB::connection('pgsql')->table($table)->insert($chunk);
                }
            }
            $this->info("Migrated " . count($data) . " records for $table.");
        }

        $this->info("Migration complete.");
    }
}
