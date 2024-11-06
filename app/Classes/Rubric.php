<?php

namespace App\Classes;

use App\Models\Absences\Absence;
use App\Models\Absences\CustomAbsence;
use App\Models\Hours\CustomHour;
use App\Models\Hours\Hour;
use App\Models\VariablesElements\VariableElement;

class Rubric
{
    public $is_used;
    public ?string $output_type;
    public ?string $therapeutic_part_time;
    public $type_rubric;
    public $input_rubric;
    public $output_rubric_id;
    public $company_folder_id;

    public function __construct($data)
    {
        $this->is_used = $data['is_used'];
        $this->output_type = $data['output_type'] ?? null;
        $this->type_rubric = $data['type_rubric'] ?? null;
        $this->therapeutic_part_time = $data['therapeutic_part_time'] ?? null;
        $this->input_rubric = $data['input_rubric'];
        $this->output_rubric_id = $data['output_rubric_id'] ?? null;
        $this->company_folder_id = $data['company_folder_id'] ?? null;
    }

    public function getSilaeRubric(?int $companyFolderId = null, ?int $rubricId = null)
    {
        if (!$this->is_used){
            return collect([$this]);
        }
        preg_match('/[^\\\]+$/', $this->output_type, $matches);
        $outputType = $matches[0];

        switch ($outputType) {
            case 'CustomAbsence':
            {
                $customAbsenceQuery = CustomAbsence::query();

                if ($this->output_rubric_id) {
                    $customAbsenceQuery->where('id', $this->output_rubric_id);
                }

                if ($companyFolderId) {
                    $customAbsenceQuery->where('company_folder_id', $companyFolderId);
                }

                $customAbsence = $customAbsenceQuery->first();

                if (!$customAbsence) {
                    return null; // ou gérer le cas où l'absence n'est pas trouvée
                }
                return $customAbsence;
            }
            case 'CustomHour':
            {
                $customHourQuery = CustomHour::query();

                if ($this->output_rubric_id) {
                    $customHourQuery->where('id', $this->output_rubric_id);
                }

                if ($companyFolderId) {
                    $customHourQuery->where('company_folder_id', $companyFolderId);
                }

                $customHour = $customHourQuery->first();

                if (!$customHour) {
                    return null; // ou gérer le cas où l'absence n'est pas trouvée
                }
                return $customHour;
            }
            case 'VariableElement':
            {
                return VariableElement::find($this->output_rubric_id);

            }
            case 'Absence':
            {
                return Absence::find($this->output_rubric_id);
            }
            case 'Hour':
            {
                return Hour::find($this->output_rubric_id);
            }
        }
    }
}
