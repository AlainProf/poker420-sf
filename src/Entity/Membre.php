<?php

namespace App\Entity;

use App\Repository\MembreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MembreRepository::class)]
class Membre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $courriel = null;

    #[ORM\Column(length: 20)]
    private ?string $motDePasse = null;

    /**
     * @var Collection<int, JoueurPartie>
     */
    #[ORM\OneToMany(targetEntity: JoueurPartie::class, mappedBy: 'membre')]
    private Collection $parties;

    public function __construct()
    {
        $this->parties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCourriel(): ?string
    {
        return $this->courriel;
    }

    public function setCourriel(?string $courriel): static
    {
        $this->courriel = $courriel;

        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(?string $motDePasse): static
    {
        $this->motDePasse = $motDePasse;

        return $this;
    }

    /**
     * @return Collection<int, JoueurPartie>
     */
    public function getParties(): Collection
    {
        return $this->parties;
    }

    public function addParty(JoueurPartie $party): static
    {
        if (!$this->parties->contains($party)) {
            $this->parties->add($party);
            $party->setMembre($this);
        }

        return $this;
    }

    public function removeParty(JoueurPartie $party): static
    {
        if ($this->parties->removeElement($party)) {
            // set the owning side to null (unless already changed)
            if ($party->getMembre() === $this) {
                $party->setMembre(null);
            }
        }

        return $this;
    }
}
