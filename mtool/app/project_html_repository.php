<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/html_template_repository.php';
require_once __DIR__ . '/legacy_db_access_reference.php';
require_once __DIR__ . '/legacy_html_reference.php';

function app_project_html_create_dataclass_name(string $basename): string
{
    return trim($basename) . 'Data';
}

function app_project_html_create_db_access_class_name(string $basename): string
{
    return trim($basename) . 'DBAccess';
}

/**
 * @param list<array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     html_key:string,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int,
 *     last_modified_dt:string
 * }> $htmlCatalog
 * @return array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     html_key:string,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int,
 *     last_modified_dt:string
 * }|null
 */
function app_project_html_find_catalog_item_by_key(array $htmlCatalog, string $htmlKey): ?array
{
    $normalizedHtmlKey = trim($htmlKey);
    if ($normalizedHtmlKey === '') {
        return null;
    }

    foreach ($htmlCatalog as $html) {
        if ((string) ($html['html_key'] ?? '') === $normalizedHtmlKey) {
            return $html;
        }
    }

    return null;
}

/**
 * @param list<array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     html_key:string,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int,
 *     last_modified_dt:string
 * }> $htmlCatalog
 * @return array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     html_key:string,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int,
 *     last_modified_dt:string
 * }|null
 */
function app_project_html_find_catalog_item_by_legacy_pid(array $htmlCatalog, int $legacyHtmlPid): ?array
{
    if ($legacyHtmlPid <= 0) {
        return null;
    }

    foreach ($htmlCatalog as $html) {
        if ((int) ($html['legacy_html_pid'] ?? 0) === $legacyHtmlPid) {
            return $html;
        }
    }

    return null;
}

/**
 * @param list<array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     legacy_parameter_pid:int,
 *     parameter_name:string,
 *     parameter_value:string
 * }> $parameterCatalog
 * @return array<string,list<array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     legacy_parameter_pid:int,
 *     parameter_name:string,
 *     parameter_value:string
 * }>
 */
function app_project_html_parameter_catalog_by_html_pid(array $parameterCatalog): array
{
    $grouped = [];
    foreach ($parameterCatalog as $parameter) {
        $legacyHtmlPid = (string) ($parameter['legacy_html_pid'] ?? 0);
        if ($legacyHtmlPid === '0') {
            continue;
        }

        if (!array_key_exists($legacyHtmlPid, $grouped)) {
            $grouped[$legacyHtmlPid] = [];
        }

        $grouped[$legacyHtmlPid][] = $parameter;
    }

    return $grouped;
}

/**
 * @param list<array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string
 * }> $templateCatalog
 * @return array<string,array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string
 * }>
 */
function app_project_html_template_catalog_by_pid(array $templateCatalog): array
{
    return app_html_template_catalog_by_pid($templateCatalog);
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         legacy_html_template_pid:int,
 *         target_type:string,
 *         parent_html_template_pid:int,
 *         name:string,
 *         program_language:string,
 *         file_name:string,
 *         comment:string
 *     }>,
 *     error:string
 * }
 */
function app_project_html_reference_template_catalog(string $projectKey): array
{
    if (trim($projectKey) === '') {
        return [
            'ok' => true,
            'items' => [],
            'error' => '',
        ];
    }

    $reference = app_load_legacy_html_reference($projectKey);
    if (!$reference['ok'] || $reference['item'] === null) {
        return [
            'ok' => true,
            'items' => [],
            'error' => '',
        ];
    }

    return [
        'ok' => true,
        'items' => $reference['item']['templates'],
        'error' => '',
    ];
}

function app_project_html_pdo_table_exists(PDO $pdo, string $tableName): bool
{
    $normalizedTableName = trim($tableName);
    if ($normalizedTableName === '') {
        return false;
    }

    static $cache = [];

    $cacheKey = spl_object_id($pdo) . ':' . $normalizedTableName;
    if (array_key_exists($cacheKey, $cache)) {
        return $cache[$cacheKey];
    }

    $cache[$cacheKey] = app_sql_table_exists($pdo, $normalizedTableName);

    return $cache[$cacheKey];
}

function app_project_html_datetime_select_expr(
    PDO $pdo,
    string $columnExpression,
    string $alias = 'last_modified_dt',
): string {
    return app_sql_datetime_select_expr(
        app_sql_dialect_from_pdo($pdo),
        $columnExpression,
        $alias,
    );
}

function app_project_html_canonical_tables_available(PDO $pdo): bool
{
    return app_project_html_pdo_table_exists($pdo, 'project_html_definitions')
        && app_project_html_pdo_table_exists($pdo, 'project_html_parameters');
}

function app_project_html_pdo_resolve_project_id(PDO $pdo, string $projectKey, int $fallbackProjectPid = 0): int
{
    $normalizedProjectKey = trim($projectKey);
    if ($normalizedProjectKey !== '') {
        $statement = $pdo->prepare(
            'SELECT id
            FROM projects
            WHERE project_key = :project_key
            LIMIT 1'
        );
        $statement->execute([
            ':project_key' => $normalizedProjectKey,
        ]);

        $projectId = $statement->fetchColumn();
        if (is_numeric($projectId)) {
            return (int) $projectId;
        }
    }

    if ($fallbackProjectPid > 0) {
        return $fallbackProjectPid;
    }

    throw new RuntimeException('project が見つかりません。');
}

function app_project_html_bootstrap_reference_source_of_truth(): string
{
    return 'bootstrap-reference';
}

function app_project_html_bootstrap_reference_notes(): string
{
    return 'Copied from legacy html reference catalog; source_dump_path stays provenance-only host metadata.';
}

/**
 * @param array<string,mixed> $html
 */
function app_project_html_canonical_upsert_definition_sqlite(
    PDO $pdo,
    int $projectId,
    array $html,
    int $htmlListOrder,
    string $lastModifiedDt,
): void {
    $existing = app_project_html_canonical_definition_row_by_legacy_pid(
        $pdo,
        $projectId,
        (int) ($html['legacy_html_pid'] ?? 0),
    );

    if ($existing !== null) {
        $statement = $pdo->prepare(
            'UPDATE project_html_definitions
            SET
                html_key = :html_key,
                name = :name,
                legacy_project_source_output_pid = :legacy_project_source_output_pid,
                legacy_html_template_pid = :legacy_html_template_pid,
                html_list_order = :html_list_order,
                last_modified_dt = :last_modified_dt,
                notes = :notes,
                source_of_truth = :source_of_truth,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id'
        );
        $statement->execute([
            ':id' => $existing['id'],
            ':html_key' => (string) ($html['html_key'] ?? ''),
            ':name' => (string) ($html['name'] ?? ''),
            ':legacy_project_source_output_pid' => (int) ($html['legacy_project_source_output_pid'] ?? 0),
            ':legacy_html_template_pid' => (int) ($html['legacy_html_template_pid'] ?? 0),
            ':html_list_order' => $htmlListOrder,
            ':last_modified_dt' => $lastModifiedDt,
            ':notes' => app_project_html_bootstrap_reference_notes(),
            ':source_of_truth' => app_project_html_bootstrap_reference_source_of_truth(),
        ]);

        return;
    }

    $statement = $pdo->prepare(
        'INSERT INTO project_html_definitions (
            project_id,
            legacy_html_pid,
            html_key,
            name,
            legacy_project_source_output_pid,
            legacy_html_template_pid,
            html_list_order,
            last_modified_dt,
            notes,
            source_of_truth
        ) VALUES (
            :project_id,
            :legacy_html_pid,
            :html_key,
            :name,
            :legacy_project_source_output_pid,
            :legacy_html_template_pid,
            :html_list_order,
            :last_modified_dt,
            :notes,
            :source_of_truth
        )'
    );
    $statement->execute([
        ':project_id' => $projectId,
        ':legacy_html_pid' => (int) ($html['legacy_html_pid'] ?? 0),
        ':html_key' => (string) ($html['html_key'] ?? ''),
        ':name' => (string) ($html['name'] ?? ''),
        ':legacy_project_source_output_pid' => (int) ($html['legacy_project_source_output_pid'] ?? 0),
        ':legacy_html_template_pid' => (int) ($html['legacy_html_template_pid'] ?? 0),
        ':html_list_order' => $htmlListOrder,
        ':last_modified_dt' => $lastModifiedDt,
        ':notes' => app_project_html_bootstrap_reference_notes(),
        ':source_of_truth' => app_project_html_bootstrap_reference_source_of_truth(),
    ]);
}

/**
 * @param array<string,mixed> $parameter
 */
function app_project_html_canonical_upsert_parameter_sqlite(
    PDO $pdo,
    int $projectId,
    int $definitionId,
    array $parameter,
    int $parameterListOrder,
): void {
    $existing = app_project_html_canonical_parameter_row_by_legacy_pid(
        $pdo,
        $projectId,
        (int) ($parameter['legacy_parameter_pid'] ?? 0),
    );

    if ($existing !== null) {
        $statement = $pdo->prepare(
            'UPDATE project_html_parameters
            SET
                project_html_definition_id = :project_html_definition_id,
                parameter_name = :parameter_name,
                parameter_value = :parameter_value,
                parameter_list_order = :parameter_list_order,
                notes = :notes,
                source_of_truth = :source_of_truth,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id'
        );
        $statement->execute([
            ':id' => $existing['id'],
            ':project_html_definition_id' => $definitionId,
            ':parameter_name' => (string) ($parameter['parameter_name'] ?? ''),
            ':parameter_value' => (string) ($parameter['parameter_value'] ?? ''),
            ':parameter_list_order' => $parameterListOrder,
            ':notes' => app_project_html_bootstrap_reference_notes(),
            ':source_of_truth' => app_project_html_bootstrap_reference_source_of_truth(),
        ]);

        return;
    }

    $statement = $pdo->prepare(
        'INSERT INTO project_html_parameters (
            project_id,
            project_html_definition_id,
            legacy_parameter_pid,
            parameter_name,
            parameter_value,
            parameter_list_order,
            notes,
            source_of_truth
        ) VALUES (
            :project_id,
            :project_html_definition_id,
            :legacy_parameter_pid,
            :parameter_name,
            :parameter_value,
            :parameter_list_order,
            :notes,
            :source_of_truth
        )'
    );
    $statement->execute([
        ':project_id' => $projectId,
        ':project_html_definition_id' => $definitionId,
        ':legacy_parameter_pid' => (int) ($parameter['legacy_parameter_pid'] ?? 0),
        ':parameter_name' => (string) ($parameter['parameter_name'] ?? ''),
        ':parameter_value' => (string) ($parameter['parameter_value'] ?? ''),
        ':parameter_list_order' => $parameterListOrder,
        ':notes' => app_project_html_bootstrap_reference_notes(),
        ':source_of_truth' => app_project_html_bootstrap_reference_source_of_truth(),
    ]);
}

function app_project_html_canonical_next_html_pid(PDO $pdo, int $projectId): int
{
    $statement = $pdo->prepare(
        'SELECT COALESCE(MAX(legacy_html_pid), 0) + 1
        FROM project_html_definitions
        WHERE project_id = :project_id'
    );
    $statement->execute([
        ':project_id' => $projectId,
    ]);

    return max(1, (int) ($statement->fetchColumn() ?? 1));
}

function app_project_html_canonical_next_html_list_order(PDO $pdo, int $projectId): int
{
    $statement = $pdo->prepare(
        'SELECT COALESCE(MAX(html_list_order), 0) + 10
        FROM project_html_definitions
        WHERE project_id = :project_id'
    );
    $statement->execute([
        ':project_id' => $projectId,
    ]);

    return max(10, (int) ($statement->fetchColumn() ?? 10));
}

function app_project_html_canonical_next_parameter_pid(PDO $pdo, int $projectId): int
{
    $statement = $pdo->prepare(
        'SELECT COALESCE(MAX(legacy_parameter_pid), 0) + 1
        FROM project_html_parameters
        WHERE project_id = :project_id'
    );
    $statement->execute([
        ':project_id' => $projectId,
    ]);

    return max(1, (int) ($statement->fetchColumn() ?? 1));
}

function app_project_html_canonical_next_parameter_list_order(PDO $pdo, int $definitionId): int
{
    $statement = $pdo->prepare(
        'SELECT COALESCE(MAX(parameter_list_order), 0) + 10
        FROM project_html_parameters
        WHERE project_html_definition_id = :definition_id'
    );
    $statement->execute([
        ':definition_id' => $definitionId,
    ]);

    return max(10, (int) ($statement->fetchColumn() ?? 10));
}

/**
 * @return array<string,bool>
 */
function app_project_html_canonical_used_html_keys(PDO $pdo, int $projectId): array
{
    $statement = $pdo->prepare(
        'SELECT html_key
        FROM project_html_definitions
        WHERE project_id = :project_id'
    );
    $statement->execute([
        ':project_id' => $projectId,
    ]);

    $usedKeys = [];
    foreach ($statement->fetchAll() as $row) {
        if (!is_array($row)) {
            continue;
        }

        $htmlKey = trim((string) ($row['html_key'] ?? ''));
        if ($htmlKey === '') {
            continue;
        }

        $usedKeys[$htmlKey] = true;
    }

    return $usedKeys;
}

function app_project_html_canonical_generate_html_key(
    PDO $pdo,
    int $projectId,
    string $name,
    int $legacyHtmlPid,
): string {
    return app_legacy_html_reference_unique_html_key(
        app_legacy_html_reference_html_key_candidate($name, $legacyHtmlPid),
        $legacyHtmlPid,
        app_project_html_canonical_used_html_keys($pdo, $projectId),
    );
}

function app_project_html_bootstrap_reference_into_canonical(PDO $pdo, string $projectKey, int $projectId): void
{
    if (!app_project_html_canonical_tables_available($pdo)) {
        return;
    }

    $countStatement = $pdo->prepare(
        'SELECT COUNT(*)
        FROM project_html_definitions
        WHERE project_id = :project_id'
    );
    $countStatement->execute([
        ':project_id' => $projectId,
    ]);

    if ((int) ($countStatement->fetchColumn() ?? 0) > 0) {
        return;
    }

    $reference = app_load_legacy_html_reference($projectKey);
    if (!$reference['ok'] || $reference['item'] === null) {
        return;
    }

    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) {
        $pdo->beginTransaction();
    }

    try {
        $dialect = app_sql_dialect_from_pdo($pdo);
        $definitionStatement = null;
        if ($dialect !== 'sqlite') {
            $definitionStatement = $pdo->prepare(
                'INSERT INTO project_html_definitions (
                    project_id,
                    legacy_html_pid,
                    html_key,
                    name,
                    legacy_project_source_output_pid,
                    legacy_html_template_pid,
                    html_list_order,
                    last_modified_dt,
                    notes,
                    source_of_truth
                ) VALUES (
                    :project_id,
                    :legacy_html_pid,
                    :html_key,
                    :name,
                    :legacy_project_source_output_pid,
                    :legacy_html_template_pid,
                    :html_list_order,
                    :last_modified_dt,
                    :notes,
                    :source_of_truth
                )
                ON DUPLICATE KEY UPDATE
                    html_key = VALUES(html_key),
                    name = VALUES(name),
                    legacy_project_source_output_pid = VALUES(legacy_project_source_output_pid),
                    legacy_html_template_pid = VALUES(legacy_html_template_pid),
                    html_list_order = VALUES(html_list_order),
                    last_modified_dt = VALUES(last_modified_dt),
                    notes = VALUES(notes),
                    source_of_truth = VALUES(source_of_truth),
                    updated_at = CURRENT_TIMESTAMP'
            );
        }

        $htmlListOrder = 10;
        foreach ($reference['item']['htmls'] as $html) {
            $lastModifiedDt = trim((string) ($html['last_modified_dt'] ?? ''));
            if ($lastModifiedDt === '') {
                $lastModifiedDt = date('Y-m-d H:i:s');
            }

            if ($dialect === 'sqlite') {
                app_project_html_canonical_upsert_definition_sqlite(
                    $pdo,
                    $projectId,
                    $html,
                    $htmlListOrder,
                    $lastModifiedDt,
                );
            } elseif ($definitionStatement instanceof PDOStatement) {
                $definitionStatement->execute([
                    ':project_id' => $projectId,
                    ':legacy_html_pid' => (int) ($html['legacy_html_pid'] ?? 0),
                    ':html_key' => (string) ($html['html_key'] ?? ''),
                    ':name' => (string) ($html['name'] ?? ''),
                    ':legacy_project_source_output_pid' => (int) ($html['legacy_project_source_output_pid'] ?? 0),
                    ':legacy_html_template_pid' => (int) ($html['legacy_html_template_pid'] ?? 0),
                    ':html_list_order' => $htmlListOrder,
                    ':last_modified_dt' => $lastModifiedDt,
                    ':notes' => app_project_html_bootstrap_reference_notes(),
                    ':source_of_truth' => app_project_html_bootstrap_reference_source_of_truth(),
                ]);
            }

            $htmlListOrder += 10;
        }

        $definitionIdStatement = $pdo->prepare(
            'SELECT id, legacy_html_pid
            FROM project_html_definitions
            WHERE project_id = :project_id'
        );
        $definitionIdStatement->execute([
            ':project_id' => $projectId,
        ]);

        $definitionIdsByLegacyHtmlPid = [];
        foreach ($definitionIdStatement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }

            $legacyHtmlPid = (int) ($row['legacy_html_pid'] ?? 0);
            $definitionId = (int) ($row['id'] ?? 0);
            if ($legacyHtmlPid <= 0 || $definitionId <= 0) {
                continue;
            }

            $definitionIdsByLegacyHtmlPid[(string) $legacyHtmlPid] = $definitionId;
        }

        $parameterStatement = null;
        if ($dialect !== 'sqlite') {
            $parameterStatement = $pdo->prepare(
                'INSERT INTO project_html_parameters (
                    project_id,
                    project_html_definition_id,
                    legacy_parameter_pid,
                    parameter_name,
                    parameter_value,
                    parameter_list_order,
                    notes,
                    source_of_truth
                ) VALUES (
                    :project_id,
                    :project_html_definition_id,
                    :legacy_parameter_pid,
                    :parameter_name,
                    :parameter_value,
                    :parameter_list_order,
                    :notes,
                    :source_of_truth
                )
                ON DUPLICATE KEY UPDATE
                    project_html_definition_id = VALUES(project_html_definition_id),
                    parameter_name = VALUES(parameter_name),
                    parameter_value = VALUES(parameter_value),
                    parameter_list_order = VALUES(parameter_list_order),
                    notes = VALUES(notes),
                    source_of_truth = VALUES(source_of_truth),
                    updated_at = CURRENT_TIMESTAMP'
            );
        }

        $parameterOrderByHtmlPid = [];
        foreach ($reference['item']['parameters'] as $parameter) {
            $legacyHtmlPid = (int) ($parameter['legacy_html_pid'] ?? 0);
            $definitionId = $definitionIdsByLegacyHtmlPid[(string) $legacyHtmlPid] ?? 0;
            if ($definitionId <= 0) {
                continue;
            }

            $orderKey = (string) $legacyHtmlPid;
            $parameterOrderByHtmlPid[$orderKey] = ($parameterOrderByHtmlPid[$orderKey] ?? 0) + 10;

            if ($dialect === 'sqlite') {
                app_project_html_canonical_upsert_parameter_sqlite(
                    $pdo,
                    $projectId,
                    $definitionId,
                    $parameter,
                    $parameterOrderByHtmlPid[$orderKey],
                );
            } elseif ($parameterStatement instanceof PDOStatement) {
                $parameterStatement->execute([
                    ':project_id' => $projectId,
                    ':project_html_definition_id' => $definitionId,
                    ':legacy_parameter_pid' => (int) ($parameter['legacy_parameter_pid'] ?? 0),
                    ':parameter_name' => (string) ($parameter['parameter_name'] ?? ''),
                    ':parameter_value' => (string) ($parameter['parameter_value'] ?? ''),
                    ':parameter_list_order' => $parameterOrderByHtmlPid[$orderKey],
                    ':notes' => app_project_html_bootstrap_reference_notes(),
                    ':source_of_truth' => app_project_html_bootstrap_reference_source_of_truth(),
                ]);
            }
        }

        if ($ownsTransaction) {
            $pdo->commit();
        }
    } catch (Throwable $throwable) {
        if ($ownsTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $throwable;
    }
}

/**
 * @return array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     html_key:string,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int,
 *     last_modified_dt:string
 * }
 */
function app_project_html_canonical_html_item_from_row(array $row): array
{
    return [
        'project_pid' => (int) ($row['project_id'] ?? 0),
        'legacy_html_pid' => (int) ($row['legacy_html_pid'] ?? 0),
        'html_key' => trim((string) ($row['html_key'] ?? '')),
        'name' => (string) ($row['name'] ?? ''),
        'legacy_project_source_output_pid' => (int) ($row['legacy_project_source_output_pid'] ?? 0),
        'legacy_html_template_pid' => (int) ($row['legacy_html_template_pid'] ?? 0),
        'last_modified_dt' => (string) ($row['last_modified_dt'] ?? ''),
    ];
}

/**
 * @return array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     legacy_parameter_pid:int,
 *     parameter_name:string,
 *     parameter_value:string
 * }
 */
function app_project_html_canonical_parameter_item_from_row(array $row): array
{
    return [
        'project_pid' => (int) ($row['project_id'] ?? 0),
        'legacy_html_pid' => (int) ($row['legacy_html_pid'] ?? 0),
        'legacy_parameter_pid' => (int) ($row['legacy_parameter_pid'] ?? 0),
        'parameter_name' => (string) ($row['parameter_name'] ?? ''),
        'parameter_value' => (string) ($row['parameter_value'] ?? ''),
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         html_key:string,
 *         name:string,
 *         legacy_project_source_output_pid:int,
 *         legacy_html_template_pid:int,
 *         last_modified_dt:string
 *     }>,
 *     error:string
 * }
 */
function app_project_html_canonical_fetch_catalog(PDO $pdo, string $projectKey, int $projectPid): array
{
    $projectId = app_project_html_pdo_resolve_project_id($pdo, $projectKey, $projectPid);
    app_project_html_bootstrap_reference_into_canonical($pdo, $projectKey, $projectId);

    $statement = $pdo->prepare(
        'SELECT
            project_id,
            legacy_html_pid,
            html_key,
            name,
            legacy_project_source_output_pid,
            legacy_html_template_pid,
            ' . app_project_html_datetime_select_expr($pdo, 'last_modified_dt') . '
        FROM project_html_definitions
        WHERE project_id = :project_id
        ORDER BY html_list_order, name, legacy_html_pid'
    );
    $statement->execute([
        ':project_id' => $projectId,
    ]);

    $items = [];
    foreach ($statement->fetchAll() as $row) {
        if (!is_array($row)) {
            continue;
        }

        $items[] = app_project_html_canonical_html_item_from_row($row);
    }

    return [
        'ok' => true,
        'items' => $items,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         html_key:string,
 *         name:string,
 *         legacy_project_source_output_pid:int,
 *         legacy_html_template_pid:int,
 *         last_modified_dt:string
 *     }>,
 *     error:string
 * }
 */
function app_project_html_legacy_fetch_catalog(PDO $pdo, string $projectKey, int $projectPid): array
{
    $statement = $pdo->prepare(
        'SELECT
            ProjectPID,
            PID,
            name,
            ProjectSourceOutputPID,
            htmlTemplatePID,
            ' . app_project_html_datetime_select_expr($pdo, 'LastModifiedDT') . '
        FROM html
        WHERE ProjectPID = :project_pid
        ORDER BY name, PID'
    );
    $statement->execute([
        ':project_pid' => $projectPid,
    ]);

    $rows = [];
    foreach ($statement->fetchAll() as $row) {
        if (!is_array($row)) {
            continue;
        }

        $rows[] = [
            'project_pid' => (int) ($row['ProjectPID'] ?? 0),
            'legacy_html_pid' => (int) ($row['PID'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'legacy_project_source_output_pid' => (int) ($row['ProjectSourceOutputPID'] ?? 0),
            'legacy_html_template_pid' => (int) ($row['htmlTemplatePID'] ?? 0),
            'last_modified_dt' => (string) ($row['last_modified_dt'] ?? ''),
        ];
    }

    return [
        'ok' => true,
        'items' => app_legacy_html_reference_assign_html_keys(
            $rows,
            app_legacy_html_reference_current_html_pid_map($projectKey),
        ),
        'error' => '',
    ];
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         html_key:string,
 *         name:string,
 *         legacy_project_source_output_pid:int,
 *         legacy_html_template_pid:int,
 *         last_modified_dt:string
 *     }>,
 *     error:string
 * }
 */
function app_fetch_project_html_catalog(array $app, string $projectKey, int $projectPid): array
{
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_project_html_canonical_tables_available($pdo)) {
            $canonicalResult = app_project_html_canonical_fetch_catalog($pdo, $projectKey, $projectPid);
            if (!$canonicalResult['ok']) {
                return $canonicalResult;
            }

            if ($canonicalResult['items'] !== [] || !app_project_html_pdo_table_exists($pdo, 'html')) {
                return $canonicalResult;
            }
        }

        if (!app_project_html_pdo_table_exists($pdo, 'html')) {
            return [
                'ok' => true,
                'items' => [],
                'error' => '',
            ];
        }

        return app_project_html_legacy_fetch_catalog($pdo, $projectKey, $projectPid);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'items' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }>,
 *     error:string
 * }
 */
function app_project_html_canonical_fetch_parameter_catalog_for_project(
    PDO $pdo,
    string $projectKey,
    int $projectPid,
): array {
    $projectId = app_project_html_pdo_resolve_project_id($pdo, $projectKey, $projectPid);
    app_project_html_bootstrap_reference_into_canonical($pdo, $projectKey, $projectId);

    $statement = $pdo->prepare(
        'SELECT
            php.project_id,
            phd.legacy_html_pid,
            php.legacy_parameter_pid,
            php.parameter_name,
            php.parameter_value
        FROM project_html_parameters AS php
        INNER JOIN project_html_definitions AS phd
            ON phd.id = php.project_html_definition_id
        WHERE php.project_id = :project_id
        ORDER BY
            phd.html_list_order,
            phd.legacy_html_pid,
            php.parameter_list_order,
            php.legacy_parameter_pid'
    );
    $statement->execute([
        ':project_id' => $projectId,
    ]);

    $items = [];
    foreach ($statement->fetchAll() as $row) {
        if (!is_array($row)) {
            continue;
        }

        $items[] = app_project_html_canonical_parameter_item_from_row($row);
    }

    return [
        'ok' => true,
        'items' => $items,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }>,
 *     error:string
 * }
 */
function app_project_html_legacy_fetch_parameter_catalog_for_project(PDO $pdo, int $projectPid): array
{
    $statement = $pdo->prepare(
        'SELECT
            ProjectPID,
            htmlPID,
            PID,
            ParameterName,
            ParameterValue
        FROM htmlParameter
        WHERE ProjectPID = :project_pid
        ORDER BY htmlPID, PID'
    );
    $statement->execute([
        ':project_pid' => $projectPid,
    ]);

    $items = [];
    foreach ($statement->fetchAll() as $row) {
        if (!is_array($row)) {
            continue;
        }

        $items[] = [
            'project_pid' => (int) ($row['ProjectPID'] ?? 0),
            'legacy_html_pid' => (int) ($row['htmlPID'] ?? 0),
            'legacy_parameter_pid' => (int) ($row['PID'] ?? 0),
            'parameter_name' => (string) ($row['ParameterName'] ?? ''),
            'parameter_value' => (string) ($row['ParameterValue'] ?? ''),
        ];
    }

    return [
        'ok' => true,
        'items' => $items,
        'error' => '',
    ];
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }>,
 *     error:string
 * }
 */
function app_fetch_project_html_parameter_catalog_for_project(
    array $app,
    string $projectKey,
    int $projectPid,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_project_html_canonical_tables_available($pdo)) {
            $canonicalResult = app_project_html_canonical_fetch_parameter_catalog_for_project(
                $pdo,
                $projectKey,
                $projectPid,
            );
            if (!$canonicalResult['ok']) {
                return $canonicalResult;
            }

            if ($canonicalResult['items'] !== [] || !app_project_html_pdo_table_exists($pdo, 'htmlParameter')) {
                return $canonicalResult;
            }
        }

        if (!app_project_html_pdo_table_exists($pdo, 'htmlParameter')) {
            return [
                'ok' => true,
                'items' => [],
                'error' => '',
            ];
        }

        return app_project_html_legacy_fetch_parameter_catalog_for_project($pdo, $projectPid);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'items' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }>,
 *     error:string
 * }
 */
function app_project_html_canonical_fetch_parameter_catalog(
    PDO $pdo,
    string $projectKey,
    int $projectPid,
    int $legacyHtmlPid,
): array {
    $projectId = app_project_html_pdo_resolve_project_id($pdo, $projectKey, $projectPid);
    app_project_html_bootstrap_reference_into_canonical($pdo, $projectKey, $projectId);

    $statement = $pdo->prepare(
        'SELECT
            php.project_id,
            phd.legacy_html_pid,
            php.legacy_parameter_pid,
            php.parameter_name,
            php.parameter_value
        FROM project_html_parameters AS php
        INNER JOIN project_html_definitions AS phd
            ON phd.id = php.project_html_definition_id
        WHERE php.project_id = :project_id
          AND phd.legacy_html_pid = :legacy_html_pid
        ORDER BY php.parameter_list_order, php.legacy_parameter_pid'
    );
    $statement->execute([
        ':project_id' => $projectId,
        ':legacy_html_pid' => $legacyHtmlPid,
    ]);

    $items = [];
    foreach ($statement->fetchAll() as $row) {
        if (!is_array($row)) {
            continue;
        }

        $items[] = app_project_html_canonical_parameter_item_from_row($row);
    }

    return [
        'ok' => true,
        'items' => $items,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }>,
 *     error:string
 * }
 */
function app_project_html_legacy_fetch_parameter_catalog(
    PDO $pdo,
    int $projectPid,
    int $legacyHtmlPid,
): array {
    $statement = $pdo->prepare(
        'SELECT
            ProjectPID,
            htmlPID,
            PID,
            ParameterName,
            ParameterValue
        FROM htmlParameter
        WHERE ProjectPID = :project_pid
          AND htmlPID = :html_pid
        ORDER BY PID'
    );
    $statement->execute([
        ':project_pid' => $projectPid,
        ':html_pid' => $legacyHtmlPid,
    ]);

    $items = [];
    foreach ($statement->fetchAll() as $row) {
        if (!is_array($row)) {
            continue;
        }

        $items[] = [
            'project_pid' => (int) ($row['ProjectPID'] ?? 0),
            'legacy_html_pid' => (int) ($row['htmlPID'] ?? 0),
            'legacy_parameter_pid' => (int) ($row['PID'] ?? 0),
            'parameter_name' => (string) ($row['ParameterName'] ?? ''),
            'parameter_value' => (string) ($row['ParameterValue'] ?? ''),
        ];
    }

    return [
        'ok' => true,
        'items' => $items,
        'error' => '',
    ];
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }>,
 *     error:string
 * }
 */
function app_fetch_project_html_parameter_catalog(
    array $app,
    string $projectKey,
    int $projectPid,
    int $legacyHtmlPid,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_project_html_canonical_tables_available($pdo)) {
            $canonicalResult = app_project_html_canonical_fetch_parameter_catalog(
                $pdo,
                $projectKey,
                $projectPid,
                $legacyHtmlPid,
            );
            if (!$canonicalResult['ok']) {
                return $canonicalResult;
            }

            if ($canonicalResult['items'] !== [] || !app_project_html_pdo_table_exists($pdo, 'htmlParameter')) {
                return $canonicalResult;
            }
        }

        if (!app_project_html_pdo_table_exists($pdo, 'htmlParameter')) {
            return [
                'ok' => true,
                'items' => [],
                'error' => '',
            ];
        }

        return app_project_html_legacy_fetch_parameter_catalog($pdo, $projectPid, $legacyHtmlPid);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'items' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }|null,
 *     error:string
 * }
 */
function app_project_html_canonical_fetch_parameter_by_pid(
    PDO $pdo,
    string $projectKey,
    int $projectPid,
    int $legacyParameterPid,
): array {
    $projectId = app_project_html_pdo_resolve_project_id($pdo, $projectKey, $projectPid);
    app_project_html_bootstrap_reference_into_canonical($pdo, $projectKey, $projectId);

    $statement = $pdo->prepare(
        'SELECT
            php.project_id,
            phd.legacy_html_pid,
            php.legacy_parameter_pid,
            php.parameter_name,
            php.parameter_value
        FROM project_html_parameters AS php
        INNER JOIN project_html_definitions AS phd
            ON phd.id = php.project_html_definition_id
        WHERE php.project_id = :project_id
          AND php.legacy_parameter_pid = :legacy_parameter_pid
        LIMIT 1'
    );
    $statement->execute([
        ':project_id' => $projectId,
        ':legacy_parameter_pid' => $legacyParameterPid,
    ]);

    $row = $statement->fetch();
    if (!is_array($row)) {
        return [
            'ok' => true,
            'item' => null,
            'error' => '',
        ];
    }

    return [
        'ok' => true,
        'item' => app_project_html_canonical_parameter_item_from_row($row),
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }|null,
 *     error:string
 * }
 */
function app_project_html_legacy_fetch_parameter_by_pid(
    PDO $pdo,
    int $projectPid,
    int $legacyParameterPid,
): array {
    $statement = $pdo->prepare(
        'SELECT
            ProjectPID,
            htmlPID,
            PID,
            ParameterName,
            ParameterValue
        FROM htmlParameter
        WHERE ProjectPID = :project_pid
          AND PID = :parameter_pid
        LIMIT 1'
    );
    $statement->execute([
        ':project_pid' => $projectPid,
        ':parameter_pid' => $legacyParameterPid,
    ]);

    $row = $statement->fetch();
    if (!is_array($row)) {
        return [
            'ok' => true,
            'item' => null,
            'error' => '',
        ];
    }

    return [
        'ok' => true,
        'item' => [
            'project_pid' => (int) ($row['ProjectPID'] ?? 0),
            'legacy_html_pid' => (int) ($row['htmlPID'] ?? 0),
            'legacy_parameter_pid' => (int) ($row['PID'] ?? 0),
            'parameter_name' => (string) ($row['ParameterName'] ?? ''),
            'parameter_value' => (string) ($row['ParameterValue'] ?? ''),
        ],
        'error' => '',
    ];
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }|null,
 *     error:string
 * }
 */
function app_fetch_project_html_parameter_by_pid(
    array $app,
    string $projectKey,
    int $projectPid,
    int $legacyParameterPid,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_project_html_canonical_tables_available($pdo)) {
            $canonicalResult = app_project_html_canonical_fetch_parameter_by_pid(
                $pdo,
                $projectKey,
                $projectPid,
                $legacyParameterPid,
            );
            if (!$canonicalResult['ok']) {
                return $canonicalResult;
            }

            if ($canonicalResult['item'] !== null || !app_project_html_pdo_table_exists($pdo, 'htmlParameter')) {
                return $canonicalResult;
            }
        }

        if (!app_project_html_pdo_table_exists($pdo, 'htmlParameter')) {
            return [
                'ok' => true,
                'item' => null,
                'error' => '',
            ];
        }

        return app_project_html_legacy_fetch_parameter_by_pid($pdo, $projectPid, $legacyParameterPid);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         legacy_html_template_pid:int,
 *         target_type:string,
 *         parent_html_template_pid:int,
 *         name:string,
 *         program_language:string,
 *         file_name:string,
 *         comment:string
 *     }>,
 *     error:string
 * }
 */
function app_fetch_project_html_template_catalog(array $app, string $projectKey = ''): array
{
    return app_fetch_html_template_catalog($app);
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         legacy_html_template_pid:int,
 *         legacy_template_parameter_pid:int,
 *         parameter_name:string,
 *         target_value_type:string,
 *         target_variable_or_class_object:string,
 *         target_property_of_class_object:string,
 *         another_template_pid:int,
 *         trim_last_space:int,
 *         trim_last_return:int,
 *         data_type:string
 *     }>,
 *     error:string
 * }
 */
function app_fetch_project_html_template_parameter_catalog(array $app, int $legacyTemplatePid = 0): array
{
    return app_fetch_html_template_parameter_catalog($app, $legacyTemplatePid);
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         legacy_data_class_pid:int,
 *         name:string,
 *         caption:string
 *     }>,
 *     error:string
 * }
 */
function app_fetch_project_html_dataclass_catalog(
    array $app,
    string $projectKey,
    int $projectPid,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        if (!app_project_html_pdo_table_exists($pdo, 'dataclass')) {
            return [
                'ok' => true,
                'items' => [],
                'error' => '',
            ];
        }

        $resolvedProjectId = app_project_html_pdo_resolve_project_id($pdo, $projectKey, $projectPid);
        $statement = $pdo->prepare(
            'SELECT PID, name
            FROM dataclass
            WHERE ProjectPID = :project_pid
            ORDER BY name, PID'
        );
        $statement->execute([
            ':project_pid' => $resolvedProjectId,
        ]);

        $items = [];
        foreach ($statement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }

            $name = (string) ($row['name'] ?? '');
            $items[] = [
                'legacy_data_class_pid' => (int) ($row['PID'] ?? 0),
                'name' => $name,
                'caption' => app_project_html_create_dataclass_name($name),
            ];
        }

        return [
            'ok' => true,
            'items' => $items,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'items' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         legacy_da_pid:int,
 *         name:string,
 *         caption:string
 *     }>,
 *     error:string
 * }
 */
function app_fetch_project_html_db_access_catalog(
    array $app,
    string $projectKey,
    int $projectPid,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $resolvedProjectId = app_project_html_pdo_resolve_project_id($pdo, $projectKey, $projectPid);

        if (app_project_html_pdo_table_exists($pdo, 'project_db_access_classes')) {
            $legacySourceNameMap = app_legacy_db_access_reference_current_da_pid_map($projectKey);
            $legacyPidBySourceName = [];
            foreach ($legacySourceNameMap as $legacyDaPid => $sourceName) {
                if (!array_key_exists($sourceName, $legacyPidBySourceName)) {
                    $legacyPidBySourceName[$sourceName] = (int) $legacyDaPid;
                }
            }

            $syntheticPidBase = $legacyPidBySourceName === [] ? 0 : 1000000;

            $statement = $pdo->prepare(
                'SELECT id, source_name
                FROM project_db_access_classes
                WHERE project_id = :project_id
                ORDER BY source_name, id'
            );
            $statement->execute([
                ':project_id' => $resolvedProjectId,
            ]);

            $items = [];
            foreach ($statement->fetchAll() as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $sourceName = (string) ($row['source_name'] ?? '');
                $canonicalId = (int) ($row['id'] ?? 0);
                if ($sourceName === '' || $canonicalId <= 0) {
                    continue;
                }

                $legacyDaPid = $legacyPidBySourceName[$sourceName] ?? (
                    $syntheticPidBase === 0
                        ? $canonicalId
                        : ($syntheticPidBase + $canonicalId)
                );

                $items[] = [
                    'legacy_da_pid' => $legacyDaPid,
                    'name' => $sourceName,
                    'caption' => app_project_html_create_db_access_class_name($sourceName),
                ];
            }

            if ($items !== [] || !app_project_html_pdo_table_exists($pdo, 'da')) {
                return [
                    'ok' => true,
                    'items' => $items,
                    'error' => '',
                ];
            }
        }

        if (!app_project_html_pdo_table_exists($pdo, 'da')) {
            return [
                'ok' => true,
                'items' => [],
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'SELECT PID, name
            FROM da
            WHERE ProjectPID = :project_pid
            ORDER BY name, PID'
        );
        $statement->execute([
            ':project_pid' => $resolvedProjectId,
        ]);

        $items = [];
        foreach ($statement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }

            $name = (string) ($row['name'] ?? '');
            $items[] = [
                'legacy_da_pid' => (int) ($row['PID'] ?? 0),
                'name' => $name,
                'caption' => app_project_html_create_db_access_class_name($name),
            ];
        }

        return [
            'ok' => true,
            'items' => $items,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'items' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

function app_project_html_touch_last_modified_legacy(PDO $pdo, int $projectPid, int $legacyHtmlPid): void
{
    $statement = $pdo->prepare(
        'UPDATE html
        SET LastModifiedDT = CURRENT_TIMESTAMP
        WHERE ProjectPID = :project_pid
          AND PID = :html_pid'
    );
    $statement->execute([
        ':project_pid' => $projectPid,
        ':html_pid' => $legacyHtmlPid,
    ]);
}

function app_project_html_touch_last_modified_canonical(PDO $pdo, int $projectId, int $legacyHtmlPid): void
{
    $statement = $pdo->prepare(
        'UPDATE project_html_definitions
        SET
            last_modified_dt = CURRENT_TIMESTAMP,
            updated_at = CURRENT_TIMESTAMP
        WHERE project_id = :project_id
          AND legacy_html_pid = :legacy_html_pid'
    );
    $statement->execute([
        ':project_id' => $projectId,
        ':legacy_html_pid' => $legacyHtmlPid,
    ]);
}

/**
 * @return array{
 *     id:int,
 *     legacy_html_pid:int
 * }|null
 */
function app_project_html_canonical_definition_row_by_legacy_pid(
    PDO $pdo,
    int $projectId,
    int $legacyHtmlPid,
): ?array {
    $statement = $pdo->prepare(
        'SELECT id, legacy_html_pid
        FROM project_html_definitions
        WHERE project_id = :project_id
          AND legacy_html_pid = :legacy_html_pid
        LIMIT 1'
    );
    $statement->execute([
        ':project_id' => $projectId,
        ':legacy_html_pid' => $legacyHtmlPid,
    ]);

    $row = $statement->fetch();
    if (!is_array($row)) {
        return null;
    }

    return [
        'id' => (int) ($row['id'] ?? 0),
        'legacy_html_pid' => (int) ($row['legacy_html_pid'] ?? 0),
    ];
}

/**
 * @return array{
 *     id:int,
 *     project_html_definition_id:int,
 *     legacy_html_pid:int,
 *     legacy_parameter_pid:int
 * }|null
 */
function app_project_html_canonical_parameter_row_by_legacy_pid(
    PDO $pdo,
    int $projectId,
    int $legacyParameterPid,
): ?array {
    $statement = $pdo->prepare(
        'SELECT
            php.id,
            php.project_html_definition_id,
            php.legacy_parameter_pid,
            phd.legacy_html_pid
        FROM project_html_parameters AS php
        INNER JOIN project_html_definitions AS phd
            ON phd.id = php.project_html_definition_id
        WHERE php.project_id = :project_id
          AND php.legacy_parameter_pid = :legacy_parameter_pid
        LIMIT 1'
    );
    $statement->execute([
        ':project_id' => $projectId,
        ':legacy_parameter_pid' => $legacyParameterPid,
    ]);

    $row = $statement->fetch();
    if (!is_array($row)) {
        return null;
    }

    return [
        'id' => (int) ($row['id'] ?? 0),
        'project_html_definition_id' => (int) ($row['project_html_definition_id'] ?? 0),
        'legacy_html_pid' => (int) ($row['legacy_html_pid'] ?? 0),
        'legacy_parameter_pid' => (int) ($row['legacy_parameter_pid'] ?? 0),
    ];
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @param array{
 *     project_pid:int,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int
 * } $input
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         html_key:string,
 *         name:string,
 *         legacy_project_source_output_pid:int,
 *         legacy_html_template_pid:int,
 *         last_modified_dt:string
 *     }|null,
 *     error:string
 * }
 */
function app_create_project_html(array $app, string $projectKey, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_project_html_canonical_tables_available($pdo)) {
            $projectId = app_project_html_pdo_resolve_project_id(
                $pdo,
                $projectKey,
                (int) $input['project_pid'],
            );
            app_project_html_bootstrap_reference_into_canonical($pdo, $projectKey, $projectId);

            $ownsTransaction = !$pdo->inTransaction();
            if ($ownsTransaction) {
                $pdo->beginTransaction();
            }

            try {
                $legacyHtmlPid = app_project_html_canonical_next_html_pid($pdo, $projectId);
                $statement = $pdo->prepare(
                    'INSERT INTO project_html_definitions (
                        project_id,
                        legacy_html_pid,
                        html_key,
                        name,
                        legacy_project_source_output_pid,
                        legacy_html_template_pid,
                        html_list_order,
                        last_modified_dt,
                        notes,
                        source_of_truth
                    ) VALUES (
                        :project_id,
                        :legacy_html_pid,
                        :html_key,
                        :name,
                        :legacy_project_source_output_pid,
                        :legacy_html_template_pid,
                        :html_list_order,
                        CURRENT_TIMESTAMP,
                        :notes,
                        :source_of_truth
                    )'
                );
                $statement->execute([
                    ':project_id' => $projectId,
                    ':legacy_html_pid' => $legacyHtmlPid,
                    ':html_key' => app_project_html_canonical_generate_html_key(
                        $pdo,
                        $projectId,
                        (string) ($input['name'] ?? ''),
                        $legacyHtmlPid,
                    ),
                    ':name' => (string) ($input['name'] ?? ''),
                    ':legacy_project_source_output_pid' => (int) ($input['legacy_project_source_output_pid'] ?? 0),
                    ':legacy_html_template_pid' => (int) ($input['legacy_html_template_pid'] ?? 0),
                    ':html_list_order' => app_project_html_canonical_next_html_list_order($pdo, $projectId),
                    ':notes' => '',
                    ':source_of_truth' => 'manual',
                ]);

                if ($ownsTransaction) {
                    $pdo->commit();
                }

                $catalogResult = app_fetch_project_html_catalog($app, $projectKey, $projectId);
                if (!$catalogResult['ok']) {
                    return [
                        'ok' => false,
                        'item' => null,
                        'error' => $catalogResult['error'],
                    ];
                }

                $item = app_project_html_find_catalog_item_by_legacy_pid(
                    $catalogResult['items'],
                    $legacyHtmlPid,
                );

                return [
                    'ok' => $item !== null,
                    'item' => $item,
                    'error' => $item !== null ? '' : '追加した html row を再読込できませんでした。',
                ];
            } catch (Throwable $throwable) {
                if ($ownsTransaction && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                throw $throwable;
            }
        }

        if (!app_project_html_pdo_table_exists($pdo, 'html')) {
            return [
                'ok' => false,
                'item' => null,
                'error' => 'HTML canonical table も legacy html table も存在しません。',
            ];
        }

        $statement = $pdo->prepare(
            'INSERT INTO html (
                ProjectPID,
                name,
                ProjectSourceOutputPID,
                htmlTemplatePID,
                LastModifiedDT
            ) VALUES (
                :project_pid,
                :name,
                :project_source_output_pid,
                :html_template_pid,
                CURRENT_TIMESTAMP
            )'
        );
        $statement->execute([
            ':project_pid' => $input['project_pid'],
            ':name' => $input['name'],
            ':project_source_output_pid' => $input['legacy_project_source_output_pid'],
            ':html_template_pid' => $input['legacy_html_template_pid'],
        ]);

        $catalogResult = app_fetch_project_html_catalog(
            $app,
            $projectKey,
            (int) $input['project_pid'],
        );
        if (!$catalogResult['ok']) {
            return [
                'ok' => false,
                'item' => null,
                'error' => $catalogResult['error'],
            ];
        }

        $item = app_project_html_find_catalog_item_by_legacy_pid(
            $catalogResult['items'],
            (int) $pdo->lastInsertId(),
        );

        return [
            'ok' => $item !== null,
            'item' => $item,
            'error' => $item !== null ? '' : '追加した html row を再読込できませんでした。',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @param array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int
 * } $input
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         html_key:string,
 *         name:string,
 *         legacy_project_source_output_pid:int,
 *         legacy_html_template_pid:int,
 *         last_modified_dt:string
 *     }|null,
 *     error:string
 * }
 */
function app_update_project_html(array $app, string $projectKey, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_project_html_canonical_tables_available($pdo)) {
            $projectId = app_project_html_pdo_resolve_project_id(
                $pdo,
                $projectKey,
                (int) $input['project_pid'],
            );
            app_project_html_bootstrap_reference_into_canonical($pdo, $projectKey, $projectId);

            $statement = $pdo->prepare(
                'UPDATE project_html_definitions
                SET
                    name = :name,
                    legacy_project_source_output_pid = :legacy_project_source_output_pid,
                    legacy_html_template_pid = :legacy_html_template_pid,
                    last_modified_dt = CURRENT_TIMESTAMP,
                    notes = :notes,
                    source_of_truth = :source_of_truth,
                    updated_at = CURRENT_TIMESTAMP
                WHERE project_id = :project_id
                  AND legacy_html_pid = :legacy_html_pid'
            );
            $statement->execute([
                ':name' => (string) ($input['name'] ?? ''),
                ':legacy_project_source_output_pid' => (int) ($input['legacy_project_source_output_pid'] ?? 0),
                ':legacy_html_template_pid' => (int) ($input['legacy_html_template_pid'] ?? 0),
                ':notes' => '',
                ':source_of_truth' => 'manual',
                ':project_id' => $projectId,
                ':legacy_html_pid' => (int) ($input['legacy_html_pid'] ?? 0),
            ]);

            $catalogResult = app_fetch_project_html_catalog($app, $projectKey, $projectId);
            if (!$catalogResult['ok']) {
                return [
                    'ok' => false,
                    'item' => null,
                    'error' => $catalogResult['error'],
                ];
            }

            $item = app_project_html_find_catalog_item_by_legacy_pid(
                $catalogResult['items'],
                (int) ($input['legacy_html_pid'] ?? 0),
            );

            return [
                'ok' => $item !== null,
                'item' => $item,
                'error' => $item !== null ? '' : '更新対象の html row が見つかりません。',
            ];
        }

        if (!app_project_html_pdo_table_exists($pdo, 'html')) {
            return [
                'ok' => false,
                'item' => null,
                'error' => 'HTML canonical table も legacy html table も存在しません。',
            ];
        }

        $statement = $pdo->prepare(
            'UPDATE html
            SET
                name = :name,
                ProjectSourceOutputPID = :project_source_output_pid,
                htmlTemplatePID = :html_template_pid,
                LastModifiedDT = CURRENT_TIMESTAMP
            WHERE ProjectPID = :project_pid
              AND PID = :html_pid'
        );
        $statement->execute([
            ':name' => $input['name'],
            ':project_source_output_pid' => $input['legacy_project_source_output_pid'],
            ':html_template_pid' => $input['legacy_html_template_pid'],
            ':project_pid' => $input['project_pid'],
            ':html_pid' => $input['legacy_html_pid'],
        ]);

        $catalogResult = app_fetch_project_html_catalog(
            $app,
            $projectKey,
            (int) $input['project_pid'],
        );
        if (!$catalogResult['ok']) {
            return [
                'ok' => false,
                'item' => null,
                'error' => $catalogResult['error'],
            ];
        }

        $item = app_project_html_find_catalog_item_by_legacy_pid(
            $catalogResult['items'],
            (int) $input['legacy_html_pid'],
        );

        return [
            'ok' => $item !== null,
            'item' => $item,
            'error' => $item !== null ? '' : '更新対象の html row が見つかりません。',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_delete_project_html(
    array $app,
    string $projectKey,
    int $projectPid,
    int $legacyHtmlPid,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_project_html_canonical_tables_available($pdo)) {
            $projectId = app_project_html_pdo_resolve_project_id($pdo, $projectKey, $projectPid);
            app_project_html_bootstrap_reference_into_canonical($pdo, $projectKey, $projectId);

            $statement = $pdo->prepare(
                'DELETE FROM project_html_definitions
                WHERE project_id = :project_id
                  AND legacy_html_pid = :legacy_html_pid'
            );
            $statement->execute([
                ':project_id' => $projectId,
                ':legacy_html_pid' => $legacyHtmlPid,
            ]);

            if ($statement->rowCount() === 0 && !app_project_html_pdo_table_exists($pdo, 'html')) {
                return [
                    'ok' => false,
                    'error' => '削除対象の html row が見つかりません。',
                ];
            }

            return [
                'ok' => true,
                'error' => '',
            ];
        }

        if (!app_project_html_pdo_table_exists($pdo, 'html')) {
            return [
                'ok' => false,
                'error' => 'HTML canonical table も legacy html table も存在しません。',
            ];
        }

        $statement = $pdo->prepare(
            'DELETE FROM html
            WHERE ProjectPID = :project_pid
              AND PID = :html_pid'
        );
        $statement->execute([
            ':project_pid' => $projectPid,
            ':html_pid' => $legacyHtmlPid,
        ]);

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @param array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     parameter_name:string,
 *     parameter_value:string
 * } $input
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }|null,
 *     error:string
 * }
 */
function app_create_project_html_parameter(array $app, string $projectKey, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_project_html_canonical_tables_available($pdo)) {
            $projectId = app_project_html_pdo_resolve_project_id(
                $pdo,
                $projectKey,
                (int) $input['project_pid'],
            );
            app_project_html_bootstrap_reference_into_canonical($pdo, $projectKey, $projectId);

            $definition = app_project_html_canonical_definition_row_by_legacy_pid(
                $pdo,
                $projectId,
                (int) ($input['legacy_html_pid'] ?? 0),
            );
            if ($definition === null) {
                return [
                    'ok' => false,
                    'item' => null,
                    'error' => '紐づく html row が見つかりません。',
                ];
            }

            $ownsTransaction = !$pdo->inTransaction();
            if ($ownsTransaction) {
                $pdo->beginTransaction();
            }

            try {
                $legacyParameterPid = app_project_html_canonical_next_parameter_pid($pdo, $projectId);
                $statement = $pdo->prepare(
                    'INSERT INTO project_html_parameters (
                        project_id,
                        project_html_definition_id,
                        legacy_parameter_pid,
                        parameter_name,
                        parameter_value,
                        parameter_list_order,
                        notes,
                        source_of_truth
                    ) VALUES (
                        :project_id,
                        :project_html_definition_id,
                        :legacy_parameter_pid,
                        :parameter_name,
                        :parameter_value,
                        :parameter_list_order,
                        :notes,
                        :source_of_truth
                    )'
                );
                $statement->execute([
                    ':project_id' => $projectId,
                    ':project_html_definition_id' => $definition['id'],
                    ':legacy_parameter_pid' => $legacyParameterPid,
                    ':parameter_name' => (string) ($input['parameter_name'] ?? ''),
                    ':parameter_value' => (string) ($input['parameter_value'] ?? ''),
                    ':parameter_list_order' => app_project_html_canonical_next_parameter_list_order(
                        $pdo,
                        $definition['id'],
                    ),
                    ':notes' => '',
                    ':source_of_truth' => 'manual',
                ]);

                app_project_html_touch_last_modified_canonical(
                    $pdo,
                    $projectId,
                    $definition['legacy_html_pid'],
                );

                if ($ownsTransaction) {
                    $pdo->commit();
                }

                return app_fetch_project_html_parameter_by_pid(
                    $app,
                    $projectKey,
                    $projectId,
                    $legacyParameterPid,
                );
            } catch (Throwable $throwable) {
                if ($ownsTransaction && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                throw $throwable;
            }
        }

        if (!app_project_html_pdo_table_exists($pdo, 'htmlParameter')) {
            return [
                'ok' => false,
                'item' => null,
                'error' => 'HTML canonical table も legacy htmlParameter table も存在しません。',
            ];
        }

        $statement = $pdo->prepare(
            'INSERT INTO htmlParameter (
                ProjectPID,
                htmlPID,
                ParameterName,
                ParameterValue
            ) VALUES (
                :project_pid,
                :html_pid,
                :parameter_name,
                :parameter_value
            )'
        );
        $statement->execute([
            ':project_pid' => $input['project_pid'],
            ':html_pid' => $input['legacy_html_pid'],
            ':parameter_name' => $input['parameter_name'],
            ':parameter_value' => $input['parameter_value'],
        ]);

        app_project_html_touch_last_modified_legacy(
            $pdo,
            (int) $input['project_pid'],
            (int) $input['legacy_html_pid'],
        );

        return app_fetch_project_html_parameter_by_pid(
            $app,
            $projectKey,
            (int) $input['project_pid'],
            (int) $pdo->lastInsertId(),
        );
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @param array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     legacy_parameter_pid:int,
 *     parameter_name:string,
 *     parameter_value:string
 * } $input
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }|null,
 *     error:string
 * }
 */
function app_update_project_html_parameter(array $app, string $projectKey, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_project_html_canonical_tables_available($pdo)) {
            $projectId = app_project_html_pdo_resolve_project_id(
                $pdo,
                $projectKey,
                (int) $input['project_pid'],
            );
            app_project_html_bootstrap_reference_into_canonical($pdo, $projectKey, $projectId);

            $parameter = app_project_html_canonical_parameter_row_by_legacy_pid(
                $pdo,
                $projectId,
                (int) ($input['legacy_parameter_pid'] ?? 0),
            );
            if ($parameter === null) {
                return [
                    'ok' => false,
                    'item' => null,
                    'error' => '更新対象の html parameter row が見つかりません。',
                ];
            }

            if ($parameter['legacy_html_pid'] !== (int) ($input['legacy_html_pid'] ?? 0)) {
                return [
                    'ok' => false,
                    'item' => null,
                    'error' => '更新対象の html parameter row と html route が一致しません。',
                ];
            }

            $ownsTransaction = !$pdo->inTransaction();
            if ($ownsTransaction) {
                $pdo->beginTransaction();
            }

            try {
                $statement = $pdo->prepare(
                    'UPDATE project_html_parameters
                    SET
                        parameter_name = :parameter_name,
                        parameter_value = :parameter_value,
                        notes = :notes,
                        source_of_truth = :source_of_truth,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id'
                );
                $statement->execute([
                    ':parameter_name' => (string) ($input['parameter_name'] ?? ''),
                    ':parameter_value' => (string) ($input['parameter_value'] ?? ''),
                    ':notes' => '',
                    ':source_of_truth' => 'manual',
                    ':id' => $parameter['id'],
                ]);

                app_project_html_touch_last_modified_canonical(
                    $pdo,
                    $projectId,
                    $parameter['legacy_html_pid'],
                );

                if ($ownsTransaction) {
                    $pdo->commit();
                }

                return app_fetch_project_html_parameter_by_pid(
                    $app,
                    $projectKey,
                    $projectId,
                    (int) ($input['legacy_parameter_pid'] ?? 0),
                );
            } catch (Throwable $throwable) {
                if ($ownsTransaction && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                throw $throwable;
            }
        }

        if (!app_project_html_pdo_table_exists($pdo, 'htmlParameter')) {
            return [
                'ok' => false,
                'item' => null,
                'error' => 'HTML canonical table も legacy htmlParameter table も存在しません。',
            ];
        }

        $statement = $pdo->prepare(
            'UPDATE htmlParameter
            SET
                ParameterName = :parameter_name,
                ParameterValue = :parameter_value
            WHERE ProjectPID = :project_pid
              AND PID = :parameter_pid'
        );
        $statement->execute([
            ':parameter_name' => $input['parameter_name'],
            ':parameter_value' => $input['parameter_value'],
            ':project_pid' => $input['project_pid'],
            ':parameter_pid' => $input['legacy_parameter_pid'],
        ]);

        app_project_html_touch_last_modified_legacy(
            $pdo,
            (int) $input['project_pid'],
            (int) $input['legacy_html_pid'],
        );

        return app_fetch_project_html_parameter_by_pid(
            $app,
            $projectKey,
            (int) $input['project_pid'],
            (int) $input['legacy_parameter_pid'],
        );
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_delete_project_html_parameter(
    array $app,
    string $projectKey,
    int $projectPid,
    int $legacyHtmlPid,
    int $legacyParameterPid,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_project_html_canonical_tables_available($pdo)) {
            $projectId = app_project_html_pdo_resolve_project_id($pdo, $projectKey, $projectPid);
            app_project_html_bootstrap_reference_into_canonical($pdo, $projectKey, $projectId);

            $parameter = app_project_html_canonical_parameter_row_by_legacy_pid(
                $pdo,
                $projectId,
                $legacyParameterPid,
            );
            if ($parameter === null) {
                return [
                    'ok' => false,
                    'error' => '削除対象の html parameter row が見つかりません。',
                ];
            }

            if ($parameter['legacy_html_pid'] !== $legacyHtmlPid) {
                return [
                    'ok' => false,
                    'error' => '削除対象の html parameter row と html route が一致しません。',
                ];
            }

            $ownsTransaction = !$pdo->inTransaction();
            if ($ownsTransaction) {
                $pdo->beginTransaction();
            }

            try {
                $statement = $pdo->prepare(
                    'DELETE FROM project_html_parameters
                    WHERE id = :id'
                );
                $statement->execute([
                    ':id' => $parameter['id'],
                ]);

                app_project_html_touch_last_modified_canonical(
                    $pdo,
                    $projectId,
                    $parameter['legacy_html_pid'],
                );

                if ($ownsTransaction) {
                    $pdo->commit();
                }

                return [
                    'ok' => true,
                    'error' => '',
                ];
            } catch (Throwable $throwable) {
                if ($ownsTransaction && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                throw $throwable;
            }
        }

        if (!app_project_html_pdo_table_exists($pdo, 'htmlParameter')) {
            return [
                'ok' => false,
                'error' => 'HTML canonical table も legacy htmlParameter table も存在しません。',
            ];
        }

        $statement = $pdo->prepare(
            'DELETE FROM htmlParameter
            WHERE ProjectPID = :project_pid
              AND PID = :parameter_pid'
        );
        $statement->execute([
            ':project_pid' => $projectPid,
            ':parameter_pid' => $legacyParameterPid,
        ]);

        app_project_html_touch_last_modified_legacy($pdo, $projectPid, $legacyHtmlPid);

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}
