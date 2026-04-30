<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Absensi;
use App\Models\AbsensiDetail;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\HariLibur;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    private function getLiburStatus($date)
    {
        $carbonDate = Carbon::parse($date);
        
        // Cek akhir pekan (Sabtu / Minggu)
        if ($carbonDate->isWeekend()) {
            $hari = $carbonDate->translatedFormat('l'); // 'Sabtu' atau 'Minggu'
            return ['isLibur' => true, 'keterangan' => "Akhir Pekan ($hari)"];
        }

        // Cek hari libur nasional
        $libur = HariLibur::where('tanggal', $date)->first();
        if ($libur) {
            return ['isLibur' => true, 'keterangan' => $libur->keterangan];
        }

        return ['isLibur' => false, 'keterangan' => null];
    }

    public function index()
    {
        $petugas = auth()->user();
        $kelas = Kelas::find($petugas->kelas_id);
        
        $today = Carbon::today()->toDateString();
        $absensi = Absensi::with(['details.siswa'])
                          ->where('kelas_id', $kelas->id)
                          ->where('tanggal', $today)
                          ->first();

        $tidakHadir = $absensi
            ? $absensi->details->whereIn('status', ['S', 'I', 'A'])->sortBy('status')
            : collect();

        $liburStatus = $this->getLiburStatus($today);

        return view('petugas.index', compact('kelas', 'absensi', 'today', 'tidakHadir', 'liburStatus'));
    }

    public function create()
    {
        $petugas = auth()->user();
        $kelas = Kelas::with('siswa')->find($petugas->kelas_id);
        
        $today = Carbon::today()->toDateString();
        
        // Cegah akses paksa via URL jika hari libur
        $liburStatus = $this->getLiburStatus($today);
        if ($liburStatus['isLibur']) {
            return redirect()->route('petugas.index')->with('error', 'Tidak dapat mengisi absensi. Hari ini libur: ' . $liburStatus['keterangan']);
        }

        $absensi = Absensi::with('details')->where('kelas_id', $kelas->id)
                          ->where('tanggal', $today)
                          ->first();

        $siswa = Siswa::where('kelas_id', $kelas->id)->orderBy('nama')->get();

        return view('petugas.create', compact('kelas', 'siswa', 'absensi', 'today'));
    }

    public function store(Request $request)
    {
        $petugas = auth()->user();
        $today = Carbon::today()->toDateString();

        // Cegah post paksa via CURL/Postman jika hari libur
        $liburStatus = $this->getLiburStatus($today);
        if ($liburStatus['isLibur']) {
            return redirect()->route('petugas.index')->with('error', 'Gagal menyimpan absensi. Hari ini libur: ' . $liburStatus['keterangan']);
        }

        $request->validate([
            'status' => 'required|array',
            'status.*' => 'required|in:H,S,I,A',
        ]);

        $absensi = Absensi::updateOrCreate(
            ['kelas_id' => $petugas->kelas_id, 'tanggal' => $today],
            ['petugas_id' => $petugas->id]
        );

        foreach ($request->status as $siswa_id => $status) {
            AbsensiDetail::updateOrCreate(
                ['absensi_id' => $absensi->id, 'siswa_id' => $siswa_id],
                ['status' => $status]
            );
        }

        return redirect()->route('petugas.index')->with('success', 'Absensi hari ini berhasil disimpan.');
    }
}
