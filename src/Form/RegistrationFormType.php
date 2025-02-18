<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
            ])
            /*
            ->add('typeUtilisateur', ChoiceType::class, [
                'label' => 'Type d\'utilisateur',
                'choices' => [
                    'Donneur' => 'donneur',
                    'Bénéficiaire' => 'beneficiaire',
                    'Professionnel' => 'professionnel',
                ],
                'mapped' => false
            ])*/
            ->add('typeUtilisateur', ChoiceType::class, [
                'choices' => [
                    'Donneur' => 'donneur',
                    'Bénéficiaire' => 'beneficiaire',
                    'Professionnel' => 'professionnel',
                ],
                'expanded' => true, // Affiche sous forme de boutons radio
                'multiple' => false,
                'label' => 'Type d\'utilisateur'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'S\'inscrire'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
