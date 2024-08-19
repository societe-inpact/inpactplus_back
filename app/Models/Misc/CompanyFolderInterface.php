<?php

namespace App\Models\Misc;

use App\Models\Companies\CompanyFolder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyFolderInterface extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'company_folder_interface';
    protected $fillable = ["company_folder_id","company_folder_interface"];

    public function companyFolder()
    {
        return $this->belongsTo(CompanyFolder::class, 'company_folder_id', 'id');
    }

    public function software()
    {
        return $this->belongsTo(InterfaceSoftware::class, 'interface_folder_id');
    }
}
