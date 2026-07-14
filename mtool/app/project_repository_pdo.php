<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

/**
 * @param array{
 *     db:array{
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         project_key:string,
 *         name:string,
 *         slug:string,
 *         lifecycle_status:string,
 *         owner_login_id:string,
 *         php_namespace:string,
 *         member_count:int,
 *         updated_at:string,
 *         description:string
 *     }>,
 *     error:string
 * }
 */
function app_pdo_fetch_project_catalog(array $app): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $dialect = app_sql_dialect_from_db_config(app_database_config($app, 'config_db'));
        $updatedAtSelect = app_sql_datetime_select_expr($dialect, 'p.updated_at', 'updated_at');
        $statement = $pdo->query(
            'SELECT
                p.project_key,
                p.name,
                p.slug,
                p.lifecycle_status,
                p.owner_login_id,
                p.php_namespace,
                COUNT(DISTINCT pm.login_id) AS member_count,
                ' . $updatedAtSelect . ',
                p.description
            FROM projects AS p
            LEFT JOIN project_memberships AS pm
                ON pm.project_id = p.id
            GROUP BY
                p.id,
                p.project_key,
                p.name,
                p.slug,
                p.lifecycle_status,
                p.owner_login_id,
                p.php_namespace,
                p.updated_at,
                p.description
            ORDER BY p.updated_at DESC, p.id DESC'
        );

        $rows = $statement->fetchAll();
        $items = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = app_pdo_project_item_from_row($row);
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
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_key:string,
 *         name:string,
 *         slug:string,
 *         lifecycle_status:string,
 *         owner_login_id:string,
 *         php_namespace:string,
 *         member_count:int,
 *         updated_at:string,
 *         description:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_project_by_key(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $dialect = app_sql_dialect_from_pdo($pdo);
        $updatedAtSelect = app_sql_datetime_select_expr($dialect, 'p.updated_at', 'updated_at');
        $statement = $pdo->prepare(
            'SELECT
                p.project_key,
                p.name,
                p.slug,
                p.lifecycle_status,
                p.owner_login_id,
                p.php_namespace,
                COUNT(DISTINCT pm.login_id) AS member_count,
                ' . $updatedAtSelect . ',
                p.description
            FROM projects AS p
            LEFT JOIN project_memberships AS pm
                ON pm.project_id = p.id
            WHERE p.project_key = :project_key
            GROUP BY
                p.id,
                p.project_key,
                p.name,
                p.slug,
                p.lifecycle_status,
                p.owner_login_id,
                p.php_namespace,
                p.updated_at,
                p.description
            ' . app_sql_limit_clause($dialect, 1)
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
            'item' => app_pdo_project_item_from_row($row),
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
 * @param array<string|int,mixed> $row
 * @return array{
 *     project_key:string,
 *     name:string,
 *     slug:string,
 *     lifecycle_status:string,
 *     owner_login_id:string,
 *     php_namespace:string,
 *     member_count:int,
 *     updated_at:string,
 *     description:string
 * }
 */
function app_pdo_project_item_from_row(array $row): array
{
    $row = app_sql_normalize_row_keys($row);

    return [
                'project_key' => (string) ($row['project_key'] ?? ''),
                'name' => (string) ($row['name'] ?? ''),
                'slug' => (string) ($row['slug'] ?? ''),
                'lifecycle_status' => (string) ($row['lifecycle_status'] ?? ''),
                'owner_login_id' => (string) ($row['owner_login_id'] ?? ''),
                'php_namespace' => (string) ($row['php_namespace'] ?? ''),
                'member_count' => (int) ($row['member_count'] ?? 0),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
                'description' => (string) ($row['description'] ?? ''),
    ];
}

/**
 * @param array{
 *     db:array{
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     }
 * } $app
 * @param array{
 *     project_key:string,
 *     name:string,
 *     slug:string,
 *     lifecycle_status:string,
 *     owner_login_id:string,
 *     php_namespace:string,
 *     description:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_insert_project(array $app, array $input): array
{
    $pdo = null;

    try {
        $pdo = app_create_config_pdo($app);
        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'INSERT INTO projects (
                project_key,
                name,
                slug,
                lifecycle_status,
                owner_login_id,
                php_namespace,
                description
            ) VALUES (
                :project_key,
                :name,
                :slug,
                :lifecycle_status,
                :owner_login_id,
                :php_namespace,
                :description
            )'
        );

        $statement->execute([
            ':project_key' => $input['project_key'],
            ':name' => $input['name'],
            ':slug' => $input['slug'],
            ':lifecycle_status' => $input['lifecycle_status'],
            ':owner_login_id' => $input['owner_login_id'],
            ':php_namespace' => (string) ($input['php_namespace'] ?? ''),
            ':description' => $input['description'],
        ]);

        $projectId = app_pdo_project_inserted_id($pdo, (string) $input['project_key']);

        $membershipStatement = $pdo->prepare(
            'INSERT INTO project_memberships (
                project_id,
                login_id,
                role_code,
                can_administer
            ) VALUES (
                :project_id,
                :login_id,
                :role_code,
                :can_administer
            )'
        );

        $membershipStatement->execute([
            ':project_id' => $projectId,
            ':login_id' => $input['owner_login_id'],
            ':role_code' => 'owner',
            ':can_administer' => 1,
        ]);

        $pdo->commit();

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (PDOException $exception) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'error' => app_pdo_project_write_error_message($exception),
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
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     }
 * } $app
 * @param array{
 *     project_key:string,
 *     name:string,
 *     slug:string,
 *     lifecycle_status:string,
 *     php_namespace:string,
 *     description:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_update_project(array $app, array $input): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $statement = $pdo->prepare(
            'UPDATE projects
            SET
                name = :name,
                slug = :slug,
                lifecycle_status = :lifecycle_status,
                php_namespace = :php_namespace,
                description = :description,
                updated_at = CURRENT_TIMESTAMP
            WHERE project_key = :project_key'
        );

        $statement->execute([
            ':project_key' => $input['project_key'],
            ':name' => $input['name'],
            ':slug' => $input['slug'],
            ':lifecycle_status' => $input['lifecycle_status'],
            ':php_namespace' => (string) ($input['php_namespace'] ?? ''),
            ':description' => $input['description'],
        ]);

        if ($statement->rowCount() > 0 || app_pdo_project_exists($pdo, $input['project_key'])) {
            return [
                'ok' => true,
                'error' => '',
            ];
        }

        return [
            'ok' => false,
            'error' => '更新対象の project が見つかりません。',
        ];
    } catch (PDOException $exception) {
        return [
            'ok' => false,
            'error' => app_pdo_project_write_error_message($exception),
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

function app_pdo_project_insert_error_message(PDOException $exception): string
{
    return app_pdo_project_write_error_message($exception);
}

function app_pdo_project_write_error_message(PDOException $exception): string
{
    if ($exception->getCode() !== '23000') {
        return $exception->getMessage();
    }

    $message = $exception->getMessage();

    if (str_contains($message, 'uq_projects_project_key')) {
        return 'project key は既に存在します。';
    }

    if (str_contains($message, 'uq_projects_slug')) {
        return 'slug は既に存在します。';
    }

    return 'project の保存に失敗しました。重複データを確認してください。';
}

function app_pdo_project_exists(PDO $pdo, string $projectKey): bool
{
    $dialect = app_sql_dialect_from_pdo($pdo);
    $statement = $pdo->prepare(
        'SELECT 1
        FROM projects
        WHERE project_key = :project_key
        ' . app_sql_limit_clause($dialect, 1)
    );

    $statement->execute([
        ':project_key' => $projectKey,
    ]);

    return $statement->fetchColumn() !== false;
}

function app_pdo_project_inserted_id(PDO $pdo, string $projectKey): int
{
    try {
        $lastInsertId = (int) $pdo->lastInsertId();
        if ($lastInsertId > 0) {
            return $lastInsertId;
        }
    } catch (Throwable) {
        // Some PDO drivers, including Firebird, may not expose a useful lastInsertId.
    }

    $dialect = app_sql_dialect_from_pdo($pdo);
    $statement = $pdo->prepare(
        'SELECT id
        FROM projects
        WHERE project_key = :project_key
        ' . app_sql_limit_clause($dialect, 1)
    );
    $statement->execute([
        ':project_key' => $projectKey,
    ]);
    $id = $statement->fetchColumn();

    return (int) $id;
}
