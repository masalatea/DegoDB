<?php

declare(strict_types=1);

function app_artifact_parity_usage(): string
{
    return <<<'TXT'
usage:
  php mtool/scripts/artifact_parity.php manifest --root=PATH --output=PATH [--lane=NAME]
  php mtool/scripts/artifact_parity.php compare --mysql=PATH --sqlite=PATH --output=PATH [--pretty]

TXT;
}

function app_artifact_parity_parse_options(array $argv): array
{
    $options = [
        'command' => (string) ($argv[1] ?? ''),
        'pretty' => false,
    ];

    foreach (array_slice($argv, 2) as $argument) {
        if ($argument === '--pretty') {
            $options['pretty'] = true;
            continue;
        }

        if (str_starts_with($argument, '--') && str_contains($argument, '=')) {
            [$key, $value] = explode('=', substr($argument, 2), 2);
            $options[$key] = $value;
            continue;
        }

        throw new InvalidArgumentException('unsupported argument: ' . $argument);
    }

    return $options;
}

function app_artifact_parity_normalize_path(string $path): string
{
    $normalized = rtrim($path, '/');
    return $normalized === '' ? '.' : $normalized;
}

/**
 * @return list<string>
 */
function app_artifact_parity_file_paths(string $root): array
{
    if (!is_dir($root)) {
        throw new RuntimeException('root directory not found: ' . $root);
    }

    $paths = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY,
    );

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        $absolutePath = $file->getPathname();
        $relativePath = ltrim(substr($absolutePath, strlen($root)), '/');
        if ($relativePath === '' || $relativePath === 'manifest.json') {
            continue;
        }

        $paths[] = $relativePath;
    }

    sort($paths, SORT_STRING);
    return $paths;
}

/**
 * @return array<string,mixed>
 */
function app_artifact_parity_file_entry(string $root, string $relativePath): array
{
    $absolutePath = $root . '/' . $relativePath;
    $contents = file_get_contents($absolutePath);
    if (!is_string($contents)) {
        throw new RuntimeException('file read failed: ' . $absolutePath);
    }

    $entry = [
        'path' => $relativePath,
        'size' => strlen($contents),
        'sha256' => hash('sha256', $contents),
        'comparison' => 'sha256',
    ];

    if (str_ends_with($relativePath, '.json')) {
        $decoded = json_decode($contents, true);
        if (is_array($decoded)) {
            $normalized = app_artifact_parity_normalize_json_payload($decoded, $relativePath);
            $encoded = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (is_string($encoded)) {
                $entry['normalized_sha256'] = hash('sha256', $encoded);
                $entry['comparison'] = 'normalized-json';
            }
        }
    }

    return $entry;
}

function app_artifact_parity_normalize_json_payload(mixed $value, string $relativePath = ''): mixed
{
    if (!is_array($value)) {
        return $value;
    }

    $volatileKeys = [
        'artifact_key' => true,
        'archive_filename' => true,
        'archive_path' => true,
        'bundle_entry_root' => true,
        'bundle_manifest_path' => true,
        'bundle_root' => true,
        'created_at' => true,
        'exported_at' => true,
        'generated_at' => true,
        'generated_catalog_summary' => true,
        'line' => true,
        'manifest_path' => true,
        'published_at' => true,
        'requested_by' => true,
        'updated_at' => true,
        'bundle_checksum' => true,
        'section_checksums' => true,
        'sha256' => true,
        'size' => true,
        'bytes' => true,
        'function_updated_at' => true,
        'target_updated_at' => true,
    ];

    $normalized = [];
    foreach ($value as $key => $item) {
        if (is_string($key) && isset($volatileKeys[$key])) {
            continue;
        }

        if (
            $key === 'id'
            && str_ends_with($relativePath, 'sample14-custom-proxy-runtime/CUSTOM-PROXY-SERVER/build-plan.json')
        ) {
            continue;
        }

        if (
            $key === 'datatype'
            && is_string($item)
            && str_contains($relativePath, 'sample15-project-metadata-export-import/PROJECT-METADATA-BUNDLE/')
        ) {
            $normalized[$key] = app_artifact_parity_normalize_sample15_datatype($item);
            continue;
        }

        $normalized[$key] = app_artifact_parity_normalize_json_payload($item, $relativePath);
    }

    return $normalized;
}

function app_artifact_parity_normalize_sample15_datatype(string $datatype): string
{
    $normalized = strtolower(trim($datatype));
    if ($normalized === '') {
        return '';
    }

    if (str_starts_with($normalized, 'bigint') || $normalized === 'integer' || $normalized === 'int') {
        return 'integer';
    }

    if (
        str_starts_with($normalized, 'varchar')
        || $normalized === 'text'
        || $normalized === 'datetime'
        || $normalized === 'string'
    ) {
        return 'string';
    }

    return $normalized;
}

/**
 * @return array<string,mixed>
 */
function app_artifact_parity_manifest(string $root, string $lane): array
{
    $normalizedRoot = app_artifact_parity_normalize_path($root);
    $files = [];
    foreach (app_artifact_parity_file_paths($normalizedRoot) as $relativePath) {
        $files[] = app_artifact_parity_file_entry($normalizedRoot, $relativePath);
    }

    return [
        'schema' => 'artifact-parity-manifest-v1',
        'lane' => $lane,
        'root' => $normalizedRoot,
        'file_count' => count($files),
        'files' => $files,
    ];
}

function app_artifact_parity_write_json(string $path, array $payload, bool $pretty): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
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

/**
 * @return array<string,array<string,mixed>>
 */
function app_artifact_parity_manifest_files_by_path(array $manifest): array
{
    $byPath = [];
    foreach (($manifest['files'] ?? []) as $file) {
        if (!is_array($file)) {
            continue;
        }

        $path = (string) ($file['path'] ?? '');
        if ($path === '') {
            continue;
        }

        $byPath[$path] = $file;
    }

    return $byPath;
}

/**
 * @return array<string,mixed>
 */
function app_artifact_parity_compare_manifests(array $mysqlManifest, array $sqliteManifest): array
{
    $mysqlFiles = app_artifact_parity_manifest_files_by_path($mysqlManifest);
    $sqliteFiles = app_artifact_parity_manifest_files_by_path($sqliteManifest);
    $paths = array_values(array_unique(array_merge(array_keys($mysqlFiles), array_keys($sqliteFiles))));
    sort($paths, SORT_STRING);

    $checks = [];
    $errors = [];
    foreach ($paths as $path) {
        $mysqlFile = $mysqlFiles[$path] ?? null;
        $sqliteFile = $sqliteFiles[$path] ?? null;
        $comparison = 'missing';
        $ok = false;
        $mysqlDigest = '';
        $sqliteDigest = '';

        if (!is_array($mysqlFile)) {
            $errors[] = 'missing in mysql lane: ' . $path;
        } elseif (!is_array($sqliteFile)) {
            $errors[] = 'missing in sqlite lane: ' . $path;
        } else {
            $comparison = (string) ($mysqlFile['comparison'] ?? 'sha256');
            if (
                $comparison === 'normalized-json'
                && (string) ($sqliteFile['comparison'] ?? '') === 'normalized-json'
            ) {
                $mysqlDigest = (string) ($mysqlFile['normalized_sha256'] ?? '');
                $sqliteDigest = (string) ($sqliteFile['normalized_sha256'] ?? '');
            } else {
                $comparison = 'sha256';
                $mysqlDigest = (string) ($mysqlFile['sha256'] ?? '');
                $sqliteDigest = (string) ($sqliteFile['sha256'] ?? '');
            }

            $ok = $mysqlDigest !== '' && $mysqlDigest === $sqliteDigest;
            if (!$ok) {
                $errors[] = 'artifact parity mismatch: ' . $path
                    . ' comparison=' . $comparison
                    . ' mysql=' . $mysqlDigest
                    . ' sqlite=' . $sqliteDigest;
            }
        }

        $checks[] = [
            'path' => $path,
            'comparison' => $comparison,
            'mysql_exists' => is_array($mysqlFile),
            'sqlite_exists' => is_array($sqliteFile),
            'mysql_digest' => $mysqlDigest,
            'sqlite_digest' => $sqliteDigest,
            'ok' => $ok,
        ];
    }

    return [
        'ok' => $errors === [],
        'mysql_root' => (string) ($mysqlManifest['root'] ?? ''),
        'sqlite_root' => (string) ($sqliteManifest['root'] ?? ''),
        'file_count' => count($paths),
        'checks' => $checks,
        'errors' => $errors,
    ];
}

try {
    $options = app_artifact_parity_parse_options($argv);
    $command = (string) ($options['command'] ?? '');
    $pretty = (bool) ($options['pretty'] ?? false);

    if ($command === 'manifest') {
        $root = (string) ($options['root'] ?? '');
        $output = (string) ($options['output'] ?? '');
        $lane = (string) ($options['lane'] ?? '');
        if ($root === '' || $output === '') {
            throw new InvalidArgumentException(app_artifact_parity_usage());
        }

        app_artifact_parity_write_json($output, app_artifact_parity_manifest($root, $lane), $pretty);
        exit(0);
    }

    if ($command === 'compare') {
        $mysqlManifestPath = (string) ($options['mysql'] ?? '');
        $sqliteManifestPath = (string) ($options['sqlite'] ?? '');
        $output = (string) ($options['output'] ?? '');
        if ($mysqlManifestPath === '' || $sqliteManifestPath === '' || $output === '') {
            throw new InvalidArgumentException(app_artifact_parity_usage());
        }

        $mysqlManifest = json_decode((string) file_get_contents($mysqlManifestPath), true);
        $sqliteManifest = json_decode((string) file_get_contents($sqliteManifestPath), true);
        if (!is_array($mysqlManifest) || !is_array($sqliteManifest)) {
            throw new RuntimeException('manifest JSON parse failed');
        }

        $result = app_artifact_parity_compare_manifests($mysqlManifest, $sqliteManifest);
        app_artifact_parity_write_json($output, $result, $pretty);
        fwrite(STDOUT, ($result['ok'] ? 'artifact parity OK' : 'artifact parity failed') . PHP_EOL);
        exit($result['ok'] ? 0 : 1);
    }

    throw new InvalidArgumentException(app_artifact_parity_usage());
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
