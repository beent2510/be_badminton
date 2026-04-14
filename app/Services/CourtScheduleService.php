<?php

namespace App\Services;
use App\Repositories\CourtScheduleRepository;

class CourtScheduleService
{
    protected CourtScheduleRepository $courtScheduleRepository;

    public function __construct(CourtScheduleRepository $courtScheduleRepository)
    {
        $this->courtScheduleRepository = $courtScheduleRepository;
    }

    public function all($params = [])
    {
        return $this->courtScheduleRepository->all($params);
    }

    public function search($params = [])
    {
        return $this->courtScheduleRepository->search($params);
    }

    public function show($id)
    {
        return $this->courtScheduleRepository->show($id);
    }

    public function store(array $data)
    {
        return $this->courtScheduleRepository->store($data);
    }

    public function update($id, array $data = [])
    {
        return $this->courtScheduleRepository->update($id, $data);
    }

    public function destroy($id)
    {
        return $this->courtScheduleRepository->destroy($id);
    }
}
