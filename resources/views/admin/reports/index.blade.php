@extends('layouts.app')

@section('title', 'Rekap Absensi')

@section('content')
<div class="space-y-8" x-data="{ mode: '{{ $filters['mode'] }}' }">


    <!-- Filter Card -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <form id="report-filter-form" action="{{ route('admin.reports.index') }}" method="GET" class="space-y-6">
                <!-- Mode Toggle -->
                <div class="flex items-center gap-4 border-b border-gray-100 pb-4">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="radio" name="mode" value="bulanan" x-model="mode" @change="$el.form.submit()" class="sr-only">
                        <span class="px-4 py-2 rounded-full text-[13px] transition-all"
                              :class="mode === 'bulanan' ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/20' : 'bg-gray-50 text-gray-400 group-hover:bg-gray-100'">
                            Bulanan
                        </span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="radio" name="mode" value="semester" x-model="mode" @change="$el.form.submit()" class="sr-only">
                        <span class="px-4 py-2 rounded-full text-[13px] transition-all"
                              :class="mode === 'semester' ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/20' : 'bg-gray-50 text-gray-400 group-hover:bg-gray-100'">
                            Semester
                        </span>
                    </label>

                    <!-- Export & Reset Buttons -->
                    <div class="ml-auto flex items-center gap-2" x-data="{ exportOpen: false, loading: false }">
                        <!-- Dropdown Export Semua -->
                        <div class="relative">
                            <button type="button" @click="exportOpen = !exportOpen" @click.away="exportOpen = false"
                                    class="flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-full text-[13px] transition-all shadow-lg shadow-primary-500/10 active:scale-95">
                                <span x-show="!loading">Export Semua</span>
                                <svg x-show="loading" class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="exportOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="exportOpen" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-2xl bg-white shadow-xl border border-gray-100 py-2 focus:outline-none"
                                 style="display: none;" x-cloak>
                                
                                <button type="button" @click="loading = true; window.location.href = '{{ route('admin.reports.exportAllPdf') }}?' + new URLSearchParams(new FormData(document.getElementById('report-filter-form'))).toString(); setTimeout(() => { loading = false; exportOpen = false; }, 3000);"
                                        class="w-full flex items-center px-4 py-2.5 text-[13px] text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors text-left">
                                    <svg class="w-4 h-4 mr-3 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    Format PDF
                                </button>

                                <button type="button" @click="loading = true; window.location.href = '{{ route('admin.reports.export') }}?' + new URLSearchParams(new FormData(document.getElementById('report-filter-form'))).toString(); setTimeout(() => { loading = false; exportOpen = false; }, 3000);"
                                        class="w-full flex items-center px-4 py-2.5 text-[13px] text-gray-700 hover:bg-green-50 hover:text-green-700 transition-colors text-left">
                                    <svg class="w-4 h-4 mr-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    Format Excel
                                </button>
                            </div>
                        </div>

                        <a href="{{ route('admin.reports.index', ['reset' => 1]) }}" 
                           class="px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-full text-[13px] transition-all border border-gray-200">
                            Reset
                        </a>
                    </div>
                </div>

                <div class="flex flex-wrap items-end gap-6">
                    <!-- Bulanan Inputs -->
                    <div x-show="mode === 'bulanan'" class="flex flex-wrap items-end gap-4" x-cloak>
                        <div class="w-full lg:w-[200px]">
                            <label class="block text-[11px] text-gray-400 uppercase tracking-wider mb-2">Pilih Bulan</label>
                            <div class="relative">
                                <select name="month" onchange="this.form.submit()"
                                        class="w-full px-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-[13px] font-medium text-gray-700 appearance-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ $filters['month'] == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Semester Inputs -->
                    <div x-show="mode === 'semester'" class="flex flex-wrap items-end gap-4" x-cloak>
                        <div class="w-full lg:w-[200px]">
                            <label class="block text-[11px] text-gray-400 uppercase tracking-wider mb-2">Pilih Semester</label>
                            <div class="relative">
                                <select name="semester" onchange="this.form.submit()"
                                        class="w-full px-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-[13px] font-medium text-gray-700 appearance-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer">
                                    <option value="1" {{ $filters['semester'] == 1 ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                                    <option value="2" {{ $filters['semester'] == 2 ? 'selected' : '' }}>Semester 2 (Genap)</option>
                                </select>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tahun Input -->
                    <div class="w-full lg:w-[150px]">
                        <label class="block text-[11px] text-gray-400 uppercase tracking-wider mb-2">Tahun</label>
                        <div class="relative">
                            <select name="year" onchange="this.form.submit()"
                                    class="w-full px-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-[13px] font-medium text-gray-700 appearance-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer">
                                @foreach(range(date('Y'), date('Y')-5) as $y)
                                    <option value="{{ $y }}" {{ $filters['year'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Jurusan Filter -->
                    <div class="w-full lg:w-[240px]">
                        <label class="block text-[11px] text-gray-400 uppercase tracking-wider mb-2">Jurusan</label>
                        <div class="relative">
                            <select name="jurusan_id" onchange="this.form.submit()"
                                    class="w-full px-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-[13px] font-medium text-gray-700 appearance-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer">
                                <option value="">Semua Jurusan</option>
                                @foreach($jurusan as $j)
                                    <option value="{{ $j->id }}" {{ $filters['jurusan_id'] == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>

                    <div class="ml-auto text-right">
                        <span class="text-[11px] text-gray-400 uppercase tracking-widest block mb-1">Hari Efektif</span>
                        <span class="text-2xl text-primary-600">{{ $filters['hari_efektif'] }} <small class="text-[12px] text-gray-400">Hari</small></span>
                    </div>
                </div>
            </form>
        </div>
    </div>



    <!-- Data Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        @include('admin.reports._table')
    </div>
</div>
@endsection
