<?php

namespace App\Models\Misc;

use App\Models\Companies\CompanyFolder;
use App\Models\Misc\InterfaceMapping;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterfaceSoftware extends Model
{
    use HasFactory;

    protected $table = 'interfaces';
    public $timestamps = false;
    protected $hidden = [ "laravel_through_key"];
    protected $fillable = [
        "name", "interface_mapping_id"
    ];

    public function companyFolders()
    {
        return $this->belongsToMany(CompanyFolder::class, 'company_folder_interface', 'interface_id');
    }

    public function interfaces()
    {
        return $this->belongsTo(InterfaceMapping::class, 'interface_mapping_id');
    }
}
