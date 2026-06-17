<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private ?string $nom = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $couleur = null;

    #[ORM\Column(length: 10)]
    private ?string $code = null;

    /**
     * @var Collection<int, MeoProduit>
     */
    #[ORM\OneToMany(targetEntity: MeoProduit::class, mappedBy: 'produit')]
    private Collection $meoProduits;

    #[ORM\Column]
    private ?bool $actif = null;

    public function __construct()
    {
        $this->meoProduits = new ArrayCollection();
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

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(?string $couleur): static
    {
        $this->couleur = $couleur;

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
     * @return Collection<int, MeoProduit>
     */
    public function getMeoProduits(): Collection
    {
        return $this->meoProduits;
    }

    public function addMeoProduit(MeoProduit $meoProduit): static
    {
        if (!$this->meoProduits->contains($meoProduit)) {
            $this->meoProduits->add($meoProduit);
            $meoProduit->setProduit($this);
        }

        return $this;
    }

    public function removeMeoProduit(MeoProduit $meoProduit): static
    {
        if ($this->meoProduits->removeElement($meoProduit)) {
            // set the owning side to null (unless already changed)
            if ($meoProduit->getProduit() === $this) {
                $meoProduit->setProduit(null);
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
