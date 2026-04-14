<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct($paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        return $this->paymentService->search($request->all());
    }

    public function show($id)
    {
        return $this->paymentService->show($id);
    }

    public function store(Request $request)
    {
        return $this->paymentService->store($request->all());
    }

    public function update(Request $request, $id)
    {
        if (!$this->paymentService->show($id)) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
        return $this->paymentService->update($id, $request->all());
    }

    public function destroy($id)
    {
        if (!$this->paymentService->show($id)) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
        $this->paymentService->destroy($id);
        return response()->json(['message' => 'Payment deleted successfully']);
    }
}
