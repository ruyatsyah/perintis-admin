@extends('layouts.app', ['title' => 'Siswa'])

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Header: Filter & Actions -->
    <div class="p-6 border-b border-gray-100 flex flex-col lg:flex-row lg:items-end justify-between space-y-4 lg:space-y-0">
        <!-- Filter -->
        <form action="{{ route('admin.siswa.index') }}" method="GET" class="w-full lg:w-auto">
            <div class="w-full md:w-64">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Filter Kelas</label>
                <select name="kelas_id" onchange="this.form.submit()" class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition">
                    <option value="">Semua Kelas</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <!-- Actions -->
        <div class="flex flex-wrap gap-2 w-full lg:w-auto">
            <button onclick="openImportModal()" class="flex-1 lg:flex-none px-3 py-1.5 bg-green-600 text-white text-xs font-bold rounded-lg hover:bg-green-700 transition flex items-center justify-center">
                <span class="mr-1.5 text-sm">📥</span> Import
            </button>
            <a href="{{ route('admin.siswa.export', ['kelas_id' => request('kelas_id')]) }}" class="flex-1 lg:flex-none px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition flex items-center justify-center">
                <span class="mr-1.5 text-sm">📤</span> Export
            </a>
            <button onclick="openAddModal()" class="flex-1 lg:flex-none px-3 py-1.5 bg-primary-600 text-white text-xs font-bold rounded-lg hover:bg-primary-700 transition flex items-center justify-center">
                <span class="mr-1.5 text-sm">➕</span> Tambah
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto px-6 py-4">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase w-12 text-center">#</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase">NIS</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase">Nama Lengkap</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase">Kelas</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase">L/P</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($siswa as $item)
                <tr class="odd:bg-white even:bg-slate-50/80 hover:bg-primary-50/30 transition">
                    <td class="px-4 py-2 text-xs text-gray-900 text-center">
                        {{ ($siswa->currentPage() - 1) * $siswa->perPage() + $loop->iteration }}
                    </td>
                    <td class="px-4 py-2 text-xs font-mono text-gray-600">{{ $item->nis }}</td>
                    <td class="px-4 py-2 text-xs font-medium text-gray-900">{{ $item->nama }}</td>
                    <td class="px-4 py-2 text-xs text-gray-600">{{ $item->kelas->nama_kelas }}</td>
                    <td class="px-4 py-2 text-xs text-gray-600 font-medium text-center">
                        {{ $item->jenis_kelamin }}
                    </td>
                    <td class="px-4 py-2 text-right space-x-1">
                        <button onclick="editSiswa({{ json_encode($item) }})" class="p-1.5 text-primary-600 hover:bg-gray-100 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <form id="delete-siswa-{{ $item->id }}" action="{{ route('admin.siswa.destroy', $item->id) }}" method="POST" hx-boost="true" hx-target="#page-content" hx-select="#page-content" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" 
                                @click="openConfirm({
                                    title: 'Hapus Siswa',
                                    message: 'Apakah Anda yakin ingin menghapus data siswa ini? Semua data absensi terkait juga akan terhapus.',
                                    confirmLabel: 'Ya, Hapus Data',
                                    type: 'danger',
                                    formId: 'delete-siswa-{{ $item->id }}'
                                })"
                                class="p-1.5 text-red-600 hover:bg-gray-100 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-xs text-gray-500">Belum ada data siswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-6 bg-gray-50 border-t border-gray-100">
        {{ $siswa->links() }}
    </div>
</div>

<!-- Modal Add/Edit -->
<div id="siswaModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-primary-600 text-white">
            <h3 class="font-bold text-lg" id="modalTitle">Tambah Siswa</h3>
            <button onclick="closeModal('siswaModal')" class="text-white/80 hover:text-white">&times;</button>
        </div>
        <form id="siswaForm" method="POST" hx-boost="true" hx-target="#page-content" hx-select="#page-content" class="p-6 space-y-3">
            @csrf
            <div id="methodField"></div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" id="form_nama" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">NIS</label>
                <input type="text" name="nis" id="form_nis" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition" placeholder="12345678">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Kelas</label>
                <select name="kelas_id" id="form_kelas_id" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition">
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                <select name="jenis_kelamin" id="form_jenis_kelamin" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-primary-500 transition">
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <button type="submit" class="w-full py-2.5 bg-primary-600 text-white text-xs font-bold rounded-lg shadow-lg shadow-primary-100 transition">Simpan Data</button>
        </form>
    </div>
</div>

<!-- Modal Import -->
<div id="importModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-green-600 text-white">
            <h3 class="font-bold text-lg">Import Siswa via Excel</h3>
            <button onclick="closeModal('importModal')" class="text-white/80 hover:text-white">&times;</button>
        </div>
        <form action="{{ route('admin.siswa.import') }}" method="POST" enctype="multipart/form-data" hx-boost="true" hx-target="#page-content" hx-select="#page-content" class="p-6 space-y-6">
            @csrf
            <div class="p-4 bg-blue-50 text-blue-700 rounded-lg text-sm">
                <p class="font-bold mb-2">Format Excel:</p>
                <p>Kolom Heading: <code class="bg-blue-100 px-1 rounded font-bold text-blue-900">nama, nis, kelas, jenis_kelamin</code></p>
                <p class="mt-2 text-xs italic">Contoh kelas: "X TKJ 1"</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Pilih File (.xlsx / .csv)</label>
                <input type="file" name="file" required class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
            </div>
            <button type="submit" class="w-full py-3 bg-green-600 text-white font-bold rounded-lg shadow-lg">Mulai Import</button>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('siswaModal').classList.remove('hidden');
    document.getElementById('modalTitle').innerText = 'Tambah Siswa Baru';
    document.getElementById('siswaForm').action = "{{ route('admin.siswa.store') }}";
    document.getElementById('methodField').innerHTML = "";
    document.getElementById('siswaForm').reset();
}

function editSiswa(data) {
    document.getElementById('siswaModal').classList.remove('hidden');
    document.getElementById('modalTitle').innerText = 'Edit Data Siswa';
    document.getElementById('siswaForm').action = `/admin/siswa/${data.id}`;
    document.getElementById('methodField').innerHTML = '@method("PUT")';
    
    document.getElementById('form_nama').value = data.nama;
    document.getElementById('form_nis').value = data.nis;
    document.getElementById('form_kelas_id').value = data.kelas_id;
    document.getElementById('form_jenis_kelamin').value = data.jenis_kelamin;
}

function openImportModal() {
    document.getElementById('importModal').classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}
</script>
@endsection

