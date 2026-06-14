<?php

declare(strict_types=1);

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
function app_render_project_db_access_function_source_page(array $app, array $request): void
{
    $bootstrap = app_project_db_access_route_bootstrap($app, $request);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $catalog = $bootstrap['generated_catalog'];
    $dbAccessKey = trim(app_route_param($request, 'db_access_key'));
    $functionKey = trim(app_route_param($request, 'function_key'));
    if ($dbAccessKey === '' || $functionKey === '') {
        app_render_bad_request_page($app, $request, 'db access key と function key が必要です。');
        return;
    }

    $entity = app_generated_catalog_find_entity($catalog, $dbAccessKey);
    if ($entity === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $methodCatalog = app_generated_file_method_catalog($entity['dbaccess_path']);
    $method = app_generated_file_find_method($methodCatalog, $functionKey);
    if ($method === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $methodExcerpt = app_generated_file_method_excerpt($entity['dbaccess_path'], $method['name'], 120);

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Function Source</title>
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
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">functions</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>"><code><?php echo app_h($method['name']); ?></code></a> / source</p>

    <h1><?php echo app_h($project['name']); ?> Function Source Preview</h1>
    <p>現段階の function source を確認する preview 画面です。build cache ではなく、<code><?php echo app_h($entity['dbaccess_file']); ?></code> の該当 method 断片を source preview として表示します。</p>

    <?php if ($methodExcerpt === ''): ?>
        <p>source preview はまだ取得できません。</p>
    <?php else: ?>
        <pre><?php echo app_h($methodExcerpt); ?></pre>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
