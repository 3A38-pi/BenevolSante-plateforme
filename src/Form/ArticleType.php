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
                'label' => 'Titre de l\'article',
                'attr' => ['placeholder' => 'Entrer le titre']
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de l\'article',
                'attr' => ['placeholder' => 'Upload une image']
            ])
            ->add('tags', TextType::class, [
                'label' => 'Tags de l\'article',
                'attr' => ['placeholder' => 'Detailler quels sont les tags']
            ])
            // ->add('nombreCommentaire', TextType::class, [
            //     'label' => 'Nombre de commentaire',
            //     'attr' => ['placeholder' => 'Entrer le nombre de commentaire']
            // ])

            ->add('description', TextareaType::class, [
                'label' => 'Description de l\'article',
                'attr' => ['placeholder' => 'Citer le contenu de l\'article']
            ])

            ->add('ajouter', SubmitType::class, [
                'label' => 'Ajouter l\'article',
                'attr' => ['class' => 'btn btn-primary']
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
