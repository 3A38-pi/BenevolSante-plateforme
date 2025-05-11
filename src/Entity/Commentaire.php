<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le commentaire ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        minMessage: "Le commentaire doit faire au moins {{ limit }} caractères."
    )]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'commentaires')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    /**
     * @var Collection<int, CommentReaction>
     */
    #[ORM\OneToMany(targetEntity: CommentReaction::class, mappedBy: 'commentaire')]
    private Collection $commentReactions;

    /**
     * @var Collection<int, CommentReply>
     */
    #[ORM\OneToMany(targetEntity: CommentReply::class, mappedBy: 'commentaire')]
    private Collection $commentReplies;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->etat = 'valide';
        $this->commentReactions = new ArrayCollection();
        $this->commentReplies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        // On peut forcer le retour en DateTimeImmutable si vous le souhaitez
        return $this->createdAt instanceof \DateTimeImmutable ? $this->createdAt : \DateTimeImmutable::createFromMutable($this->createdAt);
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;
        return $this;
    }

    /**
     * @return Collection<int, CommentReaction>
     */
    public function getCommentReactions(): Collection
    {
        return $this->commentReactions;
    }

    public function addCommentReaction(CommentReaction $commentReaction): static
    {
        if (!$this->commentReactions->contains($commentReaction)) {
            $this->commentReactions->add($commentReaction);
            $commentReaction->setCommentaire($this);
        }

        return $this;
    }

    public function removeCommentReaction(CommentReaction $commentReaction): static
    {
        if ($this->commentReactions->removeElement($commentReaction)) {
            // set the owning side to null (unless already changed)
            if ($commentReaction->getCommentaire() === $this) {
                $commentReaction->setCommentaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommentReply>
     */
    public function getCommentReplies(): Collection
    {
        return $this->commentReplies;
    }

    public function addCommentReply(CommentReply $commentReply): static
    {
        if (!$this->commentReplies->contains($commentReply)) {
            $this->commentReplies->add($commentReply);
            $commentReply->setCommentaire($this);
        }

        return $this;
    }

    public function removeCommentReply(CommentReply $commentReply): static
    {
        if ($this->commentReplies->removeElement($commentReply)) {
            // set the owning side to null (unless already changed)
            if ($commentReply->getCommentaire() === $this) {
                $commentReply->setCommentaire(null);
            }
        }

        return $this;
    }
}
