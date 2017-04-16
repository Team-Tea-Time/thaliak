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

    public function getCharacter(Request $request): Character
    {
        return $this->lodestone->getCharacter($request->id);
    }

    public function findCharacters(Request $request): Collection
    {
        $this->validate($request, [
            'name' => 'required|string',
            'world_id' => 'sometimes|exists:worlds,id'
        ]);

        return $this->lodestone->findCharacters(
            $request->name,
            $request->world_id ? World::find($request->world_id)->name : ''
        );
    }

    public function getFreeCompany(Request $request): FreeCompany
    {
        return $this->lodestone->getFreeCompany($request->id);
    }

    public function getFreeCompanyMembers(Request $request): Collection
    {
        return $this->lodestone->getFreeCompanyMembers($request->id);
    }

    public function findFreeCompanies(Request $request): Collection
    {
        $this->validate($request, [
            'name' => 'required|string',
            'world_id' => 'sometimes|exists:worlds,id'
        ]);

        return $this->lodestone->findFreeCompanies(
            $request->name,
            $request->world_id ? World::find($request->world_id)->name : ''
        );
    }
}
