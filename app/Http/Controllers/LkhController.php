<?php

namespace App\Http\Controllers;

use App\Models\Lkh;
use App\Models\KategoriKegiatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LkhController extends Controller
{
    /**
     * Display a listing of the resource.
     * Untuk staff: menampilkan LKH miliknya sendiri
     * Untuk Kepala KUA: redirect ke /staff atau /saya tergantung kebutuhan
     */
    public function index(Request $request)
    {
        // Jika Kepala KUA, redirect ke LKH Staff
        if (Auth::user()->isKepalaKua()) {
            return redirect()->route('lkh.staff');
        }

        // Untuk staff, tampilkan LKH miliknya sendiri
        return $this->saya($request);
    }

    /**
     * Display LKH Staff (untuk Kepala KUA melihat semua LKH pegawai)
     */
    public function staff(Request $request)
    {
        // Hanya Kepala KUA yang bisa akses
        if (!Auth::user()->isKepalaKua()) {
            abort(403, 'Anda tidak berhak mengakses halaman ini');
        }

        $query = Lkh::with(['user', 'kategoriKegiatan']);

        // Hanya tampilkan LKH dari staff (bukan Kepala KUA sendiri)
        $query->whereHas('user', function($q) {
            $q->where('role', '!=', 'kepala_kua');
        });

        // Filter berdasarkan tanggal
        if ($request->has('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        // Filter berdasarkan bulan dan tahun
        if ($request->has('bulan') && $request->has('tahun')) {
            $query->whereYear('tanggal', $request->tahun)
                  ->whereMonth('tanggal', $request->bulan);
        } elseif ($request->has('bulan_tahun')) {
            // Support format bulan_tahun dari form (YYYY-MM)
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
                     ->paginate(20);

        // Add can_edit property
        $lkh->getCollection()->transform(function ($item) {
            $item->can_edit = $item->canEdit();
            return $item;
        });

        // Jika request AJAX atau expects JSON, return JSON
        if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'data' => $lkh
            ]);
        }

        // Ambil list users untuk filter
        $users = User::aktif()
            ->where('role', '!=', 'kepala_kua')
            ->orderBy('name')
            ->get(['id', 'name', 'jabatan']);

        // Return view untuk web
        return view('lkh.index', compact('lkh', 'users'))->with('isStaffView', true);
    }

    /**
     * Display LKH Saya (untuk Kepala KUA melihat LKH miliknya sendiri, atau untuk staff)
     */
    public function saya(Request $request)
    {
        $query = Lkh::with(['user', 'kategoriKegiatan']);

        // Hanya tampilkan LKH milik user yang sedang login
        $query->where('user_id', Auth::id());

        // Filter berdasarkan tanggal
        if ($request->has('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        // Filter berdasarkan bulan dan tahun
        if ($request->has('bulan') && $request->has('tahun')) {
            $query->whereYear('tanggal', $request->tahun)
                  ->whereMonth('tanggal', $request->bulan);
        } elseif ($request->has('bulan_tahun')) {
            // Support format bulan_tahun dari form (YYYY-MM)
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

        $lkh = $query->orderBy('tanggal', 'desc')
                     ->orderBy('waktu_mulai', 'asc')
                     ->paginate(20);

        // Add can_edit property
        $lkh->getCollection()->transform(function ($item) {
            $item->can_edit = $item->canEdit();
            return $item;
        });

        // Jika request AJAX atau expects JSON, return JSON
        if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'data' => $lkh
            ]);
        }

        // Return view untuk web
        return view('lkh.index', compact('lkh'))->with('isStaffView', false);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Ambil kategori kegiatan berdasarkan role user
        $kategoriKegiatan = KategoriKegiatan::aktif()
            ->byRole(Auth::user()->role)
            ->get();

        // Handle copy from LKH
        $sourceLkh = null;
        if ($request->has('copy_from')) {
            $sourceLkh = Lkh::find($request->copy_from);
            if ($sourceLkh && ($sourceLkh->user_id === Auth::id() || Auth::user()->isKepalaKua())) {
                // Valid, bisa di-copy
            } else {
                $sourceLkh = null;
            }
        }

        // Generate keyword map untuk auto-suggest
        $keywordMap = $kategoriKegiatan->mapWithKeys(function($kategori) {
            $keywords = [];
            $nama = strtolower($kategori->nama);
            
            // Extract keywords dari nama kategori
            if (strpos($nama, 'nikah') !== false || strpos($nama, 'perkawinan') !== false) {
                $keywords = array_merge($keywords, ['nikah', 'perkawinan', 'akad', 'pernikahan', 'menikah']);
            }
            if (strpos($nama, 'bimbingan') !== false) {
                $keywords = array_merge($keywords, ['bimbingan', 'bimbing', 'konseling']);
            }
            if (strpos($nama, 'penyuluhan') !== false || strpos($nama, 'penyuluh') !== false) {
                $keywords = array_merge($keywords, ['penyuluhan', 'penyuluh', 'ceramah', 'pengajian']);
            }
            if (strpos($nama, 'arsip') !== false || strpos($nama, 'dokumen') !== false) {
                $keywords = array_merge($keywords, ['arsip', 'dokumen', 'file', 'arsipkan']);
            }
            if (strpos($nama, 'input') !== false || strpos($nama, 'data') !== false) {
                $keywords = array_merge($keywords, ['input', 'data', 'entry', 'masukkan']);
            }
            if (strpos($nama, 'zakat') !== false || strpos($nama, 'wakaf') !== false) {
                $keywords = array_merge($keywords, ['zakat', 'wakaf', 'infaq', 'sedekah']);
            }
            if (strpos($nama, 'masjid') !== false || strpos($nama, 'kemasjidan') !== false) {
                $keywords = array_merge($keywords, ['masjid', 'musholla', 'langgar']);
            }
            if (strpos($nama, 'rapat') !== false || strpos($nama, 'koordinasi') !== false) {
                $keywords = array_merge($keywords, ['rapat', 'koordinasi', 'meeting', 'pertemuan']);
            }
            
            return [$kategori->id => $keywords];
        });

        // Selalu return JSON karena sekarang hanya menggunakan modal
        return response()->json([
            'success' => true,
            'kategori_kegiatan' => $kategoriKegiatan,
            'source_lkh' => $sourceLkh ? [
                'id' => $sourceLkh->id,
                'tanggal' => $sourceLkh->tanggal->format('Y-m-d'),
                'uraian_kegiatan' => $sourceLkh->uraian_kegiatan,
                'waktu_mulai' => $sourceLkh->waktu_mulai,
                'waktu_selesai' => $sourceLkh->waktu_selesai,
                'hasil_output' => $sourceLkh->hasil_output,
                'kendala' => $sourceLkh->kendala,
                'tindak_lanjut' => $sourceLkh->tindak_lanjut,
                'kategori_kegiatan_id' => $sourceLkh->kategori_kegiatan_id,
                'lampiran' => $sourceLkh->lampiran,
            ] : null,
            'keyword_map' => $keywordMap
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kategori_kegiatan_id' => 'nullable|exists:kategori_kegiatan,id',
            'uraian_kegiatan' => 'required|string|max:500',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'hasil_output' => 'nullable|string',
            'kendala' => 'nullable|string',
            'tindak_lanjut' => 'nullable|string',
            'lampiran' => 'nullable|url', // Link drive, bukan file upload
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'draft';
        $validated['kategori_kegiatan_id'] = $validated['kategori_kegiatan_id'] ?? null;

        $lkh = Lkh::create($validated);

        // Auto-generate ringkasan harian setelah LKH dibuat
        $this->regenerateRingkasanHarian($validated['tanggal'], Auth::id());

        // Jika request web, redirect dengan message
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'LKH berhasil dibuat',
                'data' => $lkh->load(['user', 'kategoriKegiatan'])
            ], 201);
        }

        return redirect()->route('lkh.index')
            ->with('success', 'LKH berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $lkh = Lkh::with(['user', 'kategoriKegiatan'])
                  ->findOrFail($id);

        // Jika bukan kepala KUA, hanya bisa lihat LKH miliknya sendiri
        if (!Auth::user()->isKepalaKua() && $lkh->user_id !== Auth::id()) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak berhak mengakses LKH ini'
                ], 403);
            }
            abort(403, 'Anda tidak berhak mengakses LKH ini');
        }

        // Jika request AJAX atau expects JSON, return JSON
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $lkh
            ]);
        }

        // Return view untuk web
        return view('lkh.show', compact('lkh'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $lkh = Lkh::findOrFail($id);

        // Cek apakah bisa di-edit
        if ($lkh->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak berhak mengedit LKH ini'
            ], 403);
        }

        if (!$lkh->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'LKH yang sudah disubmit tidak dapat diedit'
            ], 422);
        }

        $kategoriKegiatan = KategoriKegiatan::aktif()
            ->byRole(Auth::user()->role)
            ->get();

        // Generate keyword map untuk auto-suggest
        $keywordMap = $kategoriKegiatan->mapWithKeys(function($kategori) {
            $keywords = [];
            $nama = strtolower($kategori->nama);
            
            // Extract keywords dari nama kategori
            if (strpos($nama, 'nikah') !== false || strpos($nama, 'perkawinan') !== false) {
                $keywords = array_merge($keywords, ['nikah', 'perkawinan', 'akad', 'pernikahan', 'menikah']);
            }
            if (strpos($nama, 'bimbingan') !== false) {
                $keywords = array_merge($keywords, ['bimbingan', 'bimbing', 'konseling']);
            }
            if (strpos($nama, 'penyuluhan') !== false || strpos($nama, 'penyuluh') !== false) {
                $keywords = array_merge($keywords, ['penyuluhan', 'penyuluh', 'ceramah', 'pengajian']);
            }
            if (strpos($nama, 'arsip') !== false || strpos($nama, 'dokumen') !== false) {
                $keywords = array_merge($keywords, ['arsip', 'dokumen', 'file', 'arsipkan']);
            }
            if (strpos($nama, 'input') !== false || strpos($nama, 'data') !== false) {
                $keywords = array_merge($keywords, ['input', 'data', 'entry', 'masukkan']);
            }
            if (strpos($nama, 'zakat') !== false || strpos($nama, 'wakaf') !== false) {
                $keywords = array_merge($keywords, ['zakat', 'wakaf', 'infaq', 'sedekah']);
            }
            if (strpos($nama, 'masjid') !== false || strpos($nama, 'kemasjidan') !== false) {
                $keywords = array_merge($keywords, ['masjid', 'musholla', 'langgar']);
            }
            if (strpos($nama, 'rapat') !== false || strpos($nama, 'koordinasi') !== false) {
                $keywords = array_merge($keywords, ['rapat', 'koordinasi', 'meeting', 'pertemuan']);
            }
            
            return [$kategori->id => $keywords];
        });

        // Jika request AJAX atau expects JSON, return JSON
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $lkh->id,
                    'tanggal' => $lkh->tanggal->format('Y-m-d'),
                    'kategori_kegiatan_id' => $lkh->kategori_kegiatan_id,
                    'uraian_kegiatan' => $lkh->uraian_kegiatan,
                    'waktu_mulai' => $lkh->waktu_mulai,
                    'waktu_selesai' => $lkh->waktu_selesai,
                    'hasil_output' => $lkh->hasil_output,
                    'kendala' => $lkh->kendala,
                    'tindak_lanjut' => $lkh->tindak_lanjut,
                    'lampiran' => $lkh->lampiran,
                ],
                'kategori_kegiatan' => $kategoriKegiatan,
                'keyword_map' => $keywordMap
            ]);
        }

        // Return view untuk web (fallback, tapi seharusnya tidak dipakai)
        return view('lkh.edit', compact('lkh', 'kategoriKegiatan', 'keywordMap'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $lkh = Lkh::findOrFail($id);

        // Cek apakah bisa di-edit
        if ($lkh->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak berhak mengedit LKH ini'
            ], 403);
        }

        if (!$lkh->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'LKH yang sudah disubmit tidak dapat diedit'
            ], 422);
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kategori_kegiatan_id' => 'nullable|exists:kategori_kegiatan,id',
            'uraian_kegiatan' => 'required|string|max:500',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'hasil_output' => 'nullable|string',
            'kendala' => 'nullable|string',
            'tindak_lanjut' => 'nullable|string',
            'lampiran' => 'nullable|url', // Link drive, bukan file upload
        ]);

        $validated['kategori_kegiatan_id'] = $validated['kategori_kegiatan_id'] ?? null;
        
        // Update status jika ada, jika tidak tetap status yang lama
        if (isset($validated['status'])) {
            $lkh->status = $validated['status'];
        }

        $tanggalLama = $lkh->tanggal;
        $lkh->update($validated);

        // Auto-regenerate ringkasan harian setelah LKH diupdate
        // Regenerate untuk tanggal lama dan tanggal baru (jika berbeda)
        $this->regenerateRingkasanHarian($tanggalLama, $lkh->user_id);
        if ($validated['tanggal'] != $tanggalLama) {
            $this->regenerateRingkasanHarian($validated['tanggal'], $lkh->user_id);
        }

        // Jika request web, redirect dengan message
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'LKH berhasil diperbarui',
                'data' => $lkh->load(['user', 'kategoriKegiatan'])
            ]);
        }

        return redirect()->route('lkh.show', $lkh->id)
            ->with('success', 'LKH berhasil diperbarui');
    }

    /**
     * Update status LKH (draft/selesai)
     */
    public function updateStatus(Request $request, string $id)
    {
        $lkh = Lkh::findOrFail($id);

        // Cek apakah bisa diubah (hanya owner)
        if ($lkh->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak berhak mengubah status LKH ini'
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,selesai',
        ]);

        $lkh->update(['status' => $validated['status']]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status LKH berhasil diperbarui',
                'data' => $lkh->load(['user', 'kategoriKegiatan'])
            ]);
        }

        return redirect()->route('lkh.show', $lkh->id)
            ->with('success', 'Status LKH berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lkh = Lkh::findOrFail($id);

        // Cek apakah bisa dihapus (hanya owner atau Kepala KUA)
        if ($lkh->user_id !== Auth::id() && !Auth::user()->isKepalaKua()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak berhak menghapus LKH ini'
            ], 403);
        }

        // Tidak perlu hapus file karena sekarang menggunakan link drive

        $tanggal = $lkh->tanggal;
        $userId = $lkh->user_id;
        $lkh->delete();

        // Auto-regenerate ringkasan harian setelah LKH dihapus
        $this->regenerateRingkasanHarian($tanggal, $userId);

        // Jika request web, redirect dengan message
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'LKH berhasil dihapus'
            ]);
        }

        return redirect()->route('lkh.index')
            ->with('success', 'LKH berhasil dihapus');
    }

    /**
     * Buka link lampiran LKH di Drive
     */
    public function download(string $id)
    {
        $lkh = Lkh::findOrFail($id);

        // Cek apakah bisa diakses
        if (!Auth::user()->isKepalaKua() && $lkh->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses lampiran ini');
        }

        if (!$lkh->lampiran) {
            abort(404, 'Lampiran tidak ditemukan');
        }

        // Jika lampiran adalah URL (link drive), redirect ke sana
        if (filter_var($lkh->lampiran, FILTER_VALIDATE_URL)) {
            return redirect($lkh->lampiran);
        }

        // Fallback: jika masih file path lama (untuk backward compatibility)
        if (Storage::disk('public')->exists($lkh->lampiran)) {
            return Storage::disk('public')->download($lkh->lampiran);
        }

        abort(404, 'Lampiran tidak ditemukan');
    }

    /**
     * Copy LKH dari LKH lain
     */
    public function copy(string $id)
    {
        $sourceLkh = Lkh::findOrFail($id);

        // Cek apakah bisa di-copy (hanya LKH sendiri atau jika Kepala KUA)
        if (!Auth::user()->isKepalaKua() && $sourceLkh->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak copy LKH ini');
        }

        // Ambil kategori kegiatan untuk form
        $kategoriKegiatan = KategoriKegiatan::aktif()
            ->byRole(Auth::user()->role)
            ->get();

        // Jika request AJAX atau expects JSON, return JSON
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $sourceLkh,
                'kategori_kegiatan' => $kategoriKegiatan
            ]);
        }

        // Return view untuk web dengan data source
        return view('lkh.create', [
            'kategoriKegiatan' => $kategoriKegiatan,
            'sourceLkh' => $sourceLkh
        ]);
    }

    /**
     * Display detail harian - semua kegiatan di tanggal tertentu
     */
    public function detailHarian(Request $request)
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $userId = $request->input('user_id', Auth::id());

        // Cek akses: hanya bisa lihat detail harian sendiri atau jika Kepala KUA
        if ($userId != Auth::id() && !Auth::user()->isKepalaKua()) {
            abort(403, 'Anda tidak berhak mengakses detail harian ini');
        }

        // Ambil semua LKH untuk tanggal tersebut
        $lkhList = Lkh::byUser($userId)
            ->byTanggal($tanggal)
            ->with(['kategoriKegiatan', 'user'])
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // Ambil atau generate ringkasan harian
        $ringkasanHarian = \App\Models\RingkasanHarian::byUser($userId)
            ->byTanggal($tanggal)
            ->first();

        // Jika tidak ada ringkasan, generate otomatis
        if (!$ringkasanHarian && $lkhList->isNotEmpty()) {
            $ringkasanOtomatis = $this->generateRingkasanHarian($lkhList);
            $ringkasanHarian = \App\Models\RingkasanHarian::create([
                'user_id' => $userId,
                'tanggal' => $tanggal,
                'ringkasan' => $ringkasanOtomatis,
            ]);
        }

        $user = \App\Models\User::find($userId);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'tanggal' => $tanggal,
                    'user' => $user ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'jabatan' => $user->jabatan,
                    ] : null,
                    'lkh_list' => $lkhList,
                    'ringkasan_harian' => $ringkasanHarian,
                    'total_kegiatan' => $lkhList->count(),
                    'total_durasi' => $lkhList->sum('durasi'),
                ]
            ]);
        }

        return view('lkh.detail-harian', compact('lkhList', 'ringkasanHarian', 'tanggal', 'user'));
    }

    /**
     * Generate ringkasan harian otomatis dari semua LKH di tanggal tersebut
     */
    private function generateRingkasanHarian($lkhList)
    {
        if ($lkhList->isEmpty()) {
            return "Tidak ada kegiatan pada hari ini.";
        }

        $totalKegiatan = $lkhList->count();
        $totalDurasi = $lkhList->sum('durasi');
        $kategoriCount = $lkhList->groupBy('kategori_kegiatan_id')->count();

        $ringkasan = "Pada hari ini telah dilaksanakan {$totalKegiatan} kegiatan dengan total durasi " . number_format($totalDurasi, 2) . " jam. ";
        
        if ($kategoriCount > 0) {
            $ringkasan .= "Kegiatan tersebut mencakup {$kategoriCount} kategori kegiatan yang berbeda. ";
        }

        // Ambil top 3 kategori kegiatan
        $topKategori = $lkhList->filter(function($item) {
            return $item->kategoriKegiatan !== null;
        })->groupBy('kategori_kegiatan_id')
          ->map(function ($items) {
              return [
                  'nama' => $items->first()->kategoriKegiatan->nama ?? 'Lainnya',
                  'jumlah' => $items->count(),
                  'durasi' => $items->sum('durasi')
              ];
          })
          ->sortByDesc('jumlah')
          ->take(3);

        if ($topKategori->isNotEmpty()) {
            $ringkasan .= "\n\nKegiatan utama:\n";
            foreach ($topKategori as $kategori) {
                $ringkasan .= "• {$kategori['nama']}: {$kategori['jumlah']} kegiatan (" . number_format($kategori['durasi'], 2) . " jam)\n";
            }
        }

        // Cek kendala
        $kendalaList = $lkhList->filter(function($item) {
            return !empty($item->kendala);
        });

        if ($kendalaList->isNotEmpty()) {
            $ringkasan .= "\nKendala yang dihadapi: " . $kendalaList->count() . " kegiatan mengalami kendala.";
        } else {
            $ringkasan .= "\nSemua kegiatan berjalan sesuai rencana tanpa kendala yang signifikan.";
        }

        return $ringkasan;
    }

    /**
     * Update ringkasan harian
     */
    public function updateRingkasanHarian(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'ringkasan' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $userId = $validated['user_id'] ?? Auth::id();

        // Cek akses
        if ($userId != Auth::id() && !Auth::user()->isKepalaKua()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak berhak mengupdate ringkasan harian ini'
            ], 403);
        }

        $ringkasanHarian = \App\Models\RingkasanHarian::byUser($userId)
            ->byTanggal($validated['tanggal'])
            ->first();

        if ($ringkasanHarian) {
            $ringkasanHarian->update(['ringkasan' => $validated['ringkasan']]);
        } else {
            $ringkasanHarian = \App\Models\RingkasanHarian::create([
                'user_id' => $userId,
                'tanggal' => $validated['tanggal'],
                'ringkasan' => $validated['ringkasan'],
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Ringkasan harian berhasil diperbarui',
                'data' => $ringkasanHarian
            ]);
        }

        return redirect()->back()->with('success', 'Ringkasan harian berhasil diperbarui');
    }

    /**
     * Regenerate ringkasan harian untuk tanggal dan user tertentu
     */
    private function regenerateRingkasanHarian($tanggal, $userId)
    {
        // Ambil semua LKH untuk tanggal tersebut
        $lkhList = Lkh::byUser($userId)
            ->byTanggal($tanggal)
            ->with(['kategoriKegiatan', 'user'])
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // Generate ringkasan otomatis
        $ringkasanOtomatis = $this->generateRingkasanHarian($lkhList);

        // Update atau create ringkasan harian
        $ringkasanHarian = \App\Models\RingkasanHarian::byUser($userId)
            ->byTanggal($tanggal)
            ->first();

        if ($ringkasanHarian) {
            $ringkasanHarian->update(['ringkasan' => $ringkasanOtomatis]);
        } else {
            \App\Models\RingkasanHarian::create([
                'user_id' => $userId,
                'tanggal' => $tanggal,
                'ringkasan' => $ringkasanOtomatis,
            ]);
        }
    }
}
