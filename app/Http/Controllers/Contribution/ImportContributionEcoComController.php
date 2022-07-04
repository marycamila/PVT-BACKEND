<?php

namespace App\Http\Controllers\Contribution;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;

class ImportContributionEcoComController extends Controller
{
      /**
    * @OA\Post(
    *      path="/api/contribution/import_contribution_eco_com",
    *      tags={"IMPORTACION-CONTRIBUCIONES-COMPLEMENTO-ECONÓMICO"},
    *      summary="IMPORTACIÓN DE APORTES POR EL DESCUENTOS DE COMPLEMENTO ECONÓMICO POR SEMESTRE",
    *      operationId="import_contribution_eco_com",
    *      description="Registro de contribuciones de los descuentos por semestre para el auxilio mortuorio",
    *       @OA\RequestBody(
    *          description= "Provide auth credentials",
    *          required=true,
    *          @OA\JsonContent(
    *              type="object",
    *              @OA\Property(property="procedure_id", type="numeric",description="Id del semestre del Complemento Económico",example= 21)
    *            )
    *     ),
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

  public function import_contribution_eco_com(Request $request){
    $request->validate([
        'procedure_id' => 'required|numeric',
    ]);
    DB::beginTransaction();
    try{
    $user_id = Auth::user()->id;
    $data_contribution_eco_com =  DB::select("select import_contribution_eco_com($user_id,$request->procedure_id)");
    $data_contribution_eco_com = explode(',',$data_contribution_eco_com[0]->import_contribution_eco_com);
    DB::commit();
        return response()->json([
            'message' => 'Realizado con éxito',
             'payload' => [
                'successfully' => true,
                'message_data' =>$data_contribution_eco_com[0],
                'num_discount_type_eco_com_process' =>$data_contribution_eco_com[2],
                'num_contribution_passives_process' =>$data_contribution_eco_com[1],
            ]
        ]);
    }catch(Exception $e){
        DB::rollBack();
        return response()->json([
            'message' => 'Error en el copiado de datos',
            'payload' => [
                'successfully' => false,
                'error' => $e->getMessage(),
            ],
        ]);
     }
    }
      /**
    * @OA\Post(
    *      path="/api/contribution/change_state_valid",
    *      tags={"IMPORTACION-CONTRIBUCIONES-COMPLEMENTO-ECONÓMICO"},
    *      summary="CAMBIO DE ESTADO DE LA CONTRIBUCIÓN IS_VALID = TRUE",
    *      operationId="change_state_valid",
    *      description="Cambio de estado en la tabla contribution_passives campo is_valid = true siempre y cuando el estado del trámite de economic_complement cambie de 'En proceso' a 'Pagado'",
    *       @OA\RequestBody(
    *          description= "Provide auth credentials",
    *          required=true,
    *          @OA\JsonContent(
    *              type="object",
    *              @OA\Property(property="economic_complemnt_id", type="numeric",description="Id del Complemento Económico",example= 89556)
    *            )
    *     ),
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

    public function change_state_valid(Request $request){
    $request->validate([
        'economic_complemnt_id' => 'required|numeric',
    ]);
    DB::beginTransaction();
    try{
    $user_id = Auth::user()->id;
    $data_contribution_eco_com =  DB::select("select change_state_valid($user_id,$request->economic_complemnt_id)");
    DB::commit();
        return response()->json([
            'message' => 'Realizado con éxito',
             'payload' => [
                'successfully' => true,
                'message_data' =>$data_contribution_eco_com[0]->change_state_valid,
            ]
        ]);
    }catch(Exception $e){
        DB::rollBack();
        return response()->json([
            'message' => 'Error en el cambio de Estado',
            'payload' => [
                'successfully' => false,
                'error' => $e->getMessage(),
            ],
        ]);
     }
    }
    /**
    * @OA\Post(
    *      path="/api/contribution/change_state_valid_false",
    *      tags={"IMPORTACION-CONTRIBUCIONES-COMPLEMENTO-ECONÓMICO"},
    *      summary="CAMBIO DE ESTADO DE LA CONTRIBUCIÓN IS_VALID = false",
    *      operationId="change_state_valid_false",
    *      description="Cambio de estado en la tabla contribution_passives el campo is_valid = false siempre y cuando el estado trámite de economic_complement cambia de 'Pagado' a 'En proceso'",
    *       @OA\RequestBody(
    *          description= "Provide auth credentials",
    *          required=true,
    *          @OA\JsonContent(
    *              type="object",
    *              @OA\Property(property="economic_complemnt_id", type="numeric",description="Id del Complemento Económico",example= 89556)
    *            )
    *     ),
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

   public function change_state_valid_false(Request $request){
    $request->validate([
        'economic_complemnt_id' => 'required|numeric',
    ]);
    DB::beginTransaction();
    try{
    $user_id = Auth::user()->id;
    $data_contribution_eco_com =  DB::select("select change_state_valid_false($user_id,$request->economic_complemnt_id)");
    DB::commit();
        return response()->json([
            'message' => 'Realizado con éxito',
             'payload' => [
                'successfully' => true,
                'message_data' =>$data_contribution_eco_com[0]->change_state_valid_false,
            ]
        ]);
    }catch(Exception $e){
        DB::rollBack();
        return response()->json([
            'message' => 'Error en el cambio de Estado',
            'payload' => [
                'successfully' => false,
                'error' => $e->getMessage(),
            ],
        ]);
     }
    }
    
}
