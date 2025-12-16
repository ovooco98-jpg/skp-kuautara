<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SASARAN KINERJA PEGAWAI - Triwulan {{ $skp->triwulan }} {{ $skp->tahun }}</title>
    <style>
        @page {
            margin: 2cm;
        }
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 15px; }
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
        }
        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header h2 {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .header p {
            font-size: 11pt;
            margin-top: 5px;
        }
        .info-tables {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 15px;
        }
        .info-table {
            width: 48%;
            border: 1px solid #000;
        }
        .info-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            border: 1px solid #000;
            font-size: 10pt;
        }
        .info-table td {
            padding: 6px 8px;
            border: 1px solid #000;
            font-size: 10pt;
        }
        .info-table td:first-child {
            width: 30px;
            text-align: center;
            font-weight: bold;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10pt;
        }
        .main-table th,
        .main-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        .main-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .main-table .col-no {
            width: 3%;
            text-align: center;
        }
        .main-table .col-rencana-pimpinan {
            width: 20%;
        }
        .main-table .col-rencana-kerja {
            width: 20%;
        }
        .main-table .col-aspek {
            width: 8%;
            text-align: center;
        }
        .main-table .col-indikator {
            width: 30%;
        }
        .main-table .col-target {
            width: 19%;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 60px;
            padding-top: 5px;
            min-height: 60px;
        }
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin: 20px 0 10px 0;
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">🖨️ Cetak</button>
    
    <div class="header">
        <h1>SASARAN KINERJA PEGAWAI</h1>
        <h2>PENDEKATAN HASIL KERJA KUANTITATIF</h2>
        <h2>BAGI PEJABAT ADMINISTRASI DAN PEJABAT FUNGSIONAL</h2>
        <p style="text-align: right; margin-top: 10px; margin-right: 50px;">
            PERIODE PENILAIAN: 
            @php
                $bulanMulai = ($skp->triwulan - 1) * 3 + 1;
                $bulanSelesai = $skp->triwulan * 3;
                $namaBulanMulai = \Carbon\Carbon::create($skp->tahun, $bulanMulai, 1)->locale('id')->translatedFormat('F');
                $namaBulanSelesai = \Carbon\Carbon::create($skp->tahun, $bulanSelesai, 1)->locale('id')->translatedFormat('F');
                $tanggalMulai = ($bulanMulai == 1) ? 1 : 1;
                $tanggalSelesai = ($bulanSelesai == 3) ? 31 : ($bulanSelesai == 6) ? 30 : ($bulanSelesai == 9) ? 30 : 31;
            @endphp
            {{ $tanggalMulai }} {{ strtoupper($namaBulanMulai) }} SD {{ $tanggalSelesai }} {{ strtoupper($namaBulanSelesai) }} TAHUN {{ $skp->tahun }}
        </p>
    </div>

    <!-- Informasi Pegawai dan Penilai -->
    <div class="info-tables">
        <table class="info-table">
            <thead>
                <tr>
                    <th colspan="2">PEGAWAI YANG DINILAI</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td><strong>NAMA</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td>{{ $skp->user->name }}</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td><strong>NIP</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td>{{ $skp->user->nip ?? '-' }}</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td><strong>PANGKAT / GOL RUANG</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td>{{ isset($skp->user->pangkat_gol) ? $skp->user->pangkat_gol : '-' }}</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td><strong>JABATAN</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td>{{ $skp->user->jabatan ?? '-' }}</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td><strong>UNIT KERJA</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td>{{ $skp->user->unit_kerja ?? 'KUA Kecamatan Banjarmasin Utara' }}</td>
                </tr>
            </tbody>
        </table>

        <table class="info-table">
            <thead>
                <tr>
                    <th colspan="2">PEJABAT PENILAI KINERJA</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $kepalaKua = \App\Models\User::where('role', 'kepala_kua')->where('is_active', true)->first();
                @endphp
                <tr>
                    <td>1</td>
                    <td><strong>NAMA</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td>{{ $kepalaKua ? $kepalaKua->name : 'Kepala KUA' }}</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td><strong>NIP</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td>{{ $kepalaKua ? ($kepalaKua->nip ?? '-') : '-' }}</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td><strong>PANGKAT / GOL. RUANG</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td>{{ $kepalaKua ? (isset($kepalaKua->pangkat_gol) ? $kepalaKua->pangkat_gol : '-') : '-' }}</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td><strong>JABATAN</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td>{{ $kepalaKua ? ($kepalaKua->jabatan ?? 'Kepala KUA') : 'Kepala KUA' }}</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td><strong>UNIT KERJA</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td>KUA Kecamatan Banjarmasin Utara</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Tabel Hasil Kerja -->
    <div class="section-title">HASIL KERJA</div>
    <table class="main-table">
        <thead>
            <tr>
                <th class="col-no">(1)<br>NO</th>
                <th class="col-rencana-pimpinan">(2)<br>RENCANA HASIL KERJA PIMPINAN YANG DIINTERVENSI</th>
                <th class="col-rencana-kerja">(3)<br>RENCANA HASIL KERJA</th>
                <th class="col-aspek">(4)<br>ASPEK</th>
                <th class="col-indikator">(5)<br>INDIKATOR KINERJA INDIVIDU</th>
                <th class="col-target">(6)<br>TARGET</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $aspekList = [];
                if ($skp->target_kuantitas) $aspekList[] = 'Kuantitas';
                if ($skp->target_kualitas) $aspekList[] = 'Kualitas';
                if ($skp->target_waktu) $aspekList[] = 'Waktu';
                if ($skp->target_biaya) $aspekList[] = 'Biaya';
            @endphp
            
            @if(count($aspekList) > 0)
                @foreach($aspekList as $index => $aspek)
                <tr>
                    @if($index === 0)
                    <td rowspan="{{ count($aspekList) }}" style="text-align: center; vertical-align: middle;">{{ $no }}</td>
                    <td rowspan="{{ count($aspekList) }}" style="vertical-align: middle;">
                        @if($skp->skpAtasan)
                            {{ $skp->skpAtasan->kegiatan_tugas_jabatan }}
                        @else
                            -
                        @endif
                    </td>
                    <td rowspan="{{ count($aspekList) }}" style="vertical-align: middle;">
                        {{ $skp->kegiatan_tugas_jabatan }}
                        @if($skp->rincian_tugas)
                            <br><small>{{ Str::limit($skp->rincian_tugas, 100) }}</small>
                        @endif
                    </td>
                    @endif
                    <td style="text-align: center;">{{ $aspek }}</td>
                    <td>
                        @if($aspek === 'Kuantitas')
                            Jumlah {{ strtolower($skp->kegiatan_tugas_jabatan) }} yang telah dilaksanakan
                        @elseif($aspek === 'Kualitas')
                            Persentase kesesuaian {{ strtolower($skp->kegiatan_tugas_jabatan) }} yang telah dilaksanakan
                        @elseif($aspek === 'Waktu')
                            Ketepatan waktu pelaksanaan {{ strtolower($skp->kegiatan_tugas_jabatan) }}
                        @elseif($aspek === 'Biaya')
                            Efisiensi biaya pelaksanaan {{ strtolower($skp->kegiatan_tugas_jabatan) }}
                        @endif
                    </td>
                    <td>
                        @if($aspek === 'Kuantitas')
                            {{ $skp->target_kuantitas }}
                        @elseif($aspek === 'Kualitas')
                            {{ $skp->target_kualitas }}
                        @elseif($aspek === 'Waktu')
                            {{ $skp->target_waktu }}
                        @elseif($aspek === 'Biaya')
                            {{ $skp->target_biaya }}
                        @endif
                    </td>
                </tr>
                @endforeach
            @else
            <tr>
                <td style="text-align: center;">{{ $no }}</td>
                <td>
                    @if($skp->skpAtasan)
                        {{ $skp->skpAtasan->kegiatan_tugas_jabatan }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    {{ $skp->kegiatan_tugas_jabatan }}
                    @if($skp->rincian_tugas)
                        <br><small>{{ Str::limit($skp->rincian_tugas, 100) }}</small>
                    @endif
                </td>
                <td style="text-align: center;">-</td>
                <td>-</td>
                <td>-</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Tabel Realisasi (jika ada) -->
    @if($skp->realisasi_kuantitas || $skp->realisasi_kualitas || $skp->realisasi_waktu)
    <div class="section-title">REALISASI KINERJA</div>
    <table class="main-table">
        <thead>
            <tr>
                <th class="col-no">NO</th>
                <th class="col-rencana-kerja">RENCANA HASIL KERJA</th>
                <th class="col-aspek">ASPEK</th>
                <th class="col-indikator">REALISASI</th>
                <th class="col-target">NILAI CAPAIAN</th>
            </tr>
        </thead>
        <tbody>
            @php
                $noReal = 1;
            @endphp
            @if($skp->realisasi_kuantitas)
            <tr>
                <td style="text-align: center;">{{ $noReal }}</td>
                <td>{{ $skp->kegiatan_tugas_jabatan }}</td>
                <td style="text-align: center;">Kuantitas</td>
                <td>{{ $skp->realisasi_kuantitas }}</td>
                <td style="text-align: center;">{{ $skp->nilai_capaian ? number_format($skp->nilai_capaian, 2) : '-' }}</td>
            </tr>
            @php $noReal++; @endphp
            @endif
            @if($skp->realisasi_kualitas)
            <tr>
                <td style="text-align: center;">{{ $noReal }}</td>
                <td>{{ $skp->kegiatan_tugas_jabatan }}</td>
                <td style="text-align: center;">Kualitas</td>
                <td>{{ $skp->realisasi_kualitas }}</td>
                <td style="text-align: center;">-</td>
            </tr>
            @php $noReal++; @endphp
            @endif
            @if($skp->realisasi_waktu)
            <tr>
                <td style="text-align: center;">{{ $noReal }}</td>
                <td>{{ $skp->kegiatan_tugas_jabatan }}</td>
                <td style="text-align: center;">Waktu</td>
                <td>{{ $skp->realisasi_waktu }}</td>
                <td style="text-align: center;">-</td>
            </tr>
            @php $noReal++; @endphp
            @endif
        </tbody>
    </table>
    @endif

    <!-- Kolom Penandatanganan -->
    <div class="signature-section">
        <div class="signature-box">
            <div style="margin-bottom: 80px;">
                <div style="margin-bottom: 5px;">Pegawai yang Dinilai</div>
                <div class="signature-line" style="margin-top: 60px;">
                    <div style="font-weight: bold;">{{ $skp->user->name }}</div>
                    <div>NIP. {{ $skp->user->nip ?? '-' }}</div>
                </div>
            </div>
        </div>
        <div class="signature-box">
            <div style="margin-bottom: 80px;">
                <div style="margin-bottom: 5px;">Pejabat Penilai Kinerja</div>
                <div class="signature-line" style="margin-top: 60px;">
                    <div style="font-weight: bold;">{{ $kepalaKua ? $kepalaKua->name : 'Kepala KUA' }}</div>
                    <div>NIP. {{ $kepalaKua ? ($kepalaKua->nip ?? '-') : '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
