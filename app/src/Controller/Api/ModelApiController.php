<?php

namespace App\Controller\Api;

use App\Api\Exception\ValidationException;
use App\Api\Schema\SchemaInterface;
use App\Api\Schema\SchemaRegistry;
use App\Api\Service\ModelService;
use App\Controller\BaseController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

/**
 * Generic, schema-driven CRUD + describe endpoints for exposed models.
 *
 * Lives under the existing ^/api firewall (stateless Keycloak bearer, ROLE_EDITOR).
 */
class ModelApiController extends BaseController
{
    public function __construct(
        ContainerInterface $container,
        private readonly SchemaRegistry $registry,
        private readonly ModelService $service,
    ) {
        parent::__construct($container);
    }

    #[Route('/api/model/{key}/schema', name: 'api_model_schema', methods: ['GET'])]
    public function schema(string $key): JsonResponse
    {
        $schema = $this->registry->get($key);

        return $this->json($this->service->describe($schema));
    }

    #[Route('/api/model/{key}', name: 'api_model_find', methods: ['GET'])]
    public function findAll(string $key, Request $request): JsonResponse
    {
        $schema = $this->requireOperation($key, SchemaInterface::OP_FIND_ALL);

        return $this->run(fn () => $this->json($this->service->findAll($schema, $request)));
    }

    #[Route('/api/model/{key}/{id}', name: 'api_model_find_one', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function findOne(string $key, int $id): JsonResponse
    {
        $schema = $this->requireOperation($key, SchemaInterface::OP_FIND_ONE);

        return $this->run(fn () => $this->json($this->service->findOne($schema, $id)));
    }

    #[Route('/api/model/{key}', name: 'api_model_create', methods: ['POST'])]
    public function create(string $key, Request $request): JsonResponse
    {
        $schema = $this->requireOperation($key, SchemaInterface::OP_CREATE);

        return $this->run(function () use ($schema, $request) {
            $data = $this->service->create($schema, $this->decode($request));

            return $this->json($data, Response::HTTP_CREATED);
        });
    }

    #[Route('/api/model/{key}/{id}', name: 'api_model_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(string $key, int $id, Request $request): JsonResponse
    {
        $schema = $this->requireOperation($key, SchemaInterface::OP_UPDATE);

        return $this->run(
            fn () => $this->json($this->service->update($schema, $id, $this->decode($request)))
        );
    }

    #[Route('/api/model/{key}/{id}', name: 'api_model_patch', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function patch(string $key, int $id, Request $request): JsonResponse
    {
        $schema = $this->requireOperation($key, SchemaInterface::OP_PATCH);

        return $this->run(
            fn () => $this->json($this->service->patch($schema, $id, $this->decode($request)))
        );
    }

    #[Route('/api/model/{key}/{id}', name: 'api_model_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(string $key, int $id): JsonResponse
    {
        $schema = $this->requireOperation($key, SchemaInterface::OP_DELETE);

        return $this->run(function () use ($schema, $id) {
            $this->service->delete($schema, $id);

            return $this->jsonSuccess('Deleted', ['id' => $id]);
        });
    }

    /**
     * Resolve the schema and assert the operation is enabled for it.
     */
    private function requireOperation(string $key, string $operation): SchemaInterface
    {
        $schema = $this->registry->get($key);
        if (!in_array($operation, $schema->getAllowedOperations(), true)) {
            throw new NotFoundHttpException(sprintf('Operation "%s" is not allowed for "%s".', $operation, $key));
        }

        return $schema;
    }

    /**
     * Uniform error handling: validation failures -> jsonFail with field errors,
     * missing records -> jsonFail 404, anything else -> jsonError.
     *
     * @param callable():JsonResponse $action
     */
    private function run(callable $action): JsonResponse
    {
        try {
            return $action();
        } catch (ValidationException $e) {
            return $this->jsonFail($e->getMessage(), $e->getErrors());
        } catch (NotFoundHttpException $e) {
            return $this->jsonStatus('fail', $e->getMessage(), null, Response::HTTP_NOT_FOUND);
        } catch (Throwable $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            throw new ValidationException([], 'Invalid JSON payload');
        }

        return $data;
    }
}
