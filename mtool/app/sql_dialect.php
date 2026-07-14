<?php

declare(strict_types=1);

function app_sql_dialect_from_dsn(string $dsn): string
{
    $normalizedDsn = strtolower(trim($dsn));
    if (str_starts_with($normalizedDsn, 'sqlite:')) {
        return 'sqlite';
    }

    if (str_starts_with($normalizedDsn, 'pgsql:')) {
        return 'pgsql';
    }

    if (str_starts_with($normalizedDsn, 'firebird:')) {
        return 'firebird';
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

    if ($driverName === 'pgsql') {
        return 'pgsql';
    }

    if ($driverName === 'firebird') {
        return 'firebird';
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

    if ($normalizedDialect === 'pgsql') {
        return "to_char({$trimmedColumnExpression}, 'YYYY-MM-DD HH24:MI:SS') AS {$trimmedAlias}";
    }

    if ($normalizedDialect === 'firebird') {
        return "CAST({$trimmedColumnExpression} AS VARCHAR(32)) AS {$trimmedAlias}";
    }

    return "DATE_FORMAT({$trimmedColumnExpression}, \"%Y-%m-%d %H:%i:%s\") AS {$trimmedAlias}";
}

function app_sql_identifier(string $dialect, string $identifier): string
{
    $trimmedIdentifier = trim($identifier);
    if ($trimmedIdentifier === '') {
        throw new InvalidArgumentException('SQL identifier cannot be empty.');
    }

    if (in_array(strtolower(trim($dialect)), ['sqlite', 'firebird'], true)) {
        return '"' . str_replace('"', '""', $trimmedIdentifier) . '"';
    }

    return $trimmedIdentifier;
}

function app_sql_limit_clause(string $dialect, int $limit): string
{
    $normalizedLimit = max(1, $limit);
    if (strtolower(trim($dialect)) === 'firebird') {
        return 'ROWS ' . $normalizedLimit;
    }

    return 'LIMIT ' . $normalizedLimit;
}

/**
 * @param array<string|int,mixed> $row
 * @return array<string|int,mixed>
 */
function app_sql_normalize_row_keys(array $row): array
{
    $normalized = [];
    foreach ($row as $key => $value) {
        $normalized[is_string($key) ? strtolower($key) : $key] = $value;
    }

    return $normalized;
}

function app_sql_table_exists(PDO $pdo, string $tableName): bool
{
    $trimmedTableName = trim($tableName);
    if ($trimmedTableName === '') {
        return false;
    }

    $dialect = app_sql_dialect_from_pdo($pdo);
    if ($dialect === 'sqlite') {
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

    if ($dialect === 'pgsql') {
        $statement = $pdo->prepare(
            'SELECT 1
            FROM information_schema.tables
            WHERE table_schema = current_schema()
              AND (table_name = :table_name OR table_name = :lower_table_name)
            LIMIT 1'
        );
        $statement->execute([
            ':table_name' => $trimmedTableName,
            ':lower_table_name' => strtolower($trimmedTableName),
        ]);

        return $statement->fetchColumn() !== false;
    }

    if ($dialect === 'firebird') {
        $statement = $pdo->prepare(
            'SELECT 1
            FROM RDB$RELATIONS
            WHERE RDB$SYSTEM_FLAG = 0
              AND TRIM(RDB$RELATION_NAME) = ?'
        );
        $statement->execute([
            strtoupper($trimmedTableName),
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

    $dialect = app_sql_dialect_from_pdo($pdo);
    if ($dialect === 'sqlite') {
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

    if ($dialect === 'pgsql') {
        $statement = $pdo->prepare(
            'SELECT 1
            FROM information_schema.columns
            WHERE table_schema = current_schema()
              AND (table_name = :table_name OR table_name = :lower_table_name)
              AND (column_name = :column_name OR column_name = :lower_column_name)
            LIMIT 1'
        );
        $statement->execute([
            ':table_name' => $trimmedTableName,
            ':lower_table_name' => strtolower($trimmedTableName),
            ':column_name' => $trimmedColumnName,
            ':lower_column_name' => strtolower($trimmedColumnName),
        ]);

        return $statement->fetchColumn() !== false;
    }

    if ($dialect === 'firebird') {
        $statement = $pdo->prepare(
            'SELECT 1
            FROM RDB$RELATION_FIELDS
            WHERE TRIM(RDB$RELATION_NAME) = ?
              AND TRIM(RDB$FIELD_NAME) = ?'
        );
        $statement->execute([
            strtoupper($trimmedTableName),
            strtoupper($trimmedColumnName),
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
    $dialect = app_sql_dialect_from_pdo($pdo);
    if ($dialect === 'sqlite') {
        $version = $pdo->query('SELECT sqlite_version()')->fetchColumn();

        return is_string($version) ? $version : '';
    }

    if ($dialect === 'pgsql') {
        $version = $pdo->query('SELECT version()')->fetchColumn();

        return is_string($version) ? $version : '';
    }

    if ($dialect === 'firebird') {
        $version = $pdo->query('SELECT RDB$GET_CONTEXT(\'SYSTEM\', \'ENGINE_VERSION\') FROM RDB$DATABASE')->fetchColumn();

        return is_string($version) ? $version : '';
    }

    $version = $pdo->query('SELECT VERSION()')->fetchColumn();

    return is_string($version) ? $version : '';
}

function app_sql_current_database_name(PDO $pdo): string
{
    $dialect = app_sql_dialect_from_pdo($pdo);
    if ($dialect === 'sqlite') {
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

    if ($dialect === 'pgsql') {
        $databaseName = $pdo->query('SELECT current_database()')->fetchColumn();

        return is_string($databaseName) ? $databaseName : '';
    }

    if ($dialect === 'firebird') {
        $databaseName = $pdo->query('SELECT RDB$GET_CONTEXT(\'SYSTEM\', \'DB_NAME\') FROM RDB$DATABASE')->fetchColumn();

        return is_string($databaseName) ? $databaseName : '';
    }

    $databaseName = $pdo->query('SELECT DATABASE()')->fetchColumn();

    return is_string($databaseName) ? $databaseName : '';
}
