<?php

declare(strict_types=1);

require_once __DIR__ . '/project_output_service.php';
require_once __DIR__ . '/runtime_storage_paths.php';

function app_runtime_reference_promotion_project_key(): string
{
    return 'MTOOL';
}

function app_runtime_reference_promotion_source_output_key(): string
{
    return 'RUNTIME-DBCLASSES';
}

function app_runtime_reference_promotion_runtime_source_relative_path(): string
{
    return app_project_output_runtime_source_relative_path();
}

function app_runtime_reference_promotion_target_relative_path(): string
{
    return 'dbclasses';
}

function app_runtime_reference_promotion_target_root(): string
{
    return app_runtime_storage_repo_path(
        app_runtime_storage_mtool_reference_relative_path(
            app_runtime_reference_promotion_target_relative_path(),
        ),
    );
}

function app_runtime_reference_snapshot_manifest_relative_path(): string
{
    return app_runtime_storage_relative_path('_support', 'runtime-reference-snapshot.json');
}

function app_runtime_reference_snapshot_manifest_path(string $snapshotRoot): string
{
    return rtrim(str_replace('\\', '/', $snapshotRoot), '/')
        . '/'
        . app_runtime_reference_snapshot_manifest_relative_path();
}

/**
 * @return array{
 *     ok:bool,
 *     promoted:array{
 *         source_root:string,
 *         target_root:string,
 *         file_count:int,
 *         total_bytes:int,
 *         promoted_at:string
 *     }|null,
 *     error:string
 * }
 */
function app_runtime_reference_promote_tree(
    string $sourceRoot,
    string $targetRoot,
    ?callable $afterPromote = null,
): array
{
    $normalizedSourceRoot = rtrim(str_replace('\\', '/', $sourceRoot), '/');
    $normalizedTargetRoot = rtrim(str_replace('\\', '/', $targetRoot), '/');

    if ($normalizedSourceRoot === '' || !is_dir($normalizedSourceRoot)) {
        return [
            'ok' => false,
            'promoted' => null,
            'error' => 'promote 元 directory が見つかりません。',
        ];
    }

    if ($normalizedTargetRoot === '') {
        return [
            'ok' => false,
            'promoted' => null,
            'error' => 'promote 先 directory が空です。',
        ];
    }

    $scanResult = app_project_output_scan_tree($normalizedSourceRoot);
    if (!$scanResult['ok']) {
        return [
            'ok' => false,
            'promoted' => null,
            'error' => $scanResult['error'],
        ];
    }

    if ($scanResult['files'] === []) {
        return [
            'ok' => false,
            'promoted' => null,
            'error' => 'promote 対象 source tree に file がありません。',
        ];
    }

    $stagingRoot = $normalizedTargetRoot . '.__promoting__.' . bin2hex(random_bytes(4));
    $backupRoot = $normalizedTargetRoot . '.__backup__.' . bin2hex(random_bytes(4));
    $targetSwapped = false;

    try {
        app_project_output_ensure_directory(dirname($normalizedTargetRoot));
        app_project_output_delete_tree($stagingRoot);
        app_project_output_delete_tree($backupRoot);
        app_project_output_copy_tree($normalizedSourceRoot, $stagingRoot, $scanResult['files']);

        $hadExistingRoot = file_exists($normalizedTargetRoot);
        if ($hadExistingRoot && !rename($normalizedTargetRoot, $backupRoot)) {
            throw new RuntimeException('既存 reference root の退避に失敗しました。');
        }

        if (!rename($stagingRoot, $normalizedTargetRoot)) {
            if ($hadExistingRoot && file_exists($backupRoot) && !file_exists($normalizedTargetRoot)) {
                @rename($backupRoot, $normalizedTargetRoot);
            }

            throw new RuntimeException('reference root の切り替えに失敗しました。');
        }

        $targetSwapped = true;
        if ($afterPromote !== null) {
            $afterPromote($normalizedTargetRoot);
        }

        $finalScanResult = app_project_output_scan_tree($normalizedTargetRoot);
        if (!$finalScanResult['ok']) {
            throw new RuntimeException($finalScanResult['error']);
        }

        app_project_output_delete_tree($backupRoot);

        return [
            'ok' => true,
            'promoted' => [
                'source_root' => $normalizedSourceRoot,
                'target_root' => $normalizedTargetRoot,
                'file_count' => count($finalScanResult['files']),
                'total_bytes' => $finalScanResult['total_bytes'],
                'promoted_at' => date(DATE_ATOM),
            ],
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        app_project_output_delete_tree($stagingRoot);

        if ($targetSwapped && file_exists($normalizedTargetRoot)) {
            app_project_output_delete_tree($normalizedTargetRoot);
        }

        if (file_exists($backupRoot) && !file_exists($normalizedTargetRoot)) {
            @rename($backupRoot, $normalizedTargetRoot);
        }

        return [
            'ok' => false,
            'promoted' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{
 *     ok:bool,
 *     snapshot:array{
 *         project_key:string,
 *         source_output_key:string,
 *         artifact_key:string,
 *         runtime_source_relative_path:string,
 *         source_root:string,
 *         snapshot_root:string,
 *         snapshot_manifest_path:string,
 *         file_count:int,
 *         total_bytes:int,
 *         captured_at:string,
 *         requested_by:string
 *     }|null,
 *     error:string
 * }
 */
function app_runtime_reference_capture_snapshot_from_root(
    string $sourceRoot,
    string $projectKey,
    string $sourceOutputKey,
    string $artifactKey,
    string $requestedBy = 'runtime-reference-promote',
    string $snapshotRoot = '',
): array {
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $normalizedSourceOutputKey = app_normalize_source_output_key($sourceOutputKey);
    $normalizedRequestedBy = app_project_output_normalize_requested_by($requestedBy);
    $normalizedSnapshotRoot = $snapshotRoot !== ''
        ? rtrim(str_replace('\\', '/', $snapshotRoot), '/')
        : app_runtime_storage_runtime_reference_snapshots_repo_root(
            $normalizedProjectKey,
            $normalizedSourceOutputKey,
            $artifactKey,
        );

    if ($normalizedProjectKey !== app_runtime_reference_promotion_project_key()) {
        return [
            'ok' => false,
            'snapshot' => null,
            'error' => 'runtime reference snapshot は MTOOL のみ対応です。',
        ];
    }

    if ($normalizedSourceOutputKey !== app_runtime_reference_promotion_source_output_key()) {
        return [
            'ok' => false,
            'snapshot' => null,
            'error' => 'runtime reference snapshot は RUNTIME-DBCLASSES のみ対応です。',
        ];
    }

    if ($artifactKey === '' || !app_project_output_artifact_key_is_valid($artifactKey)) {
        return [
            'ok' => false,
            'snapshot' => null,
            'error' => 'snapshot 用 artifact key の形式が不正です。',
        ];
    }

    $treeResult = app_runtime_reference_promote_tree(
        $sourceRoot,
        $normalizedSnapshotRoot,
        static function (string $promotedSnapshotRoot) use (
            $normalizedProjectKey,
            $normalizedSourceOutputKey,
            $artifactKey,
            $normalizedRequestedBy,
            $sourceRoot,
        ): void {
            app_project_output_set_runtime_generation_manifest_artifact_key(
                $promotedSnapshotRoot,
                $artifactKey,
            );
            app_project_output_write_json_file(
                app_runtime_reference_snapshot_manifest_path($promotedSnapshotRoot),
                [
                    'project_key' => $normalizedProjectKey,
                    'source_output_key' => $normalizedSourceOutputKey,
                    'artifact_key' => $artifactKey,
                    'runtime_source_relative_path' => app_runtime_reference_promotion_runtime_source_relative_path(),
                    'captured_at' => date(DATE_ATOM),
                    'requested_by' => $normalizedRequestedBy,
                    'source_root' => rtrim(str_replace('\\', '/', $sourceRoot), '/'),
                ],
            );
        },
    );
    if (!$treeResult['ok'] || !is_array($treeResult['promoted'])) {
        return [
            'ok' => false,
            'snapshot' => null,
            'error' => $treeResult['error'],
        ];
    }

    return [
        'ok' => true,
        'snapshot' => [
            'project_key' => $normalizedProjectKey,
            'source_output_key' => $normalizedSourceOutputKey,
            'artifact_key' => $artifactKey,
            'runtime_source_relative_path' => app_runtime_reference_promotion_runtime_source_relative_path(),
            'source_root' => $treeResult['promoted']['source_root'],
            'snapshot_root' => $treeResult['promoted']['target_root'],
            'snapshot_manifest_path' => app_runtime_reference_snapshot_manifest_path(
                $treeResult['promoted']['target_root'],
            ),
            'file_count' => $treeResult['promoted']['file_count'],
            'total_bytes' => $treeResult['promoted']['total_bytes'],
            'captured_at' => $treeResult['promoted']['promoted_at'],
            'requested_by' => $normalizedRequestedBy,
        ],
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     restored:array{
 *         project_key:string,
 *         source_output_key:string,
 *         artifact_key:string,
 *         runtime_source_relative_path:string,
 *         source_root:string,
 *         snapshot_root:string,
 *         snapshot_manifest_path:string,
 *         target_root:string,
 *         file_count:int,
 *         total_bytes:int,
 *         restored_at:string,
 *         requested_by:string
 *     }|null,
 *     error:string
 * }
 */
function app_runtime_reference_restore_snapshot(
    string $projectKey,
    string $sourceOutputKey,
    string $artifactKey,
    string $targetRoot = '',
    string $requestedBy = 'restore-runtime-reference-snapshot',
    string $snapshotRoot = '',
): array {
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $normalizedSourceOutputKey = app_normalize_source_output_key($sourceOutputKey);
    $normalizedRequestedBy = app_project_output_normalize_requested_by($requestedBy);
    $normalizedSnapshotRoot = $snapshotRoot !== ''
        ? rtrim(str_replace('\\', '/', $snapshotRoot), '/')
        : app_runtime_storage_runtime_reference_snapshots_repo_root(
            $normalizedProjectKey,
            $normalizedSourceOutputKey,
            $artifactKey,
        );
    $normalizedTargetRoot = $targetRoot !== ''
        ? rtrim(str_replace('\\', '/', $targetRoot), '/')
        : app_runtime_reference_promotion_target_root();

    if ($normalizedProjectKey !== app_runtime_reference_promotion_project_key()) {
        return [
            'ok' => false,
            'restored' => null,
            'error' => 'runtime reference snapshot restore は MTOOL のみ対応です。',
        ];
    }

    if ($normalizedSourceOutputKey !== app_runtime_reference_promotion_source_output_key()) {
        return [
            'ok' => false,
            'restored' => null,
            'error' => 'runtime reference snapshot restore は RUNTIME-DBCLASSES のみ対応です。',
        ];
    }

    if ($artifactKey === '' || !app_project_output_artifact_key_is_valid($artifactKey)) {
        return [
            'ok' => false,
            'restored' => null,
            'error' => 'restore 用 artifact key の形式が不正です。',
        ];
    }

    $snapshotManifest = app_project_output_read_manifest(
        app_runtime_reference_snapshot_manifest_path($normalizedSnapshotRoot),
    );
    if (is_array($snapshotManifest)) {
        $snapshotProjectKey = app_normalize_project_key((string) ($snapshotManifest['project_key'] ?? ''));
        $snapshotSourceOutputKey = app_normalize_source_output_key((string) ($snapshotManifest['source_output_key'] ?? ''));
        $snapshotArtifactKey = trim((string) ($snapshotManifest['artifact_key'] ?? ''));

        if ($snapshotProjectKey !== '' && $snapshotProjectKey !== $normalizedProjectKey) {
            return [
                'ok' => false,
                'restored' => null,
                'error' => 'snapshot manifest の project key が一致しません。',
            ];
        }

        if ($snapshotSourceOutputKey !== '' && $snapshotSourceOutputKey !== $normalizedSourceOutputKey) {
            return [
                'ok' => false,
                'restored' => null,
                'error' => 'snapshot manifest の source output key が一致しません。',
            ];
        }

        if ($snapshotArtifactKey !== '' && $snapshotArtifactKey !== $artifactKey) {
            return [
                'ok' => false,
                'restored' => null,
                'error' => 'snapshot manifest の artifact key が一致しません。',
            ];
        }
    }

    $runtimeManifest = app_project_output_read_manifest(
        $normalizedSnapshotRoot . '/_support/runtime-generation-manifest.json',
    );
    if (is_array($runtimeManifest)) {
        $runtimeArtifactKey = trim((string) ($runtimeManifest['artifact_key'] ?? ''));
        if ($runtimeArtifactKey !== '' && $runtimeArtifactKey !== $artifactKey) {
            return [
                'ok' => false,
                'restored' => null,
                'error' => 'snapshot runtime manifest の artifact key が一致しません。',
            ];
        }
    }

    $treeResult = app_runtime_reference_promote_tree(
        $normalizedSnapshotRoot,
        $normalizedTargetRoot,
        static function (string $promotedTargetRoot) use ($artifactKey): void {
            app_project_output_set_runtime_generation_manifest_artifact_key(
                $promotedTargetRoot,
                $artifactKey,
            );
            $snapshotManifestPath = app_runtime_reference_snapshot_manifest_path($promotedTargetRoot);
            if (is_file($snapshotManifestPath) && !unlink($snapshotManifestPath)) {
                throw new RuntimeException('runtime reference への snapshot metadata 混入を除去できませんでした。');
            }
        },
    );
    if (!$treeResult['ok'] || !is_array($treeResult['promoted'])) {
        return [
            'ok' => false,
            'restored' => null,
            'error' => $treeResult['error'],
        ];
    }

    return [
        'ok' => true,
        'restored' => [
            'project_key' => $normalizedProjectKey,
            'source_output_key' => $normalizedSourceOutputKey,
            'artifact_key' => $artifactKey,
            'runtime_source_relative_path' => app_runtime_reference_promotion_runtime_source_relative_path(),
            'source_root' => $treeResult['promoted']['source_root'],
            'snapshot_root' => $normalizedSnapshotRoot,
            'snapshot_manifest_path' => app_runtime_reference_snapshot_manifest_path($normalizedSnapshotRoot),
            'target_root' => $treeResult['promoted']['target_root'],
            'file_count' => $treeResult['promoted']['file_count'],
            'total_bytes' => $treeResult['promoted']['total_bytes'],
            'restored_at' => $treeResult['promoted']['promoted_at'],
            'requested_by' => $normalizedRequestedBy,
        ],
        'error' => '',
    ];
}

/**
 * @param array{
 *     project_key:string,
 *     source_output_key:string,
 *     artifact_key:string,
 *     bundle_root:string,
 *     runtime_source_relative_path:string
 * } $artifact
 * @return array{
 *     ok:bool,
 *     promoted:array{
 *         project_key:string,
 *         source_output_key:string,
 *         artifact_key:string,
 *         runtime_source_relative_path:string,
 *         source_root:string,
 *         target_root:string,
 *         snapshot_root:string,
 *         snapshot_manifest_path:string,
 *         file_count:int,
 *         total_bytes:int,
 *         promoted_at:string
 *     }|null,
 *     error:string
 * }
 */
function app_runtime_reference_promote_artifact(
    array $artifact,
    string $targetRoot = '',
    string $requestedBy = 'runtime-reference-promote',
    string $snapshotRoot = '',
): array
{
    $projectKey = app_normalize_project_key($artifact['project_key'] ?? '');
    $sourceOutputKey = app_normalize_source_output_key($artifact['source_output_key'] ?? '');
    $artifactKey = (string) ($artifact['artifact_key'] ?? '');
    $runtimeSourceRelativePath = app_runtime_storage_relative_path(
        (string) ($artifact['runtime_source_relative_path'] ?? ''),
    );

    if ($projectKey !== app_runtime_reference_promotion_project_key()) {
        return [
            'ok' => false,
            'promoted' => null,
            'error' => 'runtime reference promote は MTOOL artifact のみ対応です。',
        ];
    }

    if ($sourceOutputKey !== app_runtime_reference_promotion_source_output_key()) {
        return [
            'ok' => false,
            'promoted' => null,
            'error' => 'runtime reference promote は RUNTIME-DBCLASSES artifact のみ対応です。',
        ];
    }

    if ($artifactKey === '' || !app_project_output_artifact_key_is_valid($artifactKey)) {
        return [
            'ok' => false,
            'promoted' => null,
            'error' => 'artifact key の形式が不正です。',
        ];
    }

    if ($runtimeSourceRelativePath !== app_runtime_reference_promotion_runtime_source_relative_path()) {
        return [
            'ok' => false,
            'promoted' => null,
            'error' => 'artifact runtime source path が RUNTIME-DBCLASSES と一致しません。',
        ];
    }

    try {
        $artifactRuntimeRoot = app_project_output_artifact_bundle_runtime_root($artifact);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'promoted' => null,
            'error' => $throwable->getMessage(),
        ];
    }

    $treeResult = app_runtime_reference_promote_tree(
        $artifactRuntimeRoot,
        $targetRoot !== '' ? $targetRoot : app_runtime_reference_promotion_target_root(),
        static function (string $promotedTargetRoot) use (
            $projectKey,
            $sourceOutputKey,
            $artifactKey,
            $requestedBy,
            $snapshotRoot,
        ): void {
            app_project_output_set_runtime_generation_manifest_artifact_key(
                $promotedTargetRoot,
                $artifactKey,
            );
            $snapshotResult = app_runtime_reference_capture_snapshot_from_root(
                $promotedTargetRoot,
                $projectKey,
                $sourceOutputKey,
                $artifactKey,
                $requestedBy,
                $snapshotRoot,
            );
            if (!$snapshotResult['ok']) {
                throw new RuntimeException($snapshotResult['error']);
            }
        },
    );
    if (!$treeResult['ok'] || !is_array($treeResult['promoted'])) {
        return [
            'ok' => false,
            'promoted' => null,
            'error' => $treeResult['error'],
        ];
    }

    return [
        'ok' => true,
        'promoted' => [
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'artifact_key' => $artifactKey,
            'runtime_source_relative_path' => $runtimeSourceRelativePath,
            'source_root' => $treeResult['promoted']['source_root'],
            'target_root' => $treeResult['promoted']['target_root'],
            'snapshot_root' => $snapshotRoot !== ''
                ? rtrim(str_replace('\\', '/', $snapshotRoot), '/')
                : app_runtime_storage_runtime_reference_snapshots_repo_root(
                    $projectKey,
                    $sourceOutputKey,
                    $artifactKey,
                ),
            'snapshot_manifest_path' => app_runtime_reference_snapshot_manifest_path(
                $snapshotRoot !== ''
                    ? rtrim(str_replace('\\', '/', $snapshotRoot), '/')
                    : app_runtime_storage_runtime_reference_snapshots_repo_root(
                        $projectKey,
                        $sourceOutputKey,
                        $artifactKey,
                    ),
            ),
            'file_count' => $treeResult['promoted']['file_count'],
            'total_bytes' => $treeResult['promoted']['total_bytes'],
            'promoted_at' => $treeResult['promoted']['promoted_at'],
        ],
        'error' => '',
    ];
}
