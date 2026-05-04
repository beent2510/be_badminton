<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\BlockedTimeSlot;
use Illuminate\Http\Request;

class BlockedTimeSlotController extends Controller
{
    public function index(Request $request)
    {
        $query = BlockedTimeSlot::query()->where('is_active', true);

        if ($request->filled('date')) {
            $query->where('booking_date', $request->input('date'));
        }

        return response()->json($query->orderBy('start_time')->get());
    }
}
