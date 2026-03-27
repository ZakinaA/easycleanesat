<?php

namespace App\Entity;

use App\Repository\TypeSupportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeSupportRepository::class)]
class TypeSupport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(targetEntity: SupportClient::class, mappedBy: 'typeSupport', orphanRemoval: true)]
    private Collection $supportClients;

    public function __construct()
    {
        $this->supportClients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getSupportClients(): Collection
    {
        return $this->supportClients;
    }

    public function addSupportClient(SupportClient $supportClient): static
    {
        if (!$this->supportClients->contains($supportClient)) {
            $this->supportClients->add($supportClient);
            $supportClient->setTypeSupport($this);
        }
        return $this;
    }

    public function removeSupportClient(SupportClient $supportClient): static
    {
        if ($this->supportClients->removeElement($supportClient)) {
            if ($supportClient->getTypeSupport() === $this) {
                $supportClient->setTypeSupport(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}
