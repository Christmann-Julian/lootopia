<?php

namespace App\Entity;

use App\Repository\BadgeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BadgeRepository::class)]
class Badge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $icon = null;

    /**
     * @var Collection<int, BadgeTranslation>
     */
    #[ORM\OneToMany(targetEntity: BadgeTranslation::class, mappedBy: 'badge', orphanRemoval: true)]
    private Collection $badgeTranslations;

    public function __construct()
    {
        $this->badgeTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return Collection<int, BadgeTranslation>
     */
    public function getBadgeTranslations(): Collection
    {
        return $this->badgeTranslations;
    }

    public function addBadgeTranslation(BadgeTranslation $badgeTranslation): static
    {
        if (!$this->badgeTranslations->contains($badgeTranslation)) {
            $this->badgeTranslations->add($badgeTranslation);
            $badgeTranslation->setBadge($this);
        }

        return $this;
    }

    public function removeBadgeTranslation(BadgeTranslation $badgeTranslation): static
    {
        if ($this->badgeTranslations->removeElement($badgeTranslation)) {
            // set the owning side to null (unless already changed)
            if ($badgeTranslation->getBadge() === $this) {
                $badgeTranslation->setBadge(null);
            }
        }

        return $this;
    }

    public function getTranslation(string $locale): ?BadgeTranslation
    {
        foreach ($this->badgeTranslations as $translation) {
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
            'icon' => $this->getIcon(),
        ];

        if ($locale) {
            $translation = $this->getTranslation($locale);
            $data['name'] = $translation ? $translation->getName() : null;
        } else {
            $translations = [];
            foreach ($this->badgeTranslations as $t) {
                $translations[$t->getLocale()] = $t->getName();
            }
            $data['translations'] = $translations;
        }

        return $data;
    }
}
