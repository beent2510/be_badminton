<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffSchedule;
use Illuminate\Http\Request;

class StaffScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = StaffSchedule::query()->with(['staff', 'branch']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        if ($request->filled('staff_user_id')) {
            $query->where('staff_user_id', $request->input('staff_user_id'));
        }

        if ($request->filled('date')) {
            $query->where('work_date', $request->input('date'));
        }

        return response()->json($query->orderBy('work_date')->orderBy('start_time')->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_user_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'work_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $schedule = StaffSchedule::create($request->only([
            'staff_user_id',
            'branch_id',
            'work_date',
            'start_time',
            'end_time',
            'note',
        ]));

        return response()->json($schedule->load(['staff', 'branch']), 201);
    }

    public function update(Request $request, $id)
    {
        $schedule = StaffSchedule::findOrFail($id);
        $schedule->update($request->only([
            'staff_user_id',
            'branch_id',
            'work_date',
            'start_time',
            'end_time',
            'note',
        ]));

        return response()->json($schedule->load(['staff', 'branch']));
    }

    public function destroy($id)
    {
        $schedule = StaffSchedule::findOrFail($id);
        $schedule->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
