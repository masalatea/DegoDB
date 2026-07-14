#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/sample_pack_catalog.php';
require_once dirname(__DIR__) . '/app/sqlite_firebird_promotion_rehearsal.php';

/**
 * @return array<string,mixed>
 */
function app_cli_sample34_firebird_promotion_fixture(string $path): array
{
    $json = is_file($path) ? file_get_contents($path) : false;
    if (!is_string($json)) {
        return ['ok' => false, 'error' => 'fixture_not_found', 'path' => $path];
    }

    try {
        $fixture = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        return ['ok' => false, 'error' => 'fixture_json_invalid', 'message' => $exception->getMessage(), 'path' => $path];
    }

    return is_array($fixture)
        ? ['ok' => true, 'fixture' => $fixture, 'path' => $path]
        : ['ok' => false, 'error' => 'fixture_shape_invalid', 'path' => $path];
}

/**
 * @param array<string,mixed> $fixture
 * @return array<string,mixed>
 */
function app_cli_sample34_firebird_promotion_validate(array $fixture): array
{
    try {
        $contract = app_sqlite_firebird_promotion_contract_build(
            is_array($fixture['canonical_snapshot'] ?? null) ? $fixture['canonical_snapshot'] : [],
            is_array($fixture['sqlite_inspection'] ?? null) ? $fixture['sqlite_inspection'] : [],
            is_array($fixture['options'] ?? null) ? $fixture['options'] : [],
        );
        $expected = is_array($fixture['expected'] ?? null) ? $fixture['expected'] : [];
        $errors = app_sqlite_firebird_promotion_contract_errors($contract);

        $tableOrder = array_column(is_array($contract['tables'] ?? null) ? $contract['tables'] : [], 'name');
        if ($tableOrder !== ($expected['table_order'] ?? [])) {
            $errors[] = 'table_order';
        }
        if (($contract['target']['driver'] ?? '') !== ($expected['target_driver'] ?? 'firebird')) {
            $errors[] = 'target_driver_expected';
        }
        if (($contract['target']['profile'] ?? '') !== ($expected['target_profile'] ?? 'local_durable_file')) {
            $errors[] = 'target_profile_expected';
        }
        foreach (is_array($expected['required_verification'] ?? null) ? $expected['required_verification'] : [] as $key) {
            if (!in_array($key, $contract['required_verification'] ?? [], true)) {
                $errors[] = 'required_verification:' . $key;
            }
        }
        $schema = app_sqlite_firebird_target_schema_plan($contract);
        $export = app_sqlite_firebird_export(app_cli_sample34_firebird_promotion_sqlite($fixture), $contract, 1);
        $rehearsal = app_sqlite_firebird_import_rehearsal_package($contract, $schema, $export);
        $allErrors = array_values(array_unique(array_merge(
            $errors,
            is_array($schema['errors'] ?? null) ? $schema['errors'] : [],
            is_array($export['errors'] ?? null) ? $export['errors'] : [],
            is_array($rehearsal['errors'] ?? null) ? $rehearsal['errors'] : [],
        )));

        return [
            'ok' => ($contract['ok'] ?? false) === true
                && ($schema['ok'] ?? false) === true
                && ($export['ok'] ?? false) === true
                && ($rehearsal['rehearsal_ready'] ?? false) === true
                && $allErrors === [],
            'sample' => (string) ($fixture['sample'] ?? ''),
            'structure_type' => app_sample_pack_structure_type((string) ($fixture['sample'] ?? '')),
            'fixture_contract' => (string) ($fixture['promotion_contract'] ?? ''),
            'contract' => $contract,
            'target_schema' => $schema,
            'export' => $export,
            'import_rehearsal' => $rehearsal,
            'component_status' => [
                'contract_ok' => ($contract['ok'] ?? false) === true,
                'target_schema_ok' => ($schema['ok'] ?? false) === true,
                'export_ok' => ($export['ok'] ?? false) === true,
                'rehearsal_ready' => ($rehearsal['rehearsal_ready'] ?? false) === true,
                'errors' => $allErrors,
                'mutation_performed' => ($contract['mutation_performed'] ?? null) === true,
                'target_driver' => (string) ($contract['target']['driver'] ?? ''),
                'target_profile' => (string) ($contract['target']['profile'] ?? ''),
                'table_count' => count(is_array($contract['tables'] ?? null) ? $contract['tables'] : []),
                'export_row_count' => (int) ($rehearsal['export_summary']['row_count'] ?? 0),
                'export_chunk_count' => (int) ($rehearsal['export_summary']['chunk_count'] ?? 0),
            ],
        ];
    } catch (Throwable $throwable) {
        return ['ok' => false, 'error' => 'validation_exception', 'message' => $throwable->getMessage()];
    }
}

/**
 * @param array<string,mixed> $fixture
 */
function app_cli_sample34_firebird_promotion_sqlite(array $fixture): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    foreach (is_array($fixture['source_sql'] ?? null) ? $fixture['source_sql'] : [] as $sql) {
        $pdo->exec((string) $sql);
    }
    foreach (is_array($fixture['source_rows'] ?? null) ? $fixture['source_rows'] : [] as $row) {
        if (!is_array($row)) {
            continue;
        }
        $statement = $pdo->prepare((string) ($row['sql'] ?? ''));
        $values = [];
        foreach (is_array($row['values'] ?? null) ? $row['values'] : [] as $value) {
            if (is_array($value) && ($value['encoding'] ?? '') === 'base64') {
                $decoded = base64_decode((string) ($value['value'] ?? ''), true);
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

/**
 * @param array<string,mixed> $result
 */
function app_cli_sample34_firebird_promotion_text(array $result): string
{
    $contract = is_array($result['contract'] ?? null) ? $result['contract'] : [];
    $rehearsal = is_array($result['import_rehearsal'] ?? null) ? $result['import_rehearsal'] : [];
    return implode(PHP_EOL, [
        'sample34 SQLite-to-Firebird promotion validation',
        'OK: ' . (($result['ok'] ?? false) === true ? 'yes' : 'no'),
        'Sample: ' . (string) ($result['sample'] ?? ''),
        'Structure type: ' . (string) ($result['structure_type'] ?? ''),
        'Stage: ' . (string) ($contract['stage'] ?? ''),
        'Rehearsal stage: ' . (string) ($rehearsal['stage'] ?? ''),
        'Export rows/chunks: ' . (int) ($rehearsal['export_summary']['row_count'] ?? 0) . '/' . (int) ($rehearsal['export_summary']['chunk_count'] ?? 0),
        'Target driver: ' . (string) ($contract['target']['driver'] ?? ''),
        'Target profile: ' . (string) ($contract['target']['profile'] ?? ''),
        'Mutation performed by validator: no',
        'Requires explicit local profile switch: ' . (in_array('local_profile_switch', $contract['required_approvals'] ?? [], true) ? 'yes' : 'no'),
    ]) . PHP_EOL;
}

$json = in_array('--json', $argv, true);
$help = in_array('--help', $argv, true) || in_array('-h', $argv, true);
if ($help) {
    fwrite(STDOUT, "usage: mtool/scripts/validate_sample34_firebird_promotion.php [--json] [fixture-path]\n");
    exit(0);
}

$fixturePath = '';
foreach (array_slice($argv, 1) as $arg) {
    if ($arg !== '--json') {
        $fixturePath = $arg;
        break;
    }
}
if ($fixturePath === '') {
    $fixturePath = app_sample_pack_reference_root('sample34-sqlite-to-firebird-promotion') . '/promotion-contract-input.json';
}

$fixtureLoad = app_cli_sample34_firebird_promotion_fixture($fixturePath);
$result = ($fixtureLoad['ok'] ?? false) === true
    ? app_cli_sample34_firebird_promotion_validate(is_array($fixtureLoad['fixture'] ?? null) ? $fixtureLoad['fixture'] : [])
    : $fixtureLoad;
$result['fixture_path'] = $fixturePath;

if ($json) {
    fwrite(($result['ok'] ?? false) === true ? STDOUT : STDERR, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);
} else {
    fwrite(($result['ok'] ?? false) === true ? STDOUT : STDERR, app_cli_sample34_firebird_promotion_text($result));
}

exit(($result['ok'] ?? false) === true ? 0 : 2);
