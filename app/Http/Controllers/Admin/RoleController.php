<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Role;
use App\Models\Admin\Permission;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoleController extends Controller
{
   /**
     * @OA\Get(
     *     path="/api/admin/role",
     *     tags={"ROLES"},
     *     summary="LISTADO DE ROLES",
     *     operationId="getRoles",
     *     @OA\Parameter(
     *         name="display_name",
     *         in="query",
     *         description="Nombre del Rol",
     *         required=false,
     *       ),
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
     * Get list of roles
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        $query = Role::query();
        if ($request->has('display_name')) $query = $query->where('display_name', 'ilike','%'.$request->display_name.'%');

        return [
            'message' => 'Realizado con éxito',
            'payload' => [
                'modules' => $query->get(),
            ]
         ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

     /**
     * @OA\Get(
     *     path="/api/admin/role/{role}",
     *     tags={"ROLES"},
     *     summary="DETALLE DEL ROL",
     *     operationId="getROle",
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format = "int64"
     *         )
     *       ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *            type="object"
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     *
     * Get user
     *
     * @param Request $request
     * @return void
     */
    public function show(Role $role)
    {
        return [
            'message' => 'Realizado con éxito',
            'payload' => [
                'role' => $role,
                'permissions' => $role->permissions()->orderBy('id')->get()
            ]
         ];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

/**
     * @OA\Patch(
     *     path="/api/admin/role/{role}/permission",
     *     tags={"ROLES"},
     *     summary="ESTABLECER O ELIMINAR EL PERMISO A UN ROL",
     *     operationId="getPermissionsByRole",
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         description="",
     *         required=true,
     *         example=1,
     *         @OA\Schema(
     *             type="integer",
     *             format = "int64"
     *         )
     *       ),
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="permission_id", type="integer",description="nombres required",example="1")
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *           type="object"
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     *
     * Get user
     *
     * @param Request $request
     * @return void
     */


    public function set_or_remove_permission(Request $request,Role $role) {
        $request->validate([
             'permission_id' => 'required|exists:permissions,id',
         ]);
         DB::beginTransaction();
         try {
 
             $permission= Permission::find($request->permission_id);
 
             $role_permissions = $role->permissions->pluck('id');
 
             $add_permission = true;
             foreach($role_permissions as $role_permission){
                 if($role_permission === $permission->id){
                     $add_permission = false;
                     break;
                 }
             }
             $date_current = Carbon::now()->format('Y-m-d h:i:s');

             if($add_permission){
                 $insert = "INSERT INTO role_permissions (role_id,permission_id,created_at,updated_at) VALUES ($role->id, $permission->id,'$date_current','$date_current')";
                 $insert = DB::select($insert);
             }else{
                 $delete = "DELETE from role_permissions where role_id = $role->id AND permission_id = $permission->id";
                 $delete = DB::select($delete);
             }
             DB::commit();
             return response()->json([
                 'message' => $add_permission? 'Realizado con éxito la adición del permiso: '.$permission->name:'Realizado con éxito la eliminación del rol: '.$permission->name,
                 'payload' => [
                     'permissions' => Role::where('id',$role->id)->get()->first()->permissions,
                 ],
             ]);

         } catch (\Exception $e) {
             DB::rollback();
             return response()->json([
                 'message' => 'Ocurrio un error',
                 'error' => $e
             ]);
         }
     }
    /**
     * @OA\Get(
     *     path="/api/admin/role/{role}/role_permissions",
     *     tags={"ROLES"},
     *     summary="LISTADO DE PERMISOS ASIGNADOS A UN ROL",
     *     operationId="role_permissions",
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format = "int64"
     *         )
     *       ),
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
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="display_name",
     *         in="query",
     *         description="Filtro del nombre de visualización del permiso",
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
     *         description="Success",
     *           @OA\JsonContent(
     *            type="object"
     *         )
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
    public function role_permissions(Request $request,Role $role)
    {   
        $id = request('id') ?? '';
        $operation_id = request('operation_id') ?? '';
        $action_id = request('action_id') ?? '';
        $name = request('name') ?? '';
        $display_name = request('display_name') ?? '';
        $conditions = [];

        if ($id != '') array_push($conditions, array('id', '=', "{$id}"));
        if ($operation_id != '') array_push($conditions, array('operation_id', '=', $operation_id));
        if ($action_id != '') array_push($conditions, array('action_id', 'ilike', "%{$action_id}%"));
        if ($name != '') array_push($conditions, array('name', 'ilike', "%{$name}%"));
        if ($display_name != '') array_push($conditions, array('display_name', 'ilike', "%{$display_name}%"));

        $per_page = $request->per_page ?? 10;

        $permissions = Permission::where($conditions)->paginate($per_page);
        $permission_asignes = $role->permissions()->get()->pluck('id');
        $active = false;
        foreach ($permissions as $permission) {
            $contar_active = 0;
            foreach ($permission_asignes as $permission_asigne){
                if($permission->id == $permission_asigne) $contar_active++;
            }
            if($contar_active == 1) $active = true;
            else $active = false;
            $permission->active = $active;
        }
        return response()->json([
            'message' => 'Realizado con éxito',
            'payload' => $permissions
        ]);
    }

}
