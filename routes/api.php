<?php

use App\Http\Controllers\API\AbsenceController;
use App\Http\Controllers\API\AccessController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\CompanyFolderController;
use App\Http\Controllers\API\ConvertController;
use App\Http\Controllers\API\CustomAbsenceController;
use App\Http\Controllers\API\CustomHourController;
use App\Http\Controllers\API\HistoryController;
use App\Http\Controllers\API\HourController;
use App\Http\Controllers\API\InterfaceController;
use App\Http\Controllers\API\InterfaceMappingController;
use App\Http\Controllers\API\MappingController;
use App\Http\Controllers\API\PasswordController;
use App\Http\Controllers\API\VariablesElementsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --------------------------------------------------------- PUBLIC ROUTES --------------------------------------------------------- //
Route::group(['middleware' => 'cors'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']); // Controle et actions à définir pour cette fonction
    Route::delete('/user/delete/{id}', [AuthController::class, 'deleteUser']); // Controle et actions à définir pour cette fonction
});

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/password/email', [PasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::post('/password/reset', [PasswordController::class, 'reset'])->name('password.update');
});

Route::get('/password/reset/{token}', function ($token) {
    return view('auth.passwords.reset', ['token' => $token]);
})->name('password.reset');


// --------------------------------------------------------- PROTECTED ROUTES - MANDATORY AUTHENTIFICATION --------------------------------------------------------- //
Route::group(['middleware' => ['auth:sanctum']], function () {

    // AUTHENTICATED USERS
    Route::patch('/user/update/{user_id}', [AuthController::class, 'updateUser']);

    Route::patch('/user/update/{user_id}/password', [PasswordController::class, 'changePassword']);

    Route::post("/logout", [AuthController::class, 'logout']);

    Route::get("/user", [AuthController::class, 'getUser']);



    // ---------------------- MODULES ACCESS --------------------- //

    //  MODULE MAPPING & CONVERT //
    Route::middleware(['company.module.access:convert', 'company_folder.module.access:convert', 'user.module.access:convert'])->group(function () {

        Route::post("/import", [ConvertController::class, 'importFile']);

        Route::post("/convert", [ConvertController::class, 'convertFile']);

        Route::post("/mapping/store", [App\Http\Controllers\API\MappingController::class, 'storeMapping'])
            ->middleware('can:create_mapping,App\Models\Mapping\Mapping');

        Route::post("/mapping/{id}", [MappingController::class, 'getMapping'])
            ->middleware('can:read_mapping,App\Models\Mapping\Mapping');

        Route::patch("/mapping/update/{id}", [MappingController::class, 'updateMapping'])
            ->middleware('can:update_mapping,App\Models\Mapping\Mapping');

        Route::delete("/mapping/delete/{id}", [MappingController::class, 'deleteMapping'])
            ->middleware('can:delete_mapping,App\Models\Mapping\Mapping');

        Route::delete("/mapping/deleteOneLine", [MappingController::class, 'deleteOneLineMappingData'])
            ->middleware('can:delete_mapping,App\Models\Mapping\Mapping');

    });

    //  MODULE STATISTICS //
    Route::middleware(['company.module.access:statistics', 'company_folder.module.access:statistics', 'user.module.access:statistics'])->group(function () {
        // TODO : Routes et fonctions associées au module
    });
    //  MODULE HISTORY //
    Route::middleware(['company.module.access:history', 'company_folder.module.access:history', 'user.module.access:history'])->group(function () {
        // TODO : Routes et fonctions associées au module
    });
    //  MODULE ADMIN_PANEL //
    Route::middleware(['company.module.access:admin_panel', 'company_folder.module.access:admin_panel', 'user.module.access:admin_panel'])->group(function () {
        // TODO : Routes et fonctions associées au module
    });
    // ---------------------------- FIN DES ACCES AUX MODULES --------------------------- //


    // ADD AND DELETE USER FROM COMPANY FOLDER
    Route::post('/company_folder/{company_folder_id}/add-user', [AccessController::class, 'addUserToCompanyFolder'])
        ->middleware('can:add_user_to_company_folder, App\Models\Employees\UserCompanyFolder');

    Route::delete('/company_folder/{company_folder_id}/delete-user', [AccessController::class, 'deleteUserFromCompanyFolder'])
        ->middleware('can:delete_user_from_company_folder,App\Models\Employees\UserCompanyFolder');


    // CUSTOM ABSENCES
    Route::get("/custom-absences", [CustomAbsenceController::class, 'getCustomAbsences'])
        ->middleware('can:read_custom_absences,App\Models\Absences\CustomAbsence');

    Route::post("/custom-absences/create", [CustomAbsenceController::class, 'createCustomAbsence'])
        ->middleware('can:create_custom_absence,App\Models\Absences\CustomAbsence');

    Route::patch("/custom-absences/update/{custom_absence_id}", [CustomAbsenceController::class, 'updateCustomAbsence'])
        ->middleware('can:update_custom_absence,App\Models\Absences\CustomAbsence');

    Route::delete("/custom-absences/delete/{custom_absence_id}", [CustomAbsenceController::class, 'deleteCustomAbsence'])
        ->middleware('can:delete_custom_absence,App\Models\Absences\CustomAbsence');


    // ABSENCES
    Route::get("/absences", [AbsenceController::class, 'getAbsences'])
        ->middleware('can:read_absences,App\Models\Absences\Absence');

    Route::post("/absences/create", [AbsenceController::class, 'createAbsence'])
        ->middleware('can:create_absence,App\Models\Absences\Absence');

    Route::patch("/absences/update/{absence_id}", [AbsenceController::class, 'updateAbsence'])
        ->middleware('can:update_absence,App\Models\Absences\Absence');

    Route::delete("/absences/delete/{absence_id}", [AbsenceController::class, 'deleteAbsence'])
        ->middleware('can:delete_absence,App\Models\Absences\Absence');


    // CUSTOM HOURS
    Route::get("/custom-hours", [CustomHourController::class, 'getCustomHours'])
        ->middleware('can:read_custom_hours,App\Models\Hours\CustomHour');

    Route::post("/custom-hours/create", [CustomHourController::class, 'createCustomHour'])
        ->middleware('can:create_custom_hour,App\Models\Hours\CustomHour');

    Route::patch("/custom-hours/update/{custom_hour_id}", [CustomHourController::class, 'updateCustomHour'])
        ->middleware('can:update_custom_hour,App\Models\Hours\CustomHour');

    Route::delete("/custom-hours/delete/{custom_hour_id}", [CustomHourController::class, 'deleteCustomHour'])
        ->middleware('can:delete_custom_hour,App\Models\Hours\CustomHour');


    // HOURS
    Route::get("/hours", [HourController::class, 'getHours'])
        ->middleware('can:read_hours,App\Models\Hours\Hour');

    Route::post("/hours/create", [HourController::class, 'createHour'])
        ->middleware('can:create_hour,App\Models\Hours\Hour');

    Route::patch("/hours/update/{hour_id}", [HourController::class, 'updateHour'])
        ->middleware('can:update_hour,App\Models\Hours\Hour');

    Route::delete("/hours/delete/{hour_id}", [HourController::class, 'deleteHour'])
        ->middleware('can:delete_hour,App\Models\Hours\Hour');


    // VARIABLES ELEMENTS
    Route::get("/variables-elements", [VariablesElementsController::class, 'getVariablesElements'])
        ->middleware('can:read_variables_elements,App\Models\VariablesElements\VariableElement');

    Route::post("/variables-elements/create", [VariablesElementsController::class, 'createVariableElement'])
        ->middleware('can:create_variable_element,App\Models\VariablesElements\VariableElement');

    Route::patch("/variables-elements/update/{variable_element_id}", [VariablesElementsController::class, 'updateVariableElement'])
        ->middleware('can:update_variable_element,App\Models\VariablesElements\VariableElement');

    Route::delete("/variables-elements/delete/{variable_element_id}", [VariablesElementsController::class, 'deleteVariableElement'])
        ->middleware('can:delete_variable_element,App\Models\VariablesElements\VariableElement');


    // COMPANIES
    Route::get("/companies", [CompanyController::class, 'getCompanies'])
        ->middleware('can:read_companies,App\Models\Companies\Company');

    Route::post("/company/create", [CompanyController::class, 'createCompany'])
        ->middleware('can:create_company,App\Models\Companies\Company');

    Route::patch("/company/update/{company_id}", [CompanyController::class, 'updateCompany'])
        ->middleware('can:update_company,App\Models\Companies\Company');

    Route::delete("/company/delete/{company_id}", [CompanyController::class, 'deleteCompany'])
        ->middleware('can:delete_company,App\Models\Companies\Company');


    // FOLDER OF COMPANIES
    Route::middleware(['user.folder.access'])->group(function () {
        Route::get("/company_folders", [CompanyFolderController::class, 'getCompanyFolders'])
            ->middleware('can:read_company_folders,App\Models\Companies\CompanyFolder');

        Route::get("/company_folder/{company_folder_id}", [CompanyFolderController::class, 'getCompanyFolder'])
            ->middleware('can:read_company_folder,App\Models\Companies\CompanyFolder');

        Route::post("/company_folder/create", [CompanyFolderController::class, 'createCompanyFolder'])
            ->middleware('can:create_company_folder,App\Models\Companies\CompanyFolder');

        Route::patch("/company_folder/update/{company_folder_id}", [CompanyFolderController::class, 'updateCompanyFolder'])
            ->middleware('can:update_company_folder,App\Models\Companies\CompanyFolder');

        Route::delete("/company_folder/delete/{company_folder_id}", [CompanyFolderController::class, 'deleteCompanyFolder'])
            ->middleware('can:delete_company_folder,App\Models\Companies\CompanyFolder');


        // ADD-UPDATE-DELETE INTERFACES FROM FOLDER
        Route::post('company_folder/{company_folder_id}/interface/add', [CompanyFolderController::class, 'addInterfaceToCompanyFolder'])
            ->middleware('can:create_interface_company_folder,App\Models\Misc\InterfaceSoftware');

        Route::delete('company_folder/{company_folder_id}/interface/delete/{interface_id}', [CompanyFolderController::class, 'deleteInterfaceFromCompanyFolder'])
            ->middleware('can:delete_interface_company_folder,App\Models\Misc\InterfaceSoftware');


        // CREATE-AND-UPDATE NOTES FROM FOLDER
        Route::post('company_folder/notes/create', [\App\Http\Controllers\API\NoteController::class, 'createUpdateDeleteNote'])
            ->middleware('can:create_note_company_folder,App\Models\Companies\CompanyFolder');
    });


    // INTERFACES
    Route::get("/interfaces", [InterfaceController::class, 'getInterfaces'])
        ->middleware('can:read_interfaces,App\Models\Misc\InterfaceSoftware');

    Route::post("/interface/create", [InterfaceController::class, 'createInterface'])
        ->middleware('can:create_interface,App\Models\Misc\InterfaceSoftware');

    Route::patch("/interface/update/{interface_id}", [InterfaceController::class, 'updateInterface'])
        ->middleware('can:update_interface,App\Models\Misc\InterfaceSoftware');

    Route::delete("/interface/delete/{interface_id}", [InterfaceController::class, 'deleteInterface'])
        ->middleware('can:delete_interface,App\Models\Misc\InterfaceSoftware');


    // INTERFACES MAPPING
    Route::get("/interface_mapping/{interface_mapping_id}", [InterfaceMappingController::class, 'getInterfaceMapping'])
        ->middleware('can:read_interface_mapping,App\Models\Misc\InterfaceMapping');

    Route::post("/interface_mapping/create", [InterfaceMappingController::class, 'createInterfaceMapping'])
        ->middleware('can:create_interface_mapping,App\Models\Misc\InterfaceMapping');

    Route::patch("/interface_mapping/update/{interface_mapping_id}", [InterfaceMappingController::class, 'updateInterfaceMapping'])
        ->middleware('can:update_interface_mapping,App\Models\Misc\InterfaceMapping');

    Route::delete("/interface_mapping/delete/{interface_mapping_id}", [InterfaceMappingController::class, 'deleteInterfaceMapping'])
        ->middleware('can:delete_interface_mapping,App\Models\Misc\InterfaceMapping');

    // HISTORY
    Route::get("history", [HistoryController::class, 'getAllHistory']);
    Route::get("history/user_connections/{user_id}", [HistoryController::class, 'getHistoryUserConnections']);
    Route::get("history/user_conversions/{user_id}", [HistoryController::class, 'getHistoryUserConversions']);
    Route::get("history/user_mappings/{user_id}", [HistoryController::class, 'getHistoryUserMappings']);

    Route::get("history/company_folder/conversions/{company_folder_id}", [HistoryController::class, 'getHistoryCompanyFolderConversions']);
    Route::get("history/company_folder/connections/{company_folder_id}", [HistoryController::class, 'getHistoryCompanyFolderConnections']);
    Route::get("history/company_folder/mappings/{company_folder_id}", [HistoryController::class, 'getHistoryCompanyFolderMappings']);

});


