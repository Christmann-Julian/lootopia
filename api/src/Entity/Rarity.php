<?php

namespace App\Entity;

use App\Repository\RarityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RarityRepository::class)]
class Rarity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $minExperience = null;

    /**
     * @var Collection<int, Hunt>
     */
    #[ORM\OneToMany(targetEntity: Hunt::class, mappedBy: 'rarity')]
    private Collection $hunts;

    /**
     * @var Collection<int, RarityTranslation>
     */
    #[ORM\OneToMany(targetEntity: RarityTranslation::class, mappedBy: 'rarity', orphanRemoval: true)]
    private Collection $rarityTranslations;

    #[ORM\Column]
    private ?int $experienceGain = null;

    public function __construct()
    {
        $this->hunts = new ArrayCollection();
        $this->rarityTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMinExperience(): ?int
    {
        return $this->minExperience;
    }

    public function setMinExperience(int $minExperience): static
    {
        $this->minExperience = $minExperience;

        return $this;
    }

    public function getExperienceGain(): ?int
    {
        return $this->experienceGain;
    }

    public function setExperienceGain(int $experienceGain): static
    {
        $this->experienceGain = $experienceGain;

        return $this;
    }

    /**
     * @return Collection<int, Hunt>
     */
    public function getHunts(): Collection
    {
        return $this->hunts;
    }

    public function addHunt(Hunt $hunt): static
    {
        if (!$this->hunts->contains($hunt)) {
            $this->hunts->add($hunt);
            $hunt->setRarity($this);
        }

        return $this;
    }

    public function removeHunt(Hunt $hunt): static
    {
        if ($this->hunts->removeElement($hunt)) {
            // set the owning side to null (unless already changed)
            if ($hunt->getRarity() === $this) {
                $hunt->setRarity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RarityTranslation>
     */
    public function getRarityTranslations(): Collection
    {
        return $this->rarityTranslations;
    }

    public function addRarityTranslation(RarityTranslation $rarityTranslation): static
    {
        if (!$this->rarityTranslations->contains($rarityTranslation)) {
            $this->rarityTranslations->add($rarityTranslation);
            $rarityTranslation->setRarity($this);
        }

        return $this;
    }

    public function removeRarityTranslation(RarityTranslation $rarityTranslation): static
    {
        if ($this->rarityTranslations->removeElement($rarityTranslation)) {
            // set the owning side to null (unless already changed)
            if ($rarityTranslation->getRarity() === $this) {
                $rarityTranslation->setRarity(null);
            }
        }

        return $this;
    }

    public function getTranslation(string $locale): ?RarityTranslation
    {
        foreach ($this->rarityTranslations as $translation) {
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
            'minExperience' => $this->getMinExperience(),
            'experienceGain' => $this->getExperienceGain(),
        ];

        if ($locale) {
            $translation = $this->getTranslation($locale);
            $data['name'] = $translation ? $translation->getName() : null;
        } else {
            $translations = [];
            foreach ($this->rarityTranslations as $t) {
                $translations[$t->getLocale()] = $t->getName();
            }
            $data['translations'] = $translations;
        }

        return $data;
    }
}
