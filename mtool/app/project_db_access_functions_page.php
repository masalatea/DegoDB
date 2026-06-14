<?php

declare(strict_types=1);

require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/project_db_access_route_common.php';

/**
 * @param array{
 *     name:string,
 *     line:int,
 *     signature:string
 * } $method
 * @param array{
 *     function_name:string,
 *     function_list_order:string,
 *     function_suffix:string,
 *     action_type:string,
 *     select_by_distinct:string,
 *     is_blob_target:string,
 *     detected_line:string,
 *     source_of_truth:string,
 *     updated_at:string
 * }|null $canonicalItem
 */
function app_project_db_access_functions_effective_order(array $method, ?array $canonicalItem): int
{
    if ($canonicalItem !== null && ctype_digit($canonicalItem['function_list_order']) && (int) $canonicalItem['function_list_order'] > 0) {
        return (int) $canonicalItem['function_list_order'];
    }

    if ($canonicalItem !== null && ctype_digit($canonicalItem['detected_line']) && (int) $canonicalItem['detected_line'] > 0) {
        return (int) $canonicalItem['detected_line'];
    }

    return (int) $method['line'];
}

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
function app_render_project_db_access_functions_page(array $app, array $request): void
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

    $methods = app_generated_file_method_catalog($entity['dbaccess_path']);
    $legacyDafuncSchema = app_project_db_access_legacy_metadata_schema($app, 'dafunc');
    $canonicalCatalogResult = app_fetch_db_access_function_metadata_catalog($app, $projectKey, $entity['source_name']);
    $canonicalCatalogError = $canonicalCatalogResult['ok'] ? '' : $canonicalCatalogResult['error'];
    $canonicalFunctionCount = $canonicalCatalogResult['ok'] ? count($canonicalCatalogResult['items']) : 0;
    $canonicalByFunction = [];

    if ($canonicalCatalogResult['ok']) {
        foreach ($canonicalCatalogResult['items'] as $item) {
            $canonicalByFunction[$item['function_name']] = $item;
        }
    }

    usort(
        $methods,
        static function (array $left, array $right) use ($canonicalByFunction): int {
            $leftCanonical = $canonicalByFunction[$left['name']] ?? null;
            $rightCanonical = $canonicalByFunction[$right['name']] ?? null;
            $leftOrder = app_project_db_access_functions_effective_order($left, $leftCanonical);
            $rightOrder = app_project_db_access_functions_effective_order($right, $rightCanonical);

            if ($leftOrder !== $rightOrder) {
                return $leftOrder <=> $rightOrder;
            }

            if ($left['line'] !== $right['line']) {
                return $left['line'] <=> $right['line'];
            }

            return strcmp($left['name'], $right['name']);
        },
    );

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Functions</title>
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
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / functions</p>

    <h1><?php echo app_h($project['name']); ?> Function Candidate Preview</h1>
    <p>本来は <code>dafunc</code> と周辺 designer metadata を整える画面です。現段階では generated method 一覧を基準にしつつ、保存済みの canonical function metadata があれば同じ行で確認できます。</p>

    <section class="summary-card">
        <h2>Source</h2>
        <ul>
            <li>dbaccess file: <?php echo $entity['has_dbaccess_file'] ? '<code>' . app_h($entity['dbaccess_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
            <li>function candidate count: <code><?php echo app_h((string) count($methods)); ?></code></li>
            <li>legacy `dafunc` field count: <code><?php echo app_h((string) count($legacyDafuncSchema['field_names'])); ?></code></li>
            <li>saved canonical rows: <code><?php echo app_h($canonicalCatalogError === '' ? (string) $canonicalFunctionCount : 'n/a'); ?></code></li>
        </ul>
    </section>

    <section class="note-card">
        <h2>見方</h2>
        <ul>
            <li>constructor や helper method も混ざる可能性がある</li>
            <li>saved row が無い場合の action type は method 名からの推定値</li>
            <li>query category と designer 粒度はまだ整理途中</li>
            <li>最終的には designer から `dafunc` canonical table を編集する</li>
            <li>function change-order は保存済み canonical row のみを対象にする</li>
        </ul>
        <?php if ($canonicalCatalogError !== ''): ?>
            <p class="muted"><?php echo app_h($canonicalCatalogError); ?></p>
        <?php endif; ?>
    </section>

    <?php if ($canonicalCatalogError === '' && $canonicalFunctionCount > 0): ?>
        <p><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/change-order">Function Change Order</a></p>
    <?php endif; ?>

    <?php if ($methods === []): ?>
        <p>function candidate はまだ見つかっていません。</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>function candidate</th>
                <th>line</th>
                <th>action type</th>
                <th>canonical state</th>
                <th>preview</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($methods as $index => $method): ?>
                <?php $functionProfile = app_project_db_access_guess_function_profile($method['name']); ?>
                <?php $canonicalItem = $canonicalByFunction[$method['name']] ?? null; ?>
                <?php $effectiveActionType = $canonicalItem !== null && $canonicalItem['action_type'] !== '' ? $canonicalItem['action_type'] : $functionProfile['legacy_action_type']; ?>
                <tr>
                    <td><?php echo app_h((string) ($index + 1)); ?></td>
                    <td><code><?php echo app_h($method['name']); ?></code><br><span class="muted"><?php echo app_h($method['signature']); ?></span></td>
                    <td><code><?php echo app_h((string) $method['line']); ?></code></td>
                    <td>
                        <code><?php echo app_h($effectiveActionType); ?></code>
                        <br><span class="muted"><?php echo app_h($canonicalItem !== null ? 'canonical' : 'guess'); ?> / <?php echo app_h($functionProfile['http_method']); ?></span>
                        <br><span class="muted">suffix: <?php echo app_h($canonicalItem !== null && $canonicalItem['function_suffix'] !== '' ? $canonicalItem['function_suffix'] : $functionProfile['function_suffix_candidate']); ?></span>
                    </td>
                    <td>
                        <?php if ($canonicalCatalogError !== ''): ?>
                            <span class="muted">db unavailable</span>
                        <?php elseif ($canonicalItem === null): ?>
                            <span class="muted">preview only</span>
                        <?php else: ?>
                            <code><?php echo app_h($canonicalItem['source_of_truth']); ?></code><br>
                            <span class="muted">updated: <?php echo app_h($canonicalItem['updated_at']); ?></span><br>
                            <span class="muted">order: <?php echo app_h($canonicalItem['function_list_order']); ?> / distinct: <?php echo app_h($canonicalItem['select_by_distinct']); ?> / blob: <?php echo app_h($canonicalItem['is_blob_target']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>">detail</a><br>
                        <?php if ($canonicalItem !== null): ?>
                            <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/move">move</a><br>
                        <?php endif; ?>
                        <?php if (in_array($effectiveActionType, ['SELECTSINGLE', 'SELECTLIST'], true)): ?>
                            <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-where">select where</a><br>
                            <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-where/input-aid">select where input-aid</a><br>
                            <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-target-fields">select target fields</a><br>
                            <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-having">select having</a><br>
                        <?php elseif ($effectiveActionType === 'INSERT'): ?>
                            <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/insert-target-fields">insert target fields</a><br>
                        <?php elseif ($effectiveActionType === 'UPDATE'): ?>
                            <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/update-target-fields">update target fields</a><br>
                            <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/update-delete-where">update/delete where</a><br>
                            <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/update-delete-where/input-aid">update/delete input-aid</a><br>
                        <?php elseif ($effectiveActionType === 'DELETE'): ?>
                            <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/update-delete-where">update/delete where</a><br>
                            <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/update-delete-where/input-aid">update/delete input-aid</a><br>
                        <?php endif; ?>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/source">source</a><br>
                        <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/endpoint">endpoint</a>
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
