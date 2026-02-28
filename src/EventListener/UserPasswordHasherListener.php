<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: User::class)]
class UserPasswordHasherListener
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function prePersist(User $user, LifecycleEventArgs $event): void
    {
        $this->hashPassword($user);
    }

    public function preUpdate(User $user, LifecycleEventArgs $event): void
    {
        $this->hashPassword($user);
    }

    private function hashPassword(User $user): void
    {
        $plainPassword = $user->getPassword();

        // Si le mot de passe est vide (cas d'une update sans changement), on ne fait rien
        if (null === $plainPassword || '' === $plainPassword) {
            return;
        }

        // On hache le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
    }
}