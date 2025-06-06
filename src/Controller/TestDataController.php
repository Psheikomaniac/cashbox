<?php

namespace App\Controller;

use App\Service\TestDataGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller für die Generierung von Testdaten über API.
 * Nur in der Entwicklungsumgebung verfügbar.
 */
class TestDataController extends AbstractController
{
    public function __construct(
        private readonly TestDataGeneratorService $testDataGenerator
    ) {
    }

    /**
     * Generiert Testdaten über einen API-Endpunkt.
     * Dieser Endpunkt ist nur in der Entwicklungsumgebung verfügbar.
     */
    #[Route('/api/generate-test-data', name: 'app_generate_test_data', methods: ['GET'])]
    public function generateTestData(Request $request): JsonResponse
    {
        // Prüfe, ob wir in der Entwicklungsumgebung sind
        if ($this->getParameter('kernel.environment') !== 'dev') {
            return new JsonResponse(
                ['error' => 'Dieser Endpunkt ist nur in der Entwicklungsumgebung verfügbar.'],
                Response::HTTP_FORBIDDEN
            );
        }

        // Prüfe, ob der Aufruf von localhost oder Docker-Container kommt
        $clientIp = $request->getClientIp();
        $allowedIps = ['127.0.0.1', 'localhost', '::1', '172.17.0.1', 'database', 'php'];

        if (!in_array($clientIp, $allowedIps) && !str_starts_with($clientIp, '172.')) {
            return new JsonResponse(
                ['error' => 'Zugriff nur von lokalen oder Docker-Adressen erlaubt.'],
                Response::HTTP_FORBIDDEN
            );
        }

        try {
            // Hole die Menge aus dem Query-Parameter oder verwende den Standardwert
            $amount = $request->query->get('amount', 'medium');

            // Generiere die Testdaten
            $result = $this->testDataGenerator->generate($amount);

            // Erfolgsantwort zurückgeben
            return new JsonResponse([
                'success' => true,
                'message' => sprintf(
                    'Erfolgreich generiert: %d Benutzer, %d Teams, %d Strafen, %d Zahlungen',
                    $result['users'],
                    $result['teams'],
                    $result['penalties'],
                    $result['payments']
                ),
                'data' => $result
            ]);
        } catch (\Exception $e) {
            // Fehlerantwort zurückgeben
            return new JsonResponse(
                ['error' => 'Fehler beim Generieren der Testdaten: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Einfacher Gesundheitscheck-Endpunkt für Docker-Healthchecks.
     */
    #[Route('/health', name: 'app_health_check', methods: ['GET'])]
    public function healthCheck(): JsonResponse
    {
        return new JsonResponse(['status' => 'ok']);
    }
}
