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
            $temporary_person = "create temporary table tmp_copy_person_senasir_aux(id_person_senasir integer,matricula_tit varchar, carnet_tit varchar,
            num_com_tit varchar, p_nombre_tit varchar, s_nombre_tit varchar, paterno_tit varchar, materno_tit varchar, ap_casada_tit varchar,
            fecha_nacimiento_tit date, genero_tit varchar, fec_fail_tit date, matricula_dh varchar, carnet_dh varchar, num_com_dh varchar,
            p_nombre_dh varchar, s_nombre_dh varchar, paterno_dh varchar, materno_dh varchar, ap_casada_dh varchar, fecha_nacimiento_dh date, genero_dh varchar, fec_fail_dh date, clase_renta_dh varchar);";
            $temporary_person = DB::connection('db_aux')->select($temporary_person);

            $copy = "copy tmp_copy_person_senasir_aux(id_person_senasir, matricula_tit, carnet_tit,
            num_com_tit, p_nombre_tit, s_nombre_tit, paterno_tit, materno_tit, ap_casada_tit, fecha_nacimiento_tit, genero_tit, fec_fail_tit, matricula_dh, carnet_dh, num_com_dh,
            p_nombre_dh, s_nombre_dh, paterno_dh, materno_dh, ap_casada_dh, fecha_nacimiento_dh, genero_dh, fec_fail_dh, clase_renta_dh)
            FROM PROGRAM 'wget -q -O - $@  --user=$username --password=$password $base_path'
                    WITH DELIMITER ':' CSV header;";
            $copy = DB::connection('db_aux')->select($copy);
            $insert = "INSERT INTO copy_person_senasirs(id_person_senasir, matricula_tit, carnet_tit, num_com_tit, concat_carnet_num_com_tit, p_nombre_tit, s_nombre_tit, paterno_tit, materno_tit, ap_casada_tit, fecha_nacimiento_tit, genero_tit, fec_fail_tit, matricula_dh, carnet_dh, num_com_dh, concat_carnet_num_com_dh, p_nombre_dh, s_nombre_dh, paterno_dh, materno_dh, ap_casada_dh, fecha_nacimiento_dh, genero_dh, fec_fail_dh, clase_renta_dh, created_at)
                            SELECT id_person_senasir, matricula_tit, carnet_tit, num_com_tit,CASE WHEN num_com_tit is null then carnet_tit else (select * from dblink('$db_connection_name_dblink','SELECT * FROM concat_identity_card_complement('''||carnet_tit::varchar||''','''||num_com_tit::varchar||''')'::text) as  uu(concat_carnet_num_com_tit varchar)) end as concat_carnet_num_com_tit, p_nombre_tit, s_nombre_tit, paterno_tit,materno_tit, ap_casada_tit, fecha_nacimiento_tit,CASE genero_tit
                            when '2' then 'F'
                            else 'M'
                            end,fec_fail_tit,
                            matricula_dh, carnet_dh, num_com_dh,CASE WHEN num_com_dh is null then carnet_dh else (select * from dblink('$db_connection_name_dblink','SELECT * FROM concat_identity_card_complement('''||carnet_dh||''','''||num_com_dh||''')'::text) as  uu(concat_carnet_num_com_dh varchar)) end as concat_carnet_num_com_dh , p_nombre_dh, s_nombre_dh, paterno_dh, materno_dh, ap_casada_dh, fecha_nacimiento_dh,CASE genero_dh
                            when '2' then 'F'
                            else 'M'
                            end , fec_fail_dh, clase_renta_dh, current_timestamp  as created_at FROM  tmp_copy_person_senasir_aux where id_person_senasir is not null and (clase_renta_dh = 'VIUDEDAD' or clase_renta_dh is null);";
            $insert = DB::connection('db_aux')->select($insert);
            $drop = "drop table if exists tmp_copy_person_senasir_aux";
            $drop = DB::select($drop);
            //Util::close_conection_database_default();
            DB::commit();

            $consult = "select  count(*) from copy_person_senasirs";
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
    *      path="/api/temporary/update_affiliate_id_person_senasir",
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

    public function update_affiliate_id_person_senasir(Request $request){
    DB::beginTransaction();
    try{
        $connection_db_aux = Util::connection_db_aux();
        $update_affiliate_id_person_senasir =  DB::select("select tmp_update_affiliate_id_person_senasir('$connection_db_aux')");

        $update_affiliate_id_person_senasir = explode(',',$update_affiliate_id_person_senasir[0]->tmp_update_affiliate_id_person_senasir);

        DB::commit();
        return response()->json([
            'message' => 'Realizado con exito',
            'payload' => [
                'successfully' => true,
                'count_update_by_registration' => (int)$update_affiliate_id_person_senasir[0],
                'count_update_by_identity' => (int)$update_affiliate_id_person_senasir[1],
                'count_created_affiliate' => (int)$update_affiliate_id_person_senasir[2]
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
