<?php

namespace App\Dto\Badge;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateBadgeRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $icon;

    /** @var array<string, string> */
    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[Assert\Collection(
        fields: [
            'fr' => new Assert\Required([new Assert\NotBlank(), new Assert\Length(max: 255)]),
            'en' => new Assert\Required([new Assert\NotBlank(), new Assert\Length(max: 255)]),
        ],
        allowMissingFields: false
    )]
    private array $translations;

    public function __construct(string $icon = '', array $translations = [])
    {
        $this->icon = $icon;
        $this->translations = $translations;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }
}
