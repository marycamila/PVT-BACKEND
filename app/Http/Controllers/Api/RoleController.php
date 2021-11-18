<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
   /**
     * @OA\Get(
     *     path="/api/pvt/role",
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
     *         type="json"
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
     *     path="/api/pvt/role/{role}",
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
     *            type="json"
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
}
