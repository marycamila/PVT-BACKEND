<?php

namespace App\Http\Controllers\Contribution;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Helpers\Util;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArchivoPrimarioExport;
use App\Models\Contribution\PayrollTranscriptPeriod;

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
     *             @OA\Property(property="image", type="file", description="file required", example="image"),
     *             @OA\Property(property="date_payroll", type="string",description="fecha de planilla required",example= "1999-01-01"),
     *             @OA\Property(property="number_records", type="integer",description="cantidad total de regisros required",example= "19323"),
     *             @OA\Property(property="total_amount", type="number",description="Monto total de la planilla required",example= "428865.81")
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
            'image' => 'required|image|max:2048',
            'date_payroll' => 'required|date_format:"Y-m-d"',
            'number_records' => 'required|integer',
            'total_amount' => 'required|numeric',
        ]);
        $extencion = strtolower($request->file->getClientOriginalExtension());
        $file_name_entry = $request->file->getClientOriginalName();
        $image_name_entry = $request->image->getClientOriginalName();
        $extension_imge = strtolower($request->image->getClientOriginalExtension());
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
                        $base_path = 'planillas/planilla_transcripcion/'.$month.'-'.$year;
                        $file_path = Storage::disk('ftp')->putFileAs($base_path,$request->file,$file_name);
                        $base_path ='ftp://'.env('FTP_HOST').env('FTP_ROOT').$file_path;

                        $image_name = "foto-planilla-fisica-".$month."-".$year.'.'.$extension_imge;
                        $base_path_image = 'planillas/planilla_transcripcion/'.$month.'-'.$year;
                        $image_path = Storage::disk('ftp')->putFileAs($base_path_image,$request->image,$image_name);
                        $base_path_image ='ftp://'.env('FTP_HOST').env('FTP_ROOT').$image_path;

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

                        $verify_number_records = "select count(*) from payroll_copy_transcripts_tmp";
                        $verify_number_records = DB::connection('db_aux')->select($verify_number_records);

                        if($verify_number_records[0]->count !=  $request->number_records) {
                            return response()->json([
                                'message' => 'Error en el copiado de datos',
                                'payload' => [
                                    'successfully' => false,
                                    'error' => 'El total de registros ingresado no coincide con la cantidad de registros del archivo.'
                                ],
                            ]);
                        }

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
                                    'error' => 'El monto total ingresado no coincide con el monto total de la planilla, favor de verificar.'.$verify_amount[0]->sum . ' distinto a '.$request->total_amount,
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

                        $payroll_period = new PayrollTranscriptPeriod;
                        $payroll_period->updateOrInsert(
                            ['month_p' => $month_format, 'year_p' => $year],
                            ['total_amount' => $request->total_amount,'number_records' => $request->number_records]
                        );

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

     /**
     * @OA\Post(
     *      path="/api/contribution/validation_affiliate_transcript",
     *      tags={"IMPORTACION-PLANILLA-TRANSCRIPCIÓN"},
     *      summary="PASO 2 VALIDACION AFILIADOS TRANSCRITOS",
     *      operationId="validation_affiliate_transcript",
     *      description="validacion de Afiliados de la planilla transcrita",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *             @OA\Property(property="date_payroll", type="string",description="fecha de planilla required",example= "1999-01-01")
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

    public function validation_affiliate_transcript(Request $request){
        $request->validate([
        'date_payroll' => 'required|date_format:"Y-m-d"',
        ]);
            try{
                DB::beginTransaction();
                $message = "No hay datos por validar";
                $successfully =false;
                $data_count['total_data_count'] = 0;
                $data_count['count_data_automatic_link'] = 0;
                $data_count['count_data_revision'] = 0;
                $data_count['count_data_creation'] = 0;
                $date_payroll_format = $request->date_payroll;
                $date_payroll = Carbon::parse($request->date_payroll);
                $year = (int)$date_payroll->format("Y");
                $month = (int)$date_payroll->format("m");

                $connection_db_aux = Util::connection_db_aux();

                $query = "select search_affiliate_transcript('$connection_db_aux',$month,$year);";
                $data_validated = DB::select($query);

                $total_data_count = $this->data_count_payroll_transcript($month,$year);

                $count_data_automatic_link = "select count(id) from payroll_copy_transcripts pct where mes ='$month' and a_o ='$year' and criteria in ('1-CI-PN-PA-SA','2-CI-sPN-sPA-sSA','3-partCI-PN-PA-SA')";
                $count_data_automatic_link = DB::connection('db_aux')->select($count_data_automatic_link);

                $count_data_revision = "select count(id) from payroll_copy_transcripts pct where mes ='$month' and a_o ='$year' and criteria in ('4-CI')";
                $count_data_revision = DB::connection('db_aux')->select($count_data_revision);

                $count_data_creation = "select count(id) from payroll_copy_transcripts pct where mes ='$month' and a_o ='$year' and criteria in ('5-CREAR')";
                $count_data_creation = DB::connection('db_aux')->select($count_data_creation);

                $data_count['total_data_count'] = $total_data_count['num_total_data_copy'];
                $data_count['count_data_automatic_link'] = $count_data_automatic_link[0]->count;
                $data_count['count_data_revision'] = $count_data_revision[0]->count;
                $data_count['count_data_creation'] = $count_data_creation[0]->count;

                if($total_data_count['num_total_data_copy'] <= 0){
                    $successfully =false;
                    $message = 'no existen datos';
                }elseif($count_data_revision[0]->count > 0){
                    $successfully =false;
                    $message = 'Excel';
                }elseif($count_data_revision[0]->count == 0 && $count_data_creation[0]->count > 0){
                    $successfully =true;
                    $message = 'Excel';
                }elseif($count_data_revision[0]->count == 0 && $count_data_creation[0]->count == 0){
                    $successfully =true;
                    $message = 'Realizado con Exito.';
                }else{
                    $successfully =false;
                    $message = 'Ops Ocurrio algo inesperado.';
                }

                return response()->json([
                    'message' => $message,
                    'payload' => [
                        'successfully' => $successfully,
                        'data_count' => $data_count
                    ],
                ]);
            }catch(Exception $e){
                DB::rollBack();
                return response()->json([
                'message' => 'Error en la busqueda de datos de afiliados.',
                'payload' => [
                    'successfully' => false,
                    'error' => $e->getMessage(),
                ],
                ]);
            }
        }
    /**
      * @OA\Post(
      *      path="/api/contribution/download_data_revision",
      *      tags={"IMPORTACION-PLANILLA-TRANSCRIPCIÓN"},
      *      summary="Descarga el archivo, para la revisión de datos de los afiliados",
      *      operationId="download_data_revision",
      *      description="Descarga el archivo, para la revisión de datos de los afiliados identificados con CI iguales y CI Distintos",
      *      @OA\RequestBody(
      *          description= "Provide auth credentials",
      *          required=true,
      *          @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
      *             @OA\Property(property="date_payroll", type="string", description="fecha de planilla required", example= "1999-01-01")
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
    public function download_data_revision(Request $request){
        $request->validate([
            'date_payroll' => 'required|date_format:"Y-m-d"',
        ]);
    $message = "No hay datos";
    $data_header=array(array("AÑO","MES","CARNET","APELLIDO PATERNO","APELLIDO MATERNO","PRIMER NOMBRE","SEGUNDO NOMBRE","APORTE","DETALLE PARA REVISIÓN","***","NUP-AFILIADO CON SIMILITUD"));
    $date_payroll = Carbon::parse($request->date_payroll);
    $year = (int)$date_payroll->format("Y");
    $month = (int)$date_payroll->format("m");
    $data_payroll_copy_transcripts = "select a_o,mes,car,pat,mat,nom,nom2,mus,'***',
    (CASE WHEN (criteria = '4-CI') then
         'IDENTIFICADO PARA SUBSANAR'
     ELSE
         'IDENTIFICADO PARA CREAR'
    END) as criteria, affiliate_id from payroll_copy_transcripts pct where mes ='$month' and a_o ='$year' and criteria in('4-CI','5-CREAR') order by criteria DESC";
    $data_payroll_copy_transcripts = DB::connection('db_aux')->select($data_payroll_copy_transcripts);
        foreach ($data_payroll_copy_transcripts as $row){
            array_push($data_header, array($row->a_o,$row->mes,$row->car,$row->pat,
            $row->mat,$row->nom,$row->nom2,$row->mus,$row->criteria,'***',$row->affiliate_id));
        }
        $export = new ArchivoPrimarioExport($data_header);
        $file_name = "observacion-data-revision";
        $extension = '.xls';
        return Excel::download($export, $file_name."_".$month."_".$year.$extension);
    }
    /**
     * @OA\Post(
     *      path="/api/contribution/list_months_import_contribution_transcript",
     *      tags={"IMPORTACION-PLANILLA-TRANSCRIPCIÓN"},
     *      summary="LISTA LOS MESES QUE SE REALIZARON IMPORTACIONES A LA TABLA CONTRIBUTIONS TRANSCRITOS DE COMANDO EN BASE A UN AÑO DADO EJ:1999",
     *      operationId="list_months_import_contribution_transcript",
     *      description="Lista los meses importados de las contribuciones de comando, en la tabla contributions enviando como parámetro un año en específico",
     *     @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *             @OA\Property(property="period_year", type="integer",description="Año de contribución a listar",example= "1999"),
     *             @OA\Property(property="with_data_count", type="boolean",description="valor para pedir envio de conteo de datos",example= false)
     *            )
     *
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

    public function list_months_import_contribution_transcript(Request $request)
    {
        $request->validate([
            'period_year' => 'required|date_format:"Y"',
            'with_data_count'=>'boolean'
        ]);
        $with_data_count = !isset($request->with_data_count) || is_null($request->with_data_count)? true:$request->with_data_count;
        $period_year = $request->get('period_year');
        $contributionable_type = 'payroll_transcripts';
        $query = "SELECT distinct month_year, to_char( month_year, 'TMMonth') as period_month_name, extract(year from month_year) as period_year,extract(month from month_year) as period_month  from contributions where deleted_at is null and (extract(year from month_year::timestamp)) = $period_year and contributionable_type = 'payroll_transcripts' group by month_year;";
        $query = DB::select($query);
        $query_months = "select id as period_month ,name as period_month_name from months order by id asc";
        $query_months = DB::select($query_months);
        foreach ($query_months as $month) {
           $month->state_importation = false;
            foreach ($query as $month_contribution) {
                if($month->period_month == $month_contribution->period_month){
                    $month->state_importation = true;
                    break;
                }
            }
           if($with_data_count)
           $month->data_count =  $this->data_count_payroll_transcript($month->period_month,$period_year);
        }
        return response()->json([
            'message' => "Exito",
            'payload' => [
                'list_months' =>  $query_months,
                'count_months' =>  count($query)
            ],
        ]);
    }
}
