<?php
namespace App\Repositories;

use App\Core\BasicRepository;
use App\Models\Booking;

class BookingRepository extends BasicRepository
{
    public function __construct(Booking $booking)
    {
        parent::__construct($booking);
    }
    public function search($params = [])
    {
        $query = $this->model->newQuery()->with(['court.branch']);

        if (auth()->check() && auth()->user()->role === 'branch_admin') {
            $branchIds = auth()->user()->branches()->pluck('id')->toArray();
            $query->whereHas('court', function ($q) use ($branchIds) {
                $q->whereIn('branch_id', $branchIds);
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

        return $this->paging($query);
    }

    public function show($id)
    {
        $query = $this->model->newQuery()->with(['court.branch']);
        
        if (auth()->check() && auth()->user()->role === 'branch_admin') {
            $branchIds = auth()->user()->branches()->pluck('id')->toArray();
            $query->whereHas('court', function ($q) use ($branchIds) {
                $q->whereIn('branch_id', $branchIds);
            });
        }

        return $query->findOrFail($id);
    }

    public function isCourtAvailable($court_id, $booking_date, $start_time, $end_time)
    {
        $exists = $this->model->where('court_id', $court_id)
            ->where('booking_date', $booking_date)
            ->whereIn('status', ['pending', 'confirmed', 'paid'])
            ->where(function($q) use ($start_time, $end_time) {
                $q->where('start_time', '<', $end_time)
                  ->where('end_time', '>', $start_time);
            })
            ->exists();
            
        return !$exists;
    }
}