<?php

declare(strict_types=1);

require_once __DIR__ . '/project_output_service.php';
require_once __DIR__ . '/runtime_reference_promotion.php';

function app_runtime_reference_status_default_project_key(): string
{
    return app_runtime_reference_promotion_project_key();
}

function app_runtime_reference_status_default_source_output_key(): string
{
    return app_runtime_reference_promotion_source_output_key();
}

/**
 * @return array{
 *     root:string,
 *     exists:bool,
 *     manifest_path:string,
 *     manifest_exists:bool,
 *     project_key:string,
 *     source_output_key:string,
 *     runtime_source_relative_path:string,
 *     artifact_key:string,
 *     artifact_key_valid:bool,
 *     generated_at:string,
 *     mode:string
 * }
 */
function app_runtime_reference_status_runtime_manifest_summary(string $runtimeRoot): array
{
    $normalizedRoot = rtrim(str_replace('\\', '/', $runtimeRoot), '/');
    $manifestPath = $normalizedRoot . '/_support/runtime-generation-manifest.json';
    $summary = [
        'root' => $normalizedRoot,
        'exists' => $normalizedRoot !== '' && is_dir($normalizedRoot),
        'manifest_path' => $manifestPath,
        'manifest_exists' => false,
        'project_key' => '',
        'source_output_key' => '',
        'runtime_source_relative_path' => '',
        'artifact_key' => '',
        'artifact_key_valid' => false,
        'generated_at' => '',
        'mode' => '',
    ];

    $manifest = app_project_output_read_manifest($manifestPath);
    if ($manifest === null) {
        return $summary;
    }

    $artifactKey = trim((string) ($manifest['artifact_key'] ?? ''));
    $summary['manifest_exists'] = true;
    $summary['project_key'] = trim((string) ($manifest['project_key'] ?? ''));
    $summary['source_output_key'] = trim((string) ($manifest['source_output_key'] ?? ''));
    $summary['runtime_source_relative_path'] = trim((string) ($manifest['runtime_source_relative_path'] ?? ''));
    $summary['artifact_key'] = $artifactKey;
    $summary['artifact_key_valid'] = $artifactKey !== '' && app_project_output_artifact_key_is_valid($artifactKey);
    $summary['generated_at'] = trim((string) ($manifest['generated_at'] ?? ''));
    $summary['mode'] = trim((string) (($manifest['generation_summary']['mode'] ?? '') ?: ($manifest['mode'] ?? '')));

    return $summary;
}

/**
 * @param array{
 *     artifact_key:string,
 *     created_at:string,
 *     requested_by:string,
 *     manifest_path:string,
 *     bundle_manifest_path:string,
 *     bundle_root:string,
 *     runtime_source_relative_path:string
 * } $artifact
 * @return array{
 *     artifact_key:string,
 *     created_at:string,
 *     requested_by:string,
 *     manifest_path:string,
 *     bundle_manifest_path:string,
 *     runtime_manifest_path:string,
 *     runtime_manifest_exists:bool,
 *     runtime_manifest_artifact_key:string,
 *     runtime_manifest_artifact_key_valid:bool,
 *     runtime_manifest_generated_at:string,
 *     runtime_manifest_mode:string,
 *     runtime_manifest_matches_artifact:bool
 * }
 */
function app_runtime_reference_status_artifact_summary(array $artifact): array
{
    $runtimeRoot = rtrim(str_replace('\\', '/', $artifact['bundle_root']), '/')
        . '/'
        . app_runtime_storage_relative_path((string) $artifact['runtime_source_relative_path']);
    $runtimeManifest = app_runtime_reference_status_runtime_manifest_summary($runtimeRoot);

    return [
        'artifact_key' => (string) $artifact['artifact_key'],
        'created_at' => (string) $artifact['created_at'],
        'requested_by' => (string) $artifact['requested_by'],
        'manifest_path' => (string) $artifact['manifest_path'],
        'bundle_manifest_path' => (string) $artifact['bundle_manifest_path'],
        'runtime_manifest_path' => $runtimeManifest['manifest_path'],
        'runtime_manifest_exists' => $runtimeManifest['manifest_exists'],
        'runtime_manifest_artifact_key' => $runtimeManifest['artifact_key'],
        'runtime_manifest_artifact_key_valid' => $runtimeManifest['artifact_key_valid'],
        'runtime_manifest_generated_at' => $runtimeManifest['generated_at'],
        'runtime_manifest_mode' => $runtimeManifest['mode'],
        'runtime_manifest_matches_artifact' => $runtimeManifest['artifact_key_valid']
            && $runtimeManifest['artifact_key'] === (string) $artifact['artifact_key'],
    ];
}

/**
 * @return array{
 *     root:string,
 *     exists:bool,
 *     snapshot_manifest_path:string,
 *     snapshot_manifest_exists:bool,
 *     project_key:string,
 *     source_output_key:string,
 *     artifact_key:string,
 *     artifact_key_valid:bool,
 *     runtime_source_relative_path:string,
 *     captured_at:string,
 *     requested_by:string,
 *     source_root:string,
 *     runtime_manifest_path:string,
 *     runtime_manifest_exists:bool,
 *     runtime_manifest_artifact_key:string,
 *     runtime_manifest_artifact_key_valid:bool,
 *     runtime_manifest_generated_at:string,
 *     runtime_manifest_mode:string,
 *     runtime_manifest_matches_artifact:bool
 * }
 */
function app_runtime_reference_status_snapshot_summary(
    array $app,
    string $projectKey,
    string $sourceOutputKey,
    string $artifactKey,
): array {
    $snapshotRoot = app_runtime_storage_runtime_reference_snapshots_root(
        $app,
        $projectKey,
        $sourceOutputKey,
        $artifactKey,
    );
    $snapshotManifestPath = $snapshotRoot . '/' . app_runtime_reference_snapshot_manifest_relative_path();
    $runtimeManifest = app_runtime_reference_status_runtime_manifest_summary($snapshotRoot);
    $summary = [
        'root' => $snapshotRoot,
        'exists' => $snapshotRoot !== '' && is_dir($snapshotRoot),
        'snapshot_manifest_path' => $snapshotManifestPath,
        'snapshot_manifest_exists' => false,
        'project_key' => '',
        'source_output_key' => '',
        'artifact_key' => '',
        'artifact_key_valid' => false,
        'runtime_source_relative_path' => '',
        'captured_at' => '',
        'requested_by' => '',
        'source_root' => '',
        'runtime_manifest_path' => $runtimeManifest['manifest_path'],
        'runtime_manifest_exists' => $runtimeManifest['manifest_exists'],
        'runtime_manifest_artifact_key' => $runtimeManifest['artifact_key'],
        'runtime_manifest_artifact_key_valid' => $runtimeManifest['artifact_key_valid'],
        'runtime_manifest_generated_at' => $runtimeManifest['generated_at'],
        'runtime_manifest_mode' => $runtimeManifest['mode'],
        'runtime_manifest_matches_artifact' => $runtimeManifest['artifact_key_valid']
            && $runtimeManifest['artifact_key'] === $artifactKey,
    ];

    $snapshotManifest = app_project_output_read_manifest($snapshotManifestPath);
    if ($snapshotManifest === null) {
        return $summary;
    }

    $snapshotArtifactKey = trim((string) ($snapshotManifest['artifact_key'] ?? ''));
    $summary['snapshot_manifest_exists'] = true;
    $summary['project_key'] = app_normalize_project_key((string) ($snapshotManifest['project_key'] ?? ''));
    $summary['source_output_key'] = app_normalize_source_output_key((string) ($snapshotManifest['source_output_key'] ?? ''));
    $summary['artifact_key'] = $snapshotArtifactKey;
    $summary['artifact_key_valid'] = $snapshotArtifactKey !== ''
        && app_project_output_artifact_key_is_valid($snapshotArtifactKey);
    $summary['runtime_source_relative_path'] = trim((string) ($snapshotManifest['runtime_source_relative_path'] ?? ''));
    $summary['captured_at'] = trim((string) ($snapshotManifest['captured_at'] ?? ''));
    $summary['requested_by'] = trim((string) ($snapshotManifest['requested_by'] ?? ''));
    $summary['source_root'] = trim((string) ($snapshotManifest['source_root'] ?? ''));

    return $summary;
}

/**
 * @param array{
 *     exists:bool,
 *     snapshot_manifest_exists:bool,
 *     runtime_manifest_exists:bool,
 *     runtime_manifest_matches_artifact:bool
 * }|null $snapshot
 */
function app_runtime_reference_status_has_durable_recovery(?array $snapshot): bool
{
    return is_array($snapshot)
        && $snapshot['exists']
        && $snapshot['runtime_manifest_exists']
        && $snapshot['runtime_manifest_matches_artifact'];
}

/**
 * @param array{
 *     exists:bool,
 *     snapshot_manifest_exists:bool,
 *     runtime_manifest_exists:bool,
 *     runtime_manifest_matches_artifact:bool
 * }|null $snapshot
 */
function app_runtime_reference_status_durable_recovery_note(?array $snapshot): string
{
    if (!is_array($snapshot)) {
        return 'runtime reference manifest does not have a valid artifact_key yet, so no durable snapshot can be resolved.';
    }

    if (!$snapshot['exists']) {
        return 'no durable runtime reference snapshot is stored under mtool/reference/runtime-reference-snapshots for the promoted artifact.';
    }

    if (!$snapshot['runtime_manifest_exists']) {
        return 'durable snapshot root exists, but runtime-generation-manifest.json is missing.';
    }

    if (!$snapshot['runtime_manifest_matches_artifact']) {
        return 'durable snapshot root exists, but its runtime manifest does not match the promoted artifact.';
    }

    if (!$snapshot['snapshot_manifest_exists']) {
        return 'durable snapshot can restore the promoted runtime reference, but runtime-reference-snapshot.json metadata is missing.';
    }

    return 'durable snapshot can restore the promoted runtime reference after work/ cleanup.';
}

function app_runtime_reference_status_note(string $status): string
{
    return match ($status) {
        'up-to-date' => 'latest verified artifact is already promoted.',
        'stale-reference' => 'latest runtime artifact differs from the promoted runtime reference.',
        'reference-missing-provenance' => 'runtime reference manifest is missing artifact_key provenance. Re-promote a verified artifact.',
        'reference-manifest-missing' => 'runtime reference root exists, but runtime-generation-manifest.json is missing.',
        'reference-manifest-mismatch' => 'runtime reference manifest does not match the expected project/source output.',
        'reference-snapshot-only' => 'work artifact history is absent, but a durable snapshot of the promoted artifact is still available.',
        'artifact-history-missing' => 'runtime reference keeps artifact provenance, but neither comparable artifact history nor a durable snapshot is currently available.',
        'reference-missing' => 'runtime reference root does not exist.',
        'no-artifacts' => 'no runtime artifact history is available yet.',
        default => 'runtime reference status could not be classified.',
    };
}

/**
 * @param array{
 *     generated:array{
 *         dbclasses_root:string
 *     },
 *     work:array{
 *         root:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     project_key:string,
 *     source_output_key:string,
 *     status:string,
 *     note:string,
 *     is_latest_promoted:bool,
 *     needs_promote:bool,
 *     durable_recovery_ready:bool,
 *     durable_recovery_note:string,
 *     reference:array{
 *         root:string,
 *         exists:bool,
 *         manifest_path:string,
 *         manifest_exists:bool,
 *         project_key:string,
 *         source_output_key:string,
 *         runtime_source_relative_path:string,
 *         artifact_key:string,
 *         artifact_key_valid:bool,
 *         generated_at:string,
 *         mode:string
 *     },
 *     latest_artifact:array{
 *         artifact_key:string,
 *         created_at:string,
 *         requested_by:string,
 *         manifest_path:string,
 *         bundle_manifest_path:string,
 *         runtime_manifest_path:string,
 *         runtime_manifest_exists:bool,
 *         runtime_manifest_artifact_key:string,
 *         runtime_manifest_artifact_key_valid:bool,
 *         runtime_manifest_generated_at:string,
 *         runtime_manifest_mode:string,
 *         runtime_manifest_matches_artifact:bool
 *     }|null,
 *     reference_snapshot:array{
 *         root:string,
 *         exists:bool,
 *         snapshot_manifest_path:string,
 *         snapshot_manifest_exists:bool,
 *         project_key:string,
 *         source_output_key:string,
 *         artifact_key:string,
 *         artifact_key_valid:bool,
 *         runtime_source_relative_path:string,
 *         captured_at:string,
 *         requested_by:string,
 *         source_root:string,
 *         runtime_manifest_path:string,
 *         runtime_manifest_exists:bool,
 *         runtime_manifest_artifact_key:string,
 *         runtime_manifest_artifact_key_valid:bool,
 *         runtime_manifest_generated_at:string,
 *         runtime_manifest_mode:string,
 *         runtime_manifest_matches_artifact:bool
 *     }|null,
 *     error:string
 * }
 */
function app_runtime_reference_status(array $app, string $projectKey = '', string $sourceOutputKey = ''): array
{
    $normalizedProjectKey = $projectKey !== ''
        ? app_normalize_project_key($projectKey)
        : app_runtime_reference_status_default_project_key();
    $normalizedSourceOutputKey = $sourceOutputKey !== ''
        ? app_normalize_source_output_key($sourceOutputKey)
        : app_runtime_reference_status_default_source_output_key();

    if (
        $normalizedProjectKey !== app_runtime_reference_status_default_project_key()
        || $normalizedSourceOutputKey !== app_runtime_reference_status_default_source_output_key()
    ) {
        return [
            'ok' => false,
            'project_key' => $normalizedProjectKey,
            'source_output_key' => $normalizedSourceOutputKey,
            'status' => 'unsupported-target',
            'note' => '',
            'is_latest_promoted' => false,
            'needs_promote' => false,
            'durable_recovery_ready' => false,
            'durable_recovery_note' => '',
            'reference' => app_runtime_reference_status_runtime_manifest_summary(
                app_runtime_storage_runtime_dbclasses_root($app),
            ),
            'reference_snapshot' => null,
            'latest_artifact' => null,
            'error' => 'runtime reference status は MTOOL / RUNTIME-DBCLASSES のみ対応です。',
        ];
    }

    $reference = app_runtime_reference_status_runtime_manifest_summary(
        app_runtime_storage_runtime_dbclasses_root($app),
    );
    $artifactListResult = app_project_output_list($app, $normalizedProjectKey, $normalizedSourceOutputKey);
    if (!$artifactListResult['ok']) {
        return [
            'ok' => false,
            'project_key' => $normalizedProjectKey,
            'source_output_key' => $normalizedSourceOutputKey,
            'status' => 'error',
            'note' => '',
            'is_latest_promoted' => false,
            'needs_promote' => false,
            'durable_recovery_ready' => false,
            'durable_recovery_note' => '',
            'reference' => $reference,
            'reference_snapshot' => null,
            'latest_artifact' => null,
            'error' => $artifactListResult['error'],
        ];
    }

    $latestArtifact = $artifactListResult['items'] !== []
        ? app_runtime_reference_status_artifact_summary($artifactListResult['items'][0])
        : null;
    $referenceSnapshot = $reference['artifact_key_valid']
        ? app_runtime_reference_status_snapshot_summary(
            $app,
            $normalizedProjectKey,
            $normalizedSourceOutputKey,
            $reference['artifact_key'],
        )
        : null;
    $durableRecoveryReady = app_runtime_reference_status_has_durable_recovery($referenceSnapshot);

    $status = 'unknown';
    if (!$reference['exists']) {
        $status = 'reference-missing';
    } elseif (!$reference['manifest_exists']) {
        $status = 'reference-manifest-missing';
    } elseif (
        ($reference['project_key'] !== '' && $reference['project_key'] !== $normalizedProjectKey)
        || ($reference['source_output_key'] !== '' && $reference['source_output_key'] !== $normalizedSourceOutputKey)
    ) {
        $status = 'reference-manifest-mismatch';
    } elseif ($latestArtifact === null) {
        if (!$reference['artifact_key_valid']) {
            $status = 'no-artifacts';
        } elseif ($durableRecoveryReady) {
            $status = 'reference-snapshot-only';
        } else {
            $status = 'artifact-history-missing';
        }
    } elseif (!$reference['artifact_key_valid']) {
        $status = 'reference-missing-provenance';
    } elseif ($reference['artifact_key'] === $latestArtifact['artifact_key']) {
        $status = 'up-to-date';
    } else {
        $status = 'stale-reference';
    }

    return [
        'ok' => true,
        'project_key' => $normalizedProjectKey,
        'source_output_key' => $normalizedSourceOutputKey,
        'status' => $status,
        'note' => app_runtime_reference_status_note($status),
        'is_latest_promoted' => $status === 'up-to-date',
        'needs_promote' => in_array(
            $status,
            ['stale-reference', 'reference-missing-provenance', 'reference-manifest-missing'],
            true,
        ),
        'durable_recovery_ready' => $durableRecoveryReady,
        'durable_recovery_note' => app_runtime_reference_status_durable_recovery_note($referenceSnapshot),
        'reference' => $reference,
        'reference_snapshot' => $referenceSnapshot,
        'latest_artifact' => $latestArtifact,
        'error' => '',
    ];
}
