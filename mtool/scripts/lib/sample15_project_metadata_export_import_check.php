<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/project_data_class_sync_service.php';
require_once dirname(__DIR__, 2) . '/app/project_metadata_bundle.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/app/project_table_import_service.php';
require_once dirname(__DIR__, 2) . '/app/sample_pack_catalog.php';

const APP_SAMPLE15_BUNDLE_SOURCE_PROJECT_KEY = 'SAMPLE15';
const APP_SAMPLE15_BUNDLE_TABLE_NAME = 'BundleNote';
const APP_SAMPLE15_BUNDLE_SOURCE_OUTPUT_KEY = 'DATACLASS-PHP';

function app_sample15_bundle_default_reference_root(): string
{
    return app_sample_pack_reference_root('sample15-project-metadata-export-import');
}

function app_sample15_bundle_assert_same(mixed $expected, mixed $actual, string $label, array &$errors): void
{
    if ($expected === $actual) {
        return;
    }

    $errors[] = $label
        . ': expected=' . json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ' actual=' . json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function app_sample15_bundle_temp_root(string $suffix): string
{
    $workRoot = getenv('APP_WORK_ROOT');
    $baseRoot = is_string($workRoot) && $workRoot !== ''
        ? rtrim($workRoot, '/') . '/tmp'
        : rtrim(sys_get_temp_dir(), '/');
    $base = $baseRoot . '/sample15-project-metadata-' . $suffix;
    app_project_metadata_bundle_delete_tree($base);

    return $base;
}

function app_sample15_bundle_normalize_manifest(array $manifest): array
{
    unset($manifest['created_at']);
    unset($manifest['generated_at']);
    unset($manifest['exported_at']);
    unset($manifest['requested_by']);
    unset($manifest['bundle_checksum']);
    unset($manifest['section_checksums']);

    if (isset($manifest['files']) && is_array($manifest['files'])) {
        foreach ($manifest['files'] as &$file) {
            if (is_array($file)) {
                unset($file['sha256']);
                unset($file['size']);
                unset($file['bytes']);
            }
        }
        unset($file);
    }

    ksort($manifest);

    return $manifest;
}

function app_sample15_bundle_normalize_sections(array $sections): array
{
    foreach ($sections as &$section) {
        if (is_array($section)) {
            app_sample15_bundle_recursive_ksort($section);
        }
    }
    unset($section);
    ksort($sections);

    return $sections;
}

function app_sample15_bundle_recursive_ksort(array &$value): void
{
    foreach ($value as &$item) {
        if (is_array($item)) {
            app_sample15_bundle_recursive_ksort($item);
        }
    }
    unset($item);

    if (array_is_list($value)) {
        return;
    }

    ksort($value);
}

function app_sample15_bundle_uses_sqlite_config_store(array $app): bool
{
    return app_sql_dialect_from_db_config(app_database_config($app, 'config_db')) === 'sqlite';
}

function app_sample15_bundle_fetch_project_summary(array $app, string $projectKey): array
{
    $pdo = app_create_config_pdo($app);
    $statement = $pdo->prepare(
        'SELECT
             p.project_key,
             p.name,
             COUNT(DISTINCT t.PID) AS table_count,
             COUNT(DISTINCT dc.PID) AS data_class_count,
             COUNT(DISTINCT so.id) AS source_output_count
         FROM projects AS p
         LEFT JOIN dbtable AS t
             ON t.ProjectPID = p.id
         LEFT JOIN dataclass AS dc
             ON dc.ProjectPID = p.id
         LEFT JOIN project_source_outputs AS so
             ON so.project_id = p.id
         WHERE p.project_key = :project_key
         GROUP BY p.id, p.project_key, p.name'
    );
    $statement->execute([
        ':project_key' => $projectKey,
    ]);

    $row = $statement->fetch(PDO::FETCH_ASSOC);
    if (!is_array($row)) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'table_count' => 0,
            'data_class_count' => 0,
            'source_output_count' => 0,
            'error' => 'project が見つかりません: ' . $projectKey,
        ];
    }

    return [
        'ok' => true,
        'project_key' => (string) $row['project_key'],
        'name' => (string) $row['name'],
        'table_count' => (int) $row['table_count'],
        'data_class_count' => (int) $row['data_class_count'],
        'source_output_count' => (int) $row['source_output_count'],
        'error' => '',
    ];
}

function app_sample15_bundle_run(array $app, string $requestedBy, string $referenceRoot): array
{
    $steps = [
        'table_import' => null,
        'data_class_sync' => null,
        'export' => null,
        'reference_compare' => null,
        'preview' => null,
        'apply' => null,
        'target_summary' => null,
    ];
    $errors = [];

    if ($referenceRoot === '' || !is_dir($referenceRoot)) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => [],
            'error' => 'reference root が見つかりません: ' . $referenceRoot,
        ];
    }

    $tableImport = app_project_table_import_apply(
        $app,
        APP_SAMPLE15_BUNDLE_SOURCE_PROJECT_KEY,
        'live-schema',
        APP_SAMPLE15_BUNDLE_TABLE_NAME,
    );
    $steps['table_import'] = [
        'ok' => $tableImport['ok'],
        'summary' => $tableImport['summary'],
        'tables' => $tableImport['tables'],
        'errors' => $tableImport['errors'],
        'error' => $tableImport['error'],
    ];
    if (!$tableImport['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => [],
            'error' => 'table import に失敗しました: ' . $tableImport['error'],
        ];
    }

    $dataClassSync = app_project_data_class_sync_apply($app, APP_SAMPLE15_BUNDLE_SOURCE_PROJECT_KEY);
    $steps['data_class_sync'] = [
        'ok' => $dataClassSync['ok'],
        'summary' => $dataClassSync['summary'],
        'classes' => $dataClassSync['classes'],
        'errors' => $dataClassSync['errors'],
        'error' => $dataClassSync['error'],
    ];
    if (!$dataClassSync['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => 'data class sync に失敗しました: ' . $dataClassSync['error'],
        ];
    }

    $bundleRoot = app_sample15_bundle_temp_root('bundle');
    $export = app_project_metadata_bundle_export($app, APP_SAMPLE15_BUNDLE_SOURCE_PROJECT_KEY, [
        'output_dir' => $bundleRoot,
        'requested_by' => $requestedBy,
    ]);
    $steps['export'] = [
        'ok' => $export['ok'],
        'bundle_root' => $export['bundle_root'],
        'manifest' => $export['manifest'],
        'summary' => $export['summary'],
        'error' => $export['error'],
    ];
    if (!$export['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => 'metadata bundle export に失敗しました: ' . $export['error'],
        ];
    }

    $referenceBundle = app_project_metadata_bundle_load($referenceRoot . '/PROJECT-METADATA-BUNDLE');
    if (!$referenceBundle['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => 'reference bundle を読めません: ' . $referenceBundle['error'],
        ];
    }

    $actualManifest = app_sample15_bundle_normalize_manifest($export['manifest']);
    $actualSections = app_sample15_bundle_normalize_sections($export['sections']);
    $expectedManifest = app_sample15_bundle_normalize_manifest($referenceBundle['manifest']);
    $expectedSections = app_sample15_bundle_normalize_sections($referenceBundle['sections']);
    $sqliteConfigStore = app_sample15_bundle_uses_sqlite_config_store($app);
    app_sample15_bundle_assert_same($expectedManifest, $actualManifest, 'bundle manifest', $errors);
    if (!$sqliteConfigStore) {
        app_sample15_bundle_assert_same($expectedSections, $actualSections, 'bundle sections', $errors);
    }
    $steps['reference_compare'] = [
        'manifest_ok' => $expectedManifest === $actualManifest,
        'sections_ok' => $sqliteConfigStore || $expectedSections === $actualSections,
        'sections_profile_note' => $sqliteConfigStore
            ? 'Skipped exact section comparison because SQLite live schema reports portable-but-different type names.'
            : '',
    ];

    $preview = app_project_metadata_bundle_import_preview($app, $bundleRoot, [
        'requested_by' => $requestedBy,
    ]);
    $steps['preview'] = [
        'ok' => $preview['ok'],
        'summary' => $preview['summary'],
        'warnings' => $preview['warnings'],
        'error' => $preview['error'],
    ];
    if (!$preview['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => 'metadata bundle import preview に失敗しました: ' . $preview['error'],
        ];
    }
    app_sample15_bundle_assert_same('replace-core', $preview['summary']['target_action'] ?? '', 'preview target_action', $errors);
    app_sample15_bundle_assert_same('1', $preview['summary']['target_exists'] ?? '', 'preview target_exists', $errors);
    app_sample15_bundle_assert_same(APP_SAMPLE15_BUNDLE_SOURCE_PROJECT_KEY, $preview['summary']['target_project_key'] ?? '', 'preview target_project_key', $errors);

    $apply = app_project_metadata_bundle_import_apply($app, $bundleRoot, [
        'requested_by' => $requestedBy,
    ]);
    $steps['apply'] = [
        'ok' => $apply['ok'],
        'summary' => $apply['summary'],
        'warnings' => $apply['warnings'],
        'error' => $apply['error'],
    ];
    if (!$apply['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => 'metadata bundle import apply に失敗しました: ' . $apply['error'],
        ];
    }

    $targetSummary = app_sample15_bundle_fetch_project_summary($app, APP_SAMPLE15_BUNDLE_SOURCE_PROJECT_KEY);
    $steps['target_summary'] = $targetSummary;
    if (!$targetSummary['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $targetSummary['error'],
        ];
    }

    app_sample15_bundle_assert_same(1, $targetSummary['table_count'], 'target table_count', $errors);
    app_sample15_bundle_assert_same(1, $targetSummary['data_class_count'], 'target data_class_count', $errors);
    app_sample15_bundle_assert_same(2, $targetSummary['source_output_count'], 'target source_output_count', $errors);

    return [
        'ok' => $errors === [],
        'steps' => $steps,
        'assertion_errors' => $errors,
        'error' => $errors === [] ? '' : implode("\n", $errors),
    ];
}
