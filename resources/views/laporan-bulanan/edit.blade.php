@extends('layouts.app')

@section('title', 'Edit Laporan Bulanan')

@section('content')
<div>
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Laporan Bulanan</h1>
        <p class="mt-1 text-sm text-gray-500">
            @if(Auth::user()->isKepalaKua())
                Edit laporan bulanan dari staff dan/atau kegiatan sendiri - {{ $laporan->nama_bulan }}
            @else
                {{ $laporan->nama_bulan }}
            @endif
        </p>
    </div>

    <form action="{{ route('laporan-bulanan.update', $laporan->id) }}" method="POST" id="laporan-form">
        @csrf
        @method('PUT')

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
                              placeholder="@if(Auth::user()->isKepalaKua())Ringkasan kegiatan yang telah dilaksanakan selama bulan ini (dari staff dan/atau kegiatan sendiri)...@elseRingkasan kegiatan yang telah dilaksanakan selama bulan ini...@endif">{{ old('ringkasan_kegiatan', $laporan->ringkasan_kegiatan) }}</textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        @if(Auth::user()->isKepalaKua())
                            Ringkasan ini akan otomatis dihasilkan dari LKH yang Anda pilih, baik dari staff maupun LKH Anda sendiri. Silakan edit manual sesuai kebutuhan.
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
                              placeholder="@if(Auth::user()->isKepalaKua())Pencapaian dan hasil yang diperoleh (dari staff dan/atau kegiatan sendiri)...@elsePencapaian dan hasil yang diperoleh...@endif">{{ old('pencapaian', $laporan->pencapaian) }}</textarea>
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
                              placeholder="@if(Auth::user()->isKepalaKua())Kendala yang dihadapi selama bulan ini (dari staff dan/atau kegiatan sendiri)...@elseKendala yang dihadapi selama bulan ini...@endif">{{ old('kendala', $laporan->kendala) }}</textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        @if(Auth::user()->isKepalaKua())
                            Kendala ini akan otomatis dihasilkan dari LKH yang Anda pilih, baik dari staff maupun LKH Anda sendiri. Silakan edit manual sesuai kebutuhan.
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
                              placeholder="@if(Auth::user()->isKepalaKua())Rencana kegiatan untuk bulan depan (dari staff dan/atau kegiatan sendiri)...@elseRencana kegiatan untuk bulan depan...@endif">{{ old('rencana_bulan_depan', $laporan->rencana_bulan_depan) }}</textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        @if(Auth::user()->isKepalaKua())
                            Rencana ini akan otomatis dihasilkan dari LKH yang Anda pilih, baik dari staff maupun LKH Anda sendiri. Silakan edit manual sesuai kebutuhan.
                        @else
                            Field ini akan otomatis terisi dari data LKH yang dipilih. Anda bisa mengedit manual jika diperlukan.
                        @endif
                    </p>
                </x-card>

                <!-- Status -->
                <x-card title="Status">
                    <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="draft" {{ $laporan->status === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="selesai" {{ $laporan->status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </x-card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Summary -->
                <x-card title="Summary">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total LKH:</span>
                            <span class="font-semibold">{{ $laporan->lkh->count() }} kegiatan</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Durasi:</span>
                            <span class="font-semibold">{{ number_format($laporan->total_durasi, 1) }} jam</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Periode:</span>
                            <span class="font-semibold">{{ $laporan->nama_bulan }}</span>
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
                                   value="{{ old('target_lkh', $laporan->target_lkh ?? '') }}"
                                   placeholder="Contoh: 20 kegiatan"
                                   class="w-full text-xs px-2.5 py-1.5 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Target jumlah kegiatan harian untuk bulan ini</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Target Durasi</label>
                            <input type="text" 
                                   name="target_durasi" 
                                   value="{{ old('target_durasi', $laporan->target_durasi ?? '') }}"
                                   placeholder="Contoh: 80 jam"
                                   class="w-full text-xs px-2.5 py-1.5 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Target total durasi kegiatan untuk bulan ini</p>
                        </div>
                    </div>
                </x-card>

                <!-- Pilih LKH -->
                <x-card title="Pilih LKH">
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @php
                            $selectedLkhIds = $laporan->lkh->pluck('id')->toArray();
                        @endphp
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
                                            <input type="checkbox" name="lkh_ids[]" value="{{ $item->id }}" 
                                                   {{ in_array($item->id, $selectedLkhIds) ? 'checked' : '' }}
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
                                <input type="checkbox" name="lkh_ids[]" value="{{ $item->id }}" 
                                       {{ in_array($item->id, $selectedLkhIds) ? 'checked' : '' }}
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
                    <x-button type="button" href="{{ route('laporan-bulanan.show', $laporan->id) }}" variant="secondary" class="flex-1" size="sm">
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
</script>
@endpush
@endsection

