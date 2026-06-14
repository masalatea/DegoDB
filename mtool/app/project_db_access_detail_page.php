<?php

declare(strict_types=1);

require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/project_db_access_route_common.php';

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
function app_render_project_db_access_detail_page(array $app, array $request): void
{
    $bootstrap = app_project_db_access_route_bootstrap($app, $request);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $catalog = $bootstrap['generated_catalog'];
    $dbAccessKey = trim(app_route_param($request, 'db_access_key'));
    if ($dbAccessKey === '') {
        app_render_bad_request_page($app, $request, 'db access key が必要です。');
        return;
    }

    $entity = app_generated_catalog_find_entity($catalog, $dbAccessKey);
    if ($entity === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $dataClasses = app_generated_file_class_names($entity['data_path']);
    $dbaccessClasses = app_generated_file_class_names($entity['dbaccess_path']);
    $dbaccessMethods = app_generated_file_method_names($entity['dbaccess_path']);
    $dbaccessExcerpt = app_generated_file_excerpt($entity['dbaccess_path'], 36);
    $canonicalResult = app_fetch_db_access_class_metadata($app, $projectKey, $entity['source_name']);
    $canonicalError = $canonicalResult['ok'] ? '' : $canonicalResult['error'];
    $canonicalItem = $canonicalResult['ok'] ? $canonicalResult['item'] : null;

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Detail</title>
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
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <code><?php echo app_h($entity['source_name']); ?></code></p>

    <h1><?php echo app_h($project['name']); ?> DB Access Detail</h1>
    <p>本来の DB Access class metadata 整理に向けて、候補 class の定義断片を確認する preview です。runtime reference catalog から読める情報を基準にしつつ、保存済みの canonical class metadata があればこの画面でも併記します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Entity</h2>
            <ul>
                <li>source name: <code><?php echo app_h($entity['source_name']); ?></code></li>
                <li>dbaccess file: <?php echo $entity['has_dbaccess_file'] ? '<code>' . app_h($entity['dbaccess_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
                <li>paired data file: <?php echo $entity['has_data_file'] ? '<code>' . app_h($entity['data_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Detected Symbols</h2>
            <ul>
                <li>dbaccess class count: <code><?php echo app_h((string) count($dbaccessClasses)); ?></code></li>
                <li>function candidate count: <code><?php echo app_h((string) count($dbaccessMethods)); ?></code></li>
                <li>paired data class count: <code><?php echo app_h((string) count($dataClasses)); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Canonical Class Metadata</h2>
            <?php if ($canonicalError !== ''): ?>
                <p class="muted">未接続</p>
                <p class="muted"><?php echo app_h($canonicalError); ?></p>
            <?php elseif ($canonicalItem === null): ?>
                <p class="muted">未保存</p>
                <p class="muted"><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/edit">class setting</a> から canonical row を作成できます。</p>
            <?php else: ?>
                <ul>
                    <li>source of truth: <code><?php echo app_h($canonicalItem['source_of_truth']); ?></code></li>
                    <li>StoreBasePath: <code><?php echo app_h($canonicalItem['store_base_path'] !== '' ? $canonicalItem['store_base_path'] : '(blank)'); ?></code></li>
                    <li>IsAutoload: <code><?php echo app_h($canonicalItem['is_autoload']); ?></code></li>
                    <li>updated: <code><?php echo app_h($canonicalItem['updated_at']); ?></code></li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>次の導線</h2>
            <ul>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/edit">class setting (`data-da.php` / `dbaccess-da.php`)</a></li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">function candidate preview</a></li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/source">source preview</a></li>
            </ul>
            <p class="muted">function metadata、proxy endpoint、query builder 設定はまだ generated file だけでは確定できませんが、class metadata は canonical table に保存できます。</p>
        </section>
    </div>

    <?php if ($dbaccessExcerpt !== ''): ?>
        <section>
            <h2>DBAccess File Preview</h2>
            <pre><?php echo app_h($dbaccessExcerpt); ?></pre>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
