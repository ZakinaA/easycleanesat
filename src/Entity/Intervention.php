<?php

namespace App\Entity;

use App\Repository\InterventionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
class Intervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, ElementSecurite>
     */
    #[ORM\ManyToMany(targetEntity: ElementSecurite::class, mappedBy: 'intervention')]
    private Collection $elementSecurites;

    /**
     * @var Collection<int, VigilanceIntervention>
     */
    #[ORM\OneToMany(targetEntity: VigilanceIntervention::class, mappedBy: 'intervention')]
    private Collection $vigilanceInterventions;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    private ?Redacteur $redacteur = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ZonesClient $zonesClient = null;

    #[ORM\Column(nullable: true)]
    private ?int $numVersion = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateModificaion = null;

    #[ORM\Column]
    private ?int $nbTravailleur = null;

    #[ORM\Column]
    private ?int $dureeHeure = null;

    #[ORM\Column]
    private ?int $dureeMinute = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    private ?Contrat $contrat = null;

    /**
     * @var Collection<int, Plage>
     */
    #[ORM\OneToMany(targetEntity: Plage::class, mappedBy: 'intervention')]
    private Collection $plages;

    /**
     * @var Collection<int, Actions>
     */
    #[ORM\ManyToMany(targetEntity: Actions::class, mappedBy: 'intervention')]
    private Collection $actions;

    public function __construct()
    {
        $this->elementSecurites = new ArrayCollection();
        $this->vigilanceInterventions = new ArrayCollection();
        $this->plages = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->numVersion = 1;
        $this->dureeHeure = 0;
        $this->dureeMinute = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, ElementSecurite>
     */
    public function getElementSecurites(): Collection
    {
        return $this->elementSecurites;
    }

    public function addElementSecurite(ElementSecurite $elementSecurite): static
    {
        if (!$this->elementSecurites->contains($elementSecurite)) {
            $this->elementSecurites->add($elementSecurite);
            $elementSecurite->addIntervention($this);
        }

        return $this;
    }

    public function removeElementSecurite(ElementSecurite $elementSecurite): static
    {
        if ($this->elementSecurites->removeElement($elementSecurite)) {
            $elementSecurite->removeIntervention($this);
        }

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
            $vigilanceIntervention->setIntervention($this);
        }

        return $this;
    }

    public function removeVigilanceIntervention(VigilanceIntervention $vigilanceIntervention): static
    {
        if ($this->vigilanceInterventions->removeElement($vigilanceIntervention)) {
            // set the owning side to null (unless already changed)
            if ($vigilanceIntervention->getIntervention() === $this) {
                $vigilanceIntervention->setIntervention(null);
            }
        }

        return $this;
    }

    public function getRedacteur(): ?Redacteur
    {
        return $this->redacteur;
    }

    public function setRedacteur(?Redacteur $redacteur): static
    {
        $this->redacteur = $redacteur;

        return $this;
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

    public function getNumVersion(): ?int
    {
        return $this->numVersion;
    }

    public function setNumVersion(?int $numVersion): static
    {
        $this->numVersion = $numVersion;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateModificaion(): ?\DateTime
    {
        return $this->dateModificaion;
    }

    public function setDateModificaion(?\DateTime $dateModificaion): static
    {
        $this->dateModificaion = $dateModificaion;

        return $this;
    }

    public function getNbTravailleur(): ?int
    {
        return $this->nbTravailleur;
    }

    public function setNbTravailleur(int $nbTravailleur): static
    {
        $this->nbTravailleur = $nbTravailleur;

        return $this;
    }

    public function getDureeHeure(): ?int
    {
        return $this->dureeHeure;
    }

    public function setDureeHeure(int $dureeHeure): static
    {
        $this->dureeHeure = $dureeHeure;

        return $this;
    }

    public function getDureeMinute(): ?int
    {
        return $this->dureeMinute;
    }

    public function setDureeMinute(int $dureeMinute): static
    {
        $this->dureeMinute = $dureeMinute;

        return $this;
    }

    public function getContrat(): ?Contrat
    {
        return $this->contrat;
    }

    public function setContrat(?Contrat $contrat): static
    {
        $this->contrat = $contrat;

        return $this;
    }

    /**
     * @return Collection<int, Plage>
     */
    public function getPlages(): Collection
    {
        return $this->plages;
    }

    public function addPlage(Plage $plage): static
    {
        if (!$this->plages->contains($plage)) {
            $this->plages->add($plage);
            $plage->setIntervention($this);
        }

        return $this;
    }

    public function removePlage(Plage $plage): static
    {
        if ($this->plages->removeElement($plage)) {
            // set the owning side to null (unless already changed)
            if ($plage->getIntervention() === $this) {
                $plage->setIntervention(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Actions>
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addAction(Actions $action): static
    {
        if (!$this->actions->contains($action)) {
            $this->actions->add($action);
            $action->addIntervention($this);
        }

        return $this;
    }

    public function removeAction(Actions $action): static
    {
        if ($this->actions->removeElement($action)) {
            $action->removeIntervention($this);
        }

        return $this;
    }
}
