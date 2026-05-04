<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'court_id',
        'payment_id',
        'promotion_id',
        'review_id',
        'customer_name',
        'booking_type',
        'booking_purpose',
        'booking_mode',
        'series_id',
        'booking_date',
        'start_time',
        'end_time',
        'status',
        'total_price',
        'note',
        'discount_amount',
        'final_price',
        'deposit_percent',
        'deposit_amount',
        'deposit_status'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function court()
    {
        return $this->belongsTo(Court::class);
    }
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }
    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }

    public function series()
    {
        return $this->belongsTo(BookingSeries::class, 'series_id');
    }
}
