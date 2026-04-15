<?php
namespace App\Repositories;

use App\Core\BasicRepository;
use App\Models\Court;

class CourtRepository extends BasicRepository
{
    public function __construct(Court $court)
    {
        parent::__construct($court);
    }
    public function search($params = [])
    {
        $query = $this->model->newQuery();

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
        if (!empty($date)) {
            $query->with(['bookings' => function ($q) use ($date) {
                // only get active/confirmed bookings, assuming 'status' != 'cancelled'
                $q->where('booking_date', $date)->where('status', '!=', 'cancelled');
            }]);
        } else {
            $query->with('bookings');
        }

        return $this->paging($query);
    }

    public function show($id)
    {
        $query = $this->model->newQuery();
        
        $date = request()->get('date');
        if (!empty($date)) {
            $query->with(['bookings' => function ($q) use ($date) {
                // only get active/confirmed bookings, assuming 'status' != 'cancelled'
                $q->where('booking_date', $date)->where('status', '!=', 'cancelled');
            }]);
        } else {
            $query->with('bookings');
        }

        return $query->findOrFail($id);
    }

    public function store($data)
    {
       if (isset($data['image_url']) && $data['image_url'] instanceof \Illuminate\Http\UploadedFile) {
        $path = $data['image_url']->store('images', 'public');
        $data['image_url'] = $path;
    } else {
        $data['image_url'] = null;
    }
        return parent::store($data);
    }
}