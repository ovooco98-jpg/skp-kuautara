<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Sedang Maintenance - LKH KUA Banjarmasin Utara</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full">
        <!-- Logo/Icon -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-blue-100 rounded-full mb-4">
                <svg class="w-12 h-12 text-blue-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Sistem Sedang Maintenance</h1>
            <p class="text-xl text-gray-600">LKH KUA Banjarmasin Utara</p>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-6">
            <div class="text-center mb-6">
                <div class="inline-block bg-yellow-100 rounded-full px-4 py-2 mb-4">
                    <span class="text-yellow-800 font-semibold">🔧 Dalam Perbaikan</span>
                </div>
                <p class="text-gray-700 text-lg mb-4">
                    Mohon maaf atas ketidaknyamanannya. Kami sedang melakukan pemeliharaan sistem untuk meningkatkan performa dan keamanan aplikasi.
                </p>
            </div>

            <div class="bg-blue-50 rounded-lg p-6 mb-6">
                <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Yang Sedang Kami Lakukan:
                </h3>
                <ul class="space-y-2 text-gray-700">
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">✓</span>
                        <span>Optimasi performa database dan query</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">✓</span>
                        <span>Recovery dan backup data sistem</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-yellow-500 mr-2">⏳</span>
                        <span>Memastikan semua fitur berjalan optimal</span>
                    </li>
                </ul>
            </div>

            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white text-center">
                <p class="text-sm mb-2 opacity-90">Estimasi Selesai</p>
                <p class="text-3xl font-bold mb-1">~30 Menit</p>
                <p class="text-sm opacity-90">Sistem akan kembali normal segera</p>
            </div>
        </div>

        <!-- Info Card -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Butuh Bantuan?
            </h3>
            <p class="text-gray-600 text-sm mb-3">
                Jika ada keperluan mendesak, silakan hubungi:
            </p>
            <div class="space-y-2 text-sm">
                <div class="flex items-center text-gray-700">
                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <span>Admin KUA Banjarmasin Utara</span>
                </div>
                <div class="flex items-center text-gray-700">
                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Senin - Jumat: 08:00 - 16:00 WITA</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-500 text-sm">
            <p>© {{ date('Y') }} KUA Kecamatan Banjarmasin Utara</p>
            <p class="mt-1">Terima kasih atas pengertian Anda</p>
        </div>
    </div>

    <!-- Auto Refresh Script -->
    <script>
        // Auto refresh setiap 5 menit untuk cek apakah maintenance sudah selesai
        setTimeout(function() {
            location.reload();
        }, 300000); // 5 menit

        // Countdown timer
        let timeLeft = 1800; // 30 menit dalam detik
        const timerElement = document.querySelector('.text-3xl.font-bold');
        
        setInterval(() => {
            if (timeLeft > 0) {
                timeLeft--;
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                // Update hanya jika elemen ada
                if (timerElement && minutes >= 0) {
                    timerElement.textContent = `~${minutes} Menit`;
                }
            }
        }, 60000); // Update setiap 1 menit
    </script>
</body>
</html>
