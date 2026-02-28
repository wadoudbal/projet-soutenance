<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TwoFactorController extends AbstractController
{
    #[Route('/admin/2fa/setup', name: 'admin_2fa_setup')]
    public function setup(GoogleAuthenticatorInterface $googleAuthenticator, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        // On vérifie si la 2FA est déjà active
        $isEnabled = $user->isGoogleAuthenticatorEnabled();
        $qrCodeDataUri = null;

        // On ne génère le QR Code QUE si la 2FA n'est pas encore active
        if (!$isEnabled) {
            // On génère un secret temporaire s'il n'en a pas
            if (!$user->getGoogleAuthenticatorSecret()) {
                $user->setGoogleAuthenticatorSecret($googleAuthenticator->generateSecret());
                $em->persist($user);
                $em->flush();
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
            'secret' => $user->getGoogleAuthenticatorSecret(),
            'isEnabled' => $isEnabled,
        ]);
    }

    #[Route('/admin/2fa/disable', name: 'admin_2fa_disable')]
    public function disable(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Pour désactiver, on remet le secret à NULL
        $user->setGoogleAuthenticatorSecret(null);
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'La double authentification a été désactivée.');

        return $this->redirectToRoute('admin_2fa_setup');
    }
}