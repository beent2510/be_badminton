<?php

namespace App\Services;
use App\Repositories\CourtRepository;

class CourtService
{
    protected CourtRepository $courtRepository;

    public function __construct(CourtRepository $courtRepository)
    {
        $this->courtRepository = $courtRepository;
    }

    public function all($params = [])
    {
        return $this->courtRepository->all($params);
    }

    public function search($params = [])
    {
        return $this->courtRepository->search($params);
    }

    public function show($id)
    {
        return $this->courtRepository->show($id);
    }

    public function store(array $data)
    {
        return $this->courtRepository->store($data);
    }

    public function update($id, array $data = [])
    {
        return $this->courtRepository->update($id, $data);
    }

    public function destroy($id)
    {
        return $this->courtRepository->destroy($id);
    }
}
