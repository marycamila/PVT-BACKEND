<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;

class ModuleController extends Controller
{
   /**
    * Lista de módulos
    * Devuelve el listado con los datos paginados
    * @queryParam name Filtro por nombre. Example: prestamos
    * @queryParam sortBy Vector de ordenamiento. Example: [name]
    * @authenticated
    * @responseFile responses/module/index.200.json
    */
    public function index(Request $request)
    {
        $query = Module::orderBy('name');
        if ($request->has('name')) $query = $query->whereName($request->name);
        return $query->get();
    }
     /**
    * Detalle de módulo
    * Devuelve el detalle de un módulo mediante su ID
    * @urlParam module required ID de afiliado. Example: 3
    * @authenticated
    * @responseFile responses/module/show.200.json
    */
    public function show(Module $module)
    {
        return $module;
    }

    /**
    * Roles asociados al módulo
    * Devuelve la lista de roles asociados a un módulo
    * @urlParam module required ID del módulo. Example: 6
    * @authenticated
    * @responseFile responses/module/get_roles.200.json
    */
    public function get_roles(Module $module)
    {
        return $module->roles()->get();
    }
}
