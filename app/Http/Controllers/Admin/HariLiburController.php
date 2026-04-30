<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HariLibur;
use Carbon\Carbon;

class HariLiburController extends Controller
{
    public function index()
    {
        $libur = HariLibur::orderBy('tanggal', 'desc')->get();
        return view('admin.hari_libur.index', compact('libur'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:hari_libur,tanggal',
            'keterangan' => 'required|string|max:255',
        ]);

        HariLibur::create($request->only('tanggal', 'keterangan'));

        return back()->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function update(Request $request, HariLibur $hari_libur)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:hari_libur,tanggal,' . $hari_libur->id,
            'keterangan' => 'required|string|max:255',
        ]);

        $hari_libur->update($request->only('tanggal', 'keterangan'));

        return back()->with('success', 'Hari libur berhasil diperbarui.');
    }

    public function destroy(HariLibur $hari_libur)
    {
        $hari_libur->delete();
        return back()->with('success', 'Hari libur berhasil dihapus.');
    }
}
