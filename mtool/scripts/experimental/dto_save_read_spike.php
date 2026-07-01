#!/usr/bin/env php
<?php

declare(strict_types=1);

function app_cli_dto_save_read_spike_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/experimental/dto_save_read_spike.php --sqlite-path=work/feasibility/app-local-sqlite-schema-sample02/app-local.sqlite --output-dir=work/feasibility/dto-save-read-sample02

Options:
  --sqlite-path=PATH   SQLite DB produced by the App-local schema spike
  --output-dir=PATH    output directory for dto-input.json and summary.json
  --help               show this help
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{ok:bool,help:bool,sqlite_path:string,output_dir:string,error:string}
 */
function app_cli_dto_save_read_spike_parse_args(array $argv): array
{
    $sqlitePath = '';
    $outputDir = '';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return ['ok' => true, 'help' => true, 'sqlite_path' => '', 'output_dir' => '', 'error' => ''];
        }
        if (str_starts_with($argument, '--sqlite-path=')) {
            $sqlitePath = trim(substr($argument, strlen('--sqlite-path=')));
            continue;
        }
        if (str_starts_with($argument, '--output-dir=')) {
            $outputDir = trim(substr($argument, strlen('--output-dir=')));
            continue;
        }

        return ['ok' => false, 'help' => false, 'sqlite_path' => '', 'output_dir' => '', 'error' => 'unsupported argument: ' . $argument];
    }

    if ($sqlitePath === '') {
        return ['ok' => false, 'help' => false, 'sqlite_path' => '', 'output_dir' => '', 'error' => '--sqlite-path=... is required.'];
    }
    if ($outputDir === '') {
        return ['ok' => false, 'help' => false, 'sqlite_path' => '', 'output_dir' => '', 'error' => '--output-dir=... is required.'];
    }

    return ['ok' => true, 'help' => false, 'sqlite_path' => $sqlitePath, 'output_dir' => $outputDir, 'error' => ''];
}

/**
 * @return array<string,mixed>
 */
function app_dto_save_read_spike_fixture_dto(): array
{
    return [
        'id' => 1001,
        'title' => 'Draft local task',
        'status' => 'draft',
        'sortOrder' => 10,
        'isPinned' => false,
        'publishedAt' => null,
        'note' => 'saved by DTO Save/Read Spike',
    ];
}

/**
 * @return array<string,mixed>
 */
function app_dto_save_read_spike_default_dto(): array
{
    return [
        'id' => 1002,
        'title' => 'Defaulted local task',
        'status' => 'draft',
        'sortOrder' => 0,
        'isPinned' => false,
        'publishedAt' => null,
        'note' => null,
    ];
}

/**
 * @return array<string,mixed>
 */
function app_dto_save_read_spike_save_and_read(PDO $pdo, array $dto): array
{
    $pdo->prepare('DELETE FROM task WHERE id = :id')->execute([':id' => $dto['id']]);
    $statement = $pdo->prepare(
        'INSERT INTO task (
            id,
            title,
            status,
            sort_order,
            is_pinned,
            published_at,
            note,
            dirty,
            sync_status
        ) VALUES (
            :id,
            :title,
            :status,
            :sort_order,
            :is_pinned,
            :published_at,
            :note,
            1,
            :sync_status
        )'
    );
    $statement->execute([
        ':id' => $dto['id'],
        ':title' => $dto['title'],
        ':status' => $dto['status'],
        ':sort_order' => $dto['sortOrder'],
        ':is_pinned' => ((bool) $dto['isPinned']) ? 1 : 0,
        ':published_at' => $dto['publishedAt'],
        ':note' => $dto['note'],
        ':sync_status' => 'dirty',
    ]);

    $rowStatement = $pdo->prepare('SELECT * FROM task WHERE id = :id');
    $rowStatement->execute([':id' => $dto['id']]);
    $row = $rowStatement->fetch(PDO::FETCH_ASSOC);
    if (!is_array($row)) {
        throw new RuntimeException('saved row was not found: ' . (string) $dto['id']);
    }

    $readDto = [
        'id' => (int) $row['id'],
        'title' => (string) $row['title'],
        'status' => (string) $row['status'],
        'sortOrder' => (int) $row['sort_order'],
        'isPinned' => ((int) $row['is_pinned']) === 1,
        'publishedAt' => $row['published_at'] !== null ? (string) $row['published_at'] : null,
        'note' => $row['note'] !== null ? (string) $row['note'] : null,
    ];

    return [
        'input_dto' => $dto,
        'read_dto' => $readDto,
        'match' => $dto === $readDto,
        'local_metadata' => [
            'dirty' => (int) $row['dirty'],
            'sync_status' => (string) $row['sync_status'],
            'tombstone' => (int) $row['tombstone'],
            'local_updated_at_present' => trim((string) $row['local_updated_at']) !== '',
            'last_synced_at' => $row['last_synced_at'],
        ],
    ];
}

function app_dto_save_read_spike_write_json(string $path, array $payload): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('failed to create output directory: ' . $dir);
    }

    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if (!is_string($json)) {
        throw new RuntimeException('failed to encode JSON: ' . json_last_error_msg());
    }
    if (file_put_contents($path, $json . PHP_EOL) === false) {
        throw new RuntimeException('failed to write: ' . $path);
    }
}

$parsed = app_cli_dto_save_read_spike_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_dto_save_read_spike_usage() . PHP_EOL);
    exit(0);
}
if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_dto_save_read_spike_usage() . PHP_EOL);
    exit(64);
}

try {
    if (!is_file($parsed['sqlite_path'])) {
        throw new RuntimeException('SQLite DB does not exist: ' . $parsed['sqlite_path']);
    }

    $pdo = new PDO('sqlite:' . $parsed['sqlite_path']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $roundTrips = [
        app_dto_save_read_spike_save_and_read($pdo, app_dto_save_read_spike_fixture_dto()),
        app_dto_save_read_spike_save_and_read($pdo, app_dto_save_read_spike_default_dto()),
    ];
    $allMatch = array_reduce(
        $roundTrips,
        static fn (bool $carry, array $roundTrip): bool => $carry && (bool) $roundTrip['match'],
        true,
    );

    $summary = [
        'ok' => $allMatch,
        'sqlite_path' => $parsed['sqlite_path'],
        'round_trip_count' => count($roundTrips),
        'round_trips' => $roundTrips,
        'conclusion' => $allMatch
            ? 'DTO-shaped rows can be saved to and read from the App-local SQLite schema without shape loss for key/default/null fields in this minimal spike.'
            : 'DTO round trip lost shape; inspect round_trips.',
        'runtime_note' => 'PHP PDO is used to avoid making a Node SQLite library choice dominate the feasibility result. TypeScript/Node runtime selection remains a later decision.',
    ];

    $outputDir = rtrim($parsed['output_dir'], '/');
    app_dto_save_read_spike_write_json($outputDir . '/dto-input.json', [
        'dtos' => array_map(static fn (array $roundTrip): array => $roundTrip['input_dto'], $roundTrips),
    ]);
    app_dto_save_read_spike_write_json($outputDir . '/summary.json', $summary);

    fwrite(STDOUT, json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);
    exit($allMatch ? 0 : 1);
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
}
