<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{SuratPeringatan, Guru, Absensi};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use PDF;

class SuratPeringatanController extends Controller
{
    /**
     * Display list of surat peringatan
     */
    public function index(Request $request)
    {
        $query = SuratPeringatan::with('guru');

        // Filter by tingkat
        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->tingkat);
        }

        // Filter by bulan
        if ($request->filled('bulan')) {
            $query->whereMonth('created_at', $request->bulan);
        }

        // Filter by tahun
        if ($request->filled('tahun')) {
            $query->whereYear('created_at', $request->tahun);
        }

        $suratPeringatanList = $query->latest()->paginate(15);

        // Statistics
        $totalSP = SuratPeringatan::count();
        $sp1Count = SuratPeringatan::where('tingkat', 1)->count();
        $sp2Count = SuratPeringatan::where('tingkat', 2)->count();
        $sp3Count = SuratPeringatan::where('tingkat', 3)->count();

        return view('admin.surat-peringatan.index', [
            'suratPeringatanList' => $suratPeringatanList,
            'totalSP' => $totalSP,
            'sp1Count' => $sp1Count,
            'sp2Count' => $sp2Count,
            'sp3Count' => $sp3Count,
        ]);
    }

    /**
     * Show generate form
     */
    public function generate()
    {
        $totalGuruAktif = Guru::count();
        $totalAbsensiBulanIni = Absensi::whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();
        $totalAlphaBulanIni = Absensi::whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->where('status_kehadiran', 'alpha')
            ->count();

        // Calculate potential SP
        $guruList = Guru::all();
        $potensiSP = 0;

        foreach ($guruList as $guru) {
            $alphaCount = Absensi::where('guru_id', $guru->id)
                ->whereMonth('tanggal', Carbon::now()->month)
                ->whereYear('tanggal', Carbon::now()->year)
                ->where('status_kehadiran', 'alpha')
                ->count();

            if ($alphaCount >= 3) {
                $potensiSP++;
            }
        }

        return view('admin.surat-peringatan.generate', [
            'totalGuruAktif' => $totalGuruAktif,
            'totalAbsensiBulanIni' => $totalAbsensiBulanIni,
            'totalAlphaBulanIni' => $totalAlphaBulanIni,
            'potensiSP' => $potensiSP,
        ]);
    }

    /**
     * Process generate surat peringatan
     */
    public function process(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020',
            'min_alpha_sp1' => 'required|integer|min:1',
            'min_alpha_sp2' => 'required|integer|min:1',
            'min_alpha_sp3' => 'required|integer|min:1',
        ]);

        try {
            $bulan = $request->bulan;
            $tahun = $request->tahun;
            $previewOnly = $request->has('preview_only');

            $periodeAwal = Carbon::create($tahun, $bulan, 1)->startOfMonth();
            $periodeAkhir = Carbon::create($tahun, $bulan, 1)->endOfMonth();

            $guruList = Guru::all();
            $generated = 0;

            foreach ($guruList as $guru) {
                // Check if already has SP for this period
                $existingSP = SuratPeringatan::where('guru_id', $guru->id)
                    ->whereBetween('periode_awal', [$periodeAwal, $periodeAkhir])
                    ->exists();

                if ($existingSP && !$previewOnly) {
                    continue;
                }

                // Count alpha
                $alphaCount = Absensi::where('guru_id', $guru->id)
                    ->whereBetween('tanggal', [$periodeAwal, $periodeAkhir])
                    ->where('status_kehadiran', 'alpha')
                    ->count();

                // Determine tingkat
                $tingkat = null;
                if ($alphaCount >= $request->min_alpha_sp3) {
                    $tingkat = 3;
                } elseif ($alphaCount >= $request->min_alpha_sp2) {
                    $tingkat = 2;
                } elseif ($alphaCount >= $request->min_alpha_sp1) {
                    $tingkat = 1;
                }

                if ($tingkat && !$previewOnly) {
                    $nomorSurat = $this->generateNomorSurat($tingkat, $tahun);

                    SuratPeringatan::create([
                        'guru_id' => $guru->id,
                        'nomor_surat' => $nomorSurat,
                        'tingkat' => $tingkat,
                        'periode_awal' => $periodeAwal,
                        'periode_akhir' => $periodeAkhir,
                        'total_alpha' => $alphaCount,
                        'keterangan' => "Surat Peringatan {$tingkat} atas ketidakhadiran tanpa keterangan sebanyak {$alphaCount} hari",
                    ]);

                    $generated++;
                }
            }

            if ($previewOnly) {
                return redirect()->route('admin.surat-peringatan.index')
                    ->with('success', 'Preview berhasil. Akan ada ' . $generated . ' surat peringatan yang di-generate.');
            }

            return redirect()->route('admin.surat-peringatan.index')
                ->with('success', "Berhasil generate {$generated} surat peringatan");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal generate surat peringatan: ' . $e->getMessage());
        }
    }

    /**
     * Preview PDF
     */
    public function preview($id)
    {
        $sp = SuratPeringatan::with('guru')->findOrFail($id);

        $pdf = PDF::loadView('pdf.surat-peringatan', ['sp' => $sp]);
        return $pdf->stream('SP-' . $sp->nomor_surat . '.pdf');
    }

    /**
     * Download PDF
     */
    public function download($id)
    {
        $sp = SuratPeringatan::with('guru')->findOrFail($id);

        $pdf = PDF::loadView('pdf.surat-peringatan', ['sp' => $sp]);
        return $pdf->download('SP-' . $sp->nomor_surat . '.pdf');
    }

    /**
     * Delete surat peringatan
     */
    public function destroy($id)
    {
        try {
            $sp = SuratPeringatan::findOrFail($id);
            $sp->delete();

            return response()->json([
                'success' => true,
                'message' => 'Surat peringatan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus surat peringatan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate nomor surat
     */
    private function generateNomorSurat($tingkat, $tahun)
    {
        $count = SuratPeringatan::where('tingkat', $tingkat)
            ->whereYear('created_at', $tahun)
            ->count() + 1;

        return sprintf('SP%d/%03d/%s/SDN', $tingkat, $count, $tahun);
    }
}
