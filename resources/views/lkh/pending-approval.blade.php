@extends('layouts.app')

@section('title', 'Pending Approval')

@section('content')
<div>
    <!-- Page Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pending Approval</h1>
            <p class="mt-1 text-sm text-gray-500">LKH yang menunggu persetujuan</p>
        </div>
        <div class="flex space-x-3">
            <x-button variant="outline-primary" icon="filter" onclick="document.getElementById('filter-form').classList.toggle('hidden')" size="sm">
                Filter
            </x-button>
        </div>
    </div>

    <!-- Filter Form -->
    <div id="filter-form" class="hidden mb-6">
        <x-card>
            <form id="filter" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bulan/Tahun</label>
                    <input type="month" name="bulan_tahun" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pegawai</label>
                    <select name="user_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Pegawai</option>
                        <!-- Options akan diisi via JavaScript -->
                    </select>
                </div>
                <div class="flex items-end">
                    <x-button type="submit" variant="primary" class="w-full" size="sm">Terapkan Filter</x-button>
                </div>
            </form>
        </x-card>
    </div>

    <!-- Pending LKH List -->
    <div id="pending-lkh-container">
        <div class="space-y-4">
            <!-- Loading state -->
            <div id="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-500">Memuat data...</p>
            </div>
            
            <!-- Empty state -->
            <div id="empty-state" class="hidden text-center py-12">
                <x-icon name="document" class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-1">Tidak ada LKH pending</h3>
                <p class="text-gray-500">Semua LKH sudah diproses</p>
            </div>

            <!-- LKH Items -->
            <div id="lkh-items" class="hidden space-y-4"></div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<x-modal name="approval-modal" title="Konfirmasi Approval" maxWidth="md">
    <div>
        <p class="text-gray-700 mb-4">Apakah Anda yakin ingin <span id="approval-action" class="font-semibold">approve</span> LKH ini?</p>
        <form id="approval-form" method="POST">
            @csrf
            <input type="hidden" name="action" id="approval-action-input">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                <textarea name="catatan_approval" id="catatan-approval" rows="3" 
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Tambahkan catatan jika diperlukan..."></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <x-button type="button" variant="secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal'))" size="sm">Batal</x-button>
                <x-button type="submit" id="submit-approval-btn" variant="primary" size="sm">Konfirmasi</x-button>
            </div>
        </form>
    </div>
</x-modal>

@push('scripts')
<script>
    let currentLkhId = null;
    let currentAction = null;

    // Load pending LKH
    function loadPendingLKH(filters = {}) {
        document.getElementById('loading').classList.remove('hidden');
        document.getElementById('empty-state').classList.add('hidden');
        document.getElementById('lkh-items').classList.add('hidden');

        const params = new URLSearchParams(filters).toString();
        fetch(`{{ route('lkh.pending.approval') }}?${params}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading').classList.add('hidden');
                
                if (data.success && data.data.data && data.data.data.length > 0) {
                    renderLKHItems(data.data.data);
                    document.getElementById('lkh-items').classList.remove('hidden');
                } else {
                    document.getElementById('empty-state').classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('loading').classList.add('hidden');
                document.getElementById('empty-state').classList.remove('hidden');
            });
    }

    // Render LKH items
    function renderLKHItems(items) {
        const container = document.getElementById('lkh-items');
        container.innerHTML = items.map(lkh => `
            <x-card>
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-2">
                            <div>
                                <h3 class="font-semibold text-gray-900">${lkh.user ? lkh.user.name : 'N/A'}</h3>
                                <p class="text-sm text-gray-500">${lkh.user ? (lkh.user.jabatan || lkh.user.role) : ''}</p>
                            </div>
                        </div>
                        <p class="text-gray-700 mb-3">${lkh.uraian_kegiatan}</p>
                        <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                            <span class="flex items-center">
                                <x-icon name="calendar" class="h-4 w-4 mr-1" />
                                ${new Date(lkh.tanggal).toLocaleDateString('id-ID')}
                            </span>
                            <span class="flex items-center">
                                <x-icon name="clock" class="h-4 w-4 mr-1" />
                                ${lkh.waktu_mulai} - ${lkh.waktu_selesai}
                            </span>
                            ${lkh.kategori_kegiatan ? `
                                <span class="flex items-center">
                                    <x-icon name="document" class="h-4 w-4 mr-1" />
                                    ${lkh.kategori_kegiatan.nama}
                                </span>
                            ` : ''}
                        </div>
                        ${lkh.hasil_output ? `
                            <div class="mt-3 p-3 bg-gray-50 rounded-md">
                                <p class="text-xs font-medium text-gray-700 mb-1">Hasil:</p>
                                <p class="text-sm text-gray-600">${lkh.hasil_output}</p>
                            </div>
                        ` : ''}
                    </div>
                    <div class="ml-4 flex flex-col space-y-2">
                        <x-button 
                            variant="success" 
                            size="sm"
                            onclick="openApprovalModal(${lkh.id}, 'approve')"
                        >
                            Approve
                        </x-button>
                        <x-button 
                            variant="danger" 
                            size="sm"
                            onclick="openApprovalModal(${lkh.id}, 'reject')"
                        >
                            Reject
                        </x-button>
                        <x-button 
                            variant="outline-primary" 
                            size="sm"
                            href="/lkh/${lkh.id}"
                        >
                            Detail
                        </x-button>
                    </div>
                </div>
            </x-card>
        `).join('');
    }

    // Open approval modal
    function openApprovalModal(lkhId, action) {
        currentLkhId = lkhId;
        currentAction = action;
        
        const actionText = action === 'approve' ? 'menyetujui' : 'menolak';
        document.getElementById('approval-action').textContent = actionText;
        document.getElementById('approval-action-input').value = action;
        document.getElementById('approval-form').action = `/lkh/${lkhId}/${action}`;
        document.getElementById('catatan-approval').value = '';
        document.getElementById('catatan-approval').required = action === 'reject';
        
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'approval-modal' }));
    }

    // Handle approval form submit
    document.getElementById('approval-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const url = this.action;
        
        document.getElementById('submit-approval-btn').disabled = true;
        document.getElementById('submit-approval-btn').textContent = 'Memproses...';
        
        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.dispatchEvent(new CustomEvent('close-modal'));
                loadPendingLKH();
                // Show success message
                alert('LKH berhasil diproses');
            } else {
                alert('Error: ' + (data.message || 'Gagal memproses LKH'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memproses LKH');
        })
        .finally(() => {
            document.getElementById('submit-approval-btn').disabled = false;
            document.getElementById('submit-approval-btn').textContent = 'Konfirmasi';
        });
    });

    // Filter form submit
    document.getElementById('filter').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const filters = {};
        
        if (formData.get('tanggal')) filters.tanggal = formData.get('tanggal');
        if (formData.get('bulan_tahun')) {
            const [tahun, bulan] = formData.get('bulan_tahun').split('-');
            filters.bulan = bulan;
            filters.tahun = tahun;
        }
        if (formData.get('user_id')) filters.user_id = formData.get('user_id');
        
        loadPendingLKH(filters);
    });

    // Initial load
    loadPendingLKH();
</script>
@endpush
@endsection

