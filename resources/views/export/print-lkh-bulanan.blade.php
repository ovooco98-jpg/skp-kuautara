<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LKH Bulanan - {{ \Carbon\Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y') }}</title>
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
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.6;
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
            gap: 20px;
            margin-bottom: 25px;
        }
        .info-table {
            width: 48%;
            border: 2px solid #000;
            border-collapse: collapse;
        }
        .info-table thead th {
            background-color: #e5e5e5;
            font-weight: bold;
            text-align: center;
            padding: 10px 8px;
            border: 1px solid #000;
            font-size: 11pt;
            border-bottom: 2px solid #000;
        }
        .info-table tbody td {
            padding: 8px 10px;
            border: 1px solid #000;
            font-size: 10pt;
            vertical-align: top;
        }
        .info-table tbody td:first-child {
            width: 35px;
            text-align: center;
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin: 25px 0 12px 0;
            padding: 8px 0;
            border-bottom: 2px solid #000;
            text-transform: uppercase;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            border: 2px solid #000;
            font-size: 9.5pt;
        }
        .main-table thead th {
            background-color: #e5e5e5;
            font-weight: bold;
            text-align: center;
            padding: 10px 8px;
            border: 1px solid #000;
            font-size: 10pt;
        }
        .main-table tbody td {
            padding: 8px 10px;
            border: 1px solid #000;
            font-size: 9.5pt;
            vertical-align: top;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            border: 2px solid #000;
        }
        .summary-table thead th {
            background-color: #e5e5e5;
            font-weight: bold;
            text-align: center;
            padding: 12px 15px;
            border: 1px solid #000;
            font-size: 11pt;
        }
        .summary-table tbody td {
            padding: 12px 15px;
            border: 1px solid #000;
            font-size: 10.5pt;
        }
        .summary-table tbody td:first-child {
            font-weight: 600;
        }
        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            gap: 40px;
            align-items: flex-start;
        }
        .signature-box {
            width: 48%;
            text-align: left;
        }
        .signature-label {
            margin-bottom: 5px;
            font-size: 11pt;
            min-height: 20px;
        }
        .signature-title {
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 11pt;
            min-height: 20px;
        }
        .signature-line {
            border-top: 2px solid #000;
            margin-top: 70px;
            padding-top: 8px;
            min-height: 70px;
        }
        .signature-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 3px;
        }
        .signature-nip {
            font-size: 10pt;
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
        <div class="header">
            <h1>LAPORAN KEGIATAN HARIAN (LKH)</h1>
            <h2>KANTOR URUSAN AGAMA KECAMATAN BANJARMASIN UTARA</h2>
            <div class="periode">
                PERIODE: {{ strtoupper(\Carbon\Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y')) }}
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
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><strong>NIP</strong></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>{{ $user->nip ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td><strong>PANGKAT / GOL RUANG</strong></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>{{ isset($user->pangkat_gol) ? $user->pangkat_gol : '-' }}</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td><strong>JABATAN</strong></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>{{ $user->jabatan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td><strong>UNIT KERJA</strong></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>{{ $user->unit_kerja ?? 'KUA Kecamatan Banjarmasin Utara' }}</td>
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

        <!-- Tabel LKH -->
        <div class="section-title">DAFTAR KEGIATAN HARIAN</div>
        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 4%;">NO</th>
                    <th style="width: 10%;">TANGGAL</th>
                    <th style="width: 15%;">KATEGORI</th>
                    <th style="width: 35%;">URAIAN KEGIATAN</th>
                    <th style="width: 12%;">WAKTU</th>
                    <th style="width: 8%;">DURASI</th>
                    <th style="width: 16%;">HASIL/OUTPUT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lkh as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $item->kategoriKegiatan->nama ?? '-' }}</td>
                    <td>{{ Str::limit($item->uraian_kegiatan, 100) }}</td>
                    <td>{{ substr($item->waktu_mulai, 0, 5) }} - {{ substr($item->waktu_selesai, 0, 5) }}</td>
                    <td style="text-align: center;">{{ number_format($item->durasi, 1) }} j</td>
                    <td>{{ Str::limit($item->hasil_output ?? '-', 60) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Ringkasan -->
        <div class="section-title">RINGKASAN</div>
        <table class="summary-table">
            <thead>
                <tr>
                    <th style="width: 50%;">KETERANGAN</th>
                    <th style="width: 50%;">JUMLAH</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Total LKH</strong></td>
                    <td style="text-align: center;">{{ $totalLkh }} kegiatan</td>
                </tr>
                <tr>
                    <td><strong>Total Durasi</strong></td>
                    <td style="text-align: center;">{{ number_format($totalDurasi, 1) }} jam</td>
                </tr>
            </tbody>
        </table>

        <!-- Kolom Penandatanganan -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-label">Pegawai yang Melaksanakan</div>
                <div class="signature-title" style="visibility: hidden;">Placeholder</div>
                <div class="signature-line">
                    <div class="signature-name">{{ $user->name }}</div>
                    <div class="signature-nip">NIP. {{ $user->nip ?? '-' }}</div>
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
