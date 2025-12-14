<?php

namespace App\Dto\User;

use App\Validator\Constraints\UniqueEmail;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdateUserRequest
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

    /** @var array<string> */
    #[Assert\All(new Assert\Type('string'))]
    private array $roles = [];

    private bool $isVerified = false;

    public function __construct(
        string $firstname = '',
        string $lastname = '',
        string $email = '',
        array $roles = [],
        bool $isVerified = false,
    ) {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
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

    /** @return array<string> */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getIsVerified(): bool
    {
        return $this->isVerified;
    }
}
