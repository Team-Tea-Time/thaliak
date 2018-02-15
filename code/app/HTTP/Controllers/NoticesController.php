<?php

namespace Thaliak\HTTP\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Slugify;
use Thaliak\Models\Notice;

class NoticesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'get']]);
    }

    public function index(Request $request): LengthAwarePaginator
    {
        return Notice::orderBy('created_at', 'desc')->paginate();
    }
}
