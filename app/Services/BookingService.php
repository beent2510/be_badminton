<?php

namespace App\Services;
use App\Repositories\BookingRepository;

class BookingService

    
{
    protected BookingRepository $bookingRepository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    public function all($params = [])
    {
        return $this->bookingRepository->all($params);
    }

    public function search($params = [])
    {
        return $this->bookingRepository->search($params);
    }

    public function show($id)
    {
        return $this->bookingRepository->show($id);
    }

    public function store(array $data)
    {
        return $this->bookingRepository->store($data);
    }

    public function update($id, array $data = [])
    {
        return $this->bookingRepository->update($id, $data);
    }

    public function destroy($id)
    {
        return $this->bookingRepository->destroy($id);
    }
    /**
     * Kiểm tra sân có trống không
     */
    public function isCourtAvailable($court_id, $booking_date, $start_time, $end_time)
    {
        return $this->bookingRepository->isCourtAvailable($court_id, $booking_date, $start_time, $end_time);
    }

    /**
     * Đặt sân, có áp dụng mã giảm giá nếu có
     */
    public function bookCourt(array $data, $promotionService = null)
    {
        // Kiểm tra sân trống
        if (!$this->isCourtAvailable($data['court_id'], $data['booking_date'], $data['start_time'], $data['end_time'])) {
            return [
                'success' => false,
                'message' => 'Sân đã được đặt trong khung giờ này',
            ];
        }

        $total = $data['total_price'] ?? 0;
        $discount = 0;
        $final = $total;
        $promotion_id = null;
        if (!empty($data['promotion_code']) && $promotionService) {
            $result = $promotionService->applyCode($data['promotion_code'], $total);
            if ($result['success']) {
                $discount = $total - $result['total'];
                $final = $result['total'];
                $promotion_id = $result['promotion']->id;
            }
        }
        $data['discount_amount'] = $discount;
        $data['final_price'] = $final;
        $data['promotion_id'] = $promotion_id;

        $booking = $this->bookingRepository->store($data);
        
        if ($promotion_id && isset($result['promotion'])) {
            $result['promotion']->increment('usage_count');
        }
        
        return [
            'success' => true,
            'booking' => $booking,
        ];
    }
}
