<?php

namespace App\Http\Controllers\Temporary;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Util;


class TmpCopyDataSenasirController extends Controller
{   /**
    * @OA\Post(
    *      path="/api/temporary/upload_copy_affiliate_spouse_senasir",
    *      tags={"AFFILIATE-IMPORT-SENASIR"},
    *      summary="PASO 1 COPIADO DE DATOS DE AFFILIADOS Y ESPOSAS SENASIR",
    *      operationId="upload_copy_affiliate_spouse_senasir",
    *      description="Copiado de datos del archivo de afiliados senasir a la tabla tmp_copy_data_senasirs",
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
   //copiado de datos de afiliados y esposas a la tabla tmp_copy_data_senasirs
    public function upload_copy_affiliate_spouse_senasir(request $request){
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
            $temporary_payroll = "create temporary table tmp_copy_data_senasir_aux(id_person_senasir integer,matricula_titular varchar, mat_dh varchar,
            carnet varchar, num_com varchar, paterno varchar, materno varchar, p_nombre varchar, s_nombre varchar, ap_casada varchar, fecha_nacimiento date, lugar_nacimiento varchar, clase_renta varchar,
            pat_titular varchar, mat_titular varchar, p_nom_titular varchar, s_nombre_titular varchar, ap_casada_titular varchar, carnet_tit varchar, num_com_tit varchar, fec_fail_tit date, lugar_nacimiento_tit varchar);";
            $temporary_payroll = DB::connection('db_aux')->select($temporary_payroll);

            $copy = "copy tmp_copy_data_senasir_aux(id_person_senasir ,matricula_titular , mat_dh ,
            carnet , num_com , paterno , materno , p_nombre , s_nombre , ap_casada , fecha_nacimiento , lugar_nacimiento , clase_renta ,
            pat_titular , mat_titular , p_nom_titular , s_nombre_titular , ap_casada_titular , carnet_tit , num_com_tit , fec_fail_tit , lugar_nacimiento_tit )
            FROM PROGRAM 'wget -q -O - $@  --user=$username --password=$password $base_path'
                    WITH DELIMITER ':' CSV header;";
            $copy = DB::connection('db_aux')->select($copy);
            $insert = "INSERT INTO tmp_copy_data_senasirs(id_person_senasir, matricula_titular, mat_dh,
                        carnet, num_com , concat_carnet_num_com, paterno, materno, p_nombre, s_nombre, ap_casada, fecha_nacimiento, lugar_nacimiento, clase_renta,
                        pat_titular, mat_titular, p_nom_titular, s_nombre_titular, ap_casada_titular, carnet_tit, num_com_tit, concat_carnet_num_com_tit, fec_fail_tit, lugar_nacimiento_tit,created_at)
                                   SELECT id_person_senasir, matricula_titular, mat_dh, carnet, num_com ,(select * from dblink('$db_connection_name_dblink','SELECT * FROM concat_identity_card_complement('''||carnet||''','''||carnet||''')'::text) as  uu(concat_carnet_num_com character varying(250))) as concat_carnet_num_com, paterno, materno, p_nombre, s_nombre, ap_casada, fecha_nacimiento, lugar_nacimiento, clase_renta,
                        pat_titular, mat_titular, p_nom_titular, s_nombre_titular, ap_casada_titular, carnet_tit, num_com_tit,(select * from dblink('$db_connection_name_dblink','SELECT * FROM concat_identity_card_complement('''||carnet_tit||''','''||num_com_tit||''')'::text) as  uu(concat_carnet_num_com character varying(250)))  as concat_carnet_num_com_tit, fec_fail_tit, lugar_nacimiento_tit,current_timestamp  as created_at FROM  tmp_copy_data_senasir_aux;";
            $insert = DB::connection('db_aux')->select($insert);
            $drop = "drop table if exists tmp_copy_data_senasir_aux";
            $drop = DB::select($drop);
            Util::close_conection_database_default();
            DB::commit();
            $consult = "select  count(*) from tmp_copy_data_senasirs";
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
    *      tags={"AFFILIATE-IMPORT-SENASIR"},
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
    *      tags={"AFFILIATE-IMPORT-SENASIR"},
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
