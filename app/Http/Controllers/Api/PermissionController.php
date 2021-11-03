<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;


class PermissionController extends Controller
{
    /**
    * Lista de permisos
    * Devuelve el listado de los permisos disponibles en el sistema
    * @authenticated
    * @responseFile responses/permission/index.200.json
    */
    public function index(Request $request)
    {
        $query = Permission::query();
        return $query->paginate(10);
    }
}
