@extends('layouts.app', ['title' => 'Jurusan'])

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Form Tambah -->
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 h-fit">
        <h3 class="text-lg font-bold text-gray-900 mb-6">Tambah Jurusan News</h3>
        <form action="{{ route('admin.jurusan.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Nama Jurusan</label>
                <input type="text" name="nama" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition" placeholder="Contoh: Teknik Komputer">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Kode Jurusan</label>
                <input type="text" name="kode" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition" placeholder="Contoh: TKJ">
            </div>
            <button type="submit" class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-lg shadow-lg shadow-primary-100 transition">
                Simpan Jurusan
            </button>
        </form>
    </div>

    <!-- Tabel Data -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex justify-between items-center mb-6 p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Daftar Jurusan</h3>
        </div>
        <div class="overflow-x-auto px-6 py-4">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase w-12 text-center">#</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase">Jurusan</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase">Kode</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($jurusan as $index => $item)
                    <tr class="odd:bg-white even:bg-slate-50/80 hover:bg-primary-50/30 transition">
                        <td class="px-4 py-2 text-xs text-gray-900 text-center">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 text-xs font-medium text-gray-900">{{ $item->nama }}</td>
                        <td class="px-4 py-2 text-xs text-gray-600 font-bold uppercase">{{ $item->kode }}</td>
                        <td class="px-4 py-2 text-right space-x-1">
                            <button onclick="editJurusan({{ json_encode($item) }})" class="p-1.5 text-primary-600 hover:bg-gray-100 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <form id="delete-jurusan-{{ $item->id }}" action="{{ route('admin.jurusan.destroy', $item->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" 
                                    @click="openConfirm({
                                        title: 'Hapus Jurusan',
                                        message: 'Apakah Anda yakin ingin menghapus jurusan ini? Data kelas dan siswa terkait mungkin akan terpengaruh.',
                                        confirmLabel: 'Ya, Hapus Jurusan',
                                        type: 'danger',
                                        formId: 'delete-jurusan-{{ $item->id }}'
                                    })"
                                    class="p-1.5 text-red-600 hover:bg-gray-100 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada data jurusan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Placeholder (Simple implementation with JS) -->
<div id="editModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-primary-600 text-white">
            <h3 class="font-bold text-lg">Edit Jurusan</h3>
            <button onclick="closeModal()" class="text-white/80 hover:text-white">&times;</button>
        </div>
        <form id="editForm" method="POST" class="p-6 space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Nama Jurusan</label>
                <input type="text" name="nama" id="edit_nama" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Kode Jurusan</label>
                <input type="text" name="kode" id="edit_kode" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition">
            </div>
            <button type="submit" class="w-full py-2.5 bg-primary-600 text-white text-xs font-bold rounded-lg shadow-lg shadow-primary-100 transition">Update Jurusan</button>
        </form>
    </div>
</div>

<script>
function editJurusan(data) {
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('edit_nama').value = data.nama;
    document.getElementById('edit_kode').value = data.kode;
    document.getElementById('editForm').action = `/admin/jurusan/${data.id}`;
}

function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endsection

