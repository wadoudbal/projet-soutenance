<?php

namespace App\Repository;

use App\Entity\Workout;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WorkoutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Workout::class);
    }

    /**
     * RÉCUPÉRATION GROUPÉE (Optimisation Performance)
     * Récupère le volume, le record et le nombre de sessions en UNE SEULE requête.
     */
    public function getDashboardStats($user): array
    {
        // On récupère les stats globales
        $result = $this->createQueryBuilder('w')
            // On calcule le volume : Poids * Répétitions
            // Vérifie si c'est 'reps' ou 'serie' dans ton entité !
            ->select('SUM(w.weight * w.reps) as totalVolume')
            ->addSelect('MAX(w.weight) as absoluteRecord')
            // On compte le nombre de séances différentes
            ->addSelect('COUNT(DISTINCT w.id) as totalSessions')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

        // Calcul spécifique pour la semaine actuelle
        $startOfWeek = new \DateTime('monday this week 00:00:00');
        $weeklySessions = $this->createQueryBuilder('w')
            ->select('COUNT(DISTINCT SUBSTRING(w.createdAt, 1, 10))')
            ->where('w.user = :user')
            ->andWhere('w.createdAt >= :start')
            ->setParameter('user', $user)
            ->setParameter('start', $startOfWeek)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalVolume' => $result['totalVolume'] ?? 0,
            'absoluteRecord' => $result['absoluteRecord'] ?? 0,
            'weeklySessions' => $weeklySessions ?? 0,
        ];
    }
    /**
     * Record spécifique (Garder séparé car dépend de l'objectif en cours)
     */
    public function getRecordForExercise($user, $exercise): float
    {
        $result = $this->createQueryBuilder('w')
            ->select('MAX(w.weight)')
            ->where('w.user = :user')
            ->andWhere('w.exercise = :ex')
            ->setParameter('user', $user)
            ->setParameter('ex', $exercise)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }
}