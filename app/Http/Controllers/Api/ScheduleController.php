<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Models\ServiceSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Service;        // <-- TAMBAHKAN INI


class ScheduleController extends Controller
{
    /**
     * Daftar jadwal operasional untuk satu layanan (publik).
     * Bisa difilter dengan ?day_of_week=0 (Senin) dst.
     */
public function byService(Request $request, int $id): JsonResponse
{
    try {
        // Cek apakah service ada
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $query = ServiceSchedule::where('service_id', $id)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time');

        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }

        $schedules = $query->get([
            'id',
            'service_id',
            'day_of_week',
            'start_time',
            'end_time',
            'is_active',
        ]);

        return response()->json($schedules);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
}

    /**
     * Semua jadwal + relasi service (admin), urut hari.
     */
    public function index(): JsonResponse
    {
        $schedules = ServiceSchedule::with('service')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return response()->json($schedules);
    }

    /**
     * Tambah jadwal baru (admin). is_active default true.
     */
    public function store(StoreScheduleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? true;

        $schedule = ServiceSchedule::create($data);

        return response()->json($schedule, 201);
    }

    /**
     * Ubah status aktif/tidak aktif jadwal (admin).
     */
    public function setAvailability(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $schedule = ServiceSchedule::findOrFail($id);
        $schedule->update(['is_active' => $validated['is_active']]);

        return response()->json($schedule);
    }
}
