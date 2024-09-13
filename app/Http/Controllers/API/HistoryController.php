<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Companies\CompanyFolder;
use App\Models\Employees\UserCompanyFolder;
use App\Traits\JSONResponseTrait;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class HistoryController extends Controller
{
    use JSONResponseTrait;


    public function getAllHistory()
    {
        $history = Activity::all()->last();
        return $this->successResponse($history);
    }

    // HISTORIQUE DU USER SEUL

    public function getHistoryUserConnections()
    {
        $user = Auth::user();
        $historyUserConnections = Activity::where('causer_id', $user->id)->where('event', 'login')->get();
        $userConnections = [];

        if ($historyUserConnections) {
            foreach ($historyUserConnections as $historyUserConnection) {
                $userConnections[] = [
                    'label' => $historyUserConnection->log_name,
                    'description' => $historyUserConnection->description,
                    'details' => $historyUserConnection->properties,
                ];
            }
            return $this->successResponse($userConnections);
        }

        return $this->errorHistoryResponse([], 'Aucun historique de connexion trouvé pour l\'utilisateur', 404);
    }

    public function getHistoryUserConversions()
    {
        $user = Auth::user();
        $historyUserConversions = Activity::where('causer_id', $user->id)->where('event', 'convert')->get();
        $userConnections = [];

        if ($historyUserConversions) {
            foreach ($historyUserConversions as $historyUserConversion) {
                $userConnections[] = [
                    'label' => $historyUserConversion->log_name,
                    'description' => $historyUserConversion->description,
                    'details' => $historyUserConversion->properties,
                ];
            }
            return $this->successResponse($userConnections);
        }

        return $this->errorHistoryResponse([], 'Aucun historique de conversion trouvé pour l\'utilisateur', 404);
    }

    public function getHistoryUserMappings()
    {
        $user = Auth::user();
        $historyUserMappings = Activity::where('causer_id', $user->id)->where('event', 'mapping')->get();
        $userMappings = [];

        if ($historyUserMappings) {
            foreach ($historyUserMappings as $historyUserMapping) {
                $userMappings[] = [
                    'label' => $historyUserMapping->log_name,
                    'description' => $historyUserMapping->description,
                    'details' => $historyUserMapping->properties,
                ];
            }
            return $this->successResponse($userMappings);
        }

        return $this->errorHistoryResponse([], 'Aucun historique de mapping trouvé pour l\'utilisateur', 404);
    }

    // HISTORIQUE DU COMPANY FOLDER

    public function getHistoryCompanyFolderConversions($id)
    {
        $activities = Activity::where('event', 'convert')
            ->get();

        // Filtrer les activités pour ne garder que celles ayant le bon company_folder_id dans les propriétés JSON
        $filteredActivity = $activities->filter(function($activity) use ($id) {
            $properties = json_decode($activity->properties, true);
            return isset($properties['company_folder_id']) && $properties['company_folder_id'] == $id;
        });

        if ($filteredActivity->isEmpty()) {
            return $this->errorHistoryResponse([], 'Aucun historique de conversion trouvé pour le dossier', 404);
        }

        // Mapper chaque activité en un objet JSON (stdClass)
        $result = $filteredActivity->map(function($activity) {
            return (object) json_decode($activity->properties, true); // Cast en objet
        })->values(); // Assurer que les clés sont réindexées

        return $this->successResponse($result->toArray());
    }

    public function getHistoryCompanyFolderConnections($id)
    {
        $companyFolder = CompanyFolder::findOrFail($id);
        $companyFolderId = $companyFolder->id;

        $userIds = UserCompanyFolder::where('company_folder_id', $companyFolderId)
            ->pluck('user_id');

        if ($userIds->isEmpty()) {
            return $this->errorResponse('Aucun utilisateur trouvé pour ce dossier');
        }

        $activities = Activity::whereIn('causer_id', $userIds)
            ->where('event', 'login')
            ->get();

        $filteredActivity = $activities->map(function($activity) {
            return json_decode($activity->properties, true);
        });

        if ($filteredActivity->isEmpty()) {
            return $this->errorHistoryResponse([], 'Aucun historique de connexion trouvé pour le dossier', 404);
        }

        return $this->successResponse($filteredActivity);
    }

    public function getHistoryCompanyFolderMappings($id)
    {
        $activities = Activity::where('event', 'mapping')
            ->get();

        // Filtrer les activités pour ne garder que celles ayant le bon company_folder_id dans les propriétés JSON
        $filteredActivity = $activities->filter(function($activity) use ($id) {
            $properties = json_decode($activity->properties, true);
            return isset($properties['company_folder_id']) && $properties['company_folder_id'] == $id;
        });

        if ($filteredActivity->isEmpty()) {
            return $this->errorHistoryResponse([], 'Aucun historique de mapping trouvé pour le dossier', 404);
        }

        return $this->successResponse($filteredActivity->map(function($activity) {
            return json_decode($activity->properties, true);
        }));
    }
}
