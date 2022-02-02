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

                 $existing_period = "select  count(*) from  aid_contribution_copy_payroll_senasirs  where mes ='$month' and a_o='$year'";
                 $existing_period = DB::select($existing_period)[0]->count;
                 //return $existing_period;
                 if($existing_period == 0){
                     $file_name = "senasir-".$month."-".$year.'.'.$extencion;
                     if($file_name_entry == $file_name){
                         $base_path = 'contribucion/planilla_senasir';
                         $file_path = Storage::disk('ftp')->putFileAs($base_path,$request->file,$file_name);
                         $base_path ='ftp://'.env('FTP_HOST').env('FTP_ROOT').$file_path;
 
                         $temporary_payroll = "create temporary table aid_contribution_copy_payroll_senasirs_aux(a_o integer,mes integer,matricula_titular varchar, mat_dh varchar, departamento varchar, regional varchar, renta varchar,
                         tipo_renta varchar, carnet varchar, num_com varchar, paterno varchar, materno varchar, p_nombre varchar, s_nombre varchar, fecha_nacimiento date, clase_renta varchar, 
                         total_ganado NUMERIC(13,2),
                         total_descuentos NUMERIC(13,2), liquido_pagable NUMERIC(13,2),
                         renta_basica NUMERIC(13,2), rentegro_r_basica NUMERIC(13,2), bono_del_estado NUMERIC(13,2), adicion_ivm NUMERIC(13,2),
                         incremento_acumulado NUMERIC(13,2), renta_complementaria NUMERIC(13,2),  renta_dignidad NUMERIC(13,2), reintegro_renta_dignidad NUMERIC(13,2), aguinaldo_renta_dignidad NUMERIC(13,2),
                         inc_al_minimo_nacional NUMERIC(13,2), reintegro_aguinaldo NUMERIC(13,2), bono_ips_ds_27760 NUMERIC(13,2), beneficios_adicionales NUMERIC(13,2), plus_afps NUMERIC(13,2), resolucion_15_95 NUMERIC(13,2),
                         importe_adicional NUMERIC(13,2), reintegro_importe_adicional NUMERIC(13,2), bono_adicional_ip2006 NUMERIC(13,2), ajuste_adicional NUMERIC(13,2), incremento_gestion NUMERIC(13,2), 
                         reintegro_inc_gestion NUMERIC(13,2), incr_inv_prop_ip_gestion NUMERIC(13,2), caja_nacional_de_salud NUMERIC(13,2), caja_salud_banca_privada NUMERIC(13,2), conf_nac_jubil_rent_bolivia NUMERIC(13,2),
                         conf_nac_maestros_jubilados NUMERIC(13,2), desc_a_favor_cnjrb NUMERIC(13,2), moneda_fraccionada NUMERIC(13,2), pago_indebido_ivm NUMERIC(13,2), pago_adelantado_pra_ivm NUMERIC(13,2),
                         desc_cobro_indebido_r026_99_ivm NUMERIC(13,2), retencion_judicial NUMERIC(13,2), descuento_muserpol NUMERIC(13,2), descuento_covipol NUMERIC(13,2), prestamo_muserpol NUMERIC(13,2),
                         pat_titular varchar, mat_titular varchar, p_nom_titular varchar, s_nombre_titular varchar, clase_rent_tit varchar, carnet_tit varchar, num_com_tit varchar, fec_fail_tit date);";
                         //return $temporary_payroll;
                         $temporary_payroll = DB::select($temporary_payroll);

                         //return $temporary_payroll;
                         $copy = "copy aid_contribution_copy_payroll_senasirs_aux(a_o ,mes ,matricula_titular , mat_dh , departamento , regional , renta ,
                         tipo_renta , carnet , num_com , paterno , materno , p_nombre , s_nombre , fecha_nacimiento , clase_renta ,
                         total_ganado ,
                         total_descuentos , liquido_pagable ,
                         renta_basica , rentegro_r_basica , bono_del_estado , adicion_ivm ,
                         incremento_acumulado , renta_complementaria ,  renta_dignidad , reintegro_renta_dignidad , aguinaldo_renta_dignidad ,
                         inc_al_minimo_nacional , reintegro_aguinaldo , bono_ips_ds_27760 , beneficios_adicionales , plus_afps , resolucion_15_95 ,
                         importe_adicional , reintegro_importe_adicional , bono_adicional_ip2006 , ajuste_adicional , incremento_gestion ,
                         reintegro_inc_gestion , incr_inv_prop_ip_gestion , caja_nacional_de_salud , caja_salud_banca_privada , conf_nac_jubil_rent_bolivia ,
                         conf_nac_maestros_jubilados , desc_a_favor_cnjrb , moneda_fraccionada , pago_indebido_ivm , pago_adelantado_pra_ivm ,
                         desc_cobro_indebido_r026_99_ivm , retencion_judicial , descuento_muserpol , descuento_covipol , prestamo_muserpol ,
                         pat_titular , mat_titular , p_nom_titular , s_nombre_titular , clase_rent_tit , carnet_tit , num_com_tit , fec_fail_tit )
                         FROM PROGRAM 'wget -q -O - $@  --user=$username --password=$password $base_path'
                                 WITH DELIMITER ':' CSV header;";
                         $copy = DB::select($copy);
 
                         $insert = "INSERT INTO aid_contribution_copy_payroll_senasirs(a_o,mes,matricula_titular,mat_dh,departamento,carnet,num_com,paterno,materno,
                         p_nombre,s_nombre,fecha_nacimiento,clase_renta,total_ganado,liquido_pagable,renta_dignidad,descuento_muserpol,
                         pat_titular,mat_titular,p_nom_titular,s_nombre_titular,carnet_tit,num_com_tit,fec_fail_tit)
                                    SELECT a_o,mes,matricula_titular,mat_dh,departamento,carnet,num_com,paterno,materno,
                        p_nombre,s_nombre,fecha_nacimiento,clase_renta,total_ganado,liquido_pagable,renta_dignidad,descuento_muserpol,
                        pat_titular,mat_titular,p_nom_titular,s_nombre_titular,carnet_tit,num_com_tit,fec_fail_tit FROM  aid_contribution_copy_payroll_senasirs_aux; ";
                         $insert = DB::select($insert);
                       //  return $insert;
                    DB::commit();
 
                         $drop = "drop table if exists aid_contribution_copy_payroll_senasirs_aux";
                         $drop = DB::select($drop);
 
                         $consult = "select  count(*) from  aid_contribution_copy_payroll_senasirs where mes ='$month' and a_o='$year'";
                         $consult = DB::select($consult)[0]->count;
                         //return $consult;
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
            $data_cabeceraS=array(array("AÑO","MES","MATRÍCULA TITULAR", "MATRÍCULA D_H","DEPARTAMENTO","CARNET","NUM_COM", "APELLIDO PATERNO","APELLIDO MATERNO", "PRIMER NOMBRE","SEGUNDO NOMBRE"));

            $date_payroll = Carbon::parse($request->date_payroll);
            $year = (int)$date_payroll->format("Y");
            $month = (int)$date_payroll->format("m");
            $last_date = Carbon::parse($year.'-'.$month)->toDateString();

            //tabla temporal
            $temporary_payroll = "CREATE temporary table aid_contribution_copy_payroll_senasirs_aux_no_exist(
            a_o integer, mes integer, matricula_titular varchar, mat_dh varchar, departamento varchar, clase_renta varchar,
            carnet varchar, num_com varchar, paterno varchar, materno varchar, p_nombre varchar, s_nombre varchar);";
            $temporary_payroll = DB::select($temporary_payroll);

        if(!$this->exists_data_table_aid_contribution_affiliate_payrroll($month,$year)){
            if($this->exists_data_table_aid_contribution_copy_payroll_senasirs($month,$year)){
                $query = "select * from registration_aid_contribution_affiliate_payroll_senasir($month,$year);";
                $data_format = DB::select($query);

                if($data_format == []){
                    $message = "Realizado con exito";
                    $successfully = true;
                }else{
                    $message = "Error! Las matriculas de los siguientes titulare no fueron encontradas";
                    foreach ($data_format as $row){
                        array_push($data_cabeceraS, array($row->a_o_retorno,$row->mes_retorno,$row->matricula_titular_retorno, $row->mat_dh_retorno,$row->departamento_retorno,
                        $row->carnet_retorno,$row->num_com_retorno,
                        $row->paterno_retorno,$row->materno_retorno,$row->p_nombre_retorno,$row->s_nombre_retorno
                       ));
                    }
                    $export = new ArchivoPrimarioExport($data_cabeceraS);
                    $file_name = 'error-senasir'.'-'.$last_date.'.xls';
                    $base_path = 'contribucion/Error-Import-Contribution-Senasir/'.'error-senasir-'.$last_date;
                    Excel::store($export,$base_path.'/'.$file_name, 'ftp');
                    $drop = "drop table if exists aid_contribution_copy_payroll_senasirs_aux_no_exist";
                    $drop = DB::select($drop);
                    $this->delete_aid_contribution_affiliate_payroll_senasirs($month,$year);
                    $this->delete_aid_contribution_copy_payroll_senasirs($month,$year);
                }
                DB::commit();
                return response()->json([
                    'message' => $message,
                    'payload' => [
                        'successfully' => $successfully,
                    ],
                ]);

            }else{
                return response()->json([
                    'message' => "Error el primer paso no esta concluido.",
                    'payload' => [
                        'successfully' => $successfully,
                    ],
                ]);
            }
            }else{
                return response()->json([
                    'message' => "Ya existen datos, no se puede volver a realizar esta acción.",
                    'payload' => [
                        'successfully' => $successfully,
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
    public function exists_data_table_aid_contribution_copy_payroll_senasirs($mes,$a_o){
        $month = $mes;
        $year = $a_o;
        $exists_data = true;
        $query = "select * from aid_contribution_copy_payroll_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER;";
        $verify_data = DB::select($query);

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
         DB::beginTransaction();
         try{
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
         catch (Exception $e)
         {
             DB::rollback();
             return $e;
         }
     }

     //------------borrado de datos de la tabla aid_contribution_copy_payroll_senasirs paso 1
     public function delete_aid_contribution_copy_payroll_senasirs($month, $year)
     {
         DB::beginTransaction();
         try{
             if($this->exists_data_table_aid_contribution_copy_payroll_senasirs($month,$year))
             {
                $query = "delete
                        from aid_contribution_copy_payroll_senasirs
                        where a_o = $year::INTEGER and mes = $month::INTEGER ";
                $query = DB::select($query);
                DB::commit();
                return true;
             }
             else
                 return false;
         }
         catch (Exception $e)
         {
             DB::rollback();
             return $e;
         }
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

            if($this->exists_data_table_aid_contribution_copy_payroll_senasirs($month,$year) || $this->exists_data_table_aid_contribution_affiliate_payrroll($month,$year)){
                $result['delete_step_1'] = $this->delete_aid_contribution_affiliate_payroll_senasirs($month,$year);
                $result['delete_step_2'] = $this->delete_aid_contribution_copy_payroll_senasirs($month,$year);

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
                'message' => "Numero de registro creados iagual a ".$count_created." Numeros de registros actualizados igual a ".$count_updated,
                'payload' => [
                    'successfully' => $successfully
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
        $result['reg_copy'] = 0;
        $result['reg_validation'] = 0;
        $result['reg_contribution'] = 0;

        //paso1
        $result['query_step_1'] = $this->exists_data_table_aid_contribution_copy_payroll_senasirs($month,$year);
        $query = "select * from aid_contribution_copy_payroll_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER;";
        $verify_data = DB::select($query);
        $result['reg_copy'] = count($verify_data);

        //paso 2
        $result['query_step_2'] = $this->exists_data_table_aid_contribution_affiliate_payrroll($month,$year);
        $query = "select * from aid_contribution_affiliate_payroll_senasirs where mes = $month::INTEGER and a_o = $year::INTEGER;";
        $verify_data = DB::select($query);
        $result['reg_validation'] = count($verify_data);

        //paso3
        $date_payroll_format = $request->date_payroll;
        $result['query_step_3'] = $this->exists_data_table_aid_contributions($month,$year);
        $query = " SELECT id from aid_contributions ac
        where month_year = '$date_payroll_format' and ac.contribution_origin_id = $id_origin_senasir and ac.deleted_at is null";
        $verify_data = DB::select($query);
        $result['reg_contribution'] = count($verify_data);

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
                'import_progress_bar' =>  $result
            ],
        ]);
    }
}
