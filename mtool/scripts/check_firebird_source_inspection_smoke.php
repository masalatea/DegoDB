#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/firebird_source_inspection.php';

/**
 * @param list<string> $argv
 * @return array{help:bool,pretty:bool,error:string}
 */
function app_cli_firebird_source_inspection_smoke_parse_args(array $argv): array
{
    $parsed = ['help' => false, 'pretty' => false, 'error' => ''];
    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            $parsed['help'] = true;
            continue;
        }
        if ($argument === '--pretty') {
            $parsed['pretty'] = true;
            continue;
        }
        $parsed['error'] = 'unsupported argument: ' . $argument;
        return $parsed;
    }
    return $parsed;
}

function app_cli_firebird_source_inspection_smoke_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_firebird_source_inspection_smoke.php [--pretty]

Read Firebird proof-database metadata and feed it through the source inspection normalizer.
Requires MTOOL_FIREBIRD_DSN / MTOOL_FIREBIRD_USER / MTOOL_FIREBIRD_PASSWORD.
TEXT;
}

/** @return array<string,mixed> */
function app_firebird_source_inspection_smoke(): array
{
    $dsn = trim((string) getenv('MTOOL_FIREBIRD_DSN'));
    $user = trim((string) getenv('MTOOL_FIREBIRD_USER'));
    $password = (string) (getenv('MTOOL_FIREBIRD_PASSWORD') ?: '');
    if ($dsn === '') {
        return [
            'ok' => false,
            'stage' => 'firebird_source_inspection_smoke',
            'mutation_performed' => false,
            'errors' => ['MTOOL_FIREBIRD_DSN is required'],
        ];
    }

    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $metadata = app_firebird_source_inspection_smoke_metadata($pdo);
        $inspection = app_firebird_source_inspection_normalize($metadata);
        return [
            'ok' => ($inspection['ok'] ?? false) === true,
            'stage' => 'firebird_source_inspection_smoke',
            'mutation_performed' => false,
            'metadata_counts' => [
                'relations' => count($metadata['relations'] ?? []),
                'fields' => count($metadata['fields'] ?? []),
                'constraints' => count($metadata['constraints'] ?? []),
                'index_segments' => count($metadata['index_segments'] ?? []),
                'ref_constraints' => count($metadata['ref_constraints'] ?? []),
            ],
            'inspection' => $inspection,
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'stage' => 'firebird_source_inspection_smoke',
            'mutation_performed' => false,
            'errors' => [$throwable->getMessage()],
        ];
    }
}

/** @return array<string,mixed> */
function app_firebird_source_inspection_smoke_metadata(PDO $pdo): array
{
    $relations = app_firebird_source_inspection_smoke_rows($pdo, "
        SELECT
            TRIM(r.RDB\$RELATION_NAME) AS relation_name,
            COALESCE(r.RDB\$SYSTEM_FLAG, 0) AS system_flag
        FROM RDB\$RELATIONS r
        WHERE COALESCE(r.RDB\$SYSTEM_FLAG, 0) = 0
          AND r.RDB\$VIEW_BLR IS NULL
        ORDER BY r.RDB\$RELATION_NAME
    ");

    $fields = app_firebird_source_inspection_smoke_rows($pdo, "
        SELECT
            TRIM(rf.RDB\$RELATION_NAME) AS relation_name,
            TRIM(rf.RDB\$FIELD_NAME) AS field_name,
            rf.RDB\$FIELD_POSITION AS field_position,
            COALESCE(rf.RDB\$NULL_FLAG, 0) AS null_flag,
            rf.RDB\$DEFAULT_SOURCE AS default_source,
            f.RDB\$FIELD_TYPE AS field_type,
            COALESCE(f.RDB\$FIELD_SUB_TYPE, 0) AS field_sub_type,
            f.RDB\$FIELD_LENGTH AS field_length,
            f.RDB\$CHARACTER_LENGTH AS field_character_length,
            f.RDB\$FIELD_PRECISION AS field_precision,
            f.RDB\$FIELD_SCALE AS field_scale
        FROM RDB\$RELATION_FIELDS rf
        JOIN RDB\$FIELDS f ON f.RDB\$FIELD_NAME = rf.RDB\$FIELD_SOURCE
        JOIN RDB\$RELATIONS r ON r.RDB\$RELATION_NAME = rf.RDB\$RELATION_NAME
        WHERE COALESCE(r.RDB\$SYSTEM_FLAG, 0) = 0
          AND r.RDB\$VIEW_BLR IS NULL
        ORDER BY rf.RDB\$RELATION_NAME, rf.RDB\$FIELD_POSITION, rf.RDB\$FIELD_NAME
    ");
    foreach ($fields as &$field) $field = app_firebird_source_inspection_smoke_field_type($field);
    unset($field);

    $constraints = app_firebird_source_inspection_smoke_rows($pdo, "
        SELECT
            TRIM(rc.RDB\$RELATION_NAME) AS relation_name,
            TRIM(rc.RDB\$CONSTRAINT_NAME) AS constraint_name,
            TRIM(rc.RDB\$CONSTRAINT_TYPE) AS constraint_type,
            TRIM(rc.RDB\$INDEX_NAME) AS index_name
        FROM RDB\$RELATION_CONSTRAINTS rc
        JOIN RDB\$RELATIONS r ON r.RDB\$RELATION_NAME = rc.RDB\$RELATION_NAME
        WHERE COALESCE(r.RDB\$SYSTEM_FLAG, 0) = 0
          AND r.RDB\$VIEW_BLR IS NULL
        ORDER BY rc.RDB\$RELATION_NAME, rc.RDB\$CONSTRAINT_NAME
    ");

    $indexSegments = app_firebird_source_inspection_smoke_rows($pdo, "
        SELECT
            TRIM(s.RDB\$INDEX_NAME) AS index_name,
            TRIM(s.RDB\$FIELD_NAME) AS field_name,
            s.RDB\$FIELD_POSITION AS field_position
        FROM RDB\$INDEX_SEGMENTS s
        JOIN RDB\$INDICES i ON i.RDB\$INDEX_NAME = s.RDB\$INDEX_NAME
        JOIN RDB\$RELATIONS r ON r.RDB\$RELATION_NAME = i.RDB\$RELATION_NAME
        WHERE COALESCE(r.RDB\$SYSTEM_FLAG, 0) = 0
          AND r.RDB\$VIEW_BLR IS NULL
        ORDER BY s.RDB\$INDEX_NAME, s.RDB\$FIELD_POSITION
    ");

    $refConstraints = app_firebird_source_inspection_smoke_rows($pdo, "
        SELECT
            TRIM(RDB\$CONSTRAINT_NAME) AS constraint_name,
            TRIM(RDB\$CONST_NAME_UQ) AS referenced_constraint_name
        FROM RDB\$REF_CONSTRAINTS
        ORDER BY RDB\$CONSTRAINT_NAME
    ");

    return [
        'source_identity' => 'firebird-proof-db',
        'relations' => $relations,
        'fields' => $fields,
        'constraints' => $constraints,
        'index_segments' => $indexSegments,
        'ref_constraints' => $refConstraints,
        'row_counts' => app_firebird_source_inspection_smoke_row_counts($pdo, $relations),
        'value_profiles' => [],
    ];
}

/** @return list<array<string,mixed>> */
function app_firebird_source_inspection_smoke_rows(PDO $pdo, string $sql): array
{
    $statement = $pdo->query($sql);
    if (!$statement instanceof PDOStatement) return [];
    $rows = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $rows[] = array_change_key_case($row, CASE_LOWER);
    }
    return $rows;
}

/** @param array<string,mixed> $field @return array<string,mixed> */
function app_firebird_source_inspection_smoke_field_type(array $field): array
{
    $type = (int) ($field['field_type'] ?? 0);
    $subType = (int) ($field['field_sub_type'] ?? 0);
    $field['type_name'] = match ($type) {
        7 => 'SMALLINT',
        8 => 'INTEGER',
        10 => 'FLOAT',
        12 => 'DATE',
        13 => 'TIME',
        14 => 'CHAR',
        16 => in_array($subType, [1, 2], true) ? ($subType === 1 ? 'NUMERIC' : 'DECIMAL') : 'BIGINT',
        23 => 'BOOLEAN',
        26 => 'INT128',
        27 => 'DOUBLE',
        35 => 'TIMESTAMP',
        37 => 'VARCHAR',
        261 => 'BLOB',
        default => 'UNKNOWN',
    };
    return $field;
}

/** @param list<array<string,mixed>> $relations @return array<string,int> */
function app_firebird_source_inspection_smoke_row_counts(PDO $pdo, array $relations): array
{
    $counts = [];
    foreach ($relations as $relation) {
        $name = trim((string) ($relation['relation_name'] ?? ''));
        if ($name === '' || preg_match('/^[A-Z][A-Z0-9_]*$/', $name) !== 1) continue;
        $counts[strtolower($name)] = (int) $pdo->query('SELECT COUNT(*) FROM "' . $name . '"')->fetchColumn();
    }
    ksort($counts, SORT_STRING);
    return $counts;
}

$parsed = app_cli_firebird_source_inspection_smoke_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_firebird_source_inspection_smoke_usage() . PHP_EOL);
    exit(0);
}
if ($parsed['error'] !== '') {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_firebird_source_inspection_smoke_usage() . PHP_EOL);
    exit(64);
}

$result = app_firebird_source_inspection_smoke();
fwrite($result['ok'] ? STDOUT : STDERR, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | ($parsed['pretty'] ? JSON_PRETTY_PRINT : 0)) . PHP_EOL);
exit($result['ok'] ? 0 : 1);
