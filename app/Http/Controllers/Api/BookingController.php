<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceSchedule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    /**
     * Buat booking baru (customer).
     *
     * Alur:
     * 1. Validasi input (service_id, booking_date, start_time, end_time)
     * 2. Cek apakah waktu termasuk dalam jam operasional (service_schedules)
     * 3. Cek apakah ada booking lain yang overlap (bentrok)
     * 4. Hitung total harga dari durasi x price_per_hour
     * 5. Simpan booking
     */
//   public function store(Request $request): JsonResponse
//     {
//         try {
//             $request->validate([
//                 'service_id' => 'required|exists:services,id',
//                 'booking_date' => 'required|date|after_or_equal:today',
//                 'start_time' => 'required|date_format:H:i',
//                 'end_time' => 'required|date_format:H:i|after:start_time',
//                 'notes' => 'nullable|string',
//             ]);

//             $service = Service::findOrFail($request->service_id);
//             $bookingDate = Carbon::parse($request->booking_date);
//             $startTime = Carbon::parse($request->start_time);
//             $endTime = Carbon::parse($request->end_time);

//             // 1. Validasi jam operasional
//             // $dayOfWeek = $bookingDate->dayOfWeek;
//             $dayOfWeek = ($bookingDate->dayOfWeek + 6) % 7;

//             $schedule = ServiceSchedule::where('service_id', $service->id)
//                         ->where('day_of_week', $dayOfWeek)
//                         ->where('is_active', true)
//                         ->where('start_time', '<=', $request->start_time)
//                         ->where('end_time', '>=', $request->end_time)
//                         ->first();

//             if (!$schedule) {
//                 return response()->json([
//                     'message' => 'Lapangan tidak beroperasi pada hari/jam yang dipilih.'
//                 ], 422);
//             }

//             // 2. Cek bentrok
//             $conflict = Booking::where('service_id', $service->id)
//                         ->where('booking_date', $request->booking_date)
//                         ->where('status', '!=', 'canceled')
//                         ->where(function ($q) use ($request) {
//                             $q->whereBetween('start_time', [$request->start_time, $request->end_time])
//                             ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
//                             ->orWhere(function ($sub) use ($request) {
//                                 $sub->where('start_time', '<=', $request->start_time)
//                                     ->where('end_time', '>=', $request->end_time);
//                             });
//                         })->exists();

//             if ($conflict) {
//                 return response()->json([
//                     'message' => 'Maaf, lapangan sudah dibooking pada jam tersebut.'
//                 ], 422);
//             }

//             // 3. Hitung harga
//             $durationHours = $startTime->diffInHours($endTime);
//             if ($durationHours < 1) {
//                 return response()->json(['message' => 'Durasi minimal 1 jam.'], 422);
//             }
//             $totalPrice = $durationHours * $service->price_per_hour;

//             // 4. Generate kode booking
//             $bookingCode = 'FSL-' . now()->format('Ymd') . '-' . str_pad(
//                 Booking::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT
//             );

//             // 5. Simpan booking (transaksi)
//             $booking = DB::transaction(function () use ($request, $service, $totalPrice, $bookingCode) {
//                 return Booking::create([
//                     'user_id' => $request->user()->id,
//                     'service_id' => $service->id,
//                     'booking_date' => $request->booking_date,
//                     'start_time' => $request->start_time,
//                     'end_time' => $request->end_time,
//                     'total_price' => $totalPrice,
//                     'status' => 'pending',
//                     'payment_status' => 'unpaid',
//                     'notes' => $request->notes,
//                     'booking_code' => $bookingCode,
//                 ]);
//             });

//             // Jika transaksi berhasil, $booking akan terdefinisi
//             return response()->json([
//                 'message' => 'Booking berhasil dibuat.',
//                 'data' => $booking->load('service')
//             ], 201);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'error' => $e->getMessage(),
//                 'file' => $e->getFile(),
//                 'line' => $e->getLine(),
//                 'trace' => $e->getTraceAsString()
//             ], 500);
//         }
//     }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'service_id' => 'required|exists:services,id',
                'booking_date' => 'required|date|after_or_equal:today',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'notes' => 'nullable|string',
            ]);

            $service = Service::findOrFail($request->service_id);
            $bookingDate = Carbon::parse($request->booking_date);
            $startTime = Carbon::parse($request->start_time);
            $endTime = Carbon::parse($request->end_time);

            // 1. Validasi jam operasional (perbaiki mapping hari)
            $dayOfWeek = ($bookingDate->dayOfWeek + 6) % 7; // 0=Senin, 1=Selasa, ...
            $schedule = ServiceSchedule::where('service_id', $service->id)
                        ->where('day_of_week', $dayOfWeek)
                        ->where('is_active', true)
                        ->where('start_time', '<=', $request->start_time)
                        ->where('end_time', '>=', $request->end_time)
                        ->first();

            if (!$schedule) {
                return response()->json([
                    'message' => 'Lapangan tidak beroperasi pada hari/jam yang dipilih.'
                ], 422);
            }

            // 2. Cek bentrok (logika overlap yang benar)
            $conflict = Booking::where('service_id', $service->id)
                        ->where('booking_date', $request->booking_date)
                        ->where('status', '!=', 'canceled')
                        ->whereNotNull('status')
                        ->where(function ($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<', $endTime->format('H:i'))
                            ->where('end_time', '>', $startTime->format('H:i'));
                        })->exists();

            if ($conflict) {
                return response()->json([
                    'message' => 'Maaf, lapangan sudah dibooking pada jam tersebut.'
                ], 422);
            }

            // 3. Hitung harga
            $durationHours = $startTime->diffInHours($endTime);
            if ($durationHours < 1) {
                return response()->json(['message' => 'Durasi minimal 1 jam.'], 422);
            }
            $totalPrice = $durationHours * $service->price_per_hour;

            // 4. Generate kode booking
            $bookingCode = 'FSL-' . now()->format('Ymd') . '-' . str_pad(
                Booking::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT
            );

            // 5. Simpan booking
            $booking = DB::transaction(function () use ($request, $service, $totalPrice, $bookingCode) {
                return Booking::create([
                    'user_id' => $request->user()->id,
                    'service_id' => $service->id,
                    'booking_date' => $request->booking_date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'total_price' => $totalPrice,
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'notes' => $request->notes,
                    'booking_code' => $bookingCode,
                ]);
            });

            return response()->json([
                'message' => 'Booking berhasil dibuat.',
                'data' => $booking->load('service')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Daftar booking milik user yang sedang login, urut terbaru.
     * Bisa difilter dengan query parameter: ?status=, ?date=
     */
    public function my(Request $request): JsonResponse
    {
        $query = $request->user()
            ->bookings()
            ->with(['service', 'payment'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('date')) {
            $query->where('booking_date', $request->query('date'));
        }

        // Pagination
        $limit = $request->input('limit', 10);
        $bookings = $query->paginate($limit);

        return response()->json($bookings);
    }

    /**
     * Detail booking (untuk user sendiri atau admin).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $booking = Booking::with(['service', 'user', 'payment'])->findOrFail($id);

        // User biasa hanya bisa lihat milik sendiri
        if ($request->user()->role !== 'admin' && $booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json($booking);
    }

    /**
     * Batalkan booking (hanya pemilik, hanya jika status pending).
     * Booking akan diubah statusnya menjadi canceled.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $booking = Booking::findOrFail($id);

        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Bukan pemilik booking.'], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Booking tidak bisa dibatalkan karena sudah ' . $booking->status . '.'], 422);
        }

        $booking->update(['status' => 'canceled']);

        return response()->json($booking->fresh()->load('service'));
    }

    /**
     * Semua booking (admin). Filter: ?status=, ?date=, ?service_id=, ?search=
     * Dengan pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Booking::with(['user', 'service', 'payment'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('booking_date', $request->query('date'));
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->query('service_id'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->whereHas('service', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            });
        }

        $limit = $request->input('limit', 10);
        $bookings = $query->paginate($limit);

        return response()->json($bookings);
    }

    /**
     * Ubah status booking (admin).
     * Status yang diperbolehkan: confirmed, canceled, completed.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,canceled,completed'],
        ]);

        $booking = Booking::findOrFail($id);

        // Jika booking sudah completed, tidak boleh diubah lagi
        if ($booking->status === 'completed') {
            return response()->json(['message' => 'Booking yang sudah selesai tidak dapat diubah.'], 422);
        }

        $booking->update(['status' => $validated['status']]);

        return response()->json($booking->fresh()->load(['service', 'user']));
    }
}
