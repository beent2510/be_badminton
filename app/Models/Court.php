<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    use HasFactory;
    protected $fillable = [
        'branch_id',
        'name',
        'description',
        'price_per_hour',
        'status',
        'image_url',
    ];
    public function courtPeakHours()
    {
        return $this->hasMany(CourtPeakHour::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    public function schedules()
    {
        return $this->hasMany(CourtSchedule::class);
    }
}
