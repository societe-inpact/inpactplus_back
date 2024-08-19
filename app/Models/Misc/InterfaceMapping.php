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
    protected $fillable = ['colonne_matricule', 'colonne_rubrique', 'colonne_valeur','colonne_datedeb', 'colonne_datefin', 'colonne_hj','colonne_pourcentagetp', 'colonne_periode', 'type_separateur','extension'];

    public function software(){
        return $this->belongsTo(InterfaceSoftware::class, 'interface_software_id');
    }

}
