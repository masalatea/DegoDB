<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/project_db_access_route_common.php';

function app_db_access_insert_update_target_field_mode_config(string $mode): array
{
    return match ($mode) {
        'insert' => [
            'mode' => 'insert',
            'route_segment' => 'insert-target-fields',
            'route_key_name' => 'insert_target_field_key',
            'item_id_field' => 'insert_target_field_id',
            'legacy_schema_name' => 'dafuncinserttargetfields',
            'legacy_list_file' => 'da_func_insert_target_fields.php',
            'legacy_edit_file' => 'da_func_insert_target_field_edit.php',
            'designer_title' => 'Insert Target Fields Designer',
            'designer_short_label' => 'insert target field',
            'add_new_label' => 'Add New Insert Target Field',
            'created_message' => 'insert target field row を作成しました。',
            'deleted_message' => 'insert target field row を削除しました。',
            'empty_message' => 'insert target field row はまだありません。',
            'allowed_action_types' => ['INSERT'],
        ],
        'update' => [
            'mode' => 'update',
            'route_segment' => 'update-target-fields',
            'route_key_name' => 'update_target_field_key',
            'item_id_field' => 'update_target_field_id',
            'legacy_schema_name' => 'dafuncupdatetargetfields',
            'legacy_list_file' => 'da_func_update_target_fields.php',
            'legacy_edit_file' => 'da_func_update_target_field_edit.php',
            'designer_title' => 'Update Target Fields Designer',
            'designer_short_label' => 'update target field',
            'add_new_label' => 'Add New Update Target Field',
            'created_message' => 'update target field row を作成しました。',
            'deleted_message' => 'update target field row を削除しました。',
            'empty_message' => 'update target field row はまだありません。',
            'allowed_action_types' => ['UPDATE'],
        ],
        default => throw new InvalidArgumentException('Unknown mode: ' . $mode),
    };
}

function app_fetch_db_access_insert_update_target_field_catalog_by_mode(
    array $app,
    string $mode,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    return match ($mode) {
        'insert' => app_fetch_db_access_function_insert_target_field_catalog($app, $projectKey, $sourceName, $functionName),
        'update' => app_fetch_db_access_function_update_target_field_catalog($app, $projectKey, $sourceName, $functionName),
        default => throw new InvalidArgumentException('Unknown mode: ' . $mode),
    };
}

function app_fetch_db_access_insert_update_target_field_item_by_mode(
    array $app,
    string $mode,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $itemId,
): array {
    return match ($mode) {
        'insert' => app_fetch_db_access_function_insert_target_field_item($app, $projectKey, $sourceName, $functionName, $itemId),
        'update' => app_fetch_db_access_function_update_target_field_item($app, $projectKey, $sourceName, $functionName, $itemId),
        default => throw new InvalidArgumentException('Unknown mode: ' . $mode),
    };
}

function app_create_db_access_insert_update_target_field_by_mode(array $app, string $mode, array $input): array
{
    return match ($mode) {
        'insert' => app_create_db_access_function_insert_target_field($app, $input),
        'update' => app_create_db_access_function_update_target_field($app, $input),
        default => throw new InvalidArgumentException('Unknown mode: ' . $mode),
    };
}

function app_update_db_access_insert_update_target_field_by_mode(array $app, string $mode, array $input): array
{
    return match ($mode) {
        'insert' => app_update_db_access_function_insert_target_field($app, $input),
        'update' => app_update_db_access_function_update_target_field($app, $input),
        default => throw new InvalidArgumentException('Unknown mode: ' . $mode),
    };
}

function app_delete_db_access_insert_update_target_field_by_mode(
    array $app,
    string $mode,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $itemId,
): array {
    return match ($mode) {
        'insert' => app_delete_db_access_function_insert_target_field($app, $projectKey, $sourceName, $functionName, $itemId),
        'update' => app_delete_db_access_function_update_target_field($app, $projectKey, $sourceName, $functionName, $itemId),
        default => throw new InvalidArgumentException('Unknown mode: ' . $mode),
    };
}

function app_project_db_access_function_insert_update_target_field_form_from_item(array $item): array
{
    return [
        'target_table_column_name' => $item['target_table_column_name'],
        'parameter_type' => $item['parameter_type'],
        'parameter_data_type' => $item['parameter_data_type'],
        'fixed_parameter' => $item['fixed_parameter'],
        'field_list_order' => $item['field_list_order'],
        'source_of_truth' => $item['source_of_truth'],
    ];
}

function app_render_project_db_access_function_insert_update_target_fields_page(array $app, array $request, string $mode): void
{
    $config = app_db_access_insert_update_target_field_mode_config($mode);
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

    $catalogResult = app_fetch_db_access_insert_update_target_field_catalog_by_mode(
        $app,
        $mode,
        $projectKey,
        $entity['source_name'],
        $method['name'],
    );
    $catalogError = $catalogResult['ok'] ? '' : $catalogResult['error'];
    $items = $catalogResult['ok'] ? $catalogResult['items'] : [];
    $legacySchema = app_project_db_access_legacy_metadata_schema($app, $config['legacy_schema_name']);

    $effectiveActionType = $canonicalFunctionItem !== null && $canonicalFunctionItem['action_type'] !== ''
        ? $canonicalFunctionItem['action_type']
        : $functionProfile['legacy_action_type'];
    $targetTableName = $canonicalFunctionItem !== null && $canonicalFunctionItem['target_table_name'] !== ''
        ? $canonicalFunctionItem['target_table_name']
        : $entity['source_name'];
    $isBlobTargetFunction = $canonicalFunctionItem !== null && $canonicalFunctionItem['is_blob_target'] === '1';
    $blobRuntimeContractSupported = app_generated_file_method_has_blob_streaming_contract(
        $entity['dbaccess_path'],
        $method['name'],
    );
    $allowFileParameterDataType = $isBlobTargetFunction && $blobRuntimeContractSupported;
    $isCompatibleFunction = in_array($effectiveActionType, $config['allowed_action_types'], true);
    $created = app_query_param('created') === '1';
    $deleted = app_query_param('deleted') === '1';
    $fileDataTypeCount = count(
        array_filter(
            $items,
            static fn (array $item): bool => $item['parameter_data_type'] === 'file',
        ),
    );

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Function <?php echo app_h($config['designer_title']); ?></title>
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
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">functions</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>"><code><?php echo app_h($method['name']); ?></code></a> / <?php echo app_h($config['route_segment']); ?></p>

    <h1><?php echo app_h($project['name']); ?> <?php echo app_h($config['designer_title']); ?></h1>
    <p>insert/update 系 function の target field を canonical row として管理する画面です。<code>db-config</code> に保存し、一覧・編集できる状態にします。</p>

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
                    <li>is blob target: <code><?php echo app_h($canonicalFunctionItem['is_blob_target']); ?></code></li>
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
                    <li>file data type rows: <code><?php echo app_h((string) $fileDataTypeCount); ?></code></li>
                    <li>state: <code><?php echo app_h($items === [] ? 'empty' : 'active'); ?></code></li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>現在の制約</h2>
            <ul>
                <li>legacy の sync/import 画面はまだ未実装</li>
                <li><code>field_list_order</code> は当面ここで直接入力する</li>
                <li><code>file</code> data type は <code>IsBlobTarget=1</code> の function でのみ許可する</li>
                <li>blob target は current runtime generator では legacy delegate 前提であり、<code>prepare()</code> / <code>bind_param("b")</code> / <code>send_long_data()</code> を持つ legacy method が必要</li>
            </ul>
            <?php if (!$isCompatibleFunction): ?>
                <p class="muted">この route は主に <?php echo app_h(implode('/', $config['allowed_action_types'])); ?> 系 function 向けです。現在の action type は <code><?php echo app_h($effectiveActionType); ?></code> です。</p>
            <?php endif; ?>
            <?php if ($isBlobTargetFunction && !$blobRuntimeContractSupported): ?>
                <p class="muted">現在の method source では legacy blob contract が検出できません。<code>file</code> row を追加する前に function detail の <code>IsBlobTarget</code> 設定を見直してください。</p>
            <?php endif; ?>
        </section>
    </div>

    <?php if ($created): ?>
        <div class="success"><?php echo app_h($config['created_message']); ?></div>
    <?php endif; ?>

    <?php if ($deleted): ?>
        <div class="success"><?php echo app_h($config['deleted_message']); ?></div>
    <?php endif; ?>

    <?php if ($canonicalFunctionItem !== null && $catalogError === ''): ?>
        <p><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/<?php echo app_h($config['route_segment']); ?>/new"><?php echo app_h($config['add_new_label']); ?></a></p>
    <?php endif; ?>

    <?php if ($items === []): ?>
        <p><?php echo app_h($config['empty_message']); ?></p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>target column</th>
                <th>parameter</th>
                <th>order</th>
                <th>canonical state</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $index => $item): ?>
                <tr>
                    <td><?php echo app_h((string) ($index + 1)); ?></td>
                    <td><code><?php echo app_h($targetTableName . '.' . $item['target_table_column_name']); ?></code></td>
                    <td><code><?php echo app_h(app_db_access_insert_update_target_field_parameter_summary($item)); ?></code></td>
                    <td><code><?php echo app_h($item['field_list_order']); ?></code></td>
                    <td>
                        <code><?php echo app_h($item['source_of_truth']); ?></code><br>
                        <span class="muted">updated: <?php echo app_h($item['updated_at']); ?></span>
                    </td>
                    <td><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/<?php echo app_h($config['route_segment']); ?>/<?php echo rawurlencode($item[$config['item_id_field']]); ?>">edit</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <section>
        <h2>Legacy Schema Preview</h2>
        <ul>
            <li>schema file: <code><?php echo app_h($legacySchema['data_file']); ?></code></li>
            <li>field count: <code><?php echo app_h((string) count($legacySchema['field_names'])); ?></code></li>
            <li>repository method count: <code><?php echo app_h((string) count($legacySchema['dbaccess_methods'])); ?></code></li>
        </ul>
        <?php if ($legacySchema['data_excerpt'] !== ''): ?>
            <pre><?php echo app_h($legacySchema['data_excerpt']); ?></pre>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
    <?php
}

function app_render_project_db_access_function_insert_update_target_field_edit_page(array $app, array $request, string $mode): void
{
    $config = app_db_access_insert_update_target_field_mode_config($mode);
    $bootstrap = app_project_db_access_function_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $entity = $bootstrap['entity'];
    $method = $bootstrap['method'];
    $functionProfile = $bootstrap['function_profile'];
    $itemKey = trim(app_route_param($request, $config['route_key_name']));
    $isNew = $itemKey === '';

    if (!$isNew && !ctype_digit($itemKey)) {
        app_render_bad_request_page($app, $request, $config['designer_short_label'] . ' key の形式が不正です。');
        return;
    }

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
    $targetTableName = $canonicalFunctionItem !== null && $canonicalFunctionItem['target_table_name'] !== ''
        ? $canonicalFunctionItem['target_table_name']
        : $entity['source_name'];
    $isBlobTargetFunction = $canonicalFunctionItem !== null && $canonicalFunctionItem['is_blob_target'] === '1';
    $blobRuntimeContractSupported = app_generated_file_method_has_blob_streaming_contract(
        $entity['dbaccess_path'],
        $method['name'],
    );
    $allowFileParameterDataType = $isBlobTargetFunction && $blobRuntimeContractSupported;

    $catalogResult = app_fetch_db_access_insert_update_target_field_catalog_by_mode(
        $app,
        $mode,
        $projectKey,
        $entity['source_name'],
        $method['name'],
    );
    $catalogError = $catalogResult['ok'] ? '' : $catalogResult['error'];
    $catalogItems = $catalogResult['ok'] ? $catalogResult['items'] : [];
    $legacySchema = app_project_db_access_legacy_metadata_schema($app, $config['legacy_schema_name']);

    $currentItem = null;
    $currentItemError = '';
    if (!$isNew) {
        $currentItemResult = app_fetch_db_access_insert_update_target_field_item_by_mode(
            $app,
            $mode,
            $projectKey,
            $entity['source_name'],
            $method['name'],
            $itemKey,
        );
        $currentItemError = $currentItemResult['ok'] ? '' : $currentItemResult['error'];
        $currentItem = $currentItemResult['ok'] ? $currentItemResult['item'] : null;

        if ($currentItemError === '' && $currentItem === null) {
            app_render_not_found_page($app, $request);
            return;
        }
    }

    $input = $currentItem !== null
        ? app_project_db_access_function_insert_update_target_field_form_from_item($currentItem)
        : app_db_access_function_insert_update_target_field_form_defaults();
    if ($isNew && $catalogError === '' && $catalogItems !== []) {
        $input['field_list_order'] = (string) count($catalogItems);
    }

    $errors = [];
    $updated = app_query_param('updated') === '1';

    if (app_request_method_is($request, 'POST')) {
        $formAction = app_post_param('form_action', 'save');

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif ($canonicalFunctionError !== '') {
            $errors[] = $canonicalFunctionError;
        } elseif ($canonicalFunctionItem === null) {
            $errors[] = '先に function detail で canonical metadata を保存してください。';
        } elseif (!$isNew && trim(app_post_param($config['item_id_field'])) !== $itemKey) {
            $errors[] = '更新対象の key が route と一致しません。';
        } elseif ($formAction === 'delete') {
            if ($isNew) {
                $errors[] = '新規 row は削除できません。';
            } else {
                $deleteResult = app_delete_db_access_insert_update_target_field_by_mode(
                    $app,
                    $mode,
                    $projectKey,
                    $entity['source_name'],
                    $method['name'],
                    $itemKey,
                );
                if ($deleteResult['ok']) {
                    app_send_redirect_response(
                        $request,
                        '/projects/' . rawurlencode($projectKey)
                        . '/db-access/' . rawurlencode($entity['source_name'])
                        . '/functions/' . rawurlencode($method['name'])
                        . '/' . $config['route_segment'] . '?deleted=1',
                    );
                    return;
                }

                $errors[] = $deleteResult['error'];
            }
        } else {
            $validation = app_validate_db_access_function_insert_update_target_field_form([
                'target_table_column_name' => app_post_param('target_table_column_name'),
                'parameter_type' => app_post_param('parameter_type', 'argument'),
                'parameter_data_type' => app_post_param('parameter_data_type'),
                'fixed_parameter' => app_post_param('fixed_parameter'),
                'field_list_order' => app_post_param('field_list_order', '0'),
                'source_of_truth' => 'manual',
            ], $allowFileParameterDataType, $blobRuntimeContractSupported);
            $input = $validation['input'];
            $errors = array_merge($errors, $validation['errors']);

            if ($errors === []) {
                $payload = [
                    'project_key' => $projectKey,
                    'source_name' => $entity['source_name'],
                    'function_name' => $method['name'],
                    'target_table_column_name' => $input['target_table_column_name'],
                    'parameter_type' => $input['parameter_type'],
                    'parameter_data_type' => $input['parameter_data_type'],
                    'fixed_parameter' => $input['fixed_parameter'],
                    'field_list_order' => $input['field_list_order'],
                    'source_of_truth' => $input['source_of_truth'],
                ];

                if ($isNew) {
                    $createResult = app_create_db_access_insert_update_target_field_by_mode($app, $mode, $payload);
                    if ($createResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            '/projects/' . rawurlencode($projectKey)
                            . '/db-access/' . rawurlencode($entity['source_name'])
                            . '/functions/' . rawurlencode($method['name'])
                            . '/' . $config['route_segment'] . '/' . rawurlencode($createResult['item_id'])
                            . '?updated=1',
                        );
                        return;
                    }

                    $errors[] = $createResult['error'];
                } else {
                    $payload[$config['item_id_field']] = $itemKey;
                    $updateResult = app_update_db_access_insert_update_target_field_by_mode($app, $mode, $payload);
                    if ($updateResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            '/projects/' . rawurlencode($projectKey)
                            . '/db-access/' . rawurlencode($entity['source_name'])
                            . '/functions/' . rawurlencode($method['name'])
                            . '/' . $config['route_segment'] . '/' . rawurlencode($itemKey)
                            . '?updated=1',
                        );
                        return;
                    }

                    $errors[] = $updateResult['error'];
                }
            }
        }
    }

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();
    $effectiveItemKey = !$isNew ? $itemKey : '';

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Function <?php echo app_h($config['designer_title']); ?> Edit</title>
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
        label {
            display: block;
            font-weight: 600;
            margin-top: 1rem;
        }
        input, select, textarea, button {
            width: 100%;
            box-sizing: border-box;
            margin-top: 0.35rem;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font: inherit;
        }
        button {
            width: auto;
            cursor: pointer;
            background: #0f172a;
            color: #ffffff;
            border: 0;
            margin-right: 0.75rem;
        }
        button.delete {
            background: #b91c1c;
        }
        .actions {
            margin-top: 1.5rem;
        }
        .errors {
            margin-top: 1rem;
            padding: 1rem 1.25rem;
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
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">functions</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>"><code><?php echo app_h($method['name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/<?php echo app_h($config['route_segment']); ?>"><?php echo app_h($config['route_segment']); ?></a> / <?php echo $isNew ? 'new' : app_h($effectiveItemKey); ?></p>

    <h1><?php echo app_h($project['name']); ?> <?php echo app_h($config['designer_title']); ?> Edit</h1>
    <p>target table <code><?php echo app_h($targetTableName); ?></code> に対する <?php echo app_h($config['designer_short_label']); ?> row を編集します。</p>

    <?php if ($updated): ?>
        <div class="success"><?php echo app_h($config['designer_short_label']); ?> row を保存しました。</div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="errors">
            <strong>入力内容を確認してください。</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Function</h2>
            <ul>
                <li>db access: <code><?php echo app_h($entity['source_name']); ?></code></li>
                <li>function: <code><?php echo app_h($method['name']); ?></code></li>
                <li>action type: <code><?php echo app_h($effectiveActionType); ?></code></li>
                <li>target table: <code><?php echo app_h($targetTableName); ?></code></li>
                <li>is blob target: <code><?php echo app_h($canonicalFunctionItem !== null ? $canonicalFunctionItem['is_blob_target'] : '0'); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>現在の制約</h2>
            <ul>
                <li><code>field_list_order</code> は当面ここで直接入力する</li>
                <li><code>file</code> data type は <code>IsBlobTarget=1</code> の function でのみ許可する</li>
                <li>legacy sync/change-order はまだ未実装</li>
                <li>blob target は current runtime generator では legacy delegate 前提であり、<code>prepare()</code> / <code>bind_param("b")</code> / <code>send_long_data()</code> を持つ legacy method が必要</li>
            </ul>
            <?php if ($isBlobTargetFunction && !$blobRuntimeContractSupported): ?>
                <p class="muted">現在の method source では legacy blob contract が検出できません。<code>file</code> row を追加する前に function detail の <code>IsBlobTarget</code> 設定を見直してください。</p>
            <?php endif; ?>
        </section>
    </div>

    <form method="post">
        <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
        <input type="hidden" name="<?php echo app_h($config['item_id_field']); ?>" value="<?php echo app_h($effectiveItemKey); ?>">

        <label for="target_table_column_name">Target Column Name</label>
        <input id="target_table_column_name" name="target_table_column_name" value="<?php echo app_h($input['target_table_column_name']); ?>" placeholder="例: ProjectName">

        <label for="parameter_type">Parameter Type</label>
        <select id="parameter_type" name="parameter_type">
            <?php foreach (app_allowed_db_access_update_delete_where_parameter_types() as $parameterType): ?>
                <option value="<?php echo app_h($parameterType); ?>"<?php echo $input['parameter_type'] === $parameterType ? ' selected' : ''; ?>><?php echo app_h(app_db_access_update_delete_where_parameter_type_caption($parameterType)); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="parameter_data_type">Parameter Data Type</label>
        <select id="parameter_data_type" name="parameter_data_type">
            <?php foreach (app_allowed_db_access_insert_update_target_field_parameter_data_types() as $parameterDataType): ?>
                <?php if (!$allowFileParameterDataType && $parameterDataType === 'file' && $input['parameter_data_type'] !== 'file') { continue; } ?>
                <option value="<?php echo app_h($parameterDataType); ?>"<?php echo $input['parameter_data_type'] === $parameterDataType ? ' selected' : ''; ?>><?php echo app_h(app_db_access_parameter_data_type_caption($parameterDataType)); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="fixed_parameter">Fixed Parameter</label>
        <input id="fixed_parameter" name="fixed_parameter" value="<?php echo app_h($input['fixed_parameter']); ?>" placeholder="Parameter Type が fixed のときに使用">

        <label for="field_list_order">Field List Order</label>
        <input id="field_list_order" name="field_list_order" value="<?php echo app_h($input['field_list_order']); ?>" inputmode="numeric">

        <div class="actions">
            <button type="submit" name="form_action" value="save">保存</button>
            <?php if (!$isNew): ?>
                <button class="delete" type="submit" name="form_action" value="delete" onclick="return confirm('この row を削除します。よろしいですか。');">削除</button>
            <?php endif; ?>
        </div>
    </form>

    <section>
        <h2>Catalog Preview</h2>
        <?php if ($catalogError !== ''): ?>
            <p class="muted"><?php echo app_h($catalogError); ?></p>
        <?php elseif ($catalogItems === []): ?>
            <p class="muted">まだ保存済み row はありません。</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>target column</th>
                    <th>parameter</th>
                    <th>order</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($catalogItems as $catalogItem): ?>
                    <tr>
                        <td><code><?php echo app_h($catalogItem[$config['item_id_field']]); ?></code></td>
                        <td><code><?php echo app_h($catalogItem['target_table_column_name']); ?></code></td>
                        <td><code><?php echo app_h(app_db_access_insert_update_target_field_parameter_summary($catalogItem)); ?></code></td>
                        <td><code><?php echo app_h($catalogItem['field_list_order']); ?></code></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section>
        <h2>Legacy Schema Preview</h2>
        <ul>
            <li>schema file: <code><?php echo app_h($legacySchema['data_file']); ?></code></li>
            <li>field count: <code><?php echo app_h((string) count($legacySchema['field_names'])); ?></code></li>
            <li>repository method count: <code><?php echo app_h((string) count($legacySchema['dbaccess_methods'])); ?></code></li>
        </ul>
        <?php if ($legacySchema['data_excerpt'] !== ''): ?>
            <pre><?php echo app_h($legacySchema['data_excerpt']); ?></pre>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
    <?php
}
