<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/custom_proxy_service.php';
require_once __DIR__ . '/db_access_endpoint_policy.php';
require_once __DIR__ . '/project_custom_proxy_route_common.php';
require_once __DIR__ . '/project_proxy_route_common.php';

function app_project_custom_proxy_detail_is_legacy_edit_bridge_mode(string $bridgeMode): bool
{
    return trim($bridgeMode) === 'legacy-custom-proxy-edit';
}

function app_project_custom_proxy_form_from_item(array $item): array
{
    return [
        'custom_proxy_key' => $item['custom_proxy_key'],
        'basename' => $item['basename'],
        'name' => $item['name'],
        'in_transaction' => $item['in_transaction'],
        'auth_type' => $item['auth_type'],
        'single_get_function_name' => $item['single_get_function_name'],
        'continue_even_if_failed_to_insert' => $item['continue_even_if_failed_to_insert'],
        'notes' => $item['notes'],
        'source_of_truth' => $item['source_of_truth'],
    ];
}

function app_render_project_custom_proxy_detail_page(array $app, array $request): void
{
    $bootstrap = app_project_custom_proxy_item_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $customProxyKey = $bootstrap['custom_proxy_key'];
    $customProxy = $bootstrap['custom_proxy'];
    $sourceOutputCatalog = $bootstrap['source_output_catalog'];
    $customProxyTargetSourceOutputs = array_values(array_filter(
        $sourceOutputCatalog,
        static fn ($sourceOutput): bool => is_array($sourceOutput)
            && app_source_output_supports_custom_proxy_targets($sourceOutput),
    ));

    $input = app_project_custom_proxy_form_from_item($customProxy);
    $errors = app_project_proxy_bridge_errors_from_request();
    $created = app_query_param('created') === '1';
    $updated = app_query_param('updated') === '1';

    $targetKeysResult = app_fetch_project_custom_proxy_target_keys($app, $projectKey, $customProxyKey);
    if (!$targetKeysResult['ok']) {
        $errors[] = $targetKeysResult['error'];
    }
    $selectedTargetKeys = $targetKeysResult['items'];

    $stepCatalogResult = app_fetch_project_custom_proxy_step_catalog($app, $projectKey, $customProxyKey);
    if (!$stepCatalogResult['ok']) {
        $errors[] = $stepCatalogResult['error'];
    }
    $stepCatalog = $stepCatalogResult['items'];

    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif (app_normalize_custom_proxy_key(app_post_param('custom_proxy_key')) !== $customProxyKey) {
            $errors[] = '更新対象の custom proxy key が route と一致しません。';
        } else {
            $useLegacyEditBridge = app_project_custom_proxy_detail_is_legacy_edit_bridge_mode(
                app_post_param('bridge_mode'),
            );
            $validation = app_validate_custom_proxy_form([
                'custom_proxy_key' => $customProxyKey,
                'basename' => app_post_param('basename', $input['basename']),
                'name' => app_post_param('name', $input['name']),
                'in_transaction' => app_post_param('in_transaction', $input['in_transaction']),
                'auth_type' => app_post_param('auth_type', $input['auth_type']),
                'single_get_function_name' => app_post_param(
                    'single_get_function_name',
                    $input['single_get_function_name'],
                ),
                'continue_even_if_failed_to_insert' => app_post_param(
                    'continue_even_if_failed_to_insert',
                    $input['continue_even_if_failed_to_insert'],
                ),
                'notes' => app_post_param('notes', $input['notes']),
                'source_of_truth' => app_post_param('source_of_truth', $input['source_of_truth']),
            ]);
            $input = $validation['input'];
            $errors = array_merge($errors, $validation['errors']);

            if (!$useLegacyEditBridge || array_key_exists('source_output_keys', $_POST)) {
                $requestedTargetKeys = $_POST['source_output_keys'] ?? [];
                if (!is_array($requestedTargetKeys)) {
                    $requestedTargetKeys = [];
                }
                $selectedTargetKeys = app_custom_proxy_normalize_target_source_output_keys(
                    $requestedTargetKeys,
                    $customProxyTargetSourceOutputs,
                );
            }

            if ($errors === []) {
                $updateResult = app_update_project_custom_proxy($app, array_merge(
                    ['project_key' => $projectKey],
                    $input,
                ));
                if ($updateResult['ok']) {
                    $replaceTargetsResult = app_replace_project_custom_proxy_target_keys(
                        $app,
                        $projectKey,
                        $customProxyKey,
                        $selectedTargetKeys,
                    );

                    if ($replaceTargetsResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_custom_proxy_detail_path($projectKey, $customProxyKey) . '?updated=1',
                        );
                        return;
                    }

                    $errors[] = $replaceTargetsResult['error'];
                } else {
                    $errors[] = $updateResult['error'];
                }
            }
        }
    }

    $authPolicy = app_resolve_custom_proxy_auth_policy(
        $input['auth_type'],
        $input['single_get_function_name'],
    );

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Custom Proxy Detail</title>
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
            padding: 0.1rem 0.3rem;
            border-radius: 4px;
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
        .summary-card, .note-card, .error-card, .success-card {
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
        .error-card {
            background: #fef2f2;
            border-color: #fca5a5;
        }
        .success-card {
            background: #dcfce7;
            border-color: #86efac;
        }
        form {
            margin-top: 1.5rem;
            padding: 1.5rem;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            background: #f8fafc;
        }
        label {
            display: block;
            font-weight: 600;
            margin-top: 1rem;
        }
        input, select, textarea, button {
            font: inherit;
        }
        input, select, textarea {
            width: 100%;
            box-sizing: border-box;
            margin-top: 0.35rem;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
        }
        input[readonly] {
            background: #e2e8f0;
        }
        textarea {
            min-height: 6rem;
            resize: vertical;
        }
        button {
            margin-top: 1rem;
            padding: 0.7rem 1rem;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
        }
        .checkbox-grid {
            display: grid;
            gap: 0.5rem;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            margin-top: 0.75rem;
        }
        .checkbox-grid label {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-top: 0;
            font-weight: 400;
            border: 1px solid #d7dde5;
            border-radius: 10px;
            padding: 0.75rem;
            background: #ffffff;
        }
        .checkbox-grid input {
            width: auto;
            margin-top: 0.1rem;
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
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="<?php echo app_h(app_project_custom_proxies_path($projectKey)); ?>">proxy/custom</a> / <code><?php echo app_h($customProxyKey); ?></code></p>

    <h1><?php echo app_h($project['name']); ?> Custom Proxy Detail</h1>
    <p><code><?php echo app_h(app_custom_proxy_display_name($customProxy['basename'], $customProxy['name'])); ?></code> の canonical metadata を編集する画面です。step は別画面で管理し、ここでは auth policy / transaction / target source output を確定します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Proxy</h2>
            <ul>
                <li>key: <code><?php echo app_h($customProxyKey); ?></code></li>
                <li>display: <code><?php echo app_h(app_custom_proxy_display_name($input['basename'], $input['name'])); ?></code></li>
                <li>steps: <code><?php echo app_h((string) count($stepCatalog)); ?></code></li>
                <li>targets: <code><?php echo app_h((string) count($selectedTargetKeys)); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Auth Policy</h2>
            <ul>
                <li>scope: <code>custom proxy</code></li>
                <li>raw auth type: <code><?php echo app_h($authPolicy['raw_auth_type'] !== '' ? $authPolicy['raw_auth_type'] : '(blank)'); ?></code></li>
                <li>resolved auth type: <code><?php echo app_h($authPolicy['resolved_auth_type']); ?></code></li>
                <li>strategy: <code><?php echo app_h($authPolicy['strategy_caption']); ?></code></li>
                <li>status: <code><?php echo app_h($authPolicy['is_valid'] ? 'resolved' : 'incomplete'); ?></code></li>
            </ul>
            <p class="muted"><?php echo app_h($authPolicy['summary']); ?></p>
            <?php if ($authPolicy['notes'] !== []): ?>
                <ul>
                    <?php foreach ($authPolicy['notes'] as $note): ?>
                        <li class="muted"><?php echo app_h($note); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>現在の制約</h2>
            <ul>
                <li>step の構造は source/function と <code>is_list</code> までです。</li>
                <li>旧 <code>AddIndent*</code> は移植していません。</li>
                <li>endpoint preview は <code>/proxy/custom/{custom_proxy_key}/endpoint</code> で current route 化しました。実行自体は <code>/runs/endpoints/{project_key}</code> へ manual handoff します。</li>
                <li>current target 候補は <code>custom-proxy</code> scope の source output だけに制限しています。</li>
            </ul>
        </section>
    </div>

    <?php if ($created): ?>
        <div class="success-card">custom proxy を作成しました。</div>
    <?php endif; ?>

    <?php if ($updated): ?>
        <div class="success-card">custom proxy を更新しました。</div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="error-card">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo app_h(app_project_custom_proxy_detail_path($projectKey, $customProxyKey)); ?>">
        <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
        <input type="hidden" name="custom_proxy_key" value="<?php echo app_h($customProxyKey); ?>">
        <input type="hidden" name="source_of_truth" value="<?php echo app_h($input['source_of_truth']); ?>">

        <label for="custom_proxy_key_readonly">custom proxy key</label>
        <input id="custom_proxy_key_readonly" value="<?php echo app_h($customProxyKey); ?>" readonly>

        <label for="basename">basename</label>
        <input id="basename" name="basename" value="<?php echo app_h($input['basename']); ?>">

        <label for="name">name</label>
        <input id="name" name="name" value="<?php echo app_h($input['name']); ?>">

        <label for="in_transaction">InTransaction</label>
        <select id="in_transaction" name="in_transaction">
            <option value="0"<?php echo $input['in_transaction'] === '0' ? ' selected' : ''; ?>>0 (No)</option>
            <option value="1"<?php echo $input['in_transaction'] === '1' ? ' selected' : ''; ?>>1 (Yes)</option>
        </select>

        <label for="auth_type">AuthType</label>
        <select id="auth_type" name="auth_type">
            <?php foreach (app_allowed_proxy_auth_types() as $authType): ?>
                <option value="<?php echo app_h($authType); ?>"<?php echo $input['auth_type'] === $authType ? ' selected' : ''; ?>><?php echo app_h(app_proxy_auth_type_caption($authType)); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="single_get_function_name">SingleGetFunc 相当名</label>
        <input id="single_get_function_name" name="single_get_function_name" value="<?php echo app_h($input['single_get_function_name']); ?>" placeholder="例: GetProject">

        <label for="continue_even_if_failed_to_insert">ContinueEvenIfFailedToInsert</label>
        <select id="continue_even_if_failed_to_insert" name="continue_even_if_failed_to_insert">
            <option value="0"<?php echo $input['continue_even_if_failed_to_insert'] === '0' ? ' selected' : ''; ?>>0 (Stop)</option>
            <option value="1"<?php echo $input['continue_even_if_failed_to_insert'] === '1' ? ' selected' : ''; ?>>1 (Continue)</option>
        </select>

        <label>Target Source Outputs</label>
        <?php if ($customProxyTargetSourceOutputs === []): ?>
            <p class="muted">選択できる source output がまだありません。</p>
        <?php else: ?>
            <div class="checkbox-grid">
                <?php foreach ($customProxyTargetSourceOutputs as $sourceOutput): ?>
                    <?php $sourceOutputKey = $sourceOutput['source_output_key']; ?>
                    <label>
                        <input type="checkbox" name="source_output_keys[]" value="<?php echo app_h($sourceOutputKey); ?>"<?php echo in_array($sourceOutputKey, $selectedTargetKeys, true) ? ' checked' : ''; ?>>
                        <span>
                            <strong><code><?php echo app_h($sourceOutputKey); ?></code></strong><br>
                            <span class="muted"><?php echo app_h($sourceOutput['name']); ?></span><br>
                            <span class="muted"><?php echo app_h(app_source_output_target_binding_scope_caption(app_source_output_target_binding_scope($sourceOutput))); ?></span>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <label for="notes">notes</label>
        <textarea id="notes" name="notes"><?php echo app_h($input['notes']); ?></textarea>

        <button type="submit">Save Custom Proxy</button>
    </form>

    <h2>Step Preview</h2>
    <p><a href="<?php echo app_h(app_project_custom_proxy_functions_path($projectKey, $customProxyKey)); ?>">functions 画面で step を編集する</a></p>
    <p><a href="<?php echo app_h(app_project_custom_proxy_endpoint_path($projectKey, $customProxyKey)); ?>">endpoint preview を開く</a></p>

    <?php if ($stepCatalog === []): ?>
        <p class="muted">step はまだありません。</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>order</th>
                    <th>db access</th>
                    <th>function</th>
                    <th>list</th>
                    <th>notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stepCatalog as $step): ?>
                    <tr>
                        <td><code><?php echo app_h($step['step_order']); ?></code></td>
                        <td><code><?php echo app_h($step['db_access_source_name']); ?></code></td>
                        <td><code><?php echo app_h($step['db_access_function_name']); ?></code></td>
                        <td><code><?php echo app_h($step['is_list']); ?></code></td>
                        <td><?php echo app_h($step['notes'] !== '' ? $step['notes'] : '(blank)'); ?></td>
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
