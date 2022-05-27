<?php

namespace App\Http\Controllers\Temporary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TmpImportContributionEcoComController extends Controller
{
    //
           /**
    * @OA\Post(
    *      path="/api/temporary/tmp_import_contribution_eco_com",
    *      tags={"IMPORTACION-CONTRIBUCIONES-COMPLEMENTO-ECONÓMICO"},
    *      summary="IMPORTACIÓN DE APORTES POR EL DESCUENTOS YA REALIZADO POR COMPLEMENTO ECONÓMICO",
    *      operationId="tmp_import_contribution_eco_com",
    *      description="Registro de contribuciones de los descuentos ya realizados para el auxilio mortuorio",
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

  public function tmp_import_contribution_eco_com(Request $request){

    $data_contribution_eco_com =  DB::select("select tmp_contribution_eco_com()")[0]->tmp_contribution_eco_com;

        return response()->json([
            'message' => 'Realizado con éxito',
             'payload' => [
                'successfully' => true,
                'message_data' => $data_contribution_eco_com,
            ],
        ]);
    }

}
