<?php

namespace App\Dto\Rank;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateRankRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\PositiveOrZero]
    private int $experienceMin;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\GreaterThan(propertyPath: 'experienceMin')]
    private int $experienceMax;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    private int $level;

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

    public function __construct(int $experienceMin = 0, int $experienceMax = 0, int $level = 1, array $translations = [])
    {
        $this->experienceMin = $experienceMin;
        $this->experienceMax = $experienceMax;
        $this->level = $level;
        $this->translations = $translations;
    }

    public function getExperienceMin(): int
    {
        return $this->experienceMin;
    }

    public function getExperienceMax(): int
    {
        return $this->experienceMax;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }
}
