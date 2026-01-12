<?php

namespace App\Http\Controllers;

use App\Models\LaporanTriwulanan;
use App\Models\LaporanBulanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LaporanTriwulananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Optimasi: Gunakan withCount untuk menghindari load semua relations
        $query = LaporanTriwulanan::with(['user:id,name,nip,jabatan'])
            ->withCount('laporanBulanan');

        // Jika bukan kepala KUA, hanya lihat laporan sendiri
        if (!Auth::user()->isKepalaKua()) {
            $query->where('user_id', Auth::id());
        }

        // Filter berdasarkan tahun
        $tahun = $request->input('tahun', Carbon::now()->year);
        $query->where('tahun', $tahun);

        // Filter berdasarkan triwulan
        if ($request->has('triwulan')) {
            $query->where('triwulan', $request->triwulan);
        }

        // Filter berdasarkan user (untuk Kepala KUA)
        if ($request->has('user_id') && Auth::user()->isKepalaKua()) {
            $query->where('user_id', $request->user_id);
        }

        $laporan = $query->orderBy('tahun', 'desc')
                         ->orderBy('triwulan', 'desc')
                         ->paginate(20);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $laporan
            ]);
        }

        return view('laporan-triwulanan.index', compact('laporan', 'tahun'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $triwulan = $request->input('triwulan', $this->getCurrentTriwulan());
        $tahun = $request->input('tahun', Carbon::now()->year);

        // Cek apakah sudah ada laporan untuk triwulan ini
        $existing = LaporanTriwulanan::byUser(Auth::id())
            ->byTriwulanTahun($triwulan, $tahun)
            ->first();

        if ($existing) {
            return redirect()->route('laporan-triwulanan.show', $existing->id)
                ->with('info', 'Laporan triwulanan untuk periode ini sudah ada');
        }

        // Ambil semua laporan bulanan untuk triwulan ini
        // Jika Kepala KUA, ambil laporan bulanan dari semua staff + laporan bulanan sendiri
        // Jika bukan Kepala KUA, hanya ambil laporan bulanan sendiri
        $bulanMulai = ($triwulan - 1) * 3 + 1;
        $bulanSelesai = $triwulan * 3;
        
        if (Auth::user()->isKepalaKua()) {
            // Optimasi: Batasi eager load, gunakan aggregate dari laporan_bulanan table
            $laporanBulanan = LaporanBulanan::where('tahun', $tahun)
                ->whereBetween('bulan', [$bulanMulai, $bulanSelesai])
                ->whereHas('user', function($q) {
                    $q->where('role', '!=', 'kepala_kua')
                      ->orWhere('id', Auth::id());
                })
                ->with([
                    'user:id,name,jabatan',
                    // Hindari eager load semua lkh, gunakan count dan aggregate saja
                ])
                ->select('id', 'user_id', 'tahun', 'bulan', 'nama_bulan', 'total_lkh', 'total_durasi')
                ->withCount('lkh')
                ->orderBy('user_id', 'asc')
                ->orderBy('bulan', 'asc')
                ->get();
        } else {
            $laporanBulanan = LaporanBulanan::byUser(Auth::id())
                ->where('tahun', $tahun)
                ->whereBetween('bulan', [$bulanMulai, $bulanSelesai])
                ->select('id', 'user_id', 'tahun', 'bulan', 'nama_bulan', 'total_lkh', 'total_durasi')
                ->withCount('lkh')
                ->orderBy('bulan', 'asc')
                ->get();
        }

        if ($laporanBulanan->isEmpty()) {
            return redirect()->route('laporan-triwulanan.index')
                ->with('error', 'Tidak ada laporan bulanan untuk triwulan ini');
        }

        // Generate ringkasan dan pencapaian otomatis dari LKH/LKB
        $ringkasanOtomatis = $this->generateRingkasan($laporanBulanan);
        $pencapaianOtomatis = $this->generatePencapaian($laporanBulanan);
        $kendalaOtomatis = $this->generateKendala($laporanBulanan);
        $rencanaOtomatis = $this->generateRencana($laporanBulanan, $triwulan);

        return view('laporan-triwulanan.create', compact('laporanBulanan', 'triwulan', 'tahun', 'ringkasanOtomatis', 'pencapaianOtomatis', 'kendalaOtomatis', 'rencanaOtomatis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'triwulan' => 'required|integer|between:1,4',
            'tahun' => 'required|integer|min:2020',
            'ringkasan_kegiatan' => 'nullable|string',
            'pencapaian' => 'nullable|string',
            'kendala' => 'nullable|string',
            'rencana_triwulan_depan' => 'nullable|string',
            'laporan_bulanan_ids' => 'required|array',
            'laporan_bulanan_ids.*' => 'exists:laporan_bulanan,id',
        ]);

        // Cek apakah sudah ada laporan untuk triwulan ini
        $existing = LaporanTriwulanan::byUser(Auth::id())
            ->byTriwulanTahun($validated['triwulan'], $validated['tahun'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan triwulanan untuk periode ini sudah ada'
            ], 422);
        }

        // Validasi laporan bulanan
        $laporanBulananIds = $validated['laporan_bulanan_ids'];
        
        if (Auth::user()->isKepalaKua()) {
            // Kepala KUA bisa memilih laporan bulanan dari semua staff + laporan bulanan sendiri
            $validLaporan = LaporanBulanan::whereIn('id', $laporanBulananIds)
                ->whereHas('user', function($q) {
                    $q->where('role', '!=', 'kepala_kua')
                      ->orWhere('id', Auth::id());
                })
                ->count();
        } else {
            // Staff hanya bisa memilih laporan bulanan miliknya sendiri
            $validLaporan = LaporanBulanan::whereIn('id', $laporanBulananIds)
                ->where('user_id', Auth::id())
                ->count();
        }

        if ($validLaporan !== count($laporanBulananIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Beberapa laporan bulanan tidak valid atau bukan milik Anda'
            ], 422);
        }

        // Buat laporan triwulanan
        $laporan = LaporanTriwulanan::create([
            'user_id' => Auth::id(),
            'triwulan' => $validated['triwulan'],
            'tahun' => $validated['tahun'],
            'ringkasan_kegiatan' => $validated['ringkasan_kegiatan'] ?? null,
            'pencapaian' => $validated['pencapaian'] ?? null,
            'kendala' => $validated['kendala'] ?? null,
            'rencana_triwulan_depan' => $validated['rencana_triwulan_depan'] ?? null,
            'status' => 'draft',
        ]);

        // Attach laporan bulanan
        $laporan->laporanBulanan()->attach($validated['laporan_bulanan_ids']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Laporan triwulanan berhasil dibuat',
                'data' => $laporan->load(['user', 'laporanBulanan'])
            ], 201);
        }

        return redirect()->route('laporan-triwulanan.show', $laporan->id)
            ->with('success', 'Laporan triwulanan berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Optimasi: Batasi eager loading depth (max 2 level)
        $laporan = LaporanTriwulanan::with([
            'user:id,name,nip,jabatan',
            'laporanBulanan' => function($q) {
                $q->select('id', 'user_id', 'tahun', 'bulan', 'nama_bulan', 'total_lkh', 'total_durasi')
                  ->withCount('lkh'); // Hanya count untuk performa
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

        return view('laporan-triwulanan.show', compact('laporan'));
    }

    /**
     * Simpan link bukti fisik dari Drive (staff upload ke drive, link diinput di form)
     */
    public function uploadBuktiFisik(Request $request, string $id)
    {
        $laporan = LaporanTriwulanan::findOrFail($id);

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

        return redirect()->route('laporan-triwulanan.show', $laporan->id)
            ->with('success', 'Link bukti fisik berhasil disimpan');
    }

    /**
     * Redirect ke link bukti fisik di Drive
     */
    public function downloadBuktiFisik(string $id)
    {
        $laporan = LaporanTriwulanan::findOrFail($id);

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
     * Get current triwulan based on current month
     */
    private function getCurrentTriwulan(): int
    {
        $month = Carbon::now()->month;
        return (int) ceil($month / 3);
    }

    /**
     * Generate ringkasan otomatis dari laporan bulanan
     */
    private function generateRingkasan($laporanBulanan)
    {
        $totalLaporanBulanan = $laporanBulanan->count();
        
        // Optimasi: Gunakan aggregate data yang sudah ada di laporan_bulanan table
        $totalLkh = $laporanBulanan->sum('total_lkh');
        $totalDurasi = $laporanBulanan->sum('total_durasi');
        
        $bulanList = $laporanBulanan->map(function($laporan) {
            return $laporan->nama_bulan;
        })->implode(', ');
        
        $ringkasan = "Selama triwulan ini telah dilaksanakan {$totalLaporanBulanan} laporan bulanan yang mencakup {$totalLkh} kegiatan harian dengan total durasi " . number_format($totalDurasi, 1) . " jam. ";
        $ringkasan .= "Laporan bulanan tersebut meliputi periode: {$bulanList}. ";
        $ringkasan .= "Semua kegiatan telah dilaksanakan sesuai dengan rencana dan target yang ditetapkan.";
        
        return $ringkasan;
    }

    /**
     * Generate pencapaian otomatis dari laporan bulanan
     */
    private function generatePencapaian($laporanBulanan)
    {
        // Optimasi: Gunakan aggregate data yang sudah ada
        $totalLkh = $laporanBulanan->sum('total_lkh');
        $totalDurasi = $laporanBulanan->sum('total_durasi');
        
        // Optimasi: Query database langsung untuk kategori stats daripada load semua LKH
        $laporanBulananIds = $laporanBulanan->pluck('id');
        
        $kategoriStats = collect();
        if ($laporanBulananIds->isNotEmpty()) {
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
        }
        
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

    /**
     * Generate kendala otomatis dari laporan bulanan dan LKH
     */
    private function generateKendala($laporanBulanan)
    {
        // Optimasi: Ambil kendala dari laporan bulanan saja (sudah teragregasi)
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

        if (empty($kendala)) {
            $kendala = "Tidak ada kendala yang signifikan selama triwulan ini. Semua kegiatan berjalan sesuai rencana.";
        }

        return $kendala;
    }

    /**
     * Generate rencana triwulan depan otomatis
     */
    private function generateRencana($laporanBulanan, $triwulan)
    {
        // Optimasi: Ambil rencana dari laporan bulanan (sudah teragregasi)
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

        // Optimasi: Query database untuk top kategori
        $laporanBulananIds = $laporanBulanan->pluck('id');
        
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
                ->limit(3)
                ->get();
        }

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
}
