<?php

namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $question;

    #[ORM\Column(type: 'json')]
    private array $options = [];

    #[ORM\Column(type: 'boolean')]
    private bool $openEnded = false;
    #[ORM\OneToMany(mappedBy: 'evaluation', targetEntity: EvaluationResponse::class, cascade: ['persist', 'remove'])]
    private Collection $responses;


    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;


    public function __construct()
    {
        $this->responses = new ArrayCollection();
           // Initialiser createdAt à la date actuelle si non défini
           $this->createdAt = new \DateTime();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;
        return $this;
    }
    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function isOpenEnded(): bool
    {
        return $this->openEnded;
    }

    public function setOpenEnded(bool $openEnded): self
    {
        $this->openEnded = $openEnded;
        return $this;
    }
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function addResponse(EvaluationResponse $response): self
    {
        if (!$this->responses->contains($response)) {
            $this->responses[] = $response;
            $response->setEvaluation($this);
        }
        return $this;
    }

    public function removeResponse(EvaluationResponse $response): self
    {
        if ($this->responses->removeElement($response)) {
            if ($response->getEvaluation() === $this) {
                $response->setEvaluation(null);
            }
        }
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
