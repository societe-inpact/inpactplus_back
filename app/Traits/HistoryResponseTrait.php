<?php

namespace App\Traits;

use App\Models\Companies\CompanyFolderModuleAccess;
use App\Models\Modules\CompanyModuleAccess;
use App\Models\Modules\Module;
use Illuminate\Database\Eloquent\Collection;

trait HistoryResponseTrait
{
    public function setConnectionHistory($log_name, $user, $event, $date, $description = null)
    {
        return activity()
            ->inLog($log_name)
            ->performedOn($user)
            ->event($event)
            ->withProperties([
                'user_id' => $user->id,
                'lastname' => $user->lastname,
                'firstname' => $user->firstname,
                'date' => $date
            ])
            ->log($description);
    }

    public function setMappingHistory($log_name, $user, $event, $date, $description = null, $input_rubric, $type_rubric, $output_rubric){
        return activity()
            ->inLog($log_name)
            ->performedOn($user)
            ->event($event)
            ->withProperties([
                'user_id' => $user->id,
                'lastname' => $user->lastname,
                'firstname' => $user->firstname,
                'date' => $date,
                'input_rubric' => $input_rubric,
                'type_rubric' => $type_rubric,
                'output_rubric' => $output_rubric,
            ])
            ->log($description);
    }

    public function setConvertHistory($log_name, $user, $company_folder_id, $event, $date, $description = null, $imported_file, $converted_file, $updated_file = null){
        return activity()
            ->inLog($log_name)
            ->performedOn($user)
            ->event($event)
            ->withProperties([
                'user_id' => $user->id,
                'lastname' => $user->lastname,
                'firstname' => $user->firstname,
                'company_folder_id' => $company_folder_id,
                'date' => $date,
                'imported_file' => $imported_file,
                'converted_file' => $converted_file,
                'updated_file' => $updated_file,
            ])
            ->log($description);
    }
}
