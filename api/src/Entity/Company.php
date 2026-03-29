<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToOne(inversedBy: 'c', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, Hunt>
     */
    #[ORM\OneToMany(targetEntity: Hunt::class, mappedBy: 'company')]
    private Collection $hunts;

    public function __construct()
    {
        $this->hunts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

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
            $hunt->setCompany($this);
        }

        return $this;
    }

    public function removeHunt(Hunt $hunt): static
    {
        if ($this->hunts->removeElement($hunt)) {
            // set the owning side to null (unless already changed)
            if ($hunt->getCompany() === $this) {
                $hunt->setCompany(null);
            }
        }

        return $this;
    }
}
