<?php

namespace App\Form;

use App\Entity\Exercise;
use App\Entity\Goal;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GoalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('exercise', EntityType::class, [
                'class' => Exercise::class,
                'choice_label' => 'name',
                'label' => 'Exercice concerné',
                'placeholder' => 'Sélectionnez un exercice',
                'attr' => ['class' => 'form-select']
            ])
            ->add('targetWeight', NumberType::class, [
                'label' => 'Poids cible (kg)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 120'
                ]
            ])
            ->add('deadline', DateType::class, [
                'label' => 'Date d\'échéance',
                'widget' => 'single_text', // Crucial pour avoir le calendrier HTML5
                'required' => false,       // Optionnel selon ton besoin
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime())->format('Y-m-d') // Sécurité : pas de date passée
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Goal::class,
        ]);
    }
}