<?php

namespace App\Http\Controllers;

use App\Models\Skp;
use App\Models\User;
use App\Models\LaporanBulanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SkpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Skp::with(['user', 'skpAtasan', 'laporanBulanan']);

        // Jika bukan kepala KUA, hanya lihat SKP sendiri
        if (!Auth::user()->isKepalaKua()) {
            $query->where('user_id', Auth::id());
        }

        // Filter berdasarkan tahun
        $tahun = $request->input('tahun', Carbon::now()->year);
        $query->byTahun($tahun);

        // Filter berdasarkan status
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        // Filter berdasarkan user (untuk Kepala KUA)
        if ($request->has('user_id') && Auth::user()->isKepalaKua()) {
            $query->where('user_id', $request->user_id);
        }

        $skp = $query->orderBy('tahun', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $skp
            ]);
        }

        // Ambil list users untuk filter (untuk Kepala KUA)
        $users = null;
        if (Auth::user()->isKepalaKua()) {
            $users = User::aktif()
                ->orderBy('name')
                ->get(['id', 'name', 'jabatan', 'role']);
        }

        return view('skp.index', compact('skp', 'users', 'tahun'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $tahun = $request->input('tahun', Carbon::now()->year);
        $user = Auth::user();

        // Jika Kepala KUA, ambil SKP-nya sendiri sebagai referensi untuk staff
        $skpAtasan = null;
        if ($user->isKepalaKua()) {
            // Kepala KUA bisa membuat SKP untuk dirinya sendiri
            $skpAtasan = Skp::byUser($user->id)
                ->byTahun($tahun)
                ->first();
        } else {
            // Staff harus mengacu pada SKP Kepala KUA
            $kepalaKua = User::where('role', 'kepala_kua')
                ->where('is_active', true)
                ->first();
            
            if ($kepalaKua) {
                $skpAtasan = Skp::byUser($kepalaKua->id)
                    ->byTahun($tahun)
                    ->first();
            }
        }

        // Ambil laporan bulanan yang belum terhubung dengan SKP
        $laporanBulanan = LaporanBulanan::byUser($user->id)
            ->byBulanTahun(Carbon::now()->month, $tahun)
            ->whereDoesntHave('skp')
            ->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'tahun' => $tahun,
                    'skp_atasan' => $skpAtasan ? [
                        'id' => $skpAtasan->id,
                        'kegiatan_tugas_jabatan' => $skpAtasan->kegiatan_tugas_jabatan,
                        'target_kuantitas' => $skpAtasan->target_kuantitas,
                        'target_kualitas' => $skpAtasan->target_kualitas,
                        'target_waktu' => $skpAtasan->target_waktu,
                    ] : null,
                    'laporan_bulanan' => $laporanBulanan->map(function($laporan) {
                        return [
                            'id' => $laporan->id,
                            'nama_bulan' => $laporan->nama_bulan,
                            'total_lkh' => $laporan->total_lkh,
                            'total_durasi' => $laporan->total_durasi,
                        ];
                    })
                ]
            ]);
        }

        return view('skp.create', compact('tahun', 'skpAtasan', 'laporanBulanan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|min:2020|max:2100',
            'kegiatan_tugas_jabatan' => 'required|string|max:255',
            'rincian_tugas' => 'nullable|string',
            'target_kuantitas' => 'nullable|string',
            'target_kualitas' => 'nullable|string',
            'target_waktu' => 'nullable|string',
            'target_biaya' => 'nullable|string',
            'skp_atasan_id' => 'nullable|exists:skp,id',
            'laporan_bulanan_ids' => 'nullable|array',
            'laporan_bulanan_ids.*' => 'exists:laporan_bulanan,id',
        ]);

        // Cek apakah sudah ada SKP untuk tahun ini
        $existing = Skp::byUser(Auth::id())
            ->byTahun($validated['tahun'])
            ->where('kegiatan_tugas_jabatan', $validated['kegiatan_tugas_jabatan'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'SKP dengan kegiatan yang sama untuk tahun ini sudah ada'
            ], 422);
        }

        // Validasi SKP atasan (harus milik Kepala KUA jika user adalah staff)
        if (isset($validated['skp_atasan_id']) && !Auth::user()->isKepalaKua()) {
            $skpAtasan = Skp::find($validated['skp_atasan_id']);
            if (!$skpAtasan || !$skpAtasan->user->isKepalaKua()) {
                return response()->json([
                    'success' => false,
                    'message' => 'SKP atasan tidak valid'
                ], 422);
            }
        }

        // Validasi laporan bulanan (harus milik user)
        if (isset($validated['laporan_bulanan_ids'])) {
            $laporanCount = LaporanBulanan::whereIn('id', $validated['laporan_bulanan_ids'])
                ->where('user_id', Auth::id())
                ->count();

            if ($laporanCount !== count($validated['laporan_bulanan_ids'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Beberapa laporan bulanan tidak valid atau bukan milik Anda'
                ], 422);
            }
        }

        // Buat SKP
        $skp = Skp::create([
            'user_id' => Auth::id(),
            'tahun' => $validated['tahun'],
            'kegiatan_tugas_jabatan' => $validated['kegiatan_tugas_jabatan'],
            'rincian_tugas' => $validated['rincian_tugas'] ?? null,
            'target_kuantitas' => $validated['target_kuantitas'] ?? null,
            'target_kualitas' => $validated['target_kualitas'] ?? null,
            'target_waktu' => $validated['target_waktu'] ?? null,
            'target_biaya' => $validated['target_biaya'] ?? null,
            'skp_atasan_id' => $validated['skp_atasan_id'] ?? null,
            'status' => 'draft',
        ]);

        // Attach laporan bulanan jika ada
        if (isset($validated['laporan_bulanan_ids'])) {
            $skp->laporanBulanan()->attach($validated['laporan_bulanan_ids']);
            // Hitung realisasi dari laporan bulanan
            $skp->hitungRealisasiDariLaporanBulanan();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'SKP berhasil dibuat',
                'data' => $skp->load(['user', 'skpAtasan', 'laporanBulanan'])
            ], 201);
        }

        return redirect()->route('skp.show', $skp->id)
            ->with('success', 'SKP berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $skp = Skp::with(['user', 'skpAtasan', 'skpStaff', 'laporanBulanan.lkh', 'disetujuiOleh', 'dinilaiOleh'])
            ->findOrFail($id);

        // Cek akses
        if (!Auth::user()->isKepalaKua() && $skp->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses SKP ini');
        }

        // Jika Kepala KUA melihat SKP staff, hitung agregasi
        if (Auth::user()->isKepalaKua() && !$skp->user->isKepalaKua()) {
            // Agregasi data dari SKP staff untuk SKP Kepala KUA
            $this->hitungAgregasiStaff($skp);
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $skp
            ]);
        }

        return view('skp.show', compact('skp'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $skp = Skp::with(['skpAtasan', 'laporanBulanan'])->findOrFail($id);

        // Cek akses
        if ($skp->user_id !== Auth::id() && !Auth::user()->isKepalaKua()) {
            abort(403, 'Anda tidak berhak mengedit SKP ini');
        }

        if (!$skp->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'SKP yang sudah dinilai tidak dapat diedit'
            ], 422);
        }

        // Ambil laporan bulanan yang belum terhubung atau sudah terhubung dengan SKP ini
        $laporanBulanan = LaporanBulanan::byUser($skp->user_id)
            ->byBulanTahun(Carbon::now()->month, $skp->tahun)
            ->get();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'skp' => [
                        'id' => $skp->id,
                        'tahun' => $skp->tahun,
                        'kegiatan_tugas_jabatan' => $skp->kegiatan_tugas_jabatan,
                        'rincian_tugas' => $skp->rincian_tugas,
                        'target_kuantitas' => $skp->target_kuantitas,
                        'target_kualitas' => $skp->target_kualitas,
                        'target_waktu' => $skp->target_waktu,
                        'target_biaya' => $skp->target_biaya,
                        'skp_atasan_id' => $skp->skp_atasan_id,
                    ],
                    'laporan_bulanan' => $laporanBulanan->map(function($laporan) use ($skp) {
                        return [
                            'id' => $laporan->id,
                            'nama_bulan' => $laporan->nama_bulan,
                            'total_lkh' => $laporan->total_lkh,
                            'total_durasi' => $laporan->total_durasi,
                            'terhubung' => $skp->laporanBulanan->contains($laporan->id),
                        ];
                    })
                ]
            ]);
        }

        return view('skp.edit', compact('skp', 'laporanBulanan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $skp = Skp::findOrFail($id);

        // Cek akses
        if ($skp->user_id !== Auth::id() && !Auth::user()->isKepalaKua()) {
            abort(403, 'Anda tidak berhak mengupdate SKP ini');
        }

        if (!$skp->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'SKP yang sudah dinilai tidak dapat diupdate'
            ], 422);
        }

        $validated = $request->validate([
            'kegiatan_tugas_jabatan' => 'required|string|max:255',
            'rincian_tugas' => 'nullable|string',
            'target_kuantitas' => 'nullable|string',
            'target_kualitas' => 'nullable|string',
            'target_waktu' => 'nullable|string',
            'target_biaya' => 'nullable|string',
            'skp_atasan_id' => 'nullable|exists:skp,id',
            'laporan_bulanan_ids' => 'nullable|array',
            'laporan_bulanan_ids.*' => 'exists:laporan_bulanan,id',
        ]);

        // Update SKP
        $skp->update([
            'kegiatan_tugas_jabatan' => $validated['kegiatan_tugas_jabatan'],
            'rincian_tugas' => $validated['rincian_tugas'] ?? $skp->rincian_tugas,
            'target_kuantitas' => $validated['target_kuantitas'] ?? $skp->target_kuantitas,
            'target_kualitas' => $validated['target_kualitas'] ?? $skp->target_kualitas,
            'target_waktu' => $validated['target_waktu'] ?? $skp->target_waktu,
            'target_biaya' => $validated['target_biaya'] ?? $skp->target_biaya,
            'skp_atasan_id' => $validated['skp_atasan_id'] ?? $skp->skp_atasan_id,
        ]);

        // Update laporan bulanan jika ada
        if (isset($validated['laporan_bulanan_ids'])) {
            $skp->laporanBulanan()->sync($validated['laporan_bulanan_ids']);
            // Hitung ulang realisasi
            $skp->hitungRealisasiDariLaporanBulanan();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'SKP berhasil diperbarui',
                'data' => $skp->load(['user', 'skpAtasan', 'laporanBulanan'])
            ]);
        }

        return redirect()->route('skp.show', $skp->id)
            ->with('success', 'SKP berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $skp = Skp::findOrFail($id);

        // Cek akses
        if ($skp->user_id !== Auth::id() && !Auth::user()->isKepalaKua()) {
            abort(403, 'Anda tidak berhak menghapus SKP ini');
        }

        $skp->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'SKP berhasil dihapus'
            ]);
        }

        return redirect()->route('skp.index')
            ->with('success', 'SKP berhasil dihapus');
    }

    /**
     * Setujui SKP (untuk atasan)
     */
    public function setujui(Request $request, string $id)
    {
        $skp = Skp::findOrFail($id);

        // Hanya atasan yang bisa menyetujui
        if (!Auth::user()->isKepalaKua() && $skp->user->role !== Auth::user()->role) {
            abort(403, 'Anda tidak berhak menyetujui SKP ini');
        }

        $validated = $request->validate([
            'catatan' => 'nullable|string',
        ]);

        $skp->update([
            'status' => 'disetujui',
            'disetujui_oleh' => Auth::id(),
            'disetujui_pada' => now(),
            'catatan' => $validated['catatan'] ?? $skp->catatan,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'SKP berhasil disetujui',
                'data' => $skp->load(['user', 'disetujuiOleh'])
            ]);
        }

        return redirect()->route('skp.show', $skp->id)
            ->with('success', 'SKP berhasil disetujui');
    }

    /**
     * Nilai SKP (untuk atasan)
     */
    public function nilai(Request $request, string $id)
    {
        $skp = Skp::findOrFail($id);

        // Hanya atasan yang bisa menilai
        if (!Auth::user()->isKepalaKua() && $skp->user->role !== Auth::user()->role) {
            abort(403, 'Anda tidak berhak menilai SKP ini');
        }

        if (!$skp->canDinilai()) {
            return response()->json([
                'success' => false,
                'message' => 'SKP belum disetujui atau sudah dinilai'
            ], 422);
        }

        $validated = $request->validate([
            'realisasi_kuantitas' => 'nullable|string',
            'realisasi_kualitas' => 'nullable|string',
            'realisasi_waktu' => 'nullable|string',
            'realisasi_biaya' => 'nullable|string',
            'nilai_capaian' => 'nullable|numeric|min:0|max:100',
            'catatan' => 'nullable|string',
        ]);

        // Hitung realisasi dari laporan bulanan jika belum dihitung
        if (!$skp->realisasi_kuantitas && $skp->laporanBulanan->isNotEmpty()) {
            $skp->hitungRealisasiDariLaporanBulanan();
        }

        $skp->update([
            'status' => 'dinilai',
            'realisasi_kuantitas' => $validated['realisasi_kuantitas'] ?? $skp->realisasi_kuantitas,
            'realisasi_kualitas' => $validated['realisasi_kualitas'] ?? $skp->realisasi_kualitas,
            'realisasi_waktu' => $validated['realisasi_waktu'] ?? $skp->realisasi_waktu,
            'realisasi_biaya' => $validated['realisasi_biaya'] ?? $skp->realisasi_biaya,
            'nilai_capaian' => $validated['nilai_capaian'] ?? $skp->nilai_capaian,
            'catatan' => $validated['catatan'] ?? $skp->catatan,
            'dinilai_oleh' => Auth::id(),
            'dinilai_pada' => now(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'SKP berhasil dinilai',
                'data' => $skp->load(['user', 'dinilaiOleh'])
            ]);
        }

        return redirect()->route('skp.show', $skp->id)
            ->with('success', 'SKP berhasil dinilai');
    }

    /**
     * Agregasi SKP Kepala KUA dari SKP staff
     */
    private function hitungAgregasiStaff(Skp $skpKepalaKua)
    {
        // Ambil semua SKP staff yang mengacu pada SKP Kepala KUA
        $skpStaff = Skp::bySkpAtasan($skpKepalaKua->id)
            ->with('laporanBulanan')
            ->get();

        if ($skpStaff->isEmpty()) {
            return;
        }

        // Agregasi data dari SKP staff
        $totalNilaiCapaian = $skpStaff->avg('nilai_capaian') ?? 0;
        $totalLaporanBulanan = $skpStaff->sum(function($skp) {
            return $skp->laporanBulanan->count();
        });

        // Update realisasi SKP Kepala KUA
        $skpKepalaKua->realisasi_kuantitas = "Agregasi dari {$skpStaff->count()} SKP staff dengan {$totalLaporanBulanan} laporan bulanan";
        $skpKepalaKua->realisasi_kualitas = "Rata-rata nilai capaian: " . round($totalNilaiCapaian, 2);
        $skpKepalaKua->nilai_capaian = round($totalNilaiCapaian, 2);
        $skpKepalaKua->save();
    }

    /**
     * Generate SKP Kepala KUA dari agregasi SKP staff
     */
    public function generateDariStaff(Request $request)
    {
        if (!Auth::user()->isKepalaKua()) {
            abort(403, 'Hanya Kepala KUA yang bisa generate SKP dari staff');
        }

        $tahun = $request->input('tahun', Carbon::now()->year);

        // Ambil semua SKP staff untuk tahun ini
        $skpStaff = Skp::whereHas('user', function($q) {
                $q->where('role', '!=', 'kepala_kua');
            })
            ->byTahun($tahun)
            ->with(['user', 'laporanBulanan'])
            ->get();

        if ($skpStaff->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada SKP staff untuk tahun ini'
            ], 422);
        }

        // Buat atau update SKP Kepala KUA
        $skpKepalaKua = Skp::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'tahun' => $tahun,
                'kegiatan_tugas_jabatan' => 'Pengelolaan Kinerja Unit KUA'
            ],
            [
                'rincian_tugas' => 'Mengelola dan mengkoordinasikan kinerja seluruh staf KUA',
                'status' => 'draft',
            ]
        );

        // Agregasi laporan bulanan dari semua staff
        $laporanBulananIds = $skpStaff->flatMap(function($skp) {
            return $skp->laporanBulanan->pluck('id');
        })->unique()->toArray();

        $skpKepalaKua->laporanBulanan()->sync($laporanBulananIds);
        $this->hitungAgregasiStaff($skpKepalaKua);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'SKP Kepala KUA berhasil di-generate dari SKP staff',
                'data' => $skpKepalaKua->load(['user', 'skpStaff', 'laporanBulanan'])
            ], 201);
        }

        return redirect()->route('skp.show', $skpKepalaKua->id)
            ->with('success', 'SKP Kepala KUA berhasil di-generate dari SKP staff');
    }

    /**
     * Generate bukti fisik SKP dari laporan triwulanan/tahunan (auto-generate PDF)
     */
    public function generateBuktiFisik(Request $request, string $id)
    {
        $skp = Skp::with(['user', 'laporanBulanan', 'laporanTriwulanan', 'laporanTahunan'])->findOrFail($id);

        // Cek akses
        if ($skp->user_id !== Auth::id() && !Auth::user()->isKepalaKua()) {
            abort(403, 'Anda tidak berhak menggenerate bukti fisik untuk SKP ini');
        }

        // Generate HTML untuk PDF berdasarkan periode
        if ($skp->periode === 'triwulan' && $skp->laporanTriwulanan->isNotEmpty()) {
            $laporan = $skp->laporanTriwulanan->first();
            $html = view('export.bukti-fisik-skp-triwulanan', compact('skp', 'laporan'))->render();
            $filename = 'Bukti_Fisik_SKP_Triwulan_' . $skp->triwulan . '_' . $skp->tahun . '.html';
        } elseif ($skp->periode === 'tahunan' && $skp->laporanTahunan->isNotEmpty()) {
            $laporan = $skp->laporanTahunan->first();
            $html = view('export.bukti-fisik-skp-tahunan', compact('skp', 'laporan'))->render();
            $filename = 'Bukti_Fisik_SKP_Tahun_' . $skp->tahun . '.html';
        } else {
            // Fallback ke laporan bulanan jika tidak ada triwulanan/tahunan
            $html = view('export.bukti-fisik-skp-bulanan', compact('skp'))->render();
            $filename = 'Bukti_Fisik_SKP_' . $skp->tahun . '.html';
        }

        // Simpan sebagai file HTML untuk preview/download (bisa diubah ke PDF nanti dengan library seperti DomPDF)
        $filePath = 'skp/bukti-fisik/' . uniqid() . '_' . $filename;
        Storage::disk('public')->put($filePath, $html);

        // Simpan path file sementara di database untuk akses download
        // User bisa download file ini, upload ke Drive, lalu masukkan link Drive di form (akan replace path ini)
        $skp->update(['file_bukti_fisik' => $filePath]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil di-generate. Silakan download, upload ke Drive, dan masukkan link di form.',
                'data' => [
                    'skp' => $skp->load(['user']),
                    'preview_url' => route('skp.download-bukti-fisik', $skp->id),
                    'download_url' => route('skp.download-bukti-fisik', $skp->id)
                ]
            ]);
        }

        // Redirect dengan file untuk preview
        return redirect()->route('skp.show', $skp->id)
            ->with('success', 'Dokumen berhasil di-generate. Silakan download, upload ke Drive, lalu masukkan link Drive di form.')
            ->with('preview_file', $filePath);
    }

    /**
     * Simpan link bukti fisik dari Drive (staff upload ke drive, link diinput di form)
     */
    public function simpanLinkBuktiFisik(Request $request, string $id)
    {
        $skp = Skp::findOrFail($id);

        // Cek akses
        if ($skp->user_id !== Auth::id() && !Auth::user()->isKepalaKua()) {
            abort(403, 'Anda tidak berhak menyimpan link bukti fisik untuk SKP ini');
        }

        $validated = $request->validate([
            'link_drive_bukti_fisik' => 'required|url',
            'link_skp_eksternal' => 'nullable|url',
        ]);

        $skp->update([
            'file_bukti_fisik' => $validated['link_drive_bukti_fisik'], // Simpan link sebagai string
            'link_skp_eksternal' => $validated['link_skp_eksternal'] ?? null,
            'uploaded_at' => now(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Link bukti fisik berhasil disimpan',
                'data' => $skp->load(['user'])
            ]);
        }

        return redirect()->route('skp.show', $skp->id)
            ->with('success', 'Link bukti fisik berhasil disimpan');
    }

    /**
     * Redirect ke link bukti fisik di Drive
     */
    public function bukaLinkBuktiFisik(string $id)
    {
        $skp = Skp::findOrFail($id);

        // Cek akses
        if (!Auth::user()->isKepalaKua() && $skp->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses bukti fisik ini');
        }

        if (!$skp->file_bukti_fisik) {
            abort(404, 'Link bukti fisik tidak ditemukan');
        }

        // Jika file_bukti_fisik adalah URL (link drive), redirect ke sana
        if (filter_var($skp->file_bukti_fisik, FILTER_VALIDATE_URL)) {
            return redirect($skp->file_bukti_fisik);
        }

        // Jika file_bukti_fisik adalah path file, return file HTML
        if (Storage::disk('public')->exists($skp->file_bukti_fisik)) {
            $content = Storage::disk('public')->get($skp->file_bukti_fisik);
            $filename = basename($skp->file_bukti_fisik);
            
            return response($content, 200)
                ->header('Content-Type', 'text/html; charset=utf-8')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
        }

        // Jika file tidak ditemukan, coba generate ulang
        // Redirect ke halaman SKP dengan pesan error
        return redirect()->route('skp.show', $skp->id)
            ->with('error', 'File bukti fisik tidak ditemukan. Silakan generate ulang bukti fisik.');
    }
}
