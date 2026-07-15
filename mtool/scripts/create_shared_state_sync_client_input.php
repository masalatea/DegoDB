#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/shared_state_sync_client_input.php';

function app_cli_shared_state_sync_client_input_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/create_shared_state_sync_client_input.php --project-key=PROJECT --target-dir=work/source-outputs/PROJECT/SHARED-STATE-SYNC-CLIENT-INPUT

Options:
  --project-key=KEY          Project key to place in the packet. Default: PROJECT.
  --api-base-url-env=NAME    Backend API base URL environment variable. Default: APP_BACKEND_BASE_URL.
  --target-dir=DIR           Controlled artifact directory to create. Existing files are not overwritten.
  --help                     Show this help.

Boundary:
  Emits only sync-client-input.json and SYNC-CLIENT-INPUT.md.
  Does not generate SDKs, app source, token storage choices, SSO provider setup, or realtime runtime code.
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{ok:bool,help:bool,project_key:string,api_base_url_env:string,target_dir:string,error:string}
 */
function app_cli_shared_state_sync_client_input_parse_args(array $argv): array
{
    $projectKey = 'PROJECT';
    $apiBaseUrlEnv = 'APP_BACKEND_BASE_URL';
    $targetDir = '';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => $projectKey,
                'api_base_url_env' => $apiBaseUrlEnv,
                'target_dir' => $targetDir,
                'error' => '',
            ];
        }
        if (str_starts_with($argument, '--project-key=')) {
            $projectKey = trim(substr($argument, strlen('--project-key=')));
            continue;
        }
        if (str_starts_with($argument, '--api-base-url-env=')) {
            $apiBaseUrlEnv = trim(substr($argument, strlen('--api-base-url-env=')));
            continue;
        }
        if (str_starts_with($argument, '--target-dir=')) {
            $targetDir = trim(substr($argument, strlen('--target-dir=')));
            continue;
        }
        return [
            'ok' => false,
            'help' => false,
            'project_key' => $projectKey,
            'api_base_url_env' => $apiBaseUrlEnv,
            'target_dir' => $targetDir,
            'error' => 'unsupported argument: ' . $argument,
        ];
    }

    if ($projectKey === '') {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => $projectKey,
            'api_base_url_env' => $apiBaseUrlEnv,
            'target_dir' => $targetDir,
            'error' => 'valid --project-key is required',
        ];
    }
    if ($apiBaseUrlEnv === '') {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => $projectKey,
            'api_base_url_env' => $apiBaseUrlEnv,
            'target_dir' => $targetDir,
            'error' => 'valid --api-base-url-env is required',
        ];
    }
    if ($targetDir === '' || $targetDir === '.' || $targetDir === DIRECTORY_SEPARATOR) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => $projectKey,
            'api_base_url_env' => $apiBaseUrlEnv,
            'target_dir' => $targetDir,
            'error' => 'valid --target-dir is required',
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $projectKey,
        'api_base_url_env' => $apiBaseUrlEnv,
        'target_dir' => $targetDir,
        'error' => '',
    ];
}

/**
 * @param array{project_key:string,api_base_url_env:string,target_dir:string} $parsed
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,contract_errors:list<string>}
 */
function app_cli_shared_state_sync_client_input_emit_from_parsed(array $parsed): array
{
    return app_shared_state_sync_client_input_emit(
        [
            'project_key' => $parsed['project_key'],
            'api_base_url_env' => $parsed['api_base_url_env'],
        ],
        $parsed['target_dir'],
    );
}

/** @param list<string> $argv */
function app_cli_shared_state_sync_client_input_main(array $argv): int
{
    $parsed = app_cli_shared_state_sync_client_input_parse_args($argv);
    if ($parsed['help']) {
        fwrite(STDOUT, app_cli_shared_state_sync_client_input_usage() . PHP_EOL);
        return 0;
    }
    if (!$parsed['ok']) {
        fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_shared_state_sync_client_input_usage() . PHP_EOL);
        return 64;
    }

    $result = app_cli_shared_state_sync_client_input_emit_from_parsed($parsed);
    $summary = $result + [
        'project_key' => $parsed['project_key'],
        'api_base_url_env' => $parsed['api_base_url_env'],
        'artifact' => 'shared_state_sync_client_input',
    ];
    $stream = $result['ok'] ? STDOUT : STDERR;
    fwrite($stream, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
    return $result['ok'] ? 0 : 1;
}

if (PHP_SAPI === 'cli' && realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    exit(app_cli_shared_state_sync_client_input_main($argv));
}
