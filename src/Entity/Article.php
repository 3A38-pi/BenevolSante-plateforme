<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    #[Assert\Length(min: 3, max: 255, minMessage: "Le titre doit contenir au moins {{ limit }} caractères.")]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'image est obligatoire.")]
    #[Assert\File(
        maxSize: "2M",
        mimeTypes: ["image/jpeg", "image/png"],
        mimeTypesMessage: "Veuillez uploader une image valide (JPG ou PNG)."
    )]
    private ?string $image = null;

    // #[ORM\Column(length: 255)]
    // #[Assert\NotBlank(message: "Les tags ne peuvent pas être vides.")]
    // private ?string $tags = null;


        // Nouveau champ categorie
        #[ORM\Column(length: 255)]
        #[Assert\NotBlank(message: "La catégorie ne peut pas être vide.")]
        private ?string $categorie = null;

    #[ORM\Column]
    private ?int $nombreCommentaire = 0;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(min: 10, minMessage: "La description doit contenir au moins {{ limit }} caractères.")]
    private ?string $description = null;

    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'article', cascade: ['persist', 'remove'])]
    private Collection $commentaires;
    
    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;
        return $this;
    }

    // public function getTags(): ?string
    // {
    //     return $this->tags;
    // }

    // public function setTags(string $tags): static
    // {
    //     $this->tags = $tags;
    //     return $this;
    // }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getNombreCommentaire(): ?int
    {
        return $this->nombreCommentaire;
    }

    public function setNombreCommentaire(int $nombreCommentaire): static
    {
        $this->nombreCommentaire = $nombreCommentaire;
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

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setArticle($this);
        }
        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            if ($commentaire->getArticle() === $this) {
                $commentaire->setArticle(null);
            }
        }
        return $this;
    }
}
