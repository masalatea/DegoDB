<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/runtime_storage_paths.php';

function app_compare_output_pdo_canonicalize_base_path_value(string $value): string
{
    $normalized = trim(str_replace('\\', '/', $value));
    if ($normalized === '' || app_runtime_storage_path_is_absolute($normalized)) {
        return $normalized;
    }

    return app_runtime_storage_canonical_repo_relative_path($normalized);
}

function app_pdo_fetch_project_compare_output_catalog(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $statement = $pdo->prepare(
            'SELECT
                co.compare_output_key,
                co.name,
                co.storage_base_path,
                co.output_file_path,
                co.output_file_type,
                co.compare_path,
                co.compare_tool_file_path,
                co.compare_output_list_order,
                co.notes,
                co.source_of_truth,
                DATE_FORMAT(co.updated_at, "%Y-%m-%d %H:%i:%s") AS updated_at
            FROM project_compare_outputs AS co
            INNER JOIN projects AS p
                ON p.id = co.project_id
            WHERE p.project_key = :project_key
            ORDER BY co.compare_output_list_order, co.compare_output_key'
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

            $items[] = app_pdo_project_compare_output_item_from_row($row);
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

function app_pdo_fetch_project_compare_output_item(array $app, string $projectKey, string $compareOutputKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $statement = $pdo->prepare(
            'SELECT
                co.compare_output_key,
                co.name,
                co.storage_base_path,
                co.output_file_path,
                co.output_file_type,
                co.compare_path,
                co.compare_tool_file_path,
                co.compare_output_list_order,
                co.notes,
                co.source_of_truth,
                DATE_FORMAT(co.updated_at, "%Y-%m-%d %H:%i:%s") AS updated_at
            FROM project_compare_outputs AS co
            INNER JOIN projects AS p
                ON p.id = co.project_id
            WHERE p.project_key = :project_key
              AND co.compare_output_key = :compare_output_key
            LIMIT 1'
        );
        $statement->execute([
            ':project_key' => $projectKey,
            ':compare_output_key' => $compareOutputKey,
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
            'item' => app_pdo_project_compare_output_item_from_row($row),
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

function app_pdo_create_project_compare_output(array $app, array $input): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_compare_output_pdo_resolve_project_id($pdo, $input['project_key']);

        $statement = $pdo->prepare(
            'INSERT INTO project_compare_outputs (
                project_id,
                compare_output_key,
                name,
                storage_base_path,
                output_file_path,
                output_file_type,
                compare_path,
                compare_tool_file_path,
                compare_output_list_order,
                notes,
                source_of_truth
            ) VALUES (
                :project_id,
                :compare_output_key,
                :name,
                :storage_base_path,
                :output_file_path,
                :output_file_type,
                :compare_path,
                :compare_tool_file_path,
                :compare_output_list_order,
                :notes,
                :source_of_truth
            )'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':compare_output_key' => $input['compare_output_key'],
            ':name' => $input['name'],
            ':storage_base_path' => app_compare_output_pdo_canonicalize_base_path_value(
                (string) $input['storage_base_path'],
            ),
            ':output_file_path' => $input['output_file_path'],
            ':output_file_type' => $input['output_file_type'],
            ':compare_path' => $input['compare_path'],
            ':compare_tool_file_path' => $input['compare_tool_file_path'],
            ':compare_output_list_order' => (int) $input['compare_output_list_order'],
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

function app_pdo_update_project_compare_output(array $app, array $input): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_compare_output_pdo_resolve_project_id($pdo, $input['project_key']);
        app_compare_output_pdo_resolve_compare_output_id($pdo, $projectId, $input['compare_output_key']);

        $statement = $pdo->prepare(
            'UPDATE project_compare_outputs
            SET
                name = :name,
                storage_base_path = :storage_base_path,
                output_file_path = :output_file_path,
                output_file_type = :output_file_type,
                compare_path = :compare_path,
                compare_tool_file_path = :compare_tool_file_path,
                compare_output_list_order = :compare_output_list_order,
                notes = :notes,
                source_of_truth = :source_of_truth,
                updated_at = CURRENT_TIMESTAMP
            WHERE project_id = :project_id
              AND compare_output_key = :compare_output_key'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':compare_output_key' => $input['compare_output_key'],
            ':name' => $input['name'],
            ':storage_base_path' => app_compare_output_pdo_canonicalize_base_path_value(
                (string) $input['storage_base_path'],
            ),
            ':output_file_path' => $input['output_file_path'],
            ':output_file_type' => $input['output_file_type'],
            ':compare_path' => $input['compare_path'],
            ':compare_tool_file_path' => $input['compare_tool_file_path'],
            ':compare_output_list_order' => (int) $input['compare_output_list_order'],
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

function app_pdo_delete_project_compare_output(array $app, string $projectKey, string $compareOutputKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_compare_output_pdo_resolve_project_id($pdo, $projectKey);
        app_compare_output_pdo_resolve_compare_output_id($pdo, $projectId, $compareOutputKey);

        $statement = $pdo->prepare(
            'DELETE FROM project_compare_outputs
            WHERE project_id = :project_id
              AND compare_output_key = :compare_output_key'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':compare_output_key' => $compareOutputKey,
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

function app_pdo_fetch_project_compare_output_additional_path_catalog(
    array $app,
    string $projectKey,
    string $compareOutputKey,
): array {
    try {
        $pdo = app_create_config_pdo($app);
        $statement = $pdo->prepare(
            'SELECT
                ap.additional_path_key,
                ap.path_a_base_path,
                ap.path_a,
                ap.path_b_base_path,
                ap.path_b,
                ap.is_same_filename_only,
                ap.additional_path_list_order,
                ap.notes,
                ap.source_of_truth,
                DATE_FORMAT(ap.updated_at, "%Y-%m-%d %H:%i:%s") AS updated_at
            FROM project_compare_output_additional_paths AS ap
            INNER JOIN project_compare_outputs AS co
                ON co.id = ap.compare_output_id
            INNER JOIN projects AS p
                ON p.id = co.project_id
            WHERE p.project_key = :project_key
              AND co.compare_output_key = :compare_output_key
            ORDER BY ap.additional_path_list_order, ap.additional_path_key'
        );
        $statement->execute([
            ':project_key' => $projectKey,
            ':compare_output_key' => $compareOutputKey,
        ]);

        $rows = $statement->fetchAll();
        $items = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = app_pdo_project_compare_output_additional_path_item_from_row($row);
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

function app_pdo_fetch_project_compare_output_additional_path_item(
    array $app,
    string $projectKey,
    string $compareOutputKey,
    string $additionalPathKey,
): array {
    try {
        $pdo = app_create_config_pdo($app);
        $statement = $pdo->prepare(
            'SELECT
                ap.additional_path_key,
                ap.path_a_base_path,
                ap.path_a,
                ap.path_b_base_path,
                ap.path_b,
                ap.is_same_filename_only,
                ap.additional_path_list_order,
                ap.notes,
                ap.source_of_truth,
                DATE_FORMAT(ap.updated_at, "%Y-%m-%d %H:%i:%s") AS updated_at
            FROM project_compare_output_additional_paths AS ap
            INNER JOIN project_compare_outputs AS co
                ON co.id = ap.compare_output_id
            INNER JOIN projects AS p
                ON p.id = co.project_id
            WHERE p.project_key = :project_key
              AND co.compare_output_key = :compare_output_key
              AND ap.additional_path_key = :additional_path_key
            LIMIT 1'
        );
        $statement->execute([
            ':project_key' => $projectKey,
            ':compare_output_key' => $compareOutputKey,
            ':additional_path_key' => $additionalPathKey,
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
            'item' => app_pdo_project_compare_output_additional_path_item_from_row($row),
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

function app_pdo_create_project_compare_output_additional_path(array $app, array $input): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_compare_output_pdo_resolve_project_id($pdo, $input['project_key']);
        $compareOutputId = app_compare_output_pdo_resolve_compare_output_id(
            $pdo,
            $projectId,
            $input['compare_output_key'],
        );

        $statement = $pdo->prepare(
            'INSERT INTO project_compare_output_additional_paths (
                compare_output_id,
                additional_path_key,
                path_a_base_path,
                path_a,
                path_b_base_path,
                path_b,
                is_same_filename_only,
                additional_path_list_order,
                notes,
                source_of_truth
            ) VALUES (
                :compare_output_id,
                :additional_path_key,
                :path_a_base_path,
                :path_a,
                :path_b_base_path,
                :path_b,
                :is_same_filename_only,
                :additional_path_list_order,
                :notes,
                :source_of_truth
            )'
        );
        $statement->execute([
            ':compare_output_id' => $compareOutputId,
            ':additional_path_key' => $input['additional_path_key'],
            ':path_a_base_path' => app_compare_output_pdo_canonicalize_base_path_value(
                (string) $input['path_a_base_path'],
            ),
            ':path_a' => $input['path_a'],
            ':path_b_base_path' => app_compare_output_pdo_canonicalize_base_path_value(
                (string) $input['path_b_base_path'],
            ),
            ':path_b' => $input['path_b'],
            ':is_same_filename_only' => (int) $input['is_same_filename_only'],
            ':additional_path_list_order' => (int) $input['additional_path_list_order'],
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

function app_pdo_update_project_compare_output_additional_path(array $app, array $input): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_compare_output_pdo_resolve_project_id($pdo, $input['project_key']);
        $compareOutputId = app_compare_output_pdo_resolve_compare_output_id(
            $pdo,
            $projectId,
            $input['compare_output_key'],
        );
        app_compare_output_pdo_resolve_additional_path_id(
            $pdo,
            $compareOutputId,
            $input['additional_path_key'],
        );

        $statement = $pdo->prepare(
            'UPDATE project_compare_output_additional_paths
            SET
                path_a_base_path = :path_a_base_path,
                path_a = :path_a,
                path_b_base_path = :path_b_base_path,
                path_b = :path_b,
                is_same_filename_only = :is_same_filename_only,
                additional_path_list_order = :additional_path_list_order,
                notes = :notes,
                source_of_truth = :source_of_truth,
                updated_at = CURRENT_TIMESTAMP
            WHERE compare_output_id = :compare_output_id
              AND additional_path_key = :additional_path_key'
        );
        $statement->execute([
            ':compare_output_id' => $compareOutputId,
            ':additional_path_key' => $input['additional_path_key'],
            ':path_a_base_path' => app_compare_output_pdo_canonicalize_base_path_value(
                (string) $input['path_a_base_path'],
            ),
            ':path_a' => $input['path_a'],
            ':path_b_base_path' => app_compare_output_pdo_canonicalize_base_path_value(
                (string) $input['path_b_base_path'],
            ),
            ':path_b' => $input['path_b'],
            ':is_same_filename_only' => (int) $input['is_same_filename_only'],
            ':additional_path_list_order' => (int) $input['additional_path_list_order'],
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

function app_pdo_delete_project_compare_output_additional_path(
    array $app,
    string $projectKey,
    string $compareOutputKey,
    string $additionalPathKey,
): array {
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_compare_output_pdo_resolve_project_id($pdo, $projectKey);
        $compareOutputId = app_compare_output_pdo_resolve_compare_output_id($pdo, $projectId, $compareOutputKey);
        app_compare_output_pdo_resolve_additional_path_id($pdo, $compareOutputId, $additionalPathKey);

        $statement = $pdo->prepare(
            'DELETE FROM project_compare_output_additional_paths
            WHERE compare_output_id = :compare_output_id
              AND additional_path_key = :additional_path_key'
        );
        $statement->execute([
            ':compare_output_id' => $compareOutputId,
            ':additional_path_key' => $additionalPathKey,
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

function app_pdo_project_compare_output_item_from_row(array $row): array
{
    return [
        'compare_output_key' => (string) ($row['compare_output_key'] ?? ''),
        'name' => (string) ($row['name'] ?? ''),
        'storage_base_path' => app_compare_output_pdo_canonicalize_base_path_value(
            (string) ($row['storage_base_path'] ?? ''),
        ),
        'output_file_path' => (string) ($row['output_file_path'] ?? ''),
        'output_file_type' => (string) ($row['output_file_type'] ?? ''),
        'compare_path' => (string) ($row['compare_path'] ?? ''),
        'compare_tool_file_path' => (string) ($row['compare_tool_file_path'] ?? ''),
        'compare_output_list_order' => (string) ((int) ($row['compare_output_list_order'] ?? 0)),
        'notes' => (string) ($row['notes'] ?? ''),
        'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
        'updated_at' => (string) ($row['updated_at'] ?? ''),
    ];
}

function app_pdo_project_compare_output_additional_path_item_from_row(array $row): array
{
    return [
        'additional_path_key' => (string) ($row['additional_path_key'] ?? ''),
        'path_a_base_path' => app_compare_output_pdo_canonicalize_base_path_value(
            (string) ($row['path_a_base_path'] ?? ''),
        ),
        'path_a' => (string) ($row['path_a'] ?? ''),
        'path_b_base_path' => app_compare_output_pdo_canonicalize_base_path_value(
            (string) ($row['path_b_base_path'] ?? ''),
        ),
        'path_b' => (string) ($row['path_b'] ?? ''),
        'is_same_filename_only' => ((int) ($row['is_same_filename_only'] ?? 0)) === 1 ? '1' : '0',
        'additional_path_list_order' => (string) ((int) ($row['additional_path_list_order'] ?? 0)),
        'notes' => (string) ($row['notes'] ?? ''),
        'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
        'updated_at' => (string) ($row['updated_at'] ?? ''),
    ];
}

function app_compare_output_pdo_resolve_project_id(PDO $pdo, string $projectKey): int
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

function app_compare_output_pdo_resolve_compare_output_id(PDO $pdo, int $projectId, string $compareOutputKey): int
{
    $statement = $pdo->prepare(
        'SELECT id
        FROM project_compare_outputs
        WHERE project_id = :project_id
          AND compare_output_key = :compare_output_key
        LIMIT 1'
    );
    $statement->execute([
        ':project_id' => $projectId,
        ':compare_output_key' => $compareOutputKey,
    ]);

    $compareOutputId = $statement->fetchColumn();
    if (!is_numeric($compareOutputId)) {
        throw new RuntimeException('compare output が見つかりません。');
    }

    return (int) $compareOutputId;
}

function app_compare_output_pdo_resolve_additional_path_id(
    PDO $pdo,
    int $compareOutputId,
    string $additionalPathKey,
): int {
    $statement = $pdo->prepare(
        'SELECT id
        FROM project_compare_output_additional_paths
        WHERE compare_output_id = :compare_output_id
          AND additional_path_key = :additional_path_key
        LIMIT 1'
    );
    $statement->execute([
        ':compare_output_id' => $compareOutputId,
        ':additional_path_key' => $additionalPathKey,
    ]);

    $additionalPathId = $statement->fetchColumn();
    if (!is_numeric($additionalPathId)) {
        throw new RuntimeException('compare output additional path が見つかりません。');
    }

    return (int) $additionalPathId;
}
