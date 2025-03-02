<?php
// src/Form/EvaluationType.php

namespace App\Form;

use App\Entity\Evaluation;
use App\Entity\Question;  // Make sure to import the Question entity
use App\Form\QuestionType; // Import the QuestionType form class
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class EvaluationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Field for the name of the evaluation
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'évaluation',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'Le nom doit contenir au moins 3 caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser 50 caractères',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[A-Z].*/',
                        'message' => 'Le nom doit commencer par une majuscule',
                    ]),
                ],
            ])

            // Collection of questions (each question will have its own form)
            ->add('questions', CollectionType::class, [
                'entry_type' => QuestionType::class,  // Use the QuestionType form for each question
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,  // This ensures the collection is updated correctly
            ])

            // Field for the description (optional)
            ->add('description', TextareaType::class, [
                'label' => 'Description (optionnel)',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evaluation::class,  // Bind the form to the Evaluation entity
        ]);
    }
}
