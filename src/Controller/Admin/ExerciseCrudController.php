<?php

namespace App\Controller\Admin;

use App\Entity\Exercise;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ExerciseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Exercise::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Exercice')
            ->setEntityLabelInPlural('Exercices');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            
            TextField::new('name', 'Nom de l\'exercice'),
            
            // On transforme le texte en badge
            TextField::new('muscleGroup', 'Groupe Musculaire')
                ->formatValue(function ($value, $entity) {
                    // On définit des couleurs selon le muscle
                    $badgeClass = match(strtolower($value)) {
                        'pectoraux', 'pecs' => 'badge-primary',
                        'jambes', 'cuisses' => 'badge-success',
                        'dos' => 'badge-warning',
                        'épaules' => 'badge-info',
                        'bras', 'biceps', 'triceps' => 'badge-danger',
                        default => 'badge-secondary', // Gris par défaut
                    };
                    
                    // On retourne le HTML du badge (Bootstrap est déjà chargé)
                    return sprintf('<span class="badge %s">%s</span>', $badgeClass, strtoupper($value));
                }),
        ];
    }
}