<?php

namespace App\Http\Controllers\Contribution;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth;
use App\Http\Controllers\Contribution\ImportPayrollSenasirController;
use App\Models\Contribution\PayrollCommand;
use App\Models\Contribution\Contribution;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArchivoPrimarioExport;

class ImportContributionCommandController extends Controller
{
    
     //
      /**
     * @OA\Post(
     *      path="/api/contribution/list_months_import_contribution_command",
     *      tags={"IMPORTACION-APORTES-COMANDO"},
     *      summary="LISTA LOS MESES QUE SE REALIZARON IMPORTACIONES A LA TABLA CONTRIBUTIONS DE COMANDO EN BASE A UN AÑO DADO EJ:2022",
     *      operationId="list_command_months",
     *      description="Lista los meses importados en la tabla contributions enviando como parámetro un año en específico",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *             @OA\Property(property="period_year", type="integer",description="Año de contribución a listar",example= "2022")
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

    public function list_months_import_contribution_command(Request $request)
    {
        $request->validate([
            'period_year' => 'required|date_format:"Y"',
        ]);
         $period_year = $request->get('period_year');
         $contributionable_type = 'payroll_commands';

         $query = "SELECT distinct month_year, to_char( month_year, 'TMMonth') as period_month_name, extract(year from month_year) as period_year from contributions where deleted_at is null and (extract(year from month_year::timestamp)) = $period_year and contributionable_type = 'payroll_commands' group by month_year;";
         $query = DB::select($query);

         $query_months = "select id as period_month ,name as period_month_name from months order by id asc";
         $query_months = DB::select($query_months);

         foreach ($query_months as $month) {
            $month->state_importation = false;
            foreach ($query as $month_contribution) {
                if($month->period_month_name == $month_contribution->period_month_name){
                    $month->state_importation = true;
                    break;
                }
            }
            $month->state_validated_payroll = PayrollCommand::data_period($month->period_month,$period_year)['exist_data'];
            $date_payroll_format = Carbon::parse($period_year.'-'.$month->period_month.'-'.'01')->toDateString();
            $month->data_count = $this->data_count($month->period_month,$period_year,$date_payroll_format);
         }

         return response()->json([
            'message' => "Exito",
            'payload' => [
                'list_months' =>  $query_months,
                'count_months' =>  count($query)
            ],
        ]);
    }

    public function data_count($month,$year,$date_payroll_format){
        $data_count['num_total_data_copy'] = 0;
        $data_count['num_data_validated'] = 0;
        $data_count['num_data_regular'] = 0;
        $data_count['num_data_new'] = 0;
        $data_count['num_total_data_contributions'] = 0;
        $data_count['sum_amount_total_contributions'] = 0;

        //---TOTAL DE DATOS DEL ARCHIVO
        $query_total_data = "SELECT * FROM payroll_copy_commands where mes = $month::INTEGER and a_o = $year::INTEGER;";
        $query_total_data = DB::connection('db_aux')->select($query_total_data);
        $data_count['num_total_data_copy'] = count($query_total_data);
        // TOTAL VALIDADOS
        $data_count['num_data_validated'] =PayrollCommand::data_count($month,$year)['validated'];
        //CANTIDAD DE AFILIADOS REGULARES
        $data_count['num_data_regular'] = PayrollCommand::data_count($month,$year)['regular'];
        //CANTIDAD DE AFILIADOS NUEVOS
        $data_count['num_data_new'] =PayrollCommand::data_count($month,$year)['new'];
        //---TOTAL DE REGISTROS CONTRIBUTION PASSIVES
        $data_count['num_total_data_contributions'] = Contribution::data_period_command($date_payroll_format)['count_data'];
        //---suma monto total contribucion
        $data_count['sum_amount_total_contributions'] = floatval(Contribution::sum_total_command($date_payroll_format));

        return  $data_count;
    }

    /**
     * @OA\Post(
     *      path="/api/contribution/import_contribution_command",
     *      tags={"IMPORTACION-APORTES-COMANDO"},
     *      summary="PASO 3 IMPORTACIÓN DE CONTRIBUCION COMANDO GENERAL",
     *      operationId="import_contribution_command",
     *      description="Importación de aportes de Comando general",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="period_contribution", type="string",description="fecha de aporte required",example= "2022-03-01")
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
    public function import_contribution_command(Request $request){
        $request->validate([
        'period_contribution' => 'required|date_format:"Y-m-d"',
        ]);
     try{
        DB::beginTransaction();
        $user_id = Auth::user()->id;
        $message ='No realizado la importación!';
        $count_created = 0;
        $successfully = false;
        $period_contribution = Carbon::parse($request->period_contribution);
        $year = (int)$period_contribution->format("Y");
        $month = (int)$period_contribution->format("m");
        $count_registered = Contribution::data_period_command($request->period_contribution)['count_data'];
        if((int)$count_registered > 0){
            $message ="Error al realizar la importación, el periodo ya fue importado.";
        }else{
            if(Contribution::exist_contribution_rate($request->period_contribution)){
                $query ="select import_period_contribution_command('$request->period_contribution',$user_id,$year,$month)";
                $query = DB::select($query);
                $count_created = Contribution::data_period_command($request->period_contribution)['count_data'];
                DB::commit();
                $successfully = true;
                $message ="Realizado con éxito!";
            }else{
                $message ="No existe la taza de contribución para el periodo : ".$request->period_contribution.", el dato es requerido para continuar.";
            }
        }
        return response()->json([
            'message' => $message,
            'payload' => [
                'successfully' => $successfully,
                'num_created' => $count_created,
            ],
        ]);
     }catch(Exception $e){
        DB::rollBack();
        return response()->json([
            'message' => 'Error al realizar la importación',
            'payload' => [
                'successfully' => false,
                'error' => $e->getMessage(),
            ],
        ]);
     }
    }
}
