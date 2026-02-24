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
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer tous les entraînements de l'athlète
        $workouts = $workoutRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);

        // 1. CALCUL DU VOLUME TOTAL (CORRECTION TYPE ERROR)
        $totalVolume = 0;
        foreach ($workouts as $w) {
            // On force la conversion en nombres pour éviter le crash
            $poids = (float) ($w->getWeight() ?? 0);
            $reps = (int) ($w->getReps() ?? 0);
            $series = (int) ($w->getSerie() ?? 0);
            
            $totalVolume += ($poids * $reps * $series);
        }

        // 2. CALCUL DES SÉANCES DE LA SEMAINE
        $debutSemaine = new \DateTime('monday this week 00:00:00');
        $sessionsSemaine = [];
        foreach ($workouts as $w) {
            if ($w->getCreatedAt() && $w->getCreatedAt() >= $debutSemaine) {
                $sessionsSemaine[] = $w->getCreatedAt()->format('Y-m-d');
            }
        }
        $compteSeancesSemaine = count(array_unique($sessionsSemaine));

        // 3. RÉCUPÉRATION DES RECORDS
        $records = [];
        foreach ($workouts as $w) {
            $nomEx = $w->getExercise() ? $w->getExercise()->getName() : 'Inconnu';
            $poids = (float) ($w->getWeight() ?? 0);
            if (!isset($records[$nomEx]) || $poids > $records[$nomEx]) {
                $records[$nomEx] = $poids;
            }
        }
        arsort($records);
        $recordAbsolu = !empty($records) ? reset($records) : 0;
        $topRecords = array_slice($records, 0, 4);

        // 4. GESTION DE L'OBJECTIF DE FORCE
        $goal = $goalRepository->findOneBy(['user' => $user], ['id' => 'DESC']);
        $pourcentageGoal = 0;

        if ($goal && $goal->getExercise()) {
            $nomExCible = $goal->getExercise()->getName();
            $poidsCible = (float) $goal->getTargetWeight();
            
            $perfActuelle = $records[$nomExCible] ?? 0;
            
            if ($poidsCible > 0) {
                $pourcentageGoal = ($perfActuelle / $poidsCible) * 100;
                if ($pourcentageGoal > 100) $pourcentageGoal = 100;
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'volumeTotal' => $totalVolume,
            'seancesSemaine' => $compteSeancesSemaine,
            'recordAbsolu' => $recordAbsolu,
            'personalRecords' => $topRecords,
            'goal' => $goal,
            'pourcentage' => round($pourcentageGoal, 1), // Arrondi pour un affichage propre
            'lastWorkouts' => array_slice($workouts, 0, 7),
        ]);
    }
}