<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use App\Services\PromotionService;
use Illuminate\Http\Request;

class BookingController extends Controller

{
      public function __construct(BookingService $bookingService)
        {
            $this->bookingService = $bookingService;   
        }
    
        public function index(Request $request)
        {
            $params = $request->all();
            $params['user_id'] = $request->user()->id;
            return $this->bookingService->search($params);
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
        /**
     * Đặt sân, kiểm tra trống và áp dụng mã giảm giá nếu có
     */
    public function bookCourt(Request $request, PromotionService $promotionService)
    {
        $request->validate([
            'court_id' => 'required',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $data = $request->all();
        $result = $this->bookingService->bookCourt($data, $promotionService);
        if (!$result['success']) {
            return response()->json(['error' => $result['message']], 400);
        }
        return response()->json($result['booking']);
    }
}
