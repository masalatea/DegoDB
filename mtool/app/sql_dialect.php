<?php

declare(strict_types=1);

function app_sql_dialect_from_dsn(string $dsn): string
{
    $normalizedDsn = strtolower(trim($dsn));
    if (str_starts_with($normalizedDsn, 'sqlite:')) {
        return 'sqlite';
    }

    return 'mysql';
}

/**
 * @param array{
 *     dsn:string
 * } $dbConfig
 */
function app_sql_dialect_from_db_config(array $dbConfig): string
{
    return app_sql_dialect_from_dsn((string) ($dbConfig['dsn'] ?? ''));
}

function app_sql_dialect_from_pdo(PDO $pdo): string
{
    $driverName = strtolower(trim((string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME)));
    if ($driverName === 'sqlite') {
        return 'sqlite';
    }

    return 'mysql';
}

/**
 * Return a dialect-specific SQL expression for a datetime column selected as a
 * stable `YYYY-MM-DD HH:MM:SS` string.
 */
function app_sql_datetime_select_expr(string $dialect, string $columnExpression, string $alias): string
{
    $normalizedDialect = strtolower(trim($dialect));
    $trimmedColumnExpression = trim($columnExpression);
    $trimmedAlias = trim($alias);

    if ($trimmedColumnExpression === '' || $trimmedAlias === '') {
        throw new InvalidArgumentException('datetime select expression requires column and alias.');
    }

    if ($normalizedDialect === 'sqlite') {
        return "strftime('%Y-%m-%d %H:%M:%S', {$trimmedColumnExpression}) AS {$trimmedAlias}";
    }

    return "DATE_FORMAT({$trimmedColumnExpression}, \"%Y-%m-%d %H:%i:%s\") AS {$trimmedAlias}";
}

function app_sql_identifier(string $dialect, string $identifier): string
{
    $trimmedIdentifier = trim($identifier);
    if ($trimmedIdentifier === '') {
        throw new InvalidArgumentException('SQL identifier cannot be empty.');
    }

    if (strtolower(trim($dialect)) === 'sqlite') {
        return '"' . str_replace('"', '""', $trimmedIdentifier) . '"';
    }

    return $trimmedIdentifier;
}

function app_sql_table_exists(PDO $pdo, string $tableName): bool
{
    $trimmedTableName = trim($tableName);
    if ($trimmedTableName === '') {
        return false;
    }

    if (app_sql_dialect_from_pdo($pdo) === 'sqlite') {
        $statement = $pdo->prepare(
            "SELECT 1
            FROM sqlite_master
            WHERE type = 'table'
              AND name = :table_name
            LIMIT 1"
        );
        $statement->execute([
            ':table_name' => $trimmedTableName,
        ]);

        return $statement->fetchColumn() !== false;
    }

    $statement = $pdo->prepare(
        'SELECT 1
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND table_name = :table_name
        LIMIT 1'
    );
    $statement->execute([
        ':table_name' => $trimmedTableName,
    ]);

    return $statement->fetchColumn() !== false;
}

function app_sql_column_exists(PDO $pdo, string $tableName, string $columnName): bool
{
    $trimmedTableName = trim($tableName);
    $trimmedColumnName = trim($columnName);
    if ($trimmedTableName === '' || $trimmedColumnName === '') {
        return false;
    }

    if (app_sql_dialect_from_pdo($pdo) === 'sqlite') {
        $statement = $pdo->prepare(
            'SELECT 1
            FROM pragma_table_info(:table_name)
            WHERE name = :column_name
            LIMIT 1'
        );
        $statement->execute([
            ':table_name' => $trimmedTableName,
            ':column_name' => $trimmedColumnName,
        ]);

        return $statement->fetchColumn() !== false;
    }

    $statement = $pdo->prepare(
        'SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = :table_name
          AND column_name = :column_name
        LIMIT 1'
    );
    $statement->execute([
        ':table_name' => $trimmedTableName,
        ':column_name' => $trimmedColumnName,
    ]);

    return $statement->fetchColumn() !== false;
}

function app_sql_server_version(PDO $pdo): string
{
    if (app_sql_dialect_from_pdo($pdo) === 'sqlite') {
        $version = $pdo->query('SELECT sqlite_version()')->fetchColumn();

        return is_string($version) ? $version : '';
    }

    $version = $pdo->query('SELECT VERSION()')->fetchColumn();

    return is_string($version) ? $version : '';
}

function app_sql_current_database_name(PDO $pdo): string
{
    if (app_sql_dialect_from_pdo($pdo) === 'sqlite') {
        $rows = $pdo->query('PRAGMA database_list')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            if (!is_array($row) || (string) ($row['name'] ?? '') !== 'main') {
                continue;
            }

            $file = trim((string) ($row['file'] ?? ''));
            return $file !== '' ? $file : 'main';
        }

        return 'main';
    }

    $databaseName = $pdo->query('SELECT DATABASE()')->fetchColumn();

    return is_string($databaseName) ? $databaseName : '';
}
