<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/project_db_access_route_common.php';

/**
 * @param list<array{
 *     select_where_id:string,
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string,
 *     another_table_name:string,
 *     another_table_alias_name:string,
 *     another_field_name:string,
 *     join_type:string,
 *     or_group:string,
 *     relational_operator:string,
 *     where_order:string,
 *     source_of_truth:string,
 *     updated_at:string
 * }> $items
 * @return array{
 *     order_inputs:array<string,string>,
 *     updates:list<array{
 *         select_where_id:string,
 *         where_order:string
 *     }>,
 *     errors:list<string>
 * }
 */
function app_project_db_access_function_select_where_change_order_submission(array $items): array
{
    $orderInputs = [];
    $expectedIds = [];
    foreach ($items as $item) {
        $orderInputs[$item['select_where_id']] = $item['where_order'];
        $expectedIds[] = $item['select_where_id'];
    }

    $rowIds = $_POST['row_ids'] ?? null;
    $rowOrders = $_POST['row_orders'] ?? null;
    if (!is_array($rowIds) || !is_array($rowOrders) || count($rowIds) !== count($rowOrders)) {
        return [
            'order_inputs' => $orderInputs,
            'updates' => [],
            'errors' => ['送信データの形式が不正です。再読み込みしてやり直してください。'],
        ];
    }

    $updates = [];
    $submittedIds = [];
    $errors = [];

    for ($i = 0, $count = count($rowIds); $i < $count; $i++) {
        $rowIdValue = $rowIds[$i] ?? null;
        $rowOrderValue = $rowOrders[$i] ?? null;
        $rowId = is_string($rowIdValue) ? trim($rowIdValue) : '';
        $rowOrder = is_string($rowOrderValue) ? trim($rowOrderValue) : '';

        if ($rowId !== '') {
            $orderInputs[$rowId] = $rowOrder;
        }

        if (!ctype_digit($rowId)) {
            $errors[] = 'select where row id の形式が不正です。';
            continue;
        }

        if (!ctype_digit($rowOrder)) {
            $errors[] = 'where order は 0 以上の整数で入力してください。';
            continue;
        }

        $submittedIds[] = $rowId;
        $updates[] = [
            'select_where_id' => $rowId,
            'where_order' => $rowOrder,
        ];
    }

    $expectedIdsSorted = $expectedIds;
    $submittedIdsSorted = $submittedIds;
    sort($expectedIdsSorted, SORT_NUMERIC);
    sort($submittedIdsSorted, SORT_NUMERIC);

    if ($errors === [] && $expectedIdsSorted !== $submittedIdsSorted) {
        $errors[] = '送信された row 一覧が現在の catalog と一致しません。再読み込みしてやり直してください。';
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
function app_render_project_db_access_function_select_where_change_order_page(array $app, array $request): void
{
    $bootstrap = app_project_db_access_function_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $entity = $bootstrap['entity'];
    $method = $bootstrap['method'];
    $functionProfile = $bootstrap['function_profile'];
    $methodCount = count($bootstrap['method_catalog']);
    $basePath = '/projects/' . rawurlencode($projectKey)
        . '/db-access/' . rawurlencode($entity['source_name'])
        . '/functions/' . rawurlencode($method['name']);
    $listPath = $basePath . '/select-where';
    $changeOrderPath = $listPath . '/change-order';

    $canonicalFunctionResult = app_fetch_db_access_function_metadata(
        $app,
        $projectKey,
        $entity['source_name'],
        $method['name'],
    );
    $canonicalFunctionError = $canonicalFunctionResult['ok'] ? '' : $canonicalFunctionResult['error'];
    $canonicalFunctionItem = $canonicalFunctionResult['ok'] ? $canonicalFunctionResult['item'] : null;
    $effectiveActionType = $canonicalFunctionItem !== null && $canonicalFunctionItem['action_type'] !== ''
        ? $canonicalFunctionItem['action_type']
        : $functionProfile['legacy_action_type'];
    $isSelectFunction = in_array($effectiveActionType, ['SELECTSINGLE', 'SELECTLIST'], true);

    $catalogResult = app_fetch_db_access_function_select_where_catalog(
        $app,
        $projectKey,
        $entity['source_name'],
        $method['name'],
    );
    $catalogError = $catalogResult['ok'] ? '' : $catalogResult['error'];
    $items = $catalogResult['ok'] ? $catalogResult['items'] : [];

    $orderInputs = [];
    foreach ($items as $item) {
        $orderInputs[$item['select_where_id']] = $item['where_order'];
    }

    $errors = [];
    if (app_request_method_is($request, 'POST')) {
        $formAction = app_post_param('form_action', 'save');

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif ($canonicalFunctionError !== '') {
            $errors[] = $canonicalFunctionError;
        } elseif ($canonicalFunctionItem === null) {
            $errors[] = '先に function detail で canonical metadata を保存してください。';
        } elseif ($catalogError !== '') {
            $errors[] = $catalogError;
        } elseif ($items === []) {
            $errors[] = '並び替える select where row がありません。';
        } else {
            if ($formAction === 'reset') {
                $updates = [];
                foreach ($items as $index => $item) {
                    $orderInputs[$item['select_where_id']] = (string) ($index + 1);
                    $updates[] = [
                        'select_where_id' => $item['select_where_id'],
                        'where_order' => (string) ($index + 1),
                    ];
                }
            } else {
                $submission = app_project_db_access_function_select_where_change_order_submission($items);
                $orderInputs = $submission['order_inputs'];
                $updates = $submission['updates'];
                $errors = array_merge($errors, $submission['errors']);
            }

            if ($errors === []) {
                $reorderResult = app_reorder_db_access_function_select_where($app, [
                    'project_key' => $projectKey,
                    'source_name' => $entity['source_name'],
                    'function_name' => $method['name'],
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
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Function Select Where Change Order</title>
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
        code, pre {
            background: #edf2f7;
            border-radius: 6px;
        }
        code {
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
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">functions</a> / <a href="<?php echo app_h($basePath); ?>"><code><?php echo app_h($method['name']); ?></code></a> / <a href="<?php echo app_h($listPath); ?>">select-where</a> / change-order</p>

    <h1><?php echo app_h($project['name']); ?> Select Where Change Order</h1>
    <p><code>select where</code> 条件行の並び順を更新する画面です。drag-sort はまだ移植していません。まずは canonical row の <code>where_order</code> を一括更新し、必要なら <code>RESET</code> で表示順のまま 1..n に再採番します。</p>

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
                <p class="muted"><a href="<?php echo app_h($basePath); ?>">function detail</a> で canonical metadata を先に保存してください。</p>
            <?php else: ?>
                <ul>
                    <li>source of truth: <code><?php echo app_h($canonicalFunctionItem['source_of_truth']); ?></code></li>
                    <li>updated: <code><?php echo app_h($canonicalFunctionItem['updated_at']); ?></code></li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="summary-card">
            <h2>Catalog Status</h2>
            <?php if ($catalogError !== ''): ?>
                <p class="muted">未接続</p>
                <p class="muted"><?php echo app_h($catalogError); ?></p>
            <?php else: ?>
                <ul>
                    <li>saved rows: <code><?php echo app_h((string) count($items)); ?></code></li>
                    <li>current mode: <code>manual bulk update</code></li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>現在の制約</h2>
            <ul>
                <li>旧画面の jQuery UI sortable はまだ未移植</li>
                <li><code>RESET</code> は現在の表示順のまま 1..n を採番する</li>
                <li>input aid や table metadata 連携は別タスク</li>
            </ul>
            <?php if (!$isSelectFunction): ?>
                <p class="muted">この route は主に select 系 function 向けです。現在の action type は <code><?php echo app_h($effectiveActionType); ?></code> です。</p>
            <?php endif; ?>
        </section>
    </div>

    <?php if ($updated): ?>
        <div class="success">select where row の並び順を更新しました。</div>
    <?php endif; ?>

    <?php if ($reset): ?>
        <div class="success">select where row の並び順を 1..n に再採番しました。</div>
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
        <p>select where row はまだありません。</p>
    <?php else: ?>
        <form action="<?php echo app_h($changeOrderPath); ?>" method="post">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">

            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>join / target</th>
                    <th>relation / parameter</th>
                    <th>OR group</th>
                    <th>current order</th>
                    <th>new order</th>
                    <th>action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $index => $item): ?>
                    <?php
                    $targetLabel = $item['target_table_name'];
                    if ($item['target_table_alias_name'] !== '') {
                        $targetLabel .= ' as ' . $item['target_table_alias_name'];
                    }
                    if ($item['target_table_column_name'] !== '') {
                        $targetLabel .= '.' . $item['target_table_column_name'];
                    }
                    ?>
                    <tr>
                        <td><?php echo app_h((string) ($index + 1)); ?></td>
                        <td>
                            <code><?php echo app_h(app_db_access_select_where_join_type_caption($item['join_type'])); ?></code><br>
                            <span class="muted"><?php echo app_h($targetLabel !== '' ? $targetLabel : '(blank)'); ?></span>
                        </td>
                        <td>
                            <code><?php echo app_h($item['relational_operator']); ?></code><br>
                            <span class="muted"><?php echo app_h(app_db_access_select_where_parameter_summary($item)); ?></span>
                        </td>
                        <td><code><?php echo app_h($item['or_group'] !== '' ? $item['or_group'] : '(blank)'); ?></code></td>
                        <td><code><?php echo app_h($item['where_order']); ?></code></td>
                        <td>
                            <input type="hidden" name="row_ids[]" value="<?php echo app_h($item['select_where_id']); ?>">
                            <input name="row_orders[]" type="number" min="0" value="<?php echo app_h($orderInputs[$item['select_where_id']] ?? $item['where_order']); ?>">
                        </td>
                        <td><a href="<?php echo app_h($listPath); ?>/<?php echo rawurlencode($item['select_where_id']); ?>">edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <p class="muted">同じ値がある場合、一覧表示は <code>where_order</code> と row id の順で安定化されます。</p>

            <div class="actions">
                <button type="submit" name="form_action" value="save">Save Order</button>
                <button type="submit" name="form_action" value="reset" class="button-secondary">Reset Sequential Order</button>
            </div>
        </form>
    <?php endif; ?>

    <section class="summary-grid">
        <section class="summary-card">
            <h2>Legacy Route</h2>
            <ul>
                <li>legacy screen: <code>da_func_select_where_change_order.php</code></li>
                <li>legacy include: <code>da_func_select_where_change_order_include.php</code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>次の導線</h2>
            <ul>
                <li><a href="<?php echo app_h($listPath); ?>">select where designer</a></li>
                <li><a href="<?php echo app_h($basePath); ?>">function detail</a></li>
                <li><a href="<?php echo app_h($basePath); ?>/source">function source</a></li>
                <li><a href="<?php echo app_h($basePath); ?>/endpoint">endpoint preview</a></li>
            </ul>
        </section>
    </section>
</main>
</body>
</html>
    <?php
}
