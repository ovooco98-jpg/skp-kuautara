@extends('layouts.app')

@section('title', 'Buat Laporan Tahunan')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Buat Laporan Tahunan</h1>
        <p class="mt-1 text-sm text-gray-500">Tahun {{ $tahun }}</p>
    </div>

    <form method="POST" action="{{ route('laporan-tahunan.store') }}" class="space-y-6">
        @csrf
        
        <input type="hidden" name="tahun" value="{{ $tahun }}">

        <!-- Pilih Laporan Triwulanan -->
        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Pilih Laporan Triwulanan</h2>
            <p class="text-sm text-gray-600 mb-4">Pilih laporan triwulanan yang akan digunakan untuk laporan tahunan ini</p>
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @foreach($laporanTriwulanan as $laporan)
                <label class="flex items-start p-3 border border-gray-200 rounded-md hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" 
                           name="laporan_triwulanan_ids[]" 
                           value="{{ $laporan->id }}"
                           required
                           class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div class="ml-3 flex-1">
                        <p class="font-medium text-gray-900">{{ $laporan->nama_triwulan }}</p>
                        <p class="text-sm text-gray-600">{{ $laporan->laporanBulanan->count() }} laporan bulanan • {{ $laporan->total_lkh }} LKH • {{ number_format($laporan->total_durasi, 1) }} jam</p>
                    </div>
                </label>
                @endforeach
            </div>
        </x-card>

        <!-- Ringkasan Kegiatan -->
        <x-card>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Ringkasan Kegiatan</h2>
                <button type="button" 
                        onclick="fillRingkasan()" 
                        class="text-xs px-3 py-1 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition">
                    Generate Otomatis
                </button>
            </div>
            <textarea name="ringkasan_kegiatan" 
                      id="ringkasan_kegiatan"
                      rows="5" 
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="Ringkasan kegiatan yang telah dilaksanakan selama tahun ini...">{{ old('ringkasan_kegiatan', $ringkasanOtomatis ?? '') }}</textarea>
            <p class="mt-2 text-xs text-gray-500">Field ini akan otomatis terisi dari data LKH dan LKB yang dipilih. Anda bisa mengedit manual jika diperlukan.</p>
        </x-card>

        <!-- Pencapaian -->
        <x-card>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Pencapaian</h2>
                <button type="button" 
                        onclick="fillPencapaian()" 
                        class="text-xs px-3 py-1 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition">
                    Generate Otomatis
                </button>
            </div>
            <textarea name="pencapaian" 
                      id="pencapaian"
                      rows="5" 
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="Pencapaian dan hasil yang diperoleh...">{{ old('pencapaian', $pencapaianOtomatis ?? '') }}</textarea>
            <p class="mt-2 text-xs text-gray-500">Field ini akan otomatis terisi dari data LKH dan LKB yang dipilih. Anda bisa mengedit manual jika diperlukan.</p>
        </x-card>

        <!-- Kendala -->
        <x-card>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Kendala</h2>
                <button type="button" 
                        onclick="fillKendala()" 
                        class="text-xs px-3 py-1 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition">
                    Generate Otomatis
                </button>
            </div>
            <textarea name="kendala" 
                      id="kendala"
                      rows="4" 
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="Kendala yang dihadapi selama tahun ini...">{{ old('kendala', $kendalaOtomatis ?? '') }}</textarea>
            <p class="mt-2 text-xs text-gray-500">Field ini akan otomatis terisi dari data LKH dan LKB yang dipilih. Anda bisa mengedit manual jika diperlukan.</p>
        </x-card>

        <!-- Rencana Tahun Depan -->
        <x-card>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Rencana Tahun Depan</h2>
                <button type="button" 
                        onclick="fillRencana()" 
                        class="text-xs px-3 py-1 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition">
                    Generate Otomatis
                </button>
            </div>
            <textarea name="rencana_tahun_depan" 
                      id="rencana_tahun_depan"
                      rows="4" 
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="Rencana kegiatan untuk tahun depan...">{{ old('rencana_tahun_depan', $rencanaOtomatis ?? '') }}</textarea>
            <p class="mt-2 text-xs text-gray-500">Field ini akan otomatis terisi dari data LKH dan LKB yang dipilih. Anda bisa mengedit manual jika diperlukan.</p>
        </x-card>

        <!-- Actions -->
        <div class="flex justify-end space-x-3">
            <x-button href="{{ route('laporan-tahunan.index', ['tahun' => $tahun]) }}" variant="secondary" size="sm">
                Batal
            </x-button>
            <x-button type="submit" variant="primary" size="sm">
                Simpan Laporan Tahunan
            </x-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Data untuk auto-fill
    const ringkasanOtomatis = @json($ringkasanOtomatis ?? '');
    const pencapaianOtomatis = @json($pencapaianOtomatis ?? '');
    const kendalaOtomatis = @json($kendalaOtomatis ?? '');
    const rencanaOtomatis = @json($rencanaOtomatis ?? '');

    function fillRingkasan() {
        document.getElementById('ringkasan_kegiatan').value = ringkasanOtomatis || '';
    }

    function fillPencapaian() {
        document.getElementById('pencapaian').value = pencapaianOtomatis || '';
    }

    function fillKendala() {
        document.getElementById('kendala').value = kendalaOtomatis || '';
    }

    function fillRencana() {
        document.getElementById('rencana_tahun_depan').value = rencanaOtomatis || '';
    }

    // Auto-fill saat halaman dimuat jika belum ada isi
    document.addEventListener('DOMContentLoaded', function() {
        const ringkasanField = document.getElementById('ringkasan_kegiatan');
        const pencapaianField = document.getElementById('pencapaian');
        const kendalaField = document.getElementById('kendala');
        const rencanaField = document.getElementById('rencana_tahun_depan');
        
        if (ringkasanField && !ringkasanField.value && ringkasanOtomatis) {
            ringkasanField.value = ringkasanOtomatis;
        }
        
        if (pencapaianField && !pencapaianField.value && pencapaianOtomatis) {
            pencapaianField.value = pencapaianOtomatis;
        }

        if (kendalaField && !kendalaField.value && kendalaOtomatis) {
            kendalaField.value = kendalaOtomatis;
        }

        if (rencanaField && !rencanaField.value && rencanaOtomatis) {
            rencanaField.value = rencanaOtomatis;
        }
    });
</script>
@endpush
@endsection
