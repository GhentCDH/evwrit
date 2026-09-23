<?php

namespace App\Command;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

/**
 * Thorough HTTP test suite for the lexis annotation model.
 *
 * Against a running server it:
 *   1. loads the lexis schema,
 *   2. verifies every lookup service by fetching its records,
 *   3. asserts an empty payload is rejected,
 *   4. asserts random (non-existent) lookup ids are rejected,
 *   5. builds a valid record: mints a fresh id/name row for each lookup and posts a
 *      lexis record referencing them plus a text selector.
 *
 * Created rows are deleted afterwards unless --keep is passed.
 *
 * Example: php bin/console app:api:test-lexis
 */
class ApiLexisSuiteCommand extends Command
{
    public function __construct()
    {
        parent::__construct('app:api:test-lexis');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Thorough HTTP suite for the lexis model (lookups, validation, valid create).')
            ->addOption('key', null, InputOption::VALUE_REQUIRED, 'Lexis schema key', 'lexis_annotation')
            ->addOption('url', null, InputOption::VALUE_REQUIRED, 'Base URL of the API', 'http://127.0.0.1:8000')
            ->addOption('source-id', null, InputOption::VALUE_REQUIRED, 'TextSelection source_id (text id) for the selector', '4049')
            ->addOption('token', null, InputOption::VALUE_REQUIRED, 'Bearer token for a secured (non-dev) API')
            ->addOption('keep', null, InputOption::VALUE_NONE, 'Do not delete the records created during the run')
            ->setHelp(
                "Exercises the full lexis create path over HTTP against a running server.\n".
                "The dev firewall is PUBLIC_ACCESS, so no --token is needed there."
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $key = (string) $input->getOption('key');
        $baseUrl = rtrim((string) $input->getOption('url'), '/');
        $sourceId = (int) $input->getOption('source-id');
        $keep = (bool) $input->getOption('keep');

        $this->headers = ['Accept' => 'application/json'];
        $token = $input->getOption('token');
        if (is_string($token) && $token !== '') {
            $this->headers['Authorization'] = 'Bearer '.$token;
        }

        $client = HttpClient::create();
        $io->title(sprintf('Lexis suite against %s (key: %s)', $baseUrl, $key));

        $ok = true;
        /** @var array<int, array{method: string, uri: string, id: int|string, label: string}> $createdLookups */
        $createdLookups = [];
        $createdLexisId = null;
        $lexisDelete = null;

        try {
            // 1. Load the lexis schema.
            [$status, $schema] = $this->send($client, 'GET', $baseUrl.'/api/model/'.rawurlencode($key).'/schema', null);
            $this->assertStatus($status, [200], 'GET lexis schema');
            $operations = $schema['operations'] ?? [];
            $columns = $schema['columns'] ?? [];
            $lexisCreate = $this->requireOp($operations, 'create', 'lexis');
            $lexisFindOne = $this->requireOp($operations, 'findOne', 'lexis');
            $lexisDelete = $this->requireOp($operations, 'delete', 'lexis');
            $io->writeln(sprintf(' <info>✓</info> loaded schema (%d columns)', count($columns)));

            // Identify lookup columns (autocomplete + resource URL).
            $lookups = $this->lookupColumns($columns);
            if ($lookups === []) {
                throw new RuntimeException('no lookup columns found in the lexis schema');
            }
            $io->writeln(sprintf(' <info>✓</info> found %d lookup columns', count($lookups)));

            // 2. Verify each lookup service by fetching records; remember create/delete ops.
            $io->section('Lookup services');
            /** @var array<string, array{create: array{method:string,uri:string}, delete: array{method:string,uri:string}}> $lookupOps */
            $lookupOps = [];
            foreach ($lookups as $col => $resource) {
                try {
                    [$s, $lookupSchema] = $this->send($client, 'GET', $this->resolveUrl($baseUrl, $resource), null);
                    $this->assertStatus($s, [200], sprintf('GET %s schema', $col));
                    $lookupOps[$col] = [
                        'create' => $this->requireOp($lookupSchema['operations'] ?? [], 'create', $col),
                        'delete' => $this->requireOp($lookupSchema['operations'] ?? [], 'delete', $col),
                    ];
                    $findAll = $this->requireOp($lookupSchema['operations'] ?? [], 'findAll', $col);

                    [$s2, $body] = $this->send($client, $findAll['method'], $this->resolveUrl($baseUrl, $findAll['uri']), null);
                    $this->assertStatus($s2, [200], sprintf('GET %s records', $col));
                    // findAll returns the crouton envelope {data, request}.
                    $rows = $body['data'] ?? $body;
                    if (!array_is_list($rows)) {
                        throw new RuntimeException(sprintf('%s: expected a list of records', $col));
                    }
                    $io->writeln(sprintf(' <info>✓</info> %-14s %d records', $col, count($rows)));
                } catch (Throwable $e) {
                    $ok = false;
                    $io->writeln(sprintf(' <error>✗ %s</error>', $e->getMessage()));
                }
            }

            // 3. Empty payload must be rejected.
            $io->section('Validation (must fail)');
            $ok = $this->assertRejected($client, $baseUrl, $lexisCreate, [], $io, 'empty object') && $ok;

            // 4. Random lookup ids (> 10000) must be rejected.
            $randomPayload = [];
            foreach (array_keys($lookups) as $col) {
                $randomPayload[$col] = ['id' => random_int(10001, 99999)];
            }
            $randomPayload['selector'] = $this->selector($sourceId);
            $ok = $this->assertRejected($client, $baseUrl, $lexisCreate, $randomPayload, $io, 'random lookup ids') && $ok;

            // 5. Build a valid record.
            $io->section('Valid record');
            $payload = [];
            foreach ($lookupOps as $col => $ops) {
                $label = sprintf('smoke-%s-%s', $col, uniqid());
                [$s, $row] = $this->send($client, $ops['create']['method'], $this->resolveUrl($baseUrl, $ops['create']['uri']), ['label' => $label]);
                $this->assertStatus($s, [200, 201], sprintf('POST %s', $col));
                $id = $row['id'] ?? null;
                if ($id === null) {
                    throw new RuntimeException(sprintf('%s create returned no id', $col));
                }
                $createdLookups[] = ['method' => $ops['delete']['method'], 'uri' => $ops['delete']['uri'], 'id' => $id, 'label' => $label];
                $payload[$col] = ['id' => $id];
                $io->writeln(sprintf(' <info>✓</info> minted %-14s id=%s (%s)', $col, $id, $label));
            }
            $payload['selector'] = $this->selector($sourceId);

            [$s, $record] = $this->send($client, $lexisCreate['method'], $this->resolveUrl($baseUrl, $lexisCreate['uri']), $payload);
            $this->assertStatus($s, [200, 201], 'POST lexis', $record);
            $createdLexisId = $record['id'] ?? null;
            if ($createdLexisId === null) {
                throw new RuntimeException('lexis create returned no id');
            }
            $io->writeln(sprintf(' <info>✓</info> created lexis record id=%s', $createdLexisId));

            // Confirm it round-trips.
            [$s, $found] = $this->send($client, $lexisFindOne['method'], $this->fillId($baseUrl, $lexisFindOne['uri'], $createdLexisId), null);
            $this->assertStatus($s, [200], 'GET lexis record');
            $io->writeln(sprintf(' <info>✓</info> fetched lexis record id=%s', $found['id'] ?? '?'));
        } catch (Throwable $e) {
            $ok = false;
            $io->writeln(sprintf(' <error>✗ %s</error>', $e->getMessage()));
        } finally {
            if (!$keep) {
                $this->cleanup($client, $io, $baseUrl, $lexisDelete, $createdLexisId, $createdLookups);
            } elseif ($createdLexisId !== null || $createdLookups !== []) {
                $io->writeln(' <comment>—</comment> cleanup skipped (--keep)');
            }
        }

        if ($ok) {
            $io->success('All checks passed.');

            return Command::SUCCESS;
        }

        $io->error('Some checks failed.');

        return Command::FAILURE;
    }

    /** @var array<string, string> */
    private array $headers = [];

    /**
     * @return array<string, string> map of column id => lookup resource (schema) URL
     */
    private function lookupColumns(array $columns): array
    {
        $lookups = [];
        foreach ($columns as $id => $col) {
            if (($col['fieldInput']['type'] ?? null) !== 'autocomplete') {
                continue;
            }
            $resource = $col['fieldInput']['options']['resource'] ?? null;
            if (is_string($resource) && $resource !== '') {
                $lookups[(string) $id] = $resource;
            }
        }

        return $lookups;
    }

    /**
     * @return array{source_id: int, start: int, end: int, exact: string}
     */
    private function selector(int $sourceId): array
    {
        $start = random_int(1, 1000);

        return [
            'source_id' => $sourceId,
            'start' => $start,
            'end' => $start + random_int(1, 50),
            'exact' => 'test',
        ];
    }

    /**
     * @param array{method: string, uri: string} $op
     * @param array<string, mixed>                $payload
     */
    private function assertRejected(HttpClientInterface $client, string $baseUrl, array $op, array $payload, SymfonyStyle $io, string $label): bool
    {
        try {
            [$status] = $this->send($client, $op['method'], $this->resolveUrl($baseUrl, $op['uri']), $payload);
            if ($status >= 200 && $status < 300) {
                $io->writeln(sprintf(' <error>✗ %s was accepted (HTTP %d), expected rejection</error>', $label, $status));

                return false;
            }
            $io->writeln(sprintf(' <info>✓</info> %-16s rejected (HTTP %d)', $label, $status));

            return true;
        } catch (Throwable $e) {
            $io->writeln(sprintf(' <error>✗ %s: %s</error>', $label, $e->getMessage()));

            return false;
        }
    }

    /**
     * @param array<int, array{method: string, uri: string, id: int|string, label: string}> $createdLookups
     * @param array{method: string, uri: string}|null                                        $lexisDelete
     */
    private function cleanup(HttpClientInterface $client, SymfonyStyle $io, string $baseUrl, ?array $lexisDelete, int|string|null $lexisId, array $createdLookups): void
    {
        // Delete the lexis record first: it holds the foreign keys into the lookups.
        if ($lexisId !== null && $lexisDelete !== null) {
            $this->tryDelete($client, $io, $this->fillId($baseUrl, $lexisDelete['uri'], $lexisId), $lexisDelete['method'], sprintf('lexis id=%s', $lexisId));
        }
        foreach ($createdLookups as $row) {
            $this->tryDelete($client, $io, $this->fillId($baseUrl, $row['uri'], $row['id']), $row['method'], sprintf('%s', $row['label']));
        }
    }

    private function tryDelete(HttpClientInterface $client, SymfonyStyle $io, string $url, string $method, string $what): void
    {
        try {
            [$status] = $this->send($client, $method, $url, null);
            if ($status >= 200 && $status < 300) {
                $io->writeln(sprintf(' <comment>·</comment> cleaned up %s', $what));
            } else {
                $io->writeln(sprintf(' <comment>·</comment> cleanup of %s -> HTTP %d', $what, $status));
            }
        } catch (Throwable $e) {
            $io->writeln(sprintf(' <comment>·</comment> cleanup of %s failed: %s', $what, $e->getMessage()));
        }
    }

    /**
     * @param array<string, mixed>|null $payload
     *
     * @return array{0: int, 1: array<mixed>} [status, decoded body]
     */
    private function send(HttpClientInterface $client, string $method, string $url, ?array $payload): array
    {
        $options = ['headers' => $this->headers];
        if ($payload !== null) {
            $options['json'] = $payload;
        }
        $response = $client->request(strtoupper($method), $url, $options);
        $status = $response->getStatusCode();
        try {
            $body = $response->toArray(false);
        } catch (Throwable) {
            $body = [];
        }

        return [$status, $body];
    }

    /**
     * @param array<string, array{uri: string, method: string}> $operations
     *
     * @return array{method: string, uri: string}
     */
    private function requireOp(array $operations, string $op, string $what): array
    {
        if (!isset($operations[$op]['uri'], $operations[$op]['method'])) {
            throw new RuntimeException(sprintf('%s: operation "%s" is not exposed', $what, $op));
        }

        return ['method' => strtoupper((string) $operations[$op]['method']), 'uri' => (string) $operations[$op]['uri']];
    }

    /**
     * @param int[]        $expected
     * @param array<mixed> $body     response body to include in the failure message
     */
    private function assertStatus(int $status, array $expected, string $what, array $body = []): void
    {
        if (!in_array($status, $expected, true)) {
            $detail = $body === [] ? '' : ': '.json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            throw new RuntimeException(sprintf('%s -> HTTP %d (expected %s)%s', $what, $status, implode('/', $expected), $detail));
        }
    }

    private function resolveUrl(string $baseUrl, string $uri): string
    {
        if (str_starts_with($uri, 'http://') || str_starts_with($uri, 'https://')) {
            return $uri;
        }

        return $baseUrl.$uri;
    }

    private function fillId(string $baseUrl, string $uri, int|string $id): string
    {
        return $this->resolveUrl($baseUrl, str_replace('{id}', rawurlencode((string) $id), $uri));
    }
}
