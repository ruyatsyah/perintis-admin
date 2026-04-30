<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JurusanController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\PetugasController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Petugas\AttendanceController;

// Auth Routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('jurusan', JurusanController::class);
    Route::resource('kelas', KelasController::class);
    
    // Siswa with Import/Export
    Route::get('siswa/export', [SiswaController::class, 'export'])->name('siswa.export');
    Route::post('siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
    Route::resource('siswa', SiswaController::class);
    
    Route::resource('petugas', PetugasController::class);
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/export-all-pdf', [ReportController::class, 'exportAllPdf'])->name('reports.exportAllPdf');
    Route::get('/reports/export-monthly/{kelas}', [ReportController::class, 'exportMonthly'])->name('reports.exportMonthly');
    Route::get('/reports/export/{kelas}/{type}', [ReportController::class, 'exportClass'])->name('reports.exportClass');
    
    // Hari Libur
    Route::resource('hari_libur', \App\Http\Controllers\Admin\HariLiburController::class);
});

// Petugas Routes
Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('index');
    Route::get('/create/{kelas_id}', [AttendanceController::class, 'create'])->name('create');
    Route::post('/store', [AttendanceController::class, 'store'])->name('store');
});
