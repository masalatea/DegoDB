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
function app_render_project_db_access_function_update_delete_where_page(array $app, array $request): void
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

    $catalogResult = app_fetch_db_access_function_update_delete_where_catalog(
        $app,
        $projectKey,
        $entity['source_name'],
        $method['name'],
    );
    $catalogError = $catalogResult['ok'] ? '' : $catalogResult['error'];
    $items = $catalogResult['ok'] ? $catalogResult['items'] : [];
    $legacySchema = app_project_db_access_legacy_metadata_schema($app, 'dafuncupdatedeletewhere');

    $effectiveActionType = $canonicalFunctionItem !== null && $canonicalFunctionItem['action_type'] !== ''
        ? $canonicalFunctionItem['action_type']
        : $functionProfile['legacy_action_type'];
    $targetTableName = $canonicalFunctionItem !== null && $canonicalFunctionItem['target_table_name'] !== ''
        ? $canonicalFunctionItem['target_table_name']
        : $entity['source_name'];
    $isUpdateDeleteFunction = in_array($effectiveActionType, ['UPDATE', 'DELETE'], true);
    $created = app_query_param('created') === '1';
    $deleted = app_query_param('deleted') === '1';
    $basePath = '/projects/' . rawurlencode($projectKey)
        . '/db-access/' . rawurlencode($entity['source_name'])
        . '/functions/' . rawurlencode($method['name']);

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Function Update Delete Where</title>
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
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">functions</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>"><code><?php echo app_h($method['name']); ?></code></a> / update-delete-where</p>

    <h1><?php echo app_h($project['name']); ?> Update/Delete Where Designer</h1>
    <p>update/delete 系 function の where 条件を canonical row として管理する画面です。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Function</h2>
            <ul>
                <li>db access: <code><?php echo app_h($entity['source_name']); ?></code></li>
                <li>function: <code><?php echo app_h($method['name']); ?></code></li>
                <li>action type: <code><?php echo app_h($effectiveActionType); ?></code></li>
                <li>target table: <code><?php echo app_h($targetTableName); ?></code></li>
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
                    <li>state: <code><?php echo app_h($items === [] ? 'empty' : 'active'); ?></code></li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>現在の制約</h2>
            <ul>
                <li><code>where_order</code> は当面ここで直接入力する</li>
                <li>target table column の実在確認はまだ未実装</li>
                <li>change-order は一括順序更新と `RESET` のみ先行実装し、drag-sort はまだ未移植</li>
                <li>input-aid は generated property 候補ベースの簡易版を先行実装した</li>
            </ul>
            <?php if (!$isUpdateDeleteFunction): ?>
                <p class="muted">この route は主に update/delete 系 function 向けです。現在の action type は <code><?php echo app_h($effectiveActionType); ?></code> です。</p>
            <?php endif; ?>
        </section>
    </div>

    <?php if ($created): ?>
        <div class="success">update/delete where row を作成しました。</div>
    <?php endif; ?>

    <?php if ($deleted): ?>
        <div class="success">update/delete where row を削除しました。</div>
    <?php endif; ?>

    <?php if ($canonicalFunctionItem !== null && $catalogError === ''): ?>
        <p>
            <a href="<?php echo app_h($basePath); ?>/update-delete-where/new">Add New Update/Delete Where</a>
            / <a href="<?php echo app_h($basePath); ?>/update-delete-where/input-aid">Input Aid</a>
            <?php if ($items !== []): ?>
                / <a href="<?php echo app_h($basePath); ?>/update-delete-where/change-order">Change Order</a>
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <?php if ($items === []): ?>
        <p>update/delete where row はまだありません。</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>target column</th>
                <th>relation</th>
                <th>parameter</th>
                <th>OR / order</th>
                <th>canonical state</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $index => $item): ?>
                <tr>
                    <td><?php echo app_h((string) ($index + 1)); ?></td>
                    <td><code><?php echo app_h($item['target_table_column_name']); ?></code></td>
                    <td><code><?php echo app_h($item['relational_operator']); ?></code></td>
                    <td>
                        <code><?php echo app_h(app_db_access_update_delete_where_parameter_type_caption($item['parameter_type'])); ?></code><br>
                        <span class="muted"><?php echo app_h(app_db_access_update_delete_where_parameter_summary($item)); ?></span>
                    </td>
                    <td>
                        <code><?php echo app_h($item['or_group'] !== '' ? $item['or_group'] : '(blank)'); ?></code><br>
                        <span class="muted">order: <?php echo app_h($item['where_order']); ?></span>
                    </td>
                    <td>
                        <code><?php echo app_h($item['source_of_truth']); ?></code><br>
                        <span class="muted">updated: <?php echo app_h($item['updated_at']); ?></span>
                    </td>
                    <td><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/update-delete-where/<?php echo rawurlencode($item['update_delete_where_id']); ?>">edit</a></td>
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
                <li><a href="<?php echo app_h($basePath); ?>">function detail</a></li>
                <li><a href="<?php echo app_h($basePath); ?>/update-delete-where/input-aid">input-aid</a></li>
                <?php if ($items !== []): ?>
                    <li><a href="<?php echo app_h($basePath); ?>/update-delete-where/change-order">change-order</a></li>
                <?php endif; ?>
                <li><a href="<?php echo app_h($basePath); ?>/source">function source</a></li>
                <li><a href="<?php echo app_h($basePath); ?>/endpoint">endpoint preview</a></li>
            </ul>
        </section>
    </section>

    <?php if ($legacySchema['data_excerpt'] !== ''): ?>
        <section>
            <h2>`data-dafuncupdatedeletewhere.php` Preview</h2>
            <pre><?php echo app_h($legacySchema['data_excerpt']); ?></pre>
        </section>
    <?php endif; ?>

    <?php if ($legacySchema['dbaccess_excerpt'] !== ''): ?>
        <section>
            <h2>`dbaccess-dafuncupdatedeletewhere.php` Preview</h2>
            <pre><?php echo app_h($legacySchema['dbaccess_excerpt']); ?></pre>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
