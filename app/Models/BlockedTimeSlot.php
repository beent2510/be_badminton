<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedTimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_date',
        'start_time',
        'end_time',
        'reason',
        'is_active',
    ];
}
