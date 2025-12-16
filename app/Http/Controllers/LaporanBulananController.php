<?php

namespace App\Http\Controllers;

use App\Models\LaporanBulanan;
use App\Models\Lkh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LaporanBulananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LaporanBulanan::with(['user', 'lkh']);

        // Jika bukan kepala KUA, hanya lihat laporan sendiri
        if (!Auth::user()->isKepalaKua()) {
            $query->where('user_id', Auth::id());
        }

        // Filter berdasarkan tahun
        if ($request->has('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        // Filter berdasarkan bulan
        if ($request->has('bulan')) {
            $query->where('bulan', $request->bulan);
        }

        // Filter berdasarkan user (untuk Kepala KUA)
        if ($request->has('user_id') && Auth::user()->isKepalaKua()) {
            $query->where('user_id', $request->user_id);
        }

        $laporan = $query->orderBy('tahun', 'desc')
                         ->orderBy('bulan', 'desc')
                         ->paginate(20);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $laporan
            ]);
        }

        return view('laporan-bulanan.index', compact('laporan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $bulan = $request->input('bulan', Carbon::now()->month);
        $tahun = $request->input('tahun', Carbon::now()->year);

        // Cek apakah sudah ada laporan untuk bulan ini
        $existing = LaporanBulanan::byUser(Auth::id())
            ->byBulanTahun($bulan, $tahun)
            ->first();

        if ($existing) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan bulanan untuk periode ini sudah ada',
                    'data' => $existing
                ], 422);
            }
            return redirect()->route('laporan-bulanan.show', $existing->id)
                ->with('info', 'Laporan bulanan untuk periode ini sudah ada');
        }

        // Ambil semua LKH untuk bulan ini
        // Jika Kepala KUA, ambil LKH dari semua staff + LKH sendiri
        // Jika bukan Kepala KUA, hanya ambil LKH sendiri
        if (Auth::user()->isKepalaKua()) {
            // Ambil LKH dari semua staff (bukan kepala_kua) + LKH sendiri
            $lkh = Lkh::byBulanTahun($bulan, $tahun)
                ->whereHas('user', function($q) {
                    $q->where('role', '!=', 'kepala_kua')
                      ->orWhere('id', Auth::id());
                })
                ->with(['kategoriKegiatan', 'user:id,name,jabatan'])
                ->orderBy('user_id', 'asc')
                ->orderBy('tanggal', 'asc')
                ->get();
        } else {
            $lkh = Lkh::byUser(Auth::id())
                ->byBulanTahun($bulan, $tahun)
                ->with('kategoriKegiatan')
                ->orderBy('tanggal', 'asc')
                ->get();
        }

        if ($lkh->isEmpty()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada LKH untuk periode ini'
                ], 422);
            }
            return redirect()->route('laporan-bulanan.index')
                ->with('error', 'Tidak ada LKH untuk periode ini');
        }

        // Generate ringkasan, pencapaian, kendala, dan rencana otomatis dari LKH
        $ringkasanOtomatis = $this->generateRingkasan($lkh);
        $pencapaianOtomatis = $this->generatePencapaian($lkh);
        $kendalaOtomatis = $this->generateKendala($lkh);
        $rencanaOtomatis = $this->generateRencana($lkh, $bulan);

        // Ambil target dari SKP jika ada
        $targetLkhOtomatis = null;
        $targetDurasiOtomatis = null;
        $skp = \App\Models\Skp::where('user_id', Auth::id())
            ->where('tahun', $tahun)
            ->first();
        
        if ($skp) {
            if ($skp->target_kuantitas) {
                $targetLkhOtomatis = $skp->target_kuantitas;
            }
            if ($skp->target_waktu) {
                $targetDurasiOtomatis = $skp->target_waktu;
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'lkh' => $lkh,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'ringkasan_otomatis' => $ringkasanOtomatis,
                    'pencapaian_otomatis' => $pencapaianOtomatis,
                    'kendala_otomatis' => $kendalaOtomatis,
                    'rencana_otomatis' => $rencanaOtomatis,
                    'target_lkh_otomatis' => $targetLkhOtomatis,
                    'target_durasi_otomatis' => $targetDurasiOtomatis,
                ]
            ]);
        }

        return view('laporan-bulanan.create', compact('lkh', 'bulan', 'tahun', 'ringkasanOtomatis', 'pencapaianOtomatis', 'kendalaOtomatis', 'rencanaOtomatis', 'targetLkhOtomatis', 'targetDurasiOtomatis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020',
            'ringkasan_kegiatan' => 'nullable|string',
            'pencapaian' => 'nullable|string',
            'kendala' => 'nullable|string',
            'rencana_bulan_depan' => 'nullable|string',
            'target_lkh' => 'nullable|string',
            'target_durasi' => 'nullable|string',
            'lkh_ids' => 'required|array',
            'lkh_ids.*' => 'exists:lkh,id',
        ]);

        // Cek apakah sudah ada laporan untuk bulan ini
        $existing = LaporanBulanan::byUser(Auth::id())
            ->byBulanTahun($validated['bulan'], $validated['tahun'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan bulanan untuk periode ini sudah ada'
            ], 422);
        }

        // Validasi LKH
        $lkhIds = $validated['lkh_ids'];
        
        if (Auth::user()->isKepalaKua()) {
            // Kepala KUA bisa memilih LKH dari semua staff + LKH sendiri
            $validLkh = Lkh::whereIn('id', $lkhIds)
                ->whereHas('user', function($q) {
                    $q->where('role', '!=', 'kepala_kua')
                      ->orWhere('id', Auth::id());
                })
                ->count();
        } else {
            // Staff hanya bisa memilih LKH miliknya sendiri
            $validLkh = Lkh::whereIn('id', $lkhIds)
                ->where('user_id', Auth::id())
                ->count();
        }

        if ($validLkh !== count($lkhIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Beberapa LKH tidak valid atau bukan milik Anda'
            ], 422);
        }

        // Ambil target dari SKP jika tidak diinput manual
        $targetLkh = $validated['target_lkh'] ?? null;
        $targetDurasi = $validated['target_durasi'] ?? null;
        
        // Jika target tidak diinput, coba ambil dari SKP yang terkait
        if (!$targetLkh || !$targetDurasi) {
            $skp = \App\Models\Skp::where('user_id', Auth::id())
                ->where('tahun', $validated['tahun'])
                ->first();
            
            if ($skp) {
                if (!$targetLkh && $skp->target_kuantitas) {
                    $targetLkh = $skp->target_kuantitas;
                }
                if (!$targetDurasi && $skp->target_waktu) {
                    $targetDurasi = $skp->target_waktu;
                }
            }
        }

        // Generate ringkasan otomatis jika tidak diisi manual
        $ringkasanFinal = $validated['ringkasan_kegiatan'] ?? null;
        $pencapaianFinal = $validated['pencapaian'] ?? null;
        $kendalaFinal = $validated['kendala'] ?? null;
        $rencanaFinal = $validated['rencana_bulan_depan'] ?? null;
        
        // Load LKH sekali untuk semua generate functions (jika diperlukan)
        $lkhSelected = null;
        if (empty($ringkasanFinal) || empty($pencapaianFinal) || empty($kendalaFinal) || empty($rencanaFinal)) {
            $lkhSelected = Lkh::whereIn('id', $lkhIds)
                ->with(['kategoriKegiatan', 'user'])
                ->get();
        }
        
        // Jika ringkasan tidak diisi, generate otomatis dari LKH yang dipilih
        if (empty($ringkasanFinal) && $lkhSelected) {
            $ringkasanFinal = $this->generateRingkasan($lkhSelected);
        }
        
        // Jika pencapaian tidak diisi, generate otomatis
        if (empty($pencapaianFinal) && $lkhSelected) {
            $pencapaianFinal = $this->generatePencapaian($lkhSelected);
        }
        
        // Jika kendala tidak diisi, generate otomatis
        if (empty($kendalaFinal) && $lkhSelected) {
            $kendalaFinal = $this->generateKendala($lkhSelected);
        }
        
        // Jika rencana tidak diisi, generate otomatis
        if (empty($rencanaFinal) && $lkhSelected) {
            $rencanaFinal = $this->generateRencana($lkhSelected, $validated['bulan']);
        }

        // Buat laporan bulanan
        $laporan = LaporanBulanan::create([
            'user_id' => Auth::id(),
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
            'ringkasan_kegiatan' => $ringkasanFinal,
            'pencapaian' => $pencapaianFinal,
            'kendala' => $kendalaFinal,
            'rencana_bulan_depan' => $rencanaFinal,
            'target_lkh' => $targetLkh,
            'target_durasi' => $targetDurasi,
            'status' => 'draft',
        ]);

        // Attach LKH ke laporan
        $laporan->lkh()->attach($lkhIds);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Laporan bulanan berhasil dibuat',
                'data' => $laporan->load(['user', 'lkh'])
            ], 201);
        }

        return redirect()->route('laporan-bulanan.show', $laporan->id)
            ->with('success', 'Laporan bulanan berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $laporan = LaporanBulanan::with(['user', 'lkh.kategoriKegiatan'])
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

        return view('laporan-bulanan.show', compact('laporan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $laporan = LaporanBulanan::with(['lkh'])->findOrFail($id);

        // Cek akses
        if ($laporan->user_id !== Auth::id() && !Auth::user()->isKepalaKua()) {
            abort(403, 'Anda tidak berhak mengedit laporan ini');
        }

        // Ambil semua LKH untuk bulan ini (untuk update)
        // Jika Kepala KUA, ambil LKH dari semua staff + LKH sendiri
        // Jika bukan Kepala KUA, hanya ambil LKH sendiri
        if (Auth::user()->isKepalaKua()) {
            $lkh = Lkh::byBulanTahun($laporan->bulan, $laporan->tahun)
                ->whereHas('user', function($q) {
                    $q->where('role', '!=', 'kepala_kua')
                      ->orWhere('id', Auth::id());
                })
                ->with(['kategoriKegiatan', 'user:id,name,jabatan'])
                ->orderBy('user_id', 'asc')
                ->orderBy('tanggal', 'asc')
                ->get();
        } else {
            $lkh = Lkh::byUser($laporan->user_id)
                ->byBulanTahun($laporan->bulan, $laporan->tahun)
                ->with('kategoriKegiatan')
                ->orderBy('tanggal', 'asc')
                ->get();
        }

        // Generate ringkasan, pencapaian, kendala, dan rencana otomatis dari LKH
        $ringkasanOtomatis = $this->generateRingkasan($lkh, $laporan->bulan, $laporan->tahun);
        $pencapaianOtomatis = $this->generatePencapaian($lkh);
        $kendalaOtomatis = $this->generateKendala($lkh);
        $rencanaOtomatis = $this->generateRencana($lkh, $laporan->bulan);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'laporan' => [
                        'id' => $laporan->id,
                        'bulan' => $laporan->bulan,
                        'tahun' => $laporan->tahun,
                        'nama_bulan' => $laporan->nama_bulan,
                        'ringkasan_kegiatan' => $laporan->ringkasan_kegiatan,
                        'pencapaian' => $laporan->pencapaian,
                        'kendala' => $laporan->kendala,
                        'rencana_bulan_depan' => $laporan->rencana_bulan_depan,
                        'status' => $laporan->status,
                        'total_lkh' => $laporan->total_lkh,
                        'total_durasi' => $laporan->total_durasi,
                        'lkh' => $laporan->lkh->map(function($item) {
                            return ['id' => $item->id];
                        })
                    ],
                    'lkh' => $lkh->map(function($item) {
                        return [
                            'id' => $item->id,
                            'tanggal' => $item->tanggal->format('Y-m-d'),
                            'uraian_kegiatan' => $item->uraian_kegiatan,
                            'durasi' => $item->durasi
                        ];
                    })
                ]
            ]);
        }

        return view('laporan-bulanan.edit', compact('laporan', 'lkh', 'ringkasanOtomatis', 'pencapaianOtomatis', 'kendalaOtomatis', 'rencanaOtomatis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $laporan = LaporanBulanan::findOrFail($id);

        // Cek akses
        if ($laporan->user_id !== Auth::id() && !Auth::user()->isKepalaKua()) {
            abort(403, 'Anda tidak berhak mengupdate laporan ini');
        }

        $validated = $request->validate([
            'ringkasan_kegiatan' => 'nullable|string',
            'pencapaian' => 'nullable|string',
            'kendala' => 'nullable|string',
            'rencana_bulan_depan' => 'nullable|string',
            'target_lkh' => 'nullable|string',
            'target_durasi' => 'nullable|string',
            'status' => 'nullable|in:draft,selesai',
            'lkh_ids' => 'nullable|array',
            'lkh_ids.*' => 'exists:lkh,id',
        ]);

        // Update laporan dengan auto-generate jika field kosong
        $ringkasanFinal = $validated['ringkasan_kegiatan'] ?? $laporan->ringkasan_kegiatan;
        $pencapaianFinal = $validated['pencapaian'] ?? $laporan->pencapaian;
        $kendalaFinal = $validated['kendala'] ?? $laporan->kendala;
        $rencanaFinal = $validated['rencana_bulan_depan'] ?? $laporan->rencana_bulan_depan;
        
        // Load LKH sekali untuk semua generate functions (jika diperlukan)
        $lkhSelected = null;
        $needRegenerate = empty($ringkasanFinal) || empty($pencapaianFinal) || empty($kendalaFinal) || empty($rencanaFinal);
        
        if ($needRegenerate) {
            if (isset($validated['lkh_ids'])) {
                // Jika LKH diupdate, gunakan LKH baru
                $lkhSelected = Lkh::whereIn('id', $validated['lkh_ids'])
                    ->with(['kategoriKegiatan', 'user'])
                    ->get();
            } else {
                // Jika LKH tidak diupdate, gunakan LKH yang sudah ada
                $lkhSelected = $laporan->lkh()->with(['kategoriKegiatan', 'user'])->get();
            }
            
            // Regenerate jika field kosong atau null
            if (empty($ringkasanFinal)) {
                $ringkasanFinal = $this->generateRingkasan($lkhSelected, $laporan->bulan, $laporan->tahun);
            }
            if (empty($pencapaianFinal)) {
                $pencapaianFinal = $this->generatePencapaian($lkhSelected);
            }
            if (empty($kendalaFinal)) {
                $kendalaFinal = $this->generateKendala($lkhSelected);
            }
            if (empty($rencanaFinal)) {
                $rencanaFinal = $this->generateRencana($lkhSelected, $laporan->bulan);
            }
        }

        // Update laporan
        $laporan->update([
            'ringkasan_kegiatan' => $ringkasanFinal,
            'pencapaian' => $pencapaianFinal,
            'kendala' => $kendalaFinal,
            'rencana_bulan_depan' => $rencanaFinal,
            'target_lkh' => $validated['target_lkh'] ?? $laporan->target_lkh,
            'target_durasi' => $validated['target_durasi'] ?? $laporan->target_durasi,
            'status' => $validated['status'] ?? $laporan->status,
        ]);

        // Update LKH jika ada
        if (isset($validated['lkh_ids'])) {
            $lkhIds = $validated['lkh_ids'];
            
            // Validasi LKH
            if (Auth::user()->isKepalaKua()) {
                // Kepala KUA bisa memilih LKH dari semua staff + LKH sendiri
                $validLkh = Lkh::whereIn('id', $lkhIds)
                    ->whereHas('user', function($q) {
                        $q->where('role', '!=', 'kepala_kua')
                          ->orWhere('id', Auth::id());
                    })
                    ->count();
            } else {
                // Staff hanya bisa memilih LKH miliknya sendiri
                $validLkh = Lkh::whereIn('id', $lkhIds)
                    ->where('user_id', $laporan->user_id)
                    ->count();
            }

            if ($validLkh !== count($lkhIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Beberapa LKH tidak valid'
                ], 422);
            }

            $laporan->lkh()->sync($lkhIds);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Laporan bulanan berhasil diperbarui',
                'data' => $laporan->load(['user', 'lkh'])
            ]);
        }

        return redirect()->route('laporan-bulanan.show', $laporan->id)
            ->with('success', 'Laporan bulanan berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $laporan = LaporanBulanan::findOrFail($id);

        // Cek akses
        if ($laporan->user_id !== Auth::id() && !Auth::user()->isKepalaKua()) {
            abort(403, 'Anda tidak berhak menghapus laporan ini');
        }

        $laporan->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Laporan bulanan berhasil dihapus'
            ]);
        }

        return redirect()->route('laporan-bulanan.index')
            ->with('success', 'Laporan bulanan berhasil dihapus');
    }

    /**
     * Generate otomatis ringkasan, pencapaian, kendala, dan rencana dari LKH yang dipilih
     */
    public function generateOtomatis(Request $request)
    {
        $validated = $request->validate([
            'lkh_ids' => 'required|array',
            'lkh_ids.*' => 'exists:lkh,id',
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020',
        ]);

        $lkhIds = $validated['lkh_ids'];
        $bulan = $validated['bulan'];
        $tahun = $validated['tahun'];

        // Ambil LKH yang dipilih
        if (Auth::user()->isKepalaKua()) {
            // Kepala KUA bisa memilih LKH dari semua staff + LKH sendiri
            $lkh = Lkh::whereIn('id', $lkhIds)
                ->whereHas('user', function($q) {
                    $q->where('role', '!=', 'kepala_kua')
                      ->orWhere('id', Auth::id());
                })
                ->with(['kategoriKegiatan', 'user:id,name,jabatan'])
                ->get();
        } else {
            // Staff hanya bisa memilih LKH miliknya sendiri
            $lkh = Lkh::whereIn('id', $lkhIds)
                ->where('user_id', Auth::id())
                ->with(['kategoriKegiatan', 'user'])
                ->get();
        }

        if ($lkh->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada LKH yang valid untuk di-generate'
            ], 422);
        }

        // Generate ringkasan, pencapaian, kendala, dan rencana
        $ringkasan = $this->generateRingkasan($lkh, $bulan, $tahun);
        $pencapaian = $this->generatePencapaian($lkh);
        $kendala = $this->generateKendala($lkh);
        $rencana = $this->generateRencana($lkh, $bulan);

        return response()->json([
            'success' => true,
            'data' => [
                'ringkasan_kegiatan' => $ringkasan,
                'pencapaian' => $pencapaian,
                'kendala' => $kendala,
                'rencana_bulan_depan' => $rencana,
            ]
        ]);
    }

    /**
     * Generate laporan bulanan otomatis dari LKH
     */
    public function generate(Request $request)
    {
        $bulan = $request->input('bulan', Carbon::now()->month);
        $tahun = $request->input('tahun', Carbon::now()->year);

        // Cek apakah sudah ada laporan
        $existing = LaporanBulanan::byUser(Auth::id())
            ->byBulanTahun($bulan, $tahun)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan bulanan untuk periode ini sudah ada',
                'data' => $existing
            ], 422);
        }

        // Ambil semua LKH untuk bulan ini
        $lkh = Lkh::byUser(Auth::id())
            ->byBulanTahun($bulan, $tahun)
            ->with('kategoriKegiatan')
            ->orderBy('tanggal', 'asc')
            ->get();

        if ($lkh->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada LKH untuk periode ini'
            ], 422);
        }

        // Generate ringkasan otomatis
        $ringkasan = $this->generateRingkasan($lkh, $bulan, $tahun);
        $pencapaian = $this->generatePencapaian($lkh);
        $kendala = $this->generateKendala($lkh);
        $rencana = $this->generateRencana($lkh, $bulan);

        // Buat laporan
        $laporan = LaporanBulanan::create([
            'user_id' => Auth::id(),
            'bulan' => $bulan,
            'tahun' => $tahun,
            'ringkasan_kegiatan' => $ringkasan,
            'pencapaian' => $pencapaian,
            'kendala' => $kendala,
            'rencana_bulan_depan' => $rencana,
            'status' => 'draft',
        ]);

        // Attach semua LKH
        $laporan->lkh()->attach($lkh->pluck('id'));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Laporan bulanan berhasil di-generate',
                'data' => $laporan->load(['user', 'lkh'])
            ], 201);
        }

        return redirect()->route('laporan-bulanan.show', $laporan->id)
            ->with('success', 'Laporan bulanan berhasil di-generate');
    }

    /**
     * Generate ringkasan otomatis dari LKH
     * Untuk Kepala KUA: ringkasan mencakup semua staff
     * Untuk Staff: ringkasan hanya untuk kegiatan sendiri
     */
    private function generateRingkasan($lkh, $bulan = null, $tahun = null)
    {
        $isKepalaKua = Auth::user()->isKepalaKua();
        $totalLkh = $lkh->count();
        $totalDurasi = $lkh->sum('durasi');
        $kategoriCount = $lkh->groupBy('kategori_kegiatan_id')->count();
        
        // Ambil bulan dan tahun dari LKH pertama jika tidak diberikan
        if (!$bulan || !$tahun) {
            $firstLkh = $lkh->first();
            if ($firstLkh) {
                $bulan = $firstLkh->tanggal->month;
                $tahun = $firstLkh->tanggal->year;
            } else {
                $bulan = Carbon::now()->month;
                $tahun = Carbon::now()->year;
            }
        }
        
        if ($isKepalaKua) {
            // Ringkasan khusus untuk Kepala KUA - Fokus pada Kepemimpinan dan Pencapaian Organisasi
            $staffCount = $lkh->groupBy('user_id')->count();
            $avgLkhPerStaff = $staffCount > 0 ? round($totalLkh / $staffCount, 1) : 0;
            $avgDurasiPerStaff = $staffCount > 0 ? round($totalDurasi / $staffCount, 1) : 0;
            
            $namaBulan = Carbon::create($tahun, $bulan, 1)
                ->locale('id')->translatedFormat('F Y');
            
            $ringkasan = "LAPORAN BULANAN KEPALA KUA KECAMATAN BANJARMASIN UTARA\n";
            $ringkasan .= "Periode: {$namaBulan}\n\n";
            
            $ringkasan .= "I. RINGKASAN EKSEKUTIF\n\n";
            $ringkasan .= "Pada periode {$namaBulan}, di bawah kepemimpinan dan arahan yang telah ditetapkan, seluruh unit kerja KUA Kecamatan Banjarmasin Utara telah berhasil melaksanakan konsolidasi kegiatan harian yang mencakup {$totalLkh} kegiatan dengan total durasi " . number_format($totalDurasi, 1) . " jam kerja efektif. ";
            $ringkasan .= "Melalui koordinasi dan pengawasan yang sistematis, seluruh personil yang terdiri dari {$staffCount} orang telah menunjukkan komitmen tinggi dalam melaksanakan tugas pokok dan fungsi sesuai dengan visi dan misi organisasi. ";
            $ringkasan .= "Kegiatan tersebut mencakup {$kategoriCount} kategori kegiatan yang berbeda, menunjukkan diversifikasi dan optimalisasi pelayanan kepada masyarakat.\n\n";
            
            $ringkasan .= "II. PENCAPAIAN ORGANISASI\n\n";
            $ringkasan .= "Melalui pendekatan kepemimpinan yang transformatif dan manajemen yang efektif, telah tercapai beberapa pencapaian signifikan:\n\n";
            $ringkasan .= "1. Produktivitas Organisasi\n";
            $ringkasan .= "   - Rata-rata produktivitas per personil mencapai {$avgLkhPerStaff} kegiatan dengan durasi rata-rata " . number_format($avgDurasiPerStaff, 1) . " jam per personil\n";
            $ringkasan .= "   - Distribusi kegiatan yang merata menunjukkan efektivitas sistem koordinasi dan alokasi sumber daya\n\n";
            $ringkasan .= "2. Kualitas Pelayanan\n";
            $ringkasan .= "   - Cakupan {$kategoriCount} kategori kegiatan menunjukkan kemampuan organisasi dalam memberikan pelayanan yang komprehensif\n";
            $ringkasan .= "   - Konsistensi pelaksanaan kegiatan mencerminkan komitmen terhadap standar pelayanan prima\n\n";
            $ringkasan .= "3. Sinergi dan Koordinasi\n";
            $ringkasan .= "   - Koordinasi antar unit kerja berjalan dengan baik melalui mekanisme yang telah ditetapkan\n";
            $ringkasan .= "   - Sinergi dalam pelaksanaan tugas menunjukkan efektivitas sistem manajemen yang diterapkan\n\n";
            
            $ringkasan .= "III. KESIMPULAN\n\n";
            $ringkasan .= "Berdasarkan evaluasi terhadap seluruh kegiatan yang telah dilaksanakan, dapat disimpulkan bahwa pada periode {$namaBulan}, KUA Kecamatan Banjarmasin Utara telah berhasil mencapai target dan sasaran yang ditetapkan. ";
            $ringkasan .= "Melalui kepemimpinan yang visioner dan manajemen yang efektif, seluruh personil telah berhasil diarahkan untuk melaksanakan tugas sesuai dengan visi dan misi organisasi. ";
            $ringkasan .= "Koordinasi dan pengawasan yang dilakukan secara berkelanjutan telah menghasilkan tingkat partisipasi dan produktivitas yang optimal. ";
            $ringkasan .= "Pencapaian ini merupakan wujud nyata dari komitmen bersama dalam mewujudkan pelayanan prima kepada masyarakat dan pengembangan organisasi yang berkelanjutan.";
        } else {
            // Ringkasan untuk staff biasa
            $ringkasan = "Selama bulan ini telah dilaksanakan {$totalLkh} kegiatan harian dengan total durasi {$totalDurasi} jam. ";
            $ringkasan .= "Kegiatan tersebut mencakup {$kategoriCount} kategori kegiatan yang berbeda. ";
            $ringkasan .= "Semua kegiatan telah dilaksanakan sesuai dengan rencana dan target yang ditetapkan.";
        }

        return $ringkasan;
    }

    /**
     * Generate pencapaian otomatis dari LKH
     * Untuk Kepala KUA: pencapaian mencakup semua staff dengan breakdown per kategori dan per staff
     * Untuk Staff: pencapaian hanya untuk kegiatan sendiri
     */
    private function generatePencapaian($lkh)
    {
        $isKepalaKua = Auth::user()->isKepalaKua();
        
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

        if ($isKepalaKua) {
            // Pencapaian khusus untuk Kepala KUA - Fokus pada Kepemimpinan dan Pencapaian Strategis
            $staffCount = $lkh->groupBy('user_id')->count();
            $avgLkhPerStaff = $staffCount > 0 
                ? round($lkh->count() / $staffCount, 1) 
                : 0;
            $avgDurasiPerStaff = $staffCount > 0 
                ? round($lkh->sum('durasi') / $staffCount, 1) 
                : 0;
            
            $pencapaian = "PENCAPAIAN UTAMA BULAN INI\n\n";
            
            $pencapaian .= "I. PENCAPAIAN STRATEGIS ORGANISASI\n\n";
            $pencapaian .= "Melalui kepemimpinan yang visioner dan manajemen yang efektif, telah dicapai beberapa pencapaian strategis yang signifikan:\n\n";
            $pencapaian .= "1. Optimalisasi Pelayanan Berdasarkan Kategori Kegiatan\n\n";
            $counter = 1;
            foreach ($kategoriStats as $stat) {
                $pencapaian .= "   {$counter}. {$stat['nama']}\n";
                $pencapaian .= "      - Telah dilaksanakan {$stat['jumlah']} kegiatan dengan total durasi " . number_format($stat['durasi'], 1) . " jam\n";
                $pencapaian .= "      - Mencerminkan fokus organisasi dalam memberikan pelayanan yang berkualitas dan terarah\n\n";
                $counter++;
            }
            
            $pencapaian .= "2. Peningkatan Produktivitas Organisasi\n\n";
            $pencapaian .= "   - Rata-rata produktivitas per personil mencapai {$avgLkhPerStaff} kegiatan dengan durasi rata-rata " . number_format($avgDurasiPerStaff, 1) . " jam\n";
            $pencapaian .= "   - Total personil aktif yang terlibat: {$staffCount} personil\n";
            $pencapaian .= "   - Distribusi beban kerja yang merata menunjukkan efektivitas sistem koordinasi dan alokasi sumber daya\n\n";
            
            $pencapaian .= "II. PENCAPAIAN DALAM KONTEKS VISI DAN MISI\n\n";
            $pencapaian .= "Pencapaian bulan ini merupakan wujud nyata dari implementasi visi dan misi organisasi:\n\n";
            $pencapaian .= "1. Pelayanan Prima kepada Masyarakat\n";
            $pencapaian .= "   - Berbagai kategori kegiatan yang dilaksanakan menunjukkan komitmen organisasi dalam memberikan pelayanan yang komprehensif\n";
            $pencapaian .= "   - Konsistensi dan kualitas pelaksanaan kegiatan mencerminkan standar pelayanan yang tinggi\n\n";
            $pencapaian .= "2. Pengembangan Sumber Daya Manusia\n";
            $pencapaian .= "   - Tingkat partisipasi yang tinggi menunjukkan keberhasilan dalam mengarahkan dan memotivasi personil\n";
            $pencapaian .= "   - Produktivitas yang konsisten mencerminkan efektivitas sistem pengembangan kapasitas yang diterapkan\n\n";
            $pencapaian .= "3. Penguatan Sistem Manajemen\n";
            $pencapaian .= "   - Koordinasi yang efektif antar unit kerja menunjukkan keberhasilan dalam membangun sinergi organisasi\n";
            $pencapaian .= "   - Distribusi tugas yang merata dan optimal menunjukkan efektivitas sistem manajemen yang diterapkan\n\n";
            
            $pencapaian .= "III. KESIMPULAN PENCAPAIAN\n\n";
            $pencapaian .= "Pencapaian bulan ini menunjukkan bahwa melalui kepemimpinan yang transformatif dan manajemen yang efektif, seluruh personil telah berhasil diarahkan untuk melaksanakan tugas sesuai dengan visi dan misi organisasi. ";
            $pencapaian .= "Komitmen tinggi, produktivitas yang optimal, dan koordinasi yang efektif menjadi indikator keberhasilan dalam mewujudkan tujuan strategis organisasi. ";
            $pencapaian .= "Pencapaian ini menjadi landasan yang kuat untuk pengembangan organisasi ke depan dan peningkatan kualitas pelayanan kepada masyarakat.";
        } else {
            // Pencapaian untuk staff biasa
            $pencapaian = "Pencapaian utama bulan ini:\n";
            foreach ($kategoriStats as $stat) {
                $pencapaian .= "- {$stat['nama']}: {$stat['jumlah']} kegiatan ({$stat['durasi']} jam)\n";
            }
        }

        return $pencapaian;
    }

    /**
     * Generate kendala otomatis dari LKH
     * Untuk Kepala KUA: kendala mencakup semua staff dengan identifikasi per staff
     * Untuk Staff: kendala hanya untuk kegiatan sendiri
     */
    private function generateKendala($lkh)
    {
        $isKepalaKua = Auth::user()->isKepalaKua();
        
        // Kumpulkan kendala dari LKH yang ada kendala
        $kendalaLkh = $lkh->filter(function($item) {
            return !empty($item->kendala);
        })->map(function($item) {
            return [
                'tanggal' => $item->tanggal->format('d/m/Y'),
                'kendala' => $item->kendala,
                'user_name' => $item->user->name ?? 'Staff',
                'user_jabatan' => $item->user->jabatan ?? '-',
            ];
        });

        $kendala = "";
        
        if ($kendalaLkh->isNotEmpty()) {
            if ($isKepalaKua) {
                $kendala .= "KENDALA YANG DIHADAPI DAN PENANGANANNYA\n\n";
                
                $totalKendala = $kendalaLkh->count();
                $kendalaPerStaff = $kendalaLkh->groupBy('user_name');
                $staffCount = $kendalaPerStaff->count();
                
                $kendala .= "I. IDENTIFIKASI KENDALA\n\n";
                $kendala .= "Selama periode ini, telah teridentifikasi beberapa kendala dalam pelaksanaan kegiatan. ";
                $kendala .= "Total kendala yang dilaporkan sebanyak {$totalKendala} kendala dari {$staffCount} personil. ";
                $kendala .= "Kendala-kendala tersebut telah didokumentasikan dan dianalisis untuk mendapatkan solusi yang tepat.\n\n";
                
                $kendala .= "II. PENANGANAN KENDALA\n\n";
                $kendala .= "Melalui mekanisme koordinasi dan pengawasan yang telah ditetapkan, semua kendala yang dilaporkan telah ditindaklanjuti dengan langkah-langkah strategis:\n\n";
                $kendala .= "1. Koordinasi dan Komunikasi\n";
                $kendala .= "   - Dilakukan koordinasi intensif dengan seluruh unit kerja untuk mengidentifikasi akar permasalahan\n";
                $kendala .= "   - Komunikasi yang efektif telah dibangun untuk memastikan semua kendala teridentifikasi dan tertangani\n\n";
                $kendala .= "2. Solusi Strategis\n";
                $kendala .= "   - Setiap kendala telah dianalisis dan diberikan solusi yang tepat sesuai dengan karakteristik permasalahan\n";
                $kendala .= "   - Pendekatan sistematis telah diterapkan untuk memastikan kendala tidak berulang di masa depan\n\n";
                $kendala .= "3. Evaluasi dan Tindak Lanjut\n";
                $kendala .= "   - Dilakukan evaluasi berkala terhadap efektivitas solusi yang diterapkan\n";
                $kendala .= "   - Tindak lanjut dilakukan untuk memastikan semua kendala telah teratasi dengan baik\n\n";
                
                $kendala .= "III. KESIMPULAN\n\n";
                $kendala .= "Melalui kepemimpinan yang responsif dan manajemen yang proaktif, semua kendala yang muncul telah berhasil ditangani dengan baik. ";
                $kendala .= "Sistem koordinasi dan komunikasi yang efektif telah memungkinkan organisasi untuk merespons setiap tantangan dengan cepat dan tepat. ";
                $kendala .= "Pengalaman dalam menangani kendala ini menjadi pembelajaran berharga untuk pengembangan sistem manajemen yang lebih baik di masa depan.";
            } else {
                $kendala .= "Kendala yang dihadapi selama bulan ini:\n\n";
                
                foreach ($kendalaLkh->take(10) as $item) {
                    $kendala .= "• {$item['tanggal']}: {$item['kendala']}\n";
                }
            }
        }

        if (empty($kendala)) {
            if ($isKepalaKua) {
                $kendala = "TIDAK ADA KENDALA YANG SIGNIFIKAN\n\n";
                $kendala .= "I. EVALUASI KONDISI ORGANISASI\n\n";
                $kendala .= "Selama periode ini, melalui sistem koordinasi dan pengawasan yang efektif, tidak terdapat kendala yang signifikan dalam pelaksanaan seluruh kegiatan. ";
                $kendala .= "Kondisi ini mencerminkan keberhasilan dalam membangun sistem manajemen yang solid dan efektif.\n\n";
                $kendala .= "II. FAKTOR KEBERHASILAN\n\n";
                $kendala .= "Tidak adanya kendala signifikan dapat diidentifikasi dari beberapa faktor:\n\n";
                $kendala .= "1. Sistem Koordinasi yang Efektif\n";
                $kendala .= "   - Koordinasi yang baik antar unit kerja telah memungkinkan identifikasi dan penanganan potensi masalah sejak dini\n";
                $kendala .= "   - Komunikasi yang terbuka dan transparan telah memfasilitasi resolusi cepat terhadap setiap isu yang muncul\n\n";
                $kendala .= "2. Kepemimpinan yang Proaktif\n";
                $kendala .= "   - Pendekatan kepemimpinan yang proaktif telah memungkinkan antisipasi terhadap berbagai tantangan\n";
                $kendala .= "   - Pengawasan yang berkelanjutan telah memastikan semua kegiatan berjalan sesuai dengan rencana\n\n";
                $kendala .= "3. Komitmen dan Disiplin Personil\n";
                $kendala .= "   - Komitmen tinggi seluruh personil dalam melaksanakan tugas sesuai dengan standar yang ditetapkan\n";
                $kendala .= "   - Disiplin dalam pelaksanaan kegiatan telah meminimalkan potensi terjadinya kendala\n\n";
                $kendala .= "III. KESIMPULAN\n\n";
                $kendala .= "Tidak adanya kendala signifikan merupakan indikator positif terhadap efektivitas sistem manajemen dan kepemimpinan yang diterapkan. ";
                $kendala .= "Kondisi ini menunjukkan bahwa seluruh personil telah berhasil diarahkan untuk melaksanakan tugas dengan baik sesuai dengan visi dan misi organisasi. ";
                $kendala .= "Koordinasi dan komunikasi yang efektif telah memungkinkan organisasi untuk beroperasi dengan lancar dan optimal.";
            } else {
                $kendala = "Tidak ada kendala yang signifikan selama bulan ini. Semua kegiatan berjalan sesuai rencana.";
            }
        }

        return $kendala;
    }

    /**
     * Generate rencana bulan depan otomatis
     * Untuk Kepala KUA: rencana mencakup koordinasi dan pengawasan staff
     * Untuk Staff: rencana fokus pada kegiatan sendiri
     */
    private function generateRencana($lkh, $bulan)
    {
        $isKepalaKua = Auth::user()->isKepalaKua();
        
        // Tentukan bulan berikutnya
        $bulanBerikutnya = $bulan + 1;
        $tahunBerikutnya = Carbon::now()->year;
        if ($bulanBerikutnya > 12) {
            $bulanBerikutnya = 1;
            $tahunBerikutnya++;
        }

        // Kumpulkan kategori kegiatan yang sering dilakukan
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

        if ($isKepalaKua) {
            // Rencana khusus untuk Kepala KUA - Fokus pada Kepemimpinan dan Pengembangan Strategis
            $rencana = "RENCANA KEGIATAN UNTUK {$namaBulanBerikutnya}\n\n";
            
            $rencana .= "I. STRATEGI KEPEMIMPINAN DAN PENGEMBANGAN ORGANISASI\n\n";
            $rencana .= "Sebagai bentuk komitmen terhadap visi dan misi organisasi, akan dilaksanakan strategi kepemimpinan dan pengembangan organisasi sebagai berikut:\n\n";
            $rencana .= "1. Penguatan Sistem Koordinasi dan Komunikasi\n";
            $rencana .= "   - Melanjutkan dan meningkatkan mekanisme koordinasi dengan seluruh personil untuk memastikan sinergi yang optimal\n";
            $rencana .= "   - Mengoptimalkan komunikasi dua arah untuk memfasilitasi pertukaran informasi dan umpan balik yang konstruktif\n";
            $rencana .= "   - Membangun sistem komunikasi yang lebih efektif untuk mendukung pengambilan keputusan yang tepat dan cepat\n\n";
            $rencana .= "2. Pengawasan dan Evaluasi Berkelanjutan\n";
            $rencana .= "   - Meningkatkan intensitas pengawasan terhadap pelaksanaan tugas pokok dan fungsi setiap unit kerja\n";
            $rencana .= "   - Melaksanakan evaluasi kinerja secara berkala dan sistematis untuk mengidentifikasi area perbaikan\n";
            $rencana .= "   - Mengembangkan sistem monitoring yang lebih komprehensif untuk memastikan pencapaian target organisasi\n\n";
            $rencana .= "3. Pengembangan Kapasitas dan Kompetensi\n";
            $rencana .= "   - Meningkatkan kapasitas dan kompetensi personil melalui program pengembangan sumber daya manusia yang terstruktur\n";
            $rencana .= "   - Mengoptimalkan potensi setiap personil melalui pemberdayaan dan delegasi yang tepat\n";
            $rencana .= "   - Membangun budaya organisasi yang mendukung pembelajaran dan pengembangan berkelanjutan\n\n";
            
            if ($kategoriFavorit->isNotEmpty()) {
                $rencana .= "II. FOKUS KEGIATAN STRATEGIS\n\n";
                $rencana .= "Berdasarkan evaluasi terhadap kegiatan yang telah dilaksanakan, akan dilakukan penguatan pada kategori kegiatan strategis berikut:\n\n";
                $counter = 1;
                foreach ($kategoriFavorit as $kategori) {
                    $rencana .= "{$counter}. {$kategori['nama']}\n";
                    $rencana .= "   - Meningkatkan intensitas dan kualitas kegiatan untuk mencapai hasil yang lebih optimal\n";
                    $rencana .= "   - Mengembangkan inovasi dalam pelaksanaan kegiatan untuk meningkatkan efektivitas dan efisiensi\n\n";
                    $counter++;
                }
            }
            
            $rencana .= "III. PENINGKATAN KUALITAS PELAYANAN KEPADA MASYARAKAT\n\n";
            $rencana .= "Sebagai wujud komitmen terhadap pelayanan prima, akan dilakukan upaya peningkatan kualitas pelayanan:\n\n";
            $rencana .= "1. Standarisasi dan Peningkatan Kualitas\n";
            $rencana .= "   - Meningkatkan kualitas dan kuantitas pelayanan kepada masyarakat dengan mengacu pada standar pelayanan prima\n";
            $rencana .= "   - Mempertahankan konsistensi dan profesionalitas dalam pelaksanaan seluruh kegiatan\n";
            $rencana .= "   - Mengembangkan sistem penjaminan mutu untuk memastikan kualitas pelayanan yang optimal\n\n";
            $rencana .= "2. Optimalisasi Koordinasi dan Kolaborasi\n";
            $rencana .= "   - Mengoptimalkan koordinasi dan kolaborasi antar unit kerja untuk mencapai sinergi yang lebih baik\n";
            $rencana .= "   - Membangun kemitraan strategis untuk mendukung pelaksanaan kegiatan yang lebih efektif\n";
            $rencana .= "   - Mengembangkan mekanisme kerja sama yang saling menguntungkan antar unit kerja\n\n";
            $rencana .= "3. Inovasi dan Pengembangan Layanan\n";
            $rencana .= "   - Mengembangkan inovasi dalam pelayanan untuk meningkatkan kepuasan masyarakat\n";
            $rencana .= "   - Meningkatkan aksesibilitas dan kemudahan dalam mengakses layanan\n";
            $rencana .= "   - Mengoptimalkan penggunaan teknologi untuk mendukung efisiensi pelayanan\n\n";
            
            $rencana .= "IV. TARGET DAN INDIKATOR KINERJA\n\n";
            $rencana .= "Dengan rencana kegiatan yang telah ditetapkan, diharapkan dapat mencapai:\n\n";
            $rencana .= "1. Peningkatan produktivitas dan kualitas pelayanan yang signifikan\n";
            $rencana .= "2. Penguatan sistem manajemen dan koordinasi organisasi\n";
            $rencana .= "3. Peningkatan kapasitas dan kompetensi personil\n";
            $rencana .= "4. Pencapaian target strategis organisasi sesuai dengan visi dan misi\n\n";
            $rencana .= "Melalui kepemimpinan yang visioner dan manajemen yang efektif, seluruh rencana kegiatan ini akan diarahkan untuk mewujudkan tujuan strategis organisasi dan memberikan kontribusi yang optimal bagi pengembangan KUA Kecamatan Banjarmasin Utara.";
        } else {
            // Rencana untuk staff biasa
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
        }

        return $rencana;
    }
}
