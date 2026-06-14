<?php

declare(strict_types=1);

require_once __DIR__ . '/data_class_repository.php';
require_once __DIR__ . '/project_data_class_route_common.php';

/**
 * @param array{
 *     site:string,
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_project_data_class_detail_page(array $app, array $request): void
{
    $bootstrap = app_project_data_class_route_bootstrap($app, $request);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $catalog = $bootstrap['generated_catalog'];
    $dataClassKey = trim(app_route_param($request, 'data_class_key'));
    if ($dataClassKey === '') {
        app_render_bad_request_page($app, $request, 'data class key が必要です。');
        return;
    }

    $canonicalItem = app_fetch_data_class_metadata_item($app, $projectKey, $dataClassKey);
    if (!$canonicalItem['ok']) {
        app_render_bad_request_page($app, $request, $canonicalItem['error']);
        return;
    }

    $entity = app_generated_catalog_find_entity($catalog, $dataClassKey);
    if ($entity === null && $canonicalItem['item'] !== null) {
        $entity = app_generated_catalog_find_entity($catalog, $canonicalItem['item']['name']);
    }

    if ($canonicalItem['item'] === null && $entity === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Data Class Detail</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 82rem;
            background: #ffffff;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 2rem;
        }
        code, pre {
            background: #edf2f7;
            border-radius: 6px;
        }
        code {
            padding: 0.1rem 0.3rem;
        }
        pre {
            padding: 1rem;
            overflow-x: auto;
            white-space: pre-wrap;
        }
        .breadcrumbs {
            margin-bottom: 1rem;
        }
        .summary-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            margin: 1.5rem 0;
        }
        .summary-card, .note-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
        }
        .summary-card {
            background: #f8fafc;
        }
        .note-card {
            background: #fefce8;
            border-color: #facc15;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="<?php echo app_h(app_project_data_classes_path($projectKey)); ?>">data-classes</a> / <code><?php echo app_h($dataClassKey); ?></code></p>

    <?php if ($canonicalItem['item'] !== null): ?>
        <?php $dataClass = $canonicalItem['item']; ?>
        <h1><?php echo app_h($project['name']); ?> Data Class Detail</h1>
        <p>canonical <code>dataclass</code> / <code>dataclassfields</code> の detail です。</p>

        <div class="summary-grid">
            <section class="summary-card">
                <h2>Data Class</h2>
                <ul>
                    <li>name: <code><?php echo app_h($dataClass['name']); ?></code></li>
                    <li>PID: <code><?php echo app_h($dataClass['pid']); ?></code></li>
                    <li>field count: <code><?php echo app_h((string) count($dataClass['fields'])); ?></code></li>
                    <li>autoload: <code><?php echo app_h($dataClass['is_autoload']); ?></code></li>
                    <li>store base path: <?php echo $dataClass['store_base_path'] !== '' ? '<code>' . app_h($dataClass['store_base_path']) . '</code>' : '<span class="muted">none</span>'; ?></li>
                    <li>inherit parent: <?php echo $dataClass['inherit_parent_data_class_name'] !== '' ? '<code>' . app_h($dataClass['inherit_parent_data_class_name']) . '</code>' : '<span class="muted">none</span>'; ?></li>
                    <li>last modified: <code><?php echo app_h($dataClass['last_modified_dt']); ?></code></li>
                </ul>
            </section>

            <section class="note-card">
                <h2>Next</h2>
                <ul>
                    <li><a href="<?php echo app_h(app_project_data_class_fields_path($projectKey, $dataClass['name'])); ?>">field detail</a></li>
                    <li><a href="<?php echo app_h(app_project_data_class_field_new_path($projectKey, $dataClass['name'])); ?>">new field</a></li>
                    <li><a href="<?php echo app_h(app_project_data_class_edit_path($projectKey, $dataClass['name'])); ?>">data class edit</a></li>
                    <li><a href="<?php echo app_h(app_project_data_classes_sync_path($projectKey)); ?>">sync</a></li>
                    <?php if ($entity !== null): ?>
                        <li><a href="<?php echo app_h(app_project_data_class_source_path($projectKey, $dataClass['name'])); ?>">runtime source preview</a></li>
                    <?php endif; ?>
                </ul>
            </section>
        </div>

        <?php if ($entity !== null): ?>
            <section class="summary-card">
                <h2>Runtime Reference</h2>
                <ul>
                    <li>data file: <?php echo $entity['has_data_file'] ? '<code>' . app_h($entity['data_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
                    <li>dbaccess file: <?php echo $entity['has_dbaccess_file'] ? '<code>' . app_h($entity['dbaccess_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
                </ul>
            </section>
        <?php endif; ?>
    <?php else: ?>
        <?php
        $dataClasses = app_generated_file_class_names($entity['data_path']);
        $dbaccessClasses = app_generated_file_class_names($entity['dbaccess_path']);
        $dataProperties = app_generated_file_property_names($entity['data_path']);
        $dbaccessMethods = app_generated_file_method_names($entity['dbaccess_path']);
        $dataExcerpt = app_generated_file_excerpt($entity['data_path'], 28);
        $dbaccessExcerpt = app_generated_file_excerpt($entity['dbaccess_path'], 28);
        ?>
        <h1><?php echo app_h($project['name']); ?> Data Class Runtime Reference Detail</h1>
        <p>canonical row がまだ無いため、runtime reference entry を fallback preview として表示しています。</p>

        <div class="summary-grid">
            <section class="summary-card">
                <h2>Entity</h2>
                <ul>
                    <li>source name: <code><?php echo app_h($entity['source_name']); ?></code></li>
                    <li>data file: <?php echo $entity['has_data_file'] ? '<code>' . app_h($entity['data_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
                    <li>dbaccess file: <?php echo $entity['has_dbaccess_file'] ? '<code>' . app_h($entity['dbaccess_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
                </ul>
            </section>

            <section class="summary-card">
                <h2>Detected Symbols</h2>
                <ul>
                    <li>data class count: <code><?php echo app_h((string) count($dataClasses)); ?></code></li>
                    <li>field candidate count: <code><?php echo app_h((string) count($dataProperties)); ?></code></li>
                    <li>dbaccess class count: <code><?php echo app_h((string) count($dbaccessClasses)); ?></code></li>
                    <li>dbaccess method count: <code><?php echo app_h((string) count($dbaccessMethods)); ?></code></li>
                </ul>
            </section>

            <section class="note-card">
                <h2>Next</h2>
                <ul>
                    <li><a href="<?php echo app_h(app_project_data_classes_sync_path($projectKey)); ?>">sync</a></li>
                    <li><a href="<?php echo app_h(app_project_data_class_fields_path($projectKey, $entity['source_name'])); ?>">field preview</a></li>
                    <li><a href="<?php echo app_h(app_project_data_class_edit_path($projectKey, $entity['source_name'])); ?>">create canonical row</a></li>
                </ul>
            </section>
        </div>

        <?php if ($dataExcerpt !== ''): ?>
            <section>
                <h2>Data File Preview</h2>
                <pre><?php echo app_h($dataExcerpt); ?></pre>
            </section>
        <?php endif; ?>

        <?php if ($dbaccessExcerpt !== ''): ?>
            <section>
                <h2>DBAccess File Preview</h2>
                <pre><?php echo app_h($dbaccessExcerpt); ?></pre>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
