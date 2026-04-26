<?php

namespace App\Dto\Hunt;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateHuntRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('float')]
    private float $lat;

    #[Assert\NotBlank]
    #[Assert\Type('float')]
    private float $lon;

    #[Assert\Type('integer')]
    private ?int $categoryId;

    private bool $isSponsor = false;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    private int $rarityId;

    /** @var array<string, array<string, string>> */
    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[Assert\Collection(
        fields: [
            'fr' => new Assert\Collection([
                'title' => new Assert\NotBlank(),
                'description' => new Assert\NotBlank(),
                'question' => new Assert\NotBlank(),
                'answer' => new Assert\NotBlank(),
                'location' => new Assert\NotBlank(),
            ]),
            'en' => new Assert\Collection([
                'title' => new Assert\NotBlank(),
                'description' => new Assert\NotBlank(),
                'question' => new Assert\NotBlank(),
                'answer' => new Assert\NotBlank(),
                'location' => new Assert\NotBlank(),
            ]),
        ],
        allowMissingFields: false
    )]
    private array $translations;

    /** @var array<string, mixed> */
    #[Assert\NotBlank]
    #[Assert\Type('array')]
    private array $reward;

    public function __construct(
        float $lat = 0.0,
        float $lon = 0.0,
        bool $isSponsor = false,
        ?int $categoryId = null,
        int $rarityId = 0,
        array $translations = [],
        array $reward = [],
    ) {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->isSponsor = $isSponsor;
        $this->categoryId = $categoryId;
        $this->rarityId = $rarityId;
        $this->translations = $translations;
        $this->reward = $reward;
    }

    public function getLat(): float
    {
        return $this->lat;
    }

    public function getLon(): float
    {
        return $this->lon;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function getRarityId(): int
    {
        return $this->rarityId;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function getReward(): array
    {
        return $this->reward;
    }

    public function getIsSponsor(): bool
    {
        return $this->isSponsor;
    }
}
