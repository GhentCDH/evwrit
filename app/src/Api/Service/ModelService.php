<?php

namespace App\Api\Service;

use App\Api\Exception\ValidationException;
use App\Api\Schema\Field\EmbeddedRelationField;
use App\Api\Schema\Field\FieldInterface;
use App\Api\Schema\Field\RelationField;
use App\Api\Schema\FieldCollector;
use App\Api\Schema\SchemaInterface;
use App\Api\Schema\SchemaRegistry;
use App\Api\Schema\SchemaResourceResolver;
use App\Model\AbstractModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Generic, schema-driven CRUD + description service for exposed models.
 */
class ModelService
{
    /** @var array<string, array{route: string, method: string, id: bool}> */
    private const OPERATIONS = [
        SchemaInterface::OP_FIND_ALL => ['route' => 'api_model_find', 'method' => 'GET', 'id' => false],
        SchemaInterface::OP_FIND_ONE => ['route' => 'api_model_find_one', 'method' => 'GET', 'id' => true],
        SchemaInterface::OP_CREATE => ['route' => 'api_model_create', 'method' => 'POST', 'id' => false],
        SchemaInterface::OP_UPDATE => ['route' => 'api_model_update', 'method' => 'PUT', 'id' => true],
        SchemaInterface::OP_PATCH => ['route' => 'api_model_patch', 'method' => 'PATCH', 'id' => true],
        SchemaInterface::OP_DELETE => ['route' => 'api_model_delete', 'method' => 'DELETE', 'id' => true],
    ];

    // Numeric so it satisfies the {id} = \d+ route requirement, then swapped for {id}.
    private const ID_PLACEHOLDER = '900000000009';

    public function __construct(
        private readonly RouterInterface $router,
        private readonly ValidatorInterface $validator,
        private readonly SchemaRegistry $registry,
    ) {
    }

    // ---- directory -------------------------------------------------------------

    /**
     * List the registered services as {id, label, uri} (uri = schema descriptor).
     * Only listed schemas unless $all; keeps registry order.
     *
     * @return array<int, array{id: string, label: string, uri: string}>
     */
    public function listServices(bool $all = false): array
    {
        $out = [];
        foreach ($this->registry->all() as $schema) {
            if (!$all && !$schema->isListed()) {
                continue;
            }
            $out[] = [
                'id' => $schema->getKey(),
                'label' => $schema->getName(),
                'uri' => $this->router->generate('api_model_schema', ['key' => $schema->getKey()]),
            ];
        }

        return $out;
    }

    // ---- describe --------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    public function describe(SchemaInterface $schema): array
    {
        $operations = [];
        foreach ($schema->getAllowedOperations() as $op) {
            if (!isset(self::OPERATIONS[$op])) {
                continue;
            }
            $operations[$op] = [
                'uri' => $this->operationUri($schema->getKey(), $op),
                'method' => self::OPERATIONS[$op]['method'],
            ];
        }

        $resources = new SchemaResourceResolver($this->registry, $this->router);

        $columns = [];
        foreach ($schema->getFields() as $field) {
            if ($field instanceof EmbeddedRelationField && $field->isFlattened()) {
                foreach ($field->getSubFields() as $sub) {
                    $columns[$field->exposedKey($sub->getId())] = $sub->toSchemaArray($this->router, $resources);
                }
                continue;
            }
            $columns[$field->getId()] = $field->toSchemaArray($this->router, $resources);
        }

        // Custom root attributes first; fixed keys are authoritative and override.
        $out = array_merge($schema->getExtra(), [
            'id' => $schema->getKey(),
            'name' => $schema->getName(),
            'kind' => $schema->getKind(),
            'operations' => $operations,
        ]);

        if ($schema->getMetadata() !== []) {
            $out['annotation'] = $schema->getMetadata();
        }

        $out['columns'] = $columns;

        return $out;
    }

    private function operationUri(string $key, string $op): string
    {
        $params = ['key' => $key];
        if (self::OPERATIONS[$op]['id']) {
            $params['id'] = self::ID_PLACEHOLDER;
        }

        $uri = $this->router->generate(self::OPERATIONS[$op]['route'], $params);

        return str_replace(self::ID_PLACEHOLDER, '{id}', $uri);
    }

    // ---- read ------------------------------------------------------------------

    /**
     * List rows in the crouton envelope: {data: [...], request: {count, page, pageSize,
     * totalPages}}. Supports crouton `page`/`pageSize` (and legacy `limit`/`offset`); an
     * omitted pageSize returns all matching rows in a single page. Schema-specific filters
     * (e.g. annotations' `source_id`) are applied via SchemaInterface::applyListFilters().
     *
     * @return array{data: array<int, array<string, mixed>>, request: array{count: int, page: int, pageSize: int, totalPages: int}}
     */
    public function findAll(SchemaInterface $schema, Request $request): array
    {
        $modelClass = $schema->getModelClass();
        $query = $modelClass::query()->with($schema->getEagerRelations());

        $schema->applyListFilters($query, $request->query->all());

        $count = $query->count();

        $pageSize = (int) ($request->query->get('pageSize') ?? $request->query->get('limit') ?? 0);
        $page = max(1, (int) $request->query->get('page', 1));
        $offset = $request->query->has('offset')
            ? max(0, (int) $request->query->get('offset'))
            : ($pageSize > 0 ? ($page - 1) * $pageSize : 0);
        if ($pageSize > 0) {
            $query->limit($pageSize)->offset($offset);
        }

        $rows = $query->get()
            ->map(fn (AbstractModel $model) => $this->serialize($schema, $model))
            ->all();

        return [
            'data' => $rows,
            'request' => [
                'count' => $count,
                'page' => $page,
                'pageSize' => $pageSize,
                'totalPages' => $pageSize > 0 ? (int) max(1, (int) ceil($count / $pageSize)) : 1,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function findOne(SchemaInterface $schema, int $id): array
    {
        return $this->serialize($schema, $this->findOrFail($schema, $id));
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(SchemaInterface $schema, AbstractModel $model): array
    {
        $out = ['id' => $model->getKey()];

        foreach ($schema->getFields() as $field) {
            if ($field instanceof EmbeddedRelationField && $field->isFlattened()) {
                $value = $field->readValue($model);
                foreach ($field->getSubFields() as $sub) {
                    $out[$field->exposedKey($sub->getId())] = is_array($value) ? ($value[$sub->getId()] ?? null) : null;
                }
                if ($field->isLinkExposed()) {
                    $out[$field->getForeignKey()] = is_array($value) ? ($value['id'] ?? null) : null;
                }
                continue;
            }
            $out[$field->getId()] = $field->readValue($model);
        }

        return $out;
    }

    // ---- write -----------------------------------------------------------------

    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    public function create(SchemaInterface $schema, array $input): array
    {
        $this->validate($schema, $input, SchemaInterface::OP_CREATE);

        $modelClass = $schema->getModelClass();
        /** @var AbstractModel $model */
        $model = new $modelClass();

        return $this->persist($schema, $model, $input, SchemaInterface::OP_CREATE);
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    public function update(SchemaInterface $schema, int $id, array $input): array
    {
        $this->validate($schema, $input, SchemaInterface::OP_UPDATE);
        $model = $this->findOrFail($schema, $id);

        return $this->persist($schema, $model, $input, SchemaInterface::OP_UPDATE);
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    public function patch(SchemaInterface $schema, int $id, array $input): array
    {
        $this->validate($schema, $input, SchemaInterface::OP_PATCH);
        $model = $this->findOrFail($schema, $id);

        return $this->persist($schema, $model, $input, SchemaInterface::OP_PATCH);
    }

    public function delete(SchemaInterface $schema, int $id): void
    {
        $model = $this->findOrFail($schema, $id);
        $model->delete();
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    private function persist(SchemaInterface $schema, AbstractModel $model, array $input, string $op): array
    {
        $model->getConnection()->transaction(function () use ($schema, $model, $input, $op): void {
            $this->deserialize($schema, $model, $input, $op);
            // Top-level model hooks run after field writes, before save (embedded sub-model
            // hooks already ran inside their EmbeddedRelationField::writeValue()).
            FieldCollector::runWriteHooks($schema->getWriteHooks(), $model, $op);
            $model->save();
        });

        $fresh = $this->findOrFail($schema, $model->getKey());

        return $this->serialize($schema, $fresh);
    }

    /**
     * Apply an input payload onto a model according to the schema and operation.
     *
     * @param array<string, mixed> $input
     */
    private function deserialize(SchemaInterface $schema, AbstractModel $model, array $input, string $op): void
    {
        foreach ($schema->getFields() as $field) {
            if (!$field->isWritableForOperation($op)) {
                continue;
            }

            if ($field instanceof EmbeddedRelationField && $field->isFlattened()) {
                $this->deserializeFlattened($field, $model, $input, $op);
                continue;
            }

            $key = $field->getId();
            if (array_key_exists($key, $input)) {
                $field->writeValue($model, $input[$key], $op);
                continue;
            }

            // Absent key with a configured default: apply it (create/PUT only, not PATCH).
            if ($field->hasDefault() && $op !== SchemaInterface::OP_PATCH) {
                $field->writeValue($model, $field->getDefault($input), $op);
                continue;
            }

            // Absent key: PUT nulls optionals; PATCH/CREATE leave as-is.
            if ($op === SchemaInterface::OP_UPDATE && !$field->isRequiredForCreate()) {
                $field->writeValue($model, null, $op);
            }
        }
    }

    /**
     * @param array<string, mixed> $input
     */
    private function deserializeFlattened(
        EmbeddedRelationField $field,
        AbstractModel $model,
        array $input,
        string $op,
    ): void {
        $subData = [];
        $present = false;
        foreach ($field->getSubFields() as $sub) {
            $key = $field->exposedKey($sub->getId());
            if (array_key_exists($key, $input)) {
                $subData[$sub->getId()] = $input[$key];
                $present = true;
            }
        }
        if ($field->isLinkExposed() && array_key_exists($field->getForeignKey(), $input)) {
            $subData['id'] = $input[$field->getForeignKey()];
        }

        if (!$present && $op === SchemaInterface::OP_PATCH) {
            return;
        }

        $field->writeValue($model, $present || $subData !== [] ? $subData : null, $op);
    }

    // ---- validation ------------------------------------------------------------

    /**
     * @param array<string, mixed> $input
     *
     * @throws ValidationException
     */
    public function validate(SchemaInterface $schema, array $input, string $op): void
    {
        $errors = $this->validateFields($schema->getFields(), $input, $op, '');

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }

    /**
     * @param FieldInterface[]     $fields
     * @param array<string, mixed> $input
     *
     * @return array<string, string[]>
     */
    private function validateFields(array $fields, array $input, string $op, string $prefix): array
    {
        $errors = [];

        foreach ($fields as $field) {
            if (!$field->isWritableForOperation($op)) {
                continue;
            }

            if ($field instanceof EmbeddedRelationField) {
                $errors += $this->validateEmbedded($field, $input, $op, $prefix);
                continue;
            }

            $key = $field->getId();
            $path = $prefix.$key;

            if (!array_key_exists($key, $input)) {
                if ($op !== SchemaInterface::OP_PATCH && $field->isRequiredForCreate()) {
                    $errors[$path][] = 'This field is required.';
                }
                continue;
            }

            $value = $input[$key];
            if ($field instanceof RelationField) {
                $value = $field->extractId($value);
            }

            if ($value === null && $field->isRequiredForCreate() && $op !== SchemaInterface::OP_PATCH) {
                $errors[$path][] = 'This field is required.';
                continue;
            }

            foreach ($this->validator->validate($value, $field->getConstraints()) as $violation) {
                $errors[$path][] = (string) $violation->getMessage();
            }
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, string[]>
     */
    private function validateEmbedded(EmbeddedRelationField $field, array $input, string $op, string $prefix): array
    {
        // Flattened: sub-fields validated against the (de-prefixed) top-level input.
        if ($field->isFlattened()) {
            $subInput = [];
            foreach ($field->getSubFields() as $sub) {
                $key = $field->exposedKey($sub->getId());
                if (array_key_exists($key, $input)) {
                    $subInput[$sub->getId()] = $input[$key];
                }
            }

            // On a partial PATCH with none of this child's fields present, skip it.
            if ($op === SchemaInterface::OP_PATCH && $subInput === []) {
                return [];
            }

            // The child lifecycle follows the parent operation: PATCH is partial,
            // CREATE/PUT validate the full required set.
            $effectiveOp = $op === SchemaInterface::OP_PATCH
                ? SchemaInterface::OP_PATCH
                : SchemaInterface::OP_CREATE;

            return $this->validateFields($field->getSubFields(), $subInput, $effectiveOp, $prefix.$field->getPrefix());
        }

        // Nested: sub-object under the field key.
        $key = $field->getId();
        $path = $prefix.$key;

        if (!array_key_exists($key, $input)) {
            if ($op !== SchemaInterface::OP_PATCH && $field->isRequiredForCreate()) {
                return [$path => ['This field is required.']];
            }

            return [];
        }

        $subData = $input[$key];
        if ($subData === null) {
            if ($field->isRequiredForCreate() && $op !== SchemaInterface::OP_PATCH) {
                return [$path => ['This field is required.']];
            }

            return [];
        }
        if (!is_array($subData)) {
            return [$path => ['Expected an object.']];
        }

        $effectiveOp = $this->embeddedOp($op, $subData['id'] ?? null, true);

        return $this->validateFields($field->getSubFields(), $subData, $effectiveOp, $path.'.');
    }

    /**
     * Determine how to validate an embedded object: updating an existing row
     * (id present) is patch-like; creating a new one enforces required fields.
     */
    private function embeddedOp(string $op, mixed $id, bool $present): string
    {
        if ($op === SchemaInterface::OP_PATCH && !$present) {
            return SchemaInterface::OP_PATCH;
        }

        return $id !== null ? SchemaInterface::OP_PATCH : SchemaInterface::OP_CREATE;
    }

    private function findOrFail(SchemaInterface $schema, int|string $id): AbstractModel
    {
        $modelClass = $schema->getModelClass();
        /** @var AbstractModel|null $model */
        $model = $modelClass::query()->with($schema->getEagerRelations())->find($id);

        if ($model === null) {
            throw new NotFoundHttpException(sprintf('%s %s not found.', $schema->getName(), $id));
        }

        return $model;
    }
}
