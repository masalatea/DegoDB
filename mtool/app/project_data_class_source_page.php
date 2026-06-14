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
function app_render_project_data_class_source_page(array $app, array $request): void
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

    $entity = app_generated_catalog_find_entity($catalog, $dataClassKey);
    $canonicalItem = app_fetch_data_class_metadata_item($app, $projectKey, $dataClassKey);
    if (!$canonicalItem['ok']) {
        app_render_bad_request_page($app, $request, $canonicalItem['error']);
        return;
    }

    if ($entity === null && $canonicalItem['item'] === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $dataExcerpt = $entity !== null ? app_generated_file_excerpt($entity['data_path'], 120) : '';

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Data Class Source</title>
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
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/data-classes">data-classes</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/data-classes/<?php echo rawurlencode($dataClassKey); ?>"><code><?php echo app_h($dataClassKey); ?></code></a> / source</p>

    <h1><?php echo app_h($project['name']); ?> Data Class Source Preview</h1>
    <?php if ($entity !== null): ?>
        <p>現段階の Data Class source を確認する preview 画面です。build cache ではなく、runtime reference 内の <code><?php echo app_h($entity['data_file']); ?></code> の先頭を source preview として表示します。</p>
    <?php else: ?>
        <p>この data class は canonical metadata には存在しますが、runtime reference source file はまだありません。source preview は出せないため、canonical metadata を source of truth として扱います。</p>
        <?php if ($canonicalItem['item'] !== null): ?>
            <ul>
                <li>data class: <code><?php echo app_h($canonicalItem['item']['name']); ?></code></li>
                <li>field count: <code><?php echo app_h((string) count($canonicalItem['item']['fields'])); ?></code></li>
                <li>autoload: <code><?php echo app_h($canonicalItem['item']['is_autoload']); ?></code></li>
                <li>last modified: <code><?php echo app_h($canonicalItem['item']['last_modified_dt']); ?></code></li>
            </ul>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($entity === null): ?>
        <p>runtime reference が必要なら、まず対応 source があるか確認してください。</p>
    <?php elseif ($dataExcerpt === ''): ?>
        <p>source preview はまだ取得できません。</p>
    <?php else: ?>
        <pre><?php echo app_h($dataExcerpt); ?></pre>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
