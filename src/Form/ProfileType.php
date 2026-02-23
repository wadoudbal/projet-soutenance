<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Prénom / Pseudo',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ton prénom ou pseudo',
                    'class' => 'form-control bg-dark text-white border-secondary'
                ]
            ])
            ->add('poids', NumberType::class, [
                'label' => 'Poids (kg)',
                'required' => false,
                'scale' => 1, // Permet un chiffre après la virgule
                'attr' => [
                    'placeholder' => 'Ex: 75.5',
                    'class' => 'form-control bg-dark text-white border-secondary'
                ]
            ])
            ->add('taille', IntegerType::class, [
                'label' => 'Taille (cm)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: 180',
                    'class' => 'form-control bg-dark text-white border-secondary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}