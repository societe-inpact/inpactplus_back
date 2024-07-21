<?php

namespace App\Models\Companies;

use App\Models\Modules\Module;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyFolderModuleAccess extends Model
{
    use HasFactory;

    protected $table = 'company_folder_module_access';
    protected $hidden = ['company_folder_id', 'module_id'];
    protected $fillable = ["company_folder_id", "module_id", "has_access"];

    public function folder()
    {
        return $this->belongsTo(CompanyFolder::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
