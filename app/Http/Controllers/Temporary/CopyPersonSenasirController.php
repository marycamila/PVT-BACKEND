<?php

namespace App\Http\Controllers\Temporary;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Util;


class CopyPersonSenasirController extends Controller
{   /**
    * @OA\Post(
    *      path="/api/temporary/upload_copy_person_senasir",
    *      tags={"IMPORTACION-IDS-PERSONAS-SENASIR"},
    *      summary="PASO 1 COPIADO DE DATOS DE AFFILIADOS Y ESPOSAS SENASIR",
    *      operationId="upload_copy_person_senasir",
    *      description="Copiado de datos del archivo de afiliados senasir a la tabla copy_person_senasirs",
    *      @OA\RequestBody(
    *          description= "Provide auth credentials",
    *          required=true,
    *          @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
    *            @OA\Property(property="file", type="file", description="file required", example="file")
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
   //copiado de datos de afiliados y esposas a la tabla copy_person_senasir
    public function upload_copy_person_senasir(request $request){
        $request->validate([
        'file' => 'required'
        ]);
        $extension = strtolower($request->file->getClientOriginalExtension());
        DB::beginTransaction();
        try{
            $username = env('FTP_USERNAME');
            $password = env('FTP_PASSWORD');
            $file_name = 'afiliados_senasir'.'.'.$extension;
            $db_connection_name_dblink = env("DB_CONNECTION_DBLINK");
            //crear la conexion DBLINK a la base de datos principal
            Util::open_connect_database_default();
            if($extension == "csv"){
            $base_path = 'afiliados';
            $file_path = Storage::disk('ftp')->putFileAs($base_path,$request->file,$file_name);
            $base_path ='ftp://'.env('FTP_HOST').env('FTP_ROOT').$file_path;
            $temporary_person = "create temporary table tmp_copy_person_senasir_aux(id_person_senasir integer,matricula_tit varchar,carnet_tit varchar,
            num_com_tit varchar, p_nom_tit varchar, s_nombre_tit varchar, paterno_tit varchar, materno_tit varchar, ap_casada_tit varchar,
            fecha_nacimiento_tit date, genero_tit varchar, fec_fail_tit date, clase_renta_tit varchar, matricula_dh varchar, carnet_dh varchar, num_com_dh varchar,
            p_nombre_dh varchar, s_nombre_dh varchar, paterno_dh varchar, materno_dh varchar, ap_casada_dh varchar, fecha_nacimiento_dh date, genero_dh varchar, fec_fail_dh varchar, clase_renta_dh varchar);";
            $temporary_person = DB::connection('db_aux')->select($temporary_person);

            $copy = "copy tmp_copy_person_senasir_aux(id_person_senasir, matricula_tit, carnet_tit,
            num_com_tit, p_nom_tit, s_nombre_tit, paterno_tit, materno_tit, ap_casada_tit, fecha_nacimiento_tit, genero_tit, fec_fail_tit, clase_renta_tit, matricula_dh, carnet_dh, num_com_dh,
            p_nombre_dh, s_nombre_dh, paterno_dh, materno_dh, ap_casada_dh, fecha_nacimiento_dh, genero_dh, fec_fail_dh, clase_renta_dh)
            FROM PROGRAM 'wget -q -O - $@  --user=$username --password=$password $base_path'
                    WITH DELIMITER ':' CSV header;";
            $copy = DB::connection('db_aux')->select($copy);
            $insert = "INSERT INTO copy_dat_senasirs(id_person_senasir, matricula_tit, carnet_tit, num_com_tit, concat_carnet_num_com_tit, p_nom_tit, s_nombre_tit, paterno_tit, materno_tit, ap_casada_tit, fecha_nacimiento_tit, genero_tit, fec_fail_tit, clase_renta_tit, matricula_dh, carnet_dh, num_com_dh, concat_carnet_num_com_dh, p_nombre_dh, s_nombre_dh, paterno_dh, materno_dh, ap_casada_dh, fecha_nacimiento_dh, genero_dh, fec_fail_dh, clase_renta_dh, created_at)
                            SELECT id_person_senasir, matricula_tit, carnet_tit, num_com_tit,(select * from dblink('$db_connection_name_dblink','SELECT * FROM concat_identity_card_complement('''||carnet_tit||''','''||num_com_tit||''')'::text) as  uu(concat_carnet_num_com character varying(250))) as concat_carnet_num_com_tit, p_nom_tit, s_nombre_tit, paterno_tit, s_nombre,materno_tit, ap_casada_tit, fecha_nacimiento_tit, genero_tit,fec_fail_tit, clase_renta_tit,
                            matricula_dh, carnet_dh, num_com_dh, (select * from dblink('$db_connection_name_dblink','SELECT * FROM concat_identity_card_complement('''||carnet_dh||''','''||num_com_dh||''')'::text) as  uu(concat_carnet_num_com_dh character varying(250)))  as concat_carnet_num_com_dh, p_nombre_dh, s_nombre_dh, paterno_dh, materno_dh, ap_casada_dh, fecha_nacimiento_dh, genero_dh, fec_fail_dh, clase_renta_dh, current_timestamp  as created_at FROM  copy_person_senasir;";
            $insert = DB::connection('db_aux')->select($insert);
            $drop = "drop table if exists tmp_copy_person_senasir_aux";
            $drop = DB::select($drop);
            Util::close_conection_database_default();
            DB::commit();
            $consult = "select  count(*) from copy_person_senasir";
                        $consult = DB::connection('db_aux')->select($consult)[0]->count;
                        return response()->json([
                            'message' => 'Realizado con exito',
                            'payload' => [
                                'successfully' => true,
                                'copied_record' => $consult
                            ],
                        ]);
            }else{
            return response()->json([
                'message' => 'Error al subir el archivo',
                'payload' => [
                    'successfully' => false,
                    'copied_record' => 0
                ],
            ]);
            }
        } catch(Exception $e){
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
    *      path="/api/temporary/data_senasir_type_spouses",
    *      tags={"IMPORTACION-IDS-PERSONAS-SENASIR"},
    *      summary="PASO 2 COPIADO DE ID DE PERSONAS SENASIR DE TIPO VIUDEDAD Y CREACION DE AFILIADOS",
    *      operationId="data_senasir_type_spouses",
    *      description="Importacion de afiliados y data de senasir ",
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

    public function data_senasir_type_spouses(Request $request){
    DB::beginTransaction();
    try{
        $dbname_input = ENV('DB_DATABASE_AUX');
        $port_input = ENV('DB_PORT_AUX');
        $host_input = ENV('DB_HOST_AUX');
        $user_input = ENV('DB_USERNAME_AUX');
        $password_input = ENV('DB_PASSWORD_AUX');

        $update_by_registration = DB::select("select tmp_senasir_update_by_registration('dbname=$dbname_input port=$port_input host=$host_input user=$user_input password=$password_input')");
        $update_by_identity = DB::select("select tmp_senasir_update_by_identity('dbname=$dbname_input port=$port_input host=$host_input user=$user_input password=$password_input')");
        $update_by_full_name_fail = DB::select("select tmp_senasir_update_by_full_name_fail('dbname=$dbname_input port=$port_input host=$host_input user=$user_input password=$password_input')");
        $create_affiliates_senasir = DB::select("select tmp_senasir_create_affiliates_senasir('dbname=$dbname_input port=$port_input host=$host_input user=$user_input password=$password_input')");
        DB::commit();
        return response()->json([
            'message' => 'Realizado con exito',
            'payload' => [
                'successfully' => true,
                'update_by_registration' => $update_by_registration[0]->tmp_senasir_update_by_registration,
                'update_by_identity' => $update_by_identity[0]->tmp_senasir_update_by_identity,
                'update_by_full_name_fail' => $update_by_full_name_fail[0]->tmp_senasir_update_by_full_name_fail,
                'create_affiliates_senasir' => $create_affiliates_senasir[0]->tmp_senasir_create_affiliates_senasir
            ],
        ]);
    } catch(Exception $e){
        DB::rollBack();
        return response()->json([
            'message' => 'Error en la importación',
            'payload' => [
                'successfully' => false,
                'error' => $e->getMessage(),
            ],
        ]);
        }
    }
    /**
    * @OA\Post(
    *      path="/api/temporary/data_senasir_type_affiliate",
    *      tags={"IMPORTACION-IDS-PERSONAS-SENASIR"},
    *      summary="PASO 2 COPIADO DE ID DE PERSONAS SENASIR DE TIPO VEJEZ Y CREACION DE AFILIADOS",
    *      operationId="data_senasir_type_affiliate",
    *      description="Importacion de afiliados y data de senasir ",
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
    public function data_senasir_type_affiliate(){
        DB::beginTransaction();
        try{
            $db_host_aux= env("DB_HOST_AUX");
            $db_port_aux = env("DB_PORT_AUX");
            $db_database_aux = env("DB_DATABASE_AUX");
            $db_username_aux= env("DB_USERNAME_AUX");
            $db_password_aux = env("DB_PASSWORD_AUX");
            $insert = "select tmp_update_affiliate_ids_senasir('hostaddr=$db_host_aux port=$db_port_aux dbname=$db_database_aux user=$db_username_aux password=$db_password_aux');";
           
            $insert = DB::select($insert);
           DB::commit();
          return response()->json([
          'message' => 'Realizado con exito',
          'payload' => [
              'successfully' => true,
              'insert'=>$insert,
          ],
      ]);
        } catch(Exception $e){
        DB::rollBack();
        return response()->json([
            'message' => 'Error en la importación',
            'payload' => [
                'successfully' => false,
                'error' => $e->getMessage(),
            ],
        ]);
      }
    }

}
