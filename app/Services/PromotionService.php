<?php

namespace App\Services;

use App\Repositories\PromotionRepository;

class PromotionService
{
    protected PromotionRepository $promotionRepository;

    public function __construct(PromotionRepository $promotionRepository)
    {
        $this->promotionRepository = $promotionRepository;
    }
    /**
     * Kiểm tra mã giảm giá còn hiệu lực không
     */
    public function checkCode($code)
    {
        $promotion = $this->promotionRepository->findByCode($code);
        if (!$promotion) {
            return [
                'valid' => false,
                'message' => 'Mã giảm giá không tồn tại',
            ];
        }
        if (!$promotion->isValid()) {
            return [
                'valid' => false,
                'message' => 'Mã giảm giá không còn hiệu lực',
            ];
        }
        return [
            'valid' => true,
            'promotion' => $promotion,
        ];
    }

    /**
     * Áp dụng mã giảm giá cho tổng tiền
     */
    public function applyCode($code, $totalAmount, array $context = [])
    {
        $promotion = $this->promotionRepository->findByCode($code);
        if (!$promotion || !$promotion->isValid()) {
            return [
                'success' => false,
                'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn',
                'total' => $totalAmount,
            ];
        }

        $minHours = (float) ($promotion->min_hours ?? 0);
        $totalHours = (float) ($context['total_hours'] ?? 0);
        if ($minHours > 0 && $totalHours < $minHours) {
            return [
                'success' => false,
                'message' => 'Khuyến mãi yêu cầu tối thiểu ' . $minHours . ' giờ',
                'total' => $totalAmount,
            ];
        }

        $minCourts = (int) ($promotion->min_courts ?? 1);
        $courtsCount = (int) ($context['courts_count'] ?? 1);
        if ($minCourts > 1 && $courtsCount < $minCourts) {
            return [
                'success' => false,
                'message' => 'Khuyến mãi áp dụng cho đặt từ ' . $minCourts . ' sân',
                'total' => $totalAmount,
            ];
        }

        $promoCategory = $promotion->promo_category ?? 'general';
        if ($promoCategory === 'org_event' && ($context['booking_purpose'] ?? 'regular') !== 'tournament') {
            return [
                'success' => false,
                'message' => 'Khuyến mãi chỉ áp dụng cho đặt giải đấu',
                'total' => $totalAmount,
            ];
        }

        if ($promoCategory === 'peak_hour' && empty($context['has_peak_overlap'])) {
            return [
                'success' => false,
                'message' => 'Khuyến mãi chỉ áp dụng cho giờ cao điểm',
                'total' => $totalAmount,
            ];
        }

        if (!empty($promotion->requires_peak_overlap) && empty($context['has_peak_overlap'])) {
            return [
                'success' => false,
                'message' => 'Khuyến mãi yêu cầu giờ cao điểm',
                'total' => $totalAmount,
            ];
        }

        $discounted = $promotion->apply($totalAmount);
        return [
            'success' => true,
            'total' => $discounted,
            'promotion' => $promotion,
        ];
    }

    public function all($params = [])
    {
        return $this->promotionRepository->all($params);
    }

    public function search($params = [])
    {
        return $this->promotionRepository->search($params);
    }

    public function show($id)
    {
        return $this->promotionRepository->show($id);
    }

    public function store(array $data)
    {
        return $this->promotionRepository->store($data);
    }

    public function update($id, array $data = [])
    {
        return $this->promotionRepository->update($id, $data);
    }

    public function destroy($id)
    {
        return $this->promotionRepository->destroy($id);
    }
}
