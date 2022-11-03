<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\Admin\User;
use App\Models\Affiliate\Affiliate;
use App\Models\Affiliate\AffiliateToken;
use App\Models\Affiliate\AffiliateUser;
use App\Models\Affiliate\Spouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use PhpParser\Node\Stmt\Return_;

class AffiliateUserController extends Controller
{
    public static function  Generate_pin()
    {
        $referal_code = random_int(1000, 9999);
        return $referal_code;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


     /**
     * @OA\Post(
     *     path="/api/affiliate/store",
     *     tags={"AFILIADO"},
     *     summary="CREACION DE CREDENCIALES PARA AFILIADO",
     *     operationId="create_credencials",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="affiliate_id", type="int",description="id del afiliado required")
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *            type="object"
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     *
     * Get affiliate
     *
     * @param Request $request
     * @return void
     */


    public function store(Request $request)
    {
        $request->validate([
            'affiliate_id' => 'required|integer|exists:affiliates,id'
        ]);
        $AffiliateId = $request->affiliate_id;
        $isDead=Affiliate::find($AffiliateId)->dead;
        $affiliate =Affiliate::find($AffiliateId);
        if (!$affiliate->cell_phone_number) {
            return response()->json([
                'error'=>true,
                'message' => 'El afiliado no tiene registrado su numero',
                'payload'=>[]
            ]);
        }
        $isAffiliateToken = DB::table('affiliate_tokens')->where('affiliate_id', $AffiliateId)->exists();
        if (!$isAffiliateToken) {
            $AffiliateToken = new AffiliateToken;
            $AffiliateToken->affiliate_id = $AffiliateId;
            $AffiliateToken->save();
            $AffiliateUser = new AffiliateUser;
            $AffiliateUser->affiliate_token_id = $AffiliateToken->id;
            if (Affiliate::find($AffiliateId)->spouse && $isDead) {
                $spouse=Spouse::where('affiliate_id',$AffiliateId)->first();
                $AffiliateUser->username=$spouse->identity_card;
                $password=$this->Generate_pin();
                $AffiliateUser->password = Hash::make($password);
                $AffiliateUser->save();
                $message='Credenciales registradas exitosamente para viuda';
                $response=$this->send_messages($AffiliateUser->username,$password,$AffiliateId,$message);
                $existsError=$response->original['error'];
                if (!$existsError) {
                    $AffiliateUser->save();
                    return $response;
                }
                else{
                    return $response;
                }
            }
            else {
                $AffiliateUser->username= $affiliate->identity_card;
                $password=$this->Generate_pin();
                $AffiliateUser->password = Hash::make($password);
                $AffiliateUser->save();
                $message='Credenciales registradas exitosamente para titular';
                $response=$this->send_messages($AffiliateUser->username,$password,$AffiliateId,$message);
                $existsError=$response->original['error'];
                if (!$existsError) {
                    $AffiliateUser->save();
                    return $response;
                }
                else{
                    return $response;
                }
            }
        }
        else {
            $AffiliateToken = AffiliateToken::where('affiliate_id',$AffiliateId)->first();
            $isAffiliateUser = DB::table('affiliate_users')->where('affiliate_token_id', $AffiliateToken->id)->exists();
            if ($isAffiliateUser) {
                $AffiliateUser=AffiliateUser::where('affiliate_token_id',$AffiliateToken->id)->first();
                if ($AffiliateUser->access_status!='Activo'){
                    if (Affiliate::find($AffiliateId)->spouse && $isDead) {
                        $spouse=Spouse::where('affiliate_id',$AffiliateId)->first();
                        $AffiliateUser->username=$spouse->identity_card;
                        $password=$this->Generate_pin();
                        $AffiliateUser->password = Hash::make($password);
                        $AffiliateUser->access_status = 'Pendiente';
                        $message='Se reasigno credenciales para viudedad';
                        $response=$this->send_messages($AffiliateUser->username,$password,$AffiliateId,$message);
                        $existsError=$response->original['error'];
                        if (!$existsError) {
                            $AffiliateUser->save();
                            return $response;
                        }
                        else{
                            return $response;
                        }
                    }
                    else {
                        $AffiliateUser->username= $affiliate->identity_card;
                        $password=$this->Generate_pin();
                        $AffiliateUser->password = Hash::make($password);
                        $AffiliateUser->access_status = 'Pendiente';
                        $AffiliateUser->save();
                        $message='Se reasigno credenciales para el titular';
                        $response=$this->send_messages($AffiliateUser->username,$password,$AffiliateId,$message);
                        $existsError=$response->original['error'];
                        if (!$existsError) {
                            $AffiliateUser->save();
                            return $response;
                        }
                        else{
                            return $response;
                        }
                    }
                }
                else{
                    return response()->json([
                    'error'=>false,
                    'message' => 'El afiliado ya tiene una cuenta activa',
                    'payload'=>[]
                ]);
                }
            }
            else {
                $AffiliateUser = new AffiliateUser;
                $AffiliateUser->affiliate_token_id = $AffiliateToken->id;
                if (Affiliate::find($AffiliateId)->spouse && $isDead) {
                    $spouse=Spouse::where('affiliate_id',$AffiliateId)->first();
                    $AffiliateUser->username=$spouse->identity_card;
                    $password=$this->Generate_pin();
                    $AffiliateUser->password = Hash::make($password);
                    $AffiliateUser->save();
                    $message='Credenciales registradas exitosamente para viuda';
                    $response=$this->send_messages($AffiliateUser->username,$password,$AffiliateId,$message);
                    $existsError=$response->original['error'];
                    if (!$existsError) {
                        $AffiliateUser->save();
                        return $response;
                    }
                    else{
                        return $response;
                    }
                }
                else {
                    $AffiliateUser->username= $affiliate->identity_card;
                    $password=$this->Generate_pin();
                    $AffiliateUser->password = Hash::make($password);
                    $AffiliateUser->save();
                    $message='Credenciales registradas exitosamente para titular';
                    $response=$this->send_messages($AffiliateUser->username,$password,$AffiliateId,$message);
                    $existsError=$response->original['error'];
                    if (!$existsError) {
                        $AffiliateUser->save();
                        return $response;
                    }
                    else{
                        return $response;
                    }
                }
            }
        }
    }


    public static function send_messages($username,$password,$affiliateId,$message ) {
        $cell_phone_number=Affiliate::find($affiliateId)->cell_phone_number;
        $user=Auth::user()->id;
        $separator = ",";
        $separate = explode($separator,$cell_phone_number);
        $response= Http::timeout(60)->post('http://192.168.2.201:8989/api/notification/send_credentials',[
            "user_id"=>$user,
            "shipments"=> [
                [
                    "id"=>$affiliateId,
                    "sms_num"=> $separate[0],
                    "message"=> "usuario: ".$username." contraseña: ". $password
                ]
            ]
        ]);
        if (!(json_decode($response->getBody())->error)) {
                return response()->json([
                'message' => $message,
                'error'=>false,
                'telular_response' => json_decode($response->getBody()),
                'payload' => [
                    'username'=> $username,
                    'pin' => $password,
                    'affiliate_id' => $affiliateId
                ],
            ]);
        }
        else {
            return response()->json([
                'error'=>true,
                'message' => 'No se envio el mensaje',
                'telular_response' => json_decode($response->getBody()),
                'payload' => [
                ],
            ]);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Affiliate\AffiliateUser  $affiliateUser
     * @return \Illuminate\Http\Response
     */
    public function show(AffiliateUser $affiliateUser)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Affiliate\AffiliateUser  $affiliateUser
     * @return \Illuminate\Http\Response
     */
    public function edit(AffiliateUser $affiliateUser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Affiliate\AffiliateUser  $affiliateUser
     * @return \Illuminate\Http\Response
     */
/**
     * @OA\Post(
     *     path="/api/affiliate/auth",
     *     tags={"OFICINA VIRTUAL"},
     *     summary="AUTENTIFICACION",
     *     operationId="Auth credendial",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="username", type="string",description="usuario del afiliado ci del que tiene las cedenciales required"),
     *              @OA\Property(property="password", type="string",description="password o pin required"),
     *              @OA\Property(property="device_id", type="string",description="device_id required"),
     *              @OA\Property(property="firebase_token", type="string",description="token proporcionado por firebase required"),
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *            type="object"
     *         )
     *     ),
     * )
     *
     * Get affiliate
     *
     * @param Request $request
     * @return void
     */

    public static function auth(Request $request){
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'device_id' => 'required',
            'firebase_token' => 'required'
        ]);

        $isAffiliateUser = DB::table('affiliate_users')->where('username', $request->username)->exists();
        if($isAffiliateUser){
            $AffiliateUser=AffiliateUser::where('username',$request->username)->first();
            $state=$AffiliateUser->access_status;
            $password=$AffiliateUser->password;
            switch ($state) {
                case 'Pendiente':
                    if (Hash::check($request->password,$password)) {
                        return response()->json([
                            "error"=> false,
                            'message' => 'Acceso Correcto cambie la password',
                            'data'=> [
                                'status'=> $state
                            ]
                        ]);
                    }
                    else {
                        return response()->json([
                            "error"=> true,
                            'message' => 'El pin es incorrecto',
                            'data'=> [
                                'status'=> $state
                                ]
                        ],403);
                    }
                    break;
                case 'Activo':
                    if (Hash::check($request->password,$password)) {
                        $AffiliateToken=AffiliateToken::find($AffiliateUser->affiliate_token_id);
                        // $AffiliateToken->device_id=$request->device_id;
                        $AffiliateToken->api_token=Hash::make($request->device_id);
                        $token=$AffiliateToken;
                        $AffiliateToken->firebase_token=$request->firebase_token;
                        $AffiliateToken->save();
                        $affiliate=Affiliate::find($AffiliateToken->affiliate_id);
                        if ($affiliate->identity_card==$request->username) {
                            return response()->json(
                                [
                                    'error' => false,
                                    'message' => 'Acceso Correcto',
                                    'data'=> [
                                        'api_token'=>$token->api_token,
                                        'status'=> $state,
                                        "user"=> [
                                            "id" => $affiliate->id,
                                            "full_name"=> $affiliate->FullName,
                                            "identity_card"=> $affiliate->identity_card,
                                            "degree"=> $affiliate->degree->name,
                                            "category"=> $affiliate->category->name,
                                        ],
                                    ],
                                ]
                                );
                        }
                        else {
                        $spouse=Spouse::where('affiliate_id',$affiliate->id)->first();
                        return response()->json(
                            [
                                'error' => false,
                                'message' => 'Acceso Correcto',
                                'data'=> [
                                    'api_token'=>$token->api_token,
                                    'status'=> $state,
                                    "user"=> [
                                        "id" => $spouse->id,
                                        "full_name"=> $spouse->FullName,
                                        "identity_card"=> $spouse->identity_card,
                                        "degree"=> $affiliate->degree->name,
                                        "category"=> $affiliate->category->name,
                                    ],
                                ],
                            ]
                            );
                        }
                        }
                        else {
                            return response()->json([
                                "error"=> true,
                                'message' => 'El password es incorrecto',
                                'data'=> [
                                    'status'=> $state
                                    ]
                            ],403);
                        }
                    break;
                default:
                return response()->json([
                    "error"=> true,
                    'message' => 'Credenciales Inactivas',
                    'data'=> [
                        'status'=> $state
                        ]
                ],403);
                    break;
            }
        }
        else{
            return response()->json([
                "error"=> true,
                'message' => 'El username es incorrecto',
                'data'=> [

                    ]
            ],403);
        }
    }
/**
     * @OA\Patch(
     *     path="/api/affiliate/change_password",
     *     tags={"OFICINA VIRTUAL"},
     *     summary="CAMBIAR DE CONTRASEÑA",
     *     operationId="Change Password",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="username", type="string",description="usuario del afiliado ci del que tiene las cedenciales required"),
     *              @OA\Property(property="password", type="string",description="pin proporcionado required"),
     *              @OA\Property(property="new_password", type="string",description="password nueva o pin required"),
     *              @OA\Property(property="device_id", type="string",description="device_id required"),
     *              @OA\Property(property="firebase_token", type="string",description="token proporcionado por firebase required"),
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *            type="object"
     *         )
     *     ),
     * )
     *
     * Get affiliate
     *
     * @param Request $request
     * @return void
     */


    public function change_password(Request $request){
        $request->validate([
            'username' => 'required|integer|exists:affiliate_users,username',
            'password' => 'required',
            'new_password' => 'required',
            'device_id' => 'required',
            'firebase_token' => 'required'
        ]);
        $AffiliateUser=AffiliateUser::where('username',$request->username)->first();
        // $state=$AffiliateUser->access_status;
        $password=$AffiliateUser->password;
        if (Hash::check($request->password,$password)){
            $AffiliateUser->password=Hash::make($request->new_password);
            $AffiliateUser->access_status='Activo';
            $state=$AffiliateUser->access_status;
            $AffiliateUser->save();
            $AffiliateToken=AffiliateToken::find($AffiliateUser->affiliate_token_id);
            $AffiliateToken->api_token=Hash::make($request->device_id);
            $AffiliateToken->save();
            return response()->json(
                [
                    'message' => 'contraseña actualizada Correctamente ',
                    'data'=> [
                        'status'=> $state
                    ]
                ]);
        }
        else {
            return response()->json(
                [
                    'message' => 'Pin invalido '
                ],403);
        }
    }

    public function update(Request $request, AffiliateUser $affiliateUser)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Affiliate\AffiliateUser  $affiliateUser
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->affiliate->affiliate_token()->update(['api_token' => null]);
        return response()->json([
            'error' => false,
            'message' => 'Sesión terminada',
            'data' => (object)[]
        ], 200);
    }
    public static function switch_states()
    {
        $AllAffiliateUser=AffiliateUser::where('access_status','Pendiente')->get();
        foreach ($AllAffiliateUser as $AffiliateUser) {
            $AffiliateUser->access_status='Inactivo';
            $AffiliateUser->save();
        }
        return response()->json([
            'message' => 'cambiada',
        ], 200);
    }
}
