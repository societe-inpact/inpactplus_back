<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\API\MappingController;
use App\Models\Absences\Absence;
use App\Models\Absences\CustomAbsence;
use App\Models\Companies\Company;
use App\Models\Companies\CompanyFolder;
use App\Models\Employees\UserCompanyFolder;
use App\Models\Hours\CustomHour;
use App\Models\Hours\Hour;
use App\Models\Mapping\Mapping;
use App\Models\Misc\InterfaceMapping;
use App\Models\Misc\InterfaceSoftware;
use App\Models\VariablesElements\VariableElement;
use App\Policies\AbsencePolicy;
use App\Policies\CompanyFolderPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\CustomAbsencePolicy;
use App\Policies\CustomHourPolicy;
use App\Policies\HourPolicy;
use App\Policies\InterfaceMappingPolicy;
use App\Policies\InterfacePolicy;
use App\Policies\Modules\MappingModulePolicy;
use App\Policies\VariableElementPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Mapping::class => MappingModulePolicy::class,
        // TODO : Modules History, Statistics et AdminPanel à créer et ajouter ici

        Absence::class => AbsencePolicy::class,
        CustomAbsence::class => CustomAbsencePolicy::class,
        Hour::class => HourPolicy::class,
        CustomHour::class => CustomHourPolicy::class,
        Company::class => CompanyPolicy::class,
        CompanyFolder::class => CompanyFolderPolicy::class,
        UserCompanyFolder::class => CompanyFolderPolicy::class,
        InterfaceMapping::class => InterfaceMappingPolicy::class,
        InterfaceSoftware::class => InterfacePolicy::class,
        VariableElement::class => VariableElementPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
