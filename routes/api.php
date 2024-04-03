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
Route::group(['middleware' => 'api', 'cors'], function () {
    Route::post("/import", [App\Classes\Converters\MarathonConverter::class, 'importFile']);
    Route::post("/convert", [App\Classes\Converters\MarathonConverter::class, 'convertFile']);
    Route::post('/login', [App\Http\Controllers\ApiAuthController::class, 'login'])->name('login.api');
    Route::post('/register', [App\Http\Controllers\ApiAuthController::class, 'register'])->name('register.api');
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
