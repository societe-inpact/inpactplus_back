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
    Route::post('/login', [App\Http\Controllers\API\AuthController::class, 'login']);
    Route::post('/register', [App\Http\Controllers\API\AuthController::class, 'register']);
});

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/password/email', [App\Http\Controllers\API\PasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::post('/password/reset', [App\Http\Controllers\API\PasswordController::class, 'reset'])->name('password.update');
});

Route::get('/password/reset/{token}', function ($token) {
    return view('auth.passwords.reset', ['token' => $token]);
})->name('password.reset');

// PROTECTED ROUTES


Route::group(['middleware' => ['auth:sanctum']], function () {

    // ---------------------- ACCES AUX MODULES --------------------- //
    Route::middleware(['company.module.access:convert'])->group(function () {
        Route::post("/import", [App\Http\Controllers\API\ConvertController::class, 'importFile']);
        Route::post("/convert", [App\Http\Controllers\API\ConvertController::class, 'convertFile']);

        Route::post("/mapping", [App\Http\Controllers\API\MappingController::class, 'getMapping']);
        Route::post("/mapping/store", [App\Http\Controllers\API\MappingController::class, 'storeMapping']);
        Route::patch("/mapping/update/{id}", [App\Http\Controllers\API\MappingController::class, 'updateMapping']);
        Route::delete("/mapping/delete/{id}", [App\Http\Controllers\API\MappingController::class, 'deleteMapping']);
        Route::delete("/mapping/delete", [App\Http\Controllers\API\MappingController::class, 'deleteAllMapping']);
    });

    Route::middleware(['company.module.access:statistics'])->group(function () {
        // TODO : Routes et fonctions associées au module
    });

    Route::middleware(['company.module.access:history'])->group(function () {
        // TODO : Routes et fonctions associées au module
    });

    Route::middleware(['company.module.access:admin_panel'])->group(function () {
        // TODO : Routes et fonctions associées au module
    });
    // ---------------------- FIN ACCES AUX MODULES --------------------- //



    // ACCESS AND PERMISSIONS
    Route::post('/company_folder/add-user', [App\Http\Controllers\API\AccessController::class, 'addUserToCompanyFolder']);
    Route::post('/company_folder/delete-user', [App\Http\Controllers\API\AccessController::class, 'deleteUserFromCompanyFolder']);

    // USERS
    Route::patch('/user/update/{id}', [App\Http\Controllers\API\AuthController::class, 'updateUser']);
    Route::patch('/user/update/{id}/password', [App\Http\Controllers\API\PasswordController::class, 'changePassword']);

    // CUSTOM ABSENCES
    Route::get("/custom-absences", [App\Http\Controllers\API\AbsenceController::class, 'getCustomAbsences']);
    Route::post("/custom-absences/create", [App\Http\Controllers\API\AbsenceController::class, 'createCustomAbsence']);
    Route::patch("/custom-absences/update/{id}", [App\Http\Controllers\API\AbsenceController::class, 'updateCustomAbsence']);
    Route::delete("/custom-absences/delete/{id}", [App\Http\Controllers\API\AbsenceController::class, 'deleteCustomAbsence']);

    // ABSENCES
    Route::get("/absences", [App\Http\Controllers\API\AbsenceController::class, 'getAbsences']);
    Route::post("/absences/create", [App\Http\Controllers\API\AbsenceController::class, 'createAbsence']);
    Route::patch("/absences/update/{id}", [App\Http\Controllers\API\AbsenceController::class, 'updateAbsence']);
    Route::delete("/absences/delete", [App\Http\Controllers\API\AbsenceController::class, 'deleteAbsence']);

    // HOURS
    Route::get("/hours", [App\Http\Controllers\API\HourController::class, 'getHours']);
    Route::post("/hours/create", [App\Http\Controllers\API\HourController::class, 'createHour']);
    Route::patch("/hours/update/{id}", [App\Http\Controllers\API\HourController::class, 'updateHour']);
    Route::delete("/hours/delete", [App\Http\Controllers\API\HourController::class, 'deleteHour']);

    // CUSTOM HOURS
    Route::get("/custom-hours", [App\Http\Controllers\API\HourController::class, 'getCustomHours']);
    Route::post("/custom-hours/create", [App\Http\Controllers\API\HourController::class, 'createCustomHour']);
    Route::patch("/custom-hours/update/{id}", [App\Http\Controllers\API\HourController::class, 'updateCustomHour']);
    Route::delete("/custom-hours/delete/{id}", [App\Http\Controllers\API\HourController::class, 'deleteCustomHour']);

    // VARIABLES ELEMENTS
    Route::get("/variables-elements", [App\Http\Controllers\API\VariablesElementsController::class, 'getVariablesElements']);
    Route::post("/variables-elements/create", [App\Http\Controllers\API\VariablesElementsController::class, 'createVariableElement']);
    Route::patch("/variables-elements/update/{id}", [App\Http\Controllers\API\VariablesElementsController::class, 'updateVariableElement']);
    Route::delete("/variables-elements/delete/{id}", [App\Http\Controllers\API\VariablesElementsController::class, 'deleteVariableElement']);

    // COMPANIES
    Route::get("/companies", [App\Http\Controllers\API\CompanyController::class, 'getCompanies']);
    Route::post("/company/create", [App\Http\Controllers\API\CompanyController::class, 'createCompany']);
    Route::post("/company/update/{id}", [App\Http\Controllers\API\CompanyController::class, 'updateCompany']);
    Route::delete("/company/delete", [App\Http\Controllers\API\CompanyController::class, 'deleteCompany']);

    // FOLDER OF COMPANIES
    Route::get("/company_folders", [App\Http\Controllers\API\CompanyFolderController::class, 'getCompanyFolders']);
    Route::post("/company_folder/create", [App\Http\Controllers\API\CompanyFolderController::class, 'createCompanyFolder']);
    Route::patch("/company_folder/update/{id}", [App\Http\Controllers\API\CompanyFolderController::class, 'updateCompanyFolder']);
    Route::delete("/company_folder/delete", [App\Http\Controllers\API\CompanyFolderController::class, 'deleteCompanyFolder']);

    // SOFTWARE
    Route::get("/software", [App\Http\Controllers\API\SoftwareController::class, 'getSoftware']);
    Route::put("/software/update/{id}", [App\Http\Controllers\API\SoftwareController::class, 'updateNameSoftware']);
    Route::delete("/software/delete/{id}", [App\Http\Controllers\API\SoftwareController::class, 'deleteNameSoftware']); // Supprime tout le software

    // INTERFACES SOFTWARE
    Route::get("/interfacesoftware/info/{id}", [App\Http\Controllers\API\InterfaceSoftwareController::class, 'getInterfaceSoftware']);
    Route::post("/interfacesoftware/create", [App\Http\Controllers\API\InterfaceSoftwareController::class, 'createInterfaceSoftware']);
    Route::put("/interfacesoftware/update/{id}", [App\Http\Controllers\API\InterfaceSoftwareController::class, 'updateInterfaceSoftware']);
    Route::delete("/interfacesoftware/delete/{id}", [App\Http\Controllers\API\InterfaceSoftwareController::class, 'deleteInterfaceSoftware']); // Supprime le mapping du software

    // TEST

    // Route::get("/test", [App\Http\Controllers\API\ConvertController::class, 'indexColumn']);
    Route::get("/test/indexcolonne", [App\Http\Controllers\API\ConvertInterfaceController::class, 'indexColumn']);
    Route::post("/test/convert", [App\Http\Controllers\API\ConvertInterfaceController::class, 'convertinterface']);
    Route::post("/test/maraton", [App\Http\Controllers\API\ConvertMEController::class, 'marathonConvert']);

    // NOTES FROM FOLDER OF COMPANIES
    Route::get('/company_folder/notes', [App\Http\Controllers\API\NoteController::class, 'getNotes']);
    Route::post('company_folder/notes/create', [App\Http\Controllers\API\NoteController::class, 'createUpdateDeleteNote']);

    Route::post("/logout", [App\Http\Controllers\API\AuthController::class, 'logout']);
    Route::get("/user", [App\Http\Controllers\API\AuthController::class, 'getUser']);
});


