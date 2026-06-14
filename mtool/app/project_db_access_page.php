<?php

declare(strict_types=1);

require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/project_db_access_route_common.php';

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
function app_render_project_db_access_page(array $app, array $request): void
{
    $bootstrap = app_project_db_access_route_bootstrap($app, $request);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $generatedRuntime = $bootstrap['generated_runtime'];
    $catalog = $bootstrap['generated_catalog'];
    $canonicalCatalogResult = app_fetch_db_access_class_metadata_catalog($app, $projectKey);
    $canonicalCatalogError = $canonicalCatalogResult['ok'] ? '' : $canonicalCatalogResult['error'];
    $canonicalClassCount = $canonicalCatalogResult['ok'] ? count($canonicalCatalogResult['items']) : 0;
    $canonicalBySource = [];

    if ($canonicalCatalogResult['ok']) {
        foreach ($canonicalCatalogResult['items'] as $item) {
            $canonicalBySource[$item['source_name']] = $item;
        }
    }

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access</title>
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
        .start-card {
            background: #eff6ff;
            border-color: #93c5fd;
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
        .pill {
            display: inline-block;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 700;
            background: #dcfce7;
            color: #166534;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / db-access</p>

    <h1><?php echo app_h($project['name']); ?> DB Access</h1>
    <p>本来は <code>da</code> / <code>dafunc</code> と周辺 metadata を管理し、DB Access を生成する画面です。現段階では runtime reference catalog を一覧しつつ、保存済みの canonical class metadata があれば併記します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Project 概要</h2>
            <ul>
                <li>project key: <code><?php echo app_h($project['project_key']); ?></code></li>
                <li>slug: <code><?php echo app_h($project['slug']); ?></code></li>
                <li>status: <code><?php echo app_h($project['lifecycle_status']); ?></code></li>
                <li>db name: <code><?php echo app_h($app['db']['name']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Runtime Reference</h2>
            <ul>
                <li>mode: <code><?php echo app_h($generatedRuntime['dbclasses_mode']); ?></code></li>
                <li>dbaccess files: <code><?php echo app_h((string) $generatedRuntime['dbaccess_file_count']); ?></code></li>
                <li>paired entities: <code><?php echo app_h((string) $catalog['paired_count']); ?></code></li>
                <li>loader exists: <code><?php echo app_h($generatedRuntime['dbclasses_loader_exists'] ? 'yes' : 'no'); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Canonical Metadata</h2>
            <?php if ($canonicalCatalogError !== ''): ?>
                <p class="muted">未接続</p>
                <p class="muted"><?php echo app_h($canonicalCatalogError); ?></p>
            <?php else: ?>
                <ul>
                    <li>saved class rows: <code><?php echo app_h((string) $canonicalClassCount); ?></code></li>
                    <li>state: <code><?php echo app_h($canonicalClassCount > 0 ? 'active' : 'empty'); ?></code></li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="summary-card start-card">
            <h2>先に upstream を確認します</h2>
            <p class="muted">DB Access は DB import と Data Class sync の上に載る設計です。外部 DB schema に差分がある場合は、DB Access 編集より前に upstream を更新します。</p>
            <ul>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/import"><code>/projects/<?php echo app_h($projectKey); ?>/tables/import</code></a> で DB import 状態を確認する</li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/data-classes/sync"><code>/projects/<?php echo app_h($projectKey); ?>/data-classes/sync</code></a> で Data Class sync 状態を確認する</li>
                <li>差分がある場合は import / sync を先にやり直してから DB Access を触る</li>
            </ul>
        </section>

        <section class="note-card">
            <h2>現在の制約</h2>
            <p class="muted">一覧の基準は引き続き runtime reference catalog です。保存済みの canonical class metadata は補助情報として重ねています。</p>
            <ul>
                <li>sync route: <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/sync"><code>/projects/<?php echo app_h($projectKey); ?>/db-access/sync</code></a> から class / function row を bootstrap 由来で埋められる</li>
                <li>detail / functions / source は preview 主体であり、designer や query builder は未実装</li>
                <li>class edit / function detail で保存した manual row は sync 時も保持される</li>
            </ul>
        </section>
    </div>

    <?php if ($catalog['entities'] === []): ?>
        <p>db access candidate はまだ見つかっていません。</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>db access candidate</th>
                <th>dbaccess state</th>
                <th>dbaccess file</th>
                <th>function candidate count</th>
                <th>canonical state</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($catalog['entities'] as $entity): ?>
                <?php $functionCount = count(app_generated_file_method_names($entity['dbaccess_path'])); ?>
                <?php $canonicalItem = $canonicalBySource[$entity['source_name']] ?? null; ?>
                <tr>
                    <td><code><?php echo app_h($entity['source_name']); ?></code></td>
                    <td><span class="pill"><?php echo app_h($entity['has_dbaccess_file'] ? 'dbaccess source available' : 'dbaccess source missing'); ?></span></td>
                    <td><?php echo $entity['has_dbaccess_file'] ? '<code>' . app_h($entity['dbaccess_file']) . '</code>' : '<span class="muted">none</span>'; ?></td>
                    <td><code><?php echo app_h((string) $functionCount); ?></code></td>
                    <td>
                        <?php if ($canonicalCatalogError !== ''): ?>
                            <span class="muted">db unavailable</span>
                        <?php elseif ($canonicalItem === null): ?>
                            <span class="muted">preview only</span>
                        <?php else: ?>
                            <code><?php echo app_h($canonicalItem['source_of_truth']); ?></code><br>
                            <span class="muted">updated: <?php echo app_h($canonicalItem['updated_at']); ?></span><br>
                            <span class="muted">StoreBasePath: <?php echo app_h($canonicalItem['store_base_path'] !== '' ? $canonicalItem['store_base_path'] : '(blank)'); ?></span><br>
                            <span class="muted">IsAutoload: <?php echo app_h($canonicalItem['is_autoload']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>">detail</a><br>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/edit">edit</a><br>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">functions</a><br>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/source">source</a>
                    </td>
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
