#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/ai_workspace_contract.php';

/**
 * @return array<string,string>
 */
function app_cli_ai_workspace_env(): array
{
    $workspaceRoot = getenv('MTOOL_AI_WORKSPACE_ROOT');
    if (!is_string($workspaceRoot) || $workspaceRoot === '') {
        return [];
    }
    return [
        'MTOOL_AI_WORKSPACE_ROOT' => $workspaceRoot,
    ];
}

/**
 * @param array<string,mixed> $payload
 */
function app_cli_ai_workspace_write_json(array $payload, bool $ok): void
{
    $stream = $ok ? STDOUT : STDERR;
    fwrite(
        $stream,
        json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ) . PHP_EOL,
    );
}

/**
 * @param list<string> $errors
 */
function app_cli_ai_workspace_write_errors(array $errors): void
{
    foreach ($errors as $error) {
        fwrite(STDERR, $error . PHP_EOL);
    }
}

/**
 * @param array<string,mixed> $preflight
 */
function app_cli_ai_workspace_text_summary(array $preflight, ?array $applyResult): string
{
    $lines = [
        'Mtool AI workspace initialization',
        'Command: ' . (string) ($preflight['command_name'] ?? 'mtool/scripts/init_ai_workspace.php'),
        'OK: ' . (($preflight['ok'] ?? false) === true ? 'yes' : 'no'),
        'Mode: ' . (string) ($preflight['initialization_preflight']['mode'] ?? 'dry-run'),
        'Workspace root: ' . (string) ($preflight['resolution']['workspace_root'] ?? ''),
        'Can apply: ' . (($preflight['can_run_apply'] ?? false) === true ? 'yes' : 'no'),
        'Filesystem writes in preflight: no',
    ];
    if ($applyResult !== null) {
        $lines[] = 'Apply OK: ' . (($applyResult['ok'] ?? false) === true ? 'yes' : 'no');
        $lines[] = 'Apply filesystem writes: ' . (($applyResult['filesystem_writes'] ?? false) === true ? 'yes' : 'no');
    }
    return implode(PHP_EOL, $lines) . PHP_EOL;
}

$preflight = app_ai_workspace_initialization_cli_entry_preflight($argv, app_cli_ai_workspace_env());
$options = is_array($preflight['parsed']['options'] ?? null) ? $preflight['parsed']['options'] : [];
$json = (bool) ($options['json'] ?? false);
$helpRequested = (bool) ($preflight['help_requested'] ?? false);

if ($helpRequested) {
    fwrite(STDOUT, (string) ($preflight['usage'] ?? app_ai_workspace_initialization_cli_usage()) . PHP_EOL);
    exit(0);
}

if (($preflight['ok'] ?? false) !== true) {
    if ($json) {
        app_cli_ai_workspace_write_json([
            'preflight' => $preflight,
            'apply' => null,
        ], false);
    } else {
        app_cli_ai_workspace_write_errors(app_ai_workspace_string_list($preflight['errors'] ?? []));
        fwrite(STDERR, PHP_EOL . (string) ($preflight['usage'] ?? app_ai_workspace_initialization_cli_usage()) . PHP_EOL);
    }
    exit(2);
}

$applyResult = null;
if (($preflight['can_run_apply'] ?? false) === true) {
    $applyResult = app_ai_workspace_initialization_apply(
        is_array($preflight['initialization_preflight'] ?? null) ? $preflight['initialization_preflight'] : [],
    );
}

$ok = $applyResult === null || (($applyResult['ok'] ?? false) === true);
if ($json) {
    app_cli_ai_workspace_write_json([
        'preflight' => $preflight,
        'apply' => $applyResult,
    ], $ok);
} else {
    fwrite($ok ? STDOUT : STDERR, app_cli_ai_workspace_text_summary($preflight, $applyResult));
}

exit($ok ? 0 : 3);

