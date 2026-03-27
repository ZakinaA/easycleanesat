<?php

namespace App\Entity;

use App\Repository\SupportClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SupportClientRepository::class)]
class SupportClient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'supportsClient')]
    private ?ZonesClient $zonesClient = null;

    #[ORM\ManyToOne(inversedBy: 'supportClients')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeSupport $typeSupport = null;

    /**
     * @var Collection<int, SuppInter>
     */
    #[ORM\OneToMany(targetEntity: SuppInter::class, mappedBy: 'supportClient')]
    private Collection $suppInters;

    public function __construct()
    {
        $this->suppInters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getZonesClient(): ?ZonesClient
    {
        return $this->zonesClient;
    }

    public function setZonesClient(?ZonesClient $zonesClient): static
    {
        $this->zonesClient = $zonesClient;

        return $this;
    }

    public function getTypeSupport(): ?TypeSupport
    {
        return $this->typeSupport;
    }

    public function setTypeSupport(?TypeSupport $typeSupport): static
    {
        $this->typeSupport = $typeSupport;

        return $this;
    }

    /**
     * @return Collection<int, SuppInter>
     */
    public function getSuppInters(): Collection
    {
        return $this->suppInters;
    }

    public function addSuppInter(SuppInter $suppInter): static
    {
        if (!$this->suppInters->contains($suppInter)) {
            $this->suppInters->add($suppInter);
            $suppInter->setSupportClient($this);
        }

        return $this;
    }

    public function removeSuppInter(SuppInter $suppInter): static
    {
        if ($this->suppInters->removeElement($suppInter)) {
            // set the owning side to null (unless already changed)
            if ($suppInter->getSupportClient() === $this) {
                $suppInter->setSupportClient(null);
            }
        }

        return $this;
    }
}
