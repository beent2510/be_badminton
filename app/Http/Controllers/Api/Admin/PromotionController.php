<?php

namespace App\Http\Controllers\Api\Admin;

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
        $data = $this->mapFields($request->all());
        return $this->promotionService->store($data);
    }

    public function update(Request $request, $id)
    {
        $data = $this->mapFields($request->all());
        return $this->promotionService->update($id, $data);
    }

    public function destroy($id)
    {
        $this->promotionService->destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }

    /**
     * Map FE fields (value/type/usage_limit) -> DB fields (discount_value/discount_type/max_usage)
     */
    private function mapFields(array $data): array
    {
        if (isset($data['value'])) {
            $data['discount_value'] = $data['value'];
            unset($data['value']);
        }
        if (isset($data['type'])) {
            // FE sends 'percent', DB expects 'percentage'
            $data['discount_type'] = $data['type'] === 'percent' ? 'percentage' : 'fixed';
            unset($data['type']);
        }
        if (isset($data['usage_limit'])) {
            $data['max_usage'] = $data['usage_limit'] ?: null;
            unset($data['usage_limit']);
        }
        return $data;
    }
}
