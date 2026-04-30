@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
{{-- Data chart disimpan di data-attribute agar JS bisa membacanya setelah htmx swap --}}
<div id="dashboard-data"
    data-chart-status="{{ json_encode($chartStatus) }}"
    data-chart-attendance="{{ json_encode($chartAttendance) }}"
    data-trend-data="{{ json_encode($trendData) }}"
    style="display:none;"></div>

<div class="space-y-8">
    <!-- 🔝 Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Siswa -->
        <div class="p-5 bg-white rounded-xl shadow-sm border border-gray-100 flex items-center space-x-3.5">
            <div class="p-3 bg-blue-50 rounded-xl text-blue-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Siswa</p>
                <h3 class="text-2xl font-bold text-gray-900">{{ number_format($total_siswa) }}</h3>
            </div>
        </div>

        <!-- Total Kelas -->
        <div class="p-5 bg-white rounded-xl shadow-sm border border-gray-100 flex items-center space-x-3.5">
            <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Kelas</p>
                <h3 class="text-2xl font-bold text-gray-900">{{ $total_kelas }}</h3>
            </div>
        </div>

        <!-- Hadir Hari Ini -->
        <div class="p-5 bg-white rounded-xl shadow-sm border border-gray-100 flex items-center space-x-3.5">
            <div class="p-3 bg-green-50 rounded-xl text-green-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Hadir Hari Ini</p>
                <h3 class="text-2xl font-bold text-gray-900">{{ number_format($total_hadir) }}</h3>
            </div>
        </div>

        <!-- Persentase -->
        <div class="p-5 bg-white rounded-xl shadow-sm border border-gray-100 flex items-center space-x-3.5">
            <div class="p-3 bg-amber-50 rounded-xl text-amber-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tingkat Kehadiran</p>
                <h3 class="text-2xl font-bold text-gray-900">{{ $presentase }}%</h3>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- 📊 Diagram Status Absensi per Kelas -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 min-w-0">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Status Input Kelas</h3>
                    <p class="text-sm text-gray-500">Pemantauan kelas yang sudah mengisi absensi hari ini</p>
                </div>
            </div>
            <div id="statusChart"></div>
        </div>

        <!-- 📊 Diagram Kehadiran per Kelas (Stacked) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 min-w-0">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Distribusi Kehadiran</h3>
                    <p class="text-sm text-gray-500">Detail status kehadiran per kelas hari ini</p>
                </div>
            </div>
            <div id="attendanceChart"></div>
        </div>
    </div>

    <!-- 📉 Diagram Tren Kehadiran -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 min-w-0">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Tren Kehadiran (7 Hari Terakhir)</h3>
                <p class="text-sm text-gray-500">Persentase kehadiran harian secara keseluruhan</p>
            </div>
        </div>
        <div id="trendChart"></div>
    </div>
</div>

<script>
    /**
     * Inisialisasi semua chart dashboard.
     * Didefinisikan di window scope agar bisa dipanggil dari htmx:afterSwap di layout.
     * Aman dipanggil berulang kali — container dibersihkan sebelum render ulang.
     */
    window.initDashboardCharts = function() {
        var dataEl = document.getElementById('dashboard-data');
        if (!dataEl || typeof ApexCharts === 'undefined') return;

        var chartStatus     = JSON.parse(dataEl.getAttribute('data-chart-status'));
        var chartAttendance = JSON.parse(dataEl.getAttribute('data-chart-attendance'));
        var trendData       = JSON.parse(dataEl.getAttribute('data-trend-data'));

        // Bersihkan container sebelum render ulang (hindari chart duplikat)
        ['statusChart', 'attendanceChart', 'trendChart'].forEach(function(id) {
            var c = document.getElementById(id);
            if (c) c.innerHTML = '';
        });

        // 1. Status Chart (Sudah Input vs Belum)
        var statusData = chartStatus.data;
        new ApexCharts(document.getElementById('statusChart'), {
            series: [{ name: 'Status Input', data: statusData }],
            chart: { type: 'bar', height: 350, toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 4, distributed: true, horizontal: false, columnWidth: '40%' } },
            colors: statusData.map(function(v) { return v === 1 ? '#008080' : '#e5e7eb'; }),
            dataLabels: { enabled: false },
            xaxis: {
                categories: chartStatus.labels,
                labels: { style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 600 } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                min: 0, max: 1.2, tickAmount: 1,
                labels: {
                    formatter: function(val) { return val === 1 ? '\u2713' : '\u2717'; },
                    style: { colors: '#64748b', fontSize: '16px', fontWeight: 'bold' }
                }
            },
            annotations: {
                points: statusData.map(function(v, i) {
                    if (v === 1) {
                        return {
                            x: chartStatus.labels[i], y: 1,
                            marker: { size: 0 },
                            label: {
                                borderColor: '#10b981', offsetY: 0,
                                style: { color: '#fff', background: '#10b981', fontSize: '10px' },
                                text: '\u2713'
                            }
                        };
                    }
                    return null;
                }).filter(function(n) { return n !== null; }),
                yaxis: [
                    { y: 1, borderColor: '#10b981', label: { text: 'Terisi', style: { background: '#10b981', color: '#fff' } } },
                    { y: 0, borderColor: '#cbd5e1', label: { text: 'Pending', style: { background: '#94a3b8', color: '#fff' } } }
                ]
            },
            legend: { show: false },
            grid: { borderColor: '#f1f5f9' }
        }).render();

        // 2. Attendance Chart (Stacked)
        new ApexCharts(document.getElementById('attendanceChart'), {
            series: [
                { name: 'Hadir', data: chartAttendance.hadir },
                { name: 'Sakit', data: chartAttendance.sakit },
                { name: 'Izin',  data: chartAttendance.izin  },
                { name: 'Alpha', data: chartAttendance.alpha }
            ],
            chart: { type: 'bar', height: 350, stacked: true, toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 0, columnWidth: '50%', dataLabels: { position: 'center' } } },
            dataLabels: {
                enabled: true,
                formatter: function(val) { return val > 0 ? val : ''; },
                style: { fontSize: '12px', colors: ['#fff'], fontWeight: 'bold' },
                dropShadow: { enabled: true, top: 1, left: 1, blur: 1, opacity: 0.5 }
            },
            colors: ['#2563eb', '#ca8a04', '#059669', '#dc2626'],
            xaxis: {
                categories: chartAttendance.labels,
                labels: { style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 600 } }
            },
            yaxis: { labels: { style: { colors: '#64748b' } } },
            legend: { position: 'top', horizontalAlign: 'center', offsetY: -10 },
            fill: { opacity: 1 },
            grid: { borderColor: '#f1f5f9' },
            annotations: {
                xaxis: chartAttendance.labels.map(function(label, i) {
                    var total = chartAttendance.hadir[i] + chartAttendance.sakit[i] +
                                chartAttendance.izin[i]  + chartAttendance.alpha[i];
                    if (total === 0) {
                        return {
                            x: label, borderColor: 'transparent',
                            label: {
                                text: 'No Data - Pending', orientation: 'horizontal',
                                style: { background: '#f8fafc', color: '#94a3b8', fontSize: '10px', padding: 5 }
                            }
                        };
                    }
                    return null;
                }).filter(function(n) { return n !== null; })
            }
        }).render();

        // 3. Trend Chart (Area)
        new ApexCharts(document.getElementById('trendChart'), {
            series: [{ name: 'Persentase Kehadiran', data: trendData.data }],
            chart: { height: 300, type: 'area', toolbar: { show: false }, zoom: { enabled: false } },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 4 },
            colors: ['#3b82f6'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.1, stops: [0, 90, 100] }
            },
            xaxis: {
                categories: trendData.labels,
                labels: { style: { colors: '#94a3b8', fontWeight: 600 } }
            },
            yaxis: {
                min: 0, max: 100,
                labels: {
                    formatter: function(val) { return val + '%'; },
                    style: { colors: '#64748b' }
                }
            },
            grid: { borderColor: '#f1f5f9' }
        }).render();
    };

    // Langsung panggil saat halaman pertama kali dimuat
    window.initDashboardCharts();
</script>
@endsection
