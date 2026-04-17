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