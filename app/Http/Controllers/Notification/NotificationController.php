<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EconomicComplement\EcoComProcedure;
use App\Models\ObservationType;
use App\Models\Admin\Module;
use App\Models\Affiliate\AffiliateToken;
use App\Models\EconomicComplement\EcoComBeneficiary;
use App\Models\EconomicComplement\EcoComState;
use App\Models\EconomicComplement\EconomicComplement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Helpers\Util;
use App\Models\Notification\NotificationSend;
use Exception;
use Carbon\Carbon;

class NotificationController extends Controller
{
    // Para seleccionar el semestre
    public function get_semesters(){
        $semesters = EcoComProcedure::select(['id', 'year', 'semester'])->get();
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

    // Para obtener las modalidades de pago
    public function get_modalities_payment() {
        $modalities_payment = EcoComState::select('id', 'name')->whereIn('id', [24, 25, 29])->get();
        return response()->json([
            'modalities_payment' => $modalities_payment
        ]);
    }

    // Como determinamos que se agrego un nuevo semestre para el cobro?
    public function current_semester(){
        // Obtenemos el semestre actual para la notificación
        $last_eco_com = EcoComProcedure::latest('year')->first();
        return array($last_eco_com->year, $last_eco_com->semester);
    }

    public function delegate_shipping($data, $tokens){
        // $url_backend_node = env('BACKEND_NODE');
        try{
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->acceptJson()
                ->post('http://192.168.2.129:8081/api/notification/groupusers', [
                    'tokens' => $tokens, 
                    'title'  => 'COMPLEMENTO ECONÓMICO',
                    'body'   => 'COMUNICADO',
                    'image'  => 'https://www.opinion.com.bo/asset/thumbnail,992,558,center,center//media/opinion/images/2022/05/24/2022052415283420630.jpg',
                    'data'   => $data
                ]);
            $res =  [];
            if($response->successful()) {
                $delivered = [];
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
                // logger($res);

            } else {
                $message             = $response['message'];
                $res['status']       = false;
                $res['delivered']    = [];
                $res['successCount'] = $message['successCount'];
                $res['failureCount'] = $message['failureCount'];
            }
            return $res;
        }
        catch(\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function saludar($nombre, $params) {
        logger('hola '.$nombre.$params);
    }

    public function show($array) {
        for($i = 0; $i < sizeof($array); $i++){
            logger($i.'  => '.$array[$i]);
        }
    }

    public function to_register($user_id, $delivered, $message, $subject, $ids) {
        $eco_com = new EconomicComplement();
        $alias = $eco_com->getMorphClass();
        // return response()->json([
        //     'alias' => $alias
        // ]);
        $j = 0;
        foreach($ids as $id) {
            $obj = (object)$message;
            // return json_encode(['data' => $obj]);
            $notification_send = new NotificationSend();
            $notification_send->create([
                // 'user_id' => $user_id,
                'user_id' => 1,
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

    public function mass_notification(Request $request) {
        $semester = $request->semester; // El semestre Segundo o Primero
        $year     = $request->year;     // Fecha del semestre
        $type     = $request->type;     // tipo de acción que realizó el usuario
        $user_id  = $request->user;     // El id del usuario iniciado sesión en el sistema

        if($type == 'economic_complement_payment' || $type == 'receipt_of_requirements'){
            $title_notification = $request->title;
            $attached           = $request->attached;
            $loaded_image       = $request->loaded_image ?? "";     // Solo cargará la imagen en el caso de o bien la recepción de requisitos o pago de complemento
            if($request->has('payment_method')) $payment_method = $request->payment_method;
            else $payment_method = $request->has('payment_method');

            // [$year, $semester] = $this->current_semester(); // Para obtener el semestre actual para notificar

            $res = EcoComProcedure::select('normal_start_date', 'normal_end_date')->latest('year')->first();
            [$start_date, $end_date] = array($res->normal_start_date, $res->normal_end_date);

            $message = $this->build_message($type, $payment_method, $year, $semester, $start_date, $end_date); // Armado del mensaje
            $message .= ' '.$attached;

            $tokens = [];
            $ids = [];
            $params = [];
            $data   = [
                'title' => $title_notification,
                'image' => "https://www.opinion.com.bo/asset/thumbnail,992,558,center,center//media/opinion/images/2022/05/24/2022052415283420630.jpg", // ellos deben cargar la imagen
                'PublicationDate' => "alguna cosa",
                'text'  => $message 
            ];

            // return response()->json([
            //     'year' => $year,
            //     'semester' => json_encode($data),
            //     // 'fecha actual' => Carbon::now()
            // ]);

            $params['user_id'] = $user_id;
            $params['ids'] = $ids;
            $params['tokens'] = $tokens;
            $params['data'] = $data;
            $params['message'] = $message;
            $params['subject'] = 'COMPLEMENTO ECONÓMICO';

            // Todos los affiliados o beneficiaros que crearon su trámite 2-2022
            $result = EconomicComplement::join('eco_com_procedures', 'economic_complements.eco_com_procedure_id', '=', 'eco_com_procedures.id')
                ->join('affiliate_tokens', 'economic_complements.affiliate_id', '=', 'affiliate_tokens.affiliate_id')
                ->join('eco_com_applicants', 'economic_complements.id', '=', 'eco_com_applicants.economic_complement_id')
                ->join('eco_com_states', 'economic_complements.eco_com_state_id', '=', 'eco_com_states.id')
                ->where('eco_com_procedures.year', $year)
                ->where('eco_com_procedures.semester', $semester)
                ->when($payment_method, function($query, $payment_method){
                    $query->where('economic_complements.eco_com_state_id',$payment_method);
                    // $query->whereIn('economic_complements.eco_com_state_id',[24, 25, 29]);
                    logger("=======entra aca========");
                })
                ->select('economic_complements.id', 'eco_com_applicants.last_name', 'eco_com_applicants.mothers_last_name', 'eco_com_applicants.first_name', 'affiliate_tokens.firebase_token')
                ->orderBy('affiliate_tokens.affiliate_id')
                // ->get();
                // ->count();
                // ->first();
                ->chunk(500, function($registers, $count) use ($params) { // cada 500 personas armo el array tokens 
                    foreach($registers as $register) {
                        array_push($params['tokens'], $register->firebase_token);
                        array_push($params['ids'], $register->id); // Para el polimorfismo (ids del complemento económico)
                    }
                    $res = $this->delegate_shipping($params['data'], $params['tokens']); // Obteniendo los delivereds
                    if($res['status']) {
                        $status = $res['delivered'];
                        logger($params['ids']);
                        $this->to_register($params['user_id'], $status, $params['message'], $params['subject'], $params['ids']);
                    }
                    else logger("no envío");

                    logger("-----------------    ENVÍO LOTE NRO $count  --------------------------");
                    sleep(3); // esperar durante 3 segundos
                });


            // return response()->json([
            //     'get' => $result
            // ]);
            
            if($result) {
                return response()->json([
                    'error' => false,
                    'message' => "Notificación masiva exitosa",
                    'data' => []
                ]);
            } else return response()->json([
                'error' => true,
                'message' => "Error al notificar masivamente",
                'data' => []
            ]);
        }
        // Caso de observaciones
    }
    // Necesito el formato del mensaje para poder armarlo
    public function build_message($type, $payment_method, $year, $semester, $start_date, $end_date ) {
        $end   = " perteneciente al $semester semestre del $year";
        if($type == 'economic_complement_payment') {
            $message = "";
            $start = "Estimado afiliado, ya puede realizar el cobro del su complemento económico, a través de ";
            switch($payment_method) {
                case 24: // deposit into account
                    $message = $start."su cuenta bancaria,".$end;
                break;
                case 25: // payment at bank window
                    $message = $start."ventanilla del Banco Unión,".$end;
                break;
                case 29: // payment at home
                    $message = "Estimado affiliado, ya se habilitó el pago a domicilio del complemento económico,".$end;
                break;
            }

        } else {
            $message = "Estimado affiliado, a partir del $start_date hasta el $end_date podrá crear el trámite para su complemento económico desde la aplicación,".$end;
        }
        return $message;
    }
    // Para la RECEPCIÓN DE REQUISITOS 
    // se debería notificar antes de que empiece el semestre [SI]
    // para el PAGO DE COMPLEMENTO ECONOMICO
    // Se debería notificar antes de que empiece el semestre [NO]
    // Desúes de la creación del trámite [SI]
    // o bien ellos tendrían que notificar antes y después o bien el software tiene que controlar eso?
}
