<?php

declare(strict_types=1);

require_once __DIR__ . '/sso_app_user_project_policy.php';

/**
 * Validate only invariants expressible in current canonical metadata.
 * Composite unique/FK database constraints remain explicit blocking gaps.
 *
 * @param array<string,mixed> $policyInput
 * @param list<array<string,mixed>> $tables
 * @param list<array<string,mixed>> $dataClasses
 * @param list<array<string,mixed>> $dbAccessClasses
 * @return array<string,mixed>
 */
function app_sso_app_user_validate_canonical_schema(
    array $policyInput,
    array $tables,
    array $dataClasses,
    array $dbAccessClasses,
): array {
    $policyResult = app_sso_app_user_project_policy_normalize($policyInput);
    if (!$policyResult['ok']) {
        return app_sso_app_user_schema_validation_result(
            false,
            false,
            'invalid_policy',
            $policyResult['errors'],
            [],
            [],
        );
    }
    $policy = $policyResult['policy'];
    if (!$policy['enabled']) {
        return app_sso_app_user_schema_validation_result(true, false, 'not_applicable', [], [], []);
    }

    $errors = [];
    $warnings = [];
    $evidence = [];
    $tableCatalog = app_sso_app_user_schema_catalog($tables);
    $dataClassCatalog = app_sso_app_user_schema_catalog($dataClasses);
    $dbAccessCatalog = app_sso_app_user_schema_catalog($dbAccessClasses, 'source_name');
    $roles = $policy['schema_roles'];

    $requiredColumns = [
        'application_user_table' => ['app_user_id', 'status'],
        'external_identity_table' => ['app_user_id', 'issuer', 'subject'],
        'profile_table' => ['app_user_id'],
    ];
    foreach ($requiredColumns as $role => $columnNames) {
        $tableName = $roles[$role];
        $table = $tableCatalog[$tableName] ?? null;
        if (!is_array($table)) {
            $errors[] = 'missing table for ' . $role . ': ' . $tableName . '.';
            continue;
        }
        $columns = app_sso_app_user_schema_column_catalog($table);
        foreach ($columnNames as $columnName) {
            if (!isset($columns[$columnName])) {
                $errors[] = 'missing column: ' . $tableName . '.' . $columnName . '.';
            }
        }
        $evidence[$role] = ['table' => $tableName, 'columns' => array_keys($columns)];
    }

    $appUserTable = $tableCatalog[$roles['application_user_table']] ?? null;
    if (is_array($appUserTable)) {
        $columns = app_sso_app_user_schema_column_catalog($appUserTable);
        if (strtoupper(trim((string) ($columns['app_user_id']['is_key'] ?? ''))) !== 'PRI') {
            $errors[] = $roles['application_user_table'] . '.app_user_id must be the primary key.';
        }
    }

    foreach ($roles as $role => $name) {
        if (!isset($dataClassCatalog[$name])) {
            $errors[] = 'missing data class for ' . $role . ': ' . $name . '.';
        }
        if (!isset($dbAccessCatalog[$name])) {
            $errors[] = 'missing DBAccess class for ' . $role . ': ' . $name . '.';
        }
    }

    foreach (['external_identity_table', 'profile_table'] as $role) {
        $name = $roles[$role];
        $dataClass = $dataClassCatalog[$name] ?? null;
        if (!is_array($dataClass)) {
            continue;
        }
        $fields = app_sso_app_user_schema_field_catalog($dataClass);
        $reference = strtolower(trim((string) ($fields['app_user_id']['ref_data_class_name'] ?? '')));
        if ($reference !== $roles['application_user_table']) {
            $errors[] = $name . '.app_user_id must reference data class ' . $roles['application_user_table'] . '.';
        }
    }

    $requiredActions = [
        'application_user_table' => ['insert'],
        'external_identity_table' => ['select', 'insert'],
        'profile_table' => ['insert', 'update'],
    ];
    foreach ($requiredActions as $role => $actions) {
        $name = $roles[$role];
        $dbAccess = $dbAccessCatalog[$name] ?? null;
        if (!is_array($dbAccess)) {
            continue;
        }
        $available = [];
        foreach ($dbAccess['functions'] ?? [] as $function) {
            if (!is_array($function)) {
                continue;
            }
            $action = strtolower(trim((string) ($function['action_type'] ?? '')));
            if ($action !== '') {
                $available[$action] = true;
            }
        }
        foreach ($actions as $action) {
            if (!isset($available[$action])) {
                $errors[] = 'missing DBAccess action: ' . $name . '.' . $action . '.';
            }
        }
    }

    $blockingGaps = [
        'canonical metadata cannot prove UNIQUE (issuer, subject) on ' . $roles['external_identity_table'] . '.',
        'canonical metadata cannot prove database foreign keys from identity/profile/domain rows to app_user_id.',
    ];
    if ($errors === []) {
        $warnings[] = 'expressible canonical metadata is valid, but database constraint evidence is still required.';
    }

    return app_sso_app_user_schema_validation_result(
        $errors === [],
        false,
        $errors === [] ? 'metadata_valid_constraint_gap' : 'metadata_invalid',
        $errors,
        $warnings,
        $blockingGaps,
        $evidence,
    );
}

/** @return array<string,array<string,mixed>> */
function app_sso_app_user_schema_catalog(array $items, string $preferredKey = 'physical_name'): array
{
    $catalog = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $name = strtolower(trim((string) ($item[$preferredKey] ?? $item['name'] ?? '')));
        if ($name !== '') {
            $catalog[$name] = $item;
        }
    }
    return $catalog;
}

/** @return array<string,array<string,mixed>> */
function app_sso_app_user_schema_column_catalog(array $table): array
{
    return app_sso_app_user_schema_named_child_catalog($table['columns'] ?? []);
}

/** @return array<string,array<string,mixed>> */
function app_sso_app_user_schema_field_catalog(array $dataClass): array
{
    return app_sso_app_user_schema_named_child_catalog($dataClass['fields'] ?? []);
}

/** @return array<string,array<string,mixed>> */
function app_sso_app_user_schema_named_child_catalog(mixed $items): array
{
    if (!is_array($items)) {
        return [];
    }
    $catalog = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $name = strtolower(trim((string) ($item['physical_name'] ?? $item['name'] ?? '')));
        if ($name !== '') {
            $catalog[$name] = $item;
        }
    }
    return $catalog;
}

/** @return array<string,mixed> */
function app_sso_app_user_schema_validation_result(
    bool $metadataValid,
    bool $readyForGeneration,
    string $status,
    array $errors,
    array $warnings,
    array $blockingGaps,
    array $evidence = [],
): array {
    return [
        'metadata_valid' => $metadataValid,
        'ready_for_generation' => $readyForGeneration,
        'status' => $status,
        'errors' => $errors,
        'warnings' => $warnings,
        'blocking_gaps' => $blockingGaps,
        'evidence' => $evidence,
    ];
}
