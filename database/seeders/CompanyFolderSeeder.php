<?php

namespace Database\Seeders;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyFolder;
use App\Models\Mapping\Mapping;
use App\Models\Misc\User;
use App\Models\Misc\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyFolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();
        $users = User::all();

        $companyFolders = [
            // VAPIANO
            ['folder_number' => '1001', 'email' => '', 'folder_name' => 'VAP MARBEUF', 'siret' => '81168595700029', 'siren' => '811685957','notes' => 'Notes pour VAP MARBEUF', 'telephone' => '0622013048', 'company_id' => $companies[0]->id, 'referent_id' => $users[12]->id],
            ['folder_number' => '1002', 'email' => '', 'folder_name' => 'VAP LA DEFENSE', 'siret' => '52230135700011', 'siren' => '522301357','notes' => 'Notes pour VAP LA DEFENSE', 'telephone' => '0622013048', 'company_id' => $companies[0]->id, 'referent_id' => $users[13]->id],
            ['folder_number' => '1003', 'email' => '', 'folder_name' => 'VAP LYON 2', 'siret' => '53126411700023', 'siren' => '531264117','notes' => 'Notes pour VAP LYON 2', 'telephone' => '0650583599', 'company_id' => $companies[0]->id, 'referent_id' => $users[14]->id],

            // GROUPE GARNIER
            ['folder_number' => '9021', 'email' => '', 'folder_name' => 'MPB', 'siret' => '41024650800015', 'siren' => '410246508','notes' => 'Notes pour MPB', 'telephone' => '0564249560', 'company_id' => $companies[1]->id, 'referent_id' => $users[15]->id],
            ['folder_number' => '9022', 'email' => '', 'folder_name' => 'CARLI', 'siret' => '79385074400012', 'siren' => '793850744','notes' => 'Notes pour CARLI', 'telephone' => '0564249560', 'company_id' => $companies[1]->id, 'referent_id' => $users[15]->id],
            ['folder_number' => '9023', 'email' => '', 'folder_name' => 'LES ARCHES DE PERIGUEUX', 'siret' => '38935725200029', 'siren' => '389357252','notes' => 'Notes pour LES ARCHES DE PERIGUEUX', 'telephone' => '0564249560', 'company_id' => $companies[1]->id, 'referent_id' => $users[15]->id],

            // BURGER KING
            ['folder_number' => '8920', 'email' => 'rh.bk@pbkn.fr', 'folder_name' => 'OSKB', 'siret' => '48774264500061', 'siren' => '487742645','notes' => 'Notes pour OSKB', 'telephone' => '0624147998', 'company_id' => $companies[2]->id, 'referent_id' => $users[16]->id],
            ['folder_number' => '8921', 'email' => 'rh.bk@pbkn.fr', 'folder_name' => 'PBKN2', 'siret' => '82190298800020', 'siren' => '821902988','notes' => 'Notes pour PBKN2', 'telephone' => '0624147998', 'company_id' => $companies[2]->id, 'referent_id' => $users[16]->id],
            ['folder_number' => '8922', 'email' => 'rh.bk@pbkn.fr', 'folder_name' => 'PBKN3', 'siret' => '82903566600025', 'siren' => '829035666','notes' => 'Notes pour PBKN3', 'telephone' => '0624147998', 'company_id' => $companies[2]->id, 'referent_id' => $users[16]->id],

            // LAVOREL HOTELS
            ['folder_number' => '9143', 'email' => 'admin.rh@lavorelhotels.com', 'folder_name' => 'KOPSTER COLOMBES', 'siret' => '84317334500029', 'siren' => '843173345','notes' => 'Notes pour KOPSTER COLOMBES', 'telephone' => '0661193502', 'company_id' => $companies[3]->id, 'referent_id' => $users[17]->id],
            ['folder_number' => '9163', 'email' => 'admin.rh@lavorelhotels.com', 'folder_name' => 'GRAND HOTEL DE LA ROUTE DE SENLIS', 'siret' => '90148142400025', 'siren' => '901481424','notes' => 'Notes pour GRAND HOTEL DE LA ROUTE DE SENLIS', 'telephone' => '0661193502', 'company_id' => $companies[3]->id, 'referent_id' => $users[17]->id],
            ['folder_number' => '9170', 'email' => 'admin.rh@lavorelhotels.com', 'folder_name' => 'LA POTINIERE', 'siret' => '90117797200022', 'siren' => '901177972','notes' => 'Notes pour LA POTINIERE', 'telephone' => '0478665870', 'company_id' => $companies[3]->id, 'referent_id' => $users[17]->id],

            // GROUPE GSDI
            ['folder_number' => '9410', 'email' => 'claire.priolet@gsdi.com', 'folder_name' => 'RVB', 'siret' => '49250681100040', 'siren' => '492506811','notes' => 'Notes pour RVB', 'telephone' => '0176219471', 'company_id' => $companies[4]->id, 'referent_id' => $users[18]->id],
            ['folder_number' => '9204', 'email' => 'claire.priolet@gsdi.fr', 'folder_name' => 'GSDI RHONE ALPES', 'siret' => '84144192600026', 'siren' => '841441926','notes' => 'Notes pour GSDI RHONE ALPES', 'telephone' => '0145364947', 'company_id' => $companies[4]->id, 'referent_id' => $users[18]->id],
            ['folder_number' => '9206', 'email' => 'claire.priolet@gsdi.fr', 'folder_name' => 'COVERING HOLDING', 'siret' => '90772258100028', 'siren' => '907722581','notes' => 'Notes pour COVERING HOLDING', 'telephone' => '0145364946', 'company_id' => $companies[4]->id, 'referent_id' => $users[18]->id],
        ];

        foreach ($companyFolders as $companyFolder) {
            $createdCompanyFolder = CompanyFolder::create($companyFolder);
            $referent = User::find($createdCompanyFolder['referent_id']);

            if ($referent) {
                $referent->update(['company_id' => $createdCompanyFolder['company_id']]);

                if ($referent->company_id == $createdCompanyFolder->company_id) {
                    DB::table('user_company_folder')->updateOrInsert(
                        [
                            'user_id' => $referent->id,
                            'company_folder_id' => $createdCompanyFolder->id,
                            'has_access' => 1
                        ]
                    );
                }

                DB::table('model_has_roles')->updateOrInsert(
                    ['role_id' => 2, 'model_type' => 'App\\Models\\Misc\\User', 'model_id' => $referent['id']]
                );
            }
            Mapping::create([
                'company_folder_id' => $createdCompanyFolder['id'],
                'data' => [],
            ]);
        }
    }
}
