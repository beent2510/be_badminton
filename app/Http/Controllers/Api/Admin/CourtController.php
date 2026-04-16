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
        $data = $request->except('image_url');
        if ($request->hasFile('image_url')) {
            $path = $request->file('image_url')->store('images', 'public');
            $data['image_url'] = $path;
        }
        return $this->courtService->store($data);
    }
    public function update(Request $request, $id)
    {
        $data = $request->except('image_url');
        if ($request->hasFile('image_url')) {
            $path = $request->file('image_url')->store('images', 'public');
            $data['image_url'] = $path;
        }
        return $this->courtService->update($id, $data);
    }
    public function destroy($id)
    {
        return $this->courtService->destroy($id);
    }
}