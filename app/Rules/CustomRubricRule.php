<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CustomRubricRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cleanedValue = preg_replace('/\s*-\s*/', '-', trim($value));
        // Vérification des conditions pour les "EV-"
        if (str_starts_with($cleanedValue, 'EV-')) {
            // Vérifier qu'il n'y a pas d'espace autour de "-"
            if (!preg_match('/^EV-\s*\S+/', $cleanedValue)) {
                $fail(':attribute a un format invalide pour le préfixe EV.');
            }
            return;
        }

        // Vérification qu'il n'y a pas d'espace autour de "-" pour les autres cas
        if (!preg_match('/^[A-Z]{2,}-[^\s]+$/', $cleanedValue)) {
            $fail('Le format de :attribute est invalide.');
        }
    }
}
