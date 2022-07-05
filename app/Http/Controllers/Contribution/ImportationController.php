<?php

namespace App\Http\Controllers\Contribution;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Util;

class ImportationController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/contribution/list_years",
     *      tags={"METODOS-GLOBALES-IMPORTACION"},
     *      summary="OBTIENE EL LISTADO DE AÑOS CONSECUTIVAMENTE ",
     *      operationId="list_years",
     *      description="Obtiene el listado de años de contribuciones de senasir de manera consecutiva hasta el año actual Ej 2022",
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *            type="object"
     *         )
     *      )
     * )
     *
     * Logs user into the system.
     *
     * @param Request $request
     * @return void
    */
    public function list_years()
     {
        return response()->json([
            'message' => "Éxito",
            'payload' => [
                'list_years' =>  Util::list_years(1997)
            ],
        ]);
     }
}
