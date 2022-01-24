<?php

namespace App\Http\Controllers\Contribution;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use Auth;

class ImportPayrollSenasirController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/contribution/upload_copy_payroll_senasir",
     *      tags={"CONTRIBUCION"},
     *      summary="PASO 1 COPIADO DE DATOS PLANILLA SENASIR",
     *      operationId="upload_copy_payroll_senasir",
     *      description="Copiado de datos del archivo de planillas senasir a la tabla aid_contribution_copy_payroll_senasir",
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
}
