<?php
// src/Entity/Question.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Le contenu de la question ne peut pas être vide.')] // Vérifie que la question n'est pas vide
    #[Assert\Regex(
        pattern: "/[^\*&]+/",
        message: "Le contenu de la question ne doit pas contenir de caractères spéciaux comme * ou &."
    )] // Vérifie que le contenu ne contient pas '*' ou '&'
    #[Assert\Regex(
        pattern: "/^[A-Z]/",
        message: "Le contenu de la question doit commencer par une majuscule."
    )] // Vérifie que le contenu commence par une majuscule
    private string $content;

    #[ORM\ManyToOne(targetEntity: Evaluation::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Evaluation $evaluation = null;

    #[ORM\Column(type: 'boolean')]
    private bool $openEnded = false;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Response::class, cascade: ['persist', 'remove'])]
    private Collection $responses;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: EvaluationResponse::class)]
    private Collection $responseEvaluations;

    public function __construct()
    {
        $this->responses = new ArrayCollection();
        $this->responseEvaluations = new ArrayCollection();
    }

    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function addResponse(Response $response): self
    {
        if (!$this->responses->contains($response)) {
            $this->responses[] = $response;
            $response->setQuestion($this);
        }

        return $this;
    }

    public function removeResponse(Response $response): self
    {
        $this->responses->removeElement($response);
        return $this;
    }

    public function getResponseEvaluations(): Collection
    {
        return $this->responseEvaluations;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getEvaluation(): ?Evaluation
    {
        return $this->evaluation;
    }

    public function setEvaluation(?Evaluation $evaluation): self
    {
        $this->evaluation = $evaluation;
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
}
