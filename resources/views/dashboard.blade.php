@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600">Selamat datang, {{ Auth::user()->name }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-500">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}</span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total LKH -->
        <x-card padding="false" class="overflow-hidden transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5">
            <div class="p-5 bg-blue-600">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-blue-50 text-xs font-medium uppercase tracking-wide mb-2">Total LKH Bulan Ini</p>
                        <p class="text-3xl font-bold text-white mb-0" id="total-lkh">0</p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <div class="bg-blue-500/30 rounded-lg p-2.5">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Selesai -->
        <x-card padding="false" class="overflow-hidden transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5">
            <div class="p-5 bg-green-600">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-green-50 text-xs font-medium uppercase tracking-wide mb-2">LKH Selesai</p>
                        <p class="text-3xl font-bold text-white mb-0" id="selesai-count">0</p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <div class="bg-green-500/30 rounded-lg p-2.5">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Draft -->
        <x-card padding="false" class="overflow-hidden transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5">
            <div class="p-5 bg-amber-600">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-amber-50 text-xs font-medium uppercase tracking-wide mb-2">Total LKH</p>
                        <p class="text-3xl font-bold text-white mb-0" id="total-lkh-count">0</p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <div class="bg-amber-500/30 rounded-lg p-2.5">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Total Pegawai (Kepala KUA only) -->
        @if(Auth::user()->isKepalaKua())
        <x-card padding="false" class="overflow-hidden transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5">
            <div class="p-5 bg-purple-600">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-purple-50 text-xs font-medium uppercase tracking-wide mb-2">Total Pegawai</p>
                        <p class="text-3xl font-bold text-white mb-0" id="total-pegawai">0</p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <div class="bg-purple-500/30 rounded-lg p-2.5">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>
        @else
        <!-- LKH Hari Ini (Pegawai) -->
        <x-card padding="false" class="overflow-hidden transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5">
            <div class="p-5 bg-indigo-600">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-indigo-50 text-xs font-medium uppercase tracking-wide mb-2">LKH Hari Ini</p>
                        <p class="text-3xl font-bold text-white mb-0" id="lkh-hari-ini-count">0</p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <div class="bg-indigo-500/30 rounded-lg p-2.5">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent LKH / LKH Hari Ini -->
        <div class="lg:col-span-2">
            @if(Auth::user()->isKepalaKua())
            <x-card title="LKH Terbaru" class="h-full">
                <div id="recent-lkh" class="space-y-4">
                    <div class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-gray-500">Memuat data...</p>
                    </div>
                </div>
            </x-card>
            @else
            <x-card title="LKH Hari Ini" class="h-full">
                <div id="lkh-hari-ini" class="space-y-4">
                    <div class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-gray-500">Memuat data...</p>
                    </div>
                </div>
            </x-card>
            @endif
        </div>

        <!-- Quick Actions -->
        <div>
            <x-card title="Quick Actions" class="h-full text-sm">
                <div class="space-y-1">
                    <button onclick="openCreateModal()" class="w-full flex items-center px-2 py-1.5 text-xs text-gray-700 hover:bg-gray-50 rounded-md transition text-left">
                        <svg class="h-4 w-4 mr-1.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Buat LKH Baru
                    </button>
                    <a href="{{ route('lkh.index') }}" class="flex items-center px-2 py-1.5 text-xs text-gray-700 hover:bg-gray-50 rounded-md transition">
                        <svg class="h-4 w-4 mr-1.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Lihat Semua LKH
                    </a>
                    <a href="{{ route('laporan-bulanan.index') }}" class="flex items-center px-2 py-1.5 text-xs text-gray-700 hover:bg-gray-50 rounded-md transition">
                        <svg class="h-4 w-4 mr-1.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Laporan Bulanan
                    </a>
                    @if(Auth::user()->isKepalaKua())
                    <a href="{{ route('export.laporan-bulanan') }}" class="flex items-center px-2 py-1.5 text-xs text-gray-700 hover:bg-gray-50 rounded-md transition">
                        <svg class="h-4 w-4 mr-1.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Export Laporan
                    </a>
                    @endif
                </div>
            </x-card>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Fetch dashboard data
    fetch('{{ route("dashboard") }}', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Dashboard data loaded:', data);
            if (data && data.success) {
                const dashboardData = data.data;
                
                // Update total pegawai jika ada (untuk Kepala KUA)
                if (dashboardData.total_pegawai !== undefined) {
                    const totalPegawaiEl = document.getElementById('total-pegawai');
                    if (totalPegawaiEl) {
                        totalPegawaiEl.textContent = dashboardData.total_pegawai || 0;
                        console.log('Total Pegawai updated:', dashboardData.total_pegawai);
                    }
                }
                
                // Update stats cards
                if (dashboardData.lkh_bulan_ini) {
                    // For pegawai
                    const stats = dashboardData.lkh_bulan_ini;
                    const totalLkhEl = document.getElementById('total-lkh');
                    const selesaiCountEl = document.getElementById('selesai-count');
                    const totalLkhCountEl = document.getElementById('total-lkh-count');
                    
                    if (totalLkhEl) totalLkhEl.textContent = dashboardData.total_lkh_bulan_ini || 0;
                    if (selesaiCountEl) selesaiCountEl.textContent = stats.selesai || 0;
                    if (totalLkhCountEl) totalLkhCountEl.textContent = (stats.draft || 0) + (stats.selesai || 0);
                    
                    // LKH hari ini count
                    if (dashboardData.lkh_hari_ini) {
                        const lkhHariIniCountEl = document.getElementById('lkh-hari-ini-count');
                        if (lkhHariIniCountEl) {
                            lkhHariIniCountEl.textContent = dashboardData.lkh_hari_ini.length || 0;
                        }
                        
                        // Display LKH hari ini
                        const container = document.getElementById('lkh-hari-ini');
                        if (container) {
                            if (dashboardData.lkh_hari_ini && dashboardData.lkh_hari_ini.length > 0) {
                            container.innerHTML = dashboardData.lkh_hari_ini.map(lkh => `
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 mb-2">
                                                <span class="text-sm font-semibold text-gray-900">${lkh.kategori_kegiatan?.nama || '-'}</span>
                                                <span class="px-2 py-0.5 text-xs rounded-full ${lkh.status === 'selesai' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                                    ${lkh.status ? lkh.status.toUpperCase() : 'DRAFT'}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-700 mb-2">${lkh.uraian_kegiatan || '-'}</p>
                                            <div class="flex items-center space-x-4 text-xs text-gray-500">
                                                <span>🕐 ${lkh.waktu_mulai || ''} - ${lkh.waktu_selesai || ''}</span>
                                                ${lkh.durasi ? `<span>⏱ ${parseFloat(lkh.durasi).toFixed(1)} jam</span>` : ''}
                                            </div>
                                        </div>
                                        <a href="/lkh/${lkh.id}" class="ml-3 text-blue-600 hover:text-blue-800">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            `).join('');
                        } else {
                            container.innerHTML = `
                                <div class="text-center py-8">
                                    <svg class="h-12 w-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-gray-500">Belum ada LKH untuk hari ini</p>
                                    <button onclick="openCreateModal()" class="mt-3 inline-block text-blue-600 hover:text-blue-800 text-sm font-medium">Buat LKH sekarang</button>
                                </div>
                            `;
                            }
                        }
                    }
                } else if (dashboardData.total_lkh) {
                    // For Kepala KUA
                    const stats = dashboardData.total_lkh;
                    const total = stats.draft + stats.selesai;
                    const totalLkhEl = document.getElementById('total-lkh');
                    const selesaiCountEl = document.getElementById('selesai-count');
                    const totalLkhCountEl = document.getElementById('total-lkh-count');
                    
                    if (totalLkhEl) totalLkhEl.textContent = total;
                    if (selesaiCountEl) selesaiCountEl.textContent = stats.selesai || 0;
                    if (totalLkhCountEl) totalLkhCountEl.textContent = (stats.draft || 0) + (stats.selesai || 0);
                }

                // Update recent LKH for Kepala KUA
                if (dashboardData.lkh_terakhir) {
                    const recentContainer = document.getElementById('recent-lkh');
                    if (recentContainer) {
                        if (dashboardData.lkh_terakhir.length > 0) {
                        recentContainer.innerHTML = dashboardData.lkh_terakhir.map(lkh => `
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span class="text-sm font-semibold text-gray-900">${lkh.user?.name || '-'}</span>
                                            <span class="px-2 py-0.5 text-xs rounded-full ${lkh.status === 'selesai' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                                ${lkh.status ? lkh.status.toUpperCase() : 'DRAFT'}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-700 mb-2 line-clamp-2">${lkh.uraian_kegiatan || '-'}</p>
                                        <div class="flex items-center space-x-4 text-xs text-gray-500">
                                            <span>📅 ${new Date(lkh.tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</span>
                                            <span>🕐 ${lkh.waktu_mulai || ''} - ${lkh.waktu_selesai || ''}</span>
                                        </div>
                                    </div>
                                    <a href="/lkh/${lkh.id}" class="ml-3 text-blue-600 hover:text-blue-800">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        recentContainer.innerHTML = `
                            <div class="text-center py-8">
                                <svg class="h-12 w-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-gray-500">Belum ada LKH</p>
                            </div>
                        `;
                        }
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error loading dashboard:', error);
            
            // Update stats dengan 0 jika error
            const statsIds = ['total-lkh', 'selesai-count', 'total-lkh-count', 'total-pegawai', 'lkh-hari-ini-count'];
            statsIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.textContent = '0';
            });
            
            const errorHtml = `
                <div class="text-center py-6">
                    <svg class="h-10 w-10 text-red-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-red-600 font-medium text-sm">Gagal memuat data</p>
                    <button onclick="location.reload()" class="mt-2 text-xs text-blue-600 hover:text-blue-800 underline">Refresh halaman</button>
                </div>
            `;
            
            const recentLkhEl = document.getElementById('recent-lkh');
            const lkhHariIniEl = document.getElementById('lkh-hari-ini');
            if (recentLkhEl) {
                recentLkhEl.innerHTML = errorHtml;
            }
            if (lkhHariIniEl) {
                lkhHariIniEl.innerHTML = errorHtml;
            }
        });
</script>

<!-- Modal Create LKH -->
<x-modal name="create-lkh-modal" title="Buat LKH Baru" maxWidth="6xl">
    <div id="create-form-container">
        <div class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-sm text-gray-600">Memuat form...</p>
        </div>
    </div>
</x-modal>

<script>
    // Include modal functions from lkh/index.blade.php
    let keywordMap = {};
    let kategoriKegiatanOptions = '';

    // Open create modal
    function openCreateModal(copyFromId = null) {
        // Reset form container
        const formContainer = document.getElementById('create-form-container');
        if (!formContainer) {
            console.error('create-form-container element not found');
            return;
        }
        
        formContainer.innerHTML = `
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-sm text-gray-600">Memuat form...</p>
            </div>
        `;

        // Open modal
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-lkh-modal' }));

        // Load form data
        const url = copyFromId ? `/lkh/create?copy_from=${copyFromId}` : '/lkh/create';
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                keywordMap = data.keyword_map || {};
                kategoriKegiatanOptions = data.kategori_kegiatan.map(k => 
                    `<option value="${k.id}">${k.nama}</option>`
                ).join('');

                const sourceLkh = data.source_lkh;
                const today = new Date().toISOString().split('T')[0];
                
                // Build form HTML
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                                 document.querySelector('input[name="_token"]')?.value || 
                                 '{{ csrf_token() }}';
                
                let formHtml = `
                    <form id="lkh-create-form" method="POST" action="{{ route('lkh.store') }}" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        ${sourceLkh ? `
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                            <p class="text-xs text-blue-800">
                                <strong>Copy dari LKH:</strong> ${new Date(sourceLkh.tanggal).toLocaleDateString('id-ID')} - ${sourceLkh.uraian_kegiatan}
                            </p>
                        </div>
                        ` : ''}
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-4 gap-y-3">
                            <!-- Kolom Kiri -->
                            <div class="space-y-3">
                                <!-- Tanggal -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Tanggal *</label>
                                    <input type="date" name="tanggal" id="create-tanggal" required 
                                           value="${sourceLkh ? sourceLkh.tanggal : today}"
                                           class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                </div>

                                <!-- Waktu -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Waktu *</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <input type="time" name="waktu_mulai" id="create-waktu_mulai" required
                                                   value="${sourceLkh ? sourceLkh.waktu_mulai : ''}"
                                                   placeholder="Mulai"
                                                   class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <input type="time" name="waktu_selesai" id="create-waktu_selesai" required
                                                   value="${sourceLkh ? sourceLkh.waktu_selesai : ''}"
                                                   placeholder="Selesai"
                                                   class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        </div>
                                    </div>
                                </div>

                                <!-- Kategori Kegiatan -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Kategori Kegiatan</label>
                                    <select name="kategori_kegiatan_id" id="create-kategori_kegiatan_id"
                                            class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        <option value="">Pilih Kategori (Opsional)</option>
                                        ${kategoriKegiatanOptions}
                                    </select>
                                </div>

                                <!-- Uraian Kegiatan -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Uraian Kegiatan *</label>
                                    <textarea name="uraian_kegiatan" id="create-uraian_kegiatan" rows="3" required
                                              class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                                              placeholder="Deskripsi kegiatan yang dilakukan...">${sourceLkh ? sourceLkh.uraian_kegiatan : ''}</textarea>
                                    <p class="mt-0.5 text-[10px] text-gray-500">Ketik untuk saran kategori otomatis</p>
                                </div>

                                <!-- Lampiran -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Link Lampiran (Drive)</label>
                                    <input type="url" name="lampiran" 
                                           placeholder="https://drive.google.com/file/d/..."
                                           class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    <p class="mt-0.5 text-[10px] text-gray-500">Upload file ke Drive, lalu paste link di sini</p>
                                </div>
                            </div>

                            <!-- Kolom Kanan -->
                            <div class="space-y-3">
                                <!-- Hasil Output -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Hasil/Output</label>
                                    <textarea name="hasil_output" id="create-hasil_output" rows="3"
                                              class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                                              placeholder="Hasil atau output dari kegiatan...">${sourceLkh ? (sourceLkh.hasil_output || '') : ''}</textarea>
                                </div>

                                <!-- Kendala -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Kendala</label>
                                    <textarea name="kendala" id="create-kendala" rows="3"
                                              class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                                              placeholder="Kendala yang dihadapi (jika ada)...">${sourceLkh ? (sourceLkh.kendala || '') : ''}</textarea>
                                </div>

                                <!-- Tindak Lanjut -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Tindak Lanjut</label>
                                    <textarea name="tindak_lanjut" id="create-tindak_lanjut" rows="3"
                                              class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                                              placeholder="Rencana tindak lanjut (jika ada)...">${sourceLkh ? (sourceLkh.tindak_lanjut || '') : ''}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-2 pt-3 mt-3 border-t">
                            <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal'))" 
                                    class="px-3 py-1.5 text-xs border border-gray-300 rounded text-gray-700 hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit" id="create-submit-btn"
                                    class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                Simpan sebagai Draft
                            </button>
                        </div>
                    </form>
                `;

                const formContainer = document.getElementById('create-form-container');
                if (formContainer) {
                    formContainer.innerHTML = formHtml;

                    // Setup auto-suggest
                    setupAutoSuggest();
                    
                    // Setup form submit
                    const form = document.getElementById('lkh-create-form');
                    if (form) {
                        form.removeEventListener('submit', handleCreateSubmit);
                        form.addEventListener('submit', handleCreateSubmit);
                    }
                }
            } else {
                const formContainer = document.getElementById('create-form-container');
                if (formContainer) {
                    formContainer.innerHTML = `
                        <div class="text-center py-8 text-red-600">
                            <p>Gagal memuat form. Silakan refresh halaman.</p>
                        </div>
                    `;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const formContainer = document.getElementById('create-form-container');
            if (formContainer) {
                formContainer.innerHTML = `
                    <div class="text-center py-8 text-red-600">
                        <p>Terjadi kesalahan saat memuat form.</p>
                    </div>
                `;
            }
        });
    }

    // Setup auto-suggest untuk kategori
    function setupAutoSuggest() {
        const uraianInput = document.getElementById('create-uraian_kegiatan');
        const kategoriSelect = document.getElementById('create-kategori_kegiatan_id');
        
        if (!uraianInput || !kategoriSelect) return;

        uraianInput.addEventListener('input', function() {
            const text = this.value.toLowerCase();
            
            // Reset selection
            if (kategoriSelect.value === '') {
                // Cari kategori yang cocok
                for (const [kategoriId, keywords] of Object.entries(keywordMap)) {
                    const match = keywords.some(keyword => text.includes(keyword));
                    if (match) {
                        kategoriSelect.value = kategoriId;
                        // Highlight suggestion
                        kategoriSelect.classList.add('border-green-500');
                        setTimeout(() => {
                            kategoriSelect.classList.remove('border-green-500');
                        }, 2000);
                        break;
                    }
                }
            }
        });
    }

    // Handle form submit
    function handleCreateSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('create-submit-btn');
        
        // Disable button
        submitBtn.disabled = true;
        submitBtn.textContent = 'Menyimpan...';

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                window.dispatchEvent(new CustomEvent('close-modal'));
                // Reload page to refresh dashboard
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Gagal menyimpan LKH'));
                submitBtn.disabled = false;
                submitBtn.textContent = 'Simpan sebagai Draft';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan LKH');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Simpan sebagai Draft';
        });
    }
</script>
@endpush
@endsection
