<?php

namespace App\Models\Companies;

use App\Models\Modules\Module;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyModuleAccess extends Model
{
    use HasFactory;

    protected $table = 'company_module_access';
    public $timestamps = false;
    protected $fillable = ["company_id", "module_id", "has_access"];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
