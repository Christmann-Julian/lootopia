<?php

namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PasswordStrength;

final class ResetUserPasswordRequest
{
    #[Assert\NotBlank]
    private string $token;

    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 255)]
    #[PasswordStrength(minScore: PasswordStrength::STRENGTH_WEAK)]
    #[Assert\NotCompromisedPassword]
    private string $password = '';

    public function __construct(string $token = '', string $password = '')
    {
        $this->token = $token;
        $this->password = $password;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
