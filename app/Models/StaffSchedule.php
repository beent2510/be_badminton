<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_user_id',
        'branch_id',
        'work_date',
        'start_time',
        'end_time',
        'note',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
