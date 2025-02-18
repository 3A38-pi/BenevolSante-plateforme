<?php

namespace App\Entity;

use App\Repository\OffreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: OffreRepository::class)]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    



    #[ORM\Column(length: 255)]
#[Assert\NotBlank(message: "Le titre est obligatoire.")]
#[Assert\Length(
    min: 3,
    minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
    max: 255,
    maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
)]
#[Assert\Regex(
    pattern: "/^[a-zA-Z0-9\s]+$/",
    message: "Le titre ne peut contenir que des lettres, des chiffres et des espaces."
)]
private ?string $titre_offre = null;

#[ORM\Column(length: 255)]
#[Assert\NotBlank(message: "La description est obligatoire.")]
#[Assert\Length(
    min: 3,
    minMessage: "La description doit contenir au moins {{ limit }} caractères.",
    max: 255,
    maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
)]
#[Assert\Regex(
    pattern: "/^[a-zA-Z0-9\s]+$/",
    message: "La description ne peut contenir que des lettres, des chiffres et des espaces."
)]
private ?string $description_offre = null;



    /**
     * @var Collection<int, Condidat>
     */
    #[ORM\OneToMany(targetEntity: Condidat::class, mappedBy: 'offre')]
    private Collection $condidats;

    public function __construct()
    {
        $this->condidats = new ArrayCollection();
    }

  


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getTitreOffre(): ?string
    {
        return $this->titre_offre;
    }

    public function setTitreOffre(string $titre_offre): static
    {
        $this->titre_offre = $titre_offre;

        return $this;
    }

    public function getDescriptionOffre(): ?string
    {
        return $this->description_offre;
    }

    public function setDescriptionOffre(string $description_offre): static
    {
        $this->description_offre = $description_offre;

        return $this;
    }

    /**
     * @return Collection<int, condidat>
     */
    public function getOffre(): Collection
    {
        return $this->offre;
    }

    public function addOffre(Condidat $offre): static
    {
        if (!$this->offre->contains($offre)) {
            $this->offre->add($offre);
            $offre->setOffre($this);
        }

        return $this;
    }

    public function removeOffre(Condidat $offre): static
    {
        if ($this->offre->removeElement($offre)) {
            // set the owning side to null (unless already changed)
            if ($offre->getOffre() === $this) {
                $offre->setOffre(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, condidat>
     */
    public function getCondidats(): Collection
    {
        return $this->condidats;
    }

    public function addCondidat(condidat $condidat): static
    {
        if (!$this->condidats->contains($condidat)) {
            $this->condidats->add($condidat);
            $condidat->setOffreId($this);
        }

        return $this;
    }

    public function removeCondidat(condidat $condidat): static
    {
        if ($this->condidats->removeElement($condidat)) {
            // set the owning side to null (unless already changed)
            if ($condidat->getOffreId() === $this) {
                $condidat->setOffreId(null);
            }
        }

        return $this;
    }
}
