<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/project_source_output_route_common.php';
require_once __DIR__ . '/request.php';

/**
 * @param list<array{
 *     source_output_key:string,
 *     source_output_list_order:string
 * }> $items
 * @return array{
 *     order_inputs:array<string,string>,
 *     updates:list<array{
 *         source_output_key:string,
 *         source_output_list_order:string
 *     }>,
 *     errors:list<string>
 * }
 */
function app_project_source_output_change_order_submission(array $items): array
{
    $orderInputs = [];
    $expectedKeys = [];
    foreach ($items as $item) {
        $sourceOutputKey = (string) ($item['source_output_key'] ?? '');
        $orderInputs[$sourceOutputKey] = (string) ($item['source_output_list_order'] ?? '');
        $expectedKeys[] = $sourceOutputKey;
    }

    $sourceOutputKeys = $_POST['source_output_keys'] ?? null;
    $sourceOutputOrders = $_POST['source_output_orders'] ?? null;
    if (!is_array($sourceOutputKeys) || !is_array($sourceOutputOrders) || count($sourceOutputKeys) !== count($sourceOutputOrders)) {
        return [
            'order_inputs' => $orderInputs,
            'updates' => [],
            'errors' => ['送信データの形式が不正です。再読み込みしてやり直してください。'],
        ];
    }

    $updates = [];
    $submittedKeys = [];
    $errors = [];

    for ($i = 0, $count = count($sourceOutputKeys); $i < $count; $i++) {
        $sourceOutputKeyValue = $sourceOutputKeys[$i] ?? null;
        $sourceOutputOrderValue = $sourceOutputOrders[$i] ?? null;
        $sourceOutputKey = is_string($sourceOutputKeyValue)
            ? app_normalize_source_output_key($sourceOutputKeyValue)
            : '';
        $sourceOutputOrder = is_string($sourceOutputOrderValue) ? trim($sourceOutputOrderValue) : '';

        if ($sourceOutputKey !== '') {
            $orderInputs[$sourceOutputKey] = $sourceOutputOrder;
        }

        if ($sourceOutputKey === '' || !app_source_output_key_is_valid($sourceOutputKey)) {
            $errors[] = 'source output key の形式が不正です。';
            continue;
        }

        if (!ctype_digit($sourceOutputOrder)) {
            $errors[] = 'source output list order は 0 以上の整数で入力してください。';
            continue;
        }

        $submittedKeys[] = $sourceOutputKey;
        $updates[] = [
            'source_output_key' => $sourceOutputKey,
            'source_output_list_order' => $sourceOutputOrder,
        ];
    }

    $expectedKeysSorted = $expectedKeys;
    $submittedKeysSorted = $submittedKeys;
    sort($expectedKeysSorted, SORT_STRING);
    sort($submittedKeysSorted, SORT_STRING);

    if ($errors === [] && $expectedKeysSorted !== $submittedKeysSorted) {
        $errors[] = '送信された source output 一覧が現在の canonical catalog と一致しません。再読み込みしてやり直してください。';
    }

    return [
        'order_inputs' => $orderInputs,
        'updates' => $updates,
        'errors' => $errors,
    ];
}

function app_project_source_output_change_order_legacy_pid(string $notes): int
{
    if (
        preg_match(
            '/\bProjectSourceOutput\.PID\s*=\s*(\d+)/u',
            $notes,
            $matches,
        ) !== 1
    ) {
        return 0;
    }

    return (int) ($matches[1] ?? 0);
}

/**
 * @param list<array{
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     target_binding_type:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at:string
 * }> $items
 * @return list<array{
 *     source_output_key:string,
 *     source_output_list_order:string
 * }>
 */
function app_project_source_output_change_order_reset_updates(array $items): array
{
    $sortedItems = $items;
    usort($sortedItems, static function (array $left, array $right): int {
        $leftLegacyPid = app_project_source_output_change_order_legacy_pid((string) ($left['notes'] ?? ''));
        $rightLegacyPid = app_project_source_output_change_order_legacy_pid((string) ($right['notes'] ?? ''));

        if ($leftLegacyPid > 0 || $rightLegacyPid > 0) {
            if ($leftLegacyPid === 0) {
                return 1;
            }
            if ($rightLegacyPid === 0) {
                return -1;
            }
            if ($leftLegacyPid !== $rightLegacyPid) {
                return $leftLegacyPid <=> $rightLegacyPid;
            }
        }

        $leftOrder = (int) ($left['source_output_list_order'] ?? 0);
        $rightOrder = (int) ($right['source_output_list_order'] ?? 0);
        if ($leftOrder !== $rightOrder) {
            return $leftOrder <=> $rightOrder;
        }

        return strcmp(
            (string) ($left['source_output_key'] ?? ''),
            (string) ($right['source_output_key'] ?? ''),
        );
    });

    $updates = [];
    foreach ($sortedItems as $index => $item) {
        $updates[] = [
            'source_output_key' => (string) ($item['source_output_key'] ?? ''),
            'source_output_list_order' => (string) (($index + 1) * 10),
        ];
    }

    return $updates;
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
function app_render_project_source_output_change_order_page(array $app, array $request): void
{
    $bootstrap = app_project_source_output_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $principal = $bootstrap['principal'];
    $changeOrderPath = app_project_source_output_change_order_path($projectKey);

    $catalogResult = app_fetch_project_source_output_catalog($app, $projectKey);
    $catalogError = $catalogResult['ok'] ? '' : $catalogResult['error'];
    $items = $catalogResult['ok'] ? $catalogResult['items'] : [];
    $orderInputs = [];
    $legacyMappedCount = 0;
    foreach ($items as $item) {
        $sourceOutputKey = (string) ($item['source_output_key'] ?? '');
        $orderInputs[$sourceOutputKey] = (string) ($item['source_output_list_order'] ?? '');
        if (app_project_source_output_change_order_legacy_pid((string) ($item['notes'] ?? '')) > 0) {
            $legacyMappedCount++;
        }
    }

    $errors = app_project_source_output_bridge_errors_from_request();
    if (app_request_method_is($request, 'POST')) {
        $formAction = trim(app_post_param('form_action', 'save'));

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif ($catalogError !== '') {
            $errors[] = $catalogError;
        } elseif ($items === []) {
            $errors[] = '並び替える source output definition がありません。';
        } else {
            if ($formAction === 'reset') {
                $updates = app_project_source_output_change_order_reset_updates($items);
                foreach ($updates as $update) {
                    $orderInputs[$update['source_output_key']] = $update['source_output_list_order'];
                }
            } else {
                $submission = app_project_source_output_change_order_submission($items);
                $orderInputs = $submission['order_inputs'];
                $updates = $submission['updates'];
                $errors = array_merge($errors, $submission['errors']);
            }

            if ($errors === []) {
                $reorderResult = app_reorder_project_source_outputs($app, [
                    'project_key' => $projectKey,
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
    <title><?php echo app_h($app['site_name']); ?> - Source Output Change Order</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 96rem;
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
        .summary-card, .note-card, .warning-card, .error-card, .success-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
        }
        .summary-card {
            background: #f8fafc;
        }
        .note-card {
            background: #eff6ff;
            border-color: #93c5fd;
        }
        .warning-card {
            background: #fefce8;
            border-color: #facc15;
        }
        .error-card {
            background: #fef2f2;
            border-color: #fca5a5;
        }
        .success-card {
            background: #ecfdf5;
            border-color: #86efac;
        }
        .muted {
            color: #475569;
        }
        .action-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .button {
            display: inline-block;
            border: 0;
            border-radius: 8px;
            background: #0f172a;
            color: #ffffff;
            padding: 0.65rem 1rem;
            font: inherit;
            cursor: pointer;
        }
        .button-secondary {
            background: #475569;
        }
        .button-warning {
            background: #92400e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            border-bottom: 1px solid #d7dde5;
            padding: 0.75rem;
            text-align: left;
            vertical-align: top;
        }
        .position-pill {
            display: inline-block;
            min-width: 2.5rem;
            text-align: center;
            background: #e2e8f0;
            border-radius: 999px;
            padding: 0.2rem 0.5rem;
            font-weight: 700;
        }
        .row-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .row-actions button {
            min-width: 3rem;
        }
        input[type="text"] {
            width: 6rem;
            box-sizing: border-box;
            padding: 0.55rem 0.65rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font: inherit;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="<?php echo app_h(app_project_source_outputs_path($projectKey)); ?>">source-outputs</a> / change-order</p>

    <h1><?php echo app_h($project['name']); ?> Source Output Order</h1>
    <p><code>ProjectSourceOutput</code> の表示順を current canonical metadata へ保存します。legacy <code>project_source_output_change_order.php</code> の導線はこの route へ集約し、runtime 側では <code>project_source_outputs.source_output_list_order</code> だけを更新します。</p>

    <?php if ($updated): ?>
        <section class="success-card">
            <h2>更新しました</h2>
            <p>source output の並び順を保存しました。</p>
        </section>
    <?php endif; ?>

    <?php if ($reset): ?>
        <section class="success-card">
            <h2>Reset しました</h2>
            <p>legacy <code>ProjectSourceOutput.PID</code> がある row はその順、無い row は current order / key を使って 10 刻みへ正規化しました。</p>
        </section>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <section class="error-card">
            <h2>エラー</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Project</h2>
            <ul>
                <li>project key: <code><?php echo app_h($project['project_key']); ?></code></li>
                <li>definition count: <code><?php echo app_h((string) count($items)); ?></code></li>
                <li>legacy mapped rows: <code><?php echo app_h((string) $legacyMappedCount); ?></code></li>
                <li>login user: <code><?php echo app_h($principal['id']); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>操作方法</h2>
            <ul>
                <li>上下ボタンで表示順を入れ替えられます。</li>
                <li><code>Renumber 10-step</code> は画面上の順に <code>10, 20, 30...</code> を振り直します。</li>
                <li><code>Reset To Legacy / Canonical Order</code> は legacy PID を優先して stable order を再構成します。</li>
            </ul>
        </section>

        <section class="warning-card">
            <h2>互換メモ</h2>
            <ul>
                <li>legacy screen: <code>project_source_output_change_order.php</code></li>
                <li>legacy include: <code>project_source_output_change_order_include.php</code></li>
                <li>current route: <code><?php echo app_h($changeOrderPath); ?></code></li>
            </ul>
            <p class="muted">この画面は new runtime 側の canonical DB row だけを更新します。legacy copy-tree の include 実装は artifact 内 <code>_legacy/</code> へ退避されます。</p>
        </section>
    </div>

    <section>
        <h2>Definitions</h2>
        <?php if ($items === []): ?>
            <p class="muted">並び替える source output definition はまだありません。</p>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
                <table>
                    <thead>
                    <tr>
                        <th>position</th>
                        <th>order</th>
                        <th>definition</th>
                        <th>build profile</th>
                        <th>compatibility</th>
                        <th>move</th>
                    </tr>
                    </thead>
                    <tbody data-sort-body>
                    <?php foreach ($items as $item): ?>
                        <?php $sourceOutputKey = (string) ($item['source_output_key'] ?? ''); ?>
                        <?php $legacyPid = app_project_source_output_change_order_legacy_pid((string) ($item['notes'] ?? '')); ?>
                        <tr data-sort-row>
                            <td><span class="position-pill" data-position-label></span></td>
                            <td>
                                <input type="hidden" name="source_output_keys[]" value="<?php echo app_h($sourceOutputKey); ?>">
                                <input
                                    type="text"
                                    name="source_output_orders[]"
                                    value="<?php echo app_h($orderInputs[$sourceOutputKey] ?? (string) ($item['source_output_list_order'] ?? '')); ?>"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    data-order-input
                                >
                            </td>
                            <td>
                                <strong><code><?php echo app_h($sourceOutputKey); ?></code></strong><br>
                                <?php echo app_h((string) ($item['name'] ?? '')); ?><br>
                                <span class="muted">updated: <?php echo app_h((string) ($item['updated_at'] ?? '')); ?></span>
                            </td>
                            <td>
                                <code><?php echo app_h(app_source_output_artifact_strategy_caption((string) ($item['artifact_strategy'] ?? ''))); ?></code><br>
                                <span class="muted"><?php echo app_h(app_source_output_program_language_caption((string) ($item['program_language'] ?? ''))); ?></span><br>
                                <span class="muted"><?php echo app_h(app_source_output_class_type_caption((string) ($item['class_type'] ?? ''))); ?></span>
                            </td>
                            <td>
                                <span class="muted">source_of_truth: <?php echo app_h((string) ($item['source_of_truth'] ?? '')); ?></span><br>
                                <span class="muted">legacy PID: <?php echo app_h($legacyPid > 0 ? (string) $legacyPid : 'n/a'); ?></span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <button class="button button-secondary" type="button" data-move-up>Up</button>
                                    <button class="button button-secondary" type="button" data-move-down>Down</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="action-bar">
                    <button class="button button-secondary" type="button" data-renumber>Renumber 10-step</button>
                    <button class="button" type="submit">Save Order</button>
                    <button class="button button-warning" type="submit" name="form_action" value="reset">Reset To Legacy / Canonical Order</button>
                </div>
            </form>
        <?php endif; ?>
    </section>

    <p><a href="<?php echo app_h(app_project_source_outputs_path($projectKey)); ?>">Back to Source Outputs</a></p>
</main>
<script>
(() => {
    const tableBody = document.querySelector('[data-sort-body]');
    if (!tableBody) {
        return;
    }

    const updatePositionLabels = () => {
        Array.from(tableBody.querySelectorAll('[data-sort-row]')).forEach((row, index) => {
            const label = row.querySelector('[data-position-label]');
            if (label) {
                label.textContent = String(index + 1);
            }
        });
    };

    const renumber = () => {
        Array.from(tableBody.querySelectorAll('[data-sort-row]')).forEach((row, index) => {
            const input = row.querySelector('[data-order-input]');
            if (input) {
                input.value = String((index + 1) * 10);
            }
        });
    };

    tableBody.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        const row = target.closest('[data-sort-row]');
        if (!(row instanceof HTMLTableRowElement)) {
            return;
        }

        if (target.matches('[data-move-up]')) {
            const previous = row.previousElementSibling;
            if (previous) {
                tableBody.insertBefore(row, previous);
                updatePositionLabels();
                renumber();
            }
            return;
        }

        if (target.matches('[data-move-down]')) {
            const next = row.nextElementSibling;
            if (next) {
                tableBody.insertBefore(next, row);
                updatePositionLabels();
                renumber();
            }
        }
    });

    const renumberButton = document.querySelector('[data-renumber]');
    if (renumberButton instanceof HTMLElement) {
        renumberButton.addEventListener('click', () => {
            renumber();
        });
    }

    updatePositionLabels();
})();
</script>
</body>
</html>
    <?php
}
