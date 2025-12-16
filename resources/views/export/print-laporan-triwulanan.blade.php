<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Triwulanan - {{ $laporan->nama_triwulan }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        @page {
            margin: 1.5cm 2cm;
            size: A4;
        }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
            background: #fff;
        }
        .container {
            max-width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #000;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 2px;
            line-height: 1.2;
        }
        .header h2 {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 5px;
            line-height: 1.3;
        }
        .header .periode {
            text-align: right;
            margin-top: 10px;
            font-size: 11pt;
            font-weight: normal;
        }
        .info-tables-wrapper {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 20px;
        }
        .info-table {
            width: 48%;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        .info-table thead th {
            background-color: #fff;
            font-weight: bold;
            text-align: center;
            padding: 6px 4px;
            border: 1px solid #000;
            font-size: 10pt;
        }
        .info-table tbody td {
            padding: 4px 6px;
            border: 1px solid #000;
            font-size: 9.5pt;
            vertical-align: top;
        }
        .info-table tbody td:first-child {
            width: 25px;
            text-align: center;
            font-weight: bold;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9pt;
            border: 1px solid #000;
        }
        .main-table th,
        .main-table td {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: left;
            vertical-align: top;
        }
        .main-table th {
            background-color: #fff;
            font-weight: bold;
            text-align: center;
            font-size: 9pt;
            padding: 5px 3px;
        }
        .main-table .col-no {
            width: 4%;
            text-align: center;
        }
        .main-table .col-judul {
            width: 18%;
        }
        .main-table .col-isi {
            width: 78%;
        }
        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin: 20px 0 8px 0;
            padding: 5px 0;
            text-transform: uppercase;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            gap: 30px;
            align-items: flex-start;
        }
        .signature-box {
            width: 48%;
            text-align: left;
        }
        .signature-label {
            margin-bottom: 5px;
            font-size: 10pt;
            min-height: 20px;
        }
        .signature-title {
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 10pt;
            min-height: 20px;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
            min-height: 50px;
        }
        .signature-name {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 2px;
        }
        .signature-nip {
            font-size: 9pt;
        }
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        .btn-print:hover {
            background: #0056b3;
        }
        @media screen {
            body {
                padding: 20px;
                background: #f5f5f5;
            }
            .container {
                background: #fff;
                padding: 30px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                max-width: 210mm;
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">🖨️ Cetak / Print</button>
    
    <div class="container">
        <div class="kementerian">KEMENTERIAN AGAMA</div>
        <div class="header">
            <h1>LAPORAN TRIWULANAN</h1>
            <h2>KANTOR URUSAN AGAMA KECAMATAN BANJARMASIN UTARA</h2>
            <div class="periode">
                PERIODE PENILAIAN: {{ strtoupper($laporan->nama_triwulan) }} TAHUN {{ $laporan->tahun }}
            </div>
        </div>

        <!-- Informasi Pegawai dan Penilai -->
        <div class="info-tables-wrapper">
            <table class="info-table">
                <thead>
                    <tr>
                        <th colspan="2">PEGAWAI YANG MELAKSANAKAN</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><strong>NAMA</strong></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>{{ $laporan->user->name }}</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><strong>NIP</strong></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>{{ $laporan->user->nip ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td><strong>PANGKAT / GOL RUANG</strong></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>{{ isset($laporan->user->pangkat_gol) ? $laporan->user->pangkat_gol : '-' }}</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td><strong>JABATAN</strong></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>{{ $laporan->user->jabatan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td><strong>UNIT KERJA</strong></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>{{ $laporan->user->unit_kerja ?? 'KUA Kecamatan Banjarmasin Utara' }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="info-table">
                <thead>
                    <tr>
                        <th colspan="2">MENGETAHUI</th>
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

        <!-- Ringkasan Kegiatan -->
        <div class="section-title">I. RINGKASAN KEGIATAN</div>
        <table class="main-table">
            <thead>
                <tr>
                    <th class="col-no">(1)<br>NO</th>
                    <th class="col-judul">(2)<br>URAIAN</th>
                    <th class="col-isi">(3)<br>KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center; vertical-align: top;">1</td>
                    <td style="vertical-align: top;"><strong>RINGKASAN KEGIATAN</strong></td>
                    <td style="text-align: justify; white-space: pre-wrap; vertical-align: top;">{{ $ringkasan }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Pencapaian -->
        <div class="section-title">II. PENCAPAIAN</div>
        <table class="main-table">
            <thead>
                <tr>
                    <th class="col-no">(1)<br>NO</th>
                    <th class="col-judul">(2)<br>URAIAN</th>
                    <th class="col-isi">(3)<br>KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center; vertical-align: top;">1</td>
                    <td style="vertical-align: top;"><strong>PENCAPAIAN</strong></td>
                    <td style="text-align: justify; white-space: pre-wrap; vertical-align: top;">{{ $pencapaian }}</td>
                </tr>
            </tbody>
        </table>

        @if($kendala)
        <!-- Kendala -->
        <div class="section-title">III. KENDALA</div>
        <table class="main-table">
            <thead>
                <tr>
                    <th class="col-no">(1)<br>NO</th>
                    <th class="col-judul">(2)<br>URAIAN</th>
                    <th class="col-isi">(3)<br>KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center; vertical-align: top;">1</td>
                    <td style="vertical-align: top;"><strong>KENDALA</strong></td>
                    <td style="text-align: justify; white-space: pre-wrap; vertical-align: top;">{{ $kendala }}</td>
                </tr>
            </tbody>
        </table>
        @endif

        @if($rencana)
        <!-- Rencana Triwulan Depan -->
        <div class="section-title">IV. RENCANA TRIWULAN DEPAN</div>
        <table class="main-table">
            <thead>
                <tr>
                    <th class="col-no">(1)<br>NO</th>
                    <th class="col-judul">(2)<br>URAIAN</th>
                    <th class="col-isi">(3)<br>KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center; vertical-align: top;">1</td>
                    <td style="vertical-align: top;"><strong>RENCANA TRIWULAN DEPAN</strong></td>
                    <td style="text-align: justify; white-space: pre-wrap; vertical-align: top;">{{ $rencana }}</td>
                </tr>
            </tbody>
        </table>
        @endif

        <!-- Kolom Penandatanganan -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-label">Pegawai yang Melaksanakan</div>
                <div class="signature-title" style="visibility: hidden;">Placeholder</div>
                <div class="signature-line">
                    <div class="signature-name">{{ $laporan->user->name }}</div>
                    <div class="signature-nip">NIP. {{ $laporan->user->nip ?? '-' }}</div>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-label">Mengetahui,</div>
                <div class="signature-title">Kepala KUA Kecamatan Banjarmasin Utara</div>
                <div class="signature-line">
                    <div class="signature-name">{{ $kepalaKua ? $kepalaKua->name : 'Kepala KUA' }}</div>
                    <div class="signature-nip">NIP. {{ $kepalaKua ? ($kepalaKua->nip ?? '-') : '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
