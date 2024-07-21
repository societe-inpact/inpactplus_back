<?php

namespace App\Models\Modules;

use App\Models\Companies\Company;
use App\Models\Misc\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyModuleAccess extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'company_module_access';

    protected $hidden = ["id", 'company_id', 'module_id', 'has_access'];

    protected $fillable = ['company_id', 'module_id', 'has_access'];


    // RELATIONS
    public function user()
    {
        return $this->belongsTo(Company::class);
    }
}
