<?php

namespace App\EventSubscriber;

use App\Document\ActivityLog;
use Doctrine\ODM\MongoDB\DocumentManager;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bundle\SecurityBundle\Security;

class AdminActivitySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DocumentManager $dm,
        private Security $security
    ) {}

    public static function getSubscribedEvents(): array
    {
        // ON VIDE TOUT ICI POUR TESTER LA DÉSACTIVATION 2FA
        return [
            // AfterEntityPersistedEvent::class => 'onAfterPersist',
            // AfterEntityDeletedEvent::class => 'onAfterDelete',
        ];
    }

    public function onAfterPersist(AfterEntityPersistedEvent $event): void
    {
        $this->saveLog($event->getEntityInstance(), 'CRÉATION');
    }

    public function onAfterDelete(AfterEntityDeletedEvent $event): void
    {
        $this->saveLog($event->getEntityInstance(), 'SUPPRESSION');
    }

    private function saveLog(object $entity, string $action): void
    {
        $userEmail = $this->security->getUser() ? $this->security->getUser()->getUserIdentifier() : 'Système';
        $entityName = (new \ReflectionClass($entity))->getShortName();

        $log = new ActivityLog($userEmail, $action . " : " . $entityName);

        $this->dm->persist($log);
        $this->dm->flush();
    }
}