<?php

declare(strict_types=1);

require_once __DIR__ . '/compare_output_service.php';
require_once __DIR__ . '/runtime_storage_paths.php';

function app_lab_compare_output_job_path(string $jobKey): string
{
    return '/runs/compare-output/' . rawurlencode($jobKey);
}

function app_lab_compare_output_job_api_path(string $jobKey): string
{
    return '/api/runs/compare-output/' . rawurlencode($jobKey);
}

function app_compare_output_job_storage_root(array $app, string $projectKey = ''): string
{
    return app_runtime_storage_compare_output_jobs_root($app, $projectKey);
}

function app_compare_output_job_key_is_valid(string $jobKey): bool
{
    return preg_match('/^[0-9]{8}-[0-9]{6}-[a-f0-9]{8}$/', $jobKey) === 1;
}

function app_compare_output_new_job_key(): string
{
    return date('Ymd-His') . '-' . bin2hex(random_bytes(4));
}

/**
 * @param array{
 *     output_file_absolute_path:string,
 *     compare_output_key:string
 * } $output
 */
function app_compare_output_job_snapshot_relative_path(array $output): string
{
    $filename = basename($output['output_file_absolute_path']);
    if (!is_string($filename) || trim($filename) === '' || $filename === '.' || $filename === '..') {
        $filename = strtolower($output['compare_output_key']) . '.txt';
    }

    return 'output/' . $filename;
}

/**
 * @param array<mixed> $payload
 */
function app_compare_output_job_write_json_file(string $path, array $payload): void
{
    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );

    if (!is_string($json) || $json === '') {
        throw new RuntimeException('compare output job manifest JSON の生成に失敗しました。');
    }

    if (file_put_contents($path, $json . PHP_EOL) === false) {
        throw new RuntimeException('compare output job manifest の保存に失敗しました: ' . $path);
    }
}

function app_compare_output_job_delete_tree(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    if (is_file($path) || is_link($path)) {
        @unlink($path);
        return;
    }

    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        /** @var SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            $pathname = $fileInfo->getPathname();
            if ($fileInfo->isDir()) {
                @rmdir($pathname);
                continue;
            }

            @unlink($pathname);
        }
    } catch (Throwable $throwable) {
        // best effort cleanup
    }

    @rmdir($path);
}

/**
 * @param array{
 *     job_key:string,
 *     project_key:string,
 *     compare_output_key:string,
 *     compare_output_name:string,
 *     output_file_type:string,
 *     compare_tool_file_path:string,
 *     source_of_truth:string,
 *     requested_by:string,
 *     created_at:string,
 *     resolved_storage_base_path:string,
 *     compare_root_absolute_path:string,
 *     output_file_absolute_path:string,
 *     output_directory_absolute_path:string,
 *     output_snapshot_relative_path:string,
 *     deviation_pair_count:int,
 *     checked_pair_count:int,
 *     output_bytes:int,
 *     additional_path_count:int,
 *     warnings:list<string>,
 *     pairs:list<array{
 *         pair_source:string,
 *         pair_key:string,
 *         path_a:string,
 *         path_b:string
 *     }>
 * } $item
 * @return array<mixed>
 */
function app_compare_output_job_manifest_from_item(array $item): array
{
    return [
        'schema_version' => 1,
        'artifact_type' => 'compare-output-job',
        'job_key' => $item['job_key'],
        'project_key' => $item['project_key'],
        'compare_output_key' => $item['compare_output_key'],
        'compare_output_name' => $item['compare_output_name'],
        'output_file_type' => $item['output_file_type'],
        'compare_tool_file_path' => $item['compare_tool_file_path'],
        'source_of_truth' => $item['source_of_truth'],
        'requested_by' => $item['requested_by'],
        'created_at' => $item['created_at'],
        'resolved_storage_base_path' => $item['resolved_storage_base_path'],
        'compare_root_absolute_path' => $item['compare_root_absolute_path'],
        'output_file_absolute_path' => $item['output_file_absolute_path'],
        'output_directory_absolute_path' => $item['output_directory_absolute_path'],
        'output_snapshot_relative_path' => $item['output_snapshot_relative_path'],
        'deviation_pair_count' => $item['deviation_pair_count'],
        'checked_pair_count' => $item['checked_pair_count'],
        'output_bytes' => $item['output_bytes'],
        'additional_path_count' => $item['additional_path_count'],
        'warnings' => $item['warnings'],
        'pairs' => $item['pairs'],
    ];
}

/**
 * @param array{
 *     compare_output_key:string,
 *     name:string,
 *     compare_tool_file_path:string,
 *     source_of_truth:string
 * } $compareOutput
 * @param list<array{
 *     additional_path_key:string,
 *     path_a_base_path:string,
 *     path_a:string,
 *     path_b_base_path:string,
 *     path_b:string,
 *     is_same_filename_only:string,
 *     additional_path_list_order:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at?:string
 * }> $additionalPaths
 * @param array{
 *     project_key:string,
 *     compare_output_key:string,
 *     compare_output_name:string,
 *     output_file_type:string,
 *     requested_by:string,
 *     created_at:string,
 *     resolved_storage_base_path:string,
 *     compare_root_absolute_path:string,
 *     output_file_absolute_path:string,
 *     output_directory_absolute_path:string,
 *     deviation_pair_count:int,
 *     checked_pair_count:int,
 *     output_bytes:int,
 *     warnings:list<string>,
 *     pairs:list<array{
 *         pair_source:string,
 *         pair_key:string,
 *         path_a:string,
 *         path_b:string
 *     }>,
 *     rendered_content:string
 * } $output
 * @return array{
 *     ok:bool,
 *     job:array{
 *         job_key:string,
 *         project_key:string,
 *         compare_output_key:string,
 *         compare_output_name:string,
 *         output_file_type:string,
 *         compare_tool_file_path:string,
 *         source_of_truth:string,
 *         requested_by:string,
 *         created_at:string,
 *         resolved_storage_base_path:string,
 *         compare_root_absolute_path:string,
 *         output_file_absolute_path:string,
 *         output_directory_absolute_path:string,
 *         output_snapshot_relative_path:string,
 *         output_snapshot_path:string,
 *         output_snapshot_exists:bool,
 *         output_snapshot_size:int,
 *         deviation_pair_count:int,
 *         checked_pair_count:int,
 *         output_bytes:int,
 *         additional_path_count:int,
 *         warning_count:int,
 *         warnings:list<string>,
 *         pairs:list<array{
 *             pair_source:string,
 *             pair_key:string,
 *             path_a:string,
 *             path_b:string
 *         }>,
 *         rendered_content:string,
 *         job_dir:string,
 *         manifest_path:string
 *     }|null,
 *     error:string
 * }
 */
function app_compare_output_job_record(
    array $app,
    array $compareOutput,
    array $additionalPaths,
    array $output,
): array {
    $projectKey = app_normalize_project_key($output['project_key']);
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'job' => null,
            'error' => 'project key の形式が不正です。',
        ];
    }

    $compareOutputKey = app_normalize_compare_output_key($output['compare_output_key']);
    if ($compareOutputKey === '' || !app_compare_output_key_is_valid($compareOutputKey)) {
        return [
            'ok' => false,
            'job' => null,
            'error' => 'compare output key の形式が不正です。',
        ];
    }

    $jobKey = app_compare_output_new_job_key();
    $jobDir = app_compare_output_job_storage_root($app, $projectKey) . '/' . $jobKey;
    $outputSnapshotRelativePath = app_compare_output_job_snapshot_relative_path($output);
    $outputSnapshotPath = $jobDir . '/' . $outputSnapshotRelativePath;
    $manifestPath = $jobDir . '/manifest.json';

    try {
        app_compare_output_ensure_directory(dirname($outputSnapshotPath));
        if (file_put_contents($outputSnapshotPath, $output['rendered_content']) === false) {
            throw new RuntimeException('compare output job snapshot の保存に失敗しました。');
        }

        $job = [
            'job_key' => $jobKey,
            'project_key' => $projectKey,
            'compare_output_key' => $compareOutputKey,
            'compare_output_name' => $output['compare_output_name'],
            'output_file_type' => $output['output_file_type'],
            'compare_tool_file_path' => (string) ($compareOutput['compare_tool_file_path'] ?? ''),
            'source_of_truth' => (string) ($compareOutput['source_of_truth'] ?? ''),
            'requested_by' => $output['requested_by'],
            'created_at' => $output['created_at'],
            'resolved_storage_base_path' => $output['resolved_storage_base_path'],
            'compare_root_absolute_path' => $output['compare_root_absolute_path'],
            'output_file_absolute_path' => $output['output_file_absolute_path'],
            'output_directory_absolute_path' => $output['output_directory_absolute_path'],
            'output_snapshot_relative_path' => $outputSnapshotRelativePath,
            'output_snapshot_path' => $outputSnapshotPath,
            'output_snapshot_exists' => is_file($outputSnapshotPath),
            'output_snapshot_size' => is_file($outputSnapshotPath) ? (int) filesize($outputSnapshotPath) : 0,
            'deviation_pair_count' => $output['deviation_pair_count'],
            'checked_pair_count' => $output['checked_pair_count'],
            'output_bytes' => $output['output_bytes'],
            'additional_path_count' => count($additionalPaths),
            'warning_count' => count($output['warnings']),
            'warnings' => $output['warnings'],
            'pairs' => $output['pairs'],
            'rendered_content' => $output['rendered_content'],
            'job_dir' => $jobDir,
            'manifest_path' => $manifestPath,
        ];

        app_compare_output_job_write_json_file($manifestPath, app_compare_output_job_manifest_from_item($job));

        return [
            'ok' => true,
            'job' => $job,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        app_compare_output_job_delete_tree($jobDir);

        return [
            'ok' => false,
            'job' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     compare_output_key:string,
 *     name:string,
 *     storage_base_path:string,
 *     output_file_path:string,
 *     output_file_type:string,
 *     compare_path:string,
 *     compare_tool_file_path:string,
 *     compare_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at?:string
 * } $compareOutput
 * @param list<array{
 *     additional_path_key:string,
 *     path_a_base_path:string,
 *     path_a:string,
 *     path_b_base_path:string,
 *     path_b:string,
 *     is_same_filename_only:string,
 *     additional_path_list_order:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at?:string
 * }> $additionalPaths
 * @return array{
 *     ok:bool,
 *     output:array{
 *         project_key:string,
 *         compare_output_key:string,
 *         compare_output_name:string,
 *         output_file_type:string,
 *         requested_by:string,
 *         created_at:string,
 *         resolved_storage_base_path:string,
 *         compare_root_absolute_path:string,
 *         output_file_absolute_path:string,
 *         output_directory_absolute_path:string,
 *         deviation_pair_count:int,
 *         checked_pair_count:int,
 *         output_bytes:int,
 *         warnings:list<string>,
 *         pairs:list<array{
 *             pair_source:string,
 *             pair_key:string,
 *             path_a:string,
 *             path_b:string
 *         }>,
 *         rendered_content:string
 *     }|null,
 *     job:array{
 *         job_key:string,
 *         project_key:string,
 *         compare_output_key:string,
 *         compare_output_name:string,
 *         output_file_type:string,
 *         compare_tool_file_path:string,
 *         source_of_truth:string,
 *         requested_by:string,
 *         created_at:string,
 *         resolved_storage_base_path:string,
 *         compare_root_absolute_path:string,
 *         output_file_absolute_path:string,
 *         output_directory_absolute_path:string,
 *         output_snapshot_relative_path:string,
 *         output_snapshot_path:string,
 *         output_snapshot_exists:bool,
 *         output_snapshot_size:int,
 *         deviation_pair_count:int,
 *         checked_pair_count:int,
 *         output_bytes:int,
 *         additional_path_count:int,
 *         warning_count:int,
 *         warnings:list<string>,
 *         pairs:list<array{
 *             pair_source:string,
 *             pair_key:string,
 *             path_a:string,
 *             path_b:string
 *         }>,
 *         rendered_content:string,
 *         job_dir:string,
 *         manifest_path:string
 *     }|null,
 *     error:string
 * }
 */
function app_compare_output_job_create(
    array $app,
    string $projectKey,
    array $compareOutput,
    array $additionalPaths,
    string $requestedBy = 'system',
): array {
    $outputResult = app_compare_output_generate_output_file(
        $app,
        $projectKey,
        $compareOutput,
        $additionalPaths,
        $requestedBy,
    );
    if (!$outputResult['ok'] || $outputResult['output'] === null) {
        return [
            'ok' => false,
            'output' => null,
            'job' => null,
            'error' => $outputResult['error'],
        ];
    }

    $jobResult = app_compare_output_job_record(
        $app,
        $compareOutput,
        $additionalPaths,
        $outputResult['output'],
    );
    if (!$jobResult['ok'] || $jobResult['job'] === null) {
        return [
            'ok' => false,
            'output' => null,
            'job' => null,
            'error' => $jobResult['error'],
        ];
    }

    return [
        'ok' => true,
        'output' => $outputResult['output'],
        'job' => $jobResult['job'],
        'error' => '',
    ];
}

/**
 * @return array<mixed>|null
 */
function app_compare_output_job_read_manifest(string $manifestPath): ?array
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
 * @param array<mixed> $manifest
 * @return array{
 *     job_key:string,
 *     project_key:string,
 *     compare_output_key:string,
 *     compare_output_name:string,
 *     output_file_type:string,
 *     compare_tool_file_path:string,
 *     source_of_truth:string,
 *     requested_by:string,
 *     created_at:string,
 *     resolved_storage_base_path:string,
 *     compare_root_absolute_path:string,
 *     output_file_absolute_path:string,
 *     output_directory_absolute_path:string,
 *     output_snapshot_relative_path:string,
 *     output_snapshot_path:string,
 *     output_snapshot_exists:bool,
 *     output_snapshot_size:int,
 *     deviation_pair_count:int,
 *     checked_pair_count:int,
 *     output_bytes:int,
 *     additional_path_count:int,
 *     warning_count:int,
 *     warnings:list<string>,
 *     pairs:list<array{
 *         pair_source:string,
 *         pair_key:string,
 *         path_a:string,
 *         path_b:string
 *     }>,
 *     rendered_content:string,
 *     job_dir:string,
 *     manifest_path:string
 * }|null
 */
function app_compare_output_job_item_from_manifest(string $jobDir, array $manifest): ?array
{
    $jobKey = $manifest['job_key'] ?? null;
    $projectKey = $manifest['project_key'] ?? null;
    $compareOutputKey = $manifest['compare_output_key'] ?? null;
    $compareOutputName = $manifest['compare_output_name'] ?? null;
    $outputFileType = $manifest['output_file_type'] ?? null;
    $compareToolFilePath = $manifest['compare_tool_file_path'] ?? null;
    $sourceOfTruth = $manifest['source_of_truth'] ?? null;
    $requestedBy = $manifest['requested_by'] ?? null;
    $createdAt = $manifest['created_at'] ?? null;
    $resolvedStorageBasePath = $manifest['resolved_storage_base_path'] ?? null;
    $compareRootAbsolutePath = $manifest['compare_root_absolute_path'] ?? null;
    $outputFileAbsolutePath = $manifest['output_file_absolute_path'] ?? null;
    $outputDirectoryAbsolutePath = $manifest['output_directory_absolute_path'] ?? null;
    $outputSnapshotRelativePath = $manifest['output_snapshot_relative_path'] ?? null;
    $deviationPairCount = $manifest['deviation_pair_count'] ?? null;
    $checkedPairCount = $manifest['checked_pair_count'] ?? null;
    $outputBytes = $manifest['output_bytes'] ?? null;
    $additionalPathCount = $manifest['additional_path_count'] ?? null;
    $warnings = $manifest['warnings'] ?? null;
    $pairs = $manifest['pairs'] ?? null;

    if (
        !is_string($jobKey) || !app_compare_output_job_key_is_valid($jobKey)
        || !is_string($projectKey) || !app_project_key_is_valid($projectKey)
        || !is_string($compareOutputKey) || !app_compare_output_key_is_valid(app_normalize_compare_output_key($compareOutputKey))
        || !is_string($compareOutputName) || $compareOutputName === ''
        || !is_string($outputFileType) || $outputFileType === ''
        || !is_string($compareToolFilePath)
        || !is_string($sourceOfTruth)
        || !is_string($requestedBy) || $requestedBy === ''
        || !is_string($createdAt) || $createdAt === ''
        || !is_string($resolvedStorageBasePath) || $resolvedStorageBasePath === ''
        || !is_string($compareRootAbsolutePath) || $compareRootAbsolutePath === ''
        || !is_string($outputFileAbsolutePath) || $outputFileAbsolutePath === ''
        || !is_string($outputDirectoryAbsolutePath) || $outputDirectoryAbsolutePath === ''
        || !is_string($outputSnapshotRelativePath) || !app_compare_output_relative_path_is_safe($outputSnapshotRelativePath)
        || !is_int($deviationPairCount)
        || !is_int($checkedPairCount)
        || !is_int($outputBytes)
        || !is_int($additionalPathCount)
        || !is_array($warnings)
        || !is_array($pairs)
    ) {
        return null;
    }

    $normalizedWarnings = [];
    foreach ($warnings as $warning) {
        if (!is_string($warning)) {
            return null;
        }

        $normalizedWarnings[] = $warning;
    }

    $normalizedPairs = [];
    foreach ($pairs as $pair) {
        if (!is_array($pair)) {
            return null;
        }

        $pairSource = $pair['pair_source'] ?? null;
        $pairKey = $pair['pair_key'] ?? null;
        $pathA = $pair['path_a'] ?? null;
        $pathB = $pair['path_b'] ?? null;
        if (
            !is_string($pairSource) || $pairSource === ''
            || !is_string($pairKey) || $pairKey === ''
            || !is_string($pathA) || $pathA === ''
            || !is_string($pathB) || $pathB === ''
        ) {
            return null;
        }

        $normalizedPairs[] = [
            'pair_source' => $pairSource,
            'pair_key' => $pairKey,
            'path_a' => $pathA,
            'path_b' => $pathB,
        ];
    }

    $outputSnapshotPath = $jobDir . '/' . $outputSnapshotRelativePath;
    $renderedContent = '';
    if (is_file($outputSnapshotPath)) {
        $snapshotContents = file_get_contents($outputSnapshotPath);
        if (is_string($snapshotContents)) {
            $renderedContent = $snapshotContents;
        }
    }

    return [
        'job_key' => $jobKey,
        'project_key' => $projectKey,
        'compare_output_key' => app_normalize_compare_output_key($compareOutputKey),
        'compare_output_name' => $compareOutputName,
        'output_file_type' => $outputFileType,
        'compare_tool_file_path' => $compareToolFilePath,
        'source_of_truth' => $sourceOfTruth,
        'requested_by' => $requestedBy,
        'created_at' => $createdAt,
        'resolved_storage_base_path' => $resolvedStorageBasePath,
        'compare_root_absolute_path' => $compareRootAbsolutePath,
        'output_file_absolute_path' => $outputFileAbsolutePath,
        'output_directory_absolute_path' => $outputDirectoryAbsolutePath,
        'output_snapshot_relative_path' => $outputSnapshotRelativePath,
        'output_snapshot_path' => $outputSnapshotPath,
        'output_snapshot_exists' => is_file($outputSnapshotPath),
        'output_snapshot_size' => is_file($outputSnapshotPath) ? (int) filesize($outputSnapshotPath) : 0,
        'deviation_pair_count' => $deviationPairCount,
        'checked_pair_count' => $checkedPairCount,
        'output_bytes' => $outputBytes,
        'additional_path_count' => $additionalPathCount,
        'warning_count' => count($normalizedWarnings),
        'warnings' => $normalizedWarnings,
        'pairs' => $normalizedPairs,
        'rendered_content' => $renderedContent,
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
 *         compare_output_key:string,
 *         compare_output_name:string,
 *         output_file_type:string,
 *         compare_tool_file_path:string,
 *         source_of_truth:string,
 *         requested_by:string,
 *         created_at:string,
 *         resolved_storage_base_path:string,
 *         compare_root_absolute_path:string,
 *         output_file_absolute_path:string,
 *         output_directory_absolute_path:string,
 *         output_snapshot_relative_path:string,
 *         output_snapshot_path:string,
 *         output_snapshot_exists:bool,
 *         output_snapshot_size:int,
 *         deviation_pair_count:int,
 *         checked_pair_count:int,
 *         output_bytes:int,
 *         additional_path_count:int,
 *         warning_count:int,
 *         warnings:list<string>,
 *         pairs:list<array{
 *             pair_source:string,
 *             pair_key:string,
 *             path_a:string,
 *             path_b:string
 *         }>,
 *         rendered_content:string,
 *         job_dir:string,
 *         manifest_path:string
 *     }|null,
 *     error:string
 * }
 */
function app_compare_output_job_find(array $app, string $jobKey): array
{
    if (!app_compare_output_job_key_is_valid($jobKey)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'job key の形式が不正です。',
        ];
    }

    $storageRoot = app_compare_output_job_storage_root($app);
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
            'error' => 'compare output job storage の読み込みに失敗しました。',
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

        $manifest = app_compare_output_job_read_manifest($jobDir . '/manifest.json');
        if ($manifest === null) {
            continue;
        }

        $item = app_compare_output_job_item_from_manifest($jobDir, $manifest);
        if ($item === null) {
            return [
                'ok' => false,
                'item' => null,
                'error' => 'compare output job manifest の形式が不正です。',
            ];
        }

        if ($item['job_key'] !== $jobKey) {
            return [
                'ok' => false,
                'item' => null,
                'error' => 'compare output job manifest と要求 path が一致しません。',
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
 *         compare_output_key:string,
 *         compare_output_name:string,
 *         output_file_type:string,
 *         compare_tool_file_path:string,
 *         source_of_truth:string,
 *         requested_by:string,
 *         created_at:string,
 *         resolved_storage_base_path:string,
 *         compare_root_absolute_path:string,
 *         output_file_absolute_path:string,
 *         output_directory_absolute_path:string,
 *         output_snapshot_relative_path:string,
 *         output_snapshot_path:string,
 *         output_snapshot_exists:bool,
 *         output_snapshot_size:int,
 *         deviation_pair_count:int,
 *         checked_pair_count:int,
 *         output_bytes:int,
 *         additional_path_count:int,
 *         warning_count:int,
 *         warnings:list<string>,
 *         pairs:list<array{
 *             pair_source:string,
 *             pair_key:string,
 *             path_a:string,
 *             path_b:string
 *         }>,
 *         rendered_content:string,
 *         job_dir:string,
 *         manifest_path:string
 *     }>,
 *     error:string
 * }
 */
function app_compare_output_job_list(
    array $app,
    ?string $projectKey = null,
    ?string $compareOutputKey = null,
    int $limit = 20,
): array {
    $storageRoot = app_compare_output_job_storage_root($app);
    if (!is_dir($storageRoot)) {
        return [
            'ok' => true,
            'items' => [],
            'error' => '',
        ];
    }

    $normalizedProjectKey = null;
    if ($projectKey !== null && trim($projectKey) !== '') {
        $normalizedProjectKey = app_normalize_project_key($projectKey);
        if (!app_project_key_is_valid($normalizedProjectKey)) {
            return [
                'ok' => false,
                'items' => [],
                'error' => 'project key の形式が不正です。',
            ];
        }
    }

    $normalizedCompareOutputKey = null;
    if ($compareOutputKey !== null && trim($compareOutputKey) !== '') {
        $normalizedCompareOutputKey = app_normalize_compare_output_key($compareOutputKey);
        if (!app_compare_output_key_is_valid($normalizedCompareOutputKey)) {
            return [
                'ok' => false,
                'items' => [],
                'error' => 'compare output key の形式が不正です。',
            ];
        }
    }

    $projectDirs = [];
    if ($normalizedProjectKey !== null) {
        $projectDir = app_compare_output_job_storage_root($app, $normalizedProjectKey);
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
                'error' => 'compare output job storage の読み込みに失敗しました。',
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
                'error' => 'compare output job project storage の読み込みに失敗しました。',
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

            $manifest = app_compare_output_job_read_manifest($jobDir . '/manifest.json');
            if ($manifest === null) {
                continue;
            }

            $item = app_compare_output_job_item_from_manifest($jobDir, $manifest);
            if ($item === null) {
                continue;
            }

            if ($normalizedProjectKey !== null && $item['project_key'] !== $normalizedProjectKey) {
                continue;
            }

            if ($normalizedCompareOutputKey !== null && $item['compare_output_key'] !== $normalizedCompareOutputKey) {
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
