<?php

namespace App\Models\Misc;

use App\Models\Misc\Software;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterfaceSoftware extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'interface_software';
    protected $fillable = ['colonne_matricule', 'colonne_rubrique', 'colonne_valeur','colonne_datedeb', 'colonne_datefin', 'colonne_hj','colonne_pourcentagetp', 'colonne_periode', 'type_separateur','format'];

    public function software(){
        return $this->belongsTo(Software::class, 'interface_software_id');
    }

}
