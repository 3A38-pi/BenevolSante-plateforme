<?php
// src/Form/QuestionType.php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextType::class, [
                'label' => 'Question',
                'attr' => [
                    'class' => 'custom-input',  // Custom class for styling
                     'placeholder' => 'Entrez la question...',
                ],
            ])
            ->add('openEnded', CheckboxType::class, [
                'label' => 'Question ouverte',
                'required' => false,
                'attr' => ['class' => 'custom-checkbox'],  // Custom class for checkbox
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,  // Ensure mapping to Question entity
        ]);
    }
}
