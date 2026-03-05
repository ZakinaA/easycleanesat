<?php

namespace App\Entity;

use App\Repository\NecessaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NecessaireRepository::class)]
class Necessaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $code = null;

    /**
     * @var Collection<int, Actions>
     */
    #[ORM\ManyToMany(targetEntity: Actions::class, mappedBy: 'necessaire')]
    private Collection $actions;

    #[ORM\ManyToOne(inversedBy: 'necessaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeNecessaire $type_necessaire = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private ?bool $actif = true;

    public function __construct()
    {
        $this->actions = new ArrayCollection();
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

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
            $action->addNecessaire($this);
        }

        return $this;
    }

    public function removeAction(Actions $action): static
    {
        if ($this->actions->removeElement($action)) {
            $action->removeNecessaire($this);
        }

        return $this;
    }

    public function getTypeNecessaire(): ?TypeNecessaire
    {
        return $this->type_necessaire;
    }

    public function setTypeNecessaire(?TypeNecessaire $type_necessaire): static
    {
        $this->type_necessaire = $type_necessaire;

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
