<?php

declare(strict_types=1);

require_once __DIR__ . '/generated_runtime.php';
require_once __DIR__ . '/project_table_route_common.php';
require_once __DIR__ . '/table_metadata_repository.php';

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     },
 *     generated:array{
 *         dbclasses_root:string,
 *         dbclasses_loader:string,
 *         dbclasses_mode:string
 *     }
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_project_tables_page(array $app, array $request): void
{
    $bootstrap = app_project_table_route_bootstrap($app, $request);
    if ($bootstrap === null) {
        return;
    }

    $item = $bootstrap['project'];
    $projectKey = $bootstrap['project_key'];
    $generatedRuntime = $bootstrap['generated_runtime'];
    $generatedCatalog = $bootstrap['generated_catalog'];
    $canonicalSnapshot = app_fetch_table_metadata_snapshot($app, $projectKey);
    $canonicalError = $canonicalSnapshot['ok'] ? '' : $canonicalSnapshot['error'];
    $canonicalTableCount = 0;
    $canonicalColumnCount = 0;
    $canonicalNames = [];
    $bootstrapOnlyEntities = [];
    $deleted = app_query_param('deleted') === '1';
    $deletedTableName = trim(app_query_param('table'));

    if ($canonicalSnapshot['ok']) {
        $canonicalTableCount = count($canonicalSnapshot['items']);
        foreach ($canonicalSnapshot['items'] as $table) {
            $canonicalColumnCount += count($table['columns']);
            $canonicalNames[strtolower((string) $table['name'])] = true;
        }
    }

    foreach ($generatedCatalog['entities'] as $entity) {
        $normalizedName = strtolower((string) ($entity['source_name'] ?? ''));
        if ($normalizedName === '' || array_key_exists($normalizedName, $canonicalNames)) {
            continue;
        }

        $bootstrapOnlyEntities[] = $entity;
    }

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Tables</title>
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
        code {
            background: #edf2f7;
            padding: 0.1rem 0.3rem;
            border-radius: 4px;
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
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / tables</p>

    <h1><?php echo app_h($item['name']); ?> DB Table metadata</h1>
    <p>DB 設計情報を import して canonical な <code>dbtable</code> / <code>dbtablecolumns</code> を管理する画面です。canonical row と bootstrap preview を並べて表示し、未移行 table はここから create canonical row に進めます。</p>

    <?php if ($deleted): ?>
        <div class="success">table metadata<?php echo $deletedTableName !== '' ? ' <code>' . app_h($deletedTableName) . '</code>' : ''; ?> を削除しました。</div>
    <?php endif; ?>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Project</h2>
            <ul>
                <li>project key: <code><?php echo app_h($item['project_key']); ?></code></li>
                <li>slug: <code><?php echo app_h($item['slug']); ?></code></li>
                <li>status: <code><?php echo app_h($item['lifecycle_status']); ?></code></li>
                <li>db name: <code><?php echo app_h($app['db']['name']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Catalog Status</h2>
            <ul>
                <li>canonical tables: <code><?php echo app_h((string) $canonicalTableCount); ?></code></li>
                <li>canonical columns: <code><?php echo app_h((string) $canonicalColumnCount); ?></code></li>
                <li>runtime reference candidates: <code><?php echo app_h((string) $generatedCatalog['total_entities']); ?></code></li>
                <li>reference-only: <code><?php echo app_h((string) count($bootstrapOnlyEntities)); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Runtime Reference</h2>
            <ul>
                <li>mode: <code><?php echo app_h($generatedRuntime['dbclasses_mode']); ?></code></li>
                <li>loader exists: <code><?php echo app_h($generatedRuntime['dbclasses_loader_exists'] ? 'yes' : 'no'); ?></code></li>
                <li>data files: <code><?php echo app_h((string) $generatedRuntime['data_file_count']); ?></code></li>
                <li>dbaccess files: <code><?php echo app_h((string) $generatedRuntime['dbaccess_file_count']); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>Start Here</h2>
            <p class="muted">外部 DB schema に差分がある場合は、まず import をやり直します。</p>
            <ul>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/import"><code>/projects/<?php echo app_h($projectKey); ?>/tables/import</code></a></li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/data-classes/sync"><code>/projects/<?php echo app_h($projectKey); ?>/data-classes/sync</code></a></li>
                <?php if ($canonicalError !== ''): ?>
                    <li class="muted"><?php echo app_h($canonicalError); ?></li>
                <?php endif; ?>
            </ul>
        </section>
    </div>

    <?php if ($canonicalSnapshot['items'] !== []): ?>
        <h2>Canonical Tables</h2>
        <table>
            <thead>
            <tr>
                <th>table</th>
                <th>column count</th>
                <th>bootstrap ref</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($canonicalSnapshot['items'] as $table): ?>
                <?php $bootstrapEntity = app_generated_catalog_find_entity($generatedCatalog, $table['name']); ?>
                <tr>
                    <td><code><?php echo app_h($table['name']); ?></code></td>
                    <td><code><?php echo app_h((string) count($table['columns'])); ?></code></td>
                    <td>
                        <?php if ($bootstrapEntity !== null): ?>
                            <span class="muted"><?php echo app_h($bootstrapEntity['has_data_file'] && $bootstrapEntity['has_dbaccess_file'] ? 'paired bootstrap' : 'partial bootstrap'); ?></span>
                        <?php else: ?>
                            <span class="muted">none</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($table['name']); ?>">detail</a><br>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($table['name']); ?>/columns">columns</a><br>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($table['name']); ?>/edit">edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($bootstrapOnlyEntities !== []): ?>
        <h2>Bootstrap-Only Candidates</h2>
        <table>
            <thead>
            <tr>
                <th>bootstrap candidate</th>
                <th>state</th>
                <th>data file</th>
                <th>dbaccess file</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($bootstrapOnlyEntities as $entity): ?>
                <tr>
                    <td><code><?php echo app_h($entity['source_name']); ?></code></td>
                    <td><span class="muted"><?php echo app_h($entity['has_data_file'] && $entity['has_dbaccess_file'] ? 'paired bootstrap' : 'partial bootstrap'); ?></span></td>
                    <td><?php echo $entity['has_data_file'] ? '<code>' . app_h($entity['data_file']) . '</code>' : '<span class="muted">none</span>'; ?></td>
                    <td><?php echo $entity['has_dbaccess_file'] ? '<code>' . app_h($entity['dbaccess_file']) . '</code>' : '<span class="muted">none</span>'; ?></td>
                    <td>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($entity['source_name']); ?>">detail</a><br>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($entity['source_name']); ?>/columns">columns</a><br>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/<?php echo rawurlencode($entity['source_name']); ?>/edit">create canonical row</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($canonicalSnapshot['items'] === [] && $bootstrapOnlyEntities === []): ?>
        <p>canonical metadata も bootstrap candidate もまだ見つかっていません。</p>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
