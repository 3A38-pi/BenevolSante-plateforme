<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Choice as ChoiceConstraint;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir votre nom.',
                    ]),
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'Votre nom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir votre prénom.',
                    ]),
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'Votre prénom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un email.',
                    ]),
                    new Email([
                        'message' => 'Veuillez saisir un email valide.',
                    ]),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                // 'mapped' => false,  // Supprimez cette option pour mapper directement
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le mot de passe est requis',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('typeUtilisateur', ChoiceType::class, [
                'choices' => [
                    'Donneur' => 'donneur',
                    'Bénéficiaire' => 'beneficiaire',
                    'Professionnel' => 'professionnel',
                ],
                'expanded' => true, // Display as radio buttons
                'multiple' => false,
                'label' => 'Type d\'utilisateur',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez choisir un type d\'utilisateur.',
                    ]),
                    new ChoiceConstraint([
                        'choices' => ['donneur', 'beneficiaire', 'professionnel'],
                        'message' => 'Le type d\'utilisateur doit être "donneur", "beneficiaire" ou "professionnel".',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
