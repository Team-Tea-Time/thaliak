<?php

namespace Thaliak\HTTP\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Thaliak\Models\DataCentre;

class DataCentresController extends Controller
{
    public function index(): Collection
    {
        return DataCentre::with('worlds')->get();
    }
}
