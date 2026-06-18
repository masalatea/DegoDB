<?php

declare(strict_types=1);

function app_user_db_contract_normalize_path(string $path): string
{
    $normalized = rtrim($path, '/');
    return $normalized === '' ? '.' : $normalized;
}

/**
 * @return list<string>
 */
function app_user_db_contract_dbaccess_base_files(string $root): array
{
    $normalizedRoot = app_user_db_contract_normalize_path($root);
    $baseRoot = $normalizedRoot . '/base';
    if (!is_dir($baseRoot)) {
        throw new RuntimeException('DBAccess base directory not found: ' . $baseRoot);
    }

    $paths = glob($baseRoot . '/dbaccess-*Base.php') ?: [];
    $files = [];
    foreach ($paths as $path) {
        if (is_file($path)) {
            $files[] = $path;
        }
    }

    sort($files, SORT_STRING);
    return $files;
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_manifest(string $root, string $dialect, string $sample): array
{
    $normalizedRoot = app_user_db_contract_normalize_path($root);
    $classes = [];
    foreach (app_user_db_contract_dbaccess_base_files($normalizedRoot) as $path) {
        $classes[] = app_user_db_contract_parse_dbaccess_base_file($path, $normalizedRoot);
    }

    usort(
        $classes,
        static fn (array $a, array $b): int => strcmp((string) ($a['class'] ?? ''), (string) ($b['class'] ?? '')),
    );

    $manifest = [
        'schema' => 'user-db-contract-manifest-v1',
        'dialect' => $dialect,
        'sample' => $sample,
        'root' => $normalizedRoot,
        'class_count' => count($classes),
        'classes' => $classes,
    ];

    $runtimePath = dirname($normalizedRoot) . '/runtime.json';
    if (is_file($runtimePath)) {
        $runtime = json_decode((string) file_get_contents($runtimePath), true);
        if (is_array($runtime)) {
            unset($runtime['dialect']);
            $manifest['runtime'] = $runtime;
        }
    }

    return $manifest;
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_parse_dbaccess_base_file(string $path, string $root): array
{
    $source = file_get_contents($path);
    if (!is_string($source)) {
        throw new RuntimeException('failed to read DBAccess base file: ' . $path);
    }

    if (preg_match('/class\s+([A-Za-z_][A-Za-z0-9_]*)DBAccessBase\b/', $source, $classMatches) !== 1) {
        throw new RuntimeException('DBAccess base class not found: ' . $path);
    }

    $relativePath = ltrim(substr($path, strlen(app_user_db_contract_normalize_path($root))), '/');
    $methods = app_user_db_contract_parse_methods($source);

    return [
        'class' => $classMatches[1],
        'base_class' => $classMatches[1] . 'DBAccessBase',
        'path' => $relativePath,
        'method_count' => count($methods),
        'methods' => $methods,
    ];
}

/**
 * @return list<array<string,mixed>>
 */
function app_user_db_contract_parse_methods(string $source): array
{
    $pattern = '/(?P<comment>\/\/[^\n]*\n\s*)?public\s+function\s+(?P<name>[A-Za-z_][A-Za-z0-9_]*)\s*\((?P<args>[^)]*)\)\s*\{/m';
    if (preg_match_all($pattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) === false) {
        throw new RuntimeException('failed to parse DBAccess methods');
    }

    $methods = [];
    $methodCount = count($matches);
    for ($index = 0; $index < $methodCount; $index++) {
        $match = $matches[$index];
        $start = (int) $match[0][1];
        $end = $index + 1 < $methodCount ? (int) $matches[$index + 1][0][1] : strlen($source);
        $body = substr($source, $start, $end - $start);
        $comment = isset($match['comment'][0]) ? trim((string) $match['comment'][0]) : '';

        $methodName = (string) $match['name'][0];
        if ($methodName === '__construct') {
            continue;
        }

        $methods[] = [
            'name' => $methodName,
            'action_type' => app_user_db_contract_comment_value($comment, 'action_type'),
            'parameters' => app_user_db_contract_parse_parameters((string) $match['args'][0]),
            'sql' => app_user_db_contract_extract_sql($body),
            'binds' => app_user_db_contract_extract_binds($body),
            'result_fields' => app_user_db_contract_extract_result_fields($body),
            'cardinality' => app_user_db_contract_infer_cardinality($comment, $body),
        ];
    }

    return $methods;
}

function app_user_db_contract_comment_value(string $comment, string $key): string
{
    if (preg_match('/\b' . preg_quote($key, '/') . '=([A-Za-z0-9_-]+)/', $comment, $matches) !== 1) {
        return '';
    }

    return (string) $matches[1];
}

/**
 * @return list<array{name:string,default:string}>
 */
function app_user_db_contract_parse_parameters(string $args): array
{
    $args = trim($args);
    if ($args === '') {
        return [];
    }

    $parameters = [];
    foreach (explode(',', $args) as $rawArg) {
        $arg = trim($rawArg);
        if ($arg === '') {
            continue;
        }

        $default = '';
        if (str_contains($arg, '=')) {
            [$arg, $default] = explode('=', $arg, 2);
            $arg = trim($arg);
            $default = trim($default);
        }

        if (preg_match('/(\$[A-Za-z_][A-Za-z0-9_]*)$/', $arg, $matches) !== 1) {
            continue;
        }

        $parameters[] = [
            'name' => substr((string) $matches[1], 1),
            'default' => $default,
        ];
    }

    return $parameters;
}

function app_user_db_contract_extract_sql(string $body): string
{
    if (preg_match('/\$last_sql_command_for_mtooldb\s*=\s*(["\'])(.*?)\1\s*;/s', $body, $matches) !== 1) {
        return '';
    }

    return app_user_db_contract_normalize_sql(stripslashes((string) $matches[2]));
}

function app_user_db_contract_normalize_sql(string $sql): string
{
    $normalized = preg_replace('/\s+/', ' ', trim($sql));
    return is_string($normalized) ? $normalized : trim($sql);
}

/**
 * @return list<string>
 */
function app_user_db_contract_extract_binds(string $body): array
{
    if (preg_match('/->execute\s*\([^,]+,\s*\[(?P<binds>.*?)\]\s*\)/s', $body, $matches) !== 1) {
        return [];
    }

    $binds = [];
    foreach (explode("\n", (string) $matches['binds']) as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $line = rtrim($line, ',');
        $line = preg_replace('/\s+/', ' ', $line);
        if (is_string($line) && $line !== '') {
            $binds[] = $line;
        }
    }

    return $binds;
}

/**
 * @return list<array{name:string,index:int}>
 */
function app_user_db_contract_extract_result_fields(string $body): array
{
    if (preg_match_all('/\$thisresult->([A-Za-z_][A-Za-z0-9_]*)\s*=\s*\$thisline\[(\d+)\]\s*;/', $body, $matches, PREG_SET_ORDER) === false) {
        throw new RuntimeException('failed to parse result fields');
    }

    $fields = [];
    foreach ($matches as $match) {
        $fields[] = [
            'name' => (string) $match[1],
            'index' => (int) $match[2],
        ];
    }

    return $fields;
}

function app_user_db_contract_infer_cardinality(string $comment, string $body): string
{
    $actionType = app_user_db_contract_comment_value($comment, 'action_type');
    if (in_array($actionType, ['INSERT', 'UPDATE', 'DELETE'], true)) {
        return 'write-result';
    }

    if ($actionType === 'SELECTLIST') {
        return 'list';
    }

    if ($actionType === 'SELECTSINGLE') {
        return 'single';
    }

    if (str_contains($body, 'array_push($result')) {
        return 'list';
    }

    if (str_contains($body, 'return NULL')) {
        return 'single';
    }

    return 'unknown';
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_compare_manifests(array $leftManifest, array $rightManifest): array
{
    $leftComparable = app_user_db_contract_comparable_manifest($leftManifest);
    $rightComparable = app_user_db_contract_comparable_manifest($rightManifest);

    $leftDigest = app_user_db_contract_digest($leftComparable);
    $rightDigest = app_user_db_contract_digest($rightComparable);
    $errors = [];
    if ($leftDigest !== $rightDigest) {
        $errors[] = 'user DB contract mismatch: left=' . $leftDigest . ' right=' . $rightDigest;
    }

    return [
        'ok' => $errors === [],
        'schema' => 'user-db-contract-compare-v1',
        'left_dialect' => (string) ($leftManifest['dialect'] ?? ''),
        'right_dialect' => (string) ($rightManifest['dialect'] ?? ''),
        'sample' => (string) ($leftManifest['sample'] ?? $rightManifest['sample'] ?? ''),
        'left_digest' => $leftDigest,
        'right_digest' => $rightDigest,
        'class_count' => count($leftComparable['classes'] ?? []),
        'errors' => $errors,
    ];
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_comparable_manifest(array $manifest): array
{
    return [
        'schema' => 'user-db-contract-comparable-v1',
        'sample' => (string) ($manifest['sample'] ?? ''),
        'classes' => $manifest['classes'] ?? [],
        'runtime' => $manifest['runtime'] ?? null,
    ];
}

function app_user_db_contract_digest(array $payload): string
{
    $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($encoded)) {
        throw new RuntimeException('JSON encode failed: ' . json_last_error_msg());
    }

    return hash('sha256', $encoded);
}

function app_user_db_contract_write_json(string $path, array $payload, bool $pretty): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('failed to create directory: ' . $dir);
    }

    $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    if ($pretty) {
        $flags |= JSON_PRETTY_PRINT;
    }

    $encoded = json_encode($payload, $flags);
    if (!is_string($encoded)) {
        throw new RuntimeException('JSON encode failed: ' . json_last_error_msg());
    }

    file_put_contents($path, $encoded . "\n");
}
