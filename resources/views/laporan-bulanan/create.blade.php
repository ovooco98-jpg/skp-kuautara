@extends('layouts.app')

@section('title', 'Buat Laporan Bulanan')

@section('content')
<div>
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Buat Laporan Bulanan</h1>
        <p class="mt-1 text-sm text-gray-500">
            @if(Auth::user()->isKepalaKua())
                Konsolidasi LKH harian dari staff dan/atau kegiatan sendiri untuk periode {{ \Carbon\Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y') }}
            @else
                Konsolidasi LKH harian untuk periode {{ \Carbon\Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y') }}
            @endif
        </p>
    </div>

    <form action="{{ route('laporan-bulanan.store') }}" method="POST" id="laporan-form">
        @csrf
        
        <input type="hidden" name="bulan" value="{{ $bulan }}">
        <input type="hidden" name="tahun" value="{{ $tahun }}">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
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
                              placeholder="@if(Auth::user()->isKepalaKua())Ringkasan kegiatan yang telah dilaksanakan selama bulan ini (dari staff dan/atau kegiatan sendiri)...@elseRingkasan kegiatan yang telah dilaksanakan selama bulan ini...@endif">{{ old('ringkasan_kegiatan', $ringkasanOtomatis ?? '') }}</textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        @if(Auth::user()->isKepalaKua())
                            Field ini akan otomatis terisi dari data LKH yang dipilih (dari staff dan/atau LKH sendiri). Anda bisa mengedit manual jika diperlukan.
                        @else
                            Field ini akan otomatis terisi dari data LKH yang dipilih. Anda bisa mengedit manual jika diperlukan.
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
                            Pencapaian ini akan otomatis dihasilkan dari LKH yang Anda pilih, baik dari staff maupun LKH Anda sendiri. Silakan edit manual sesuai kebutuhan.
                        @else
                            Field ini akan otomatis terisi dari data LKH yang dipilih. Anda bisa mengedit manual jika diperlukan.
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
                              placeholder="@if(Auth::user()->isKepalaKua())Kendala yang dihadapi selama bulan ini (dari staff dan/atau kegiatan sendiri)...@elseKendala yang dihadapi selama bulan ini...@endif">{{ old('kendala', $kendalaOtomatis ?? '') }}</textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        @if(Auth::user()->isKepalaKua())
                            Field ini akan otomatis terisi dari data LKH yang dipilih (dari staff dan/atau LKH sendiri). Anda bisa mengedit manual jika diperlukan.
                        @else
                            Field ini akan otomatis terisi dari data LKH yang dipilih. Anda bisa mengedit manual jika diperlukan.
                        @endif
                    </p>
                </x-card>

                <!-- Rencana Bulan Depan -->
                <x-card>
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Rencana Bulan Depan</h2>
                        <button type="button" 
                                onclick="fillRencana()" 
                                class="text-xs px-3 py-1 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition">
                            Generate Otomatis
                        </button>
                    </div>
                    <textarea name="rencana_bulan_depan" 
                              id="rencana_bulan_depan"
                              rows="4" 
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="@if(Auth::user()->isKepalaKua())Rencana kegiatan untuk bulan depan (dari staff dan/atau kegiatan sendiri)...@elseRencana kegiatan untuk bulan depan...@endif">{{ old('rencana_bulan_depan', $rencanaOtomatis ?? '') }}</textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        @if(Auth::user()->isKepalaKua())
                            Rencana ini akan otomatis dihasilkan dari LKH yang Anda pilih, baik dari staff maupun LKH Anda sendiri. Silakan edit manual sesuai kebutuhan.
                        @else
                            Field ini akan otomatis terisi dari data LKH yang dipilih. Anda bisa mengedit manual jika diperlukan.
                        @endif
                    </p>
                </x-card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Summary -->
                <x-card title="Summary">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total LKH:</span>
                            <span class="font-semibold">{{ $lkh->count() }} kegiatan</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Durasi:</span>
                            <span class="font-semibold">{{ number_format($lkh->sum('durasi'), 1) }} jam</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Periode:</span>
                            <span class="font-semibold">{{ \Carbon\Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y') }}</span>
                        </div>
                    </div>
                </x-card>

                <!-- Target -->
                <x-card title="Target Bulanan">
                    <div class="space-y-3 text-sm">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Target LKH</label>
                            <input type="text" 
                                   name="target_lkh" 
                                   value="{{ old('target_lkh', $targetLkhOtomatis ?? '') }}"
                                   placeholder="Contoh: 20 kegiatan"
                                   class="w-full text-xs px-2.5 py-1.5 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Target jumlah kegiatan harian untuk bulan ini</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Target Durasi</label>
                            <input type="text" 
                                   name="target_durasi" 
                                   value="{{ old('target_durasi', $targetDurasiOtomatis ?? '') }}"
                                   placeholder="Contoh: 80 jam"
                                   class="w-full text-xs px-2.5 py-1.5 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Target total durasi kegiatan untuk bulan ini</p>
                        </div>
                    </div>
                </x-card>

                <!-- Quick Actions -->
                <x-card title="Quick Actions" class="text-sm">
                    <div class="space-y-1">
                        <button type="button" onclick="generateAutomatic()" class="flex items-center justify-center w-full px-2 py-1.5 text-xs border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 transition">
                            Generate Otomatis
                        </button>
                        <button type="button" onclick="window.location.reload()" class="flex items-center justify-center w-full px-2 py-1.5 text-xs border border-gray-600 text-gray-600 rounded-md hover:bg-gray-50 transition">
                            Reset Form
                        </button>
                    </div>
                </x-card>

                <!-- Pilih LKH -->
                <x-card title="Pilih LKH">
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @if(Auth::user()->isKepalaKua())
                            @php
                                $lkhGrouped = $lkh->groupBy('user_id');
                            @endphp
                            @foreach($lkhGrouped as $userId => $userLkh)
                                <div class="mb-3 pb-2 border-b border-gray-200">
                                    <div class="text-xs font-semibold text-gray-700 mb-1.5">
                                        {{ $userLkh->first()->user->name }}
                                        @if($userLkh->first()->user->jabatan)
                                            <span class="text-gray-500">({{ $userLkh->first()->user->jabatan }})</span>
                                        @endif
                                    </div>
                                    <div class="space-y-1.5 pl-2">
                                        @foreach($userLkh as $item)
                                        <label class="flex items-start space-x-2 p-1.5 rounded border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" name="lkh_ids[]" value="{{ $item->id }}" checked
                                                   class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <div class="flex-1 text-xs">
                                                <div class="font-medium">{{ $item->tanggal->format('d/m/Y') }}</div>
                                                <div class="text-gray-600 text-xs truncate">{{ Str::limit($item->uraian_kegiatan, 45) }}</div>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @else
                            @foreach($lkh as $item)
                            <label class="flex items-start space-x-2 p-2 rounded border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="lkh_ids[]" value="{{ $item->id }}" checked
                                       class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <div class="flex-1 text-sm">
                                    <div class="font-medium">{{ $item->tanggal->format('d/m/Y') }}</div>
                                    <div class="text-gray-600 text-xs truncate">{{ Str::limit($item->uraian_kegiatan, 50) }}</div>
                                </div>
                            </label>
                            @endforeach
                        @endif
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        @if(Auth::user()->isKepalaKua())
                            Pilih LKH dari staff dan/atau LKH sendiri yang akan dimasukkan ke laporan bulanan
                        @else
                            Pilih LKH yang akan dimasukkan ke laporan bulanan
                        @endif
                    </p>
                </x-card>

                <!-- Submit Button -->
                <div class="flex space-x-3">
                    <x-button type="button" href="{{ route('laporan-bulanan.index') }}" variant="secondary" class="flex-1" size="sm">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" class="flex-1" size="sm">
                        Simpan
                    </x-button>
                </div>
            </div>
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
        document.getElementById('rencana_bulan_depan').value = rencanaOtomatis || '';
    }

    // Auto-fill saat halaman dimuat jika belum ada isi
    document.addEventListener('DOMContentLoaded', function() {
        const ringkasanField = document.getElementById('ringkasan_kegiatan');
        const pencapaianField = document.getElementById('pencapaian');
        const kendalaField = document.getElementById('kendala');
        const rencanaField = document.getElementById('rencana_bulan_depan');
        
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

    function generateAutomatic() {
        if (!confirm('Generate otomatis akan mengganti semua field yang sudah Anda tulis. Lanjutkan?')) {
            return;
        }

        fetch('{{ route("laporan-bulanan.generate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                bulan: {{ $bulan }},
                tahun: {{ $tahun }}
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/laporan-bulanan/' + data.data.id;
            } else {
                alert('Error: ' + (data.message || 'Gagal generate laporan'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat generate laporan');
        });
    }
</script>
@endpush
@endsection

