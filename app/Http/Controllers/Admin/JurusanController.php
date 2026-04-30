<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Jurusan;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusan = Jurusan::all();
        return view('admin.jurusan.index', compact('jurusan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'kode' => 'required|string|unique:jurusan,kode',
        ]);

        Jurusan::create($request->all());
        return back()->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $request->validate([
            'nama' => 'required|string',
            'kode' => 'required|string|unique:jurusan,kode,' . $jurusan->id,
        ]);

        $jurusan->update($request->all());
        return back()->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan)
    {
        $jurusan->delete();
        return back()->with('success', 'Jurusan berhasil dihapus.');
    }
}
