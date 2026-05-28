<?php

namespace App\Controller;

use App\Service\Lookup\LookupService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LookupServiceController extends BaseController
{
    private const OPERATION_MAP = [
        'lookup' => ['route' => 'lookup_service_lookup', 'method' => 'GET',    'params' => [],            'query' => ['q' => '{text}']],
        'create' => ['route' => 'lookup_service_create', 'method' => 'POST',   'params' => [],           'query' => []],
        'update' => ['route' => 'lookup_service_update', 'method' => 'PUT',    'params' => ['id'=>'{id}'],'query' => []],
        'delete' => ['route' => 'lookup_service_delete', 'method' => 'DELETE', 'params' => ['id'=>'{id}'],'query' => []],
    ];

    #[Route('/api/lookup/{modelName}', name: 'lookup_service_info', methods: ['GET'])]
    public function lookupServiceInfo(string $modelName): JsonResponse
    {
        $lookupService = LookupService::factory($modelName);
        $info = $lookupService->getInfo();

        $params = ['modelName' => $modelName];
        $info['uri'] = $this->generateUrl('lookup_service_info', $params, UrlGeneratorInterface::ABSOLUTE_URL);

        $info['operations'] = [];
        foreach ($info['capabilities'] as $capability) {
            $def = self::OPERATION_MAP[$capability];
            $routeParams = array_merge($params, $def['params']);
            $uri = urldecode($this->generateUrl($def['route'], $routeParams, UrlGeneratorInterface::ABSOLUTE_URL));
            if (!empty($def['query'])) {
                $uri .= '?' . urldecode(http_build_query($def['query']));
            }
            $info['operations'][$capability] = [
                'uri' => $uri,
                'method' => $def['method'],
            ];
        }

        return new JsonResponse($info);
    }

    #[Route('/api/lookup/{modelName}/lookup', name: 'lookup_service_lookup', methods: ['GET'])]
    public function lookup(Request $request, string $modelName): JsonResponse
    {
        $query = $request->query->get('q', '');
        $lookupService = LookupService::factory($modelName);

        return new JsonResponse([
            'data' => $lookupService->lookup($query),
        ]);
    }

    #[Route('/api/lookup/{modelName}', name: 'lookup_service_create', methods: ['POST'])]
    public function create(Request $request, string $modelName): JsonResponse
    {
        $lookupService = LookupService::factory($modelName);

        return new JsonResponse(
            $lookupService->create($request->getPayload()->all()),
            Response::HTTP_CREATED
        );
    }

    #[Route('/api/lookup/{modelName}/{id}', name: 'lookup_service_update', methods: ['PUT'])]
    public function update(Request $request, string $modelName, int $id): JsonResponse
    {
        $lookupService = LookupService::factory($modelName);

        return new JsonResponse(
            $lookupService->update($id, $request->getPayload()->all()),
        );
    }

    #[Route('/api/lookup/{modelName}/{id}', name: 'lookup_service_delete', methods: ['DELETE'])]
    public function delete(string $modelName, int $id): JsonResponse
    {
        $lookupService = LookupService::factory($modelName);
        $lookupService->delete($id);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
