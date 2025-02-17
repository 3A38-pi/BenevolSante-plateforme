<?php

namespace App\Form;

use App\Entity\EvaluationResponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EvaluationResponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('answer', TextType::class, [
                'required' => false,
                'label' => 'Your Answer',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Your response...']
            ])
            ->add('rating', IntegerType::class, [
                'required' => false,
                'label' => 'Your Rating',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Rate from 1 to 5']
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit Response',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EvaluationResponse::class,
        ]);
    }
}
