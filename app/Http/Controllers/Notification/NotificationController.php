<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\NotificationRequest;
use App\Models\EconomicComplement\EcoComProcedure;
use App\Models\EconomicComplement\EcoComState;
use App\Models\EconomicComplement\EconomicComplement;
use App\Models\EconomicComplement\EcoComModality;
use App\Models\Notification\NotificationSend;
use App\Models\Affiliate\Hierarchy;
use App\Models\ObservationType;
use App\Models\Admin\Module;
use App\Models\Affiliate\AffiliateToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ExceptionMicroservice;

class NotificationController extends Controller
{
    // Para obtener todos los semestres
    public function get_semesters(){
        $semesters = EcoComProcedure::select(['id', 'year', 'semester'])
        ->orderBy('year')
        ->get();
        return response()->json([
            'semesters' => $semesters,
        ]);
    }

    // Para seleccionar las observaciones module_id: 2
    public function get_observations($module_id){
        $observation_types = Module::find($module_id)->observation_types;
        return response()->json([
            'observations' => $observation_types
        ]);
    }

    // Para obtener las modalidades de pago   (pago en cuenta, pago en ventanilla, pago a domicilio)
    public function get_modalities_payment() {
        $modalities_payment = EcoComState::select('id', 'name')->whereIn('id', [24, 25, 29])->get();
        return response()->json([
            'modalities_payment' => $modalities_payment
        ]);
    }

    // Para obtener los tipos de beneficiarios  (vejez, viuda, orfandad)
    public function get_beneficiary_type(){
        $types = EcoComModality::join('procedure_modalities', 'eco_com_modalities.procedure_modality_id', '=', 'procedure_modalities.id')
                                ->select('procedure_modalities.id', 'procedure_modalities.name')
                                ->distinct()
                                ->get();
        return response()->json([
            'beneficiary_type' => $types
        ]);
    }

    // Para obtener el nivel de jerarquia (4 posibles casos)
    public function get_hierarchical_level(){
        $hierarchies = Hierarchy::select('id', 'name')->where('id', '>', 1)
        ->orderBy('id')
        ->get();
        return response()->json([
            'hierarchies' => $hierarchies
        ]);
    }

    // Microservicio para consumir la ruta del backend node
    public function delegate_shipping($data, $tokens){ 
        // $url_backend_node = env('BACKEND_NODE');
        $res = [];
        try{
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->acceptJson()
                // ->post('http://192.168.2.129:8082/api/notification/groupusers', [
                ->post(env('URL_BACKEND_NODE'),[
                    'tokens' => $tokens, 
                    'title'  => 'COMPLEMENTO ECONÓMICO',
                    'body'   => 'COMUNICADO',
                    'image'  => env('NOTIFICATION_IMAGE', ''),
                    'data'   => $data
                ]);
            if($response->successful()) {
                $delivered = [];                    // Para el estado en la base de datos
                $message   = $response['message'];
                $responses = $message['responses'];

                foreach($responses as $check) {
                    $var = $check['success'] ? true : false;
                    array_push($delivered, $var);
                }
                $res['status']       = true;
                $res['delivered']    = $delivered;
                $res['successCount'] = $message['successCount'];
                $res['failureCount'] = $message['failureCount'];
                $res['message'] = 'Notificación masiva exitosa';
            } else {
                $res['status']  = false;
                $res['message'] = 'Notificación masiva fallida';
            }
        }
        catch(\Exception $e) {
            $res['status'] = false;
            $res['message'] = $e->getMessage();
        }
        return $res;
    }

    // Guardar en el registro tabla notification_send
    public function to_register($user_id, $delivered, $message, $subject, $ids) {
        $eco_com = new EconomicComplement();
        $alias = $eco_com->getMorphClass();
        $j = 0;
        foreach($ids as $id) {
            $obj = (object)$message;
            $notification_send = new NotificationSend();
            $notification_send->create([
                'user_id' => $user_id,
                'carrier_id' => 1,
                'number_id' => null, 
                'sendable_type' => $alias,
                'sendable_id' => $id,
                'send_date' => Carbon::now(),
                'delivered' => $delivered[$j],
                'message' => json_encode(['data' => $obj]),
                'subject' => $subject
            ]);
            $j++;
        }
    }

    // Función que crea las tablas temporales necesarias para el manejo de las consultas
    public function create_temporary_tables_payments() {
        DB::transaction(function() {
            DB::statement('drop table if exists tmp_affiliates');
            DB::statement("create temp table tmp_affiliates (
                affiliate_id integer,
                firebase_token varchar,
                economic_complement_id integer,
                last_year date,
                last_semester varchar,
                payment_id integer,
                payment_name varchar,
                modality_id integer,
                modality_name varchar)");
            DB::statement('drop table if exists temporal');
            DB::statement('create temp table temporal as (
                select ts.affiliate_id as affiliate_id, ts.firebase_token as firebase_token, ec.id as economic_complement_id, ecp.year as last_year, ecp.semester as last_semester, ecs.id as payment_id, ecs.name as payment_name, pm.id as modality_id, pm.name as modality_name
                from affiliate_tokens ts
                inner join economic_complements ec
                on ts.affiliate_id = ec.affiliate_id
                inner join eco_com_procedures ecp
                on ec.eco_com_procedure_id = ecp.id
                inner join eco_com_states ecs
                on ec.eco_com_state_id = ecs.id
                inner join eco_com_modalities ecm
                on ec.eco_com_modality_id = ecm.id
                inner join procedure_modalities pm
                on ecm.procedure_modality_id = pm.id
                where ts.api_token is not null
                and ts.firebase_token is not null
                and ecs.id in (24, 25, 29)
                order by ts.affiliate_id, ecp.year desc)');
            DB::statement('create or replace procedure fill_temp()
                        as
                        $$
                        declare
                                campo RECORD;
                            begin 
                                for campo in (select affiliate_id
                                            from affiliate_tokens
                                            where api_token is not null
                                            order by affiliate_id)
                                loop
                                    insert into tmp_affiliates (affiliate_id, firebase_token, economic_complement_id, last_year, last_semester, payment_id, payment_name, modality_id, modality_name)
                                    select ts.affiliate_id, ts.firebase_token, ts.economic_complement_id, ts.last_year, ts.last_semester, ts.payment_id, ts.payment_name, ts.modality_id, ts.modality_name
                                    from temporal ts
                                    where ts.affiliate_id = campo.affiliate_id
                                    offset 0 rows
                                    fetch first 1 row only;
                                end loop;
                            end;
                            $$ LANGUAGE plpgsql;');
            DB::statement('call fill_temp();');
        });
    }

    // Función que crea la tabla temporal necesaria para las observaciones
    public function create_temporary_table_observation($year, $semester) {
        DB::transaction(function() use ($year, $semester) {
            DB::statement('drop table if exists tmp_observations');
            DB::statement("create temp table tmp_observations as (
                select distinct at.affiliate_id, at.firebase_token, ecp.semester, ecp.year
                from affiliate_tokens at
                inner join affiliate_devices ad
                on at.id = ad.affiliate_token_id
                inner join eco_com_procedures ecp
                on ad.eco_com_procedure_id = ecp.id
                where ecp.year = '$year'
                and ecp.semester = '$semester'
                and at.api_token is not null
                --and at.firebase_token is not null
            )");
        });
    }

    // Proceso de consulta 
    public function consultation_process($count, $sql_base, $params){
        $result = false;
        $offset = 0;
        $sql_base .= " \n"; 
        $query = $sql_base . "limit 500 offset $offset";
        $i = 0;
        $res = [];
        do {
            $i++;
            $chunk = DB::select($query);
            $offset += 500;

            logger('esto es chunk '. count($chunk));
            foreach($chunk as $crumb){
                array_push($params['tokens'], $crumb->firebase_token);
                array_push($params['ids'],    $crumb->affiliate_id);
            }
            $res = $this->delegate_shipping($params['data'], $params['tokens']); 
            if($res['status']) {
                $status = $res['delivered'];
                $this->to_register($params['user_id'], $status, $params['data'], $params['subject'], $params['ids']);
                $result = true;
            }
            else { $result = false; break; }
            logger("-----------------    ENVÍO LOTE NRO $i  --------------------------");
            sleep(1);
        } while($i < $count);
        return $result ? response()->json([
            'error'   => $res['status'],
            'message' => $res['message'],
            'data'    => []
        ]) : response()->json([
            'error'   => true,
            'message' => $res['message'],
            'data'    => []
        ], 404);
    }

    // Proceso general
    public function mass_notification(NotificationRequest $request) {

        ini_set('max_execution_time', 60);
        try {
            $action = $request->action;
            $title_notification = $request->title;
            $message = $request->message;
            $params  = [];
            $tokens  = [];
            $ids     = [];
            $data    = [
                'title' => $title_notification,
                'image' => "https://www.opinion.com.bo/asset/thumbnail,992,558,center,center//media/opinion/images/2022/05/24/2022052415283420630.jpg", 
                'PublicationDate' => "alguna cosa",
                'text'  => $message 
            ];

            $params['data']    = $data;
            $params['tokens']  = $tokens;
            $params['ids']     = $ids;
            $params['subject'] = $request->attached;
            $params['user_id'] = $request->user_id;


            if($action == 'receipt_of_requirements'){

                $res = [];
                $result = AffiliateToken::whereNotNull('api_token')
                    ->whereNotNull('firebase_token')
                    ->orderBy('affiliate_id')
                    ->chunk(500, function($registers, $count) use ($params) { 
                        foreach($registers as $register) {
                            array_push($params['tokens'], $register->firebase_token);
                            array_push($params['ids'], $register->id); 
                        }
                        $res = $this->delegate_shipping($params['data'], $params['tokens']);  
                        if($res['status']) {
                            $status = $res['delivered'];
                            $this->to_register($params['user_id'], $status, $params['data'], $params['subject'], $params['ids']);
                        }
                        else return false;
                        
                        logger("-----------------    ENVÍO LOTE ALL NRO $count  --------------------------");
                        sleep(1);
                });
                return $result ? response()->json([
                    'error'   => false,
                    'message' => 'Notificación masiva exitosa',
                    'data'    => []
                ]) : response()->json([
                    'error'   => true,
                    'message' => 'Notificación masiva fallida',
                    'data'    => []
                ], 404);

            } else {
                if($action == 'economic_complement_payment') {
                    $payment_method = $request->payment_method;
                    $this->create_temporary_tables_payments(); 
                    if($payment_method == 0) {
                        logger("A todos los habilitados para pago de complemento económico");
                        $count = DB::select("select ceil(cast(count(distinct affiliate_id) as decimal) / 500) as interval
                                    from tmp_affiliates");

                        $query = "select affiliate_id, firebase_token
                                from tmp_affiliates
                                order by affiliate_id";
                    } else {
                        logger("primer else");
                        if($request->has('modality')){
                            logger("segundo if");
                            $modality = $request->modality;
                            if($request->has('hierarchies')){
                                $hierarchies = $request->hierarchies;
                                logger("A $payment_method con su modalidad de $modality y con la jerarquia $hierarchies");
                                $count = DB::select("select ceil(cast(count(distinct affiliate_id) as decimal) / 500) as interval
                                                    from tmp_affiliates ta
                                                    left join affiliates a
                                                    on ta.affiliate_id = a.id
                                                    inner join degrees d
                                                    on a.degree_id = d.id
                                                    inner join hierarchies h
                                                    on d.hierarchy_id = h.id
                                                    where payment_id = $payment_method
                                                    and ta.modality_id = $modality
                                                    and h.id = $hierarchies");
                                
                                
                                $query = "select ta.affiliate_id, ta.firebase_token
                                        from tmp_affiliates ta
                                        left join affiliates a
                                        on ta.affiliate_id = a.id
                                        inner join degrees d
                                        on a.degree_id = d.id
                                        inner join hierarchies h
                                        on d.hierarchy_id = h.id
                                        where payment_id = $payment_method
                                        and ta.modality_id = $modality
                                        and h.id = $hierarchies";
                            } else {
                                logger("A $payment_method con su modalidad de $modality");
                                $count = DB::select("select ceil(cast(count(distinct affiliate_id) as decimal) / 500) as interval
                                            from tmp_affiliates
                                            where payment_id = $payment_method
                                            and modality_id = $modality");

                                $query = "select distinct affiliate_id, firebase_token
                                        from tmp_affiliates
                                        where payment_id = $payment_method
                                        and modality_id = $modality";
                            }
                        } else {
                            logger("Solo a su método de pago $payment_method");
                            $count = DB::select("select ceil(cast(count(distinct affiliate_id) as decimal) / 500) as interval
                                        from tmp_affiliates
                                        where payment_id = $payment_method");
                            
                            $query = "select affiliate_id, firebase_token
                                    from tmp_affiliates
                                    where payment_id = $payment_method";
                        }
                    }
                } elseif($action == 'observations') {
                    $year = $request->year;
                    $semester = $request->semester;
                    $this->create_temporary_table_observation($year, $semester); 
                    $type = $request->type_observation;
                    $count = DB::select("select ceil(cast(count(distinct tos.affiliate_id) as decimal) / 500) as interval
                                from tmp_observations tos, economic_complements ec, observables o, observation_types ot
                                where tos.affiliate_id = ec.affiliate_id
                                and o.observable_type = 'economic_complements'
                                and o.observable_id = ec.id
                                and o.observation_type_id = $type
                                and o.enabled = true
                                and ot.type = 'AT'
                                and ot.description is not null
                                and ot.description <> ''");
                    
                    $query = "select distinct tos.affiliate_id, tos.firebase_token
                                from tmp_observations tos, economic_complements ec, observables o, observation_types ot
                                where tos.affiliate_id = ec.affiliate_id
                                and o.observable_type = 'economic_complements'
                                and o.observable_id = ec.id
                                and o.observation_type_id = $type
                                and o.enabled = true
                                and ot.type = 'AT'
                                and ot.description is not null
                                and ot.description <> ''";
                }
                return $this->consultation_process($count[0]->interval, $query, $params);
            }
        } catch(\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
