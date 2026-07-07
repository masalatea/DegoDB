<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/project_shared_contract_route_common.php';
require_once __DIR__ . '/project_source_output_route_common.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/shared_contract_metadata_repository_pdo.php';

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{name:string}
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_project_shared_contracts_page(array $app, array $request): void
{
    $bootstrap = app_project_shared_contract_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $errors = [];
    $updatedContractKey = trim(app_query_param('updated'));

    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $contractKey = trim(app_post_param('contract_key'));
            $usageIntent = trim(app_post_param('usage_intent'));
            $viewVariantPreference = trim(app_post_param('view_variant_preference'));
            $usageIntentValidation = app_project_shared_contract_validate_usage_intent($usageIntent);
            $viewVariantValidation = app_project_shared_contract_validate_view_variant_preference($viewVariantPreference);
            $itemResult = app_pdo_fetch_shared_contract_metadata_item($app, $projectKey, $contractKey);

            if ($contractKey === '') {
                $errors[] = 'contract key が空です。';
            } elseif (!$itemResult['ok']) {
                $errors[] = $itemResult['error'];
            } elseif ($itemResult['item'] === null) {
                $errors[] = 'shared contract metadata が見つかりません: ' . $contractKey;
            } elseif (!$usageIntentValidation['ok']) {
                $errors[] = $usageIntentValidation['error'];
            } elseif (!$viewVariantValidation['ok']) {
                $errors[] = $viewVariantValidation['error'];
            } else {
                $item = $itemResult['item'];
                $updateResult = app_pdo_upsert_shared_contract_metadata($app, $projectKey, [
                    'contract_key' => $contractKey,
                    'data_class_physical_name' => (string) ($item['data_class_physical_name'] ?? $contractKey),
                    'status' => (string) ($item['status'] ?? 'active'),
                    'usage_intent' => $usageIntent,
                    'view_variant_preference' => $viewVariantPreference,
                    'sync_role' => (string) ($item['sync_role'] ?? ''),
                    'no_code_role' => (string) ($item['no_code_role'] ?? ''),
                    'app_persistence_role' => (string) ($item['app_persistence_role'] ?? ''),
                    'notes' => (string) ($item['notes'] ?? ''),
                    'source_of_truth' => (string) ($item['source_of_truth'] ?? 'manual'),
                ]);

                if (!$updateResult['ok']) {
                    $errors[] = $updateResult['error'];
                } else {
                    app_send_redirect_response(
                        $request,
                        app_project_shared_contracts_path($projectKey) . '?updated=' . rawurlencode($contractKey),
                    );
                    return;
                }
            }
        }
    }

    $snapshot = app_pdo_fetch_shared_contract_metadata_snapshot($app, $projectKey);
    if (!$snapshot['ok']) {
        $errors[] = $snapshot['error'];
    }

    $items = $snapshot['ok'] ? $snapshot['items'] : [];
    $usageIntentOptions = app_project_shared_contract_usage_intent_options();
    $viewVariantOptions = app_project_shared_contract_view_variant_preference_options();

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Shared Contracts</title>
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
        select {
            min-width: 13rem;
        }
        button {
            cursor: pointer;
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
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .muted {
            color: #475569;
        }
        .inline-form {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / shared-contracts</p>

    <h1><?php echo app_h($project['name']); ?> Shared Contracts</h1>
    <p><code>project_shared_contracts</code> の no-code 用 metadata を管理する最小画面です。ここでは interface usage intent と view variant preference を編集し、既存の <code>no_code_role</code>、<code>sync_role</code>、<code>app_persistence_role</code> は派生 fallback の材料として残します。</p>

    <?php if ($updatedContractKey !== ''): ?>
        <div class="success">usage intent を保存しました: <code><?php echo app_h($updatedContractKey); ?></code></div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="error">
            <p>保存または読み込みに失敗しました。</p>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Project</h2>
            <ul>
                <li>project key: <code><?php echo app_h($project['project_key']); ?></code></li>
                <li>status: <code><?php echo app_h($project['lifecycle_status']); ?></code></li>
                <li>contracts: <code><?php echo app_h((string) count($items)); ?></code></li>
            </ul>
        </section>
        <section class="note-card">
            <h2>No-Code Boundary</h2>
            <p class="muted">空の usage intent は explicit 指定なしを意味します。その場合、no-code screen definition は既存 role から用途を派生します。</p>
        </section>
    </div>

    <?php if ($items === []): ?>
        <p class="muted">shared contract metadata はまだありません。先に Data Class / table metadata sync と shared contract output generation を確認してください。</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>contract</th>
                <th>no-code preference</th>
                <th>existing roles</th>
                <th>fields</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <?php $currentUsageIntent = (string) ($item['usage_intent'] ?? ''); ?>
                <?php $currentViewVariantPreference = (string) ($item['view_variant_preference'] ?? ''); ?>
                <tr>
                    <td>
                        <code><?php echo app_h((string) ($item['contract_key'] ?? '')); ?></code><br>
                        <span class="muted"><?php echo app_h((string) ($item['data_class_physical_name'] ?? '')); ?></span>
                    </td>
                    <td>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="_csrf" value="<?php echo app_h(app_csrf_token()); ?>">
                            <input type="hidden" name="contract_key" value="<?php echo app_h((string) ($item['contract_key'] ?? '')); ?>">
                            <select name="usage_intent" aria-label="usage intent for <?php echo app_h((string) ($item['contract_key'] ?? '')); ?>">
                                <?php foreach ($usageIntentOptions as $value => $label): ?>
                                    <option value="<?php echo app_h($value); ?>"<?php echo $currentUsageIntent === $value ? ' selected' : ''; ?>>
                                        <?php echo app_h($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <select name="view_variant_preference" aria-label="view variant preference for <?php echo app_h((string) ($item['contract_key'] ?? '')); ?>">
                                <?php foreach ($viewVariantOptions as $value => $label): ?>
                                    <option value="<?php echo app_h($value); ?>"<?php echo $currentViewVariantPreference === $value ? ' selected' : ''; ?>>
                                        <?php echo app_h($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Save</button>
                        </form>
                    </td>
                    <td>
                        <ul>
                            <li>no-code: <code><?php echo app_h((string) ($item['no_code_role'] ?? '')); ?></code></li>
                            <li>sync: <code><?php echo app_h((string) ($item['sync_role'] ?? '')); ?></code></li>
                            <li>app persistence: <code><?php echo app_h((string) ($item['app_persistence_role'] ?? '')); ?></code></li>
                        </ul>
                    </td>
                    <td><code><?php echo app_h((string) count($item['fields'] ?? [])); ?></code></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="<?php echo app_h(app_project_source_outputs_path($projectKey)); ?>">Back to Source Outputs</a></p>
</main>
</body>
</html>
    <?php
}

/**
 * @return array<string,string>
 */
function app_project_shared_contract_usage_intent_options(): array
{
    return [
        '' => 'Derived from existing roles',
        'screen' => 'Screen',
        'external_integration' => 'External integration',
        'sync' => 'Sync',
        'reporting' => 'Reporting',
        'workflow' => 'Workflow',
        'internal' => 'Internal',
    ];
}

/**
 * @return array{ok:bool,error:string}
 */
function app_project_shared_contract_validate_usage_intent(string $usageIntent): array
{
    if (array_key_exists($usageIntent, app_project_shared_contract_usage_intent_options())) {
        return [
            'ok' => true,
            'error' => '',
        ];
    }

    return [
        'ok' => false,
        'error' => 'usage intent が不正です: ' . $usageIntent,
    ];
}

/**
 * @return array<string,string>
 */
function app_project_shared_contract_view_variant_preference_options(): array
{
    return [
        '' => 'Auto by screen type',
        'standard_table' => 'Standard table',
        'detail_record' => 'Detail record',
        'edit_form' => 'Edit form',
        'review_list' => 'Review list',
    ];
}

/**
 * @return array{ok:bool,error:string}
 */
function app_project_shared_contract_validate_view_variant_preference(string $viewVariantPreference): array
{
    if (array_key_exists($viewVariantPreference, app_project_shared_contract_view_variant_preference_options())) {
        return [
            'ok' => true,
            'error' => '',
        ];
    }

    return [
        'ok' => false,
        'error' => 'view variant preference が不正です: ' . $viewVariantPreference,
    ];
}
