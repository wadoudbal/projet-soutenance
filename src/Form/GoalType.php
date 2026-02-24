<?php

namespace App\Form;

use App\Entity\Exercise;
use App\Entity\Goal;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GoalType extends AbstractType
{
   public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('targetWeight', null, [
            'label' => 'Poids cible (kg)',
            'attr' => ['class' => 'form-control']
        ])
        ->add('exercise', EntityType::class, [
            'class' => Exercise::class,
            'choice_label' => 'name',
            'label' => 'Exercice concerné',
            'attr' => ['class' => 'form-control']
        ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Goal::class,
        ]);
    }
}
