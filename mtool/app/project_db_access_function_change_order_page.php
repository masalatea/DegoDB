<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/project_db_access_route_common.php';

/**
 * @param list<array{
 *     function_name:string,
 *     function_list_order:string,
 *     function_suffix:string,
 *     action_type:string,
 *     select_by_distinct:string,
 *     is_blob_target:string,
 *     detected_line:string,
 *     source_of_truth:string,
 *     updated_at:string,
 *     generated_line:string,
 *     generated_signature:string,
 *     missing_from_generated:bool
 * }> $items
 * @return array{
 *     order_inputs:array<string,string>,
 *     updates:list<array{
 *         function_name:string,
 *         function_list_order:string
 *     }>,
 *     errors:list<string>
 * }
 */
function app_project_db_access_function_change_order_submission(array $items): array
{
    $orderInputs = [];
    $expectedNames = [];
    foreach ($items as $item) {
        $orderInputs[$item['function_name']] = $item['function_list_order'];
        $expectedNames[] = $item['function_name'];
    }

    $functionNames = $_POST['function_names'] ?? null;
    $functionOrders = $_POST['function_orders'] ?? null;
    if (!is_array($functionNames) || !is_array($functionOrders) || count($functionNames) !== count($functionOrders)) {
        return [
            'order_inputs' => $orderInputs,
            'updates' => [],
            'errors' => ['送信データの形式が不正です。再読み込みしてやり直してください。'],
        ];
    }

    $updates = [];
    $submittedNames = [];
    $errors = [];

    for ($i = 0, $count = count($functionNames); $i < $count; $i++) {
        $functionNameValue = $functionNames[$i] ?? null;
        $functionOrderValue = $functionOrders[$i] ?? null;
        $functionName = is_string($functionNameValue) ? trim($functionNameValue) : '';
        $functionOrder = is_string($functionOrderValue) ? trim($functionOrderValue) : '';

        if ($functionName !== '') {
            $orderInputs[$functionName] = $functionOrder;
        }

        if ($functionName === '') {
            $errors[] = 'function name が空です。';
            continue;
        }

        if (!ctype_digit($functionOrder)) {
            $errors[] = 'function list order は 0 以上の整数で入力してください。';
            continue;
        }

        $submittedNames[] = $functionName;
        $updates[] = [
            'function_name' => $functionName,
            'function_list_order' => $functionOrder,
        ];
    }

    $expectedNamesSorted = $expectedNames;
    $submittedNamesSorted = $submittedNames;
    sort($expectedNamesSorted, SORT_STRING);
    sort($submittedNamesSorted, SORT_STRING);

    if ($errors === [] && $expectedNamesSorted !== $submittedNamesSorted) {
        $errors[] = '送信された function 一覧が現在の canonical catalog と一致しません。再読み込みしてやり直してください。';
    }

    return [
        'order_inputs' => $orderInputs,
        'updates' => $updates,
        'errors' => $errors,
    ];
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
function app_render_project_db_access_function_change_order_page(array $app, array $request): void
{
    $bootstrap = app_project_db_access_route_bootstrap($app, $request, ['GET', 'POST']);
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
    $methodCount = count($methods);
    $methodByName = [];
    foreach ($methods as $method) {
        $methodByName[$method['name']] = $method;
    }

    $canonicalCatalogResult = app_fetch_db_access_function_metadata_catalog($app, $projectKey, $entity['source_name']);
    $canonicalCatalogError = $canonicalCatalogResult['ok'] ? '' : $canonicalCatalogResult['error'];
    $catalogItems = $canonicalCatalogResult['ok'] ? $canonicalCatalogResult['items'] : [];
    $basePath = '/projects/' . rawurlencode($projectKey)
        . '/db-access/' . rawurlencode($entity['source_name']);
    $functionsPath = $basePath . '/functions';
    $changeOrderPath = $functionsPath . '/change-order';

    $items = [];
    $missingGeneratedCount = 0;
    foreach ($catalogItems as $item) {
        $generatedMethod = $methodByName[$item['function_name']] ?? null;
        if ($generatedMethod === null) {
            $missingGeneratedCount++;
        }
        $items[] = [
            'function_name' => $item['function_name'],
            'function_list_order' => $item['function_list_order'],
            'function_suffix' => $item['function_suffix'],
            'action_type' => $item['action_type'],
            'select_by_distinct' => $item['select_by_distinct'],
            'is_blob_target' => $item['is_blob_target'],
            'detected_line' => $item['detected_line'],
            'source_of_truth' => $item['source_of_truth'],
            'updated_at' => $item['updated_at'],
            'generated_line' => $generatedMethod !== null ? (string) $generatedMethod['line'] : '',
            'generated_signature' => $generatedMethod !== null ? $generatedMethod['signature'] : '',
            'missing_from_generated' => $generatedMethod === null,
        ];
    }

    $orderInputs = [];
    foreach ($items as $item) {
        $orderInputs[$item['function_name']] = $item['function_list_order'];
    }

    $errors = [];
    if (app_request_method_is($request, 'POST')) {
        $formAction = app_post_param('form_action', 'save');

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif ($canonicalCatalogError !== '') {
            $errors[] = $canonicalCatalogError;
        } elseif ($items === []) {
            $errors[] = '並び替える canonical function row がありません。先に function detail を保存してください。';
        } else {
            if ($formAction === 'reset') {
                $updates = [];
                foreach ($items as $index => $item) {
                    $newOrder = (string) ($index + 1);
                    $orderInputs[$item['function_name']] = $newOrder;
                    $updates[] = [
                        'function_name' => $item['function_name'],
                        'function_list_order' => $newOrder,
                    ];
                }
            } else {
                $submission = app_project_db_access_function_change_order_submission($items);
                $orderInputs = $submission['order_inputs'];
                $updates = $submission['updates'];
                $errors = array_merge($errors, $submission['errors']);
            }

            if ($errors === []) {
                $reorderResult = app_reorder_db_access_functions($app, [
                    'project_key' => $projectKey,
                    'source_name' => $entity['source_name'],
                    'orders' => $updates,
                ]);

                if ($reorderResult['ok']) {
                    app_send_redirect_response(
                        $request,
                        $changeOrderPath . ($formAction === 'reset' ? '?reset=1' : '?updated=1'),
                    );
                    return;
                }

                $errors[] = $reorderResult['error'];
            }
        }
    }

    $updated = app_query_param('updated') === '1';
    $reset = app_query_param('reset') === '1';
    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Function Change Order</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 88rem;
            background: #ffffff;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 2rem;
        }
        code {
            background: #edf2f7;
            border-radius: 6px;
            padding: 0.1rem 0.3rem;
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
        .error-list {
            margin-top: 1rem;
            padding: 1rem 1.25rem;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
            border-radius: 8px;
        }
        form {
            margin-top: 1.5rem;
            padding: 1.5rem;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            background: #f8fafc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border-bottom: 1px solid #d7dde5;
            padding: 0.75rem;
            vertical-align: top;
            text-align: left;
        }
        input[type="number"] {
            width: 8rem;
            padding: 0.55rem 0.65rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-sizing: border-box;
        }
        .actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        button {
            border: 0;
            border-radius: 999px;
            padding: 0.7rem 1.2rem;
            cursor: pointer;
            background: #0f172a;
            color: #ffffff;
        }
        .button-secondary {
            background: #475569;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="<?php echo app_h($basePath); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / <a href="<?php echo app_h($functionsPath); ?>">functions</a> / change-order</p>

    <h1><?php echo app_h($project['name']); ?> Function Change Order</h1>
    <p>保存済み canonical function row の並び順を更新する画面です。まずは <code>function_list_order</code> を一括更新し、必要なら <code>RESET</code> で現在の表示順のまま 1..n に再採番します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Source</h2>
            <ul>
                <li>db access: <code><?php echo app_h($entity['source_name']); ?></code></li>
                <li>dbaccess file: <?php echo $entity['has_dbaccess_file'] ? '<code>' . app_h($entity['dbaccess_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
                <li>generated function candidate count: <code><?php echo app_h((string) $methodCount); ?></code></li>
                <li>saved canonical rows: <code><?php echo app_h((string) count($items)); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Catalog Status</h2>
            <?php if ($canonicalCatalogError !== ''): ?>
                <p class="muted">未接続</p>
                <p class="muted"><?php echo app_h($canonicalCatalogError); ?></p>
            <?php else: ?>
                <ul>
                    <li>current mode: <code>manual bulk update</code></li>
                    <li>rows missing from generated catalog: <code><?php echo app_h((string) $missingGeneratedCount); ?></code></li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>現在の制約</h2>
            <ul>
                <li>対象は保存済み canonical function row のみで、未保存の generated method は含めない</li>
                <li>旧画面の drag-sort はまだ未移植で、数値一括更新と <code>RESET</code> のみ</li>
                <li>function sync は別タスク</li>
            </ul>
        </section>
    </div>

    <?php if ($updated): ?>
        <div class="success">function row の並び順を更新しました。</div>
    <?php endif; ?>

    <?php if ($reset): ?>
        <div class="success">function row の並び順を 1..n に再採番しました。</div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="error-list">
            <strong>保存できませんでした。</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($items === []): ?>
        <p>並び替える canonical function row はまだありません。先に各 function detail を保存してください。</p>
    <?php else: ?>
        <form action="<?php echo app_h($changeOrderPath); ?>" method="post">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">

            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>function</th>
                    <th>generated</th>
                    <th>canonical state</th>
                    <th>function list order</th>
                    <th>action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $index => $item): ?>
                    <tr>
                        <td><?php echo app_h((string) ($index + 1)); ?></td>
                        <td>
                            <code><?php echo app_h($item['function_name']); ?></code><br>
                            <span class="muted">suffix: <?php echo app_h($item['function_suffix'] !== '' ? $item['function_suffix'] : '(blank)'); ?></span><br>
                            <span class="muted">action: <?php echo app_h($item['action_type'] !== '' ? $item['action_type'] : '(blank)'); ?></span>
                        </td>
                        <td>
                            <?php if ($item['missing_from_generated']): ?>
                                <span class="muted">current generated catalog では未検出</span><br>
                                <span class="muted">last line: <?php echo app_h($item['detected_line'] !== '' ? $item['detected_line'] : '(blank)'); ?></span>
                            <?php else: ?>
                                <code>line <?php echo app_h($item['generated_line']); ?></code><br>
                                <span class="muted"><?php echo app_h($item['generated_signature']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code><?php echo app_h($item['source_of_truth']); ?></code><br>
                            <span class="muted">updated: <?php echo app_h($item['updated_at']); ?></span><br>
                            <span class="muted">distinct: <?php echo app_h($item['select_by_distinct']); ?> / blob: <?php echo app_h($item['is_blob_target']); ?></span>
                        </td>
                        <td>
                            <input type="hidden" name="function_names[]" value="<?php echo app_h($item['function_name']); ?>">
                            <input type="number" min="0" name="function_orders[]" value="<?php echo app_h($orderInputs[$item['function_name']] ?? $item['function_list_order']); ?>">
                        </td>
                        <td>
                            <a href="<?php echo app_h($functionsPath); ?>/<?php echo rawurlencode($item['function_name']); ?>">detail</a><br>
                            <a href="<?php echo app_h($functionsPath); ?>/<?php echo rawurlencode($item['function_name']); ?>/move">move</a><br>
                            <a href="<?php echo app_h($functionsPath); ?>/<?php echo rawurlencode($item['function_name']); ?>/source">source</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="actions">
                <button type="submit" name="form_action" value="save">Save Order</button>
                <button type="submit" name="form_action" value="reset" class="button-secondary">Reset To 1..n</button>
            </div>
        </form>
    <?php endif; ?>

    <p><a href="<?php echo app_h($functionsPath); ?>">Back to Function List</a></p>
    <p><a href="<?php echo app_h($basePath); ?>">Back to DB Access Detail</a></p>
</main>
</body>
</html>
    <?php
}
