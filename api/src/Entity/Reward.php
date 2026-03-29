<?php

namespace App\Entity;

use App\Repository\RewardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RewardRepository::class)]
class Reward
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?string $link = null;

    #[ORM\Column]
    private ?\DateTime $endDate = null;

    /**
     * @var Collection<int, RewardTranslation>
     */
    #[ORM\OneToMany(targetEntity: RewardTranslation::class, mappedBy: 'reward', orphanRemoval: true)]
    private Collection $rewardTranslations;

    #[ORM\OneToOne(inversedBy: 'reward', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Hunt $hunt = null;

    public function __construct()
    {
        $this->rewardTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTime $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return Collection<int, RewardTranslation>
     */
    public function getRewardTranslations(): Collection
    {
        return $this->rewardTranslations;
    }

    public function addRewardTranslation(RewardTranslation $rewardTranslation): static
    {
        if (!$this->rewardTranslations->contains($rewardTranslation)) {
            $this->rewardTranslations->add($rewardTranslation);
            $rewardTranslation->setReward($this);
        }

        return $this;
    }

    public function removeRewardTranslation(RewardTranslation $rewardTranslation): static
    {
        if ($this->rewardTranslations->removeElement($rewardTranslation)) {
            // set the owning side to null (unless already changed)
            if ($rewardTranslation->getReward() === $this) {
                $rewardTranslation->setReward(null);
            }
        }

        return $this;
    }

    public function getHunt(): ?Hunt
    {
        return $this->hunt;
    }

    public function setHunt(Hunt $hunt): static
    {
        $this->hunt = $hunt;

        return $this;
    }

    public function getTranslation(string $locale): ?RewardTranslation
    {
        foreach ($this->rewardTranslations as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
    }

    public function toArray(?string $locale = null): array
    {
        $data = [
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'link' => $this->getLink(),
            'endDate' => $this->getEndDate()?->format(\DateTimeInterface::ATOM),
            'huntId' => $this->getHunt()?->getId(),
        ];

        if ($locale) {
            $translation = $this->getTranslation($locale);
            $data['title'] = $translation ? $translation->getTitle() : null;
        } else {
            $translations = [];
            foreach ($this->rewardTranslations as $t) {
                $translations[$t->getLocale()] = $t->getTitle();
            }
            $data['translations'] = $translations;
        }

        return $data;
    }
}
