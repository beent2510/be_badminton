<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'amount',
        'payment_method',
        'payment_status',
        'paid_at',
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function booking()
    {
        return $this->hasOne(Booking::class);
    }
}
