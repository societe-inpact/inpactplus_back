<?php

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
Route::post("/mapping", [App\Http\Controllers\MappingController::class, 'setMapping']);

Route::group(['middleware' => 'cors'], function () {
    Route::post('/login', [App\Http\Controllers\API\ApiAuthController::class, 'login'])->name('login');
    Route::post('/register', [App\Http\Controllers\API\ApiAuthController::class, 'register']);
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post("/import", [App\Classes\Converters\MarathonConverter::class, 'importFile']);
    Route::post("/convert", [App\Classes\Converters\MarathonConverter::class, 'convertFile']);
    Route::post("/logout", [App\Http\Controllers\API\ApiAuthController::class, 'logout']);
    Route::get("/user", [App\Http\Controllers\API\ApiAuthController::class, 'getUser']);
});
