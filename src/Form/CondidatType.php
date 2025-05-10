<?php

namespace App\Form;

use App\Entity\Condidat;
use App\Entity\Offre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CondidatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('email')
            ->add('telephone')
            ->add('cv', ChoiceType::class, [
                'choices' => [
                    'Docteur' => 'Docteur',
                    'Infirmier' => 'Infirmier',
                    'Aide-soignant' => 'Aide-soignant',
                ],
                'expanded' => false, // Liste déroulante
                'multiple' => false, // Une seule sélection
                'attr' => ['class' => 'form-select form-control-lg border-primary shadow-sm'],
                'label' => 'Profession'
            ])
            
            ->add('offre', EntityType::class, [
                'class' => Offre::class,
                'choice_label' => 'titreOffre', // Vérifie que c'est bien le bon champ dans l'entité Offre
                'disabled' => true, // Empêcher l'utilisateur de modifier l'offre sélectionnée
                'required' => false, // Permet de soumettre sans erreur
            ]);
            
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Condidat::class,
        ]);
    }
}
