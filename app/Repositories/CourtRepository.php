<?php

namespace App\Repositories;

use App\Core\BasicRepository;
use App\Models\Booking;
use App\Models\Court;
use Carbon\Carbon;

class CourtRepository extends BasicRepository
{
    private const PAYMENT_HOLD_MINUTES = 5;

    public function __construct(Court $court)
    {
        parent::__construct($court);
    }

    private function expireStalePendingBookings(): void
    {
        $expireBefore = Carbon::now()->subMinutes(self::PAYMENT_HOLD_MINUTES);

        Booking::query()
            ->where('status', 'pending')
            ->where('created_at', '<=', $expireBefore)
            ->where(function ($q) {
                $q->whereNull('payment_id')
                    ->orWhereHas('payment', function ($p) {
                        $p->where('payment_status', '!=', 'paid');
                    });
            })
            ->update(['status' => 'cancelled']);
    }

    public function search($params = [])
    {
        $this->expireStalePendingBookings();

        $query = $this->model->newQuery();

        if (auth()->check() && auth()->user()->role === 'branch_admin') {
            $branchIds = auth()->user()->branches()->pluck('id')->toArray();
            $query->whereIn('branch_id', $branchIds);
        }

        $keyword = $params['keyword'] ?? request()->get('keyword');
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%");
            });
        }

        $branchId = $params['branch_id'] ?? request()->get('branch_id');
        if (!empty($branchId)) {
            $query->where('branch_id', $branchId);
        }

        $date = $params['date'] ?? request()->get('date');
        $dayOfWeek = $params['day_of_week'] ?? request()->get('day_of_week');

        $withRelations = ['courtPeakHours' => function ($q) use ($dayOfWeek) {
            if (isset($dayOfWeek)) {
                $q->where('day_of_week', $dayOfWeek);
            }
        }];

        $withRelations['schedules'] = function ($q) use ($dayOfWeek) {
            if (isset($dayOfWeek)) {
                $q->where('day_of_week', $dayOfWeek)->where('is_active', true);
            } else {
                $q->where('is_active', true);
            }
        };

        if (!empty($date)) {
            $withRelations['bookings'] = function ($q) use ($date) {
                $q->where('booking_date', $date)
                    ->whereIn('status', ['confirmed', 'paid'])
                    ->orWhere(function ($pendingPaid) use ($date) {
                        $pendingPaid->where('booking_date', $date)
                            ->where('status', 'pending')
                            ->whereHas('payment', function ($p) {
                                $p->where('payment_status', 'paid');
                            });
                    });
            };
            $withRelations['bookingItems'] = function ($q) use ($date) {
                $q->where('booking_date', $date)
                    ->whereHas('booking', function ($b) {
                        $b->whereIn('status', ['confirmed', 'paid'])
                            ->orWhere(function ($pendingPaid) {
                                $pendingPaid->where('status', 'pending')
                                    ->whereHas('payment', function ($p) {
                                        $p->where('payment_status', 'paid');
                                    });
                            });
                    });
            };
        } else {
            $withRelations[] = 'bookings';
        }
        $query->with($withRelations);

        return $this->paging($query);
    }

    public function show($id)
    {
        $this->expireStalePendingBookings();

        $query = $this->model->newQuery();

        if (auth()->check() && auth()->user()->role === 'branch_admin') {
            $branchIds = auth()->user()->branches()->pluck('id')->toArray();
            $query->whereIn('branch_id', $branchIds);
        }

        $date = request()->get('date');
        $dayOfWeek = request()->get('day_of_week');

        $withRelations = ['courtPeakHours' => function ($q) use ($dayOfWeek) {
            if (isset($dayOfWeek)) {
                $q->where('day_of_week', $dayOfWeek);
            }
        }];

        $withRelations['schedules'] = function ($q) use ($dayOfWeek) {
            if (isset($dayOfWeek)) {
                $q->where('day_of_week', $dayOfWeek)->where('is_active', true);
            } else {
                $q->where('is_active', true);
            }
        };

        if (!empty($date)) {
            $withRelations['bookings'] = function ($q) use ($date) {
                $q->where('booking_date', $date)
                    ->whereIn('status', ['confirmed', 'paid'])
                    ->orWhere(function ($pendingPaid) use ($date) {
                        $pendingPaid->where('booking_date', $date)
                            ->where('status', 'pending')
                            ->whereHas('payment', function ($p) {
                                $p->where('payment_status', 'paid');
                            });
                    });
            };
            $withRelations['bookingItems'] = function ($q) use ($date) {
                $q->where('booking_date', $date)
                    ->whereHas('booking', function ($b) {
                        $b->whereIn('status', ['confirmed', 'paid'])
                            ->orWhere(function ($pendingPaid) {
                                $pendingPaid->where('status', 'pending')
                                    ->whereHas('payment', function ($p) {
                                        $p->where('payment_status', 'paid');
                                    });
                            });
                    });
            };
        } else {
            $withRelations[] = 'bookings';
        }
        $query->with($withRelations);

        return $query->findOrFail($id);
    }

    public function store($data)
    {
        return parent::store($data);
    }
}
