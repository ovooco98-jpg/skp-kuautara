@extends('layouts.app')

@section('title', 'Buat Laporan Triwulanan')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Buat Laporan Triwulanan</h1>
        <p class="mt-1 text-sm text-gray-500">
            @if(Auth::user()->isKepalaKua())
                Konsolidasi laporan bulanan dari staff dan/atau laporan bulanan sendiri - Triwulan {{ $triwulan }} Tahun {{ $tahun }}
            @else
                Triwulan {{ $triwulan }} Tahun {{ $tahun }}
            @endif
        </p>
    </div>

    <form method="POST" action="{{ route('laporan-triwulanan.store') }}" class="space-y-6">
        @csrf
        
        <input type="hidden" name="triwulan" value="{{ $triwulan }}">
        <input type="hidden" name="tahun" value="{{ $tahun }}">

        <!-- Pilih Laporan Bulanan -->
        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Pilih Laporan Bulanan</h2>
            <p class="text-sm text-gray-600 mb-4">
                @if(Auth::user()->isKepalaKua())
                    Pilih laporan bulanan dari staff dan/atau laporan bulanan sendiri yang akan digunakan untuk laporan triwulanan ini
                @else
                    Pilih laporan bulanan yang akan digunakan untuk laporan triwulanan ini
                @endif
            </p>
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @if(Auth::user()->isKepalaKua())
                    @php
                        $laporanBulananGrouped = $laporanBulanan->groupBy('user_id');
                    @endphp
                    @foreach($laporanBulananGrouped as $userId => $userLaporan)
                        <div class="mb-3 pb-2 border-b border-gray-200">
                            <div class="text-xs font-semibold text-gray-700 mb-1.5">
                                {{ $userLaporan->first()->user->name }}
                                @if($userLaporan->first()->user->jabatan)
                                    <span class="text-gray-500">({{ $userLaporan->first()->user->jabatan }})</span>
                                @endif
                            </div>
                            <div class="space-y-1.5 pl-2">
                                @foreach($userLaporan as $laporan)
                                <label class="flex items-start p-2 border border-gray-200 rounded-md hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" 
                                           name="laporan_bulanan_ids[]" 
                                           value="{{ $laporan->id }}"
                                           required
                                           class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <div class="ml-2 flex-1">
                                        <p class="font-medium text-sm text-gray-900">{{ $laporan->nama_bulan }}</p>
                                        <p class="text-xs text-gray-600">{{ $laporan->total_lkh }} LKH • {{ number_format($laporan->total_durasi, 1) }} jam</p>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    @foreach($laporanBulanan as $laporan)
                    <label class="flex items-start p-3 border border-gray-200 rounded-md hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" 
                               name="laporan_bulanan_ids[]" 
                               value="{{ $laporan->id }}"
                               required
                               class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div class="ml-3 flex-1">
                            <p class="font-medium text-gray-900">{{ $laporan->nama_bulan }}</p>
                            <p class="text-sm text-gray-600">{{ $laporan->total_lkh }} LKH • {{ number_format($laporan->total_durasi, 1) }} jam</p>
                        </div>
                    </label>
                    @endforeach
                @endif
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
                      placeholder="@if(Auth::user()->isKepalaKua())Ringkasan kegiatan yang telah dilaksanakan selama triwulan ini (dari staff dan/atau kegiatan sendiri)...@elseRingkasan kegiatan yang telah dilaksanakan selama triwulan ini...@endif">{{ old('ringkasan_kegiatan', $ringkasanOtomatis ?? '') }}</textarea>
            <p class="mt-2 text-xs text-gray-500">
                @if(Auth::user()->isKepalaKua())
                    Ringkasan ini akan otomatis dihasilkan dari laporan bulanan yang Anda pilih, baik dari staff maupun laporan bulanan Anda sendiri. Silakan edit manual sesuai kebutuhan.
                @else
                    Field ini akan otomatis terisi dari data LKH dan LKB yang dipilih. Anda bisa mengedit manual jika diperlukan.
                @endif
            </p>
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
                      placeholder="@if(Auth::user()->isKepalaKua())Pencapaian dan hasil yang diperoleh (dari staff dan/atau kegiatan sendiri)...@elsePencapaian dan hasil yang diperoleh...@endif">{{ old('pencapaian', $pencapaianOtomatis ?? '') }}</textarea>
            <p class="mt-2 text-xs text-gray-500">
                @if(Auth::user()->isKepalaKua())
                    Pencapaian ini akan otomatis dihasilkan dari laporan bulanan yang Anda pilih, baik dari staff maupun laporan bulanan Anda sendiri. Silakan edit manual sesuai kebutuhan.
                @else
                    Field ini akan otomatis terisi dari data LKH dan LKB yang dipilih. Anda bisa mengedit manual jika diperlukan.
                @endif
            </p>
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
                      placeholder="@if(Auth::user()->isKepalaKua())Kendala yang dihadapi selama triwulan ini (dari staff dan/atau kegiatan sendiri)...@elseKendala yang dihadapi selama triwulan ini...@endif">{{ old('kendala', $kendalaOtomatis ?? '') }}</textarea>
            <p class="mt-2 text-xs text-gray-500">
                @if(Auth::user()->isKepalaKua())
                    Kendala ini akan otomatis dihasilkan dari laporan bulanan yang Anda pilih, baik dari staff maupun laporan bulanan Anda sendiri. Silakan edit manual sesuai kebutuhan.
                @else
                    Field ini akan otomatis terisi dari data LKH dan LKB yang dipilih. Anda bisa mengedit manual jika diperlukan.
                @endif
            </p>
        </x-card>

        <!-- Rencana Triwulan Depan -->
        <x-card>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Rencana Triwulan Depan</h2>
                <button type="button" 
                        onclick="fillRencana()" 
                        class="text-xs px-3 py-1 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition">
                    Generate Otomatis
                </button>
            </div>
            <textarea name="rencana_triwulan_depan" 
                      id="rencana_triwulan_depan"
                      rows="4" 
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="@if(Auth::user()->isKepalaKua())Rencana kegiatan untuk triwulan depan (dari staff dan/atau kegiatan sendiri)...@elseRencana kegiatan untuk triwulan depan...@endif">{{ old('rencana_triwulan_depan', $rencanaOtomatis ?? '') }}</textarea>
            <p class="mt-2 text-xs text-gray-500">
                @if(Auth::user()->isKepalaKua())
                    Rencana ini akan otomatis dihasilkan dari laporan bulanan yang Anda pilih, baik dari staff maupun laporan bulanan Anda sendiri. Silakan edit manual sesuai kebutuhan.
                @else
                    Field ini akan otomatis terisi dari data LKH dan LKB yang dipilih. Anda bisa mengedit manual jika diperlukan.
                @endif
            </p>
        </x-card>

        <!-- Actions -->
        <div class="flex justify-end space-x-3">
            <x-button href="{{ route('laporan-triwulanan.index', ['tahun' => $tahun]) }}" variant="secondary" size="sm">
                Batal
            </x-button>
            <x-button type="submit" variant="primary" size="sm">
                Simpan Laporan Triwulanan
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
        document.getElementById('rencana_triwulan_depan').value = rencanaOtomatis || '';
    }

    // Auto-fill saat halaman dimuat jika belum ada isi
    document.addEventListener('DOMContentLoaded', function() {
        const ringkasanField = document.getElementById('ringkasan_kegiatan');
        const pencapaianField = document.getElementById('pencapaian');
        const kendalaField = document.getElementById('kendala');
        const rencanaField = document.getElementById('rencana_triwulan_depan');
        
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
