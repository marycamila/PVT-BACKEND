<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Listado de todos los Usuario Paginado con filtros con full name.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $per_page = $request->per_page ?? 10;
        $users = User::with('roles')->paginate($per_page);
        return response()->json($users);
    }

}
