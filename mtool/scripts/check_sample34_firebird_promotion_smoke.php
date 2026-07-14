#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/sample_pack_catalog.php';
require_once dirname(__DIR__) . '/app/sqlite_firebird_promotion_rehearsal.php';

/**
 * @param list<string> $argv
 * @return array{help:bool,pretty:bool,dsn:string,user:string,password:string,error:string}
 */
function app_cli_sample34_firebird_promotion_smoke_parse_args(array $argv): array
{
    $parsed = [
        'help' => false,
        'pretty' => false,
        'dsn' => trim((string) getenv('MTOOL_FIREBIRD_DSN')),
        'user' => trim((string) (getenv('MTOOL_FIREBIRD_USER') ?: '')),
        'password' => (string) (getenv('MTOOL_FIREBIRD_PASSWORD') ?: ''),
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
        if (str_starts_with($argument, '--dsn=')) {
            $parsed['dsn'] = trim(substr($argument, strlen('--dsn=')));
            continue;
        }
        if (str_starts_with($argument, '--user=')) {
            $parsed['user'] = trim(substr($argument, strlen('--user=')));
            continue;
        }
        if (str_starts_with($argument, '--password=')) {
            $parsed['password'] = substr($argument, strlen('--password='));
            continue;
        }
        $parsed['error'] = 'unsupported argument: ' . $argument;
        return $parsed;
    }

    return $parsed;
}

function app_cli_sample34_firebird_promotion_smoke_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_sample34_firebird_promotion_smoke.php [--pretty]

Environment:
  MTOOL_FIREBIRD_DSN       PDO Firebird DSN
  MTOOL_FIREBIRD_USER      Firebird user
  MTOOL_FIREBIRD_PASSWORD  Firebird password

Notes:
  - Requires PHP PDO_FIREBIRD.
  - Mutates only the disposable sample34 target tables in the configured proof database.
  - Backup/restore remains a separate smoke boundary.
TEXT;
}

/** @return array<string,mixed> */
function app_sample34_firebird_promotion_smoke_result(
    bool $ok,
    string $stage,
    string $error,
    array $details = [],
    bool $mutationPerformed = false,
): array {
    return [
        'ok' => $ok,
        'stage' => $stage,
        'error' => $error,
        'mutation_performed' => $mutationPerformed,
        'details' => $details,
    ];
}

/** @return array<string,mixed> */
function app_sample34_firebird_promotion_smoke(string $dsn, string $user, string $password): array
{
    if (!extension_loaded('pdo_firebird')) {
        return app_sample34_firebird_promotion_smoke_result(false, 'runtime_preflight', 'pdo_firebird_extension_missing', [
            'loaded_pdo_drivers' => PDO::getAvailableDrivers(),
            'required_driver' => 'firebird',
        ]);
    }
    if (trim($dsn) === '') {
        return app_sample34_firebird_promotion_smoke_result(false, 'runtime_preflight', 'firebird_dsn_required');
    }

    try {
        $fixture = app_sample34_firebird_promotion_smoke_fixture();
        $contract = app_sqlite_firebird_promotion_contract_build($fixture['canonical_snapshot'], $fixture['sqlite_inspection'], $fixture['options']);
        $schema = app_sqlite_firebird_target_schema_plan($contract);
        $export = app_sqlite_firebird_export(app_sample34_firebird_promotion_smoke_sqlite($fixture), $contract, 1);
        $rehearsal = app_sqlite_firebird_import_rehearsal_package($contract, $schema, $export);
        if (($rehearsal['rehearsal_ready'] ?? false) !== true) {
            return app_sample34_firebird_promotion_smoke_result(false, 'rehearsal_preflight', 'import_rehearsal_not_ready', [
                'rehearsal' => $rehearsal,
            ]);
        }

        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        app_sample34_firebird_promotion_smoke_drop_tables($pdo, $contract);
        foreach ($schema['statements'] as $statement) {
            $pdo->exec((string) $statement);
        }

        $pdo->beginTransaction();
        foreach ($export['chunks'] as $chunk) {
            app_sample34_firebird_promotion_smoke_insert_chunk($pdo, $chunk);
        }
        $pdo->commit();

        $verification = app_sample34_firebird_promotion_smoke_verify($pdo, $contract);
        if (($verification['ok'] ?? false) !== true) {
            return app_sample34_firebird_promotion_smoke_result(false, 'verification', 'live_import_verification_failed', [
                'verification' => $verification,
            ], true);
        }

        return app_sample34_firebird_promotion_smoke_result(true, 'sample34_firebird_live_import_smoke', '', [
            'promotion_contract_sha256' => app_sqlite_mysql_promotion_digest($contract),
            'target_schema_sha256' => (string) $schema['schema_sha256'],
            'rehearsal_sha256' => (string) $rehearsal['rehearsal_sha256'],
            'verification' => $verification,
            'backup_restore_smoke' => 'not_run_in_this_target',
        ], true);
    } catch (Throwable $throwable) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return app_sample34_firebird_promotion_smoke_result(false, 'exception', $throwable->getMessage(), [], true);
    }
}

/** @return array<string,mixed> */
function app_sample34_firebird_promotion_smoke_fixture(): array
{
    $path = app_sample_pack_reference_root('sample34-sqlite-to-firebird-promotion') . '/promotion-contract-input.json';
    $json = file_get_contents($path);
    if (!is_string($json)) {
        throw new RuntimeException('sample34_fixture_not_found');
    }
    $fixture = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($fixture)) {
        throw new RuntimeException('sample34_fixture_invalid');
    }
    return $fixture;
}

/** @param array<string,mixed> $fixture */
function app_sample34_firebird_promotion_smoke_sqlite(array $fixture): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    foreach ($fixture['source_sql'] as $sql) {
        $pdo->exec((string) $sql);
    }
    foreach ($fixture['source_rows'] as $row) {
        $statement = $pdo->prepare((string) $row['sql']);
        $values = [];
        foreach ($row['values'] as $value) {
            if (is_array($value) && ($value['encoding'] ?? '') === 'base64') {
                $decoded = base64_decode((string) $value['value'], true);
                if (!is_string($decoded)) {
                    throw new RuntimeException('source_row_base64_invalid');
                }
                $values[] = $decoded;
                continue;
            }
            $values[] = $value;
        }
        $statement->execute($values);
    }
    return $pdo;
}

/** @param array<string,mixed> $contract */
function app_sample34_firebird_promotion_smoke_drop_tables(PDO $pdo, array $contract): void
{
    $tables = array_reverse(is_array($contract['tables'] ?? null) ? $contract['tables'] : []);
    foreach ($tables as $table) {
        if (!is_array($table)) {
            continue;
        }
        try {
            $pdo->exec('DROP TABLE ' . app_sqlite_firebird_quote_identifier((string) $table['name']));
        } catch (Throwable) {
            // The disposable proof tables may not exist yet.
        }
    }
}

/** @param array<string,mixed> $chunk */
function app_sample34_firebird_promotion_smoke_insert_chunk(PDO $pdo, array $chunk): void
{
    $table = (string) ($chunk['table'] ?? '');
    $rows = is_array($chunk['rows'] ?? null) ? $chunk['rows'] : [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $columns = array_keys($row);
        $sql = 'INSERT INTO ' . app_sqlite_firebird_quote_identifier($table)
            . ' (' . implode(', ', array_map('app_sqlite_firebird_quote_identifier', $columns)) . ')'
            . ' VALUES (' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $statement = $pdo->prepare($sql);
        $values = [];
        foreach ($columns as $column) {
            $values[] = app_sample34_firebird_promotion_smoke_import_value($row[$column]);
        }
        $statement->execute($values);
    }
}

function app_sample34_firebird_promotion_smoke_import_value(mixed $value): mixed
{
    if (!is_array($value)) {
        return $value;
    }
    if (($value['encoding'] ?? '') === 'base64') {
        $decoded = base64_decode((string) ($value['value'] ?? ''), true);
        if (!is_string($decoded)) {
            throw new RuntimeException('import_base64_invalid');
        }
        return $decoded;
    }
    if (($value['encoding'] ?? '') === 'json-text') {
        return (string) ($value['value'] ?? '');
    }
    throw new RuntimeException('unsupported_import_value_encoding');
}

/** @param array<string,mixed> $contract @return array<string,mixed> */
function app_sample34_firebird_promotion_smoke_verify(PDO $pdo, array $contract): array
{
    $errors = [];
    $rowCounts = [];
    $nextIdCandidates = [];
    foreach ($contract['tables'] as $table) {
        if (!is_array($table)) {
            continue;
        }
        $name = (string) $table['name'];
        $actual = (int) $pdo->query('SELECT COUNT(*) FROM ' . app_sqlite_firebird_quote_identifier($name))->fetchColumn();
        $expected = (int) ($table['row_count'] ?? -1);
        $rowCounts[$name] = $actual;
        if ($actual !== $expected) {
            $errors[] = 'row_count_mismatch:' . $name . ':expected=' . $expected . ':actual=' . $actual;
        }
        $primaryKey = is_array($table['primary_key'] ?? null) ? array_values($table['primary_key']) : [];
        if (count($primaryKey) === 1) {
            $pk = (string) $primaryKey[0];
            $max = $pdo->query('SELECT MAX(' . app_sqlite_firebird_quote_identifier($pk) . ') FROM ' . app_sqlite_firebird_quote_identifier($name))->fetchColumn();
            $nextIdCandidates[$name . '.' . $pk] = ((int) $max) + 1;
        }
    }

    $record = $pdo->query(
        'SELECT "id", "parent_id", "title", "enabled", "amount", CAST("payload" AS VARCHAR(1024)) AS "payload_text", '
        . 'CAST("body" AS VARCHAR(1024)) AS "body_text", "recorded_at", "bytes" '
        . 'FROM "record" WHERE "id" = 9'
    )->fetch();
    if (!is_array($record)) {
        $errors[] = 'record_9_not_found';
    } else {
        if ((string) ($record['id'] ?? $record['ID'] ?? '') !== '9') $errors[] = 'record_9_id';
        if ((string) ($record['parent_id'] ?? $record['PARENT_ID'] ?? '') !== '2') $errors[] = 'record_9_parent_id';
        if ((string) ($record['title'] ?? $record['TITLE'] ?? '') !== 'First') $errors[] = 'record_9_title';
        if ((string) ($record['enabled'] ?? $record['ENABLED'] ?? '') !== '1') $errors[] = 'record_9_enabled';
        if ((string) ($record['amount'] ?? $record['AMOUNT'] ?? '') !== '10.50') $errors[] = 'record_9_amount';
        if ((string) ($record['payload_text'] ?? $record['PAYLOAD_TEXT'] ?? '') !== '{"a":1,"z":2}') $errors[] = 'record_9_payload';
        if ((string) ($record['body_text'] ?? $record['BODY_TEXT'] ?? '') !== 'hello Firebird') $errors[] = 'record_9_body';
        $bytes = $record['bytes'] ?? $record['BYTES'] ?? null;
        if (!is_string($bytes) || base64_encode($bytes) !== 'AEEB') $errors[] = 'record_9_bytes';
    }

    return [
        'ok' => $errors === [],
        'row_counts' => $rowCounts,
        'checks' => [
            'row_counts',
            'primary_key_record_9',
            'foreign_key_parent_2',
            'json_text_blob',
            'binary_blob',
            'timestamp_selectable',
            'next_id_candidates',
        ],
        'next_id_candidates' => $nextIdCandidates,
        'errors' => $errors,
    ];
}

$parsed = app_cli_sample34_firebird_promotion_smoke_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_sample34_firebird_promotion_smoke_usage() . PHP_EOL);
    exit(0);
}
if ($parsed['error'] !== '') {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_sample34_firebird_promotion_smoke_usage() . PHP_EOL);
    exit(64);
}

$result = app_sample34_firebird_promotion_smoke($parsed['dsn'], $parsed['user'], $parsed['password']);
fwrite(
    ($result['ok'] ?? false) === true ? STDOUT : STDERR,
    json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | ($parsed['pretty'] ? JSON_PRETTY_PRINT : 0)) . PHP_EOL,
);
exit(($result['ok'] ?? false) === true ? 0 : 2);
