<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;

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
     *     tags={"USUARIO"},
     *     summary="LISTADO DE USUARIOS",
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
        return response()->json([
            'message' => 'Realizado con exito',
            'payload' => [
                'users' => $users
            ],
        ]);
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
     *     tags={"USUARIO"},
     *     summary="OBTENER DEL USUARIO EL MODULO ROLES Y PERMISOS ",
     *     operationId="module_role_permision",
     *     description="Obtiene los modulos, roles y permisos del usuario AUTENTICADO",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="ok",
     *     @OA\JsonContent(
     *     type="object"
     *     )
     * )
     * )
     */
    public function module_role_permision(Request $request){
        $modules_objects = collect();
        $user = Auth::user();
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
        return response()->json([
            'message' => 'Realizado con exito',
            'payload' => [
                'modules' => $modules_objects
            ],
        ]);
    }
    /**
     * @OA\Post(
     *      path="/api/pvt/user",
     *      tags={"USUARIO"},
     *      summary="NUEVO USUARIO",
     *      operationId="crear usuario",
     *      description="Creación de un nuevo usuario",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="first_name", type="string",description="nombres required"),
     *              @OA\Property(property="last_name", type="string",description="apellidos required"),
     *              @OA\Property(property="username", type="string",description="nombre de usuario required"),
     *              @OA\Property(property="password", type="string",description="contraceña required"),
     *              @OA\Property(property="active", type="boolean",description="true o false required"),
     *              @OA\Property(property="position", type="boolean",description="Cargo del usuario required"),
     *              @OA\Property(property="city_id", type="boolean",description="ide de ciudad"),
     *          )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *            type="object"
     *         )
     *      )
     * )
     *
     * @return void
    */

    public function store(UserRequest $request)
    {
        $user = User::create($request->all());
        return [
            'message' => 'Usuario creado',
            'payload' => [
                'user' => new UserResource($user),
            ]
        ];
    }

    /**
     * @OA\Get(
     *     path="/api/pvt/user/{user}",
     *     tags={"USUARIO"},
     *     summary="DETALLE DE USUARIO",
     *     operationId="getUser",
     *     @OA\Parameter(
     *         name="user",
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

    public function show(User $user)
    {
        return [
            'message' => 'Usuario encontrado',
            'payload' => [
                'user' => new UserResource($user),
            ]
         ];
    }
    /**
     * @OA\Patch(
     *     path="/api/pvt/user/{user}/role",
     *     tags={"USUARIO"},
     *     summary="ESTABLECER ROLES A UN USUARIO",
     *     operationId="setRolesForUser",
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="",
     *         required=true,
     *         example=240,
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
     *              @OA\Property(property="roles", type="[]",description="nombres required",example="[1,2]")
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

    public function set_roles(Request $request, User $user)
    {
        //return $user->roles;
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);
        $user->syncRoles($request->roles);

        return response()->json([
            'message' => 'Realizado con éxito',
            'payload' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

}
