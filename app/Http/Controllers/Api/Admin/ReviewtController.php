<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReviewtController extends Controller
{
    protected $reviewService;

    public function __construct($reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function index(Request $request)
    {
        return $this->reviewService->search($request->all());
    }

    public function show($id)
    {
        return $this->reviewService->show($id);
    }

    public function store(Request $request)
    {
        return $this->reviewService->store($request->all());
    }

    public function update(Request $request, $id)
    {
        if (!$this->reviewService->show($id)) {
            return response()->json(['error' => 'Review not found'], 404);
        }
        return $this->reviewService->update($id, $request->all());
    }

    public function destroy($id)
    {
        if (!$this->reviewService->show($id)) {
            return response()->json(['error' => 'Review not found'], 404);
        }
        $this->reviewService->destroy($id);
        return response()->json(['message' => 'Review deleted successfully']);
}
}
