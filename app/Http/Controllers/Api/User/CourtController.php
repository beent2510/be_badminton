<?php

namespace App\Http\Controllers\Api\User;

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

   
}
