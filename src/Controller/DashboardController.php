<?php

namespace App\Controller;

use App\Repository\WorkoutRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(WorkoutRepository $workoutRepository): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $workouts = $workoutRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);

        $totalVolume = 0;
        foreach ($workouts as $w) {
            $weight = (float) $w->getWeight();
            $reps   = (int)   $w->getReps();
            $serie  = (int)   $w->getSerie();
            
            $totalVolume += ($weight * $reps * $serie);
        }

        $sessions = [];
        foreach ($workouts as $w) {
            $sessions[] = $w->getName() . $w->getCreatedAt()->format('Y-m-d');
        }
        $workoutCount = count(array_unique($sessions));

        $records = [];
        foreach ($workouts as $w) {
            $exName = $w->getExercise() ? $w->getExercise()->getName() : 'Inconnu';
            $currentWeight = (float) $w->getWeight();

            if (!isset($records[$exName]) || $currentWeight > $records[$exName]) {
                $records[$exName] = $currentWeight;
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'totalVolume' => $totalVolume,
            'workoutCount' => $workoutCount,
            'lastWorkouts' => array_slice($workouts, 0, 7),
            'personalRecords' => array_slice($records, 0, 5),
            'allWorkouts' => $workouts
        ]);
    }
}