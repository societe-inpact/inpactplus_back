<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyFolder extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'company_folders';
    protected $fillable = ["company_id", "folder_number","folder_name", "siret", "siren"];

    protected $hidden = [
        'company_id',
        'laravel_through_key',
        'interface_id',
    ];

    public function company(){
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function software() {
        return $this->hasOne(Software::class, 'id', 'interface_id');
    }

    public function mappings(){
        return $this->belongsTo(Mapping::class, 'id', 'company_folder_id');
    }
}
