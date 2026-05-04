<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedTimeSlot;
use Illuminate\Http\Request;

class BlockedTimeSlotController extends Controller
{
    public function index(Request $request)
    {
        $query = BlockedTimeSlot::query();

        if ($request->filled('date')) {
            $query->where('booking_date', $request->input('date'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->input('is_active'));
        }

        return response()->json($query->orderBy('booking_date')->orderBy('start_time')->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'reason' => 'nullable|string',
        ]);

        $slot = BlockedTimeSlot::create([
            'booking_date' => $request->booking_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'is_active' => $request->input('is_active', true),
        ]);

        return response()->json($slot, 201);
    }

    public function update(Request $request, $id)
    {
        $slot = BlockedTimeSlot::findOrFail($id);

        $data = $request->only(['booking_date', 'start_time', 'end_time', 'reason', 'is_active']);
        $slot->update($data);

        return response()->json($slot);
    }

    public function destroy($id)
    {
        $slot = BlockedTimeSlot::findOrFail($id);
        $slot->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
