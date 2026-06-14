<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/project_db_access_route_common.php';

/**
 * @param array{
 *     select_target_field_id:string,
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     target_table_column_prefix:string,
 *     target_table_column_suffix:string,
 *     store_class_field_name:string,
 *     group_by_target:string,
 *     field_list_order:string,
 *     source_of_truth:string,
 *     updated_at:string
 * } $item
 * @return array{
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     target_table_column_prefix:string,
 *     target_table_column_suffix:string,
 *     store_class_field_name:string,
 *     group_by_target:string,
 *     field_list_order:string,
 *     source_of_truth:string
 * }
 */
function app_project_db_access_function_select_target_field_form_from_item(array $item): array
{
    return [
        'target_table_name' => $item['target_table_name'],
        'target_table_alias_name' => $item['target_table_alias_name'],
        'target_table_column_name' => $item['target_table_column_name'],
        'target_table_column_prefix' => $item['target_table_column_prefix'],
        'target_table_column_suffix' => $item['target_table_column_suffix'],
        'store_class_field_name' => $item['store_class_field_name'],
        'group_by_target' => $item['group_by_target'],
        'field_list_order' => $item['field_list_order'],
        'source_of_truth' => $item['source_of_truth'],
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
function app_render_project_db_access_function_select_target_field_edit_page(array $app, array $request): void
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
    $selectTargetFieldKey = trim(app_route_param($request, 'select_target_field_key'));
    $isNew = $selectTargetFieldKey === '';

    if (!$isNew && !ctype_digit($selectTargetFieldKey)) {
        app_render_bad_request_page($app, $request, 'select target field key の形式が不正です。');
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

    $catalogResult = app_fetch_db_access_function_select_target_field_catalog(
        $app,
        $projectKey,
        $entity['source_name'],
        $method['name'],
    );
    $catalogError = $catalogResult['ok'] ? '' : $catalogResult['error'];
    $catalogItems = $catalogResult['ok'] ? $catalogResult['items'] : [];
    $legacySchema = app_project_db_access_legacy_metadata_schema($app, 'dafuncselecttargetfields');

    $currentItem = null;
    $currentItemError = '';
    if (!$isNew) {
        $currentItemResult = app_fetch_db_access_function_select_target_field_item(
            $app,
            $projectKey,
            $entity['source_name'],
            $method['name'],
            $selectTargetFieldKey,
        );
        $currentItemError = $currentItemResult['ok'] ? '' : $currentItemResult['error'];
        $currentItem = $currentItemResult['ok'] ? $currentItemResult['item'] : null;

        if ($currentItemError === '' && $currentItem === null) {
            app_render_not_found_page($app, $request);
            return;
        }
    }

    $input = $currentItem !== null
        ? app_project_db_access_function_select_target_field_form_from_item($currentItem)
        : app_db_access_function_select_target_field_form_defaults();
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
        } elseif (!$isNew && trim(app_post_param('select_target_field_id')) !== $selectTargetFieldKey) {
            $errors[] = '更新対象の select target field key が route と一致しません。';
        } elseif ($formAction === 'delete') {
            if ($isNew) {
                $errors[] = '新規 row は削除できません。';
            } else {
                $deleteResult = app_delete_db_access_function_select_target_field(
                    $app,
                    $projectKey,
                    $entity['source_name'],
                    $method['name'],
                    $selectTargetFieldKey,
                );
                if ($deleteResult['ok']) {
                    app_send_redirect_response(
                        $request,
                        '/projects/' . rawurlencode($projectKey)
                        . '/db-access/' . rawurlencode($entity['source_name'])
                        . '/functions/' . rawurlencode($method['name'])
                        . '/select-target-fields?deleted=1',
                    );
                    return;
                }

                $errors[] = $deleteResult['error'];
            }
        } else {
            $validation = app_validate_db_access_function_select_target_field_form([
                'target_table_name' => app_post_param('target_table_name'),
                'target_table_alias_name' => app_post_param('target_table_alias_name'),
                'target_table_column_name' => app_post_param('target_table_column_name'),
                'target_table_column_prefix' => app_post_param('target_table_column_prefix'),
                'target_table_column_suffix' => app_post_param('target_table_column_suffix'),
                'store_class_field_name' => app_post_param('store_class_field_name'),
                'group_by_target' => app_post_param('group_by_target', '0'),
                'field_list_order' => app_post_param('field_list_order', '0'),
                'source_of_truth' => 'manual',
            ]);
            $input = $validation['input'];
            $errors = array_merge($errors, $validation['errors']);
            if ($errors === []) {
                $errors = array_merge(
                    $errors,
                    app_project_db_access_validate_select_target_field_metadata_refs(
                        $app,
                        $projectKey,
                        app_project_db_access_resolve_select_result_data_class_name(
                            $entity['source_name'],
                            $canonicalFunctionItem,
                        ),
                        $input,
                    ),
                );
            }

            if ($errors === []) {
                if ($isNew) {
                    $createResult = app_create_db_access_function_select_target_field($app, [
                        'project_key' => $projectKey,
                        'source_name' => $entity['source_name'],
                        'function_name' => $method['name'],
                        'target_table_name' => $input['target_table_name'],
                        'target_table_alias_name' => $input['target_table_alias_name'],
                        'target_table_column_name' => $input['target_table_column_name'],
                        'target_table_column_prefix' => $input['target_table_column_prefix'],
                        'target_table_column_suffix' => $input['target_table_column_suffix'],
                        'store_class_field_name' => $input['store_class_field_name'],
                        'group_by_target' => $input['group_by_target'],
                        'field_list_order' => $input['field_list_order'],
                        'source_of_truth' => $input['source_of_truth'],
                    ]);

                    if ($createResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            '/projects/' . rawurlencode($projectKey)
                            . '/db-access/' . rawurlencode($entity['source_name'])
                            . '/functions/' . rawurlencode($method['name'])
                            . '/select-target-fields/' . rawurlencode($createResult['item_id'])
                            . '?updated=1',
                        );
                        return;
                    }

                    $errors[] = $createResult['error'];
                } else {
                    $updateResult = app_update_db_access_function_select_target_field($app, [
                        'project_key' => $projectKey,
                        'source_name' => $entity['source_name'],
                        'function_name' => $method['name'],
                        'select_target_field_id' => $selectTargetFieldKey,
                        'target_table_name' => $input['target_table_name'],
                        'target_table_alias_name' => $input['target_table_alias_name'],
                        'target_table_column_name' => $input['target_table_column_name'],
                        'target_table_column_prefix' => $input['target_table_column_prefix'],
                        'target_table_column_suffix' => $input['target_table_column_suffix'],
                        'store_class_field_name' => $input['store_class_field_name'],
                        'group_by_target' => $input['group_by_target'],
                        'field_list_order' => $input['field_list_order'],
                        'source_of_truth' => $input['source_of_truth'],
                    ]);

                    if ($updateResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            '/projects/' . rawurlencode($projectKey)
                            . '/db-access/' . rawurlencode($entity['source_name'])
                            . '/functions/' . rawurlencode($method['name'])
                            . '/select-target-fields/' . rawurlencode($selectTargetFieldKey)
                            . '?updated=1',
                        );
                        return;
                    }

                    $errors[] = $updateResult['error'];
                }
            }
        }
    }

    $duplicateStoreFieldRows = [];
    foreach ($catalogItems as $catalogItem) {
        if (
            !$isNew
            && $catalogItem['select_target_field_id'] === $selectTargetFieldKey
        ) {
            continue;
        }

        if (
            trim($input['store_class_field_name']) !== ''
            && trim($catalogItem['store_class_field_name']) === trim($input['store_class_field_name'])
        ) {
            $duplicateStoreFieldRows[] = $catalogItem;
        }
    }

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();
    $effectiveSelectTargetFieldKey = !$isNew ? $selectTargetFieldKey : '';

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Function Select Target Field Edit</title>
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
        textarea {
            min-height: 6rem;
            resize: vertical;
        }
        button {
            margin-top: 1.25rem;
            margin-right: 0.75rem;
            padding: 0.75rem 1rem;
            border: 0;
            border-radius: 8px;
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            cursor: pointer;
        }
        button.danger {
            background: #991b1b;
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
        .warning {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: #ffedd5;
            color: #9a3412;
            border-radius: 8px;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">functions</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>"><code><?php echo app_h($method['name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-target-fields">select-target-fields</a> / <?php echo app_h($isNew ? 'new' : $effectiveSelectTargetFieldKey); ?></p>

    <h1><?php echo app_h($project['name']); ?> Select Target Field Edit</h1>
    <p><code>select target field</code> を編集する画面です。<code>field_list_order</code> は当面ここで直接入力し、change-order や sync 画面は後続で実装します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Function</h2>
            <ul>
                <li>db access: <code><?php echo app_h($entity['source_name']); ?></code></li>
                <li>function: <code><?php echo app_h($method['name']); ?></code></li>
                <li>action type: <code><?php echo app_h($effectiveActionType); ?></code></li>
                <li>signature: <code><?php echo app_h($method['signature']); ?></code></li>
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

        <section class="note-card">
            <h2>現在の制約</h2>
            <ul>
                <li>canonical <code>dbtable</code> / <code>dbtablecolumns</code> / select result <code>dataclassfields</code> への存在確認は行うが、alias graph の自動検証はまだ未実装</li>
                <li>legacy sync 由来の自動順序調整はまだ未移植</li>
                <li>重複する <code>store_class_field_name</code> は警告のみで、まだ保存時にブロックしない</li>
            </ul>
        </section>
    </div>

    <?php if ($updated): ?>
        <div class="success">select target field row を保存しました。</div>
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

    <?php if ($catalogError !== ''): ?>
        <div class="error"><?php echo app_h($catalogError); ?></div>
    <?php endif; ?>

    <?php if ($currentItemError !== ''): ?>
        <div class="error"><?php echo app_h($currentItemError); ?></div>
    <?php endif; ?>

    <?php if ($duplicateStoreFieldRows !== []): ?>
        <div class="warning">
            <p><code><?php echo app_h($input['store_class_field_name']); ?></code> は既存 row と重複しています。</p>
            <ul>
                <?php foreach ($duplicateStoreFieldRows as $duplicateItem): ?>
                    <li>#<?php echo app_h($duplicateItem['select_target_field_id']); ?> / <code><?php echo app_h(app_db_access_target_table_reference_label($duplicateItem)); ?></code> / <code><?php echo app_h(app_db_access_select_target_field_column_expression($duplicateItem)); ?></code></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($canonicalFunctionItem !== null && $catalogError === '' && $currentItemError === ''): ?>
        <form method="post" action="<?php
        echo $isNew
            ? '/projects/' . rawurlencode($projectKey) . '/db-access/' . rawurlencode($entity['source_name']) . '/functions/' . rawurlencode($method['name']) . '/select-target-fields/new'
            : '/projects/' . rawurlencode($projectKey) . '/db-access/' . rawurlencode($entity['source_name']) . '/functions/' . rawurlencode($method['name']) . '/select-target-fields/' . rawurlencode($effectiveSelectTargetFieldKey);
        ?>">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="select_target_field_id" value="<?php echo app_h($effectiveSelectTargetFieldKey); ?>">

            <label for="target_table_name">Target Table Name</label>
            <input id="target_table_name" name="target_table_name" value="<?php echo app_h($input['target_table_name']); ?>" placeholder="例: Project">

            <label for="target_table_alias_name">Target Table Alias Name</label>
            <input id="target_table_alias_name" name="target_table_alias_name" value="<?php echo app_h($input['target_table_alias_name']); ?>" placeholder="例: P">

            <label for="target_table_column_prefix">Target Column Prefix</label>
            <input id="target_table_column_prefix" name="target_table_column_prefix" value="<?php echo app_h($input['target_table_column_prefix']); ?>" placeholder="例: DATE_FORMAT(">

            <label for="target_table_column_name">Target Column Name</label>
            <input id="target_table_column_name" name="target_table_column_name" value="<?php echo app_h($input['target_table_column_name']); ?>" placeholder="例: ProjectName">

            <label for="target_table_column_suffix">Target Column Suffix</label>
            <input id="target_table_column_suffix" name="target_table_column_suffix" value="<?php echo app_h($input['target_table_column_suffix']); ?>" placeholder="例: , '%Y-%m-%d')">

            <label for="store_class_field_name">Store Class Field Name</label>
            <input id="store_class_field_name" name="store_class_field_name" value="<?php echo app_h($input['store_class_field_name']); ?>" placeholder="例: ProjectName">

            <label for="group_by_target">Group-By Target</label>
            <select id="group_by_target" name="group_by_target">
                <option value="0"<?php echo $input['group_by_target'] === '0' ? ' selected' : ''; ?>>0 (No)</option>
                <option value="1"<?php echo $input['group_by_target'] === '1' ? ' selected' : ''; ?>>1 (Yes)</option>
            </select>

            <label for="field_list_order">Field List Order</label>
            <input id="field_list_order" name="field_list_order" type="number" min="0" value="<?php echo app_h($input['field_list_order']); ?>">

            <button type="submit" name="form_action" value="save">保存</button>
            <?php if (!$isNew): ?>
                <button type="submit" name="form_action" value="delete" class="danger" onclick="return confirm('この row を削除しますか？');">削除</button>
            <?php endif; ?>
        </form>
    <?php endif; ?>

    <section class="summary-grid">
        <section class="summary-card">
            <h2>Normalized Preview</h2>
            <ul>
                <li>target: <code><?php echo app_h(app_db_access_target_table_reference_label($input)); ?></code></li>
                <li>column expression: <code><?php echo app_h(app_db_access_select_target_field_column_expression($input)); ?></code></li>
                <li>store field: <code><?php echo app_h($input['store_class_field_name'] !== '' ? $input['store_class_field_name'] : '(blank)'); ?></code></li>
                <li>group-by: <code><?php echo app_h(app_db_access_group_by_target_caption($input['group_by_target'])); ?></code></li>
                <li>field order: <code><?php echo app_h($input['field_list_order']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Legacy Schema</h2>
            <ul>
                <li>data file: <code><?php echo app_h($legacySchema['data_file']); ?></code></li>
                <li>field count: <code><?php echo app_h((string) count($legacySchema['field_names'])); ?></code></li>
                <li>repository methods: <code><?php echo app_h((string) count($legacySchema['dbaccess_methods'])); ?></code></li>
            </ul>
        </section>
    </section>

    <?php if ($legacySchema['data_excerpt'] !== ''): ?>
        <section>
            <h2>`data-dafuncselecttargetfields.php` Preview</h2>
            <pre><?php echo app_h($legacySchema['data_excerpt']); ?></pre>
        </section>
    <?php endif; ?>

    <p><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-target-fields">Back to Select Target Fields List</a></p>
</main>
</body>
</html>
    <?php
}
