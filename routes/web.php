<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LkhController;
use App\Http\Controllers\ExportController;

// Public routes
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Health check route (for Render, monitoring, etc.)
Route::get('/up', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()], 200);
});

// Auth routes
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Protected routes - require authentication
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/statistics', [DashboardController::class, 'statistics'])->name('statistics');
    
    // Export
    Route::get('/export/excel', [ExportController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/laporan-bulanan', [ExportController::class, 'exportLaporanBulanan'])->name('export.laporan-bulanan');
    Route::get('/export/laporan-triwulanan/{id}', [ExportController::class, 'exportLaporanTriwulanan'])->name('export.laporan-triwulanan');
    Route::get('/export/laporan-tahunan/{id}', [ExportController::class, 'exportLaporanTahunan'])->name('export.laporan-tahunan');
    
    // Print
    Route::get('/print/lkh/{id?}', [ExportController::class, 'printLkh'])->name('print.lkh');
    Route::get('/print/laporan-bulanan/{id}', [ExportController::class, 'printLaporanBulanan'])->name('print.laporan-bulanan');
    Route::get('/print/laporan-triwulanan/{id}', [ExportController::class, 'printLaporanTriwulanan'])->name('print.laporan-triwulanan');
    Route::get('/print/laporan-tahunan/{id}', [ExportController::class, 'printLaporanTahunan'])->name('print.laporan-tahunan');
    
    // Laporan Bulanan
    Route::prefix('laporan-bulanan')->name('laporan-bulanan.')->group(function () {
        Route::get('/', [App\Http\Controllers\LaporanBulananController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\LaporanBulananController::class, 'create'])->name('create');
        Route::post('/generate', [App\Http\Controllers\LaporanBulananController::class, 'generate'])->name('generate');
        Route::post('/generate-otomatis', [App\Http\Controllers\LaporanBulananController::class, 'generateOtomatis'])->name('generate-otomatis');
        Route::post('/', [App\Http\Controllers\LaporanBulananController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\LaporanBulananController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\LaporanBulananController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\LaporanBulananController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\LaporanBulananController::class, 'destroy'])->name('destroy');
    });
    
    // Laporan Triwulanan
    Route::prefix('laporan-triwulanan')->name('laporan-triwulanan.')->group(function () {
        Route::get('/', [App\Http\Controllers\LaporanTriwulananController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\LaporanTriwulananController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\LaporanTriwulananController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\LaporanTriwulananController::class, 'show'])->name('show');
        Route::post('/{id}/upload-bukti-fisik', [App\Http\Controllers\LaporanTriwulananController::class, 'uploadBuktiFisik'])->name('upload-bukti-fisik');
        Route::get('/{id}/download-bukti-fisik', [App\Http\Controllers\LaporanTriwulananController::class, 'downloadBuktiFisik'])->name('download-bukti-fisik');
    });
    
    // Laporan Tahunan
    Route::prefix('laporan-tahunan')->name('laporan-tahunan.')->group(function () {
        Route::get('/', [App\Http\Controllers\LaporanTahunanController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\LaporanTahunanController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\LaporanTahunanController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\LaporanTahunanController::class, 'show'])->name('show');
        Route::post('/{id}/upload-bukti-fisik', [App\Http\Controllers\LaporanTahunanController::class, 'uploadBuktiFisik'])->name('upload-bukti-fisik');
        Route::get('/{id}/download-bukti-fisik', [App\Http\Controllers\LaporanTahunanController::class, 'downloadBuktiFisik'])->name('download-bukti-fisik');
    });
    
    // LKH Routes
    Route::prefix('lkh')->name('lkh.')->group(function () {
        // CRUD LKH
        Route::get('/', [LkhController::class, 'index'])->name('index');
        Route::get('/staff', [LkhController::class, 'staff'])->name('staff'); // LKH Staff untuk Kepala KUA
        Route::get('/saya', [LkhController::class, 'saya'])->name('saya'); // LKH Saya untuk Kepala KUA
        Route::get('/create', [LkhController::class, 'create'])->name('create');
        Route::post('/', [LkhController::class, 'store'])->name('store');
        Route::post('/{id}/update-status', [LkhController::class, 'updateStatus'])->name('update-status'); // Update status LKH
        Route::get('/{id}', [LkhController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [LkhController::class, 'edit'])->name('edit');
        Route::put('/{id}', [LkhController::class, 'update'])->name('update');
        Route::delete('/{id}', [LkhController::class, 'destroy'])->name('destroy');
        
        // Download lampiran
        Route::get('/{id}/download', [LkhController::class, 'download'])->name('download');
        
        // Copy LKH
        Route::get('/{id}/copy', [LkhController::class, 'copy'])->name('copy');
        
        // Detail Harian
        Route::get('/detail-harian', [LkhController::class, 'detailHarian'])->name('detail-harian');
        Route::post('/update-ringkasan-harian', [LkhController::class, 'updateRingkasanHarian'])->name('update-ringkasan-harian');
    });
    
    // SKP Routes
    Route::prefix('skp')->name('skp.')->group(function () {
        Route::get('/', [App\Http\Controllers\SkpController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\SkpController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\SkpController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\SkpController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\SkpController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\SkpController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\SkpController::class, 'destroy'])->name('destroy');
        
        // Approval dan penilaian
        Route::post('/{id}/setujui', [App\Http\Controllers\SkpController::class, 'setujui'])->name('setujui');
        Route::post('/{id}/nilai', [App\Http\Controllers\SkpController::class, 'nilai'])->name('nilai');
        
        // Generate dan simpan link bukti fisik
        Route::post('/{id}/generate-bukti-fisik', [App\Http\Controllers\SkpController::class, 'generateBuktiFisik'])->name('generate-bukti-fisik');
        Route::post('/{id}/simpan-link-bukti-fisik', [App\Http\Controllers\SkpController::class, 'simpanLinkBuktiFisik'])->name('simpan-link-bukti-fisik');
        Route::get('/{id}/buka-link-bukti-fisik', [App\Http\Controllers\SkpController::class, 'bukaLinkBuktiFisik'])->name('buka-link-bukti-fisik');
        Route::get('/{id}/download-bukti-fisik', [App\Http\Controllers\SkpController::class, 'bukaLinkBuktiFisik'])->name('download-bukti-fisik');
        
        // Generate SKP Kepala KUA dari staff
        Route::post('/generate-dari-staff', [App\Http\Controllers\SkpController::class, 'generateDariStaff'])->name('generate-dari-staff');
    });
});
