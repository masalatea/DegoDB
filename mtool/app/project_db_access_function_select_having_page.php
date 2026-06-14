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
function app_render_project_db_access_function_select_having_page(array $app, array $request): void
{
    $bootstrap = app_project_db_access_function_route_bootstrap($app, $request);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $entity = $bootstrap['entity'];
    $method = $bootstrap['method'];
    $functionProfile = $bootstrap['function_profile'];
    $methodCount = count($bootstrap['method_catalog']);

    $canonicalFunctionResult = app_fetch_db_access_function_metadata(
        $app,
        $projectKey,
        $entity['source_name'],
        $method['name'],
    );
    $canonicalFunctionError = $canonicalFunctionResult['ok'] ? '' : $canonicalFunctionResult['error'];
    $canonicalFunctionItem = $canonicalFunctionResult['ok'] ? $canonicalFunctionResult['item'] : null;

    $catalogResult = app_fetch_db_access_function_select_having_catalog(
        $app,
        $projectKey,
        $entity['source_name'],
        $method['name'],
    );
    $catalogError = $catalogResult['ok'] ? '' : $catalogResult['error'];
    $items = $catalogResult['ok'] ? $catalogResult['items'] : [];

    $targetFieldCatalogResult = app_fetch_db_access_function_select_target_field_catalog(
        $app,
        $projectKey,
        $entity['source_name'],
        $method['name'],
    );
    $targetFieldCatalogError = $targetFieldCatalogResult['ok'] ? '' : $targetFieldCatalogResult['error'];
    $targetFieldCatalog = $targetFieldCatalogResult['ok'] ? $targetFieldCatalogResult['items'] : [];
    $targetFieldById = [];
    foreach ($targetFieldCatalog as $targetField) {
        $targetFieldById[$targetField['select_target_field_id']] = $targetField;
    }

    $legacySchema = app_project_db_access_legacy_metadata_schema($app, 'dafuncselecthaving');
    $effectiveActionType = $canonicalFunctionItem !== null && $canonicalFunctionItem['action_type'] !== ''
        ? $canonicalFunctionItem['action_type']
        : $functionProfile['legacy_action_type'];
    $isSelectFunction = in_array($effectiveActionType, ['SELECTSINGLE', 'SELECTLIST'], true);
    $created = app_query_param('created') === '1';
    $deleted = app_query_param('deleted') === '1';

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Function Select Having</title>
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
        .success {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: #dcfce7;
            color: #166534;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">functions</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>"><code><?php echo app_h($method['name']); ?></code></a> / select-having</p>

    <h1><?php echo app_h($project['name']); ?> Select Having Designer</h1>
    <p>select 系 function の having 条件を canonical row として管理する画面です。<code>select target fields</code> の canonical row を参照しつつ、having 条件を保存します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Function</h2>
            <ul>
                <li>db access: <code><?php echo app_h($entity['source_name']); ?></code></li>
                <li>function: <code><?php echo app_h($method['name']); ?></code></li>
                <li>action type: <code><?php echo app_h($effectiveActionType); ?></code></li>
                <li>signature: <code><?php echo app_h($method['signature']); ?></code></li>
                <li>function catalog size: <code><?php echo app_h((string) $methodCount); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Canonical Function</h2>
            <?php if ($canonicalFunctionError !== ''): ?>
                <p class="muted">未接続</p>
                <p class="muted"><?php echo app_h($canonicalFunctionError); ?></p>
            <?php elseif ($canonicalFunctionItem === null): ?>
                <p class="muted">未保存</p>
                <p class="muted"><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>">function detail</a> で canonical metadata を先に保存してください。</p>
            <?php else: ?>
                <ul>
                    <li>source of truth: <code><?php echo app_h($canonicalFunctionItem['source_of_truth']); ?></code></li>
                    <li>suffix: <code><?php echo app_h($canonicalFunctionItem['function_suffix']); ?></code></li>
                    <li>updated: <code><?php echo app_h($canonicalFunctionItem['updated_at']); ?></code></li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="summary-card">
            <h2>Designer Status</h2>
            <?php if ($catalogError !== ''): ?>
                <p class="muted">未接続</p>
                <p class="muted"><?php echo app_h($catalogError); ?></p>
            <?php else: ?>
                <ul>
                    <li>saved rows: <code><?php echo app_h((string) count($items)); ?></code></li>
                    <li>available target fields: <code><?php echo app_h((string) count($targetFieldCatalog)); ?></code></li>
                    <li>state: <code><?php echo app_h($items === [] ? 'empty' : 'active'); ?></code></li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>現在の制約</h2>
            <ul>
                <li>参照できる field は `select-target-fields` に保存済みの row のみ</li>
                <li>`having_order` は当面ここで直接入力する</li>
                <li>aggregate 関数や group-by との整合性チェックはまだ未実装</li>
            </ul>
            <?php if (!$isSelectFunction): ?>
                <p class="muted">この route は主に select 系 function 向けです。現在の action type は <code><?php echo app_h($effectiveActionType); ?></code> です。</p>
            <?php endif; ?>
        </section>
    </div>

    <?php if ($created): ?>
        <div class="success">select having row を作成しました。</div>
    <?php endif; ?>

    <?php if ($deleted): ?>
        <div class="success">select having row を削除しました。</div>
    <?php endif; ?>

    <?php if ($targetFieldCatalogError !== ''): ?>
        <p class="muted"><?php echo app_h($targetFieldCatalogError); ?></p>
    <?php elseif ($targetFieldCatalog === []): ?>
        <p class="muted">まず <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-target-fields">select target fields designer</a> で row を保存してください。</p>
    <?php endif; ?>

    <?php if ($canonicalFunctionItem !== null && $catalogError === '' && $targetFieldCatalogError === '' && $targetFieldCatalog !== []): ?>
        <p><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-having/new">Add New Select Having</a></p>
    <?php endif; ?>

    <?php if ($items === []): ?>
        <p>select having row はまだありません。</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>left target</th>
                <th>relation</th>
                <th>right target</th>
                <th>order</th>
                <th>canonical state</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $index => $item): ?>
                <tr>
                    <td><?php echo app_h((string) ($index + 1)); ?></td>
                    <td><code><?php echo app_h(app_db_access_select_having_left_summary($item, $targetFieldById)); ?></code></td>
                    <td><code><?php echo app_h($item['relational_operator']); ?></code></td>
                    <td>
                        <code><?php echo app_h(app_db_access_select_having_parameter_type_caption($item['right_parameter_type'])); ?></code><br>
                        <span class="muted"><?php echo app_h(app_db_access_select_having_right_summary($item, $targetFieldById)); ?></span>
                    </td>
                    <td><code><?php echo app_h($item['having_order']); ?></code></td>
                    <td>
                        <code><?php echo app_h($item['source_of_truth']); ?></code><br>
                        <span class="muted">updated: <?php echo app_h($item['updated_at']); ?></span>
                    </td>
                    <td><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-having/<?php echo rawurlencode($item['select_having_id']); ?>">edit</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <section class="summary-grid">
        <section class="summary-card">
            <h2>Legacy Schema</h2>
            <ul>
                <li>data file: <code><?php echo app_h($legacySchema['data_file']); ?></code></li>
                <li>field count: <code><?php echo app_h((string) count($legacySchema['field_names'])); ?></code></li>
                <li>repository methods: <code><?php echo app_h((string) count($legacySchema['dbaccess_methods'])); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>次の導線</h2>
            <ul>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>">function detail</a></li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-target-fields">select target fields designer</a></li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-where">select where designer</a></li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/source">function source</a></li>
            </ul>
        </section>
    </section>

    <?php if ($legacySchema['data_excerpt'] !== ''): ?>
        <section>
            <h2>`data-dafuncselecthaving.php` Preview</h2>
            <pre><?php echo app_h($legacySchema['data_excerpt']); ?></pre>
        </section>
    <?php endif; ?>

    <?php if ($legacySchema['dbaccess_excerpt'] !== ''): ?>
        <section>
            <h2>`dbaccess-dafuncselecthaving.php` Preview</h2>
            <pre><?php echo app_h($legacySchema['dbaccess_excerpt']); ?></pre>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
