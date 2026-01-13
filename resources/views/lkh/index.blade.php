@extends('layouts.app')

@section('title', 'Daftar LKH')

@section('content')
<div class="space-y-4">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-gray-900">
                @if(isset($isStaffView) && $isStaffView)
                    Daftar LKH Staff
                @else
                    Daftar LKH Saya
                @endif
            </h1>
            <p class="mt-0.5 text-xs text-gray-500">Laporan Kegiatan Harian</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('export.excel') }}" onclick="exportWithFilter(event)" class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 transition">
                Export Excel
            </a>
            <button onclick="openCreateModal()" class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition">
                + Buat LKH
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-gray-200 rounded-lg p-3">
        <form id="filter-form" class="flex flex-wrap items-end gap-2">
            @if(isset($isStaffView) && $isStaffView && isset($users))
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">👤 Pegawai</label>
                <select name="user_id" id="filter-user-id" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">Semua Pegawai</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">📅 Tanggal</label>
                <input type="date" name="tanggal" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex-1 min-w-[120px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">📊 Status</label>
                <select name="status" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">🗓️ Bulan/Tahun</label>
                <input type="month" name="bulan_tahun" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-3 py-1 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition">
                    🔍 Filter
                </button>
                <button type="button" onclick="resetFilter()" class="px-3 py-1 text-xs font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-1 transition">
                    ↻ Reset
                </button>
            </div>
        </form>
    </div>

    <!-- LKH List (Grouped by Date) -->
    <div id="lkh-container" class="space-y-4">
        <div class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
            <p class="mt-1 text-xs text-gray-500">Memuat data...</p>
        </div>
    </div>
    <div id="pagination-container" class="mt-4"></div>
</div>

@push('scripts')
<script>
    // Load LKH data
    function loadLKH(filters = {}) {
        // Get filters from form
        const form = document.getElementById('filter-form');
        if (form) {
            const formData = new FormData(form);
            formData.forEach((value, key) => {
                if (value) {
                    filters[key] = value;
                }
            });
        }
        
        const params = new URLSearchParams(filters).toString();
        const currentRoute = @if(request()->routeIs('lkh.staff'))'{{ route('lkh.staff') }}'@elseif(request()->routeIs('lkh.saya'))'{{ route('lkh.saya') }}'@else'{{ route('lkh.index') }}'@endif;
        fetch(`${currentRoute}?${params}`, {
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
                console.log('LKH data loaded:', data);
                const container = document.getElementById('lkh-container');
                const paginationContainer = document.getElementById('pagination-container');
                
                if (!container) {
                    console.error('Container element not found');
                    return;
                }
                
                if (data.success && data.data && data.data.data && data.data.data.length > 0) {
                    const isStaffView = {{ (isset($isStaffView) && $isStaffView) ? 'true' : 'false' }};
                    
                    // Group LKH by date
                    const lkhByDate = {};
                    data.data.data.forEach(lkh => {
                        const date = lkh.tanggal.split('T')[0];
                        if (!lkhByDate[date]) {
                            lkhByDate[date] = [];
                        }
                        lkhByDate[date].push(lkh);
                    });
                    
                    // Build HTML grouped by date
                    let html = '';
                    Object.keys(lkhByDate).sort().reverse().forEach(date => {
                        const lkhList = lkhByDate[date];
                        const tanggal = new Date(date);
                        const tanggalStr = tanggal.toLocaleDateString('id-ID', { 
                            weekday: 'long',
                            day: 'numeric', 
                            month: 'long', 
                            year: 'numeric' 
                        });
                        const detailHarianUrl = `/lkh/detail-harian?tanggal=${date}${isStaffView && lkhList[0].user_id ? `&user_id=${lkhList[0].user_id}` : ''}`;
                        
                        html += `
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-semibold text-gray-900">${tanggalStr}</span>
                                        <a href="${detailHarianUrl}" class="text-blue-600 hover:text-blue-800 hover:underline text-xs" title="Lihat Detail Harian">
                                            📋 Detail Harian
                                        </a>
                                    </div>
                                    <span class="text-xs text-gray-500">${lkhList.length} kegiatan</span>
                                </div>
                                <div class="divide-y divide-gray-200">
                        `;
                        
                        lkhList.forEach(lkh => {
                            const statusClass = lkh.status === 'selesai' 
                                ? 'bg-green-100 text-green-800' 
                                : 'bg-gray-100 text-gray-800';
                            
                            // Format waktu
                            const waktuMulai = lkh.waktu_mulai ? (lkh.waktu_mulai.includes(':') ? lkh.waktu_mulai.substring(0, 5) : lkh.waktu_mulai) : '-';
                            const waktuSelesai = lkh.waktu_selesai ? (lkh.waktu_selesai.includes(':') ? lkh.waktu_selesai.substring(0, 5) : lkh.waktu_selesai) : '-';
                            
                            html += `
                                    <div class="px-4 py-3 hover:bg-gray-50 transition">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 min-w-0">
                                                ${isStaffView ? `
                                                <div class="mb-1">
                                                    <span class="text-xs font-medium text-gray-900">${lkh.user?.name || '-'}</span>
                                                    ${lkh.user?.jabatan ? `<span class="text-xs text-gray-500 ml-2">${lkh.user.jabatan}</span>` : ''}
                                                </div>
                                                ` : ''}
                                                <div class="text-sm text-gray-900 mb-1">
                                                    ${lkh.uraian_kegiatan || '-'}
                                                </div>
                                                <div class="flex items-center space-x-3 text-xs text-gray-500">
                                                    <span>🕐 ${waktuMulai} - ${waktuSelesai}</span>
                                                    <span class="px-2 py-0.5 rounded-full ${statusClass}">
                                                        ${(lkh.status || 'draft').toUpperCase()}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2 ml-4">
                                                <a href="/lkh/${lkh.id}" class="text-blue-600 hover:text-blue-800" title="Lihat">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </a>
                                                ${lkh.can_edit ? `
                                                <button onclick="openEditModal(${lkh.id})" class="text-indigo-600 hover:text-indigo-800" title="Edit">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                                ` : ''}
                                            </div>
                                        </div>
                                    </div>
                            `;
                        });
                        
                        html += `
                                </div>
                            </div>
                        `;
                    });
                    
                    container.innerHTML = html;
                    
                    // Pagination (simple version)
                    if (paginationContainer) {
                        if (data.data.links && data.data.links.length > 3) {
                            let paginationHtml = '<div class="flex items-center justify-between bg-white border border-gray-200 rounded-lg px-4 py-3">';
                            paginationHtml += `<div class="text-xs text-gray-700">Menampilkan ${data.data.from || 0} - ${data.data.to || 0} dari ${data.data.total || 0} data</div>`;
                            paginationHtml += '<div class="flex space-x-1">';
                            
                            // Previous
                            if (data.data.prev_page_url) {
                                paginationHtml += `<a href="#" onclick="loadPageFromUrl('${data.data.prev_page_url}'); return false;" class="px-2 py-1 text-xs text-blue-600 hover:bg-blue-50 rounded">Sebelumnya</a>`;
                            }
                            
                            // Next
                            if (data.data.next_page_url) {
                                paginationHtml += `<a href="#" onclick="loadPageFromUrl('${data.data.next_page_url}'); return false;" class="px-2 py-1 text-xs text-blue-600 hover:bg-blue-50 rounded">Selanjutnya</a>`;
                            }
                            
                            paginationHtml += '</div></div>';
                            paginationContainer.innerHTML = paginationHtml;
                        } else {
                            paginationContainer.innerHTML = '';
                        }
                    }
                } else {
                    container.innerHTML = `
                        <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
                            <svg class="h-12 w-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-sm text-gray-500">Tidak ada data LKH</p>
                        </div>
                    `;
                    if (paginationContainer) {
                        paginationContainer.innerHTML = '';
                    }
                }
            })
            .catch(error => {
                console.error('Error loading LKH:', error);
                const container = document.getElementById('lkh-container');
                if (container) {
                    container.innerHTML = `
                        <div class="bg-white border border-red-200 rounded-lg p-12 text-center">
                            <svg class="h-12 w-12 text-red-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm font-medium text-red-600 mb-2">Gagal memuat data</p>
                            <button onclick="loadLKH()" class="text-sm text-blue-600 hover:text-blue-800 underline">Coba lagi</button>
                        </div>
                    `;
                }
            });
    }
    
    // Load page from URL (for pagination)
    function loadPageFromUrl(url) {
        const urlObj = new URL(url);
        const filters = {};
        if (urlObj.searchParams.get('tanggal')) filters.tanggal = urlObj.searchParams.get('tanggal');
        if (urlObj.searchParams.get('status')) filters.status = urlObj.searchParams.get('status');
        if (urlObj.searchParams.get('bulan')) filters.bulan = urlObj.searchParams.get('bulan');
        if (urlObj.searchParams.get('tahun')) filters.tahun = urlObj.searchParams.get('tahun');
        if (urlObj.searchParams.get('user_id')) filters.user_id = urlObj.searchParams.get('user_id');
        if (urlObj.searchParams.get('bulan_tahun')) filters.bulan_tahun = urlObj.searchParams.get('bulan_tahun');
        loadLKH(filters);
    }

    // Reset filter
    function resetFilter() {
        document.getElementById('filter-form').reset();
        // Clear user_id filter jika ada
        const userFilter = document.getElementById('filter-user-id');
        if (userFilter) {
            userFilter.value = '';
        }
        loadLKH();
    }

    // Filter form submit
    document.getElementById('filter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const filters = {};
        
        if (formData.get('tanggal')) filters.tanggal = formData.get('tanggal');
        if (formData.get('status')) filters.status = formData.get('status');
        if (formData.get('bulan_tahun')) {
            const [tahun, bulan] = formData.get('bulan_tahun').split('-');
            filters.bulan = bulan;
            filters.tahun = tahun;
        }
        
        loadLKH(filters);
    });

    // Export dengan filter
    function exportWithFilter(e) {
        e.preventDefault();
        const formData = new FormData(document.getElementById('filter-form'));
        const params = new URLSearchParams();
        
        if (formData.get('tanggal')) params.append('tanggal', formData.get('tanggal'));
        if (formData.get('status')) params.append('status', formData.get('status'));
        if (formData.get('bulan_tahun')) {
            const [tahun, bulan] = formData.get('bulan_tahun').split('-');
            params.append('bulan', bulan);
            params.append('tahun', tahun);
        }
        
        window.location.href = '{{ route("export.excel") }}?' + params.toString();
    }

    // Initial load
    loadLKH();

    // Modal Create LKH Functions
    let keywordMap = {};
    let kategoriKegiatanOptions = '';

    // Open create modal
    function openCreateModal(copyFromId = null) {
        // Reset form container
        document.getElementById('create-form-container').innerHTML = `
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
                                            <input type="time" name="waktu_mulai" id="create-waktu_mulai" step="1" required
                                                   value="${sourceLkh ? sourceLkh.waktu_mulai : ''}"
                                                   placeholder="Mulai"
                                                   class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <input type="time" name="waktu_selesai" id="create-waktu_selesai" step="1" required
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
                                    <input type="url" name="lampiran" id="create-lampiran"
                                           value="${sourceLkh && sourceLkh.lampiran && sourceLkh.lampiran.startsWith('http') ? sourceLkh.lampiran : ''}"
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
                                Simpan LKH
                            </button>
                        </div>
                    </form>
                `;

                document.getElementById('create-form-container').innerHTML = formHtml;

                // Setup auto-suggest
                setupAutoSuggest();
                
                // Setup form submit
                const form = document.getElementById('lkh-create-form');
                if (form) {
                    form.removeEventListener('submit', handleCreateSubmit);
                    form.addEventListener('submit', handleCreateSubmit);
                }
            } else {
                document.getElementById('create-form-container').innerHTML = `
                    <div class="text-center py-8 text-red-600">
                        <p>Gagal memuat form. Silakan refresh halaman.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('create-form-container').innerHTML = `
                <div class="text-center py-8 text-red-600">
                    <p>Terjadi kesalahan saat memuat form.</p>
                </div>
            `;
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
                // Reload LKH list
                loadLKH();
            } else {
                alert('Error: ' + (data.message || 'Gagal menyimpan LKH'));
                submitBtn.disabled = false;
                submitBtn.textContent = 'Simpan LKH';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan LKH');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Simpan LKH';
        });
    }

    // Open edit modal
    function openEditModal(lkhId) {
        // Reset form container
        document.getElementById('edit-form-container').innerHTML = `
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-sm text-gray-600">Memuat form...</p>
            </div>
        `;

        // Open modal
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-lkh-modal' }));

        // Load form data
        fetch(`/lkh/${lkhId}/edit`, {
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
                    `<option value="${k.id}" ${data.data.kategori_kegiatan_id == k.id ? 'selected' : ''}>${k.nama}</option>`
                ).join('');

                const lkh = data.data;
                const today = new Date().toISOString().split('T')[0];
                
                // Build form HTML
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                                 document.querySelector('input[name="_token"]')?.value || 
                                 '{{ csrf_token() }}';
                
                let formHtml = `
                    <form id="lkh-edit-form" method="POST" action="/lkh/${lkhId}" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="PUT">
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <!-- Kolom Kiri -->
                            <div class="space-y-4">
                                <!-- Tanggal -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal *</label>
                                    <input type="date" name="tanggal" id="edit-tanggal" required 
                                           value="${lkh.tanggal}"
                                           class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <!-- Kategori Kegiatan -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Kategori Kegiatan</label>
                                    <select name="kategori_kegiatan_id" id="edit-kategori_kegiatan_id"
                                            class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Pilih Kategori (Opsional)</option>
                                        ${kategoriKegiatanOptions}
                                    </select>
                                </div>

                                <!-- Uraian Kegiatan -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Uraian Kegiatan *</label>
                                    <textarea name="uraian_kegiatan" id="edit-uraian_kegiatan" rows="3" required
                                              class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="Deskripsi kegiatan yang dilakukan...">${lkh.uraian_kegiatan || ''}</textarea>
                                    <p class="mt-1 text-xs text-gray-500">Ketik untuk mendapatkan saran kategori otomatis</p>
                                </div>

                                <!-- Waktu -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Waktu Mulai *</label>
                                        <input type="time" name="waktu_mulai" id="edit-waktu_mulai" step="1" required
                                               value="${lkh.waktu_mulai || ''}"
                                               class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Waktu Selesai *</label>
                                        <input type="time" name="waktu_selesai" id="edit-waktu_selesai" step="1" required
                                               value="${lkh.waktu_selesai || ''}"
                                               class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>

                                <!-- Lampiran -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Link Lampiran (Drive)</label>
                                    <input type="url" name="lampiran" 
                                           value="${lkh.lampiran && lkh.lampiran.startsWith('http') ? lkh.lampiran : ''}"
                                           placeholder="https://drive.google.com/file/d/..."
                                           class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <p class="mt-1 text-xs text-gray-500">Upload file ke Drive, lalu paste link di sini. Kosongkan jika tidak ingin mengubah.</p>
                                </div>
                            </div>

                            <!-- Kolom Kanan -->
                            <div class="space-y-4">
                                <!-- Hasil Output -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Hasil/Output</label>
                                    <textarea name="hasil_output" id="edit-hasil_output" rows="3"
                                              class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="Hasil atau output dari kegiatan...">${lkh.hasil_output || ''}</textarea>
                                </div>

                                <!-- Kendala -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Kendala</label>
                                    <textarea name="kendala" id="edit-kendala" rows="3"
                                              class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="Kendala yang dihadapi (jika ada)...">${lkh.kendala || ''}</textarea>
                                </div>

                                <!-- Tindak Lanjut -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Tindak Lanjut</label>
                                    <textarea name="tindak_lanjut" id="edit-tindak_lanjut" rows="3"
                                              class="w-full text-sm px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="Rencana tindak lanjut (jika ada)...">${lkh.tindak_lanjut || ''}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-3 pt-4 mt-4 border-t">
                            <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal'))" 
                                    class="px-4 py-2 text-sm border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit" id="edit-submit-btn"
                                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                `;

                document.getElementById('edit-form-container').innerHTML = formHtml;

                // Setup auto-suggest
                setupEditAutoSuggest();
                
                // Setup form submit
                const form = document.getElementById('lkh-edit-form');
                if (form) {
                    form.removeEventListener('submit', handleEditSubmit);
                    form.addEventListener('submit', handleEditSubmit);
                }
            } else {
                document.getElementById('edit-form-container').innerHTML = `
                    <div class="text-center py-8 text-red-600">
                        <p>${data.message || 'Gagal memuat form. Silakan refresh halaman.'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('edit-form-container').innerHTML = `
                <div class="text-center py-8 text-red-600">
                    <p>Terjadi kesalahan saat memuat form.</p>
                </div>
            `;
        });
    }

    // Setup auto-suggest untuk edit form
    function setupEditAutoSuggest() {
        const uraianInput = document.getElementById('edit-uraian_kegiatan');
        const kategoriSelect = document.getElementById('edit-kategori_kegiatan_id');
        
        if (!uraianInput || !kategoriSelect) return;

        uraianInput.addEventListener('input', function() {
            const text = this.value.toLowerCase();
            
            // Reset selection hanya jika masih kosong
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

    // Handle edit form submit
    function handleEditSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('edit-submit-btn');
        
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
                // Reload LKH list
                loadLKH();
            } else {
                alert('Error: ' + (data.message || 'Gagal menyimpan perubahan LKH'));
                submitBtn.disabled = false;
                submitBtn.textContent = 'Simpan Perubahan';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan perubahan LKH');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Simpan Perubahan';
        });
    }
</script>
@endpush

<!-- Modal Create LKH -->
<x-modal name="create-lkh-modal" title="Buat LKH Baru" maxWidth="6xl">
    <div id="create-form-container">
        <div class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-sm text-gray-600">Memuat form...</p>
        </div>
    </div>
</x-modal>

@endsection
