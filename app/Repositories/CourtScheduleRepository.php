<?php
namespace App\Repositories;

use App\Core\BasicRepository;
use App\Models\CourtSchedule;

class CourtScheduleRepository extends BasicRepository
{
    public function __construct(CourtSchedule $courtSchedule)
    {
        parent::__construct($courtSchedule);
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

        $courtId = $params['court_id'] ?? request()->get('court_id');
        if (!empty($courtId)) {
            $query->where('court_id', $courtId);
        }

         $dayOfWeek = $params['day_of_week'] ?? request()->get('day_of_week');
        if (!is_null($dayOfWeek) && $dayOfWeek !== '') {
            $query->where('day_of_week', $dayOfWeek);
        }

         $isActive = $params['is_active'] ?? request()->get('is_active');
        if (!is_null($isActive)) {
            $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }

         return $this->paging($query);
    }
     public function store( $data)
    {
        return parent::store($data);
    }
     public function update($id, $data = [])
    {
        return parent::update($id, $data);
    }
}