<?php

namespace App\Services;
use App\Repositories\CourtPeakHourRepository;

class CourtPeakHourService
{
    protected CourtPeakHourRepository $courtPeakHourRepository;

    public function __construct(CourtPeakHourRepository $courtPeakHourRepository)
    {
        $this->courtPeakHourRepository = $courtPeakHourRepository;
    }

    public function all($params = [])
    {
        return $this->courtPeakHourRepository->all($params);
    }

    public function search($params = [])
    {
        return $this->courtPeakHourRepository->search($params);
    }

    public function show($id)
    {
        return $this->courtPeakHourRepository->show($id);
    }

    public function store(array $data)
    {
        return $this->courtPeakHourRepository->store($data);
    }

    public function update($id, array $data = [])
    {
        return $this->courtPeakHourRepository->update($id, $data);
    }

    public function destroy($id)
    {
        return $this->courtPeakHourRepository->destroy($id);
    }
}
