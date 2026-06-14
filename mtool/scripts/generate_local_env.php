#!/usr/bin/env php
<?php

declare(strict_types=1);

const APP_LOCAL_ENV_RANDOM_BYTES = 16;

/**
 * @return array<string,string>
 */
function app_local_env_secret_prefixes(): array
{
    return [
        'ADMIN_AUTH_STUB_PASSWORD' => 'admin_',
        'LAB_AUTH_STUB_PASSWORD' => 'lab_',
        'CONFIG_DB_ROOT_PASSWORD' => 'cfgroot_',
        'CONFIG_DB_PASSWORD' => 'cfgapp_',
        'LAB_DB_ROOT_PASSWORD' => 'labroot_',
        'LAB_DB_PASSWORD' => 'labapp_',
    ];
}

function app_local_env_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/generate_local_env.php [options]

Options:
  --output=PATH     output .env path (default: <repo>/.env)
  --template=PATH   template path (default: <repo>/.env.example)
  --force           overwrite an existing output file
  --help            show this help
TEXT;
}

/**
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     output:string,
 *     template:string,
 *     force:bool,
 *     error:string
 * }
 */
function app_local_env_parse_args(array $argv): array
{
    $repoRoot = dirname(__DIR__, 2);
    $parsed = [
        'ok' => true,
        'help' => false,
        'output' => $repoRoot . '/.env',
        'template' => $repoRoot . '/.env.example',
        'force' => false,
        'error' => '',
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            $parsed['help'] = true;

            return $parsed;
        }

        if ($argument === '--force') {
            $parsed['force'] = true;
            continue;
        }

        if (str_starts_with($argument, '--output=')) {
            $parsed['output'] = (string) substr($argument, strlen('--output='));
            continue;
        }

        if (str_starts_with($argument, '--template=')) {
            $parsed['template'] = (string) substr($argument, strlen('--template='));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'output' => '',
            'template' => '',
            'force' => false,
            'error' => 'unknown argument: ' . $argument,
        ];
    }

    if ($parsed['output'] === '') {
        $parsed['ok'] = false;
        $parsed['error'] = '--output=... is required.';

        return $parsed;
    }

    if ($parsed['template'] === '') {
        $parsed['ok'] = false;
        $parsed['error'] = '--template=... is required.';

        return $parsed;
    }

    if (!is_file($parsed['template'])) {
        $parsed['ok'] = false;
        $parsed['error'] = 'template file not found: ' . $parsed['template'];

        return $parsed;
    }

    return $parsed;
}

function app_local_env_random_secret(string $key): string
{
    $prefixes = app_local_env_secret_prefixes();
    $prefix = $prefixes[$key] ?? 'local_';

    return $prefix . bin2hex(random_bytes(APP_LOCAL_ENV_RANDOM_BYTES));
}

function app_local_env_render(string $templateContents): string
{
    $prefixes = app_local_env_secret_prefixes();
    $lines = preg_split("/\r\n|\n|\r/", $templateContents);
    if (!is_array($lines)) {
        throw new RuntimeException('failed to split template contents');
    }

    $renderedLines = [];
    $seenSecretKeys = [];
    foreach ($lines as $line) {
        if (preg_match('/^([A-Z0-9_]+)=(.*)$/', $line, $matches) !== 1) {
            $renderedLines[] = $line;
            continue;
        }

        $key = (string) $matches[1];
        if (!array_key_exists($key, $prefixes)) {
            $renderedLines[] = $line;
            continue;
        }

        $renderedLines[] = $key . '=' . app_local_env_random_secret($key);
        $seenSecretKeys[$key] = true;
    }

    $missingSecretKeys = array_values(
        array_diff(array_keys($prefixes), array_keys($seenSecretKeys)),
    );
    if ($missingSecretKeys !== []) {
        throw new RuntimeException(
            'template is missing secret keys: ' . implode(', ', $missingSecretKeys),
        );
    }

    return implode(PHP_EOL, $renderedLines) . PHP_EOL;
}

$parsed = app_local_env_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_local_env_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_local_env_usage() . PHP_EOL);
    exit(64);
}

if (is_file($parsed['output']) && !$parsed['force']) {
    fwrite(STDOUT, '.env already exists: ' . $parsed['output'] . PHP_EOL);
    exit(0);
}

$templateContents = file_get_contents($parsed['template']);
if (!is_string($templateContents)) {
    fwrite(STDERR, 'failed to read template: ' . $parsed['template'] . PHP_EOL);
    exit(1);
}

try {
    $renderedContents = app_local_env_render($templateContents);
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
}

$outputDirectory = dirname($parsed['output']);
if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0777, true) && !is_dir($outputDirectory)) {
    fwrite(STDERR, 'failed to create output directory: ' . $outputDirectory . PHP_EOL);
    exit(1);
}

if (file_put_contents($parsed['output'], $renderedContents) === false) {
    fwrite(STDERR, 'failed to write output file: ' . $parsed['output'] . PHP_EOL);
    exit(1);
}

@chmod($parsed['output'], 0600);

fwrite(STDOUT, 'wrote ' . $parsed['output'] . ' with random local passwords.' . PHP_EOL);
fwrite(STDOUT, 'if you rotate DB passwords, recreate DB volumes before the next startup.' . PHP_EOL);
