<?php

declare(strict_types=1);

require_once __DIR__ . '/firebird_mysql_promotion_rehearsal.php';
require_once __DIR__ . '/sqlite_mysql_cutover.php';

const APP_FIREBIRD_MYSQL_CUTOVER_PLAN_VERSION = 'firebird-mysql-cutover-plan-v1';
const APP_FIREBIRD_MYSQL_CUTOVER_REQUIRED_APPROVALS = ['freeze_confirmed', 'switch_approved', 'rollback_acknowledged'];
const APP_FIREBIRD_MYSQL_CUTOVER_OPERATOR_PACKAGE_VERSION = 'firebird-mysql-cutover-operator-package-v1';
const APP_FIREBIRD_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS = ['operator_package_approved', 'rollback_rehearsal_acknowledged'];

/** @param array<string,mixed> $manifest @param array<string,mixed> $verification @param array<string,mixed> $cutover @param array<string,mixed> $rollback @param list<string> $approvals @return array<string,mixed> */
function app_firebird_mysql_cutover_plan(array $manifest, array $verification, array $cutover, array $rollback, array $approvals = []): array
{
    $errors = [];
    if (app_firebird_mysql_promotion_manifest_contract_errors($manifest) !== [] || ($manifest['ok'] ?? false) !== true) $errors[] = 'promotion_manifest_not_ready';
    if (($verification['verification_version'] ?? '') !== APP_FIREBIRD_MYSQL_VERIFICATION_VERSION || ($verification['cutover_ready'] ?? false) !== true || ($verification['mutation_performed'] ?? true) !== false) $errors[] = 'verification_not_ready';
    $manifestDigest = app_sqlite_mysql_promotion_digest($manifest);
    if ((string) ($verification['context']['promotion_manifest_sha256'] ?? '') !== $manifestDigest) $errors[] = 'verification_manifest_digest_mismatch';
    if (app_sqlite_mysql_promotion_contains_secret([$cutover, $rollback, $approvals])) $errors[] = 'secret_in_cutover_artifact';

    $freezeWindowId = trim((string) ($cutover['freeze_window_id'] ?? ''));
    $targetConfigRef = (string) ($cutover['target_config_ref'] ?? '');
    $postCutoverSmokeRef = (string) ($cutover['post_cutover_smoke_ref'] ?? '');
    $sourceRetentionRef = (string) ($rollback['source_retention_ref'] ?? '');
    $rollbackProcedureRef = (string) ($rollback['rollback_procedure_ref'] ?? '');
    if (!app_sqlite_mysql_cutover_token_valid($freezeWindowId)) $errors[] = 'invalid_freeze_window_id';
    if (($cutover['writes_frozen'] ?? false) !== true) $errors[] = 'writes_not_frozen';
    if (preg_match('/^[a-f0-9]{64}$/', (string) ($cutover['final_source_snapshot_sha256'] ?? '')) !== 1) $errors[] = 'invalid_final_source_snapshot_sha256';
    if (preg_match('/^[a-f0-9]{64}$/', (string) ($cutover['final_verification_sha256'] ?? '')) !== 1) $errors[] = 'invalid_final_verification_sha256';
    if (!app_sqlite_mysql_cutover_reference_valid($targetConfigRef)) $errors[] = 'invalid_target_config_ref';
    if (!app_sqlite_mysql_cutover_reference_valid($postCutoverSmokeRef)) $errors[] = 'invalid_post_cutover_smoke_ref';
    if (($cutover['post_cutover_smoke_passed'] ?? false) !== true) $errors[] = 'post_cutover_smoke_not_passed';
    if (($cutover['automatic_source_delete'] ?? false) === true) $errors[] = 'automatic_source_delete_forbidden';

    if (($rollback['retain_source'] ?? false) !== true) $errors[] = 'source_retention_required';
    if (!app_sqlite_mysql_cutover_reference_valid($sourceRetentionRef)) $errors[] = 'invalid_source_retention_ref';
    if (!app_sqlite_mysql_cutover_reference_valid($rollbackProcedureRef)) $errors[] = 'invalid_rollback_procedure_ref';
    if (!app_sqlite_mysql_cutover_timestamp_valid((string) ($rollback['rollback_window_until'] ?? ''))) $errors[] = 'invalid_rollback_window_until';
    if (!in_array((string) ($rollback['post_window_source_disposition'] ?? ''), ['archive', 'manual_delete_after_approval'], true)) $errors[] = 'invalid_post_window_source_disposition';

    $approvalSet = array_fill_keys(array_values(array_map('strval', $approvals)), true);
    foreach (APP_FIREBIRD_MYSQL_CUTOVER_REQUIRED_APPROVALS as $approval) {
        if (!isset($approvalSet[$approval])) $errors[] = 'missing_approval:' . $approval;
    }

    $errors = array_values(array_unique($errors));
    $safe = [
        'cutover_plan_version' => APP_FIREBIRD_MYSQL_CUTOVER_PLAN_VERSION,
        'cutover_allowed' => $errors === [],
        'stage' => $errors === [] ? 'firebird_mysql_cutover_contract_ready' : 'firebird_mysql_cutover_contract_blocked',
        'mutation_performed' => false,
        'promotion_manifest_sha256' => $manifestDigest,
        'verification_sha256' => app_sqlite_mysql_promotion_digest($verification),
        'requires_explicit_approval' => true,
        'required_approvals' => APP_FIREBIRD_MYSQL_CUTOVER_REQUIRED_APPROVALS,
        'approvals' => array_values(array_intersect(APP_FIREBIRD_MYSQL_CUTOVER_REQUIRED_APPROVALS, array_keys($approvalSet))),
        'cutover' => [
            'freeze_window_id' => $freezeWindowId,
            'writes_frozen' => ($cutover['writes_frozen'] ?? false) === true,
            'final_source_snapshot_sha256' => (string) ($cutover['final_source_snapshot_sha256'] ?? ''),
            'final_verification_sha256' => (string) ($cutover['final_verification_sha256'] ?? ''),
            'target_config_ref' => app_sqlite_mysql_cutover_reference_valid($targetConfigRef) ? $targetConfigRef : '',
            'post_cutover_smoke_ref' => app_sqlite_mysql_cutover_reference_valid($postCutoverSmokeRef) ? $postCutoverSmokeRef : '',
            'post_cutover_smoke_passed' => ($cutover['post_cutover_smoke_passed'] ?? false) === true,
            'automatic_source_delete' => ($cutover['automatic_source_delete'] ?? false) === true,
        ],
        'rollback' => [
            'retain_source' => ($rollback['retain_source'] ?? false) === true,
            'source_retention_ref' => app_sqlite_mysql_cutover_reference_valid($sourceRetentionRef) ? $sourceRetentionRef : '',
            'rollback_procedure_ref' => app_sqlite_mysql_cutover_reference_valid($rollbackProcedureRef) ? $rollbackProcedureRef : '',
            'rollback_window_until' => (string) ($rollback['rollback_window_until'] ?? ''),
            'post_window_source_disposition' => (string) ($rollback['post_window_source_disposition'] ?? ''),
        ],
        'errors' => $errors,
    ];
    $safe['cutover_contract_sha256'] = app_sqlite_mysql_promotion_digest($safe);
    return $safe;
}

/** @param array<string,mixed> $cutoverPlan @param array<string,mixed> $switch @param array<string,mixed> $rehearsal @param list<string> $approvals @return array<string,mixed> */
function app_firebird_mysql_cutover_operator_package(array $cutoverPlan, array $switch, array $rehearsal, array $approvals = []): array
{
    $errors = [];
    if (($cutoverPlan['cutover_plan_version'] ?? '') !== APP_FIREBIRD_MYSQL_CUTOVER_PLAN_VERSION || ($cutoverPlan['cutover_allowed'] ?? false) !== true || ($cutoverPlan['mutation_performed'] ?? true) !== false) $errors[] = 'cutover_plan_not_ready';
    if (preg_match('/^[a-f0-9]{64}$/', (string) ($cutoverPlan['cutover_contract_sha256'] ?? '')) !== 1) $errors[] = 'invalid_cutover_contract_sha256';
    if (app_sqlite_mysql_promotion_contains_secret([$switch, $rehearsal, $approvals])) $errors[] = 'secret_in_operator_package';

    $packageId = trim((string) ($switch['package_id'] ?? ''));
    $switchConfigRef = (string) ($switch['switch_config_ref'] ?? '');
    $switchCommandRef = (string) ($switch['switch_command_ref'] ?? '');
    $preSwitchBackupRef = (string) ($switch['pre_switch_backup_ref'] ?? '');
    $postSwitchSmokeRef = (string) ($switch['post_switch_smoke_ref'] ?? '');
    $rollbackCommandRef = (string) ($switch['rollback_command_ref'] ?? '');
    $rehearsalReportRef = (string) ($rehearsal['rehearsal_report_ref'] ?? '');

    if (!app_sqlite_mysql_cutover_token_valid($packageId)) $errors[] = 'invalid_package_id';
    if ((string) ($switch['switch_target_driver'] ?? '') !== 'mysql') $errors[] = 'invalid_switch_target_driver';
    if (!app_sqlite_mysql_cutover_reference_valid($switchConfigRef)) $errors[] = 'invalid_switch_config_ref';
    if (!app_sqlite_mysql_cutover_reference_valid($switchCommandRef)) $errors[] = 'invalid_switch_command_ref';
    if (!app_sqlite_mysql_cutover_reference_valid($preSwitchBackupRef)) $errors[] = 'invalid_pre_switch_backup_ref';
    if (!app_sqlite_mysql_cutover_reference_valid($postSwitchSmokeRef)) $errors[] = 'invalid_post_switch_smoke_ref';
    if (!app_sqlite_mysql_cutover_reference_valid($rollbackCommandRef)) $errors[] = 'invalid_rollback_command_ref';
    if (!app_sqlite_mysql_cutover_reference_valid($rehearsalReportRef)) $errors[] = 'invalid_rehearsal_report_ref';

    foreach (['inline_switch_command', 'inline_rollback_command', 'shell_command', 'sql'] as $forbiddenKey) {
        if (array_key_exists($forbiddenKey, $switch) || array_key_exists($forbiddenKey, $rehearsal)) $errors[] = 'inline_execution_forbidden:' . $forbiddenKey;
    }
    if (($switch['automatic_apply'] ?? false) === true) $errors[] = 'automatic_apply_forbidden';
    if (($switch['mutation_performed'] ?? false) === true || ($rehearsal['mutation_performed'] ?? false) === true) $errors[] = 'operator_package_must_be_side_effect_free';
    if (($switch['source_delete'] ?? false) === true || ($switch['automatic_source_delete'] ?? false) === true) $errors[] = 'source_delete_forbidden';
    if (($rehearsal['switch_dry_run_passed'] ?? false) !== true) $errors[] = 'switch_dry_run_not_passed';
    if (($rehearsal['rollback_rehearsal_passed'] ?? false) !== true) $errors[] = 'rollback_rehearsal_not_passed';
    if (($rehearsal['post_switch_smoke_rehearsed'] ?? false) !== true) $errors[] = 'post_switch_smoke_not_rehearsed';

    $approvalSet = array_fill_keys(array_values(array_map('strval', $approvals)), true);
    foreach (APP_FIREBIRD_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS as $approval) {
        if (!isset($approvalSet[$approval])) $errors[] = 'missing_approval:' . $approval;
    }

    $errors = array_values(array_unique($errors));
    $safe = [
        'operator_package_version' => APP_FIREBIRD_MYSQL_CUTOVER_OPERATOR_PACKAGE_VERSION,
        'operator_package_ready' => $errors === [],
        'stage' => $errors === [] ? 'firebird_mysql_operator_package_ready' : 'firebird_mysql_operator_package_blocked',
        'mutation_performed' => false,
        'cutover_contract_sha256' => (string) ($cutoverPlan['cutover_contract_sha256'] ?? ''),
        'requires_explicit_approval' => true,
        'required_approvals' => APP_FIREBIRD_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS,
        'approvals' => array_values(array_intersect(APP_FIREBIRD_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS, array_keys($approvalSet))),
        'switch' => [
            'package_id' => $packageId,
            'switch_target_driver' => (string) ($switch['switch_target_driver'] ?? ''),
            'switch_config_ref' => app_sqlite_mysql_cutover_reference_valid($switchConfigRef) ? $switchConfigRef : '',
            'switch_command_ref' => app_sqlite_mysql_cutover_reference_valid($switchCommandRef) ? $switchCommandRef : '',
            'pre_switch_backup_ref' => app_sqlite_mysql_cutover_reference_valid($preSwitchBackupRef) ? $preSwitchBackupRef : '',
            'post_switch_smoke_ref' => app_sqlite_mysql_cutover_reference_valid($postSwitchSmokeRef) ? $postSwitchSmokeRef : '',
            'rollback_command_ref' => app_sqlite_mysql_cutover_reference_valid($rollbackCommandRef) ? $rollbackCommandRef : '',
            'automatic_apply' => ($switch['automatic_apply'] ?? false) === true,
            'source_delete' => ($switch['source_delete'] ?? false) === true,
        ],
        'rehearsal' => [
            'rehearsal_report_ref' => app_sqlite_mysql_cutover_reference_valid($rehearsalReportRef) ? $rehearsalReportRef : '',
            'switch_dry_run_passed' => ($rehearsal['switch_dry_run_passed'] ?? false) === true,
            'rollback_rehearsal_passed' => ($rehearsal['rollback_rehearsal_passed'] ?? false) === true,
            'post_switch_smoke_rehearsed' => ($rehearsal['post_switch_smoke_rehearsed'] ?? false) === true,
        ],
        'errors' => $errors,
    ];
    $safe['operator_package_sha256'] = app_sqlite_mysql_promotion_digest($safe);
    return $safe;
}
