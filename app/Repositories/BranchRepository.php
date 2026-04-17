<?php
namespace App\Repositories;

use App\Core\BasicRepository;
use App\Models\Branch;

class BranchRepository extends BasicRepository
{
    public function __construct(Branch $branch)
    {
        parent::__construct($branch);
    }
    public function search($params = [])
    {
        $query = $this->model->newQuery();

        if (auth()->check() && auth()->user()->role === 'branch_admin') {
            $query->where('user_id', auth()->id());
        }

        $keyword = $params['keyword'] ?? request()->get('keyword');
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%");
            });
        }

        return $this->paging($query);
    }

    public function show($id)
    {
        $query = $this->model->with(['reviews' => function($q) {
            $q->where('is_visible', true)->with('user')->latest();
        }]);

        if (auth()->check() && auth()->user()->role === 'branch_admin') {
            $query->where('user_id', auth()->id());
        }

        return $query->findOrFail($id);
    }
}