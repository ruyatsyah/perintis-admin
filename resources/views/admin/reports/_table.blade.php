<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50/50 border-b border-gray-100">
                <th class="px-6 py-4 text-[11px] text-gray-400 uppercase tracking-wider text-center w-16">
                    No
                </th>
                <th class="px-6 py-4 text-[11px] text-gray-400 uppercase tracking-wider">
                    Nama Kelas
                </th>
                <th class="px-6 py-4 text-[11px] text-gray-400 uppercase tracking-wider">
                    Jurusan
                </th>
                <th class="px-6 py-4 text-[11px] text-gray-400 uppercase tracking-wider text-right">
                    Total Siswa
                </th>
                <th class="px-6 py-4 text-[11px] text-gray-400 uppercase tracking-wider text-center">
                    Kehadiran (%)
                </th>
                <th class="px-6 py-4 text-[11px] text-gray-400 uppercase tracking-wider text-center w-48">
                    Aksi
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($records as $rec)
                <tr class="group hover:bg-primary-50/30 transition-colors duration-200 {{ $loop->odd ? 'bg-white' : 'bg-gray-50/20' }}">
                    <td class="px-6 py-4 text-[13px] text-gray-500 text-center">
                        {{ ($records->currentPage() - 1) * $records->perPage() + $loop->iteration }}
                    </td>
                    <td class="px-6 py-4 text-[13px] text-gray-600">
                        {{ $rec->nama_kelas }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-[13px] text-gray-600">{{ $rec->jurusan->nama ?? '-' }}</div>
                        <div class="text-[10px] text-gray-400 uppercase tracking-tight mt-0.5">{{ $rec->jurusan->kode ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4 text-[13px] text-gray-600 text-right">
                        <span class="tabular-nums">{{ number_format($rec->siswa_count) }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex flex-col items-center gap-1">
                            <span class="text-[14px] text-gray-600">
                                {{ $rec->kehadiran_persen }}%
                            </span>
                            <!-- Progress Bar -->
                            <div class="w-20 h-1 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full {{ $rec->kehadiran_persen < 75 ? 'bg-red-500' : ($rec->kehadiran_persen < 90 ? 'bg-amber-500' : 'bg-emerald-500') }}" 
                                     style="width: {{ $rec->kehadiran_persen }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2" x-data="{ open: false }">
                            <!-- Rekap Button -->
                            <a href="{{ route('admin.reports.exportClass', ['kelas' => $rec->id, 'type' => 'preview', 'start_date' => $filters['start_date'], 'end_date' => $filters['end_date']]) }}" 
                               target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white rounded-lg text-[11px] transition-all border border-blue-100"
                               title="Lihat Rekap PDF">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                Rekap
                            </a>

                            <!-- Dropdown for Export -->
                            <div class="relative">
                                <button @click="open = !open" @click.away="open = false"
                                        class="inline-flex items-center px-2 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-500 rounded-lg transition border border-gray-200">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                </button>
                                
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute right-0 z-50 mt-2 w-40 origin-top-right rounded-xl bg-white shadow-xl border border-gray-100 py-1 overflow-hidden"
                                     style="display: none;" x-cloak>
                                    
                                    <a href="{{ route('admin.reports.exportClass', ['kelas' => $rec->id, 'type' => 'excel', 'start_date' => $filters['start_date'], 'end_date' => $filters['end_date']]) }}"
                                       target="_blank"
                                       class="flex items-center px-4 py-2 text-[11px] text-gray-700 hover:bg-emerald-50 hover:text-emerald-700">
                                        Download Excel
                                    </a>
                                    <a href="{{ route('admin.reports.exportClass', ['kelas' => $rec->id, 'type' => 'pdf', 'start_date' => $filters['start_date'], 'end_date' => $filters['end_date']]) }}"
                                       target="_blank"
                                       class="flex items-center px-4 py-2 text-[11px] text-gray-700 hover:bg-red-50 hover:text-red-700">
                                        Download PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-20 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <p class="text-[13px] text-gray-600">Data Tidak Ditemukan</p>
                            <p class="text-[11px] text-gray-400 mt-1">Silakan sesuaikan filter Anda untuk melihat hasil lain.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($records->hasPages())
<div class="px-6 py-4 bg-gray-50/50 border-t border-gray-50">
    {{ $records->appends(request()->all())->links() }}
</div>
@endif
