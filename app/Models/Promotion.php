<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'description',
        'discount_value',
        'discount_type',
        'usage_count',
        'start_date',
        'end_date',
        'max_usage',
        'is_active',
    ];
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function userPromotions()
    {
        return $this->hasMany(UserPromotion::class);
    }

    /**
     * Kiểm tra mã giảm giá còn hiệu lực không
     */
    public function isValid()
    {
        $now = now();
        if (!$this->is_active) return false;
        if ($this->start_date && $now->lt($this->start_date)) return false;
        if ($this->end_date && $now->gt($this->end_date)) return false;
        if ($this->max_usage !== null && $this->usage_count >= $this->max_usage) return false;
        return true;
    }

    /**
     * Áp dụng mã giảm giá cho tổng tiền
     * @param float $totalAmount
     * @return float
     */
    public function apply($totalAmount)
    {
        if (!$this->isValid()) {
            return $totalAmount;
        }
        if ($this->discount_type === 'percentage' || $this->discount_type === 'percent') {
            return max(0, $totalAmount - ($totalAmount * $this->discount_value / 100));
        } elseif ($this->discount_type === 'fixed') {
            return max(0, $totalAmount - $this->discount_value);
        }
        return $totalAmount;
    }
}
