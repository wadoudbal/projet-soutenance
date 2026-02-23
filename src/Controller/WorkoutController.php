<?php

namespace App\Controller;

use App\Entity\Workout;
use App\Form\WorkoutType;
use App\Repository\WorkoutRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/workout')]
final class WorkoutController extends AbstractController
{
    #[Route(name: 'app_workout_index', methods: ['GET'])]
    public function index(Request $request, WorkoutRepository $workoutRepository): Response
    {
        $query = $request->query->get('q');
        $user = $this->getUser();

        if ($query) {
            // Recherche par nom d'exercice ou nom de séance
            $workouts = $workoutRepository->createQueryBuilder('w')
                ->join('w.exercise', 'e')
                ->where('w.user = :user')
                ->andWhere('e.name LIKE :q OR w.name LIKE :q')
                ->setParameter('user', $user)
                ->setParameter('q', '%' . $query . '%')
                ->orderBy('w.createdAt', 'DESC')
                ->getQuery()
                ->getResult();
        } else {
            $workouts = $workoutRepository->findBy(
                ['user' => $user], 
                ['createdAt' => 'DESC', 'name' => 'ASC']
            );
        }

        return $this->render('workout/index.html.twig', [
            'workouts' => $workouts,
            'searchTerm' => $query
        ]);
    }

    #[Route('/new', name: 'app_workout_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $workout = new Workout();
        $workout->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(WorkoutType::class, $workout);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workout->setUser($this->getUser()); 
            $entityManager->persist($workout);
            $entityManager->flush();

            $this->addFlash('success', 'Série enregistrée !');
            return $this->redirectToRoute('app_workout_new');
        }

        return $this->render('workout/new.html.twig', [
            'workout' => $workout,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_workout_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Workout $workout, EntityManagerInterface $entityManager): Response
    {
        if ($workout->getUser() !== $this->getUser()) { throw $this->createAccessDeniedException(); }

        $form = $this->createForm(WorkoutType::class, $workout);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_workout_index');
        }

        return $this->render('workout/edit.html.twig', [
            'workout' => $workout,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_workout_delete', methods: ['POST'])]
    public function delete(Request $request, Workout $workout, EntityManagerInterface $entityManager): Response
    {
        if ($workout->getUser() !== $this->getUser()) { throw $this->createAccessDeniedException(); }

        if ($this->isCsrfTokenValid('delete'.$workout->getId(), $request->request->get('_token'))) {
            $entityManager->remove($workout);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_workout_index');
    }
}