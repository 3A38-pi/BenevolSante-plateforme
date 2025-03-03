<?php

namespace App\Entity;

use App\Repository\DemandeDonsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: DemandeDonsRepository::class)]
class DemandeDons
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "demandesDons")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $beneficiaire = null;

    #[ORM\ManyToOne(targetEntity: Dons::class, inversedBy: "demandes")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Dons $dons = null;

    #[ORM\Column(type: "string", length: 20)]
    private string $statut = "En attente"; // Possible values: "En attente", "Acceptée", "Refusée"

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDemande = null;

    #[ORM\Column(type: 'boolean')]
    private bool $chatActif = false;

    public function isChatActif(): bool
    {
        return $this->chatActif;
    }

    public function setChatActif(bool $chatActif): self
    {
        $this->chatActif = $chatActif;

        return $this;
    }

    #[ORM\OneToMany(mappedBy: "demandeDon", targetEntity: Messagerie::class, cascade: ["remove"])]
    private Collection $messages;



    public function __construct()
    {
        $this->dateDemande = new \DateTime();
        $this->messages = new ArrayCollection();
    }




    // Getter & Setter pour id
    public function getId(): ?int
    {
        return $this->id;

    }

    public function activerChat(): void
    {
        $this->chatActif = true;
    }

    // Getter & Setter pour beneficiaire
    public function getBeneficiaire(): ?User
    {
        return $this->beneficiaire;
    }

    public function setBeneficiaire(?User $beneficiaire): static
    {
        $this->beneficiaire = $beneficiaire;
        return $this;
    }

    // Getter & Setter pour dons


    public function getDons(): ?Dons
    {
        return $this->dons;
    }

    public function setDons(?Dons $dons): static
    {
        $this->dons = $dons;
        return $this;
    }

    // Getter & Setter pour statut
    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    // Getter & Setter pour dateDemande
    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTimeInterface $dateDemande): static
    {
        $this->dateDemande = $dateDemande;
        return $this;
    }

    // Gestion des messages liés à cette demande
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Messagerie $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setDemandeDon($this);
        }
        return $this;
    }
    public function removeMessage(Messagerie $message): self
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getDemandeDon() !== null && $message->getDemandeDon() === $this) {
                $message->setDemandeDon(null);
            }
        }
        return $this;
    }
}
