<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\EconomicComplement\EcoComProcedure;
use App\Models\EconomicComplement\EcoComState;
use App\Models\EconomicComplement\EconomicComplement;
use App\Models\EconomicComplement\EcoComModality;
use App\Models\Notification\NotificationSend;
use App\Models\Affiliate\Hierarchy;
use App\Models\ObservationType;
use App\Models\Admin\Module;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;

class NotificationController extends Controller
{
    // Para obtener todos los semestres
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
        $hierarchies = Hierarchy::select('id', 'name')->where('id', '>', 1)->get();
        return response()->json([
            'hierarchies' => $hierarchies
        ]);
    }

    // Obtener el semestre actual para la notificación
    public function current_semester(){
        $last_eco_com = EcoComProcedure::latest('year')->first();
        return array($last_eco_com->year, $last_eco_com->semester);
    }

    // Microservicio para consumir la ruta del backend node
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

    // Método para pruebas
    public function show($array) {
        for($i = 0; $i < sizeof($array); $i++){
            logger($i.'  => '.$array[$i]);
        }
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

    public function mass_notification(Request $request) {
        // ---------------------------------  FILTRADOS -----------------------------------------
        $semester         = $request->semester;          // Si selecciona el semestre
        $year             = $request->year;              // Necesariamente se necesita el año
        $action           = $request->action;            // La acción que se necesita consultar
        $reception_type   = $request->reception_type;    // Vejez, viudedad y orfandad          (quizá no sea necesario)
        $beneficiary_type = $request->beneficiary_type;  // habitual o inclusión
        $hierarchies      = $request->hierarchies_level; // nivel jerarquico
        // ---------------------------------------------------------------------------------------
        $user_id          = Auth::user();                // el usuario que generó la notificación

        if($action == 'economic_complement_payment' || $action == 'receipt_of_requirements'){
            $title_notification = $request->title;                // título de notificación
            $attached           = $request->attached;             // mensaje para la notificación
            $loaded_image       = $request->loaded_image ?? "";   // Si carga la imagen

            if($request->has('payment_method')) $payment_method = $request->payment_method;
            else $payment_method = $request->has('payment_method');

            // [$year, $semester] = $this->current_semester(); // Para obtener el semestre actual para notificar

            $res = EcoComProcedure::select('normal_start_date', 'normal_end_date')->latest('year')->first();
            [$start_date, $end_date] = array($res->normal_start_date, $res->normal_end_date);

            $message  = $this->build_message($action, $payment_method, $year, $semester, $start_date, $end_date); // Armado del mensaje
            $message .= ' '.$attached;

            $tokens = [];
            $ids    = [];
            $params = [];
            $data   = [
                'title' => $title_notification,
                'image' => "https://www.opinion.com.bo/asset/thumbnail,992,558,center,center//media/opinion/images/2022/05/24/2022052415283420630.jpg", // ellos deben cargar la imagen
                'PublicationDate' => "alguna cosa",
                'text'  => $message 
            ];

            $params['user_id'] = $user_id;
            $params['ids']     = $ids;
            $params['tokens']  = $tokens;
            $params['data']    = $data;
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
                        $this->to_register($params['user_id'], $status, $params['data'], $params['subject'], $params['ids']);
                    }
                    else logger("no envío");

                    logger("-----------------    ENVÍO LOTE NRO $count  --------------------------");
                    sleep(3); // esperar durante 3 segundos
                });

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
        $eco_com_state_types = EcoComState::all();
        $eco_com = EconomicComplement::find($request->ecoComId);
        $observation = ObservationType::find($request->observationTypeId);
        $eco_com->observations()->save($observation, [
            'user_id' => Auth::user()->id,
            'date' => Carbon::now(),
            'message' => $request->message,
            'enabled' => $request->enabled
        ]);
    }

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
}
