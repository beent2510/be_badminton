<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtPeakHour extends Model
{
    use HasFactory;
    protected $fillable = [
        'court_id',
        'day_of_week',
        'from_time',
        'to_time',
        'price_peak_hour'
    ];
    public function court()
    {
        return $this->belongsTo(Court::class);
    }
}
