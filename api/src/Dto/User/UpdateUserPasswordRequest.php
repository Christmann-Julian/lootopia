<?php

namespace App\Dto\User;

use App\Validator\Constraints\CurrentPassword;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PasswordStrength;

final class UpdateUserPasswordRequest
{
    #[Assert\NotBlank]
    #[CurrentPassword]
    private string $currentPassword;

    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 255)]
    #[PasswordStrength(minScore: PasswordStrength::STRENGTH_WEAK)]
    #[Assert\NotCompromisedPassword]
    private string $newPassword;

    public function __construct(string $currentPassword, string $newPassword)
    {
        $this->currentPassword = $currentPassword;
        $this->newPassword = $newPassword;
    }

    public function getCurrentPassword(): string
    {
        return $this->currentPassword;
    }

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }
}
