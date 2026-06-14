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
function app_render_project_db_access_function_select_target_fields_page(array $app, array $request): void
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

    $catalogResult = app_fetch_db_access_function_select_target_field_catalog(
        $app,
        $projectKey,
        $entity['source_name'],
        $method['name'],
    );
    $catalogError = $catalogResult['ok'] ? '' : $catalogResult['error'];
    $items = $catalogResult['ok'] ? $catalogResult['items'] : [];
    $legacySchema = app_project_db_access_legacy_metadata_schema($app, 'dafuncselecttargetfields');

    $effectiveActionType = $canonicalFunctionItem !== null && $canonicalFunctionItem['action_type'] !== ''
        ? $canonicalFunctionItem['action_type']
        : $functionProfile['legacy_action_type'];
    $isSelectFunction = in_array($effectiveActionType, ['SELECTSINGLE', 'SELECTLIST'], true);
    $created = app_query_param('created') === '1';
    $deleted = app_query_param('deleted') === '1';

    $storeFieldNameCounts = [];
    foreach ($items as $item) {
        $storeClassFieldName = trim($item['store_class_field_name']);
        if ($storeClassFieldName === '') {
            continue;
        }

        if (!array_key_exists($storeClassFieldName, $storeFieldNameCounts)) {
            $storeFieldNameCounts[$storeClassFieldName] = 0;
        }
        $storeFieldNameCounts[$storeClassFieldName]++;
    }

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Function Select Target Fields</title>
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
        .warning {
            color: #9a3412;
            font-weight: 600;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">functions</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>"><code><?php echo app_h($method['name']); ?></code></a> / select-target-fields</p>

    <h1><?php echo app_h($project['name']); ?> Select Target Fields Designer</h1>
    <p>select 系 function の返却 field を canonical row として管理する画面です。まずは <code>db-config</code> に保存し、一覧・編集できる状態にします。</p>

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
                    <li>duplicate store fields: <code><?php echo app_h((string) count(array_filter($storeFieldNameCounts, static fn (int $count): bool => $count > 1))); ?></code></li>
                    <li>state: <code><?php echo app_h($items === [] ? 'empty' : 'active'); ?></code></li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>現在の制約</h2>
            <ul>
                <li>legacy の sync/import 画面はまだ未実装</li>
                <li><code>field_list_order</code> は当面ここで直接入力する</li>
                <li>同一 <code>store_class_field_name</code> は警告のみで、まだ保存時にブロックしない</li>
            </ul>
            <?php if (!$isSelectFunction): ?>
                <p class="muted">この route は主に select 系 function 向けです。現在の action type は <code><?php echo app_h($effectiveActionType); ?></code> です。</p>
            <?php endif; ?>
        </section>
    </div>

    <?php if ($created): ?>
        <div class="success">select target field row を作成しました。</div>
    <?php endif; ?>

    <?php if ($deleted): ?>
        <div class="success">select target field row を削除しました。</div>
    <?php endif; ?>

    <?php if ($canonicalFunctionItem !== null && $catalogError === ''): ?>
        <p><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-target-fields/new">Add New Select Target Field</a></p>
    <?php endif; ?>

    <?php if ($items === []): ?>
        <p>select target field row はまだありません。</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>target</th>
                <th>column expression</th>
                <th>store field</th>
                <th>group-by / order</th>
                <th>canonical state</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $index => $item): ?>
                <?php $storeFieldName = trim($item['store_class_field_name']); ?>
                <tr>
                    <td><?php echo app_h((string) ($index + 1)); ?></td>
                    <td><code><?php echo app_h(app_db_access_target_table_reference_label($item)); ?></code></td>
                    <td><code><?php echo app_h(app_db_access_select_target_field_column_expression($item)); ?></code></td>
                    <td>
                        <code><?php echo app_h($storeFieldName !== '' ? $storeFieldName : '(blank)'); ?></code>
                        <?php if ($storeFieldName !== '' && ($storeFieldNameCounts[$storeFieldName] ?? 0) > 1): ?>
                            <br><span class="warning">duplicate store field</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <code><?php echo app_h(app_db_access_group_by_target_caption($item['group_by_target'])); ?></code><br>
                        <span class="muted">order: <?php echo app_h($item['field_list_order']); ?></span>
                    </td>
                    <td>
                        <code><?php echo app_h($item['source_of_truth']); ?></code><br>
                        <span class="muted">updated: <?php echo app_h($item['updated_at']); ?></span>
                    </td>
                    <td><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-target-fields/<?php echo rawurlencode($item['select_target_field_id']); ?>">edit</a></td>
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
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-where">select where designer</a></li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/source">function source</a></li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/endpoint">endpoint preview</a></li>
            </ul>
        </section>
    </section>

    <?php if ($legacySchema['data_excerpt'] !== ''): ?>
        <section>
            <h2>`data-dafuncselecttargetfields.php` Preview</h2>
            <pre><?php echo app_h($legacySchema['data_excerpt']); ?></pre>
        </section>
    <?php endif; ?>

    <?php if ($legacySchema['dbaccess_excerpt'] !== ''): ?>
        <section>
            <h2>`dbaccess-dafuncselecttargetfields.php` Preview</h2>
            <pre><?php echo app_h($legacySchema['dbaccess_excerpt']); ?></pre>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
