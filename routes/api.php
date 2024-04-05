<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::group(['middleware' => 'cors'], function () {

    Route::post('/register', [App\Http\Controllers\ApiAuthController::class, 'register']);
    Route::post('/login', [App\Http\Controllers\ApiAuthController::class, 'login'])->name('login');
});
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post("/import", [App\Classes\Converters\MarathonConverter::class, 'importFile']);
    Route::post("/convert", [App\Classes\Converters\MarathonConverter::class, 'convertFile']);
    Route::post("/logout", [App\Http\Controllers\ApiAuthController::class, 'logout']);
    Route::get("/user", [App\Http\Controllers\ApiAuthController::class, 'user']);
});
