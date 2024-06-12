<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    use HasFactory;

    protected $table = 'interfaces';

    protected $fillable = [
        "name"
    ];

    public function companyFolders()
    {
        return $this->hasMany(CompanyFolder::class, 'interface_id');
    }
}
