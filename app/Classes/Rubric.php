<?php

namespace App\Classes;

use App\Models\Absences\Absence;
use App\Models\Absences\CustomAbsence;
use App\Models\Hours\CustomHour;
use App\Models\Hours\Hour;

class Rubric
{
    public $is_used;
    public ?string $output_type;
    public $name_rubrique;
    public $input_rubrique;
    public $output_rubrique_id;

    public function __construct($data)
    {
        $this->is_used = $data['is_used'];
        $this->output_type = $data['output_type'] ?? null;
        $this->name_rubrique = $data['name_rubrique'] ?? null;
        $this->input_rubrique = $data['input_rubrique'];
        $this->output_rubrique_id = $data['output_rubrique_id'] ?? null;
    }

    public function getSilaeRubric(?int $companyFolderId = null, ?int $rubricId = null)
    {
        if (!$this->is_used){
            return collect($this);
        }
        preg_match('/[^\\\]+$/', $this->output_type, $matches);
        $outputType = $matches[0];

        switch ($outputType) {
            case 'CustomAbsence':
            {
                $customAbsence = CustomAbsence::find($this->output_rubrique_id);
                if (!$customAbsence && !$companyFolderId){
                    return $customAbsence;
                }
                return $customAbsence->where('company_folder_id', '=', $companyFolderId);
            }
            case 'CustomHour':
            {
                $customHour = CustomHour::find($this->output_rubrique_id);
                if (!$customHour && !$companyFolderId){
                    return $customHour;
                }
                return $customHour->where('company_folder_id', '=', $companyFolderId);
            }
            case 'Absence':
            {
                return Absence::find($this->output_rubrique_id);
            }
            case 'Hour':
            {
                return Hour::find($this->output_rubrique_id);
            }
        }
    }
}
