<?php

/**
 * Script untuk mengupdate pangkat/golongan ruang staff
 * 
 * Usage: php update-pangkat-gol.php
 * 
 * Format data: NIP => Pangkat/Gol. Ruang
 * Contoh: '197001011995031002' => 'Pembina Tingkat I / IV/b'
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Data pangkat/golongan ruang berdasarkan NIP
// Silakan sesuaikan dengan data yang ada
$pangkatData = [
    // Contoh format:
    // '197001011995031002' => 'Pembina Tingkat I / IV/b',
    // '196907101997031007' => 'Pembina / IV/a',
    // Tambahkan data lainnya di sini
];

echo "=== UPDATE PANGKAT / GOL. RUANG ===\n\n";

if (empty($pangkatData)) {
    echo "INFO: Belum ada data pangkat yang diisi.\n";
    echo "Silakan edit file ini dan tambahkan data pangkat sesuai format:\n";
    echo "  'NIP' => 'Pangkat / Gol. Ruang',\n\n";
    
    // Tampilkan daftar user yang belum punya pangkat
    $usersWithoutPangkat = \App\Models\User::whereNull('pangkat_gol')
        ->orWhere('pangkat_gol', '')
        ->get(['id', 'name', 'nip', 'jabatan']);
    
    if ($usersWithoutPangkat->count() > 0) {
        echo "Daftar user yang belum memiliki pangkat/gol:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-5s | %-40s | %-18s | %s\n", "ID", "NAMA", "NIP", "JABATAN");
        echo str_repeat("-", 80) . "\n";
        foreach ($usersWithoutPangkat as $user) {
            printf("%-5d | %-40s | %-18s | %s\n", 
                $user->id, 
                $user->name, 
                $user->nip ?? '-', 
                $user->jabatan ?? '-'
            );
        }
        echo "\n";
    }
    
    exit(0);
}

$updated = 0;
$notFound = [];

foreach ($pangkatData as $nip => $pangkat) {
    $user = \App\Models\User::where('nip', $nip)->first();
    
    if ($user) {
        $user->pangkat_gol = $pangkat;
        $user->save();
        echo "✓ Updated: {$user->name} ({$nip}) => {$pangkat}\n";
        $updated++;
    } else {
        $notFound[] = $nip;
    }
}

echo "\n";
echo "Total updated: {$updated}\n";

if (!empty($notFound)) {
    echo "\nNIP tidak ditemukan:\n";
    foreach ($notFound as $nip) {
        echo "  - {$nip}\n";
    }
}

echo "\nSelesai!\n";
