<?php

namespace App\Entity;

use App\Repository\RankRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RankRepository::class)]
class Rank
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $experienceMin = null;

    #[ORM\Column]
    private ?int $experienceMax = null;

    #[ORM\Column]
    private ?int $level = null;

    /**
     * @var Collection<int, RankTranslation>
     */
    #[ORM\OneToMany(targetEntity: RankTranslation::class, mappedBy: 'rank', orphanRemoval: true)]
    private Collection $rankTranslations;

    public function __construct()
    {
        $this->rankTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExperienceMin(): ?int
    {
        return $this->experienceMin;
    }

    public function setExperienceMin(int $experienceMin): static
    {
        $this->experienceMin = $experienceMin;

        return $this;
    }

    public function getExperienceMax(): ?int
    {
        return $this->experienceMax;
    }

    public function setExperienceMax(int $experienceMax): static
    {
        $this->experienceMax = $experienceMax;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return Collection<int, RankTranslation>
     */
    public function getRankTranslations(): Collection
    {
        return $this->rankTranslations;
    }

    public function addRankTranslation(RankTranslation $rankTranslation): static
    {
        if (!$this->rankTranslations->contains($rankTranslation)) {
            $this->rankTranslations->add($rankTranslation);
            $rankTranslation->setRank($this);
        }

        return $this;
    }

    public function removeRankTranslation(RankTranslation $rankTranslation): static
    {
        if ($this->rankTranslations->removeElement($rankTranslation)) {
            // set the owning side to null (unless already changed)
            if ($rankTranslation->getRank() === $this) {
                $rankTranslation->setRank(null);
            }
        }

        return $this;
    }

    public function getTranslation(string $locale): ?RankTranslation
    {
        foreach ($this->rankTranslations as $translation) {
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
            'experienceMin' => $this->getExperienceMin(),
            'experienceMax' => $this->getExperienceMax(),
            'level' => $this->getLevel(),
        ];

        if ($locale) {
            $translation = $this->getTranslation($locale);
            $data['name'] = $translation ? $translation->getName() : null;
        } else {
            $translations = [];
            foreach ($this->rankTranslations as $t) {
                $translations[$t->getLocale()] = $t->getName();
            }
            $data['translations'] = $translations;
        }

        return $data;
    }
}
