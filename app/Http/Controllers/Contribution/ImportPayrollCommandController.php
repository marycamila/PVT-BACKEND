<?php

namespace App\Http\Controllers\Contribution;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Contribution\ContributionCopyPayrollCommand;
use Carbon\Carbon;
use DateTime;
use DB;

class ImportPayrollCommandController extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/contribution/period_copy_payroll_upload_command",
     *     tags={"CONTRIBUCION"},
     *     summary="PERIODO DE LA CONTRIBUCION",
     *     operationId="period_upload_command",
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
     * Get list of modules.
     *
     * @param Request $request
     * @return void
     */
    public function command_payroll_period(request $request){
        $last_iportation =  ContributionCopyPayrollCommand::orderBy('id')->get()->last();
        if($last_iportation){
            $last_year = $last_iportation->a_o;
            $year = DateTime::createFromFormat('y', $last_year);
            $last_date = Carbon::parse($year->format('Y').'-'.$last_iportation->mes);
            $estimated_date = $last_date->addMonth();
        }else{
            $month_year="select max(month_year) as date from contributions";
            $estimated_date = DB::select($month_year);
            $estimated_date = $estimated_date[0]->date;
            $estimated_date = Carbon::parse($estimated_date)->addMonth();
        }
        return response()->json([
            'message' => 'Realizado con exito',
            'payload' => [
                'estimated_date' => $estimated_date
          ]
        ]); 
    }
    /**
     * @OA\Post(
     *      path="/api/contribution/format_payroll_data_type_command",
     *      tags={"CONTRIBUCION"},
     *      summary="PASO 2 FORMATEO DE DATOS",
     *      operationId="formatData",
     *      description="Formateo de datos de la tabla contribution_copy_payroll_commands",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="month", type="integer",description="mes required",example=11),
     *              @OA\Property(property="year", type="integer",description="año required",example=2021)
     *          )
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

   public function format_payroll_data_type_command(Request $request){
    $request->validate([
      'month' => 'required|integer|min:1|max:12',
      'year' => 'required|integer|min:1',
    ]);

    try{
        DB::beginTransaction();
        $message = "No hay datos";
        $successfully =false;

        $month = $request->get('month');
        $year =  $request->get('year');
        if(strlen($month) == 1) $month = '0'.$month;
        $year = substr(strval($year), strlen($year)-2,2);

        $query = "select * from format_contribution_format_payroll_command('$month','$year');";
        $data_format = DB::select($query);

        if($data_format != []){
            $message = "Realizado con exito";
            $successfully = true;
        }

        DB::commit();

        return response()->json([
            'message' => $message,
            'payload' => [
                'successfully' => $successfully,
            ],
        ]);
        }catch(Exception $e){
        DB::rollBack();
        return response()->json([
            'message' => 'Error en el formateo de datos',
            'payload' => [
                'successfully' => false,
                'error' => $e->getMessage(),
            ],
        ]);
        }
    }
}
