<?php

namespace App\Http\Controllers\Contribution;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArchivoPrimarioExport;

class ImportPayrollTranscriptController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/contribution/upload_copy_payroll_transcript",
     *      tags={"IMPORTACION-PLANILLA-TRANSCRIPCIÓN"},
     *      summary="PASO 1 COPIADO DE DATOS PLANILLA TRANSCRIPCIÓN",
     *      operationId="upload_copy_payroll_transcribed",
     *      description="Copiado de datos del archivo de planillas transcritas a la tabla payroll_copy_transcribeds",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *             @OA\Property(property="file", type="file", description="file required", example="file"),
     *             @OA\Property(property="date_payroll", type="string",description="fecha de planilla required",example= "1998-02-01"),
     *             @OA\Property(property="total_amount", type="number",description="Monto total de la planilla required",example= "401585.31")
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

    public function upload_copy_payroll_transcript(request $request)
    {
        $request->validate([
            'file' => 'required',
            'date_payroll' => 'required|date_format:"Y-m-d"',
            'total_amount' => 'required|numeric',
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

                $rollback_period = "delete from payroll_copy_transcripts where mes =$month_format and a_o= $year;";
                $rollback_period  = DB::connection('db_aux')->select($rollback_period);
                $file_name = "transcripcion-".$month."-".$year.'.'.$extencion;
                    if($file_name_entry == $file_name){
                        $base_path = 'planillas/planilla_transcripcion';
                        $file_path = Storage::disk('ftp')->putFileAs($base_path,$request->file,$file_name);
                        $base_path ='ftp://'.env('FTP_HOST').env('FTP_ROOT').$file_path;

                        $drop = "drop table if exists payroll_copy_transcripts_tmp";
                        $drop = DB::connection('db_aux')->select($drop);

                        $temporary_payroll = "create temporary table payroll_copy_transcripts_tmp(nro integer,obs varchar,uni varchar, mes integer, a_o integer,
                        car varchar,pat varchar,mat varchar,nom varchar,nom2 varchar,
                        niv varchar,gra varchar,sue decimal(13,2),cat decimal(13,2),gan decimal(13,2),mus decimal(13,2),est decimal(13,2),
                        carg decimal(13,2),fro decimal(13,2),ori decimal(13,2),nac date,ing date)";
                        $temporary_payroll = DB::connection('db_aux')->select($temporary_payroll);

                        $copy = "copy payroll_copy_transcripts_tmp(nro,obs,uni, mes,a_o,car,pat,mat,nom,nom2,niv,gra,sue,cat,gan,mus,est,carg,fro,ori,nac,ing)
                                FROM PROGRAM 'wget -q -O - $@  --user=$username --password=$password $base_path'
                                WITH DELIMITER ':' CSV header;";
                        $copy = DB::connection('db_aux')->select($copy);

                        //******validación de datos****************/

                        $verify_data = "select count(*) from payroll_copy_transcripts_tmp where mes <> $month_format or a_o <> $year or mes is null or a_o is null;";
                        $verify_data = DB::connection('db_aux')->select($verify_data);

                        if($verify_data[0]->count > 0){
                            return response()->json([
                                'message' => 'Error en el copiado de datos',
                                'payload' => [
                                    'successfully' => false,
                                    'error' => 'Existen datos incorrectos en la(s) columnas de mes o año.',
                                ],
                            ]);
                        }

                        $verify_amount = "select sum(mus) from payroll_copy_transcripts_tmp";
                        $verify_amount = DB::connection('db_aux')->select($verify_amount);

                        if($verify_amount[0]->sum !=  $request->total_amount) {
                            return response()->json([
                                'message' => 'Error en el copiado de datos',
                                'payload' => [
                                    'successfully' => false,
                                    'error' => 'El monto total ingresado no coincide con el monto total de la planilla, favor de verificar.',
                                ],
                            ]);
                        }
                        //****************************************/
                        $insert = "INSERT INTO payroll_copy_transcripts(obs,uni,mes,a_o,car,pat,mat,nom,nom2,niv,gra,sue,cat,gan,mus,est,carg,fro,ori,nac,ing,created_at,updated_at)
                                   SELECT obs,uni,mes::INTEGER,a_o::INTEGER,car,pat,mat,nom,nom2,niv,gra,sue,cat,gan,mus,est,carg,fro,ori,nac,ing,current_timestamp,current_timestamp FROM payroll_copy_transcripts_tmp; ";
                        $insert = DB::connection('db_aux')->select($insert);

                        $drop = "drop table if exists payroll_copy_transcripts_tmp";
                        $drop = DB::connection('db_aux')->select($drop);

                        $data_count = $this->data_count_payroll_transcript($month_format,$year);

                        //******validación de datos****************/
                        $verify_data = "update payroll_copy_transcripts pt set error_messaje = concat(error_messaje,' - ','Los valores de los apellidos son NULOS ') from (select id from payroll_copy_transcripts where mes =$month_format and a_o= $year and pat is null and mat is null) as subquery where pt.id = subquery.id;";
                        $verify_data = DB::connection('db_aux')->select($verify_data);

                        $verify_data = "update payroll_copy_transcripts pt set error_messaje = concat(error_messaje,' - ','El valor del primer nombre es NULO ') from (select id from payroll_copy_transcripts where mes =$month_format and a_o= $year and nom is null) as subquery where pt.id = subquery.id;";
                        $verify_data = DB::connection('db_aux')->select($verify_data);

                        $verify_data = "update payroll_copy_transcripts pt set error_messaje = concat(error_messaje,' - ','El monto del aporte es 0 o inferior ') from (select id from payroll_copy_transcripts where mes =$month_format and a_o= $year and mus <= 0) as subquery where pt.id = subquery.id;";
                        $verify_data = DB::connection('db_aux')->select($verify_data);

                        $verify_data = "update payroll_copy_transcripts pt set error_messaje = concat(error_messaje,' - ','El numero de carnet es duplicado ') from (select car,count(car) from payroll_copy_transcripts where mes =$month_format and a_o= $year group by car having count(car) > 1) as subquery where pt.car = subquery.car;";
                        $verify_data = DB::connection('db_aux')->select($verify_data);

                        $verify_data = "select count(id) from payroll_copy_transcripts pct where mes =$month_format and a_o= $year and error_messaje is not null;";
                        $verify_data = DB::connection('db_aux')->select($verify_data);

                        if($verify_data[0]->count > 0) {
                            return response()->json([
                                'message' => 'Excel',
                                'payload' => [
                                    'successfully' => false,
                                    'error' => 'Existen datos en el archivo que son incorrectos, favor revisar.',
                                ],
                            ]);
                        }
                        //****************************************/
                        DB::commit();

                        if($data_count['num_total_data_copy'] > 0){
                            $message = "Realizado con éxito";
                            $successfully = true;
                        }

                        return response()->json([
                            'message' => $message,
                            'payload' => [
                                'successfully' => $successfully,
                                'data_count' => $data_count
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

    public function data_count_payroll_transcript($month,$year){
        $data_count['num_total_data_copy'] = 0;

        //---TOTAL DE DATOS DEL ARCHIVO
        $query_total_data = "SELECT count(id) FROM payroll_copy_transcripts where mes = $month::INTEGER and a_o = $year::INTEGER;";
        $query_total_data = DB::connection('db_aux')->select($query_total_data);
        $data_count['num_total_data_copy'] = $query_total_data[0]->count;

        return  $data_count;
    }
     /**
      * @OA\Post(
      *      path="/api/contribution/download_error_data_archive",
      *      tags={"IMPORTACION-PLANILLA-TRANSCRIPCIÓN"},
      *      summary="Descarga el archivo, con el listado de afiliados que tengan observaciones en el archivo ",
      *      operationId="download_error_data_archive",
      *      description="Descarga el archivo con el listado de afiliados con CI duplicado, primer nombre nulo, apellido paterno y materno en nulo ",
      *      @OA\RequestBody(
      *          description= "Provide auth credentials",
      *          required=true,
      *          @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
      *             @OA\Property(property="date_payroll", type="string", description="fecha de planilla required", example= "2021-10-01")
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
    public function download_error_data_archive(Request $request){
        $request->validate([
            'date_payroll' => 'required|date_format:"Y-m-d"',
        ]);
    $message = "No hay datos";
    $data_header=array(array("AÑO","MES","CARNET","APELLIDO PATERNO","APELLIDO MATERNO","PRIMER NOMBRE","SEGUNDO NOMBRE","APORTE","OBSERVACIÓN"));
    $date_payroll = Carbon::parse($request->date_payroll);
    $year = (int)$date_payroll->format("Y");
    $month = (int)$date_payroll->format("m");
    $data_payroll_copy_transcripts = "select a_o,mes,car,pat,mat,nom,nom2,mus,error_messaje from payroll_copy_transcripts pct where mes ='$month' and a_o ='$year' and error_messaje is not null or error_messaje ='' order by car";
    $data_payroll_copy_transcripts = DB::connection('db_aux')->select($data_payroll_copy_transcripts);
        foreach ($data_payroll_copy_transcripts as $row){
            array_push($data_header, array($row->a_o,$row->mes,$row->car,$row->pat,
            $row->mat,$row->nom,$row->nom2,$row->mus,$row->error_messaje));
        }
        $export = new ArchivoPrimarioExport($data_header);
        $file_name = "observacion-planilla-transcrita";
        $extension = '.xls';
        return Excel::download($export, $file_name."_".$month."_".$year.$extension);
    }
}
