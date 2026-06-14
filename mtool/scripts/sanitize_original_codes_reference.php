#!/usr/bin/env php
<?php

declare(strict_types=1);

function app_original_codes_sanitizer_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/sanitize_original_codes_reference.php [options]

Options:
  --root=PATH    対象ディレクトリ (default: <repo>/original-codes)
  --write        実際にファイルを書き換える
  --help         このヘルプを表示
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     root:string,
 *     write:bool,
 *     error:string
 * }
 */
function app_original_codes_sanitizer_parse_args(array $argv): array
{
    $repoRoot = dirname(__DIR__, 2);
    $root = $repoRoot . '/original-codes';
    $write = false;

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'root' => $root,
                'write' => $write,
                'error' => '',
            ];
        }

        if ($argument === '--write') {
            $write = true;
            continue;
        }

        if (str_starts_with($argument, '--root=')) {
            $root = (string) substr($argument, strlen('--root='));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'root' => '',
            'write' => false,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if ($root === '' || !is_dir($root)) {
        return [
            'ok' => false,
            'help' => false,
            'root' => '',
            'write' => false,
            'error' => '対象ディレクトリが見つかりません: ' . $root,
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'root' => $root,
        'write' => $write,
        'error' => '',
    ];
}

/**
 * @return list<string>
 */
function app_original_codes_text_extensions(): array
{
    return [
        'command',
        'css',
        'csv',
        'html',
        'ini',
        'java',
        'js',
        'json',
        'md',
        'php',
        'sh',
        'sql',
        'svg',
        'text',
        'tsv',
        'txt',
        'xml',
        'yaml',
        'yml',
    ];
}

function app_original_codes_is_supported_text_file(string $path): bool
{
    if (!is_file($path)) {
        return false;
    }

    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($extension === '') {
        return false;
    }

    return in_array($extension, app_original_codes_text_extensions(), true);
}

/**
 * @return list<string>
 */
function app_original_codes_collect_files(string $root): array
{
    $paths = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $root,
            FilesystemIterator::CURRENT_AS_FILEINFO
            | FilesystemIterator::KEY_AS_PATHNAME
            | FilesystemIterator::SKIP_DOTS,
        ),
    );

    foreach ($iterator as $path => $fileInfo) {
        if (!$fileInfo instanceof SplFileInfo) {
            continue;
        }

        $realPath = $fileInfo->getPathname();
        if (!app_original_codes_is_supported_text_file($realPath)) {
            continue;
        }

        $paths[] = $realPath;
    }

    sort($paths);

    return $paths;
}

/**
 * @param array<string,int> $stats
 */
function app_original_codes_increment_stat(array &$stats, string $key, int $count = 1): void
{
    if (!isset($stats[$key])) {
        $stats[$key] = 0;
    }

    $stats[$key] += $count;
}

/**
 * @param array<string,int> $stats
 */
function app_original_codes_replace_callback(
    string $line,
    string $pattern,
    callable $callback,
    array &$stats,
    string $statKey
): string {
    $count = 0;
    $result = preg_replace_callback(
        $pattern,
        /**
         * @param array<int,string> $matches
         */
        function (array $matches) use ($callback, &$count): string {
            $count++;

            return (string) $callback($matches);
        },
        $line,
    );
    if (!is_string($result)) {
        throw new RuntimeException('preg_replace_callback failed for pattern: ' . $pattern);
    }

    if ($count > 0) {
        app_original_codes_increment_stat($stats, $statKey, $count);
    }

    return $result;
}

/**
 * @param array<string,int> $stats
 */
function app_original_codes_replace_literal(
    string $line,
    string $pattern,
    string $replacement,
    array &$stats,
    string $statKey
): string {
    $count = 0;
    $result = preg_replace($pattern, $replacement, $line, -1, $count);
    if (!is_string($result)) {
        throw new RuntimeException('preg_replace failed for pattern: ' . $pattern);
    }

    if ($count > 0) {
        app_original_codes_increment_stat($stats, $statKey, $count);
    }

    return $result;
}

function app_original_codes_sanitize_email(string $email): string
{
    $lowerEmail = strtolower($email);
    if (str_ends_with($lowerEmail, '@example.invalid')) {
        return $email;
    }

    if ($lowerEmail === 'xxx@xxx.xxx') {
        return $email;
    }

    $localPart = strtolower((string) strtok($lowerEmail, '@'));
    if ($localPart === 'office') {
        return 'office@example.invalid';
    }

    if ($localPart === 'system') {
        return 'system@example.invalid';
    }

    if ($localPart === 'admin') {
        return 'admin@example.invalid';
    }

    if ($localPart === 'root') {
        return 'root@example.invalid';
    }

    return 'legacy-contact@example.invalid';
}

/**
 * @param array<string,int> $stats
 */
function app_original_codes_sanitize_line(string $line, array &$stats): string
{
    $line = app_original_codes_replace_callback(
        $line,
        '/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/',
        /**
         * @param array<int,string> $matches
         */
        static function (array $matches): string {
            return app_original_codes_sanitize_email($matches[0]);
        },
        $stats,
        'email',
    );

    $line = app_original_codes_replace_literal(
        $line,
        '/dbid:[A-Za-z0-9_-]+/',
        'dbid:legacy-account-redacted',
        $stats,
        'id.dbid',
    );
    $line = app_original_codes_replace_literal(
        $line,
        '/\bid:[A-Za-z0-9_-]+\b/',
        'id:legacy-object-redacted',
        $stats,
        'id.object',
    );
    $line = app_original_codes_replace_callback(
        $line,
        '/\b(legacy-user-[abc])_[A-Za-z0-9]{8,}\b/',
        /**
         * @param array<int,string> $matches
         */
        static function (array $matches): string {
            return $matches[1] . '_legacy-id-redacted';
        },
        $stats,
        'id.user_workspace_suffix',
    );

    $line = app_original_codes_replace_literal(
        $line,
        '/(\\\\\"TOKEN\\\\\"\s*:\s*\\\\\")[A-Za-z0-9]{16,}(\\\\\")/',
        '$1legacy-token-redacted$2',
        $stats,
        'token.escaped_json',
    );
    $line = app_original_codes_replace_literal(
        $line,
        '/(\\\\\"TOKEN\\\\\"\s*=>\s*\\\\\")[A-Za-z0-9]{16,}(\\\\\")/',
        '$1legacy-token-redacted$2',
        $stats,
        'token.escaped_php',
    );
    $line = app_original_codes_replace_literal(
        $line,
        '/(\\\\\"TOKEN\\\\\"__DELIMITER_FOR_CUSTOM_PROXY__\s*\\\\\")[A-Za-z0-9]{16,}(\\\\\")/',
        '$1legacy-token-redacted$2',
        $stats,
        'token.escaped_custom_proxy',
    );
    $line = app_original_codes_replace_literal(
        $line,
        '/("TOKEN"\s*:\s*")[A-Za-z0-9]{16,}(")/',
        '$1legacy-token-redacted$2',
        $stats,
        'token.json',
    );
    $line = app_original_codes_replace_literal(
        $line,
        '/("TOKEN"\s*=>\s*")[A-Za-z0-9]{16,}(")/',
        '$1legacy-token-redacted$2',
        $stats,
        'token.php',
    );
    $line = app_original_codes_replace_literal(
        $line,
        '/("TOKEN"__DELIMITER_FOR_CUSTOM_PROXY__\s*")[A-Za-z0-9]{16,}(")/',
        '$1legacy-token-redacted$2',
        $stats,
        'token.custom_proxy',
    );

    $line = app_original_codes_replace_literal(
        $line,
        "/(\\(\\d+,\\s*')[A-Za-z0-9_-]{24,}(',\\s*'\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}',\\s*\\d+\\),?)/",
        '$1legacy-hash-redacted$2',
        $stats,
        'id.hash_row',
    );
    $line = app_original_codes_replace_literal(
        $line,
        "/'([A-Za-z0-9_-]{48,})'/",
        "'legacy-opaque-id-redacted'",
        $stats,
        'id.opaque_single_quote',
    );

    return $line;
}

/**
 * @param array<string,int> $aggregateStats
 * @return array{changed:bool,replacements:int}
 */
function app_original_codes_process_file(string $path, bool $write, array &$aggregateStats): array
{
    $input = fopen($path, 'rb');
    if ($input === false) {
        throw new RuntimeException('failed to open input file: ' . $path);
    }

    $tempPath = $path . '.codex-sanitize-tmp';
    $output = $write ? fopen($tempPath, 'wb') : null;
    if ($write && $output === false) {
        fclose($input);
        throw new RuntimeException('failed to open temp file: ' . $tempPath);
    }

    $changed = false;
    $fileStats = [];
    while (($line = fgets($input)) !== false) {
        $sanitizedLine = app_original_codes_sanitize_line($line, $fileStats);
        if ($sanitizedLine !== $line) {
            $changed = true;
        }

        if ($write && is_resource($output) && fwrite($output, $sanitizedLine) === false) {
            fclose($input);
            fclose($output);
            @unlink($tempPath);
            throw new RuntimeException('failed to write temp file: ' . $tempPath);
        }
    }

    if (!feof($input)) {
        fclose($input);
        if (is_resource($output)) {
            fclose($output);
        }
        @unlink($tempPath);
        throw new RuntimeException('failed while reading file: ' . $path);
    }

    fclose($input);
    if (is_resource($output)) {
        fclose($output);
    }

    foreach ($fileStats as $statKey => $count) {
        app_original_codes_increment_stat($aggregateStats, $statKey, $count);
    }

    if (!$write) {
        return [
            'changed' => $changed,
            'replacements' => array_sum($fileStats),
        ];
    }

    if (!$changed) {
        @unlink($tempPath);

        return [
            'changed' => false,
            'replacements' => 0,
        ];
    }

    $mode = fileperms($path);
    if ($mode !== false) {
        @chmod($tempPath, $mode & 0777);
    }

    if (!rename($tempPath, $path)) {
        @unlink($tempPath);
        throw new RuntimeException('failed to replace file: ' . $path);
    }

    return [
        'changed' => true,
        'replacements' => array_sum($fileStats),
    ];
}

/**
 * @param array<string,int> $stats
 */
function app_original_codes_print_stats(array $stats): void
{
    ksort($stats);
    foreach ($stats as $key => $count) {
        fwrite(STDOUT, '  ' . $key . ': ' . $count . PHP_EOL);
    }
}

$parsed = app_original_codes_sanitizer_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_original_codes_sanitizer_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_original_codes_sanitizer_usage() . PHP_EOL);
    exit(64);
}

$paths = app_original_codes_collect_files($parsed['root']);
$changedFiles = [];
$stats = [];

foreach ($paths as $path) {
    $result = app_original_codes_process_file($path, $parsed['write'], $stats);
    if (!$result['changed']) {
        continue;
    }

    $changedFiles[] = [
        'path' => $path,
        'replacements' => $result['replacements'],
    ];
}

fwrite(
    STDOUT,
    ($parsed['write'] ? 'sanitized' : 'would sanitize')
    . ' ' . count($changedFiles)
    . ' file(s) under ' . $parsed['root'] . PHP_EOL,
);

if ($changedFiles !== []) {
    usort(
        $changedFiles,
        /**
         * @param array{path:string,replacements:int} $left
         * @param array{path:string,replacements:int} $right
         */
        static function (array $left, array $right): int {
            return strcmp($left['path'], $right['path']);
        },
    );

    foreach ($changedFiles as $changedFile) {
        fwrite(
            STDOUT,
            '  ' . $changedFile['path'] . ' (' . $changedFile['replacements'] . ' replacement(s))' . PHP_EOL,
        );
    }
}

if ($stats !== []) {
    fwrite(STDOUT, 'replacement stats:' . PHP_EOL);
    app_original_codes_print_stats($stats);
}

exit(0);
