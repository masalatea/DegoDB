<?php

declare(strict_types=1);

require_once __DIR__ . '/sso_app_user_project_policy.php';
require_once __DIR__ . '/generated_name.php';

/**
 * Validate canonical schema and explicit key/FK constraint evidence.
 *
 * @param array<string,mixed> $policyInput
 * @param list<array<string,mixed>> $tables
 * @param list<array<string,mixed>> $dataClasses
 * @param list<array<string,mixed>> $dbAccessClasses
 * @param array{keys?:array,foreign_keys?:array} $constraints
 * @return array<string,mixed>
 */
function app_sso_app_user_validate_canonical_schema(
    array $policyInput,
    array $tables,
    array $dataClasses,
    array $dbAccessClasses,
    array $constraints = [],
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
    foreach ($roles as $physicalName) {
        if (isset($dbAccessCatalog[$physicalName])) {
            continue;
        }
        $generatedName = strtolower(app_generated_name_pascal_case($physicalName));
        if (isset($dbAccessCatalog[$generatedName])) {
            $dbAccessCatalog[$physicalName] = $dbAccessCatalog[$generatedName];
        }
    }

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
        'profile_table' => ['update'],
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
            $action = app_sso_app_user_schema_action_family((string) ($function['action_type'] ?? ''));
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

    $constraintEvidence = app_sso_app_user_schema_constraint_evidence($tables, $constraints, $roles);
    $evidence['constraints'] = $constraintEvidence['evidence'];
    $blockingGaps = $constraintEvidence['blocking_gaps'];
    if ($errors === [] && $blockingGaps !== []) {
        $warnings[] = 'canonical metadata is valid, but required database constraint evidence is incomplete.';
    }
    $readyForGeneration = $errors === [] && $blockingGaps === [];

    return app_sso_app_user_schema_validation_result(
        $errors === [],
        $readyForGeneration,
        $errors !== [] ? 'metadata_invalid' : ($readyForGeneration ? 'generation_ready' : 'metadata_valid_constraint_gap'),
        $errors,
        $warnings,
        $blockingGaps,
        $evidence,
    );
}

function app_sso_app_user_schema_action_family(string $actionType): string
{
    return match (strtoupper(trim($actionType))) {
        'SELECT', 'SELECTLIST', 'SELECTSINGLE' => 'select',
        'INSERT' => 'insert',
        'UPDATE' => 'update',
        default => strtolower(trim($actionType)),
    };
}

/** @return array{blocking_gaps:list<string>,evidence:array<string,mixed>} */
function app_sso_app_user_schema_constraint_evidence(array $tables, array $constraints, array $roles): array
{
    $tableNamesByPid = [];
    $columnNamesByPid = [];
    foreach ($tables as $table) {
        if (!is_array($table)) {
            continue;
        }
        $tablePid = (int) ($table['pid'] ?? $table['PID'] ?? 0);
        $tableName = strtolower(trim((string) ($table['physical_name'] ?? $table['name'] ?? '')));
        if ($tablePid <= 0 || $tableName === '') {
            continue;
        }
        $tableNamesByPid[$tablePid] = $tableName;
        foreach (is_array($table['columns'] ?? null) ? $table['columns'] : [] as $column) {
            if (!is_array($column)) {
                continue;
            }
            $columnPid = (int) ($column['pid'] ?? $column['PID'] ?? 0);
            $columnName = strtolower(trim((string) ($column['physical_name'] ?? $column['name'] ?? '')));
            if ($columnPid > 0 && $columnName !== '') {
                $columnNamesByPid[$columnPid] = $columnName;
            }
        }
    }

    $identityTable = $roles['external_identity_table'];
    $appUserTable = $roles['application_user_table'];
    $profileTable = $roles['profile_table'];
    $identityUnique = false;
    foreach (is_array($constraints['keys'] ?? null) ? $constraints['keys'] : [] as $key) {
        if (!is_array($key) || strtolower((string) ($key['key_kind'] ?? '')) !== 'unique') {
            continue;
        }
        $tableName = $tableNamesByPid[(int) ($key['table_pid'] ?? 0)] ?? '';
        $columnNames = [];
        foreach (is_array($key['columns'] ?? null) ? $key['columns'] : [] as $column) {
            $columnNames[] = $columnNamesByPid[(int) ($column['column_pid'] ?? 0)] ?? '';
        }
        if ($tableName === $identityTable && $columnNames === ['issuer', 'subject']) {
            $identityUnique = true;
            break;
        }
    }

    $requiredForeignKeys = [$identityTable => false, $profileTable => false];
    foreach (is_array($constraints['foreign_keys'] ?? null) ? $constraints['foreign_keys'] : [] as $foreignKey) {
        if (!is_array($foreignKey)) {
            continue;
        }
        $sourceTable = $tableNamesByPid[(int) ($foreignKey['table_pid'] ?? 0)] ?? '';
        $targetTable = $tableNamesByPid[(int) ($foreignKey['referenced_table_pid'] ?? 0)] ?? '';
        if (!array_key_exists($sourceTable, $requiredForeignKeys) || $targetTable !== $appUserTable) {
            continue;
        }
        $columns = is_array($foreignKey['columns'] ?? null) ? $foreignKey['columns'] : [];
        if (count($columns) !== 1 || !is_array($columns[0])) {
            continue;
        }
        $sourceColumn = $columnNamesByPid[(int) ($columns[0]['column_pid'] ?? 0)] ?? '';
        $targetColumn = $columnNamesByPid[(int) ($columns[0]['referenced_column_pid'] ?? 0)] ?? '';
        if ($sourceColumn === 'app_user_id' && $targetColumn === 'app_user_id') {
            $requiredForeignKeys[$sourceTable] = true;
        }
    }

    $blockingGaps = [];
    if (!$identityUnique) {
        $blockingGaps[] = 'canonical metadata cannot prove UNIQUE (issuer, subject) on ' . $identityTable . '.';
    }
    $missingForeignKeyTables = array_keys(array_filter($requiredForeignKeys, static fn (bool $present): bool => !$present));
    if ($missingForeignKeyTables !== []) {
        $blockingGaps[] = 'canonical metadata cannot prove app_user_id foreign keys to ' . $appUserTable
            . ' from: ' . implode(', ', $missingForeignKeyTables) . '.';
    }
    return [
        'blocking_gaps' => $blockingGaps,
        'evidence' => [
            'identity_unique_issuer_subject' => $identityUnique,
            'app_user_foreign_keys' => $requiredForeignKeys,
        ],
    ];
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
