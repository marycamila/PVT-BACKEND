<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City;

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
