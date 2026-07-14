#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/config_db_bootstrap.php';

/**
 * @param list<string> $argv
 * @return array{help:bool,sql_dir:string,pretty:bool,include_issues:bool,max_issues:int,error:string}
 */
function app_cli_firebird_config_schema_preflight_parse_args(array $argv): array
{
    $parsed = [
        'help' => false,
        'sql_dir' => app_config_db_bootstrap_default_sql_dir(),
        'pretty' => false,
        'include_issues' => false,
        'max_issues' => 25,
        'error' => '',
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            $parsed['help'] = true;
            continue;
        }
        if ($argument === '--pretty') {
            $parsed['pretty'] = true;
            continue;
        }
        if ($argument === '--include-issues') {
            $parsed['include_issues'] = true;
            continue;
        }
        if (str_starts_with($argument, '--max-issues=')) {
            $parsed['max_issues'] = max(0, (int) substr($argument, strlen('--max-issues=')));
            continue;
        }
        if (str_starts_with($argument, '--sql-dir=')) {
            $parsed['sql_dir'] = app_config_db_bootstrap_resolve_sql_dir(substr($argument, strlen('--sql-dir=')));
            continue;
        }

        $parsed['error'] = 'unsupported argument: ' . $argument;
        return $parsed;
    }

    return $parsed;
}

function app_cli_firebird_config_schema_preflight_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/firebird_config_schema_preflight.php [--sql-dir=PATH] [--pretty] [--include-issues] [--max-issues=N]

Read-only Firebird config-store schema fit preflight.
It inspects the current MariaDB config-initdb SQL and reports Firebird conversion requirements.
TEXT;
}

/**
 * @return array<string,mixed>
 */
function app_firebird_config_schema_preflight(string $sqlDir, bool $includeIssues = false, int $maxIssues = 25): array
{
    $files = app_config_db_bootstrap_sql_files($sqlDir);
    $issues = [];
    $statementCount = 0;
    $createTableCount = 0;
    $alterCount = 0;
    $dropCount = 0;

    foreach ($files as $file) {
        $contents = file_get_contents($file);
        if (!is_string($contents)) {
            $issues[] = app_firebird_config_schema_preflight_issue('file_read_failed', $file, '', 'blocker');
            continue;
        }

        foreach (app_config_db_bootstrap_split_sql_statements($contents) as $statement) {
            $trimmed = trim(preg_replace('/^\s*--.*$/m', '', $statement) ?? $statement);
            if ($trimmed === '') {
                continue;
            }

            $statementCount++;
            if (preg_match('/^CREATE\s+TABLE\b/i', $trimmed) === 1) {
                $createTableCount++;
            } elseif (preg_match('/^ALTER\s+TABLE\b/i', $trimmed) === 1) {
                $alterCount++;
            } elseif (preg_match('/^DROP\b/i', $trimmed) === 1) {
                $dropCount++;
            }

            foreach (app_firebird_config_schema_preflight_statement_issues($trimmed) as $issue) {
                $issue['file'] = $file;
                $issue['statement_sha256'] = hash('sha256', $trimmed);
                $issue['statement_excerpt'] = substr(preg_replace('/\s+/', ' ', $trimmed) ?? $trimmed, 0, 220);
                $issues[] = $issue;
            }
        }
    }

    $byCode = [];
    $severityCounts = ['blocker' => 0, 'warning' => 0, 'info' => 0];
    foreach ($issues as $issue) {
        $code = (string) ($issue['code'] ?? 'unknown');
        $severity = (string) ($issue['severity'] ?? 'warning');
        $byCode[$code] = ($byCode[$code] ?? 0) + 1;
        $severityCounts[$severity] = ($severityCounts[$severity] ?? 0) + 1;
    }
    ksort($byCode, SORT_STRING);

    $result = [
        'ok' => $severityCounts['blocker'] === 0,
        'stage' => 'firebird_config_schema_preflight',
        'mutation_performed' => false,
        'sql_dir' => $sqlDir,
        'summary' => [
            'file_count' => count($files),
            'statement_count' => $statementCount,
            'create_table_count' => $createTableCount,
            'alter_table_count' => $alterCount,
            'drop_statement_count' => $dropCount,
            'issue_count' => count($issues),
            'severity_counts' => $severityCounts,
            'issue_counts_by_code' => $byCode,
        ],
        'recommended_first_slice' => [
            'scope' => 'read-only converter/preflight first, then generated Firebird DDL for disposable proof database',
            'must_handle' => [
                'identity columns or generators for AUTO_INCREMENT',
                'integer type normalization for UNSIGNED',
                'VARCHAR/TEXT/BLOB length policy',
                'timestamp default and updated_at policy without MySQL ON UPDATE',
                'ALTER ADD COLUMN IF NOT EXISTS replacement using metadata checks',
                'DROP COLUMN IF EXISTS replacement or skip policy for legacy cleanup statements',
                'identifier case policy',
            ],
            'must_not_claim_yet' => [
                'Firebird config-store support',
                'normal make test Firebird requirement',
                'generated DBAccess Firebird dialect support',
                'SQLite-to-Firebird promotion support',
            ],
        ],
    ];

    if ($includeIssues) {
        $result['issues'] = $issues;
    } else {
        $result['issue_sample'] = array_slice($issues, 0, $maxIssues);
        $result['issue_sample_note'] = 'Use --include-issues for the full issue list.';
    }

    return $result;
}

/**
 * @return list<array<string,mixed>>
 */
function app_firebird_config_schema_preflight_statement_issues(string $statement): array
{
    $checks = [
        'mysql_table_options' => ['/ENGINE\s*=\s*InnoDB|DEFAULT\s+CHARSET|COLLATE\s*=/i', 'blocker', 'Remove MySQL table options.'],
        'mysql_auto_increment' => ['/\bAUTO_INCREMENT\b/i', 'blocker', 'Map to Firebird identity column or generator policy.'],
        'mysql_unsigned_integer' => ['/\bUNSIGNED\b/i', 'blocker', 'Normalize integer ranges; Firebird has no UNSIGNED integer.'],
        'mysql_on_update_timestamp' => ['/\bON\s+UPDATE\s+CURRENT_TIMESTAMP\b/i', 'blocker', 'Replace with explicit application update or trigger policy.'],
        'mysql_alter_add_if_not_exists' => ['/\bALTER\s+TABLE\b.+\bADD\s+COLUMN\s+IF\s+NOT\s+EXISTS\b/is', 'blocker', 'Use Firebird metadata check before ALTER.'],
        'mysql_alter_after_position' => ['/\bAFTER\s+[A-Za-z_][A-Za-z0-9_]*\b/i', 'warning', 'Column position hint is MySQL-only and can be dropped.'],
        'mysql_drop_if_exists' => ['/\bDROP\s+COLUMN\s+IF\s+EXISTS\b/i', 'blocker', 'Use metadata check or skip legacy cleanup statement.'],
        'mysql_tinyint_boolean' => ['/\bTINYINT\s*\(\s*1\s*\)/i', 'warning', 'Map boolean-like fields to SMALLINT or BOOLEAN policy.'],
        'mysql_mediumtext' => ['/\bMEDIUMTEXT\b/i', 'warning', 'Map to BLOB SUB_TYPE TEXT or bounded VARCHAR policy.'],
        'mysql_text' => ['/\bTEXT\b/i', 'warning', 'Map to BLOB SUB_TYPE TEXT or bounded VARCHAR policy.'],
        'mysql_backtick_identifier' => ['/`/', 'blocker', 'Replace MySQL backtick identifiers with Firebird identifier policy.'],
        'mysql_insert_ignore' => ['/\bINSERT\s+IGNORE\b/i', 'blocker', 'Use explicit existence check or MERGE policy.'],
        'mysql_upsert' => ['/\bON\s+DUPLICATE\s+KEY\s+UPDATE\b/i', 'blocker', 'Use Firebird MERGE or explicit upsert policy.'],
        'mysql_session_variable' => ['/@[A-Za-z_][A-Za-z0-9_]*/', 'blocker', 'Replace MySQL session variables in seed/migration flow.'],
        'mysql_last_insert_id' => ['/\bLAST_INSERT_ID\s*\(/i', 'blocker', 'Use RETURNING or sequence/identity-specific retrieval.'],
        'mysql_datetime' => ['/\bDATETIME\b/i', 'warning', 'Map DATETIME to TIMESTAMP.'],
    ];

    $issues = [];
    foreach ($checks as $code => [$pattern, $severity, $message]) {
        if (preg_match($pattern, $statement) === 1) {
            $issues[] = [
                'code' => $code,
                'severity' => $severity,
                'message' => $message,
            ];
        }
    }

    return $issues;
}

/**
 * @return array{code:string,file:string,statement_sha256:string,severity:string,message:string,statement_excerpt:string}
 */
function app_firebird_config_schema_preflight_issue(string $code, string $file, string $message, string $severity): array
{
    return [
        'code' => $code,
        'file' => $file,
        'statement_sha256' => '',
        'severity' => $severity,
        'message' => $message,
        'statement_excerpt' => '',
    ];
}

$parsed = app_cli_firebird_config_schema_preflight_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_firebird_config_schema_preflight_usage() . PHP_EOL);
    exit(0);
}
if ($parsed['error'] !== '') {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_firebird_config_schema_preflight_usage() . PHP_EOL);
    exit(64);
}

$result = app_firebird_config_schema_preflight(
    $parsed['sql_dir'],
    $parsed['include_issues'],
    $parsed['max_issues'],
);
fwrite(
    $result['ok'] ? STDOUT : STDERR,
    json_encode(
        $result,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | ($parsed['pretty'] ? JSON_PRETTY_PRINT : 0),
    ) . PHP_EOL,
);

exit($result['ok'] ? 0 : 1);
