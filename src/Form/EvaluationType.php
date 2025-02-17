<?php
// src/Form/EvaluationType.php

namespace App\Form;

use App\Entity\Evaluation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EvaluationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       
        
        $builder
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
            ->add('question', TextType::class, [
                'label' => 'Question',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La question est obligatoire']),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description (optionnel)',
                'required' => false,
            ])
            ->add('options', TextareaType::class, [
                'label' => 'Options (séparées par des virgules)',
                'mapped' => false,  
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^(\s*[^,]+(,\s*[^,]+)*)?$/',
                        'message' => 'Les options doivent être séparées par des virgules',
                    ]),
                ],
            ])
            ->add('openEnded', CheckboxType::class, [
                'label' => 'Question ouverte',
                'required' => false,
            ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evaluation::class,
        ]);
    }
}
