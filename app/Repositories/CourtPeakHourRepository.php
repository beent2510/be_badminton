<?php
namespace App\Repositories;

use App\Core\BasicRepository;
use App\Models\CourtPeakHour;

class CourtPeakHourRepository extends BasicRepository
{
    public function __construct(CourtPeakHour $courtPeakHour)
    {
        parent::__construct($courtPeakHour);
    }
    public function search($params = [])
    {
        $query = $this->model->newQuery();

        $keyword = $params['keyword'] ?? request()->get('keyword');
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('day_of_week', 'like', "%{$keyword}%")
                    ->orWhere('from_time', 'like', "%{$keyword}%")
                    ->orWhere('to_time', 'like', "%{$keyword}%");
            });
        }
    }
}