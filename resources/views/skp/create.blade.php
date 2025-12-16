@extends('layouts.app')

@section('title', 'Buat SKP')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Buat SKP Baru</h1>
        <p class="mt-1 text-sm text-gray-500">Tahun {{ $tahun ?? date('Y') }}</p>
    </div>

    <form method="POST" action="{{ route('skp.store') }}" class="space-y-6">
        @csrf
        
        <input type="hidden" name="tahun" value="{{ $tahun ?? date('Y') }}">

        <!-- Informasi SKP Atasan (untuk cascading) -->
        @if(isset($skpAtasan) && $skpAtasan)
        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Mengacu pada SKP Atasan</h2>
            <div class="p-4 bg-blue-50 rounded-md">
                <p class="font-medium text-gray-900">{{ $skpAtasan->kegiatan_tugas_jabatan }}</p>
                @if($skpAtasan->target_kuantitas)
                <p class="text-sm text-gray-600 mt-1">Target: {{ $skpAtasan->target_kuantitas }}</p>
                @endif
            </div>
            <input type="hidden" name="skp_atasan_id" value="{{ $skpAtasan->id }}">
        </x-card>
        @endif

        <!-- Kegiatan Tugas Jabatan -->
        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Kegiatan Tugas Jabatan</h2>
            <div class="space-y-4">
                <div>
                    <label for="kegiatan_tugas_jabatan" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Kegiatan Tugas Jabatan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="kegiatan_tugas_jabatan" 
                           name="kegiatan_tugas_jabatan" 
                           value="{{ old('kegiatan_tugas_jabatan') }}"
                           required
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('kegiatan_tugas_jabatan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="rincian_tugas" class="block text-sm font-medium text-gray-700 mb-1">
                        Rincian Tugas, Tanggung Jawab, dan Wewenang
                    </label>
                    <textarea id="rincian_tugas" 
                              name="rincian_tugas" 
                              rows="4"
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('rincian_tugas') }}</textarea>
                    @error('rincian_tugas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-card>

        <!-- Target Kinerja -->
        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Target Kinerja</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="target_kuantitas" class="block text-sm font-medium text-gray-700 mb-1">
                        Target Kuantitas
                    </label>
                    <input type="text" 
                           id="target_kuantitas" 
                           name="target_kuantitas" 
                           value="{{ old('target_kuantitas') }}"
                           placeholder="Contoh: 100 kegiatan"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('target_kuantitas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="target_kualitas" class="block text-sm font-medium text-gray-700 mb-1">
                        Target Kualitas
                    </label>
                    <input type="text" 
                           id="target_kualitas" 
                           name="target_kualitas" 
                           value="{{ old('target_kualitas') }}"
                           placeholder="Contoh: 100% akurat"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('target_kualitas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="target_waktu" class="block text-sm font-medium text-gray-700 mb-1">
                        Target Waktu
                    </label>
                    <input type="text" 
                           id="target_waktu" 
                           name="target_waktu" 
                           value="{{ old('target_waktu') }}"
                           placeholder="Contoh: 12 bulan"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('target_waktu')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="target_biaya" class="block text-sm font-medium text-gray-700 mb-1">
                        Target Biaya (opsional)
                    </label>
                    <input type="text" 
                           id="target_biaya" 
                           name="target_biaya" 
                           value="{{ old('target_biaya') }}"
                           placeholder="Contoh: Rp 10.000.000"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('target_biaya')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-card>

        <!-- Laporan Bulanan -->
        @if(isset($laporanBulanan) && $laporanBulanan->isNotEmpty())
        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Pilih Laporan Bulanan</h2>
            <p class="text-sm text-gray-600 mb-4">Pilih laporan bulanan yang akan digunakan untuk evaluasi SKP ini</p>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($laporanBulanan as $laporan)
                <label class="flex items-start p-3 border border-gray-200 rounded-md hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" 
                           name="laporan_bulanan_ids[]" 
                           value="{{ $laporan->id }}"
                           class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div class="ml-3 flex-1">
                        <p class="font-medium text-gray-900">{{ $laporan->nama_bulan }}</p>
                        <p class="text-sm text-gray-600">{{ $laporan->total_lkh }} LKH • {{ number_format($laporan->total_durasi, 1) }} jam</p>
                    </div>
                </label>
                @endforeach
            </div>
        </x-card>
        @endif

        <!-- Actions -->
        <div class="flex justify-end space-x-3">
            <x-button href="{{ route('skp.index', ['tahun' => $tahun ?? date('Y')]) }}" variant="secondary" size="sm">
                Batal
            </x-button>
            <x-button type="submit" variant="primary" size="sm">
                Simpan SKP
            </x-button>
        </div>
    </form>
</div>
@endsection
