<?php

// src/Entity/EvaluationResponse.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\EvaluationResponseRepository')]
class EvaluationResponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $answer = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $rating = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Evaluation')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Evaluation $evaluation = null;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'responseEvaluations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(?string $answer): self
    {
        $this->answer = $answer;
        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): self
    {
        $this->rating = $rating;
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

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;
        return $this;
    }
}
