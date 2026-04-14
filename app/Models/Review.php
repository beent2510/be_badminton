<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    protected $fillable = [
        'court_id',
        'user_id',
        'rating',
        'comment',
        'is_visible',
    ];
    public function court()
    {
        return $this->belongsTo(Court::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function booking()
    {
        return $this->hasOne(Booking::class);
    }
}
