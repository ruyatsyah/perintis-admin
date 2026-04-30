@extends('layouts.app', ['title' => 'Input Kehadiran'])

@section('content')
<div class="max-w-4xl mx-auto pb-20">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 bg-primary-600 text-white flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold">{{ $kelas->nama_kelas }}</h2>
                <p class="text-xs text-primary-100">{{ \Carbon\Carbon::parse($today)->translatedFormat('d F Y') }}</p>
            </div>
            <div class="text-right">
                <span class="text-xs uppercase font-bold tracking-wider">Total Siswa</span>
                <p class="text-2xl font-bold">{{ $siswa->count() }}</p>
            </div>
        </div>

        <!-- Instant Search -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 sticky top-0 z-10">
            <div class="relative" x-data="{ search: '' }">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" 
                    placeholder="Cari nama atau NIS siswa..." 
                    class="block w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm"
                    @input="$dispatch('search-siswa', $event.target.value)"
                >
            </div>
        </div>

        <form action="{{ route('petugas.store') }}" method="POST" hx-boost="true" hx-target="#page-content" hx-select="#page-content">
            @csrf
            
            <div class="divide-y divide-gray-100" x-data="{ search: '' }" @search-siswa.window="search = $event.detail.toLowerCase()">
                @foreach($siswa as $s)
                <div class="p-4 md:p-6 hover:bg-gray-50 transition" x-show="'{{ strtolower($s->nama) }}'.includes(search) || '{{ $s->nis }}'.includes(search)">
                    <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
                        <div class="flex items-center flex-1 space-x-3 md:space-x-6 mr-4 overflow-hidden">
                            <div class="text-gray-900 text-sm w-5 md:w-8 shrink-0">{{ $loop->iteration }}.</div>
                            <div class="text-gray-900 text-sm w-20 md:w-24 shrink-0">{{ $s->nis }}</div>
                            <div class="text-gray-900 text-sm flex-1 truncate">{{ $s->nama }}</div>
                        </div>

                        <!-- Status Toggles -->
                        <div class="flex items-center space-x-1 md:space-x-2" x-data="{ status: '{{ $absensi ? ($absensi->details->where('siswa_id', $s->id)->first()->status ?? 'H') : 'H' }}' }">
                            <input type="hidden" name="status[{{ $s->id }}]" x-model="status">
                            
                            <!-- Hadir -->
                            <button type="button" @click="status = 'H'" 
                                :class="status === 'H' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-400'"
                                class="flex-1 md:flex-none px-4 py-2 rounded-lg text-xs font-bold transition">
                                H
                            </button>
                            
                            <!-- Sakit -->
                            <button type="button" @click="status = 'S'" 
                                :class="status === 'S' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-400'"
                                class="flex-1 md:flex-none px-4 py-2 rounded-lg text-xs font-bold transition">
                                S
                            </button>
                            
                            <!-- Izin -->
                            <button type="button" @click="status = 'I'" 
                                :class="status === 'I' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-400'"
                                class="flex-1 md:flex-none px-4 py-2 rounded-lg text-xs font-bold transition">
                                I
                            </button>
                            
                            <!-- Alpha -->
                            <button type="button" @click="status = 'A'" 
                                :class="status === 'A' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-400'"
                                class="flex-1 md:flex-none px-4 py-2 rounded-lg text-xs font-bold transition">
                                A
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Sticky Submit Mobile -->
            <div class="fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 md:relative md:bg-transparent md:border-0 md:p-8">
                <button type="submit" class="w-full py-4 bg-primary-600 text-white font-bold rounded-xl shadow-2xl shadow-primary-200 hover:bg-primary-700 transition transform active:scale-95">
                    Simpan Absensi Hari Ini
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
