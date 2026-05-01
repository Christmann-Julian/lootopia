<?php

namespace App\Entity;

use App\Repository\HuntRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HuntRepository::class)]
class Hunt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $lat = null;

    #[ORM\Column]
    private ?float $lon = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isSponsor = false;

    /**
     * @var Collection<int, HuntTranslation>
     */
    #[ORM\OneToMany(targetEntity: HuntTranslation::class, mappedBy: 'hunt', orphanRemoval: true, cascade: ['persist'])]
    private Collection $huntTranslations;

    #[ORM\ManyToOne(inversedBy: 'hunts')]
    private ?Company $company = null;

    #[ORM\OneToOne(mappedBy: 'hunt', cascade: ['persist', 'remove'])]
    private ?Reward $reward = null;

    #[ORM\ManyToOne(inversedBy: 'hunts')]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'hunts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rarity $rarity = null;

    public function __construct()
    {
        $this->huntTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(float $lat): static
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLon(): ?float
    {
        return $this->lon;
    }

    public function setLon(float $lon): static
    {
        $this->lon = $lon;

        return $this;
    }

    public function isSponsor(): bool
    {
        return $this->isSponsor;
    }

    public function setIsSponsor(bool $isSponsor): static
    {
        $this->isSponsor = $isSponsor;

        return $this;
    }

    /**
     * @return Collection<int, HuntTranslation>
     */
    public function getHuntTranslations(): Collection
    {
        return $this->huntTranslations;
    }

    public function addHuntTranslation(HuntTranslation $huntTranslation): static
    {
        if (!$this->huntTranslations->contains($huntTranslation)) {
            $this->huntTranslations->add($huntTranslation);
            $huntTranslation->setHunt($this);
        }

        return $this;
    }

    public function removeHuntTranslation(HuntTranslation $huntTranslation): static
    {
        if ($this->huntTranslations->removeElement($huntTranslation)) {
            // set the owning side to null (unless already changed)
            if ($huntTranslation->getHunt() === $this) {
                $huntTranslation->setHunt(null);
            }
        }

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getReward(): ?Reward
    {
        return $this->reward;
    }

    public function setReward(Reward $reward): static
    {
        // set the owning side of the relation if necessary
        if ($reward->getHunt() !== $this) {
            $reward->setHunt($this);
        }

        $this->reward = $reward;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getRarity(): ?Rarity
    {
        return $this->rarity;
    }

    public function setRarity(?Rarity $rarity): static
    {
        $this->rarity = $rarity;

        return $this;
    }

    public function getTranslation(string $locale): ?HuntTranslation
    {
        foreach ($this->huntTranslations as $translation) {
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
            'lat' => $this->getLat(),
            'lon' => $this->getLon(),
            'isSponsor' => $this->isSponsor(),
            'company' => $this->getCompany()?->getName(),
            'category' => $this->getCategory()?->toArray($locale),
            'rarity' => $this->getRarity()?->toArray($locale),
            'reward' => $this->getReward()?->toArray($locale),
        ];

        if ($locale) {
            $translation = $this->getTranslation($locale);
            if ($translation) {
                $data['title'] = $translation->getTitle();
                $data['description'] = $translation->getDescription();
                $data['question'] = $translation->getQuestion();
                $data['answer'] = $translation->getAnswer();
                $data['location'] = $translation->getLocation();
            }
        } else {
            $translations = [];
            foreach ($this->huntTranslations as $t) {
                $translations[$t->getLocale()] = [
                    'title' => $t->getTitle(),
                    'description' => $t->getDescription(),
                    'question' => $t->getQuestion(),
                    'answer' => $t->getAnswer(),
                    'location' => $t->getLocation(),
                ];
            }
            $data['translations'] = $translations;
        }

        return $data;
    }
}
