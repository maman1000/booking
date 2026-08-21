<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceSchedule;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Lapangan Futsal A',
                'description' => 'Lapangan futsal standar internasional dengan lantai kayu dan pencahayaan LED.',
                'price_per_hour' => 150000,
                'status' => 'available', // ← pakai status, bukan is_active
            ],
            [
                'name' => 'Lapangan Futsal B',
                'description' => 'Lapangan futsal dengan tribune penonton dan area parkir luas.',
                'price_per_hour' => 200000,
                'status' => 'available',
            ],
            [
                'name' => 'Lapangan Futsal C',
                'description' => 'Lapangan futsal premium dengan AC dan ruang ganti eksklusif.',
                'price_per_hour' => 250000,
                'status' => 'available',
            ],
            [
                'name' => 'Lapangan Futsal Mini',
                'description' => 'Lapangan futsal ukuran kecil, cocok untuk latihan dan anak-anak.',
                'price_per_hour' => 100000,
                'status' => 'available',
            ],
        ];

        foreach ($services as $serviceData) {
            $service = Service::create($serviceData);

            // Jadwal operasional (0=Senin, 1=Selasa, ..., 6=Minggu)
            $schedules = [
                ['day_of_week' => 0, 'start_time' => '08:00', 'end_time' => '22:00'],
                ['day_of_week' => 1, 'start_time' => '08:00', 'end_time' => '22:00'],
                ['day_of_week' => 2, 'start_time' => '08:00', 'end_time' => '22:00'],
                ['day_of_week' => 3, 'start_time' => '08:00', 'end_time' => '22:00'],
                ['day_of_week' => 4, 'start_time' => '08:00', 'end_time' => '22:00'],
                ['day_of_week' => 5, 'start_time' => '07:00', 'end_time' => '23:00'], // Sabtu
                ['day_of_week' => 6, 'start_time' => '07:00', 'end_time' => '23:00'], // Minggu
            ];

            foreach ($schedules as $schedule) {
                ServiceSchedule::create([
                    'service_id' => $service->id,
                    'day_of_week' => $schedule['day_of_week'],
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                    'is_active' => true, // jika kolom is_active ada di service_schedules
                ]);
            }
        }
    }
}
