<?php

namespace App\Http\Controllers\Contribution;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

    public function upload_copy_payroll_transcript(request $request)
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

                $rollback_period = "delete from payroll_copy_transcripts where mes =$month_format and a_o= $year;";
                $rollback_period  = DB::connection('db_aux')->select($rollback_period);
                $file_name = "transcripcion-".$month."-".$year.'.'.$extencion;
                    if($file_name_entry == $file_name){
                        $base_path = 'planillas/planilla_transcripcion';
                        $file_path = Storage::disk('ftp')->putFileAs($base_path,$request->file,$file_name);
                        $base_path ='ftp://'.env('FTP_HOST').env('FTP_ROOT').$file_path;

                        $temporary_payroll = "create temporary table payroll_copy_transcripts_tmp(nro integer,obs varchar,uni varchar, mes integer, a_o integer,
                        car varchar,pat varchar,mat varchar,nom varchar,nom2 varchar,
                        gan decimal(13,2),mus decimal(13,2), niv varchar,gra varchar,sue decimal(13,2),cat decimal(13,2),est decimal(13,2),
                        carg decimal(13,2),fro decimal(13,2),ori decimal(13,2),nac date,ing date)";
                        $temporary_payroll = DB::connection('db_aux')->select($temporary_payroll);

                        $copy = "copy payroll_copy_transcripts_tmp(nro,obs,uni, mes,a_o,car,pat,mat,nom,nom2,gan,mus,niv,gra,sue,cat,est,carg,fro,ori,nac,ing)
                                FROM PROGRAM 'wget -q -O - $@  --user=$username --password=$password $base_path'
                                WITH DELIMITER ':' CSV header;";
                        $copy = DB::connection('db_aux')->select($copy);
                        $insert = "INSERT INTO payroll_copy_transcripts(obs,uni,mes,a_o,car,pat,mat,nom,nom2,gan,mus,niv,gra,sue,cat,est,carg,fro,ori,nac,ing,created_at,updated_at)
                                   SELECT obs,uni,mes::INTEGER,a_o::INTEGER,car,pat,mat,nom,nom2,gan,mus,niv,gra,sue,cat,est,carg,fro,ori,nac,ing,current_timestamp,current_timestamp FROM payroll_copy_transcripts_tmp; ";
                        $insert = DB::connection('db_aux')->select($insert);

                        $drop = "drop table if exists payroll_copy_transcripts_tmp";
                        $drop = DB::connection('db_aux')->select($drop);

                        $data_count = $this->data_count_payroll_transcript($month_format,$year);

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
}
