<?php

namespace App\Repositories;

use App\Core\BasicRepository;
use App\Models\BlockedTimeSlot;
use App\Models\Booking;
use App\Models\BookingItem;
use Carbon\Carbon;

class BookingRepository extends BasicRepository
{
    private const PAYMENT_HOLD_MINUTES = 5;

    public function __construct(Booking $booking)
    {
        parent::__construct($booking);
    }

    public function expireStalePendingBookings(?int $userId = null): void
    {
        $expireBefore = Carbon::now()->subMinutes(self::PAYMENT_HOLD_MINUTES);

        $query = $this->model->newQuery()
            ->where('status', 'pending')
            ->where('created_at', '<=', $expireBefore)
            ->where(function ($q) {
                $q->whereNull('payment_id')
                    ->orWhereHas('payment', function ($p) {
                        $p->where('payment_status', '!=', 'paid');
                    });
            });

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $query->update(['status' => 'cancelled']);
    }

    public function search($params = [])
    {
        if (isset($params['user_id'])) {
            $this->expireStalePendingBookings((int) $params['user_id']);
        } else {
            $this->expireStalePendingBookings();
        }

        $query = $this->model->newQuery()->with([
            'court.branch',
            'items.court.branch',
            'payment',
            'user',
        ]);

        if (auth()->check() && auth()->user()->role === 'branch_admin') {
            $branchIds = auth()->user()->branches()->pluck('id')->toArray();
            $query->where(function ($q) use ($branchIds) {
                $q->whereHas('court', function ($c) use ($branchIds) {
                    $c->whereIn('branch_id', $branchIds);
                })->orWhereHas('items.court', function ($c) use ($branchIds) {
                    $c->whereIn('branch_id', $branchIds);
                });
            });
        }

        if (isset($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }

        $keyword = $params['keyword'] ?? request()->get('keyword');
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_email', 'like', "%{$keyword}%");
            });
        }

        // Newest bookings first for stable pagination order.
        $query->orderByDesc('created_at')->orderByDesc('id');

        return $this->paging($query);
    }

    public function show($id)
    {
        $query = $this->model->newQuery()->with([
            'court.branch',
            'items.court.branch',
            'payment',
            'user',
        ]);

        if (auth()->check() && auth()->user()->role === 'branch_admin') {
            $branchIds = auth()->user()->branches()->pluck('id')->toArray();
            $query->where(function ($q) use ($branchIds) {
                $q->whereHas('court', function ($c) use ($branchIds) {
                    $c->whereIn('branch_id', $branchIds);
                })->orWhereHas('items.court', function ($c) use ($branchIds) {
                    $c->whereIn('branch_id', $branchIds);
                });
            });
        }

        return $query->findOrFail($id);
    }

    public function isCourtAvailable($court_id, $booking_date, $start_time, $end_time)
    {
        $this->expireStalePendingBookings();

        $blocked = BlockedTimeSlot::query()
            ->where('booking_date', $booking_date)
            ->where('is_active', true)
            ->where('start_time', '<', $end_time)
            ->where('end_time', '>', $start_time)
            ->exists();

        if ($blocked) {
            return false;
        }

        $exists = Booking::query()
            ->where(function ($q) use ($court_id, $booking_date, $start_time, $end_time) {
                $q->where(function ($slot) use ($court_id, $booking_date, $start_time, $end_time) {
                    $slot->where('court_id', $court_id)
                        ->where('booking_date', $booking_date)
                        ->where('start_time', '<', $end_time)
                        ->where('end_time', '>', $start_time);
                })->orWhereHas('items', function ($itemQ) use ($court_id, $booking_date, $start_time, $end_time) {
                    $itemQ->where('court_id', $court_id)
                        ->where('booking_date', $booking_date)
                        ->where('start_time', '<', $end_time)
                        ->where('end_time', '>', $start_time);
                });
            })
            ->where(function ($q) {
                $q->whereIn('status', ['confirmed', 'paid'])
                    ->orWhere(function ($pendingPaid) {
                        $pendingPaid->where('status', 'pending')
                            ->whereHas('payment', function ($p) {
                                $p->where('payment_status', 'paid');
                            });
                    });
            })
            ->exists();

        return !$exists;
    }
}
