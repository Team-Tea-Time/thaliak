<?php

namespace Thaliak\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Thaliak\Http\Controllers\Controller;
use Thaliak\Http\Lodestone\Api;
use Thaliak\Http\Lodestone\Character;
use Thaliak\Http\Lodestone\FreeCompany;
use Thaliak\Models\World;

class LodestoneController extends Controller
{
    protected $lodestone; // Api

    public function __construct(Api $lodestone)
    {
        $this->middleware('auth:api');
        $this->lodestone = $lodestone;
    }

    public function getCharacter(Request $request)
    {
        $lodestone = $this->lodestone->getCharacter($request->id);

        if (!$lodestone) {
            abort(404);
        }

        return $lodestone;
    }

    public function findCharacters(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'world_id' => 'sometimes|exists:worlds,id'
        ]);

        $lodestone = $this->lodestone->findCharacters(
            $request->name,
            $request->world_id ? World::find($request->world_id)->name : ''
        );

        if (!$lodestone){
            abort(404);
        }

        return $lodestone;
    }

    public function getFreeCompany(Request $request)
    {
        $lodestone = $this->lodestone->getFreeCompany($request->id);

        if (!$lodestone) {
             abort(404);
        }

        return $lodestone;
    }

    public function getFreeCompanyMembers(Request $request)
    {
        $lodestone = $this->lodestone->getFreeCompanyMembers($request->id);
    
        if (!$lodestone) {
            abort(404);
        }

        return $lodestone;
    }

    public function findFreeCompanies(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'world_id' => 'sometimes|exists:worlds,id'
        ]);

        $lodestone = $this->lodestone->findFreeCompanies(
            $request->name,
            $request->world_id ? World::find($request->world_id)->name : ''
        );

        if (!$lodestone) {
            abort(404);
        }

        return $lodestone;
    }
}
