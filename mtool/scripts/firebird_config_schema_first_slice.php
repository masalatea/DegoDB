#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/config_db_bootstrap.php';

/**
 * @param list<string> $argv
 * @return array{help:bool,sql_dir:string,limit:int,apply:bool,pretty:bool,error:string}
 */
function app_cli_firebird_config_schema_first_slice_parse_args(array $argv): array
{
    $parsed = [
        'help' => false,
        'sql_dir' => app_config_db_bootstrap_default_sql_dir(),
        'limit' => 5,
        'apply' => false,
        'pretty' => false,
        'error' => '',
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            $parsed['help'] = true;
            continue;
        }
        if ($argument === '--pretty') {
            $parsed['pretty'] = true;
            continue;
        }
        if ($argument === '--apply') {
            $parsed['apply'] = true;
            continue;
        }
        if (str_starts_with($argument, '--sql-dir=')) {
            $parsed['sql_dir'] = app_config_db_bootstrap_resolve_sql_dir(substr($argument, strlen('--sql-dir=')));
            continue;
        }
        if (str_starts_with($argument, '--limit=')) {
            $parsed['limit'] = max(0, (int) substr($argument, strlen('--limit=')));
            continue;
        }

        $parsed['error'] = 'unsupported argument: ' . $argument;
        return $parsed;
    }

    return $parsed;
}

function app_cli_firebird_config_schema_first_slice_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/firebird_config_schema_first_slice.php [--sql-dir=PATH] [--limit=N] [--apply] [--pretty]

Generate a narrow Firebird CREATE TABLE first-slice plan from MariaDB config-initdb SQL.
With --apply, use MTOOL_FIREBIRD_DSN / USER / PASSWORD against a disposable proof database.
TEXT;
}

/**
 * @return array<string,mixed>
 */
function app_firebird_config_schema_first_slice(string $sqlDir, int $limit, bool $apply): array
{
    $plans = [];
    foreach (app_config_db_bootstrap_sql_files($sqlDir) as $file) {
        $contents = file_get_contents($file);
        if (!is_string($contents)) continue;
        foreach (app_config_db_bootstrap_split_sql_statements($contents) as $statement) {
            $trimmed = trim(preg_replace('/^\s*--.*$/m', '', $statement) ?? $statement);
            if ($trimmed === '' || preg_match('/^CREATE\s+TABLE\b/i', $trimmed) !== 1) continue;
            $plans[] = app_firebird_config_schema_first_slice_convert_create_table($trimmed, $file);
            if ($limit > 0 && count($plans) >= $limit) break 2;
        }
    }

    $applyResult = ['requested' => $apply, 'applied' => 0, 'skipped' => 0, 'errors' => []];
    if ($apply) $applyResult = app_firebird_config_schema_first_slice_apply($plans);

    return [
        'ok' => $apply ? $applyResult['errors'] === [] : true,
        'stage' => 'firebird_config_schema_first_slice',
        'mutation_performed' => $applyResult['applied'] > 0,
        'sql_dir' => $sqlDir,
        'summary' => [
            'planned_table_count' => count($plans),
            'apply' => $applyResult,
            'scope' => 'CREATE TABLE first slice only; indexes, unique keys, foreign keys, ALTER, seed DML, and runtime wiring are intentionally out of scope.',
        ],
        'plans' => $plans,
    ];
}

/**
 * @return array{source_file:string,table:string,firebird_sql:string,dropped_entries:list<string>,warnings:list<string>}
 */
function app_firebird_config_schema_first_slice_convert_create_table(string $statement, string $file): array
{
    if (preg_match('/^CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?([A-Za-z0-9_]+)\s*\((.*)\)\s*ENGINE\s*=/is', $statement, $matches) !== 1) {
        throw new RuntimeException('unsupported CREATE TABLE statement for Firebird first slice.');
    }
    $tableName = strtoupper($matches[1]);
    $convertedEntries = [];
    $droppedEntries = [];
    $warnings = [];
    foreach (app_config_db_bootstrap_split_top_level_csv($matches[2]) as $entry) {
        $normalized = trim($entry);
        if ($normalized === '') continue;
        if (preg_match('/^(UNIQUE\s+KEY|KEY|CONSTRAINT|FOREIGN\s+KEY)\b/i', $normalized) === 1) {
            $droppedEntries[] = substr(preg_replace('/\s+/', ' ', $normalized) ?? $normalized, 0, 180);
            continue;
        }
        if (preg_match('/^PRIMARY\s+KEY\b/i', $normalized) === 1) {
            $convertedEntries[] = strtoupper($normalized);
            continue;
        }
        $convertedEntries[] = app_firebird_config_schema_first_slice_convert_column($normalized, $warnings);
    }

    return [
        'source_file' => $file,
        'table' => $tableName,
        'firebird_sql' => "CREATE TABLE {$tableName} (\n    " . implode(",\n    ", $convertedEntries) . "\n)",
        'dropped_entries' => $droppedEntries,
        'warnings' => array_values(array_unique($warnings)),
    ];
}

/**
 * @param list<string> $warnings
 */
function app_firebird_config_schema_first_slice_convert_column(string $definition, array &$warnings): string
{
    if (preg_match('/\b(?:MEDIUMTEXT|TEXT)\b/i', $definition) === 1) {
        $warnings[] = 'text_columns_mapped_to_blob_sub_type_text';
    }

    return app_config_db_bootstrap_firebird_convert_column_definition($definition);
}

/**
 * @param list<array<string,mixed>> $plans
 * @return array{requested:bool,applied:int,skipped:int,errors:list<array<string,string>>}
 */
function app_firebird_config_schema_first_slice_apply(array $plans): array
{
    $dsn = trim((string) getenv('MTOOL_FIREBIRD_DSN'));
    $user = trim((string) getenv('MTOOL_FIREBIRD_USER'));
    $password = (string) (getenv('MTOOL_FIREBIRD_PASSWORD') ?: '');
    if ($dsn === '') return ['requested' => true, 'applied' => 0, 'skipped' => 0, 'errors' => [['table' => '', 'error' => 'MTOOL_FIREBIRD_DSN is required']]];

    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $applied = 0;
    $skipped = 0;
    $errors = [];
    foreach ($plans as $plan) {
        $table = (string) ($plan['table'] ?? '');
        try {
            if (app_firebird_config_schema_first_slice_table_exists($pdo, $table)) {
                $skipped++;
                continue;
            }
            $pdo->exec((string) $plan['firebird_sql']);
            $applied++;
        } catch (Throwable $throwable) {
            $errors[] = ['table' => $table, 'error' => $throwable->getMessage()];
        }
    }
    return ['requested' => true, 'applied' => $applied, 'skipped' => $skipped, 'errors' => $errors];
}

function app_firebird_config_schema_first_slice_table_exists(PDO $pdo, string $table): bool
{
    $statement = $pdo->prepare("SELECT 1 FROM RDB\$RELATIONS WHERE RDB\$SYSTEM_FLAG = 0 AND TRIM(RDB\$RELATION_NAME) = ?");
    $statement->execute([strtoupper($table)]);
    return $statement->fetchColumn() !== false;
}

$parsed = app_cli_firebird_config_schema_first_slice_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_firebird_config_schema_first_slice_usage() . PHP_EOL);
    exit(0);
}
if ($parsed['error'] !== '') {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_firebird_config_schema_first_slice_usage() . PHP_EOL);
    exit(64);
}

$result = app_firebird_config_schema_first_slice($parsed['sql_dir'], $parsed['limit'], $parsed['apply']);
fwrite($result['ok'] ? STDOUT : STDERR, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | ($parsed['pretty'] ? JSON_PRETTY_PRINT : 0)) . PHP_EOL);
exit($result['ok'] ? 0 : 1);
