<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/generated_catalog.php';

function app_pdo_resolve_generated_dbaccess_path(
    array $app,
    string $sourceName,
    string $lastDetectedDbaccessFile = '',
): string {
    $catalog = app_generated_entity_catalog($app);
    $entity = app_generated_catalog_find_entity($catalog, $sourceName);
    if ($entity !== null && app_generated_file_exists_and_readable((string) ($entity['dbaccess_path'] ?? ''))) {
        return (string) $entity['dbaccess_path'];
    }

    $dbclassesRoot = rtrim((string) ($app['generated']['dbclasses_root'] ?? ''), '/');
    if ($dbclassesRoot === '' || $lastDetectedDbaccessFile === '') {
        return '';
    }

    $candidatePath = $dbclassesRoot . '/' . basename($lastDetectedDbaccessFile);
    if (app_generated_file_exists_and_readable($candidatePath)) {
        return $candidatePath;
    }

    return '';
}

function app_pdo_db_access_function_supports_blob_streaming_contract(
    array $app,
    string $sourceName,
    string $functionName,
    string $lastDetectedDbaccessFile = '',
): bool {
    $dbaccessPath = app_pdo_resolve_generated_dbaccess_path($app, $sourceName, $lastDetectedDbaccessFile);
    if ($dbaccessPath === '') {
        return false;
    }

    return app_generated_file_method_has_blob_streaming_contract($dbaccessPath, $functionName);
}

function app_pdo_db_access_datetime_select_expr(
    array $app,
    string $columnExpression = 'updated_at',
    string $alias = 'updated_at',
): string {
    return app_sql_datetime_select_expr(
        app_sql_dialect_from_db_config(app_database_config($app, 'config_db')),
        $columnExpression,
        $alias,
    );
}

/**
 * @param array{
 *     source_name:string,
 *     function_name:string,
 *     action_type:string,
 *     is_blob_target:string,
 *     last_detected_dbaccess_file?:string
 * } $input
 */
function app_pdo_validate_db_access_function_blob_target_constraint(array $app, array $input): string
{
    if (($input['is_blob_target'] ?? '0') !== '1') {
        return '';
    }

    if (!in_array((string) ($input['action_type'] ?? ''), ['INSERT', 'UPDATE'], true)) {
        return 'IsBlobTarget=1 は INSERT/UPDATE のみ設定できます。';
    }

    if (!app_pdo_db_access_function_supports_blob_streaming_contract(
        $app,
        (string) ($input['source_name'] ?? ''),
        (string) ($input['function_name'] ?? ''),
        (string) ($input['last_detected_dbaccess_file'] ?? ''),
    )) {
        return 'IsBlobTarget=1 は legacy method source に prepare()/bind_param("b")/send_long_data() がある function でのみ保存できます。';
    }

    return '';
}

function app_pdo_validate_db_access_function_file_parameter_constraint(
    array $app,
    string $sourceName,
    string $functionName,
    string $parameterDataType,
    string $isBlobTarget,
    string $lastDetectedDbaccessFile = '',
): string {
    if ($parameterDataType !== 'file') {
        return '';
    }

    if ($isBlobTarget !== '1') {
        return 'この function では file data type は利用できません。';
    }

    if (!app_pdo_db_access_function_supports_blob_streaming_contract(
        $app,
        $sourceName,
        $functionName,
        $lastDetectedDbaccessFile,
    )) {
        return 'file data type を使うには legacy method source に prepare()/bind_param("b")/send_long_data() が必要です。';
    }

    return '';
}

/**
 * @return array{
 *     action_type:string,
 *     is_blob_target:string,
 *     last_detected_dbaccess_file:string
 * }
 */
function app_pdo_fetch_db_access_function_blob_target_context(
    PDO $pdo,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    $statement = $pdo->prepare(
        'SELECT
            f.action_type,
            f.is_blob_target,
            c.last_detected_dbaccess_file
        FROM project_db_access_functions AS f
        INNER JOIN project_db_access_classes AS c
            ON c.id = f.db_access_class_id
        INNER JOIN projects AS p
            ON p.id = c.project_id
        WHERE p.project_key = :project_key
          AND c.source_name = :source_name
          AND f.function_name = :function_name
        LIMIT 1'
    );
    $statement->execute([
        ':project_key' => $projectKey,
        ':source_name' => $sourceName,
        ':function_name' => $functionName,
    ]);

    $row = $statement->fetch();
    if (!is_array($row)) {
        return [
            'action_type' => '',
            'is_blob_target' => '0',
            'last_detected_dbaccess_file' => '',
        ];
    }

    return [
        'action_type' => (string) ($row['action_type'] ?? ''),
        'is_blob_target' => ((int) ($row['is_blob_target'] ?? 0)) === 1 ? '1' : '0',
        'last_detected_dbaccess_file' => (string) ($row['last_detected_dbaccess_file'] ?? ''),
    ];
}

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     item:array{
 *         source_name:string,
 *         store_base_path:string,
 *         is_autoload:string,
 *         notes:string,
 *         source_of_truth:string,
 *         last_detected_dbaccess_file:string,
 *         last_detected_data_file:string,
 *         updated_at:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_db_access_class_metadata(array $app, string $projectKey, string $sourceName): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $dialect = app_sql_dialect_from_db_config(app_database_config($app, 'config_db'));
        $updatedAtSelect = app_sql_datetime_select_expr($dialect, 'c.updated_at', 'updated_at');
        $statement = $pdo->prepare(
            'SELECT
                c.source_name,
                c.store_base_path,
                c.is_autoload,
                c.notes,
                c.source_of_truth,
                c.last_detected_dbaccess_file,
                c.last_detected_data_file,
                ' . $updatedAtSelect . '
            FROM project_db_access_classes AS c
            INNER JOIN projects AS p
                ON p.id = c.project_id
            WHERE p.project_key = :project_key
              AND c.source_name = :source_name
            LIMIT 1'
        );
        $statement->execute([
            ':project_key' => $projectKey,
            ':source_name' => $sourceName,
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
                'source_name' => (string) ($row['source_name'] ?? ''),
                'store_base_path' => (string) ($row['store_base_path'] ?? ''),
                'is_autoload' => ((int) ($row['is_autoload'] ?? 0)) === 1 ? '1' : '0',
                'notes' => (string) ($row['notes'] ?? ''),
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
                'last_detected_dbaccess_file' => (string) ($row['last_detected_dbaccess_file'] ?? ''),
                'last_detected_data_file' => (string) ($row['last_detected_data_file'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
            ],
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

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         source_name:string,
 *         store_base_path:string,
 *         is_autoload:string,
 *         notes:string,
 *         source_of_truth:string,
 *         function_count:int,
 *         updated_at:string
 *     }>,
 *     error:string
 * }
 */
function app_pdo_fetch_db_access_class_metadata_catalog(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $dialect = app_sql_dialect_from_db_config(app_database_config($app, 'config_db'));
        $updatedAtSelect = app_sql_datetime_select_expr($dialect, 'c.updated_at', 'updated_at');
        $statement = $pdo->prepare(
            'SELECT
                c.source_name,
                c.store_base_path,
                c.is_autoload,
                c.notes,
                c.source_of_truth,
                COUNT(f.id) AS function_count,
                ' . $updatedAtSelect . '
            FROM project_db_access_classes AS c
            INNER JOIN projects AS p
                ON p.id = c.project_id
            LEFT JOIN project_db_access_functions AS f
                ON f.db_access_class_id = c.id
            WHERE p.project_key = :project_key
            GROUP BY
                c.id,
                c.source_name,
                c.store_base_path,
                c.is_autoload,
                c.notes,
                c.source_of_truth,
                c.updated_at
            ORDER BY c.source_name'
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

            $items[] = [
                'source_name' => (string) ($row['source_name'] ?? ''),
                'store_base_path' => (string) ($row['store_base_path'] ?? ''),
                'is_autoload' => ((int) ($row['is_autoload'] ?? 0)) === 1 ? '1' : '0',
                'notes' => (string) ($row['notes'] ?? ''),
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
                'function_count' => (int) ($row['function_count'] ?? 0),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @param array{
 *     project_key:string,
 *     source_name:string,
 *     store_base_path:string,
 *     is_autoload:string,
 *     notes:string,
 *     source_of_truth:string,
 *     last_detected_dbaccess_file:string,
 *     last_detected_data_file:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_upsert_db_access_class_metadata(array $app, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $dialect = app_sql_dialect_from_db_config(app_database_config($app, 'config_db'));
        $projectId = app_pdo_resolve_project_id($pdo, $input['project_key']);

        if ($dialect === 'sqlite') {
            $classId = app_pdo_find_db_access_class_id($pdo, $input['project_key'], $input['source_name']);
            if ($classId !== null) {
                $statement = $pdo->prepare(
                    'UPDATE project_db_access_classes
                    SET
                        store_base_path = :store_base_path,
                        is_autoload = :is_autoload,
                        notes = :notes,
                        source_of_truth = :source_of_truth,
                        last_detected_dbaccess_file = :last_detected_dbaccess_file,
                        last_detected_data_file = :last_detected_data_file,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id'
                );
                $statement->execute([
                    ':id' => $classId,
                    ':store_base_path' => $input['store_base_path'],
                    ':is_autoload' => $input['is_autoload'] === '1' ? 1 : 0,
                    ':notes' => $input['notes'],
                    ':source_of_truth' => $input['source_of_truth'],
                    ':last_detected_dbaccess_file' => $input['last_detected_dbaccess_file'],
                    ':last_detected_data_file' => $input['last_detected_data_file'],
                ]);
            } else {
                $statement = $pdo->prepare(
                    'INSERT INTO project_db_access_classes (
                        project_id,
                        source_name,
                        store_base_path,
                        is_autoload,
                        notes,
                        source_of_truth,
                        last_detected_dbaccess_file,
                        last_detected_data_file
                    ) VALUES (
                        :project_id,
                        :source_name,
                        :store_base_path,
                        :is_autoload,
                        :notes,
                        :source_of_truth,
                        :last_detected_dbaccess_file,
                        :last_detected_data_file
                    )'
                );
                $statement->execute([
                    ':project_id' => $projectId,
                    ':source_name' => $input['source_name'],
                    ':store_base_path' => $input['store_base_path'],
                    ':is_autoload' => $input['is_autoload'] === '1' ? 1 : 0,
                    ':notes' => $input['notes'],
                    ':source_of_truth' => $input['source_of_truth'],
                    ':last_detected_dbaccess_file' => $input['last_detected_dbaccess_file'],
                    ':last_detected_data_file' => $input['last_detected_data_file'],
                ]);
            }

            return [
                'ok' => true,
                'error' => '',
            ];
        }

        $sql = $dialect === 'sqlite'
            ? 'INSERT INTO project_db_access_classes (
                    project_id,
                    source_name,
                    store_base_path,
                    is_autoload,
                    notes,
                    source_of_truth,
                    last_detected_dbaccess_file,
                    last_detected_data_file
                ) VALUES (
                    :project_id,
                    :source_name,
                    :store_base_path,
                    :is_autoload,
                    :notes,
                    :source_of_truth,
                    :last_detected_dbaccess_file,
                    :last_detected_data_file
                )
                ON CONFLICT(project_id, source_name) DO UPDATE SET
                    store_base_path = excluded.store_base_path,
                    is_autoload = excluded.is_autoload,
                    notes = excluded.notes,
                    source_of_truth = excluded.source_of_truth,
                    last_detected_dbaccess_file = excluded.last_detected_dbaccess_file,
                    last_detected_data_file = excluded.last_detected_data_file,
                    updated_at = CURRENT_TIMESTAMP'
            : 'INSERT INTO project_db_access_classes (
                    project_id,
                    source_name,
                    store_base_path,
                    is_autoload,
                    notes,
                    source_of_truth,
                    last_detected_dbaccess_file,
                    last_detected_data_file
                ) VALUES (
                    :project_id,
                    :source_name,
                    :store_base_path,
                    :is_autoload,
                    :notes,
                    :source_of_truth,
                    :last_detected_dbaccess_file,
                    :last_detected_data_file
                )
                ON DUPLICATE KEY UPDATE
                    store_base_path = VALUES(store_base_path),
                    is_autoload = VALUES(is_autoload),
                    notes = VALUES(notes),
                    source_of_truth = VALUES(source_of_truth),
                    last_detected_dbaccess_file = VALUES(last_detected_dbaccess_file),
                    last_detected_data_file = VALUES(last_detected_data_file),
                    updated_at = CURRENT_TIMESTAMP';

        $statement = $pdo->prepare($sql);

        $statement->execute([
            ':project_id' => $projectId,
            ':source_name' => $input['source_name'],
            ':store_base_path' => $input['store_base_path'],
            ':is_autoload' => $input['is_autoload'] === '1' ? 1 : 0,
            ':notes' => $input['notes'],
            ':source_of_truth' => $input['source_of_truth'],
            ':last_detected_dbaccess_file' => $input['last_detected_dbaccess_file'],
            ':last_detected_data_file' => $input['last_detected_data_file'],
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     item:array{
 *         source_name:string,
 *         function_name:string,
 *         function_list_order:string,
 *         function_suffix:string,
 *         action_type:string,
 *         data_class_base_name:string,
 *         target_table_name:string,
 *         parameter_type:string,
 *         select_by_distinct:string,
 *         sort_order_columns:string,
 *         memo:string,
 *         limit_parameter_type:string,
 *         limit_fixed_parameter:string,
 *         or_group_type:string,
 *         single_proxy_auth_type:string,
 *         single_proxy_single_get_function_name:string,
 *         is_blob_target:string,
 *         detected_signature:string,
 *         detected_line:string,
 *         source_of_truth:string,
 *         updated_at:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_db_access_function_metadata(array $app, string $projectKey, string $sourceName, string $functionName): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $dialect = app_sql_dialect_from_db_config(app_database_config($app, 'config_db'));
        $updatedAtSelect = app_sql_datetime_select_expr($dialect, 'f.updated_at', 'updated_at');
        $statement = $pdo->prepare(
            'SELECT
                c.source_name,
                f.function_name,
                f.function_list_order,
                f.function_suffix,
                f.action_type,
                f.data_class_base_name,
                f.target_table_name,
                f.parameter_type,
                f.select_by_distinct,
                f.sort_order_columns,
                f.memo,
                f.limit_parameter_type,
                f.limit_fixed_parameter,
                f.or_group_type,
                f.single_proxy_auth_type,
                f.single_proxy_single_get_function_name,
                f.auth_policy_version,
                f.auth_policy_json,
                f.is_blob_target,
                f.detected_signature,
                f.detected_line,
                f.source_of_truth,
                ' . $updatedAtSelect . '
            FROM project_db_access_functions AS f
            INNER JOIN project_db_access_classes AS c
                ON c.id = f.db_access_class_id
            INNER JOIN projects AS p
                ON p.id = c.project_id
            WHERE p.project_key = :project_key
              AND c.source_name = :source_name
              AND f.function_name = :function_name
            LIMIT 1'
        );
        $statement->execute([
            ':project_key' => $projectKey,
            ':source_name' => $sourceName,
            ':function_name' => $functionName,
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
                'source_name' => (string) ($row['source_name'] ?? ''),
                'function_name' => (string) ($row['function_name'] ?? ''),
                'function_list_order' => (string) ((int) ($row['function_list_order'] ?? 0)),
                'function_suffix' => (string) ($row['function_suffix'] ?? ''),
                'action_type' => (string) ($row['action_type'] ?? ''),
                'data_class_base_name' => (string) ($row['data_class_base_name'] ?? ''),
                'target_table_name' => (string) ($row['target_table_name'] ?? ''),
                'parameter_type' => (string) ($row['parameter_type'] ?? ''),
                'select_by_distinct' => ((int) ($row['select_by_distinct'] ?? 0)) === 1 ? '1' : '0',
                'sort_order_columns' => (string) ($row['sort_order_columns'] ?? ''),
                'memo' => (string) ($row['memo'] ?? ''),
                'limit_parameter_type' => (string) ($row['limit_parameter_type'] ?? ''),
                'limit_fixed_parameter' => (string) ($row['limit_fixed_parameter'] ?? ''),
                'or_group_type' => (string) ($row['or_group_type'] ?? ''),
                'single_proxy_auth_type' => (string) ($row['single_proxy_auth_type'] ?? ''),
                'single_proxy_single_get_function_name' => (string) ($row['single_proxy_single_get_function_name'] ?? ''),
                'auth_policy_version' => (string) ((int) ($row['auth_policy_version'] ?? 1)),
                'auth_policy_json' => (string) ($row['auth_policy_json'] ?? ''),
                'is_blob_target' => ((int) ($row['is_blob_target'] ?? 0)) === 1 ? '1' : '0',
                'detected_signature' => (string) ($row['detected_signature'] ?? ''),
                'detected_line' => (string) ((int) ($row['detected_line'] ?? 0)),
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
            ],
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

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         function_name:string,
 *         function_list_order:string,
 *         function_suffix:string,
 *         action_type:string,
 *         data_class_base_name:string,
 *         target_table_name:string,
 *         parameter_type:string,
 *         select_by_distinct:string,
 *         sort_order_columns:string,
 *         memo:string,
 *         limit_parameter_type:string,
 *         limit_fixed_parameter:string,
 *         or_group_type:string,
 *         single_proxy_auth_type:string,
 *         single_proxy_single_get_function_name:string,
 *         is_blob_target:string,
 *         detected_signature:string,
 *         detected_line:string,
 *         source_of_truth:string,
 *         updated_at:string
 *     }>,
 *     error:string
 * }
 */
function app_pdo_fetch_db_access_function_metadata_catalog(array $app, string $projectKey, string $sourceName): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $dialect = app_sql_dialect_from_db_config(app_database_config($app, 'config_db'));
        $updatedAtSelect = app_sql_datetime_select_expr($dialect, 'f.updated_at', 'updated_at');
        $statement = $pdo->prepare(
            'SELECT
                f.function_name,
                f.function_list_order,
                f.function_suffix,
                f.action_type,
                f.data_class_base_name,
                f.target_table_name,
                f.parameter_type,
                f.select_by_distinct,
                f.sort_order_columns,
                f.memo,
                f.limit_parameter_type,
                f.limit_fixed_parameter,
                f.or_group_type,
                f.single_proxy_auth_type,
                f.single_proxy_single_get_function_name,
                f.auth_policy_version,
                f.auth_policy_json,
                f.is_blob_target,
                f.detected_signature,
                f.detected_line,
                f.source_of_truth,
                ' . $updatedAtSelect . '
            FROM project_db_access_functions AS f
            INNER JOIN project_db_access_classes AS c
                ON c.id = f.db_access_class_id
            INNER JOIN projects AS p
                ON p.id = c.project_id
            WHERE p.project_key = :project_key
              AND c.source_name = :source_name
            ORDER BY
                CASE
                    WHEN f.function_list_order > 0 THEN f.function_list_order
                    WHEN f.detected_line > 0 THEN f.detected_line
                    ELSE 2147483647
                END,
                CASE
                    WHEN f.detected_line > 0 THEN f.detected_line
                    ELSE 2147483647
                END,
                f.function_name'
        );
        $statement->execute([
            ':project_key' => $projectKey,
            ':source_name' => $sourceName,
        ]);

        $rows = $statement->fetchAll();
        $items = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = [
                'function_name' => (string) ($row['function_name'] ?? ''),
                'function_list_order' => (string) ((int) ($row['function_list_order'] ?? 0)),
                'function_suffix' => (string) ($row['function_suffix'] ?? ''),
                'action_type' => (string) ($row['action_type'] ?? ''),
                'data_class_base_name' => (string) ($row['data_class_base_name'] ?? ''),
                'target_table_name' => (string) ($row['target_table_name'] ?? ''),
                'parameter_type' => (string) ($row['parameter_type'] ?? ''),
                'select_by_distinct' => ((int) ($row['select_by_distinct'] ?? 0)) === 1 ? '1' : '0',
                'sort_order_columns' => (string) ($row['sort_order_columns'] ?? ''),
                'memo' => (string) ($row['memo'] ?? ''),
                'limit_parameter_type' => (string) ($row['limit_parameter_type'] ?? ''),
                'limit_fixed_parameter' => (string) ($row['limit_fixed_parameter'] ?? ''),
                'or_group_type' => (string) ($row['or_group_type'] ?? ''),
                'single_proxy_auth_type' => (string) ($row['single_proxy_auth_type'] ?? ''),
                'single_proxy_single_get_function_name' => (string) ($row['single_proxy_single_get_function_name'] ?? ''),
                'auth_policy_version' => (string) ((int) ($row['auth_policy_version'] ?? 1)),
                'auth_policy_json' => (string) ($row['auth_policy_json'] ?? ''),
                'is_blob_target' => ((int) ($row['is_blob_target'] ?? 0)) === 1 ? '1' : '0',
                'detected_signature' => (string) ($row['detected_signature'] ?? ''),
                'detected_line' => (string) ((int) ($row['detected_line'] ?? 0)),
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @param array{
 *     project_key:string,
 *     source_name:string,
 *     function_name:string,
 *     function_list_order:string,
 *     function_suffix:string,
 *     action_type:string,
 *     data_class_base_name:string,
 *     target_table_name:string,
 *     parameter_type:string,
 *     select_by_distinct:string,
 *     sort_order_columns:string,
 *     memo:string,
 *     limit_parameter_type:string,
 *     limit_fixed_parameter:string,
 *     or_group_type:string,
 *     single_proxy_auth_type:string,
 *     single_proxy_single_get_function_name:string,
 *     is_blob_target:string,
 *     detected_signature:string,
 *     detected_line:string,
 *     source_of_truth:string,
 *     last_detected_dbaccess_file:string,
 *     last_detected_data_file:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_upsert_db_access_function_metadata(array $app, array $input): array
{
    $pdo = null;

    try {
        $blobTargetConstraintError = app_pdo_validate_db_access_function_blob_target_constraint($app, $input);
        if ($blobTargetConstraintError !== '') {
            return [
                'ok' => false,
                'error' => $blobTargetConstraintError,
            ];
        }

        $pdo = app_create_metadata_pdo($app);
        $dialect = app_sql_dialect_from_db_config(app_database_config($app, 'config_db'));
        $pdo->beginTransaction();

        $classId = app_pdo_ensure_db_access_class_id(
            $pdo,
            $input['project_key'],
            $input['source_name'],
            $input['last_detected_dbaccess_file'],
            $input['last_detected_data_file'],
        );

        if ($dialect === 'sqlite') {
            $functionId = app_pdo_find_db_access_function_id(
                $pdo,
                $input['project_key'],
                $input['source_name'],
                $input['function_name'],
            );
            if ($functionId !== null) {
                $statement = $pdo->prepare(
                    'UPDATE project_db_access_functions
                    SET
                        function_list_order = :function_list_order,
                        function_suffix = :function_suffix,
                        action_type = :action_type,
                        data_class_base_name = :data_class_base_name,
                        target_table_name = :target_table_name,
                        parameter_type = :parameter_type,
                        select_by_distinct = :select_by_distinct,
                        sort_order_columns = :sort_order_columns,
                        memo = :memo,
                        limit_parameter_type = :limit_parameter_type,
                        limit_fixed_parameter = :limit_fixed_parameter,
                        or_group_type = :or_group_type,
                        single_proxy_auth_type = :single_proxy_auth_type,
                        single_proxy_single_get_function_name = :single_proxy_single_get_function_name,
                        is_blob_target = :is_blob_target,
                        detected_signature = :detected_signature,
                        detected_line = :detected_line,
                        source_of_truth = :source_of_truth,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id'
                );
                $statement->execute([
                    ':id' => $functionId,
                    ':function_list_order' => (int) $input['function_list_order'],
                    ':function_suffix' => $input['function_suffix'],
                    ':action_type' => $input['action_type'],
                    ':data_class_base_name' => $input['data_class_base_name'],
                    ':target_table_name' => $input['target_table_name'],
                    ':parameter_type' => $input['parameter_type'],
                    ':select_by_distinct' => $input['select_by_distinct'] === '1' ? 1 : 0,
                    ':sort_order_columns' => $input['sort_order_columns'],
                    ':memo' => $input['memo'],
                    ':limit_parameter_type' => $input['limit_parameter_type'],
                    ':limit_fixed_parameter' => $input['limit_fixed_parameter'],
                    ':or_group_type' => $input['or_group_type'],
                    ':single_proxy_auth_type' => $input['single_proxy_auth_type'],
                    ':single_proxy_single_get_function_name' => $input['single_proxy_single_get_function_name'],
                    ':is_blob_target' => $input['is_blob_target'] === '1' ? 1 : 0,
                    ':detected_signature' => $input['detected_signature'],
                    ':detected_line' => (int) $input['detected_line'],
                    ':source_of_truth' => $input['source_of_truth'],
                ]);
            } else {
                $statement = $pdo->prepare(
                    'INSERT INTO project_db_access_functions (
                        db_access_class_id,
                        function_name,
                        function_list_order,
                        function_suffix,
                        action_type,
                        data_class_base_name,
                        target_table_name,
                        parameter_type,
                        select_by_distinct,
                        sort_order_columns,
                        memo,
                        limit_parameter_type,
                        limit_fixed_parameter,
                        or_group_type,
                        single_proxy_auth_type,
                        single_proxy_single_get_function_name,
                        is_blob_target,
                        detected_signature,
                        detected_line,
                        source_of_truth
                    ) VALUES (
                        :db_access_class_id,
                        :function_name,
                        :function_list_order,
                        :function_suffix,
                        :action_type,
                        :data_class_base_name,
                        :target_table_name,
                        :parameter_type,
                        :select_by_distinct,
                        :sort_order_columns,
                        :memo,
                        :limit_parameter_type,
                        :limit_fixed_parameter,
                        :or_group_type,
                        :single_proxy_auth_type,
                        :single_proxy_single_get_function_name,
                        :is_blob_target,
                        :detected_signature,
                        :detected_line,
                        :source_of_truth
                    )'
                );
                $statement->execute([
                    ':db_access_class_id' => $classId,
                    ':function_name' => $input['function_name'],
                    ':function_list_order' => (int) $input['function_list_order'],
                    ':function_suffix' => $input['function_suffix'],
                    ':action_type' => $input['action_type'],
                    ':data_class_base_name' => $input['data_class_base_name'],
                    ':target_table_name' => $input['target_table_name'],
                    ':parameter_type' => $input['parameter_type'],
                    ':select_by_distinct' => $input['select_by_distinct'] === '1' ? 1 : 0,
                    ':sort_order_columns' => $input['sort_order_columns'],
                    ':memo' => $input['memo'],
                    ':limit_parameter_type' => $input['limit_parameter_type'],
                    ':limit_fixed_parameter' => $input['limit_fixed_parameter'],
                    ':or_group_type' => $input['or_group_type'],
                    ':single_proxy_auth_type' => $input['single_proxy_auth_type'],
                    ':single_proxy_single_get_function_name' => $input['single_proxy_single_get_function_name'],
                    ':is_blob_target' => $input['is_blob_target'] === '1' ? 1 : 0,
                    ':detected_signature' => $input['detected_signature'],
                    ':detected_line' => (int) $input['detected_line'],
                    ':source_of_truth' => $input['source_of_truth'],
                ]);
            }

            $pdo->commit();

            return [
                'ok' => true,
                'error' => '',
            ];
        }

        $sql = $dialect === 'sqlite'
            ? 'INSERT INTO project_db_access_functions (
                    db_access_class_id,
                    function_name,
                    function_list_order,
                    function_suffix,
                    action_type,
                    data_class_base_name,
                    target_table_name,
                    parameter_type,
                    select_by_distinct,
                    sort_order_columns,
                    memo,
                    limit_parameter_type,
                    limit_fixed_parameter,
                    or_group_type,
                    single_proxy_auth_type,
                    single_proxy_single_get_function_name,
                    is_blob_target,
                    detected_signature,
                    detected_line,
                    source_of_truth
                ) VALUES (
                    :db_access_class_id,
                    :function_name,
                    :function_list_order,
                    :function_suffix,
                    :action_type,
                    :data_class_base_name,
                    :target_table_name,
                    :parameter_type,
                    :select_by_distinct,
                    :sort_order_columns,
                    :memo,
                    :limit_parameter_type,
                    :limit_fixed_parameter,
                    :or_group_type,
                    :single_proxy_auth_type,
                    :single_proxy_single_get_function_name,
                    :is_blob_target,
                    :detected_signature,
                    :detected_line,
                    :source_of_truth
                )
                ON CONFLICT(db_access_class_id, function_name) DO UPDATE SET
                    function_list_order = excluded.function_list_order,
                    function_suffix = excluded.function_suffix,
                    action_type = excluded.action_type,
                    data_class_base_name = excluded.data_class_base_name,
                    target_table_name = excluded.target_table_name,
                    parameter_type = excluded.parameter_type,
                    select_by_distinct = excluded.select_by_distinct,
                    sort_order_columns = excluded.sort_order_columns,
                    memo = excluded.memo,
                    limit_parameter_type = excluded.limit_parameter_type,
                    limit_fixed_parameter = excluded.limit_fixed_parameter,
                    or_group_type = excluded.or_group_type,
                    single_proxy_auth_type = excluded.single_proxy_auth_type,
                    single_proxy_single_get_function_name = excluded.single_proxy_single_get_function_name,
                    is_blob_target = excluded.is_blob_target,
                    detected_signature = excluded.detected_signature,
                    detected_line = excluded.detected_line,
                    source_of_truth = excluded.source_of_truth,
                    updated_at = CURRENT_TIMESTAMP'
            : 'INSERT INTO project_db_access_functions (
                    db_access_class_id,
                    function_name,
                    function_list_order,
                    function_suffix,
                    action_type,
                    data_class_base_name,
                    target_table_name,
                    parameter_type,
                    select_by_distinct,
                    sort_order_columns,
                    memo,
                    limit_parameter_type,
                    limit_fixed_parameter,
                    or_group_type,
                    single_proxy_auth_type,
                    single_proxy_single_get_function_name,
                    is_blob_target,
                    detected_signature,
                    detected_line,
                    source_of_truth
                ) VALUES (
                    :db_access_class_id,
                    :function_name,
                    :function_list_order,
                    :function_suffix,
                    :action_type,
                    :data_class_base_name,
                    :target_table_name,
                    :parameter_type,
                    :select_by_distinct,
                    :sort_order_columns,
                    :memo,
                    :limit_parameter_type,
                    :limit_fixed_parameter,
                    :or_group_type,
                    :single_proxy_auth_type,
                    :single_proxy_single_get_function_name,
                    :is_blob_target,
                    :detected_signature,
                    :detected_line,
                    :source_of_truth
                )
                ON DUPLICATE KEY UPDATE
                    function_list_order = VALUES(function_list_order),
                    function_suffix = VALUES(function_suffix),
                    action_type = VALUES(action_type),
                    data_class_base_name = VALUES(data_class_base_name),
                    target_table_name = VALUES(target_table_name),
                    parameter_type = VALUES(parameter_type),
                    select_by_distinct = VALUES(select_by_distinct),
                    sort_order_columns = VALUES(sort_order_columns),
                    memo = VALUES(memo),
                    limit_parameter_type = VALUES(limit_parameter_type),
                    limit_fixed_parameter = VALUES(limit_fixed_parameter),
                    or_group_type = VALUES(or_group_type),
                    single_proxy_auth_type = VALUES(single_proxy_auth_type),
                    single_proxy_single_get_function_name = VALUES(single_proxy_single_get_function_name),
                    is_blob_target = VALUES(is_blob_target),
                    detected_signature = VALUES(detected_signature),
                    detected_line = VALUES(detected_line),
                    source_of_truth = VALUES(source_of_truth),
                    updated_at = CURRENT_TIMESTAMP';

        $statement = $pdo->prepare($sql);

        $statement->execute([
            ':db_access_class_id' => $classId,
            ':function_name' => $input['function_name'],
            ':function_list_order' => (int) $input['function_list_order'],
            ':function_suffix' => $input['function_suffix'],
            ':action_type' => $input['action_type'],
            ':data_class_base_name' => $input['data_class_base_name'],
            ':target_table_name' => $input['target_table_name'],
            ':parameter_type' => $input['parameter_type'],
            ':select_by_distinct' => $input['select_by_distinct'] === '1' ? 1 : 0,
            ':sort_order_columns' => $input['sort_order_columns'],
            ':memo' => $input['memo'],
            ':limit_parameter_type' => $input['limit_parameter_type'],
            ':limit_fixed_parameter' => $input['limit_fixed_parameter'],
            ':or_group_type' => $input['or_group_type'],
            ':single_proxy_auth_type' => $input['single_proxy_auth_type'],
            ':single_proxy_single_get_function_name' => $input['single_proxy_single_get_function_name'],
            ':is_blob_target' => $input['is_blob_target'] === '1' ? 1 : 0,
            ':detected_signature' => $input['detected_signature'],
            ':detected_line' => (int) $input['detected_line'],
            ':source_of_truth' => $input['source_of_truth'],
        ]);

        $pdo->commit();

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_delete_db_access_function_metadata(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    $pdo = null;

    try {
        $pdo = app_create_metadata_pdo($app);
        $pdo->beginTransaction();

        $functionId = app_pdo_require_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);
        $statement = $pdo->prepare(
            'DELETE FROM project_db_access_functions
            WHERE id = :id'
        );
        $statement->execute([
            ':id' => $functionId,
        ]);

        $pdo->commit();

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<string>,
 *     error:string
 * }
 */
function app_pdo_fetch_db_access_function_source_output_target_keys(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $dialect = app_sql_dialect_from_db_config(app_database_config($app, 'config_db'));
        $functionUpdatedAtSelect = app_sql_datetime_select_expr($dialect, 'f.updated_at', 'function_updated_at');
        $targetUpdatedAtSelect = app_sql_datetime_select_expr($dialect, 'target.updated_at', 'target_updated_at');
        $statement = $pdo->prepare(
            'SELECT
                target.source_output_key
            FROM project_db_access_function_source_output_targets AS target
            INNER JOIN project_db_access_functions AS f
                ON f.id = target.db_access_function_id
            INNER JOIN project_db_access_classes AS c
                ON c.id = f.db_access_class_id
            INNER JOIN projects AS p
                ON p.id = c.project_id
            WHERE p.project_key = :project_key
              AND c.source_name = :source_name
              AND f.function_name = :function_name
            ORDER BY target.source_output_key'
        );
        $statement->execute([
            ':project_key' => $projectKey,
            ':source_name' => $sourceName,
            ':function_name' => $functionName,
        ]);

        $rows = $statement->fetchAll();
        $items = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $sourceOutputKey = (string) ($row['source_output_key'] ?? '');
            if ($sourceOutputKey !== '') {
                $items[] = $sourceOutputKey;
            }
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_replace_db_access_function_source_output_target_keys(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    array $sourceOutputKeys,
): array {
    $pdo = null;

    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);
        $availableSourceOutputKeys = app_pdo_fetch_project_source_output_key_map($pdo, $projectKey);

        $normalizedKeys = [];
        foreach ($sourceOutputKeys as $sourceOutputKey) {
            if (!is_string($sourceOutputKey)) {
                continue;
            }

            $normalizedKey = app_normalize_source_output_key($sourceOutputKey);
            if (
                $normalizedKey !== ''
                && app_source_output_key_is_valid($normalizedKey)
                && isset($availableSourceOutputKeys[$normalizedKey])
            ) {
                $normalizedKeys[$normalizedKey] = $normalizedKey;
            }
        }

        $pdo->beginTransaction();

        $deleteStatement = $pdo->prepare(
            'DELETE FROM project_db_access_function_source_output_targets
            WHERE db_access_function_id = :db_access_function_id'
        );
        $deleteStatement->execute([
            ':db_access_function_id' => $functionId,
        ]);

        if ($normalizedKeys !== []) {
            $insertStatement = $pdo->prepare(
                'INSERT INTO project_db_access_function_source_output_targets (
                    db_access_function_id,
                    source_output_key
                ) VALUES (
                    :db_access_function_id,
                    :source_output_key
                )'
            );

            foreach ($normalizedKeys as $normalizedKey) {
                $insertStatement->execute([
                    ':db_access_function_id' => $functionId,
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
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         source_name:string,
 *         function_name:string,
 *         function_list_order:string,
 *         action_type:string,
 *         single_proxy_auth_type:string,
 *         single_proxy_single_get_function_name:string,
 *         source_of_truth:string,
 *         function_updated_at:string,
 *         target_updated_at:string
 *     }>,
 *     error:string
 * }
 */
function app_pdo_fetch_source_output_db_access_function_target_catalog(
    array $app,
    string $projectKey,
    string $sourceOutputKey,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $dialect = app_sql_dialect_from_db_config(app_database_config($app, 'config_db'));
        $functionUpdatedAtSelect = app_sql_datetime_select_expr($dialect, 'f.updated_at', 'function_updated_at');
        $targetUpdatedAtSelect = app_sql_datetime_select_expr($dialect, 'target.updated_at', 'target_updated_at');
        $statement = $pdo->prepare(
            'SELECT
                c.source_name,
                f.function_name,
                f.function_list_order,
                f.action_type,
                f.limit_parameter_type,
                f.limit_fixed_parameter,
                f.single_proxy_auth_type,
                f.single_proxy_single_get_function_name,
                f.auth_policy_version,
                f.auth_policy_json,
                f.source_of_truth,
                ' . $functionUpdatedAtSelect . ',
                ' . $targetUpdatedAtSelect . '
            FROM project_db_access_function_source_output_targets AS target
            INNER JOIN project_db_access_functions AS f
                ON f.id = target.db_access_function_id
            INNER JOIN project_db_access_classes AS c
                ON c.id = f.db_access_class_id
            INNER JOIN projects AS p
                ON p.id = c.project_id
            WHERE p.project_key = :project_key
              AND target.source_output_key = :source_output_key
            ORDER BY
                c.source_name,
                CASE
                    WHEN f.function_list_order > 0 THEN f.function_list_order
                    WHEN f.detected_line > 0 THEN f.detected_line
                    ELSE 2147483647
                END,
                CASE
                    WHEN f.detected_line > 0 THEN f.detected_line
                    ELSE 2147483647
                END,
                f.function_name'
        );
        $statement->execute([
            ':project_key' => $projectKey,
            ':source_output_key' => $sourceOutputKey,
        ]);

        $rows = $statement->fetchAll();
        $items = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = [
                'source_name' => (string) ($row['source_name'] ?? ''),
                'function_name' => (string) ($row['function_name'] ?? ''),
                'function_list_order' => (string) ((int) ($row['function_list_order'] ?? 0)),
                'action_type' => (string) ($row['action_type'] ?? ''),
                'limit_parameter_type' => (string) ($row['limit_parameter_type'] ?? ''),
                'limit_fixed_parameter' => (string) ($row['limit_fixed_parameter'] ?? ''),
                'single_proxy_auth_type' => (string) ($row['single_proxy_auth_type'] ?? ''),
                'single_proxy_single_get_function_name' => (string) ($row['single_proxy_single_get_function_name'] ?? ''),
                'auth_policy_version' => (string) ((int) ($row['auth_policy_version'] ?? 1)),
                'auth_policy_json' => (string) ($row['auth_policy_json'] ?? ''),
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
                'function_updated_at' => (string) ($row['function_updated_at'] ?? ''),
                'target_updated_at' => (string) ($row['target_updated_at'] ?? ''),
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @param array{
 *     project_key:string,
 *     source_name:string,
 *     orders:list<array{
 *         function_name:string,
 *         function_list_order:string
 *     }>
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_reorder_db_access_functions(array $app, array $input): array
{
    $pdo = null;

    try {
        $pdo = app_create_metadata_pdo($app);
        $pdo->beginTransaction();

        $classId = app_pdo_require_db_access_class_id($pdo, $input['project_key'], $input['source_name']);
        $statement = $pdo->prepare(
            'SELECT function_name
            FROM project_db_access_functions
            WHERE db_access_class_id = :db_access_class_id'
        );
        $statement->execute([
            ':db_access_class_id' => $classId,
        ]);

        $existingFunctionNames = [];
        foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $value) {
            if (is_string($value) && $value !== '') {
                $existingFunctionNames[] = $value;
            }
        }

        $submittedFunctionNames = [];
        foreach ($input['orders'] as $order) {
            $submittedFunctionNames[] = $order['function_name'];
        }

        $existingFunctionNamesSorted = $existingFunctionNames;
        $submittedFunctionNamesSorted = $submittedFunctionNames;
        sort($existingFunctionNamesSorted, SORT_STRING);
        sort($submittedFunctionNamesSorted, SORT_STRING);

        if ($existingFunctionNamesSorted !== $submittedFunctionNamesSorted) {
            throw new RuntimeException('送信された function 一覧が現在の canonical catalog と一致しません。再読み込みしてやり直してください。');
        }

        $updateStatement = $pdo->prepare(
            'UPDATE project_db_access_functions
            SET function_list_order = :function_list_order,
                updated_at = CURRENT_TIMESTAMP
            WHERE db_access_class_id = :db_access_class_id
              AND function_name = :function_name'
        );

        foreach ($input['orders'] as $order) {
            $updateStatement->execute([
                ':db_access_class_id' => $classId,
                ':function_name' => $order['function_name'],
                ':function_list_order' => (int) $order['function_list_order'],
            ]);
        }

        $pdo->commit();

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @param array{
 *     project_key:string,
 *     source_name:string,
 *     function_name:string,
 *     destination_source_name:string,
 *     destination_last_detected_dbaccess_file:string,
 *     destination_last_detected_data_file:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_move_db_access_function(array $app, array $input): array
{
    $pdo = null;

    try {
        $pdo = app_create_metadata_pdo($app);
        $pdo->beginTransaction();

        $functionId = app_pdo_require_db_access_function_id(
            $pdo,
            $input['project_key'],
            $input['source_name'],
            $input['function_name'],
        );

        if (app_pdo_find_db_access_function_id(
            $pdo,
            $input['project_key'],
            $input['destination_source_name'],
            $input['function_name'],
        ) !== null) {
            throw new RuntimeException('移動先には同名 function の canonical row が既に存在します。');
        }

        $destinationClassId = app_pdo_ensure_db_access_class_id(
            $pdo,
            $input['project_key'],
            $input['destination_source_name'],
            $input['destination_last_detected_dbaccess_file'],
            $input['destination_last_detected_data_file'],
        );

        $updateStatement = $pdo->prepare(
            'UPDATE project_db_access_functions
            SET db_access_class_id = :db_access_class_id,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id'
        );
        $updateStatement->execute([
            ':db_access_class_id' => $destinationClassId,
            ':id' => $functionId,
        ]);

        $pdo->commit();

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         select_where_id:string,
 *         target_table_name:string,
 *         target_table_alias_name:string,
 *         target_table_column_name:string,
 *         parameter_type:string,
 *         parameter_data_type:string,
 *         fixed_parameter:string,
 *         another_table_name:string,
 *         another_table_alias_name:string,
 *         another_field_name:string,
 *         join_type:string,
 *         or_group:string,
 *         relational_operator:string,
 *         where_order:string,
 *         source_of_truth:string,
 *         updated_at:string
 *     }>,
 *     error:string
 * }
 */
function app_pdo_fetch_db_access_function_select_where_catalog(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_find_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);
        if ($functionId === null) {
            return [
                'ok' => true,
                'items' => [],
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'SELECT
                id,
                target_table_name,
                target_table_alias_name,
                target_table_column_name,
                parameter_type,
                parameter_data_type,
                fixed_parameter,
                another_table_name,
                another_table_alias_name,
                another_field_name,
                join_type,
                or_group,
                relational_operator,
                where_order,
                source_of_truth,
                ' . app_pdo_db_access_datetime_select_expr($app) . '
            FROM project_db_access_function_select_wheres
            WHERE db_access_function_id = :function_id
            ORDER BY where_order, id'
        );
        $statement->execute([
            ':function_id' => $functionId,
        ]);

        $rows = $statement->fetchAll();
        $items = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = [
                'select_where_id' => (string) ((int) ($row['id'] ?? 0)),
                'target_table_name' => (string) ($row['target_table_name'] ?? ''),
                'target_table_alias_name' => (string) ($row['target_table_alias_name'] ?? ''),
                'target_table_column_name' => (string) ($row['target_table_column_name'] ?? ''),
                'parameter_type' => (string) ($row['parameter_type'] ?? ''),
                'parameter_data_type' => (string) ($row['parameter_data_type'] ?? ''),
                'fixed_parameter' => (string) ($row['fixed_parameter'] ?? ''),
                'another_table_name' => (string) ($row['another_table_name'] ?? ''),
                'another_table_alias_name' => (string) ($row['another_table_alias_name'] ?? ''),
                'another_field_name' => (string) ($row['another_field_name'] ?? ''),
                'join_type' => (string) ($row['join_type'] ?? ''),
                'or_group' => (string) ($row['or_group'] ?? ''),
                'relational_operator' => (string) ($row['relational_operator'] ?? ''),
                'where_order' => (string) ((int) ($row['where_order'] ?? 0)),
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     item:array{
 *         select_where_id:string,
 *         target_table_name:string,
 *         target_table_alias_name:string,
 *         target_table_column_name:string,
 *         parameter_type:string,
 *         parameter_data_type:string,
 *         fixed_parameter:string,
 *         another_table_name:string,
 *         another_table_alias_name:string,
 *         another_field_name:string,
 *         join_type:string,
 *         or_group:string,
 *         relational_operator:string,
 *         where_order:string,
 *         source_of_truth:string,
 *         updated_at:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_db_access_function_select_where_item(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $selectWhereId,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_find_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);
        if ($functionId === null) {
            return [
                'ok' => true,
                'item' => null,
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'SELECT
                id,
                target_table_name,
                target_table_alias_name,
                target_table_column_name,
                parameter_type,
                parameter_data_type,
                fixed_parameter,
                another_table_name,
                another_table_alias_name,
                another_field_name,
                join_type,
                or_group,
                relational_operator,
                where_order,
                source_of_truth,
                ' . app_pdo_db_access_datetime_select_expr($app) . '
            FROM project_db_access_function_select_wheres
            WHERE db_access_function_id = :function_id
              AND id = :id
            LIMIT 1'
        );
        $statement->execute([
            ':function_id' => $functionId,
            ':id' => (int) $selectWhereId,
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
                'select_where_id' => (string) ((int) ($row['id'] ?? 0)),
                'target_table_name' => (string) ($row['target_table_name'] ?? ''),
                'target_table_alias_name' => (string) ($row['target_table_alias_name'] ?? ''),
                'target_table_column_name' => (string) ($row['target_table_column_name'] ?? ''),
                'parameter_type' => (string) ($row['parameter_type'] ?? ''),
                'parameter_data_type' => (string) ($row['parameter_data_type'] ?? ''),
                'fixed_parameter' => (string) ($row['fixed_parameter'] ?? ''),
                'another_table_name' => (string) ($row['another_table_name'] ?? ''),
                'another_table_alias_name' => (string) ($row['another_table_alias_name'] ?? ''),
                'another_field_name' => (string) ($row['another_field_name'] ?? ''),
                'join_type' => (string) ($row['join_type'] ?? ''),
                'or_group' => (string) ($row['or_group'] ?? ''),
                'relational_operator' => (string) ($row['relational_operator'] ?? ''),
                'where_order' => (string) ((int) ($row['where_order'] ?? 0)),
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
            ],
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

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @param array{
 *     project_key:string,
 *     source_name:string,
 *     function_name:string,
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string,
 *     another_table_name:string,
 *     another_table_alias_name:string,
 *     another_field_name:string,
 *     join_type:string,
 *     or_group:string,
 *     relational_operator:string,
 *     where_order:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     ok:bool,
 *     item_id:string,
 *     error:string
 * }
 */
function app_pdo_create_db_access_function_select_where(array $app, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id(
            $pdo,
            $input['project_key'],
            $input['source_name'],
            $input['function_name'],
        );

        $statement = $pdo->prepare(
            'INSERT INTO project_db_access_function_select_wheres (
                db_access_function_id,
                target_table_name,
                target_table_alias_name,
                target_table_column_name,
                parameter_type,
                parameter_data_type,
                fixed_parameter,
                another_table_name,
                another_table_alias_name,
                another_field_name,
                join_type,
                or_group,
                relational_operator,
                where_order,
                source_of_truth
            ) VALUES (
                :db_access_function_id,
                :target_table_name,
                :target_table_alias_name,
                :target_table_column_name,
                :parameter_type,
                :parameter_data_type,
                :fixed_parameter,
                :another_table_name,
                :another_table_alias_name,
                :another_field_name,
                :join_type,
                :or_group,
                :relational_operator,
                :where_order,
                :source_of_truth
            )'
        );
        $statement->execute([
            ':db_access_function_id' => $functionId,
            ':target_table_name' => $input['target_table_name'],
            ':target_table_alias_name' => $input['target_table_alias_name'],
            ':target_table_column_name' => $input['target_table_column_name'],
            ':parameter_type' => $input['parameter_type'],
            ':parameter_data_type' => $input['parameter_data_type'],
            ':fixed_parameter' => $input['fixed_parameter'],
            ':another_table_name' => $input['another_table_name'],
            ':another_table_alias_name' => $input['another_table_alias_name'],
            ':another_field_name' => $input['another_field_name'],
            ':join_type' => $input['join_type'],
            ':or_group' => $input['or_group'],
            ':relational_operator' => $input['relational_operator'],
            ':where_order' => (int) $input['where_order'],
            ':source_of_truth' => $input['source_of_truth'],
        ]);

        return [
            'ok' => true,
            'item_id' => (string) $pdo->lastInsertId(),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item_id' => '',
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @param array{
 *     project_key:string,
 *     source_name:string,
 *     function_name:string,
 *     select_where_id:string,
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string,
 *     another_table_name:string,
 *     another_table_alias_name:string,
 *     another_field_name:string,
 *     join_type:string,
 *     or_group:string,
 *     relational_operator:string,
 *     where_order:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_update_db_access_function_select_where(array $app, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id(
            $pdo,
            $input['project_key'],
            $input['source_name'],
            $input['function_name'],
        );

        $statement = $pdo->prepare(
            'UPDATE project_db_access_function_select_wheres
            SET
                target_table_name = :target_table_name,
                target_table_alias_name = :target_table_alias_name,
                target_table_column_name = :target_table_column_name,
                parameter_type = :parameter_type,
                parameter_data_type = :parameter_data_type,
                fixed_parameter = :fixed_parameter,
                another_table_name = :another_table_name,
                another_table_alias_name = :another_table_alias_name,
                another_field_name = :another_field_name,
                join_type = :join_type,
                or_group = :or_group,
                relational_operator = :relational_operator,
                where_order = :where_order,
                source_of_truth = :source_of_truth,
                updated_at = CURRENT_TIMESTAMP
            WHERE db_access_function_id = :db_access_function_id
              AND id = :id'
        );
        $statement->execute([
            ':target_table_name' => $input['target_table_name'],
            ':target_table_alias_name' => $input['target_table_alias_name'],
            ':target_table_column_name' => $input['target_table_column_name'],
            ':parameter_type' => $input['parameter_type'],
            ':parameter_data_type' => $input['parameter_data_type'],
            ':fixed_parameter' => $input['fixed_parameter'],
            ':another_table_name' => $input['another_table_name'],
            ':another_table_alias_name' => $input['another_table_alias_name'],
            ':another_field_name' => $input['another_field_name'],
            ':join_type' => $input['join_type'],
            ':or_group' => $input['or_group'],
            ':relational_operator' => $input['relational_operator'],
            ':where_order' => (int) $input['where_order'],
            ':source_of_truth' => $input['source_of_truth'],
            ':db_access_function_id' => $functionId,
            ':id' => (int) $input['select_where_id'],
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_delete_db_access_function_select_where(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $selectWhereId,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);

        $statement = $pdo->prepare(
            'DELETE FROM project_db_access_function_select_wheres
            WHERE db_access_function_id = :db_access_function_id
              AND id = :id'
        );
        $statement->execute([
            ':db_access_function_id' => $functionId,
            ':id' => (int) $selectWhereId,
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @param array{
 *     project_key:string,
 *     source_name:string,
 *     function_name:string,
 *     orders:list<array{
 *         select_where_id:string,
 *         where_order:string
 *     }>
 * } $input
 * @return array{
 *     ok:bool,
 *     updated_count:int,
 *     error:string
 * }
 */
function app_pdo_reorder_db_access_function_select_where(array $app, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id(
            $pdo,
            $input['project_key'],
            $input['source_name'],
            $input['function_name'],
        );

        $statement = $pdo->prepare(
            'SELECT id, where_order
            FROM project_db_access_function_select_wheres
            WHERE db_access_function_id = :db_access_function_id
            ORDER BY id'
        );
        $statement->execute([
            ':db_access_function_id' => $functionId,
        ]);

        $rows = $statement->fetchAll();
        $existingOrderById = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $existingOrderById[(string) ((int) ($row['id'] ?? 0))] = (string) ((int) ($row['where_order'] ?? 0));
        }

        $existingIds = array_map(
            static fn (int|string $value): string => (string) $value,
            array_keys($existingOrderById),
        );
        $submittedIds = [];
        foreach ($input['orders'] as $orderItem) {
            $submittedIds[] = (string) ((int) ($orderItem['select_where_id'] ?? 0));
        }

        sort($existingIds, SORT_NUMERIC);
        sort($submittedIds, SORT_NUMERIC);
        if ($existingIds !== $submittedIds) {
            return [
                'ok' => false,
                'updated_count' => 0,
                'error' => '送信された select where row が最新の catalog と一致しません。再読み込みしてやり直してください。',
            ];
        }

        $updateStatement = $pdo->prepare(
            'UPDATE project_db_access_function_select_wheres
            SET
                where_order = :where_order,
                updated_at = CURRENT_TIMESTAMP
            WHERE db_access_function_id = :db_access_function_id
              AND id = :id'
        );

        $updatedCount = 0;
        $pdo->beginTransaction();

        foreach ($input['orders'] as $orderItem) {
            $selectWhereId = (string) ((int) ($orderItem['select_where_id'] ?? 0));
            $whereOrder = (string) ((int) ($orderItem['where_order'] ?? 0));

            if (($existingOrderById[$selectWhereId] ?? null) === $whereOrder) {
                continue;
            }

            $updateStatement->execute([
                ':where_order' => (int) $whereOrder,
                ':db_access_function_id' => $functionId,
                ':id' => (int) $selectWhereId,
            ]);
            if ($updateStatement->rowCount() > 0) {
                $updatedCount++;
            }
        }

        $pdo->commit();

        return [
            'ok' => true,
            'updated_count' => $updatedCount,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'updated_count' => 0,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         select_target_field_id:string,
 *         target_table_name:string,
 *         target_table_alias_name:string,
 *         target_table_column_name:string,
 *         target_table_column_prefix:string,
 *         target_table_column_suffix:string,
 *         store_class_field_name:string,
 *         group_by_target:string,
 *         field_list_order:string,
 *         source_of_truth:string,
 *         updated_at:string
 *     }>,
 *     error:string
 * }
 */
function app_pdo_fetch_db_access_function_select_target_field_catalog(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_find_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);
        if ($functionId === null) {
            return [
                'ok' => true,
                'items' => [],
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'SELECT
                id,
                target_table_name,
                target_table_alias_name,
                target_table_column_name,
                target_table_column_prefix,
                target_table_column_suffix,
                store_class_field_name,
                group_by_target,
                field_list_order,
                source_of_truth,
                ' . app_pdo_db_access_datetime_select_expr($app) . '
            FROM project_db_access_function_select_target_fields
            WHERE db_access_function_id = :function_id
            ORDER BY field_list_order, id'
        );
        $statement->execute([
            ':function_id' => $functionId,
        ]);

        $rows = $statement->fetchAll();
        $items = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = [
                'select_target_field_id' => (string) ((int) ($row['id'] ?? 0)),
                'target_table_name' => (string) ($row['target_table_name'] ?? ''),
                'target_table_alias_name' => (string) ($row['target_table_alias_name'] ?? ''),
                'target_table_column_name' => (string) ($row['target_table_column_name'] ?? ''),
                'target_table_column_prefix' => (string) ($row['target_table_column_prefix'] ?? ''),
                'target_table_column_suffix' => (string) ($row['target_table_column_suffix'] ?? ''),
                'store_class_field_name' => (string) ($row['store_class_field_name'] ?? ''),
                'group_by_target' => ((string) ((int) ($row['group_by_target'] ?? 0))) === '1' ? '1' : '0',
                'field_list_order' => (string) ((int) ($row['field_list_order'] ?? 0)),
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     item:array{
 *         select_target_field_id:string,
 *         target_table_name:string,
 *         target_table_alias_name:string,
 *         target_table_column_name:string,
 *         target_table_column_prefix:string,
 *         target_table_column_suffix:string,
 *         store_class_field_name:string,
 *         group_by_target:string,
 *         field_list_order:string,
 *         source_of_truth:string,
 *         updated_at:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_db_access_function_select_target_field_item(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $selectTargetFieldId,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_find_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);
        if ($functionId === null) {
            return [
                'ok' => true,
                'item' => null,
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'SELECT
                id,
                target_table_name,
                target_table_alias_name,
                target_table_column_name,
                target_table_column_prefix,
                target_table_column_suffix,
                store_class_field_name,
                group_by_target,
                field_list_order,
                source_of_truth,
                ' . app_pdo_db_access_datetime_select_expr($app) . '
            FROM project_db_access_function_select_target_fields
            WHERE db_access_function_id = :function_id
              AND id = :id
            LIMIT 1'
        );
        $statement->execute([
            ':function_id' => $functionId,
            ':id' => (int) $selectTargetFieldId,
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
                'select_target_field_id' => (string) ((int) ($row['id'] ?? 0)),
                'target_table_name' => (string) ($row['target_table_name'] ?? ''),
                'target_table_alias_name' => (string) ($row['target_table_alias_name'] ?? ''),
                'target_table_column_name' => (string) ($row['target_table_column_name'] ?? ''),
                'target_table_column_prefix' => (string) ($row['target_table_column_prefix'] ?? ''),
                'target_table_column_suffix' => (string) ($row['target_table_column_suffix'] ?? ''),
                'store_class_field_name' => (string) ($row['store_class_field_name'] ?? ''),
                'group_by_target' => ((string) ((int) ($row['group_by_target'] ?? 0))) === '1' ? '1' : '0',
                'field_list_order' => (string) ((int) ($row['field_list_order'] ?? 0)),
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
            ],
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

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @param array{
 *     project_key:string,
 *     source_name:string,
 *     function_name:string,
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     target_table_column_prefix:string,
 *     target_table_column_suffix:string,
 *     store_class_field_name:string,
 *     group_by_target:string,
 *     field_list_order:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     ok:bool,
 *     item_id:string,
 *     error:string
 * }
 */
function app_pdo_create_db_access_function_select_target_field(array $app, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id(
            $pdo,
            $input['project_key'],
            $input['source_name'],
            $input['function_name'],
        );

        $statement = $pdo->prepare(
            'INSERT INTO project_db_access_function_select_target_fields (
                db_access_function_id,
                target_table_name,
                target_table_alias_name,
                target_table_column_name,
                target_table_column_prefix,
                target_table_column_suffix,
                store_class_field_name,
                group_by_target,
                field_list_order,
                source_of_truth
            ) VALUES (
                :db_access_function_id,
                :target_table_name,
                :target_table_alias_name,
                :target_table_column_name,
                :target_table_column_prefix,
                :target_table_column_suffix,
                :store_class_field_name,
                :group_by_target,
                :field_list_order,
                :source_of_truth
            )'
        );
        $statement->execute([
            ':db_access_function_id' => $functionId,
            ':target_table_name' => $input['target_table_name'],
            ':target_table_alias_name' => $input['target_table_alias_name'],
            ':target_table_column_name' => $input['target_table_column_name'],
            ':target_table_column_prefix' => $input['target_table_column_prefix'],
            ':target_table_column_suffix' => $input['target_table_column_suffix'],
            ':store_class_field_name' => $input['store_class_field_name'],
            ':group_by_target' => $input['group_by_target'] === '1' ? 1 : 0,
            ':field_list_order' => (int) $input['field_list_order'],
            ':source_of_truth' => $input['source_of_truth'],
        ]);

        return [
            'ok' => true,
            'item_id' => (string) $pdo->lastInsertId(),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item_id' => '',
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @param array{
 *     project_key:string,
 *     source_name:string,
 *     function_name:string,
 *     select_target_field_id:string,
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     target_table_column_prefix:string,
 *     target_table_column_suffix:string,
 *     store_class_field_name:string,
 *     group_by_target:string,
 *     field_list_order:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_update_db_access_function_select_target_field(array $app, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id(
            $pdo,
            $input['project_key'],
            $input['source_name'],
            $input['function_name'],
        );

        $statement = $pdo->prepare(
            'UPDATE project_db_access_function_select_target_fields
            SET
                target_table_name = :target_table_name,
                target_table_alias_name = :target_table_alias_name,
                target_table_column_name = :target_table_column_name,
                target_table_column_prefix = :target_table_column_prefix,
                target_table_column_suffix = :target_table_column_suffix,
                store_class_field_name = :store_class_field_name,
                group_by_target = :group_by_target,
                field_list_order = :field_list_order,
                source_of_truth = :source_of_truth,
                updated_at = CURRENT_TIMESTAMP
            WHERE db_access_function_id = :db_access_function_id
              AND id = :id'
        );
        $statement->execute([
            ':target_table_name' => $input['target_table_name'],
            ':target_table_alias_name' => $input['target_table_alias_name'],
            ':target_table_column_name' => $input['target_table_column_name'],
            ':target_table_column_prefix' => $input['target_table_column_prefix'],
            ':target_table_column_suffix' => $input['target_table_column_suffix'],
            ':store_class_field_name' => $input['store_class_field_name'],
            ':group_by_target' => $input['group_by_target'] === '1' ? 1 : 0,
            ':field_list_order' => (int) $input['field_list_order'],
            ':source_of_truth' => $input['source_of_truth'],
            ':db_access_function_id' => $functionId,
            ':id' => (int) $input['select_target_field_id'],
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_delete_db_access_function_select_target_field(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $selectTargetFieldId,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);

        $statement = $pdo->prepare(
            'DELETE FROM project_db_access_function_select_target_fields
            WHERE db_access_function_id = :db_access_function_id
              AND id = :id'
        );
        $statement->execute([
            ':db_access_function_id' => $functionId,
            ':id' => (int) $selectTargetFieldId,
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         select_having_id:string,
 *         left_target_prefix:string,
 *         left_target_field_id:string,
 *         left_target_suffix:string,
 *         relational_operator:string,
 *         right_target_prefix:string,
 *         right_parameter_type:string,
 *         right_parameter_data_type:string,
 *         right_fixed_parameter:string,
 *         right_target_field_id:string,
 *         right_target_suffix:string,
 *         having_order:string,
 *         source_of_truth:string,
 *         updated_at:string
 *     }>,
 *     error:string
 * }
 */
function app_pdo_fetch_db_access_function_select_having_catalog(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_find_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);
        if ($functionId === null) {
            return [
                'ok' => true,
                'items' => [],
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'SELECT
                id,
                left_target_prefix,
                left_target_field_id,
                left_target_suffix,
                relational_operator,
                right_target_prefix,
                right_parameter_type,
                right_parameter_data_type,
                right_fixed_parameter,
                right_target_field_id,
                right_target_suffix,
                having_order,
                source_of_truth,
                ' . app_pdo_db_access_datetime_select_expr($app) . '
            FROM project_db_access_function_select_havings
            WHERE db_access_function_id = :function_id
            ORDER BY having_order, id'
        );
        $statement->execute([
            ':function_id' => $functionId,
        ]);

        $rows = $statement->fetchAll();
        $items = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = [
                'select_having_id' => (string) ((int) ($row['id'] ?? 0)),
                'left_target_prefix' => (string) ($row['left_target_prefix'] ?? ''),
                'left_target_field_id' => (string) ((int) ($row['left_target_field_id'] ?? 0)),
                'left_target_suffix' => (string) ($row['left_target_suffix'] ?? ''),
                'relational_operator' => (string) ($row['relational_operator'] ?? ''),
                'right_target_prefix' => (string) ($row['right_target_prefix'] ?? ''),
                'right_parameter_type' => (string) ($row['right_parameter_type'] ?? ''),
                'right_parameter_data_type' => (string) ($row['right_parameter_data_type'] ?? ''),
                'right_fixed_parameter' => (string) ($row['right_fixed_parameter'] ?? ''),
                'right_target_field_id' => (string) ((int) ($row['right_target_field_id'] ?? 0)),
                'right_target_suffix' => (string) ($row['right_target_suffix'] ?? ''),
                'having_order' => (string) ((int) ($row['having_order'] ?? 0)),
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     item:array{
 *         select_having_id:string,
 *         left_target_prefix:string,
 *         left_target_field_id:string,
 *         left_target_suffix:string,
 *         relational_operator:string,
 *         right_target_prefix:string,
 *         right_parameter_type:string,
 *         right_parameter_data_type:string,
 *         right_fixed_parameter:string,
 *         right_target_field_id:string,
 *         right_target_suffix:string,
 *         having_order:string,
 *         source_of_truth:string,
 *         updated_at:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_db_access_function_select_having_item(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $selectHavingId,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_find_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);
        if ($functionId === null) {
            return [
                'ok' => true,
                'item' => null,
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'SELECT
                id,
                left_target_prefix,
                left_target_field_id,
                left_target_suffix,
                relational_operator,
                right_target_prefix,
                right_parameter_type,
                right_parameter_data_type,
                right_fixed_parameter,
                right_target_field_id,
                right_target_suffix,
                having_order,
                source_of_truth,
                ' . app_pdo_db_access_datetime_select_expr($app) . '
            FROM project_db_access_function_select_havings
            WHERE db_access_function_id = :function_id
              AND id = :id
            LIMIT 1'
        );
        $statement->execute([
            ':function_id' => $functionId,
            ':id' => (int) $selectHavingId,
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
                'select_having_id' => (string) ((int) ($row['id'] ?? 0)),
                'left_target_prefix' => (string) ($row['left_target_prefix'] ?? ''),
                'left_target_field_id' => (string) ((int) ($row['left_target_field_id'] ?? 0)),
                'left_target_suffix' => (string) ($row['left_target_suffix'] ?? ''),
                'relational_operator' => (string) ($row['relational_operator'] ?? ''),
                'right_target_prefix' => (string) ($row['right_target_prefix'] ?? ''),
                'right_parameter_type' => (string) ($row['right_parameter_type'] ?? ''),
                'right_parameter_data_type' => (string) ($row['right_parameter_data_type'] ?? ''),
                'right_fixed_parameter' => (string) ($row['right_fixed_parameter'] ?? ''),
                'right_target_field_id' => (string) ((int) ($row['right_target_field_id'] ?? 0)),
                'right_target_suffix' => (string) ($row['right_target_suffix'] ?? ''),
                'having_order' => (string) ((int) ($row['having_order'] ?? 0)),
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
            ],
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

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @param array{
 *     project_key:string,
 *     source_name:string,
 *     function_name:string,
 *     left_target_prefix:string,
 *     left_target_field_id:string,
 *     left_target_suffix:string,
 *     relational_operator:string,
 *     right_target_prefix:string,
 *     right_parameter_type:string,
 *     right_parameter_data_type:string,
 *     right_fixed_parameter:string,
 *     right_target_field_id:string,
 *     right_target_suffix:string,
 *     having_order:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     ok:bool,
 *     item_id:string,
 *     error:string
 * }
 */
function app_pdo_create_db_access_function_select_having(array $app, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id(
            $pdo,
            $input['project_key'],
            $input['source_name'],
            $input['function_name'],
        );

        $statement = $pdo->prepare(
            'INSERT INTO project_db_access_function_select_havings (
                db_access_function_id,
                left_target_prefix,
                left_target_field_id,
                left_target_suffix,
                relational_operator,
                right_target_prefix,
                right_parameter_type,
                right_parameter_data_type,
                right_fixed_parameter,
                right_target_field_id,
                right_target_suffix,
                having_order,
                source_of_truth
            ) VALUES (
                :db_access_function_id,
                :left_target_prefix,
                :left_target_field_id,
                :left_target_suffix,
                :relational_operator,
                :right_target_prefix,
                :right_parameter_type,
                :right_parameter_data_type,
                :right_fixed_parameter,
                :right_target_field_id,
                :right_target_suffix,
                :having_order,
                :source_of_truth
            )'
        );
        $statement->execute([
            ':db_access_function_id' => $functionId,
            ':left_target_prefix' => $input['left_target_prefix'],
            ':left_target_field_id' => (int) $input['left_target_field_id'],
            ':left_target_suffix' => $input['left_target_suffix'],
            ':relational_operator' => $input['relational_operator'],
            ':right_target_prefix' => $input['right_target_prefix'],
            ':right_parameter_type' => $input['right_parameter_type'],
            ':right_parameter_data_type' => $input['right_parameter_data_type'],
            ':right_fixed_parameter' => $input['right_fixed_parameter'],
            ':right_target_field_id' => (int) $input['right_target_field_id'],
            ':right_target_suffix' => $input['right_target_suffix'],
            ':having_order' => (int) $input['having_order'],
            ':source_of_truth' => $input['source_of_truth'],
        ]);

        return [
            'ok' => true,
            'item_id' => (string) $pdo->lastInsertId(),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item_id' => '',
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @param array{
 *     project_key:string,
 *     source_name:string,
 *     function_name:string,
 *     select_having_id:string,
 *     left_target_prefix:string,
 *     left_target_field_id:string,
 *     left_target_suffix:string,
 *     relational_operator:string,
 *     right_target_prefix:string,
 *     right_parameter_type:string,
 *     right_parameter_data_type:string,
 *     right_fixed_parameter:string,
 *     right_target_field_id:string,
 *     right_target_suffix:string,
 *     having_order:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_update_db_access_function_select_having(array $app, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id(
            $pdo,
            $input['project_key'],
            $input['source_name'],
            $input['function_name'],
        );

        $statement = $pdo->prepare(
            'UPDATE project_db_access_function_select_havings
            SET
                left_target_prefix = :left_target_prefix,
                left_target_field_id = :left_target_field_id,
                left_target_suffix = :left_target_suffix,
                relational_operator = :relational_operator,
                right_target_prefix = :right_target_prefix,
                right_parameter_type = :right_parameter_type,
                right_parameter_data_type = :right_parameter_data_type,
                right_fixed_parameter = :right_fixed_parameter,
                right_target_field_id = :right_target_field_id,
                right_target_suffix = :right_target_suffix,
                having_order = :having_order,
                source_of_truth = :source_of_truth,
                updated_at = CURRENT_TIMESTAMP
            WHERE db_access_function_id = :db_access_function_id
              AND id = :id'
        );
        $statement->execute([
            ':left_target_prefix' => $input['left_target_prefix'],
            ':left_target_field_id' => (int) $input['left_target_field_id'],
            ':left_target_suffix' => $input['left_target_suffix'],
            ':relational_operator' => $input['relational_operator'],
            ':right_target_prefix' => $input['right_target_prefix'],
            ':right_parameter_type' => $input['right_parameter_type'],
            ':right_parameter_data_type' => $input['right_parameter_data_type'],
            ':right_fixed_parameter' => $input['right_fixed_parameter'],
            ':right_target_field_id' => (int) $input['right_target_field_id'],
            ':right_target_suffix' => $input['right_target_suffix'],
            ':having_order' => (int) $input['having_order'],
            ':source_of_truth' => $input['source_of_truth'],
            ':db_access_function_id' => $functionId,
            ':id' => (int) $input['select_having_id'],
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_delete_db_access_function_select_having(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $selectHavingId,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);

        $statement = $pdo->prepare(
            'DELETE FROM project_db_access_function_select_havings
            WHERE db_access_function_id = :db_access_function_id
              AND id = :id'
        );
        $statement->execute([
            ':db_access_function_id' => $functionId,
            ':id' => (int) $selectHavingId,
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         update_delete_where_id:string,
 *         target_table_column_name:string,
 *         parameter_type:string,
 *         parameter_data_type:string,
 *         fixed_parameter:string,
 *         or_group:string,
 *         relational_operator:string,
 *         where_order:string,
 *         source_of_truth:string,
 *         updated_at:string
 *     }>,
 *     error:string
 * }
 */
function app_pdo_fetch_db_access_function_update_delete_where_catalog(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_find_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);
        if ($functionId === null) {
            return [
                'ok' => true,
                'items' => [],
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'SELECT
                id,
                target_table_column_name,
                parameter_type,
                parameter_data_type,
                fixed_parameter,
                or_group,
                relational_operator,
                where_order,
                source_of_truth,
                ' . app_pdo_db_access_datetime_select_expr($app) . '
            FROM project_db_access_function_update_delete_wheres
            WHERE db_access_function_id = :function_id
            ORDER BY where_order, id'
        );
        $statement->execute([
            ':function_id' => $functionId,
        ]);

        $rows = $statement->fetchAll();
        $items = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = [
                'update_delete_where_id' => (string) ((int) ($row['id'] ?? 0)),
                'target_table_column_name' => (string) ($row['target_table_column_name'] ?? ''),
                'parameter_type' => (string) ($row['parameter_type'] ?? ''),
                'parameter_data_type' => (string) ($row['parameter_data_type'] ?? ''),
                'fixed_parameter' => (string) ($row['fixed_parameter'] ?? ''),
                'or_group' => (string) ($row['or_group'] ?? ''),
                'relational_operator' => (string) ($row['relational_operator'] ?? ''),
                'where_order' => (string) ((int) ($row['where_order'] ?? 0)),
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     item:array{
 *         update_delete_where_id:string,
 *         target_table_column_name:string,
 *         parameter_type:string,
 *         parameter_data_type:string,
 *         fixed_parameter:string,
 *         or_group:string,
 *         relational_operator:string,
 *         where_order:string,
 *         source_of_truth:string,
 *         updated_at:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_db_access_function_update_delete_where_item(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $updateDeleteWhereId,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_find_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);
        if ($functionId === null) {
            return [
                'ok' => true,
                'item' => null,
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'SELECT
                id,
                target_table_column_name,
                parameter_type,
                parameter_data_type,
                fixed_parameter,
                or_group,
                relational_operator,
                where_order,
                source_of_truth,
                ' . app_pdo_db_access_datetime_select_expr($app) . '
            FROM project_db_access_function_update_delete_wheres
            WHERE db_access_function_id = :function_id
              AND id = :id
            LIMIT 1'
        );
        $statement->execute([
            ':function_id' => $functionId,
            ':id' => (int) $updateDeleteWhereId,
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
                'update_delete_where_id' => (string) ((int) ($row['id'] ?? 0)),
                'target_table_column_name' => (string) ($row['target_table_column_name'] ?? ''),
                'parameter_type' => (string) ($row['parameter_type'] ?? ''),
                'parameter_data_type' => (string) ($row['parameter_data_type'] ?? ''),
                'fixed_parameter' => (string) ($row['fixed_parameter'] ?? ''),
                'or_group' => (string) ($row['or_group'] ?? ''),
                'relational_operator' => (string) ($row['relational_operator'] ?? ''),
                'where_order' => (string) ((int) ($row['where_order'] ?? 0)),
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
            ],
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

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @param array{
 *     project_key:string,
 *     source_name:string,
 *     function_name:string,
 *     target_table_column_name:string,
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string,
 *     or_group:string,
 *     relational_operator:string,
 *     where_order:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     ok:bool,
 *     item_id:string,
 *     error:string
 * }
 */
function app_pdo_create_db_access_function_update_delete_where(array $app, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id(
            $pdo,
            $input['project_key'],
            $input['source_name'],
            $input['function_name'],
        );

        $statement = $pdo->prepare(
            'INSERT INTO project_db_access_function_update_delete_wheres (
                db_access_function_id,
                target_table_column_name,
                parameter_type,
                parameter_data_type,
                fixed_parameter,
                or_group,
                relational_operator,
                where_order,
                source_of_truth
            ) VALUES (
                :db_access_function_id,
                :target_table_column_name,
                :parameter_type,
                :parameter_data_type,
                :fixed_parameter,
                :or_group,
                :relational_operator,
                :where_order,
                :source_of_truth
            )'
        );
        $statement->execute([
            ':db_access_function_id' => $functionId,
            ':target_table_column_name' => $input['target_table_column_name'],
            ':parameter_type' => $input['parameter_type'],
            ':parameter_data_type' => $input['parameter_data_type'],
            ':fixed_parameter' => $input['fixed_parameter'],
            ':or_group' => $input['or_group'],
            ':relational_operator' => $input['relational_operator'],
            ':where_order' => (int) $input['where_order'],
            ':source_of_truth' => $input['source_of_truth'],
        ]);

        return [
            'ok' => true,
            'item_id' => (string) $pdo->lastInsertId(),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item_id' => '',
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @param array{
 *     project_key:string,
 *     source_name:string,
 *     function_name:string,
 *     update_delete_where_id:string,
 *     target_table_column_name:string,
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string,
 *     or_group:string,
 *     relational_operator:string,
 *     where_order:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_update_db_access_function_update_delete_where(array $app, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id(
            $pdo,
            $input['project_key'],
            $input['source_name'],
            $input['function_name'],
        );

        $statement = $pdo->prepare(
            'UPDATE project_db_access_function_update_delete_wheres
            SET
                target_table_column_name = :target_table_column_name,
                parameter_type = :parameter_type,
                parameter_data_type = :parameter_data_type,
                fixed_parameter = :fixed_parameter,
                or_group = :or_group,
                relational_operator = :relational_operator,
                where_order = :where_order,
                source_of_truth = :source_of_truth,
                updated_at = CURRENT_TIMESTAMP
            WHERE db_access_function_id = :db_access_function_id
              AND id = :id'
        );
        $statement->execute([
            ':target_table_column_name' => $input['target_table_column_name'],
            ':parameter_type' => $input['parameter_type'],
            ':parameter_data_type' => $input['parameter_data_type'],
            ':fixed_parameter' => $input['fixed_parameter'],
            ':or_group' => $input['or_group'],
            ':relational_operator' => $input['relational_operator'],
            ':where_order' => (int) $input['where_order'],
            ':source_of_truth' => $input['source_of_truth'],
            ':db_access_function_id' => $functionId,
            ':id' => (int) $input['update_delete_where_id'],
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_delete_db_access_function_update_delete_where(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $updateDeleteWhereId,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);

        $statement = $pdo->prepare(
            'DELETE FROM project_db_access_function_update_delete_wheres
            WHERE db_access_function_id = :db_access_function_id
              AND id = :id'
        );
        $statement->execute([
            ':db_access_function_id' => $functionId,
            ':id' => (int) $updateDeleteWhereId,
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
 *     db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @param array{
 *     project_key:string,
 *     source_name:string,
 *     function_name:string,
 *     orders:list<array{
 *         update_delete_where_id:string,
 *         where_order:string
 *     }>
 * } $input
 * @return array{
 *     ok:bool,
 *     updated_count:int,
 *     error:string
 * }
 */
function app_pdo_reorder_db_access_function_update_delete_where(array $app, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id(
            $pdo,
            $input['project_key'],
            $input['source_name'],
            $input['function_name'],
        );

        $statement = $pdo->prepare(
            'SELECT id, where_order
            FROM project_db_access_function_update_delete_wheres
            WHERE db_access_function_id = :db_access_function_id
            ORDER BY id'
        );
        $statement->execute([
            ':db_access_function_id' => $functionId,
        ]);

        $rows = $statement->fetchAll();
        $existingOrderById = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $existingOrderById[(string) ((int) ($row['id'] ?? 0))] = (string) ((int) ($row['where_order'] ?? 0));
        }

        $existingIds = array_map(
            static fn (int|string $value): string => (string) $value,
            array_keys($existingOrderById),
        );
        $submittedIds = [];
        foreach ($input['orders'] as $orderItem) {
            $submittedIds[] = (string) ((int) ($orderItem['update_delete_where_id'] ?? 0));
        }

        sort($existingIds, SORT_NUMERIC);
        sort($submittedIds, SORT_NUMERIC);
        if ($existingIds !== $submittedIds) {
            return [
                'ok' => false,
                'updated_count' => 0,
                'error' => '送信された update/delete where row が最新の catalog と一致しません。再読み込みしてやり直してください。',
            ];
        }

        $updateStatement = $pdo->prepare(
            'UPDATE project_db_access_function_update_delete_wheres
            SET
                where_order = :where_order,
                updated_at = CURRENT_TIMESTAMP
            WHERE db_access_function_id = :db_access_function_id
              AND id = :id'
        );

        $updatedCount = 0;
        $pdo->beginTransaction();

        foreach ($input['orders'] as $orderItem) {
            $updateDeleteWhereId = (string) ((int) ($orderItem['update_delete_where_id'] ?? 0));
            $whereOrder = (string) ((int) ($orderItem['where_order'] ?? 0));

            if (($existingOrderById[$updateDeleteWhereId] ?? null) === $whereOrder) {
                continue;
            }

            $updateStatement->execute([
                ':where_order' => (int) $whereOrder,
                ':db_access_function_id' => $functionId,
                ':id' => (int) $updateDeleteWhereId,
            ]);
            if ($updateStatement->rowCount() > 0) {
                $updatedCount++;
            }
        }

        $pdo->commit();

        return [
            'ok' => true,
            'updated_count' => $updatedCount,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'updated_count' => 0,
            'error' => $throwable->getMessage(),
        ];
    }
}

function app_pdo_fetch_db_access_function_insert_target_field_catalog(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    return app_pdo_fetch_db_access_function_simple_target_field_catalog(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        'project_db_access_function_insert_target_fields',
        'insert_target_field_id',
    );
}

function app_pdo_fetch_db_access_function_insert_target_field_item(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $insertTargetFieldId,
): array {
    return app_pdo_fetch_db_access_function_simple_target_field_item(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        $insertTargetFieldId,
        'project_db_access_function_insert_target_fields',
        'insert_target_field_id',
    );
}

function app_pdo_create_db_access_function_insert_target_field(array $app, array $input): array
{
    return app_pdo_create_db_access_function_simple_target_field(
        $app,
        $input,
        'project_db_access_function_insert_target_fields',
    );
}

function app_pdo_update_db_access_function_insert_target_field(array $app, array $input): array
{
    return app_pdo_update_db_access_function_simple_target_field(
        $app,
        $input,
        'project_db_access_function_insert_target_fields',
        'insert_target_field_id',
    );
}

function app_pdo_delete_db_access_function_insert_target_field(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $insertTargetFieldId,
): array {
    return app_pdo_delete_db_access_function_simple_target_field(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        $insertTargetFieldId,
        'project_db_access_function_insert_target_fields',
    );
}

function app_pdo_fetch_db_access_function_update_target_field_catalog(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    return app_pdo_fetch_db_access_function_simple_target_field_catalog(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        'project_db_access_function_update_target_fields',
        'update_target_field_id',
    );
}

function app_pdo_fetch_db_access_function_update_target_field_item(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $updateTargetFieldId,
): array {
    return app_pdo_fetch_db_access_function_simple_target_field_item(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        $updateTargetFieldId,
        'project_db_access_function_update_target_fields',
        'update_target_field_id',
    );
}

function app_pdo_create_db_access_function_update_target_field(array $app, array $input): array
{
    return app_pdo_create_db_access_function_simple_target_field(
        $app,
        $input,
        'project_db_access_function_update_target_fields',
    );
}

function app_pdo_update_db_access_function_update_target_field(array $app, array $input): array
{
    return app_pdo_update_db_access_function_simple_target_field(
        $app,
        $input,
        'project_db_access_function_update_target_fields',
        'update_target_field_id',
    );
}

function app_pdo_delete_db_access_function_update_target_field(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $updateTargetFieldId,
): array {
    return app_pdo_delete_db_access_function_simple_target_field(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        $updateTargetFieldId,
        'project_db_access_function_update_target_fields',
    );
}

function app_pdo_fetch_db_access_function_simple_target_field_catalog(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $tableName,
    string $itemIdKey,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_find_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);
        if ($functionId === null) {
            return [
                'ok' => true,
                'items' => [],
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'SELECT
                id,
                target_table_column_name,
                parameter_type,
                parameter_data_type,
                fixed_parameter,
                field_list_order,
                source_of_truth,
                ' . app_pdo_db_access_datetime_select_expr($app) . '
            FROM ' . $tableName . '
            WHERE db_access_function_id = :function_id
            ORDER BY field_list_order, id'
        );
        $statement->execute([
            ':function_id' => $functionId,
        ]);

        $rows = $statement->fetchAll();
        $items = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = [
                $itemIdKey => (string) ((int) ($row['id'] ?? 0)),
                'target_table_column_name' => (string) ($row['target_table_column_name'] ?? ''),
                'parameter_type' => (string) ($row['parameter_type'] ?? ''),
                'parameter_data_type' => (string) ($row['parameter_data_type'] ?? ''),
                'fixed_parameter' => (string) ($row['fixed_parameter'] ?? ''),
                'field_list_order' => (string) ((int) ($row['field_list_order'] ?? 0)),
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
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

function app_pdo_fetch_db_access_function_simple_target_field_item(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $itemId,
    string $tableName,
    string $itemIdKey,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_find_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);
        if ($functionId === null) {
            return [
                'ok' => true,
                'item' => null,
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'SELECT
                id,
                target_table_column_name,
                parameter_type,
                parameter_data_type,
                fixed_parameter,
                field_list_order,
                source_of_truth,
                ' . app_pdo_db_access_datetime_select_expr($app) . '
            FROM ' . $tableName . '
            WHERE db_access_function_id = :function_id
              AND id = :id
            LIMIT 1'
        );
        $statement->execute([
            ':function_id' => $functionId,
            ':id' => (int) $itemId,
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
                $itemIdKey => (string) ((int) ($row['id'] ?? 0)),
                'target_table_column_name' => (string) ($row['target_table_column_name'] ?? ''),
                'parameter_type' => (string) ($row['parameter_type'] ?? ''),
                'parameter_data_type' => (string) ($row['parameter_data_type'] ?? ''),
                'fixed_parameter' => (string) ($row['fixed_parameter'] ?? ''),
                'field_list_order' => (string) ((int) ($row['field_list_order'] ?? 0)),
                'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
            ],
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

function app_pdo_create_db_access_function_simple_target_field(array $app, array $input, string $tableName): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id(
            $pdo,
            $input['project_key'],
            $input['source_name'],
            $input['function_name'],
        );
        $blobTargetContext = app_pdo_fetch_db_access_function_blob_target_context(
            $pdo,
            $input['project_key'],
            $input['source_name'],
            $input['function_name'],
        );
        $fileParameterConstraintError = app_pdo_validate_db_access_function_file_parameter_constraint(
            $app,
            $input['source_name'],
            $input['function_name'],
            (string) ($input['parameter_data_type'] ?? ''),
            $blobTargetContext['is_blob_target'],
            $blobTargetContext['last_detected_dbaccess_file'],
        );
        if ($fileParameterConstraintError !== '') {
            return [
                'ok' => false,
                'item_id' => '',
                'error' => $fileParameterConstraintError,
            ];
        }

        $statement = $pdo->prepare(
            'INSERT INTO ' . $tableName . ' (
                db_access_function_id,
                target_table_column_name,
                parameter_type,
                parameter_data_type,
                fixed_parameter,
                field_list_order,
                source_of_truth
            ) VALUES (
                :db_access_function_id,
                :target_table_column_name,
                :parameter_type,
                :parameter_data_type,
                :fixed_parameter,
                :field_list_order,
                :source_of_truth
            )'
        );
        $statement->execute([
            ':db_access_function_id' => $functionId,
            ':target_table_column_name' => $input['target_table_column_name'],
            ':parameter_type' => $input['parameter_type'],
            ':parameter_data_type' => $input['parameter_data_type'],
            ':fixed_parameter' => $input['fixed_parameter'],
            ':field_list_order' => (int) $input['field_list_order'],
            ':source_of_truth' => $input['source_of_truth'],
        ]);

        return [
            'ok' => true,
            'item_id' => (string) $pdo->lastInsertId(),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item_id' => '',
            'error' => $throwable->getMessage(),
        ];
    }
}

function app_pdo_update_db_access_function_simple_target_field(
    array $app,
    array $input,
    string $tableName,
    string $idInputKey,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id(
            $pdo,
            $input['project_key'],
            $input['source_name'],
            $input['function_name'],
        );
        $blobTargetContext = app_pdo_fetch_db_access_function_blob_target_context(
            $pdo,
            $input['project_key'],
            $input['source_name'],
            $input['function_name'],
        );
        $fileParameterConstraintError = app_pdo_validate_db_access_function_file_parameter_constraint(
            $app,
            $input['source_name'],
            $input['function_name'],
            (string) ($input['parameter_data_type'] ?? ''),
            $blobTargetContext['is_blob_target'],
            $blobTargetContext['last_detected_dbaccess_file'],
        );
        if ($fileParameterConstraintError !== '') {
            return [
                'ok' => false,
                'error' => $fileParameterConstraintError,
            ];
        }

        $statement = $pdo->prepare(
            'UPDATE ' . $tableName . '
            SET
                target_table_column_name = :target_table_column_name,
                parameter_type = :parameter_type,
                parameter_data_type = :parameter_data_type,
                fixed_parameter = :fixed_parameter,
                field_list_order = :field_list_order,
                source_of_truth = :source_of_truth,
                updated_at = CURRENT_TIMESTAMP
            WHERE db_access_function_id = :db_access_function_id
              AND id = :id'
        );
        $statement->execute([
            ':target_table_column_name' => $input['target_table_column_name'],
            ':parameter_type' => $input['parameter_type'],
            ':parameter_data_type' => $input['parameter_data_type'],
            ':fixed_parameter' => $input['fixed_parameter'],
            ':field_list_order' => (int) $input['field_list_order'],
            ':source_of_truth' => $input['source_of_truth'],
            ':db_access_function_id' => $functionId,
            ':id' => (int) $input[$idInputKey],
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

function app_pdo_delete_db_access_function_simple_target_field(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $itemId,
    string $tableName,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);
        $functionId = app_pdo_require_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);

        $statement = $pdo->prepare(
            'DELETE FROM ' . $tableName . '
            WHERE db_access_function_id = :db_access_function_id
              AND id = :id'
        );
        $statement->execute([
            ':db_access_function_id' => $functionId,
            ':id' => (int) $itemId,
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

function app_pdo_resolve_project_id(PDO $pdo, string $projectKey): int
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

    $projectId = $statement->fetchColumn();
    if ($projectId === false) {
        throw new RuntimeException('対応する project が見つかりません。');
    }

    return (int) $projectId;
}

function app_pdo_fetch_project_source_output_key_map(PDO $pdo, string $projectKey): array
{
    $statement = $pdo->prepare(
        'SELECT so.source_output_key
        FROM project_source_outputs AS so
        INNER JOIN projects AS p
            ON p.id = so.project_id
        WHERE p.project_key = :project_key'
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

        $sourceOutputKey = (string) ($row['source_output_key'] ?? '');
        if ($sourceOutputKey !== '') {
            $items[$sourceOutputKey] = true;
        }
    }

    return $items;
}

function app_pdo_find_db_access_function_id(PDO $pdo, string $projectKey, string $sourceName, string $functionName): ?int
{
    $statement = $pdo->prepare(
        'SELECT f.id
        FROM project_db_access_functions AS f
        INNER JOIN project_db_access_classes AS c
            ON c.id = f.db_access_class_id
        INNER JOIN projects AS p
            ON p.id = c.project_id
        WHERE p.project_key = :project_key
          AND c.source_name = :source_name
          AND f.function_name = :function_name
        LIMIT 1'
    );
    $statement->execute([
        ':project_key' => $projectKey,
        ':source_name' => $sourceName,
        ':function_name' => $functionName,
    ]);

    $functionId = $statement->fetchColumn();
    if ($functionId === false) {
        return null;
    }

    return (int) $functionId;
}

function app_pdo_find_db_access_class_id(PDO $pdo, string $projectKey, string $sourceName): ?int
{
    $statement = $pdo->prepare(
        'SELECT c.id
        FROM project_db_access_classes AS c
        INNER JOIN projects AS p
            ON p.id = c.project_id
        WHERE p.project_key = :project_key
          AND c.source_name = :source_name
        LIMIT 1'
    );
    $statement->execute([
        ':project_key' => $projectKey,
        ':source_name' => $sourceName,
    ]);

    $classId = $statement->fetchColumn();
    if ($classId === false) {
        return null;
    }

    return (int) $classId;
}

function app_pdo_require_db_access_class_id(PDO $pdo, string $projectKey, string $sourceName): int
{
    $classId = app_pdo_find_db_access_class_id($pdo, $projectKey, $sourceName);
    if ($classId === null) {
        throw new RuntimeException('先に DB Access Class と function detail で canonical metadata を保存してください。');
    }

    return $classId;
}

function app_pdo_require_db_access_function_id(PDO $pdo, string $projectKey, string $sourceName, string $functionName): int
{
    $functionId = app_pdo_find_db_access_function_id($pdo, $projectKey, $sourceName, $functionName);
    if ($functionId === null) {
        throw new RuntimeException('先に function detail で canonical metadata を保存してください。');
    }

    return $functionId;
}

function app_pdo_ensure_db_access_class_id(
    PDO $pdo,
    string $projectKey,
    string $sourceName,
    string $lastDetectedDbaccessFile,
    string $lastDetectedDataFile,
): int {
    $classId = app_pdo_find_db_access_class_id($pdo, $projectKey, $sourceName);
    if ($classId !== null) {
        return $classId;
    }

    $projectId = app_pdo_resolve_project_id($pdo, $projectKey);
    $insertStatement = $pdo->prepare(
        'INSERT INTO project_db_access_classes (
            project_id,
            source_name,
            store_base_path,
            is_autoload,
            notes,
            source_of_truth,
            last_detected_dbaccess_file,
            last_detected_data_file
        ) VALUES (
            :project_id,
            :source_name,
            :store_base_path,
            :is_autoload,
            :notes,
            :source_of_truth,
            :last_detected_dbaccess_file,
            :last_detected_data_file
        )'
    );
    $insertStatement->execute([
        ':project_id' => $projectId,
        ':source_name' => $sourceName,
        ':store_base_path' => '',
        ':is_autoload' => 0,
        ':notes' => '',
        ':source_of_truth' => 'preview-bootstrap',
        ':last_detected_dbaccess_file' => $lastDetectedDbaccessFile,
        ':last_detected_data_file' => $lastDetectedDataFile,
    ]);

    return (int) $pdo->lastInsertId();
}
