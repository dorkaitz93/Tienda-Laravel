<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;

//login
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Requests\LoginRequest;
use App\Events\UserLoggedIn;
use App\Events\UserRegistered;


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
        
        event(new UserRegistered($user));
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
        return response()->json([
            'error' => 'No se pudo generar el token'
        ], Response::HTTP_INTERNAL_SERVER_ERROR); //500
    }
    
    event(new UserLoggedIn(auth()->user(), $request->ip()));
    // 4. Éxito: Devolvemos el token al cliente
    return $this->respondWithToken($token);
}

    public function who()
    {
        return response()->json(auth()->user());
    }

    public function logout()
{
    try {
        //Recuperamos el token de la cabecera Authorization
        $token = JWTAuth::getToken();

        //Lo metemos en la blacklist
        JWTAuth::invalidate($token);

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    } catch (JWTException $e) {
        //Si el token ya era inválido o no existe
        return response()->json([
            'error' => 'No se pudo cerrar la sesión, el token no es válido'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
    public function refresh()
{
    try {
        // 1. Recuperamos el token actual de la petición
        $token = JWTAuth::getToken();

        // 2. Generamos el nuevo token (automáticamente invalida el anterior)
        $newToken = auth()->refresh();

        // 3. Invalidamos el token viejo manualmente (por seguridad extra)
        JWTAuth::invalidate($token);

        // 4. Devolvemos la respuesta estándar con el nuevo "expires_in"
        return $this->respondWithToken($newToken);

    } catch (JWTException $e) {
        return response()->json([
            'error' => 'Error al refrescar el token, la sesión puede haber expirado del todo'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

    protected function respondWithToken($token)
    {
        return response()->json([
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60 // Convertimos minutos a segundos
        ]);
    }
}
