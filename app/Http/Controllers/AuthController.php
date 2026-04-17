<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

//login
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    public function register(UserRequest $request)
    {
        // Obtenemos solo los datos que han pasado la validación
        $validatedData = $request->validated();

        // Creamos el usuario en la base de datos
        $user = User::create([
            'name'     => $validatedData["name"],
            'email'    => $validatedData["email"],
            'password' => bcrypt($validatedData["password"]) // Encriptación obligatoria
        ]);

        return response()->json([
            "message" => "Usuario registrado correctamente"
        ], Response::HTTP_CREATED); // Status 201
    }

    public function login(LoginRequest $request){
        
    // 1. Obtener datos validados
    $validatedData = $request->validated();
    
    $credentials = [
        'email'    => $validatedData['email'],
        'password' => $validatedData['password']
    ];

    try {
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'error' => 'Usuario o contraseña inválida'
            ], Response::HTTP_UNAUTHORIZED); // 401
        }
    } catch (JWTException $e) {
        // 3. Error técnico al generar el token
        return response()->json([
            'error' => 'No se pudo generar el token'
        ], Response::HTTP_INTERNAL_SERVER_ERROR); // 500
    }

    // 4. Éxito: Devolvemos el token al cliente
    return $this->respondWithToken($token);
}
    protected function respondWithToken($token)
    {
        return response()->json([
            'token'      => $token,
            'token_type' => 'bearer', // Indica que es un token de "portador"
            'expires_in' => auth()->factory()->getTTL() * 60 // Convertimos minutos a segundos
        ]);
    }
}
