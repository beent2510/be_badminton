<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\CourtScheduleService;
use Illuminate\Http\Request;

class CourtScheduleController extends Controller
{
    public function __construct(CourtScheduleService $courtScheduleService)
    {
        $this->courtScheduleService = $courtScheduleService;   
    }

    public function index(Request $request)
    {
        return $this->courtScheduleService->all($request->all());
    }

    public function show($id)
    {
        return $this->courtScheduleService->show($id);
    }

    public function store(Request $request)
    {
        return $this->courtScheduleService->store($request->all());
    }

    public function update(Request $request, $id)
    {
        return $this->courtScheduleService->update($id, $request->all());
    }

    public function destroy($id)
    {
        return $this->courtScheduleService->destroy($id);
    }
}
