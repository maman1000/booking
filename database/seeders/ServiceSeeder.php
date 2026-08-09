<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Seed 6 layanan barbershop/salon + 2-3 jadwal per layanan.
     * Tanggal jadwal H+1..H+7 dari hari ini (Carbon::today()->addDays(n)).
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Potong Rambut Premium',
                'description' => 'Potong rambut dengan stylist berpengalaman, termasuk cuci dan styling.',
                'price' => 150000,
                'duration' => 60,
            ],
            [
                'name' => 'Cukur Jenggot',
                'description' => 'Perapihan jenggot dengan pisau cukur klasik dan hot towel.',
                'price' => 50000,
                'duration' => 30,
            ],
            [
                'name' => 'Hair Spa',
                'description' => 'Perawatan rambut intensif untuk rambut sehat dan berkilau.',
                'price' => 200000,
                'duration' => 90,
            ],
            [
                'name' => 'Creambath',
                'description' => 'Creambath tradisional dengan pijatan kepala yang menenangkan.',
                'price' => 120000,
                'duration' => 75,
            ],
            [
                'name' => 'Manicure',
                'description' => 'Perawatan kuku tangan lengkap: shaping, cuticle care, dan polish.',
                'price' => 100000,
                'duration' => 60,
            ],
            [
                'name' => 'Pedicure',
                'description' => 'Perawatan kuku kaki lengkap dengan foot soak dan scrub.',
                'price' => 120000,
                'duration' => 90,
            ],
        ];

        // Slot jam dan hari (H+n) per layanan — 2 sampai 3 jadwal per layanan.
        $slotTemplates = [
            [['days' => 1, 'start' => '09:00', 'end' => '10:00'], ['days' => 2, 'start' => '13:00', 'end' => '14:00'], ['days' => 4, 'start' => '16:00', 'end' => '17:00']],
            [['days' => 1, 'start' => '10:00', 'end' => '10:30'], ['days' => 3, 'start' => '14:00', 'end' => '14:30']],
            [['days' => 2, 'start' => '09:00', 'end' => '10:30'], ['days' => 5, 'start' => '13:00', 'end' => '14:30'], ['days' => 7, 'start' => '15:00', 'end' => '16:30']],
            [['days' => 3, 'start' => '10:00', 'end' => '11:15'], ['days' => 6, 'start' => '14:00', 'end' => '15:15']],
            [['days' => 2, 'start' => '11:00', 'end' => '12:00'], ['days' => 5, 'start' => '10:00', 'end' => '11:00']],
            [['days' => 4, 'start' => '09:00', 'end' => '10:30'], ['days' => 7, 'start' => '13:00', 'end' => '14:30']],
        ];

        foreach ($services as $index => $data) {
            $service = Service::create(array_merge($data, ['is_active' => true]));

            foreach ($slotTemplates[$index] as $slot) {
                ServiceSchedule::create([
                    'service_id' => $service->id,
                    'date' => Carbon::today()->addDays($slot['days'])->toDateString(),
                    'start_time' => $slot['start'],
                    'end_time' => $slot['end'],
                    'is_available' => true,
                ]);
            }
        }
    }
}
