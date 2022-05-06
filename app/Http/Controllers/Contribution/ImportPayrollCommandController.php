<?php

namespace App\Http\Controllers\Contribution;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Contribution\ContributionCopyPayrollCommand;
use Carbon\Carbon;
use DateTime;
use DB;
use Auth;

class ImportPayrollCommandController extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/contribution/command_payroll_period",
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
     *      path="/api/contribution/update_base_wages",
     *      tags={"CONTRIBUCION"},
     *      summary="PASO 3 ACTUALIZACION DE SUELDOS BASE",
     *      operationId="updateData",
     *      description="Actualización de sueldos base tabla base_wages",
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
    public function update_base_wages(Request $request){
        $request->validate([
          'month' => 'required|integer|min:1|max:12',
          'year' => 'required|integer|min:1',
        ]);

        try{
            DB::beginTransaction();
            $message = "No hay datos de sueldos base por actualizar";
            $successfully =false;
            $user = Auth::user();
            $user_id = Auth::user()->id;

            $month = $request->get('month');
            $year_completed =  $request->get('year');
            $year = substr(strval($year_completed), strlen($year_completed)-2,2);

            $date_base_wages = Carbon::create($year_completed, $month, 1);
            $date_base_wages = Carbon::parse($date_base_wages)->format('Y-m-d');

            if(!$this->exists_data_table_base_wages($date_base_wages)){
                $query = "select * from update_base_wages($month,$year,$user_id,'$date_base_wages');";
            $update_base_wages = DB::select($query);

            if($update_base_wages != []){
                $message = "Realizado con exito la actualización de sueldos base";
                $successfully = true;
            }

            DB::commit();

            return response()->json([
                'message' => $message,
                'payload' => [
                    'successfully' => $successfully,
                    'update_base_wages'=> $update_base_wages
                ],
            ]);
            }else{
                return response()->json([
                    'message' => "Ya existen datos de sueldos base, no se puede volver a realizar esta acción",
                    'payload' => [
                        'successfully' => $successfully,
                    ],
                ]);

            }
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
     /**
     * @OA\Post(
     *      path="/api/contribution/upload_copy_payroll_command",
     *      tags={"IMPORTACION-PLANILLA-COMANDO"},
     *      summary="PASO 1 COPIADO DE DATOS PLANILLA COMANDO",
     *      operationId="upload_copy_payroll_command",
     *      description="Copiado de datos del archivo de planillas comando a la tabla payroll_copy_commands",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *            @OA\Property(property="file", type="file", description="file required", example="file"),
     *             @OA\Property(property="date_payroll", type="string",description="fecha de planilla required",example= "2022-03-01")
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

    public function upload_payroll_copy_command(request $request)
    {
        $request->validate([
            'file' => 'required',
            'date_payroll' => 'required|date_format:"Y-m-d"',
        ]);
        $extencion = strtolower($request->file->getClientOriginalExtension());
        $file_name_entry = $request->file->getClientOriginalName();
       DB::beginTransaction();
        try{
            $username = env('FTP_USERNAME');
            $password = env('FTP_PASSWORD');
            $successfully = false;
            if($extencion == "csv"){
                $date_payroll = Carbon::parse($request->date_payroll);
                $year = $date_payroll->format("Y");
                $year_format = $date_payroll->format("y");
                $month = $date_payroll->format("m");
                $month_format =(int)$month;

                $rollback_period = "delete from payroll_copy_commands where mes =$month_format and a_o= $year;";
                $rollback_period  = DB::connection('db_aux')->select($rollback_period);
                $file_name = "comando-".$month."-".$year.'.'.$extencion;
                    if($file_name_entry == $file_name){
                        $base_path = 'planillas/planilla_comando';
                        $file_path = Storage::disk('ftp')->putFileAs($base_path,$request->file,$file_name);
                        $base_path ='ftp://'.env('FTP_HOST').env('FTP_ROOT').$file_path;

                        $temporary_payroll = "create temporary table payroll_copy_commands_tmp(uni varchar,desg varchar, mes varchar, a_o varchar,che varchar,item varchar,car varchar,pat varchar,mat varchar,apes varchar,nom varchar,nom2 varchar,eciv varchar,niv varchar,gra varchar,sex varchar,sue varchar,cat varchar,est varchar,carg varchar,fro varchar,ori varchar,bseg varchar,
                                      dfu varchar, nat varchar,lac varchar, pre varchar, sub varchar,gan varchar, mus varchar, ode varchar,lpag varchar,nac varchar,ing varchar, c31 varchar)";
                        $temporary_payroll = DB::connection('db_aux')->select($temporary_payroll);

                        $copy = "copy payroll_copy_commands_tmp(uni,desg,mes,a_o,che,item,car,pat,mat,apes,nom,nom2,eciv,niv,gra,sex,sue,cat,est,carg,fro,ori,bseg,   
                                dfu, nat,lac, pre, sub,gan, mus, ode,lpag,nac,ing,c31)
                                FROM PROGRAM 'wget -q -O - $@  --user=$username --password=$password $base_path'
                                WITH DELIMITER ':' CSV header;";
                        $copy = DB::connection('db_aux')->select($copy);
                        $insert = "INSERT INTO payroll_copy_commands(uni,desg,mes,a_o,car,pat,mat,apes,nom,nom2,eciv,niv,gra,sex,sue,cat,est,carg,fro,ori,bseg,gan,mus,lpag,nac,ing,created_at,updated_at)
                                   SELECT uni::INTEGER,desg::INTEGER,mes::INTEGER,a_o::INTEGER,car,pat,mat,apes,nom,nom2,eciv,niv,gra,sex,sue,cat,est,carg,fro,ori,bseg,gan,mus,lpag,nac,ing,current_timestamp,current_timestamp FROM payroll_copy_commands_tmp; ";
                        $insert = DB::connection('db_aux')->select($insert);

                        $update_year="UPDATE payroll_copy_commands set a_o = concat(20,'',a_o)::integer where mes =$month_format and a_o=$year_format";
                        $update_year = DB::connection('db_aux')->select($update_year);

                        $drop = "drop table if exists payroll_copy_commands_tmp";
                        $drop = DB::select($drop);

                        $query = "select * from format_payroll_copy_commands($month_format,$year);";
                        $data_format = DB::connection('db_aux')->select($query);
                        DB::commit();


                        if($data_format != []){
                            $message = "Realizado con exito";
                            $successfully = true;
                        }

                        return response()->json([
                            'message' => $message,
                            'payload' => [
                                'successfully' => $successfully,
                                'copied_record' => $this->data_count_payroll_command($month_format,$year)
                            ],
                        ]);
                    } else {
                           return response()->json([
                            'message' => 'Error en el copiado del archivo',
                            'payload' => [
                                'successfully' => $successfully,
                                'error' => 'El nombre del archivo no coincide con en nombre requerido'
                            ],
                        ]);
                    }
            } else {
                    return response()->json([
                        'message' => 'Error en el copiado del archivo',
                        'payload' => [
                            'successfully' => $successfully,
                            'error' => 'El archivo no es un archivo CSV'
                        ],
                    ]);
            }
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
    // -------------metodo para verificar si ya existen sueldos base registrados-----//
    public function exists_data_table_base_wages($date){
        $exists_data = true;
        $query = "select * from base_wages bw  where month_year = '$date'";
        $verify_data = DB::select($query);

        if($verify_data == []) $exists_data = false;

        return $exists_data;
    }

    //data count payroll commnada
    public function data_count_payroll_command($month,$year){
        $data_count['num_total_data_copy'] = 0;
        $data_count['num_data_validated'] = 0;
        $data_count['num_data_regular'] = 0;
        $data_count['num_data_new'] = 0;

        //---TOTAL DE DATOS DEL ARCHIVO
        $query_total_data = "SELECT * FROM payroll_copy_commands where mes = $month::INTEGER and a_o = $year::INTEGER;";
        $query_total_data = DB::connection('db_aux')->select($query_total_data);
        $data_count['num_total_data_copy'] = count($query_total_data);

        return  $data_count;
    }
}
