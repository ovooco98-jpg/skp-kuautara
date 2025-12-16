@extends('layouts.app')

@section('title', 'Sasaran Kerja Pegawai (SKP)')

@section('content')
<div>
    <!-- Page Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Sasaran Kerja Pegawai (SKP)</h1>
            <p class="mt-1 text-sm text-gray-500">Rencana kerja tahunan yang dievaluasi dari laporan bulanan</p>
        </div>
        <div class="flex space-x-2">
            @if(Auth::user()->isKepalaKua())
            <form method="POST" action="{{ route('skp.generate-dari-staff') }}" class="inline">
                @csrf
                <input type="hidden" name="tahun" value="{{ $tahun ?? date('Y') }}">
                <x-button type="submit" variant="secondary" size="sm">
                    Generate dari Staff
                </x-button>
            </form>
            @endif
            <x-button href="{{ route('skp.create', ['tahun' => $tahun ?? date('Y')]) }}" variant="primary" icon="plus" size="sm">
                Buat SKP
            </x-button>
        </div>
    </div>

    <!-- Filters -->
    <x-card class="mb-6">
        <form method="GET" action="{{ route('skp.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <select name="tahun" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ ($tahun ?? request('tahun', date('Y'))) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="dinilai" {{ request('status') == 'dinilai' ? 'selected' : '' }}>Dinilai</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            @if(Auth::user()->isKepalaKua())
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pegawai</label>
                <select name="user_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Pegawai</option>
                    @foreach($users ?? [] as $user)
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

    <!-- SKP List -->
    <div class="space-y-4">
        @forelse($skp as $item)
        <x-card>
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $item->kegiatan_tugas_jabatan }}
                        </h3>
                        <x-badge variant="{{ $item->status === 'selesai' ? 'success' : ($item->status === 'dinilai' ? 'primary' : 'default') }}" size="sm">
                            {{ $item->status_label }}
                        </x-badge>
                        @if($item->skp_atasan)
                        <x-badge variant="secondary" size="sm">
                            Mengacu SKP Atasan
                        </x-badge>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600 mb-3">
                        <div>
                            <span class="font-medium">Pegawai:</span> {{ $item->user->name }}
                        </div>
                        <div>
                            <span class="font-medium">Tahun:</span> {{ $item->tahun }}
                        </div>
                        <div>
                            <span class="font-medium">Laporan Bulanan:</span> {{ $item->laporanBulanan->count() }} laporan
                        </div>
                        @if($item->nilai_capaian)
                        <div>
                            <span class="font-medium">Nilai Capaian:</span> {{ number_format($item->nilai_capaian, 2) }}
                        </div>
                        @endif
                    </div>
                    @if($item->rincian_tugas)
                    <p class="text-sm text-gray-600 line-clamp-2">
                        {{ Str::limit($item->rincian_tugas, 150) }}
                    </p>
                    @endif
                    @if($item->target_kuantitas)
                    <div class="mt-2 text-sm">
                        <span class="font-medium text-gray-700">Target:</span>
                        <span class="text-gray-600">{{ $item->target_kuantitas }}</span>
                    </div>
                    @endif
                </div>
                <div class="ml-4 flex flex-col space-y-2">
                    <x-button href="{{ route('skp.show', $item->id) }}" variant="primary" size="sm" icon="eye">
                        Lihat
                    </x-button>
                    @if($item->canEdit())
                    <x-button href="{{ route('skp.edit', $item->id) }}" variant="secondary" size="sm" icon="pencil">
                        Edit
                    </x-button>
                    @endif
                </div>
            </div>
        </x-card>
        @empty
        <x-card>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada SKP</h3>
                <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat SKP baru untuk tahun {{ $tahun ?? date('Y') }}.</p>
                <div class="mt-6">
                    <x-button href="{{ route('skp.create', ['tahun' => $tahun ?? date('Y')]) }}" variant="primary" size="sm">
                        Buat SKP
                    </x-button>
                </div>
            </div>
        </x-card>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($skp->hasPages())
    <div class="mt-6">
        {{ $skp->links() }}
    </div>
    @endif
</div>
@endsection
