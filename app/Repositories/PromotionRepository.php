<?php
namespace App\Repositories;

use App\Core\BasicRepository;
use App\Models\Promotion;

class PromotionRepository extends BasicRepository
{
    public function __construct(Promotion $promotion)
    {
        parent::__construct($promotion);
    }
    public function search($params = [])
    {
        $query = $this->model->newQuery();

        $keyword = $params['keyword'] ?? request()->get('keyword');
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        return $this->paging($query);
    }
    /**
     * Tìm promotion theo code
     */
    public function findByCode($code)
    {
        return $this->model->where('code', $code)->first();
    }
}