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
        $model = method_exists($this->bookingRepository, 'getModel')
            ? $this->bookingRepository->getModel()
            : (property_exists($this->bookingRepository, 'model') ? $this->bookingRepository->model : null);
        if (!$model) {
            throw new 
untimeException('BookingRepository missing model instance');
        }
        $exists = $model->where('court_id', $court_id)
            ->where('booking_date', $booking_date)
            ->whereIn('status', ['pending', 'confirmed', 'paid'])
            ->where(function($q) use ($start_time, $end_time) {
                $q->whereBetween('start_time', [$start_time, $end_time])
                  ->orWhereBetween('end_time', [$start_time, $end_time])
                  ->orWhere(function($q2) use ($start_time, $end_time) {
                      $q2->where('start_time', '<=', $start_time)
                          ->where('end_time', '>=', $end_time);
                  });
            })
            ->exists();
        return !$exists;
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
        return [
            'success' => true,
            'booking' => $booking,
        ];
    }
}
