<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;

  /**
     * @OA\Info(
     *      version="1.0.0",
     *      title="PVT OpenApi",
     *      description="L5 Swagger OpenApi description"
     * )
     *
     */

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/pvt/user",
     *     tags={"USER"},
     *     summary="GET LIST OF USERS",
     *     operationId="getUsers",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Pagina a mostrar",
     *         required=false, 
     *       ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Por Pagina",
     *         example=10,
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="Usuarios activos(1) o inactivos(0) ",
     *         example=1,
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="first_name",
     *         in="query",
     *         description="Filtro por nombre de Usuario ",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="last_name",
     *         in="query",
     *         description="Filtro por Apellidos",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="position",
     *         in="query",
     *         description="Filtro por cargo",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="username",
     *         in="query",
     *         description="filtro por nombre de Usuario",
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
     * Get list of users.
     *
     * @param Request $request
     * @return void
     */

    public function index(Request $request){
        $active = request('active') ?? 1;
        $first_name = request('first_name') ?? '';
        $last_name = request('last_name') ?? '';
        $position = request('position') ?? '';
        $username = request('username') ?? '';
        $conditions = [];
        if ($position != '') {
          array_push($conditions, array('position', 'ilike', "%{$position}%"));
        }
        if ($first_name != '') {
            array_push($conditions, array('first_name', 'ilike', "%{$first_name}%"));      
        }
        if ($last_name != '') {
            array_push($conditions, array('last_name', 'ilike', "%{$last_name}%"));  
        }  
        if ($username != '') {
            array_push($conditions, array('username', 'ilike', "%{$username}%"));
        }  
        $per_page = $request->per_page ?? 10;
        $users = User::where('active',$active)->where($conditions)->paginate($per_page);
        return response()->json($users);
    }

    /**
     * @OA\SecurityScheme(
     *       securityScheme="bearerAuth",
     *       type="http",
     *       scheme="bearer"
     * )
     */


    /**
     * @OA\Get(
     *     path="/api/pvt/user/module_role_permision",
     *     tags={"USER"},
     *     summary="GET MODULE ROLE PERMSION USER",
     *     operationId="module_role_permision",
     *     description="Obtiene los modulos, roles y permisos del usuario",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="ok")
     * )
     */
    public function module_role_permision(Request $request){
        $modules_objects = collect();
        $user = Auth::user();
        //$user = User::find(1);
        $modules = $user->modules;
        if(isset($modules)){
            foreach($modules as $module){
                $module_object = Module::find($module);
                $module_object->roles = $user->roles;
                $module_object->roles = $user->rolesByModule($module);
                if(isset($module_object->roles)){
                    foreach($module_object->roles as $role){
                        $role_permissions = $role->permissions;
                    }
                }
                $module_object->roles->permissions = $role_permissions;
                $modules_objects->push($module_object);
            }
        }
        return response()->json($modules_objects);
    }

}
