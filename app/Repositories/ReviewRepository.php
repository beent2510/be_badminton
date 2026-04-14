<?php
namespace App\Repositories;

use App\Core\BasicRepository;
use App\Models\Review;

class ReviewRepository extends BasicRepository
{
    public function __construct(Review $review)
    {
        parent::__construct($review);
    }

    public function search($params = [])
    {
        $query = $this->model->newQuery();

        $courtId = $params['court_id'] ?? request()->get('court_id');
        if (!empty($courtId)) {
            $query->where('court_id', $courtId);
        }

         $userId = $params['user_id'] ?? request()->get('user_id');
        if (!empty($userId)) {
            $query->where('user_id', $userId);
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

