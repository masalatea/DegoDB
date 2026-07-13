#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/sample_pack_catalog.php';
require_once dirname(__DIR__) . '/app/sqlite_mysql_promotion_rehearsal.php';

/**
 * @return array<string,mixed>
 */
function app_cli_sample33_promotion_fixture(string $path): array
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
    return is_array($fixture) ? ['ok' => true, 'fixture' => $fixture, 'path' => $path] : ['ok' => false, 'error' => 'fixture_shape_invalid', 'path' => $path];
}

/**
 * @param array<string,mixed> $fixture
 */
function app_cli_sample33_promotion_sqlite(array $fixture): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    foreach (is_array($fixture['source_sql'] ?? null) ? $fixture['source_sql'] : [] as $sql) {
        $pdo->exec((string) $sql);
    }
    foreach (is_array($fixture['source_rows'] ?? null) ? $fixture['source_rows'] : [] as $row) {
        if (!is_array($row)) continue;
        $statement = $pdo->prepare((string) ($row['sql'] ?? ''));
        $values = [];
        foreach (is_array($row['values'] ?? null) ? $row['values'] : [] as $value) {
            if (is_array($value) && ($value['encoding'] ?? '') === 'base64') {
                $decoded = base64_decode((string) ($value['value'] ?? ''), true);
                if (!is_string($decoded)) throw new RuntimeException('invalid_base64_fixture_value');
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
 * @param array<string,mixed> $fixture
 * @return array<string,mixed>
 */
function app_cli_sample33_promotion_validate(array $fixture): array
{
    try {
        $manifest = is_array($fixture['manifest'] ?? null) ? $fixture['manifest'] : [];
        $schema = app_sqlite_mysql_target_schema_plan($manifest);
        $export = app_sqlite_mysql_export(app_cli_sample33_promotion_sqlite($fixture), $manifest, 1);
        $checkpoint = is_array($fixture['import_checkpoint'] ?? null) ? $fixture['import_checkpoint'] : [];
        $import = ['ok' => true, 'stage' => 'chunk_committed', 'error' => '', 'checkpoint' => $checkpoint, 'mutation_performed' => true];
        $verification = app_sqlite_mysql_verification_artifact([
            'promotion_manifest_sha256' => app_sqlite_mysql_promotion_digest($manifest),
            'target_schema_sha256' => (string) ($schema['schema_sha256'] ?? ''),
            'import_checkpoint_sha256' => app_sqlite_mysql_promotion_digest($checkpoint),
        ], array_map(static fn (string $key): array => ['check_key' => $key, 'status' => 'passed'], APP_SQLITE_MYSQL_VERIFICATION_REQUIRED));
        $cutover = app_sqlite_mysql_cutover_plan(
            $manifest,
            $verification,
            is_array($fixture['cutover'] ?? null) ? $fixture['cutover'] : [],
            is_array($fixture['rollback'] ?? null) ? $fixture['rollback'] : [],
            APP_SQLITE_MYSQL_CUTOVER_REQUIRED_APPROVALS,
        );
        $operator = app_sqlite_mysql_cutover_operator_package(
            $cutover,
            is_array($fixture['switch'] ?? null) ? $fixture['switch'] : [],
            is_array($fixture['operator_rehearsal'] ?? null) ? $fixture['operator_rehearsal'] : [],
            APP_SQLITE_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS,
        );
        $package = app_sqlite_mysql_promotion_rehearsal_package($manifest, $schema, $export, $import, $verification, $cutover, $operator);
        return [
            'ok' => ($package['rehearsal_ready'] ?? false) === true,
            'sample' => (string) ($fixture['sample'] ?? ''),
            'structure_type' => app_sample_pack_structure_type((string) ($fixture['sample'] ?? '')),
            'fixture_contract' => (string) ($fixture['promotion_contract'] ?? ''),
            'rehearsal_package' => $package,
            'component_status' => [
                'target_schema_ok' => ($schema['ok'] ?? false) === true,
                'export_ok' => ($export['ok'] ?? false) === true,
                'verification_ready' => ($verification['cutover_ready'] ?? false) === true,
                'cutover_allowed' => ($cutover['cutover_allowed'] ?? false) === true,
                'operator_package_ready' => ($operator['operator_package_ready'] ?? false) === true,
            ],
        ];
    } catch (Throwable $throwable) {
        return ['ok' => false, 'error' => 'validation_exception', 'message' => $throwable->getMessage()];
    }
}

/**
 * @param array<string,mixed> $result
 */
function app_cli_sample33_promotion_text(array $result): string
{
    $package = is_array($result['rehearsal_package'] ?? null) ? $result['rehearsal_package'] : [];
    return implode(PHP_EOL, [
        'sample33 SQLite-to-MySQL promotion validation',
        'OK: ' . (($result['ok'] ?? false) === true ? 'yes' : 'no'),
        'Sample: ' . (string) ($result['sample'] ?? ''),
        'Structure type: ' . (string) ($result['structure_type'] ?? ''),
        'Stage: ' . (string) ($package['stage'] ?? ''),
        'Rehearsal package: ' . (string) ($package['rehearsal_package_sha256'] ?? ''),
        'Mutation performed by validator: no',
        'Requires explicit cutover: ' . (($package['requires_explicit_cutover'] ?? false) === true ? 'yes' : 'no'),
    ]) . PHP_EOL;
}

$json = in_array('--json', $argv, true);
$help = in_array('--help', $argv, true) || in_array('-h', $argv, true);
if ($help) {
    fwrite(STDOUT, "usage: mtool/scripts/validate_sample33_promotion.php [--json] [fixture-path]\n");
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
    $fixturePath = app_sample_pack_reference_root('sample33-sqlite-to-mysql-promotion') . '/promotion-rehearsal-contract.json';
}
$fixtureLoad = app_cli_sample33_promotion_fixture($fixturePath);
$result = ($fixtureLoad['ok'] ?? false) === true
    ? app_cli_sample33_promotion_validate(is_array($fixtureLoad['fixture'] ?? null) ? $fixtureLoad['fixture'] : [])
    : $fixtureLoad;
$result['fixture_path'] = $fixturePath;

if ($json) {
    fwrite(($result['ok'] ?? false) === true ? STDOUT : STDERR, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);
} else {
    fwrite(($result['ok'] ?? false) === true ? STDOUT : STDERR, app_cli_sample33_promotion_text($result));
}

exit(($result['ok'] ?? false) === true ? 0 : 2);
