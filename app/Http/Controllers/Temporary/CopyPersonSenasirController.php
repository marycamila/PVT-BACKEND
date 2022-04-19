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
    *      summary="PASO 1 COPIADO DE DATOS IMPORTACION DE IDS SENASIR",
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
    *      path="/api/temporary/update_affiliate_id_senasir",
    *      tags={"IMPORTACION-IDS-PERSONAS-SENASIR"},
    *      summary="PASO 2 ACTUALIZACIÓN DE IDS VERIFICACION BAJO 5 CRITERIOS BASE",
    *      operationId="update_affiliate_id_senasir",
    *      description="Actualización  de ids senasir a la tabla affiliates bajo 5 criterios automaticos",
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

  public function update_affiliate_id_senasir(Request $request){
 
        $connection_db_aux = Util::connection_db_aux();
        $update_affiliate_id_person_senasir =  DB::select("select tmp_update_affiliate_id_senasir('$connection_db_aux')");
        $update_affiliate_id_person_senasir = explode(',',$update_affiliate_id_person_senasir[0]->tmp_update_affiliate_id_senasir);

        $count_copy_total_senasir = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps")[0]->count;

        $count_unrealized_senasir =  DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion is null  and cps.state like 'unrealized'")[0]->count;
        $count_update_by_criterion_one = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion like '1-CI-MAT-PN-AP-AM-FN' and cps.state like 'accomplished'")[0]->count;
        $count_update_by_criterion_two = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion like '2-CI-PN-AP-AM-FN' and cps.state like 'accomplished'")[0]->count;
        $count_update_by_criterion_three = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion like '3-CI-MAT-PN-AP-AM' and cps.state like 'accomplished'")[0]->count;
        $count_update_by_criterion_four = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion like '4-MAT-PN-AP-AM-FN' and cps.state like 'accomplished'")[0]->count;
        $count_update_by_criterion_five =  DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion like '5-CI-PN-AP-AM' and cps.state like 'accomplished'")[0]->count;
        $count_total_affiliates_update = DB::select("select count(*) from affiliates a where a.id_person_senasir is not null")[0]->count;
        $count_total_accomplished_senasir = $count_update_by_criterion_one +  $count_update_by_criterion_two + $count_update_by_criterion_three + $count_update_by_criterion_four + $count_update_by_criterion_five;
        return response()->json([
            'message' => 'Realizado con exito',
            'payload' => [
                'successfully' => true,
                'count_update_by_criterion_one' => (int)$update_affiliate_id_person_senasir[0],
                'count_update_by_criterion_two' => (int)$update_affiliate_id_person_senasir[1],
                'count_update_by_criterion_three' => (int)$update_affiliate_id_person_senasir[2],
                'count_update_by_criterion_four' => (int)$update_affiliate_id_person_senasir[3],
                'count_update_by_criterion_five' => (int)$update_affiliate_id_person_senasir[4]
            ],
        
            'count_data_copy_person_senasir' => [
                'count_copy_total_senasir' => $count_copy_total_senasir,
                'count_unrealized_senasir' => $count_unrealized_senasir,
                'count_update_by_1-CI-MAT-PN-AP-AM-FN' => $count_update_by_criterion_one,
                'count_update_by_2-CI-PN-AP-AM-FN' => $count_update_by_criterion_two,
                'count_update_by_3-CI-MAT-PN-AP-AM' => $count_update_by_criterion_three,
                'count_update_by_4-MAT-PN-AP-AM-FN' => $count_update_by_criterion_four,
                'count_update_by_5-CI-PN-AP-AM' => $count_update_by_criterion_five,
                '_count_total_accomplished_senasir' => $count_total_accomplished_senasir
            ],
        ]);

    }
        /**
    * @OA\Post(
    *      path="/api/temporary/update_affiliate_id_senasir_registration_and_identity_card",
    *      tags={"IMPORTACION-IDS-PERSONAS-SENASIR"},
    *      summary="PASO 3 ACTUALIZACIÓN DE CRITERIOS 6 y 7 REVISION MANUAL",
    *      operationId="update_affiliate_id_senasir_registration_and_identity_card",
    *      description="Actualización  de ids senasir a la tabla affiliates bajo 2 criterios de revisión manual",
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

  public function update_affiliate_id_senasir_registration_and_identity_card(Request $request){

    $connection_db_aux = Util::connection_db_aux();
    $count_update_by_registration = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.state like 'accomplished'")[0]->count;
        if($count_update_by_registration>0){
            $update_affiliate_id_person_senasir =  DB::select("select tmp_update_affiliate_id_senasir_registration_and_identity_card('$connection_db_aux')");
            $update_affiliate_id_person_senasir = explode(',',$update_affiliate_id_person_senasir[0]->tmp_update_affiliate_id_senasir_registration_and_identity_card);
            $count_copy_total_senasir = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps")[0]->count;

            $count_unrealized_senasir =  DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion is null  and cps.state like 'unrealized'")[0]->count;
            $count_update_by_criterion_six = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion like '6-MAT-REV-MANUAL' and cps.state like 'accomplished'")[0]->count;
            $count_update_by_criterion_seven = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion like '7-CI-REV-MANUAL' and cps.state like 'accomplished'")[0]->count;
            $count_total_affiliates_update = DB::select("select count(*) from affiliates a where a.id_person_senasir is not null")[0]->count;

            $count_total_accomplished_senasir = $count_update_by_criterion_six +  $count_update_by_criterion_seven;
            return response()->json([
                'message' => 'Realizado con exito',
                'payload' => [
                    'successfully' => true,
                    'count_update_by_criterion_six' => (int)$update_affiliate_id_person_senasir[0],
                    'count_update_by_criterion_seven' => (int)$update_affiliate_id_person_senasir[1],
                ],

                'count_data_copy_person_senasir' => [
                    'count_copy_total_senasir' => $count_copy_total_senasir,
                    'count_unrealized_senasir' => $count_unrealized_senasir,
                    'count_update_by_6-MAT-REV-MANUAL' => $count_update_by_criterion_six,
                    'count_update_by_7-CI-REV-MANUAL' => $count_update_by_criterion_seven,
                    '_count_total_accomplished_senasir' => $count_total_accomplished_senasir
                ],
            ]);
        }else{
                return response()->json([
                    'message' => 'No a realizado la actualizacion de los primeros criterios',
                    'payload' => [
                        'successfully' => false,
                    ],
                ]);
        }

    }
          /**
    * @OA\Post(
    *      path="/api/temporary/create_affiliate_spouse_senasir",
    *      tags={"IMPORTACION-IDS-PERSONAS-SENASIR"},
    *      summary="PASO 4 CREACION DE AFILIADOS Y ESPOSAS SENASIR",
    *      operationId="create_affiliate_spouse_senasir",
    *      description="Creación de afiliados y esposas senasir no encontrados en la tabla affiliates",
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

   public function create_affiliate_spouse_senasir(Request $request){
    $connection_db_aux = Util::connection_db_aux();
    $create_affiliate =  DB::select("select tmp_create_affiliate_senasir('$connection_db_aux')")[0]->tmp_create_affiliate_senasir;
    $create_spouse =  DB::select("select tmp_create_spouse_senasir('$connection_db_aux')")[0]->tmp_create_spouse_senasir;

    //total conteos
    $count_copy_total_senasir = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps")[0]->count;

        $count_unrealized_senasir =  DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion is null  and cps.state like 'unrealized'")[0]->count;
        $count_update_by_criterion_one = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion like '1-CI-MAT-PN-AP-AM-FN' and cps.state like 'accomplished'")[0]->count;
        $count_update_by_criterion_two = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion like '2-CI-PN-AP-AM-FN' and cps.state like 'accomplished'")[0]->count;
        $count_update_by_criterion_three = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion like '3-CI-MAT-PN-AP-AM' and cps.state like 'accomplished'")[0]->count;
        $count_update_by_criterion_four = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion like '4-MAT-PN-AP-AM-FN' and cps.state like 'accomplished'")[0]->count;
        $count_update_by_criterion_five =  DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion like '5-CI-PN-AP-AM' and cps.state like 'accomplished'")[0]->count;
        $count_update_by_criterion_six = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion like '6-MAT-REV-MANUAL' and cps.state like 'accomplished'")[0]->count;
        $count_update_by_criterion_seven = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion like '7-CI-REV-MANUAL' and cps.state like 'accomplished'")[0]->count;
        $count_create_affiliate = DB::connection('db_aux')->select("select count(*) from copy_person_senasirs cps where cps.observacion like 'AFILIADO_CREADO' and cps.state like 'accomplished'")[0]->count;
        $count_total_affiliates_update = DB::select("select count(*) from affiliates a where a.id_person_senasir is not null")[0]->count;
        $count_total_accomplished_senasir = $count_update_by_criterion_one +  $count_update_by_criterion_two + $count_update_by_criterion_three + $count_update_by_criterion_four + $count_update_by_criterion_five
                                            + $count_update_by_criterion_six + $count_update_by_criterion_seven + $count_create_affiliate;
    return response()->json([
        'message' => 'Realizado con exito',
        'payload' => [
            'successfully' => true,
            'count_create_affiliate' => (int)$create_affiliate,
            'count_create_spouse' => (int)$create_spouse,
        ],
        'count_data_copy_person_senasir' => [
            'count_copy_total_senasir' => $count_copy_total_senasir,
            'count_unrealized_senasir' => $count_unrealized_senasir,
            'count_update_by_1-CI-MAT-PN-AP-AM-FN' => $count_update_by_criterion_one,
            'count_update_by_2-CI-PN-AP-AM-FN' => $count_update_by_criterion_two,
            'count_update_by_3-CI-MAT-PN-AP-AM' => $count_update_by_criterion_three,
            'count_update_by_4-MAT-PN-AP-AM-FN' => $count_update_by_criterion_four,
            'count_update_by_5-CI-PN-AP-AM' => $count_update_by_criterion_five,
            'count_update_by_6-MAT-REV-MANUAL' => $count_update_by_criterion_six,
            'count_update_by_7-CI-REV-MANUAL' => $count_update_by_criterion_seven,
            'count_create_affiliate' => $count_create_affiliate,
            '_*count_total_accomplished_senasir' => $count_total_accomplished_senasir,
            '_*count_total_affiliates_update'=> $count_total_affiliates_update,
        ],
    ]);
    }
         /**
    * @OA\Post(
    *      path="/api/temporary/update_affiliate_data",
    *      tags={"IMPORTACION-IDS-PERSONAS-SENASIR"},
    *      summary="PASO 5 ACTUALIZACIÓN DE DATOS DEL AFILIADO",
    *      operationId="update_affiliate_data",
    *      description="Actualización de datos del afiliado, matricula, fecha de nacimiento, fecha de fallecimiento, ente gestor, y el estado del afiliado",
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

  public function update_affiliate_data(Request $request){

    $connection_db_aux = Util::connection_db_aux();

    $update_affiliate_data =  DB::select("select tmp_update_affiliate_data('$connection_db_aux')")[0]->tmp_update_affiliate_data;

        return response()->json([
            'message' => 'Realizado con exito',
             'payload' => [
                'successfully' => true,
                'message_data' => $update_affiliate_data,
            ],
        ]);
    }
}
