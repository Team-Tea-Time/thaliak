<?php

namespace Thaliak\HTTP\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Thaliak\Models\World;

class WorldsController extends Controller
{
    public function index(): Collection
    {
        return World::all();
    }

    public function search(Request $request): Collection
    {
        $this->validate($request, ['name' => 'required|string']);
        return World::where('name', 'LIKE', "%{$request->name}%")->get();
    }
}
