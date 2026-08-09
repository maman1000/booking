<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            'method' => ['required', 'string', 'max:50'],
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

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'method' => $validated['method'],
            'paid_at' => now(),
        ]);

        return response()->json($payment, 201);
    }
}
