<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $icon = null;

    /**
     * @var Collection<int, Hunt>
     */
    #[ORM\OneToMany(targetEntity: Hunt::class, mappedBy: 'category')]
    private Collection $hunts;

    /**
     * @var Collection<int, CategoryTranslation>
     */
    #[ORM\OneToMany(targetEntity: CategoryTranslation::class, mappedBy: 'category', orphanRemoval: true)]
    private Collection $categoryTranslations;

    public function __construct()
    {
        $this->hunts = new ArrayCollection();
        $this->categoryTranslations = new ArrayCollection();
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
            $hunt->setCategory($this);
        }

        return $this;
    }

    public function removeHunt(Hunt $hunt): static
    {
        if ($this->hunts->removeElement($hunt)) {
            // set the owning side to null (unless already changed)
            if ($hunt->getCategory() === $this) {
                $hunt->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CategoryTranslation>
     */
    public function getCategoryTranslations(): Collection
    {
        return $this->categoryTranslations;
    }

    public function addCategoryTranslation(CategoryTranslation $categoryTranslation): static
    {
        if (!$this->categoryTranslations->contains($categoryTranslation)) {
            $this->categoryTranslations->add($categoryTranslation);
            $categoryTranslation->setCategory($this);
        }

        return $this;
    }

    public function removeCategoryTranslation(CategoryTranslation $categoryTranslation): static
    {
        if ($this->categoryTranslations->removeElement($categoryTranslation)) {
            // set the owning side to null (unless already changed)
            if ($categoryTranslation->getCategory() === $this) {
                $categoryTranslation->setCategory(null);
            }
        }

        return $this;
    }

    public function getTranslation(string $locale): ?CategoryTranslation
    {
        foreach ($this->categoryTranslations as $translation) {
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
            foreach ($this->categoryTranslations as $t) {
                $translations[$t->getLocale()] = $t->getName();
            }
            $data['translations'] = $translations;
        }

        return $data;
    }
}
