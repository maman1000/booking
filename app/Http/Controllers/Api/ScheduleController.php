<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Models\ServiceSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Service;        // <-- TAMBAHKAN INI

use App\Models\Booking;
use Carbon\Carbon;



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
    // public function index(): JsonResponse
    // {
    //     $schedules = ServiceSchedule::with('service')
    //         ->orderBy('day_of_week')
    //         ->orderBy('start_time')
    //         ->get();

    //     return response()->json($schedules);
    // }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('limit', 10);
        $query = ServiceSchedule::with('service')
            ->orderBy('day_of_week')
            ->orderBy('start_time');

        // Tambahkan filter service_id jika ada
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        $schedules = $query->paginate($perPage);
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


    // public function availableSlots(Request $request, int $serviceId): JsonResponse
    // {
    //     // 1. Validasi parameter date
    //     $date = $request->query('date');
    //     if (!$date) {
    //         return response()->json(['message' => 'Parameter date wajib diisi.'], 422);
    //     }

    //     // 2. Konversi tanggal ke hari (0=Senin, 1=Selasa, ..., 6=Minggu)
    //     $carbonDate = Carbon::parse($date);
    //     // Carbon dayOfWeek: 0=Sunday, 1=Monday, ...
    //     // Kita mapping agar 0=Senin, 1=Selasa, ..., 6=Minggu
    //     $dayOfWeek = ($carbonDate->dayOfWeek + 6) % 7;

    //     // 3. Ambil jadwal operasional untuk layanan dan hari tersebut (aktif)
    //     $schedule = ServiceSchedule::where('service_id', $serviceId)
    //         ->where('day_of_week', $dayOfWeek)
    //         ->where('is_active', true)
    //         ->first();

    //     if (!$schedule) {
    //         // Tidak ada jadwal operasional -> tidak ada slot tersedia
    //         return response()->json([]);
    //     }

    //     // 4. Generate semua slot dengan interval (default 60 menit)
    //     $intervalMinutes = (int) $request->input('interval', 60);
    //     $start = Carbon::parse($schedule->start_time);
    //     $end = Carbon::parse($schedule->end_time);

    //     $slots = [];
    //     $current = clone $start;
    //     while ($current < $end) {
    //         $next = (clone $current)->addMinutes($intervalMinutes);
    //         if ($next > $end) break;
    //         $slots[] = [
    //             'start' => $current->format('H:i'),
    //             'end'   => $next->format('H:i'),
    //         ];
    //         $current = $next;
    //     }

    //     // 5. Ambil booking yang sudah ada untuk tanggal dan layanan ini (tidak canceled)
    //     $bookings = Booking::where('service_id', $serviceId)
    //         ->where('booking_date', $date)
    //         ->where('status', '!=', 'canceled')
    //         ->get(['start_time', 'end_time']);

    //     // 6. Tandai setiap slot apakah tersedia atau tidak
    //     foreach ($slots as &$slot) {
    //         $available = true;
    //         foreach ($bookings as $booking) {
    //             // Cek overlap: slot.start < booking.end AND slot.end > booking.start
    //             if ($slot['start'] < $booking->end_time && $slot['end'] > $booking->start_time) {
    //                 $available = false;
    //                 break;
    //             }
    //         }
    //         $slot['available'] = $available;
    //     }

    //     return response()->json($slots);
    // }

    public function availableSlots(Request $request, int $serviceId): JsonResponse
    {
        // 1. Validasi parameter date
        $date = $request->query('date');
        if (!$date) {
            return response()->json(['message' => 'Parameter date wajib diisi.'], 422);
        }

        // 2. Konversi tanggal ke hari (0=Senin, 1=Selasa, ..., 6=Minggu)
        $carbonDate = Carbon::parse($date);
        $dayOfWeek = ($carbonDate->dayOfWeek + 6) % 7;

        // 3. Ambil jadwal operasional untuk layanan dan hari tersebut (aktif)
        $schedule = ServiceSchedule::where('service_id', $serviceId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return response()->json([]);
        }

        // 4. Generate semua slot dengan interval (default 60 menit)
        $intervalMinutes = (int) $request->input('interval', 60);
        $start = Carbon::parse($schedule->start_time);
        $end = Carbon::parse($schedule->end_time);

        $slots = [];
        $current = clone $start;
        while ($current < $end) {
            $next = (clone $current)->addMinutes($intervalMinutes);
            if ($next > $end) break;
            $slots[] = [
                'start' => $current->format('H:i'),
                'end'   => $next->format('H:i'),
            ];
            $current = $next;
        }

        // 5. Ambil booking yang sudah ada (tidak canceled dan status tidak NULL)
        $bookings = Booking::where('service_id', $serviceId)
            ->where('booking_date', $date)
            ->whereNotNull('status') // tambahkan untuk mengabaikan status NULL
            ->where('status', '!=', 'canceled')
            ->get(['start_time', 'end_time']);

        // 6. Tandai setiap slot apakah tersedia atau tidak
        foreach ($slots as &$slot) {
            $available = true;
            $slotStart = Carbon::parse($slot['start']);
            $slotEnd   = Carbon::parse($slot['end']);

            foreach ($bookings as $booking) {
                // Konversi waktu booking ke Carbon (hanya waktu, tanggal hari ini agar perbandingan murni)
                $bookStart = Carbon::parse($booking->start_time);
                $bookEnd   = Carbon::parse($booking->end_time);

                // Overlap jika slot.start < booking.end AND slot.end > booking.start
                if ($slotStart->lt($bookEnd) && $slotEnd->gt($bookStart)) {
                    $available = false;
                    break;
                }
            }
            $slot['available'] = $available;
        }

        return response()->json($slots);
    }

    /**
 * Update jadwal (admin).
 */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $schedule = ServiceSchedule::findOrFail($id);
        $schedule->update($validated);

        return response()->json([
            'message' => 'Jadwal berhasil diperbarui.',
            'data' => $schedule
        ]);
    }
}
