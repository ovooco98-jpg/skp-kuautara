@extends('layouts.app')

@section('title', 'Laporan Tahunan')

@section('content')
<div>
    <!-- Page Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Tahunan</h1>
            <p class="mt-1 text-sm text-gray-500">Konsolidasi laporan triwulanan menjadi laporan tahunan</p>
        </div>
        <x-button href="{{ route('laporan-tahunan.create', ['tahun' => $tahun ?? date('Y')]) }}" variant="primary" icon="plus" size="sm">
            Buat Laporan Tahunan
        </x-button>
    </div>

    <!-- Filters -->
    <x-card class="mb-6">
        <form method="GET" action="{{ route('laporan-tahunan.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <select name="tahun" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ ($tahun ?? request('tahun', date('Y'))) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
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

    <!-- Laporan Tahunan Table -->
    <x-card>
        @if($laporan->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pegawai</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Laporan Triwulanan</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total LKH</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Durasi</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($laporan as $lap)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $lap->tahun }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-900">{{ $lap->user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $lap->user->jabatan ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-sm text-gray-900">{{ $lap->laporanTriwulanan->count() }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-sm text-gray-900">{{ $lap->total_lkh }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-sm text-gray-900">{{ number_format($lap->total_durasi, 1) }} jam</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex flex-col items-center space-y-1">
                                <x-badge variant="{{ $lap->status === 'ditandatangani' ? 'success' : ($lap->status === 'selesai' ? 'primary' : 'default') }}" size="sm">
                                    {{ strtoupper($lap->status) }}
                                </x-badge>
                                @if($lap->file_bukti_fisik)
                                <x-badge variant="success" size="sm">
                                    ✓ Bukti Fisik
                                </x-badge>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <x-button href="{{ route('laporan-tahunan.show', $lap->id) }}" variant="primary" size="sm" icon="eye">
                                Lihat
                            </x-button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada laporan tahunan</h3>
            <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat laporan tahunan baru untuk tahun {{ $tahun ?? date('Y') }}.</p>
            <div class="mt-6">
                <x-button href="{{ route('laporan-tahunan.create', ['tahun' => $tahun ?? date('Y')]) }}" variant="primary" size="sm">
                    Buat Laporan Tahunan
                </x-button>
            </div>
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
@endsection
