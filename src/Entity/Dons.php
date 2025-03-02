<?php

namespace App\Entity;

use App\Repository\DonsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: DonsRepository::class)]
class Dons
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: '/[\W_]/',
        message: "Le titre doit contenir au moins un caractère spécial."
    )]
    private ?string $titre = null;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: "La description doit contenir au moins {{ limit }} caractères.",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: '/[\W_]/',
        message: "La description doit contenir au moins un caractère spécial."
    )]
    private ?string $description = null;
    

#[ORM\Column(type: Types::DATE_MUTABLE)]
#[Assert\NotNull(message: "La date de création est obligatoire.")]
#[Assert\Type("\DateTimeInterface", message: "Veuillez entrer une date valide.")]
private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(inversedBy: "dons")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $donneur = null;

    #[ORM\Column(type: "boolean")]
    private bool $valide = false; // false par défaut, à valider par l’admin

    #[ORM\OneToMany(mappedBy: "dons", targetEntity: DemandeDons::class, orphanRemoval: true)]
    private Collection $demandes;


    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Image(
        maxSize: "2M",
        mimeTypes: ["image/jpeg", "image/png", "image/webp"],
        mimeTypesMessage: "Veuillez uploader une image valide (JPG, PNG, WEBP).",
        maxSizeMessage: "L'image ne doit pas dépasser 2 Mo."
    )]
    private ?string $imageUrl = null;
    

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire.")]
    #[Assert\Choice(
        choices: ['vetements', 'nourriture', 'electronique', 'meubles', 'autre'],
        message: "Choisissez une catégorie valide."
    )]
    private ?string $categorie = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime(); // Date automatique
        $this->demandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDonneur(): ?User
    {
        return $this->donneur;
    }

    public function setDonneur(?User $donneur): static
    {
        $this->donneur = $donneur;
        return $this;
    }

    public function isValide(): bool
    {
        return $this->valide;
    }

    public function setValide(bool $valide): static
    {
        $this->valide = $valide;
        return $this;
    }

    /**
     * @return Collection<int, DemandeDons>
     */
    public function getDemandes(): Collection
    {
        return $this->demandes;
    }

    public function addDemande(DemandeDons $demande): static
    {
        if (!$this->demandes->contains($demande)) {
            $this->demandes->add($demande);
            $demande->setDons($this);
        }
        return $this;
    }

    public function removeDemande(DemandeDons $demande): static
    {
        if ($this->demandes->removeElement($demande)) {
            if ($demande->getDons() === $this) {
                $demande->setDons(null);
            }
        }
        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }
}
