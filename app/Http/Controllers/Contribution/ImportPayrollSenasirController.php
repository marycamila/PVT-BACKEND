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
     *      tags={"IMPORT-PAYROLL-SENASIR"},
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

                         return response()->json([
                             'message' => 'Realizado con exito',
                             'payload' => [
                                 'successfully' => true,
                                'data_count' =>  $this->data_count_payroll_senasir($month,$year,$date_payroll_format)
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
     *      path="/api/contribution/validation_payroll_senasir",
     *      tags={"IMPORT-PAYROLL-SENASIR"},
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

    public function validation_payroll_senasir(Request $request){
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

            if($this->exists_data_payroll_copy_senasirs($month,$year)){
                if(!$this->exists_data_payroll_validated_senasirs($month,$year)){

                    $query = "select registration_payroll_validated_senasir('dbname=platform_auxiliar_2 port=5432 host=192.168.2.242 user=admin password=admin',2,2022);";
                    $data_validated = DB::select($query);

                        if($data_validated[0]->registration_payroll_validated_senasir > 0){
                            $message = "Realizado con exito";
                            $successfully = true;
                            $data_payroll_copy_senasir = "select  * from  payroll_copy_senasirs  where mes ='$month' and a_o='$year' and is_validated = false and clase_renta not like 'ORFANDAD%'";
                            $data_payroll_copy_senasir = DB::connection('db_aux')->select($data_payroll_copy_senasir);
                            if(count($data_payroll_copy_senasir)> 0){
                                $message = "Excel";
                                foreach ($data_payroll_copy_senasir as $row){
                                    array_push($data_cabeceraS, array($row->a_o ,$row->mes ,$row->matricula_titular ,$row->mat_dh ,
                                    $row->departamento ,$row->carnet ,$row->paterno , $row->materno , $row->p_nombre ,$row->s_nombre ,
                                    $row->fecha_nacimiento , $row->clase_renta , $row->total_ganado , $row->liquido_pagable , $row->renta_dignidad , $row->renta_dignidad ,
                                    $row->pat_titular , $row->mat_titular , $row->p_nom_titular , $row->s_nombre_titular , $row->carnet_tit , $row->fec_fail_tit 
                                ));
                                $num_data_no_validated++;
                                }
                                $export = new ArchivoPrimarioExport($data_cabeceraS);
                                $file_name = 'no-encontrados-planilla-senasir'.'-'.$last_date.'.xls';
                                $base_path = 'planillas/no-encontrados-planilla-senasir/'.$file_name;
                                Excel::store($export,$base_path.'/'.$file_name, 'ftp');
                            }
                        }
                    DB::commit();
                    $data_count= $this->data_count_payroll_senasir($month,$year,$date_payroll_format);
                    return response()->json([
                        'message' => $message,
                        'payload' => [
                            'successfully' => $successfully,
                            'data_count' =>  $data_count
                        ],
                    ]);
                }else{
                    return response()->json([
                        'message' => " Error! ya realizó la validación de datos",
                        'payload' => [
                            'successfully' => $successfully,
                            'error' => 'Error! ya realizó la validación de datos.'
                        ],
                    ]);
                }

            }else{
                return response()->json([
                    'message' => "Error no existen datos en la tabla del copiado de datos",
                    'payload' => [
                        'successfully' => $successfully,
                        'error' => 'Error el primer paso no esta concluido.'
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
     *      path="/api/contribution/download_fail_not_found_payroll_senasir",
     *      tags={"IMPORT-PAYROLL-SENASIR"},
     *      summary="DESCARGA DE ARCHIVO DE NO ENCONTRADOS DEL PASO 2 DE VALIDACION DE DATOS AFILIADO TITULAR ",
     *      operationId="download_fail_not_found_payroll_senasir",
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
        public function download_fail_not_found_payroll_senasir(Request $request){

            $request->validate([
                'date_payroll' => 'required|date_format:"Y-m-d"',
            ]);

            $date_payroll = Carbon::parse($request->date_payroll);
            $year = (int)$date_payroll->format("Y");
            $month = (int)$date_payroll->format("m");
            $last_date = Carbon::parse($year.'-'.$month)->toDateString();

            $file_name = 'no-encontrados-planilla-senasir'.'-'.$last_date.'.xls';
            $base_path = 'planillas/no-encontrados-planilla-senasir/'.$file_name;

            if(Storage::disk('ftp')->has($base_path.'/'.$file_name)){
                return $file = Storage::disk('ftp')->download($base_path.'/'.$file_name);
            }else{
                return abort(403, 'No existe archivo de falla senasir para mostrar');
            }
        }
    // -------------metodo para verificar si existe datos en el paso 2 -----//
    public function exists_data_payroll_validated_senasirs($mes,$a_o){
        $month = $mes;
        $year = $a_o;
        $exists_data = true;
        $query = "select * from payroll_validated_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER;";
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
     public function delete_payroll_validated_senasirs($month, $year)
     {
             if($this->exists_data_payroll_validated_senasirs($month,$year))
             {
                $query = "delete
                        from payroll_validated_senasirs
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
     *      tags={"METODOS-GLOBALES"},
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
     *      path="/api/contribution/rollback_payroll_copy_senasir",
     *      tags={"IMPORT-PAYROLL-SENASIR"},
     *      summary="REHACER LOS PASOS DE PASO 1 Y 2 IMPORTACION SENASIR",
     *      operationId="rollback_payroll_copy_senasir",
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

     public function rollback_payroll_copy_senasir(Request $request)
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

            if($this->exists_data_payroll_copy_senasirs($month,$year) || $this->exists_data_payroll_validated_senasirs($month,$year)){
                $result['delete_step_1'] = $this->delete_payroll_copy_senasirs($month,$year);
                $result['delete_step_2'] = $this->delete_payroll_validated_senasirs($month,$year);

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
     *      path="/api/contribution/import_payroll_senasir_progress_bar",
     *      tags={"IMPORT-PAYROLL-SENASIR"},
     *      summary="INFORMACION DE PROGRESO DE IMPORTACION SENASIR",
     *      operationId="import_payroll_senasir_progress_bar",
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

    public function  import_payroll_senasir_progress_bar(Request $request){

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
        $result['query_step_2'] = $this->exists_data_payrroll_validated_senasirs($month,$year);
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
     *      tags={"IMPORT-PAYROLL-SENASIR"},
     *      summary="LISTA LOS MESES QUE SE REALIZARON IMPORTACIONES PLANILLA DE TIPO SENASIR EN BASE A UN AÑO DADO EJ:2021",
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
