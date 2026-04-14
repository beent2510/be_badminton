<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct($bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        return $this->bookingService->search($request->all());
    }

    public function show($id)
    {
        return $this->bookingService->show($id);
    }

    public function store(Request $request)
    {
        return $this->bookingService->store($request->all());
    }

    public function update(Request $request, $id)
    {
        if (!$this->bookingService->show($id)) {
            return response()->json(['error' => 'Booking not found'], 404);
        }
        return $this->bookingService->update($id, $request->all());
    }

    public function destroy($id)
    {
        if (!$this->bookingService->show($id)) {
            return response()->json(['error' => 'Booking not found'], 404);
        }
        $this->bookingService->destroy($id);
        return response()->json(['message' => 'Booking deleted successfully']);
}
}
