<?php

namespace Thaliak\HTTP\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Thaliak\HTTP\Controllers\Controller;
use Thaliak\HTTP\Lodestone\API;
use Thaliak\HTTP\Lodestone\Character;
use Thaliak\HTTP\Lodestone\FreeCompany;
use Thaliak\Models\World;

class LodestoneController extends Controller
{
    protected $lodestone;

    public function __construct(API $lodestone)
    {
        $this->middleware('auth:api');
        $this->lodestone = $lodestone;
    }

    public function findCharacters(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'worldname' => 'sometimes|exists:worlds,name',
            'page' => 'sometimes|int',
        ]);

        $results = $this->lodestone->findCharacters(
            $request->name,
            $request->worldname,
            $request->page
        );

        if (!$results) {
            abort(404);
        }

        return $results;
    }

    public function getCharacter(Request $request)
    {
        $character = $this->lodestone->getCharacter($request->id);

        if (!$character) {
            abort(404);
        }

        return $character;
    }

    public function findFreeCompanies(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'worldname' => 'sometimes|exists:worlds,name',
            'page' => 'sometimes|int',
        ]);

        $results = $this->lodestone->findFreeCompanies(
            $request->name,
            $request->worldname,
            $request->page
        );

        if (!$results) {
            abort(404);
        }

        return $results;
    }

    public function getFreeCompany(Request $request)
    {
        $freecompany = $this->lodestone->getFreeCompany($request->id);

        if (!$freecompany) {
             abort(404);
        }

        return $freecompany;
    }

    public function getFreeCompanyMembers(Request $request)
    {
        $results = $this->lodestone->getFreeCompanyMembers($request->id);

        if (!$results) {
            abort(404);
        }

        return $results;
    }
}
