<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'price_per_hour', 'image', 'status'
    ];

    // Relasi ke jadwal operasional
    public function schedules()
    {
        return $this->hasMany(ServiceSchedule::class);
    }

    // Relasi ke booking
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Scope untuk yang tersedia
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
