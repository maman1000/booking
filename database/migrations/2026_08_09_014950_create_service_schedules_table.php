<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->tinyInteger('day_of_week'); // 0=Senin, 1=Selasa, ... 6=Minggu
            $table->time('start_time'); // Jam buka (contoh: 08:00)
            $table->time('end_time');   // Jam tutup (contoh: 22:00)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Satu lapangan hanya boleh punya 1 jadwal per hari
            $table->unique(['service_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_schedules');
    }
};
