<?php

namespace App\Repository;

use App\Entity\Workout;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Workout>
 */
class WorkoutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Workout::class);
    }

    /**
     * Calcule le volume total (Poids * Reps * Séries) directement en base de données.
     */
    public function getTotalVolumeByUser($user): float
    {
        return (float) $this->createQueryBuilder('w')
            ->select('SUM(w.weight * w.reps * w.serie)')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère la valeur maximale de la colonne weight pour cet utilisateur.
     */
    public function getAbsoluteRecordByUser($user): float
    {
        return (float) $this->createQueryBuilder('w')
            ->select('MAX(w.weight)')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte le nombre de jours d'entraînement différents sur la semaine en cours.
     */
    public function countSessionsThisWeek($user): int
    {
        $startOfWeek = new \DateTime('monday this week 00:00:00');
        
        return (int) $this->createQueryBuilder('w')
            ->select('COUNT(DISTINCT SUBSTRING(w.createdAt, 1, 10))') 
            ->where('w.user = :user')
            ->andWhere('w.createdAt >= :start')
            ->setParameter('user', $user)
            ->setParameter('start', $startOfWeek)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère le record (poids max) pour un exercice spécifique.
     */
    public function getRecordForExercise($user, $exercise): float
    {
        return (float) $this->createQueryBuilder('w')
            ->select('MAX(w.weight)')
            ->where('w.user = :user')
            ->andWhere('w.exercise = :ex')
            ->setParameter('user', $user)
            ->setParameter('ex', $exercise)
            ->getQuery()
            ->getSingleScalarResult();
    }
}