<?php

namespace App\Services;
use App\Repositories\ReviewRepository;

class ReviewService
{
    protected ReviewRepository $reviewRepository;

    public function __construct(ReviewRepository $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    public function all($params = [])
    {
        return $this->reviewRepository->all($params);
    }

    public function search($params = [])
    {
        return $this->reviewRepository->search($params);
    }

    public function show($id)
    {
        return $this->reviewRepository->show($id);
    }

    public function store(array $data)
    {
        return $this->reviewRepository->store($data);
    }

    public function update($id, array $data = [])
    {
        return $this->reviewRepository->update($id, $data);
    }

    public function destroy($id)
    {
        return $this->reviewRepository->destroy($id);
    }
}