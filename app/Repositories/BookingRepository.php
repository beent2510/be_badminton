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
        $query = $this->model->newQuery();

        $keyword = $params['keyword'] ?? request()->get('keyword');
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_email', 'like', "%{$keyword}%");
            });
        }

        return $this->paging($query);
    }
}