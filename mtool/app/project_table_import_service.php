<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/project_scope_policy.php';
require_once __DIR__ . '/project_table_import_source.php';
require_once __DIR__ . '/table_metadata_repository.php';
require_once __DIR__ . '/table_constraint_metadata_repository.php';

function app_project_table_import_preflight(array $app, string $projectKey, string $sourceKey = 'live-schema', bool $forApply = false): array
{
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        return [
            'ok' => false,
            'error' => 'project key の形式が不正です。',
        ];
    }

    $normalizedSourceKey = app_project_table_import_source_normalize($normalizedProjectKey, $sourceKey, $app);
    $sourceOption = app_project_table_import_source_option($normalizedProjectKey, $normalizedSourceKey, $app);
    if ($forApply && ($sourceOption === null || !$sourceOption['apply_supported'])) {
        return [
            'ok' => false,
            'error' => 'この import source は preview only です。apply 対応 source を選択してください。',
        ];
    }

    $liveSourceDefinition = app_project_table_import_live_source_definition($normalizedSourceKey, $app);
    if ($liveSourceDefinition !== null) {
        $sourceProbe = app_probe_database_source($app, $liveSourceDefinition['database_source_key']);
        if (!$sourceProbe['ok']) {
            return [
                'ok' => false,
                'error' => 'import source DB に接続できません: ' . $sourceProbe['detail'],
            ];
        }
    } else {
        $reference = app_load_legacy_table_schema_reference($normalizedProjectKey);
        if (!$reference['ok']) {
            return [
                'ok' => false,
                'error' => $reference['error'],
            ];
        }
    }

    $canonicalProbe = app_probe_config_database($app);
    if (!$canonicalProbe['ok']) {
        return [
            'ok' => false,
            'error' => 'canonical metadata DB に接続できません: ' . $canonicalProbe['detail'],
        ];
    }

    return [
        'ok' => true,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     summary:array{
 *         project_key:string,
 *         source_schema_name:string,
 *         source_key:string,
 *         source_label:string,
 *         source_apply_supported:bool,
 *         source_table_count:int,
 *         live_table_count:int,
 *         canonical_table_count:int,
 *         table_insert_count:int,
 *         table_delete_count:int,
 *         table_same_count:int,
 *         table_changed_count:int,
 *         column_insert_count:int,
 *         column_update_count:int,
 *         column_delete_count:int,
 *         column_same_count:int,
 *         review_required:bool,
 *         destructive_change_count:int,
 *         metadata_update_count:int
 *     },
 *     tables:list<array{
 *         name:string,
 *         status:string,
 *         source_column_count:int,
 *         live_column_count:int,
 *         canonical_column_count:int,
 *         column_insert_count:int,
 *         column_update_count:int,
 *         column_delete_count:int,
 *         column_same_count:int,
 *         review:array{
 *             risk_level:string,
 *             requires_review:bool,
 *             reasons:list<string>,
 *             column_changes:list<array<string,mixed>>
 *         }
 *     }>,
 *     errors:list<string>,
 *     error:string
 * }
 */
function app_project_table_import_preview(
    array $app,
    string $projectKey,
    string $sourceKey = 'live-schema',
    string $focusTableName = '',
): array
{
    $preflight = app_project_table_import_preflight($app, $projectKey, $sourceKey);
    if (!$preflight['ok']) {
        return [
            'ok' => false,
            'summary' => app_project_table_import_empty_summary(
                $projectKey,
                (string) ($app['db']['name'] ?? ''),
                app_project_table_import_source_normalize($projectKey, $sourceKey, $app),
                '',
                false,
            ),
            'tables' => [],
            'errors' => [$preflight['error']],
            'error' => $preflight['error'],
        ];
    }

    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $sourceResult = app_project_table_import_source_resolve($app, $normalizedProjectKey, $sourceKey);
    if (!$sourceResult['ok']) {
        return [
            'ok' => false,
            'summary' => app_project_table_import_empty_summary(
                $normalizedProjectKey,
                $sourceResult['source_schema_name'],
                $sourceResult['source_key'],
                $sourceResult['source_label'],
                $sourceResult['apply_supported'],
            ),
            'tables' => [],
            'errors' => [$sourceResult['error']],
            'error' => $sourceResult['error'],
        ];
    }

    $canonicalSnapshot = app_fetch_table_metadata_snapshot($app, $normalizedProjectKey);
    if (!$canonicalSnapshot['ok']) {
        return [
            'ok' => false,
            'summary' => app_project_table_import_empty_summary(
                $normalizedProjectKey,
                $sourceResult['source_schema_name'],
                $sourceResult['source_key'],
                $sourceResult['source_label'],
                $sourceResult['apply_supported'],
            ),
            'tables' => [],
            'errors' => [$canonicalSnapshot['error']],
            'error' => $canonicalSnapshot['error'],
        ];
    }

    $managedSourceTables = app_project_table_import_managed_source_tables($sourceResult);
    $previewCanonicalTables = app_project_table_import_preview_canonical_tables($canonicalSnapshot['items'], $sourceResult);
    $focusScope = app_project_table_import_focus_scope(
        $focusTableName,
        $managedSourceTables,
        $previewCanonicalTables,
    );
    if (!$focusScope['ok']) {
        return [
            'ok' => false,
            'summary' => app_project_table_import_empty_summary(
                $normalizedProjectKey,
                $sourceResult['source_schema_name'],
                $sourceResult['source_key'],
                $sourceResult['source_label'],
                $sourceResult['apply_supported'],
            ),
            'tables' => [],
            'errors' => [$focusScope['error']],
            'error' => $focusScope['error'],
        ];
    }

    return app_project_table_import_build_plan(
        $normalizedProjectKey,
        $sourceResult['source_schema_name'],
        $sourceResult['source_key'],
        $sourceResult['source_label'],
        $sourceResult['apply_supported'],
        $focusScope['source_tables'],
        $focusScope['canonical_tables'],
        (bool) ($sourceResult['constraints_supported'] ?? false),
        is_array($sourceResult['constraints'] ?? null) ? $sourceResult['constraints'] : [],
        trim($focusTableName) !== '',
    );
}

/**
 * @return array{
 *     ok:bool,
 *     summary:array{
 *         project_key:string,
 *         source_schema_name:string,
 *         source_key:string,
 *         source_label:string,
 *         source_apply_supported:bool,
 *         source_table_count:int,
 *         live_table_count:int,
 *         canonical_table_count:int,
 *         table_insert_count:int,
 *         table_delete_count:int,
 *         table_same_count:int,
 *         table_changed_count:int,
 *         column_insert_count:int,
 *         column_update_count:int,
 *         column_delete_count:int,
 *         column_same_count:int,
 *         review_required:bool,
 *         destructive_change_count:int,
 *         metadata_update_count:int
 *     },
 *     tables:list<array{
 *         name:string,
 *         status:string,
 *         source_column_count:int,
 *         live_column_count:int,
 *         canonical_column_count:int,
 *         column_insert_count:int,
 *         column_update_count:int,
 *         column_delete_count:int,
 *         column_same_count:int,
 *         review:array{
 *             risk_level:string,
 *             requires_review:bool,
 *             reasons:list<string>,
 *             column_changes:list<array<string,mixed>>
 *         }
 *     }>,
 *     errors:list<string>,
 *     error:string
 * }
 */
function app_project_table_import_apply(
    array $app,
    string $projectKey,
    string $sourceKey = 'live-schema',
    string $focusTableName = '',
): array
{
    $preview = app_project_table_import_preview($app, $projectKey, $sourceKey, $focusTableName);
    if (!$preview['ok']) {
        return $preview;
    }

    $preflight = app_project_table_import_preflight($app, $projectKey, $sourceKey, true);
    if (!$preflight['ok']) {
        return [
            'ok' => false,
            'summary' => $preview['summary'],
            'tables' => $preview['tables'],
            'errors' => [$preflight['error']],
            'error' => $preflight['error'],
        ];
    }

    $normalizedProjectKey = $preview['summary']['project_key'];
    $sourceResult = app_project_table_import_source_resolve($app, $normalizedProjectKey, $sourceKey);
    if (!$sourceResult['ok']) {
        return [
            'ok' => false,
            'summary' => $preview['summary'],
            'tables' => $preview['tables'],
            'errors' => [$sourceResult['error']],
            'error' => $sourceResult['error'],
        ];
    }

    $canonicalSnapshot = app_fetch_table_metadata_snapshot($app, $normalizedProjectKey);
    if (!$canonicalSnapshot['ok']) {
        return [
            'ok' => false,
            'summary' => $preview['summary'],
            'tables' => $preview['tables'],
            'errors' => [$canonicalSnapshot['error']],
            'error' => $canonicalSnapshot['error'],
        ];
    }

    try {
        $pdo = app_create_config_pdo($app);
        $dialect = app_sql_dialect_from_db_config(app_database_config($app, 'config_db'));
        $isNullIdentifier = app_sql_identifier($dialect, 'IsNull');
        $projectId = app_table_metadata_pdo_resolve_project_id($pdo, $normalizedProjectKey);
        $managedSourceTables = app_project_table_import_managed_source_tables($sourceResult);
        $managedCanonicalTables = app_project_table_import_managed_canonical_tables($canonicalSnapshot['items'], $sourceResult);
        $focusScope = app_project_table_import_focus_scope(
            $focusTableName,
            $managedSourceTables,
            $managedCanonicalTables,
        );
        if (!$focusScope['ok']) {
            return [
                'ok' => false,
                'summary' => $preview['summary'],
                'tables' => $preview['tables'],
                'errors' => [$focusScope['error']],
                'error' => $focusScope['error'],
            ];
        }

        $managedSourceTables = $focusScope['source_tables'];
        $managedCanonicalTables = $focusScope['canonical_tables'];
        $canonicalByName = app_project_table_import_canonical_by_name($managedCanonicalTables);
        $sourceByName = app_project_table_import_source_by_name($managedSourceTables);

        $pdo->beginTransaction();

        foreach ($managedSourceTables as $table) {
            $tableName = $table['name'];
            $tablePhysicalName = (string) ($table['physical_name'] ?? $tableName);
            $existingTable = $canonicalByName[$tableName] ?? null;
            $tablePid = $existingTable['pid'] ?? '';
            if ($tablePid === '') {
                $statement = $pdo->prepare(
                    'INSERT INTO dbtable (ProjectPID, name, physical_name)
                     VALUES (:project_id, :name, :physical_name)'
                );
                $statement->execute([
                    ':project_id' => $projectId,
                    ':name' => $tableName,
                    ':physical_name' => $tablePhysicalName,
                ]);
                $tablePid = (string) $pdo->lastInsertId();
            } elseif ((string) ($existingTable['physical_name'] ?? $existingTable['name']) !== $tablePhysicalName) {
                $updateTable = $pdo->prepare(
                    'UPDATE dbtable
                     SET physical_name = :physical_name
                     WHERE PID = :pid
                       AND ProjectPID = :project_id'
                );
                $updateTable->execute([
                    ':physical_name' => $tablePhysicalName,
                    ':pid' => (int) $tablePid,
                    ':project_id' => $projectId,
                ]);
            }

            $existingColumnsByName = [];
            if ($existingTable !== null) {
                foreach ($existingTable['columns'] as $column) {
                    $existingColumnsByName[$column['name']] = $column;
                }
            }

            foreach ($table['columns'] as $column) {
                $existingColumn = $existingColumnsByName[$column['name']] ?? null;
                if ($existingColumn === null) {
                    $insert = $pdo->prepare(
                        'INSERT INTO dbtablecolumns (
                            ProjectPID,
                            dbtablePID,
                            name,
                            physical_name,
                            datatype,
                            ' . $isNullIdentifier . ',
                            IsKey,
                            IsDefault,
                            Extra,
                            ColumnListOrder,
                            memo
                        ) VALUES (
                            :project_id,
                            :dbtable_pid,
                            :name,
                            :physical_name,
                            :datatype,
                            :is_null,
                            :is_key,
                            :is_default,
                            :extra,
                            :column_list_order,
                            :memo
                        )'
                    );
                    $insert->execute([
                        ':project_id' => $projectId,
                        ':dbtable_pid' => (int) $tablePid,
                        ':name' => $column['name'],
                        ':physical_name' => (string) ($column['physical_name'] ?? $column['name']),
                        ':datatype' => $column['datatype'],
                        ':is_null' => $column['is_null'],
                        ':is_key' => $column['is_key'],
                        ':is_default' => $column['is_default'],
                        ':extra' => $column['extra'],
                        ':column_list_order' => $column['column_list_order'],
                        ':memo' => '',
                    ]);
                    continue;
                }

                if (!app_project_table_import_column_matches($column, $existingColumn)) {
                    $update = $pdo->prepare(
                        'UPDATE dbtablecolumns
                         SET
                            datatype = :datatype,
                            physical_name = :physical_name,
                            ' . $isNullIdentifier . ' = :is_null,
                            IsKey = :is_key,
                            IsDefault = :is_default,
                            Extra = :extra,
                            ColumnListOrder = :column_list_order,
                            memo = :memo
                         WHERE PID = :pid
                           AND ProjectPID = :project_id'
                    );
                    $update->execute([
                        ':datatype' => $column['datatype'],
                        ':physical_name' => (string) ($column['physical_name'] ?? $column['name']),
                        ':is_null' => $column['is_null'],
                        ':is_key' => $column['is_key'],
                        ':is_default' => $column['is_default'],
                        ':extra' => $column['extra'],
                        ':column_list_order' => $column['column_list_order'],
                        ':memo' => $existingColumn['memo'],
                        ':pid' => (int) $existingColumn['pid'],
                        ':project_id' => $projectId,
                    ]);
                }
            }

            if ($existingTable !== null) {
                foreach ($existingTable['columns'] as $existingColumn) {
                    if (array_key_exists($existingColumn['name'], $table['columns_by_name'])) {
                        continue;
                    }

                    $delete = $pdo->prepare(
                        'DELETE FROM dbtablecolumns
                         WHERE PID = :pid
                           AND ProjectPID = :project_id'
                    );
                    $delete->execute([
                        ':pid' => (int) $existingColumn['pid'],
                        ':project_id' => $projectId,
                    ]);
                }
            }
        }

        foreach ($managedCanonicalTables as $existingTable) {
            if (array_key_exists($existingTable['name'], $sourceByName)) {
                continue;
            }

            $delete = $pdo->prepare(
                'DELETE FROM dbtable
                 WHERE PID = :pid
                   AND ProjectPID = :project_id'
            );
            $delete->execute([
                ':pid' => (int) $existingTable['pid'],
                ':project_id' => $projectId,
            ]);
        }

        if (($sourceResult['constraints_supported'] ?? false) === true && trim($focusTableName) === '') {
            app_replace_project_table_constraints_portable_pdo(
                $pdo,
                $projectId,
                is_array($sourceResult['constraints'] ?? null) ? $sourceResult['constraints'] : [],
            );
        }

        $pdo->commit();
    } catch (Throwable $throwable) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'summary' => $preview['summary'],
            'tables' => $preview['tables'],
            'errors' => [$throwable->getMessage()],
            'error' => $throwable->getMessage(),
        ];
    }

    return [
        'ok' => true,
        'summary' => $preview['summary'],
        'tables' => $preview['tables'],
        'errors' => [],
        'error' => '',
    ];
}

/**
 * @param list<array<string,mixed>> $canonicalTables
 * @param array{
 *     managed_target_table_names?:list<string>,
 *     compare_against_all_canonical?:bool,
 *     tables?:list<array{name:string}>
 * } $sourceResult
 * @return list<array<string,mixed>>
 */
function app_project_table_import_preview_canonical_tables(array $canonicalTables, array $sourceResult): array
{
    if (($sourceResult['compare_against_all_canonical'] ?? false) === true) {
        return $canonicalTables;
    }

    return app_project_table_import_managed_canonical_tables($canonicalTables, $sourceResult);
}

/**
 * @param array{
 *     managed_target_table_names?:list<string>,
 *     tables?:list<array<string,mixed>>
 * } $sourceResult
 * @return list<array<string,mixed>>
 */
function app_project_table_import_managed_source_tables(array $sourceResult): array
{
    $sourceTables = $sourceResult['tables'] ?? [];
    if (!is_array($sourceTables) || $sourceTables === []) {
        return [];
    }

    $managedTargetTableNames = $sourceResult['managed_target_table_names'] ?? [];
    if (!is_array($managedTargetTableNames) || $managedTargetTableNames === []) {
        return $sourceTables;
    }

    $managedNameSet = [];
    foreach ($managedTargetTableNames as $tableName) {
        $normalizedTableName = trim((string) $tableName);
        if ($normalizedTableName === '') {
            continue;
        }
        $managedNameSet[$normalizedTableName] = true;
    }

    if ($managedNameSet === []) {
        return $sourceTables;
    }

    $filteredTables = [];
    foreach ($sourceTables as $sourceTable) {
        if (!is_array($sourceTable)) {
            continue;
        }

        $tableName = trim((string) ($sourceTable['name'] ?? ''));
        if ($tableName === '' || !isset($managedNameSet[$tableName])) {
            continue;
        }

        $filteredTables[] = $sourceTable;
    }

    return $filteredTables;
}

/**
 * @param list<array<string,mixed>> $canonicalTables
 * @param array{
 *     managed_target_table_names?:list<string>,
 *     tables?:list<array{name:string}>
 * } $sourceResult
 * @return list<array<string,mixed>>
 */
function app_project_table_import_managed_canonical_tables(array $canonicalTables, array $sourceResult): array
{
    $managedTargetTableNames = $sourceResult['managed_target_table_names'] ?? [];
    if (!is_array($managedTargetTableNames) || $managedTargetTableNames === []) {
        $managedTargetTableNames = [];
        foreach (($sourceResult['tables'] ?? []) as $sourceTable) {
            if (!is_array($sourceTable)) {
                continue;
            }
            $tableName = trim((string) ($sourceTable['name'] ?? ''));
            if ($tableName === '') {
                continue;
            }
            $managedTargetTableNames[] = $tableName;
        }
    }

    if ($managedTargetTableNames === []) {
        return [];
    }

    $managedNameSet = [];
    foreach ($managedTargetTableNames as $tableName) {
        $normalizedTableName = trim((string) $tableName);
        if ($normalizedTableName === '') {
            continue;
        }
        $managedNameSet[$normalizedTableName] = true;
    }

    $filteredTables = [];
    foreach ($canonicalTables as $canonicalTable) {
        if (!is_array($canonicalTable)) {
            continue;
        }

        $tableName = trim((string) ($canonicalTable['name'] ?? ''));
        if ($tableName === '' || !isset($managedNameSet[$tableName])) {
            continue;
        }

        $filteredTables[] = $canonicalTable;
    }

    return $filteredTables;
}

/**
 * @param list<array<string,mixed>> $sourceTables
 * @param list<array<string,mixed>> $canonicalTables
 * @return array{
 *     ok:bool,
 *     focus_table_name:string,
 *     source_tables:list<array<string,mixed>>,
 *     canonical_tables:list<array<string,mixed>>,
 *     error:string
 * }
 */
function app_project_table_import_focus_scope(
    string $focusTableName,
    array $sourceTables,
    array $canonicalTables,
): array {
    $normalizedFocusTableName = trim($focusTableName);
    if ($normalizedFocusTableName === '') {
        return [
            'ok' => true,
            'focus_table_name' => '',
            'source_tables' => $sourceTables,
            'canonical_tables' => $canonicalTables,
            'error' => '',
        ];
    }

    $resolvedTableName = app_project_table_import_resolve_focus_table_name(
        $normalizedFocusTableName,
        $sourceTables,
        $canonicalTables,
    );
    if ($resolvedTableName === '') {
        return [
            'ok' => false,
            'focus_table_name' => '',
            'source_tables' => [],
            'canonical_tables' => [],
            'error' => 'focus table が import scope に見つかりません: ' . $normalizedFocusTableName,
        ];
    }

    return [
        'ok' => true,
        'focus_table_name' => $resolvedTableName,
        'source_tables' => app_project_table_import_filter_tables_by_name($sourceTables, $resolvedTableName),
        'canonical_tables' => app_project_table_import_filter_tables_by_name($canonicalTables, $resolvedTableName),
        'error' => '',
    ];
}

/**
 * @param list<array<string,mixed>> $sourceTables
 * @param list<array<string,mixed>> $canonicalTables
 */
function app_project_table_import_resolve_focus_table_name(
    string $focusTableName,
    array $sourceTables,
    array $canonicalTables,
): string {
    $candidates = array_merge($sourceTables, $canonicalTables);
    foreach ($candidates as $table) {
        $tableName = trim((string) ($table['name'] ?? ''));
        if ($tableName === '') {
            continue;
        }

        if (strcasecmp($tableName, $focusTableName) === 0) {
            return $tableName;
        }
    }

    return '';
}

/**
 * @param list<array<string,mixed>> $tables
 * @return list<array<string,mixed>>
 */
function app_project_table_import_filter_tables_by_name(array $tables, string $tableName): array
{
    $filtered = [];
    foreach ($tables as $table) {
        $candidateTableName = trim((string) ($table['name'] ?? ''));
        if ($candidateTableName === '') {
            continue;
        }

        if (strcasecmp($candidateTableName, $tableName) !== 0) {
            continue;
        }

        $filtered[] = $table;
    }

    return $filtered;
}

function app_project_table_import_column_matches(array $liveColumn, array $canonicalColumn): bool
{
    return $liveColumn['name'] === $canonicalColumn['name']
        && (string) ($liveColumn['physical_name'] ?? $liveColumn['name']) === (string) ($canonicalColumn['physical_name'] ?? $canonicalColumn['name'])
        && $liveColumn['datatype'] === $canonicalColumn['datatype']
        && $liveColumn['is_null'] === $canonicalColumn['is_null']
        && $liveColumn['is_key'] === $canonicalColumn['is_key']
        && $liveColumn['is_default'] === $canonicalColumn['is_default']
        && $liveColumn['extra'] === $canonicalColumn['extra']
        && $liveColumn['column_list_order'] === $canonicalColumn['column_list_order'];
}

function app_project_table_import_build_plan(
    string $projectKey,
    string $schemaName,
    string $sourceKey,
    string $sourceLabel,
    bool $sourceApplySupported,
    array $sourceTables,
    array $canonicalTables,
    bool $constraintsSupported = false,
    array $constraints = [],
    bool $focusedImport = false,
): array {
    $canonicalByName = app_project_table_import_canonical_by_name($canonicalTables);
    $sourceByName = app_project_table_import_source_by_name($sourceTables);
    $summary = app_project_table_import_empty_summary($projectKey, $schemaName, $sourceKey, $sourceLabel, $sourceApplySupported);
    $summary['source_table_count'] = count($sourceTables);
    $summary['live_table_count'] = count($sourceTables);
    $summary['canonical_table_count'] = count($canonicalTables);
    $summary['constraints_supported'] = $constraintsSupported;
    $summary['constraint_apply_supported'] = $constraintsSupported && !$focusedImport;
    $summary['source_key_constraint_count'] = count(is_array($constraints['keys'] ?? null) ? $constraints['keys'] : []);
    $summary['source_foreign_key_constraint_count'] = count(is_array($constraints['foreign_keys'] ?? null) ? $constraints['foreign_keys'] : []);
    $tables = [];

    foreach ($sourceTables as $sourceTable) {
        $existingTable = $canonicalByName[$sourceTable['name']] ?? null;
        $columnInsertCount = 0;
        $columnUpdateCount = 0;
        $columnDeleteCount = 0;
        $columnSameCount = 0;
        $status = 'same';
        $existingColumnsByName = [];
        $columnChanges = [];
        $namingWarnings = app_project_table_import_naming_warnings($sourceTable);
        $summary['unsafe_physical_name_count'] += count($namingWarnings);

        if ($existingTable !== null) {
            foreach ($existingTable['columns'] as $column) {
                $existingColumnsByName[$column['name']] = $column;
            }
        }

        foreach ($sourceTable['columns'] as $column) {
            $existingColumn = $existingColumnsByName[$column['name']] ?? null;
            if ($existingColumn === null) {
                $columnInsertCount++;
                $columnChanges[] = app_project_table_import_column_change('insert', $column['name'], null, $column);
                continue;
            }

            if (app_project_table_import_column_matches($column, $existingColumn)) {
                $columnSameCount++;
            } else {
                $columnUpdateCount++;
                $columnChanges[] = app_project_table_import_column_change('update', $column['name'], $existingColumn, $column);
            }
        }

        if ($existingTable !== null) {
            foreach ($existingTable['columns'] as $existingColumn) {
                if (!array_key_exists($existingColumn['name'], $sourceTable['columns_by_name'])) {
                    $columnDeleteCount++;
                    $columnChanges[] = app_project_table_import_column_change('delete', $existingColumn['name'], $existingColumn, null);
                }
            }
        }

        if ($existingTable === null) {
            $status = 'new';
            $summary['table_insert_count']++;
        } elseif ($columnInsertCount > 0 || $columnUpdateCount > 0 || $columnDeleteCount > 0) {
            $status = 'changed';
            $summary['table_changed_count']++;
        } else {
            $summary['table_same_count']++;
        }

        $summary['column_insert_count'] += $columnInsertCount;
        $summary['column_update_count'] += $columnUpdateCount;
        $summary['column_delete_count'] += $columnDeleteCount;
        $summary['column_same_count'] += $columnSameCount;

        $tables[] = [
            'name' => $sourceTable['name'],
            'status' => $status,
            'source_column_count' => count($sourceTable['columns']),
            'live_column_count' => count($sourceTable['columns']),
            'canonical_column_count' => $existingTable !== null ? count($existingTable['columns']) : 0,
            'column_insert_count' => $columnInsertCount,
            'column_update_count' => $columnUpdateCount,
            'column_delete_count' => $columnDeleteCount,
            'column_same_count' => $columnSameCount,
            'review' => app_project_table_import_table_review(
                $status,
                $columnInsertCount,
                $columnUpdateCount,
                $columnDeleteCount,
                $columnChanges,
                $namingWarnings,
            ),
        ];
    }

    foreach ($canonicalTables as $existingTable) {
        if (array_key_exists($existingTable['name'], $sourceByName)) {
            continue;
        }

        $summary['table_delete_count']++;
        $summary['column_delete_count'] += count($existingTable['columns']);
        $columnChanges = [];
        foreach ($existingTable['columns'] as $existingColumn) {
            $columnChanges[] = app_project_table_import_column_change('delete', $existingColumn['name'], $existingColumn, null);
        }
        $tables[] = [
            'name' => $existingTable['name'],
            'status' => 'stale',
            'source_column_count' => 0,
            'live_column_count' => 0,
            'canonical_column_count' => count($existingTable['columns']),
            'column_insert_count' => 0,
            'column_update_count' => 0,
            'column_delete_count' => count($existingTable['columns']),
            'column_same_count' => 0,
            'review' => app_project_table_import_table_review('stale', 0, 0, count($existingTable['columns']), $columnChanges, []),
        ];
    }

    $summary['destructive_change_count'] = $summary['table_delete_count'] + $summary['column_delete_count'];
    $summary['metadata_update_count'] = $summary['table_insert_count']
        + $summary['table_changed_count']
        + $summary['column_insert_count']
        + $summary['column_update_count'];
    $summary['review_required'] = $summary['destructive_change_count'] > 0 || $summary['column_update_count'] > 0;

    usort(
        $tables,
        static fn (array $left, array $right): int => strcasecmp($left['name'], $right['name']),
    );

    return [
        'ok' => true,
        'summary' => $summary,
        'tables' => $tables,
        'errors' => [],
        'error' => '',
    ];
}

function app_project_table_import_empty_summary(
    string $projectKey,
    string $schemaName,
    string $sourceKey,
    string $sourceLabel,
    bool $sourceApplySupported,
): array
{
    return [
        'project_key' => app_normalize_project_key($projectKey),
        'source_schema_name' => $schemaName,
        'source_key' => $sourceKey,
        'source_label' => $sourceLabel,
        'source_apply_supported' => $sourceApplySupported,
        'source_table_count' => 0,
        'live_table_count' => 0,
        'canonical_table_count' => 0,
        'table_insert_count' => 0,
        'table_delete_count' => 0,
        'table_same_count' => 0,
        'table_changed_count' => 0,
        'column_insert_count' => 0,
        'column_update_count' => 0,
        'column_delete_count' => 0,
        'column_same_count' => 0,
        'unsafe_physical_name_count' => 0,
        'review_required' => false,
        'destructive_change_count' => 0,
        'metadata_update_count' => 0,
        'constraints_supported' => false,
        'constraint_apply_supported' => false,
        'source_key_constraint_count' => 0,
        'source_foreign_key_constraint_count' => 0,
    ];
}

function app_project_table_import_table_review(
    string $status,
    int $columnInsertCount,
    int $columnUpdateCount,
    int $columnDeleteCount,
    array $columnChanges,
    array $namingWarnings = [],
): array {
    $reasons = [];
    if ($status === 'new') {
        $reasons[] = 'source table is new and will create canonical table metadata on apply.';
    } elseif ($status === 'stale') {
        $reasons[] = 'canonical table is not present in the import source and will be removed on apply.';
    }

    if ($columnInsertCount > 0) {
        $reasons[] = $columnInsertCount . ' source column(s) will be added to canonical metadata.';
    }
    if ($columnUpdateCount > 0) {
        $reasons[] = $columnUpdateCount . ' canonical column definition(s) will be updated from source metadata.';
    }
    if ($columnDeleteCount > 0) {
        $reasons[] = $columnDeleteCount . ' canonical column(s) are not present in source metadata and will be removed on apply.';
    }

    $riskLevel = 'none';
    if ($status === 'stale' || $columnDeleteCount > 0) {
        $riskLevel = 'destructive';
    } elseif ($columnUpdateCount > 0) {
        $riskLevel = 'review';
    } elseif ($status === 'new' || $columnInsertCount > 0) {
        $riskLevel = 'additive';
    }

    return [
        'risk_level' => $riskLevel,
        'requires_review' => in_array($riskLevel, ['destructive', 'review'], true),
        'reasons' => $reasons,
        'naming_warnings' => $namingWarnings,
        'column_changes' => $columnChanges,
    ];
}

function app_project_table_import_naming_warnings(array $sourceTable): array
{
    $warnings = [];
    $tablePhysicalName = (string) ($sourceTable['physical_name'] ?? $sourceTable['name'] ?? '');
    if ($tablePhysicalName !== '' && !app_physical_name_is_safe_unquoted_sql_identifier($tablePhysicalName)) {
        $warnings[] = [
            'kind' => 'table',
            'physical_name' => $tablePhysicalName,
            'message' => 'physical table name is unsafe for unquoted SQL; prefer snake_case for new PostgreSQL-compatible metadata.',
        ];
    }

    foreach (($sourceTable['columns'] ?? []) as $column) {
        if (!is_array($column)) {
            continue;
        }
        $columnPhysicalName = (string) ($column['physical_name'] ?? $column['name'] ?? '');
        if ($columnPhysicalName === '' || app_physical_name_is_safe_unquoted_sql_identifier($columnPhysicalName)) {
            continue;
        }
        $warnings[] = [
            'kind' => 'column',
            'physical_name' => $columnPhysicalName,
            'message' => 'physical column name is unsafe for unquoted SQL; prefer snake_case for new PostgreSQL-compatible metadata.',
        ];
    }

    return $warnings;
}

function app_project_table_import_column_change(
    string $status,
    string $columnName,
    ?array $before,
    ?array $after,
): array {
    $change = [
        'name' => $columnName,
        'status' => $status,
    ];

    if ($before !== null) {
        $change['before'] = app_project_table_import_column_review_shape($before);
    }
    if ($after !== null) {
        $change['after'] = app_project_table_import_column_review_shape($after);
    }

    return $change;
}

function app_project_table_import_column_review_shape(array $column): array
{
    return [
        'datatype' => (string) ($column['datatype'] ?? ''),
        'is_null' => (int) ($column['is_null'] ?? 0),
        'is_key' => (int) ($column['is_key'] ?? 0),
        'is_default' => (int) ($column['is_default'] ?? 0),
        'extra' => (string) ($column['extra'] ?? ''),
        'column_list_order' => (int) ($column['column_list_order'] ?? 0),
    ];
}

function app_project_table_import_canonical_by_name(array $tables): array
{
    $result = [];
    foreach ($tables as $table) {
        $result[$table['name']] = $table;
    }

    return $result;
}

function app_project_table_import_source_by_name(array $tables): array
{
    $result = [];
    foreach ($tables as $table) {
        $result[$table['name']] = $table;
    }

    return $result;
}
