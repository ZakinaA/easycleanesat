<?php

namespace App\Entity;

use App\Repository\ActionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionsRepository::class)]
class Actions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'actions')]
    private ?MeoProduit $meo_produit = null;

    /**
     * @var Collection<int, Necessaire>
     */
    #[ORM\ManyToMany(targetEntity: Necessaire::class, inversedBy: 'actions')]
    private Collection $necessaire;

    /**
     * @var Collection<int, Intervention>
     */
    #[ORM\ManyToMany(targetEntity: Intervention::class, inversedBy: 'actions')]
    private Collection $intervention;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private ?bool $actif = true;

    public function __construct()
    {
        $this->necessaire = new ArrayCollection();
        $this->intervention = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMeoProduit(): ?MeoProduit
    {
        return $this->meo_produit;
    }

    public function setMeoProduit(?MeoProduit $meo_produit): static
    {
        $this->meo_produit = $meo_produit;

        return $this;
    }

    /**
     * @return Collection<int, Necessaire>
     */
    public function getNecessaire(): Collection
    {
        return $this->necessaire;
    }

    public function addNecessaire(Necessaire $necessaire): static
    {
        if (!$this->necessaire->contains($necessaire)) {
            $this->necessaire->add($necessaire);
        }

        return $this;
    }

    public function removeNecessaire(Necessaire $necessaire): static
    {
        $this->necessaire->removeElement($necessaire);

        return $this;
    }

    /**
     * @return Collection<int, Intervention>
     */
    public function getIntervention(): Collection
    {
        return $this->intervention;
    }

    public function addIntervention(Intervention $intervention): static
    {
        if (!$this->intervention->contains($intervention)) {
            $this->intervention->add($intervention);
        }

        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        $this->intervention->removeElement($intervention);

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
