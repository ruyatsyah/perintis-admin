<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;

class PetugasController extends Controller
{
    public function index()
    {
        $petugas = User::where('role', 'petugas')->with('kelas')->get();
        $kelas = Kelas::all();
        return view('admin.petugas.index', compact('petugas', 'kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'petugas',
            'kelas_id' => $request->kelas_id,
        ]);

        return back()->with('success', 'Petugas absensi berhasil ditambahkan.');
    }

    public function update(Request $request, User $petuga)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $petuga->id,
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'kelas_id' => $request->kelas_id,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $data['password'] = Hash::make($request->password);
        }

        $petuga->update($data);

        return back()->with('success', 'Data petugas berhasil diperbarui.');
    }

    public function destroy(User $petuga)
    {
        $petuga->delete();
        return back()->with('success', 'Petugas berhasil dihapus.');
    }
}
