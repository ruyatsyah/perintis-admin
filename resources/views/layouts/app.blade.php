<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SMK PERINTIS - Absensi' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 
                            400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 
                            800: '#1e40af', 900: '#1e3a8a', 950: '#172554',
                        },
                    }
                }
            }
        }
    </script>
    <!-- ApexCharts: dimuat di head agar tersedia setelah htmx swap -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <!-- htmx: partial page swap on sidebar navigation -->
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.4/dist/htmx.min.js"></script>
    <!-- NProgress: navigation loading bar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.css">
    <script src="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Poppins', sans-serif; }
        /* Page fade transition */
        body { transition: opacity 0.12s ease; }
        body.page-exit { opacity: 0; }
        /* NProgress customization */
        #nprogress .bar { background: #2563eb !important; height: 2.5px !important; }
        #nprogress .peg { box-shadow: 0 0 10px #2563eb, 0 0 5px #2563eb !important; }
    </style>
    @livewireStyles
</head>
<body class="font-sans antialiased bg-[#f4f7fe] text-gray-900">
    @php
        $isSidebarOpen = (isset($_COOKIE['sidebar_open']) ? $_COOKIE['sidebar_open'] : 'true') === 'true';
    @endphp
    <div class="h-screen flex overflow-hidden" 
         @show-toast.window="showToast($event.detail.message, $event.detail.type)"
         x-data="{ 
            sidebarOpen: {{ $isSidebarOpen ? 'true' : 'false' }},
            show: false, 
            message: '', 
            type: 'success',
            init() {
                this.$watch('sidebarOpen', val => document.cookie = 'sidebar_open=' + val + '; path=/; max-age=31536000');
            },
            showToast(msg, type) {
                this.message = msg;
                this.type = type;
                this.show = true;
                setTimeout(() => this.show = false, 5000);
            },
            confirmModal: {
                show: false, title: '', message: '', confirmLabel: '', type: 'danger', formId: null,
            },
            openConfirm(config) {
                this.confirmModal = { ...this.confirmModal, ...config, show: true };
            }
        }">
        
        <!-- Sidebar Wrapper -->
        <div class="relative flex-shrink-0 h-full {{ $isSidebarOpen ? 'md:w-60' : 'md:w-20' }} md:transition-all md:duration-300 md:ease-in-out"
             :class="sidebarOpen ? 'md:w-60' : 'md:w-20'">
             
            <!-- Mobile Sidebar Overlay -->
            <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-40 md:hidden" x-transition.opacity style="display: none;" x-cloak></div>

            <aside class="bg-white border-gray-200 flex flex-col z-50 fixed md:absolute inset-y-0 left-0 overflow-hidden shadow-2xl md:shadow-none h-full {{ $isSidebarOpen ? 'w-[260px] md:w-60 translate-x-0 border-r' : 'w-[260px] -translate-x-full md:translate-x-0 md:w-20 border-r' }} transition-all duration-300 ease-in-out"
                :class="sidebarOpen ? 'w-[260px] md:w-60 translate-x-0 border-r' : 'w-[260px] -translate-x-full md:translate-x-0 md:w-20 border-r'">
                <div class="w-[260px] md:w-60 flex-shrink-0 h-full flex flex-col">
                    <div class="px-5 py-4 flex items-center h-[64px] flex-shrink-0">
                        <div class="font-bold text-lg text-primary-700 tracking-tight flex items-center w-full whitespace-nowrap">
                            <div class="w-10 h-10 flex items-center justify-center shrink-0 mr-3">
                                <img src="{{ asset('assets/logo_perintis.png') }}" alt="Logo" class="w-full h-full object-contain">
                            </div>
                            <span class="truncate text-black font-extrabold tracking-tight {{ $isSidebarOpen ? 'opacity-100' : 'opacity-0' }} transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">SMK PERINTIS</span>
                        </div>
                    </div>
                
                    <nav id="sidebar-nav" class="px-4 py-4 space-y-1.5 flex-1 overflow-y-auto overflow-x-hidden">
                        @if(auth()->user()->role === 'admin')
                            <a wire:navigate.hover href="{{ route('admin.dashboard') }}" class="flex items-center text-[13px] px-3 py-2.5 rounded-lg hover:bg-primary-50 transition w-full whitespace-nowrap {{ request()->routeIs('admin.dashboard') ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600 font-medium' }}" title="Dashboard">
                                <svg class="w-[18px] h-[18px] shrink-0 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg> 
                                <span class="truncate {{ $isSidebarOpen ? 'opacity-100' : 'opacity-0' }} transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">Dashboard</span>
                            </a>
                            
                            <div class="border-t border-gray-100 my-4 w-full"></div>
                            
                            <a wire:navigate.hover href="{{ route('admin.jurusan.index') }}" class="flex items-center text-[13px] px-3 py-2.5 rounded-lg hover:bg-primary-50 transition w-full whitespace-nowrap {{ request()->routeIs('admin.jurusan.*') ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600 font-medium' }}" title="Jurusan">
                                <svg class="w-[18px] h-[18px] shrink-0 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg> 
                                <span class="truncate {{ $isSidebarOpen ? 'opacity-100' : 'opacity-0' }} transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">Jurusan</span>
                            </a>
                            <a wire:navigate.hover href="{{ route('admin.kelas.index') }}" class="flex items-center text-[13px] px-3 py-2.5 rounded-lg hover:bg-primary-50 transition w-full whitespace-nowrap {{ request()->routeIs('admin.kelas.*') ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600 font-medium' }}" title="Kelas">
                                <svg class="w-[18px] h-[18px] shrink-0 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg> 
                                <span class="truncate {{ $isSidebarOpen ? 'opacity-100' : 'opacity-0' }} transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">Kelas</span>
                            </a>
                            <a wire:navigate.hover href="{{ route('admin.siswa.index') }}" class="flex items-center text-[13px] px-3 py-2.5 rounded-lg hover:bg-primary-50 transition w-full whitespace-nowrap {{ request()->routeIs('admin.siswa.*') ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600 font-medium' }}" title="Siswa">
                                <svg class="w-[18px] h-[18px] shrink-0 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg> 
                                <span class="truncate {{ $isSidebarOpen ? 'opacity-100' : 'opacity-0' }} transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">Siswa</span>
                            </a>
                            <a wire:navigate.hover href="{{ route('admin.petugas.index') }}" class="flex items-center text-[13px] px-3 py-2.5 rounded-lg hover:bg-primary-50 transition w-full whitespace-nowrap {{ request()->routeIs('admin.petugas.*') ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600 font-medium' }}" title="Petugas">
                                <svg class="w-[18px] h-[18px] shrink-0 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg> 
                                <span class="truncate {{ $isSidebarOpen ? 'opacity-100' : 'opacity-0' }} transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">Petugas</span>
                            </a>
                            
                            <div class="border-t border-gray-100 my-4 w-full"></div>
                            
                            <a wire:navigate.hover href="{{ route('admin.reports.index') }}" class="flex items-center text-[13px] px-3 py-2.5 rounded-lg hover:bg-primary-50 transition w-full whitespace-nowrap {{ request()->routeIs('admin.reports.*') ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600 font-medium' }}" title="Rekap Absensi">
                                <svg class="w-[18px] h-[18px] shrink-0 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg> 
                                <span class="truncate {{ $isSidebarOpen ? 'opacity-100' : 'opacity-0' }} transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">Rekap Absensi</span>
                            </a>
                            
                            <a wire:navigate.hover href="{{ route('admin.hari_libur.index') }}" class="flex items-center text-[13px] px-3 py-2.5 rounded-lg hover:bg-red-50 transition w-full whitespace-nowrap {{ request()->routeIs('admin.hari_libur.*') ? 'bg-red-50 text-red-700 font-medium' : 'text-gray-600 font-medium' }}" title="Hari Libur">
                                <svg class="w-[18px] h-[18px] shrink-0 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg> 
                                <span class="truncate {{ $isSidebarOpen ? 'opacity-100' : 'opacity-0' }} transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">Hari Libur</span>
                            </a>
                        @else
                            <a wire:navigate.hover href="{{ route('petugas.index') }}" class="flex items-center text-[13px] px-3 py-2.5 rounded-lg hover:bg-primary-50 transition w-full whitespace-nowrap {{ request()->routeIs('petugas.*') ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600 font-medium' }}" title="Input Absensi">
                                <svg class="w-[18px] h-[18px] shrink-0 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg> 
                                <span class="truncate {{ $isSidebarOpen ? 'opacity-100' : 'opacity-0' }} transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">Input Absensi</span>
                            </a>
                        @endif

                    </nav>
                </div>
            </aside>

            <!-- Edge Arrow Toggle (Desktop Only) -->
            <button @click="sidebarOpen = !sidebarOpen" 
                class="hidden md:flex absolute top-[20px] -right-3.5 w-7 h-7 bg-white border border-gray-200 text-gray-400 hover:text-primary-600 hover:border-primary-200 rounded-full items-center justify-center shadow-sm z-50 transition-colors">
                <!-- if open: point left -->
                <svg x-show="sidebarOpen" class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                <!-- if closed: point right -->
                <svg x-show="!sidebarOpen" x-cloak class="w-3.5 h-3.5 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </button>
        </div>

        <!-- Main Content Wrapper -->
        <main class="flex-1 flex flex-col min-w-0 bg-[#f4f7fe]">
            <!-- Universal Topbar -->
            <header class="bg-white border-b border-gray-200/80 h-[64px] px-4 lg:px-6 flex items-center justify-between flex-shrink-0 z-30">
                <div class="flex items-center">
                    <button @click="sidebarOpen = true" class="p-2 mr-3 -ml-2 text-gray-500 hover:text-primary-600 md:hidden flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <!-- Title moved to Breadcrumbs -->
                </div>
                <div class="flex items-center space-x-2 relative" x-data="{ profileOpen: false }">
                    <div class="text-right hidden sm:block mr-1">
                        <p class="text-[13px] font-semibold text-gray-900 leading-tight">{{ auth()->user()->name }}</p>
                    </div>
                    <button @click="profileOpen = !profileOpen" @click.away="profileOpen = false" class="flex items-center gap-1.5 focus:outline-none group">
                        <div class="w-8 h-8 rounded-full bg-primary-100 border border-primary-200 text-primary-700 flex items-center justify-center text-xs font-bold shadow-sm group-hover:opacity-80 transition">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-gray-600 transition-transform duration-200" :class="profileOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                    </button>

                    <!-- Profile Dropdown -->
                    <div x-show="profileOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-[0_10px_40px_-15px_rgba(0,0,0,0.1)] border border-gray-100 py-2 z-50"
                         style="display: none;"
                         x-cloak>
                        
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                        
                        <button type="button" 
                            @click="openConfirm({
                                title: 'Konfirmasi Keluar',
                                message: 'Apakah Anda yakin ingin keluar dari sistem?',
                                confirmLabel: 'Ya, Keluar',
                                type: 'logout',
                                formId: 'logout-form'
                            }); profileOpen = false;"
                            class="w-full flex items-center px-4 py-2 hover:bg-red-50 text-sm text-red-600 font-medium transition text-left">
                            <span class="mr-3"></span> Keluar Sistem
                        </button>
                    </div>
                </div>
            </header>
            
            <!-- Breadcrumbs (Sticky) -->
            <nav class="px-4 lg:px-6 py-3 bg-white text-[12px] font-medium tracking-wide border-b border-gray-100 flex-shrink-0 z-20">
                <ol class="flex items-center space-x-2">
                    <li><span class="text-gray-500 capitalize">{{ auth()->user()->role === 'admin' ? 'Admin' : 'Petugas' }}</span></li>
                    <li><span class="text-gray-300">/</span></li>
                    <li class="text-gray-900">{{ $title ?? 'Dashboard' }}</li>
                </ol>
            </nav>

            <!-- Minimalist Toast Notifications -->
            <div x-data="{ 
                    show: false, 
                    message: '', 
                    type: 'success',
                    init() {
                        @if(session('success'))
                            this.showToast('{{ session('success') }}', 'success');
                        @endif
                        @if(session('error'))
                            this.showToast('{{ session('error') }}', 'error');
                        @endif
                    },
                    showToast(msg, type) {
                        this.message = msg;
                        this.type = type;
                        this.show = true;
                        setTimeout(() => this.show = false, 5000);
                    }
                }"
                x-show="show"
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 -translate-y-10 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-10 scale-95"
                class="fixed top-6 left-1/2 -translate-x-1/2 z-[9999] w-full max-w-[280px] px-4"
                style="display: none;"
                x-cloak
            >
                <div class="bg-white/90 backdrop-blur-md border border-gray-100 px-4 py-2.5 rounded-xl shadow-[0_10px_40px_-15px_rgba(0,0,0,0.1)] flex items-center space-x-3">
                    <!-- Icon Indicator -->
                    <div :class="{
                        'bg-green-100 text-green-600': type === 'success',
                        'bg-red-100 text-red-600': type === 'error'
                    }" class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center">
                        <template x-if="type === 'success'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </template>
                        <template x-if="type === 'error'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </template>
                    </div>
                    
                    <div class="flex-1 overflow-hidden">
                        <p class="text-[11px] font-bold text-gray-900 leading-tight" x-text="type === 'success' ? 'Sukses' : 'Error'"></p>
                        <p class="text-[10px] text-gray-500 mt-0.5 truncate" x-text="message"></p>
                    </div>

                    <button @click="show = false" class="text-gray-300 hover:text-gray-500 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Global Confirmation Modal -->
            <div x-show="confirmModal.show" 
                class="fixed inset-0 z-[9999] overflow-y-auto" 
                style="display: none;"
                x-cloak>
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="confirmModal.show" 
                        x-transition:enter="ease-out duration-300" 
                        x-transition:enter-start="opacity-0" 
                        x-transition:enter-end="opacity-100" 
                        x-transition:leave="ease-in duration-200" 
                        x-transition:leave-start="opacity-100" 
                        x-transition:leave-end="opacity-0" 
                        class="fixed inset-0 transition-opacity bg-black/50 backdrop-blur-sm" 
                        @click="confirmModal.show = false"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="confirmModal.show" 
                        x-transition:enter="ease-out duration-300" 
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                        x-transition:leave="ease-in duration-200" 
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                        class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        
                        <div class="px-8 pt-8 pb-6 bg-white">
                            <div class="sm:flex sm:items-start">
                                <div x-show="confirmModal.type !== 'logout'" :class="{
                                    'bg-red-100 text-red-600': confirmModal.type === 'danger',
                                    'bg-blue-100 text-blue-600': confirmModal.type === 'info',
                                    'bg-yellow-100 text-yellow-600': confirmModal.type === 'warning'
                                }" class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                    <template x-if="confirmModal.type === 'danger'">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </template>
                                    <template x-if="confirmModal.type === 'info' || confirmModal.type === 'warning'">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </template>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg font-bold leading-6 text-gray-900" x-text="confirmModal.title"></h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500" x-text="confirmModal.message"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-8 py-6 bg-gray-50 sm:flex sm:flex-row-reverse space-y-2 sm:space-y-0">
                            <button type="button" 
                                @click="document.getElementById(confirmModal.formId).submit()"
                                :class="{
                                    'bg-red-600 hover:bg-red-700': confirmModal.type === 'danger' || confirmModal.type === 'logout',
                                    'bg-primary-600 hover:bg-primary-700': confirmModal.type === 'info',
                                    'bg-yellow-600 hover:bg-yellow-700': confirmModal.type === 'warning'
                                }"
                                class="inline-flex justify-center w-full px-6 py-2.5 text-sm font-bold text-white rounded-xl shadow-lg transition sm:ml-3 sm:w-auto" 
                                x-text="confirmModal.confirmLabel"></button>
                            <button type="button" 
                                @click="confirmModal.show = false"
                                class="inline-flex justify-center w-full px-6 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition sm:w-auto">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Content Area and Footer Wrapper -->
            <div id="page-content" class="flex-1 overflow-y-auto flex flex-col" hx-boost="true" hx-target="#page-content" hx-select="#page-content">
                <!-- Session Toasts Trigger -->
                @if(session('success'))
                    <div x-data x-init="$dispatch('show-toast', { message: @json(session('success')), type: 'success' })"></div>
                @endif
                @if(session('error'))
                    <div x-data x-init="$dispatch('show-toast', { message: @json(session('error')), type: 'error' })"></div>
                @endif
                @if($errors->any())
                    <div x-data x-init="$dispatch('show-toast', { message: @json($errors->first()), type: 'error' })"></div>
                @endif

                <div class="p-4 lg:p-6 flex-1">
                    @yield('content')
                </div>
                
                <!-- Footer -->
                <footer class="mt-auto px-4 lg:px-6 py-4 text-[11px] font-medium text-gray-500 border-t border-gray-200 bg-white/50 flex flex-col sm:flex-row items-center justify-between">
                    <p>&copy; {{ date('Y') }} SMK Perintis. All Rights Reserved.</p>
                    <p class="mt-2 sm:mt-0 text-gray-900">
                        Developed by <span class="font-bold">Ruyatsyah</span>
                    </p>
                </footer>
            </div>
        </main>
    </div>
    @livewireScripts
</body>
<script>
    // Allow htmx to execute inline scripts (needed for ApexCharts re-init)
    htmx.config.allowScriptTags = true;

    // NProgress configuration
    NProgress.configure({ showSpinner: false, speed: 250, minimum: 0.08 });

    // ── Sidebar active menu updater ──────────────────────────────────────────
    function updateSidebarActive() {
        const nav = document.getElementById('sidebar-nav');
        if (!nav) return;
        const currentPath = window.location.pathname;

        nav.querySelectorAll('a[href]').forEach(function (link) {
            let linkPath;
            try { linkPath = new URL(link.href).pathname; } catch (e) { return; }

            const isActive = currentPath === linkPath ||
                             (currentPath.startsWith(linkPath + '/') && linkPath !== '/');

            if (isActive) {
                link.classList.remove('text-gray-600');
                link.classList.add('bg-primary-50', 'text-primary-700');
            } else {
                link.classList.remove('bg-primary-50', 'text-primary-700');
                link.classList.add('text-gray-600');
            }
        });
    }
    // ────────────────────────────────────────────────────────────────────────

    // Connect Livewire navigate to NProgress
    document.addEventListener('livewire:navigating', () => {
        NProgress.start();
    });

    document.addEventListener('livewire:navigated', () => {
        NProgress.done();
        updateSidebarActive();
        
        var pc = document.getElementById('page-content');
        if (pc) pc.scrollTop = 0;

        if (typeof window.initDashboardCharts === 'function' && document.getElementById('dashboard-data')) {
            setTimeout(window.initDashboardCharts, 50);
        }
    });

    // Connect htmx to NProgress
    document.addEventListener('htmx:beforeRequest', function () {
        NProgress.start();
    });
    document.addEventListener('htmx:afterSwap', function (e) {
        NProgress.done();
        updateSidebarActive();

        var isFullPageSwap = (e.detail.target && e.detail.target.id === 'page-content');

        // Scroll ke atas hanya saat navigasi full halaman (bukan partial filter/pagination)
        if (isFullPageSwap) {
            var pc = document.getElementById('page-content');
            if (pc) pc.scrollTop = 0;
        }

        // Re-render dashboard charts (didefinisikan di window oleh dashboard.blade.php)
        if (isFullPageSwap && typeof window.initDashboardCharts === 'function' && document.getElementById('dashboard-data')) {
            // Beri sedikit delay agar DOM fully settled sebelum render ApexCharts
            setTimeout(window.initDashboardCharts, 50);
        }

    });
    document.addEventListener('htmx:responseError', function () {
        NProgress.done();
    });

    // Fallback NProgress for normal link clicks not handled by htmx/livewire
    document.addEventListener('click', function (e) {
        if (e.defaultPrevented) return;
        const link = e.target.closest('a[href]');
        if (!link || link.hasAttribute('hx-boost') || link.closest('[hx-boost]') || link.hasAttribute('wire:navigate') || link.hasAttribute('wire:navigate.hover')) return;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('mailto:') ||
            link.target === '_blank' || href.startsWith('javascript:')) return;
        if (link.href.includes('export') || link.href.includes('download')) return;
        NProgress.start();
        document.body.classList.add('page-exit');
    });
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form.hasAttribute('hx-boost') || form.closest('[hx-boost]')) return;
        NProgress.start();
        document.body.classList.add('page-exit');
    });
    window.addEventListener('pageshow', function () {
        NProgress.done();
        document.body.classList.remove('page-exit');
    });
</script>
</html>
