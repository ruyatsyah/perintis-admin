<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Absensi;
use App\Models\AbsensiDetail;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Siswa;
use App\Models\HariLibur;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;
use App\Exports\MonthlyBreakdownExport;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('reset')) {
            session()->forget(['report_mode', 'report_month', 'report_year', 'report_semester', 'report_jurusan_id']);
            return redirect()->route('admin.reports.index');
        }

        $filters = $this->parseFilters($request);
        
        // Persistence
        session([
            'report_mode'       => $filters['mode'],
            'report_month'      => $filters['month'],
            'report_year'       => $filters['year'],
            'report_semester'   => $filters['semester'],
            'report_jurusan_id' => $filters['jurusan_id'],
        ]);

        $startDate = $filters['start_date'];
        $endDate = $filters['end_date'];

        // Calculate Hari Efektif (Exclude Weekends & Holidays)
        $hariEfektif = $this->calculateHariEfektif($startDate, $endDate);
        $filters['hari_efektif'] = $hariEfektif;

        $jurusan = Jurusan::orderBy('nama')->get();
        $query = Kelas::with('jurusan')->withCount('siswa');

        if (!empty($filters['jurusan_id'])) {
            $query->where('jurusan_id', $filters['jurusan_id']);
        }

        $records = $query->orderBy('jurusan_id')
                         ->orderBy('nama_kelas')
                         ->paginate(50);

        // For each record, calculate stats
        foreach ($records as $rec) {
            $stats = $this->getClassStats($rec->id, $startDate, $endDate);
            $rec->hadir_count = $stats['hadir'];
            $rec->sakit_count = $stats['sakit'];
            $rec->izin_count  = $stats['izin'];
            $rec->alfa_count  = $stats['alfa'];
            
            // Percentage: (Total Hadir / (Total Siswa * Hari Efektif)) * 100
            $totalPossible = $rec->siswa_count * $hariEfektif;
            $rec->kehadiran_persen = $totalPossible > 0 
                ? round(($stats['hadir'] / $totalPossible) * 100, 1) 
                : 0;
        }

        return view('admin.reports.index', compact('jurusan', 'records', 'filters'));
    }

    protected function parseFilters(Request $request)
    {
        $mode = $request->input('mode', session('report_mode', 'bulanan'));
        $month = $request->input('month', session('report_month', date('n')));
        $year = $request->input('year', session('report_year', date('Y')));
        $semester = $request->input('semester', session('report_semester', (date('n') > 6 ? 1 : 2)));
        $jurusan_id = $request->input('jurusan_id', session('report_jurusan_id'));

        // If request has explicit dates, use them (useful for direct export links)
        if ($request->has(['start_date', 'end_date'])) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            // Ensure Y-m-d format if they come with time
            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate = Carbon::parse($endDate)->format('Y-m-d');
        } else {
            // Determine Start & End Dates from mode/month/year
            if ($mode === 'semester') {
                if ($semester == 1) {
                    $startDate = Carbon::create($year, 7, 1)->format('Y-m-d');
                    $endDate = Carbon::create($year, 12, 31)->format('Y-m-d');
                } else {
                    $startDate = Carbon::create($year, 1, 1)->format('Y-m-d');
                    $endDate = Carbon::create($year, 6, 30)->format('Y-m-d');
                }
            } else {
                $startDate = Carbon::create($year, $month, 1)->format('Y-m-d');
                $endDate = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
            }
        }

        return [
            'mode' => $mode,
            'month' => $month,
            'year' => $year,
            'semester' => $semester,
            'jurusan_id' => $jurusan_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    protected function calculateHariEfektif($start, $end)
    {
        $period = CarbonPeriod::create($start, $end);
        $holidays = HariLibur::whereBetween('tanggal', [$start, $end])->pluck('tanggal')->toArray();
        
        $count = 0;
        foreach ($period as $date) {
            // Exclude Sat (6) and Sun (0)
            if ($date->isWeekend()) {
                continue;
            }
            // Exclude Holidays
            if (in_array($date->format('Y-m-d'), $holidays)) {
                continue;
            }
            $count++;
        }
        return $count;
    }

    protected function getClassStats($kelas_id, $start, $end)
    {
        return AbsensiDetail::join('siswa', 'absensi_detail.siswa_id', '=', 'siswa.id')
            ->where('siswa.kelas_id', $kelas_id)
            ->whereHas('absensi', function($q) use ($start, $end) {
                $q->whereBetween('tanggal', [$start, $end]);
            })
            ->selectRaw('
                SUM(CASE WHEN status = "H" THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = "S" THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status = "I" THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status = "A" THEN 1 ELSE 0 END) as alfa
            ')
            ->first()
            ->toArray();
    }

    public function exportClass(Request $request, $kelas, $type)
    {
        $kelas_id = $kelas;
        $kelas = Kelas::with('jurusan')->findOrFail($kelas_id);
        
        // Use consistent filter parsing
        $filters = $this->parseFilters($request);
        $filters['kelas_id'] = $kelas_id;

        if ($type === 'excel') {
            $filename = 'rekap_absensi_' . str_replace(' ', '_', $kelas->nama_kelas) . '_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new MonthlyBreakdownExport($filters), $filename);
        }

        if ($type === 'pdf' || $type === 'preview') {
            $query = Siswa::where('kelas_id', $kelas_id)->orderBy('nama');

            $dateFilter = function($q) use ($filters) {
                $q->whereHas('absensi', function($q) use ($filters) {
                    $q->whereBetween('tanggal', [$filters['start_date'], $filters['end_date']]);
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

            $siswa = $query->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.pdf', compact('kelas', 'siswa', 'filters'))
                    ->setPaper('a4', 'portrait');
            
            $filename = 'rekap_absensi_' . str_replace(' ', '_', $kelas->nama_kelas) . '.pdf';

            if ($type === 'preview') {
                return $pdf->stream($filename);
            }

            return $pdf->download($filename);
        }

        abort(404);
    }

    public function export(Request $request)
    {
        $filters = $this->parseFilters($request);
        $filename = 'rekap_absensi_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new MonthlyBreakdownExport($filters), $filename);
    }

    public function exportAllPdf(Request $request)
    {
        $filters = $this->parseFilters($request);

        $kelasQuery = Kelas::with('jurusan')->orderBy('jurusan_id')->orderBy('nama_kelas');

        if ($filters['jurusan_id']) {
            $kelasQuery->where('jurusan_id', $filters['jurusan_id']);
        }

        $kelasList = $kelasQuery->get();
        $allData = [];

        foreach ($kelasList as $kelas) {
            $siswaQuery = Siswa::where('kelas_id', $kelas->id)->orderBy('nama');

            $dateFilter = function($q) use ($filters) {
                $q->whereHas('absensi', function($q) use ($filters) {
                    $q->whereBetween('tanggal', [$filters['start_date'], $filters['end_date']]);
                });
            };

            $siswaQuery->withCount([
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

            $allData[] = [
                'kelas' => $kelas,
                'siswa' => $siswaQuery->get()
            ];
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.pdf_all', compact('allData', 'filters'))
                ->setPaper('a4', 'portrait');
        
        $filename = 'rekap_absensi_semua_kelas_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }
}
