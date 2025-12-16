<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LKH KUA Banjarmasin Utara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full space-y-8 p-8">
        <div class="text-center">
            <div class="flex justify-center mb-4">
                @if(file_exists(public_path('images/logo-kua.png')))
                    <img src="{{ asset('images/logo-kua.png') }}" alt="Logo KUA" class="h-24 w-auto object-contain">
                @elseif(file_exists(public_path('images/logo-kua.jpg')))
                    <img src="{{ asset('images/logo-kua.jpg') }}" alt="Logo KUA" class="h-24 w-auto object-contain">
                @elseif(file_exists(public_path('images/logo-kua.svg')))
                    <img src="{{ asset('images/logo-kua.svg') }}" alt="Logo KUA" class="h-24 w-auto object-contain">
                @else
                    <!-- Placeholder logo jika belum ada file -->
                    <div class="h-20 w-20 bg-blue-600 rounded-lg flex items-center justify-center mx-auto">
                        <span class="text-white font-bold text-2xl">KUA</span>
                    </div>
                @endif
            </div>
            <h1 class="text-3xl font-bold text-gray-900">LKH KUA</h1>
            <p class="mt-2 text-sm text-gray-600">Kantor Urusan Agama Banjarmasin Utara</p>
            <p class="mt-1 text-xs text-gray-500">Sistem Laporan Kegiatan Harian</p>
        </div>

        <x-card>
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email
                    </label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        autocomplete="email" 
                        required 
                        autofocus
                        value="{{ old('email') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                        placeholder="email@example.com"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password
                    </label>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        autocomplete="current-password" 
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            id="remember" 
                            name="remember" 
                            type="checkbox" 
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Ingat saya
                        </label>
                    </div>
                </div>

                <div>
                    <x-button type="submit" variant="primary" class="w-full" size="lg">
                        Masuk
                    </x-button>
                </div>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-xs text-center text-gray-500">
                    Default password untuk semua user: <strong>password</strong>
                </p>
                <div class="mt-4 space-y-2 text-xs text-gray-600">
                    <p><strong>Kepala KUA:</strong> kepalakua@kua-banjarutara.go.id</p>
                    <p><strong>Penghulu:</strong> penghulu1@kua-banjarutara.go.id</p>
                    <p><strong>Penyuluh:</strong> penyuluh1@kua-banjarutara.go.id</p>
                    <p><strong>Pelaksana:</strong> pelaksana1@kua-banjarutara.go.id</p>
                </div>
            </div>
        </x-card>
    </div>
</body>
</html>

