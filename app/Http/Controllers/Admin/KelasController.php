<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Kelas;
use App\Models\Jurusan;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with('jurusan')->orderBy('tingkat')->orderBy('nama_kelas')->get();
        $jurusan = Jurusan::all();
        return view('admin.kelas.index', compact('kelas', 'jurusan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tingkat' => 'required|in:10,11,12',
            'nama_kelas' => 'required|string',
            'jurusan_id' => 'required|exists:jurusan,id',
        ]);

        Kelas::create($request->all());
        return back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, Kelas $kela)
    {
        $request->validate([
            'tingkat' => 'required|in:10,11,12',
            'nama_kelas' => 'required|string',
            'jurusan_id' => 'required|exists:jurusan,id',
        ]);

        $kela->update($request->all());
        return back()->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kela)
    {
        $kela->delete();
        return back()->with('success', 'Kelas berhasil dihapus.');
    }
}
