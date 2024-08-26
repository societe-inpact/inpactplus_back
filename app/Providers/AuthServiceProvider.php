<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\API\MappingController;
use App\Models\Companies\CompanyFolder;
use App\Models\Mapping\Mapping;
use App\Policies\CompanyFolderPolicy;
use App\Policies\Modules\MappingModulePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        CompanyFolder::class => CompanyFolderPolicy::class,
        Mapping::class => MappingModulePolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
