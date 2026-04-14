<?php

namespace App\Core;

interface RepositoryInterface
{
    public function all($params = []);

    public function search($params = []);

    public function show($id);

    public function store($data);

    public function update($model, $data = []);

    public function destroy($id);
}
