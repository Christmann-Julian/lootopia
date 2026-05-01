<?php

namespace App\Dto\Reward;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateRewardRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $code;

    #[Assert\NotBlank]
    #[Assert\Url]
    #[Assert\Length(max: 255)]
    private string $link;

    #[Assert\NotBlank]
    #[Assert\DateTime(format: 'Y-m-d\TH:i')]
    private string $endDate;

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

    public function __construct(string $code = '', string $link = '', string $endDate = '', array $translations = [])
    {
        $this->code = $code;
        $this->link = $link;
        $this->endDate = $endDate;
        $this->translations = $translations;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getEndDate(): \DateTime
    {
        return new \DateTime($this->endDate);
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }
}
