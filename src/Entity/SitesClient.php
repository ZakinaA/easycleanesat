<?php

namespace App\Entity;

use App\Repository\SitesClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SitesClientRepository::class)]
class SitesClient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'sitesClients')]
    private ?Client $client = null;

    /**
     * @var Collection<int, Contrat>
     */
    #[ORM\OneToMany(targetEntity: Contrat::class, mappedBy: 'sitesClient', orphanRemoval: true)]
    private Collection $contrats;

    /**
     * @var Collection<int, ZonesClient>
     */
    #[ORM\OneToMany(targetEntity: ZonesClient::class, mappedBy: 'sitesClient')]
    private Collection $zonesClients;

    #[ORM\Column]
    private ?bool $actif = null;

    public function __construct()
    {
        $this->contrats = new ArrayCollection();
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

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection<int, Contrat>
     */
    public function getContrats(): Collection
    {
        return $this->contrats;
    }

    public function addContrat(Contrat $contrat): static
    {
        if (!$this->contrats->contains($contrat)) {
            $this->contrats->add($contrat);
            $contrat->setSitesClient($this);
        }

        return $this;
    }

    public function removeContrat(Contrat $contrat): static
    {
        if ($this->contrats->removeElement($contrat)) {
            // set the owning side to null (unless already changed)
            if ($contrat->getSitesClient() === $this) {
                $contrat->setSitesClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ZonesClient>
     */
    public function getZonesClients(): Collection
    {
        return $this->zonesClients;
    }

    public function addZonesClient(ZonesClient $zonesClient): static
    {
        if (!$this->zonesClients->contains($zonesClient)) {
            $this->zonesClients->add($zonesClient);
            $zonesClient->setSitesClient($this);
        }

        return $this;
    }

    public function removeZonesClient(ZonesClient $zonesClient): static
    {
        if ($this->zonesClients->removeElement($zonesClient)) {
            // set the owning side to null (unless already changed)
            if ($zonesClient->getSitesClient() === $this) {
                $zonesClient->setSitesClient(null);
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
