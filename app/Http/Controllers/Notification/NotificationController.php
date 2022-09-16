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
    /**
     * @OA\Get(
     *     path="/api/notification/get_semesters",
     *     tags={"NOTIFICACIONES"},
     *     summary="LISTADO DE SEMESTRES",
     *     operationId="getSemestres",
     *     description="Obtiene el listado de semestres para complemento económico",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *         type="object"
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     *
     * Get list of cities
     *
     * @param Request $request
     * @return void
     */
    public function get_semesters(){
        $semesters = EcoComProcedure::select(['id', 'year', 'semester'])
        ->orderBy('year')
        ->get();
        return response()->json([
            'semesters' => $semesters,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/notification/get_observations/{module_id}",
     *     tags={"NOTIFICACIONES"},
     *     summary="LISTADO DE OBSERVACIONES",
     *     operationId="getObservaciones",
     *     description="Obtiene el listado de las observaciones de tipo 'AT' para complemento económico",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *         type="object"
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     *
     * Get list of cities
     *
     * @param Request $request
     * @return void
     */
    public function get_observations($module_id){
        $observation_types = Module::find($module_id)->observation_types;
        return response()->json([
            'observations' => $observation_types
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/notification/get_modalities_payment",
     *     tags={"NOTIFICACIONES"},
     *     summary="LISTADO DE MODALIDADES DE PAGO",
     *     operationId="getModalidadesDePago",
     *     description="Obtiene el listado de las modalidades de pago para complemento económico",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *         type="object"
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     *
     * Get list of cities
     *
     * @param Request $request
     * @return void
     */
    public function get_modalities_payment() {
        $modalities_payment = EcoComState::select('id', 'name')->whereIn('id', [24, 25, 29])->get();
        return response()->json([
            'modalities_payment' => $modalities_payment
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/notification/get_beneficiary_type",
     *     tags={"NOTIFICACIONES"},
     *     summary="LISTADO DE TIPOS DE BENEFICIARIOS",
     *     operationId="getTiposBeneficiarios",
     *     description="Obtiene el listado de los tipos de beneficiarios para complemento económico",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *         type="object"
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     *
     * Get list of cities
     *
     * @param Request $request
     * @return void
     */
    public function get_beneficiary_type(){
        $types = EcoComModality::join('procedure_modalities', 'eco_com_modalities.procedure_modality_id', '=', 'procedure_modalities.id')
                                ->select('procedure_modalities.id', 'procedure_modalities.name')
                                ->distinct()
                                ->get();
        return response()->json([
            'beneficiary_type' => $types
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/notification/get_hierarchical_level",
     *     tags={"NOTIFICACIONES"},
     *     summary="LISTADO DE JERARQUIAS",
     *     operationId="getJerarquias",
     *     description="Obtiene el listado de las jerarquias para complemento económico",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *         type="object"
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     *
     * Get list of cities
     *
     * @param Request $request
     * @return void
     */
    public function get_hierarchical_level(){
        $hierarchies = Hierarchy::select('id', 'name')->where('id', '>', 1)
        ->orderBy('id')
        ->get();
        return response()->json([
            'hierarchies' => $hierarchies
        ]);
    }

    // Microservicio para consumir la ruta del backend node
    public function delegate_shipping($data, $tokens, $ids){ 
        $res = [];
        try{
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->acceptJson()
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

                $i = 0;
                foreach($responses as $check) {
                    $var = $check['success'] ? true : false;
                    $delivered = array(
                        $ids[$i] => $var
                    );
                    $i++;
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
        foreach($ids as $id) {
            logger($delivered[$id]);
            $obj = (object)$message;
            $notification_send = new NotificationSend();
            $notification_send->create([
                'user_id' => $user_id,
                'carrier_id' => 1,
                'number_id' => null, 
                'sendable_type' => $alias,
                'sendable_id' => $id,
                'send_date' => Carbon::now(),
                'delivered' => $delivered[$id],
                'message' => json_encode(['data' => $obj]),
                'subject' => $subject
            ]);
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
            foreach($chunk as $crumb){
                array_push($params['tokens'], $crumb->firebase_token);
                array_push($params['ids'],    $crumb->affiliate_id);
            }
            $res = $this->delegate_shipping($params['data'], $params['tokens'], $params['ids']); 
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
            'error'   => false,
            'message' => $res['message'],
            'data'    => [
                'delivered'     => $res['delivered'],
                'success_count' => $res['successCount'],
                'failure_count' => $res['failureCount']
            ]
        ]) : response()->json([
            'error'   => true,
            'message' => $res['message'],
            'data'    => []
        ], 404);
    }

    /**
     * @OA\Post(
     *     path="/api/notification/mass_notify",
     *     tags={"NOTIFICACIONES"},
     *     summary="ENVÍO DE NOTIFICACIONES",
     *     operationId="Envío masivo de notificaciones",
     *     description="Ruta para el envío masivo de notificaciones",
     *     @OA\RequestBody(
     *          description= "Envío de notificaciones",
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="action", type="string",description="Recepción de requerimientos, complemento económico u observaciones (Receipt_of_requirements, economic_complement_payment, observatinos", example="economic_complement_payment"),
     *              @OA\Property(property="payment_method", type="integer",description="Método de pago para el complemento económico (Abono en cuenta SIGEP, Ventanilla Banco Unión y a domicilio)",example="24"),
     *              @OA\Property(property="modality", type="integer",description="Modalidad (vejez, viudedad u orfandad)", example="29"),
     *              @OA\Property(property="type_observation", type="integer",description="Tipo de observación", example="2"),
     *              @OA\Property(property="title", type="string",description="título de la notificación", example="Complemento Económico"),
     *              @OA\Property(property="message", type="string",description="mensaje de la notificación",example="Esto es un mensaje para notificar"),
     *              @OA\Property(property="attached", type="integer",description="adjunto de la notificación", example="Adjunto de la notificación"),
     *              @OA\Property(property="loaded_image", type="boolean",description="parámetro para saber si se cargo la imagen",example="true"),
     *              @OA\Property(property="user_id", type="integer",description="id del usuario",example="1"),
     *              @OA\Property(property="year", type="string",description="Fecha perteneciente del complemento económico",example="2022-01-01"),
     *              @OA\Property(property="semester", type="string",description="Semestre del complemento económico observado",example="Segundo"),
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *         type="object"
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     *
     *  mass_notification 
     *
     * @param Request $request
     * @return void
     */
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
                        if($request->has('modality')){
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
