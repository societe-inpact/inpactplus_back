<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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

    public function getHistoryUserConnections()
    {
        $user = Auth::user();
        $historyUserConnections = Activity::where('causer_id', $user->id)->where('event', 'login')->get();
        $userConnections = [];

        if ($historyUserConnections){
            foreach ($historyUserConnections as $historyUserConnection){
                $userConnections[] = [
                        'label' => $historyUserConnection->log_name,
                        'description' => $historyUserConnection->description,
                        'details' => $historyUserConnection->properties,
                ];
            }
            return $this->successResponse($userConnections);
        }

        return $this->errorResponse('Aucun historique pour l\'utilisateur');
    }

    public function getHistoryUserConversions()
    {
        $user = Auth::user();
        $historyUserConversions = Activity::where('causer_id', $user->id)->where('event', 'convert')->get();
        $userConnections = [];

        if ($historyUserConversions){
            foreach ($historyUserConversions as $historyUserConversion){
                $userConnections[] = [
                    'label' => $historyUserConversion->log_name,
                    'description' => $historyUserConversion->description,
                    'details' => $historyUserConversion->properties,
                ];
            }
            return $this->successResponse($userConnections);
        }

        return $this->errorResponse('Aucun historique pour l\'utilisateur');
    }

    public function getHistoryUserMappings(){
        // TODO : En cours
    }
}
