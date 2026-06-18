<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/runtime_storage_paths.php';

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         source_output_key:string,
 *         name:string,
 *         program_language:string,
 *         class_type:string,
 *         release_target_type:string,
 *         source_template_dir:string,
 *         source_output_dir:string,
 *         source_temp_output_dir:string,
 *         proxy_base_url:string,
 *         autoload_filename_suffix:string,
 *         source_text_char_code:string,
 *         runtime_source_relative_path:string,
 *         artifact_strategy:string,
 *         target_binding_type:string,
 *         spec_visibility:string,
 *         output_archive_format:string,
 *         source_output_list_order:string,
 *         notes:string,
 *         source_of_truth:string,
 *         updated_at:string
 *     }>,
 *     error:string
 * }
 */
function app_pdo_fetch_project_source_output_catalog(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $dialect = app_sql_dialect_from_db_config(app_database_config($app, 'config_db'));
        $updatedAtSelect = app_sql_datetime_select_expr($dialect, 'so.updated_at', 'updated_at');
        $statement = $pdo->prepare(
            'SELECT
                so.source_output_key,
                so.name,
                so.program_language,
                so.class_type,
                so.release_target_type,
                so.source_template_dir,
                so.source_output_dir,
                so.source_temp_output_dir,
                so.proxy_base_url,
                so.autoload_filename_suffix,
                so.source_text_char_code,
                so.runtime_source_relative_path,
                so.artifact_strategy,
                so.target_binding_type,
                so.spec_visibility,
                so.output_archive_format,
                so.source_output_list_order,
                so.notes,
                so.source_of_truth,
                ' . $updatedAtSelect . '
            FROM project_source_outputs AS so
            INNER JOIN projects AS p
                ON p.id = so.project_id
            WHERE p.project_key = :project_key
            ORDER BY so.source_output_list_order, so.source_output_key'
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

            $items[] = app_pdo_project_source_output_item_from_row($row);
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
 * @return array{
 *     ok:bool,
 *     item:array{
 *         source_output_key:string,
 *         name:string,
 *         program_language:string,
 *         class_type:string,
 *         release_target_type:string,
 *         source_template_dir:string,
 *         source_output_dir:string,
 *         source_temp_output_dir:string,
 *         proxy_base_url:string,
 *         autoload_filename_suffix:string,
 *         source_text_char_code:string,
 *         runtime_source_relative_path:string,
 *         artifact_strategy:string,
 *         target_binding_type:string,
 *         spec_visibility:string,
 *         output_archive_format:string,
 *         source_output_list_order:string,
 *         notes:string,
 *         source_of_truth:string,
 *         updated_at:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_project_source_output_item(array $app, string $projectKey, string $sourceOutputKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $dialect = app_sql_dialect_from_db_config(app_database_config($app, 'config_db'));
        $updatedAtSelect = app_sql_datetime_select_expr($dialect, 'so.updated_at', 'updated_at');
        $statement = $pdo->prepare(
            'SELECT
                so.source_output_key,
                so.name,
                so.program_language,
                so.class_type,
                so.release_target_type,
                so.source_template_dir,
                so.source_output_dir,
                so.source_temp_output_dir,
                so.proxy_base_url,
                so.autoload_filename_suffix,
                so.source_text_char_code,
                so.runtime_source_relative_path,
                so.artifact_strategy,
                so.target_binding_type,
                so.spec_visibility,
                so.output_archive_format,
                so.source_output_list_order,
                so.notes,
                so.source_of_truth,
                ' . $updatedAtSelect . '
            FROM project_source_outputs AS so
            INNER JOIN projects AS p
                ON p.id = so.project_id
            WHERE p.project_key = :project_key
              AND so.source_output_key = :source_output_key
            LIMIT 1'
        );
        $statement->execute([
            ':project_key' => $projectKey,
            ':source_output_key' => $sourceOutputKey,
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
            'item' => app_pdo_project_source_output_item_from_row($row),
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
 * @return array{
 *     ok:bool,
 *     item:array{
 *         source_output_key:string,
 *         name:string,
 *         program_language:string,
 *         class_type:string,
 *         release_target_type:string,
 *         source_template_dir:string,
 *         source_output_dir:string,
 *         source_temp_output_dir:string,
 *         proxy_base_url:string,
 *         autoload_filename_suffix:string,
 *         source_text_char_code:string,
 *         runtime_source_relative_path:string,
 *         artifact_strategy:string,
 *         target_binding_type:string,
 *         spec_visibility:string,
 *         output_archive_format:string,
 *         source_output_list_order:string,
 *         notes:string,
 *         source_of_truth:string,
 *         updated_at:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_project_source_output_default_item(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $dialect = app_sql_dialect_from_db_config(app_database_config($app, 'config_db'));
        $updatedAtSelect = app_sql_datetime_select_expr($dialect, 'so.updated_at', 'updated_at');
        $statement = $pdo->prepare(
            'SELECT
                so.source_output_key,
                so.name,
                so.program_language,
                so.class_type,
                so.release_target_type,
                so.source_template_dir,
                so.source_output_dir,
                so.source_temp_output_dir,
                so.proxy_base_url,
                so.autoload_filename_suffix,
                so.source_text_char_code,
                so.runtime_source_relative_path,
                so.artifact_strategy,
                so.target_binding_type,
                so.spec_visibility,
                so.output_archive_format,
                so.source_output_list_order,
                so.notes,
                so.source_of_truth,
                ' . $updatedAtSelect . '
            FROM project_source_outputs AS so
            INNER JOIN projects AS p
                ON p.id = so.project_id
            WHERE p.project_key = :project_key
            ORDER BY so.source_output_list_order, so.source_output_key
            LIMIT 1'
        );
        $statement->execute([
            ':project_key' => $projectKey,
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
            'item' => app_pdo_project_source_output_item_from_row($row),
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
 *     project_key:string,
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     target_binding_type:string,
 *     spec_visibility:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_create_project_source_output(array $app, array $input): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_source_output_pdo_resolve_project_id($pdo, $input['project_key']);

        $statement = $pdo->prepare(
            'INSERT INTO project_source_outputs (
                project_id,
                source_output_key,
                name,
                program_language,
                class_type,
                release_target_type,
                source_template_dir,
                source_output_dir,
                source_temp_output_dir,
                proxy_base_url,
                autoload_filename_suffix,
                source_text_char_code,
                runtime_source_relative_path,
                artifact_strategy,
                target_binding_type,
                spec_visibility,
                output_archive_format,
                source_output_list_order,
                notes,
                source_of_truth
            ) VALUES (
                :project_id,
                :source_output_key,
                :name,
                :program_language,
                :class_type,
                :release_target_type,
                :source_template_dir,
                :source_output_dir,
                :source_temp_output_dir,
                :proxy_base_url,
                :autoload_filename_suffix,
                :source_text_char_code,
                :runtime_source_relative_path,
                :artifact_strategy,
                :target_binding_type,
                :spec_visibility,
                :output_archive_format,
                :source_output_list_order,
                :notes,
                :source_of_truth
            )'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':source_output_key' => $input['source_output_key'],
            ':name' => $input['name'],
            ':program_language' => $input['program_language'],
            ':class_type' => $input['class_type'],
            ':release_target_type' => $input['release_target_type'],
            ':source_template_dir' => $input['source_template_dir'],
            ':source_output_dir' => $input['source_output_dir'],
            ':source_temp_output_dir' => $input['source_temp_output_dir'],
            ':proxy_base_url' => $input['proxy_base_url'],
            ':autoload_filename_suffix' => $input['autoload_filename_suffix'],
            ':source_text_char_code' => $input['source_text_char_code'],
            ':runtime_source_relative_path' => app_runtime_storage_canonical_generated_relative_path(
                (string) $input['runtime_source_relative_path'],
            ),
            ':artifact_strategy' => $input['artifact_strategy'],
            ':target_binding_type' => $input['target_binding_type'],
            ':spec_visibility' => $input['spec_visibility'],
            ':output_archive_format' => $input['output_archive_format'],
            ':source_output_list_order' => (int) $input['source_output_list_order'],
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

/**
 * @param array{
 *     project_key:string,
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     target_binding_type:string,
 *     spec_visibility:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_update_project_source_output(array $app, array $input): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_source_output_pdo_resolve_project_id($pdo, $input['project_key']);
        app_source_output_pdo_resolve_source_output_id($pdo, $projectId, $input['source_output_key']);

        $statement = $pdo->prepare(
            'UPDATE project_source_outputs
            SET
                name = :name,
                program_language = :program_language,
                class_type = :class_type,
                release_target_type = :release_target_type,
                source_template_dir = :source_template_dir,
                source_output_dir = :source_output_dir,
                source_temp_output_dir = :source_temp_output_dir,
                proxy_base_url = :proxy_base_url,
                autoload_filename_suffix = :autoload_filename_suffix,
                source_text_char_code = :source_text_char_code,
                runtime_source_relative_path = :runtime_source_relative_path,
                artifact_strategy = :artifact_strategy,
                target_binding_type = :target_binding_type,
                spec_visibility = :spec_visibility,
                output_archive_format = :output_archive_format,
                source_output_list_order = :source_output_list_order,
                notes = :notes,
                source_of_truth = :source_of_truth,
                updated_at = CURRENT_TIMESTAMP
            WHERE project_id = :project_id
              AND source_output_key = :source_output_key'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':source_output_key' => $input['source_output_key'],
            ':name' => $input['name'],
            ':program_language' => $input['program_language'],
            ':class_type' => $input['class_type'],
            ':release_target_type' => $input['release_target_type'],
            ':source_template_dir' => $input['source_template_dir'],
            ':source_output_dir' => $input['source_output_dir'],
            ':source_temp_output_dir' => $input['source_temp_output_dir'],
            ':proxy_base_url' => $input['proxy_base_url'],
            ':autoload_filename_suffix' => $input['autoload_filename_suffix'],
            ':source_text_char_code' => $input['source_text_char_code'],
            ':runtime_source_relative_path' => app_runtime_storage_canonical_generated_relative_path(
                (string) $input['runtime_source_relative_path'],
            ),
            ':artifact_strategy' => $input['artifact_strategy'],
            ':target_binding_type' => $input['target_binding_type'],
            ':spec_visibility' => $input['spec_visibility'],
            ':output_archive_format' => $input['output_archive_format'],
            ':source_output_list_order' => (int) $input['source_output_list_order'],
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

/**
 * @param array{
 *     project_key:string,
 *     source_output_key:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_delete_project_source_output(array $app, array $input): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_source_output_pdo_resolve_project_id($pdo, $input['project_key']);
        app_source_output_pdo_resolve_source_output_id($pdo, $projectId, $input['source_output_key']);

        $statement = $pdo->prepare(
            'DELETE FROM project_source_outputs
            WHERE project_id = :project_id
              AND source_output_key = :source_output_key'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':source_output_key' => $input['source_output_key'],
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
 *     project_key:string,
 *     orders:list<array{
 *         source_output_key:string,
 *         source_output_list_order:string
 *     }>
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_reorder_project_source_outputs(array $app, array $input): array
{
    $pdo = null;

    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_source_output_pdo_resolve_project_id($pdo, $input['project_key']);

        $expectedKeysStatement = $pdo->prepare(
            'SELECT source_output_key
            FROM project_source_outputs
            WHERE project_id = :project_id'
        );
        $expectedKeysStatement->execute([
            ':project_id' => $projectId,
        ]);
        $expectedKeys = array_values(
            array_filter(
                array_map(
                    static fn (mixed $value): string => is_string($value) ? $value : '',
                    $expectedKeysStatement->fetchAll(PDO::FETCH_COLUMN, 0),
                ),
                static fn (string $value): bool => $value !== '',
            ),
        );

        $submittedKeys = [];
        foreach ($input['orders'] as $order) {
            $submittedKey = trim((string) ($order['source_output_key'] ?? ''));
            if ($submittedKey !== '') {
                $submittedKeys[] = $submittedKey;
            }
        }

        $expectedKeysSorted = $expectedKeys;
        $submittedKeysSorted = $submittedKeys;
        sort($expectedKeysSorted, SORT_STRING);
        sort($submittedKeysSorted, SORT_STRING);
        if ($expectedKeysSorted !== $submittedKeysSorted) {
            throw new RuntimeException('並び替え対象の source output 一覧が現在の canonical catalog と一致しません。再読み込みしてやり直してください。');
        }

        $pdo->beginTransaction();

        $updateStatement = $pdo->prepare(
            'UPDATE project_source_outputs
            SET
                source_output_list_order = :source_output_list_order,
                updated_at = CURRENT_TIMESTAMP
            WHERE project_id = :project_id
              AND source_output_key = :source_output_key'
        );

        foreach ($input['orders'] as $order) {
            $updateStatement->execute([
                ':project_id' => $projectId,
                ':source_output_key' => $order['source_output_key'],
                ':source_output_list_order' => (int) $order['source_output_list_order'],
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
 * @param array<mixed> $row
 * @return array{
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     target_binding_type:string,
 *     spec_visibility:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at:string
 * }
 */
function app_pdo_project_source_output_item_from_row(array $row): array
{
    return [
        'source_output_key' => (string) ($row['source_output_key'] ?? ''),
        'name' => (string) ($row['name'] ?? ''),
        'program_language' => (string) ($row['program_language'] ?? ''),
        'class_type' => (string) ($row['class_type'] ?? ''),
        'release_target_type' => (string) ($row['release_target_type'] ?? ''),
        'source_template_dir' => (string) ($row['source_template_dir'] ?? ''),
        'source_output_dir' => (string) ($row['source_output_dir'] ?? ''),
        'source_temp_output_dir' => (string) ($row['source_temp_output_dir'] ?? ''),
        'proxy_base_url' => (string) ($row['proxy_base_url'] ?? ''),
        'autoload_filename_suffix' => (string) ($row['autoload_filename_suffix'] ?? ''),
        'source_text_char_code' => (string) ($row['source_text_char_code'] ?? ''),
        'runtime_source_relative_path' => app_runtime_storage_canonical_generated_relative_path(
            (string) ($row['runtime_source_relative_path'] ?? ''),
        ),
        'artifact_strategy' => (string) ($row['artifact_strategy'] ?? ''),
        'target_binding_type' => (string) ($row['target_binding_type'] ?? ''),
        'spec_visibility' => app_source_output_effective_spec_visibility($row),
        'output_archive_format' => (string) ($row['output_archive_format'] ?? ''),
        'source_output_list_order' => (string) ((int) ($row['source_output_list_order'] ?? 0)),
        'notes' => (string) ($row['notes'] ?? ''),
        'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
        'updated_at' => (string) ($row['updated_at'] ?? ''),
    ];
}

function app_source_output_pdo_resolve_project_id(PDO $pdo, string $projectKey): int
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
    if (!is_numeric($projectId)) {
        throw new RuntimeException('project が見つかりません。');
    }

    return (int) $projectId;
}

function app_source_output_pdo_resolve_source_output_id(PDO $pdo, int $projectId, string $sourceOutputKey): int
{
    $statement = $pdo->prepare(
        'SELECT id
        FROM project_source_outputs
        WHERE project_id = :project_id
          AND source_output_key = :source_output_key
        LIMIT 1'
    );
    $statement->execute([
        ':project_id' => $projectId,
        ':source_output_key' => $sourceOutputKey,
    ]);

    $sourceOutputId = $statement->fetchColumn();
    if (!is_numeric($sourceOutputId)) {
        throw new RuntimeException('source output が見つかりません。');
    }

    return (int) $sourceOutputId;
}
