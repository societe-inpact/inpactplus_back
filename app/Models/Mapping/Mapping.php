<?php

namespace App\Models\Mapping;

use App\Models\Companies\CompanyFolder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapping extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "mapping";
    protected $hidden = ['id', 'company_folder_id'];
    protected $fillable = [
        "company_folder_id",
        "data",
    ];

    protected $casts = ['data' => 'array'];

    public function output()
    {
        return $this->morphTo('output_rubrique', 'output_type', 'output_rubrique_id');
    }

    public function folder()
    {
        return $this->belongsTo(CompanyFolder::class, 'company_folder_id');
    }
}
