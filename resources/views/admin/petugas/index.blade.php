@extends('layouts.app', ['title' => 'Petugas'])

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Form Tambah -->
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 h-fit">
        <h3 class="text-lg font-bold text-gray-900 mb-6">Tambah Petugas Baru</h3>
        <form action="{{ route('admin.petugas.store') }}" method="POST" hx-boost="true" hx-target="#page-content" hx-select="#page-content" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Nama Petugas</label>
                <input type="text" name="name" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Hak Akses Kelas</label>
                <select name="kelas_id" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition">
                    <option value="" disabled selected>Pilih Kelas</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-[10px] text-gray-400 italic">Petugas hanya bisa menginput absensi di kelas ini.</p>
            </div>
            <button type="submit" class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-lg shadow-lg shadow-primary-100 transition">
                Simpan Petugas
            </button>
        </form>
    </div>

    <!-- Tabel Data -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Daftar Petugas</h3>
        </div>
        <div class="overflow-x-auto px-6 py-4">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase w-12 text-center">#</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase">Kelas</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($petugas as $index => $item)
                    <tr class="odd:bg-white even:bg-slate-50/80 hover:bg-primary-50/30 transition">
                        <td class="px-4 py-2 text-xs text-gray-900 text-center">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 text-xs font-medium text-gray-900">{{ $item->name }}</td>
                        <td class="px-4 py-2 text-xs text-gray-500">{{ $item->email }}</td>
                        <td class="px-4 py-2 text-xs text-gray-600 font-bold">
                            {{ $item->kelas->nama_kelas }}
                        </td>
                        <td class="px-4 py-2 text-right space-x-1">
                            <button onclick="editPetugas({{ json_encode($item) }})" class="p-1.5 text-primary-600 hover:bg-gray-100 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <form id="delete-petugas-{{ $item->id }}" action="{{ route('admin.petugas.destroy', $item->id) }}" method="POST" hx-boost="true" hx-target="#page-content" hx-select="#page-content" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" 
                                    @click="openConfirm({
                                        title: 'Hapus Petugas',
                                        message: 'Apakah Anda yakin ingin menghapus petugas ini? Petugas tersebut tidak akan bisa login lagi.',
                                        confirmLabel: 'Ya, Hapus Petugas',
                                        type: 'danger',
                                        formId: 'delete-petugas-{{ $item->id }}'
                                    })"
                                    class="p-1.5 text-red-600 hover:bg-gray-100 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada petugas absensi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-primary-600 text-white">
            <h3 class="font-bold text-lg">Edit Petugas</h3>
            <button onclick="closeModal()" class="text-white/80 hover:text-white">&times;</button>
        </div>
        <form id="editForm" method="POST" hx-boost="true" hx-target="#page-content" hx-select="#page-content" class="p-6 space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Nama Petugas</label>
                <input type="text" name="name" id="edit_name" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="edit_email" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition" placeholder="Kosongkan jika tidak ubah password">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Hak Akses Kelas</label>
                <select name="kelas_id" id="edit_kelas_id" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition">
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full py-2.5 bg-primary-600 text-white text-xs font-bold rounded-lg shadow-lg shadow-primary-100 transition">Update Petugas</button>
        </form>
    </div>
</div>

<script>
function editPetugas(data) {
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('edit_name').value = data.name;
    document.getElementById('edit_email').value = data.email;
    document.getElementById('edit_kelas_id').value = data.kelas_id;
    document.getElementById('editForm').action = `/admin/petugas/${data.id}`;
}

function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endsection

