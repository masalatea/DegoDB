<?php

declare(strict_types=1);

require_once __DIR__ . '/project_table_route_common.php';
require_once __DIR__ . '/table_metadata_repository.php';

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_project_table_detail_page(array $app, array $request): void
{
    $bootstrap = app_project_table_route_bootstrap($app, $request);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $catalog = $bootstrap['generated_catalog'];
    $tableKey = trim(app_route_param($request, 'table_key'));
    if ($tableKey === '') {
        app_render_bad_request_page($app, $request, 'table key が必要です。');
        return;
    }

    $canonicalItem = app_fetch_table_metadata_item($app, $projectKey, $tableKey);
    if (!$canonicalItem['ok']) {
        app_render_bad_request_page($app, $request, $canonicalItem['error']);
        return;
    }

    $entity = app_generated_catalog_find_entity($catalog, $tableKey);
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
    <title><?php echo app_h($app['site_name']); ?> - Project Table Detail</title>
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
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables">tables</a> / <code><?php echo app_h($tableKey); ?></code></p>

    <?php if ($canonicalItem['item'] !== null): ?>
        <?php $table = $canonicalItem['item']; ?>
        <h1><?php echo app_h($project['name']); ?> Table Detail</h1>
        <p>canonical <code>dbtable</code> / <code>dbtablecolumns</code> の detail です。</p>

        <div class="summary-grid">
            <section class="summary-card">
                <h2>Table</h2>
                <ul>
                    <li>name: <code><?php echo app_h($table['name']); ?></code></li>
                    <li>PID: <code><?php echo app_h($table['pid']); ?></code></li>
                    <li>column count: <code><?php echo app_h((string) count($table['columns'])); ?></code></li>
                </ul>
            </section>

            <section class="note-card">
                <h2>Next</h2>
                <ul>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($table['name']); ?>/columns">column detail</a></li>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($table['name']); ?>/columns/new">new column</a></li>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($table['name']); ?>/edit">table edit</a></li>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/data-classes/sync">data class sync</a></li>
                </ul>
            </section>
        </div>

        <?php if ($entity !== null): ?>
            <section class="summary-card">
                <h2>Runtime Reference</h2>
                <p class="muted">同名 runtime reference entry があるため、移行中の参照情報として残しています。</p>
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
        <h1><?php echo app_h($project['name']); ?> Table Runtime Reference Detail</h1>
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
                    <li>property count: <code><?php echo app_h((string) count($dataProperties)); ?></code></li>
                    <li>dbaccess class count: <code><?php echo app_h((string) count($dbaccessClasses)); ?></code></li>
                    <li>dbaccess method count: <code><?php echo app_h((string) count($dbaccessMethods)); ?></code></li>
                </ul>
            </section>

            <section class="note-card">
                <h2>Next</h2>
                <ul>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/import">import</a></li>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($entity['source_name']); ?>/columns">column preview</a></li>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($entity['source_name']); ?>/edit">create canonical row</a></li>
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
