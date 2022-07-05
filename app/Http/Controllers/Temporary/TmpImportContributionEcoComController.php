<?php

namespace App\Http\Controllers\Temporary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;

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
    DB::beginTransaction();
    try{
    $user_id = Auth::user()->id;
    $data_contribution_eco_com =  DB::select("select tmp_contribution_eco_com($user_id)");
    $data_contribution_eco_com = explode(',',$data_contribution_eco_com[0]->tmp_contribution_eco_com);
    $conunt_reg_contribution_passives =  DB::select("select count(*) from contribution_passives cp where is_valid is true");
    $count_reg_eco_comd =DB::select("select count(*) from discount_type_economic_complement dtec inner join economic_complements ec on ec.id = dtec.economic_complement_id
            inner join eco_com_modalities ecm on ecm.id = ec.eco_com_modality_id
            inner join eco_com_states ecs on ec.eco_com_state_id = ecs.id
            where dtec.discount_type_id = 7 and ecs.eco_com_state_type_id = 1 and ec.deleted_at is null");
    DB::commit();
        return response()->json([
            'message' => 'Realizado con éxito',
             'payload' => [
                'successfully' => true,
                'message_data' =>$data_contribution_eco_com[0],
                'total_amount_economic_complement' =>$data_contribution_eco_com[1],
                'total_amount_contribution_passives' =>$data_contribution_eco_com[2],
                'num_reg_economic_complement' =>$count_reg_eco_comd[0]->count,
                'num_reg_contribution_passives' =>$conunt_reg_contribution_passives[0]->count,
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

}
