<?php

namespace App\Controller;

use App\Repository\WorkoutRepository;
use App\Repository\GoalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(WorkoutRepository $workoutRepository, GoalRepository $goalRepository): Response
    {
        $user = $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');

        // On ne récupère PLUS tous les objets, on récupère des valeurs simples
        $totalVolume = $workoutRepository->getTotalVolumeByUser($user);
        $recordAbsolu = $workoutRepository->getAbsoluteRecordByUser($user);
        
        // Calcul des séances de la semaine directement en SQL
        $compteSeancesSemaine = $workoutRepository->countSessionsThisWeek($user);

        // Gestion de l'objectif
        $goal = $goalRepository->findOneBy(['user' => $user], ['id' => 'DESC']);
        $pourcentageGoal = 0;

        if ($goal && $goal->getExercise()) {
            $poidsCible = (float) $goal->getTargetWeight();
            // On récupère le record spécifique pour cet exercice
            $perfActuelle = $workoutRepository->getRecordForExercise($user, $goal->getExercise());
            
            if ($poidsCible > 0) {
                $pourcentageGoal = min(100, ($perfActuelle / $poidsCible) * 100);
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'volumeTotal' => $totalVolume,
            'seancesSemaine' => $compteSeancesSemaine,
            'recordAbsolu' => $recordAbsolu,
            'goal' => $goal,
            'pourcentage' => round($pourcentageGoal, 1),
            // On ne prend que les 5 derniers pour l'affichage, c'est suffisant
            'lastWorkouts' => $workoutRepository->findBy(['user' => $user], ['createdAt' => 'DESC'], 5),
        ]);
    }
}