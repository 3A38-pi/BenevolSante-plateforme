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

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'email ne peut pas être vide')]
    #[Assert\Email(message: 'Veuillez saisir un email valide')]
    #[Assert\Regex(
        pattern: '/^.+@/',
        message: 'L\'email doit contenir au moins un caractère avant le "@".'
    )]
    private ?string $email = null;

    #[ORM\Column]
#[Assert\NotBlank(message: 'Le mot de passe est requis')]
#[Assert\Length(
    min: 6,
    minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caractères'
)]
private ?string $password = null;


    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\s-]+$/',
        message: 'Le nom ne doit contenir que des lettres, espaces et tirets.'
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\s-]+$/',
        message: 'Le prénom ne doit contenir que des lettres, espaces et tirets.'
    )]
    private ?string $prenom = null;

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

    public function __construct()
    {
        $this->etatCompte = "verrouillé"; // Par défaut, tous les comptes sont verrouillés
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
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

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
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
}
