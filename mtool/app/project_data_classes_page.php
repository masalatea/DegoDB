<?php

declare(strict_types=1);

require_once __DIR__ . '/data_class_repository.php';
require_once __DIR__ . '/project_data_class_route_common.php';

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
function app_render_project_data_classes_page(array $app, array $request): void
{
    $bootstrap = app_project_data_class_route_bootstrap($app, $request);
    if ($bootstrap === null) {
        return;
    }

    $project = $bootstrap['project'];
    $projectKey = $bootstrap['project_key'];
    $generatedRuntime = $bootstrap['generated_runtime'];
    $generatedCatalog = $bootstrap['generated_catalog'];
    $canonicalSnapshot = app_fetch_data_class_metadata_snapshot($app, $projectKey);
    $canonicalError = $canonicalSnapshot['ok'] ? '' : $canonicalSnapshot['error'];
    $canonicalDataClassCount = 0;
    $canonicalFieldCount = 0;
    $canonicalNameSet = [];

    if ($canonicalSnapshot['ok']) {
        $canonicalDataClassCount = count($canonicalSnapshot['items']);
        foreach ($canonicalSnapshot['items'] as $dataClass) {
            $canonicalNameSet[strtolower($dataClass['name'])] = true;
            $canonicalFieldCount += count($dataClass['fields']);
        }
    }

    $bootstrapOnlyEntities = [];
    foreach ($generatedCatalog['entities'] as $entity) {
        if (isset($canonicalNameSet[strtolower($entity['source_name'])])) {
            continue;
        }

        $bootstrapOnlyEntities[] = $entity;
    }

    $deleted = app_query_param('deleted') === '1';
    $deletedDataClass = trim(app_query_param('data_class'));

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Data Classes</title>
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
        .success {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / data-classes</p>

    <h1><?php echo app_h($project['name']); ?> Data Class</h1>
    <p><code>dataclass</code> / <code>dataclassfields</code> を current route で管理する画面です。canonical row と bootstrap preview を並べて表示し、未移行 data class はここから create canonical row に進めます。</p>

    <?php if ($deleted): ?>
        <div class="success">
            data class metadata row を削除しました<?php echo $deletedDataClass !== '' ? ': <code>' . app_h($deletedDataClass) . '</code>' : ''; ?>。
        </div>
    <?php endif; ?>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Project</h2>
            <ul>
                <li>project key: <code><?php echo app_h($project['project_key']); ?></code></li>
                <li>slug: <code><?php echo app_h($project['slug']); ?></code></li>
                <li>status: <code><?php echo app_h($project['lifecycle_status']); ?></code></li>
                <li>db name: <code><?php echo app_h($app['db']['name']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Catalog Status</h2>
            <ul>
                <li>canonical classes: <code><?php echo app_h((string) $canonicalDataClassCount); ?></code></li>
                <li>canonical fields: <code><?php echo app_h((string) $canonicalFieldCount); ?></code></li>
                <li>runtime reference candidates: <code><?php echo app_h((string) $generatedCatalog['total_entities']); ?></code></li>
                <li>reference-only: <code><?php echo app_h((string) count($bootstrapOnlyEntities)); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Runtime Reference</h2>
            <ul>
                <li>mode: <code><?php echo app_h($generatedRuntime['dbclasses_mode']); ?></code></li>
                <li>data files: <code><?php echo app_h((string) $generatedRuntime['data_file_count']); ?></code></li>
                <li>dbaccess files: <code><?php echo app_h((string) $generatedRuntime['dbaccess_file_count']); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>Start Here</h2>
            <p class="muted">table metadata が変わった場合は、まず sync をやり直します。</p>
            <ul>
                <li><a href="<?php echo app_h(app_project_data_classes_sync_path($projectKey)); ?>"><code><?php echo app_h(app_project_data_classes_sync_path($projectKey)); ?></code></a></li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/import"><code>/projects/<?php echo app_h($projectKey); ?>/tables/import</code></a></li>
                <?php if ($canonicalError !== ''): ?>
                    <li class="muted"><?php echo app_h($canonicalError); ?></li>
                <?php endif; ?>
            </ul>
        </section>
    </div>

    <?php if ($canonicalSnapshot['items'] !== []): ?>
        <h2>Canonical Data Classes</h2>
        <table>
            <thead>
            <tr>
                <th>data class</th>
                <th>field count</th>
                <th>autoload</th>
                <th>bootstrap ref</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($canonicalSnapshot['items'] as $dataClass): ?>
                <?php $bootstrapEntity = app_generated_catalog_find_entity($generatedCatalog, $dataClass['name']); ?>
                <tr>
                    <td><code><?php echo app_h($dataClass['name']); ?></code></td>
                    <td><code><?php echo app_h((string) count($dataClass['fields'])); ?></code></td>
                    <td><code><?php echo app_h($dataClass['is_autoload']); ?></code></td>
                    <td>
                        <?php if ($bootstrapEntity !== null): ?>
                            <span class="muted"><?php echo app_h($bootstrapEntity['has_data_file'] && $bootstrapEntity['has_dbaccess_file'] ? 'paired bootstrap' : 'partial bootstrap'); ?></span>
                        <?php else: ?>
                            <span class="muted">none</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo app_h(app_project_data_class_detail_path($projectKey, $dataClass['name'])); ?>">detail</a><br>
                        <a href="<?php echo app_h(app_project_data_class_fields_path($projectKey, $dataClass['name'])); ?>">fields</a><br>
                        <a href="<?php echo app_h(app_project_data_class_edit_path($projectKey, $dataClass['name'])); ?>">edit</a>
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
                        <a href="<?php echo app_h(app_project_data_class_detail_path($projectKey, $entity['source_name'])); ?>">detail</a><br>
                        <a href="<?php echo app_h(app_project_data_class_fields_path($projectKey, $entity['source_name'])); ?>">fields</a><br>
                        <a href="<?php echo app_h(app_project_data_class_edit_path($projectKey, $entity['source_name'])); ?>">create canonical row</a>
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
