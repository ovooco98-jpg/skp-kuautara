<?php

use Illuminate\Support\Facades\Route;

// ============================================
// 🔧 MAINTENANCE MODE - ALL ROUTES
// ============================================
// Semua route akan menampilkan halaman maintenance
// Untuk restore route normal, copy dari: routes/web.php.backup

// Secret bypass untuk admin
Route::get('/recovery2026', function () {
    return view('maintenance')->cookie('maintenance_bypass', 'true', 60 * 24 * 7);
})->name('maintenance.bypass');

// Catch all routes - tampilkan maintenance
Route::any('/{any}', function () {
    // Check bypass cookie
    if (request()->cookie('maintenance_bypass') === 'true') {
        // Kalau sudah bypass, redirect ke login
        return redirect('/login-bypass-maintenance');
    }
    
    return response()->view('maintenance', [], 503);
})->where('any', '.*');

// ============================================
// INSTRUKSI DISABLE MAINTENANCE:
// ============================================
// 1. Hapus isi file ini
// 2. Copy dari web.php.backup:
//    Copy-Item routes/web.php.backup -Destination routes/web.php
// 3. Commit dan push ke Railway
// ============================================
