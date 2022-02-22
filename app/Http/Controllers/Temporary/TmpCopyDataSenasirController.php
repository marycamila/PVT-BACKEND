<?php

namespace App\Http\Controllers\Temporary;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class TmpCopyDataSenasirController extends Controller
{
    public function upload_copy_affiliate_spouse_senasir(request $request){
        $request->validate([
        'file' => 'required'
        ]);
        $extension = strtolower($request->file->getClientOriginalExtension());
        $username = env('FTP_USERNAME');
        $password = env('FTP_PASSWORD');
        $file_name = 'afiliados_senasir'.'.'.$extension;
        if($extension == "csv"){
            $base_path = 'afiliados';
            $file_path = Storage::disk('ftp')->putFileAs($base_path,$request->file,$file_name);
            $base_path ='ftp://'.env('FTP_HOST').env('FTP_ROOT').$file_path;
            $temporary_payroll = "create temporary table tmp_copy_data_senasir_aux(id_person_senasir integer,matricula_titular varchar, mat_dh varchar,
            carnet varchar, num_com varchar, paterno varchar, materno varchar, p_nombre varchar, s_nombre varchar, ap_casada varchar, fecha_nacimiento date, lugar_nacimiento varchar, clase_renta varchar,
            pat_titular varchar, mat_titular varchar, p_nom_titular varchar, s_nombre_titular varchar, ap_casada_titular varchar, carnet_tit varchar, num_com_tit varchar, fec_fail_tit date, lugar_nacimiento_tit varchar);";
            $temporary_payroll = DB::select($temporary_payroll);

            $copy = "copy tmp_copy_data_senasir_aux(id_person_senasir ,matricula_titular , mat_dh ,
            carnet , num_com , paterno , materno , p_nombre , s_nombre , ap_casada , fecha_nacimiento , lugar_nacimiento , clase_renta ,
            pat_titular , mat_titular , p_nom_titular , s_nombre_titular , ap_casada_titular , carnet_tit , num_com_tit , fec_fail_tit , lugar_nacimiento_tit )
            FROM PROGRAM 'wget -q -O - $@  --user=$username --password=$password $base_path'
                    WITH DELIMITER ':' CSV header;";
            $copy = DB::select($copy);

            $insert = "INSERT INTO tmp_copy_data_senasirs(id_person_senasir, matricula_titular, mat_dh,
            carnet, num_com , concat_carnet_num_com, paterno, materno, p_nombre, s_nombre, ap_casada, fecha_nacimiento, lugar_nacimiento, clase_renta,
            pat_titular, mat_titular, p_nom_titular, s_nombre_titular, ap_casada_titular, carnet_tit, num_com_tit, concat_carnet_num_com_tit, fec_fail_tit, lugar_nacimiento_tit)
                       SELECT id_person_senasir, matricula_titular, mat_dh,
            carnet, num_com ,concat_identity_card_complement(carnet,num_com)::varchar as concat_carnet_num_com, paterno, materno, p_nombre, s_nombre, ap_casada, fecha_nacimiento, lugar_nacimiento, clase_renta,
            pat_titular, mat_titular, p_nom_titular, s_nombre_titular, ap_casada_titular, carnet_tit, num_com_tit, concat_identity_card_complement(carnet_tit,num_com_tit)::varchar as concat_carnet_num_com_tit, fec_fail_tit, lugar_nacimiento_tit FROM  tmp_copy_data_senasir_aux; ";
            $insert = DB::select($insert);
            DB::commit();
            $drop = "drop table if exists tmp_copy_data_senasir_aux";
            $drop = DB::select($drop);
        }
        return  $base_path;
    }
}
