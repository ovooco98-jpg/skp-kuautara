@extends('layouts.app')

@section('title', 'Laporan Bulanan')

@section('content')
<div>
    <!-- Page Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            @if(Auth::user()->isKepalaKua())
            <h1 class="text-2xl font-bold text-gray-900">Laporan Bulanan (Semua Staff)</h1>
            <p class="mt-1 text-sm text-gray-500">Konsolidasi LKH harian menjadi laporan bulanan dari seluruh staff</p>
            @else
            <h1 class="text-2xl font-bold text-gray-900">Laporan Bulanan Saya</h1>
            <p class="mt-1 text-sm text-gray-500">Konsolidasi LKH harian menjadi laporan bulanan</p>
            @endif
        </div>
        <button onclick="openCreateLaporanModal()" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center space-x-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Buat Laporan Bulanan</span>
        </button>
    </div>

    <!-- Filters -->
    <x-card class="mb-6">
        <form method="GET" action="{{ route('laporan-bulanan.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <select name="tahun" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Tahun</option>
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                <select name="bulan" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Bulan</option>
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->locale('id')->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(Auth::user()->isKepalaKua())
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pegawai</label>
                <select name="user_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Pegawai</option>
                    @foreach(\App\Models\User::aktif()->where('role', '!=', 'kepala_kua')->get() as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="flex items-end">
                <x-button type="submit" variant="primary" class="w-full" size="sm">Filter</x-button>
            </div>
        </form>
    </x-card>

    <!-- Laporan Bulanan Table -->
    <x-card>
        @if($laporan->count() > 0)
        <div class="overflow-x-auto overflow-y-auto max-h-[600px]" style="scrollbar-width: thin; scrollbar-color: #cbd5e1 #f1f5f9;">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                        @if(Auth::user()->isKepalaKua())
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pegawai</th>
                        @endif
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total LKH</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Durasi</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($laporan as $lap)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $lap->nama_bulan }}</div>
                            <div class="text-xs text-gray-500">{{ $lap->tahun }}</div>
                        </td>
                        @if(Auth::user()->isKepalaKua())
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-900">{{ $lap->user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $lap->user->jabatan ?? '-' }}</div>
                        </td>
                        @endif
                        <td class="px-4 py-3 text-center">
                            <span class="text-sm text-gray-900">{{ $lap->total_lkh }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-sm text-gray-900">{{ number_format($lap->total_durasi, 1) }} jam</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <x-badge variant="{{ $lap->status === 'selesai' ? 'success' : 'default' }}" size="sm">
                                {{ strtoupper($lap->status) }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-sm text-gray-500">{{ $lap->created_at->format('d/m/Y') }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <x-button href="{{ route('laporan-bulanan.show', $lap->id) }}" variant="primary" size="sm" icon="eye">
                                    Lihat
                                </x-button>
                                @if($lap->user_id === Auth::id() || Auth::user()->isKepalaKua())
                                <button onclick="openEditLaporanModal({{ $lap->id }})" class="px-2 py-1 text-xs border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 transition">
                                    Edit
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <x-icon name="document" class="h-16 w-16 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada laporan bulanan</h3>
            <p class="text-gray-500 mb-4">Mulai dengan membuat laporan bulanan baru</p>
            <button onclick="openCreateLaporanModal()" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center space-x-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Buat Laporan Bulanan</span>
            </button>
        </div>
        @endif
    </x-card>

    <!-- Pagination -->
    @if($laporan->hasPages())
    <div class="mt-6">
        {{ $laporan->links() }}
    </div>
    @endif
</div>

<!-- Modal Create Laporan Bulanan -->
<x-modal name="create-laporan-modal" title="Buat Laporan Bulanan" maxWidth="6xl">
    <div id="create-laporan-container">
        <div class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-sm text-gray-600">Memuat form...</p>
        </div>
    </div>
</x-modal>

<!-- Modal Edit Laporan Bulanan -->
<x-modal name="edit-laporan-modal" title="Edit Laporan Bulanan" maxWidth="6xl">
    <div id="edit-laporan-container">
        <div class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-sm text-gray-600">Memuat form...</p>
        </div>
    </div>
</x-modal>

@push('scripts')
<script>
    // Create Laporan Bulanan Modal
    function openCreateLaporanModal() {
        document.getElementById('create-laporan-container').innerHTML = `
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Periode</label>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Bulan</label>
                        <select id="create-bulan" class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $m, 1)->locale('id')->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Tahun</label>
                        <select id="create-tahun" class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <button onclick="loadCreateLaporanForm()" class="mt-3 w-full px-3 py-1.5 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                    Lanjutkan
                </button>
            </div>
        `;
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-laporan-modal' }));
    }

    function loadCreateLaporanForm() {
        const bulan = document.getElementById('create-bulan').value;
        const tahun = document.getElementById('create-tahun').value;
        
        document.getElementById('create-laporan-container').innerHTML = `
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-sm text-gray-600">Memuat form...</p>
            </div>
        `;

        fetch(`/laporan-bulanan/create?bulan=${bulan}&tahun=${tahun}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.lkh && data.data.lkh.length > 0) {
                // Build form HTML (simplified version for modal)
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
                const lkh = data.data.lkh;
                const totalDurasi = lkh.reduce((sum, item) => sum + parseFloat(item.durasi || 0), 0);
                
                // Group LKH by date
                const lkhByDate = {};
                lkh.forEach(item => {
                    const date = item.tanggal;
                    if (!lkhByDate[date]) {
                        lkhByDate[date] = [];
                    }
                    lkhByDate[date].push(item);
                });
                
                // Create checkboxes grouped by date
                let lkhCheckboxes = '';
                Object.keys(lkhByDate).sort().forEach(date => {
                    const dateLabel = new Date(date).toLocaleDateString('id-ID', { 
                        weekday: 'short', 
                        day: 'numeric', 
                        month: 'long', 
                        year: 'numeric' 
                    });
                    lkhCheckboxes += `<div class="lkh-date-group mb-2" data-date="${date}">
                        <div class="text-xs font-semibold text-gray-700 mb-1 px-1">${dateLabel}</div>
                    `;
                    lkhByDate[date].forEach(item => {
                        lkhCheckboxes += `
                            <label class="lkh-item flex items-start space-x-2 p-1.5 rounded border border-gray-200 hover:bg-gray-50 cursor-pointer mb-1" 
                                   data-text="${(item.uraian_kegiatan || '').toLowerCase()}">
                                <input type="checkbox" name="lkh_ids[]" value="${item.id}" checked
                                       class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <div class="flex-1 text-xs">
                                    <div class="text-gray-600">${(item.uraian_kegiatan || '').substring(0, 60)}</div>
                                    <div class="text-gray-400 text-xs">${parseFloat(item.durasi || 0).toFixed(1)} jam</div>
                                </div>
                            </label>
                        `;
                    });
                    lkhCheckboxes += `</div>`;
                });

                let formHtml = `
                    <form id="create-laporan-form" method="POST" action="{{ route('laporan-bulanan.store') }}">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="bulan" value="${bulan}">
                        <input type="hidden" name="tahun" value="${tahun}">
                        
                        <div class="mb-4">
                            @if(Auth::user()->isKepalaKua())
                            <button type="button" onclick="generateOtomatisLaporan('create-laporan-form')" 
                                    class="w-full px-4 py-2 text-sm bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-md hover:from-blue-700 hover:to-blue-800 transition flex items-center justify-center space-x-2 shadow-md">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <span>Generate Otomatis (Bahasa Profesional)</span>
                            </button>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <!-- Kolom Kiri -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Ringkasan Kegiatan</label>
                                    <textarea id="create-ringkasan-kegiatan" name="ringkasan_kegiatan" rows="4"
                                              class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="Ringkasan kegiatan yang telah dilaksanakan..."></textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Pencapaian</label>
                                    <textarea id="create-pencapaian" name="pencapaian" rows="4"
                                              class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="Pencapaian dan hasil yang diperoleh..."></textarea>
                                </div>
                            </div>

                            <!-- Kolom Kanan -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Kendala</label>
                                    <textarea id="create-kendala" name="kendala" rows="4"
                                              class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="Kendala yang dihadapi..."></textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Rencana Bulan Depan</label>
                                    <textarea id="create-rencana-bulan-depan" name="rencana_bulan_depan" rows="4"
                                              class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="Rencana kegiatan untuk bulan depan..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Summary & LKH Selection -->
                        <div class="mt-4 pt-4 border-t space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-3 rounded-md">
                                    <div class="text-xs space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Total LKH:</span>
                                            <span class="font-semibold">${lkh.length} kegiatan</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Total Durasi:</span>
                                            <span class="font-semibold">${totalDurasi.toFixed(1)} jam</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-xs font-medium text-gray-700">Pilih LKH</label>
                                        <div class="flex gap-1">
                                            <button type="button" onclick="selectAllLkh('create-laporan-form')" 
                                                    class="text-xs px-2 py-0.5 text-blue-600 hover:text-blue-700 hover:underline">
                                                Pilih Semua
                                            </button>
                                            <span class="text-gray-400">|</span>
                                            <button type="button" onclick="deselectAllLkh('create-laporan-form')" 
                                                    class="text-xs px-2 py-0.5 text-gray-600 hover:text-gray-700 hover:underline">
                                                Batal Semua
                                            </button>
                                        </div>
                                    </div>
                                    <input type="text" id="create-lkh-search" placeholder="Cari LKH..." 
                                           oninput="filterLkhList('create-laporan-form', this.value)"
                                           class="w-full mb-2 px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <div id="create-lkh-list" class="space-y-1 max-h-60 overflow-y-auto border border-gray-200 rounded-md p-2 bg-gray-50">
                                        ${lkhCheckboxes}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 mt-4 border-t">
                            <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal'))" 
                                    class="px-4 py-2 text-sm border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit" id="create-laporan-submit-btn"
                                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                Simpan
                            </button>
                        </div>
                    </form>
                `;

                document.getElementById('create-laporan-container').innerHTML = formHtml;

                // Setup form submit
                document.getElementById('create-laporan-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const form = e.target;
                    const formData = new FormData(form);
                    const submitBtn = document.getElementById('create-laporan-submit-btn');
                    
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Menyimpan...';

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.dispatchEvent(new CustomEvent('close-modal'));
                            window.location.href = '/laporan-bulanan/' + data.data.id;
                        } else {
                            alert('Error: ' + (data.message || 'Gagal menyimpan laporan'));
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Simpan';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menyimpan laporan');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Simpan';
                    });
                });
            } else {
                document.getElementById('create-laporan-container').innerHTML = `
                    <div class="text-center py-8 text-red-600">
                        <p>${data.message || 'Tidak ada LKH untuk periode ini'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('create-laporan-container').innerHTML = `
                <div class="text-center py-8 text-red-600">
                    <p>Terjadi kesalahan saat memuat form.</p>
                </div>
            `;
        });
    }

    // Edit Laporan Bulanan Modal
    function openEditLaporanModal(laporanId) {
        document.getElementById('edit-laporan-container').innerHTML = `
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-sm text-gray-600">Memuat form...</p>
            </div>
        `;
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-laporan-modal' }));

        fetch(`/laporan-bulanan/${laporanId}/edit`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const laporan = data.data.laporan;
                const lkh = data.data.lkh;
                const selectedLkhIds = laporan.lkh ? laporan.lkh.map(l => l.id) : [];
                const totalDurasi = laporan.total_durasi || 0;

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
                
                // Group LKH by date
                const lkhByDate = {};
                lkh.forEach(item => {
                    const date = item.tanggal;
                    if (!lkhByDate[date]) {
                        lkhByDate[date] = [];
                    }
                    lkhByDate[date].push(item);
                });
                
                // Create checkboxes grouped by date
                let lkhCheckboxes = '';
                Object.keys(lkhByDate).sort().forEach(date => {
                    const dateLabel = new Date(date).toLocaleDateString('id-ID', { 
                        weekday: 'short', 
                        day: 'numeric', 
                        month: 'long', 
                        year: 'numeric' 
                    });
                    lkhCheckboxes += `<div class="lkh-date-group mb-2" data-date="${date}">
                        <div class="text-xs font-semibold text-gray-700 mb-1 px-1">${dateLabel}</div>
                    `;
                    lkhByDate[date].forEach(item => {
                        lkhCheckboxes += `
                            <label class="lkh-item flex items-start space-x-2 p-1.5 rounded border border-gray-200 hover:bg-gray-50 cursor-pointer mb-1" 
                                   data-text="${(item.uraian_kegiatan || '').toLowerCase()}">
                                <input type="checkbox" name="lkh_ids[]" value="${item.id}" 
                                       ${selectedLkhIds.includes(item.id) ? 'checked' : ''}
                                       class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <div class="flex-1 text-xs">
                                    <div class="text-gray-600">${(item.uraian_kegiatan || '').substring(0, 60)}</div>
                                    <div class="text-gray-400 text-xs">${parseFloat(item.durasi || 0).toFixed(1)} jam</div>
                                </div>
                            </label>
                        `;
                    });
                    lkhCheckboxes += `</div>`;
                });

                let formHtml = `
                    <form id="edit-laporan-form" method="POST" action="/laporan-bulanan/${laporanId}">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="PUT">
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <!-- Kolom Kiri -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Ringkasan Kegiatan</label>
                                    <textarea name="ringkasan_kegiatan" rows="4"
                                              class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="Ringkasan kegiatan yang telah dilaksanakan...">${laporan.ringkasan_kegiatan || ''}</textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Pencapaian</label>
                                    <textarea name="pencapaian" rows="4"
                                              class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="Pencapaian dan hasil yang diperoleh...">${laporan.pencapaian || ''}</textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                    <select name="status" class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="draft" ${laporan.status === 'draft' ? 'selected' : ''}>Draft</option>
                                        <option value="selesai" ${laporan.status === 'selesai' ? 'selected' : ''}>Selesai</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Kolom Kanan -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Kendala</label>
                                    <textarea name="kendala" rows="4"
                                              class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="Kendala yang dihadapi...">${laporan.kendala || ''}</textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Rencana Bulan Depan</label>
                                    <textarea name="rencana_bulan_depan" rows="4"
                                              class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="Rencana kegiatan untuk bulan depan...">${laporan.rencana_bulan_depan || ''}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Summary & LKH Selection -->
                        <div class="mt-4 pt-4 border-t space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-3 rounded-md">
                                    <div class="text-xs space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Total LKH:</span>
                                            <span class="font-semibold">${laporan.total_lkh || 0} kegiatan</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Total Durasi:</span>
                                            <span class="font-semibold">${parseFloat(totalDurasi).toFixed(1)} jam</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-xs font-medium text-gray-700">Pilih LKH</label>
                                        <div class="flex gap-1">
                                            <button type="button" onclick="selectAllLkh('edit-laporan-form')" 
                                                    class="text-xs px-2 py-0.5 text-blue-600 hover:text-blue-700 hover:underline">
                                                Pilih Semua
                                            </button>
                                            <span class="text-gray-400">|</span>
                                            <button type="button" onclick="deselectAllLkh('edit-laporan-form')" 
                                                    class="text-xs px-2 py-0.5 text-gray-600 hover:text-gray-700 hover:underline">
                                                Batal Semua
                                            </button>
                                        </div>
                                    </div>
                                    <input type="text" id="edit-lkh-search" placeholder="Cari LKH..." 
                                           oninput="filterLkhList('edit-laporan-form', this.value)"
                                           class="w-full mb-2 px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <div id="edit-lkh-list" class="space-y-1 max-h-60 overflow-y-auto border border-gray-200 rounded-md p-2 bg-gray-50">
                                        ${lkhCheckboxes}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 mt-4 border-t">
                            <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal'))" 
                                    class="px-4 py-2 text-sm border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit" id="edit-laporan-submit-btn"
                                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                `;

                document.getElementById('edit-laporan-container').innerHTML = formHtml;

                // Setup form submit
                document.getElementById('edit-laporan-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const form = e.target;
                    const formData = new FormData(form);
                    const submitBtn = document.getElementById('edit-laporan-submit-btn');
                    
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Menyimpan...';

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.dispatchEvent(new CustomEvent('close-modal'));
                            window.location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Gagal menyimpan perubahan'));
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Simpan Perubahan';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menyimpan perubahan');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Simpan Perubahan';
                    });
                });
            } else {
                document.getElementById('edit-laporan-container').innerHTML = `
                    <div class="text-center py-8 text-red-600">
                        <p>${data.message || 'Gagal memuat form.'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('edit-laporan-container').innerHTML = `
                <div class="text-center py-8 text-red-600">
                    <p>Terjadi kesalahan saat memuat form.</p>
                </div>
            `;
        });
    }

    // Generate Otomatis Laporan
    function generateOtomatisLaporan(formId) {
        const form = document.getElementById(formId);
        if (!form) {
            alert('Form tidak ditemukan');
            return;
        }

        // Ambil LKH yang dipilih
        const selectedLkhIds = Array.from(form.querySelectorAll('input[name="lkh_ids[]"]:checked'))
            .map(cb => cb.value);

        if (selectedLkhIds.length === 0) {
            alert('Pilih minimal satu LKH untuk di-generate');
            return;
        }

        // Ambil bulan dan tahun
        const bulan = form.querySelector('input[name="bulan"]').value;
        const tahun = form.querySelector('input[name="tahun"]').value;

        // Tampilkan loading
        const generateBtn = event.target.closest('button');
        const originalText = generateBtn.innerHTML;
        generateBtn.disabled = true;
        generateBtn.innerHTML = '<div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>Menggenerate...';

        // Panggil API
        fetch('/laporan-bulanan/generate-otomatis', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                lkh_ids: selectedLkhIds,
                bulan: bulan,
                tahun: tahun
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Isi form dengan hasil generate
                document.getElementById('create-ringkasan-kegiatan').value = data.data.ringkasan_kegiatan || '';
                document.getElementById('create-pencapaian').value = data.data.pencapaian || '';
                document.getElementById('create-kendala').value = data.data.kendala || '';
                document.getElementById('create-rencana-bulan-depan').value = data.data.rencana_bulan_depan || '';
                
                // Scroll ke atas form
                document.getElementById('create-ringkasan-kegiatan').scrollIntoView({ behavior: 'smooth', block: 'start' });
                
                alert('Generate otomatis berhasil! Silakan review dan edit jika diperlukan.');
            } else {
                alert('Error: ' + (data.message || 'Gagal mengenerate laporan'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengenerate laporan');
        })
        .finally(() => {
            generateBtn.disabled = false;
            generateBtn.innerHTML = originalText;
        });
    }

    // Helper functions for LKH selection
    function selectAllLkh(formId) {
        const listContainer = formId === 'create-laporan-form' 
            ? document.getElementById('create-lkh-list')
            : document.getElementById('edit-lkh-list');
        
        if (!listContainer) {
            console.error('List container not found for form:', formId);
            return;
        }
        
        // Select all visible checkboxes
        const visibleItems = listContainer.querySelectorAll('.lkh-item:not([style*="display: none"]) input[name="lkh_ids[]"]');
        const allItems = listContainer.querySelectorAll('input[name="lkh_ids[]"]');
        
        // If filter is active, only select visible ones, otherwise select all
        const itemsToSelect = visibleItems.length > 0 ? visibleItems : allItems;
        itemsToSelect.forEach(cb => {
            cb.checked = true;
        });
    }

    function deselectAllLkh(formId) {
        const listContainer = formId === 'create-laporan-form' 
            ? document.getElementById('create-lkh-list')
            : document.getElementById('edit-lkh-list');
        
        if (!listContainer) {
            console.error('List container not found for form:', formId);
            return;
        }
        
        // Deselect all visible checkboxes
        const visibleItems = listContainer.querySelectorAll('.lkh-item:not([style*="display: none"]) input[name="lkh_ids[]"]');
        const allItems = listContainer.querySelectorAll('input[name="lkh_ids[]"]');
        
        // If filter is active, only deselect visible ones, otherwise deselect all
        const itemsToDeselect = visibleItems.length > 0 ? visibleItems : allItems;
        itemsToDeselect.forEach(cb => {
            cb.checked = false;
        });
    }

    function filterLkhList(formId, searchText) {
        const listContainer = formId === 'create-laporan-form' 
            ? document.getElementById('create-lkh-list')
            : document.getElementById('edit-lkh-list');
        
        if (!listContainer) {
            console.error('List container not found for form:', formId);
            return;
        }
        
        const searchLower = searchText.toLowerCase().trim();
        const items = listContainer.querySelectorAll('.lkh-item');
        const dateGroups = listContainer.querySelectorAll('.lkh-date-group');
        
        if (searchLower === '') {
            // Show all items
            items.forEach(item => item.style.display = '');
            dateGroups.forEach(group => group.style.display = '');
            return;
        }
        
        // Filter items
        items.forEach(item => {
            const itemText = item.getAttribute('data-text') || '';
            
            if (itemText.includes(searchLower)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
        
        // Hide date groups that have no visible items
        dateGroups.forEach(group => {
            const visibleItems = group.querySelectorAll('.lkh-item:not([style*="display: none"])');
            if (visibleItems.length === 0) {
                group.style.display = 'none';
            } else {
                group.style.display = '';
            }
        });
    }
</script>
@endpush

@endsection

