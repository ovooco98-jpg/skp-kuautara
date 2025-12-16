<?php

namespace App\Http\Controllers;

use App\Models\LaporanTahunan;
use App\Models\LaporanTriwulanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LaporanTahunanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LaporanTahunan::with(['user', 'laporanTriwulanan']);

        // Jika bukan kepala KUA, hanya lihat laporan sendiri
        if (!Auth::user()->isKepalaKua()) {
            $query->where('user_id', Auth::id());
        }

        // Filter berdasarkan tahun
        $tahun = $request->input('tahun', Carbon::now()->year);
        $query->where('tahun', $tahun);

        // Filter berdasarkan user (untuk Kepala KUA)
        if ($request->has('user_id') && Auth::user()->isKepalaKua()) {
            $query->where('user_id', $request->user_id);
        }

        $laporan = $query->orderBy('tahun', 'desc')
                         ->paginate(20);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $laporan
            ]);
        }

        return view('laporan-tahunan.index', compact('laporan', 'tahun'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $tahun = $request->input('tahun', Carbon::now()->year);

        // Cek apakah sudah ada laporan untuk tahun ini
        $existing = LaporanTahunan::byUser(Auth::id())
            ->byTahun($tahun)
            ->first();

        if ($existing) {
            return redirect()->route('laporan-tahunan.show', $existing->id)
                ->with('info', 'Laporan tahunan untuk tahun ini sudah ada');
        }

        // Ambil semua laporan triwulanan untuk tahun ini
        $laporanTriwulanan = LaporanTriwulanan::byUser(Auth::id())
            ->where('tahun', $tahun)
            ->with('laporanBulanan.lkh.kategoriKegiatan')
            ->orderBy('triwulan', 'asc')
            ->get();

        if ($laporanTriwulanan->isEmpty()) {
            return redirect()->route('laporan-tahunan.index')
                ->with('error', 'Tidak ada laporan triwulanan untuk tahun ini');
        }

        // Generate ringkasan dan pencapaian otomatis dari laporan triwulanan
        $ringkasanOtomatis = $this->generateRingkasan($laporanTriwulanan);
        $pencapaianOtomatis = $this->generatePencapaian($laporanTriwulanan);
        $kendalaOtomatis = $this->generateKendala($laporanTriwulanan);
        $rencanaOtomatis = $this->generateRencana($laporanTriwulanan, $tahun);

        return view('laporan-tahunan.create', compact('laporanTriwulanan', 'tahun', 'ringkasanOtomatis', 'pencapaianOtomatis', 'kendalaOtomatis', 'rencanaOtomatis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|min:2020',
            'ringkasan_kegiatan' => 'nullable|string',
            'pencapaian' => 'nullable|string',
            'kendala' => 'nullable|string',
            'rencana_tahun_depan' => 'nullable|string',
            'laporan_triwulanan_ids' => 'required|array',
            'laporan_triwulanan_ids.*' => 'exists:laporan_triwulanan,id',
        ]);

        // Cek apakah sudah ada laporan untuk tahun ini
        $existing = LaporanTahunan::byUser(Auth::id())
            ->byTahun($validated['tahun'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tahunan untuk tahun ini sudah ada'
            ], 422);
        }

        // Validasi bahwa semua laporan triwulanan adalah milik user
        $laporanCount = LaporanTriwulanan::whereIn('id', $validated['laporan_triwulanan_ids'])
            ->where('user_id', Auth::id())
            ->count();

        if ($laporanCount !== count($validated['laporan_triwulanan_ids'])) {
            return response()->json([
                'success' => false,
                'message' => 'Beberapa laporan triwulanan tidak valid atau bukan milik Anda'
            ], 422);
        }

        // Buat laporan tahunan
        $laporan = LaporanTahunan::create([
            'user_id' => Auth::id(),
            'tahun' => $validated['tahun'],
            'ringkasan_kegiatan' => $validated['ringkasan_kegiatan'] ?? null,
            'pencapaian' => $validated['pencapaian'] ?? null,
            'kendala' => $validated['kendala'] ?? null,
            'rencana_tahun_depan' => $validated['rencana_tahun_depan'] ?? null,
            'status' => 'draft',
        ]);

        // Attach laporan triwulanan
        $laporan->laporanTriwulanan()->attach($validated['laporan_triwulanan_ids']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Laporan tahunan berhasil dibuat',
                'data' => $laporan->load(['user', 'laporanTriwulanan'])
            ], 201);
        }

        return redirect()->route('laporan-tahunan.show', $laporan->id)
            ->with('success', 'Laporan tahunan berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $laporan = LaporanTahunan::with(['user', 'laporanTriwulanan.laporanBulanan.lkh.kategoriKegiatan'])
            ->findOrFail($id);

        // Cek akses
        if (!Auth::user()->isKepalaKua() && $laporan->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses laporan ini');
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $laporan
            ]);
        }

        return view('laporan-tahunan.show', compact('laporan'));
    }

    /**
     * Simpan link bukti fisik dari Drive (staff upload ke drive, link diinput di form)
     */
    public function uploadBuktiFisik(Request $request, string $id)
    {
        $laporan = LaporanTahunan::findOrFail($id);

        // Cek akses
        if ($laporan->user_id !== Auth::id() && !Auth::user()->isKepalaKua()) {
            abort(403, 'Anda tidak berhak menyimpan link bukti fisik untuk laporan ini');
        }

        $validated = $request->validate([
            'file_bukti_fisik' => 'required|url', // Link drive, bukan file upload
        ]);

        $laporan->update([
            'file_bukti_fisik' => $validated['file_bukti_fisik'],
            'status' => 'ditandatangani',
            'ditandatangani_pada' => now(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Link bukti fisik berhasil disimpan',
                'data' => $laporan->load(['user'])
            ]);
        }

        return redirect()->route('laporan-tahunan.show', $laporan->id)
            ->with('success', 'Link bukti fisik berhasil disimpan');
    }

    /**
     * Redirect ke link bukti fisik di Drive
     */
    public function downloadBuktiFisik(string $id)
    {
        $laporan = LaporanTahunan::findOrFail($id);

        // Cek akses
        if (!Auth::user()->isKepalaKua() && $laporan->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses bukti fisik ini');
        }

        if (!$laporan->file_bukti_fisik) {
            abort(404, 'Link bukti fisik tidak ditemukan');
        }

        // Jika file_bukti_fisik adalah URL (link drive), redirect ke sana
        if (filter_var($laporan->file_bukti_fisik, FILTER_VALIDATE_URL)) {
            return redirect($laporan->file_bukti_fisik);
        }

        // Fallback: jika masih file path lama, download
        if (Storage::disk('public')->exists($laporan->file_bukti_fisik)) {
            return Storage::disk('public')->download($laporan->file_bukti_fisik);
        }

        abort(404, 'Bukti fisik tidak ditemukan');
    }

    /**
     * Generate ringkasan otomatis dari laporan triwulanan
     */
    private function generateRingkasan($laporanTriwulanan)
    {
        $totalLaporanTriwulanan = $laporanTriwulanan->count();
        
        // Hitung total dari semua laporan bulanan di semua triwulanan
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

    /**
     * Generate pencapaian otomatis dari laporan triwulanan
     */
    private function generatePencapaian($laporanTriwulanan)
    {
        // Kumpulkan semua LKH dari semua laporan triwulanan
        $allLkh = collect();
        foreach ($laporanTriwulanan as $triwulanan) {
            foreach ($triwulanan->laporanBulanan as $bulanan) {
                $allLkh = $allLkh->merge($bulanan->lkh);
            }
        }
        
        $totalLkh = $allLkh->count();
        $totalDurasi = $allLkh->sum('durasi');
        
        // Ambil kategori kegiatan dari semua LKH
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

    /**
     * Generate kendala otomatis dari laporan triwulanan
     */
    private function generateKendala($laporanTriwulanan)
    {
        // Kumpulkan kendala dari laporan triwulanan
        $kendalaTriwulanan = $laporanTriwulanan->filter(function($laporan) {
            return !empty($laporan->kendala);
        })->map(function($laporan) {
            return [
                'triwulan' => $laporan->nama_triwulan,
                'kendala' => $laporan->kendala
            ];
        });

        // Kumpulkan kendala dari laporan bulanan
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

        // Kumpulkan kendala dari LKH
        $kendalaLkh = collect();
        foreach ($laporanTriwulanan as $triwulanan) {
            foreach ($triwulanan->laporanBulanan as $bulanan) {
                foreach ($bulanan->lkh as $lkh) {
                    if (!empty($lkh->kendala)) {
                        $kendalaLkh->push([
                            'tanggal' => $lkh->tanggal->format('d/m/Y'),
                            'kendala' => $lkh->kendala
                        ]);
                    }
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

    /**
     * Generate rencana tahun depan otomatis
     */
    private function generateRencana($laporanTriwulanan, $tahun)
    {
        // Ambil rencana dari laporan triwulanan
        $rencanaTriwulanan = $laporanTriwulanan->filter(function($laporan) {
            return !empty($laporan->rencana_triwulan_depan);
        })->map(function($laporan) {
            return [
                'triwulan' => $laporan->nama_triwulan,
                'rencana' => $laporan->rencana_triwulan_depan
            ];
        });

        // Kumpulkan semua LKH untuk analisis kategori
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
