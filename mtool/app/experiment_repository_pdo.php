<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

function app_pdo_lab_experiment_datetime_select_expr(
    array $app,
    string $columnExpression = 'updated_at',
    string $alias = 'updated_at',
): string {
    return app_sql_datetime_select_expr(
        app_sql_dialect_from_db_config(app_database_config($app, 'db')),
        $columnExpression,
        $alias,
    );
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
 *     items:list<array{
 *         experiment_key:string,
 *         project_key:string,
 *         name:string,
 *         execution_status:string,
 *         runtime_target:string,
 *         executed_by:string,
 *         updated_at:string,
 *         notes:string
 *     }>,
 *     error:string
 * }
 */
function app_pdo_fetch_lab_experiment_catalog(array $app): array
{
    try {
        $pdo = app_create_pdo($app);
        $statement = $pdo->query(
            'SELECT
                experiment_key,
                project_key,
                name,
                execution_status,
                runtime_target,
                COALESCE(executed_by, "") AS executed_by,
                ' . app_pdo_lab_experiment_datetime_select_expr($app) . ',
                notes
            FROM lab_experiments
            ORDER BY updated_at DESC, id DESC'
        );

        $rows = $statement->fetchAll();
        $items = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = [
                'experiment_key' => (string) ($row['experiment_key'] ?? ''),
                'project_key' => (string) ($row['project_key'] ?? ''),
                'name' => (string) ($row['name'] ?? ''),
                'execution_status' => (string) ($row['execution_status'] ?? ''),
                'runtime_target' => (string) ($row['runtime_target'] ?? ''),
                'executed_by' => (string) ($row['executed_by'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
                'notes' => (string) ($row['notes'] ?? ''),
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
 *         experiment_key:string,
 *         project_key:string,
 *         name:string,
 *         execution_status:string,
 *         runtime_target:string,
 *         executed_by:string,
 *         updated_at:string,
 *         notes:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_lab_experiment_by_key(array $app, string $experimentKey): array
{
    try {
        $pdo = app_create_pdo($app);
        $statement = $pdo->prepare(
            'SELECT
                experiment_key,
                project_key,
                name,
                execution_status,
                runtime_target,
                COALESCE(executed_by, "") AS executed_by,
                ' . app_pdo_lab_experiment_datetime_select_expr($app) . ',
                notes
            FROM lab_experiments
            WHERE experiment_key = :experiment_key
            LIMIT 1'
        );

        $statement->execute([
            ':experiment_key' => $experimentKey,
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
                'experiment_key' => (string) ($row['experiment_key'] ?? ''),
                'project_key' => (string) ($row['project_key'] ?? ''),
                'name' => (string) ($row['name'] ?? ''),
                'execution_status' => (string) ($row['execution_status'] ?? ''),
                'runtime_target' => (string) ($row['runtime_target'] ?? ''),
                'executed_by' => (string) ($row['executed_by'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
                'notes' => (string) ($row['notes'] ?? ''),
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
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     }
 * } $app
 * @param array{
 *     experiment_key:string,
 *     project_key:string,
 *     name:string,
 *     execution_status:string,
 *     runtime_target:string,
 *     executed_by:string,
 *     notes:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_insert_lab_experiment(array $app, array $input): array
{
    try {
        $pdo = app_create_pdo($app);
        $statement = $pdo->prepare(
            'INSERT INTO lab_experiments (
                experiment_key,
                project_key,
                name,
                execution_status,
                runtime_target,
                executed_by,
                notes
            ) VALUES (
                :experiment_key,
                :project_key,
                :name,
                :execution_status,
                :runtime_target,
                :executed_by,
                :notes
            )'
        );

        $statement->execute([
            ':experiment_key' => $input['experiment_key'],
            ':project_key' => $input['project_key'],
            ':name' => $input['name'],
            ':execution_status' => $input['execution_status'],
            ':runtime_target' => $input['runtime_target'],
            ':executed_by' => $input['executed_by'],
            ':notes' => $input['notes'],
        ]);

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (PDOException $exception) {
        return [
            'ok' => false,
            'error' => app_pdo_lab_experiment_write_error_message($exception),
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
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     }
 * } $app
 * @param array{
 *     experiment_key:string,
 *     project_key:string,
 *     name:string,
 *     execution_status:string,
 *     runtime_target:string,
 *     notes:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_update_lab_experiment(array $app, array $input): array
{
    try {
        $pdo = app_create_pdo($app);
        $statement = $pdo->prepare(
            'UPDATE lab_experiments
            SET
                project_key = :project_key,
                name = :name,
                execution_status = :execution_status,
                runtime_target = :runtime_target,
                notes = :notes,
                updated_at = CURRENT_TIMESTAMP
            WHERE experiment_key = :experiment_key'
        );

        $statement->execute([
            ':experiment_key' => $input['experiment_key'],
            ':project_key' => $input['project_key'],
            ':name' => $input['name'],
            ':execution_status' => $input['execution_status'],
            ':runtime_target' => $input['runtime_target'],
            ':notes' => $input['notes'],
        ]);

        if ($statement->rowCount() > 0 || app_pdo_lab_experiment_exists($pdo, $input['experiment_key'])) {
            return [
                'ok' => true,
                'error' => '',
            ];
        }

        return [
            'ok' => false,
            'error' => '更新対象の experiment が見つかりません。',
        ];
    } catch (PDOException $exception) {
        return [
            'ok' => false,
            'error' => app_pdo_lab_experiment_write_error_message($exception),
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

function app_pdo_lab_experiment_insert_error_message(PDOException $exception): string
{
    return app_pdo_lab_experiment_write_error_message($exception);
}

function app_pdo_lab_experiment_write_error_message(PDOException $exception): string
{
    if ($exception->getCode() !== '23000') {
        return $exception->getMessage();
    }

    $message = $exception->getMessage();

    if (str_contains($message, 'uq_lab_experiments_experiment_key')) {
        return 'experiment key は既に存在します。';
    }

    return 'experiment の保存に失敗しました。重複データを確認してください。';
}

function app_pdo_lab_experiment_exists(PDO $pdo, string $experimentKey): bool
{
    $statement = $pdo->prepare(
        'SELECT 1
        FROM lab_experiments
        WHERE experiment_key = :experiment_key
        LIMIT 1'
    );

    $statement->execute([
        ':experiment_key' => $experimentKey,
    ]);

    return $statement->fetchColumn() !== false;
}
