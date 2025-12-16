<?php

namespace App\Http\Controllers;

use App\Models\Lkh;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    /**
     * Export LKH ke Excel
     */
    public function exportExcel(Request $request)
    {
        $query = Lkh::with(['user', 'kategoriKegiatan']);

        // Jika bukan kepala KUA, hanya export LKH miliknya sendiri
        if (!Auth::user()->isKepalaKua()) {
            $query->where('user_id', Auth::id());
        }

        // Filter berdasarkan tanggal
        if ($request->has('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        // Filter berdasarkan bulan dan tahun
        if ($request->has('bulan') && $request->has('tahun')) {
            $query->whereYear('tanggal', $request->tahun)
                  ->whereMonth('tanggal', $request->bulan);
        } elseif ($request->has('bulan_tahun')) {
            $bulanTahun = explode('-', $request->bulan_tahun);
            if (count($bulanTahun) == 2) {
                $query->whereYear('tanggal', $bulanTahun[0])
                      ->whereMonth('tanggal', $bulanTahun[1]);
            }
        }

        // Filter berdasarkan status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan user (untuk kepala KUA)
        if ($request->has('user_id') && Auth::user()->isKepalaKua()) {
            $query->where('user_id', $request->user_id);
        }

        $lkh = $query->orderBy('tanggal', 'desc')
                     ->orderBy('waktu_mulai', 'asc')
                     ->get();

        // Generate CSV/Excel content
        $filename = 'LKH_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($lkh) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, [
                'No',
                'Tanggal',
                'Nama Pegawai',
                'NIP',
                'Jabatan',
                'Kategori Kegiatan',
                'Uraian Kegiatan',
                'Waktu Mulai',
                'Waktu Selesai',
                'Durasi (Jam)',
                'Hasil/Output',
                'Kendala',
                'Tindak Lanjut',
                'Status',
                'Tanda Tangan Kepala KUA',
                'Nama Kepala KUA',
                'NIP Kepala KUA'
            ]);

            // Ambil data Kepala KUA
            $kepalaKua = User::where('role', 'kepala_kua')->where('is_active', true)->first();
            $namaKepalaKua = $kepalaKua ? $kepalaKua->name : '-';
            $nipKepalaKua = $kepalaKua ? ($kepalaKua->nip ?? '-') : '-';

            // Data
            $no = 1;
            foreach ($lkh as $item) {
                fputcsv($file, [
                    $no++,
                    $item->tanggal->format('d/m/Y'),
                    $item->user->name,
                    $item->user->nip ?? '-',
                    $item->user->jabatan ?? '-',
                    $item->kategoriKegiatan->nama ?? '-',
                    $item->uraian_kegiatan,
                    $item->waktu_mulai,
                    $item->waktu_selesai,
                    number_format($item->durasi, 2),
                    $item->hasil_output ?? '-',
                    $item->kendala ?? '-',
                    $item->tindak_lanjut ?? '-',
                    strtoupper($item->status),
                    '', // Kolom tanda tangan (kosong untuk diisi manual)
                    $namaKepalaKua,
                    $nipKepalaKua
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Laporan Bulanan
     */
    public function exportLaporanBulanan(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $lkh = Lkh::with(['user', 'kategoriKegiatan'])
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('user_id')
            ->orderBy('tanggal')
            ->get();

        $filename = 'Laporan_LKH_' . Carbon::create($tahun, $bulan, 1)->format('F_Y') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($lkh, $bulan, $tahun) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header Laporan
            fputcsv($file, ['LAPORAN KEGIATAN HARIAN (LKH)']);
            fputcsv($file, ['KUA Kecamatan Banjarmasin Utara']);
            fputcsv($file, ['Periode: ' . Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y')]);
            fputcsv($file, []);

            // Group by user
            $grouped = $lkh->groupBy('user_id');
            
            foreach ($grouped as $userId => $userLkh) {
                $user = $userLkh->first()->user;
                fputcsv($file, []);
                fputcsv($file, ['PEGAWAI: ' . $user->name . ' (' . ($user->nip ?? '-') . ')']);
                fputcsv($file, ['Jabatan: ' . ($user->jabatan ?? '-')]);
                fputcsv($file, []);

                // Header tabel
                fputcsv($file, [
                    'No',
                    'Tanggal',
                    'Kategori',
                    'Uraian Kegiatan',
                    'Waktu',
                    'Durasi',
                    'Hasil',
                    'Status'
                ]);

                $no = 1;
                foreach ($userLkh as $item) {
                    fputcsv($file, [
                        $no++,
                        $item->tanggal->format('d/m/Y'),
                        $item->kategoriKegiatan->nama ?? '-',
                        $item->uraian_kegiatan,
                        $item->waktu_mulai . ' - ' . $item->waktu_selesai,
                        number_format($item->durasi, 2) . ' jam',
                        $item->hasil_output ?? '-',
                        strtoupper($item->status)
                    ]);
                }

                fputcsv($file, ['Total LKH: ' . $userLkh->count()]);
                fputcsv($file, []);
            }

            // Summary
            fputcsv($file, []);
            fputcsv($file, ['RINGKASAN']);
            fputcsv($file, ['Total Pegawai: ' . $grouped->count()]);
            fputcsv($file, ['Total LKH: ' . $lkh->count()]);
            
            $totalDurasi = 0;
            foreach ($lkh as $item) {
                try {
                    $totalDurasi += $item->durasi;
                } catch (\Exception $e) {
                    // Skip jika error
                }
            }
            fputcsv($file, ['Total Durasi: ' . number_format($totalDurasi, 2) . ' jam']);
            fputcsv($file, []);

            // Tanda Tangan Kepala KUA
            $kepalaKua = User::where('role', 'kepala_kua')->where('is_active', true)->first();
            if ($kepalaKua) {
                fputcsv($file, []);
                fputcsv($file, ['Mengetahui,']);
                fputcsv($file, ['Kepala KUA Kecamatan Banjarmasin Utara']);
                fputcsv($file, []);
                fputcsv($file, []);
                fputcsv($file, []);
                fputcsv($file, [$kepalaKua->name]);
                fputcsv($file, ['NIP. ' . ($kepalaKua->nip ?? '-')]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Laporan Triwulanan untuk ditandatangani (PDF)
     */
    public function exportLaporanTriwulanan($id)
    {
        $laporan = \App\Models\LaporanTriwulanan::with(['user', 'laporanBulanan.lkh.kategoriKegiatan'])
            ->findOrFail($id);

        // Cek akses
        if (!Auth::user()->isKepalaKua() && $laporan->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses laporan ini');
        }

        // Generate HTML untuk PDF
        $html = view('export.laporan-triwulanan', compact('laporan'))->render();

        // Untuk sementara return HTML, bisa diubah ke PDF menggunakan library seperti DomPDF
        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Export Laporan Tahunan untuk ditandatangani (PDF)
     */
    public function exportLaporanTahunan($id)
    {
        $laporan = \App\Models\LaporanTahunan::with(['user', 'laporanTriwulanan.laporanBulanan.lkh.kategoriKegiatan'])
            ->findOrFail($id);

        // Cek akses
        if (!Auth::user()->isKepalaKua() && $laporan->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses laporan ini');
        }

        // Generate HTML untuk PDF
        $html = view('export.laporan-tahunan', compact('laporan'))->render();

        // Untuk sementara return HTML, bisa diubah ke PDF menggunakan library seperti DomPDF
        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Print LKH (per hari atau per bulan)
     */
    public function printLkh(Request $request, $id = null)
    {
        if ($id) {
            // Print single LKH
            $lkh = Lkh::with(['user', 'kategoriKegiatan'])->findOrFail($id);
            
            // Cek akses
            if (!Auth::user()->isKepalaKua() && $lkh->user_id !== Auth::id()) {
                abort(403, 'Anda tidak berhak mengakses LKH ini');
            }

            return view('export.print-lkh', compact('lkh'));
        } else {
            // Print LKH per bulan
            $bulan = $request->input('bulan', Carbon::now()->month);
            $tahun = $request->input('tahun', Carbon::now()->year);
            $userId = $request->input('user_id', Auth::id());

            // Cek akses
            if (!Auth::user()->isKepalaKua() && $userId !== Auth::id()) {
                abort(403, 'Anda tidak berhak mengakses laporan ini');
            }

            $lkh = Lkh::with(['user', 'kategoriKegiatan'])
                ->where('user_id', $userId)
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->orderBy('tanggal', 'asc')
                ->orderBy('waktu_mulai', 'asc')
                ->get();

            if ($lkh->isEmpty()) {
                abort(404, 'Tidak ada LKH untuk periode ini');
            }

            $user = $lkh->first()->user;
            $totalDurasi = $lkh->sum('durasi');
            $totalLkh = $lkh->count();

            return view('export.print-lkh-bulanan', compact('lkh', 'user', 'bulan', 'tahun', 'totalDurasi', 'totalLkh'));
        }
    }

    /**
     * Print Laporan Bulanan dengan ringkasan auto-generate
     */
    public function printLaporanBulanan($id)
    {
        $laporan = \App\Models\LaporanBulanan::with(['user', 'lkh.kategoriKegiatan', 'skp'])->findOrFail($id);

        // Cek akses
        if (!Auth::user()->isKepalaKua() && $laporan->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses laporan ini');
        }

        // Generate ringkasan otomatis jika belum ada
        $ringkasanOtomatis = $this->generateRingkasanLaporanBulanan($laporan->lkh);
        $pencapaianOtomatis = $this->generatePencapaianLaporanBulanan($laporan->lkh);
        $kendalaOtomatis = $this->generateKendalaLaporanBulanan($laporan->lkh);
        $rencanaOtomatis = $this->generateRencanaLaporanBulanan($laporan->lkh, $laporan->bulan);

        // Gunakan data yang ada atau yang di-generate
        $ringkasan = $laporan->ringkasan_kegiatan ?: $ringkasanOtomatis;
        $pencapaian = $laporan->pencapaian ?: $pencapaianOtomatis;
        $kendala = $laporan->kendala ?: $kendalaOtomatis;
        $rencana = $laporan->rencana_bulan_depan ?: $rencanaOtomatis;

        // Ambil target dari database laporan bulanan (jika sudah diinput)
        // Jika tidak ada, ambil dari SKP yang terkait
        $targetLkh = $laporan->target_lkh;
        $targetDurasi = $laporan->target_durasi;
        
        // Jika target belum diinput di laporan, coba ambil dari SKP
        if (!$targetLkh || !$targetDurasi) {
            $skp = $laporan->skp->first();
            
            if (!$skp) {
                // Coba cari SKP tahunan atau triwulanan yang sesuai
                $skp = \App\Models\Skp::where('user_id', $laporan->user_id)
                    ->where('tahun', $laporan->tahun)
                    ->first();
            }
            
            if ($skp) {
                if (!$targetLkh && $skp->target_kuantitas) {
                    $targetLkh = $skp->target_kuantitas;
                }
                if (!$targetDurasi && $skp->target_waktu) {
                    $targetDurasi = $skp->target_waktu;
                }
            }
        }

        return view('export.print-laporan-bulanan', compact('laporan', 'ringkasan', 'pencapaian', 'kendala', 'rencana', 'targetLkh', 'targetDurasi'));
    }

    /**
     * Print Laporan Triwulanan dengan ringkasan auto-generate
     */
    public function printLaporanTriwulanan($id)
    {
        $laporan = \App\Models\LaporanTriwulanan::with(['user', 'laporanBulanan.lkh.kategoriKegiatan'])->findOrFail($id);

        // Cek akses
        if (!Auth::user()->isKepalaKua() && $laporan->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses laporan ini');
        }

        // Generate ringkasan otomatis jika belum ada
        $ringkasanOtomatis = $this->generateRingkasanLaporanTriwulanan($laporan->laporanBulanan);
        $pencapaianOtomatis = $this->generatePencapaianLaporanTriwulanan($laporan->laporanBulanan);
        $kendalaOtomatis = $this->generateKendalaLaporanTriwulanan($laporan->laporanBulanan);
        $rencanaOtomatis = $this->generateRencanaLaporanTriwulanan($laporan->laporanBulanan, $laporan->triwulan);

        // Gunakan data yang ada atau yang di-generate
        $ringkasan = $laporan->ringkasan_kegiatan ?: $ringkasanOtomatis;
        $pencapaian = $laporan->pencapaian ?: $pencapaianOtomatis;
        $kendala = $laporan->kendala ?: $kendalaOtomatis;
        $rencana = $laporan->rencana_triwulan_depan ?: $rencanaOtomatis;

        return view('export.print-laporan-triwulanan', compact('laporan', 'ringkasan', 'pencapaian', 'kendala', 'rencana'));
    }

    /**
     * Print Laporan Tahunan dengan ringkasan auto-generate
     */
    public function printLaporanTahunan($id)
    {
        $laporan = \App\Models\LaporanTahunan::with(['user', 'laporanTriwulanan.laporanBulanan.lkh.kategoriKegiatan'])->findOrFail($id);

        // Cek akses
        if (!Auth::user()->isKepalaKua() && $laporan->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses laporan ini');
        }

        // Generate ringkasan otomatis jika belum ada
        $ringkasanOtomatis = $this->generateRingkasanLaporanTahunan($laporan->laporanTriwulanan);
        $pencapaianOtomatis = $this->generatePencapaianLaporanTahunan($laporan->laporanTriwulanan);
        $kendalaOtomatis = $this->generateKendalaLaporanTahunan($laporan->laporanTriwulanan);
        $rencanaOtomatis = $this->generateRencanaLaporanTahunan($laporan->laporanTriwulanan, $laporan->tahun);

        // Gunakan data yang ada atau yang di-generate
        $ringkasan = $laporan->ringkasan_kegiatan ?: $ringkasanOtomatis;
        $pencapaian = $laporan->pencapaian ?: $pencapaianOtomatis;
        $kendala = $laporan->kendala ?: $kendalaOtomatis;
        $rencana = $laporan->rencana_tahun_depan ?: $rencanaOtomatis;

        return view('export.print-laporan-tahunan', compact('laporan', 'ringkasan', 'pencapaian', 'kendala', 'rencana'));
    }

    // Helper methods untuk generate ringkasan (copy dari controller lain)
    private function generateRingkasanLaporanBulanan($lkh)
    {
        $totalLkh = $lkh->count();
        $totalDurasi = $lkh->sum('durasi');
        $kategoriCount = $lkh->groupBy('kategori_kegiatan_id')->count();
        
        $ringkasan = "Selama bulan ini telah dilaksanakan {$totalLkh} kegiatan harian dengan total durasi " . number_format($totalDurasi, 1) . " jam. ";
        $ringkasan .= "Kegiatan tersebut mencakup {$kategoriCount} kategori kegiatan yang berbeda. ";
        $ringkasan .= "Semua kegiatan telah dilaksanakan sesuai dengan rencana dan target yang ditetapkan.";

        return $ringkasan;
    }

    private function generatePencapaianLaporanBulanan($lkh)
    {
        $kategoriStats = $lkh->groupBy('kategori_kegiatan_id')
            ->map(function ($items) {
                return [
                    'nama' => $items->first()->kategoriKegiatan->nama ?? 'Lainnya',
                    'jumlah' => $items->count(),
                    'durasi' => $items->sum('durasi')
                ];
            })
            ->sortByDesc('jumlah')
            ->take(5);

        $pencapaian = "Pencapaian utama bulan ini:\n";
        foreach ($kategoriStats as $stat) {
            $pencapaian .= "- {$stat['nama']}: {$stat['jumlah']} kegiatan (" . number_format($stat['durasi'], 1) . " jam)\n";
        }

        return $pencapaian;
    }

    private function generateKendalaLaporanBulanan($lkh)
    {
        $kendalaLkh = $lkh->filter(function($item) {
            return !empty($item->kendala);
        })->map(function($item) {
            return [
                'tanggal' => $item->tanggal->format('d/m/Y'),
                'kendala' => $item->kendala
            ];
        });

        $kendala = "";
        
        if ($kendalaLkh->isNotEmpty()) {
            $kendala .= "Kendala yang dihadapi selama bulan ini:\n\n";
            foreach ($kendalaLkh->take(10) as $item) {
                $kendala .= "• {$item['tanggal']}: {$item['kendala']}\n";
            }
        }

        if (empty($kendala)) {
            $kendala = "Tidak ada kendala yang signifikan selama bulan ini. Semua kegiatan berjalan sesuai rencana.";
        }

        return $kendala;
    }

    private function generateRencanaLaporanBulanan($lkh, $bulan)
    {
        $bulanBerikutnya = $bulan + 1;
        $tahunBerikutnya = Carbon::now()->year;
        if ($bulanBerikutnya > 12) {
            $bulanBerikutnya = 1;
            $tahunBerikutnya++;
        }

        $kategoriFavorit = $lkh->filter(function($item) {
            return $item->kategoriKegiatan !== null;
        })->groupBy('kategori_kegiatan_id')
          ->map(function ($items) {
              return [
                  'nama' => $items->first()->kategoriKegiatan->nama ?? 'Lainnya',
                  'jumlah' => $items->count()
              ];
          })
          ->sortByDesc('jumlah')
          ->take(3);

        $namaBulanBerikutnya = Carbon::create($tahunBerikutnya, $bulanBerikutnya, 1)->locale('id')->translatedFormat('F Y');

        $rencana = "Rencana kegiatan untuk {$namaBulanBerikutnya}:\n\n";
        if ($kategoriFavorit->isNotEmpty()) {
            $rencana .= "Fokus kegiatan yang akan dilanjutkan:\n";
            foreach ($kategoriFavorit as $kategori) {
                $rencana .= "• Meningkatkan kegiatan {$kategori['nama']}\n";
            }
            $rencana .= "\n";
        }
        $rencana .= "• Melanjutkan kegiatan rutin sesuai dengan tugas pokok dan fungsi\n";
        $rencana .= "• Meningkatkan kualitas dan kuantitas pelayanan\n";
        $rencana .= "• Mempertahankan konsistensi dalam pelaksanaan kegiatan";

        return $rencana;
    }

    private function generateRingkasanLaporanTriwulanan($laporanBulanan)
    {
        $totalLaporanBulanan = $laporanBulanan->count();
        $totalLkh = $laporanBulanan->sum(function($laporan) {
            return $laporan->lkh->count();
        });
        $totalDurasi = $laporanBulanan->sum(function($laporan) {
            return $laporan->lkh->sum('durasi');
        });
        
        $bulanList = $laporanBulanan->map(function($laporan) {
            return $laporan->nama_bulan;
        })->implode(', ');
        
        $ringkasan = "Selama triwulan ini telah dilaksanakan {$totalLaporanBulanan} laporan bulanan yang mencakup {$totalLkh} kegiatan harian dengan total durasi " . number_format($totalDurasi, 1) . " jam. ";
        $ringkasan .= "Laporan bulanan tersebut meliputi periode: {$bulanList}. ";
        $ringkasan .= "Semua kegiatan telah dilaksanakan sesuai dengan rencana dan target yang ditetapkan.";
        
        return $ringkasan;
    }

    private function generatePencapaianLaporanTriwulanan($laporanBulanan)
    {
        $allLkh = collect();
        foreach ($laporanBulanan as $laporan) {
            $allLkh = $allLkh->merge($laporan->lkh);
        }
        
        $totalLkh = $allLkh->count();
        $totalDurasi = $allLkh->sum('durasi');
        
        $kategoriStats = $allLkh->filter(function($lkh) {
            return $lkh->kategoriKegiatan !== null;
        })->groupBy('kategori_kegiatan_id')
          ->map(function ($items) {
              return [
                  'nama' => $items->first()->kategoriKegiatan->nama ?? 'Lainnya',
                  'jumlah' => $items->count(),
                  'durasi' => $items->sum('durasi')
              ];
          })
          ->sortByDesc('jumlah')
          ->take(5);
        
        $pencapaian = "Pencapaian utama triwulan ini:\n";
        $pencapaian .= "- Total kegiatan harian: {$totalLkh} LKH\n";
        $pencapaian .= "- Total durasi: " . number_format($totalDurasi, 1) . " jam\n";
        $pencapaian .= "- Laporan bulanan: {$laporanBulanan->count()} laporan\n";
        
        if ($kategoriStats->isNotEmpty()) {
            $pencapaian .= "\nKategori kegiatan utama:\n";
            foreach ($kategoriStats as $stat) {
                $pencapaian .= "- {$stat['nama']}: {$stat['jumlah']} kegiatan (" . number_format($stat['durasi'], 1) . " jam)\n";
            }
        }
        
        return $pencapaian;
    }

    private function generateKendalaLaporanTriwulanan($laporanBulanan)
    {
        $kendalaLkh = collect();
        foreach ($laporanBulanan as $laporan) {
            foreach ($laporan->lkh as $lkh) {
                if (!empty($lkh->kendala)) {
                    $kendalaLkh->push([
                        'tanggal' => $lkh->tanggal->format('d/m/Y'),
                        'kendala' => $lkh->kendala
                    ]);
                }
            }
        }

        $kendalaLaporanBulanan = $laporanBulanan->filter(function($laporan) {
            return !empty($laporan->kendala);
        })->map(function($laporan) {
            return [
                'bulan' => $laporan->nama_bulan,
                'kendala' => $laporan->kendala
            ];
        });

        $kendala = "";
        
        if ($kendalaLaporanBulanan->isNotEmpty()) {
            $kendala .= "Kendala yang dihadapi selama triwulan ini:\n\n";
            foreach ($kendalaLaporanBulanan as $item) {
                $kendala .= "• {$item['bulan']}: {$item['kendala']}\n";
            }
        }

        if ($kendalaLkh->isNotEmpty() && $kendalaLkh->count() <= 10) {
            if (!empty($kendala)) {
                $kendala .= "\n";
            }
            $kendala .= "Kendala spesifik dari kegiatan harian:\n\n";
            foreach ($kendalaLkh->take(5) as $item) {
                $kendala .= "• {$item['tanggal']}: {$item['kendala']}\n";
            }
        }

        if (empty($kendala)) {
            $kendala = "Tidak ada kendala yang signifikan selama triwulan ini. Semua kegiatan berjalan sesuai rencana.";
        }

        return $kendala;
    }

    private function generateRencanaLaporanTriwulanan($laporanBulanan, $triwulan)
    {
        $rencanaLaporanBulanan = $laporanBulanan->filter(function($laporan) {
            return !empty($laporan->rencana_bulan_depan);
        })->map(function($laporan) {
            return [
                'bulan' => $laporan->nama_bulan,
                'rencana' => $laporan->rencana_bulan_depan
            ];
        });

        $triwulanBerikutnya = $triwulan + 1;
        if ($triwulanBerikutnya > 4) {
            $triwulanBerikutnya = 1;
        }

        $allLkh = collect();
        foreach ($laporanBulanan as $laporan) {
            $allLkh = $allLkh->merge($laporan->lkh);
        }

        $kategoriFavorit = $allLkh->filter(function($lkh) {
            return $lkh->kategoriKegiatan !== null;
        })->groupBy('kategori_kegiatan_id')
          ->map(function ($items) {
              return [
                  'nama' => $items->first()->kategoriKegiatan->nama ?? 'Lainnya',
                  'jumlah' => $items->count()
              ];
          })
          ->sortByDesc('jumlah')
          ->take(3);

        $rencana = "Rencana kegiatan untuk Triwulan {$triwulanBerikutnya}:\n\n";

        if ($rencanaLaporanBulanan->isNotEmpty()) {
            $rencana .= "Berdasarkan rencana dari laporan bulanan:\n";
            foreach ($rencanaLaporanBulanan as $item) {
                $rencana .= "• {$item['bulan']}: {$item['rencana']}\n";
            }
            $rencana .= "\n";
        }

        if ($kategoriFavorit->isNotEmpty()) {
            $rencana .= "Fokus kegiatan yang akan dilanjutkan:\n";
            foreach ($kategoriFavorit as $kategori) {
                $rencana .= "• Meningkatkan kegiatan {$kategori['nama']}\n";
            }
        }

        $rencana .= "\n• Melanjutkan kegiatan rutin sesuai dengan tugas pokok dan fungsi\n";
        $rencana .= "• Meningkatkan kualitas dan kuantitas pelayanan\n";
        $rencana .= "• Mempertahankan konsistensi dalam pelaksanaan kegiatan";

        return $rencana;
    }

    private function generateRingkasanLaporanTahunan($laporanTriwulanan)
    {
        $totalLaporanTriwulanan = $laporanTriwulanan->count();
        
        $totalLaporanBulanan = 0;
        $totalLkh = 0;
        $totalDurasi = 0;
        
        foreach ($laporanTriwulanan as $triwulanan) {
            $totalLaporanBulanan += $triwulanan->laporanBulanan->count();
            foreach ($triwulanan->laporanBulanan as $bulanan) {
                $totalLkh += $bulanan->lkh->count();
                $totalDurasi += $bulanan->lkh->sum('durasi');
            }
        }
        
        $triwulanList = $laporanTriwulanan->map(function($laporan) {
            return "Triwulan {$laporan->triwulan}";
        })->implode(', ');
        
        $ringkasan = "Selama tahun ini telah dilaksanakan {$totalLaporanTriwulanan} laporan triwulanan yang mencakup {$totalLaporanBulanan} laporan bulanan dengan {$totalLkh} kegiatan harian dan total durasi " . number_format($totalDurasi, 1) . " jam. ";
        $ringkasan .= "Laporan triwulanan tersebut meliputi: {$triwulanList}. ";
        $ringkasan .= "Semua kegiatan telah dilaksanakan sesuai dengan rencana dan target yang ditetapkan.";
        
        return $ringkasan;
    }

    private function generatePencapaianLaporanTahunan($laporanTriwulanan)
    {
        $allLkh = collect();
        foreach ($laporanTriwulanan as $triwulanan) {
            foreach ($triwulanan->laporanBulanan as $bulanan) {
                $allLkh = $allLkh->merge($bulanan->lkh);
            }
        }
        
        $totalLkh = $allLkh->count();
        $totalDurasi = $allLkh->sum('durasi');
        
        $kategoriStats = $allLkh->filter(function($lkh) {
            return $lkh->kategoriKegiatan !== null;
        })->groupBy('kategori_kegiatan_id')
          ->map(function ($items) {
              return [
                  'nama' => $items->first()->kategoriKegiatan->nama ?? 'Lainnya',
                  'jumlah' => $items->count(),
                  'durasi' => $items->sum('durasi')
              ];
          })
          ->sortByDesc('jumlah')
          ->take(5);
        
        $pencapaian = "Pencapaian utama tahun ini:\n";
        $pencapaian .= "- Total kegiatan harian: {$totalLkh} LKH\n";
        $pencapaian .= "- Total durasi: " . number_format($totalDurasi, 1) . " jam\n";
        $pencapaian .= "- Laporan triwulanan: {$laporanTriwulanan->count()} laporan\n";
        
        if ($kategoriStats->isNotEmpty()) {
            $pencapaian .= "\nKategori kegiatan utama:\n";
            foreach ($kategoriStats as $stat) {
                $pencapaian .= "- {$stat['nama']}: {$stat['jumlah']} kegiatan (" . number_format($stat['durasi'], 1) . " jam)\n";
            }
        }
        
        return $pencapaian;
    }

    private function generateKendalaLaporanTahunan($laporanTriwulanan)
    {
        $kendalaTriwulanan = $laporanTriwulanan->filter(function($laporan) {
            return !empty($laporan->kendala);
        })->map(function($laporan) {
            return [
                'triwulan' => $laporan->nama_triwulan,
                'kendala' => $laporan->kendala
            ];
        });

        $kendalaBulanan = collect();
        foreach ($laporanTriwulanan as $triwulanan) {
            foreach ($triwulanan->laporanBulanan as $bulanan) {
                if (!empty($bulanan->kendala)) {
                    $kendalaBulanan->push([
                        'bulan' => $bulanan->nama_bulan,
                        'kendala' => $bulanan->kendala
                    ]);
                }
            }
        }

        $kendala = "";
        
        if ($kendalaTriwulanan->isNotEmpty()) {
            $kendala .= "Kendala yang dihadapi selama tahun ini:\n\n";
            foreach ($kendalaTriwulanan as $item) {
                $kendala .= "• {$item['triwulan']}: {$item['kendala']}\n";
            }
        }

        if ($kendalaBulanan->isNotEmpty() && $kendalaBulanan->count() <= 10) {
            if (!empty($kendala)) {
                $kendala .= "\n";
            }
            $kendala .= "Kendala dari laporan bulanan:\n\n";
            foreach ($kendalaBulanan->take(5) as $item) {
                $kendala .= "• {$item['bulan']}: {$item['kendala']}\n";
            }
        }

        if (empty($kendala)) {
            $kendala = "Tidak ada kendala yang signifikan selama tahun ini. Semua kegiatan berjalan sesuai rencana.";
        }

        return $kendala;
    }

    private function generateRencanaLaporanTahunan($laporanTriwulanan, $tahun)
    {
        $rencanaTriwulanan = $laporanTriwulanan->filter(function($laporan) {
            return !empty($laporan->rencana_triwulan_depan);
        })->map(function($laporan) {
            return [
                'triwulan' => $laporan->nama_triwulan,
                'rencana' => $laporan->rencana_triwulan_depan
            ];
        });

        $allLkh = collect();
        foreach ($laporanTriwulanan as $triwulanan) {
            foreach ($triwulanan->laporanBulanan as $bulanan) {
                $allLkh = $allLkh->merge($bulanan->lkh);
            }
        }

        $kategoriFavorit = $allLkh->filter(function($lkh) {
            return $lkh->kategoriKegiatan !== null;
        })->groupBy('kategori_kegiatan_id')
          ->map(function ($items) {
              return [
                  'nama' => $items->first()->kategoriKegiatan->nama ?? 'Lainnya',
                  'jumlah' => $items->count()
              ];
          })
          ->sortByDesc('jumlah')
          ->take(5);

        $tahunDepan = $tahun + 1;
        $rencana = "Rencana kegiatan untuk Tahun {$tahunDepan}:\n\n";

        if ($rencanaTriwulanan->isNotEmpty()) {
            $rencana .= "Berdasarkan rencana dari laporan triwulanan:\n";
            foreach ($rencanaTriwulanan as $item) {
                $rencana .= "• {$item['triwulan']}: {$item['rencana']}\n";
            }
            $rencana .= "\n";
        }

        if ($kategoriFavorit->isNotEmpty()) {
            $rencana .= "Fokus kegiatan yang akan dilanjutkan dan ditingkatkan:\n";
            foreach ($kategoriFavorit as $kategori) {
                $rencana .= "• Meningkatkan kegiatan {$kategori['nama']}\n";
            }
            $rencana .= "\n";
        }

        $rencana .= "Rencana strategis:\n";
        $rencana .= "• Melanjutkan kegiatan rutin sesuai dengan tugas pokok dan fungsi\n";
        $rencana .= "• Meningkatkan kualitas dan kuantitas pelayanan\n";
        $rencana .= "• Mempertahankan konsistensi dalam pelaksanaan kegiatan\n";
        $rencana .= "• Mengoptimalkan penggunaan sumber daya yang tersedia\n";
        $rencana .= "• Meningkatkan koordinasi dan kolaborasi dengan pihak terkait";

        return $rencana;
    }
}
