<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setPageTitle('index', 'Gestion des membres')
            ->setDefaultSort(['email' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        
        yield TextField::new('firstName', 'Prénom');
        yield TextField::new('lastName', 'Nom de famille');
        yield EmailField::new('email', 'Adresse Email');

        yield ChoiceField::new('roles', 'Droits / Rôles')
            ->allowMultipleChoices()
            ->setChoices([
                'Administrateur' => 'ROLE_ADMIN',
                'Utilisateur' => 'ROLE_USER',
            ])
            ->renderAsBadges([
                'ROLE_ADMIN' => 'danger',
                'ROLE_USER' => 'info',
            ]);

        // --- CHAMP MOT DE PASSE ---
        yield TextField::new('password', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->onlyOnForms()
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->setHelp($pageName === Crud::PAGE_NEW 
                ? 'Définissez un mot de passe solide.' 
                : 'Laissez vide pour conserver le mot de passe actuel.');

        // --- CHAMP 2FA (Nouveau) ---
        // On affiche si la 2FA est activée ou non sous forme de badge
        yield TextField::new('googleAuthenticatorSecret', 'Statut 2FA')
            ->hideOnForm()
            ->formatValue(function ($value) {
                return $value ? '✅ Activé' : '❌ Désactivé';
            });
            
        // Permet de réinitialiser la 2FA en cas de perte du téléphone (champ caché d'habitude)
        yield TextField::new('googleAuthenticatorSecret', 'Secret 2FA (Clé)')
            ->onlyOnDetail()
            ->setHelp('Cette clé permet de configurer Google Authenticator manuellement.');
    }
}