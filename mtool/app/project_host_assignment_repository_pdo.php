<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/project_page_security_repository_pdo.php';

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         id:int,
 *         apache_setting_name:string,
 *         server_local_name:string,
 *         virtual_host_name:string,
 *         template_name:string,
 *         notes:string,
 *         source_of_truth:string
 *     }>,
 *     error:string
 * }
 */
function app_pdo_fetch_project_host_assignments(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $statement = $pdo->prepare(
            'SELECT
                pha.id,
                pha.apache_setting_name,
                pha.server_local_name,
                pha.virtual_host_name,
                pha.template_name,
                pha.notes,
                pha.source_of_truth
            FROM project_host_assignments AS pha
            INNER JOIN projects AS p
                ON p.id = pha.project_id
            WHERE p.project_key = :project_key
            ORDER BY
                pha.apache_setting_name,
                pha.server_local_name,
                pha.virtual_host_name,
                pha.template_name,
                pha.id'
        );
        $statement->execute([
            ':project_key' => $projectKey,
        ]);

        $items = [];
        foreach ($statement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = [
                'id' => (int) ($row['id'] ?? 0),
                'apache_setting_name' => trim((string) ($row['apache_setting_name'] ?? '')),
                'server_local_name' => trim((string) ($row['server_local_name'] ?? '')),
                'virtual_host_name' => trim((string) ($row['virtual_host_name'] ?? '')),
                'template_name' => trim((string) ($row['template_name'] ?? '')),
                'notes' => (string) ($row['notes'] ?? ''),
                'source_of_truth' => trim((string) ($row['source_of_truth'] ?? 'manual')),
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
 * @return array{
 *     ok:bool,
 *     item:array{
 *         id:int,
 *         apache_setting_name:string,
 *         server_local_name:string,
 *         virtual_host_name:string,
 *         template_name:string,
 *         notes:string,
 *         source_of_truth:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_project_host_assignment(array $app, string $projectKey, int $assignmentId): array
{
    if ($assignmentId <= 0) {
        return [
            'ok' => true,
            'item' => null,
            'error' => '',
        ];
    }

    try {
        $pdo = app_create_config_pdo($app);
        $statement = $pdo->prepare(
            'SELECT
                pha.id,
                pha.apache_setting_name,
                pha.server_local_name,
                pha.virtual_host_name,
                pha.template_name,
                pha.notes,
                pha.source_of_truth
            FROM project_host_assignments AS pha
            INNER JOIN projects AS p
                ON p.id = pha.project_id
            WHERE
                p.project_key = :project_key
                AND pha.id = :assignment_id
            LIMIT 1'
        );
        $statement->execute([
            ':project_key' => $projectKey,
            ':assignment_id' => $assignmentId,
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
                'id' => (int) ($row['id'] ?? 0),
                'apache_setting_name' => trim((string) ($row['apache_setting_name'] ?? '')),
                'server_local_name' => trim((string) ($row['server_local_name'] ?? '')),
                'virtual_host_name' => trim((string) ($row['virtual_host_name'] ?? '')),
                'template_name' => trim((string) ($row['template_name'] ?? '')),
                'notes' => (string) ($row['notes'] ?? ''),
                'source_of_truth' => trim((string) ($row['source_of_truth'] ?? 'manual')),
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
 *     apache_setting_name:string,
 *     server_local_name:string,
 *     virtual_host_name:string,
 *     template_name:string,
 *     notes:string,
 *     source_of_truth?:string
 * } $input
 * @return array{
 *     ok:bool,
 *     assignment_id:int,
 *     error:string
 * }
 */
function app_pdo_create_project_host_assignment(array $app, string $projectKey, array $input): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_pdo_fetch_project_id_for_security_domain($pdo, $projectKey);
        if ($projectId === null) {
            return [
                'ok' => false,
                'assignment_id' => 0,
                'error' => '保存対象の project が見つかりません。',
            ];
        }

        $statement = $pdo->prepare(
            'INSERT INTO project_host_assignments (
                project_id,
                apache_setting_name,
                server_local_name,
                virtual_host_name,
                template_name,
                notes,
                source_of_truth
            ) VALUES (
                :project_id,
                :apache_setting_name,
                :server_local_name,
                :virtual_host_name,
                :template_name,
                :notes,
                :source_of_truth
            )'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':apache_setting_name' => $input['apache_setting_name'],
            ':server_local_name' => $input['server_local_name'],
            ':virtual_host_name' => $input['virtual_host_name'],
            ':template_name' => $input['template_name'],
            ':notes' => $input['notes'],
            ':source_of_truth' => $input['source_of_truth'] ?? 'manual',
        ]);

        return [
            'ok' => true,
            'assignment_id' => (int) $pdo->lastInsertId(),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'assignment_id' => 0,
            'error' => app_pdo_map_project_host_assignment_error($throwable),
        ];
    }
}

/**
 * @param array{
 *     apache_setting_name:string,
 *     server_local_name:string,
 *     virtual_host_name:string,
 *     template_name:string,
 *     notes:string,
 *     source_of_truth?:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_update_project_host_assignment(
    array $app,
    string $projectKey,
    int $assignmentId,
    array $input
): array {
    if ($assignmentId <= 0) {
        return [
            'ok' => false,
            'error' => '更新対象の host assignment が不正です。',
        ];
    }

    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_pdo_fetch_project_id_for_security_domain($pdo, $projectKey);
        if ($projectId === null) {
            return [
                'ok' => false,
                'error' => '更新対象の project が見つかりません。',
            ];
        }

        $statement = $pdo->prepare(
            'UPDATE project_host_assignments
            SET
                apache_setting_name = :apache_setting_name,
                server_local_name = :server_local_name,
                virtual_host_name = :virtual_host_name,
                template_name = :template_name,
                notes = :notes,
                source_of_truth = :source_of_truth
            WHERE
                id = :assignment_id
                AND project_id = :project_id'
        );
        $statement->execute([
            ':apache_setting_name' => $input['apache_setting_name'],
            ':server_local_name' => $input['server_local_name'],
            ':virtual_host_name' => $input['virtual_host_name'],
            ':template_name' => $input['template_name'],
            ':notes' => $input['notes'],
            ':source_of_truth' => $input['source_of_truth'] ?? 'manual',
            ':assignment_id' => $assignmentId,
            ':project_id' => $projectId,
        ]);

        if ($statement->rowCount() === 0 && !app_pdo_project_host_assignment_exists($pdo, $projectId, $assignmentId)) {
            return [
                'ok' => false,
                'error' => '更新対象の host assignment が見つかりません。',
            ];
        }

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'error' => app_pdo_map_project_host_assignment_error($throwable),
        ];
    }
}

/**
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_delete_project_host_assignment(array $app, string $projectKey, int $assignmentId): array
{
    if ($assignmentId <= 0) {
        return [
            'ok' => false,
            'error' => '削除対象の host assignment が不正です。',
        ];
    }

    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_pdo_fetch_project_id_for_security_domain($pdo, $projectKey);
        if ($projectId === null) {
            return [
                'ok' => false,
                'error' => '削除対象の project が見つかりません。',
            ];
        }

        $statement = $pdo->prepare(
            'DELETE FROM project_host_assignments
            WHERE
                id = :assignment_id
                AND project_id = :project_id'
        );
        $statement->execute([
            ':assignment_id' => $assignmentId,
            ':project_id' => $projectId,
        ]);

        if ($statement->rowCount() === 0) {
            return [
                'ok' => false,
                'error' => '削除対象の host assignment が見つかりません。',
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

function app_pdo_project_host_assignment_exists(PDO $pdo, int $projectId, int $assignmentId): bool
{
    $statement = $pdo->prepare(
        'SELECT id
        FROM project_host_assignments
        WHERE
            id = :assignment_id
            AND project_id = :project_id
        LIMIT 1'
    );
    $statement->execute([
        ':assignment_id' => $assignmentId,
        ':project_id' => $projectId,
    ]);

    return is_array($statement->fetch());
}

function app_pdo_map_project_host_assignment_error(Throwable $throwable): string
{
    $message = $throwable->getMessage();
    if (str_contains($message, 'uq_project_host_assignments_identity')) {
        return '同じ host assignment は既に存在します。';
    }

    return $message;
}
