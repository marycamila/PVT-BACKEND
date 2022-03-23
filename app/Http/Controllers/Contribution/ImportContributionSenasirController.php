<?php

namespace App\Http\Controllers\Contribution;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Contribution\ImportPayrollSenasirController;
use Auth;


class ImportContributionSenasirController extends Controller
{
     //
      /**
     * @OA\Post(
     *      path="/api/contribution/list_months_import_contribution_senasir",
     *      tags={"IMPORTACION-APORTES-SENASIR"},
     *      summary="LISTA LOS MESES QUE SE REALIZARON IMPORTACIONES A LA TABLA CONTRIBUTION PASSIVES SENASIR EN BASE A UN AÑO DADO EJ:2021",
     *      operationId="list_senasir_months",
     *      description="Lista los meses importados en la tabla contribution_passives enviando como parametro un año en especifico",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *             @OA\Property(property="period_year", type="integer",description="Año de contribucion a listar",example= "2021")
     *            )
     *          ),
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
    public function list_months_import_contribution_senasir(Request $request)
     {
        $request->validate([
            'period_year' => 'required|date_format:"Y"',
        ]);
         $period_year = $request->get('period_year');
         $contributionable_type = 'payroll_senasirs';

         $query = "SELECT  distinct month_year,  to_char( (to_date(month_year, 'YYYY/MM/DD')), 'TMMonth') as period_month_name, extract(year from month_year::timestamp) as period_year from contribution_passives where deleted_at  is null and  (extract(year from month_year::timestamp)) = $period_year and contributionable_type = '$contributionable_type' group by month_year;";
         $query = DB::select($query);

         $query_months = "select id as period_month ,name  as period_month_name from months order by id asc";
         $query_months = DB::select($query_months);

         foreach ($query_months as $month) {
            $month->state_importation = false;
            foreach ($query as $month_contribution) {
                if($month->period_month_name == $month_contribution->period_month_name){
                    $month->state_importation = true;
                    break;
                }
            }
            $month->state_validated_payroll = ImportPayrollSenasirController::exists_data_payroll_senasirs($month->period_month,$period_year);
            $date_payroll_format = Carbon::parse($period_year.'-'.$month->period_month.'-'.'01')->toDateString();
            $month->data_count = $this->data_count($month->period_month,$period_year,$date_payroll_format);
         }

         return response()->json([
            'message' => "Exito",
            'payload' => [
                'list_senasir_months' =>  $query_months,
                'count_senasir_months' =>  count($query)
            ],
        ]);
     }

     public function data_count($mes,$a_o,$date_payroll_format){
        $month = $mes;
        $year = $a_o;
        $data_count['num_total_data_copy'] = 0;
        $data_count['num_data_not_considered'] = 0;
        $data_count['num_data_considered'] = 0;
        $data_count['num_data_validated'] = 0;
        $data_count['num_data_not_validated'] = 0;
        $data_count['num_total_data_contribution_passives'] = 0;
        $data_count['sum_amount_total_contribution_passives'] = 0;

        $contributionable_type ='payroll_senasirs';
        //---TOTAL DE DATOS DEL ARCHIVO
        $query_total_data = "SELECT * FROM payroll_copy_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER;";
        $query_total_data = DB::connection('db_aux')->select($query_total_data);
        $data_count['num_total_data_copy'] = count($query_total_data);

        //---NUMERO DE DATOS NO CONSIDERADOs
        $query_data_not_considered = "SELECT * FROM payroll_copy_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER and clase_renta like 'ORFANDAD%';";
        $query_data_not_considered = DB::connection('db_aux')->select($query_data_not_considered);
        $data_count['num_data_not_considered'] = count($query_data_not_considered);

        //---NUMERO DE DATOS CONSIDERADOS
        $query_data_considered = "SELECT * FROM payroll_copy_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER and clase_renta not like 'ORFANDAD%';";
        $query_data_considered = DB::connection('db_aux')->select($query_data_considered);
        $data_count['num_data_considered'] = count($query_data_considered);

        //---NUMERO DE DATOS VALIDADOS
        $query_data_validated = "SELECT * FROM payroll_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER;";
        $query_data_validated = DB::select($query_data_validated);
        $data_count['num_data_validated'] = count($query_data_validated);
         //---NUMERO DE DATOS NO VALIDADOS
        $data_count['num_data_not_validated'] = $data_count['num_data_considered'] - $data_count['num_data_validated'];

        //---TOTAL DE REGISTROS CONTRIBUTION PASSIVES
        $query_data_contribution_passives = "SELECT id from contribution_passives ac
        where month_year = '$date_payroll_format' and ac.contributionable_type = '$contributionable_type' and ac.deleted_at is null";
        $query_data_contribution_passives = DB::select($query_data_contribution_passives);
        $data_count['num_total_data_contribution_passives'] = count($query_data_contribution_passives);

        //---suma monto total contribucion
        $query_sum_amount = "SELECT sum(ac.total) as amount_total from contribution_passives ac
        where month_year = '$date_payroll_format' and ac.contributionable_type = '$contributionable_type' and ac.deleted_at is null";
        $query_sum_amount = DB::select($query_sum_amount);
        $data_count['sum_amount_total_contribution_passives'] = isset($query_sum_amount[0]->amount_total) ? floatval($query_sum_amount[0]->amount_total):0;

        return  $data_count;
    }
    /**
     * @OA\Post(
     *      path="/api/contribution/import_create_or_update_contribution_period_senasir",
     *      tags={"IMPORTACION-APORTES-SENASIR"},
     *      summary="PASO 3 IMPORTACIÓN REGISTRO O ACTUALIZACIÓN DE DATOS DE CONTRIBUCION SENASIR",
     *      operationId="import_create_or_update_contribution_period_senasir",
     *      description="Creacion o actualizacion de contribution_passives y registro de la tabla tmp_registration_contribution_passives de contribuciones actualizadas",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="period_contribution_senasir", type="string",description="fecha de planilla required",example= "2021-10-01")
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
    public function import_create_or_update_contribution_period_senasir(Request $request){
        $request->validate([
        'period_contribution_senasir' => 'required|date_format:"Y-m-d"',
        ]);
     try{
            DB::beginTransaction();
        $user_id = Auth::user()->id;
        $successfully = false;
        $period_contribution_senasir = Carbon::parse($request->period_contribution_senasir);
        $year = (int)$period_contribution_senasir->format("Y");
        $month = (int)$period_contribution_senasir->format("m");
        $count_registered = "select count(*) from contribution_passives where  month_year = '$request->period_contribution_senasir' and contributionable_type ='payroll_senasirs';";
        $count_registered = DB::select($count_registered)[0]->count;
        if((int)$count_registered > 0){
            return response()->json([
                'message' => "Error al realizar la importacion, el periodo ya fue importado.",
                'payload' => [
                    'successfully' => $successfully
                ],
            ]);
        }else{
            $query ="select import_period_contribution_senasir('$request->period_contribution_senasir',$user_id,$year,$month)";
            $query = DB::select($query);
            $count_created = "select count(*) from contribution_passives where  month_year = '$request->period_contribution_senasir' and contributionable_type ='payroll_senasirs';";
            $count_created = DB::select($count_created)[0]->count;
            DB::commit();
            $successfully = true;
            return response()->json([
                'message' => "Realizado con exito!",
                'payload' => [
                    'successfully' => $successfully,
                    'num_created' => $count_created,
                ],
            ]);
        }
     }catch(Exception $e){
        DB::rollBack();
        return response()->json([
            'message' => 'Error al realizar la importacion',
            'payload' => [
                'successfully' => false,
                'error' => $e->getMessage(),
            ],
        ]);
     }
    }

}
