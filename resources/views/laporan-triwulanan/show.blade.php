@extends('layouts.app')

@section('title', 'Detail Laporan Triwulanan')

@section('content')
<div>
    <!-- Page Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Laporan Triwulanan</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $laporan->nama_triwulan }}</p>
        </div>
        <div class="flex space-x-2">
            <x-button href="{{ route('laporan-triwulanan.index', ['tahun' => $laporan->tahun]) }}" variant="secondary" icon="arrow-left" size="sm">
                Kembali
            </x-button>
            <x-button href="{{ route('print.laporan-triwulanan', $laporan->id) }}" target="_blank" variant="outline-primary" icon="printer" size="sm">
                Cetak
            </x-button>
            @if($laporan->file_bukti_fisik)
            <x-button href="{{ route('laporan-triwulanan.download-bukti-fisik', $laporan->id) }}" target="_blank" variant="primary" icon="download" size="sm">
                Download Bukti Fisik
            </x-button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informasi Umum -->
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Umum</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pegawai</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $laporan->user->name }} ({{ $laporan->user->jabatan }})</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Periode</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $laporan->nama_triwulan }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Laporan Bulanan</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $laporan->laporanBulanan->count() }} laporan</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total LKH</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $laporan->total_lkh }} kegiatan</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Durasi</label>
                        <p class="mt-1 text-sm text-gray-900">{{ number_format($laporan->total_durasi, 1) }} jam</p>
                    </div>
                </div>
            </x-card>

            <!-- Ringkasan Kegiatan -->
            @if($laporan->ringkasan_kegiatan)
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Kegiatan</h2>
                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $laporan->ringkasan_kegiatan }}</p>
            </x-card>
            @endif

            <!-- Pencapaian -->
            @if($laporan->pencapaian)
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Pencapaian</h2>
                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $laporan->pencapaian }}</p>
            </x-card>
            @endif

            <!-- Kendala -->
            @if($laporan->kendala)
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Kendala</h2>
                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $laporan->kendala }}</p>
            </x-card>
            @endif

            <!-- Rencana Triwulan Depan -->
            @if($laporan->rencana_triwulan_depan)
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Rencana Triwulan Depan</h2>
                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $laporan->rencana_triwulan_depan }}</p>
            </x-card>
            @endif

            <!-- Laporan Bulanan Terkait -->
            @if($laporan->laporanBulanan->isNotEmpty())
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Laporan Bulanan Terkait</h2>
                <div class="space-y-3">
                    @foreach($laporan->laporanBulanan as $laporanBulanan)
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-md">
                        <div>
                            <p class="font-medium text-gray-900">{{ $laporanBulanan->nama_bulan }}</p>
                            <p class="text-sm text-gray-600">{{ $laporanBulanan->total_lkh }} LKH • {{ number_format($laporanBulanan->total_durasi, 1) }} jam</p>
                        </div>
                        <x-button href="{{ route('laporan-bulanan.show', $laporanBulanan->id) }}" variant="secondary" size="sm">
                            Lihat
                        </x-button>
                    </div>
                    @endforeach
                </div>
            </x-card>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Card -->
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Status</h2>
                <div class="space-y-3">
                    <div>
                        <x-badge variant="{{ $laporan->status === 'ditandatangani' ? 'success' : ($laporan->status === 'selesai' ? 'primary' : 'default') }}" size="lg" class="w-full justify-center">
                            {{ strtoupper($laporan->status) }}
                        </x-badge>
                    </div>
                    @if($laporan->ditandatangani_pada)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ditandatangani Pada</label>
                        <p class="text-xs text-gray-500">{{ $laporan->ditandatangani_pada->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </x-card>

            <!-- Upload Bukti Fisik -->
            @if(!$laporan->file_bukti_fisik)
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Simpan Link Bukti Fisik</h2>
                <form method="POST" action="{{ route('laporan-triwulanan.upload-bukti-fisik', $laporan->id) }}">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Link Drive Bukti Fisik <span class="text-red-500">*</span>
                    </label>
                    <input type="url" 
                           name="file_bukti_fisik" 
                           placeholder="https://drive.google.com/file/d/..."
                           required
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm mb-2">
                    <p class="text-xs text-gray-500 mb-3">
                        Upload file PDF ke Google Drive, lalu paste link di sini
                    </p>
                    <x-button type="submit" variant="primary" class="w-full" size="sm">
                        Simpan Link
                    </x-button>
                </form>
            </x-card>
            @else
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Bukti Fisik</h2>
                <div class="p-3 bg-green-50 border border-green-200 rounded-md">
                    <p class="text-sm font-medium text-green-800 mb-2">✓ Bukti fisik sudah tersedia</p>
                    @php
                        $isUrl = filter_var($laporan->file_bukti_fisik, FILTER_VALIDATE_URL);
                    @endphp
                    @if($isUrl)
                    <x-button href="{{ $laporan->file_bukti_fisik }}" target="_blank" variant="primary" size="sm" class="w-full">
                        Buka di Drive
                    </x-button>
                    @else
                    <x-button href="{{ route('laporan-triwulanan.download-bukti-fisik', $laporan->id) }}" variant="primary" size="sm" class="w-full">
                        Download
                    </x-button>
                    @endif
                </div>
            </x-card>
            @endif
        </div>
    </div>
</div>
@endsection
