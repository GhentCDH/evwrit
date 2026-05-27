<?php

namespace App\Controller;

use App\Service\Lookup\LookupService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

class LookupServiceController extends BaseController
{
    private array $allowedModels = [];
    private array $allowedModelPrefixes = [];

    public function __construct(
    ) {
    }

    #[Route('/api/lookup/{modelName}/info.json', name: 'lookup_service_info', methods: ['GET'])]
    public function lookupServiceInfo(Request $request, string $modelName): ?JsonResponse
    {
        try {

            $lookupService = LookupService::factory($modelName);

            $ret = [
                'id' => 'lookup_service_' . $modelName,
                'uri' => $this->generateUrl('lookup_service_info', ['modelName' => $modelName]),
                'operations' => [
                    'lookup' => $this->generateUrl('lookup_service_lookup', ['model' => $modelName]) . "?q={text}",
                    'create' => [
                        'uri' => $this->generateUrl('lookup_service_create', ['model' => $modelName]),
                        'method' => 'POST',
                    ],
                ],
                'schema' => [
                    'data' => [
                        '$schema' => 'http://json-schema.org/draft-07/schema#',
                        'type' => 'object',
                        'properties' => [
                            'name' => [
                                'type' => 'string',
                                'minLength' => 1,
                            ]
                        ],
                        'required' => [
                            "name"
                        ]
                    ],
                ]
            ];

            return new JsonResponse($ret);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    // create a lookup route that expects a querystring parameter q
    #[Route('/api/lookup/{modelName}', name: 'lookup_service_lookup', methods: ['GET'])]
    public function lookup(Request $request, string $modelName): JsonResponse
    {
        $query = $request->query->get('q');

        try {
            $lookupService = LookupService::factory($modelName);
            $results = $lookupService->lookup($query);

            $ret = [
                'data' => $results,
            ];

            return new JsonResponse($ret);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/lookup/{modelName}', name: 'lookup_service_create', methods: ['POST'])]
    public function create(Request $request, string $modelName): JsonResponse
    {
        try {
            $name = $request->getPayload()->get('name');
            if (empty($name) || !is_string($name)) {
                throw new \InvalidArgumentException("Invalid or missing 'name' property in request body");
            }

            $lookupService = LookupService::factory($modelName);
            $ret = $lookupService->create($name);

            return new JsonResponse($ret);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch ()
        catch (Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

}
