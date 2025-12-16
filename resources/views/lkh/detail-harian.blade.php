@extends('layouts.app')

@section('title', 'Detail Harian LKH')

@section('content')
<div class="space-y-4">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Detail Harian LKH</h1>
            <p class="mt-0.5 text-xs text-gray-500">
                {{ \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('l, d F Y') }}
                @if($user)
                    - {{ $user->name }}
                @endif
            </p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('lkh.index') }}" class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 transition">
                ← Kembali
            </a>
        </div>
    </div>

    <!-- Ringkasan Harian -->
    <x-card>
        <div class="flex justify-between items-start mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Ringkasan Harian</h2>
            @if(Auth::id() == ($user ? $user->id : Auth::id()) || Auth::user()->isKepalaKua())
            <button onclick="openEditRingkasanModal()" class="px-2 py-1 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 transition">
                ✏️ Edit Ringkasan
            </button>
            @endif
        </div>
        @if($ringkasanHarian && $ringkasanHarian->ringkasan)
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $ringkasanHarian->ringkasan }}</p>
            </div>
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-sm text-yellow-700">Ringkasan harian belum tersedia. Ringkasan akan otomatis di-generate setelah ada kegiatan.</p>
            </div>
        @endif
    </x-card>

    <!-- Statistik Harian -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-card padding="true">
            <div class="text-center">
                <p class="text-2xl font-bold text-blue-600">{{ $lkhList->count() }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Kegiatan</p>
            </div>
        </x-card>
        <x-card padding="true">
            <div class="text-center">
                <p class="text-2xl font-bold text-green-600">{{ number_format($lkhList->sum('durasi'), 1) }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Durasi (jam)</p>
            </div>
        </x-card>
        <x-card padding="true">
            <div class="text-center">
                <p class="text-2xl font-bold text-purple-600">{{ $lkhList->groupBy('kategori_kegiatan_id')->count() }}</p>
                <p class="text-xs text-gray-500 mt-1">Kategori Kegiatan</p>
            </div>
        </x-card>
    </div>

    <!-- Daftar Kegiatan -->
    <x-card padding="false">
        <div class="px-4 py-3 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Kegiatan</h2>
        </div>
        <div class="overflow-x-auto">
            @if($lkhList->isEmpty())
                <div class="px-4 py-8 text-center text-gray-500">
                    <p class="text-sm">Tidak ada kegiatan pada tanggal ini.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Uraian Kegiatan</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Durasi</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($lkhList as $lkh)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-xs text-gray-900">
                                <div>{{ $lkh->waktu_mulai }} - {{ $lkh->waktu_selesai }}</div>
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-900">
                                <div class="max-w-md">{{ Str::limit($lkh->uraian_kegiatan, 80) }}</div>
                            </td>
                            <td class="px-4 py-2 text-xs">
                                @if($lkh->kategoriKegiatan)
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">{{ $lkh->kategoriKegiatan->nama }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-600">
                                {{ number_format($lkh->durasi, 1) }} jam
                            </td>
                            <td class="px-4 py-2 text-xs">
                                <span class="px-2 py-1 rounded text-xs {{ $lkh->status === 'selesai' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ strtoupper($lkh->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-xs text-right">
                                <div class="flex justify-end space-x-1">
                                    <a href="{{ route('lkh.show', $lkh->id) }}" class="px-2 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition">
                                        👁️
                                    </a>
                                    @if($lkh->user_id === Auth::id() || Auth::user()->isKepalaKua())
                                    <button onclick="openEditModal({{ $lkh->id }})" class="px-2 py-1 text-yellow-600 hover:text-yellow-800 hover:bg-yellow-50 rounded transition">
                                        ✏️
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </x-card>
</div>

<!-- Modal Edit Ringkasan -->
<div id="edit-ringkasan-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit Ringkasan Harian</h3>
                <button onclick="closeEditRingkasanModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="edit-ringkasan-form" onsubmit="saveRingkasan(event)">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                @if($user)
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                @endif
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ringkasan Harian</label>
                    <textarea name="ringkasan" id="ringkasan-textarea" rows="8" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>{{ $ringkasanHarian ? $ringkasanHarian->ringkasan : '' }}</textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeEditRingkasanModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openEditRingkasanModal() {
    document.getElementById('edit-ringkasan-modal').classList.remove('hidden');
}

function closeEditRingkasanModal() {
    document.getElementById('edit-ringkasan-modal').classList.add('hidden');
}

function saveRingkasan(event) {
    event.preventDefault();
    const form = document.getElementById('edit-ringkasan-form');
    const formData = new FormData(form);
    
    fetch('{{ route('lkh.update-ringkasan-harian') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Ringkasan harian berhasil diperbarui');
            location.reload();
        } else {
            alert('Gagal memperbarui ringkasan: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan ringkasan');
    });
}

// Include edit modal function from index if exists
@if(file_exists(resource_path('views/lkh/index.blade.php')))
// Function openEditModal akan diambil dari index.blade.php jika ada
@endif
</script>
@endpush
@endsection
