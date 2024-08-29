<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CustomRubricRule implements ValidationRule
{
    /**
     * Règle de contrôle qui s'assure qu'il n'y a pas d'espace dans la chaîne de caractère du code rubrique.
     * Exemple : EV - Nom => EV-Nom
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cleanedValue = preg_replace('/\s*-\s*/', '-', trim($value));

        /** Vérification des conditions pour les éléments variables */
        if (str_starts_with($cleanedValue, 'EV-')) {

            /** Vérification qu'il n'y a pas d'espace autour de "-" */
            if (!preg_match('/^EV-\s*\S+/', $cleanedValue)) {
                $fail(':attribute a un format invalide pour le préfixe EV.');
            }
            return;
        }

        /** Vérification qu'il n'y a pas d'espace autour de "-" pour les autres cas */
        if (!preg_match('/^[A-Z]{2,}-[^\s]+$/', $cleanedValue)) {
            $fail('Le format de :attribute est invalide.');
        }
    }
}
