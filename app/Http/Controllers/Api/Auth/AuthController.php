<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
use Validator;
use App\Models\User;
use App\Http\Resources\UserResource;

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
        $user = User::whereUsername($request->username)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $tokens = $user->tokens()->count();
                if ($tokens == 1) {
                    $token = $user->getRememberToken();
                } else {
                    if ($tokens > 1) {
                        $user->tokens()->delete();
                    }
                    $token = $user->createToken('api')->plainTextToken;
                    $user->remember_token = $token;
                    $user->save();
                }
                return [
                    'message' => 'Sesión iniciada',
                    'payload' => [
                        'access_token' => $token,
                        'token_type' => 'Bearer',
                        'user' => new UserResource($user),
                        //'permissions' => $user->getAllPermissions()->pluck('name')->unique(),
                    ],
                ];
            }
        }
        return response()->json([
            'message' => 'Credenciales inválidas',
            'errors' => [
                'username' => ['Usuario o contraseña incorrecta']
            ]
        ], 401);
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
