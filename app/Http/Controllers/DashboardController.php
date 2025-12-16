<?php

namespace App\Http\Controllers;

use App\Models\Lkh;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard data
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        // Default bulan dan tahun
        $bulan = $request->input('bulan', $now->month);
        $tahun = $request->input('tahun', $now->year);

        try {
            if ($user->isKepalaKua()) {
                $data = $this->dashboardKepalaKua($bulan, $tahun);
            } else {
                $data = $this->dashboardPegawai($user, $bulan, $tahun);
            }

            // Jika request AJAX atau expects JSON, return JSON
            if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
                return $data;
            }

            // Return view untuk web (extract data dari response JSON)
            $responseData = json_decode($data->getContent(), true);
            return view('dashboard', array_merge($responseData['data'] ?? [], ['user' => $user]));
        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Jika request AJAX atau expects JSON, return error JSON
            if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memuat data dashboard: ' . $e->getMessage()
                ], 500);
            }

            // Return view dengan error
            return view('dashboard', [
                'user' => $user,
                'error' => 'Gagal memuat data dashboard'
            ]);
        }
    }

    /**
     * Dashboard untuk Kepala KUA
     */
    private function dashboardKepalaKua($bulan, $tahun)
    {
        // Total LKH per status
        $totalLkh = [
            'draft' => Lkh::byStatus('draft')
                ->byBulanTahun($bulan, $tahun)
                ->count(),
            'selesai' => Lkh::byStatus('selesai')
                ->byBulanTahun($bulan, $tahun)
                ->count(),
        ];

        // Total pegawai aktif
        $totalPegawai = User::aktif()
            ->where('role', '!=', 'kepala_kua')
            ->count();

        // LKH per pegawai
        $lkhPerPegawai = Lkh::selectRaw('user_id, COUNT(*) as total')
            ->byBulanTahun($bulan, $tahun)
            ->groupBy('user_id')
            ->with('user:id,name,nip,jabatan')
            ->get();

        // LKH hari ini
        $lkhHariIni = Lkh::whereDate('tanggal', Carbon::today())
            ->with(['user', 'kategoriKegiatan'])
            ->get()
            ->count();

        // Statistik per minggu
        $startOfMonth = Carbon::create($tahun, $bulan, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        $statistikMingguan = [];
        $currentWeek = $startOfMonth->copy();
        
        while ($currentWeek->lte($endOfMonth)) {
            $weekEnd = $currentWeek->copy()->endOfWeek();
            if ($weekEnd->gt($endOfMonth)) {
                $weekEnd = $endOfMonth;
            }

            $statistikMingguan[] = [
                'minggu' => 'Minggu ' . $currentWeek->weekOfMonth,
                'tanggal' => $currentWeek->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                'total' => Lkh::whereBetween('tanggal', [$currentWeek->format('Y-m-d'), $weekEnd->format('Y-m-d')])
                    ->count()
            ];

            $currentWeek->addWeek()->startOfWeek();
        }

        // Recent LKH untuk dashboard
        $lkhTerakhir = Lkh::with(['user', 'kategoriKegiatan'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($lkh) {
                $waktuMulai = $lkh->waktu_mulai;
                $waktuSelesai = $lkh->waktu_selesai;
                
                // Handle waktu format
                if ($waktuMulai) {
                    if (is_string($waktuMulai)) {
                        $waktuMulai = substr($waktuMulai, 0, 5); // Extract HH:MM
                    } elseif ($waktuMulai instanceof \Carbon\Carbon || $waktuMulai instanceof \DateTime) {
                        $waktuMulai = $waktuMulai->format('H:i');
                    }
                }
                
                if ($waktuSelesai) {
                    if (is_string($waktuSelesai)) {
                        $waktuSelesai = substr($waktuSelesai, 0, 5); // Extract HH:MM
                    } elseif ($waktuSelesai instanceof \Carbon\Carbon || $waktuSelesai instanceof \DateTime) {
                        $waktuSelesai = $waktuSelesai->format('H:i');
                    }
                }
                
                return [
                    'id' => $lkh->id,
                    'uraian_kegiatan' => $lkh->uraian_kegiatan ?? '',
                    'waktu_mulai' => $waktuMulai,
                    'waktu_selesai' => $waktuSelesai,
                    'status' => $lkh->status ?? 'draft',
                    'tanggal' => $lkh->tanggal ? $lkh->tanggal->format('Y-m-d') : date('Y-m-d'),
                    'user' => [
                        'name' => $lkh->user->name ?? 'Unknown'
                    ]
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total_lkh' => $totalLkh,
                'total_pegawai' => $totalPegawai,
                'lkh_per_pegawai' => $lkhPerPegawai,
                'lkh_terakhir' => $lkhTerakhir,
                'statistik_mingguan' => $statistikMingguan,
                'bulan' => $bulan,
                'tahun' => $tahun,
            ]
        ]);
    }

    /**
     * Dashboard untuk Pegawai
     */
    private function dashboardPegawai($user, $bulan, $tahun)
    {
        // LKH bulan ini per status
        $lkhBulanIni = [
            'draft' => Lkh::byUser($user->id)
                ->byStatus('draft')
                ->byBulanTahun($bulan, $tahun)
                ->count(),
            'selesai' => Lkh::byUser($user->id)
                ->byStatus('selesai')
                ->byBulanTahun($bulan, $tahun)
                ->count(),
        ];

        // Total LKH bulan ini
        $totalLkhBulanIni = array_sum($lkhBulanIni);

        // LKH hari ini
        $lkhHariIni = Lkh::byUser($user->id)
            ->whereDate('tanggal', Carbon::today())
            ->with('kategoriKegiatan')
            ->orderBy('waktu_mulai', 'asc')
            ->get()
            ->map(function($lkh) {
                $waktuMulai = $lkh->waktu_mulai;
                $waktuSelesai = $lkh->waktu_selesai;
                
                // Handle waktu format
                if ($waktuMulai) {
                    if (is_string($waktuMulai)) {
                        $waktuMulai = substr($waktuMulai, 0, 5); // Extract HH:MM
                    } elseif ($waktuMulai instanceof \Carbon\Carbon || $waktuMulai instanceof \DateTime) {
                        $waktuMulai = $waktuMulai->format('H:i');
                    }
                }
                
                if ($waktuSelesai) {
                    if (is_string($waktuSelesai)) {
                        $waktuSelesai = substr($waktuSelesai, 0, 5); // Extract HH:MM
                    } elseif ($waktuSelesai instanceof \Carbon\Carbon || $waktuSelesai instanceof \DateTime) {
                        $waktuSelesai = $waktuSelesai->format('H:i');
                    }
                }
                
                return [
                    'id' => $lkh->id,
                    'uraian_kegiatan' => $lkh->uraian_kegiatan ?? '',
                    'waktu_mulai' => $waktuMulai,
                    'waktu_selesai' => $waktuSelesai,
                    'durasi' => $lkh->durasi ?? 0,
                    'status' => $lkh->status ?? 'draft',
                    'tanggal' => $lkh->tanggal ? $lkh->tanggal->format('Y-m-d') : date('Y-m-d'),
                    'kategori_kegiatan' => [
                        'nama' => $lkh->kategoriKegiatan->nama ?? null
                    ]
                ];
            });

        // LKH terakhir yang dibuat
        $lkhTerakhir = Lkh::byUser($user->id)
            ->with(['kategoriKegiatan', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Statistik per minggu bulan ini
        $startOfMonth = Carbon::create($tahun, $bulan, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        $statistikMingguan = [];
        $currentWeek = $startOfMonth->copy();
        
        while ($currentWeek->lte($endOfMonth)) {
            $weekEnd = $currentWeek->copy()->endOfWeek();
            if ($weekEnd->gt($endOfMonth)) {
                $weekEnd = $endOfMonth;
            }

            $statistikMingguan[] = [
                'minggu' => 'Minggu ' . $currentWeek->weekOfMonth,
                'tanggal' => $currentWeek->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                'total' => Lkh::byUser($user->id)
                    ->whereBetween('tanggal', [$currentWeek->format('Y-m-d'), $weekEnd->format('Y-m-d')])
                    ->count()
            ];

            $currentWeek->addWeek()->startOfWeek();
        }

        // Format lkh terakhir untuk JSON
        $lkhTerakhirFormatted = $lkhTerakhir->map(function($lkh) {
            $waktuMulai = $lkh->waktu_mulai;
            $waktuSelesai = $lkh->waktu_selesai;
            
            // Handle waktu format
            if ($waktuMulai) {
                if (is_string($waktuMulai)) {
                    $waktuMulai = substr($waktuMulai, 0, 5); // Extract HH:MM
                } elseif ($waktuMulai instanceof \Carbon\Carbon || $waktuMulai instanceof \DateTime) {
                    $waktuMulai = $waktuMulai->format('H:i');
                }
            }
            
            if ($waktuSelesai) {
                if (is_string($waktuSelesai)) {
                    $waktuSelesai = substr($waktuSelesai, 0, 5); // Extract HH:MM
                } elseif ($waktuSelesai instanceof \Carbon\Carbon || $waktuSelesai instanceof \DateTime) {
                    $waktuSelesai = $waktuSelesai->format('H:i');
                }
            }
            
            return [
                'id' => $lkh->id,
                'uraian_kegiatan' => $lkh->uraian_kegiatan ?? '',
                'waktu_mulai' => $waktuMulai,
                'waktu_selesai' => $waktuSelesai,
                'status' => $lkh->status ?? 'draft',
                'tanggal' => $lkh->tanggal ? $lkh->tanggal->format('Y-m-d') : date('Y-m-d'),
                'user' => [
                    'name' => $lkh->user->name ?? 'Unknown'
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'lkh_bulan_ini' => $lkhBulanIni,
                'total_lkh_bulan_ini' => $totalLkhBulanIni,
                'lkh_hari_ini' => $lkhHariIni,
                'lkh_terakhir' => $lkhTerakhirFormatted,
                'statistik_mingguan' => $statistikMingguan,
                'bulan' => $bulan,
                'tahun' => $tahun,
            ]
        ]);
    }

    /**
     * Get statistics for chart
     */
    public function statistics(Request $request)
    {
        $user = Auth::user();
        $tahun = $request->input('tahun', Carbon::now()->year);

        if ($user->isKepalaKua()) {
            // Statistik untuk semua pegawai
            $data = [];
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $data[] = [
                    'bulan' => Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F'),
                    'total' => Lkh::byBulanTahun($bulan, $tahun)
                        ->count(),
                ];
            }
        } else {
            // Statistik untuk pegawai tertentu
            $data = [];
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $data[] = [
                    'bulan' => Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F'),
                    'total' => Lkh::byUser($user->id)
                        ->byBulanTahun($bulan, $tahun)
                        ->count(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
