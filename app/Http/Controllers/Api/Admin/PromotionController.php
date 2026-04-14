<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\PromotionService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
     public function __construct(PromotionService  $promotionService)
    {
        $this->promotionService = $promotionService;   
    }

    public function index(Request $request)
    {
        return $this->promotionService->all($request->all());
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
        return $this->promotionService->update($id, $request->all());
    }

    public function destroy($id)
    {
        return $this->promotionService->destroy($id);
    }

}
