<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class MonthlyBreakdownExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected $filters;
    protected $months;

    public function __construct($filters)
    {
        $this->filters = $filters;
        
        // Tentukan rentang bulan berdasarkan filter tanggal
        $start = !empty($filters['start_date']) ? Carbon::parse($filters['start_date'])->startOfMonth() : now()->startOfYear();
        $end = !empty($filters['end_date']) ? Carbon::parse($filters['end_date'])->endOfMonth() : now()->endOfMonth();
        
        $period = CarbonPeriod::create($start, '1 month', $end);
        $this->months = [];
        foreach ($period as $date) {
            $this->months[] = [
                'name' => $date->translatedFormat('F Y'),
                'month' => $date->month,
                'year' => $date->year,
            ];
        }
    }

    public function title(): string
    {
        return 'Rekap Bulanan';
    }

    public function collection()
    {
        $filters = $this->filters;
        $rows = new Collection();

        // Tentukan kelas mana saja yang akan diekspor
        if (!empty($filters['kelas_id'])) {
            $kelasList = Kelas::where('id', $filters['kelas_id'])->get();
        } else {
            $query = Kelas::with('jurusan')->orderBy('jurusan_id')->orderBy('nama_kelas');
            if (!empty($filters['jurusan_id'])) {
                $query->where('jurusan_id', $filters['jurusan_id']);
            }
            $kelasList = $query->get();
        }

        foreach ($kelasList as $kelas) {
            // Tambahkan baris pemisah/header kelas jika ekspor semua
            if (count($kelasList) > 1) {
                $rows->push(['']); // Spacer
                $rows->push(['KELAS: ' . strtoupper($kelas->nama_kelas) . ' (' . ($kelas->jurusan->nama ?? '-') . ')']);
            }

            $students = Siswa::where('kelas_id', $kelas->id)
                ->orderBy('nama')
                ->get();

        foreach ($students as $siswa) {
            $row = [
                $siswa->nis,
                $siswa->nama,
            ];

            $grandTotalH = 0;
            $grandTotalS = 0;
            $grandTotalI = 0;
            $grandTotalA = 0;

            foreach ($this->months as $m) {
                // Ambil rekap untuk bulan ini
                $stats = \App\Models\AbsensiDetail::where('siswa_id', $siswa->id)
                    ->whereHas('absensi', function($q) use ($m) {
                        $q->whereYear('tanggal', $m['year'])
                          ->whereMonth('tanggal', $m['month']);
                    })
                    ->selectRaw('
                        SUM(CASE WHEN status = "H" THEN 1 ELSE 0 END) as hadir,
                        SUM(CASE WHEN status = "S" THEN 1 ELSE 0 END) as sakit,
                        SUM(CASE WHEN status = "I" THEN 1 ELSE 0 END) as izin,
                        SUM(CASE WHEN status = "A" THEN 1 ELSE 0 END) as alfa
                    ')
                    ->first();

                $h = $stats->hadir ?? 0;
                $s = $stats->sakit ?? 0;
                $i = $stats->izin ?? 0;
                $a = $stats->alfa ?? 0;

                $row[] = $h;
                $row[] = $s;
                $row[] = $i;
                $row[] = $a;
                
                $grandTotalH += $h;
                $grandTotalS += $s;
                $grandTotalI += $i;
                $grandTotalA += $a;
            }

            // Total Akhir
            $row[] = $grandTotalH;
            $row[] = $grandTotalS;
            $row[] = $grandTotalI;
            $row[] = $grandTotalA;
            
            $rows->push($row);
        }
    }

        return $rows;
    }

    public function headings(): array
    {
        $namaKelas = 'SEMUA KELAS';
        $jurusan = 'SEMUA JURUSAN';

        if (!empty($this->filters['kelas_id'])) {
            $kelas = Kelas::with('jurusan')->find($this->filters['kelas_id']);
            $namaKelas = $kelas ? $kelas->nama_kelas : '';
            $jurusan = $kelas && $kelas->jurusan ? $kelas->jurusan->nama : '';
        } elseif (!empty($this->filters['jurusan_id'])) {
            $jur = \App\Models\Jurusan::find($this->filters['jurusan_id']);
            $jurusan = $jur ? $jur->nama : 'SEMUA JURUSAN';
        }

        // Baris 1: Judul
        $headers = [
            ['REKAPITULASI ABSENSI BULANAN - ' . strtoupper($namaKelas)],
            ['JURUSAN: ' . strtoupper($jurusan)],
            [], // Spacer
        ];

        // Baris 4: Header Utama (Nama Bulan)
        $mainHeader = ['NIS', 'Nama Siswa'];
        foreach ($this->months as $m) {
            $mainHeader[] = strtoupper($m['name']);
            $mainHeader[] = ''; // Empty for merging
            $mainHeader[] = ''; // Empty for merging
            $mainHeader[] = ''; // Empty for merging
        }
        $mainHeader[] = 'TOTAL AKHIR';
        $mainHeader[] = ''; // Empty for merging
        $mainHeader[] = ''; // Empty for merging
        $mainHeader[] = ''; // Empty for merging
        $headers[] = $mainHeader;

        // Baris 5: Sub Header (H, S, I, A)
        $subHeader = ['', ''];
        foreach ($this->months as $m) {
            $subHeader[] = 'H';
            $subHeader[] = 'S';
            $subHeader[] = 'I';
            $subHeader[] = 'A';
        }
        $subHeader[] = 'H';
        $subHeader[] = 'S';
        $subHeader[] = 'I';
        $subHeader[] = 'A';
        $headers[] = $subHeader;

        return $headers;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        // Style Judul Utama
        $sheet->mergeCells('A1:' . $highestCol . '1');
        $sheet->mergeCells('A2:' . $highestCol . '2');
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Style Header Tabel (Baris 4 & 5)
        // Merge cells untuk bulan
        $colIndex = 3; // Mulai dari kolom C
        foreach ($this->months as $m) {
            $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 3);
            $sheet->mergeCells("{$startCol}4:{$endCol}4");
            $colIndex += 4;
        }
        // Merge cells untuk Total Akhir
        $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
        $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 3);
        $sheet->mergeCells("{$startCol}4:{$endCol}4");

        $sheet->getStyle('A4:' . $highestCol . '5')->getFont()->setBold(true);
        $sheet->getStyle('A4:' . $highestCol . '5')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD1FAE5'); // Emerald 100
        $sheet->getStyle('A4:' . $highestCol . '5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4:' . $highestCol . '5')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Style Data
        $sheet->getStyle('A6:' . $highestCol . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('C6:' . $highestCol . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Kolom Total (4 kolom terakhir)
        $totalStartColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol) - 3;
        $totalStartCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalStartColIndex);
        
        $sheet->getStyle($totalStartCol . '4:' . $highestCol . $highestRow)->getFont()->setBold(true);
        $sheet->getStyle($totalStartCol . '4:' . $highestCol . $highestRow)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFEE2E2'); // Red 100

        return [];
    }
}
