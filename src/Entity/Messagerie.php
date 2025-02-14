<?php

namespace App\Entity;

use App\Repository\MessagerieRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessagerieRepository::class)]
class Messagerie
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $expediteur;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $destinataire;

    #[ORM\ManyToOne(targetEntity: DemandeDons::class, inversedBy: "messages")]
    #[ORM\JoinColumn(nullable: false)]
    private DemandeDons $demandeDon;

    #[ORM\Column(type: "text")]
    private string $contenu;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $dateEnvoi;

    public function __construct()
    {
        $this->dateEnvoi = new \DateTime();
    }

    // Getter & Setter pour id
    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter & Setter pour expediteur
    public function getExpediteur(): ?User
    {
        return $this->expediteur;
    }

    public function setExpediteur(User $expediteur): self
    {
        $this->expediteur = $expediteur;
        return $this;
    }

    // Getter & Setter pour destinataire
    public function getDestinataire(): ?User
    {
        return $this->destinataire;
    }

    public function setDestinataire(User $destinataire): self
    {
        $this->destinataire = $destinataire;
        return $this;
    }

    // Getter & Setter pour demandeDon
    public function getDemandeDon(): ?DemandeDons
    {
        return $this->demandeDon;
    }

    public function setDemandeDon(?DemandeDons $demandeDon): self
    {
        $this->demandeDon = $demandeDon;
        return $this;
    }
    

    // Getter & Setter pour contenu
    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    // Getter & Setter pour dateEnvoi
    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->dateEnvoi;
    }

    public function setDateEnvoi(\DateTimeInterface $dateEnvoi): self
    {
        $this->dateEnvoi = $dateEnvoi;
        return $this;
    }
}

