<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
final class CurrentPassword extends Constraint
{
    public string $message = 'invalid_current_password';

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}
