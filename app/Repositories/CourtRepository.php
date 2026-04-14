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

        return $this->paging($query);
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