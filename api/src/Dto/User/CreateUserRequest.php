<?php

namespace App\Dto\User;

use App\Validator\Constraints\UniqueEmail;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PasswordStrength;

final class CreateUserRequest
{
    #[Assert\NotBlank()]
    #[Assert\Length(min: 2, max: 100)]
    private string $firstname;

    #[Assert\NotBlank()]
    #[Assert\Length(min: 2, max: 100)]
    private string $lastname;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[UniqueEmail]
    private string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 255)]
    #[PasswordStrength(minScore: PasswordStrength::STRENGTH_WEAK)]
    #[Assert\NotCompromisedPassword]
    private string $password;

    /** @var array<string> */
    #[Assert\All(new Assert\Type('string'))]
    private array $roles = ['ROLE_USER'];

    private bool $isVerified = false;

    public function __construct(
        string $firstname,
        string $lastname,
        string $email,
        string $password,
        array $roles = ['ROLE_USER'],
        bool $isVerified = false,
    ) {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
        $this->isVerified = $isVerified;
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

    /** @return array<string> */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }
}
