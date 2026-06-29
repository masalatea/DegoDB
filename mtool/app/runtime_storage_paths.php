<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';

function app_runtime_storage_repo_root(): string
{
    $resolved = realpath(dirname(__DIR__, 2));
    if (is_string($resolved) && $resolved !== '') {
        return str_replace('\\', '/', $resolved);
    }

    return str_replace('\\', '/', dirname(__DIR__, 2));
}

function app_runtime_storage_relative_path(string ...$segments): string
{
    $parts = [];

    foreach ($segments as $segment) {
        $normalized = trim(str_replace('\\', '/', $segment));
        if ($normalized === '') {
            continue;
        }

        foreach (explode('/', $normalized) as $part) {
            $trimmed = trim($part);
            if ($trimmed === '' || $trimmed === '.') {
                continue;
            }

            $parts[] = $trimmed;
        }
    }

    return implode('/', $parts);
}

function app_runtime_storage_repo_path(string $relativePath = ''): string
{
    $normalized = app_runtime_storage_relative_path($relativePath);
    if ($normalized === '') {
        return app_runtime_storage_repo_root();
    }

    return app_runtime_storage_repo_root() . '/' . $normalized;
}

function app_runtime_storage_legacy_dbclasses_fixture_relative_path(string $relativePath = ''): string
{
    return app_runtime_storage_relative_path(
        'tests',
        'fixtures',
        'legacy-dbclasses',
        $relativePath,
    );
}

function app_runtime_storage_legacy_dbclasses_fixture_root(): string
{
    return app_runtime_storage_repo_path(app_runtime_storage_legacy_dbclasses_fixture_relative_path());
}

function app_runtime_storage_legacy_dbclasses_fixture_path(string $relativePath = ''): string
{
    return app_runtime_storage_repo_path(app_runtime_storage_legacy_dbclasses_fixture_relative_path($relativePath));
}

function app_runtime_storage_mtool_reference_relative_path(string $relativePath = ''): string
{
    return app_runtime_storage_relative_path('mtool', 'reference', $relativePath);
}

function app_runtime_storage_runtime_reference_snapshots_relative_path(
    string $projectKey = '',
    string $sourceOutputKey = '',
    string $artifactKey = '',
    string $relativePath = '',
): string {
    return app_runtime_storage_relative_path(
        'runtime-reference-snapshots',
        app_normalize_project_key($projectKey),
        app_normalize_source_output_key($sourceOutputKey),
        trim($artifactKey),
        $relativePath,
    );
}

function app_runtime_storage_runtime_reference_snapshots_repo_relative_path(
    string $projectKey = '',
    string $sourceOutputKey = '',
    string $artifactKey = '',
    string $relativePath = '',
): string {
    return app_runtime_storage_mtool_reference_relative_path(
        app_runtime_storage_runtime_reference_snapshots_relative_path(
            $projectKey,
            $sourceOutputKey,
            $artifactKey,
            $relativePath,
        ),
    );
}

function app_runtime_storage_runtime_reference_snapshots_repo_root(
    string $projectKey = '',
    string $sourceOutputKey = '',
    string $artifactKey = '',
    string $relativePath = '',
): string {
    return app_runtime_storage_repo_path(
        app_runtime_storage_runtime_reference_snapshots_repo_relative_path(
            $projectKey,
            $sourceOutputKey,
            $artifactKey,
            $relativePath,
        ),
    );
}

function app_runtime_storage_reference_repo_relative_path(string $relativePath = ''): string
{
    return app_runtime_storage_mtool_reference_relative_path($relativePath);
}

function app_runtime_storage_default_reference_root(): string
{
    return app_runtime_storage_repo_path(app_runtime_storage_mtool_reference_relative_path());
}

function app_runtime_storage_default_generated_root(): string
{
    return app_runtime_storage_default_reference_root();
}

function app_runtime_storage_default_work_root(): string
{
    return app_runtime_storage_repo_path('work');
}

function app_runtime_storage_generated_repo_relative_path(string $relativePath = ''): string
{
    return app_runtime_storage_runtime_source_repo_relative_path($relativePath);
}

function app_runtime_storage_work_repo_relative_path(string $relativePath = ''): string
{
    return app_runtime_storage_relative_path('work', $relativePath);
}

function app_runtime_storage_generated_root(array $app): string
{
    $root = $app['reference']['root'] ?? $app['generated']['root'] ?? app_runtime_storage_default_generated_root();
    if (!is_string($root) || trim($root) === '') {
        return app_runtime_storage_default_generated_root();
    }

    return rtrim(str_replace('\\', '/', $root), '/');
}

function app_runtime_storage_reference_root(array $app): string
{
    return app_runtime_storage_generated_root($app);
}

function app_runtime_storage_work_root(array $app): string
{
    $root = $app['work']['root'] ?? app_runtime_storage_default_work_root();
    if (!is_string($root) || trim($root) === '') {
        return app_runtime_storage_default_work_root();
    }

    return rtrim(str_replace('\\', '/', $root), '/');
}

function app_runtime_storage_path_is_absolute(string $value): bool
{
    return $value !== ''
        && ($value[0] === '/' || preg_match('/^[A-Za-z]:[\\\\\\/]/', $value) === 1);
}

function app_runtime_storage_path_from_generated_root(
    string $generatedRoot,
    string $relativePath = '',
): string {
    $normalizedRoot = rtrim(str_replace('\\', '/', $generatedRoot), '/');
    $normalizedPath = app_runtime_storage_relative_path($relativePath);

    if ($normalizedRoot === '') {
        return $normalizedPath;
    }

    if ($normalizedPath === '') {
        return $normalizedRoot;
    }

    return $normalizedRoot . '/' . $normalizedPath;
}

function app_runtime_storage_path_from_reference_root(
    string $referenceRoot,
    string $relativePath = '',
): string {
    return app_runtime_storage_path_from_generated_root($referenceRoot, $relativePath);
}

function app_runtime_storage_runtime_reference_snapshots_root_from_reference_root(
    string $referenceRoot,
    string $projectKey = '',
    string $sourceOutputKey = '',
    string $artifactKey = '',
    string $relativePath = '',
): string {
    return app_runtime_storage_path_from_reference_root(
        $referenceRoot,
        app_runtime_storage_runtime_reference_snapshots_relative_path(
            $projectKey,
            $sourceOutputKey,
            $artifactKey,
            $relativePath,
        ),
    );
}

function app_runtime_storage_generated_path(array $app, string $relativePath = ''): string
{
    return app_runtime_storage_reference_path($app, $relativePath);
}

function app_runtime_storage_reference_path(array $app, string $relativePath = ''): string
{
    return app_runtime_storage_path_from_reference_root(
        app_runtime_storage_reference_root($app),
        $relativePath,
    );
}

function app_runtime_storage_runtime_reference_snapshots_root(
    array $app,
    string $projectKey = '',
    string $sourceOutputKey = '',
    string $artifactKey = '',
    string $relativePath = '',
): string {
    return app_runtime_storage_runtime_reference_snapshots_root_from_reference_root(
        app_runtime_storage_reference_root($app),
        $projectKey,
        $sourceOutputKey,
        $artifactKey,
        $relativePath,
    );
}

function app_runtime_storage_path_from_work_root(
    string $workRoot,
    string $relativePath = '',
): string {
    $normalizedRoot = rtrim(str_replace('\\', '/', $workRoot), '/');
    $normalizedPath = app_runtime_storage_relative_path($relativePath);

    if ($normalizedRoot === '') {
        return $normalizedPath;
    }

    if ($normalizedPath === '') {
        return $normalizedRoot;
    }

    return $normalizedRoot . '/' . $normalizedPath;
}

function app_runtime_storage_work_path(array $app, string $relativePath = ''): string
{
    return app_runtime_storage_path_from_work_root(
        app_runtime_storage_work_root($app),
        $relativePath,
    );
}

function app_runtime_storage_runtime_dbclasses_relative_path(): string
{
    return app_runtime_storage_relative_path('mtool', 'dbclasses');
}

function app_runtime_storage_runtime_dbclasses_root_from_generated_root(string $generatedRoot): string
{
    return app_runtime_storage_path_from_reference_root(
        $generatedRoot,
        app_runtime_storage_relative_path('dbclasses'),
    );
}

function app_runtime_storage_runtime_dbclasses_root(array $app): string
{
    return app_runtime_storage_reference_path(
        $app,
        app_runtime_storage_relative_path('dbclasses'),
    );
}

function app_runtime_storage_html_source_outputs_relative_path(
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_relative_path(
        'mtool',
        'html-source-outputs',
        app_normalize_project_key($projectKey),
        app_normalize_source_output_key($sourceOutputKey),
    );
}

function app_runtime_storage_proxy_source_outputs_relative_path(
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_relative_path(
        'mtool',
        'proxy-source-outputs',
        app_normalize_project_key($projectKey),
        app_normalize_source_output_key($sourceOutputKey),
    );
}

function app_runtime_storage_openapi_source_outputs_relative_path(
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_relative_path(
        'mtool',
        'openapi-source-outputs',
        app_normalize_project_key($projectKey),
        app_normalize_source_output_key($sourceOutputKey),
    );
}

function app_runtime_storage_shared_contract_source_outputs_relative_path(
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_relative_path(
        'mtool',
        'shared-contract-source-outputs',
        app_normalize_project_key($projectKey),
        app_normalize_source_output_key($sourceOutputKey),
    );
}

function app_runtime_storage_typescript_dto_source_outputs_relative_path(
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_relative_path(
        'mtool',
        'typescript-dto-source-outputs',
        app_normalize_project_key($projectKey),
        app_normalize_source_output_key($sourceOutputKey),
    );
}

function app_runtime_storage_ai_context_source_outputs_relative_path(
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_relative_path(
        'mtool',
        'ai-context-source-outputs',
        app_normalize_project_key($projectKey),
        app_normalize_source_output_key($sourceOutputKey),
    );
}

function app_runtime_storage_legacy_source_outputs_relative_path(
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_relative_path(
        'mtool',
        'legacy-source-outputs',
        app_normalize_project_key($projectKey),
        app_normalize_source_output_key($sourceOutputKey),
    );
}

function app_runtime_storage_data_class_source_outputs_relative_path(
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_relative_path(
        'mtool',
        'dataclass-source-outputs',
        app_normalize_project_key($projectKey),
        app_normalize_source_output_key($sourceOutputKey),
    );
}

function app_runtime_storage_db_access_source_outputs_relative_path(
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_relative_path(
        'mtool',
        'dbaccess-source-outputs',
        app_normalize_project_key($projectKey),
        app_normalize_source_output_key($sourceOutputKey),
    );
}

function app_runtime_storage_generated_source_outputs_relative_path(
    string $projectKey = '',
    string $artifactKey = '',
): string {
    return app_runtime_storage_source_output_artifacts_relative_path($projectKey, $artifactKey);
}

function app_runtime_storage_source_output_artifacts_relative_path(
    string $projectKey = '',
    string $artifactKey = '',
): string {
    return app_runtime_storage_relative_path(
        'artifacts',
        'source-outputs',
        app_normalize_project_key($projectKey),
        trim($artifactKey),
    );
}

function app_runtime_storage_generated_source_outputs_root(
    array $app,
    string $projectKey = '',
    string $artifactKey = '',
): string {
    return app_runtime_storage_source_output_artifacts_root($app, $projectKey, $artifactKey);
}

function app_runtime_storage_source_output_artifacts_root(
    array $app,
    string $projectKey = '',
    string $artifactKey = '',
): string {
    return app_runtime_storage_work_path(
        $app,
        app_runtime_storage_source_output_artifacts_relative_path($projectKey, $artifactKey),
    );
}

function app_runtime_storage_project_output_runtime_staging_relative_path(
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_relative_path(
        'staging',
        'project-output-runtime',
        app_normalize_project_key($projectKey),
        app_normalize_source_output_key($sourceOutputKey),
    );
}

function app_runtime_storage_project_output_runtime_staging_root(
    array $app,
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_work_path(
        $app,
        app_runtime_storage_project_output_runtime_staging_relative_path($projectKey, $sourceOutputKey),
    );
}

function app_runtime_storage_compare_output_workspace_relative_path(
    string $projectKey = '',
    string $path = '',
): string {
    return app_runtime_storage_relative_path(
        'compare-output',
        app_normalize_project_key($projectKey),
        trim($path),
    );
}

function app_runtime_storage_compare_output_workspace_root(
    array $app,
    string $projectKey = '',
    string $path = '',
): string {
    return app_runtime_storage_work_path(
        $app,
        app_runtime_storage_compare_output_workspace_relative_path($projectKey, $path),
    );
}

function app_runtime_storage_compare_output_assets_relative_path(
    string $projectKey = '',
    string $path = '',
): string {
    return app_runtime_storage_relative_path(
        'compare-output-assets',
        app_normalize_project_key($projectKey),
        trim($path),
    );
}

function app_runtime_storage_compare_output_assets_root(
    array $app,
    string $projectKey = '',
    string $path = '',
): string {
    return app_runtime_storage_work_path(
        $app,
        app_runtime_storage_compare_output_assets_relative_path($projectKey, $path),
    );
}

function app_runtime_storage_compare_output_jobs_relative_path(
    string $projectKey = '',
    string $jobKey = '',
): string {
    return app_runtime_storage_relative_path(
        'job-history',
        'compare-output',
        app_normalize_project_key($projectKey),
        trim($jobKey),
    );
}

function app_runtime_storage_compare_output_jobs_root(
    array $app,
    string $projectKey = '',
    string $jobKey = '',
): string {
    return app_runtime_storage_work_path(
        $app,
        app_runtime_storage_compare_output_jobs_relative_path($projectKey, $jobKey),
    );
}

function app_runtime_storage_build_jobs_relative_path(
    string $projectKey = '',
    string $jobKey = '',
): string {
    return app_runtime_storage_relative_path(
        'job-history',
        'build',
        app_normalize_project_key($projectKey),
        trim($jobKey),
    );
}

function app_runtime_storage_build_jobs_root(
    array $app,
    string $projectKey = '',
    string $jobKey = '',
): string {
    return app_runtime_storage_work_path(
        $app,
        app_runtime_storage_build_jobs_relative_path($projectKey, $jobKey),
    );
}

function app_runtime_storage_endpoint_test_jobs_relative_path(
    string $projectKey = '',
    string $jobKey = '',
): string {
    return app_runtime_storage_relative_path(
        'job-history',
        'endpoint-test',
        app_normalize_project_key($projectKey),
        trim($jobKey),
    );
}

function app_runtime_storage_endpoint_test_jobs_root(
    array $app,
    string $projectKey = '',
    string $jobKey = '',
): string {
    return app_runtime_storage_work_path(
        $app,
        app_runtime_storage_endpoint_test_jobs_relative_path($projectKey, $jobKey),
    );
}

function app_runtime_storage_work_source_outputs_relative_path(
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_relative_path(
        'source-outputs',
        app_normalize_project_key($projectKey),
        app_normalize_source_output_key($sourceOutputKey),
    );
}

function app_runtime_storage_work_source_outputs_root(
    array $app,
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_work_path(
        $app,
        app_runtime_storage_work_source_outputs_relative_path($projectKey, $sourceOutputKey),
    );
}

function app_runtime_storage_source_output_temp_relative_path(
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_relative_path(
        'staging',
        'source-outputs',
        app_normalize_project_key($projectKey),
        app_normalize_source_output_key($sourceOutputKey),
    );
}

function app_runtime_storage_source_output_temp_root(
    array $app,
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_work_path(
        $app,
        app_runtime_storage_source_output_temp_relative_path($projectKey, $sourceOutputKey),
    );
}

function app_runtime_storage_custom_source_outputs_relative_path(
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_mtool_extensions_relative_path($projectKey, $sourceOutputKey);
}

function app_runtime_storage_mtool_extensions_relative_path(
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_relative_path(
        'mtool',
        'extensions',
        app_normalize_project_key($projectKey),
        app_normalize_source_output_key($sourceOutputKey),
    );
}

function app_runtime_storage_custom_source_outputs_root(
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_mtool_extensions_root($projectKey, $sourceOutputKey);
}

function app_runtime_storage_mtool_extensions_root(
    string $projectKey = '',
    string $sourceOutputKey = '',
): string {
    return app_runtime_storage_repo_path(
        app_runtime_storage_mtool_extensions_relative_path($projectKey, $sourceOutputKey),
    );
}

function app_runtime_storage_runtime_source_stage_repo_relative_path(string $relativePath = ''): string
{
    return app_runtime_storage_work_repo_relative_path(
        app_runtime_storage_relative_path('runtime-sources', $relativePath),
    );
}

/**
 * @return array<string,string>
 */
function app_runtime_storage_generated_relative_prefix_map(): array
{
    return [
        'mtool/dbclasses' => app_runtime_storage_runtime_dbclasses_relative_path(),
        'runtime-sources/mtool/dbclasses' => app_runtime_storage_runtime_dbclasses_relative_path(),
        'runtime-sources/mtool/html-source-outputs' => app_runtime_storage_relative_path('mtool', 'html-source-outputs'),
        'mtool/html-source-outputs' => app_runtime_storage_relative_path('mtool', 'html-source-outputs'),
        'runtime-sources/mtool/proxy-source-outputs' => app_runtime_storage_relative_path('mtool', 'proxy-source-outputs'),
        'mtool/proxy-source-outputs' => app_runtime_storage_relative_path('mtool', 'proxy-source-outputs'),
        'runtime-sources/mtool/shared-contract-source-outputs' => app_runtime_storage_relative_path('mtool', 'shared-contract-source-outputs'),
        'mtool/shared-contract-source-outputs' => app_runtime_storage_relative_path('mtool', 'shared-contract-source-outputs'),
        'runtime-sources/mtool/typescript-dto-source-outputs' => app_runtime_storage_relative_path('mtool', 'typescript-dto-source-outputs'),
        'mtool/typescript-dto-source-outputs' => app_runtime_storage_relative_path('mtool', 'typescript-dto-source-outputs'),
        'runtime-sources/mtool/ai-context-source-outputs' => app_runtime_storage_relative_path('mtool', 'ai-context-source-outputs'),
        'mtool/ai-context-source-outputs' => app_runtime_storage_relative_path('mtool', 'ai-context-source-outputs'),
        'runtime-sources/mtool/legacy-source-outputs' => app_runtime_storage_relative_path('mtool', 'legacy-source-outputs'),
        'mtool/legacy-source-outputs' => app_runtime_storage_relative_path('mtool', 'legacy-source-outputs'),
        'runtime-sources/mtool/dataclass-source-outputs' => app_runtime_storage_relative_path('mtool', 'dataclass-source-outputs'),
        'mtool/dataclass-source-outputs' => app_runtime_storage_relative_path('mtool', 'dataclass-source-outputs'),
        'runtime-sources/mtool/dbaccess-source-outputs' => app_runtime_storage_relative_path('mtool', 'dbaccess-source-outputs'),
        'mtool/dbaccess-source-outputs' => app_runtime_storage_relative_path('mtool', 'dbaccess-source-outputs'),
    ];
}

function app_runtime_storage_runtime_source_repo_relative_path(string $relativePath = ''): string
{
    $canonical = app_runtime_storage_canonical_generated_relative_path($relativePath);
    if ($canonical === '') {
        return '';
    }

    $dbclassesPrefix = app_runtime_storage_runtime_dbclasses_relative_path();
    if ($canonical === $dbclassesPrefix || str_starts_with($canonical, $dbclassesPrefix . '/')) {
        $suffix = ltrim(substr($canonical, strlen($dbclassesPrefix)), '/');

        return app_runtime_storage_reference_repo_relative_path(
            app_runtime_storage_relative_path('dbclasses', $suffix),
        );
    }

    foreach ([
        app_runtime_storage_relative_path('mtool', 'html-source-outputs'),
        app_runtime_storage_relative_path('mtool', 'proxy-source-outputs'),
        app_runtime_storage_relative_path('mtool', 'shared-contract-source-outputs'),
        app_runtime_storage_relative_path('mtool', 'typescript-dto-source-outputs'),
        app_runtime_storage_relative_path('mtool', 'ai-context-source-outputs'),
        app_runtime_storage_relative_path('mtool', 'legacy-source-outputs'),
        app_runtime_storage_relative_path('mtool', 'dataclass-source-outputs'),
        app_runtime_storage_relative_path('mtool', 'dbaccess-source-outputs'),
    ] as $stagePrefix) {
        if ($canonical === $stagePrefix || str_starts_with($canonical, $stagePrefix . '/')) {
            return app_runtime_storage_runtime_source_stage_repo_relative_path($canonical);
        }
    }

    return app_runtime_storage_runtime_source_stage_repo_relative_path($canonical);
}

function app_runtime_storage_runtime_source_root(array $app, string $relativePath = ''): string
{
    $repoRelativePath = app_runtime_storage_runtime_source_repo_relative_path($relativePath);
    $normalized = app_runtime_storage_relative_path($repoRelativePath);
    if ($normalized === '') {
        return app_runtime_storage_repo_root();
    }

    $referencePrefix = app_runtime_storage_mtool_reference_relative_path();
    if ($normalized === $referencePrefix || str_starts_with($normalized, $referencePrefix . '/')) {
        $suffix = ltrim(substr($normalized, strlen($referencePrefix)), '/');

        return app_runtime_storage_reference_path($app, $suffix);
    }

    $workPrefix = 'work';
    if ($normalized === $workPrefix || str_starts_with($normalized, $workPrefix . '/')) {
        $suffix = ltrim(substr($normalized, strlen($workPrefix)), '/');

        return app_runtime_storage_work_path($app, $suffix);
    }

    return app_runtime_storage_repo_path($normalized);
}

/**
 * @return array<string,string>
 */
function app_runtime_storage_work_relative_prefix_map(): array
{
    return [
        'artifacts/source-outputs' => app_runtime_storage_source_output_artifacts_relative_path(),
        'staging/project-output-runtime' => app_runtime_storage_project_output_runtime_staging_relative_path(),
        'project-output-runtime-staging' => app_runtime_storage_project_output_runtime_staging_relative_path(),
        'workspaces/compare-output' => app_runtime_storage_compare_output_workspace_relative_path(),
        'compare-output' => app_runtime_storage_compare_output_workspace_relative_path(),
        'state/compare-output-assets' => app_runtime_storage_compare_output_assets_relative_path(),
        'compare-output-assets' => app_runtime_storage_compare_output_assets_relative_path(),
        'job-history/compare-output' => app_runtime_storage_compare_output_jobs_relative_path(),
        'compare-output-jobs' => app_runtime_storage_compare_output_jobs_relative_path(),
        'job-history/build' => app_runtime_storage_build_jobs_relative_path(),
        'build-jobs' => app_runtime_storage_build_jobs_relative_path(),
        'job-history/endpoint-test' => app_runtime_storage_endpoint_test_jobs_relative_path(),
        'endpoint-test-jobs' => app_runtime_storage_endpoint_test_jobs_relative_path(),
    ];
}

/**
 * @param array<string,string> $prefixMap
 * @return array{
 *     matched:bool,
 *     path:string
 * }
 */
function app_runtime_storage_canonicalize_prefixed_relative_path(
    string $relativePath,
    array $prefixMap,
): array {
    $normalized = app_runtime_storage_relative_path($relativePath);
    if ($normalized === '') {
        return [
            'matched' => false,
            'path' => '',
        ];
    }

    $matchedPrefix = '';
    $canonicalPrefix = '';

    foreach ($prefixMap as $sourcePrefix => $targetPrefix) {
        $normalizedSourcePrefix = app_runtime_storage_relative_path($sourcePrefix);
        if ($normalizedSourcePrefix === '') {
            continue;
        }

        if (
            $normalized !== $normalizedSourcePrefix
            && !str_starts_with($normalized, $normalizedSourcePrefix . '/')
        ) {
            continue;
        }

        if ($matchedPrefix !== '' && strlen($normalizedSourcePrefix) <= strlen($matchedPrefix)) {
            continue;
        }

        $matchedPrefix = $normalizedSourcePrefix;
        $canonicalPrefix = app_runtime_storage_relative_path($targetPrefix);
    }

    if ($matchedPrefix === '') {
        return [
            'matched' => false,
            'path' => $normalized,
        ];
    }

    $suffix = substr($normalized, strlen($matchedPrefix));

    return [
        'matched' => true,
        'path' => app_runtime_storage_relative_path($canonicalPrefix, $suffix),
    ];
}

function app_runtime_storage_canonical_generated_relative_path(string $relativePath): string
{
    $result = app_runtime_storage_canonicalize_prefixed_relative_path(
        $relativePath,
        app_runtime_storage_generated_relative_prefix_map(),
    );

    return $result['path'];
}

/**
 * @return array{
 *     matched:bool,
 *     path:string
 * }
 */
function app_runtime_storage_canonical_work_relative_path_result(string $relativePath): array
{
    $normalized = app_runtime_storage_relative_path($relativePath);
    if (
        preg_match('#^scenarios/([^/]+)/(.*)$#', $normalized, $matches) === 1
        && isset($matches[1], $matches[2])
    ) {
        $scenarioRelativeResult = app_runtime_storage_canonicalize_prefixed_relative_path(
            (string) $matches[2],
            app_runtime_storage_work_relative_prefix_map(),
        );
        if ($scenarioRelativeResult['matched']) {
            return [
                'matched' => true,
                'path' => app_runtime_storage_relative_path(
                    'scenarios',
                    (string) $matches[1],
                    $scenarioRelativeResult['path'],
                ),
            ];
        }
    }

    return app_runtime_storage_canonicalize_prefixed_relative_path(
        $normalized,
        app_runtime_storage_work_relative_prefix_map(),
    );
}

function app_runtime_storage_canonical_work_relative_path(string $relativePath): string
{
    $result = app_runtime_storage_canonical_work_relative_path_result($relativePath);

    return $result['path'];
}

function app_runtime_storage_canonical_repo_relative_path(string $relativePath): string
{
    $normalized = app_runtime_storage_relative_path($relativePath);
    if ($normalized === '') {
        return '';
    }

    if ($normalized === 'shared/reference' || str_starts_with($normalized, 'shared/reference/')) {
        $suffix = ltrim(substr($normalized, strlen('shared/reference')), '/');

        return app_runtime_storage_mtool_reference_relative_path($suffix);
    }

    if ($normalized === 'mtool/shared/reference' || str_starts_with($normalized, 'mtool/shared/reference/')) {
        $suffix = ltrim(substr($normalized, strlen('mtool/shared/reference')), '/');

        return app_runtime_storage_mtool_reference_relative_path($suffix);
    }

    if ($normalized === 'reference' || str_starts_with($normalized, 'reference/')) {
        $suffix = ltrim(substr($normalized, strlen('reference')), '/');

        return app_runtime_storage_mtool_reference_relative_path($suffix);
    }

    if ($normalized === 'mtool/reference' || str_starts_with($normalized, 'mtool/reference/')) {
        $suffix = ltrim(substr($normalized, strlen('mtool/reference')), '/');

        return app_runtime_storage_mtool_reference_relative_path($suffix);
    }

    if ($normalized === 'sample/source-outputs' || str_starts_with($normalized, 'sample/source-outputs/')) {
        $suffix = ltrim(substr($normalized, strlen('sample/source-outputs')), '/');

        return app_runtime_storage_work_repo_relative_path(
            app_runtime_storage_relative_path('source-outputs', $suffix),
        );
    }

    if ($normalized === 'generated' || str_starts_with($normalized, 'generated/')) {
        $generatedRelativePath = ltrim(substr($normalized, strlen('generated')), '/');
        $generatedRelativeResult = app_runtime_storage_canonicalize_prefixed_relative_path(
            $generatedRelativePath,
            app_runtime_storage_generated_relative_prefix_map(),
        );
        if ($generatedRelativeResult['matched']) {
            return app_runtime_storage_runtime_source_repo_relative_path($generatedRelativeResult['path']);
        }

        $workRelativeResult = app_runtime_storage_canonical_work_relative_path_result($generatedRelativePath);
        if ($workRelativeResult['matched']) {
            return app_runtime_storage_work_repo_relative_path($workRelativeResult['path']);
        }

        return app_runtime_storage_work_repo_relative_path(
            app_runtime_storage_relative_path('legacy-generated', $generatedRelativePath),
        );
    }

    if ($normalized === 'work' || str_starts_with($normalized, 'work/')) {
        $workRelativePath = ltrim(substr($normalized, strlen('work')), '/');

        return app_runtime_storage_work_repo_relative_path(
            app_runtime_storage_canonical_work_relative_path($workRelativePath),
        );
    }

    $generatedRelativeResult = app_runtime_storage_canonicalize_prefixed_relative_path(
        $normalized,
        app_runtime_storage_generated_relative_prefix_map(),
    );
    if ($generatedRelativeResult['matched']) {
        return app_runtime_storage_runtime_source_repo_relative_path($generatedRelativeResult['path']);
    }

    $workRelativeResult = app_runtime_storage_canonical_work_relative_path_result($normalized);
    if ($workRelativeResult['matched']) {
        return app_runtime_storage_work_repo_relative_path($workRelativeResult['path']);
    }

    return $normalized;
}
