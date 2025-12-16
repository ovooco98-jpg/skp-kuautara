<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LKH - {{ $lkh->tanggal->format('d F Y') }}</title>
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
        .header .tanggal {
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
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            border: 2px solid #000;
        }
        .main-table thead th {
            background-color: #e5e5e5;
            font-weight: bold;
            text-align: center;
            padding: 12px 15px;
            border: 1px solid #000;
            font-size: 11pt;
        }
        .main-table tbody td {
            padding: 12px 15px;
            border: 1px solid #000;
            font-size: 10.5pt;
        }
        .main-table tbody td:first-child {
            font-weight: 600;
            background-color: #f9f9f9;
        }
        .content-section {
            margin-bottom: 25px;
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
        .content-text {
            padding: 15px;
            border: 2px solid #000;
            text-align: justify;
            line-height: 1.8;
            font-size: 10.5pt;
            white-space: pre-wrap;
            background-color: #fafafa;
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
            <div class="tanggal">
                TANGGAL: {{ strtoupper($lkh->tanggal->locale('id')->translatedFormat('d F Y')) }}
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
                        <td>{{ $lkh->user->name }}</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><strong>NIP</strong></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>{{ $lkh->user->nip ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td><strong>PANGKAT / GOL RUANG</strong></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>{{ isset($lkh->user->pangkat_gol) ? $lkh->user->pangkat_gol : '-' }}</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td><strong>JABATAN</strong></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>{{ $lkh->user->jabatan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td><strong>UNIT KERJA</strong></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>{{ $lkh->user->unit_kerja ?? 'KUA Kecamatan Banjarmasin Utara' }}</td>
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

        <!-- Informasi Kegiatan -->
        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 25%;">INFORMASI</th>
                    <th style="width: 75%;">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Tanggal</strong></td>
                    <td>{{ $lkh->tanggal->format('d F Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Waktu</strong></td>
                    <td>{{ $lkh->waktu_mulai }} - {{ $lkh->waktu_selesai }} ({{ number_format($lkh->durasi, 1) }} jam)</td>
                </tr>
                @if($lkh->kategoriKegiatan)
                <tr>
                    <td><strong>Kategori Kegiatan</strong></td>
                    <td>{{ $lkh->kategoriKegiatan->nama }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <!-- Uraian Kegiatan -->
        <div class="content-section">
            <div class="section-title">URAIAN KEGIATAN</div>
            <div class="content-text">{{ $lkh->uraian_kegiatan }}</div>
        </div>

        @if($lkh->hasil_output)
        <div class="content-section">
            <div class="section-title">HASIL/OUTPUT</div>
            <div class="content-text">{{ $lkh->hasil_output }}</div>
        </div>
        @endif

        @if($lkh->kendala)
        <div class="content-section">
            <div class="section-title">KENDALA</div>
            <div class="content-text">{{ $lkh->kendala }}</div>
        </div>
        @endif

        @if($lkh->tindak_lanjut)
        <div class="content-section">
            <div class="section-title">TINDAK LANJUT</div>
            <div class="content-text">{{ $lkh->tindak_lanjut }}</div>
        </div>
        @endif

        <!-- Kolom Penandatanganan -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-label">Pegawai yang Melaksanakan</div>
                <div class="signature-title" style="visibility: hidden;">Placeholder</div>
                <div class="signature-line">
                    <div class="signature-name">{{ $lkh->user->name }}</div>
                    <div class="signature-nip">NIP. {{ $lkh->user->nip ?? '-' }}</div>
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
