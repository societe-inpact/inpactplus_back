<?php

namespace App\Models\Misc;

use App\Models\Misc\InterfaceSoftware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterfaceMapping extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'interface_mapping';
    protected $fillable = ['employee_number', 'rubric', 'value','start_date', 'end_date', 'hj','pourcentage_tp', 'period', 'separator_type', 'extension'];

    public function software(){
        return $this->belongsTo(InterfaceSoftware::class, 'interface_software_id');
    }

}
