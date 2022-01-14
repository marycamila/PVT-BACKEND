<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Global\City;

class CityController extends Controller
{
    public function index()
    {
        return City::orderBy('name')->get();
    }

    public function show(City $city)
    {
        return $city;
    }
}
