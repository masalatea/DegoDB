#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/config_db_bootstrap.php';

/**
 * @param list<string> $argv
 * @return array{ok:bool,help:bool,files:list<string>,requested_by:string,error:string}
 */
function app_cli_apply_config_sample_seed_sqlite_parse_args(array $argv): array
{
    $files = [];
    $requestedBy = 'cli';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return ['ok' => true, 'help' => true, 'files' => [], 'requested_by' => $requestedBy, 'error' => ''];
        }

        if (str_starts_with($argument, '--requested-by=')) {
            $requestedBy = trim(substr($argument, strlen('--requested-by=')));
            continue;
        }

        $files[] = $argument;
    }

    if ($files === []) {
        return ['ok' => false, 'help' => false, 'files' => [], 'requested_by' => $requestedBy, 'error' => 'seed file is required.'];
    }

    return ['ok' => true, 'help' => false, 'files' => $files, 'requested_by' => $requestedBy, 'error' => ''];
}

function app_cli_apply_config_sample_seed_sqlite_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/apply_config_sample_seed_sqlite.php [--requested-by=NAME] FILE.sql [...]

Apply sample seed SQL files to a SQLite config store.
TEXT;
}

/**
 * @param array<string,mixed> $variables
 */
function app_sqlite_seed_substitute_variables(PDO $pdo, string $statement, array $variables): string
{
    return preg_replace_callback(
        '/@([A-Za-z0-9_]+)/',
        static function (array $matches) use ($pdo, $variables): string {
            $name = $matches[1];
            if (!array_key_exists($name, $variables) || $variables[$name] === null) {
                return 'NULL';
            }

            $value = $variables[$name];
            if (is_int($value) || is_float($value) || (is_string($value) && preg_match('/^-?[0-9]+(?:\.[0-9]+)?$/', $value) === 1)) {
                return (string) $value;
            }

            return $pdo->quote((string) $value);
        },
        $statement,
    ) ?? $statement;
}

function app_sqlite_seed_strip_mysql_upsert(string $statement): string
{
    return preg_replace('/\s+ON\s+DUPLICATE\s+KEY\s+UPDATE\s+.+$/is', '', $statement) ?? $statement;
}

function app_sqlite_seed_strip_line_comments(string $statement): string
{
    return trim(preg_replace('/^\s*--.*$/m', '', $statement) ?? $statement);
}

function app_sqlite_seed_convert_insert_ignore(string $statement): string
{
    return preg_replace('/^\s*INSERT\s+IGNORE\s+INTO\s+/i', 'INSERT OR IGNORE INTO ', $statement) ?? $statement;
}

function app_sqlite_seed_convert_if_function(string $statement): string
{
    return preg_replace('/\bIF\s*\(/i', 'IIF(', $statement) ?? $statement;
}

function app_sqlite_seed_convert_common_functions(string $statement): string
{
    return preg_replace('/\bCHAR_LENGTH\s*\(/i', 'LENGTH(', $statement) ?? $statement;
}

function app_sqlite_seed_convert_mysql_string_escapes(string $statement): string
{
    return preg_replace_callback(
        "/'([^']*)'/s",
        static function (array $matches): string {
            $value = str_replace('\\\\', '\\', (string) ($matches[1] ?? ''));

            return "'" . str_replace("'", "''", $value) . "'";
        },
        $statement,
    ) ?? $statement;
}

function app_sqlite_seed_convert_temporary_table_drop(string $statement): string
{
    return preg_replace('/\bDROP\s+TEMPORARY\s+TABLE\s+/i', 'DROP TABLE ', $statement) ?? $statement;
}

function app_sqlite_seed_convert_temporary_table_create(string $statement): string
{
    return preg_replace('/\bCREATE\s+TEMPORARY\s+TABLE\s+/i', 'CREATE TABLE ', $statement) ?? $statement;
}

function app_sqlite_seed_convert_update_join(string $statement): string
{
    $trimmed = trim($statement);
    if (
        preg_match(
            '/^UPDATE\s+(.+?)\s+SET\s+(.+?)\s+WHERE\s+(.+)$/is',
            $trimmed,
            $matches,
        ) !== 1
    ) {
        return $statement;
    }

    $targetAndJoins = trim($matches[1]);
    if (stripos($targetAndJoins, 'INNER JOIN') === false) {
        return $statement;
    }

    $joinParts = preg_split('/\s+INNER\s+JOIN\s+/i', $targetAndJoins);
    if (!is_array($joinParts) || count($joinParts) < 2) {
        return $statement;
    }

    $target = trim(array_shift($joinParts));
    $targetAlias = '';
    if (preg_match('/\s+AS\s+([A-Za-z_][A-Za-z0-9_]*)$/i', $target, $aliasMatches) === 1) {
        $targetAlias = $aliasMatches[1];
    }

    $fromParts = [];
    $joinConditions = [];
    foreach ($joinParts as $joinPart) {
        $joinSplit = preg_split('/\s+ON\s+/i', trim($joinPart), 2);
        if (!is_array($joinSplit) || count($joinSplit) !== 2) {
            return $statement;
        }

        $fromParts[] = trim($joinSplit[0]);
        $joinConditions[] = '(' . trim($joinSplit[1]) . ')';
    }

    $setClause = trim($matches[2]);
    if ($targetAlias !== '') {
        $setClause = preg_replace(
            '/(^|,\s*)' . preg_quote($targetAlias, '/') . '\.([A-Za-z_][A-Za-z0-9_]*)\s*=/m',
            '$1$2 =',
            $setClause,
        ) ?? $setClause;
    }

    $whereClause = trim($matches[3]);
    $allConditions = array_merge($joinConditions, ['(' . $whereClause . ')']);

    return 'UPDATE ' . $target
        . ' SET ' . $setClause
        . ' FROM ' . implode(', ', $fromParts)
        . ' WHERE ' . implode(' AND ', $allConditions);
}

function app_sqlite_seed_convert_delete_alias(string $statement): string
{
    $trimmed = trim($statement);
    if (
        preg_match(
            '/^DELETE\s+([A-Za-z_][A-Za-z0-9_]*)\s+FROM\s+([A-Za-z_][A-Za-z0-9_]*)\s+AS\s+\1\s+WHERE\s+(.+)$/is',
            $trimmed,
            $matches,
        ) === 1
    ) {
        $alias = $matches[1];
        $table = $matches[2];
        $where = preg_replace('/\b' . preg_quote($alias, '/') . '\./', '', $matches[3]) ?? $matches[3];

        return 'DELETE FROM ' . $table . ' WHERE ' . $where;
    }

    if (
        preg_match(
            '/^DELETE\s+([A-Za-z_][A-Za-z0-9_]*)\s+FROM\s+([A-Za-z_][A-Za-z0-9_]*)\s+AS\s+\1\s+(.+)\s+WHERE\s+(.+)$/is',
            $trimmed,
            $matches,
        ) !== 1
    ) {
        return $statement;
    }

    $alias = $matches[1];
    $table = $matches[2];
    $joins = trim($matches[3]);
    $where = trim($matches[4]);

    return 'DELETE FROM ' . $table
        . ' WHERE EXISTS (SELECT 1 FROM ' . $table . ' AS ' . $alias . ' ' . $joins
        . ' WHERE ' . $where . ' AND ' . $alias . '.rowid = ' . $table . '.rowid)';
}

/**
 * @param array<string,mixed> $variables
 * @return array{ok:bool,applied:int,skipped:int,error:string}
 */
function app_sqlite_seed_apply_statement(PDO $pdo, string $statement, array &$variables): array
{
    $trimmed = trim($statement);
    if ($trimmed === '') {
        return ['ok' => true, 'applied' => 0, 'skipped' => 1, 'error' => ''];
    }

    if (preg_match('/^SET\s+@([A-Za-z0-9_]+)\s*=\s*NULL$/i', $trimmed, $matches) === 1) {
        $variables[$matches[1]] = null;
        return ['ok' => true, 'applied' => 0, 'skipped' => 1, 'error' => ''];
    }

    if (preg_match('/^SET\s+@([A-Za-z0-9_]+)\s*=\s*LAST_INSERT_ID\(\)$/i', $trimmed, $matches) === 1) {
        $variables[$matches[1]] = $pdo->lastInsertId();
        return ['ok' => true, 'applied' => 0, 'skipped' => 1, 'error' => ''];
    }

    if (preg_match('/^SET\s+@([A-Za-z0-9_]+)\s*=\s*\((.*)\)$/is', $trimmed, $matches) === 1) {
        $selectSql = app_sqlite_seed_substitute_variables($pdo, trim($matches[2]), $variables);
        $variables[$matches[1]] = $pdo->query($selectSql)->fetchColumn();
        return ['ok' => true, 'applied' => 0, 'skipped' => 1, 'error' => ''];
    }

    $prepared = app_sqlite_seed_substitute_variables($pdo, $trimmed, $variables);
    $prepared = app_sqlite_seed_strip_line_comments($prepared);
    $prepared = app_sqlite_seed_convert_insert_ignore($prepared);
    $prepared = app_sqlite_seed_convert_if_function($prepared);
    $prepared = app_sqlite_seed_convert_common_functions($prepared);
    $prepared = app_sqlite_seed_convert_mysql_string_escapes($prepared);
    $prepared = app_sqlite_seed_convert_temporary_table_drop($prepared);
    $prepared = app_sqlite_seed_convert_temporary_table_create($prepared);
    $prepared = app_sqlite_seed_convert_update_join($prepared);
    $prepared = str_replace('`', '', $prepared);
    $prepared = app_sqlite_seed_strip_mysql_upsert($prepared);
    $prepared = app_sqlite_seed_convert_delete_alias($prepared);

    $applied = 0;
    foreach (app_config_db_bootstrap_sqlite_prepare_statement($pdo, $prepared) as $sqliteStatement) {
        $pdo->exec($sqliteStatement);
        $applied++;
    }

    return ['ok' => true, 'applied' => $applied, 'skipped' => 0, 'error' => ''];
}

$parsed = app_cli_apply_config_sample_seed_sqlite_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_apply_config_sample_seed_sqlite_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_apply_config_sample_seed_sqlite_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
$configDb = app_database_config($app, 'config_db');
if (app_sql_dialect_from_db_config($configDb) !== 'sqlite') {
    fwrite(STDERR, "config store is not SQLite.\n");
    exit(65);
}

$pdo = app_create_config_pdo($app);
$pdo->exec('PRAGMA foreign_keys = ON');
$variables = [];
$appliedFiles = [];
$appliedStatements = 0;
$skippedStatements = 0;

try {
    foreach ($parsed['files'] as $file) {
        if (!is_file($file)) {
            throw new RuntimeException('seed file not found: ' . $file);
        }

        $contents = file_get_contents($file);
        if (!is_string($contents)) {
            throw new RuntimeException('seed file を読み込めません: ' . $file);
        }

        $pdo->beginTransaction();
        foreach (app_config_db_bootstrap_split_sql_statements($contents) as $statement) {
            try {
                $result = app_sqlite_seed_apply_statement($pdo, $statement, $variables);
                $appliedStatements += $result['applied'];
                $skippedStatements += $result['skipped'];
            } catch (Throwable $throwable) {
                $snippet = preg_replace('/\s+/', ' ', trim($statement)) ?? trim($statement);
                throw new RuntimeException(
                    'seed statement failed in ' . $file . ': ' . substr($snippet, 0, 240)
                        . ' / ' . $throwable->getMessage(),
                    0,
                    $throwable,
                );
            }
        }
        $pdo->commit();
        $appliedFiles[] = $file;
    }

    fwrite(
        STDOUT,
        json_encode(
            [
                'ok' => true,
                'requested_by' => $parsed['requested_by'],
                'target' => $configDb['name'],
                'applied_file_count' => count($appliedFiles),
                'applied_statement_count' => $appliedStatements,
                'skipped_statement_count' => $skippedStatements,
                'applied_files' => $appliedFiles,
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ) . PHP_EOL,
    );
    exit(0);
} catch (Throwable $throwable) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
}
