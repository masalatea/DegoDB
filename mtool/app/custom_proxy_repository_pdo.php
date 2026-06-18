<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/domain_validation.php';

function app_custom_proxy_pdo_datetime_select_expr(array $app, string $columnExpression, string $alias = 'updated_at'): string
{
    return app_sql_datetime_select_expr(
        app_sql_dialect_from_db_config(app_database_config($app, 'config_db')),
        $columnExpression,
        $alias,
    );
}

function app_pdo_fetch_project_custom_proxy_catalog(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $statement = $pdo->prepare(
            'SELECT
                cp.custom_proxy_key,
                cp.basename,
                cp.name,
                cp.in_transaction,
                cp.auth_type,
                cp.single_get_function_name,
                cp.continue_even_if_failed_to_insert,
                cp.notes,
                cp.source_of_truth,
                ' . app_custom_proxy_pdo_datetime_select_expr($app, 'cp.updated_at') . ',
                COUNT(DISTINCT cps.id) AS step_count,
                COUNT(DISTINCT cpt.id) AS target_count
            FROM project_custom_proxies AS cp
            INNER JOIN projects AS p
                ON p.id = cp.project_id
            LEFT JOIN project_custom_proxy_steps AS cps
                ON cps.custom_proxy_id = cp.id
            LEFT JOIN project_custom_proxy_source_output_targets AS cpt
                ON cpt.custom_proxy_id = cp.id
            WHERE p.project_key = :project_key
            GROUP BY
                cp.id,
                cp.custom_proxy_key,
                cp.basename,
                cp.name,
                cp.in_transaction,
                cp.auth_type,
                cp.single_get_function_name,
                cp.continue_even_if_failed_to_insert,
                cp.notes,
                cp.source_of_truth,
                cp.updated_at
            ORDER BY cp.basename, cp.name, cp.custom_proxy_key'
        );
        $statement->execute([
            ':project_key' => $projectKey,
        ]);

        $rows = $statement->fetchAll();
        $items = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = app_pdo_project_custom_proxy_item_from_row($row);
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

function app_pdo_fetch_project_custom_proxy_item(array $app, string $projectKey, string $customProxyKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $statement = $pdo->prepare(
            'SELECT
                cp.custom_proxy_key,
                cp.basename,
                cp.name,
                cp.in_transaction,
                cp.auth_type,
                cp.single_get_function_name,
                cp.continue_even_if_failed_to_insert,
                cp.notes,
                cp.source_of_truth,
                ' . app_custom_proxy_pdo_datetime_select_expr($app, 'cp.updated_at') . ',
                COUNT(DISTINCT cps.id) AS step_count,
                COUNT(DISTINCT cpt.id) AS target_count
            FROM project_custom_proxies AS cp
            INNER JOIN projects AS p
                ON p.id = cp.project_id
            LEFT JOIN project_custom_proxy_steps AS cps
                ON cps.custom_proxy_id = cp.id
            LEFT JOIN project_custom_proxy_source_output_targets AS cpt
                ON cpt.custom_proxy_id = cp.id
            WHERE p.project_key = :project_key
              AND cp.custom_proxy_key = :custom_proxy_key
            GROUP BY
                cp.id,
                cp.custom_proxy_key,
                cp.basename,
                cp.name,
                cp.in_transaction,
                cp.auth_type,
                cp.single_get_function_name,
                cp.continue_even_if_failed_to_insert,
                cp.notes,
                cp.source_of_truth,
                cp.updated_at
            LIMIT 1'
        );
        $statement->execute([
            ':project_key' => $projectKey,
            ':custom_proxy_key' => $customProxyKey,
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
            'item' => app_pdo_project_custom_proxy_item_from_row($row),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

function app_pdo_create_project_custom_proxy(array $app, array $input): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_custom_proxy_pdo_resolve_project_id($pdo, $input['project_key']);

        $statement = $pdo->prepare(
            'INSERT INTO project_custom_proxies (
                project_id,
                custom_proxy_key,
                basename,
                name,
                in_transaction,
                auth_type,
                single_get_function_name,
                continue_even_if_failed_to_insert,
                notes,
                source_of_truth
            ) VALUES (
                :project_id,
                :custom_proxy_key,
                :basename,
                :name,
                :in_transaction,
                :auth_type,
                :single_get_function_name,
                :continue_even_if_failed_to_insert,
                :notes,
                :source_of_truth
            )'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':custom_proxy_key' => $input['custom_proxy_key'],
            ':basename' => $input['basename'],
            ':name' => $input['name'],
            ':in_transaction' => (int) $input['in_transaction'],
            ':auth_type' => $input['auth_type'],
            ':single_get_function_name' => $input['single_get_function_name'],
            ':continue_even_if_failed_to_insert' => (int) $input['continue_even_if_failed_to_insert'],
            ':notes' => $input['notes'],
            ':source_of_truth' => $input['source_of_truth'],
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

function app_pdo_update_project_custom_proxy(array $app, array $input): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_custom_proxy_pdo_resolve_project_id($pdo, $input['project_key']);
        app_custom_proxy_pdo_resolve_custom_proxy_id($pdo, $projectId, $input['custom_proxy_key']);

        $statement = $pdo->prepare(
            'UPDATE project_custom_proxies
            SET
                basename = :basename,
                name = :name,
                in_transaction = :in_transaction,
                auth_type = :auth_type,
                single_get_function_name = :single_get_function_name,
                continue_even_if_failed_to_insert = :continue_even_if_failed_to_insert,
                notes = :notes,
                source_of_truth = :source_of_truth,
                updated_at = CURRENT_TIMESTAMP
            WHERE project_id = :project_id
              AND custom_proxy_key = :custom_proxy_key'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':custom_proxy_key' => $input['custom_proxy_key'],
            ':basename' => $input['basename'],
            ':name' => $input['name'],
            ':in_transaction' => (int) $input['in_transaction'],
            ':auth_type' => $input['auth_type'],
            ':single_get_function_name' => $input['single_get_function_name'],
            ':continue_even_if_failed_to_insert' => (int) $input['continue_even_if_failed_to_insert'],
            ':notes' => $input['notes'],
            ':source_of_truth' => $input['source_of_truth'],
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

function app_pdo_delete_project_custom_proxy(array $app, string $projectKey, string $customProxyKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_custom_proxy_pdo_resolve_project_id($pdo, $projectKey);
        app_custom_proxy_pdo_resolve_custom_proxy_id($pdo, $projectId, $customProxyKey);

        $statement = $pdo->prepare(
            'DELETE FROM project_custom_proxies
            WHERE project_id = :project_id
              AND custom_proxy_key = :custom_proxy_key'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':custom_proxy_key' => $customProxyKey,
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

function app_pdo_fetch_project_custom_proxy_target_keys(array $app, string $projectKey, string $customProxyKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $statement = $pdo->prepare(
            'SELECT
                cpt.source_output_key
            FROM project_custom_proxy_source_output_targets AS cpt
            INNER JOIN project_custom_proxies AS cp
                ON cp.id = cpt.custom_proxy_id
            INNER JOIN projects AS p
                ON p.id = cp.project_id
            WHERE p.project_key = :project_key
              AND cp.custom_proxy_key = :custom_proxy_key
            ORDER BY cpt.source_output_key'
        );
        $statement->execute([
            ':project_key' => $projectKey,
            ':custom_proxy_key' => $customProxyKey,
        ]);

        $rows = $statement->fetchAll();
        $items = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = (string) ($row['source_output_key'] ?? '');
        }

        return [
            'ok' => true,
            'items' => array_values(array_filter($items, static fn (string $value): bool => $value !== '')),
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

function app_pdo_replace_project_custom_proxy_target_keys(
    array $app,
    string $projectKey,
    string $customProxyKey,
    array $sourceOutputKeys,
): array {
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_custom_proxy_pdo_resolve_project_id($pdo, $projectKey);
        $customProxyId = app_custom_proxy_pdo_resolve_custom_proxy_id($pdo, $projectId, $customProxyKey);

        $normalizedKeys = [];
        foreach ($sourceOutputKeys as $sourceOutputKey) {
            if (!is_string($sourceOutputKey)) {
                continue;
            }

            $normalizedKey = app_normalize_source_output_key($sourceOutputKey);
            if ($normalizedKey === '' || !app_source_output_key_is_valid($normalizedKey)) {
                continue;
            }

            $normalizedKeys[$normalizedKey] = $normalizedKey;
        }

        $pdo->beginTransaction();

        $deleteStatement = $pdo->prepare(
            'DELETE FROM project_custom_proxy_source_output_targets
            WHERE custom_proxy_id = :custom_proxy_id'
        );
        $deleteStatement->execute([
            ':custom_proxy_id' => $customProxyId,
        ]);

        if ($normalizedKeys !== []) {
            $insertStatement = $pdo->prepare(
                'INSERT INTO project_custom_proxy_source_output_targets (
                    custom_proxy_id,
                    source_output_key
                ) VALUES (
                    :custom_proxy_id,
                    :source_output_key
                )'
            );

            foreach ($normalizedKeys as $normalizedKey) {
                $insertStatement->execute([
                    ':custom_proxy_id' => $customProxyId,
                    ':source_output_key' => $normalizedKey,
                ]);
            }
        }

        $pdo->commit();

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

function app_pdo_fetch_project_custom_proxy_step_catalog(array $app, string $projectKey, string $customProxyKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $statement = $pdo->prepare(
            'SELECT
                cps.id,
                cps.db_access_source_name,
                cps.db_access_function_name,
                cps.is_list,
                cps.step_order,
                cps.notes,
                cps.source_of_truth,
                ' . app_custom_proxy_pdo_datetime_select_expr($app, 'cps.updated_at') . '
            FROM project_custom_proxy_steps AS cps
            INNER JOIN project_custom_proxies AS cp
                ON cp.id = cps.custom_proxy_id
            INNER JOIN projects AS p
                ON p.id = cp.project_id
            WHERE p.project_key = :project_key
              AND cp.custom_proxy_key = :custom_proxy_key
            ORDER BY cps.step_order, cps.id'
        );
        $statement->execute([
            ':project_key' => $projectKey,
            ':custom_proxy_key' => $customProxyKey,
        ]);

        $rows = $statement->fetchAll();
        $items = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = app_pdo_project_custom_proxy_step_item_from_row($row);
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

function app_pdo_create_project_custom_proxy_step(array $app, array $input): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_custom_proxy_pdo_resolve_project_id($pdo, $input['project_key']);
        $customProxyId = app_custom_proxy_pdo_resolve_custom_proxy_id($pdo, $projectId, $input['custom_proxy_key']);

        $statement = $pdo->prepare(
            'INSERT INTO project_custom_proxy_steps (
                custom_proxy_id,
                db_access_source_name,
                db_access_function_name,
                is_list,
                step_order,
                notes,
                source_of_truth
            ) VALUES (
                :custom_proxy_id,
                :db_access_source_name,
                :db_access_function_name,
                :is_list,
                :step_order,
                :notes,
                :source_of_truth
            )'
        );
        $statement->execute([
            ':custom_proxy_id' => $customProxyId,
            ':db_access_source_name' => $input['db_access_source_name'],
            ':db_access_function_name' => $input['db_access_function_name'],
            ':is_list' => (int) $input['is_list'],
            ':step_order' => (int) $input['step_order'],
            ':notes' => $input['notes'],
            ':source_of_truth' => $input['source_of_truth'],
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

function app_pdo_update_project_custom_proxy_step(array $app, array $input): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_custom_proxy_pdo_resolve_project_id($pdo, $input['project_key']);
        $customProxyId = app_custom_proxy_pdo_resolve_custom_proxy_id($pdo, $projectId, $input['custom_proxy_key']);
        $stepId = app_custom_proxy_pdo_resolve_step_id($pdo, $customProxyId, $input['step_id']);

        $statement = $pdo->prepare(
            'UPDATE project_custom_proxy_steps
            SET
                db_access_source_name = :db_access_source_name,
                db_access_function_name = :db_access_function_name,
                is_list = :is_list,
                step_order = :step_order,
                notes = :notes,
                source_of_truth = :source_of_truth,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :step_id
              AND custom_proxy_id = :custom_proxy_id'
        );
        $statement->execute([
            ':step_id' => $stepId,
            ':custom_proxy_id' => $customProxyId,
            ':db_access_source_name' => $input['db_access_source_name'],
            ':db_access_function_name' => $input['db_access_function_name'],
            ':is_list' => (int) $input['is_list'],
            ':step_order' => (int) $input['step_order'],
            ':notes' => $input['notes'],
            ':source_of_truth' => $input['source_of_truth'],
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

function app_pdo_delete_project_custom_proxy_step(
    array $app,
    string $projectKey,
    string $customProxyKey,
    string $stepId,
): array {
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_custom_proxy_pdo_resolve_project_id($pdo, $projectKey);
        $customProxyId = app_custom_proxy_pdo_resolve_custom_proxy_id($pdo, $projectId, $customProxyKey);
        $resolvedStepId = app_custom_proxy_pdo_resolve_step_id($pdo, $customProxyId, $stepId);

        $statement = $pdo->prepare(
            'DELETE FROM project_custom_proxy_steps
            WHERE id = :step_id
              AND custom_proxy_id = :custom_proxy_id'
        );
        $statement->execute([
            ':step_id' => $resolvedStepId,
            ':custom_proxy_id' => $customProxyId,
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

function app_pdo_reorder_project_custom_proxy_steps(
    array $app,
    string $projectKey,
    string $customProxyKey,
    array $stepIds,
): array {
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_custom_proxy_pdo_resolve_project_id($pdo, $projectKey);
        $customProxyId = app_custom_proxy_pdo_resolve_custom_proxy_id($pdo, $projectId, $customProxyKey);

        $normalizedStepIds = [];
        foreach ($stepIds as $stepId) {
            if (!is_string($stepId) && !is_int($stepId)) {
                continue;
            }

            $normalizedStepId = trim((string) $stepId);
            if ($normalizedStepId === '' || !ctype_digit($normalizedStepId)) {
                throw new RuntimeException('step id の形式が不正です。');
            }

            if (isset($normalizedStepIds[$normalizedStepId])) {
                throw new RuntimeException('step id が重複しています。');
            }

            $normalizedStepIds[$normalizedStepId] = $normalizedStepId;
        }

        $existingStepIds = app_custom_proxy_pdo_fetch_step_ids($pdo, $customProxyId);
        if (count($normalizedStepIds) !== count($existingStepIds)) {
            throw new RuntimeException('並び替え対象の step 集合が current catalog と一致しません。');
        }

        $existingStepIdLookup = array_fill_keys($existingStepIds, true);
        foreach ($normalizedStepIds as $normalizedStepId) {
            if (!isset($existingStepIdLookup[$normalizedStepId])) {
                throw new RuntimeException('custom proxy step が見つかりません。');
            }
        }

        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'UPDATE project_custom_proxy_steps
            SET
                step_order = :step_order,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :step_id
              AND custom_proxy_id = :custom_proxy_id'
        );

        $stepOrder = 1;
        foreach ($normalizedStepIds as $normalizedStepId) {
            $statement->execute([
                ':step_order' => $stepOrder,
                ':step_id' => (int) $normalizedStepId,
                ':custom_proxy_id' => $customProxyId,
            ]);
            $stepOrder++;
        }

        $pdo->commit();

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

function app_pdo_reset_project_custom_proxy_step_order(
    array $app,
    string $projectKey,
    string $customProxyKey,
): array {
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_custom_proxy_pdo_resolve_project_id($pdo, $projectKey);
        $customProxyId = app_custom_proxy_pdo_resolve_custom_proxy_id($pdo, $projectId, $customProxyKey);

        $stepOrderResetExpression = app_sql_dialect_from_db_config(app_database_config($app, 'config_db')) === 'sqlite'
            ? '100'
            : 'DEFAULT(step_order)';

        $statement = $pdo->prepare(
            'UPDATE project_custom_proxy_steps
            SET
                step_order = ' . $stepOrderResetExpression . ',
                updated_at = CURRENT_TIMESTAMP
            WHERE custom_proxy_id = :custom_proxy_id'
        );
        $statement->execute([
            ':custom_proxy_id' => $customProxyId,
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

function app_custom_proxy_pdo_resolve_project_id(PDO $pdo, string $projectKey): int
{
    $statement = $pdo->prepare(
        'SELECT id
        FROM projects
        WHERE project_key = :project_key
        LIMIT 1'
    );
    $statement->execute([
        ':project_key' => $projectKey,
    ]);

    $value = $statement->fetchColumn();
    if ($value === false) {
        throw new RuntimeException('project が見つかりません。');
    }

    return (int) $value;
}

function app_custom_proxy_pdo_resolve_custom_proxy_id(PDO $pdo, int $projectId, string $customProxyKey): int
{
    $statement = $pdo->prepare(
        'SELECT id
        FROM project_custom_proxies
        WHERE project_id = :project_id
          AND custom_proxy_key = :custom_proxy_key
        LIMIT 1'
    );
    $statement->execute([
        ':project_id' => $projectId,
        ':custom_proxy_key' => $customProxyKey,
    ]);

    $value = $statement->fetchColumn();
    if ($value === false) {
        throw new RuntimeException('custom proxy が見つかりません。');
    }

    return (int) $value;
}

/**
 * @return list<string>
 */
function app_custom_proxy_pdo_fetch_step_ids(PDO $pdo, int $customProxyId): array
{
    $statement = $pdo->prepare(
        'SELECT id
        FROM project_custom_proxy_steps
        WHERE custom_proxy_id = :custom_proxy_id
        ORDER BY step_order, id'
    );
    $statement->execute([
        ':custom_proxy_id' => $customProxyId,
    ]);

    $rows = $statement->fetchAll(PDO::FETCH_COLUMN);
    $items = [];
    foreach ($rows as $row) {
        if ($row === false || $row === null) {
            continue;
        }

        $items[] = trim((string) $row);
    }

    return array_values(array_filter($items, static fn (string $item): bool => $item !== ''));
}

function app_custom_proxy_pdo_resolve_step_id(PDO $pdo, int $customProxyId, string $stepId): int
{
    if ($stepId === '' || !ctype_digit($stepId)) {
        throw new RuntimeException('step id の形式が不正です。');
    }

    $statement = $pdo->prepare(
        'SELECT id
        FROM project_custom_proxy_steps
        WHERE id = :step_id
          AND custom_proxy_id = :custom_proxy_id
        LIMIT 1'
    );
    $statement->execute([
        ':step_id' => (int) $stepId,
        ':custom_proxy_id' => $customProxyId,
    ]);

    $value = $statement->fetchColumn();
    if ($value === false) {
        throw new RuntimeException('custom proxy step が見つかりません。');
    }

    return (int) $value;
}

function app_pdo_project_custom_proxy_item_from_row(array $row): array
{
    return [
        'custom_proxy_key' => (string) ($row['custom_proxy_key'] ?? ''),
        'basename' => (string) ($row['basename'] ?? ''),
        'name' => (string) ($row['name'] ?? ''),
        'in_transaction' => (string) ($row['in_transaction'] ?? '0') === '1' ? '1' : '0',
        'auth_type' => (string) ($row['auth_type'] ?? ''),
        'single_get_function_name' => (string) ($row['single_get_function_name'] ?? ''),
        'continue_even_if_failed_to_insert' => (string) ($row['continue_even_if_failed_to_insert'] ?? '0') === '1' ? '1' : '0',
        'notes' => (string) ($row['notes'] ?? ''),
        'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
        'updated_at' => (string) ($row['updated_at'] ?? ''),
        'step_count' => (int) ($row['step_count'] ?? 0),
        'target_count' => (int) ($row['target_count'] ?? 0),
    ];
}

function app_pdo_project_custom_proxy_step_item_from_row(array $row): array
{
    return [
        'id' => (string) ($row['id'] ?? ''),
        'db_access_source_name' => (string) ($row['db_access_source_name'] ?? ''),
        'db_access_function_name' => (string) ($row['db_access_function_name'] ?? ''),
        'is_list' => (string) ($row['is_list'] ?? '0') === '1' ? '1' : '0',
        'step_order' => (string) ($row['step_order'] ?? '100'),
        'notes' => (string) ($row['notes'] ?? ''),
        'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
        'updated_at' => (string) ($row['updated_at'] ?? ''),
    ];
}
