<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/custom_proxy_service.php';
require_once __DIR__ . '/project_custom_proxy_route_common.php';
require_once __DIR__ . '/project_proxy_route_common.php';

function app_project_custom_proxy_functions_find_step(array $stepCatalog, string $stepId): ?array
{
    foreach ($stepCatalog as $step) {
        if ((string) ($step['id'] ?? '') === $stepId) {
            return $step;
        }
    }

    return null;
}

/**
 * @param mixed $rawStepIds
 * @return list<string>
 */
function app_project_custom_proxy_functions_normalize_step_ids($rawStepIds): array
{
    if (is_string($rawStepIds)) {
        $rawStepIds = preg_split('/,+/', $rawStepIds) ?: [];
    }

    if (!is_array($rawStepIds)) {
        return [];
    }

    $items = [];
    foreach ($rawStepIds as $rawStepId) {
        if (!is_string($rawStepId) && !is_int($rawStepId)) {
            continue;
        }

        $normalizedStepId = trim((string) $rawStepId);
        if ($normalizedStepId === '' || !ctype_digit($normalizedStepId)) {
            continue;
        }

        $items[$normalizedStepId] = $normalizedStepId;
    }

    return array_values($items);
}

function app_render_project_custom_proxy_functions_page(array $app, array $request): void
{
    $bootstrap = app_project_custom_proxy_item_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $customProxyKey = $bootstrap['custom_proxy_key'];
    $customProxy = $bootstrap['custom_proxy'];
    $generatedCatalog = $bootstrap['generated_catalog'];

    $generatedFunctionCatalog = app_custom_proxy_generated_function_catalog($generatedCatalog);
    $sourceNameOptions = [];
    $functionNameOptions = [];
    foreach ($generatedFunctionCatalog as $generatedFunction) {
        $sourceNameOptions[$generatedFunction['source_name']] = $generatedFunction['source_name'];
        $functionNameOptions[$generatedFunction['function_name']] = $generatedFunction['function_name'];
    }

    $createInput = app_custom_proxy_step_form_defaults();
    $rowOverrides = [];
    $errors = app_project_proxy_bridge_errors_from_request();
    $created = app_query_param('created') === '1';
    $updated = app_query_param('updated') === '1';
    $deleted = app_query_param('deleted') === '1';

    $stepCatalogResult = app_fetch_project_custom_proxy_step_catalog($app, $projectKey, $customProxyKey);
    if (!$stepCatalogResult['ok']) {
        $errors[] = $stepCatalogResult['error'];
    }
    $stepCatalog = $stepCatalogResult['items'];

    if (app_request_method_is($request, 'POST')) {
        $bridgeErrors = app_project_proxy_bridge_errors_from_request();
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif (app_normalize_custom_proxy_key(app_post_param('custom_proxy_key')) !== $customProxyKey) {
            $errors[] = '更新対象の custom proxy key が route と一致しません。';
        } else {
            $action = trim(app_post_param('action'));
            if ($action === 'create-step') {
                $validation = app_validate_custom_proxy_step_form([
                    'db_access_source_name' => app_post_param('db_access_source_name'),
                    'db_access_function_name' => app_post_param('db_access_function_name'),
                    'is_list' => app_post_param('is_list', '0'),
                    'step_order' => app_post_param('step_order', '100'),
                    'notes' => app_post_param('notes'),
                    'source_of_truth' => 'manual',
                ]);
                $createInput = $validation['input'];
                $errors = array_merge($errors, $validation['errors']);

                $functionSummary = app_custom_proxy_find_generated_function(
                    $generatedCatalog,
                    $createInput['db_access_source_name'],
                    $createInput['db_access_function_name'],
                );
                if ($functionSummary === null) {
                    $errors[] = '指定した db access / function は current generated catalog に存在しません。';
                }

                if ($errors === []) {
                    $createResult = app_create_project_custom_proxy_step($app, array_merge(
                        [
                            'project_key' => $projectKey,
                            'custom_proxy_key' => $customProxyKey,
                        ],
                        $createInput,
                    ));

                    if ($createResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_custom_proxy_functions_path($projectKey, $customProxyKey) . '?created=1',
                        );
                        return;
                    }

                    $errors[] = $createResult['error'];
                }
            } elseif ($action === 'update-step') {
                $stepId = trim(app_post_param('step_id'));
                $currentStep = app_project_custom_proxy_functions_find_step($stepCatalog, $stepId);
                if ($currentStep === null) {
                    $errors[] = '更新対象の step が見つかりません。';
                }

                $validation = app_validate_custom_proxy_step_form([
                    'db_access_source_name' => app_post_param(
                        'db_access_source_name',
                        (string) ($currentStep['db_access_source_name'] ?? ''),
                    ),
                    'db_access_function_name' => app_post_param(
                        'db_access_function_name',
                        (string) ($currentStep['db_access_function_name'] ?? ''),
                    ),
                    'is_list' => app_post_param(
                        'is_list',
                        (string) ($currentStep['is_list'] ?? '0'),
                    ),
                    'step_order' => app_post_param(
                        'step_order',
                        (string) ($currentStep['step_order'] ?? '100'),
                    ),
                    'notes' => app_post_param(
                        'notes',
                        (string) ($currentStep['notes'] ?? ''),
                    ),
                    'source_of_truth' => app_post_param(
                        'source_of_truth',
                        (string) ($currentStep['source_of_truth'] ?? 'manual'),
                    ),
                ]);
                $rowOverrides[$stepId] = $validation['input'];
                $errors = array_merge($errors, $validation['errors']);

                $functionSummary = app_custom_proxy_find_generated_function(
                    $generatedCatalog,
                    $validation['input']['db_access_source_name'],
                    $validation['input']['db_access_function_name'],
                );
                if ($functionSummary === null) {
                    $errors[] = '指定した db access / function は current generated catalog に存在しません。';
                }

                if ($errors === []) {
                    $updateResult = app_update_project_custom_proxy_step($app, array_merge(
                        [
                            'project_key' => $projectKey,
                            'custom_proxy_key' => $customProxyKey,
                            'step_id' => $stepId,
                        ],
                        $validation['input'],
                    ));

                    if ($updateResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_custom_proxy_functions_path($projectKey, $customProxyKey) . '?updated=1',
                        );
                        return;
                    }

                    $errors[] = $updateResult['error'];
                }
            } elseif ($action === 'delete-step') {
                $stepId = trim(app_post_param('step_id'));
                if ($errors === []) {
                    $deleteResult = app_delete_project_custom_proxy_step($app, $projectKey, $customProxyKey, $stepId);
                    if ($deleteResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_custom_proxy_functions_path($projectKey, $customProxyKey) . '?deleted=1',
                        );
                        return;
                    }

                    $errors[] = $deleteResult['error'];
                }
            } elseif ($action === 'reorder-steps') {
                $requestedStepIds = $_POST['step_ids'] ?? app_post_param('step_ids_csv');
                $normalizedStepIds = app_project_custom_proxy_functions_normalize_step_ids($requestedStepIds);
                $currentStepIds = array_values(array_map(
                    static fn (array $step): string => (string) ($step['id'] ?? ''),
                    $stepCatalog,
                ));

                if ($normalizedStepIds === [] && $currentStepIds !== []) {
                    $errors[] = '並び替え対象の step id が空です。';
                } elseif (count($normalizedStepIds) !== count($currentStepIds)) {
                    $errors[] = '並び替え対象の step 集合が current catalog と一致しません。';
                } else {
                    $currentStepLookup = array_fill_keys($currentStepIds, true);
                    foreach ($normalizedStepIds as $normalizedStepId) {
                        if (!isset($currentStepLookup[$normalizedStepId])) {
                            $errors[] = '並び替え対象に存在しない step id が含まれています。';
                            break;
                        }
                    }
                }

                if ($errors === []) {
                    $reorderResult = app_reorder_project_custom_proxy_steps(
                        $app,
                        $projectKey,
                        $customProxyKey,
                        $normalizedStepIds,
                    );
                    if ($reorderResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_custom_proxy_functions_path($projectKey, $customProxyKey) . '?updated=1',
                        );
                        return;
                    }

                    $errors[] = $reorderResult['error'];
                }
            } elseif ($action === 'reset-step-order') {
                if ($errors === []) {
                    $resetResult = app_reset_project_custom_proxy_step_order(
                        $app,
                        $projectKey,
                        $customProxyKey,
                    );
                    if ($resetResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_custom_proxy_functions_path($projectKey, $customProxyKey) . '?updated=1',
                        );
                        return;
                    }

                    $errors[] = $resetResult['error'];
                }
            } elseif ($bridgeErrors === []) {
                $errors[] = '未対応の操作です。';
            }
        }
    }

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Custom Proxy Functions</title>
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
        .inline-form {
            margin-top: 0;
            padding: 0;
            border: 0;
            background: transparent;
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
            min-height: 5rem;
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
    <datalist id="custom-proxy-source-names">
        <?php foreach ($sourceNameOptions as $sourceNameOption): ?>
            <option value="<?php echo app_h($sourceNameOption); ?>"></option>
        <?php endforeach; ?>
    </datalist>
    <datalist id="custom-proxy-function-names">
        <?php foreach ($functionNameOptions as $functionNameOption): ?>
            <option value="<?php echo app_h($functionNameOption); ?>"></option>
        <?php endforeach; ?>
    </datalist>

    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="<?php echo app_h(app_project_custom_proxies_path($projectKey)); ?>">proxy/custom</a> / <a href="<?php echo app_h(app_project_custom_proxy_detail_path($projectKey, $customProxyKey)); ?>"><code><?php echo app_h($customProxyKey); ?></code></a> / functions</p>

    <h1><?php echo app_h($project['name']); ?> Custom Proxy Functions</h1>
    <p><code><?php echo app_h(app_custom_proxy_display_name($customProxy['basename'], $customProxy['name'])); ?></code> に含める step を定義する画面です。旧 <code>daCustomProxyFunc</code> のうち、意味のある可変点として <code>db access</code> / <code>function</code> / <code>is_list</code> / <code>step_order</code> だけを保持します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Summary</h2>
            <ul>
                <li>proxy key: <code><?php echo app_h($customProxyKey); ?></code></li>
                <li>display: <code><?php echo app_h(app_custom_proxy_display_name($customProxy['basename'], $customProxy['name'])); ?></code></li>
                <li>steps: <code><?php echo app_h((string) count($stepCatalog)); ?></code></li>
                <li>generated functions: <code><?php echo app_h((string) count($generatedFunctionCatalog)); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>現在の制約</h2>
            <ul>
                <li>現在は current generated catalog に存在する function だけを受けます。</li>
                <li><code>AddIndentCount</code> / <code>AddIndentType</code> は保持しません。</li>
                <li>ここで保存した step は target source output の custom proxy build plan に取り込まれます。</li>
            </ul>
        </section>
    </div>

    <?php if ($created): ?>
        <div class="success-card">step を追加しました。</div>
    <?php endif; ?>

    <?php if ($updated): ?>
        <div class="success-card">step を更新しました。</div>
    <?php endif; ?>

    <?php if ($deleted): ?>
        <div class="success-card">step を削除しました。</div>
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

    <form method="post" action="<?php echo app_h(app_project_custom_proxy_functions_path($projectKey, $customProxyKey)); ?>">
        <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
        <input type="hidden" name="action" value="create-step">
        <input type="hidden" name="custom_proxy_key" value="<?php echo app_h($customProxyKey); ?>">

        <h2>Create Step</h2>

        <label for="db_access_source_name">db access key</label>
        <input id="db_access_source_name" name="db_access_source_name" value="<?php echo app_h($createInput['db_access_source_name']); ?>" list="custom-proxy-source-names" placeholder="例: daCustomProxy">

        <label for="db_access_function_name">function name</label>
        <input id="db_access_function_name" name="db_access_function_name" value="<?php echo app_h($createInput['db_access_function_name']); ?>" list="custom-proxy-function-names" placeholder="例: GetProjectList">

        <label for="is_list">IsList</label>
        <select id="is_list" name="is_list">
            <option value="0"<?php echo $createInput['is_list'] === '0' ? ' selected' : ''; ?>>0 (Single)</option>
            <option value="1"<?php echo $createInput['is_list'] === '1' ? ' selected' : ''; ?>>1 (List)</option>
        </select>

        <label for="step_order">step order</label>
        <input id="step_order" name="step_order" value="<?php echo app_h($createInput['step_order']); ?>">

        <label for="notes">notes</label>
        <textarea id="notes" name="notes"><?php echo app_h($createInput['notes']); ?></textarea>

        <button type="submit">Create Step</button>
    </form>

    <h2>Current Steps</h2>
    <?php if ($stepCatalog === []): ?>
        <p class="muted">step はまだありません。</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>step</th>
                    <th>db access / function</th>
                    <th>list / status</th>
                    <th>notes</th>
                    <th>updated</th>
                    <th>actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stepCatalog as $step): ?>
                    <?php
                    $stepInput = $rowOverrides[$step['id']] ?? $step;
                    $functionSummary = app_custom_proxy_find_generated_function(
                        $generatedCatalog,
                        $stepInput['db_access_source_name'],
                        $stepInput['db_access_function_name'],
                    );
                    $updateFormId = 'update-step-' . $step['id'];
                    ?>
                    <tr>
                        <td><code><?php echo app_h($step['id']); ?></code></td>
                        <td>
                            <label>db access key
                                <input form="<?php echo app_h($updateFormId); ?>" name="db_access_source_name" value="<?php echo app_h($stepInput['db_access_source_name']); ?>" list="custom-proxy-source-names">
                            </label>

                            <label>function name
                                <input form="<?php echo app_h($updateFormId); ?>" name="db_access_function_name" value="<?php echo app_h($stepInput['db_access_function_name']); ?>" list="custom-proxy-function-names">
                            </label>
                        </td>
                        <td>
                            <label>IsList
                                <select form="<?php echo app_h($updateFormId); ?>" name="is_list">
                                    <option value="0"<?php echo $stepInput['is_list'] === '0' ? ' selected' : ''; ?>>0 (Single)</option>
                                    <option value="1"<?php echo $stepInput['is_list'] === '1' ? ' selected' : ''; ?>>1 (List)</option>
                                </select>
                            </label>

                            <label>step order
                                <input form="<?php echo app_h($updateFormId); ?>" name="step_order" value="<?php echo app_h($stepInput['step_order']); ?>">
                            </label>

                            <p class="muted">
                                <?php if ($functionSummary === null): ?>
                                    missing from generated catalog
                                <?php else: ?>
                                    line <?php echo app_h((string) $functionSummary['line']); ?>
                                <?php endif; ?>
                            </p>
                        </td>
                        <td>
                            <input type="hidden" form="<?php echo app_h($updateFormId); ?>" name="source_of_truth" value="<?php echo app_h($stepInput['source_of_truth']); ?>">
                            <label>notes
                                <textarea form="<?php echo app_h($updateFormId); ?>" name="notes"><?php echo app_h($stepInput['notes']); ?></textarea>
                            </label>
                        </td>
                        <td><code><?php echo app_h($step['updated_at']); ?></code></td>
                        <td>
                            <form id="<?php echo app_h($updateFormId); ?>" class="inline-form" method="post" action="<?php echo app_h(app_project_custom_proxy_functions_path($projectKey, $customProxyKey)); ?>">
                                <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
                                <input type="hidden" name="action" value="update-step">
                                <input type="hidden" name="custom_proxy_key" value="<?php echo app_h($customProxyKey); ?>">
                                <input type="hidden" name="step_id" value="<?php echo app_h($step['id']); ?>">
                                <button type="submit">save</button>
                            </form>

                            <form class="inline-form" method="post" action="<?php echo app_h(app_project_custom_proxy_functions_path($projectKey, $customProxyKey)); ?>">
                                <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
                                <input type="hidden" name="action" value="delete-step">
                                <input type="hidden" name="custom_proxy_key" value="<?php echo app_h($customProxyKey); ?>">
                                <input type="hidden" name="step_id" value="<?php echo app_h($step['id']); ?>">
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
