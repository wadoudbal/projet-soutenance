<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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

        // 1. Si l'utilisateur n'a pas encore de secret 2FA, on en crée un
        if (!$user->getGoogleAuthenticatorSecret()) {
            $user->setGoogleAuthenticatorSecret($googleAuthenticator->generateSecret());
            $entityManager->flush();
        }

        // 2. On génère le contenu du QR Code (format otpauth://)
        $qrCodeContent = $googleAuthenticator->getQRContent($user);

        // 3. On génère une URL de QR Code (via un service externe ou bundle)
        // Ici, on utilise Google Charts pour la simplicité, mais tu pourras passer en local plus tard
        $qrCodeUrl = sprintf(
            'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=%s',
            urlencode($qrCodeContent)
        );

        return $this->render('profile/2fa_enable.html.twig', [
            'qrCodeUrl' => $qrCodeUrl,
            'user' => $user,
        ]);
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
        return $this->redirectToRoute('app_profile'); // Change vers ta route de profil
    }
}