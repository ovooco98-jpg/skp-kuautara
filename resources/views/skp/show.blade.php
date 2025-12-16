@extends('layouts.app')

@section('title', 'Detail SKP')

@section('content')
<div>
    <!-- Page Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail SKP</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $skp->kegiatan_tugas_jabatan }}</p>
        </div>
        <div class="flex space-x-2">
            <x-button href="{{ route('skp.index', ['tahun' => $skp->tahun]) }}" variant="secondary" icon="arrow-left" size="sm">
                Kembali
            </x-button>
            @if($skp->canEdit())
            <x-button href="{{ route('skp.edit', $skp->id) }}" variant="primary" icon="pencil" size="sm">
                Edit
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
                        <p class="mt-1 text-sm text-gray-900">{{ $skp->user->name }} ({{ $skp->user->jabatan }})</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tahun</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $skp->tahun }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kegiatan Tugas Jabatan</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $skp->kegiatan_tugas_jabatan }}</p>
                    </div>
                    @if($skp->rincian_tugas)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Rincian Tugas</label>
                        <p class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $skp->rincian_tugas }}</p>
                    </div>
                    @endif
                    @if($skp->skp_atasan)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mengacu pada SKP Atasan</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('skp.show', $skp->skp_atasan->id) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $skp->skp_atasan->kegiatan_tugas_jabatan }}
                            </a>
                        </p>
                    </div>
                    @endif
                </div>
            </x-card>

            <!-- Target Kinerja -->
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Target Kinerja</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($skp->target_kuantitas)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kuantitas</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $skp->target_kuantitas }}</p>
                    </div>
                    @endif
                    @if($skp->target_kualitas)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kualitas</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $skp->target_kualitas }}</p>
                    </div>
                    @endif
                    @if($skp->target_waktu)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Waktu</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $skp->target_waktu }}</p>
                    </div>
                    @endif
                    @if($skp->target_biaya)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Biaya</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $skp->target_biaya }}</p>
                    </div>
                    @endif
                </div>
            </x-card>

            <!-- Realisasi Kinerja -->
            @if($skp->realisasi_kuantitas || $skp->realisasi_kualitas || $skp->realisasi_waktu)
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Realisasi Kinerja</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($skp->realisasi_kuantitas)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kuantitas</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $skp->realisasi_kuantitas }}</p>
                    </div>
                    @endif
                    @if($skp->realisasi_kualitas)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kualitas</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $skp->realisasi_kualitas }}</p>
                    </div>
                    @endif
                    @if($skp->realisasi_waktu)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Waktu</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $skp->realisasi_waktu }}</p>
                    </div>
                    @endif
                    @if($skp->realisasi_biaya)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Biaya</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $skp->realisasi_biaya }}</p>
                    </div>
                    @endif
                    @if($skp->nilai_capaian)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nilai Capaian</label>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ number_format($skp->nilai_capaian, 2) }}</p>
                    </div>
                    @endif
                </div>
            </x-card>
            @endif

            <!-- Laporan Bulanan Terkait -->
            @if($skp->laporanBulanan->isNotEmpty())
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Laporan Bulanan Terkait</h2>
                <div class="space-y-3">
                    @foreach($skp->laporanBulanan as $laporan)
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-md">
                        <div>
                            <p class="font-medium text-gray-900">{{ $laporan->nama_bulan }}</p>
                            <p class="text-sm text-gray-600">{{ $laporan->total_lkh }} LKH • {{ number_format($laporan->total_durasi, 1) }} jam</p>
                        </div>
                        <x-button href="{{ route('laporan-bulanan.show', $laporan->id) }}" variant="secondary" size="sm">
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
                        <x-badge variant="{{ $skp->status === 'selesai' ? 'success' : ($skp->status === 'dinilai' ? 'primary' : 'default') }}" size="lg" class="w-full justify-center">
                            {{ $skp->status_label }}
                        </x-badge>
                    </div>
                    @if($skp->disetujui_oleh)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Disetujui Oleh</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $skp->disetujuiOleh->name }}</p>
                        <p class="text-xs text-gray-500">{{ $skp->disetujui_pada->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                    @if($skp->dinilai_oleh)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dinilai Oleh</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $skp->dinilaiOleh->name }}</p>
                        <p class="text-xs text-gray-500">{{ $skp->dinilai_pada->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </x-card>

            <!-- Actions -->
            @if(Auth::user()->isKepalaKua() && $skp->user_id !== Auth::id())
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Tindakan</h2>
                <div class="space-y-2">
                    @if($skp->status === 'draft')
                    <form method="POST" action="{{ route('skp.setujui', $skp->id) }}">
                        @csrf
                        <x-button type="submit" variant="primary" class="w-full" size="sm">
                            Setujui SKP
                        </x-button>
                    </form>
                    @endif
                    @if($skp->canDinilai())
                    <form method="POST" action="{{ route('skp.nilai', $skp->id) }}">
                        @csrf
                        <x-button type="submit" variant="primary" class="w-full" size="sm">
                            Nilai SKP
                        </x-button>
                    </form>
                    @endif
                </div>
            </x-card>
            @endif

            <!-- Bukti Fisik untuk e-Kinerja -->
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Bukti Fisik e-Kinerja</h2>
                <div class="space-y-3">
                    @if($skp->file_bukti_fisik)
                    <div class="p-3 bg-green-50 border border-green-200 rounded-md">
                        <p class="text-sm font-medium text-green-800 mb-2">✓ Link bukti fisik sudah tersedia</p>
                        <div class="space-y-2">
                            @php
                                $isUrl = filter_var($skp->file_bukti_fisik, FILTER_VALIDATE_URL);
                            @endphp
                            @if($isUrl)
                            <x-button href="{{ $skp->file_bukti_fisik }}" target="_blank" variant="primary" size="sm" class="w-full">
                                Buka di Drive
                            </x-button>
                            @else
                            <x-button href="{{ route('skp.buka-link-bukti-fisik', $skp->id) }}" target="_blank" variant="primary" size="sm" class="w-full">
                                Buka Bukti Fisik
                            </x-button>
                            @endif
                            @if($skp->link_skp_eksternal)
                            <x-button href="{{ $skp->link_skp_eksternal }}" target="_blank" variant="secondary" size="sm" class="w-full">
                                Buka e-Kinerja
                            </x-button>
                            @endif
                        </div>
                        @if($isUrl)
                        <p class="text-xs text-gray-600 mt-2 break-all">{{ Str::limit($skp->file_bukti_fisik, 50) }}</p>
                        @endif
                        @if($skp->uploaded_at)
                        <p class="text-xs text-gray-500 mt-2">Disimpan: {{ $skp->uploaded_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                    @else
                    <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                        <p class="text-sm text-yellow-800 mb-3">Belum ada link bukti fisik.</p>
                        <form method="POST" action="{{ route('skp.generate-bukti-fisik', $skp->id) }}" id="generate-bukti-form" class="mb-3">
                            @csrf
                            <x-button type="submit" variant="primary" class="w-full mb-2" size="sm">
                                Generate Dokumen (Preview)
                            </x-button>
                        </form>
                        <p class="text-xs text-gray-600 mb-3">
                            Generate dokumen untuk preview/download. Setelah itu upload ke Drive dan masukkan link di form bawah.
                        </p>
                    </div>
                    @endif

                    @if(session('preview_file'))
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-md">
                        <p class="text-sm font-medium text-blue-800 mb-2">✓ Dokumen sudah di-generate!</p>
                        <div class="space-y-2">
                            <x-button href="{{ Storage::disk('public')->url(session('preview_file')) }}" target="_blank" variant="primary" size="sm" class="w-full">
                                Buka & Download Dokumen
                            </x-button>
                            <p class="text-xs text-gray-600">
                                1. Buka dokumen di atas<br>
                                2. Print atau Save as PDF<br>
                                3. Upload PDF ke Google Drive<br>
                                4. Copy link Drive dan paste di form bawah
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- Form Input Link Drive -->
                    <form method="POST" action="{{ route('skp.simpan-link-bukti-fisik', $skp->id) }}" class="mt-3">
                        @csrf
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Link Drive Bukti Fisik <span class="text-red-500">*</span>
                        </label>
                        <input type="url" 
                               name="link_drive_bukti_fisik" 
                               value="{{ $skp->file_bukti_fisik && filter_var($skp->file_bukti_fisik, FILTER_VALIDATE_URL) ? $skp->file_bukti_fisik : '' }}"
                               placeholder="https://drive.google.com/file/d/..." 
                               required
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm mb-2">
                        <p class="text-xs text-gray-500 mb-2">
                            Upload file PDF ke Google Drive, lalu copy link dan paste di sini
                        </p>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Link e-Kinerja (opsional)
                        </label>
                        <input type="url" 
                               name="link_skp_eksternal" 
                               value="{{ $skp->link_skp_eksternal }}"
                               placeholder="https://e-kinerja.kemenag.go.id/..." 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm mb-2">
                        <x-button type="submit" variant="primary" size="sm" class="w-full">
                            Simpan Link
                        </x-button>
                    </form>
                </div>
            </x-card>

            <!-- SKP Staff (untuk Kepala KUA) -->
            @if($skp->skpStaff->isNotEmpty())
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">SKP Staff</h2>
                <div class="space-y-2">
                    @foreach($skp->skpStaff as $skpStaff)
                    <div class="p-2 bg-gray-50 rounded-md">
                        <a href="{{ route('skp.show', $skpStaff->id) }}" class="text-sm text-blue-600 hover:text-blue-800">
                            {{ $skpStaff->kegiatan_tugas_jabatan }}
                        </a>
                        <p class="text-xs text-gray-500">{{ $skpStaff->user->name }}</p>
                    </div>
                    @endforeach
                </div>
            </x-card>
            @endif

            @if($skp->catatan)
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Catatan</h2>
                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $skp->catatan }}</p>
            </x-card>
            @endif
        </div>
    </div>
</div>
@endsection
