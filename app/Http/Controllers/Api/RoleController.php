<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
   /**
    * Lista de roles
    * Devuelve el listado de los roles disponibles en el sistema
    * @queryParam name Filtrar roles por nombre. Example: PRE-recepcion
    * @authenticated
    * @responseFile responses/role/index.200.json
    */
    public function index(Request $request)
    {
        $query = Role::orderBy('name');
        if ($request->has('name')) $query = $query->whereName($request->name);
        return $query->get();
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
    * Detalle de rol
    * Devuelve el detalle de un rol mediante su ID
    * @urlParam role required ID de rol. Example: 42
    * @authenticated
    * @responseFile responses/role/show.200.json
    */
    public function show(Role $role)
    {
        return $role;
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
