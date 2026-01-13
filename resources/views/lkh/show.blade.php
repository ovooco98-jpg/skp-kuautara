@extends('layouts.app')

@section('title', 'Detail LKH')

@section('content')
<div>
    <!-- Page Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail LKH</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $lkh->tanggal->format('d F Y') }}</p>
        </div>
        <div class="flex space-x-3">
            <x-button href="{{ route('lkh.index') }}" variant="secondary" icon="document" size="sm">
                Kembali
            </x-button>
            <x-button href="{{ route('print.lkh', $lkh->id) }}" target="_blank" variant="outline-primary" icon="printer" size="sm">
                Cetak
            </x-button>
            @if($lkh->user_id === Auth::id() || Auth::user()->isKepalaKua())
            <button onclick="openEditModal({{ $lkh->id }})" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center space-x-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span>Edit</span>
            </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informasi Utama -->
            <x-card title="Informasi Kegiatan">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Uraian Kegiatan</label>
                        <p class="text-gray-900">{{ $lkh->uraian_kegiatan }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal</label>
                            <p class="text-gray-900">{{ $lkh->tanggal->format('d F Y') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Waktu</label>
                            <p class="text-gray-900">{{ $lkh->waktu_mulai }} - {{ $lkh->waktu_selesai }}</p>
                            <p class="text-xs text-gray-500 mt-1">Durasi: {{ number_format($lkh->durasi, 1) }} jam</p>
                        </div>
                    </div>

                    @if($lkh->kategoriKegiatan)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Kategori Kegiatan</label>
                        <x-badge variant="info" size="sm">{{ $lkh->kategoriKegiatan->nama }}</x-badge>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        <div class="flex items-center space-x-3">
                            <x-badge 
                                variant="{{ $lkh->status === 'selesai' ? 'success' : 'default' }}" 
                                size="md"
                                id="status-badge"
                            >
                                {{ strtoupper($lkh->status) }}
                            </x-badge>
                            @if($lkh->user_id === Auth::id() && $lkh->status === 'draft')
                            <button onclick="updateStatus({{ $lkh->id }}, 'selesai')" 
                                    class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition">
                                Tandai sebagai Selesai
                            </button>
                            @elseif($lkh->user_id === Auth::id() && $lkh->status === 'selesai')
                            <button onclick="updateStatus({{ $lkh->id }}, 'draft')" 
                                    class="px-3 py-1.5 text-xs font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 transition">
                                Kembalikan ke Draft
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Hasil & Output -->
            @if($lkh->hasil_output)
            <x-card title="Hasil/Output">
                <p class="text-gray-700 whitespace-pre-wrap">{{ $lkh->hasil_output }}</p>
            </x-card>
            @endif

            <!-- Kendala -->
            @if($lkh->kendala)
            <x-card title="Kendala">
                <p class="text-gray-700 whitespace-pre-wrap">{{ $lkh->kendala }}</p>
            </x-card>
            @endif

            <!-- Tindak Lanjut -->
            @if($lkh->tindak_lanjut)
            <x-card title="Tindak Lanjut">
                <p class="text-gray-700 whitespace-pre-wrap">{{ $lkh->tindak_lanjut }}</p>
            </x-card>
            @endif

            <!-- Lampiran -->
            @if($lkh->lampiran)
            <x-card title="Lampiran">
                @php
                    $isUrl = filter_var($lkh->lampiran, FILTER_VALIDATE_URL);
                @endphp
                <div class="flex items-center space-x-3">
                    <x-icon name="document" class="h-8 w-8 text-gray-400" />
                    <div class="flex-1">
                        @if($isUrl)
                            <p class="text-sm font-medium text-gray-900">Link Drive</p>
                            <p class="text-xs text-gray-500 break-all">{{ Str::limit($lkh->lampiran, 60) }}</p>
                        @else
                            <p class="text-sm font-medium text-gray-900">{{ basename($lkh->lampiran) }}</p>
                            <p class="text-xs text-gray-500">File lampiran (lama)</p>
                        @endif
                    </div>
                    @if($isUrl)
                    <x-button 
                        href="{{ $lkh->lampiran }}" 
                        target="_blank"
                        variant="primary" 
                        size="sm"
                    >
                        Buka di Drive
                    </x-button>
                    @else
                    <x-button 
                        href="{{ route('lkh.download', $lkh->id) }}" 
                        variant="outline-primary" 
                        size="sm"
                    >
                        Download
                    </x-button>
                    @endif
                </div>
            </x-card>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Informasi Pegawai -->
            <x-card title="Pegawai">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Nama</label>
                        <p class="text-sm font-medium text-gray-900">{{ $lkh->user->name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">NIP</label>
                        <p class="text-sm text-gray-700">{{ $lkh->user->nip ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Jabatan</label>
                        <p class="text-sm text-gray-700">{{ $lkh->user->jabatan ?? '-' }}</p>
                    </div>
                </div>
            </x-card>


            <!-- Quick Actions -->
            <x-card title="Quick Actions" class="text-sm">
                <div class="space-y-1">
                    @if($lkh->user_id === Auth::id() || Auth::user()->isKepalaKua())
                    <button onclick="openEditModal({{ $lkh->id }})" class="w-full px-2 py-1.5 text-xs bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center justify-center space-x-1.5">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span>Edit LKH</span>
                    </button>
                    @endif
                    <button onclick="openCreateModal({{ $lkh->id }})" class="w-full px-2 py-1.5 text-xs border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 transition flex items-center justify-center space-x-1.5">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                        </svg>
                        <span>Copy LKH Ini</span>
                    </button>
                </div>
            </x-card>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<x-modal name="image-preview" title="Preview Gambar" maxWidth="4xl">
    <div class="text-center">
        <img id="preview-image" src="" alt="Preview" class="max-w-full h-auto rounded-lg mx-auto" 
             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27400%27 height=%27300%27%3E%3Crect fill=%27%23ddd%27 width=%27400%27 height=%27300%27/%3E%3Ctext fill=%27%23999%27 font-family=%27sans-serif%27 font-size=%2718%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dy=%27.3em%27%3EGambar tidak dapat dimuat%3C/text%3E%3C/svg%3E';">
    </div>
</x-modal>

<!-- Modal Edit LKH -->
<x-modal name="edit-lkh-modal" title="Edit LKH" maxWidth="6xl">
    <div id="edit-form-container">
        <div class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-sm text-gray-600">Memuat form...</p>
        </div>
    </div>
</x-modal>

<!-- Modal Create LKH -->
<x-modal name="create-lkh-modal" title="Buat LKH Baru" maxWidth="6xl">
    <div id="create-form-container-show">
        <div class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-sm text-gray-600">Memuat form...</p>
        </div>
    </div>
</x-modal>

@push('scripts')
<script>
    function previewImage(url) {
        const img = document.getElementById('preview-image');
        img.src = '';
        // Add timestamp to prevent caching issues
        img.src = url + (url.includes('?') ? '&' : '?') + 't=' + new Date().getTime();
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'image-preview' }));
    }

    // Include modal functions from index page (shared functionality)
    // Copy the openEditModal, setupEditAutoSuggest, handleEditSubmit, openCreateModal, setupAutoSuggest, handleCreateSubmit functions here
    // Or better: include them from a shared script file
    
    let keywordMap = {};
    let kategoriKegiatanOptions = '';

    // Copy functions from index page - Edit Modal
    function openEditModal(lkhId) {
        document.getElementById('edit-form-container').innerHTML = `
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-sm text-gray-600">Memuat form...</p>
            </div>
        `;
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-lkh-modal' }));
        fetch(`/lkh/${lkhId}/edit`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                keywordMap = data.keyword_map || {};
                kategoriKegiatanOptions = data.kategori_kegiatan.map(k => 
                    `<option value="${k.id}" ${data.data.kategori_kegiatan_id == k.id ? 'selected' : ''}>${k.nama}</option>`
                ).join('');
                const lkh = data.data;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
                let formHtml = `
                    <form id="lkh-edit-form" method="POST" action="/lkh/${lkhId}" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="PUT">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-4 gap-y-3">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Tanggal *</label>
                                    <input type="date" name="tanggal" id="edit-tanggal" required value="${lkh.tanggal}"
                                           class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Waktu *</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <input type="time" name="waktu_mulai" id="edit-waktu_mulai" step="1" required
                                                   value="${lkh.waktu_mulai ? (typeof lkh.waktu_mulai === 'string' ? lkh.waktu_mulai.substring(0, 5) : lkh.waktu_mulai) : ''}"
                                                   placeholder="Mulai"
                                                   class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <input type="time" name="waktu_selesai" id="edit-waktu_selesai" step="1" required
                                                   value="${lkh.waktu_selesai ? (typeof lkh.waktu_selesai === 'string' ? lkh.waktu_selesai.substring(0, 5) : lkh.waktu_selesai) : ''}"
                                                   placeholder="Selesai"
                                                   class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Kategori Kegiatan</label>
                                    <select name="kategori_kegiatan_id" id="edit-kategori_kegiatan_id"
                                            class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        <option value="">Pilih Kategori (Opsional)</option>
                                        ${kategoriKegiatanOptions}
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Uraian Kegiatan *</label>
                                    <textarea name="uraian_kegiatan" id="edit-uraian_kegiatan" rows="3" required
                                              class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                                              placeholder="Deskripsi kegiatan yang dilakukan...">${lkh.uraian_kegiatan || ''}</textarea>
                                    <p class="mt-0.5 text-[10px] text-gray-500">Ketik untuk saran kategori otomatis</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Lampiran (Link Drive)</label>
                                    <input type="url" name="lampiran" id="edit-lampiran" value="${lkh.lampiran || ''}"
                                           placeholder="https://drive.google.com/..."
                                           class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    <p class="mt-0.5 text-[10px] text-gray-500">Link Google Drive atau Dropbox</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Hasil/Output</label>
                                    <textarea name="hasil_output" id="edit-hasil_output" rows="3"
                                              class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                                              placeholder="Hasil atau output dari kegiatan...">${lkh.hasil_output || ''}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Kendala</label>
                                    <textarea name="kendala" id="edit-kendala" rows="3"
                                              class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                                              placeholder="Kendala yang dihadapi (jika ada)...">${lkh.kendala || ''}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Tindak Lanjut</label>
                                    <textarea name="tindak_lanjut" id="edit-tindak_lanjut" rows="3"
                                              class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                                              placeholder="Rencana tindak lanjut (jika ada)...">${lkh.tindak_lanjut || ''}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-2 pt-3 mt-3 border-t">
                            <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal'))" 
                                    class="px-3 py-1.5 text-xs border border-gray-300 rounded text-gray-700 hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit" id="edit-submit-btn"
                                    class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                `;
                document.getElementById('edit-form-container').innerHTML = formHtml;
                setupEditAutoSuggest();
                const form = document.getElementById('lkh-edit-form');
                if (form) {
                    form.removeEventListener('submit', handleEditSubmit);
                    form.addEventListener('submit', handleEditSubmit);
                }
            } else {
                document.getElementById('edit-form-container').innerHTML = `
                    <div class="text-center py-8 text-red-600">
                        <p>${data.message || 'Gagal memuat form.'}</p>
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

    function setupEditAutoSuggest() {
        const uraianInput = document.getElementById('edit-uraian_kegiatan');
        const kategoriSelect = document.getElementById('edit-kategori_kegiatan_id');
        if (!uraianInput || !kategoriSelect) return;
        uraianInput.addEventListener('input', function() {
            const text = this.value.toLowerCase();
            if (kategoriSelect.value === '') {
                for (const [kategoriId, keywords] of Object.entries(keywordMap)) {
                    const match = keywords.some(keyword => text.includes(keyword));
                    if (match) {
                        kategoriSelect.value = kategoriId;
                        kategoriSelect.classList.add('border-green-500');
                        setTimeout(() => kategoriSelect.classList.remove('border-green-500'), 2000);
                        break;
                    }
                }
            }
        });
    }

    function handleEditSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('edit-submit-btn');
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

    // Open Create Modal
    function openCreateModal(copyFromId = null) {
        // Reset form container
        document.getElementById('create-form-container-show').innerHTML = `
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
                
                // Build form HTML (same as index.blade.php)
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
                
                let formHtml = `
                    <form id="lkh-create-form-show" method="POST" action="/lkh" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        ${sourceLkh ? `
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                            <p class="text-xs text-blue-800">
                                <strong>Copy dari LKH:</strong> ${new Date(sourceLkh.tanggal).toLocaleDateString('id-ID')} - ${sourceLkh.uraian_kegiatan}
                            </p>
                        </div>
                        ` : ''}
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-4 gap-y-3">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Tanggal *</label>
                                    <input type="date" name="tanggal" id="create-tanggal-show" required 
                                           value="${sourceLkh ? sourceLkh.tanggal : today}"
                                           class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Waktu *</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="time" name="waktu_mulai" id="create-waktu_mulai-show" step="1" required
                                               value="${sourceLkh ? sourceLkh.waktu_mulai : ''}"
                                               placeholder="Mulai"
                                               class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        <input type="time" name="waktu_selesai" id="create-waktu_selesai-show" step="1" required
                                               value="${sourceLkh ? sourceLkh.waktu_selesai : ''}"
                                               placeholder="Selesai"
                                               class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Kategori Kegiatan</label>
                                    <select name="kategori_kegiatan_id" id="create-kategori_kegiatan_id-show"
                                            class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        <option value="">Pilih Kategori (Opsional)</option>
                                        ${kategoriKegiatanOptions}
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Uraian Kegiatan *</label>
                                    <textarea name="uraian_kegiatan" id="create-uraian_kegiatan-show" rows="3" required
                                              class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                                              placeholder="Deskripsi kegiatan yang dilakukan...">${sourceLkh ? sourceLkh.uraian_kegiatan : ''}</textarea>
                                    <p class="mt-0.5 text-[10px] text-gray-500">Ketik untuk saran kategori otomatis</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Lampiran (Link Drive)</label>
                                    <input type="url" name="lampiran" id="create-lampiran-show"
                                           placeholder="https://drive.google.com/..."
                                           value="${sourceLkh ? (sourceLkh.lampiran || '') : ''}"
                                           class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    <p class="mt-0.5 text-[10px] text-gray-500">Link Google Drive atau Dropbox (opsional)</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Hasil/Output</label>
                                    <textarea name="hasil_output" id="create-hasil_output-show" rows="3"
                                              class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                                              placeholder="Hasil atau output dari kegiatan...">${sourceLkh ? (sourceLkh.hasil_output || '') : ''}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Kendala</label>
                                    <textarea name="kendala" id="create-kendala-show" rows="3"
                                              class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                                              placeholder="Kendala yang dihadapi (jika ada)...">${sourceLkh ? (sourceLkh.kendala || '') : ''}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Tindak Lanjut</label>
                                    <textarea name="tindak_lanjut" id="create-tindak_lanjut-show" rows="3"
                                              class="w-full text-xs px-2.5 py-1.5 rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                                              placeholder="Rencana tindak lanjut (jika ada)...">${sourceLkh ? (sourceLkh.tindak_lanjut || '') : ''}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-2 pt-3 mt-3 border-t">
                            <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal'))" 
                                    class="px-3 py-1.5 text-xs border border-gray-300 rounded text-gray-700 hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit" id="create-submit-btn-show"
                                    class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                Simpan LKH
                            </button>
                        </div>
                    </form>
                `;

                document.getElementById('create-form-container-show').innerHTML = formHtml;

                // Setup auto-suggest
                const uraianInput = document.getElementById('create-uraian_kegiatan-show');
                const kategoriSelect = document.getElementById('create-kategori_kegiatan_id-show');
                if (uraianInput && kategoriSelect) {
                    uraianInput.addEventListener('input', function() {
                        const text = this.value.toLowerCase();
                        if (kategoriSelect.value === '') {
                            for (const [kategoriId, keywords] of Object.entries(keywordMap)) {
                                const match = keywords.some(keyword => text.includes(keyword));
                                if (match) {
                                    kategoriSelect.value = kategoriId;
                                    kategoriSelect.classList.add('border-green-500');
                                    setTimeout(() => kategoriSelect.classList.remove('border-green-500'), 2000);
                                    break;
                                }
                            }
                        }
                    });
                }
                
                // Setup form submit
                const form = document.getElementById('lkh-create-form-show');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formData = new FormData(form);
                        const submitBtn = document.getElementById('create-submit-btn-show');
                        
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
                    });
                }
            } else {
                document.getElementById('create-form-container-show').innerHTML = `
                    <div class="text-center py-8 text-red-600">
                        <p>Gagal memuat form. Silakan refresh halaman.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('create-form-container-show').innerHTML = `
                <div class="text-center py-8 text-red-600">
                    <p>Terjadi kesalahan saat memuat form.</p>
                </div>
            `;
        });
    }
    
    // Update status LKH
    function updateStatus(lkhId, newStatus) {
        if (!confirm(`Apakah Anda yakin ingin mengubah status menjadi ${newStatus === 'selesai' ? 'Selesai' : 'Draft'}?`)) {
            return;
        }

        fetch(`/lkh/${lkhId}/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal mengubah status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengubah status');
        });
    }
</script>
@endpush
@endsection

