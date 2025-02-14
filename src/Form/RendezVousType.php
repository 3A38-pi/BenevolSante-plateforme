<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 3])
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prenom',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 3])
                ]
            ])
            
            ->add('telephone', TelType::class, [
                'label' => 'Numéro de téléphone',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 10, 'max' => 15])
                ]
            ])
            ->add('dateRendezVous', DateType::class, [
                'label' => 'Date du rendez-vous',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotNull()
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de Rendez-vous',
                'choices' => [
                    'En Ligne' => 'en_ligne',
                    'Sur Place' => 'sur_place'
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Réserver',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
        ]);
    }
}
