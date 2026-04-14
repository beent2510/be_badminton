<?php
namespace App\Repositories;

use App\Core\BasicRepository;
use App\Models\Payment;

class PaymentRepository extends BasicRepository
{
    public function __construct(Payment $payment)
    {
        parent::__construct($payment);
    }
    public function search($params = [])
    {
        $query = $this->model->newQuery();

        $userId = $params['user_id'] ?? request()->get('user_id');
        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }

         $paymentStatus = $params['payment_status'] ?? request()->get('payment_status');
        if (!empty($paymentStatus)) {
            $query->where('payment_status', $paymentStatus);
         }

         return $this->paging($query);
     }
     public function store( $data)
    {
        return parent::store($data);
    }
     public function update($id, $data = [])
    {
        return parent::update($id, $data);
     }
}