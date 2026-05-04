<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingSeries extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'day_of_week',
        'start_time',
        'end_time',
        'interval_unit',
        'interval_value',
        'booking_type',
        'booking_purpose',
        'note',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'series_id');
    }
}
