<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\CourtPeakHourService;
use Illuminate\Http\Request;

class CourtPeakHourController extends Controller
{
    public function __construct(CourtPeakHourService $courtPeakHourService)
    {
        $this->courtPeakHourService = $courtPeakHourService;   
    }

    public function index(Request $request)
    {
        return $this->courtPeakHourService->all($request->all());
    }

    public function show($id)
    {
        return $this->courtPeakHourService->show($id);
    }

    public function store(Request $request)
    {
        return $this->courtPeakHourService->store($request->all());
    }

    public function update(Request $request, $id)
    {
        return $this->courtPeakHourService->update($id, $request->all());
    }

    public function destroy($id)
    {
        return $this->courtPeakHourService->destroy($id);
    }
}
