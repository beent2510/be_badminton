<?php

namespace App\Services;
use App\Repositories\PaymentRepository;

class PaymentService
{
    protected PaymentRepository $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function all($params = [])
    {
        return $this->paymentRepository->all($params);
    }

    public function search($params = [])
    {
        return $this->paymentRepository->search($params);
    }

    public function show($id)
    {
        return $this->paymentRepository->show($id);
    }

    public function store(array $data)
    {
        return $this->paymentRepository->store($data);
    }

    public function update($id, array $data = [])
    {
        return $this->paymentRepository->update($id, $data);
    }

    public function destroy($id)
    {
        return $this->paymentRepository->destroy($id);
    }
}