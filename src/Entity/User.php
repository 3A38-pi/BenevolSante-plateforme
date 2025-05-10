<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(
    fields: ['email'],
    message: 'Cet email est déjà utilisé.'
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'email ne peut pas être vide')]
    #[Assert\Email(message: 'Veuillez saisir un email valide')]
    #[Assert\Regex(
        pattern: '/^.+@/',
        message: 'L\'email doit contenir au moins un caractère avant le "@".'
    )]
    private ?string $email = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank(message: 'Le mot de passe est requis')]
    #[Assert\Length(
        min: 6,
        minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caractères'
    )]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\s-]+$/',
        message: 'Le nom ne doit contenir que des lettres, espaces et tirets.'
    )]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\s-]+$/',
        message: 'Le prénom ne doit contenir que des lettres, espaces et tirets.'
    )]
    private ?string $prenom = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $blockExpiration = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastLoginDate = null;

    #[ORM\Column(length: 20, options: ["default" => "verrouillé"])]
    private string $etatCompte;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le type d\'utilisateur est requis')]
    #[Assert\Choice(
        choices: ['donneur', 'beneficiaire', 'professionnel'],
        message: 'Le type d\'utilisateur doit être "donneur", "beneficiaire" ou "professionnel"'
    )]
    private ?string $typeUtilisateur = null;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'user')]
    private Collection $notifications;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetToken = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $GoogleId = null;

    /**
     * @var Collection<int, CommentReaction>
     */
    #[ORM\OneToMany(targetEntity: CommentReaction::class, mappedBy: 'User')]
    private Collection $commentaire;

    /**
     * @var Collection<int, CommentReaction>
     */
    #[ORM\OneToMany(targetEntity: CommentReaction::class, mappedBy: 'user')]
    private Collection $commentReactions;

    /**
     * @var Collection<int, CommentReply>
     */
    #[ORM\OneToMany(targetEntity: CommentReply::class, mappedBy: 'user')]
    private Collection $commentReplies;

    /**
     * @var Collection<int, CommentReport>
     */
    #[ORM\OneToMany(targetEntity: CommentReport::class, mappedBy: 'user')]
    private Collection $commentReports;

    /**
     * @var Collection<int, Reaction>
     */
    #[ORM\OneToMany(targetEntity: Reaction::class, mappedBy: 'user')]
    private Collection $reactions;

    /**
     * @var Collection<int, ConnectionHistory>
     */
    #[ORM\OneToMany(targetEntity: ConnectionHistory::class, mappedBy: 'user')]
    private Collection $connectionHistories;

    public function __construct()
    {
        $this->etatCompte = "verrouillé"; // Par défaut, tous les comptes sont verrouillés
        $this->notifications = new ArrayCollection();
        $this->commentaire = new ArrayCollection();
        $this->commentReactions = new ArrayCollection();
        $this->commentReplies = new ArrayCollection();
        $this->commentReports = new ArrayCollection();
        $this->reactions = new ArrayCollection();
        $this->connectionHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        return array_unique(array_merge($this->roles, ['ROLE_USER']));
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getTypeUtilisateur(): ?string
    {
        return $this->typeUtilisateur;
    }

    public function setTypeUtilisateur(string $type): self
    {
        $this->typeUtilisateur = $type;
        return $this;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEtatCompte(): string
    {
        return $this->etatCompte;
    }

    public function setEtatCompte(string $etatCompte): static
    {
        $this->etatCompte = $etatCompte;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // Symfony demande d'implémenter cette méthode pour des raisons de sécurité
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    public function getBlockExpiration(): ?\DateTimeInterface
{
    return $this->blockExpiration;
}

public function setBlockExpiration(?\DateTimeInterface $blockExpiration): static
{
    $this->blockExpiration = $blockExpiration;
    return $this;
}

public function getLastLoginDate(): ?\DateTimeInterface
{
    return $this->lastLoginDate;
}

public function setLastLoginDate(?\DateTimeInterface $lastLoginDate): static
{
    $this->lastLoginDate = $lastLoginDate;
    return $this;
}


    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->GoogleId;
    }

    public function setGoogleId(?string $GoogleId): static
    {
        $this->GoogleId = $GoogleId;

        return $this;
    }

    /**
     * @return Collection<int, CommentReaction>
     */
    public function getCommentaire(): Collection
    {
        return $this->commentaire;
    }

    public function addCommentaire(CommentReaction $commentaire): static
    {
        if (!$this->commentaire->contains($commentaire)) {
            $this->commentaire->add($commentaire);
            $commentaire->setUser($this);
        }

        return $this;
    }

    public function removeCommentaire(CommentReaction $commentaire): static
    {
        if ($this->commentaire->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getUser() === $this) {
                $commentaire->setUser(null);
            }
        }

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
            $commentReaction->setUser($this);
        }

        return $this;
    }

    public function removeCommentReaction(CommentReaction $commentReaction): static
    {
        if ($this->commentReactions->removeElement($commentReaction)) {
            // set the owning side to null (unless already changed)
            if ($commentReaction->getUser() === $this) {
                $commentReaction->setUser(null);
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
            $commentReply->setUser($this);
        }

        return $this;
    }

    public function removeCommentReply(CommentReply $commentReply): static
    {
        if ($this->commentReplies->removeElement($commentReply)) {
            // set the owning side to null (unless already changed)
            if ($commentReply->getUser() === $this) {
                $commentReply->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommentReport>
     */
    public function getCommentReports(): Collection
    {
        return $this->commentReports;
    }

    public function addCommentReport(CommentReport $commentReport): static
    {
        if (!$this->commentReports->contains($commentReport)) {
            $this->commentReports->add($commentReport);
            $commentReport->setUser($this);
        }

        return $this;
    }

    public function removeCommentReport(CommentReport $commentReport): static
    {
        if ($this->commentReports->removeElement($commentReport)) {
            // set the owning side to null (unless already changed)
            if ($commentReport->getUser() === $this) {
                $commentReport->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reaction>
     */
    public function getReactions(): Collection
    {
        return $this->reactions;
    }

    public function addReaction(Reaction $reaction): static
    {
        if (!$this->reactions->contains($reaction)) {
            $this->reactions->add($reaction);
            $reaction->setUser($this);
        }

        return $this;
    }

    public function removeReaction(Reaction $reaction): static
    {
        if ($this->reactions->removeElement($reaction)) {
            // set the owning side to null (unless already changed)
            if ($reaction->getUser() === $this) {
                $reaction->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ConnectionHistory>
     */
    public function getConnectionHistories(): Collection
    {
        return $this->connectionHistories;
    }

    public function addConnectionHistory(ConnectionHistory $connectionHistory): static
    {
        if (!$this->connectionHistories->contains($connectionHistory)) {
            $this->connectionHistories->add($connectionHistory);
            $connectionHistory->setUser($this);
        }

        return $this;
    }

    public function removeConnectionHistory(ConnectionHistory $connectionHistory): static
    {
        if ($this->connectionHistories->removeElement($connectionHistory)) {
            // set the owning side to null (unless already changed)
            if ($connectionHistory->getUser() === $this) {
                $connectionHistory->setUser(null);
            }
        }

        return $this;
    }
}
