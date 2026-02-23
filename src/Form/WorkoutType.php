<?php

namespace App\Form;

use App\Entity\Exercise;
use App\Entity\Workout;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Nom de la séance globale
            ->add('name', TextType::class, [
                'label' => 'Nom de la seance',
                'attr' => ['placeholder' => 'ex: Pecs et Dos', 'class' => 'form-control mb-3']
            ])
            
            // LA CASE SÉRIE QUE TU ATTENDAIS
            ->add('serie', ChoiceType::class, [
                'label' => 'Serie',
                'choices'  => [
                    'Serie 1' => 'Serie 1',
                    'Serie 2' => 'Serie 2',
                    'Serie 3' => 'Serie 3',
                    'Serie 4' => 'Serie 4',
                    'Serie 5' => 'Serie 5',
                ],
                'attr' => ['class' => 'form-select mb-3']
            ])

            // Choix de l'exercice
            ->add('exercise', EntityType::class, [
                'class' => Exercise::class,
                'choice_label' => 'name',
                'label' => 'Exercice',
                'placeholder' => 'Selectionnez l\'exercice...',
                'attr' => ['class' => 'form-select mb-3']
            ])

            // Poids utilisé
            ->add('weight', NumberType::class, [
                'label' => 'Poids (kg)',
                'attr' => ['placeholder' => 'ex: 60', 'class' => 'form-control mb-3']
            ])

            // Nombre de répétitions
            ->add('reps', IntegerType::class, [
                'label' => 'Nombre de repetitions',
                'attr' => ['placeholder' => 'ex: 10', 'class' => 'form-control mb-3']
            ])

            // Date de l'enregistrement
            ->add('createdAt', null, [
                'widget' => 'single_text',
                'label' => 'Date',
                'attr' => ['class' => 'form-control mb-3']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Workout::class,
        ]);
    }
}