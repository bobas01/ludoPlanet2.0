<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

final class DebugController
{
    #[Route('/debug-db', name: 'debug_db')]
    public function debugDb(EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();

        return new JsonResponse([
            'DATABASE_URL' => $_ENV['DATABASE_URL'] ?? null,
            'host' => $conn->getParams()['host'] ?? null,
            'port' => $conn->getParams()['port'] ?? null,
            'dbname' => $conn->getParams()['dbname'] ?? null,
        ]);
    }
}
