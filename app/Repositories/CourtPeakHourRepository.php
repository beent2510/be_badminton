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

        if (auth()->check() && auth()->user()->role === 'branch_admin') {
            $branchIds = auth()->user()->branches()->pluck('id')->toArray();
            $query->whereHas('court', function ($q) use ($branchIds) {
                $q->whereIn('branch_id', $branchIds);
            });
        }

        $keyword = $params['keyword'] ?? request()->get('keyword');
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('day_of_week', 'like', "%{$keyword}%")
                    ->orWhere('from_time', 'like', "%{$keyword}%")
                    ->orWhere('to_time', 'like', "%{$keyword}%");
            });
        }

        $courtId = $params['court_id'] ?? request()->get('court_id');
        if (!empty($courtId)) {
            $query->where('court_id', $courtId);
        }

        $branchId = $params['branch_id'] ?? request()->get('branch_id');
        if (!empty($branchId)) {
            $query->whereHas('court', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $dayOfWeek = $params['day_of_week'] ?? request()->get('day_of_week');
        if (!is_null($dayOfWeek) && $dayOfWeek !== '') {
            $query->where('day_of_week', $dayOfWeek);
        }

        $query->with('court');
        return $this->paging($query);
    }
}