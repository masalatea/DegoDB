<?php

declare(strict_types=1);

require_once __DIR__ . '/sqlite_mysql_cutover.php';
require_once __DIR__ . '/sqlite_mysql_import.php';

const APP_SQLITE_MYSQL_PROMOTION_REHEARSAL_PACKAGE_VERSION = 'sqlite-mysql-promotion-rehearsal-package-v1';

/** @param array<string,mixed> $manifest @param array<string,mixed> $targetSchemaPlan @param array<string,mixed> $exportResult @param array<string,mixed> $importResult @param array<string,mixed> $verification @param array<string,mixed> $cutoverPlan @param array<string,mixed> $operatorPackage @return array<string,mixed> */
function app_sqlite_mysql_promotion_rehearsal_package(
    array $manifest,
    array $targetSchemaPlan,
    array $exportResult,
    array $importResult,
    array $verification,
    array $cutoverPlan,
    array $operatorPackage,
): array {
    $errors = [];
    $manifestDigest = app_sqlite_mysql_promotion_digest($manifest);
    $schemaDigest = (string) ($targetSchemaPlan['schema_sha256'] ?? '');
    $checkpoint = is_array($importResult['checkpoint'] ?? null) ? $importResult['checkpoint'] : [];
    $checkpointDigest = app_sqlite_mysql_promotion_digest($checkpoint);
    $cutoverContractDigest = (string) ($cutoverPlan['cutover_contract_sha256'] ?? '');
    $operatorPackageDigest = (string) ($operatorPackage['operator_package_sha256'] ?? '');

    if (app_sqlite_mysql_promotion_contains_secret([$manifest, $targetSchemaPlan, $exportResult, $importResult, $verification, $cutoverPlan, $operatorPackage])) $errors[] = 'secret_in_rehearsal_package';
    if (app_sqlite_mysql_promotion_manifest_contract_errors($manifest) !== [] || ($manifest['ok'] ?? false) !== true) $errors[] = 'promotion_manifest_not_ready';
    if (($targetSchemaPlan['plan_version'] ?? '') !== APP_SQLITE_MYSQL_TARGET_SCHEMA_PLAN_VERSION || ($targetSchemaPlan['ok'] ?? false) !== true || ($targetSchemaPlan['mutation_performed'] ?? true) !== false) $errors[] = 'target_schema_plan_not_ready';
    if ((string) ($targetSchemaPlan['promotion_manifest_sha256'] ?? '') !== $manifestDigest) $errors[] = 'target_schema_manifest_digest_mismatch';
    if (preg_match('/^[a-f0-9]{64}$/', $schemaDigest) !== 1) $errors[] = 'invalid_target_schema_sha256';

    if (($exportResult['ok'] ?? false) !== true || ($exportResult['mutation_performed'] ?? true) !== false) $errors[] = 'export_not_ready';
    $chunkSummary = app_sqlite_mysql_promotion_rehearsal_chunk_summary($exportResult);
    if (!$chunkSummary['ok']) $errors[] = 'export_chunk_contract_invalid';
    if ($chunkSummary['row_count'] !== app_sqlite_mysql_promotion_rehearsal_manifest_row_count($manifest)) $errors[] = 'export_row_count_mismatch';

    if (($importResult['ok'] ?? false) !== true) $errors[] = 'import_not_ready';
    if (($checkpoint['checkpoint_version'] ?? '') !== APP_SQLITE_MYSQL_IMPORT_CHECKPOINT_VERSION) $errors[] = 'import_checkpoint_not_ready';
    if (($importResult['stage'] ?? '') !== 'chunk_committed' && ($importResult['stage'] ?? '') !== 'already_committed') $errors[] = 'import_stage_not_accepted';

    if (($verification['verification_version'] ?? '') !== APP_SQLITE_MYSQL_VERIFICATION_VERSION || ($verification['cutover_ready'] ?? false) !== true || ($verification['mutation_performed'] ?? true) !== false) $errors[] = 'verification_not_ready';
    if ((string) ($verification['context']['promotion_manifest_sha256'] ?? '') !== $manifestDigest) $errors[] = 'verification_manifest_digest_mismatch';
    if ((string) ($verification['context']['target_schema_sha256'] ?? '') !== $schemaDigest) $errors[] = 'verification_schema_digest_mismatch';
    if ((string) ($verification['context']['import_checkpoint_sha256'] ?? '') !== $checkpointDigest) $errors[] = 'verification_checkpoint_digest_mismatch';

    if (($cutoverPlan['cutover_plan_version'] ?? '') !== APP_SQLITE_MYSQL_CUTOVER_PLAN_VERSION || ($cutoverPlan['cutover_allowed'] ?? false) !== true || ($cutoverPlan['mutation_performed'] ?? true) !== false) $errors[] = 'cutover_plan_not_ready';
    if ((string) ($cutoverPlan['promotion_manifest_sha256'] ?? '') !== $manifestDigest) $errors[] = 'cutover_manifest_digest_mismatch';
    if ((string) ($cutoverPlan['verification_sha256'] ?? '') !== app_sqlite_mysql_promotion_digest($verification)) $errors[] = 'cutover_verification_digest_mismatch';
    if (preg_match('/^[a-f0-9]{64}$/', $cutoverContractDigest) !== 1) $errors[] = 'invalid_cutover_contract_sha256';

    if (($operatorPackage['operator_package_version'] ?? '') !== APP_SQLITE_MYSQL_CUTOVER_OPERATOR_PACKAGE_VERSION || ($operatorPackage['operator_package_ready'] ?? false) !== true || ($operatorPackage['mutation_performed'] ?? true) !== false) $errors[] = 'operator_package_not_ready';
    if ((string) ($operatorPackage['cutover_contract_sha256'] ?? '') !== $cutoverContractDigest) $errors[] = 'operator_cutover_digest_mismatch';
    if (preg_match('/^[a-f0-9]{64}$/', $operatorPackageDigest) !== 1) $errors[] = 'invalid_operator_package_sha256';

    $errors = array_values(array_unique($errors));
    $safe = [
        'rehearsal_package_version' => APP_SQLITE_MYSQL_PROMOTION_REHEARSAL_PACKAGE_VERSION,
        'rehearsal_ready' => $errors === [],
        'stage' => $errors === [] ? 'promotion_rehearsal_ready' : 'promotion_rehearsal_blocked',
        'mutation_performed' => false,
        'promotion_manifest_sha256' => $manifestDigest,
        'target_schema_sha256' => $schemaDigest,
        'export_summary' => [
            'table_count' => $chunkSummary['table_count'],
            'chunk_count' => $chunkSummary['chunk_count'],
            'row_count' => $chunkSummary['row_count'],
        ],
        'import_checkpoint_sha256' => $checkpointDigest,
        'verification_sha256' => app_sqlite_mysql_promotion_digest($verification),
        'cutover_contract_sha256' => $cutoverContractDigest,
        'operator_package_sha256' => $operatorPackageDigest,
        'requires_explicit_cutover' => true,
        'non_goals' => ['mysql_to_sqlite', 'bidirectional_sync', 'zero_downtime_cdc', 'automatic_cutover'],
        'errors' => $errors,
    ];
    $safe['rehearsal_package_sha256'] = app_sqlite_mysql_promotion_digest($safe);
    return $safe;
}

/** @param array<string,mixed> $exportResult @return array{ok:bool,table_count:int,chunk_count:int,row_count:int} */
function app_sqlite_mysql_promotion_rehearsal_chunk_summary(array $exportResult): array
{
    $ok = true;
    $tables = [];
    $chunkCount = 0;
    $rowCount = 0;
    foreach (is_array($exportResult['chunks'] ?? null) ? $exportResult['chunks'] : [] as $chunk) {
        if (!is_array($chunk) || ($chunk['export_version'] ?? '') !== APP_SQLITE_MYSQL_EXPORT_VERSION) {
            $ok = false;
            continue;
        }
        $table = (string) ($chunk['table'] ?? '');
        $rows = is_array($chunk['rows'] ?? null) ? $chunk['rows'] : [];
        $json = json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        if ($table === '' || (int) ($chunk['row_count'] ?? -1) !== count($rows) || !hash_equals((string) ($chunk['rows_sha256'] ?? ''), hash('sha256', $json))) $ok = false;
        $tables[$table] = true;
        $chunkCount++;
        $rowCount += count($rows);
    }
    return ['ok' => $ok, 'table_count' => count($tables), 'chunk_count' => $chunkCount, 'row_count' => $rowCount];
}

/** @param array<string,mixed> $manifest */
function app_sqlite_mysql_promotion_rehearsal_manifest_row_count(array $manifest): int
{
    $count = 0;
    foreach (is_array($manifest['tables'] ?? null) ? $manifest['tables'] : [] as $table) {
        if (is_array($table)) $count += max(0, (int) ($table['row_count'] ?? 0));
    }
    return $count;
}
