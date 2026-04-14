<?php

namespace App\Http\Controllers\Api\User;

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
    public function bookCourt(Request $request, \App\Services\PromotionService $promotionService)
    {
        $data = $request->all();
        $result = $this->bookingService->bookCourt($data, $promotionService);
        if (!$result['success']) {
            return response()->json(['error' => $result['message']], 400);
        }
        return response()->json($result['booking']);
    }
}
