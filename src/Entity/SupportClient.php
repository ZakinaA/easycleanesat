<?php

namespace App\Entity;

use App\Repository\SupportClientRepository;
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
}
