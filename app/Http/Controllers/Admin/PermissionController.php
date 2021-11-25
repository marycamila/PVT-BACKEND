<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Permission;
use App\Models\Admin\Role;


class PermissionController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/admin/permission",
     *     tags={"PERMISOS"},
     *     summary="LISTADO DE PERMISOS",
     *     operationId="getUsers",
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="id permiso",
     *         required=false, 
     *       ),
     *     @OA\Parameter(
     *         name="operation_id",
     *         in="query",
     *         description="id de la operacion",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="action_id",
     *         in="query",
     *         description="id de la acción ",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filtro por nombre de permiso ",
     *         example="delete-note",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="display_name",
     *         in="query",
     *         description="Filtro del nombre de visualización del permiso",
     *         example="Eliminar notas",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Pagina a mostrar",
     *         example=1,
     *         required=false,
     *       ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Por Pagina",
     *         example=10,
     *         required=false,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     *
     * obtener listado de permisos
     *
     * @param Request $request
     * @return void
     */

    public function index(Request $request)
    {
        $id = request('id') ?? '';
        $operation_id = request('operation_id') ?? '';
        $action_id = request('action_id') ?? '';
        $name = request('name') ?? '';
        $display_name = request('display_name') ?? '';
        $conditions = [];

        if ($id != '') array_push($conditions, array('id', '=', "{$id}"));
        if ($operation_id != '') array_push($conditions, array('operation_id', '=', "%{$operation_id}%"));
        if ($action_id != '') array_push($conditions, array('action_id', 'ilike', "%{$action_id}%"));
        if ($name != '') array_push($conditions, array('name', 'ilike', "%{$name}%"));
        if ($display_name != '') array_push($conditions, array('display_name', 'ilike', "%{$display_name}%"));

        $per_page = $request->per_page ?? 10;
        $query = Permission::query()->where($conditions)->paginate($per_page);
        return response()->json([
            'message' => 'Listado de permisos',
            'payload' => $query
        ]);
    }
}