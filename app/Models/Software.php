<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'interfaces';

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
