<?php

namespace App\Entity;

use App\Repository\SuppInterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuppInterRepository::class)]
#[ORM\Table(
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: 'index_support_inter',
            columns: ['support_id', 'inter_id']
        )
    ]
)]
class SuppInter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $ordre = null;

    #[ORM\ManyToOne(inversedBy: 'suppInters')]
    #[ORM\JoinColumn(name: 'support_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?SupportClient $supportClient = null;

    #[ORM\ManyToOne(inversedBy: 'suppInters')]
    #[ORM\JoinColumn(name: 'inter_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Intervention $intervention = null;

    /**
     * @var Collection<int, Actions>
     */
    #[ORM\ManyToMany(targetEntity: Actions::class, inversedBy: 'suppInters')]
    private Collection $actions;

    public function __construct()
    {
        $this->actions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(?int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getSupportClient(): ?SupportClient
    {
        return $this->supportClient;
    }

    public function setSupportClient(?SupportClient $supportClient): static
    {
        $this->supportClient = $supportClient;

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
        }

        return $this;
    }

    public function removeAction(Actions $action): static
    {
        $this->actions->removeElement($action);

        return $this;
    }
}
