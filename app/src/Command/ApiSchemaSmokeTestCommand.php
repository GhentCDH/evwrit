<?php

namespace App\Command;

use App\Api\Schema\SchemaInterface;
use App\Api\Schema\SchemaRegistry;
use App\Api\Service\ModelService;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Exercises a schema's CRUD operations end-to-end (create -> findOne -> findAll ->
 * update -> delete) over the in-process service and/or the live HTTP endpoints.
 *
 * Example: php bin/console app:api:test-schema annotation_type_lexis
 */
class ApiSchemaSmokeTestCommand extends Command
{
    public function __construct(
        private readonly SchemaRegistry $registry,
        private readonly ModelService $service,
    ) {
        parent::__construct('app:api:test-schema');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Smoke-test a schema\'s CRUD operations (service and/or HTTP).')
            ->addArgument('key', InputArgument::REQUIRED, 'Schema key, e.g. annotation_type_lexis')
            ->addOption('transport', null, InputOption::VALUE_REQUIRED, 'service | http | both', 'both')
            ->addOption('url', null, InputOption::VALUE_REQUIRED, 'Base URL for the http transport', 'http://127.0.0.1:8000')
            ->addOption('create', null, InputOption::VALUE_REQUIRED, 'Create payload (JSON)', '{"label":"frederic"}')
            ->addOption('update', null, InputOption::VALUE_REQUIRED, 'Update payload (JSON)', '{"label":"freddy"}')
            ->addOption('keep', null, InputOption::VALUE_NONE, 'Do not delete the created record')
            ->setHelp(
                "Runs create -> findOne -> findAll -> update -> findOne -> delete for a schema.\n".
                "The http transport defaults to Symfony's in-container port (http://127.0.0.1:8000)."
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $key = (string) $input->getArgument('key');
        $transport = (string) $input->getOption('transport');
        $keep = (bool) $input->getOption('keep');

        try {
            $schema = $this->registry->get($key);
        } catch (NotFoundHttpException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $create = $this->decode($io, (string) $input->getOption('create'));
        $update = $this->decode($io, (string) $input->getOption('update'));
        if ($create === null || $update === null) {
            return Command::FAILURE;
        }

        $io->title(sprintf('Smoke-testing "%s"', $key));

        $drivers = [];
        if (in_array($transport, ['service', 'both'], true)) {
            $drivers['service'] = new ServiceCrudDriver($this->service, $schema);
        }
        if (in_array($transport, ['http', 'both'], true)) {
            $drivers['http'] = new HttpCrudDriver(HttpClient::create(), rtrim((string) $input->getOption('url'), '/'), $key);
        }
        if ($drivers === []) {
            $io->error(sprintf('Invalid --transport "%s" (use service | http | both).', $transport));

            return Command::FAILURE;
        }

        $ok = true;
        foreach ($drivers as $name => $driver) {
            $io->section(sprintf('Transport: %s', $name));
            $ok = $this->runSequence($driver, $io, $create, $update, $keep) && $ok;
        }

        if ($ok) {
            $io->success('All operations passed.');

            return Command::SUCCESS;
        }

        $io->error('Some operations failed.');

        return Command::FAILURE;
    }

    /**
     * @param array<string, mixed> $create
     * @param array<string, mixed> $update
     */
    private function runSequence(CrudDriver $driver, SymfonyStyle $io, array $create, array $update, bool $keep): bool
    {
        $id = null;
        $deleted = false;

        try {
            $record = $driver->create($create);
            $id = $record['id'] ?? null;
            if ($id === null) {
                throw new RuntimeException('create did not return an id');
            }
            $this->assertMatches($record, $create, 'create');
            $io->writeln(sprintf(' <info>✓</info> create        id=%s %s', $id, $this->fmt($record)));

            $found = $driver->findOne($id);
            $this->assertMatches($found ?? [], $create, 'findOne');
            $io->writeln(sprintf(' <info>✓</info> findOne       %s', $this->fmt($found)));

            $all = $driver->findAll();
            if (!$this->containsId($all, $id)) {
                throw new RuntimeException(sprintf('findAll did not contain id=%s (%d rows)', $id, count($all)));
            }
            $io->writeln(sprintf(' <info>✓</info> findAll       found id=%s among %d rows', $id, count($all)));

            $updated = $driver->update($id, $update);
            $this->assertMatches($updated, $update, 'update');
            $io->writeln(sprintf(' <info>✓</info> update        %s', $this->fmt($updated)));

            $reFound = $driver->findOne($id);
            $this->assertMatches($reFound ?? [], $update, 'findOne (after update)');
            $io->writeln(sprintf(' <info>✓</info> findOne       %s', $this->fmt($reFound)));

            if ($keep) {
                $io->writeln(sprintf(' <comment>—</comment> delete        skipped (--keep); row id=%s left in place', $id));

                return true;
            }

            $driver->delete($id);
            $deleted = true;
            if ($driver->findOne($id) !== null) {
                throw new RuntimeException(sprintf('record id=%s still present after delete', $id));
            }
            $io->writeln(sprintf(' <info>✓</info> delete        id=%s removed', $id));

            return true;
        } catch (Throwable $e) {
            $io->writeln(sprintf(' <error>✗ %s</error>', $e->getMessage()));

            return false;
        } finally {
            // Clean up on failure so no orphan row is left behind.
            if ($id !== null && !$deleted && !$keep) {
                try {
                    $driver->delete($id);
                    $io->writeln(sprintf(' <comment>·</comment> cleaned up    id=%s', $id));
                } catch (Throwable) {
                    // best effort
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $record
     * @param array<string, mixed> $expected
     */
    private function assertMatches(array $record, array $expected, string $step): void
    {
        foreach ($expected as $key => $value) {
            $actual = $record[$key] ?? null;
            if ($actual !== $value) {
                throw new RuntimeException(sprintf(
                    '%s: "%s" expected %s, got %s',
                    $step,
                    $key,
                    json_encode($value),
                    json_encode($actual)
                ));
            }
        }
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function containsId(array $rows, mixed $id): bool
    {
        foreach ($rows as $row) {
            if (is_array($row) && ($row['id'] ?? null) == $id) {
                return true;
            }
        }

        return false;
    }

    private function fmt(?array $record): string
    {
        return $record === null ? 'null' : json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decode(SymfonyStyle $io, string $json): ?array
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            $io->error(sprintf('Invalid JSON payload: %s', $json));

            return null;
        }

        return $data;
    }
}

/**
 * Minimal CRUD contract shared by the service and HTTP transports.
 */
interface CrudDriver
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function create(array $payload): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findOne(mixed $id): ?array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAll(): array;

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function update(mixed $id, array $payload): array;

    public function delete(mixed $id): void;
}

/**
 * Drives the operations in-process through ModelService.
 */
class ServiceCrudDriver implements CrudDriver
{
    public function __construct(
        private readonly ModelService $service,
        private readonly SchemaInterface $schema,
    ) {
    }

    public function create(array $payload): array
    {
        return $this->service->create($this->schema, $payload);
    }

    public function findOne(mixed $id): ?array
    {
        try {
            return $this->service->findOne($this->schema, (int) $id);
        } catch (NotFoundHttpException) {
            return null;
        }
    }

    public function findAll(): array
    {
        return $this->service->findAll($this->schema, new Request());
    }

    public function update(mixed $id, array $payload): array
    {
        return $this->service->update($this->schema, (int) $id, $payload);
    }

    public function delete(mixed $id): void
    {
        $this->service->delete($this->schema, (int) $id);
    }
}

/**
 * Drives the operations over HTTP, discovering the endpoints from the schema output.
 */
class HttpCrudDriver implements CrudDriver
{
    /** @var array<string, array{uri: string, method: string}>|null */
    private ?array $operations = null;

    public function __construct(
        private readonly \Symfony\Contracts\HttpClient\HttpClientInterface $client,
        private readonly string $baseUrl,
        private readonly string $key,
    ) {
    }

    public function create(array $payload): array
    {
        [$method, $uri] = $this->op(SchemaInterface::OP_CREATE);

        return $this->send($method, $uri, $payload, [200, 201]);
    }

    public function findOne(mixed $id): ?array
    {
        [$method, $uri] = $this->op(SchemaInterface::OP_FIND_ONE);
        $response = $this->client->request($method, $this->url($uri, $id), ['headers' => ['Accept' => 'application/json']]);
        $status = $response->getStatusCode();
        if ($status === 404) {
            return null;
        }
        $this->assertStatus($status, [200], $method, $uri);

        return $response->toArray(false);
    }

    public function findAll(): array
    {
        [$method, $uri] = $this->op(SchemaInterface::OP_FIND_ALL);

        return $this->send($method, $uri, null, [200]);
    }

    public function update(mixed $id, array $payload): array
    {
        [$method, $uri] = $this->op(SchemaInterface::OP_UPDATE);

        return $this->send($method, $this->url($uri, $id), $payload, [200]);
    }

    public function delete(mixed $id): void
    {
        [$method, $uri] = $this->op(SchemaInterface::OP_DELETE);
        $response = $this->client->request($method, $this->url($uri, $id), ['headers' => ['Accept' => 'application/json']]);
        $this->assertStatus($response->getStatusCode(), [200, 204], $method, $uri);
    }

    /**
     * @param array<string, mixed>|null $payload
     * @param int[]                     $expectStatus
     *
     * @return array<string, mixed>
     */
    private function send(string $method, string $url, ?array $payload, array $expectStatus): array
    {
        $options = ['headers' => ['Accept' => 'application/json']];
        if ($payload !== null) {
            $options['json'] = $payload;
        }
        $response = $this->client->request($method, $this->isAbsolute($url) ? $url : $this->baseUrl.$url, $options);
        $this->assertStatus($response->getStatusCode(), $expectStatus, $method, $url);

        return $response->toArray(false);
    }

    /**
     * @return array{0: string, 1: string} [method, uri]
     */
    private function op(string $op): array
    {
        if ($this->operations === null) {
            $describe = $this->client
                ->request('GET', sprintf('%s/api/model/%s/schema', $this->baseUrl, $this->key), ['headers' => ['Accept' => 'application/json']])
                ->toArray(false);
            $this->operations = $describe['operations'] ?? [];
        }

        if (!isset($this->operations[$op])) {
            throw new RuntimeException(sprintf('Operation "%s" is not exposed by the schema.', $op));
        }

        return [strtoupper($this->operations[$op]['method']), $this->operations[$op]['uri']];
    }

    private function url(string $uri, mixed $id): string
    {
        $uri = str_replace('{id}', rawurlencode((string) $id), $uri);

        return $this->isAbsolute($uri) ? $uri : $this->baseUrl.$uri;
    }

    private function isAbsolute(string $uri): bool
    {
        return str_starts_with($uri, 'http://') || str_starts_with($uri, 'https://');
    }

    /**
     * @param int[] $expected
     */
    private function assertStatus(int $status, array $expected, string $method, string $uri): void
    {
        if (!in_array($status, $expected, true)) {
            throw new RuntimeException(sprintf('%s %s -> HTTP %d (expected %s)', $method, $uri, $status, implode('/', $expected)));
        }
    }
}
