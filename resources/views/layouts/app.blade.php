<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LKH KUA Banjarmasin Utara')</title>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="{{ asset('images/logo-kua.png') }}" as="image" crossorigin>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="bg-gray-50 antialiased">
    <div class="min-h-screen flex" x-data="{ 
        sidebarOpen: true,
        init() {
            // Set initial state: open on desktop, closed on mobile
            this.sidebarOpen = window.innerWidth >= 1024;
        }
    }">
        @auth
        <!-- Sidebar -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0"
        >
            <div class="flex flex-col h-full">
                <!-- Logo & Title -->
                <div class="flex items-center space-x-3 px-4 py-4 border-b border-gray-200">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 flex-1">
                        @if(file_exists(public_path('images/logo-kua.png')))
                            <img src="{{ asset('images/logo-kua.png') }}" alt="Logo KUA" class="h-12 w-auto object-contain" loading="lazy" width="48" height="48">
                        @elseif(file_exists(public_path('images/logo-kua.jpg')))
                            <img src="{{ asset('images/logo-kua.jpg') }}" alt="Logo KUA" class="h-12 w-auto object-contain" loading="lazy" width="48" height="48">
                        @elseif(file_exists(public_path('images/logo-kua.svg')))
                            <img src="{{ asset('images/logo-kua.svg') }}" alt="Logo KUA" class="h-12 w-auto object-contain" loading="lazy" width="48" height="48">
                        @else
                            <div class="h-10 w-10 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-white font-bold text-sm">KUA</span>
                            </div>
                        @endif
                        <div class="flex-shrink-0">
                            <h1 class="text-lg font-bold text-gray-900">LKH KUA</h1>
                            <p class="text-xs text-gray-500">Banjarmasin Utara</p>
                        </div>
                    </a>
                    <!-- Close button for mobile -->
                    <button 
                        @click="sidebarOpen = false"
                        class="lg:hidden text-gray-500 hover:text-gray-700"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Menu -->
                <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>
                    
                    @if(Auth::user()->isKepalaKua())
                    <a href="{{ route('lkh.staff') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('lkh.staff') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        LKH Staff
                    </a>
                    <a href="{{ route('lkh.saya') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('lkh.saya') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        LKH Saya
                    </a>
                    @else
                    <a href="{{ route('lkh.index') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('lkh.index') || request()->routeIs('lkh.saya') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        LKH Saya
                    </a>
                    @endif
                    
                    @if(Auth::user()->isKepalaKua())
                    <a href="{{ route('laporan-bulanan.index') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('laporan-bulanan.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Laporan Bulanan (Semua Staff)
                    </a>
                    @else
                    <a href="{{ route('laporan-bulanan.index') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('laporan-bulanan.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Laporan Bulanan Saya
                    </a>
                    @endif
                    
                    <a href="{{ route('laporan-triwulanan.index') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('laporan-triwulanan.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Laporan Triwulanan
                    </a>
                    
                    <a href="{{ route('laporan-tahunan.index') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('laporan-tahunan.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Laporan Tahunan
                    </a>
                    
                    <a href="{{ route('skp.index') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('skp.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        SKP
                    </a>
                </nav>

                <!-- User Info & Logout -->
                <div class="border-t border-gray-200 p-4">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold flex-shrink-0">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-md transition">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Sidebar Overlay (Mobile only) -->
        <div 
            x-show="sidebarOpen"
            @click="sidebarOpen = false"
            x-cloak
            class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden transition-opacity duration-300"
            style="display: none;"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        ></div>
        @endauth

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 lg:ml-64">
            @auth
            <!-- Top Header -->
            <header class="bg-white shadow-sm sticky top-0 z-30">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <!-- Toggle Sidebar Button -->
                        <button 
                            @click="sidebarOpen = !sidebarOpen"
                            class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition"
                            title="Toggle Sidebar"
                        >
                            <!-- Menu Icon (when sidebar closed) -->
                            <svg x-show="!sidebarOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <!-- Close Icon (when sidebar open) -->
                            <svg x-show="sidebarOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </header>
            @endauth

            <!-- Main Content -->
            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6">
                @if(session('success'))
                    <div x-data="{ show: true }" 
                         x-show="show"
                         x-init="setTimeout(() => show = false, 3000)"
                         class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between"
                    >
                        <div class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ session('success') }}
                        </div>
                        <button x-on:click="show = false" class="text-green-600 hover:text-green-800">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" 
                         x-show="show"
                         x-init="setTimeout(() => show = false, 5000)"
                         class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center justify-between"
                    >
                        <div class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            {{ session('error') }}
                        </div>
                        <button x-on:click="show = false" class="text-red-600 hover:text-red-800">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @if(session('info'))
                    <div x-data="{ show: true }" 
                         x-show="show"
                         x-init="setTimeout(() => show = false, 3000)"
                         class="mb-4 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg flex items-center justify-between"
                    >
                        <div class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            {{ session('info') }}
                        </div>
                        <button x-on:click="show = false" class="text-blue-600 hover:text-blue-800">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 mt-auto">
                <div class="px-4 sm:px-6 lg:px-8 py-4">
                    <p class="text-center text-sm text-gray-500">
                        &copy; {{ date('Y') }} LKH KUA Banjarmasin Utara. All rights reserved.
                    </p>
                </div>
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
