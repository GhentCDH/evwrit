<?php

namespace App\Controller;

use App\Service\ElasticSearch\Client as ElasticClient;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class HealthController extends BaseController
{

    public function __construct(
        private readonly Capsule $capsule,
        private readonly ElasticClient $elasticClient,
    ) {
    }

    #[Route('/health', name: 'health', methods: ['GET'])]
    public function health(Request $request): JsonResponse
    {
        $db = $this->checkDatabase();
        $es = $this->checkElasticsearch();

        $allOk = $db['status'] === 'ok' && $es['status'] === 'ok';

        return new JsonResponse(
            [
                'status' => $allOk ? 'ok' : 'degraded',
                'services' => [
                    'database' => $db,
                    'elasticsearch' => $es,
                ],
            ],
            $allOk ? 200 : 503
        );
    }

    private function checkDatabase(): array
    {
        try {
            $this->capsule->getConnection()->select('SELECT 1');
            return ['status' => 'ok'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkElasticsearch(): array
    {
        try {
            $response = $this->elasticClient->request('/');
            if ($response->isOk()) {
                return ['status' => 'ok'];
            }
            return ['status' => 'error', 'message' => 'Unexpected response: ' . $response->getStatus()];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
