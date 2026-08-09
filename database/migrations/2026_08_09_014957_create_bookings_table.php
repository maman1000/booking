<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // Relasi ke user & lapangan (LANGSUNG ke service, bukan schedule)
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();

            // Waktu booking yang dipilih pelanggan
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');

            // Status Booking
            $table->enum('status', ['pending', 'confirmed', 'canceled', 'completed'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');

            // Harga total (dihitung otomatis di controller)
            $table->decimal('total_price', 10, 2);

            $table->text('notes')->nullable();
            $table->string('booking_code')->unique()->nullable(); // opsional untuk struk

            $table->timestamps();

            // Index agar query pengecekan bentrok lebih cepat
            $table->index(['service_id', 'booking_date', 'start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
