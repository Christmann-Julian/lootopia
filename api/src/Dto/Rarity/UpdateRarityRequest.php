<?php

namespace App\Dto\Rarity;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateRarityRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\PositiveOrZero]
    private int $minExperience;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\PositiveOrZero]
    private int $experienceGain;

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

    public function __construct(int $minExperience = 0, int $experienceGain = 0, array $translations = [])
    {
        $this->minExperience = $minExperience;
        $this->experienceGain = $experienceGain;
        $this->translations = $translations;
    }

    public function getMinExperience(): int
    {
        return $this->minExperience;
    }

    public function getExperienceGain(): int
    {
        return $this->experienceGain;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }
}
