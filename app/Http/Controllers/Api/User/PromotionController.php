<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Services\PromotionService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    public function index(Request $request)
    {
        return $this->promotionService->search($request->all());
    }

    public function show($id)
    {
        return $this->promotionService->show($id);
    }

    public function store(Request $request)
    {
        return $this->promotionService->store($request->all());
    }

    public function update(Request $request, $id)
    {
        if (!$this->promotionService->show($id)) {
            return response()->json(['error' => 'Promotion not found'], 404);
        }
        return $this->promotionService->update($id, $request->all());
    }

    public function destroy($id)
    {
        if (!$this->promotionService->show($id)) {
            return response()->json(['error' => 'Promotion not found'], 404);
        }
        $this->promotionService->destroy($id);
        return response()->json(['message' => 'Promotion deleted successfully']);
    }
    /**
     * Kiểm tra mã giảm giá còn hiệu lực không
     */
    public function checkCode(Request $request)
    {
        $code = $request->input('code');
        $result = $this->promotionService->checkCode($code);
        return response()->json($result);
    }

    /**
     * Áp dụng mã giảm giá cho tổng tiền
     */
    public function applyCode(Request $request)
    {
        $code = $request->input('code');
        $total = $request->input('total');
        $result = $this->promotionService->applyCode($code, $total);
        return response()->json($result);
    }
}
