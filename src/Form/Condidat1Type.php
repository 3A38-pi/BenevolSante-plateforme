<?php

namespace App\Form;

use App\Entity\Condidat;
use App\Entity\Offre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
class Condidat1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, [
            'label' => 'Nom',
            'required' => true, // Or false if optional
            'empty_data' => '', // Ensure default value is empty string if not filled
        ])
        ->add('prenom', TextType::class, [
            'label' => 'Prénom',
            'required' => true, // Or false if optional
            'empty_data' => '', // Default empty string if not filled
        ])
        ->add('email', EmailType::class, [
            'label' => 'Email',
            'required' => true, // Should be required for email field
            'empty_data' => '', // Default empty string if not filled
        ])
        ->add('telephone', TelType::class, [
            'label' => 'Téléphone',
            'required' => true, // Or false if optional
            'empty_data' => '', // Default empty string if not filled
        ])
            ->add('cv', TextType::class, [
                'required' => false, 
                'empty_data' => '', // Set default empty string if no value is provided
            ])
            ->add('offre', EntityType::class, [
                'class' => Offre::class,
'choice_label' => 'titre_offre',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Condidat::class,
        ]);
    }
}
