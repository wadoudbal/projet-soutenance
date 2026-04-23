<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TwoFactorController extends AbstractController
{
    #[Route('/profile/2fa/enable', name: 'app_2fa_enable')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function enable2fa(
        GoogleAuthenticatorInterface $googleAuthenticator,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->getGoogleAuthenticatorSecret()) {
            $user->setGoogleAuthenticatorSecret($googleAuthenticator->generateSecret());
            $entityManager->flush();
        }

        $qrCodeContent = $googleAuthenticator->getQRContent($user);
       
        $qrCodeUrl = sprintf(
            'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=%s&choe=UTF-8',
            rawurlencode($qrCodeContent)
        );

        return $this->render('profile/2fa_enable.html.twig', [
            'qrCodeUrl' => $qrCodeUrl,
            'qrCodeContent' => $qrCodeContent, 
            'user' => $user,
        ]);
    }

    // AJOUT : methods: ['GET', 'POST'] pour permettre la soumission du formulaire
    #[Route('/2fa', name: '2fa_login', methods: ['GET', 'POST'])]
    public function display2faForm(): Response
    {
        // On affiche le formulaire
        return $this->render('bundles/SchebTwoFactorBundle/Authentication/form.html.twig');
    }

    #[Route('/profile/2fa/disable', name: 'app_2fa_disable')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function disable2fa(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setGoogleAuthenticatorSecret(null);
        $entityManager->flush();

        $this->addFlash('success', 'La double authentification a été désactivée.');
        
        return $this->redirectToRoute('app_home'); 
    }
}