<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/custom_proxy_service.php';
require_once __DIR__ . '/project_custom_proxy_route_common.php';
require_once __DIR__ . '/project_proxy_route_common.php';

function app_project_custom_proxies_is_legacy_create_bridge_mode(string $bridgeMode): bool
{
    return trim($bridgeMode) === 'legacy-custom-proxy-create';
}

function app_render_project_custom_proxies_page(array $app, array $request): void
{
    $bootstrap = app_project_custom_proxy_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $sourceOutputCatalog = $bootstrap['source_output_catalog'];
    $customProxyTargetSourceOutputs = array_values(array_filter(
        $sourceOutputCatalog,
        static fn ($sourceOutput): bool => is_array($sourceOutput)
            && app_source_output_supports_custom_proxy_targets($sourceOutput),
    ));

    $input = app_custom_proxy_form_defaults();
    $errors = app_project_proxy_bridge_errors_from_request();
    $created = app_query_param('created') === '1';
    $deleted = app_query_param('deleted') === '1';

    if (app_request_method_is($request, 'POST')) {
        $bridgeErrors = app_project_proxy_bridge_errors_from_request();
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $action = trim(app_post_param('action'));
            if ($action === 'create') {
                $bridgeMode = app_post_param('bridge_mode');
                $useLegacyCreateBridge = app_project_custom_proxies_is_legacy_create_bridge_mode($bridgeMode);
                $candidateKey = app_post_param('custom_proxy_key');
                if (trim($candidateKey) === '') {
                    $candidateKey = app_build_custom_proxy_key_candidate(
                        app_post_param('basename'),
                        app_post_param('name'),
                    );
                }

                $validation = app_validate_custom_proxy_form([
                    'custom_proxy_key' => $candidateKey,
                    'basename' => app_post_param('basename'),
                    'name' => app_post_param('name'),
                    'in_transaction' => app_post_param('in_transaction', '0'),
                    'auth_type' => app_post_param('auth_type'),
                    'single_get_function_name' => app_post_param('single_get_function_name'),
                    'continue_even_if_failed_to_insert' => app_post_param('continue_even_if_failed_to_insert', '0'),
                    'notes' => app_post_param('notes'),
                    'source_of_truth' => 'manual',
                ]);
                $input = $validation['input'];
                $errors = array_merge($errors, $validation['errors']);

                if ($errors === []) {
                    $createResult = app_create_project_custom_proxy($app, array_merge(
                        ['project_key' => $projectKey],
                        $input,
                    ));

                    if ($createResult['ok']) {
                        if ($useLegacyCreateBridge && array_key_exists('source_output_keys', $_POST)) {
                            $requestedTargetKeys = $_POST['source_output_keys'] ?? [];
                            if (!is_array($requestedTargetKeys)) {
                                $requestedTargetKeys = [];
                            }

                            $selectedTargetKeys = app_custom_proxy_normalize_target_source_output_keys(
                                $requestedTargetKeys,
                                $customProxyTargetSourceOutputs,
                            );
                            $replaceTargetsResult = app_replace_project_custom_proxy_target_keys(
                                $app,
                                $projectKey,
                                $input['custom_proxy_key'],
                                $selectedTargetKeys,
                            );
                            if (!$replaceTargetsResult['ok']) {
                                $errors[] = $replaceTargetsResult['error'];
                            }
                        }

                        if ($errors === []) {
                            app_send_redirect_response(
                                $request,
                                app_project_custom_proxy_detail_path($projectKey, $input['custom_proxy_key']) . '?created=1',
                            );
                            return;
                        }
                    } else {
                        $errors[] = $createResult['error'];
                    }
                }
            } elseif ($action === 'delete') {
                $customProxyKey = app_normalize_custom_proxy_key(app_post_param('custom_proxy_key'));
                if ($customProxyKey === '' || !app_custom_proxy_key_is_valid($customProxyKey)) {
                    $errors[] = '削除対象の custom proxy key が不正です。';
                } elseif ($errors === []) {
                    $deleteResult = app_delete_project_custom_proxy($app, $projectKey, $customProxyKey);
                    if ($deleteResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_custom_proxies_path($projectKey) . '?deleted=1',
                        );
                        return;
                    }

                    $errors[] = $deleteResult['error'];
                }
            } elseif ($bridgeErrors === []) {
                $errors[] = '未対応の操作です。';
            }
        }
    }

    $catalogResult = app_fetch_project_custom_proxy_catalog($app, $projectKey);
    if (!$catalogResult['ok']) {
        $errors[] = $catalogResult['error'];
    }
    $catalog = $catalogResult['items'];

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Custom Proxy</title>
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
        .button-secondary {
            background: #475569;
        }
        .inline-form {
            margin: 0;
            padding: 0;
            border: 0;
            background: transparent;
        }
        .muted {
            color: #475569;
        }
        .actions a, .actions button {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / proxy / custom</p>

    <h1><?php echo app_h($project['name']); ?> Custom Proxy</h1>
    <p>複数の DB Access function を step として束ね、target source output ごとに含める custom proxy の canonical metadata を project 単位で管理する画面です。auth policy / step / target source output をここで確定します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>現在の到達点</h2>
            <ul>
                <li>custom proxy 本体の key / basename / name / auth / transaction を保存できます。</li>
                <li>step 一覧は detail から <code>functions</code> 画面へ遷移して編集します。</li>
                <li>source output target は detail 画面で複数選択できます。</li>
            </ul>
        </section>

        <section class="note-card">
            <h2>現在の制約</h2>
            <ul>
                <li><code>AddIndentCount</code> と <code>AddIndentType</code> は移植していません。</li>
                <li>target source output を持つ proxy は custom proxy build plan として source output generator から参照されます。</li>
                <li>legacy seed がある project では既存 proxy が catalog に出ます。seed が無い project はここから作り始めます。</li>
            </ul>
            <p class="muted">利用可能な source output: <code><?php echo app_h((string) count($sourceOutputCatalog)); ?></code></p>
        </section>
    </div>

    <?php if ($created): ?>
        <div class="success-card">custom proxy を作成しました。</div>
    <?php endif; ?>

    <?php if ($deleted): ?>
        <div class="success-card">custom proxy を削除しました。</div>
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

    <form method="post" action="<?php echo app_h(app_project_custom_proxies_path($projectKey)); ?>">
        <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
        <input type="hidden" name="action" value="create">

        <h2>Create Custom Proxy</h2>

        <label for="custom_proxy_key">custom proxy key</label>
        <input id="custom_proxy_key" name="custom_proxy_key" value="<?php echo app_h($input['custom_proxy_key']); ?>" placeholder="空欄なら basename + name から自動生成">

        <label for="basename">basename</label>
        <input id="basename" name="basename" value="<?php echo app_h($input['basename']); ?>" placeholder="例: DB">

        <label for="name">name</label>
        <input id="name" name="name" value="<?php echo app_h($input['name']); ?>" placeholder="例: Import">

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

        <label for="notes">notes</label>
        <textarea id="notes" name="notes"><?php echo app_h($input['notes']); ?></textarea>

        <button type="submit">Create Custom Proxy</button>
    </form>

    <h2>Catalog</h2>
    <?php if ($catalog === []): ?>
        <p class="muted">custom proxy はまだありません。</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>key</th>
                    <th>display</th>
                    <th>auth</th>
                    <th>steps / targets</th>
                    <th>updated</th>
                    <th>actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($catalog as $customProxy): ?>
                    <?php $authPolicy = app_resolve_custom_proxy_auth_policy($customProxy['auth_type'], $customProxy['single_get_function_name']); ?>
                    <tr>
                        <td><code><?php echo app_h($customProxy['custom_proxy_key']); ?></code></td>
                        <td>
                            <strong><?php echo app_h(app_custom_proxy_display_name($customProxy['basename'], $customProxy['name'])); ?></strong><br>
                            <span class="muted"><?php echo app_h($customProxy['source_of_truth']); ?></span>
                        </td>
                        <td>
                            <?php echo app_h($authPolicy['resolved_auth_type_caption']); ?><br>
                            <span class="muted"><?php echo app_h($authPolicy['summary']); ?></span><br>
                            <span class="muted"><?php echo app_h($customProxy['in_transaction'] === '1' ? 'transaction on' : 'transaction off'); ?></span>
                        </td>
                        <td>
                            <code><?php echo app_h((string) $customProxy['step_count']); ?></code> steps<br>
                            <code><?php echo app_h((string) $customProxy['target_count']); ?></code> targets
                        </td>
                        <td><code><?php echo app_h($customProxy['updated_at']); ?></code></td>
                        <td class="actions">
                            <a href="<?php echo app_h(app_project_custom_proxy_detail_path($projectKey, $customProxy['custom_proxy_key'])); ?>">detail</a>
                            <a href="<?php echo app_h(app_project_custom_proxy_functions_path($projectKey, $customProxy['custom_proxy_key'])); ?>">functions</a>
                            <form class="inline-form" method="post" action="<?php echo app_h(app_project_custom_proxies_path($projectKey)); ?>">
                                <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="custom_proxy_key" value="<?php echo app_h($customProxy['custom_proxy_key']); ?>">
                                <button type="submit" class="button-secondary">delete</button>
                            </form>
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
