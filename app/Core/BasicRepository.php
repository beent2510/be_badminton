<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BasicRepository implements RepositoryInterface
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all($params = [])
    {
        $query = $this->model->newQuery();
        return $this->paging($query);
    }

    public function search($params = [])
    {
        $query = $this->model->newQuery();
        return $this->paging($query);
    }

    public function show($id)
    {
        return $this->model->find($id);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function store($data)
    {
        return $this->model->create($data);
    }

    /**
     * @param $model
     * @param array $data
     * @return mixed
     */
    public function update($model, $data = [])
    {
        if (!is_object($model)) {
            $model = $this->model->find($model);
        }
        if ($model) {
            $model->update($data);
        }
        return $model;
    }

    public function destroy($id)
    {
        return $this->model->destroy($id);
    }

    public function paging(Builder $query): array
    {
        $perPage = request()->get('items_per_page', config('site.paging', 10));
        $paginate = $query->paginate($perPage);
        return [
            'items' => $paginate->items(),
            'paginate' => [
                'current_page' => $paginate->currentPage(),
                'per_page' => $paginate->perPage(),
                'total' => $paginate->total(),
            ],
        ];
    }
}
