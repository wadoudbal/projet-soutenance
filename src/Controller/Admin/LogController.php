<?php

namespace App\Controller\Admin;

use App\Document\ActivityLog;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogController extends AbstractController
{
    #[Route('/admin/logs', name: 'admin_logs')]
    public function index(DocumentManager $dm): Response
    {
        // On récupère les 50 derniers logs de MongoDB triés par date
        $logs = $dm->getRepository(ActivityLog::class)->findBy([], ['createdAt' => 'DESC'], 50);

        return $this->render('admin/logs.html.twig', [
            'logs' => $logs,
        ]);
    }
}