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

// LOGIN AND REGISTER
Route::group(['middleware' => 'cors'], function () {
    Route::post('/login', [App\Http\Controllers\API\ApiAuthController::class, 'login']);
    Route::post('/register', [App\Http\Controllers\API\ApiAuthController::class, 'register']);
    Route::post('/reset-password', [App\Http\Controllers\API\ApiAuthController::class, 'resetPassword']);
});

// IMPORT AND CONVERT
Route::post("/import", [App\Http\Controllers\API\ConvertController::class, 'importFile']);
Route::post("/convert", [App\Http\Controllers\API\ConvertController::class, 'convertFile']);

// MAPPING
Route::post("/mapping", [App\Http\Controllers\API\MappingController::class, 'getMapping']);
Route::post("/mapping/store", [App\Http\Controllers\API\MappingController::class, 'storeMapping']);
Route::put("/mapping/update/{id}", [App\Http\Controllers\API\MappingController::class, 'updateMapping']);

// ABSENCES
Route::get("/absences", [App\Http\Controllers\API\AbsenceController::class, 'getAbsences']);
Route::get("/custom-absences", [App\Http\Controllers\API\AbsenceController::class, 'getCustomAbsences']);
Route::post("/custom-absences/create", [App\Http\Controllers\API\AbsenceController::class, 'createCustomAbsence']);

// HOURS
Route::get("/hours", [App\Http\Controllers\API\HourController::class, 'getHours']);
Route::get("/custom-hours", [App\Http\Controllers\API\HourController::class, 'getCustomHours']);
Route::post("/custom-hours/create", [App\Http\Controllers\API\HourController::class, 'createCustomHour']);

// VARIABLES ELEMENTS
Route::get("/variables-elements", [App\Http\Controllers\API\VariablesElementsController::class, 'getVariablesElements']);
Route::post("/variables-elements/create", [App\Http\Controllers\API\VariablesElementsController::class, 'createVariableElement']);

// COMPANIES
Route::get("/companies", [App\Http\Controllers\API\CompanyController::class, 'getCompanies']);
Route::post("/company/create", [App\Http\Controllers\API\CompanyController::class, 'createCompany']);

// FOLDER OF COMPANIES
Route::post("/company_folder/create", [App\Http\Controllers\API\CompanyFolderController::class, 'createCompanyFolder']);
Route::put("/company_folder/update/{id}", [App\Http\Controllers\API\CompanyFolderController::class, 'updateCompanyFolder']);

// SOFTWARE
Route::get("/software", [App\Http\Controllers\API\SoftwareController::class, 'getSoftware']);
Route::put("/software/update", [App\Http\Controllers\API\SoftwareController::class, 'updateNameSoftware']);
Route::delete("/software/delete", [App\Http\Controllers\API\SoftwareController::class, 'deleteNameSoftware']);

// INTERFACES SOFTWARE
Route::get("/interfacesoftware/info", [App\Http\Controllers\API\InterfaceSoftwareController::class, 'getInterfaceSoftware']);
Route::post("/interfacesoftware/create", [App\Http\Controllers\API\InterfaceSoftwareController::class, 'createInterfaceSoftware']);
Route::put("/interfacesoftware/update", [App\Http\Controllers\API\InterfaceSoftwareController::class, 'updateInterfaceSoftware']);
Route::delete("/interfacesoftware/delete", [App\Http\Controllers\API\InterfaceSoftwareController::class, 'deleteInterfaceSoftware']);

// NOTES FROM FOLDER OF COMPANIES
Route::get('/company_folder/notes', [App\Http\Controllers\API\NoteController::class, 'getNotes']);
Route::post('company_folder/notes/create', [App\Http\Controllers\API\NoteController::class, 'createNotes']);
Route::put('company_folder/notes/update', [App\Http\Controllers\API\NoteController::class, 'updateNotes']);
Route::delete('company_folder/notes/delete', [App\Http\Controllers\API\NoteController::class, 'deleteNotes']);

// PROTECTED ROUTES
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post("/logout", [App\Http\Controllers\API\ApiAuthController::class, 'logout']);
    Route::get("/user", [App\Http\Controllers\API\ApiAuthController::class, 'getUser']);
});
