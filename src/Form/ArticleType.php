<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => false,
                'attr'  => ['placeholder' => 'Entrer le titre']
            ])
            ->add('image', FileType::class, [
                'label' => false,
                'attr'  => ['placeholder' => 'Upload une image']
            ])
                     
                        ->add('categorie', TextType::class, [
                            'label' => false,
                            'attr'  => ['placeholder' => 'Définir la catégorie']
                        ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'attr'  => ['placeholder' => 'Citer le contenu de l\'article']
            ])
            ->add('ajouter', SubmitType::class, [
                'label' => 'Ajouter l\'article',
                'attr'  => ['class' => 'btn bg-gradient-dark w-30 my-4 mb-2']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
