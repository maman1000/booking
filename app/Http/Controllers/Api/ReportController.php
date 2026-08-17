<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Ringkasan laporan (admin):
     * - total_bookings: semua booking.
     * - total_revenue: total_price dari booking yang punya payment (tidak ada kolom amount).
     * - bookings_by_status: jumlah per status (pending/done/batal).
     * - top_services: 5 layanan teratas berdasar jumlah booking berstatus pending/done.
     */
    public function summary(): JsonResponse
    {
        $totalBookings = Booking::count();

        $totalRevenue = (int) DB::table('payments')
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->sum('bookings.total_price');

        $bookingsByStatus = Booking::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Pastikan semua status selalu ada di response dan nilai count di-cast ke int.
        // $bookingsByStatus = collect(['pending' => 0, 'done' => 0, 'batal' => 0])
        //     ->merge($bookingsByStatus)
        //     ->map(fn ($total) => (int) $total);

        $bookingsByStatus = collect([
            'pending' => 0,
            'confirmed' => 0,
            'canceled' => 0,
            'completed' => 0,
        ])
            ->merge($bookingsByStatus)
            ->map(fn ($total) => (int) $total);

        $topServices = Booking::query()
            // ->join('service_schedules', 'bookings.schedule_id', '=', 'service_schedules.id')
            // ->join('services', 'service_schedules.service_id', '=', 'services.id')
            ->join('services', 'bookings.service_id', '=', 'services.id')
            ->whereIn('bookings.status', [
                'pending',
                'confirmed',
                'completed',
            ])
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('total_bookings')
            ->limit(5)
            ->get([
                'services.id as service_id',
                'services.name',
                DB::raw('count(bookings.id) as total_bookings'),
                DB::raw('sum(bookings.total_price) as revenue'),
            ])
            ->map(fn ($row) => [
                'service_id' => (int) $row->service_id,
                'name' => $row->name,
                'total_bookings' => (int) $row->total_bookings,
                'revenue' => (int) $row->revenue,
            ]);

        return response()->json([
            'total_bookings' => $totalBookings,
            'total_revenue' => $totalRevenue,
            'bookings_by_status' => $bookingsByStatus,
            'top_services' => $topServices,
        ]);
    }

    /**
     * Daftar booking untuk laporan (admin).
     * Filter opsional ?from=&to= berdasarkan booking_date, urut booking_date desc.
     */
    public function bookings(Request $request): JsonResponse
    {
        $query = Booking::with(['user', 'service', 'payment'])
            ->orderBy('booking_date', 'desc');

        if ($request->filled('from')) {
            $query->whereDate('booking_date', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('booking_date', '<=', $request->query('to'));
        }

        return response()->json($query->get());
    }
}
