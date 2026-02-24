<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Form\GoalType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GoalController extends AbstractController
{
    #[Route('/goal/new', name: 'app_goal_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $goal = new Goal();
        $form = $this->createForm(GoalType::class, $goal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On lie l'objectif à l'utilisateur connecté
            $goal->setUser($this->getUser());

            $entityManager->persist($goal);
            $entityManager->flush();

            $this->addFlash('success', 'Objectif enregistré avec succès !');

            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('goal/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}