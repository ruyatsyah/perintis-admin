<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Siswa;
use App\Models\Kelas;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SiswaImport;
use App\Exports\SiswaExport;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        // Persistence: Get from request or session
        $kelas_id = $request->input('kelas_id') ?? session('siswa_filter_kelas');
        
        // Store if provided in request
        if ($request->has('kelas_id')) {
            session(['siswa_filter_kelas' => $request->kelas_id]);
        }

        $query = Siswa::select('siswa.*')
                        ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
                        ->with('kelas.jurusan');

        if ($kelas_id) {
            $query->where('siswa.kelas_id', $kelas_id);
        }

        $siswa = $query->orderBy('kelas.tingkat', 'asc')
                       ->orderBy('kelas.nama_kelas', 'asc')
                       ->orderBy('siswa.nama', 'asc')
                       ->paginate(50);
        $kelas = Kelas::with('jurusan')->get();

        return view('admin.siswa.index', compact('siswa', 'kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'nis' => 'required|numeric|digits_between:1,20|unique:siswa,nis',
            'kelas_id' => 'required|exists:kelas,id',
            'jenis_kelamin' => 'required|in:L,P',
        ]);

        Siswa::create($request->all());
        return back()->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function update(Request $request, Siswa $siswa)
    {
        $request->validate([
            'nama' => 'required|string',
            'nis' => 'required|string|unique:siswa,nis,' . $siswa->id,
            'kelas_id' => 'required|exists:kelas,id',
            'jenis_kelamin' => 'required|in:L,P',
        ]);

        $siswa->update($request->all());
        return back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return back()->with('success', 'Siswa berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new SiswaImport, $request->file('file'));
            return back()->with('success', 'Data siswa berhasil diimport.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        return Excel::download(new SiswaExport($request->kelas_id), 'data_siswa.xlsx');
    }
}
