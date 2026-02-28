<?php

namespace App\Controller\Admin;

use App\Entity\Exercise;
use App\Entity\User;
use App\Document\ActivityLog;
use App\Repository\ExerciseRepository;
use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    // Injection des services MySQL et MongoDB
    public function __construct(
        private UserRepository $userRepository,
        private ExerciseRepository $exerciseRepository,
        private DocumentManager $dm
    ) {
    }

    public function index(): Response
    {
        // 1. Récupération des 5 derniers logs (MongoDB)
        // On vérifie que la collection existe ou on gère le retour vide
        $recentLogs = $this->dm->getRepository(ActivityLog::class)
            ->findBy([], ['createdAt' => 'DESC'], 5);

        // 2. Préparation des statistiques pour le graphique Chart.js
        $chartData = [];
        $chartLabels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = new \DateTime("- $i days");
            $chartLabels[] = $date->format('d/m');

            $start = (clone $date)->setTime(0, 0, 0);
            $end = (clone $date)->setTime(23, 59, 59);

            // Requête optimisée sur MongoDB pour compter les actions par jour
            $count = $this->dm->createQueryBuilder(ActivityLog::class)
                ->field('createdAt')->range($start, $end)
                ->count()
                ->getQuery()
                ->execute();

            $chartData[] = $count;
        }

        // 3. Rendu du template personnalisé avec toutes les variables
        return $this->render('admin/dashboard.html.twig', [
            'countUsers' => $this->userRepository->count([]),
            'countExercises' => $this->exerciseRepository->count([]),
            'recentLogs' => $recentLogs,
            'chartLabels' => json_encode($chartLabels),
            'chartData' => json_encode($chartData),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<b class="text-danger">PERFORMANCE</b> ID')
            ->setLocales(['fr'])
            // Désactive le mode sombre si tu as des problèmes de contraste sur tes cartes custom
            ->disableDarkMode();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Vue d\'ensemble', 'fa fa-home');

        yield MenuItem::section('Gestion Catalogue');
        yield MenuItem::linkToCrud('Exercices', 'fas fa-dumbbell', Exercise::class);

        yield MenuItem::section('Communauté');
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);

        yield MenuItem::section('Maintenance');
        // Cette route 'admin_logs' doit correspondre à ton contrôleur de logs MongoDB
        yield MenuItem::linkToRoute('Logs d\'activité', 'fas fa-history', 'admin_logs');
        // Dans configureMenuItems()
        yield MenuItem::section('Sécurité');
        yield MenuItem::linkToRoute('Configurer ma 2FA', 'fas fa-lock', 'admin_2fa_setup');
    }
}