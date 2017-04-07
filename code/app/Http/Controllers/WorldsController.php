<?php

namespace Thaliak\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Thaliak\Models\World;

class WorldsController extends Controller
{
    public function index(): Collection
    {
        return World::all();
    }
}
