<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City;

class CityController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/global/city",
     *     tags={"CIUDAD"},
     *     summary="LISTADO DE CIUDADES",
     *     operationId="getCiudades",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *         type="object"
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     *
     * Get list of cities
     *
     * @param Request $request
     * @return void
     */
    public function index()
    {
        return City::orderBy('name')->get();
    }

    public function show(City $city)
    {
        return $city;
    }
}
