<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Honeypot validation rule.
 * This field should be empty (bots will fill it).
 */
class Honeypot implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Honeypot fields should be empty
        if (! empty($value)) {
            $fail('The :attribute field must be empty.');
        }
    }
}

