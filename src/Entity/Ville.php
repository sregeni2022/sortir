<?php

namespace App\Entity;

use App\Repository\VilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: VilleRepository::class)]
class Ville
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\Length(max: 30,maxMessage:'Le nom de la ville ne doit pas dépasser 30 caractères')]
    #[Assert\NotBlank(message:'Merci d\'ajouter un nom de la ville')]
    private ?string $nom = null;

    #[ORM\Column(length: 10)]
    #[Assert\Length(max: 10,maxMessage:'Le code postal ne doit pas dépasser 10 caractères')]
    #[Assert\NotBlank(message:'Merci d\'ajouter un code postal à la ville')]
    private ?string $codePostal = null;

    #[ORM\OneToMany(mappedBy: 'villes', targetEntity: Lieu::class)]
    private Collection $lieux;

    public function __construct()
    {
        $this->lieux = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * @return Collection<int, Lieu>
     */
    public function getLieux(): Collection
    {
        return $this->lieux;
    }

    public function addLieux(Lieu $lieux): self
    {
        if (!$this->lieux->contains($lieux)) {
            $this->lieux->add($lieux);
            $lieux->setVilles($this);
        }

        return $this;
    }

    public function removeLieux(Lieu $lieux): self
    {
        if ($this->lieux->removeElement($lieux)) {
            // set the owning side to null (unless already changed)
            if ($lieux->getVilles() === $this) {
                $lieux->setVilles(null);
            }
        }

        return $this;
    }
}
