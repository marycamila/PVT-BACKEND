<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
use Validator;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/pvt/aut",
     *     tags={"AUTENTICACIÓN"},
     *     summary="OBTENER USUARIO AUTENTICADO",
     *     operationId="getuser",
     *     description="Obtiene el usuario autenticado",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="ok")
     * )
     */
    public function index()
    {
        return Auth::user();
    }
 
    /**
     * @OA\Post(
     *      path="/api/pvt/login",
     *      tags={"AUTENTICACIÓN"},
     *      summary="ACCESO AL SISTEMA",
     *      operationId="login",
     *      description="Acceso al sistema con el token",
     *      @OA\RequestBody(
     *          description= "Provide auth credentials",
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="username", type="string",description="username"),
     *              @OA\Property(property="password", type="string",description="password")
     *          )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *      )
     * )
     *
     * Logs user into the system.
     *
     * @param Request $request
     * @return void
    */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:8'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors());
        }
        if(Auth::attempt(['username' => $request->username, 'password' => $request->password])){
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()
                ->json(['data' => $user,'access_token' => $token, 'token_type' => 'Bearer', ]);
        }else{
            return response()->json(['error' => 'No Autorizado'], 401);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/pvt/logout",
     *     tags={"AUTENTICACIÓN"},
     *     summary="CERRAR SESIÓN",
     *     operationId="logout",
     *     description="Cierra la sesion actual del usuario",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Sesion cerrada correctamente")
     * )
     */

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'Has cerrado sesión correctamente y el token se ha eliminado correctamente.'
        ];
    }

     /**
     * @OA\Patch(
     *     path="/api/pvt/refresh",
     *     tags={"AUTENTICACIÓN"},
     *     summary="REFRESCAR TOCKEN",
     *     operationId="refresh",
     *     description="Refresca la sesion actual del usuario",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Refrescado correctamente")
     * )
     */
    public function refresh()
    {
        Auth::user()->tokens()->delete();

        return response()->json([
            'access_token' => Auth::user()->createToken('api')->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }

    public function guard()
    {
        return Auth::Guard('api');
    }
}
