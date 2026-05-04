<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{

    public function __construct(BookingService $bookingService)
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
        $booking = $this->bookingService->show($id);
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }
        if (($request->input('status') === 'cancelled') && ($booking->booking_type === 'fixed')) {
            return response()->json(['error' => 'Đặt cố định không thể hủy'], 400);
        }
        return $this->bookingService->update($id, $request->all());
    }

    public function destroy($id)
    {
        $booking = $this->bookingService->show($id);
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }
        if ($booking->booking_type === 'fixed') {
            return response()->json(['error' => 'Đặt cố định không thể hủy'], 400);
        }
        $this->bookingService->destroy($id);
        return response()->json(['message' => 'Booking deleted successfully']);
    }
}
