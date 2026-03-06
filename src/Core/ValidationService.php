<?php

namespace App\Core;

class ValidationService
{
    public function validate(array $input, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $value = trim($input[$field] ?? '');
            $fieldRules = explode('|', $ruleString);

            foreach ($fieldRules as $rule) {
                if ($rule === 'required' && $value === '') {
                    $errors[] = ucfirst($field) . ' is required.';
                }
                
                if ($rule === 'email' && $value !== '') {
                    if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                        $errors[] = 'A valid email is required.';
                    }
                }
            }
        }

        return $errors;
    }
}
