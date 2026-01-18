<?php

namespace App\Dto\User;

use App\Enum\RolesEnum;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdateUserRequest
{
    #[Assert\NotBlank()]
    #[Assert\Length(min: 2, max: 100)]
    private string $firstname;

    #[Assert\NotBlank()]
    #[Assert\Length(min: 2, max: 100)]
    private string $lastname;

    #[Assert\Length(max: 255)]
    private ?string $company = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email;

    /** @var array<string> */
    #[Assert\All(
        new Assert\Choice(choices: RolesEnum::VALUES)
    )]
    private array $roles = [];

    private bool $isVerified = false;

    public function __construct(
        string $firstname = '',
        string $lastname = '',
        ?string $company = null,
        string $email = '',
        array $roles = [],
        bool $isVerified = false,
    ) {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->company = $company;
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

    public function getCompany(): ?string
    {
        return $this->company;
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
