<?php

namespace App\Exports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $filters = $this->filters;
        $query = Siswa::select('siswa.*')
            ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->join('jurusan', 'kelas.jurusan_id', '=', 'jurusan.id')
            ->with(['kelas.jurusan']);

        // Filter kelas/jurusan
        if (!empty($filters['kelas_id'])) {
            $query->where('siswa.kelas_id', $filters['kelas_id']);
        } elseif (!empty($filters['jurusan_id'])) {
            $query->where('kelas.jurusan_id', $filters['jurusan_id']);
        }

        // Filter tanggal untuk agregat
        $dateFilter = function($q) use ($filters) {
            $q->whereHas('absensi', function($q) use ($filters) {
                if (!empty($filters['start_date'])) {
                    $q->whereDate('tanggal', '>=', $filters['start_date']);
                }
                if (!empty($filters['end_date'])) {
                    $q->whereDate('tanggal', '<=', $filters['end_date']);
                }
            });
        };

        $query->withCount([
            'absensi_details as total_hadir' => function($q) use ($dateFilter) {
                $q->where('status', 'H');
                $dateFilter($q);
            },
            'absensi_details as total_sakit' => function($q) use ($dateFilter) {
                $q->where('status', 'S');
                $dateFilter($q);
            },
            'absensi_details as total_izin' => function($q) use ($dateFilter) {
                $q->where('status', 'I');
                $dateFilter($q);
            },
            'absensi_details as total_alfa' => function($q) use ($dateFilter) {
                $q->where('status', 'A');
                $dateFilter($q);
            }
        ]);

        // Urutkan berdasarkan Jurusan -> Kelas (10,11,12) -> Nama Siswa
        $students = $query->orderBy('jurusan.nama')
                          ->orderBy('kelas.nama_kelas')
                          ->orderBy('siswa.nama')
                          ->get();

        $rows = new Collection();
        $currentKelasId = null;
        $rowNumber = 0;

        $columnHeaders = [
            'No',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Jumlah Hari Aktif',
            'Hadir',
            'Sakit',
            'Izin',
            'Alfa',
        ];

        foreach ($students as $siswa) {
            // Jika kelas berubah, buat blok baru
            if ($currentKelasId !== $siswa->kelas_id) {
                if ($currentKelasId !== null) {
                    $rows->push(['', '', '', '', '', '', '', '', '']); // Pemisah 1
                    $rows->push(['', '', '', '', '', '', '', '', '']); // Pemisah 2
                }

                // Baris Nama Kelas (Sub-judul)
                $namaKelas = $siswa->kelas->nama_kelas ?? '-';
                $rows->push(['KELAS: ' . strtoupper($namaKelas), '', '', '', '', '', '', '', '']);
                
                // Baris Header Kolom
                $rows->push($columnHeaders);
                
                // Opsional: reset nomor urut per kelas
                $rowNumber = 0; 
            }

            $currentKelasId = $siswa->kelas_id;
            $rowNumber++;

            $hariAktif = $siswa->total_hadir + $siswa->total_sakit + $siswa->total_izin + $siswa->total_alfa;

            $rows->push([
                $rowNumber,
                $siswa->nis,
                $siswa->nama,
                $siswa->kelas->nama_kelas ?? '-',
                $hariAktif,
                $siswa->total_hadir,
                $siswa->total_sakit,
                $siswa->total_izin,
                $siswa->total_alfa,
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        $title = 'REKAP ABSENSI';
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $start = \Carbon\Carbon::parse($this->filters['start_date'])->translatedFormat('d F Y');
            $end = \Carbon\Carbon::parse($this->filters['end_date'])->translatedFormat('d F Y');
            $title = 'REKAP ABSENSI PERIODE ' . strtoupper($start . ' - ' . $end);
        } elseif (!empty($this->filters['start_date'])) {
            $start = \Carbon\Carbon::parse($this->filters['start_date'])->translatedFormat('F Y');
            $title = 'REKAP ABSENSI BULAN ' . strtoupper($start);
        }

        return [
            [$title],
            [], // Baris kosong untuk jarak
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // Merge cell untuk judul di baris 1 (A1 sampai I1)
        $sheet->mergeCells('A1:I1');
        
        // Style untuk Judul Utama
        $sheet->getStyle('A1:I1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E40AF'); // Biru tua elegan
        $sheet->getStyle('A1:I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFF6FF'); // Biru sangat muda (Blue-50)
        $sheet->getStyle('A1:I1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:I1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        
        // Loop untuk styling dinamis per blok kelas
        for ($row = 2; $row <= $highestRow; $row++) {
            $valA = trim((string) $sheet->getCell('A' . $row)->getValue());
            $valB = trim((string) $sheet->getCell('B' . $row)->getValue());
            
            // Deteksi Sub-Judul Kelas (Kolom A ada isi KELAS:, atau Kolom B kosong)
            if ($valA !== '' && $valB === '' && str_contains(strtoupper($valA), 'KELAS')) {
                // Style untuk Sub-judul Kelas
                $sheet->mergeCells("A{$row}:I{$row}");
                $sheet->getStyle("A{$row}:I{$row}")->getFont()->setBold(true)->getColor()->setARGB('FF065F46'); // Hijau tua (Emerald-800)
                $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD1FAE5'); // Hijau muda (Emerald-100)
                $sheet->getStyle("A{$row}:I{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$row}:I{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            } 
            elseif ($valA === 'No' && $valB === 'NIS') {
                // Style untuk Header Kolom (Hitam Pekat)
                $sheet->getStyle("A{$row}:I{$row}")->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
                $sheet->getStyle("A{$row}:I{$row}")->getFill()
                      ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FF000000'); // Hitam Pekat
            }
            
            // Berikan border tipis ke baris yang ada datanya (bukan baris pemisah kosong)
            if ($valA !== null && $valA !== '') {
                $sheet->getStyle("A{$row}:I{$row}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        }
        
        // Center alignment untuk beberapa kolom angka & No
        $sheet->getStyle('A3:A' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E3:I' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }
}
