<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BookingSeries;
use App\Models\CourtPeakHour;
use App\Models\Promotion;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
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
        return $this->bookGroup($data, $promotionService);
    }

    /**
     * Đặt sân theo nhóm (nhiều sân, nhiều khung giờ).
     */
    public function bookGroup(array $data, $promotionService = null)
    {
        $items = $data['items'] ?? [];
        if (empty($items) && !empty($data['court_id'])) {
            $items = [
                [
                    'court_id' => $data['court_id'],
                    'booking_date' => $data['booking_date'] ?? null,
                    'day_of_week' => $data['day_of_week'] ?? null,
                    'start_time' => $data['start_time'] ?? null,
                    'end_time' => $data['end_time'] ?? null,
                    'total_price' => $data['total_price'] ?? 0,
                ]
            ];
        }

        if (empty($items)) {
            return [
                'success' => false,
                'message' => 'Không có dữ liệu đặt sân',
            ];
        }

        $now = Carbon::now();
        foreach ($items as $item) {
            try {
                $bookingStart = Carbon::parse(($item['booking_date'] ?? '') . ' ' . ($item['start_time'] ?? ''));
            } catch (\Throwable $th) {
                return [
                    'success' => false,
                    'message' => 'Thời gian đặt sân không hợp lệ',
                ];
            }

            if ($bookingStart->lessThanOrEqualTo($now)) {
                return [
                    'success' => false,
                    'message' => 'Không thể đặt sân cho khung giờ đã qua',
                ];
            }

            if (!$this->isCourtAvailable($item['court_id'], $item['booking_date'], $item['start_time'], $item['end_time'])) {
                return [
                    'success' => false,
                    'message' => 'Sân đã được đặt trong khung giờ này',
                ];
            }
        }

        $total = 0;
        foreach ($items as $item) {
            $total += (float) ($item['total_price'] ?? 0);
        }

        $bookingType = $data['booking_type'] ?? 'adhoc';
        $fixedDiscount = 0;
        if ($bookingType === 'fixed') {
            $fixedDiscount = $total * 0.10;
            $total = max(0, $total - $fixedDiscount);
        }

        $hours = $this->calculateTotalHours($items);
        $courtsCount = count(array_unique(array_map(fn($i) => $i['court_id'], $items)));
        $hasPeakOverlap = $this->hasPeakOverlap($items);

        $discount = $fixedDiscount;
        $final = $total;
        $promotion_id = null;
        if (!empty($data['promotion_code']) && $promotionService) {
            $result = $promotionService->applyCode($data['promotion_code'], $total, [
                'total_hours' => $hours,
                'courts_count' => $courtsCount,
                'has_peak_overlap' => $hasPeakOverlap,
                'booking_purpose' => $data['booking_purpose'] ?? 'regular',
            ]);
            if ($result['success']) {
                $discount += $total - $result['total'];
                $final = $result['total'];
                $promotion_id = $result['promotion']->id;
            } else {
                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'Mã giảm giá không hợp lệ',
                ];
            }
        }

        $bookingMode = $data['booking_mode'] ?? 'single';
        if ($bookingMode === 'recurring' && ($data['payment_method'] ?? '') !== 'cash') {
            return [
                'success' => false,
                'message' => 'Đặt định kỳ hiện chỉ hỗ trợ thanh toán tiền mặt',
            ];
        }
        $depositPercent = (float) ($data['deposit_percent'] ?? 30);
        $useDeposit = (bool) ($data['use_deposit'] ?? false);
        if ($bookingType === 'fixed') {
            $useDeposit = true;
        }

        $paymentId = $data['payment_id'] ?? null;
        if (!empty($data['payment_method']) && $data['payment_method'] === 'cash') {
            $chargeAmount = $useDeposit ? ($final * $depositPercent / 100) : $final;
            $payment = Payment::create([
                'amount' => round($chargeAmount, 2),
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'user_id' => $data['user_id'],
            ]);
            $paymentId = $payment->id;
        }

        $seriesDates = [$items[0]['booking_date']];
        if ($bookingMode === 'recurring') {
            $seriesDates = $this->buildSeriesDates(
                $data['series_start_date'] ?? $items[0]['booking_date'],
                $data['series_end_date'] ?? $items[0]['booking_date'],
                $data['interval_unit'] ?? 'month',
                (int) ($data['interval_value'] ?? 1),
                (int) ($items[0]['day_of_week'] ?? 0)
            );
        }

        foreach ($seriesDates as $date) {
            foreach ($items as $item) {
                if (!$this->isCourtAvailable($item['court_id'], $date, $item['start_time'], $item['end_time'])) {
                    return [
                        'success' => false,
                        'message' => 'Sân đã được đặt trong khung giờ này',
                    ];
                }
            }
        }

        return DB::transaction(function () use ($data, $items, $bookingMode, $bookingType, $promotion_id, $discount, $final, $total, $depositPercent, $useDeposit, $paymentId, $seriesDates) {
            $seriesId = null;

            if ($bookingMode === 'recurring') {
                $series = BookingSeries::create([
                    'user_id' => $data['user_id'],
                    'start_date' => $data['series_start_date'] ?? $items[0]['booking_date'],
                    'end_date' => $data['series_end_date'] ?? $items[0]['booking_date'],
                    'day_of_week' => (int) ($items[0]['day_of_week'] ?? 0),
                    'start_time' => $items[0]['start_time'],
                    'end_time' => $items[0]['end_time'],
                    'interval_unit' => $data['interval_unit'] ?? 'month',
                    'interval_value' => (int) ($data['interval_value'] ?? 1),
                    'booking_type' => $bookingType,
                    'booking_purpose' => $data['booking_purpose'] ?? 'regular',
                    'note' => $data['note'] ?? null,
                    'is_active' => true,
                ]);

                $seriesId = $series->id;
            }

            $bookings = [];
            foreach ($seriesDates as $date) {
                $status = $this->resolveBookingStatus($paymentId);
                $booking = Booking::create([
                    'user_id' => $data['user_id'],
                    'court_id' => $items[0]['court_id'] ?? null,
                    'payment_id' => $paymentId,
                    'promotion_id' => $promotion_id,
                    'review_id' => null,
                    'customer_name' => $data['customer_name'] ?? null,
                    'booking_type' => $bookingType,
                    'booking_purpose' => $data['booking_purpose'] ?? 'regular',
                    'booking_mode' => $bookingMode,
                    'series_id' => $seriesId,
                    'booking_date' => $date,
                    'start_time' => $items[0]['start_time'],
                    'end_time' => $items[0]['end_time'],
                    'status' => $status,
                    'total_price' => $total,
                    'note' => $data['note'] ?? null,
                    'discount_amount' => $discount,
                    'final_price' => $final,
                    'deposit_percent' => $useDeposit ? $depositPercent : 0,
                    'deposit_amount' => $useDeposit ? round($final * $depositPercent / 100, 2) : 0,
                    'deposit_status' => $useDeposit ? ($status === 'paid' ? 'paid' : 'pending') : 'none',
                ]);

                foreach ($items as $item) {
                    BookingItem::create([
                        'booking_id' => $booking->id,
                        'court_id' => $item['court_id'],
                        'booking_date' => $date,
                        'day_of_week' => (int) ($item['day_of_week'] ?? 0),
                        'start_time' => $item['start_time'],
                        'end_time' => $item['end_time'],
                        'total_price' => (float) ($item['total_price'] ?? 0),
                    ]);
                }

                $bookings[] = $booking->load(['items.court', 'payment']);
            }

            if ($promotion_id) {
                Promotion::query()->where('id', $promotion_id)->increment('usage_count');
            }

            return [
                'success' => true,
                'booking' => count($bookings) === 1 ? $bookings[0] : $bookings,
            ];
        });
    }

    private function resolveBookingStatus(?int $paymentId): string
    {
        if ($paymentId) {
            $payment = Payment::find($paymentId);
            if ($payment && $payment->payment_status === 'paid') {
                return 'paid';
            }
        }
        return 'pending';
    }

    private function calculateTotalHours(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $start = Carbon::parse('2000-01-01 ' . $item['start_time']);
            $end = Carbon::parse('2000-01-01 ' . $item['end_time']);
            $total += max(0, $end->diffInMinutes($start) / 60);
        }
        return $total;
    }

    private function hasPeakOverlap(array $items): bool
    {
        foreach ($items as $item) {
            $day = (int) ($item['day_of_week'] ?? 0);
            $peakHours = CourtPeakHour::query()
                ->where('court_id', $item['court_id'])
                ->where('day_of_week', $day)
                ->get();

            foreach ($peakHours as $ph) {
                if ($item['start_time'] < $ph->to_time && $item['end_time'] > $ph->from_time) {
                    return true;
                }
            }
        }
        return false;
    }

    private function buildSeriesDates(string $startDate, string $endDate, string $unit, int $value, int $dayOfWeek): array
    {
        $dates = [];
        $current = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($unit === 'week') {
            if ($current->dayOfWeek !== $dayOfWeek) {
                $current->next($dayOfWeek);
            }
        }

        while ($current->lessThanOrEqualTo($end)) {
            $dates[] = $current->toDateString();
            if ($unit === 'week') {
                $current->addWeeks($value);
            } elseif ($unit === 'quarter') {
                $current->addMonths($value * 3);
            } elseif ($unit === 'year') {
                $current->addYears($value);
            } else {
                $current->addMonths($value);
            }
        }

        return $dates;
    }
}
