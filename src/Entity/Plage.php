<?php

namespace App\Entity;

use App\Repository\PlageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlageRepository::class)]
class Plage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $heureDebut = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $heureFin = null;

    #[ORM\ManyToOne(inversedBy: 'plages')]
    private ?Intervention $intervention = null;

    #[ORM\ManyToOne(inversedBy: 'plages')]
    private ?JourDeLaSemaine $jourDeLaSemaine = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeureDebut(): ?\DateTime
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(\DateTime $heureDebut): static
    {
        $this->heureDebut = $heureDebut;

        return $this;
    }

    public function getHeureFin(): ?\DateTime
    {
        return $this->heureFin;
    }

    public function setHeureFin(\DateTime $heureFin): static
    {
        $this->heureFin = $heureFin;

        return $this;
    }

    public function getIntervention(): ?Intervention
    {
        return $this->intervention;
    }

    public function setIntervention(?Intervention $intervention): static
    {
        $this->intervention = $intervention;

        return $this;
    }

    public function getJourDeLaSemaine(): ?JourDeLaSemaine
    {
        return $this->jourDeLaSemaine;
    }

    public function setJourDeLaSemaine(?JourDeLaSemaine $jourDeLaSemaine): static
    {
        $this->jourDeLaSemaine = $jourDeLaSemaine;

        return $this;
    }
}
