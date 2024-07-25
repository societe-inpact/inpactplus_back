<?php

namespace App\Models\Misc;

use App\Models\Companies\CompanyFolder;
use App\Models\Misc\InterfaceSoftware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    use HasFactory;

    protected $table = 'interfaces';
    public $timestamps = false;
    protected $fillable = [
        "name", "interface_software_id"
    ];

    public function companyFolders()
    {
        return $this->hasMany(CompanyFolder::class, 'interface_id');
    }

    public function software()
    {
        return $this->hasOne(InterfaceSoftware::class, 'id');
    }
}
