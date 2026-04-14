<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\BranchService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }
    public function index(Request $request)
    {
        return $this->branchService->search($request->all());
    }
    public function show($id)
    {
        return $this->branchService->show($id);
    }
    public function store(Request $request)
    {
        return $this->branchService->store($request->all());
    }
    public function update(Request $request, $id)
    {
        if (!$this->branchService->show($id)) {
            return response()->json(['error' => 'Branch not found'], 404);
        }
        return $this->branchService->update($id, $request->all());
    }
    public function destroy($id)
    {
        if (!$this->branchService->show($id)) {
            return response()->json(['error' => 'Branch not found'], 404);
        }
        $this->branchService->destroy($id);
        return response()->json(['message' => 'Branch deleted successfully']);
    }
}
