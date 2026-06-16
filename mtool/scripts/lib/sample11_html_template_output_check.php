<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/database.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/app/sample_pack_catalog.php';
require_once dirname(__DIR__, 2) . '/app/source_output_repository.php';

const APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_PROJECT_KEY = 'SAMPLE11';
const APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_SOURCE_OUTPUT_KEY = 'HTML-PAGE';
const APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_HTML_KEY = 'SAMPLE11-PAGE';
const APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_TEMPLATE_PID = 110100;
const APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_LEGACY_SOURCE_OUTPUT_PID = 110030;
const APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_REFERENCE_FILES = [
    'README.md',
    'page.php',
];

function app_sample11_html_template_output_default_reference_root(): string
{
    return app_sample_pack_reference_root('sample11-html-template-output') . '/HTML-PAGE';
}

function app_sample11_html_template_output_assert_same(
    mixed $expected,
    mixed $actual,
    string $label,
    array &$errors,
): void {
    if ($expected === $actual) {
        return;
    }

    $errors[] = $label
        . ': expected=' . json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ' actual=' . json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function app_sample11_html_template_output_tree_snapshot(string $root): array
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

        $absolutePath = $root . '/' . $relativePath;
        $sha256 = hash_file('sha256', $absolutePath);
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

function app_sample11_html_template_output_compare_file_sets(
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

function app_sample11_html_template_output_fetch_one(PDO $pdo, string $sql, array $params): ?array
{
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    return is_array($row) ? $row : null;
}

function app_sample11_html_template_output_fetch_all(PDO $pdo, string $sql, array $params): array
{
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    return is_array($rows) ? $rows : [];
}

function app_sample11_html_template_output_verify_metadata(array $app, array &$errors): array
{
    $pdo = app_create_config_pdo($app);

    $project = app_sample11_html_template_output_fetch_one(
        $pdo,
        'SELECT id, project_key, slug FROM projects WHERE project_key = :project_key',
        [':project_key' => APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_PROJECT_KEY],
    );
    if ($project === null) {
        $errors[] = 'SAMPLE11 project row missing.';

        return [];
    }

    $projectId = (int) ($project['id'] ?? 0);
    $binding = app_sample11_html_template_output_fetch_one(
        $pdo,
        'SELECT legacy_project_source_output_pid, source_output_key, module_source_ref, refresh_policy
           FROM project_html_source_bindings
          WHERE project_id = :project_id
            AND legacy_project_source_output_pid = :legacy_pid',
        [
            ':project_id' => $projectId,
            ':legacy_pid' => APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_LEGACY_SOURCE_OUTPUT_PID,
        ],
    );
    if ($binding === null) {
        $errors[] = 'sample11 HTML source binding row missing.';
    } else {
        app_sample11_html_template_output_assert_same(
            APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_SOURCE_OUTPUT_KEY,
            (string) ($binding['source_output_key'] ?? ''),
            'source binding source_output_key',
            $errors,
        );
        app_sample11_html_template_output_assert_same(
            'catalog://html-module/SAMPLE11/HTML-PAGE',
            (string) ($binding['module_source_ref'] ?? ''),
            'source binding module_source_ref',
            $errors,
        );
    }

    $html = app_sample11_html_template_output_fetch_one(
        $pdo,
        'SELECT id, html_key, legacy_project_source_output_pid, legacy_html_template_pid
           FROM project_html_definitions
          WHERE project_id = :project_id
            AND html_key = :html_key',
        [
            ':project_id' => $projectId,
            ':html_key' => APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_HTML_KEY,
        ],
    );
    if ($html === null) {
        $errors[] = 'sample11 HTML definition row missing.';
    } else {
        app_sample11_html_template_output_assert_same(
            APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_LEGACY_SOURCE_OUTPUT_PID,
            (int) ($html['legacy_project_source_output_pid'] ?? 0),
            'HTML definition legacy_project_source_output_pid',
            $errors,
        );
        app_sample11_html_template_output_assert_same(
            APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_TEMPLATE_PID,
            (int) ($html['legacy_html_template_pid'] ?? 0),
            'HTML definition legacy_html_template_pid',
            $errors,
        );
    }

    $template = app_sample11_html_template_output_fetch_one(
        $pdo,
        'SELECT legacy_html_template_pid, target_type, file_name
           FROM html_templates
          WHERE legacy_html_template_pid = :template_pid',
        [':template_pid' => APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_TEMPLATE_PID],
    );
    if ($template === null) {
        $errors[] = 'sample11 HTML template row missing.';
    } else {
        app_sample11_html_template_output_assert_same('html', (string) ($template['target_type'] ?? ''), 'template target_type', $errors);
        app_sample11_html_template_output_assert_same('page.php', (string) ($template['file_name'] ?? ''), 'template file_name', $errors);
    }

    $parameters = app_sample11_html_template_output_fetch_all(
        $pdo,
        'SELECT parameter_name, parameter_value
           FROM project_html_parameters
          WHERE project_id = :project_id
          ORDER BY parameter_list_order, legacy_parameter_pid',
        [':project_id' => $projectId],
    );
    app_sample11_html_template_output_assert_same(2, count($parameters), 'project HTML parameter count', $errors);

    return [
        'project' => $project,
        'binding' => $binding,
        'html' => $html,
        'template' => $template,
        'parameters' => $parameters,
    ];
}

function app_sample11_html_template_output_run(
    array $app,
    string $requestedBy = 'sample11-check',
    ?string $referenceRoot = null,
): array {
    $errors = [];
    $steps = [];

    $metadata = app_sample11_html_template_output_verify_metadata($app, $errors);
    $steps['metadata'] = $metadata;

    $sourceOutputResult = app_fetch_project_source_output_item(
        $app,
        APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_PROJECT_KEY,
        APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_SOURCE_OUTPUT_KEY,
    );
    if (!$sourceOutputResult['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $sourceOutputResult['error'],
        ];
    }

    $sourceOutput = $sourceOutputResult['item'];
    if (!is_array($sourceOutput)) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => 'sample11 source output definition missing.',
        ];
    }

    app_sample11_html_template_output_assert_same('html', (string) ($sourceOutput['class_type'] ?? ''), 'source output class_type', $errors);
    app_sample11_html_template_output_assert_same('html-module-catalog', (string) ($sourceOutput['artifact_strategy'] ?? ''), 'source output artifact_strategy', $errors);
    app_sample11_html_template_output_assert_same('catalog://html-module/SAMPLE11/HTML-PAGE', (string) ($sourceOutput['source_template_dir'] ?? ''), 'source output source_template_dir', $errors);

    $createResult = app_project_output_create_from_definition(
        $app,
        APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_PROJECT_KEY,
        $sourceOutput,
        $requestedBy,
    );
    if (!$createResult['ok'] || !is_array($createResult['artifact'])) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $createResult['error'],
        ];
    }
    $steps['artifact'] = $createResult['artifact'];

    $publishResult = app_project_output_publish_artifact($app, $createResult['artifact'], $sourceOutput);
    if (!$publishResult['ok'] || !is_array($publishResult['published'])) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $publishResult['error'],
        ];
    }
    $steps['published'] = $publishResult['published'];

    $publishedRoot = (string) ($publishResult['published']['published_root'] ?? '');
    $publishedSnapshot = app_sample11_html_template_output_tree_snapshot($publishedRoot);
    $steps['published_snapshot'] = $publishedSnapshot;
    if (!$publishedSnapshot['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $publishedSnapshot['error'],
        ];
    }

    $publishedPaths = array_map(
        static fn (array $file): string => (string) ($file['relative_path'] ?? ''),
        $publishedSnapshot['files'],
    );
    sort($publishedPaths, SORT_STRING);
    app_sample11_html_template_output_assert_same(
        APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_REFERENCE_FILES,
        $publishedPaths,
        'published file list',
        $errors,
    );

    $referenceRoot = $referenceRoot ?? app_sample11_html_template_output_default_reference_root();
    $referenceSnapshot = app_sample11_html_template_output_tree_snapshot($referenceRoot);
    $steps['reference_snapshot'] = $referenceSnapshot;
    if (!$referenceSnapshot['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $referenceSnapshot['error'],
        ];
    }

    $steps['file_checks'] = app_sample11_html_template_output_compare_file_sets(
        $referenceSnapshot['files'],
        $publishedSnapshot['files'],
        APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_SOURCE_OUTPUT_KEY,
        $errors,
    );

    return [
        'ok' => $errors === [],
        'steps' => $steps,
        'assertion_errors' => $errors,
        'error' => $errors === [] ? '' : 'sample11 HTML template output verification failed.',
    ];
}
