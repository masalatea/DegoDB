<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         id:int,
 *         server_name:string,
 *         script_name:string,
 *         security_types:list<string>,
 *         notes:string,
 *         source_of_truth:string
 *     }>,
 *     error:string
 * }
 */
function app_pdo_fetch_project_page_security_policies(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $statement = $pdo->prepare(
            'SELECT
                psp.id,
                psp.server_name,
                psp.script_name,
                psp.notes,
                psp.source_of_truth,
                GROUP_CONCAT(pspc.security_type ORDER BY pspc.security_type SEPARATOR ",") AS security_types
            FROM project_page_security_policies AS psp
            INNER JOIN projects AS p
                ON p.id = psp.project_id
            LEFT JOIN project_page_security_policy_capabilities AS pspc
                ON pspc.page_security_policy_id = psp.id
            WHERE p.project_key = :project_key
            GROUP BY
                psp.id,
                psp.server_name,
                psp.script_name,
                psp.notes,
                psp.source_of_truth
            ORDER BY
                psp.server_name,
                psp.script_name,
                psp.id'
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
                'server_name' => trim((string) ($row['server_name'] ?? '')),
                'script_name' => trim((string) ($row['script_name'] ?? '')),
                'security_types' => app_pdo_parse_project_page_security_types(
                    (string) ($row['security_types'] ?? ''),
                ),
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
 *         server_name:string,
 *         script_name:string,
 *         security_types:list<string>,
 *         notes:string,
 *         source_of_truth:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_project_page_security_policy(array $app, string $projectKey, int $policyId): array
{
    if ($policyId <= 0) {
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
                psp.id,
                psp.server_name,
                psp.script_name,
                psp.notes,
                psp.source_of_truth,
                GROUP_CONCAT(pspc.security_type ORDER BY pspc.security_type SEPARATOR ",") AS security_types
            FROM project_page_security_policies AS psp
            INNER JOIN projects AS p
                ON p.id = psp.project_id
            LEFT JOIN project_page_security_policy_capabilities AS pspc
                ON pspc.page_security_policy_id = psp.id
            WHERE
                p.project_key = :project_key
                AND psp.id = :policy_id
            GROUP BY
                psp.id,
                psp.server_name,
                psp.script_name,
                psp.notes,
                psp.source_of_truth
            LIMIT 1'
        );
        $statement->execute([
            ':project_key' => $projectKey,
            ':policy_id' => $policyId,
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
                'server_name' => trim((string) ($row['server_name'] ?? '')),
                'script_name' => trim((string) ($row['script_name'] ?? '')),
                'security_types' => app_pdo_parse_project_page_security_types(
                    (string) ($row['security_types'] ?? ''),
                ),
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
 *     server_name:string,
 *     script_name:string,
 *     security_types:list<string>,
 *     notes:string,
 *     source_of_truth?:string
 * } $input
 * @return array{
 *     ok:bool,
 *     policy_id:int,
 *     error:string
 * }
 */
function app_pdo_create_project_page_security_policy(array $app, string $projectKey, array $input): array
{
    $pdo = null;

    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_pdo_fetch_project_id_for_security_domain($pdo, $projectKey);
        if ($projectId === null) {
            return [
                'ok' => false,
                'policy_id' => 0,
                'error' => '保存対象の project が見つかりません。',
            ];
        }

        $pdo->beginTransaction();

        $insertStatement = $pdo->prepare(
            'INSERT INTO project_page_security_policies (
                project_id,
                server_name,
                script_name,
                notes,
                source_of_truth
            ) VALUES (
                :project_id,
                :server_name,
                :script_name,
                :notes,
                :source_of_truth
            )'
        );
        $insertStatement->execute([
            ':project_id' => $projectId,
            ':server_name' => $input['server_name'],
            ':script_name' => $input['script_name'],
            ':notes' => $input['notes'],
            ':source_of_truth' => $input['source_of_truth'] ?? 'manual',
        ]);

        $policyId = (int) $pdo->lastInsertId();
        app_pdo_replace_project_page_security_policy_capabilities(
            $pdo,
            $policyId,
            $input['security_types'],
        );

        $pdo->commit();

        return [
            'ok' => true,
            'policy_id' => $policyId,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'policy_id' => 0,
            'error' => app_pdo_map_project_page_security_error($throwable),
        ];
    }
}

/**
 * @param array{
 *     server_name:string,
 *     script_name:string,
 *     security_types:list<string>,
 *     notes:string,
 *     source_of_truth?:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_update_project_page_security_policy(
    array $app,
    string $projectKey,
    int $policyId,
    array $input
): array {
    if ($policyId <= 0) {
        return [
            'ok' => false,
            'error' => '更新対象の page security policy が不正です。',
        ];
    }

    $pdo = null;

    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_pdo_fetch_project_id_for_security_domain($pdo, $projectKey);
        if ($projectId === null) {
            return [
                'ok' => false,
                'error' => '更新対象の project が見つかりません。',
            ];
        }

        $pdo->beginTransaction();

        $updateStatement = $pdo->prepare(
            'UPDATE project_page_security_policies
            SET
                server_name = :server_name,
                script_name = :script_name,
                notes = :notes,
                source_of_truth = :source_of_truth
            WHERE
                id = :policy_id
                AND project_id = :project_id'
        );
        $updateStatement->execute([
            ':server_name' => $input['server_name'],
            ':script_name' => $input['script_name'],
            ':notes' => $input['notes'],
            ':source_of_truth' => $input['source_of_truth'] ?? 'manual',
            ':policy_id' => $policyId,
            ':project_id' => $projectId,
        ]);

        if ($updateStatement->rowCount() === 0 && !app_pdo_project_page_security_policy_exists($pdo, $projectId, $policyId)) {
            $pdo->rollBack();

            return [
                'ok' => false,
                'error' => '更新対象の page security policy が見つかりません。',
            ];
        }

        app_pdo_replace_project_page_security_policy_capabilities(
            $pdo,
            $policyId,
            $input['security_types'],
        );

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
            'error' => app_pdo_map_project_page_security_error($throwable),
        ];
    }
}

/**
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_delete_project_page_security_policy(array $app, string $projectKey, int $policyId): array
{
    if ($policyId <= 0) {
        return [
            'ok' => false,
            'error' => '削除対象の page security policy が不正です。',
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
            'DELETE FROM project_page_security_policies
            WHERE
                id = :policy_id
                AND project_id = :project_id'
        );
        $statement->execute([
            ':policy_id' => $policyId,
            ':project_id' => $projectId,
        ]);

        if ($statement->rowCount() === 0) {
            return [
                'ok' => false,
                'error' => '削除対象の page security policy が見つかりません。',
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

function app_pdo_fetch_project_id_for_security_domain(PDO $pdo, string $projectKey): ?int
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

    $row = $statement->fetch();
    if (!is_array($row)) {
        return null;
    }

    $projectId = (int) ($row['id'] ?? 0);
    return $projectId > 0 ? $projectId : null;
}

/**
 * @param list<string> $securityTypes
 */
function app_pdo_replace_project_page_security_policy_capabilities(PDO $pdo, int $policyId, array $securityTypes): void
{
    $deleteStatement = $pdo->prepare(
        'DELETE FROM project_page_security_policy_capabilities
        WHERE page_security_policy_id = :policy_id'
    );
    $deleteStatement->execute([
        ':policy_id' => $policyId,
    ]);

    if ($securityTypes === []) {
        return;
    }

    $insertStatement = $pdo->prepare(
        'INSERT INTO project_page_security_policy_capabilities (
            page_security_policy_id,
            security_type
        ) VALUES (
            :policy_id,
            :security_type
        )'
    );

    foreach ($securityTypes as $securityType) {
        $insertStatement->execute([
            ':policy_id' => $policyId,
            ':security_type' => $securityType,
        ]);
    }
}

function app_pdo_project_page_security_policy_exists(PDO $pdo, int $projectId, int $policyId): bool
{
    $statement = $pdo->prepare(
        'SELECT id
        FROM project_page_security_policies
        WHERE
            id = :policy_id
            AND project_id = :project_id
        LIMIT 1'
    );
    $statement->execute([
        ':policy_id' => $policyId,
        ':project_id' => $projectId,
    ]);

    return is_array($statement->fetch());
}

/**
 * @return list<string>
 */
function app_pdo_parse_project_page_security_types(string $value): array
{
    if ($value === '') {
        return [];
    }

    return array_values(
        array_filter(
            array_map(
                static fn (string $item): string => trim($item),
                explode(',', $value),
            ),
            static fn (string $item): bool => $item !== '',
        ),
    );
}

function app_pdo_map_project_page_security_error(Throwable $throwable): string
{
    $message = $throwable->getMessage();
    if (str_contains($message, 'uq_project_page_security_policy_scope')) {
        return '同じ server name / script name の page security は既に存在します。';
    }

    return $message;
}
