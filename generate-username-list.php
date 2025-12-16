<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = \App\Models\User::where('role', '!=', 'kepala_kua')
    ->orWhere(function($query) {
        $query->where('role', 'kepala_kua');
    })
    ->orderBy('name')
    ->get(['name', 'email', 'nip', 'role']);

$output = "DAFTAR USERNAME STAFF KUA BANJARMASIN UTARA\n";
$output .= str_repeat("=", 80) . "\n\n";
$output .= "Password Default: password123\n";
$output .= "(Disarankan untuk mengubah password saat login pertama kali)\n\n";
$output .= str_repeat("=", 80) . "\n";
$output .= sprintf("%-3s | %-45s | %-40s | %-18s | %s\n", "NO", "NAMA LENGKAP", "EMAIL/USERNAME", "NIP", "ROLE");
$output .= str_repeat("-", 80) . "\n";

foreach ($users as $index => $user) {
    $role = ucfirst(str_replace('_', ' ', $user->role ?? 'pelaksana'));
    $output .= sprintf(
        "%-3d | %-45s | %-40s | %-18s | %s\n",
        $index + 1,
        $user->name,
        $user->email,
        $user->nip ?? '-',
        $role
    );
}

$output .= "\n" . str_repeat("=", 80) . "\n";
$output .= "Total: " . $users->count() . " staff\n";

echo $output;

// Save to file
file_put_contents(__DIR__ . '/DAFTAR_USERNAME_STAFF.txt', $output);
echo "\nFile saved to: DAFTAR_USERNAME_STAFF.txt\n";
