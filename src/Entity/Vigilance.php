<?php

namespace App\Entity;

use App\Repository\VigilanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VigilanceRepository::class)]
class Vigilance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $definition = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $picto = null;

    /**
     * @var Collection<int, VigilanceIntervention>
     */
    #[ORM\OneToMany(targetEntity: VigilanceIntervention::class, mappedBy: 'vigilance')]
    private Collection $vigilanceInterventions;

    #[ORM\Column]
    private ?bool $actif = true;

    public function __construct()
    {
        $this->vigilanceInterventions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDefinition(): ?string
    {
        return $this->definition;
    }

    public function setDefinition(?string $definition): static
    {
        $this->definition = $definition;

        return $this;
    }

    public function getPicto(): ?string
    {
        return $this->picto;
    }

    public function setPicto(string $picto): static
    {
        $this->picto = $picto;

        return $this;
    }

    /**
     * @return Collection<int, VigilanceIntervention>
     */
    public function getVigilanceInterventions(): Collection
    {
        return $this->vigilanceInterventions;
    }

    public function addVigilanceIntervention(VigilanceIntervention $vigilanceIntervention): static
    {
        if (!$this->vigilanceInterventions->contains($vigilanceIntervention)) {
            $this->vigilanceInterventions->add($vigilanceIntervention);
            $vigilanceIntervention->setVigilance($this);
        }

        return $this;
    }

    public function removeVigilanceIntervention(VigilanceIntervention $vigilanceIntervention): static
    {
        if ($this->vigilanceInterventions->removeElement($vigilanceIntervention)) {
            // set the owning side to null (unless already changed)
            if ($vigilanceIntervention->getVigilance() === $this) {
                $vigilanceIntervention->setVigilance(null);
            }
        }

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }
}
