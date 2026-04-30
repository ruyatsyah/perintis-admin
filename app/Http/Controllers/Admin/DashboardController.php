<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Absensi;
use App\Models\AbsensiDetail;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $last7Days = collect(range(0, 6))->map(fn($i) => now()->subDays($i)->toDateString())->reverse();

        // 1. Summary Statistics
        $totalSiswa = Siswa::count();
        $totalHadir = AbsensiDetail::whereHas('absensi', fn($q) => $q->where('tanggal', $today))
                                    ->where('status', 'H')->count();
        $presentase = $totalSiswa > 0 ? round(($totalHadir / $totalSiswa) * 100, 1) : 0;

        // 2. Class Status & Stacked Attendance
        $kelasData = Kelas::with(['siswa', 'absensi' => fn($q) => $q->where('tanggal', $today)->with('details')])
                            ->orderBy('tingkat')->orderBy('nama_kelas')->get();

        $chartStatus = [
            'labels' => [],
            'data' => []
        ];

        $chartAttendance = [
            'labels' => [],
            'hadir' => [],
            'sakit' => [],
            'izin' => [],
            'alpha' => []
        ];

        foreach ($kelasData as $k) {
            $absensi = $k->absensi->first();
            $labelN = $k->nama_kelas . ' (N=' . $k->siswa->count() . ')';
            
            $chartStatus['labels'][] = $labelN;
            $chartStatus['data'][] = $absensi ? 1 : 0;

            $chartAttendance['labels'][] = $labelN;
            if ($absensi) {
                $details = $absensi->details->groupBy('status');
                $chartAttendance['hadir'][] = $details->has('H') ? $details['H']->count() : 0;
                $chartAttendance['sakit'][] = $details->has('S') ? $details['S']->count() : 0;
                $chartAttendance['izin'][]  = $details->has('I') ? $details['I']->count() : 0;
                $chartAttendance['alpha'][] = $details->has('A') ? $details['A']->count() : 0;
            } else {
                $chartAttendance['hadir'][] = 0;
                $chartAttendance['sakit'][] = 0;
                $chartAttendance['izin'][]  = 0;
                $chartAttendance['alpha'][] = 0;
            }
        }

        // 3. 7-Day Trend
        $trendData = [
            'labels' => $last7Days->map(fn($d) => \Carbon\Carbon::parse($d)->translatedFormat('d M'))->values(),
            'data' => []
        ];

        foreach ($last7Days as $date) {
            $dayTotalSiswa = Siswa::count(); // Static for now, can be optimized if needed
            $dayHadir = AbsensiDetail::whereHas('absensi', fn($q) => $q->where('tanggal', $date))
                                      ->where('status', 'H')->count();
            $trendData['data'][] = $dayTotalSiswa > 0 ? round(($dayHadir / $dayTotalSiswa) * 100, 1) : 0;
        }

        return view('admin.dashboard', [
            'total_siswa' => $totalSiswa,
            'total_kelas' => $kelasData->count(),
            'total_hadir' => $totalHadir,
            'presentase'  => $presentase,
            'chartStatus' => $chartStatus,
            'chartAttendance' => $chartAttendance,
            'trendData' => $trendData
        ]);
    }
}
