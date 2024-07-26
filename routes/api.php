<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\CheckTokenExpiry;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum']);

Route::get('/testTokenExpires', function (Request $request) {
    return $request->user();
})->middleware(['checkTokenExpiry']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout',[AuthController::class,'logout']);
