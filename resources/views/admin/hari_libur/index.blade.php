@extends('layouts.app', ['title' => 'Hari Libur Nasional'])

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Header: Actions -->
    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-red-50 text-red-900">
        <div>
            <h2 class="text-lg font-bold">Manajemen Hari Libur</h2>
            <p class="text-xs text-red-700 mt-1">Petugas tidak bisa menginput absensi pada hari libur.</p>
        </div>
        <button onclick="openAddModal()" class="px-4 py-2 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-700 transition shadow-lg shadow-red-200 flex items-center">
            <span class="mr-1.5 text-sm">➕</span> Tambah Hari Libur
        </button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto px-6 py-4">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase w-12 text-center">#</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase">Keterangan Libur</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($libur as $item)
                <tr class="odd:bg-white even:bg-slate-50/80 hover:bg-red-50/50 transition">
                    <td class="px-4 py-3 text-xs text-gray-900 text-center">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 text-xs font-bold text-red-600">
                        {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-700">{{ $item->keterangan }}</td>
                    <td class="px-4 py-3 text-right space-x-1">
                        <button onclick="editLibur({{ json_encode($item) }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <form id="delete-libur-{{ $item->id }}" action="{{ route('admin.hari_libur.destroy', $item->id) }}" method="POST" hx-boost="true" hx-target="#page-content" hx-select="#page-content" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" 
                                @click="openConfirm({
                                    title: 'Hapus Hari Libur',
                                    message: 'Apakah Anda yakin ingin menghapus data hari libur ini?',
                                    confirmLabel: 'Ya, Hapus Data',
                                    type: 'danger',
                                    formId: 'delete-libur-{{ $item->id }}'
                                })"
                                class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-xs text-gray-500">Belum ada data hari libur.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add/Edit -->
<div id="liburModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-red-600 text-white">
            <h3 class="font-bold text-lg" id="modalTitle">Tambah Hari Libur</h3>
            <button onclick="closeModal('liburModal')" class="text-white/80 hover:text-white">&times;</button>
        </div>
        <form id="liburForm" method="POST" hx-boost="true" hx-target="#page-content" hx-select="#page-content" class="p-6 space-y-4">
            @csrf
            <div id="methodField"></div>
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Tanggal Libur</label>
                <input type="date" name="tanggal" id="form_tanggal" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Keterangan (Contoh: Idul Fitri)</label>
                <input type="text" name="keterangan" id="form_keterangan" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 transition">
            </div>
            <button type="submit" class="w-full py-3 bg-red-600 text-white text-sm font-bold rounded-lg shadow-lg shadow-red-200 hover:bg-red-700 transition">Simpan Data</button>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('liburModal').classList.remove('hidden');
    document.getElementById('modalTitle').innerText = 'Tambah Hari Libur';
    document.getElementById('liburForm').action = "{{ route('admin.hari_libur.store') }}";
    document.getElementById('methodField').innerHTML = "";
    document.getElementById('liburForm').reset();
}

function editLibur(data) {
    document.getElementById('liburModal').classList.remove('hidden');
    document.getElementById('modalTitle').innerText = 'Edit Hari Libur';
    document.getElementById('liburForm').action = `/admin/hari_libur/${data.id}`;
    document.getElementById('methodField').innerHTML = '@method("PUT")';
    
    document.getElementById('form_tanggal').value = data.tanggal;
    document.getElementById('form_keterangan').value = data.keterangan;
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}
</script>
@endsection
