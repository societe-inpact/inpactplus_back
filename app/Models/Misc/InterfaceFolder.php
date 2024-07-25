<?php

namespace App\Models\Misc;

use App\Models\Companies\CompanyFolder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterfaceFolder extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'interface_folder';
    protected $fillable = ["company_folder_id","interface_folder_id"];

    public function companyFolder()
    {
        return $this->belongsTo(CompanyFolder::class, 'company_folder_id', 'id');
    }

    public function software()
    {
        return $this->belongsTo(Software::class, 'interface_folder_id');
    }
}
