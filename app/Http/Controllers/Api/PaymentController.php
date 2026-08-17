<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Bayar booking (customer, hanya pemilik booking).
     *
     * Validasi bisnis:
     * - Booking harus milik user yang login (403).
     * - Status booking harus pending (422).
     * - Booking belum punya payment (422).
     */

public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        'booking_id' => ['required', 'exists:bookings,id'],
        'method' => ['required', 'string', 'in:transfer,cash,e-wallet'],
    ]);

    $booking = Booking::with('payment')->findOrFail($validated['booking_id']);

    if ($booking->user_id !== $request->user()->id) {
        return response()->json(['message' => 'Bukan pemilik booking.'], 403);
    }

    if ($booking->status !== 'pending') {
        return response()->json(['message' => 'Booking tidak bisa dibayar.'], 422);
    }

    if ($booking->payment) {
        return response()->json(['message' => 'Booking sudah dibayar.'], 422);
    }

    $payment = DB::transaction(function () use ($booking, $validated) {
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'method' => $validated['method'],
            'amount' => $booking->total_price, // ← ini penting
            'payment_date' => now(), // atau 'paid_at' sesuai nama kolom
        ]);

        $booking->update([
            'payment_status' => 'paid',
        ]);

        return $payment;
    });

    return response()->json([
        'message' => 'Pembayaran berhasil.',
        'payment' => $payment,
        'booking' => $booking->fresh(),
    ], 201);
}

}
