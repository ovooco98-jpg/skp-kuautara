<?php

namespace App\Http\Controllers;

use App\Models\Lkh;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        $startOfMonth = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();
        
        // Optimasi: Gabungkan semua count queries dalam satu query
        $lkhStats = Lkh::selectRaw('status, COUNT(*) as total')
            ->byBulanTahun($bulan, $tahun)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $totalLkh = [
            'draft' => $lkhStats['draft'] ?? 0,
            'selesai' => $lkhStats['selesai'] ?? 0,
        ];

        // Optimasi: Total pegawai aktif - cache lebih lama (rarely changes)
        $totalPegawai = \Cache::remember('total_pegawai_aktif', 86400, function () {
            return User::aktif()
                ->where('role', '!=', 'kepala_kua')
                ->count();
        });

        // LKH per pegawai - optimasi dengan select spesifik
        $lkhPerPegawai = Lkh::selectRaw('user_id, COUNT(*) as total')
            ->byBulanTahun($bulan, $tahun)
            ->groupBy('user_id')
            ->with('user:id,name,nip,jabatan')
            ->get();

        // LKH hari ini - optimasi tanpa eager loading yang tidak perlu
        $lkhHariIni = Lkh::whereDate('tanggal', Carbon::today())
            ->count();

        // Optimasi statistik mingguan: Query database sekali dengan aggregate
        $weeklyStats = \DB::table('lkh')
            ->select(\DB::raw('WEEK(tanggal, 1) - WEEK(?, 1) + 1 as week_num'), \DB::raw('COUNT(*) as total'))
            ->whereBetween('tanggal', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->groupBy('week_num')
            ->pluck('total', 'week_num');

        $statistikMingguan = [];
        $currentWeek = $startOfMonth->copy();
        $weekNumber = 1;
        
        while ($currentWeek->lte($endOfMonth)) {
            $weekEnd = $currentWeek->copy()->endOfWeek();
            if ($weekEnd->gt($endOfMonth)) {
                $weekEnd = $endOfMonth;
            }

            $statistikMingguan[] = [
                'minggu' => 'Minggu ' . $weekNumber,
                'tanggal' => $currentWeek->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                'total' => $weeklyStats->get($weekNumber, 0) // Ambil dari hasil aggregate
            ];

            $currentWeek->addWeek()->startOfWeek();
            $weekNumber++;
        }

        // Recent LKH untuk dashboard - optimasi dengan select spesifik
        $lkhTerakhir = Lkh::select('id', 'uraian_kegiatan', 'waktu_mulai', 'waktu_selesai', 'status', 'tanggal', 'user_id')
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($lkh) {
                $waktuMulai = $lkh->waktu_mulai;
                $waktuSelesai = $lkh->waktu_selesai;
                
                // Handle waktu format
                if ($waktuMulai) {
                    if (is_string($waktuMulai)) {
                        $waktuMulai = substr($waktuMulai, 0, 5);
                    } elseif ($waktuMulai instanceof \Carbon\Carbon || $waktuMulai instanceof \DateTime) {
                        $waktuMulai = $waktuMulai->format('H:i');
                    }
                }
                
                if ($waktuSelesai) {
                    if (is_string($waktuSelesai)) {
                        $waktuSelesai = substr($waktuSelesai, 0, 5);
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
        $startOfMonth = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();
        
        // Optimasi: Gabungkan count queries
        $lkhStats = Lkh::selectRaw('status, COUNT(*) as total')
            ->byUser($user->id)
            ->byBulanTahun($bulan, $tahun)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $lkhBulanIni = [
            'draft' => $lkhStats['draft'] ?? 0,
            'selesai' => $lkhStats['selesai'] ?? 0,
        ];

        // Total LKH bulan ini
        $totalLkhBulanIni = array_sum($lkhBulanIni);

        // LKH hari ini - optimasi dengan select spesifik
        $lkhHariIni = Lkh::select('id', 'uraian_kegiatan', 'waktu_mulai', 'waktu_selesai', 'status', 'tanggal', 'kategori_kegiatan_id')
            ->byUser($user->id)
            ->whereDate('tanggal', Carbon::today())
            ->with('kategoriKegiatan:id,nama')
            ->orderBy('waktu_mulai', 'asc')
            ->get()
            ->map(function($lkh) {
                $waktuMulai = $lkh->waktu_mulai;
                $waktuSelesai = $lkh->waktu_selesai;
                
                // Handle waktu format
                if ($waktuMulai) {
                    if (is_string($waktuMulai)) {
                        $waktuMulai = substr($waktuMulai, 0, 5);
                    } elseif ($waktuMulai instanceof \Carbon\Carbon || $waktuMulai instanceof \DateTime) {
                        $waktuMulai = $waktuMulai->format('H:i');
                    }
                }
                
                if ($waktuSelesai) {
                    if (is_string($waktuSelesai)) {
                        $waktuSelesai = substr($waktuSelesai, 0, 5);
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

        // LKH terakhir yang dibuat - optimasi dengan select spesifik
        $lkhTerakhir = Lkh::select('id', 'uraian_kegiatan', 'waktu_mulai', 'waktu_selesai', 'status', 'tanggal', 'user_id')
            ->byUser($user->id)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Optimasi statistik mingguan: Query sekali untuk semua data
        $allLkhInMonth = Lkh::select('tanggal')
            ->byUser($user->id)
            ->whereBetween('tanggal', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get()
            ->groupBy(function($lkh) {
                return Carbon::parse($lkh->tanggal)->weekOfMonth;
            });
        
        $statistikMingguan = [];
        $currentWeek = $startOfMonth->copy();
        $weekNumber = 1;
        
        while ($currentWeek->lte($endOfMonth)) {
            $weekEnd = $currentWeek->copy()->endOfWeek();
            if ($weekEnd->gt($endOfMonth)) {
                $weekEnd = $endOfMonth;
            }

            $statistikMingguan[] = [
                'minggu' => 'Minggu ' . $weekNumber,
                'tanggal' => $currentWeek->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                'total' => $allLkhInMonth->get($weekNumber, collect())->count()
            ];

            $currentWeek->addWeek()->startOfWeek();
            $weekNumber++;
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

        // Optimasi: Query sekali untuk semua bulan
        if ($user->isKepalaKua()) {
            $stats = Lkh::selectRaw('MONTH(tanggal) as bulan, COUNT(*) as total')
                ->whereYear('tanggal', $tahun)
                ->groupBy('bulan')
                ->pluck('total', 'bulan')
                ->toArray();
        } else {
            $stats = Lkh::selectRaw('MONTH(tanggal) as bulan, COUNT(*) as total')
                ->byUser($user->id)
                ->whereYear('tanggal', $tahun)
                ->groupBy('bulan')
                ->pluck('total', 'bulan')
                ->toArray();
        }

        // Build response dengan data yang sudah di-query
        $data = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $data[] = [
                'bulan' => Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F'),
                'total' => $stats[$bulan] ?? 0,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
