<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    /**
     * Get Settings by Category
     */
    public function index($category = 'all')
    {
        $cacheKey = 'app_settings_' . $category;

        $settings = Cache::remember($cacheKey, 3600, function() use ($category) {
            $allSettings = [
                'absensi' => [
                    'jam_kerja_mulai' => '07:00',
                    'jam_kerja_selesai' => '16:00',
                    'toleransi_keterlambatan' => 15, // minutes
                    'radius_absensi' => 200, // meters
                    'koordinat_sekolah' => [
                        'latitude' => -6.2088,
                        'longitude' => 106.8456,
                    ],
                ],
                'qr' => [
                    'qr_expiry_minutes' => 5,
                    'qr_refresh_seconds' => 30,
                    'allow_manual_refresh' => true,
                ],
                'laporan' => [
                    'export_formats' => ['pdf', 'excel'],
                    'max_export_rows' => 1000,
                    'include_charts' => true,
                ],
                'notifikasi' => [
                    'enable_push' => true,
                    'enable_email' => false,
                    'notify_on_approval' => true,
                    'notify_on_schedule_change' => true,
                ],
                'sistem' => [
                    'nama_sekolah' => 'SMK Negeri 1 Jakarta',
                    'tahun_ajaran_aktif' => '2025/2026',
                    'semester_aktif' => 1,
                    'mode_maintenance' => false,
                ],
            ];

            if ($category === 'all') {
                return $allSettings;
            }

            return $allSettings[$category] ?? [];
        });

        if (empty($settings) && $category !== 'all') {
            return response()->json([
                'success' => false,
                'message' => 'Settings category not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'category' => $category,
            'data' => $settings,
        ]);
    }

    /**
     * Get Specific Setting
     */
    public function show($category, $key)
    {
        $settings = $this->index($category)->getData();

        if (!$settings->success) {
            return response()->json([
                'success' => false,
                'message' => 'Settings category not found',
            ], 404);
        }

        $value = $settings->data->{$key} ?? null;

        if ($value === null) {
            return response()->json([
                'success' => false,
                'message' => 'Setting key not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'category' => $category,
            'key' => $key,
            'value' => $value,
        ]);
    }

    /**
     * Clear Settings Cache
     */
    public function clearCache()
    {
        Cache::flush();

        return response()->json([
            'success' => true,
            'message' => 'Settings cache cleared',
        ]);
    }
}
