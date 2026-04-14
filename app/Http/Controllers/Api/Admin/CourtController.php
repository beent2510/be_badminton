<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\CourtService;
use Illuminate\Http\Request;

class CourtController extends Controller
{
    public function __construct(CourtService $courtService)
    {
        $this->courtService = $courtService;
    }

    public function index(Request $request)
    {
        return $this->courtService->search($request->all());
    }
    public function show($id)
    {
        return $this->courtService->show($id);
    }
    public function store(Request $request)
    {
        return $this->courtService->store($request->all());
    }
    public function update(Request $request, $id)
    {
        return $this->courtService->update($id, $request->all());
    }
    public function destroy($id)
    {
        return $this->courtService->destroy($id);
    }
}