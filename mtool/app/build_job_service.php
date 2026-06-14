<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/project_output_service.php';
require_once __DIR__ . '/runtime_storage_paths.php';

function app_lab_build_path(
    string $projectKey,
    string $releaseTargetType = '',
    string $view = 'summary',
): string {
    $path = '/runs/builds/' . rawurlencode(app_normalize_project_key($projectKey));
    $query = [];
    $normalizedReleaseTargetType = app_build_job_normalize_release_target_filter($releaseTargetType);
    if ($normalizedReleaseTargetType !== '') {
        $query['release_target_type'] = $normalizedReleaseTargetType;
    }

    $normalizedView = app_build_job_requested_view($view);
    if ($normalizedView === 'detailed') {
        $query['view'] = 'detailed';
    }

    if ($query !== []) {
        $path .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    return $path;
}

function app_lab_build_job_path(string $jobKey): string
{
    return '/runs/builds/' . rawurlencode($jobKey);
}

function app_lab_build_job_api_path(string $jobKey): string
{
    return '/api/runs/builds/' . rawurlencode($jobKey);
}

function app_build_job_storage_root(array $app, string $projectKey = ''): string
{
    return app_runtime_storage_build_jobs_root($app, $projectKey);
}

function app_build_job_key_is_valid(string $jobKey): bool
{
    return preg_match('/^[0-9]{8}-[0-9]{6}-[a-f0-9]{8}$/', $jobKey) === 1;
}

function app_build_new_job_key(): string
{
    return date('Ymd-His') . '-' . bin2hex(random_bytes(4));
}

function app_build_job_requested_view(string $value): string
{
    return trim($value) === 'detailed' ? 'detailed' : 'summary';
}

function app_build_job_normalize_release_target_filter(string $value): string
{
    $normalized = trim($value);
    if ($normalized === '' || !in_array($normalized, app_allowed_source_output_release_target_types(), true)) {
        return 'Release';
    }

    return $normalized;
}

/**
 * @param mixed $input
 * @return list<string>
 */
function app_build_job_normalize_selected_source_output_keys(mixed $input): array
{
    if (!is_array($input)) {
        return [];
    }

    $keys = [];
    foreach ($input as $value) {
        if (!is_string($value)) {
            continue;
        }

        $normalized = app_normalize_source_output_key($value);
        if ($normalized === '' || !app_source_output_key_is_valid($normalized)) {
            continue;
        }

        $keys[$normalized] = $normalized;
    }

    return array_values($keys);
}

/**
 * @param list<array<string,mixed>> $catalog
 * @return list<array<string,mixed>>
 */
function app_build_job_supported_source_outputs(array $catalog): array
{
    return array_values(array_filter(
        $catalog,
        static function (array $sourceOutput): bool {
            $artifactStrategy = is_string($sourceOutput['artifact_strategy'] ?? null)
                ? $sourceOutput['artifact_strategy']
                : '';

            return app_source_output_artifact_strategy_supports_generation($artifactStrategy);
        },
    ));
}

/**
 * @param list<array<string,mixed>> $catalog
 * @return list<string>
 */
function app_build_job_default_selected_source_output_keys(
    array $catalog,
    string $releaseTargetType = 'Release',
): array {
    $normalizedReleaseTargetType = app_build_job_normalize_release_target_filter($releaseTargetType);
    $buildable = app_build_job_supported_source_outputs($catalog);

    $selectedKeys = [];
    foreach ($buildable as $sourceOutput) {
        $sourceOutputKey = is_string($sourceOutput['source_output_key'] ?? null)
            ? app_normalize_source_output_key($sourceOutput['source_output_key'])
            : '';
        $sourceOutputReleaseTargetType = is_string($sourceOutput['release_target_type'] ?? null)
            ? trim($sourceOutput['release_target_type'])
            : '';
        if ($sourceOutputKey === '' || !app_source_output_key_is_valid($sourceOutputKey)) {
            continue;
        }

        if ($sourceOutputReleaseTargetType !== $normalizedReleaseTargetType) {
            continue;
        }

        $selectedKeys[] = $sourceOutputKey;
    }

    if ($selectedKeys !== []) {
        return $selectedKeys;
    }

    foreach ($buildable as $sourceOutput) {
        $sourceOutputKey = is_string($sourceOutput['source_output_key'] ?? null)
            ? app_normalize_source_output_key($sourceOutput['source_output_key'])
            : '';
        if ($sourceOutputKey === '' || !app_source_output_key_is_valid($sourceOutputKey)) {
            continue;
        }

        $selectedKeys[] = $sourceOutputKey;
    }

    return $selectedKeys;
}

function app_build_job_cli_command(
    string $projectKey,
    string $sourceOutputKey,
    string $requestedBy = 'manual',
    string $container = 'web-admin',
): string {
    return 'docker compose exec -T ' . trim($container)
        . ' php /var/www/mtool/scripts/create_project_output.php'
        . ' --project-key=' . app_normalize_project_key($projectKey)
        . ' --source-output-key=' . app_normalize_source_output_key($sourceOutputKey)
        . ' --requested-by=' . app_project_output_normalize_requested_by($requestedBy)
        . ' --publish';
}

function app_build_job_format_bytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $value = (float) $bytes;
    $unitIndex = 0;

    while ($value >= 1024 && $unitIndex < count($units) - 1) {
        $value /= 1024;
        $unitIndex++;
    }

    if ($unitIndex === 0) {
        return (string) $bytes . ' ' . $units[$unitIndex];
    }

    return number_format($value, 1) . ' ' . $units[$unitIndex];
}

function app_build_job_status_caption(string $status): string
{
    return match ($status) {
        'completed' => 'completed',
        'partial' => 'partial success',
        'failed' => 'failed',
        default => $status,
    };
}

function app_build_job_entry_status_caption(string $status): string
{
    return match ($status) {
        'completed' => 'published',
        'artifact-created' => 'artifact only',
        'failed' => 'failed',
        'publish-failed' => 'publish failed',
        default => $status,
    };
}

/**
 * @param array<mixed> $payload
 */
function app_build_job_write_json_file(string $path, array $payload): void
{
    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );

    if (!is_string($json) || $json === '') {
        throw new RuntimeException('build job manifest JSON の生成に失敗しました。');
    }

    app_project_output_ensure_directory(dirname($path));
    if (file_put_contents($path, $json . PHP_EOL) === false) {
        throw new RuntimeException('build job manifest の保存に失敗しました: ' . $path);
    }
}

/**
 * @param array{
 *     job_key:string,
 *     project_key:string,
 *     requested_by:string,
 *     created_at:string,
 *     status:string,
 *     publish_requested:bool,
 *     view:string,
 *     release_target_type_filter:string,
 *     selected_source_output_keys:list<string>,
 *     selected_source_output_count:int,
 *     successful_count:int,
 *     failed_count:int,
 *     artifact_count:int,
 *     published_count:int,
 *     errors:list<string>,
 *     entries:list<array{
 *         source_output_key:string,
 *         source_output_name:string,
 *         source_output_program_language:string,
 *         class_type:string,
 *         release_target_type:string,
 *         artifact_strategy:string,
 *         status:string,
 *         error:string,
 *         artifact_key:string,
 *         artifact_created_at:string,
 *         archive_path:string,
 *         archive_size:int,
 *         manifest_path:string,
 *         source_file_count:int,
 *         source_total_bytes:int,
 *         source_output_dir:string,
 *         published_root:string,
 *         published_file_count:int,
 *         published_total_bytes:int,
 *         published_at:string
 *     }>
 * } $item
 * @return array<mixed>
 */
function app_build_job_manifest_from_item(array $item): array
{
    return [
        'schema_version' => 1,
        'artifact_type' => 'build-job',
        'job_key' => $item['job_key'],
        'project_key' => $item['project_key'],
        'requested_by' => $item['requested_by'],
        'created_at' => $item['created_at'],
        'status' => $item['status'],
        'publish_requested' => $item['publish_requested'],
        'view' => $item['view'],
        'release_target_type_filter' => $item['release_target_type_filter'],
        'selected_source_output_keys' => $item['selected_source_output_keys'],
        'selected_source_output_count' => $item['selected_source_output_count'],
        'successful_count' => $item['successful_count'],
        'failed_count' => $item['failed_count'],
        'artifact_count' => $item['artifact_count'],
        'published_count' => $item['published_count'],
        'errors' => $item['errors'],
        'entries' => $item['entries'],
    ];
}

/**
 * @param list<array<string,mixed>> $sourceOutputs
 * @param array{
 *     publish_requested?:bool,
 *     view?:string,
 *     release_target_type_filter?:string
 * } $options
 * @return array{
 *     ok:bool,
 *     job:array{
 *         job_key:string,
 *         project_key:string,
 *         requested_by:string,
 *         created_at:string,
 *         status:string,
 *         publish_requested:bool,
 *         view:string,
 *         release_target_type_filter:string,
 *         selected_source_output_keys:list<string>,
 *         selected_source_output_count:int,
 *         successful_count:int,
 *         failed_count:int,
 *         artifact_count:int,
 *         published_count:int,
 *         errors:list<string>,
 *         entries:list<array{
 *             source_output_key:string,
 *             source_output_name:string,
 *             source_output_program_language:string,
 *             class_type:string,
 *             release_target_type:string,
 *             artifact_strategy:string,
 *             status:string,
 *             error:string,
 *             artifact_key:string,
 *             artifact_created_at:string,
 *             archive_path:string,
 *             archive_size:int,
 *             manifest_path:string,
 *             source_file_count:int,
 *             source_total_bytes:int,
 *             source_output_dir:string,
 *             published_root:string,
 *             published_file_count:int,
 *             published_total_bytes:int,
 *             published_at:string
 *         }>,
 *         job_dir:string,
 *         manifest_path:string
 *     }|null,
 *     error:string
 * }
 */
function app_build_job_create(
    array $app,
    string $projectKey,
    array $sourceOutputs,
    string $requestedBy = 'system',
    array $options = [],
): array {
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        return [
            'ok' => false,
            'job' => null,
            'error' => 'project key の形式が不正です。',
        ];
    }

    $definitions = [];
    $selectedSourceOutputKeys = [];
    foreach ($sourceOutputs as $sourceOutput) {
        if (!is_array($sourceOutput)) {
            continue;
        }

        $definition = app_project_output_merge_source_output_definition($normalizedProjectKey, $sourceOutput);
        if ($definition['source_output_key'] === '' || !app_source_output_key_is_valid($definition['source_output_key'])) {
            continue;
        }

        $definitions[$definition['source_output_key']] = $definition;
        $selectedSourceOutputKeys[] = $definition['source_output_key'];
    }

    if ($definitions === []) {
        return [
            'ok' => false,
            'job' => null,
            'error' => 'build 対象の source output が選択されていません。',
        ];
    }

    $publishRequested = (bool) ($options['publish_requested'] ?? true);
    $view = app_build_job_requested_view((string) ($options['view'] ?? 'summary'));
    $releaseTargetTypeFilter = app_build_job_normalize_release_target_filter(
        (string) ($options['release_target_type_filter'] ?? 'Release'),
    );
    $normalizedRequestedBy = app_project_output_normalize_requested_by($requestedBy);
    $jobKey = app_build_new_job_key();
    $jobDir = app_build_job_storage_root($app, $normalizedProjectKey) . '/' . $jobKey;
    $manifestPath = $jobDir . '/manifest.json';
    $entries = [];
    $errors = [];
    $successfulCount = 0;
    $failedCount = 0;
    $artifactCount = 0;
    $publishedCount = 0;
    $createdAt = date(DATE_ATOM);

    try {
        app_project_output_ensure_directory($jobDir);

        foreach ($definitions as $definition) {
            $entry = [
                'source_output_key' => $definition['source_output_key'],
                'source_output_name' => $definition['name'],
                'source_output_program_language' => $definition['program_language'],
                'class_type' => $definition['class_type'],
                'release_target_type' => $definition['release_target_type'],
                'artifact_strategy' => $definition['artifact_strategy'],
                'status' => 'failed',
                'error' => '',
                'artifact_key' => '',
                'artifact_created_at' => '',
                'archive_path' => '',
                'archive_size' => 0,
                'manifest_path' => '',
                'source_file_count' => 0,
                'source_total_bytes' => 0,
                'source_output_dir' => $definition['source_output_dir'],
                'published_root' => '',
                'published_file_count' => 0,
                'published_total_bytes' => 0,
                'published_at' => '',
            ];

            if (!app_source_output_artifact_strategy_supports_generation($definition['artifact_strategy'])) {
                $entry['error'] = 'この source output は artifact 生成に未対応です。';
                $failedCount++;
                $errors[] = $definition['source_output_key'] . ': ' . $entry['error'];
                $entries[] = $entry;
                continue;
            }

            $createResult = app_project_output_create_from_definition(
                $app,
                $normalizedProjectKey,
                $definition,
                $normalizedRequestedBy,
            );
            if (!$createResult['ok'] || !is_array($createResult['artifact'])) {
                $entry['error'] = $createResult['error'];
                $failedCount++;
                $errors[] = $definition['source_output_key'] . ': ' . $entry['error'];
                $entries[] = $entry;
                continue;
            }

            $artifact = $createResult['artifact'];
            $entry['artifact_key'] = (string) ($artifact['artifact_key'] ?? '');
            $entry['artifact_created_at'] = (string) ($artifact['created_at'] ?? '');
            $entry['archive_path'] = (string) ($artifact['archive_path'] ?? '');
            $entry['archive_size'] = (int) ($artifact['archive_size'] ?? 0);
            $entry['manifest_path'] = (string) ($artifact['manifest_path'] ?? '');
            $entry['source_file_count'] = (int) ($artifact['source_file_count'] ?? 0);
            $entry['source_total_bytes'] = (int) ($artifact['source_total_bytes'] ?? 0);
            $artifactCount++;

            if (!$publishRequested) {
                $entry['status'] = 'artifact-created';
                $successfulCount++;
                $entries[] = $entry;
                continue;
            }

            $publishResult = app_project_output_publish_artifact($app, $artifact, $definition);
            if (!$publishResult['ok'] || !is_array($publishResult['published'])) {
                $entry['status'] = 'publish-failed';
                $entry['error'] = $publishResult['error'];
                $failedCount++;
                $errors[] = $definition['source_output_key'] . ': ' . $entry['error'];
                $entries[] = $entry;
                continue;
            }

            $published = $publishResult['published'];
            $entry['status'] = 'completed';
            $entry['published_root'] = (string) ($published['published_root'] ?? '');
            $entry['published_file_count'] = (int) ($published['published_file_count'] ?? 0);
            $entry['published_total_bytes'] = (int) ($published['published_total_bytes'] ?? 0);
            $entry['published_at'] = (string) ($published['published_at'] ?? '');
            $successfulCount++;
            $publishedCount++;
            $entries[] = $entry;
        }

        $status = 'completed';
        if ($failedCount > 0 && $successfulCount > 0) {
            $status = 'partial';
        } elseif ($failedCount > 0) {
            $status = 'failed';
        }

        $job = [
            'job_key' => $jobKey,
            'project_key' => $normalizedProjectKey,
            'requested_by' => $normalizedRequestedBy,
            'created_at' => $createdAt,
            'status' => $status,
            'publish_requested' => $publishRequested,
            'view' => $view,
            'release_target_type_filter' => $releaseTargetTypeFilter,
            'selected_source_output_keys' => array_values(array_unique($selectedSourceOutputKeys)),
            'selected_source_output_count' => count($definitions),
            'successful_count' => $successfulCount,
            'failed_count' => $failedCount,
            'artifact_count' => $artifactCount,
            'published_count' => $publishedCount,
            'errors' => $errors,
            'entries' => $entries,
            'job_dir' => $jobDir,
            'manifest_path' => $manifestPath,
        ];

        app_build_job_write_json_file($manifestPath, app_build_job_manifest_from_item($job));

        return [
            'ok' => true,
            'job' => $job,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        app_project_output_delete_tree($jobDir);

        return [
            'ok' => false,
            'job' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array<mixed>|null
 */
function app_build_job_read_manifest(string $manifestPath): ?array
{
    if (!is_file($manifestPath)) {
        return null;
    }

    $json = file_get_contents($manifestPath);
    if (!is_string($json) || $json === '') {
        return null;
    }

    $decoded = json_decode($json, true);
    if (!is_array($decoded)) {
        return null;
    }

    return $decoded;
}

/**
 * @param array<mixed> $entry
 * @return array{
 *     source_output_key:string,
 *     source_output_name:string,
 *     source_output_program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     artifact_strategy:string,
 *     status:string,
 *     error:string,
 *     artifact_key:string,
 *     artifact_created_at:string,
 *     archive_path:string,
 *     archive_size:int,
 *     manifest_path:string,
 *     source_file_count:int,
 *     source_total_bytes:int,
 *     source_output_dir:string,
 *     published_root:string,
 *     published_file_count:int,
 *     published_total_bytes:int,
 *     published_at:string
 * }|null
 */
function app_build_job_entry_from_manifest(array $entry): ?array
{
    $sourceOutputKey = $entry['source_output_key'] ?? null;
    $sourceOutputName = $entry['source_output_name'] ?? null;
    $programLanguage = $entry['source_output_program_language'] ?? null;
    $classType = $entry['class_type'] ?? null;
    $releaseTargetType = $entry['release_target_type'] ?? null;
    $artifactStrategy = $entry['artifact_strategy'] ?? null;
    $status = $entry['status'] ?? null;
    $error = $entry['error'] ?? null;
    $artifactKey = $entry['artifact_key'] ?? null;
    $artifactCreatedAt = $entry['artifact_created_at'] ?? null;
    $archivePath = $entry['archive_path'] ?? null;
    $archiveSize = $entry['archive_size'] ?? null;
    $manifestPath = $entry['manifest_path'] ?? null;
    $sourceFileCount = $entry['source_file_count'] ?? null;
    $sourceTotalBytes = $entry['source_total_bytes'] ?? null;
    $sourceOutputDir = $entry['source_output_dir'] ?? null;
    $publishedRoot = $entry['published_root'] ?? null;
    $publishedFileCount = $entry['published_file_count'] ?? null;
    $publishedTotalBytes = $entry['published_total_bytes'] ?? null;
    $publishedAt = $entry['published_at'] ?? null;

    if (
        !is_string($sourceOutputKey) || !app_source_output_key_is_valid(app_normalize_source_output_key($sourceOutputKey))
        || !is_string($sourceOutputName) || $sourceOutputName === ''
        || !is_string($programLanguage)
        || !is_string($classType)
        || !is_string($releaseTargetType)
        || !is_string($artifactStrategy)
        || !is_string($status) || $status === ''
        || !is_string($error)
        || !is_string($artifactKey)
        || !is_string($artifactCreatedAt)
        || !is_string($archivePath)
        || !is_int($archiveSize)
        || !is_string($manifestPath)
        || !is_int($sourceFileCount)
        || !is_int($sourceTotalBytes)
        || !is_string($sourceOutputDir)
        || !is_string($publishedRoot)
        || !is_int($publishedFileCount)
        || !is_int($publishedTotalBytes)
        || !is_string($publishedAt)
    ) {
        return null;
    }

    return [
        'source_output_key' => app_normalize_source_output_key($sourceOutputKey),
        'source_output_name' => $sourceOutputName,
        'source_output_program_language' => $programLanguage,
        'class_type' => $classType,
        'release_target_type' => $releaseTargetType,
        'artifact_strategy' => $artifactStrategy,
        'status' => $status,
        'error' => $error,
        'artifact_key' => $artifactKey,
        'artifact_created_at' => $artifactCreatedAt,
        'archive_path' => $archivePath,
        'archive_size' => $archiveSize,
        'manifest_path' => $manifestPath,
        'source_file_count' => $sourceFileCount,
        'source_total_bytes' => $sourceTotalBytes,
        'source_output_dir' => $sourceOutputDir,
        'published_root' => $publishedRoot,
        'published_file_count' => $publishedFileCount,
        'published_total_bytes' => $publishedTotalBytes,
        'published_at' => $publishedAt,
    ];
}

/**
 * @param array<mixed> $manifest
 * @return array{
 *     job_key:string,
 *     project_key:string,
 *     requested_by:string,
 *     created_at:string,
 *     status:string,
 *     publish_requested:bool,
 *     view:string,
 *     release_target_type_filter:string,
 *     selected_source_output_keys:list<string>,
 *     selected_source_output_count:int,
 *     successful_count:int,
 *     failed_count:int,
 *     artifact_count:int,
 *     published_count:int,
 *     errors:list<string>,
 *     entries:list<array{
 *         source_output_key:string,
 *         source_output_name:string,
 *         source_output_program_language:string,
 *         class_type:string,
 *         release_target_type:string,
 *         artifact_strategy:string,
 *         status:string,
 *         error:string,
 *         artifact_key:string,
 *         artifact_created_at:string,
 *         archive_path:string,
 *         archive_size:int,
 *         manifest_path:string,
 *         source_file_count:int,
 *         source_total_bytes:int,
 *         source_output_dir:string,
 *         published_root:string,
 *         published_file_count:int,
 *         published_total_bytes:int,
 *         published_at:string
 *     }>,
 *     job_dir:string,
 *     manifest_path:string
 * }|null
 */
function app_build_job_item_from_manifest(string $jobDir, array $manifest): ?array
{
    $jobKey = $manifest['job_key'] ?? null;
    $projectKey = $manifest['project_key'] ?? null;
    $requestedBy = $manifest['requested_by'] ?? null;
    $createdAt = $manifest['created_at'] ?? null;
    $status = $manifest['status'] ?? null;
    $publishRequested = $manifest['publish_requested'] ?? null;
    $view = $manifest['view'] ?? null;
    $releaseTargetTypeFilter = $manifest['release_target_type_filter'] ?? null;
    $selectedSourceOutputKeys = $manifest['selected_source_output_keys'] ?? null;
    $selectedSourceOutputCount = $manifest['selected_source_output_count'] ?? null;
    $successfulCount = $manifest['successful_count'] ?? null;
    $failedCount = $manifest['failed_count'] ?? null;
    $artifactCount = $manifest['artifact_count'] ?? null;
    $publishedCount = $manifest['published_count'] ?? null;
    $errors = $manifest['errors'] ?? null;
    $entries = $manifest['entries'] ?? null;

    if (
        !is_string($jobKey) || !app_build_job_key_is_valid($jobKey)
        || !is_string($projectKey) || !app_project_key_is_valid($projectKey)
        || !is_string($requestedBy) || $requestedBy === ''
        || !is_string($createdAt) || $createdAt === ''
        || !is_string($status) || $status === ''
        || !is_bool($publishRequested)
        || !is_string($view)
        || !is_string($releaseTargetTypeFilter)
        || !is_array($selectedSourceOutputKeys)
        || !is_int($selectedSourceOutputCount)
        || !is_int($successfulCount)
        || !is_int($failedCount)
        || !is_int($artifactCount)
        || !is_int($publishedCount)
        || !is_array($errors)
        || !is_array($entries)
    ) {
        return null;
    }

    $normalizedSelectedSourceOutputKeys = [];
    foreach ($selectedSourceOutputKeys as $sourceOutputKey) {
        if (!is_string($sourceOutputKey)) {
            return null;
        }

        $normalizedSourceOutputKey = app_normalize_source_output_key($sourceOutputKey);
        if ($normalizedSourceOutputKey === '' || !app_source_output_key_is_valid($normalizedSourceOutputKey)) {
            return null;
        }

        $normalizedSelectedSourceOutputKeys[] = $normalizedSourceOutputKey;
    }

    $normalizedErrors = [];
    foreach ($errors as $error) {
        if (!is_string($error)) {
            return null;
        }

        $normalizedErrors[] = $error;
    }

    $normalizedEntries = [];
    foreach ($entries as $entry) {
        if (!is_array($entry)) {
            return null;
        }

        $normalizedEntry = app_build_job_entry_from_manifest($entry);
        if ($normalizedEntry === null) {
            return null;
        }

        $normalizedEntries[] = $normalizedEntry;
    }

    return [
        'job_key' => $jobKey,
        'project_key' => $projectKey,
        'requested_by' => $requestedBy,
        'created_at' => $createdAt,
        'status' => $status,
        'publish_requested' => $publishRequested,
        'view' => app_build_job_requested_view($view),
        'release_target_type_filter' => app_build_job_normalize_release_target_filter($releaseTargetTypeFilter),
        'selected_source_output_keys' => $normalizedSelectedSourceOutputKeys,
        'selected_source_output_count' => $selectedSourceOutputCount,
        'successful_count' => $successfulCount,
        'failed_count' => $failedCount,
        'artifact_count' => $artifactCount,
        'published_count' => $publishedCount,
        'errors' => $normalizedErrors,
        'entries' => $normalizedEntries,
        'job_dir' => $jobDir,
        'manifest_path' => $jobDir . '/manifest.json',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     item:array{
 *         job_key:string,
 *         project_key:string,
 *         requested_by:string,
 *         created_at:string,
 *         status:string,
 *         publish_requested:bool,
 *         view:string,
 *         release_target_type_filter:string,
 *         selected_source_output_keys:list<string>,
 *         selected_source_output_count:int,
 *         successful_count:int,
 *         failed_count:int,
 *         artifact_count:int,
 *         published_count:int,
 *         errors:list<string>,
 *         entries:list<array{
 *             source_output_key:string,
 *             source_output_name:string,
 *             source_output_program_language:string,
 *             class_type:string,
 *             release_target_type:string,
 *             artifact_strategy:string,
 *             status:string,
 *             error:string,
 *             artifact_key:string,
 *             artifact_created_at:string,
 *             archive_path:string,
 *             archive_size:int,
 *             manifest_path:string,
 *             source_file_count:int,
 *             source_total_bytes:int,
 *             source_output_dir:string,
 *             published_root:string,
 *             published_file_count:int,
 *             published_total_bytes:int,
 *             published_at:string
 *         }>,
 *         job_dir:string,
 *         manifest_path:string
 *     }|null,
 *     error:string
 * }
 */
function app_build_job_find(array $app, string $jobKey): array
{
    if (!app_build_job_key_is_valid($jobKey)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'job key の形式が不正です。',
        ];
    }

    $storageRoot = app_build_job_storage_root($app);
    if (!is_dir($storageRoot)) {
        return [
            'ok' => true,
            'item' => null,
            'error' => '',
        ];
    }

    $projectEntries = scandir($storageRoot);
    if ($projectEntries === false) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'build job storage の読み込みに失敗しました。',
        ];
    }

    foreach ($projectEntries as $projectEntry) {
        if ($projectEntry === '.' || $projectEntry === '..') {
            continue;
        }

        $projectDir = $storageRoot . '/' . $projectEntry;
        if (!is_dir($projectDir)) {
            continue;
        }

        $jobDir = $projectDir . '/' . $jobKey;
        if (!is_dir($jobDir)) {
            continue;
        }

        $manifest = app_build_job_read_manifest($jobDir . '/manifest.json');
        if ($manifest === null) {
            continue;
        }

        $item = app_build_job_item_from_manifest($jobDir, $manifest);
        if ($item === null) {
            return [
                'ok' => false,
                'item' => null,
                'error' => 'build job manifest の形式が不正です。',
            ];
        }

        if ($item['job_key'] !== $jobKey) {
            return [
                'ok' => false,
                'item' => null,
                'error' => 'build job manifest と要求 path が一致しません。',
            ];
        }

        return [
            'ok' => true,
            'item' => $item,
            'error' => '',
        ];
    }

    return [
        'ok' => true,
        'item' => null,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         job_key:string,
 *         project_key:string,
 *         requested_by:string,
 *         created_at:string,
 *         status:string,
 *         publish_requested:bool,
 *         view:string,
 *         release_target_type_filter:string,
 *         selected_source_output_keys:list<string>,
 *         selected_source_output_count:int,
 *         successful_count:int,
 *         failed_count:int,
 *         artifact_count:int,
 *         published_count:int,
 *         errors:list<string>,
 *         entries:list<array{
 *             source_output_key:string,
 *             source_output_name:string,
 *             source_output_program_language:string,
 *             class_type:string,
 *             release_target_type:string,
 *             artifact_strategy:string,
 *             status:string,
 *             error:string,
 *             artifact_key:string,
 *             artifact_created_at:string,
 *             archive_path:string,
 *             archive_size:int,
 *             manifest_path:string,
 *             source_file_count:int,
 *             source_total_bytes:int,
 *             source_output_dir:string,
 *             published_root:string,
 *             published_file_count:int,
 *             published_total_bytes:int,
 *             published_at:string
 *         }>,
 *         job_dir:string,
 *         manifest_path:string
 *     }>,
 *     error:string
 * }
 */
function app_build_job_list(array $app, ?string $projectKey = null, int $limit = 20): array
{
    $storageRoot = app_build_job_storage_root($app);
    if (!is_dir($storageRoot)) {
        return [
            'ok' => true,
            'items' => [],
            'error' => '',
        ];
    }

    $projectDirs = [];
    if ($projectKey !== null && trim($projectKey) !== '') {
        $normalizedProjectKey = app_normalize_project_key($projectKey);
        if (!app_project_key_is_valid($normalizedProjectKey)) {
            return [
                'ok' => false,
                'items' => [],
                'error' => 'project key の形式が不正です。',
            ];
        }

        $projectDir = app_build_job_storage_root($app, $normalizedProjectKey);
        if (!is_dir($projectDir)) {
            return [
                'ok' => true,
                'items' => [],
                'error' => '',
            ];
        }

        $projectDirs[] = $projectDir;
    } else {
        $entries = scandir($storageRoot);
        if ($entries === false) {
            return [
                'ok' => false,
                'items' => [],
                'error' => 'build job storage の読み込みに失敗しました。',
            ];
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $projectDir = $storageRoot . '/' . $entry;
            if (is_dir($projectDir)) {
                $projectDirs[] = $projectDir;
            }
        }
    }

    $items = [];
    foreach ($projectDirs as $projectDir) {
        $entries = scandir($projectDir);
        if ($entries === false) {
            return [
                'ok' => false,
                'items' => [],
                'error' => 'build job project storage の読み込みに失敗しました。',
            ];
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $jobDir = $projectDir . '/' . $entry;
            if (!is_dir($jobDir)) {
                continue;
            }

            $manifest = app_build_job_read_manifest($jobDir . '/manifest.json');
            if ($manifest === null) {
                continue;
            }

            $item = app_build_job_item_from_manifest($jobDir, $manifest);
            if ($item === null) {
                continue;
            }

            $items[] = $item;
        }
    }

    usort(
        $items,
        static fn (array $left, array $right): int => strcmp($right['job_key'], $left['job_key']),
    );

    if ($limit > 0 && count($items) > $limit) {
        $items = array_slice($items, 0, $limit);
    }

    return [
        'ok' => true,
        'items' => $items,
        'error' => '',
    ];
}
