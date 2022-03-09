<?php

namespace App\Http\Controllers\Contribution;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArchivoPrimarioExport;

class ImportPayrollSenasirController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/contribution/upload_copy_payroll_senasir",
     *      tags={"CONTRIBUCION-IMPORT-SENASIR"},
     *      summary="PASO 1 COPIADO DE DATOS PLANILLA SENASIR",
     *      operationId="upload_copy_payroll_senasir",
     *      description="Copiado de datos del archivo de planillas senasir a la tabla aid_contribution_copy_payroll_senasir",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *            @OA\Property(property="file", type="file", description="file required", example="file"),
     *             @OA\Property(property="date_payroll", type="string",description="fecha de planilla required",example= "2021-10-01")
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
    //copiado de datos a la tabla

     public function upload_copy_payroll_senasir(request $request)
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
                 $month = $date_payroll->format("m");
                 $date_payroll_format = $request->date_payroll;

                 $existing_period = "select  count(*) from  payroll_copy_senasirs  where mes ='$month' and a_o='$year'";
                 $existing_period = DB::connection('db_aux')->select($existing_period)[0]->count;
                    $this->delete_payroll_copy_senasirs($month,$year);
                     $file_name = "senasir-".$month."-".$year.'.'.$extencion;
                     if($file_name_entry == $file_name){
                         $base_path = 'planillas/planilla_senasir';
                         $file_path = Storage::disk('ftp')->putFileAs($base_path,$request->file,$file_name);
                         $base_path ='ftp://'.env('FTP_HOST').env('FTP_ROOT').$file_path;

                         $copy = "copy payroll_copy_senasirs(a_o, mes, id_person_titular, matricula_titular, mat_dh,departamento,regional,renta,tipo_renta,
                                  carnet,num_com, paterno, materno, p_nombre,s_nombre, ap_casada, fecha_nacimiento, clase_renta,total_ganado, total_descuentos,
                                  liquido_pagable, rentegro_r_basica, renta_dignidad, reintegro_renta_dignidad, reintegro_aguinaldo,reintegro_importe_adicional,
                                  reintegro_inc_gestion, descuento_aporte_muserpol, descuento_covipol, descuento_prestamo_musepol,carnet_tit,
                                  num_com_tit, pat_titular, mat_titular, p_nom_titular, s_nombre_titular, ap_casada_titular, fecha_nac_titular,
                                  clase_renta_tit, fec_fail_tit) FROM PROGRAM 'wget -q -O - $@  --user=$username --password=$password $base_path' WITH DELIMITER ':' CSV header;";
                         $copy = DB::connection('db_aux')->select($copy);
                         DB::commit();
                         $query_count = "select  count(*) from  payroll_copy_senasirs where mes ='$month' and a_o='$year'";
                         $query_count = DB::connection('db_aux')->select($query_count)[0]->count;

                         return response()->json([
                             'message' => 'Realizado con exito',
                             'payload' => [
                                 'successfully' => true,
                                'copied_record' => $query_count
                                //'data_count' =>  $this->data_count($month,$year,$date_payroll_format)
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

     /**
     * @OA\Post(
     *      path="/api/contribution/validation_aid_contribution_affiliate_payroll_senasir",
     *      tags={"CONTRIBUCION-IMPORT-SENASIR"},
     *      summary="PASO 2 VALIDACION DE DATOS DE TITULARES SENASIR",
     *      operationId="validation_aid_contribution_affiliate_payroll_senasir",
     *      description="validacion de datos de titulares senasir a la tabla validation_aid_contribution_affiliate_payroll_senasir",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *             @OA\Property(property="date_payroll", type="string",description="fecha de planilla required",example= "2021-10-01")
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
     public function validation_aid_contribution_affiliate_payroll_senasir(Request $request){
        $request->validate([
          'date_payroll' => 'required|date_format:"Y-m-d"',
        ]);
      try{
            DB::beginTransaction();
            $message = "No hay datos";
            $successfully =false;
            $date_payroll_format = $request->date_payroll;
            $data_cabeceraS=array(array("AÑO","MES","MATRÍCULA TITULAR", "MATRÍCULA D_H","DEPARTAMENTO","CARNET", "APELLIDO PATERNO","APELLIDO MATERNO", "PRIMER NOMBRE","SEGUNDO NOMBRE",
        "FECHA DE NACIMIENTO","CLASE DE RENTA","TOTAL GANADO","LIQUIDO PAGABLE","RENTA DIGNIDAD","DESCUENTO MUSERPOL","PATERNO TITULAR","MATERNO TITULAR"," PRIMER NOMBRE TITULAR",
        "SEGUNDO NOMBRE TITULAR","CARNET TITULAR","FECHA DE FALLECIMIENTO TITULAR"));

            $date_payroll = Carbon::parse($request->date_payroll);
            $year = (int)$date_payroll->format("Y");
            $month = (int)$date_payroll->format("m");
            $last_date = Carbon::parse($year.'-'.$month)->toDateString();
            $num_data_no_validated = 0;

            //tabla temporal
            $temporary_payroll = "CREATE temporary table aid_contribution_copy_payroll_senasirs_aux_no_exist(
                a_o integer,mes integer,matricula_titular varchar,mat_dh varchar,departamento varchar,
                carnet_num_com varchar,paterno varchar,materno varchar,p_nombre varchar,s_nombre varchar,
                fecha_nacimiento  date,clase_renta varchar,total_ganado NUMERIC(13,2) ,liquido_pagable NUMERIC(13,2),
                renta_dignidad NUMERIC(13,2),descuento_muserpol NUMERIC(13,2),pat_titular varchar,mat_titular varchar,
                p_nom_titular varchar,s_nombre_titular varchar,carnet_num_com_tit varchar,fec_fail_tit date
               );";
            $temporary_payroll = DB::select($temporary_payroll);

            if(!$this->exists_data_table_aid_contributions($month,$year)){
            $this->delete_aid_contribution_affiliate_payroll_senasirs($month,$year);
            if($this->exists_data_payroll_copy_senasirs($month,$year)){
                $query = "select * from registration_aid_contribution_affiliate_payroll_senasir($month,$year);";
                $data_validated = DB::select($query);

                if($data_validated == []){
                    $message = "Realizado con exito";
                    $successfully = true;
                }else{
                    $message = "Excel";
                    foreach ($data_validated as $row){
                        array_push($data_cabeceraS, array($row->a_o_retorno,$row->mes_retorno,$row->matricula_titular_retorno,$row->mat_dh_retorno,
                        $row->departamento_retorno,$row->carnet_num_com_retorno,$row->paterno_retorno, $row->materno_retorno, $row->p_nombre_retorno,$row->s_nombre_retorno,
                        $row->fecha_nacimiento_retorno, $row->clase_renta_retorno, $row->total_ganado_retorno, $row->liquido_pagable_retorno, $row->renta_dignidad_retorno, $row->descuento_muserpol_retorno,
                        $row->pat_titular_retorno, $row->mat_titular_retorno, $row->p_nom_titular_retorno, $row->s_nombre_titular_retorno, $row->carnet_num_com_tit_retorno, $row->fec_fail_tit_retorno
                       ));
                       $num_data_no_validated++;
                    }
                    $export = new ArchivoPrimarioExport($data_cabeceraS);
                    $file_name = 'error-senasir'.'-'.$last_date.'.xls';
                    $base_path = 'contribucion/Error-Import-Contribution-Senasir/'.'error-senasir-'.$last_date;
                    Excel::store($export,$base_path.'/'.$file_name, 'ftp');
                    $drop = "drop table if exists aid_contribution_copy_payroll_senasirs_aux_no_exist";
                    $drop = DB::select($drop);
                    $this->delete_aid_contribution_affiliate_payroll_senasirs($month,$year);
                    $this->delete_payroll_copy_senasirs($month,$year);
                }
                $consult = "select  count(*) from  aid_contribution_affiliate_payroll_senasirs where mes ='$month' and a_o='$year'";
                $consult = DB::select($consult)[0]->count;
                DB::commit();
                $data_count= $this->data_count($month,$year,$date_payroll_format);
                $data_count['num_data_not_validated'] = $num_data_no_validated;
                $data_count['num_data_validated'] =$consult;
                return response()->json([
                    'message' => $message,
                    'payload' => [
                        'successfully' => $successfully,
                        //'validated_record' => $consult,
                        'data_count' =>  $data_count
                    ],
                ]);

            }else{
                return response()->json([
                    'message' => "Error el primer paso no esta concluido.",
                    'payload' => [
                        'successfully' => $successfully,
                        'error' => 'Error el primer paso no esta concluido.'
                    ],
                ]);
            }
            }else{
                return response()->json([
                    'message' => "El periodo ya existe",
                    'payload' => [
                        'successfully' => $successfully,
                        'error' => 'El periodo ya existe'
                    ],
                ]);
            }
            }catch(Exception $e){
            DB::rollBack();
            return response()->json([
                'message' => 'Error en la busqueda de datos de titulares.',
                'payload' => [
                    'successfully' => false,
                    'error' => $e->getMessage(),
                ],
            ]);
            }
        }
         /**
     * @OA\Post(
     *      path="/api/contribution/download_fail_validated_senasir",
     *      tags={"CONTRIBUCION-IMPORT-SENASIR"},
     *      summary="DESCARGA DE ARCHIVO DE FALLA DEL PASO 2 DE VALIDACION DE DATOS AFILIADO TITULAR ",
     *      operationId="download_fail_validated_senasir",
     *      description="Descarga el archivo de falla del paso 2 de validacion de datos del afiliado titular",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *             @OA\Property(property="date_payroll", type="string",description="fecha de planilla required",example= "2021-10-01")
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
        public function download_fail_validated_senasir(Request $request){

            $request->validate([
                'date_payroll' => 'required|date_format:"Y-m-d"',
            ]);

            $date_payroll = Carbon::parse($request->date_payroll);
            $year = (int)$date_payroll->format("Y");
            $month = (int)$date_payroll->format("m");
            $last_date = Carbon::parse($year.'-'.$month)->toDateString();

            $file_name = 'error-senasir'.'-'.$last_date.'.xls';
            $base_path = 'contribucion/Error-Import-Contribution-Senasir/'.'error-senasir-'.$last_date;

            if(Storage::disk('ftp')->has($base_path.'/'.$file_name)){
                return $file = Storage::disk('ftp')->download($base_path.'/'.$file_name);
            }else{
                return abort(403, 'No existe archivo de falla senasir para mostrar');
            }
        }
    // -------------metodo para verificar si existe datos en el paso 2 -----//
    public function exists_data_table_aid_contribution_affiliate_payrroll($mes,$a_o){
        $month = $mes;
        $year = $a_o;
        $exists_data = true;
        $query = "select * from aid_contribution_affiliate_payroll_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER;";
        $verify_data = DB::select($query);

        if($verify_data == []) $exists_data = false;

        return $exists_data;
    }
    // -------------metodo para verificar si existe datos en el paso 1 -----//
    public function exists_data_payroll_copy_senasirs($mes,$a_o){
        $month = $mes;
        $year = $a_o;
        $exists_data = true;
        $query = "select * from payroll_copy_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER;";
        $verify_data = DB::connection('db_aux')->select($query);

        if($verify_data == []) $exists_data = false;

        return $exists_data;
    }
    //----------- verificar si existen datos importados senasir en tabla contribucion
    public function exists_data_table_aid_contributions($mes,$a_o){
        $month = $mes;
        $year = $a_o;
        $date_payroll = Carbon::create($year, $month, 1);
        $date_payroll = Carbon::parse($date_payroll)->format('Y-m-d');

        $exists_data = true;
        $query_origin_senasir = "SELECT id from contribution_origins where name like 'senasir'";
        $query_origin_senasir = DB::select($query_origin_senasir);

        if($query_origin_senasir != []) $id_origin_senasir =$query_origin_senasir[0]->id;
        else $id_origin_senasir = 1;//en caso que cambie el nombre senasir de la tabla contribution origin

        $query = " SELECT id from aid_contributions ac
        where month_year = '$date_payroll' and ac.contribution_origin_id = $id_origin_senasir and ac.deleted_at is null";
        $verify_data = DB::select($query);

        if($verify_data == []) $exists_data = false;

        return $exists_data;
    }

     //-----------borrado de datos de la tabla aid_contribution_affiliate_payroll_senasirs paso 2
     public function delete_aid_contribution_affiliate_payroll_senasirs($month, $year)
     {
             if($this->exists_data_table_aid_contribution_affiliate_payrroll($month,$year))
             {
                $query = "delete
                        from aid_contribution_affiliate_payroll_senasirs
                        where a_o = $year::INTEGER and mes = $month::INTEGER ";
                $query = DB::select($query);
                DB::commit();
                return true;
             }
             else
                 return false;
     }

     //------------borrado de datos de la tabla payroll_copy_senasirs paso 1
     public function delete_payroll_copy_senasirs($month, $year)
     {
             if($this->exists_data_payroll_copy_senasirs($month,$year))
             {
                $query = "delete
                        from payroll_copy_senasirs
                        where a_o = $year::INTEGER and mes = $month::INTEGER ";
                $query = DB::connection('db_aux')->select($query);
                DB::commit();
                return true;
             }
             else
                 return false;
     }


    /**
     * @OA\Get(
     *      path="/api/contribution/list_senasir_years",
     *      tags={"CONTRIBUCION-IMPORT-SENASIR"},
     *      summary="OBTIENE EL LISTADO DE AÑOS DE CONTRIBUCIONES DE SENASIR CONSECUTIVAMENTE ",
     *      operationId="list_senasir_years",
     *      description="Obtiene el listado de años de contribuciones de senasir de manera consecutiva hasta el año actual Ej 2022",
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
    public function list_senasir_years()
     {
            $start_year = 1980;
            $end_year =Carbon::now()->format('Y');
            $list_senasir_years =[];
            while ($end_year >= $start_year) {
                array_push($list_senasir_years, $start_year);
                $start_year++;
            }

            return response()->json([
                'message' => "Exito",
                'payload' => [
                    'list_senasir_years' =>  $list_senasir_years
                ],
            ]);
     }
          /**
     * @OA\Post(
     *      path="/api/contribution/list_senasir_months",
     *      tags={"CONTRIBUCION-IMPORT-SENASIR"},
     *      summary="LISTA LOS MESES QUE SE REALIZARON IMPORTACIONES DE TIPO SENASIR EN BASE A UN AÑO DADO EJ:2021",
     *      operationId="list_senasir_months",
     *      description="Lista los meses importados en la tabla aid_contributions enviando como parametro un año en especifico",
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
    public function list_senasir_months(Request $request)
     {
        $request->validate([
            'period_year' => 'required|date_format:"Y"',
        ]);
         $period_year = $request->get('period_year');
         $list_senasir_months =[];

         $query_origin_senasir = "SELECT id from contribution_origins where name like 'senasir'";
         $query_origin_senasir = DB::select($query_origin_senasir);

         if($query_origin_senasir != []) $id_origin_senasir =$query_origin_senasir[0]->id;
         else $id_origin_senasir = 1;//en caso que cambie el nombre senasir de la tabla contribution origin

         $query = "SELECT period_month, to_char(month_y, 'TMMonth') as period_month_name
         from (select to_date(month_year, 'YYYY/MM/DD') as month_y,extract(year from periods.month_year::timestamp) as period_year, extract(month from periods.month_year::timestamp) as period_month
         from (select month_year ,count(*) from  aid_contributions ac  where ac.deleted_at is null and ac.contribution_origin_id = $id_origin_senasir group by month_year ) as periods) as period_months
         where period_months.period_year = $period_year
         order by period_months.period_month";
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

    /**
     * @OA\Post(
     *      path="/api/contribution/rollback_copy_validate_senasir",
     *      tags={"CONTRIBUCION-IMPORT-SENASIR"},
     *      summary="REHACER LOS PASOS DE PASO 1 Y 2 IMPORTACION SENASIR",
     *      operationId="rollback_copy_validate_senasir",
     *      description="Para rehacer paso 1 y paso 2 de la importacion senasir",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *             @OA\Property(property="date_payroll", type="string",description="fecha de planilla required",example= "2021-10-01")
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

     public function rollback_copy_validate_senasir(Request $request)
     {
        $request->validate([
            'date_payroll' => 'required|date_format:"Y-m-d"',
          ]);
        DB::beginTransaction();
        try{
            $result['delete_step_1'] = false;
            $result['delete_step_2'] = false;
            $validated_rollback = false;
            $date_payroll = Carbon::parse($request->date_payroll);

            $year = (int)$date_payroll->format("Y");
            $month = (int)$date_payroll->format("m");

            if($this->exists_data_payroll_copy_senasirs($month,$year) || $this->exists_data_table_aid_contribution_affiliate_payrroll($month,$year)){
                $result['delete_step_1'] = $this->delete_payroll_copy_senasirs($month,$year);
                $result['delete_step_2'] = $this->delete_aid_contribution_affiliate_payroll_senasirs($month,$year);

                if($result['delete_step_1'] == true || $result['delete_step_2'] == true){
                    $validated_rollback = true;
                }
            }

            if(!$validated_rollback){
                $message = "No se rehizó, no existe datos en ningun paso";

            }else{
                $message = "Realizado con exito!";
            }

            DB::commit();

            return response()->json([
                'message' => $message,
                'payload' => [
                    'validated_rollback' =>  $validated_rollback,
                    'delete_step' =>  $result
                ],
            ]);
        }catch (Exception $e)
        {
            DB::rollback();
            return $e;
        }
     }
    /**
     * @OA\Post(
     *      path="/api/contribution/import_create_or_update_contribution_payroll_period_senasir",
     *      tags={"CONTRIBUCION-IMPORT-SENASIR"},
     *      summary="PASO 3 IMPORTACIÓN REGISTRO O ACTUALIZACIÓN DE DATOS DE LA PLANILLA SENASIR",
     *      operationId="import_create_or_update_contribution_payroll_period_senasir",
     *      description="Creacion o actualizacion de aid_contributions y actualizacion de la tabla aid_contribution_copy_payroll_senasir por periodo",
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
    public function import_create_or_update_contribution_payroll_period_senasir(Request $request){
        $request->validate([
        'period_contribution_senasir' => 'required|date_format:"Y-m-d"',
        ]);
        $user_id = Auth::user()->id;
        $successfully = false;
        $period_contribution_senasir = Carbon::parse($request->period_contribution_senasir);
        $year = (int)$period_contribution_senasir->format("Y");
        $month = (int)$period_contribution_senasir->format("m");
        $count_registered = "select count(*) from aid_contribution_affiliate_payroll_senasirs where a_o = $year::INTEGER and mes = $month::INTEGER and state='registered'";
        $count_registered = DB::select($count_registered)[0]->count;
        if((int)$count_registered == 0){
            return response()->json([
                'message' => "Error al realizar la importacion, el periodo ya fue importado.",
                'payload' => [
                    'successfully' => $successfully
                ],
            ]);
        }else{
            $query ="select import_period_payroll_contribution_senasir('$request->period_contribution_senasir',$user_id,$year,$month)";
            $query = DB::select($query);
            $count_updated = "select count(*) from aid_contribution_affiliate_payroll_senasirs where a_o = $year::INTEGER and mes = $month::INTEGER and state='updated'";
            $count_updated = DB::select($count_updated)[0]->count;
            $count_created = "select count(*) from aid_contribution_affiliate_payroll_senasirs where a_o = $year::INTEGER and mes = $month::INTEGER and state='created'";
            $count_created = DB::select($count_created)[0]->count;
            $successfully = true;
            return response()->json([
                'message' => "Realizado con exito!",
                'payload' => [
                    'successfully' => $successfully,
                    'num_created' => $count_created,
                    'num_updated' => $count_updated,
                    'num_total' => $count_created + $count_updated
                ],
            ]);
        }
    }
     /**
     * @OA\Post(
     *      path="/api/contribution/import_progress_bar",
     *      tags={"CONTRIBUCION-IMPORT-SENASIR"},
     *      summary="INFORMACION DE PROGRESO DE IMPORTACION SENASIR",
     *      operationId="import_progress_bar",
     *      description="Muestra la informacion de la importación de senasir  (-1)Si exixtio al gun error en algun paso, (100)Si todo fue exitoso, (30-60)Paso 1 y 2 (0)si esta iniciando la importación",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *             @OA\Property(property="date_payroll", type="string",description="fecha de planilla required",example= "2021-10-01")
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

    public function  import_progress_bar(Request $request){

        $request->validate([
            'date_payroll' => 'required|date_format:"Y-m-d"',
          ]);

        $date_payroll = Carbon::parse($request->date_payroll);
        $year = (int)$date_payroll->format("Y");
        $month = (int)$date_payroll->format("m");
        $message = "Exito";

        //---id---origen contribution----//
        $query_origin_senasir = "SELECT id from contribution_origins where name like 'senasir'";
        $query_origin_senasir = DB::select($query_origin_senasir);
        if($query_origin_senasir != []) $id_origin_senasir =$query_origin_senasir[0]->id;
        else $id_origin_senasir = 1;//en caso que cambie el nombre senasir de la tabla contribution origin

        $result['file_exists'] = false;
        $result['file_name'] = "";
        $result['percentage'] = 0;
        $result['query_step_1'] = false;
        $result['query_step_2'] = false;
        $result['query_step_3'] = false;

        $result['query_step_1'] = $this->exists_data_payroll_copy_senasirs($month,$year);
        $result['query_step_2'] = $this->exists_data_table_aid_contribution_affiliate_payrroll($month,$year);
        $date_payroll_format = $request->date_payroll;
        $result['query_step_3'] = $this->exists_data_table_aid_contributions($month,$year);

        //verificamos si existe el el archivo de importación 
        $date_month= strlen($month)==1?'0'.$month:$month;
        $origin_name = 'senasir-';
        $new_file_name = "senasir-".$date_month."-".$year.'.csv';
        $base_path = 'contribucion/planilla_senasir'.'/'.$new_file_name;
        if (Storage::disk('ftp')->has($base_path)) {
            $result['file_name'] = $new_file_name;
            $result['file_exists'] = true;
        }

        if($result['file_exists'] == true && $result['query_step_1'] == true && $result['query_step_2'] == true && $result['query_step_3'] == true){
            $result['percentage'] = 100;
        }else{
            if($result['file_exists'] == true && $result['query_step_1'] == true && $result['query_step_2'] == true && $result['query_step_3'] == false){
                $result['percentage'] = 60;
            }else{
                if ($result['file_exists'] == true && $result['query_step_1'] == true && $result['query_step_2'] == false && $result['query_step_3'] == false) {
                    $result['percentage'] = 30;
                } else {
                    if ($result['query_step_1'] == false && $result['query_step_2'] == false && $result['query_step_3'] == false) {
                        $result['percentage'] = 0;
                    } else {
                        $result['percentage'] = -1;
                        $message = "Error! Algo salio mal en algun paso, favor vuelva a iniciar la importación.";
                    }
                }
            }
        }

        return response()->json([
            'message' => $message,
            'payload' => [
                'import_progress_bar' =>  $result,
                'data_count' =>  $this->data_count($month,$year,$date_payroll_format)
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
        $data_count['num_total_data_aid_contributions'] = 0;
        $data_count['sum_amount_total_aid_contribution'] = 0;

        $query_origin_senasir = "SELECT id from contribution_origins where name like 'senasir'";
        $query_origin_senasir = DB::select($query_origin_senasir);
        if($query_origin_senasir != []) $id_origin_senasir =$query_origin_senasir[0]->id;
        else $id_origin_senasir = 1;//en caso que cambie el nombre senasir de la tabla contribution origin

        //---TOTAL DE DATOS DEL ARCHIVO
        $query_total_data = "SELECT * FROM aid_contribution_copy_payroll_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER;";
        $query_total_data = DB::select($query_total_data);
        $data_count['num_total_data_copy'] = count($query_total_data);

        //---NUMERO DE DATOS NO CONSIDERADOs
        $query_data_not_considered = "SELECT * FROM aid_contribution_copy_payroll_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER and clase_renta like 'ORFANDAD%';";
        $query_data_not_considered = DB::select($query_data_not_considered);
        $data_count['num_data_not_considered'] = count($query_data_not_considered);

        //---NUMERO DE DATOS CONSIDERADOS
        $query_data_considered = "SELECT * FROM aid_contribution_copy_payroll_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER and clase_renta not like 'ORFANDAD%';";
        $query_data_considered = DB::select($query_data_considered);
        $data_count['num_data_considered'] = count($query_data_considered);

        //---NUMERO DE DATOS VALIDADOS
        $query_data_validated = "SELECT * FROM aid_contribution_affiliate_payroll_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER;";
        $query_data_validated = DB::select($query_data_validated);
        $data_count['num_data_validated'] = count($query_data_validated);

        //---NUMERO DE DATOS NO VALIDADOS
        $data_count['num_data_not_validated'] = $data_count['num_data_considered'] - $data_count['num_data_validated'];

        //---TOTAL DE REGISTROS AID_CONTRIBUTIONS
        $query_data_aid_contributions = "SELECT id from aid_contributions ac
        where month_year = '$date_payroll_format' and ac.contribution_origin_id = $id_origin_senasir and ac.deleted_at is null";
        $query_data_aid_contributions = DB::select($query_data_aid_contributions);
        $data_count['num_total_data_aid_contributions'] = count($query_data_aid_contributions);

        //---suma monto total contribucion
        $query_sum_amount = "SELECT sum(ac.total) as amount_total from aid_contributions ac
        where month_year = '$date_payroll_format' and ac.contribution_origin_id = $id_origin_senasir and ac.deleted_at is null";
        $query_sum_amount = DB::select($query_sum_amount);
        $data_count['sum_amount_total_aid_contribution'] = isset($query_sum_amount[0]->amount_total) ? floatval($query_sum_amount[0]->amount_total):0;

        return  $data_count;
    }

            /**
     * @OA\Post(
     *      path="/api/contribution/list_months_validate_senasir",
     *      tags={"CONTRIBUCION-IMPORT-SENASIR"},
     *      summary="LISTA LOS MESES QUE SE REALIZARON IMPORTACIONES DE TIPO SENASIR EN BASE A UN AÑO DADO EJ:2021",
     *      operationId="list_months_validate_senasir",
     *      description="Lista los meses importados en la tabla payroll_copy_senasirs enviando como parametro un año en especifico",
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
    public function list_months_validate_senasir(Request $request)
    {
       $request->validate([
           'period_year' => 'required|date_format:"Y"',
       ]);
        $period_year = $request->get('period_year');
        $query = "SELECT  distinct mes,a_o,  to_char( (to_date(a_o|| '-' ||mes, 'YYYY/MM/DD')), 'TMMonth') as period_month_name from payroll_validated_senasirs where deleted_at  is null and a_o =$period_year group by mes, a_o ";
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
           $date_payroll_format = Carbon::parse($period_year.'-'.$month->period_month.'-'.'01')->toDateString();
           $month->data_count = $this->data_count_payroll_senasir($month->period_month,$period_year,$date_payroll_format);
        }

        return response()->json([
           'message' => "Exito",
           'payload' => [
               'list_senasir_months' =>  $query_months,
               'count_senasir_months' =>  count($query)
           ],
       ]);
    }
    public function data_count_payroll_senasir($mes,$a_o,$date_payroll_format){
        $month = $mes;
        $year = $a_o;
        $data_count['num_total_data_copy'] = 0;
        $data_count['num_data_not_considered'] = 0;
        $data_count['num_data_considered'] = 0;
        $data_count['num_data_validated'] = 0;
        $data_count['num_data_not_validated'] = 0;

        //---TOTAL DE DATOS DEL ARCHIVO
        $query_total_data = "SELECT * FROM payroll_copy_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER;";
        $query_total_data = DB::connection('db_aux')->select($query_total_data);
        $data_count['num_total_data_copy'] = count($query_total_data);

        //---NUMERO DE DATOS NO CONSIDERADOS
        $query_data_not_considered = "SELECT * FROM payroll_copy_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER and clase_renta like 'ORFANDAD%';";
        $query_data_not_considered = DB::connection('db_aux')->select($query_data_not_considered);
        $data_count['num_data_not_considered'] = count($query_data_not_considered);

        //---NUMERO DE DATOS CONSIDERADOS
        $query_data_considered = "SELECT * FROM payroll_copy_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER and clase_renta not like 'ORFANDAD%';";
        $query_data_considered = DB::connection('db_aux')->select($query_data_considered);
        $data_count['num_data_considered'] = count($query_data_considered);

        //---NUMERO DE DATOS VALIDADOS
        $query_data_validated = "SELECT * FROM payroll_validated_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER;";
        $query_data_validated = DB::select($query_data_validated);
        $data_count['num_data_validated'] = count($query_data_validated);
         //---NUMERO DE DATOS NO VALIDADOS
        $data_count['num_data_not_validated'] = $data_count['num_data_considered'] - $data_count['num_data_validated'];

        return  $data_count;
    }
}
