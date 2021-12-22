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
     *      tags={"CONTRIBUCION"},
     *      summary="PASO 1 COPIADO DE DATOS PLANILLA COMANDO",
     *      operationId="upload_copy_payroll_command",
     *      description="Copiado de datos del archivo de planillas comando a la tabla contribution_copy_payroll_commands",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *            @OA\Property(property="file", type="file", description="file required", example="file"),
     *             @OA\Property(property="date_payroll", type="string",description="fecha de planilla required",example= "2021-11-01")
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
    
    public function upload_copy_payroll_command(request $request)
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
            if($extencion == "csv"){
                $date_payroll = Carbon::parse($request->date_payroll);
                $year = $date_payroll->format("Y");
                $year_format = $date_payroll->format("y");
                $month = $date_payroll->format("m");
                $existing_period = "select  count(*) from  contribution_copy_payroll_commands  where mes ='$month' and a_o='$year_format'";
                $existing_period = DB::select($existing_period)[0]->count;
                if($existing_period == 0){   
                    $file_name = "comando-".$month."-".$year.'.'.$extencion;
                    if($file_name_entry == $file_name){
                        $base_path = 'contribucion/planilla_comando';
                        $file_path = Storage::disk('ftp')->putFileAs($base_path,$request->file,$file_name);
                        $base_path ='ftp://'.env('FTP_HOST').env('FTP_ROOT').$file_path;

                        $temporary_payroll = "create temporary table contribution_copy_payroll_commands_aux(uni varchar,desg varchar, mes varchar, a_o varchar,che varchar,car varchar,pat varchar,mat varchar,apes varchar,nom varchar,nom2 varchar,eciv varchar,niv varchar,gra varchar,sex varchar,dtr varchar,sue varchar,cat varchar,est varchar,carg varchar,fro varchar,ori varchar,bseg varchar,      
                                      dfu varchar, nat varchar,lac varchar, pre varchar, sub varchar,gan varchar, mus varchar, ode varchar,lpag varchar,nac varchar,ing varchar, c31 varchar)";
                        $temporary_payroll = DB::select($temporary_payroll);

                        $copy = "copy contribution_copy_payroll_commands_aux(uni,desg,mes,a_o,che,car,pat,mat,apes,nom,nom2,eciv,niv,gra,sex,dtr,sue,cat,est,carg,fro,ori,bseg,   
                                dfu, nat,lac, pre, sub,gan, mus, ode,lpag,nac,ing,c31)
                                FROM PROGRAM 'wget -q -O - $@  --user=$username --password=$password $base_path'
                                WITH DELIMITER ';' CSV header;";
                        $copy = DB::select($copy);

                        $insert = "INSERT INTO contribution_copy_payroll_commands(uni,desg,mes,a_o,car,pat,mat,apes,nom,nom2,eciv,niv,gra,sex,sue,cat,est,carg,fro,ori,bseg,gan,mus,lpag,nac,ing)
                                   SELECT uni,desg,mes,a_o,car,pat,mat,apes,nom,nom2,eciv,niv,gra,sex,sue,cat,est,carg,fro,ori,bseg,gan,mus,lpag,nac,ing FROM contribution_copy_payroll_commands_aux; ";
                        $insert = DB::select($insert);
                        DB::commit();

                        $drop = "drop table if exists contribution_copy_payroll_commands_aux";
                        $drop = DB::select($drop);

                        $consult = "select  count(*) from  contribution_copy_payroll_commands where mes ='$month' and a_o='$year_format'";
                        $consult = DB::select($consult)[0]->count;

                        return response()->json([
                            'message' => 'Realizado con exito',
                            'payload' => [
                                'successfully' => true,
                                'copied_record' => $consult
                            ],
                        ]);
                    } else {  
                           return response()->json([
                            'message' => 'Error en el copiado del archivo',
                            'payload' => [
                                'successfully' => false,
                                'error' => 'El nombre del archivo no coincide con en nombre requerido'
                            ],
                        ]);
                    }
                } else {
                    return response()->json([
                        'message' => 'Error en el copiado del archivo',
                        'payload' => [
                            'successfully' => false,
                            'error' => 'El periodo ya existe'
                        ],
                    ]);
                }
            } else {   
                    return response()->json([
                        'message' => 'Error en el copiado del archivo',
                        'payload' => [
                            'successfully' => false,
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
}
