<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    /**
     * @var Collection<int, SitesClient>
     */
    #[ORM\OneToMany(targetEntity: SitesClient::class, mappedBy: 'client')]
    private Collection $sitesClients;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $picto = null;

    public function __construct()
    {
        $this->sitesClients = new ArrayCollection();
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

    /**
     * @return Collection<int, SitesClient>
     */
    public function getSitesClients(): Collection
    {
        return $this->sitesClients;
    }

    public function addSitesClient(SitesClient $sitesClient): static
    {
        if (!$this->sitesClients->contains($sitesClient)) {
            $this->sitesClients->add($sitesClient);
            $sitesClient->setClient($this);
        }

        return $this;
    }

    public function removeSitesClient(SitesClient $sitesClient): static
    {
        if ($this->sitesClients->removeElement($sitesClient)) {
            // set the owning side to null (unless already changed)
            if ($sitesClient->getClient() === $this) {
                $sitesClient->setClient(null);
            }
        }

        return $this;
    }

    public function getPicto(): ?string
    {
        return $this->picto;
    }

    public function setPicto(?string $picto): static
    {
        $this->picto = $picto;

        return $this;
    }
}
