@extends('layouts.app')

@section('title', 'Detail Laporan Bulanan')

@section('content')
<div>
    <!-- Page Header -->
    <div class="mb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Detail Laporan Bulanan</h1>
            <p class="mt-0.5 text-xs text-gray-500">{{ $laporan->nama_bulan }}</p>
        </div>
        <div class="flex space-x-2">
            <x-button href="{{ route('laporan-bulanan.index') }}" variant="secondary" icon="document" size="sm">
                Kembali
            </x-button>
            @if($laporan->user_id === Auth::id() || Auth::user()->isKepalaKua())
            <x-button href="{{ route('laporan-bulanan.edit', $laporan->id) }}" variant="primary" icon="pencil" size="sm">
                Edit
            </x-button>
            @endif
            <x-button href="{{ route('print.laporan-bulanan', $laporan->id) }}" target="_blank" variant="outline-primary" icon="printer" size="sm">
                Cetak
            </x-button>
            <x-button href="{{ route('export.laporan-bulanan') }}?bulan={{ $laporan->bulan }}&tahun={{ $laporan->tahun }}" variant="outline-primary" icon="document" size="sm">
                Export
            </x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Ringkasan Kegiatan -->
            <x-card title="Ringkasan Kegiatan">
                <div class="prose prose-sm max-w-none">
                    <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $laporan->ringkasan_kegiatan ?? '-' }}</p>
                </div>
            </x-card>

            <!-- Pencapaian -->
            @if($laporan->pencapaian)
            <x-card title="Pencapaian">
                <div class="prose max-w-none">
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $laporan->pencapaian }}</p>
                </div>
            </x-card>
            @endif

            <!-- Kendala -->
            @if($laporan->kendala)
            <x-card title="Kendala">
                <div class="prose max-w-none">
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $laporan->kendala }}</p>
                </div>
            </x-card>
            @endif

            <!-- Rencana Bulan Depan -->
            @if($laporan->rencana_bulan_depan)
            <x-card title="Rencana Bulan Depan">
                <div class="prose max-w-none">
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $laporan->rencana_bulan_depan }}</p>
                </div>
            </x-card>
            @endif

            <!-- Daftar LKH -->
            <x-card title="Daftar LKH ({{ $laporan->lkh->count() }} kegiatan)">
                <div class="space-y-2">
                    @foreach($laporan->lkh as $lkh)
                    <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50">
                        <div class="flex justify-between items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-xs font-medium text-gray-900">{{ $lkh->tanggal->format('d F Y') }}</span>
                                    @if($lkh->kategoriKegiatan)
                                    <x-badge variant="info" size="sm">{{ $lkh->kategoriKegiatan->nama }}</x-badge>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-700 mb-1.5 line-clamp-2">{{ $lkh->uraian_kegiatan }}</p>
                                <div class="text-xs text-gray-500">
                                    <span>{{ substr($lkh->waktu_mulai, 0, 5) }} - {{ substr($lkh->waktu_selesai, 0, 5) }}</span>
                                    <span class="mx-1.5">•</span>
                                    <span>{{ number_format($lkh->durasi, 1) }} jam</span>
                                </div>
                            </div>
                            <x-button href="{{ route('lkh.show', $lkh->id) }}" variant="outline-primary" size="sm" class="flex-shrink-0">
                                Detail
                            </x-button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
            <!-- Informasi Pegawai -->
            <x-card title="Informasi">
                <div class="space-y-2.5">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Pegawai</label>
                        <p class="text-sm font-medium text-gray-900">{{ $laporan->user->name }}</p>
                        <p class="text-xs text-gray-600">{{ $laporan->user->nip ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Periode</label>
                        <p class="text-sm text-gray-900">{{ $laporan->nama_bulan }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                        <x-badge variant="{{ $laporan->status === 'selesai' ? 'success' : 'default' }}" size="sm">
                            {{ strtoupper($laporan->status) }}
                        </x-badge>
                    </div>
                </div>
            </x-card>

            <!-- Statistik -->
            <x-card title="Statistik">
                <div class="space-y-2.5">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Total LKH</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $laporan->total_lkh }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Total Durasi</span>
                        <span class="text-sm font-semibold text-gray-900">{{ number_format($laporan->total_durasi, 1) }} jam</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Dibuat</span>
                        <span class="text-sm text-gray-900">{{ $laporan->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($laporan->updated_at != $laporan->created_at)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Diupdate</span>
                        <span class="text-sm text-gray-900">{{ $laporan->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </x-card>

            <!-- Quick Actions -->
            <x-card title="Quick Actions" class="text-sm">
                <div class="space-y-1">
                    <a href="{{ route('print.laporan-bulanan', $laporan->id) }}" target="_blank" class="flex items-center w-full px-2 py-1.5 text-xs bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                        <x-icon name="printer" class="mr-1.5 h-3.5 w-3.5" />
                        Cetak Laporan
                    </a>
                    <a href="{{ route('export.laporan-bulanan') }}?bulan={{ $laporan->bulan }}&tahun={{ $laporan->tahun }}" class="flex items-center w-full px-2 py-1.5 text-xs border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 transition">
                        <x-icon name="document" class="mr-1.5 h-3.5 w-3.5" />
                        Export Laporan
                    </a>
                    @if($laporan->user_id === Auth::id() || Auth::user()->isKepalaKua())
                    <a href="{{ route('laporan-bulanan.edit', $laporan->id) }}" class="flex items-center w-full px-2 py-1.5 text-xs border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 transition">
                        <x-icon name="pencil" class="mr-1.5 h-3.5 w-3.5" />
                        Edit Laporan
                    </a>
                    @endif
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

