<?php

namespace App\Controller\Admin;

use App\Entity\Exercise;
use App\Entity\User;
use App\Document\ActivityLog;
use App\Repository\ExerciseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private UserRepository $userRepository,
        private ExerciseRepository $exerciseRepository,
        private DocumentManager $dm,
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $recentLogs = $this->dm->getRepository(ActivityLog::class)
            ->findBy([], ['createdAt' => 'DESC'], 5);

        $chartData = [];
        $chartLabels = [];

        foreach (range(6, 0) as $i) {
            $date = new \DateTime("- $i days");
            $chartLabels[] = $date->format('d/m');
            $start = (clone $date)->setTime(0, 0, 0);
            $end = (clone $date)->setTime(23, 59, 59);

            $count = $this->dm->createQueryBuilder(ActivityLog::class)
                ->field('createdAt')->range($start, $end)
                ->count()->getQuery()->execute();

            $chartData[] = $count;
        }

        return $this->render('admin/dashboard.html.twig', [
            'countUsers' => $this->userRepository->count([]),
            'countExercises' => $this->exerciseRepository->count([]),
            'recentLogs' => $recentLogs,
            'chartLabels' => json_encode($chartLabels),
            'chartData' => json_encode($chartData),
        ]);
    }

    #[Route('/admin/2fa-setup', name: 'admin_2fa_setup')]
    public function setup2fa(GoogleAuthenticatorInterface $googleAuthenticator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('admin');
        }

        // 1. On force la lecture réelle en BDD pour éviter les données fantômes
        $this->em->refresh($user);

        $isEnabled = $user->isGoogleAuthenticatorEnabled();
        $secret = $user->getGoogleAuthenticatorSecret();
        $qrCodeDataUri = null;

        if (!$isEnabled) {
            // 2. IMPORTANT : Si pas de secret, on en génère un UNIQUEMENT en mémoire
            // On ne fait PAS de persist() ni de flush() ici.
            // Cela évite que la clé soit réactivée "toute seule" en base après une désactivation.
            if (empty($secret)) {
                $secret = $googleAuthenticator->generateSecret();
                $user->setGoogleAuthenticatorSecret($secret);
                // On laisse la BDD à NULL tant que l'utilisateur n'a pas validé.
            }

            $qrCodeContent = $googleAuthenticator->getQRContent($user);
            $builder = new Builder(
                writer: new PngWriter(),
                data: $qrCodeContent,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 250,
                margin: 10
            );
            $qrCodeDataUri = $builder->build()->getDataUri();
        }

        return $this->render('admin/2fa_setup.html.twig', [
            'qrCodeDataUri' => $qrCodeDataUri,
            'secret' => $secret,
            'isEnabled' => $isEnabled,
        ]);
    }

    #[Route('/admin/2fa-disable', name: 'admin_2fa_disable')]
    public function disable2fa(AdminUrlGenerator $adminUrlGenerator, TokenStorageInterface $tokenStorage): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user) {
            // 1. Suppression brute en SQL (radical et immédiat)
            $this->em->getConnection()->executeStatement(
                'UPDATE user SET google_authenticator_secret = NULL WHERE id = ?',
                [$user->getId()]
            );

            // 2. Mise à jour de l'objet en session
            $user->setGoogleAuthenticatorSecret(null);

            // 3. On force Symfony à oublier l'ancien token (indispensable pour que Symfony sache que la 2FA est coupée)
            $token = new PostAuthenticationToken($user, 'main', $user->getRoles());
            $tokenStorage->setToken($token);

            // 4. On détache l'objet pour empêcher Doctrine de faire une resauvegarde automatique accidentelle
            $this->em->detach($user);

            $this->addFlash('success', 'La double authentification a été désactivée.');
        }

        return $this->redirect($adminUrlGenerator->setRoute('admin_2fa_setup')->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<b class="text-danger">PERFORMANCE</b> ID')
            ->setLocales(['fr'])
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
        yield MenuItem::linkToRoute('Logs d\'activité', 'fas fa-history', 'admin_logs');
        yield MenuItem::section('Sécurité');
        yield MenuItem::linkToRoute('Configurer ma 2FA', 'fas fa-lock', 'admin_2fa_setup');
    }
}