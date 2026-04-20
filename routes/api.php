<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

//test
Route::get("/test", function() {
    return "El backend funciona correctamente";
});

//Login & registro:

Route::post("/register", [AuthController::class, 'register']);
Route::post("/login", [AuthController::class, 'login'])->name("login");

// Rutas Protegidas por JWT
Route::middleware("jwt.auth")->group(function(){
    Route::get('/who', [AuthController::class, 'who']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});