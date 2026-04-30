@extends('layouts.app', ['title' => 'Absensi Kelas'])

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 bg-primary-600 text-white">
            <h2 class="text-3xl font-bold">{{ $kelas->nama_kelas }}</h2>
            <p class="mt-2 text-primary-100 italic">{{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}</p>
        </div>
        
        <div class="p-8">
            @if($absensi)
                <div class="flex items-center justify-between mb-8 p-4 bg-green-50 text-green-700 border border-green-100 rounded-xl">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">✅</span>
                        <div>
                            <p class="font-bold">Absensi Selesai</p>
                            <p class="text-sm">Data kehadiran telah disimpan pada {{ $absensi->updated_at->format('H:i') }} WIB</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    @php
                        $stats = $absensi->details()->select('status', \DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status')->toArray();
                    @endphp
                    <div class="p-4 bg-gray-50 rounded-xl text-center">
                        <div class="text-xs font-bold text-gray-400 uppercase mb-1">Hadir</div>
                        <div class="text-xl font-bold text-green-600">{{ $stats['H'] ?? 0 }}</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl text-center">
                        <div class="text-xs font-bold text-gray-400 uppercase mb-1">Sakit</div>
                        <div class="text-xl font-bold text-yellow-600">{{ $stats['S'] ?? 0 }}</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl text-center">
                        <div class="text-xs font-bold text-gray-400 uppercase mb-1">Izin</div>
                        <div class="text-xl font-bold text-blue-600">{{ $stats['I'] ?? 0 }}</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl text-center">
                        <div class="text-xs font-bold text-gray-400 uppercase mb-1">Alpha</div>
                        <div class="text-xl font-bold text-red-600">{{ $stats['A'] ?? 0 }}</div>
                    </div>
                </div>

                @if($tidakHadir->isNotEmpty())
                <div class="mb-6">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Siswa Tidak Hadir</h4>
                    <div class="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden">
                        @foreach($tidakHadir as $detail)
                        @php
                            $statusMap = [
                                'S' => ['label' => 'Sakit', 'bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'badge' => 'bg-yellow-100 text-yellow-700'],
                                'I' => ['label' => 'Izin',  'bg' => 'bg-blue-50',   'text' => 'text-blue-700',   'badge' => 'bg-blue-100 text-blue-700'],
                                'A' => ['label' => 'Alpha', 'bg' => 'bg-red-50',    'text' => 'text-red-700',    'badge' => 'bg-red-100 text-red-700'],
                            ];
                            $s = $statusMap[$detail->status] ?? ['label' => $detail->status, 'bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'badge' => 'bg-gray-100 text-gray-700'];
                        @endphp
                        <div class="flex items-center justify-between px-4 py-2.5 {{ $s['bg'] }}">
                            <span class="text-sm text-gray-900">{{ $detail->siswa->nama }}</span>
                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full {{ $s['badge'] }}">{{ $s['label'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!isset($liburStatus) || !$liburStatus['isLibur'])
                <div class="flex flex-col space-y-3 mt-4">
                    <a wire:navigate.hover href="{{ route('petugas.create', $kelas->id) }}" class="w-full py-4 bg-white border-2 border-primary-600 text-primary-600 font-bold rounded-xl hover:bg-primary-50 transition text-center">
                        Edit Absensi Hari Ini
                    </a>
                </div>
                @else
                <div class="mt-6 p-4 bg-red-50 text-red-700 border border-red-100 rounded-xl text-center">
                    <p class="font-bold">HARI LIBUR: {{ $liburStatus['keterangan'] }}</p>
                    <p class="text-xs mt-1">Data absensi tidak dapat diubah karena hari ini telah ditandai sebagai hari libur.</p>
                </div>
                @endif
            @else
                @if(isset($liburStatus) && $liburStatus['isLibur'])
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">🏖️</div>
                        <h3 class="text-xl font-bold text-red-600 uppercase tracking-widest">Hari Libur</h3>
                        <p class="text-gray-900 font-bold mt-2">{{ $liburStatus['keterangan'] }}</p>
                        <p class="text-gray-500 mt-2">Tidak ada pengisian absensi untuk hari ini.</p>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">📝</div>
                        <h3 class="text-xl font-bold text-gray-900">Belum Ada Absensi</h3>
                        <p class="text-gray-500 mt-2 mb-8">Silakan isi daftar kehadiran siswa hari ini.</p>
                        
                        <a wire:navigate.hover href="{{ route('petugas.create', $kelas->id) }}" class="inline-block px-12 py-4 bg-primary-600 text-white font-bold rounded-xl shadow-lg shadow-primary-200 hover:bg-primary-700 transition transform active:scale-95">
                            Mulai Absensi Sekarang
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Info/Tips -->
    <div class="mt-8 p-6 bg-blue-50 border border-blue-100 rounded-2xl flex items-start">
        <span class="text-xl mr-4">💡</span>
        <div>
            <h4 class="font-bold text-blue-800">Tips Petugas</h4>
            <p class="text-sm text-blue-700 mt-1">Gunakan perangkat HP untuk memudahkan input langsung di dalam kelas. Pastikan semua siswa sudah terdata sebelum mengirim.</p>
        </div>
    </div>
</div>
@endsection
