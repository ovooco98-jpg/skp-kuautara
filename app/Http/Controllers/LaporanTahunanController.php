<?php

namespace App\Http\Controllers;

use App\Models\LaporanTahunan;
use App\Models\LaporanTriwulanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LaporanTahunanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Optimasi: Gunakan withCount untuk menghindari load semua relations
        $query = LaporanTahunan::with(['user:id,name,nip,jabatan'])
            ->withCount('laporanTriwulanan');

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

        // Optimasi: Batasi eager loading - max 2 level depth
        $laporanTriwulanan = LaporanTriwulanan::byUser(Auth::id())
            ->where('tahun', $tahun)
            ->with([
                'laporanBulanan' => function($q) {
                    $q->select('id', 'user_id', 'tahun', 'bulan', 'nama_bulan', 'total_lkh', 'total_durasi')
                      ->withCount('lkh'); // Hanya count, jangan load semua LKH
                }
            ])
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
        // Optimasi CRITICAL: Hindari 4-level deep eager loading (user > triwulanan > bulanan > lkh > kategori)
        // Load hanya data yang diperlukan untuk tampilan
        $laporan = LaporanTahunan::with([
            'user:id,name,nip,jabatan',
            'laporanTriwulanan' => function($q) {
                $q->select('id', 'user_id', 'tahun', 'triwulan', 'nama_triwulan', 'status')
                  ->with(['laporanBulanan' => function($q2) {
                      $q2->select('id', 'user_id', 'tahun', 'bulan', 'nama_bulan', 'total_lkh', 'total_durasi')
                         ->withCount('lkh'); // Count saja untuk performa
                  }]);
            }
        ])->findOrFail($id);

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
        
        // Optimasi: Gunakan aggregate sum daripada nested loops
        $totalLaporanBulanan = $laporanTriwulanan->sum(function($triwulanan) {
            return $triwulanan->laporanBulanan->count();
        });
        
        // Gunakan data yang sudah ter-aggregate di laporan bulanan
        $totalLkh = $laporanTriwulanan->sum(function($triwulanan) {
            return $triwulanan->laporanBulanan->sum('total_lkh');
        });
        
        $totalDurasi = $laporanTriwulanan->sum(function($triwulanan) {
            return $triwulanan->laporanBulanan->sum('total_durasi');
        });
        
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
        // Optimasi: Gunakan data aggregate yang sudah ada
        $totalLkh = $laporanTriwulanan->sum(function($triwulanan) {
            return $triwulanan->laporanBulanan->sum('total_lkh');
        });
        
        $totalDurasi = $laporanTriwulanan->sum(function($triwulanan) {
            return $triwulanan->laporanBulanan->sum('total_durasi');
        });
        
        // Optimasi: Query database langsung untuk kategori stats daripada load semua LKH
        $laporanBulananIds = $laporanTriwulanan->flatMap(function($triwulanan) {
            return $triwulanan->laporanBulanan->pluck('id');
        });
        
        $kategoriStats = \DB::table('lkh')
            ->join('laporan_bulanan_lkh', 'lkh.id', '=', 'laporan_bulanan_lkh.lkh_id')
            ->join('kategori_kegiatan', 'lkh.kategori_kegiatan_id', '=', 'kategori_kegiatan.id')
            ->whereIn('laporan_bulanan_lkh.laporan_bulanan_id', $laporanBulananIds)
            ->select(
                'kategori_kegiatan.nama',
                \DB::raw('COUNT(*) as jumlah'),
                \DB::raw('SUM(CASE 
                    WHEN TIME(lkh.waktu_selesai) > TIME(lkh.waktu_mulai) 
                    THEN TIME_TO_SEC(TIMEDIFF(lkh.waktu_selesai, lkh.waktu_mulai)) / 3600 
                    ELSE 0 END) as durasi')
            )
            ->groupBy('kategori_kegiatan.id', 'kategori_kegiatan.nama')
            ->orderByDesc('jumlah')
            ->limit(5)
            ->get();
        
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
        // Optimasi: Ambil kendala langsung dari collection yang sudah ada
        $kendalaTriwulanan = $laporanTriwulanan->filter(function($laporan) {
            return !empty($laporan->kendala);
        })->map(function($laporan) {
            return [
                'triwulan' => $laporan->nama_triwulan,
                'kendala' => $laporan->kendala
            ];
        });

        // Optimasi: Flatten laporan bulanan tanpa nested loop
        $kendalaBulanan = $laporanTriwulanan->flatMap(function($triwulanan) {
            return $triwulanan->laporanBulanan;
        })->filter(function($bulanan) {
            return !empty($bulanan->kendala);
        })->map(function($bulanan) {
            return [
                'bulan' => $bulanan->nama_bulan,
                'kendala' => $bulanan->kendala
            ];
        });

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
        // Optimasi: Gunakan collection methods
        $rencanaTriwulanan = $laporanTriwulanan->filter(function($laporan) {
            return !empty($laporan->rencana_triwulan_depan);
        })->map(function($laporan) {
            return [
                'triwulan' => $laporan->nama_triwulan,
                'rencana' => $laporan->rencana_triwulan_depan
            ];
        });

        // Optimasi: Query database untuk top kategori daripada load semua LKH
        $laporanBulananIds = $laporanTriwulanan->flatMap(function($triwulanan) {
            return $triwulanan->laporanBulanan->pluck('id');
        });
        
        $kategoriFavorit = collect();
        if ($laporanBulananIds->isNotEmpty()) {
            $kategoriFavorit = \DB::table('lkh')
                ->join('laporan_bulanan_lkh', 'lkh.id', '=', 'laporan_bulanan_lkh.lkh_id')
                ->join('kategori_kegiatan', 'lkh.kategori_kegiatan_id', '=', 'kategori_kegiatan.id')
                ->whereIn('laporan_bulanan_lkh.laporan_bulanan_id', $laporanBulananIds)
                ->select(
                    'kategori_kegiatan.nama',
                    \DB::raw('COUNT(*) as jumlah')
                )
                ->groupBy('kategori_kegiatan.id', 'kategori_kegiatan.nama')
                ->orderByDesc('jumlah')
                ->limit(5)
                ->get();
        }

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
