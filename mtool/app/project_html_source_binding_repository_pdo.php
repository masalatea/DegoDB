<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         legacy_project_source_output_pid:int,
 *         source_output_key:string,
 *         module_source_ref:string,
 *         refresh_policy:string,
 *         notes:string,
 *         source_of_truth:string,
 *         updated_at:string
 *     }>,
 *     error:string
 * }
 */
function app_pdo_fetch_project_html_source_bindings(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $statement = $pdo->prepare(
            'SELECT
                phsb.legacy_project_source_output_pid,
                phsb.source_output_key,
                phsb.module_source_ref,
                phsb.refresh_policy,
                phsb.notes,
                phsb.source_of_truth,
                DATE_FORMAT(phsb.updated_at, "%Y-%m-%d %H:%i:%s") AS updated_at
            FROM project_html_source_bindings AS phsb
            INNER JOIN projects AS p
                ON p.id = phsb.project_id
            WHERE p.project_key = :project_key
            ORDER BY phsb.legacy_project_source_output_pid'
        );
        $statement->execute([
            ':project_key' => $projectKey,
        ]);

        $items = [];
        foreach ($statement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = app_pdo_project_html_source_binding_item_from_row($row);
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
 *         legacy_project_source_output_pid:int,
 *         source_output_key:string,
 *         module_source_ref:string,
 *         refresh_policy:string,
 *         notes:string,
 *         source_of_truth:string,
 *         updated_at:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_project_html_source_binding(
    array $app,
    string $projectKey,
    int $legacyProjectSourceOutputPid,
): array {
    if ($legacyProjectSourceOutputPid <= 0) {
        return [
            'ok' => true,
            'item' => null,
            'error' => '',
        ];
    }

    try {
        $pdo = app_create_metadata_pdo($app);
        $statement = $pdo->prepare(
            'SELECT
                phsb.legacy_project_source_output_pid,
                phsb.source_output_key,
                phsb.module_source_ref,
                phsb.refresh_policy,
                phsb.notes,
                phsb.source_of_truth,
                DATE_FORMAT(phsb.updated_at, "%Y-%m-%d %H:%i:%s") AS updated_at
            FROM project_html_source_bindings AS phsb
            INNER JOIN projects AS p
                ON p.id = phsb.project_id
            WHERE
                p.project_key = :project_key
                AND phsb.legacy_project_source_output_pid = :legacy_project_source_output_pid
            LIMIT 1'
        );
        $statement->execute([
            ':project_key' => $projectKey,
            ':legacy_project_source_output_pid' => $legacyProjectSourceOutputPid,
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
            'item' => app_pdo_project_html_source_binding_item_from_row($row),
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
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     module_source_ref:string,
 *     refresh_policy:string,
 *     notes:string,
 *     source_of_truth?:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_upsert_project_html_source_binding(array $app, string $projectKey, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $projectId = app_project_html_source_binding_pdo_resolve_project_id($pdo, $projectKey);

        $statement = $pdo->prepare(
            'INSERT INTO project_html_source_bindings (
                project_id,
                legacy_project_source_output_pid,
                source_output_key,
                module_source_ref,
                refresh_policy,
                notes,
                source_of_truth
            ) VALUES (
                :project_id,
                :legacy_project_source_output_pid,
                :source_output_key,
                :module_source_ref,
                :refresh_policy,
                :notes,
                :source_of_truth
            )
            ON DUPLICATE KEY UPDATE
                source_output_key = VALUES(source_output_key),
                module_source_ref = VALUES(module_source_ref),
                refresh_policy = VALUES(refresh_policy),
                notes = VALUES(notes),
                source_of_truth = VALUES(source_of_truth),
                updated_at = CURRENT_TIMESTAMP'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':legacy_project_source_output_pid' => $input['legacy_project_source_output_pid'],
            ':source_output_key' => $input['source_output_key'],
            ':module_source_ref' => $input['module_source_ref'],
            ':refresh_policy' => $input['refresh_policy'],
            ':notes' => $input['notes'],
            ':source_of_truth' => $input['source_of_truth'] ?? 'manual',
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
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_delete_project_html_source_binding(
    array $app,
    string $projectKey,
    int $legacyProjectSourceOutputPid,
): array {
    if ($legacyProjectSourceOutputPid <= 0) {
        return [
            'ok' => false,
            'error' => '削除対象の legacy ProjectSourceOutputPID が不正です。',
        ];
    }

    try {
        $pdo = app_create_metadata_pdo($app);
        $projectId = app_project_html_source_binding_pdo_resolve_project_id($pdo, $projectKey);
        $statement = $pdo->prepare(
            'DELETE FROM project_html_source_bindings
            WHERE
                project_id = :project_id
                AND legacy_project_source_output_pid = :legacy_project_source_output_pid'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':legacy_project_source_output_pid' => $legacyProjectSourceOutputPid,
        ]);

        if ($statement->rowCount() === 0) {
            return [
                'ok' => false,
                'error' => '削除対象の HTML source binding が見つかりません。',
            ];
        }

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
 * @return array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     module_source_ref:string,
 *     refresh_policy:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at:string
 * }
 */
function app_pdo_project_html_source_binding_item_from_row(array $row): array
{
    return [
        'legacy_project_source_output_pid' => (int) ($row['legacy_project_source_output_pid'] ?? 0),
        'source_output_key' => trim((string) ($row['source_output_key'] ?? '')),
        'module_source_ref' => trim((string) ($row['module_source_ref'] ?? '')),
        'refresh_policy' => trim((string) ($row['refresh_policy'] ?? 'follow-source-output')),
        'notes' => (string) ($row['notes'] ?? ''),
        'source_of_truth' => trim((string) ($row['source_of_truth'] ?? 'manual')),
        'updated_at' => (string) ($row['updated_at'] ?? ''),
    ];
}

function app_project_html_source_binding_pdo_resolve_project_id(PDO $pdo, string $projectKey): int
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
