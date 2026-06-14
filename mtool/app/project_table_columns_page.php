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
function app_render_project_table_columns_page(array $app, array $request): void
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

    $deleted = app_query_param('deleted') === '1';
    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Table Columns</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 78rem;
            background: #ffffff;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 2rem;
        }
        code {
            background: #edf2f7;
            padding: 0.1rem 0.3rem;
            border-radius: 4px;
        }
        .breadcrumbs {
            margin-bottom: 1rem;
        }
        .summary-card, .note-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .summary-card {
            background: #f8fafc;
        }
        .note-card {
            background: #fefce8;
            border-color: #facc15;
        }
        .success {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: #dcfce7;
            color: #166534;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        th, td {
            border-bottom: 1px solid #d7dde5;
            padding: 0.75rem;
            vertical-align: top;
            text-align: left;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables">tables</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($tableKey); ?>"><code><?php echo app_h($tableKey); ?></code></a> / columns</p>

    <?php if ($canonicalItem['item'] !== null): ?>
        <?php $table = $canonicalItem['item']; ?>
        <h1><?php echo app_h($project['name']); ?> Column Detail</h1>
        <p>canonical <code>dbtablecolumns</code> の一覧です。</p>

        <?php if ($deleted): ?>
            <div class="success">column metadata row を削除しました。</div>
        <?php endif; ?>

        <section class="summary-card">
            <h2>Table</h2>
            <ul>
                <li>name: <code><?php echo app_h($table['name']); ?></code></li>
                <li>column count: <code><?php echo app_h((string) count($table['columns'])); ?></code></li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($table['name']); ?>/columns/new">new column</a></li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($table['name']); ?>/edit">table edit</a></li>
            </ul>
        </section>

        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>column</th>
                <th>datatype</th>
                <th>null</th>
                <th>key</th>
                <th>default</th>
                <th>extra</th>
                <th>memo</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($table['columns'] as $column): ?>
                <tr>
                    <td><?php echo app_h((string) $column['column_list_order']); ?></td>
                    <td><code><?php echo app_h($column['name']); ?></code></td>
                    <td><code><?php echo app_h($column['datatype']); ?></code></td>
                    <td><code><?php echo app_h($column['is_null']); ?></code></td>
                    <td><code><?php echo app_h($column['is_key']); ?></code></td>
                    <td><?php echo $column['is_default'] !== '' ? '<code>' . app_h($column['is_default']) . '</code>' : '<span class="muted">none</span>'; ?></td>
                    <td><?php echo $column['extra'] !== '' ? '<code>' . app_h($column['extra']) . '</code>' : '<span class="muted">none</span>'; ?></td>
                    <td><?php echo $column['memo'] !== '' ? app_h($column['memo']) : '<span class="muted">none</span>'; ?></td>
                    <td><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($table['name']); ?>/columns/<?php echo rawurlencode($column['name']); ?>/edit">edit</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <?php $dataProperties = app_generated_file_property_names($entity['data_path']); ?>
        <h1><?php echo app_h($project['name']); ?> Column Candidate Preview</h1>
        <p>canonical row がまだ無いため、<code>data-*.php</code> の public property を fallback preview として表示しています。</p>

        <section class="note-card">
            <h2>Next</h2>
            <ul>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/import">import</a></li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($entity['source_name']); ?>">table detail</a></li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($entity['source_name']); ?>/edit">create canonical row</a></li>
            </ul>
        </section>

        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>column candidate</th>
                <th>current status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($dataProperties as $index => $propertyName): ?>
                <tr>
                    <td><?php echo app_h((string) ($index + 1)); ?></td>
                    <td><code><?php echo app_h($propertyName); ?></code></td>
                    <td><span class="muted">datatype 未解決 / canonical metadata 未作成</span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
