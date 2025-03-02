<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks] 
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Regex(
        pattern: "/^[A-Z][^*]*$/",
        message: "Le nom doit commencer par une majuscule et ne pas contenir de '*'."
    )]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'boolean')]
    private bool $openEnded = false;

    #[ORM\OneToMany(mappedBy: 'evaluation', targetEntity: EvaluationResponse::class, cascade: ['persist', 'remove'])]
    private Collection $responses;

    #[ORM\OneToMany(mappedBy: 'evaluation', targetEntity: Question::class, cascade: ['persist', 'remove'])]
    #[Assert\Count(
        min: 1,
        minMessage: "L'évaluation doit contenir au moins une question."
    )]
    private Collection $questions;

    public function __construct()
    {
        $this->responses = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->createdAt = new \DateTime(); // ✅ Initialisation automatique
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if (is_null($this->createdAt)) {
            $this->createdAt = new \DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
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

    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setEvaluation($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            if ($question->getEvaluation() === $this) {
                $question->setEvaluation(null);
            }
        }

        return $this;
    }
}
