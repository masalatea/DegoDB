<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/database.php';
require_once dirname(__DIR__, 2) . '/app/database_source_repository.php';
require_once dirname(__DIR__, 2) . '/app/project_data_class_sync_service.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/app/project_table_import_service.php';
require_once dirname(__DIR__, 2) . '/app/project_table_import_source.php';
require_once dirname(__DIR__, 2) . '/app/sample_pack_catalog.php';
require_once dirname(__DIR__, 2) . '/app/source_output_repository.php';

const APP_SAMPLE12_EXTERNAL_DB_PROJECT_KEY = 'SAMPLE12';
const APP_SAMPLE12_EXTERNAL_DB_SOURCE_KEY = 'sample12_lab';
const APP_SAMPLE12_EXTERNAL_DB_TABLE_NAME = 'external_article';
const APP_SAMPLE12_EXTERNAL_DB_SOURCE_OUTPUT_KEY = 'DATACLASS-PHP';

function app_sample12_external_db_default_reference_root(): string
{
    return app_sample_pack_reference_root('sample12-external-db-source-import');
}

function app_sample12_external_db_assert_same(mixed $expected, mixed $actual, string $label, array &$errors): void
{
    if ($expected === $actual) {
        return;
    }

    $errors[] = $label
        . ': expected=' . json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ' actual=' . json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function app_sample12_external_db_tree_snapshot(string $root): array
{
    $scanResult = app_project_output_scan_tree($root);
    if (!$scanResult['ok']) {
        return [
            'ok' => false,
            'root' => $root,
            'file_count' => 0,
            'total_bytes' => 0,
            'files' => [],
            'error' => $scanResult['error'],
        ];
    }

    $files = [];
    foreach ($scanResult['files'] as $file) {
        $relativePath = (string) ($file['relative_path'] ?? '');
        if ($relativePath === '') {
            continue;
        }

        $sha256 = hash_file('sha256', $root . '/' . $relativePath);
        if (!is_string($sha256) || $sha256 === '') {
            return [
                'ok' => false,
                'root' => $root,
                'file_count' => 0,
                'total_bytes' => 0,
                'files' => [],
                'error' => 'sha256 の計算に失敗しました: ' . $relativePath,
            ];
        }

        $files[] = [
            'relative_path' => $relativePath,
            'sha256' => strtolower($sha256),
            'size' => (int) ($file['size'] ?? 0),
        ];
    }

    usort(
        $files,
        static fn (array $left, array $right): int => strcmp($left['relative_path'], $right['relative_path']),
    );

    return [
        'ok' => true,
        'root' => $root,
        'file_count' => count($files),
        'total_bytes' => (int) ($scanResult['total_bytes'] ?? 0),
        'files' => $files,
        'error' => '',
    ];
}

function app_sample12_external_db_compare_file_sets(
    array $expectedFiles,
    array $actualFiles,
    string $label,
    array &$errors,
): array {
    $expectedByPath = [];
    foreach ($expectedFiles as $file) {
        $expectedByPath[$file['relative_path']] = $file;
    }

    $actualByPath = [];
    foreach ($actualFiles as $file) {
        $actualByPath[$file['relative_path']] = $file;
    }

    $paths = array_values(array_unique(array_merge(array_keys($expectedByPath), array_keys($actualByPath))));
    sort($paths, SORT_STRING);

    $checks = [];
    foreach ($paths as $relativePath) {
        $expectedFile = $expectedByPath[$relativePath] ?? null;
        $actualFile = $actualByPath[$relativePath] ?? null;
        $expectedExists = is_array($expectedFile);
        $actualExists = is_array($actualFile);
        $expectedSha256 = $expectedExists ? (string) $expectedFile['sha256'] : '';
        $actualSha256 = $actualExists ? (string) $actualFile['sha256'] : '';
        $ok = $expectedExists && $actualExists && $expectedSha256 === $actualSha256;

        if (!$expectedExists) {
            $errors[] = $label . ' unexpected extra file: ' . $relativePath;
        } elseif (!$actualExists) {
            $errors[] = $label . ' missing file: ' . $relativePath;
        } elseif ($expectedSha256 !== $actualSha256) {
            $errors[] = $label . ' digest mismatch: ' . $relativePath
                . ' expected=' . $expectedSha256
                . ' actual=' . $actualSha256;
        }

        $checks[] = [
            'relative_path' => $relativePath,
            'expected_exists' => $expectedExists,
            'actual_exists' => $actualExists,
            'expected_sha256' => $expectedSha256,
            'actual_sha256' => $actualSha256,
            'ok' => $ok,
        ];
    }

    return $checks;
}

function app_sample12_external_db_expected_source_input(array $app): array
{
    $labDb = app_database_config($app, 'lab_db');

    return [
        'source_key' => APP_SAMPLE12_EXTERNAL_DB_SOURCE_KEY,
        'label' => 'Sample12 lab DB',
        'description' => 'Sample12 external source for live schema import verification.',
        'host' => $labDb['host'],
        'port' => $labDb['port'],
        'database_name' => $labDb['name'],
        'user_name' => $labDb['user'],
        'password' => $labDb['password'],
        'supports_live_schema_import' => true,
        'supports_proxy_runtime_read' => false,
        'proxy_runtime_priority' => 500,
        'source_of_truth' => 'sample-pack',
    ];
}

function app_sample12_external_db_prepare_fixture(array $app): array
{
    try {
        $sourceInput = app_sample12_external_db_expected_source_input($app);
        $databaseSources = app_fetch_database_sources($app);
        if (!$databaseSources['ok']) {
            return [
                'ok' => false,
                'database_source' => null,
                'error' => $databaseSources['error'],
            ];
        }

        $sourceItem = null;
        foreach ($databaseSources['items'] as $item) {
            if ((string) ($item['source_key'] ?? '') === APP_SAMPLE12_EXTERNAL_DB_SOURCE_KEY) {
                $sourceItem = $item;
                break;
            }
        }

        if ($sourceItem === null) {
            $createResult = app_create_database_source($app, $sourceInput);
            if (!$createResult['ok']) {
                return [
                    'ok' => false,
                    'database_source' => null,
                    'error' => $createResult['error'],
                ];
            }
        } else {
            $updateResult = app_update_database_source($app, (int) $sourceItem['id'], $sourceInput);
            if (!$updateResult['ok']) {
                return [
                    'ok' => false,
                    'database_source' => $sourceItem,
                    'error' => $updateResult['error'],
                ];
            }
        }

        $labPdo = app_create_pdo_from_db_config(app_database_config($app, 'lab_db'));
        $labPdo->exec(
            'CREATE TABLE IF NOT EXISTS external_article (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(191) NOT NULL,
                status VARCHAR(32) NOT NULL DEFAULT \'draft\',
                published_at DATETIME DEFAULT NULL,
                body TEXT NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_external_article_slug (slug),
                KEY idx_external_article_status_published (status, published_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $insertStatement = $labPdo->prepare(
            'INSERT INTO external_article (title, slug, status, published_at, body)
            VALUES (:title, :slug, :status, :published_at, :body)
            ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                status = VALUES(status),
                published_at = VALUES(published_at),
                body = VALUES(body)'
        );
        foreach (
            [
                [
                    'title' => 'Sample12 Imported Article',
                    'slug' => 'sample12-imported-article',
                    'status' => 'published',
                    'published_at' => '2026-06-16 10:00:00',
                    'body' => 'This article lives in the external lab database and is imported as project metadata.',
                ],
                [
                    'title' => 'Sample12 Draft Article',
                    'slug' => 'sample12-draft-article',
                    'status' => 'draft',
                    'published_at' => null,
                    'body' => 'Draft content proves nullable datetime and text field import behavior.',
                ],
            ] as $row
        ) {
            $insertStatement->execute([
                ':title' => $row['title'],
                ':slug' => $row['slug'],
                ':status' => $row['status'],
                ':published_at' => $row['published_at'],
                ':body' => $row['body'],
            ]);
        }

        $refreshedSources = app_fetch_database_sources($app);
        if (!$refreshedSources['ok']) {
            return [
                'ok' => false,
                'database_source' => null,
                'error' => $refreshedSources['error'],
            ];
        }

        $refreshedSourceItem = null;
        foreach ($refreshedSources['items'] as $item) {
            if ((string) ($item['source_key'] ?? '') === APP_SAMPLE12_EXTERNAL_DB_SOURCE_KEY) {
                $refreshedSourceItem = $item;
                break;
            }
        }

        return [
            'ok' => $refreshedSourceItem !== null,
            'database_source' => $refreshedSourceItem,
            'error' => $refreshedSourceItem !== null ? '' : 'sample12 database source の準備に失敗しました。',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'database_source' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

function app_sample12_external_db_run(array $app, string $requestedBy, string $referenceRoot): array
{
    $steps = [
        'fixture' => null,
        'database_source' => null,
        'source_options' => [],
        'table_import' => null,
        'table_preview_after_import' => null,
        'data_class_sync' => null,
        'data_class_preview_after_sync' => null,
        'output' => null,
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

    $fixtureResult = app_sample12_external_db_prepare_fixture($app);
    $steps['fixture'] = $fixtureResult;
    if (!$fixtureResult['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => [],
            'error' => $fixtureResult['error'],
        ];
    }

    $sourceItem = $fixtureResult['database_source'];
    $steps['database_source'] = $sourceItem;
    if ($sourceItem === null) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => [],
            'error' => 'sample12 database source が見つかりません。',
        ];
    }

    app_sample12_external_db_assert_same(1, (int) ($sourceItem['supports_live_schema_import'] ?? 0), 'database source supports_live_schema_import', $errors);
    app_sample12_external_db_assert_same(0, (int) ($sourceItem['supports_proxy_runtime_read'] ?? 0), 'database source supports_proxy_runtime_read', $errors);

    $sourceOptionKey = app_project_table_import_named_live_source_option_key(APP_SAMPLE12_EXTERNAL_DB_SOURCE_KEY);
    $sourceOptions = app_project_table_import_source_options(APP_SAMPLE12_EXTERNAL_DB_PROJECT_KEY, $app);
    $steps['source_options'] = $sourceOptions;
    $optionFound = false;
    foreach ($sourceOptions as $option) {
        if ((string) ($option['key'] ?? '') === $sourceOptionKey) {
            $optionFound = true;
            break;
        }
    }
    app_sample12_external_db_assert_same(true, $optionFound, 'named live source option exists', $errors);

    $tableImport = app_project_table_import_apply(
        $app,
        APP_SAMPLE12_EXTERNAL_DB_PROJECT_KEY,
        $sourceOptionKey,
        APP_SAMPLE12_EXTERNAL_DB_TABLE_NAME,
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
            'assertion_errors' => $errors,
            'error' => 'table import に失敗しました: ' . $tableImport['error'],
        ];
    }

    $tablePreview = app_project_table_import_preview(
        $app,
        APP_SAMPLE12_EXTERNAL_DB_PROJECT_KEY,
        $sourceOptionKey,
        APP_SAMPLE12_EXTERNAL_DB_TABLE_NAME,
    );
    $steps['table_preview_after_import'] = [
        'ok' => $tablePreview['ok'],
        'summary' => $tablePreview['summary'],
        'tables' => $tablePreview['tables'],
        'errors' => $tablePreview['errors'],
        'error' => $tablePreview['error'],
    ];
    if (!$tablePreview['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => 'table preview に失敗しました: ' . $tablePreview['error'],
        ];
    }

    app_sample12_external_db_assert_same(1, $tablePreview['summary']['source_table_count'], 'table source_table_count', $errors);
    app_sample12_external_db_assert_same(0, $tablePreview['summary']['table_insert_count'], 'table table_insert_count', $errors);

    $dataClassSync = app_project_data_class_sync_apply($app, APP_SAMPLE12_EXTERNAL_DB_PROJECT_KEY);
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

    $dataClassPreview = app_project_data_class_sync_preview($app, APP_SAMPLE12_EXTERNAL_DB_PROJECT_KEY);
    $steps['data_class_preview_after_sync'] = [
        'ok' => $dataClassPreview['ok'],
        'summary' => $dataClassPreview['summary'],
        'classes' => $dataClassPreview['classes'],
        'errors' => $dataClassPreview['errors'],
        'error' => $dataClassPreview['error'],
    ];
    if (!$dataClassPreview['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => 'data class preview に失敗しました: ' . $dataClassPreview['error'],
        ];
    }

    app_sample12_external_db_assert_same(1, $dataClassPreview['summary']['canonical_data_class_count'], 'data_class canonical_data_class_count', $errors);
    app_sample12_external_db_assert_same(0, $dataClassPreview['summary']['class_insert_count'], 'data_class class_insert_count', $errors);

    $sourceOutputResult = app_fetch_project_source_output_item(
        $app,
        APP_SAMPLE12_EXTERNAL_DB_PROJECT_KEY,
        APP_SAMPLE12_EXTERNAL_DB_SOURCE_OUTPUT_KEY,
    );
    if (!$sourceOutputResult['ok'] || $sourceOutputResult['item'] === null) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $sourceOutputResult['error'] !== ''
                ? $sourceOutputResult['error']
                : 'source output definition が見つかりません。',
        ];
    }

    $artifactResult = app_project_output_create_from_definition(
        $app,
        APP_SAMPLE12_EXTERNAL_DB_PROJECT_KEY,
        $sourceOutputResult['item'],
        $requestedBy,
    );
    if (!$artifactResult['ok'] || $artifactResult['artifact'] === null) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $artifactResult['error'],
        ];
    }

    $publishResult = app_project_output_publish_artifact(
        $app,
        $artifactResult['artifact'],
        $sourceOutputResult['item'],
    );
    if (!$publishResult['ok'] || $publishResult['published'] === null) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $publishResult['error'],
        ];
    }

    $publishedRoot = (string) $publishResult['published']['published_root'];
    $expectedRoot = $referenceRoot . '/' . APP_SAMPLE12_EXTERNAL_DB_SOURCE_OUTPUT_KEY;
    $expectedSnapshot = app_sample12_external_db_tree_snapshot($expectedRoot);
    $actualSnapshot = app_sample12_external_db_tree_snapshot($publishedRoot);
    if (!$expectedSnapshot['ok'] || !$actualSnapshot['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => !$expectedSnapshot['ok'] ? $expectedSnapshot['error'] : $actualSnapshot['error'],
        ];
    }

    $fileChecks = app_sample12_external_db_compare_file_sets(
        $expectedSnapshot['files'],
        $actualSnapshot['files'],
        APP_SAMPLE12_EXTERNAL_DB_SOURCE_OUTPUT_KEY,
        $errors,
    );

    $steps['output'] = [
        'source_output_key' => APP_SAMPLE12_EXTERNAL_DB_SOURCE_OUTPUT_KEY,
        'artifact_key' => $artifactResult['artifact']['artifact_key'],
        'published_root' => $publishedRoot,
        'reference_root' => $expectedRoot,
        'expected_snapshot' => $expectedSnapshot,
        'actual_snapshot' => $actualSnapshot,
        'file_checks' => $fileChecks,
    ];

    return [
        'ok' => $errors === [],
        'steps' => $steps,
        'assertion_errors' => $errors,
        'error' => $errors === [] ? '' : 'sample12 external DB source import verification failed.',
    ];
}
