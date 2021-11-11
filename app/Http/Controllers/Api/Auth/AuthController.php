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
     *     tags={"AUTHENTICATION"},
     *     summary="USER AUTENTICATED",
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
     *      tags={"AUTHENTICATION"},
     *      summary="LOGS USER INTO THE SYSTEM",
     *      operationId="login",
     *      @OA\Parameter(
     *          name="username",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
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

        $user= User::where('username', $request->username)->first();
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response([
                    'message' => ['These credentials do not match our records.']
                ], 404);
            }
        
             $token = $user->createToken('my-app-token')->plainTextToken;
        
            $response = [
                'user' => $user,
                'token' => $token
            ];
        
             return response($response, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/pvt/logout",
     *     tags={"AUTHENTICATION"},
     *     summary="LOGOUT SESION USER",
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
     * @OA\Get(
     *     path="/api/pvt/refresh",
     *     tags={"AUTHENTICATION"},
     *     summary="REFRESH TOCKEN OF USER",
     *     operationId="refresh",
     *     description="Refresca la sesion actual del usuario",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Refrescado correctamente")
     * )
     */
    public function refresh()
    {
        return Auth::refresh();
    }

    public function guard()
    {
        return Auth::Guard('api');
    }
}
