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

// --------------------------------------------------------- ROUTES PUBLIQUES --------------------------------------------------------- //
Route::group(['middleware' => 'cors'], function () {
    Route::post('/login', [App\Http\Controllers\API\AuthController::class, 'login']);
    Route::post('/register', [App\Http\Controllers\API\AuthController::class, 'register']); // Controle et actions à définir pour cette fonction
    Route::delete('/user/delete/{id}', [App\Http\Controllers\API\AuthController::class, 'deleteUser']); // Controle et actions à définir pour cette fonction
});

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/password/email', [App\Http\Controllers\API\PasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::post('/password/reset', [App\Http\Controllers\API\PasswordController::class, 'reset'])->name('password.update');
});

Route::get('/password/reset/{token}', function ($token) {
    return view('auth.passwords.reset', ['token' => $token]);
})->name('password.reset');


// --------------------------------------------------------- ROUTES PROTEGEES - AUTHENTIFICATION OBLIGATOIRE --------------------------------------------------------- //
Route::group(['middleware' => ['auth:sanctum']], function () {

    // ---------------------- ACCES AUX MODULES --------------------- //
    Route::middleware([
        'company.module.access:convert',
        'company_folder.module.access:convert',
        'user.module.access:convert'
    ])->group(function () {
        Route::post("/import", [App\Http\Controllers\API\ConvertController::class, 'importFile']);
        Route::post("/convert", [App\Http\Controllers\API\ConvertController::class, 'convertFile']);
        Route::post("/mapping/store", [App\Http\Controllers\API\MappingController::class, 'storeMapping']);
        Route::post("/mapping/{id}", [App\Http\Controllers\API\MappingController::class, 'getMapping']);
        Route::patch("/mapping/update/{id}", [App\Http\Controllers\API\MappingController::class, 'updateMapping']);
        Route::delete("/mapping/delete/{id}", [App\Http\Controllers\API\MappingController::class, 'deleteMapping']);
        Route::delete("/mapping/deleteOneLine", [App\Http\Controllers\API\MappingController::class, 'deleteOneLineMappingData']);
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

    // Middleware company
    // Middleware folder
    // Middleware user


    // ACCESS AND PERMISSIONS
    Route::post('/company_folder/add-user', [App\Http\Controllers\API\AccessController::class, 'addUserToCompanyFolder']);
    Route::post('/company_folder/delete-user', [App\Http\Controllers\API\AccessController::class, 'deleteUserFromCompanyFolder']);

    // USERS
    Route::patch('/user/update/{user_id}', [App\Http\Controllers\API\AuthController::class, 'updateUser']);
    Route::patch('/user/update/{user_id}/password', [App\Http\Controllers\API\PasswordController::class, 'changePassword']);


    // CUSTOM ABSENCES
    Route::get("/custom-absences", [App\Http\Controllers\API\AbsenceController::class, 'getCustomAbsences']);
    Route::post("/custom-absences/create", [App\Http\Controllers\API\AbsenceController::class, 'createCustomAbsence']);
    Route::patch("/custom-absences/update/{custom_absence_id}", [App\Http\Controllers\API\AbsenceController::class, 'updateCustomAbsence']);
    Route::delete("/custom-absences/delete/{custom_absence_id}", [App\Http\Controllers\API\AbsenceController::class, 'deleteCustomAbsence']);

    // ABSENCES
    Route::get("/absences", [App\Http\Controllers\API\AbsenceController::class, 'getAbsences']);
    Route::post("/absences/create", [App\Http\Controllers\API\AbsenceController::class, 'createAbsence']);
    Route::patch("/absences/update/{absence_id}", [App\Http\Controllers\API\AbsenceController::class, 'updateAbsence']);
    Route::delete("/absences/delete/{absence_id}", [App\Http\Controllers\API\AbsenceController::class, 'deleteAbsence']);

    // HOURS
    Route::get("/hours", [App\Http\Controllers\API\HourController::class, 'getHours']);
    Route::post("/hours/create", [App\Http\Controllers\API\HourController::class, 'createHour']);
    Route::patch("/hours/update/{hour_id}", [App\Http\Controllers\API\HourController::class, 'updateHour']);
    Route::delete("/hours/delete/{hour_id}", [App\Http\Controllers\API\HourController::class, 'deleteHour']);

    // CUSTOM HOURS
    Route::get("/custom-hours", [App\Http\Controllers\API\HourController::class, 'getCustomHours']);
    Route::post("/custom-hours/create", [App\Http\Controllers\API\HourController::class, 'createCustomHour']);
    Route::patch("/custom-hours/update/{custom_hour_id}", [App\Http\Controllers\API\HourController::class, 'updateCustomHour']);
    Route::delete("/custom-hours/delete/{custom_hour_id}", [App\Http\Controllers\API\HourController::class, 'deleteCustomHour']);

    // VARIABLES ELEMENTS
    Route::get("/variables-elements", [App\Http\Controllers\API\VariablesElementsController::class, 'getVariablesElements']);
    Route::post("/variables-elements/create", [App\Http\Controllers\API\VariablesElementsController::class, 'createVariableElement']);
    Route::patch("/variables-elements/update/{variable_element_id}", [App\Http\Controllers\API\VariablesElementsController::class, 'updateVariableElement']);
    Route::delete("/variables-elements/delete/{variable_element_id}", [App\Http\Controllers\API\VariablesElementsController::class, 'deleteVariableElement']);

    // COMPANIES
    Route::get("/companies", [App\Http\Controllers\API\CompanyController::class, 'getCompanies']);
    Route::post("/company/create", [App\Http\Controllers\API\CompanyController::class, 'createCompany']);
    Route::post("/company/update/{company_id}", [App\Http\Controllers\API\CompanyController::class, 'updateCompany']);
    Route::delete("/company/delete/{company_id}", [App\Http\Controllers\API\CompanyController::class, 'deleteCompany']);

    // FOLDER OF COMPANIES
    // TODO : Controller le middleware
    // Route::middleware(['user.folder.access'])->group(function () {
        Route::get("/company_folders", [App\Http\Controllers\API\CompanyFolderController::class, 'getCompanyFolders'])
            ->middleware('can:read_company_folder,App\Models\Companies\CompanyFolder');

        Route::post("/company_folder/create", [App\Http\Controllers\API\CompanyFolderController::class, 'createCompanyFolder'])
            ->middleware('can:create_company_folder,App\Models\Companies\CompanyFolder');

        Route::patch("/company_folder/update/{company_folder_id}", [App\Http\Controllers\API\CompanyFolderController::class, 'updateCompanyFolder'])
            ->middleware('can:update_company_folder,App\Models\Companies\CompanyFolder');

        Route::delete("/company_folder/delete/{company_folder_id}", [App\Http\Controllers\API\CompanyFolderController::class, 'deleteCompanyFolder'])
            ->middleware('can:delete_company_folder,App\Models\Companies\CompanyFolder');

        // ADD-UPDATE-DELETE INTERFACES FROM FOLDER
        Route::post('company_folder/{company_folder_id}/interface/add', [App\Http\Controllers\API\CompanyFolderController::class, 'addInterfaceToCompanyFolder']);
        Route::patch('company_folder/{company_folder_id}/interface/update/{interface_id}', [App\Http\Controllers\API\CompanyFolderController::class, 'updateInterfaceFromCompanyFolder']);
        Route::delete('company_folder/{company_folder_id}/interface/delete/{interface_id}', [App\Http\Controllers\API\CompanyFolderController::class, 'deleteInterfaceFromCompanyFolder']);

        // CREATE-UPDATE NOTES FROM FOLDER
        Route::post('company_folder/notes/create', [App\Http\Controllers\API\NoteController::class, 'createUpdateDeleteNote']);
    // });

    // SOFTWARE
    Route::get("/interfaces", [App\Http\Controllers\API\InterfaceController::class, 'getInterfaces']);
    Route::put("/interface/update/{interface_id}", [App\Http\Controllers\API\InterfaceController::class, 'updateNameInterface']);
    Route::delete("/interface/delete/{interface_id}", [App\Http\Controllers\API\InterfaceController::class, 'deleteNameInterface']);

    // INTERFACES MAPPING
    Route::get("/interface_mapping/{interface_mapping_id}", [App\Http\Controllers\API\InterfaceMappingController::class, 'getInterfaceMapping']);
    Route::post("/interface_mapping/create", [App\Http\Controllers\API\InterfaceMappingController::class, 'createInterfaceMapping']);
    Route::put("/interface_mapping/update/{interface_mapping_id}", [App\Http\Controllers\API\InterfaceMappingController::class, 'updateInterfaceMapping']);
    Route::delete("/interface_mapping/delete/{interface_mapping_id}", [App\Http\Controllers\API\InterfaceMappingController::class, 'deleteInterfaceMapping']);

    // Route::get("/test", [App\Http\Controllers\API\ConvertController::class, 'indexColumn']);
    Route::get("/test/indexcolonne", [App\Http\Controllers\API\ConvertInterfaceController::class, 'indexColumn']);
    Route::post("/test/convert", [App\Http\Controllers\API\ConvertInterfaceController::class, 'convertinterface']);
    Route::post("/test/maraton", [App\Http\Controllers\API\ConvertMEController::class, 'marathonConvert']);
    Route::delete("/test//mapping/deleteOne", [App\Http\Controllers\API\MappingController::class, 'deleteOneMappingData']);

    Route::post("/logout", [App\Http\Controllers\API\AuthController::class, 'logout']);
    Route::get("/user", [App\Http\Controllers\API\AuthController::class, 'getUser']);
});


