<?php

declare(strict_types=1);

require_once __DIR__ . '/sqlite_mysql_promotion_manifest.php';

const APP_SQLITE_MYSQL_TARGET_SCHEMA_PLAN_VERSION = 'sqlite-mysql-target-schema-plan-v1';

/** @param array<string,mixed> $manifest @param array<string,mixed> $options @return array<string,mixed> */
function app_sqlite_mysql_target_schema_plan(array $manifest, array $options = []): array
{
    $errors = app_sqlite_mysql_promotion_manifest_contract_errors($manifest);
    if (($manifest['ok'] ?? false) !== true) $errors[] = 'promotion_manifest_not_ready';
    $charset = strtolower(trim((string) ($options['charset'] ?? 'utf8mb4')));
    $collation = strtolower(trim((string) ($options['collation'] ?? 'utf8mb4_bin')));
    if ($charset !== 'utf8mb4') $errors[] = 'unsupported_target_charset';
    if (!in_array($collation, ['utf8mb4_bin', 'utf8mb4_0900_bin'], true)) $errors[] = 'unsupported_target_collation';

    $statements = [];
    $tables = [];
    foreach (is_array($manifest['tables'] ?? null) ? $manifest['tables'] : [] as $table) {
        if (!is_array($table)) continue;
        $name = trim((string) ($table['name'] ?? ''));
        if (!app_sqlite_mysql_target_identifier_valid($name)) {
            $errors[] = 'invalid_table_identifier:' . $name;
            continue;
        }
        $definitions = [];
        foreach (is_array($table['columns'] ?? null) ? $table['columns'] : [] as $column) {
            if (!is_array($column)) continue;
            $columnName = trim((string) ($column['name'] ?? ''));
            $type = strtoupper(trim((string) ($column['target_type'] ?? '')));
            if (!app_sqlite_mysql_target_identifier_valid($columnName)) {
                $errors[] = 'invalid_column_identifier:' . $name . '.' . $columnName;
                continue;
            }
            if (!app_sqlite_mysql_target_type_valid($type)) {
                $errors[] = 'invalid_target_type:' . $name . '.' . $columnName;
                continue;
            }
            $definition = app_sqlite_mysql_target_quote_identifier($columnName) . ' ' . $type;
            $definition .= (($column['nullable'] ?? false) === true) ? ' NULL' : ' NOT NULL';
            if (array_key_exists('default', $column) && $column['default'] !== null) {
                $default = app_sqlite_mysql_target_default_sql($column['default']);
                if ($default === null) $errors[] = 'unsupported_target_default:' . $name . '.' . $columnName;
                else $definition .= ' DEFAULT ' . $default;
            }
            $definitions[] = $definition;
        }
        foreach (is_array($table['keys'] ?? null) ? $table['keys'] : [] as $key) {
            if (!is_array($key)) continue;
            $kind = strtolower((string) ($key['kind'] ?? ''));
            $keyName = trim((string) ($key['name'] ?? ''));
            $columns = app_sqlite_mysql_target_identifier_list($key['columns'] ?? []);
            if ($columns === null || !in_array($kind, ['primary', 'unique'], true)) {
                $errors[] = 'invalid_target_key:' . $name;
                continue;
            }
            if ($kind === 'primary') $definitions[] = 'PRIMARY KEY (' . $columns . ')';
            elseif (!app_sqlite_mysql_target_identifier_valid($keyName)) $errors[] = 'invalid_target_key_identifier:' . $name;
            else $definitions[] = 'CONSTRAINT ' . app_sqlite_mysql_target_quote_identifier($keyName) . ' UNIQUE (' . $columns . ')';
        }
        foreach (is_array($table['foreign_keys'] ?? null) ? $table['foreign_keys'] : [] as $foreignKey) {
            if (!is_array($foreignKey)) continue;
            $constraintName = trim((string) ($foreignKey['name'] ?? ''));
            $referencedTable = trim((string) ($foreignKey['referenced_table'] ?? ''));
            $columns = app_sqlite_mysql_target_identifier_list($foreignKey['columns'] ?? []);
            $referencedColumns = app_sqlite_mysql_target_identifier_list($foreignKey['referenced_columns'] ?? []);
            if (!app_sqlite_mysql_target_identifier_valid($constraintName)
                || !app_sqlite_mysql_target_identifier_valid($referencedTable)
                || $columns === null || $referencedColumns === null) {
                $errors[] = 'invalid_target_foreign_key:' . $name;
                continue;
            }
            $definitions[] = 'CONSTRAINT ' . app_sqlite_mysql_target_quote_identifier($constraintName)
                . ' FOREIGN KEY (' . $columns . ') REFERENCES '
                . app_sqlite_mysql_target_quote_identifier($referencedTable) . ' (' . $referencedColumns . ')';
        }
        if ($definitions === []) $errors[] = 'target_table_has_no_definitions:' . $name;
        $statement = 'CREATE TABLE ' . app_sqlite_mysql_target_quote_identifier($name)
            . " (\n  " . implode(",\n  ", $definitions) . "\n) ENGINE=InnoDB DEFAULT CHARACTER SET {$charset} COLLATE {$collation}";
        $statements[] = $statement;
        $tables[] = ['name' => $name, 'statement_sha256' => hash('sha256', $statement)];
    }
    $errors = array_values(array_unique($errors));
    $digestInput = implode(";\n", $statements) . ($statements === [] ? '' : ";\n");
    return [
        'plan_version' => APP_SQLITE_MYSQL_TARGET_SCHEMA_PLAN_VERSION,
        'ok' => $errors === [],
        'stage' => 'target_schema_plan',
        'mutation_performed' => false,
        'promotion_manifest_sha256' => app_sqlite_mysql_promotion_digest($manifest),
        'schema_sha256' => hash('sha256', $digestInput),
        'charset' => $charset,
        'collation' => $collation,
        'requires_empty_target' => true,
        'requires_explicit_approval' => true,
        'tables' => $tables,
        'statements' => $statements,
        'errors' => $errors,
    ];
}

/** @return array<string,mixed> */
function app_sqlite_mysql_target_schema_inspect(PDO $pdo): array
{
    $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver !== 'mysql') return ['ok' => false, 'empty' => false, 'tables' => [], 'error' => 'mysql_target_required', 'mutation_performed' => false];
    $rows = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);
    $tables = array_values(array_map('strval', is_array($rows) ? $rows : []));
    return ['ok' => true, 'empty' => $tables === [], 'tables' => $tables, 'error' => $tables === [] ? '' : 'target_not_empty', 'mutation_performed' => false];
}

/** @param array<string,mixed> $plan @return array<string,mixed> */
function app_sqlite_mysql_target_schema_apply(PDO $pdo, array $plan, bool $approved): array
{
    if (!$approved) return ['ok' => false, 'stage' => 'approval', 'created_tables' => [], 'error' => 'explicit_approval_required', 'mutation_performed' => false];
    if (($plan['ok'] ?? false) !== true || ($plan['plan_version'] ?? '') !== APP_SQLITE_MYSQL_TARGET_SCHEMA_PLAN_VERSION) {
        return ['ok' => false, 'stage' => 'plan', 'created_tables' => [], 'error' => 'valid_target_schema_plan_required', 'mutation_performed' => false];
    }
    $inspection = app_sqlite_mysql_target_schema_inspect($pdo);
    if (!$inspection['ok'] || !$inspection['empty']) return ['ok' => false, 'stage' => 'target_preflight', 'created_tables' => [], 'error' => $inspection['error'], 'mutation_performed' => false];
    $created = [];
    try {
        foreach ($plan['statements'] as $index => $statement) {
            $pdo->exec((string) $statement);
            $created[] = (string) ($plan['tables'][$index]['name'] ?? '');
        }
        return ['ok' => true, 'stage' => 'target_schema_ready', 'created_tables' => $created, 'schema_sha256' => $plan['schema_sha256'], 'error' => '', 'mutation_performed' => true];
    } catch (Throwable $throwable) {
        return ['ok' => false, 'stage' => 'partial_failure', 'created_tables' => $created, 'error' => $throwable->getMessage(), 'mutation_performed' => $created !== []];
    }
}

function app_sqlite_mysql_target_identifier_valid(string $value): bool { return preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,63}$/', $value) === 1; }
function app_sqlite_mysql_target_quote_identifier(string $value): string { return '`' . $value . '`'; }
function app_sqlite_mysql_target_type_valid(string $value): bool
{
    return preg_match('/^(BIGINT|TINYINT\(1\)|DECIMAL\([1-9][0-9]?,[0-9]{1,2}\)|DATETIME\(6\)|JSON|LONGBLOB|TEXT|VARCHAR\([1-9][0-9]{0,4}\)|CHAR\([1-9][0-9]{0,2}\))$/', $value) === 1;
}
function app_sqlite_mysql_target_identifier_list(mixed $values): ?string
{
    if (!is_array($values) || $values === []) return null;
    $result = [];
    foreach ($values as $value) { $name = (string) $value; if (!app_sqlite_mysql_target_identifier_valid($name)) return null; $result[] = app_sqlite_mysql_target_quote_identifier($name); }
    return implode(', ', $result);
}
function app_sqlite_mysql_target_default_sql(mixed $value): ?string
{
    if (is_int($value) || is_float($value)) return (string) $value;
    if (is_bool($value)) return $value ? '1' : '0';
    if (!is_string($value) || str_contains($value, "\0")) return null;
    if (strtoupper(trim($value)) === 'CURRENT_TIMESTAMP') return 'CURRENT_TIMESTAMP';
    return "'" . str_replace("'", "''", $value) . "'";
}
