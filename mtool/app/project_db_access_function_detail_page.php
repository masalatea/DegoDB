<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db_access_endpoint_policy.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/project_db_access_metadata_helper.php';
require_once __DIR__ . '/project_db_access_route_common.php';
require_once __DIR__ . '/project_proxy_route_common.php';
require_once __DIR__ . '/project_source_output_route_common.php';
require_once __DIR__ . '/source_output_repository.php';

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
function app_render_project_db_access_function_detail_page(array $app, array $request): void
{
    $bootstrap = app_project_db_access_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $catalog = $bootstrap['generated_catalog'];
    $dbAccessKey = trim(app_route_param($request, 'db_access_key'));
    $functionKey = trim(app_route_param($request, 'function_key'));
    if ($dbAccessKey === '' || $functionKey === '') {
        app_render_bad_request_page($app, $request, 'db access key と function key が必要です。');
        return;
    }

    $entity = app_generated_catalog_find_entity($catalog, $dbAccessKey);
    if ($entity === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $methodCatalog = app_generated_file_method_catalog($entity['dbaccess_path']);
    $method = app_generated_file_find_method($methodCatalog, $functionKey);
    if ($method === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $functionProfile = app_project_db_access_guess_function_profile($method['name']);
    $methodExcerpt = app_generated_file_method_excerpt($entity['dbaccess_path'], $method['name'], 40);
    $blobRuntimeContractSupported = app_generated_file_method_has_blob_streaming_contract(
        $entity['dbaccess_path'],
        $method['name'],
    );
    $methodCount = count($methodCatalog);
    $legacyDafuncSchema = app_project_db_access_legacy_metadata_schema($app, 'dafunc');
    $canonicalResult = app_fetch_db_access_function_metadata($app, $projectKey, $entity['source_name'], $method['name']);
    $canonicalError = $canonicalResult['ok'] ? '' : $canonicalResult['error'];
    $canonicalItem = $canonicalResult['ok'] ? $canonicalResult['item'] : null;
    $sourceOutputCatalogResult = app_fetch_project_source_output_catalog($app, $projectKey);
    $sourceOutputCatalog = $sourceOutputCatalogResult['ok'] ? $sourceOutputCatalogResult['items'] : [];
    $singleProxyTargetSourceOutputs = array_values(array_filter(
        $sourceOutputCatalog,
        static fn ($sourceOutput): bool => is_array($sourceOutput)
            && app_source_output_supports_single_function_proxy_targets($sourceOutput),
    ));

    $input = $canonicalItem !== null
        ? app_project_db_access_function_form_from_item($canonicalItem)
        : app_project_db_access_function_form_from_preview($entity, $method, $functionProfile);
    $effectiveActionType = $canonicalItem !== null && $canonicalItem['action_type'] !== ''
        ? $canonicalItem['action_type']
        : $functionProfile['legacy_action_type'];

    $errors = [];
    $updated = app_query_param('updated') === '1';
    if (!$sourceOutputCatalogResult['ok']) {
        $errors[] = $sourceOutputCatalogResult['error'];
    }

    $targetKeysResult = app_fetch_db_access_function_source_output_target_keys(
        $app,
        $projectKey,
        $entity['source_name'],
        $method['name'],
    );
    if (!$targetKeysResult['ok']) {
        $errors[] = $targetKeysResult['error'];
    }
    $selectedTargetKeys = $targetKeysResult['items'];

    if (app_request_method_is($request, 'POST')) {
        $bridgeMode = app_post_param('bridge_mode');
        $useLegacySingleProxyAuthBridge = app_project_db_access_function_detail_is_legacy_single_proxy_auth_bridge_mode(
            $bridgeMode,
        );
        $errors = array_merge($errors, app_project_proxy_bridge_errors_from_request());
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $submittedSourceName = trim(app_post_param('source_name', $entity['source_name']));
            if (strcasecmp($submittedSourceName, $entity['source_name']) !== 0) {
                $errors[] = '更新対象の db access key が route と一致しません。';
            }
            $submittedFunctionName = trim(app_post_param('function_name', $method['name']));
            if (strcasecmp($submittedFunctionName, $method['name']) !== 0) {
                $errors[] = '更新対象の function name が route と一致しません。';
            }

            $validationInput = $useLegacySingleProxyAuthBridge
                ? app_project_db_access_function_detail_build_legacy_single_proxy_auth_bridge_input(
                    $input,
                    $entity,
                    $method,
                )
                : [
                    'source_name' => $entity['source_name'],
                    'function_name' => $method['name'],
                    'function_list_order' => app_post_param('function_list_order', $input['function_list_order']),
                    'function_suffix' => app_post_param('function_suffix'),
                    'action_type' => app_post_param('action_type'),
                    'data_class_base_name' => app_post_param('data_class_base_name'),
                    'target_table_name' => app_post_param('target_table_name'),
                    'parameter_type' => app_post_param('parameter_type'),
                    'select_by_distinct' => app_post_param('select_by_distinct', '0'),
                    'sort_order_columns' => app_post_param('sort_order_columns'),
                    'memo' => app_post_param('memo'),
                    'limit_parameter_type' => app_post_param('limit_parameter_type'),
                    'limit_fixed_parameter' => app_post_param('limit_fixed_parameter'),
                    'or_group_type' => app_post_param('or_group_type'),
                    'single_proxy_auth_type' => app_post_param('single_proxy_auth_type'),
                    'single_proxy_single_get_function_name' => app_post_param('single_proxy_single_get_function_name'),
                    'is_blob_target' => app_post_param('is_blob_target', '0'),
                    'detected_signature' => app_post_param('detected_signature'),
                    'detected_line' => app_post_param('detected_line', '0'),
                    'source_of_truth' => 'manual',
                ];
            $validation = app_validate_db_access_function_form($validationInput);
            $input = $validation['input'];
            $errors = array_merge($errors, $validation['errors']);

            if ($input['is_blob_target'] === '1' && !$blobRuntimeContractSupported) {
                $errors[] = 'IsBlobTarget=1 は legacy method source に prepare()/bind_param("b")/send_long_data() がある function でのみ保存できます。';
            }

            if (!$useLegacySingleProxyAuthBridge) {
                $requestedTargetKeys = $_POST['source_output_keys'] ?? [];
                if (!is_array($requestedTargetKeys)) {
                    $requestedTargetKeys = [];
                }
                $selectedTargetKeys = app_project_db_access_normalize_target_source_output_keys(
                    $requestedTargetKeys,
                    $singleProxyTargetSourceOutputs,
                );
            }

            if ($errors === []) {
                $updateResult = app_upsert_db_access_function_metadata($app, [
                    'project_key' => $projectKey,
                    'source_name' => $input['source_name'],
                    'function_name' => $input['function_name'],
                    'function_list_order' => $input['function_list_order'],
                    'function_suffix' => $input['function_suffix'],
                    'action_type' => $input['action_type'],
                    'data_class_base_name' => $input['data_class_base_name'],
                    'target_table_name' => $input['target_table_name'],
                    'parameter_type' => $input['parameter_type'],
                    'select_by_distinct' => $input['select_by_distinct'],
                    'sort_order_columns' => $input['sort_order_columns'],
                    'memo' => $input['memo'],
                    'limit_parameter_type' => $input['limit_parameter_type'],
                    'limit_fixed_parameter' => $input['limit_fixed_parameter'],
                    'or_group_type' => $input['or_group_type'],
                    'single_proxy_auth_type' => $input['single_proxy_auth_type'],
                    'single_proxy_single_get_function_name' => $input['single_proxy_single_get_function_name'],
                    'is_blob_target' => $input['is_blob_target'],
                    'detected_signature' => $input['detected_signature'],
                    'detected_line' => $input['detected_line'],
                    'source_of_truth' => $input['source_of_truth'],
                    'last_detected_dbaccess_file' => $entity['dbaccess_file'],
                    'last_detected_data_file' => $entity['data_file'],
                ]);

                if ($updateResult['ok']) {
                    $replaceTargetsResult = app_replace_db_access_function_source_output_target_keys(
                        $app,
                        $projectKey,
                        $entity['source_name'],
                        $method['name'],
                        $selectedTargetKeys,
                    );

                    if ($replaceTargetsResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            '/projects/' . rawurlencode($projectKey)
                            . '/db-access/' . rawurlencode($entity['source_name'])
                            . '/functions/' . rawurlencode($method['name'])
                            . '?updated=1',
                        );
                        return;
                    }

                    $errors[] = $replaceTargetsResult['error'];
                }

                if (!$updateResult['ok']) {
                    $errors[] = $updateResult['error'];
                }
            }
        }
    }

    $authPolicy = app_resolve_db_access_single_proxy_auth_policy(
        $input['single_proxy_auth_type'],
        $input['single_proxy_single_get_function_name'],
    );
    $fieldPreviewRows = [
        [
            'field' => 'function_suffix',
            'preview' => $input['function_suffix'] !== '' ? $input['function_suffix'] : '(blank)',
            'note' => 'legacy `dafunc.name` に相当する suffix。prefix は ActionType から導出する設計だった。',
        ],
        [
            'field' => 'action_type',
            'preview' => $input['action_type'] !== '' ? $input['action_type'] : '(blank)',
            'note' => 'legacy ActionType。現在は `SELECTSINGLE` / `SELECTLIST` / `INSERT` / `UPDATE` / `DELETE` を採用する。',
        ],
    ];

    if (in_array($input['action_type'], ['SELECTSINGLE', 'SELECTLIST'], true)) {
        $fieldPreviewRows[] = [
            'field' => 'data_class_base_name',
            'preview' => $input['data_class_base_name'] !== '' ? $input['data_class_base_name'] : '(blank)',
            'note' => 'select 系が返す Data Class の基準名。',
        ];
        $fieldPreviewRows[] = [
            'field' => 'select_by_distinct / limit_parameter_type / limit_fixed_parameter',
            'preview' => $input['select_by_distinct'] . ' / ' . ($input['limit_parameter_type'] !== '' ? $input['limit_parameter_type'] : '(blank)') . ' / ' . ($input['limit_fixed_parameter'] !== '' ? $input['limit_fixed_parameter'] : '(blank)'),
            'note' => 'select 系の振る舞いを制御する legacy 項目。',
        ];
    }

    if (in_array($input['action_type'], ['INSERT', 'UPDATE', 'DELETE'], true)) {
        $fieldPreviewRows[] = [
            'field' => 'target_table_name / parameter_type',
            'preview' => ($input['target_table_name'] !== '' ? $input['target_table_name'] : '(blank)') . ' / ' . ($input['parameter_type'] !== '' ? $input['parameter_type'] : '(blank)'),
            'note' => '更新系 action の target table と parameter type。',
        ];
    }

    $fieldPreviewRows[] = [
        'field' => 'function_list_order / detected_line',
        'preview' => $input['function_list_order'] . ' / ' . $input['detected_line'],
        'note' => 'function 一覧の表示順と source 上の検出行。通常は change-order 画面で前者を編集する。',
    ];
    $fieldPreviewRows[] = [
        'field' => 'memo / sort_order_columns / or_group_type',
        'preview' => ($input['memo'] !== '' ? $input['memo'] : '(blank)') . ' / ' . ($input['sort_order_columns'] !== '' ? $input['sort_order_columns'] : '(blank)') . ' / ' . ($input['or_group_type'] !== '' ? $input['or_group_type'] : '(blank)'),
        'note' => 'designer 実装後も保持する予定の補助項目。',
    ];
    $fieldPreviewRows[] = [
        'field' => 'auth policy / single_proxy_single_get_function_name / is_blob_target',
        'preview' => $authPolicy['strategy_caption']
            . ' / '
            . ($authPolicy['single_get_function_name'] !== '' ? $authPolicy['single_get_function_name'] : '(blank)')
            . ' / '
            . $input['is_blob_target'],
        'note' => '単体 function proxy / endpoint preview 側の認証ポリシー。multi-step custom proxy は project_custom_proxies 側で別管理する。',
    ];
    $fieldPreviewRows[] = [
        'field' => 'single proxy target source outputs',
        'preview' => $selectedTargetKeys === [] ? '(none)' : implode(', ', $selectedTargetKeys),
        'note' => 'legacy `dafuncSimpleProxySourceOutputTarget` 相当。function 単位で target source output key を保持する。',
    ];

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Function Detail</title>
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
        .checkbox-list {
            display: grid;
            gap: 0.75rem;
            margin-top: 0.75rem;
        }
        .checkbox-option {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            margin-top: 0;
            padding: 0.75rem 0.9rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            font-weight: 400;
        }
        .checkbox-option input {
            width: auto;
            margin: 0.15rem 0 0;
            padding: 0;
            flex: 0 0 auto;
        }
        textarea {
            min-height: 8rem;
            resize: vertical;
        }
        button {
            margin-top: 1.25rem;
            padding: 0.75rem 1rem;
            border: 0;
            border-radius: 8px;
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            cursor: pointer;
        }
        .error {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 8px;
        }
        .success {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: #dcfce7;
            color: #166534;
            border-radius: 8px;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">functions</a> / <code><?php echo app_h($method['name']); ?></code></p>

    <h1><?php echo app_h($project['name']); ?> Function Detail</h1>
    <p><code>dafunc</code> metadata を編集する画面です。generated method 定義を初期値にしつつ、<code>db-config</code> 側の canonical <code>dafunc</code> metadata を持ち始めます。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Function</h2>
            <ul>
                <li>db access: <code><?php echo app_h($entity['source_name']); ?></code></li>
                <li>function name: <code><?php echo app_h($method['name']); ?></code></li>
                <li>line: <code><?php echo app_h((string) $method['line']); ?></code> - <code><?php echo app_h((string) $method['end_line']); ?></code></li>
                <li>signature: <code><?php echo app_h($method['signature']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Canonical Status</h2>
            <?php if ($canonicalError !== ''): ?>
                <p class="muted">未接続</p>
                <p class="muted"><?php echo app_h($canonicalError); ?></p>
            <?php elseif ($canonicalItem === null): ?>
                <p class="muted">未保存</p>
                <p class="muted">まだ `project_db_access_functions` に row がありません。</p>
            <?php else: ?>
                <ul>
                    <li>action_type: <code><?php echo app_h($canonicalItem['action_type']); ?></code></li>
                    <li>function_suffix: <code><?php echo app_h($canonicalItem['function_suffix']); ?></code></li>
                    <li>source_of_truth: <code><?php echo app_h($canonicalItem['source_of_truth']); ?></code></li>
                    <li>updated: <code><?php echo app_h($canonicalItem['updated_at']); ?></code></li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="summary-card">
            <h2>Heuristic</h2>
            <ul>
                <li>action guess: <code><?php echo app_h($functionProfile['action']); ?></code></li>
                <li>HTTP guess: <code><?php echo app_h($functionProfile['http_method']); ?></code></li>
                <li>legacy ActionType candidate: <code><?php echo app_h($functionProfile['legacy_action_type']); ?></code></li>
                <li>function suffix candidate: <code><?php echo app_h($functionProfile['function_suffix_candidate']); ?></code></li>
                <li>function catalog size: <code><?php echo app_h((string) $methodCount); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Auth Policy (Single Function)</h2>
            <ul>
                <li>scope: <code>single-function proxy</code></li>
                <li>raw auth type: <code><?php echo app_h($authPolicy['raw_auth_type'] !== '' ? $authPolicy['raw_auth_type'] : '(blank)'); ?></code> / <?php echo app_h($authPolicy['raw_auth_type_caption']); ?></li>
                <li>resolved auth type: <code><?php echo app_h($authPolicy['resolved_auth_type']); ?></code> / <?php echo app_h($authPolicy['resolved_auth_type_caption']); ?></li>
                <li>strategy: <code><?php echo app_h($authPolicy['strategy_key']); ?></code> / <?php echo app_h($authPolicy['strategy_caption']); ?></li>
                <li>single get function: <code><?php echo app_h($authPolicy['single_get_function_name'] !== '' ? $authPolicy['single_get_function_name'] : '(blank)'); ?></code></li>
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

        <section class="summary-card">
            <h2>Target Source Outputs</h2>
            <ul>
                <li>assigned: <code><?php echo app_h((string) count($selectedTargetKeys)); ?></code></li>
                <li>available: <code><?php echo app_h((string) count($singleProxyTargetSourceOutputs)); ?></code></li>
                <li>scope: <code>single-function proxy target assignment</code></li>
            </ul>
            <?php if ($selectedTargetKeys === []): ?>
                <p class="muted">この function に紐づく source output target はまだありません。</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($selectedTargetKeys as $targetKey): ?>
                        <li><a href="<?php echo app_h(app_project_source_output_detail_path($projectKey, $targetKey)); ?>"><code><?php echo app_h($targetKey); ?></code></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <p class="muted">legacy <code>dafuncSimpleProxySourceOutputTarget</code> 相当の保存先です。single-proxy 系 strategy はこの target assignment を読んで direct per-function artifact を組み立てます。</p>
            <?php if ($singleProxyTargetSourceOutputs === []): ?>
                <p class="muted">current source output は <code>runtime</code> と <code>custom-proxy</code> だけなので、single-function proxy 用に直接選べる target はまだありません。legacy <code>proxy_paypal</code> / <code>proxy_uploader</code> row は dedicated source output を作るまで backfill しません。</p>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>現在の制約</h2>
            <ul>
                <li>where / having / target field designer はまだ未移植</li>
                <li>multi-step custom proxy は `project_custom_proxies` / `project_custom_proxy_steps` 側へ移してあり、ここの `SingleProxy_*` は単体 function proxy 用です</li>
                <li>current `DBIMPORT-PROXY-*` は custom proxy target 専用として扱い、single-function target には出しません</li>
                <li>single-function proxy target metadata は `single-proxy-*` artifact strategy が読む。`custom-proxy-*` とは別経路で扱う</li>
                <li>schema 未適用の既存 volume では保存できない</li>
            </ul>
            <p class="muted">それでも action type や suffix を先に canonical 化しておくことで、次段の designer 実装を進めやすくします。</p>
        </section>
    </div>

    <?php if ($updated): ?>
        <div class="success">function <code><?php echo app_h($method['name']); ?></code> の canonical metadata を保存しました。</div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($canonicalError === ''): ?>
        <form method="post" action="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="source_name" value="<?php echo app_h($entity['source_name']); ?>">
            <input type="hidden" name="function_name" value="<?php echo app_h($method['name']); ?>">
            <input type="hidden" name="function_list_order" value="<?php echo app_h($input['function_list_order']); ?>">
            <input type="hidden" name="detected_signature" value="<?php echo app_h($method['signature']); ?>">
            <input type="hidden" name="detected_line" value="<?php echo app_h((string) $method['line']); ?>">

            <label for="function_name_readonly">function name</label>
            <input id="function_name_readonly" value="<?php echo app_h($method['name']); ?>" readonly>

            <label for="function_suffix">function suffix</label>
            <input id="function_suffix" name="function_suffix" value="<?php echo app_h($input['function_suffix']); ?>" placeholder="例: Project, ProjectList">

            <label for="action_type">ActionType</label>
            <select id="action_type" name="action_type">
                <?php foreach (app_allowed_db_access_action_types() as $actionType): ?>
                    <?php $caption = $actionType === '' ? '(blank)' : $actionType; ?>
                    <option value="<?php echo app_h($actionType); ?>"<?php echo $input['action_type'] === $actionType ? ' selected' : ''; ?>><?php echo app_h($caption); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="data_class_base_name">DataClassBaseNameForSelectAction</label>
            <input id="data_class_base_name" name="data_class_base_name" value="<?php echo app_h($input['data_class_base_name']); ?>">

            <label for="target_table_name">InsertUpdateDeleteTargetTable</label>
            <input id="target_table_name" name="target_table_name" value="<?php echo app_h($input['target_table_name']); ?>">

            <label for="parameter_type">InsertUpdateDeleteParamType</label>
            <input id="parameter_type" name="parameter_type" value="<?php echo app_h($input['parameter_type']); ?>" placeholder="例: VAL, CLASSOBJECT">

            <label for="select_by_distinct">SelectByDistinct</label>
            <select id="select_by_distinct" name="select_by_distinct">
                <option value="0"<?php echo $input['select_by_distinct'] === '0' ? ' selected' : ''; ?>>0 (No)</option>
                <option value="1"<?php echo $input['select_by_distinct'] === '1' ? ' selected' : ''; ?>>1 (Yes)</option>
            </select>

            <label for="sort_order_columns">SortOrderColumns</label>
            <input id="sort_order_columns" name="sort_order_columns" value="<?php echo app_h($input['sort_order_columns']); ?>" placeholder="例: updated_at desc">

            <label for="limit_parameter_type">limitParameterType</label>
            <input id="limit_parameter_type" name="limit_parameter_type" value="<?php echo app_h($input['limit_parameter_type']); ?>">

            <label for="limit_fixed_parameter">limitFixedParameter</label>
            <input id="limit_fixed_parameter" name="limit_fixed_parameter" value="<?php echo app_h($input['limit_fixed_parameter']); ?>">

            <label for="or_group_type">ORGroupType</label>
            <input id="or_group_type" name="or_group_type" value="<?php echo app_h($input['or_group_type']); ?>">

            <label for="single_proxy_auth_type">SingleProxy_AuthType</label>
            <select id="single_proxy_auth_type" name="single_proxy_auth_type">
                <?php if (
                    $input['single_proxy_auth_type'] !== ''
                    && !in_array($input['single_proxy_auth_type'], app_allowed_db_access_single_proxy_auth_types(), true)
                ): ?>
                    <option value="<?php echo app_h($input['single_proxy_auth_type']); ?>" selected><?php echo app_h('[invalid] ' . $input['single_proxy_auth_type']); ?></option>
                <?php endif; ?>
                <?php foreach (app_allowed_db_access_single_proxy_auth_types() as $authType): ?>
                    <option value="<?php echo app_h($authType); ?>"<?php echo $input['single_proxy_auth_type'] === $authType ? ' selected' : ''; ?>><?php echo app_h(app_db_access_single_proxy_auth_type_caption($authType)); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="muted">この設定は単体 function proxy / endpoint preview 用です。空欄は legacy default として ProjectToken へ解決します。</p>

            <label for="single_proxy_single_get_function_name">SingleProxy_SingleGetFuncPID 相当名</label>
            <input id="single_proxy_single_get_function_name" name="single_proxy_single_get_function_name" value="<?php echo app_h($input['single_proxy_single_get_function_name']); ?>" placeholder="例: GetProject">
            <p class="muted">`GetFunc` と `ProjectTokenOrGetFunc` では必須です。</p>

            <label>Single Function Proxy Target Source Outputs</label>
            <?php if ($singleProxyTargetSourceOutputs === []): ?>
                <p class="muted">選択可能な source output はまだありません。</p>
            <?php else: ?>
                <div class="checkbox-list">
                    <?php foreach ($singleProxyTargetSourceOutputs as $sourceOutput): ?>
                        <?php $sourceOutputKey = (string) ($sourceOutput['source_output_key'] ?? ''); ?>
                        <label class="checkbox-option">
                            <input type="checkbox" name="source_output_keys[]" value="<?php echo app_h($sourceOutputKey); ?>"<?php echo in_array($sourceOutputKey, $selectedTargetKeys, true) ? ' checked' : ''; ?>>
                            <span>
                                <strong><code><?php echo app_h($sourceOutputKey); ?></code></strong>
                                <?php if (($sourceOutput['name'] ?? '') !== ''): ?>
                                    <br><span class="muted"><?php echo app_h((string) $sourceOutput['name']); ?></span>
                                <?php endif; ?>
                                <br><span class="muted"><?php echo app_h(app_source_output_artifact_strategy_caption((string) ($sourceOutput['artifact_strategy'] ?? ''))); ?></span>
                                <br><span class="muted"><?php echo app_h(app_source_output_target_binding_scope_caption(app_source_output_target_binding_scope($sourceOutput))); ?></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <p class="muted">旧 <code>dafuncSimpleProxySourceOutputTarget</code> 相当の target assignment です。function 単位でどの source output に出すかだけをここで保持します。</p>

            <label for="is_blob_target">IsBlobTarget</label>
            <select id="is_blob_target" name="is_blob_target">
                <option value="0"<?php echo $input['is_blob_target'] === '0' ? ' selected' : ''; ?>>0 (No)</option>
                <option value="1"<?php echo $input['is_blob_target'] === '1' ? ' selected' : ''; ?><?php echo !$blobRuntimeContractSupported && $input['is_blob_target'] !== '1' ? ' disabled' : ''; ?>>1 (Yes<?php echo !$blobRuntimeContractSupported ? ' - legacy blob contract required' : ''; ?>)</option>
            </select>
            <p class="muted"><code>IsBlobTarget=1</code> は <code>INSERT</code> / <code>UPDATE</code> のみ対象です。current runtime generator は blob target を legacy delegate として扱うため、legacy method source に <code>prepare()</code> / <code>bind_param("b")</code> / <code>send_long_data()</code> がある function でのみ有効です。</p>

            <label for="memo">memo</label>
            <textarea id="memo" name="memo" placeholder="補足メモや未移植要素"><?php echo app_h($input['memo']); ?></textarea>

            <button type="submit">保存</button>
        </form>
    <?php endif; ?>

    <section class="summary-grid">
        <section class="summary-card">
            <h2>Legacy `dafunc` Schema</h2>
            <ul>
                <li>schema file: <code><?php echo app_h($legacyDafuncSchema['data_file']); ?></code></li>
                <li>field count: <code><?php echo app_h((string) count($legacyDafuncSchema['field_names'])); ?></code></li>
                <li>repository method count: <code><?php echo app_h((string) count($legacyDafuncSchema['dbaccess_methods'])); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Blob Runtime Contract</h2>
            <ul>
                <li>status: <code><?php echo app_h($blobRuntimeContractSupported ? 'legacy-send-long-data-detected' : 'not-detected'); ?></code></li>
                <li>is blob target: <code><?php echo app_h($input['is_blob_target']); ?></code></li>
            </ul>
            <p class="muted">current runtime generator は blob target を SQL regenerate せず legacy delegate に落とします。<code>prepare()</code> / <code>bind_param("b")</code> / <code>send_long_data()</code> を持つ legacy method が確認できる function だけ <code>IsBlobTarget=1</code> を維持できます。</p>
        </section>

        <section class="note-card">
            <h2>次の導線</h2>
            <ul>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/change-order">function change-order</a></li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/move">function move</a></li>
                <?php if (in_array($effectiveActionType, ['SELECTSINGLE', 'SELECTLIST'], true)): ?>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-where">select where designer</a></li>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-where/input-aid">select where input-aid</a></li>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-where/change-order">select where change-order</a></li>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-target-fields">select target fields designer</a></li>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-having">select having designer</a></li>
                <?php elseif ($effectiveActionType === 'INSERT'): ?>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/insert-target-fields">insert target fields designer</a></li>
                <?php elseif ($effectiveActionType === 'UPDATE'): ?>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/update-target-fields">update target fields designer</a></li>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/update-delete-where">update/delete where designer</a></li>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/update-delete-where/input-aid">update/delete where input-aid</a></li>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/update-delete-where/change-order">update/delete where change-order</a></li>
                <?php elseif ($effectiveActionType === 'DELETE'): ?>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/update-delete-where">update/delete where designer</a></li>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/update-delete-where/input-aid">update/delete where input-aid</a></li>
                    <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/update-delete-where/change-order">update/delete where change-order</a></li>
                <?php endif; ?>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/source">source preview</a></li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/endpoint">endpoint preview</a></li>
            </ul>
            <p class="muted">function-level change-order に加え、select where / select target fields / select having / update/delete where / insert/update target fields を順次実装しており、change-order は select where と update/delete where、input-aid は select where と update/delete where で利用できます。</p>
        </section>
    </section>

    <section>
        <h2>Canonical Field Draft</h2>
        <table>
            <thead>
            <tr>
                <th>field</th>
                <th>preview value</th>
                <th>note</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($fieldPreviewRows as $row): ?>
                <tr>
                    <td><code><?php echo app_h($row['field']); ?></code></td>
                    <td><code><?php echo app_h($row['preview']); ?></code></td>
                    <td><?php echo app_h($row['note']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <?php if ($methodExcerpt !== ''): ?>
        <section>
            <h2>Function Source Preview</h2>
            <pre><?php echo app_h($methodExcerpt); ?></pre>
        </section>
    <?php endif; ?>

    <?php if ($legacyDafuncSchema['data_excerpt'] !== ''): ?>
        <section>
            <h2>`data-dafunc.php` Preview</h2>
            <pre><?php echo app_h($legacyDafuncSchema['data_excerpt']); ?></pre>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}

function app_project_db_access_function_detail_is_legacy_single_proxy_auth_bridge_mode(string $bridgeMode): bool
{
    return trim($bridgeMode) === 'legacy-single-proxy-auth';
}

/**
 * @param array{
 *     source_name:string,
 *     function_name:string,
 *     function_list_order:string,
 *     function_suffix:string,
 *     action_type:string,
 *     data_class_base_name:string,
 *     target_table_name:string,
 *     parameter_type:string,
 *     select_by_distinct:string,
 *     sort_order_columns:string,
 *     memo:string,
 *     limit_parameter_type:string,
 *     limit_fixed_parameter:string,
 *     or_group_type:string,
 *     single_proxy_auth_type:string,
 *     single_proxy_single_get_function_name:string,
 *     is_blob_target:string,
 *     detected_signature:string,
 *     detected_line:string,
 *     source_of_truth:string
 * } $baseInput
 * @param array{
 *     source_name:string
 * } $entity
 * @param array{
 *     name:string,
 *     line:int,
 *     signature:string
 * } $method
 * @return array{
 *     source_name:string,
 *     function_name:string,
 *     function_list_order:string,
 *     function_suffix:string,
 *     action_type:string,
 *     data_class_base_name:string,
 *     target_table_name:string,
 *     parameter_type:string,
 *     select_by_distinct:string,
 *     sort_order_columns:string,
 *     memo:string,
 *     limit_parameter_type:string,
 *     limit_fixed_parameter:string,
 *     or_group_type:string,
 *     single_proxy_auth_type:string,
 *     single_proxy_single_get_function_name:string,
 *     is_blob_target:string,
 *     detected_signature:string,
 *     detected_line:string,
 *     source_of_truth:string
 * }
 */
function app_project_db_access_function_detail_build_legacy_single_proxy_auth_bridge_input(
    array $baseInput,
    array $entity,
    array $method,
): array {
    $input = $baseInput;
    $authType = app_project_db_access_function_detail_raw_post_value('single_proxy_auth_type');
    if ($authType === null) {
        $authType = app_project_db_access_function_detail_raw_post_value('SingleProxy_AuthType');
    }

    $singleGetFunctionName = app_project_db_access_function_detail_raw_post_value(
        'single_proxy_single_get_function_name',
    );
    if ($singleGetFunctionName === null) {
        $singleGetFunctionName = app_project_db_access_function_detail_raw_post_value(
            'SingleProxy_SingleGetFuncPID',
        );
    }

    $input['source_name'] = $entity['source_name'];
    $input['function_name'] = $method['name'];
    if ($authType !== null) {
        $input['single_proxy_auth_type'] = $authType;
    }
    if ($singleGetFunctionName !== null) {
        $input['single_proxy_single_get_function_name'] = $singleGetFunctionName;
    }
    $input['detected_signature'] = $method['signature'];
    $input['detected_line'] = (string) $method['line'];
    $input['source_of_truth'] = 'manual';

    return $input;
}

function app_project_db_access_function_detail_raw_post_value(string $name): ?string
{
    if (!array_key_exists($name, $_POST)) {
        return null;
    }

    $value = $_POST[$name];
    if (is_array($value)) {
        return null;
    }

    if (is_string($value) || is_numeric($value)) {
        return trim((string) $value);
    }

    return null;
}
