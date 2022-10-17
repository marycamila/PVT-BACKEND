<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Affiliate\AddressRequest;
use App\Models\Affiliate\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/affiliate/address",
     *      tags={"DIRECCIÓN"},
     *      summary="NUEVA DIRECCIÓN",
     *      operationId="crear dirección",
     *      description="Creación de una nueva dirección",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="city_address_id", type="integer",description="id de ciudad", example=5),
     *              @OA\Property(property="zone", type="string",description="nombre de zona", example="Belzu"),
     *              @OA\Property(property="street", type="string",description="nombre de calle", example="10 de febrero"),
     *              @OA\Property(property="number_address", type="string",description="número de dirección", example="N°85"),
     *              @OA\Property(property="description", type="string",description="nombre descriptivo de dirección", example="")
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
    public function store(AddressRequest $request)
    {
        return Address::create($request->all());
    }

    /**
     * @OA\Patch(
     *      path="/api/affiliate/address/{address}",
     *      tags={"DIRECCIÓN"},
     *      summary="ACTUALIZAR DIRECCIÓN",
     *      operationId="ActualizarDireccion",
     *      description="Actualizar dirección",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="address",
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
     *          required=false,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="city_address_id", type="integer",description="id de ciudad",example="5"),
     *              @OA\Property(property="zone", type="string",description="nombre de zona",example=""),
     *              @OA\Property(property="street", type="string",description="nombre de calle",example=""),
     *              @OA\Property(property="number_address", type="string",description="número de dirección",example=""),
     *              @OA\Property(property="description", type="string",description="nombre descriptivo de dirección",example="AV SEBASTIAN PAGADOR N°45")
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
    public function update(AddressRequest $request, Address $address)
    {
        $address->fill($request->all());
        $address->save();
        return $address;
    }


    /**
     * @OA\Delete(
     *     path="/api/affiliate/address/{address}",
     *     tags={"DIRECCIÓN"},
     *     summary="ELIMINAR DIRECCIÓN",
     *     operationId="deleteAddress",
     * @OA\Parameter(
     *         name="address",
     *         in="path",
     *         description="",
     *         example=1,
     *
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
     *         type="object"
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     *
     * Get status of virtual office.
     *
     * @param Request $request
     * @return void
     */

    public function destroy(Address $address)
    {
        $address->delete();
        return $address;
    }
}
