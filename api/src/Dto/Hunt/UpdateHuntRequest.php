<?php

namespace App\Dto\Hunt;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateHuntRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('float')]
    private float $lat;

    #[Assert\NotBlank]
    #[Assert\Type('float')]
    private float $lon;

    private ?bool $isSponsor = false;

    #[Assert\Type('integer')]
    private ?int $categoryId;

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

    public function __construct(
        float $lat = 0.0,
        float $lon = 0.0,
        ?int $categoryId = null,
        int $rarityId = 0,
        array $translations = [],
        ?bool $isSponsor = false,
    ) {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->categoryId = $categoryId;
        $this->rarityId = $rarityId;
        $this->translations = $translations;
        $this->isSponsor = $isSponsor;
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

    public function getIsSponsor(): ?bool
    {
        return $this->isSponsor;
    }
}
