<?php

namespace Src\Validator;

class PasswordLengthValidator extends AbstractValidator
{
    protected string $message = 'Password must be above 6 characters';

    public function rule(): bool
    {
        $length = strlen($this->value);
        $result = $length > 6;
        return $result;
    }
}
