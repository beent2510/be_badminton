<?php

namespace App\Http\Controllers\Api\User;
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

    public function courts(Request $request, $id, \App\Services\CourtService $courtService)
    {
        $params = $request->all();
        $params['branch_id'] = $id;
        return $courtService->search($params);
    }
}
