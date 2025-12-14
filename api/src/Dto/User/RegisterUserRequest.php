<?php

namespace App\Dto\User;

use App\Validator\Constraints\UniqueEmail;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class RegisterUserRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private string $firstname = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private string $lastname = '';

    #[Assert\NotBlank]
    #[Assert\Email]
    #[UniqueEmail]
    private string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 255)]
    #[PasswordStrength(minScore: PasswordStrength::STRENGTH_WEAK)]
    #[Assert\NotCompromisedPassword]
    private string $password = '';

    public function __construct(
        string $firstname,
        string $lastname,
        string $email,
        string $password,
    ) {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->password = $password;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
