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

    public function company_folder() {
        return $this->belongsToMany(CompanyFolder::class, 'id', 'interface_id');
    }
}
