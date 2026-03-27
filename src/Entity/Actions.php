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

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private ?bool $actif = true;

    /**
     * @var Collection<int, SuppInter>
     */
    #[ORM\ManyToMany(targetEntity: SuppInter::class, mappedBy: 'actions')]
    private Collection $suppInters;

    public function __construct()
    {
        $this->necessaire = new ArrayCollection();
        $this->suppInters = new ArrayCollection();
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

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

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
            $suppInter->addAction($this);
        }

        return $this;
    }

    public function removeSuppInter(SuppInter $suppInter): static
    {
        if ($this->suppInters->removeElement($suppInter)) {
            $suppInter->removeAction($this);
        }

        return $this;
    }
}
