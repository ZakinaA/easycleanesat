<?php

namespace App\Entity;

use App\Repository\TypeZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeZoneRepository::class)]
class TypeZone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(targetEntity: ZonesClient::class, mappedBy: 'typeZone')]
    private Collection $zonesClients;

    public function __construct()
    {
        $this->zonesClients = new ArrayCollection();
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

    public function getZonesClients(): Collection
    {
        return $this->zonesClients;
    }

    public function addZonesClient(ZonesClient $zonesClient): static
    {
        if (!$this->zonesClients->contains($zonesClient)) {
            $this->zonesClients->add($zonesClient);
            $zonesClient->setTypeZone($this);
        }
        return $this;
    }

    public function removeZonesClient(ZonesClient $zonesClient): static
    {
        if ($this->zonesClients->removeElement($zonesClient)) {
            if ($zonesClient->getTypeZone() === $this) {
                $zonesClient->setTypeZone(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}
