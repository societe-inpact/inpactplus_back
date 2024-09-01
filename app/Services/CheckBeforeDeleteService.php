<?php

namespace App\Services;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyFolder;
use App\Traits\JSONResponseTrait;

class CheckBeforeDeleteService
{
    use JSONResponseTrait;

    public function checkCompanyBeforeDelete(int $companyId): array
    {
        $company = Company::find($companyId);
        $message = "La suppression de l'entreprise entraînera la suppression de ses employés et son référent.";

        if (!$company) {
            return $this->errorResponse('Entreprise non trouvée', 404);
        }

        $usersCount = $company->employees()->count();

        if ($usersCount > 0) {
            $message .= " Il y a actuellement {$usersCount} employé(s) associé(s).";
        }

        $message .= " Êtes-vous sûr de vouloir continuer ?";

        return [
            'message' => $message,
            'confirm' => $usersCount > 0
        ];
    }

    public function checkCompanyFolderBeforeDelete(int $companyFolderId): array
    {
        $companyFolder = CompanyFolder::find($companyFolderId);
        $message = "La suppression du dossier entraînera la suppression de ses employés et son référent.";

        if (!$companyFolder) {
            return $this->errorResponse('Dossier d\'entreprise non trouvée', 404);
        }

        $usersCount = $companyFolder->employees()->count();

        if ($usersCount > 0) {
            $message .= " Il y a actuellement {$usersCount} employé(s) associé(s).";
        }

        $message .= " Êtes-vous sûr de vouloir continuer ?";

        return [
            'message' => $message,
            'confirm' => $usersCount > 0
        ];
    }
}
