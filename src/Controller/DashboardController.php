<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Repository\WorkoutRepository;
use App\Repository\GoalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(WorkoutRepository $workoutRepository, GoalRepository $goalRepository): Response
    {
        $user = $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');

        // Stats globales
        $stats = $workoutRepository->getDashboardStats($user);
        
        // Gestion de l'objectif
        $goal = $goalRepository->findOneBy(['user' => $user], ['id' => 'DESC']);
        $pourcentageGoal = 0;
        $perfActuelle = 0;

        if ($goal && $goal->getExercise()) {
            $poidsCible = (float) $goal->getTargetWeight();
            // On récupère la meilleure perf sur CET exercice précis
            $perfActuelle = $workoutRepository->getRecordForExercise($user, $goal->getExercise());
            
            if ($poidsCible > 0) {
                $pourcentageGoal = min(100, ($perfActuelle / $poidsCible) * 100);
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'volumeTotal'    => $stats['totalVolume'] ?? 0,
            'seancesSemaine' => $stats['weeklySessions'] ?? 0,
            'recordAbsolu'   => $stats['absoluteRecord'] ?? 0,
            'perfActuelle'   => $perfActuelle, // Ajouté pour le Twig
            'goal'           => $goal,
            'pourcentage'    => round($pourcentageGoal, 1),
            'lastWorkouts'   => $workoutRepository->findBy(['user' => $user], ['createdAt' => 'DESC'], 5),
        ]);
    }

    #[Route('/goal/delete/{id}', name: 'app_goal_delete', methods: ['POST'])]
    public function deleteGoal(Request $request, Goal $goal, EntityManagerInterface $entityManager): Response
    {
        // Sécurité : Vérifier que l'objectif appartient bien à l'utilisateur
        if ($goal->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Vérification du token CSRF pour la sécurité des formulaires
        if ($this->isCsrfTokenValid('delete' . $goal->getId(), $request->request->get('_token'))) {
            $entityManager->remove($goal);
            $entityManager->flush();
            $this->addFlash('success', 'Objectif supprimé.');
        }

        return $this->redirectToRoute('app_dashboard');
    }
}