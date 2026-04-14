<?php

namespace App\Services;
use App\Repositories\BranchRepository;

class BranchService
{
    protected BranchRepository $branchRepository;

    public function __construct(BranchRepository $branchRepository)
    {
        $this->branchRepository = $branchRepository;
    }

    public function all($params = [])
    {
        return $this->branchRepository->all($params);
    }

    public function search($params = [])
    {
        return $this->branchRepository->search($params);
    }

    public function show($id)
    {
        return $this->branchRepository->show($id);
    }

    public function store(array $data)
    {
        return $this->branchRepository->store($data);
    }

    public function update($id, array $data = [])
    {
        return $this->branchRepository->update($id, $data);
    }

    public function destroy($id)
    {
        return $this->branchRepository->destroy($id);
    }
}
