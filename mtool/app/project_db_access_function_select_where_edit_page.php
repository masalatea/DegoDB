<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/project_db_access_route_common.php';

/**
 * @param array{
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
 * } $item
 * @return array{
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
 *     source_of_truth:string
 * }
 */
function app_project_db_access_function_select_where_form_from_item(array $item): array
{
    return [
        'target_table_name' => $item['target_table_name'],
        'target_table_alias_name' => $item['target_table_alias_name'],
        'target_table_column_name' => $item['target_table_column_name'],
        'parameter_type' => $item['parameter_type'],
        'parameter_data_type' => $item['parameter_data_type'],
        'fixed_parameter' => $item['fixed_parameter'],
        'another_table_name' => $item['another_table_name'],
        'another_table_alias_name' => $item['another_table_alias_name'],
        'another_field_name' => $item['another_field_name'],
        'join_type' => $item['join_type'],
        'or_group' => $item['or_group'],
        'relational_operator' => $item['relational_operator'],
        'where_order' => $item['where_order'],
        'source_of_truth' => $item['source_of_truth'],
    ];
}

/**
 * @param array{
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
 *     source_of_truth:string
 * } $input
 * @return array{
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
 *     source_of_truth:string
 * }
 */
function app_project_db_access_function_select_where_form_apply_query_defaults(array $input): array
{
    $input['target_table_name'] = app_query_param('target_table_name', $input['target_table_name']);
    $input['target_table_alias_name'] = app_query_param('target_table_alias_name', $input['target_table_alias_name']);
    $input['target_table_column_name'] = app_query_param('target_table_column_name', $input['target_table_column_name']);
    $input['parameter_type'] = app_query_param('parameter_type', $input['parameter_type']);
    $input['parameter_data_type'] = app_query_param('parameter_data_type', $input['parameter_data_type']);
    $input['fixed_parameter'] = app_query_param('fixed_parameter', $input['fixed_parameter']);
    $input['another_table_name'] = app_query_param('another_table_name', $input['another_table_name']);
    $input['another_table_alias_name'] = app_query_param('another_table_alias_name', $input['another_table_alias_name']);
    $input['another_field_name'] = app_query_param('another_field_name', $input['another_field_name']);
    $input['join_type'] = app_query_param('join_type', $input['join_type']);
    $input['or_group'] = app_query_param('or_group', $input['or_group']);
    $input['relational_operator'] = app_query_param('relational_operator', $input['relational_operator']);
    $input['where_order'] = app_query_param('where_order', $input['where_order']);

    return $input;
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
function app_render_project_db_access_function_select_where_edit_page(array $app, array $request): void
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
    $selectWhereKey = trim(app_route_param($request, 'select_where_key'));
    $isNew = $selectWhereKey === '';

    if (!$isNew && !ctype_digit($selectWhereKey)) {
        app_render_bad_request_page($app, $request, 'select where key の形式が不正です。');
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

    $catalogResult = app_fetch_db_access_function_select_where_catalog(
        $app,
        $projectKey,
        $entity['source_name'],
        $method['name'],
    );
    $catalogError = $catalogResult['ok'] ? '' : $catalogResult['error'];
    $catalogItems = $catalogResult['ok'] ? $catalogResult['items'] : [];
    $legacySchema = app_project_db_access_legacy_metadata_schema($app, 'dafuncselectwhere');

    $currentItem = null;
    $currentItemError = '';
    if (!$isNew) {
        $currentItemResult = app_fetch_db_access_function_select_where_item(
            $app,
            $projectKey,
            $entity['source_name'],
            $method['name'],
            $selectWhereKey,
        );
        $currentItemError = $currentItemResult['ok'] ? '' : $currentItemResult['error'];
        $currentItem = $currentItemResult['ok'] ? $currentItemResult['item'] : null;

        if ($currentItemError === '' && $currentItem === null) {
            app_render_not_found_page($app, $request);
            return;
        }
    }

    $input = $currentItem !== null
        ? app_project_db_access_function_select_where_form_from_item($currentItem)
        : app_db_access_function_select_where_form_defaults();
    if ($isNew && $catalogError === '' && $catalogItems !== []) {
        $input['where_order'] = (string) (count($catalogItems));
    }
    if ($isNew) {
        $input = app_project_db_access_function_select_where_form_apply_query_defaults($input);
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
        } elseif (!$isNew && trim(app_post_param('select_where_id')) !== $selectWhereKey) {
            $errors[] = '更新対象の select where key が route と一致しません。';
        } elseif ($formAction === 'delete') {
            if ($isNew) {
                $errors[] = '新規 row は削除できません。';
            } else {
                $deleteResult = app_delete_db_access_function_select_where(
                    $app,
                    $projectKey,
                    $entity['source_name'],
                    $method['name'],
                    $selectWhereKey,
                );
                if ($deleteResult['ok']) {
                    app_send_redirect_response(
                        $request,
                        '/projects/' . rawurlencode($projectKey)
                        . '/db-access/' . rawurlencode($entity['source_name'])
                        . '/functions/' . rawurlencode($method['name'])
                        . '/select-where?deleted=1',
                    );
                    return;
                }

                $errors[] = $deleteResult['error'];
            }
        } else {
            $validation = app_validate_db_access_function_select_where_form([
                'target_table_name' => app_post_param('target_table_name'),
                'target_table_alias_name' => app_post_param('target_table_alias_name'),
                'target_table_column_name' => app_post_param('target_table_column_name'),
                'parameter_type' => app_post_param('parameter_type'),
                'parameter_data_type' => app_post_param('parameter_data_type'),
                'fixed_parameter' => app_post_param('fixed_parameter'),
                'another_table_name' => app_post_param('another_table_name'),
                'another_table_alias_name' => app_post_param('another_table_alias_name'),
                'another_field_name' => app_post_param('another_field_name'),
                'join_type' => app_post_param('join_type'),
                'or_group' => app_post_param('or_group'),
                'relational_operator' => app_post_param('relational_operator', '='),
                'where_order' => app_post_param('where_order', '0'),
                'source_of_truth' => 'manual',
            ]);
            $input = $validation['input'];
            $errors = array_merge($errors, $validation['errors']);
            if ($errors === []) {
                $errors = array_merge(
                    $errors,
                    app_project_db_access_validate_select_where_metadata_refs(
                        $app,
                        $projectKey,
                        $input,
                    ),
                );
            }

            if ($errors === []) {
                if ($isNew) {
                    $createResult = app_create_db_access_function_select_where($app, [
                        'project_key' => $projectKey,
                        'source_name' => $entity['source_name'],
                        'function_name' => $method['name'],
                        'target_table_name' => $input['target_table_name'],
                        'target_table_alias_name' => $input['target_table_alias_name'],
                        'target_table_column_name' => $input['target_table_column_name'],
                        'parameter_type' => $input['parameter_type'],
                        'parameter_data_type' => $input['parameter_data_type'],
                        'fixed_parameter' => $input['fixed_parameter'],
                        'another_table_name' => $input['another_table_name'],
                        'another_table_alias_name' => $input['another_table_alias_name'],
                        'another_field_name' => $input['another_field_name'],
                        'join_type' => $input['join_type'],
                        'or_group' => $input['or_group'],
                        'relational_operator' => $input['relational_operator'],
                        'where_order' => $input['where_order'],
                        'source_of_truth' => $input['source_of_truth'],
                    ]);

                    if ($createResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            '/projects/' . rawurlencode($projectKey)
                            . '/db-access/' . rawurlencode($entity['source_name'])
                            . '/functions/' . rawurlencode($method['name'])
                            . '/select-where/' . rawurlencode($createResult['item_id'])
                            . '?updated=1',
                        );
                        return;
                    }

                    $errors[] = $createResult['error'];
                } else {
                    $updateResult = app_update_db_access_function_select_where($app, [
                        'project_key' => $projectKey,
                        'source_name' => $entity['source_name'],
                        'function_name' => $method['name'],
                        'select_where_id' => $selectWhereKey,
                        'target_table_name' => $input['target_table_name'],
                        'target_table_alias_name' => $input['target_table_alias_name'],
                        'target_table_column_name' => $input['target_table_column_name'],
                        'parameter_type' => $input['parameter_type'],
                        'parameter_data_type' => $input['parameter_data_type'],
                        'fixed_parameter' => $input['fixed_parameter'],
                        'another_table_name' => $input['another_table_name'],
                        'another_table_alias_name' => $input['another_table_alias_name'],
                        'another_field_name' => $input['another_field_name'],
                        'join_type' => $input['join_type'],
                        'or_group' => $input['or_group'],
                        'relational_operator' => $input['relational_operator'],
                        'where_order' => $input['where_order'],
                        'source_of_truth' => $input['source_of_truth'],
                    ]);

                    if ($updateResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            '/projects/' . rawurlencode($projectKey)
                            . '/db-access/' . rawurlencode($entity['source_name'])
                            . '/functions/' . rawurlencode($method['name'])
                            . '/select-where/' . rawurlencode($selectWhereKey)
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
    $effectiveSelectWhereKey = !$isNew ? $selectWhereKey : '';

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Function Select Where Edit</title>
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
        input[readonly] {
            background: #e2e8f0;
        }
        textarea {
            min-height: 8rem;
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
        .muted {
            color: #475569;
        }
        .field-group {
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">functions</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>"><code><?php echo app_h($method['name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-where">select-where</a> / <?php echo app_h($isNew ? 'new' : $effectiveSelectWhereKey); ?></p>

    <h1><?php echo app_h($project['name']); ?> Select Where Edit</h1>
    <p><code>select where</code> 条件行を編集する画面です。<code>where_order</code> はここで直接入力でき、まとめて並び替える場合は change-order 画面を使います。</p>

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
                <li>canonical <code>dbtable</code> / <code>dbtablecolumns</code> に対する table / column existence check は行うが、join alias graph の自動検証はまだ未実装</li>
                <li>Join Type は legacy 互換で `inner` / `left` / `right` / blank を直接持つ</li>
                <li>Input Aid は generated property 候補ベースの簡易版であり、join alias の候補導出はまだ未実装</li>
            </ul>
        </section>
    </div>

    <?php if ($updated): ?>
        <div class="success">select where row を保存しました。</div>
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

    <?php if ($canonicalFunctionItem !== null && $catalogError === '' && $currentItemError === ''): ?>
        <form method="post" action="<?php
        echo $isNew
            ? '/projects/' . rawurlencode($projectKey) . '/db-access/' . rawurlencode($entity['source_name']) . '/functions/' . rawurlencode($method['name']) . '/select-where/new'
            : '/projects/' . rawurlencode($projectKey) . '/db-access/' . rawurlencode($entity['source_name']) . '/functions/' . rawurlencode($method['name']) . '/select-where/' . rawurlencode($effectiveSelectWhereKey);
        ?>">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="select_where_id" value="<?php echo app_h($effectiveSelectWhereKey); ?>">

            <label for="parameter_type">Parameter Type</label>
            <select id="parameter_type" name="parameter_type">
                <?php foreach (app_allowed_db_access_select_where_parameter_types() as $parameterType): ?>
                    <option value="<?php echo app_h($parameterType); ?>"<?php echo $input['parameter_type'] === $parameterType ? ' selected' : ''; ?>><?php echo app_h(app_db_access_select_where_parameter_type_caption($parameterType)); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="join_type">Join Type</label>
            <select id="join_type" name="join_type">
                <?php foreach (app_allowed_db_access_select_where_join_types() as $joinType): ?>
                    <option value="<?php echo app_h($joinType); ?>"<?php echo $input['join_type'] === $joinType ? ' selected' : ''; ?>><?php echo app_h(app_db_access_select_where_join_type_caption($joinType)); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="target_table_name">Target Table Name</label>
            <input id="target_table_name" name="target_table_name" value="<?php echo app_h($input['target_table_name']); ?>" placeholder="例: Project">

            <label for="target_table_alias_name">Target Table Alias Name</label>
            <input id="target_table_alias_name" name="target_table_alias_name" value="<?php echo app_h($input['target_table_alias_name']); ?>" placeholder="例: Project">

            <label for="target_table_column_name">Target Column Name</label>
            <input id="target_table_column_name" name="target_table_column_name" value="<?php echo app_h($input['target_table_column_name']); ?>" placeholder="例: Id">

            <label for="relational_operator">Relational Operator</label>
            <select id="relational_operator" name="relational_operator">
                <?php foreach (app_allowed_db_access_relational_operators() as $operator): ?>
                    <option value="<?php echo app_h($operator); ?>"<?php echo $input['relational_operator'] === $operator ? ' selected' : ''; ?>><?php echo app_h($operator); ?></option>
                <?php endforeach; ?>
            </select>

            <div class="field-group" id="parameter_data_type_group">
                <label for="parameter_data_type">Parameter Data Type</label>
                <select id="parameter_data_type" name="parameter_data_type">
                    <?php foreach (app_allowed_db_access_select_where_parameter_data_types() as $dataType): ?>
                        <option value="<?php echo app_h($dataType); ?>"<?php echo $input['parameter_data_type'] === $dataType ? ' selected' : ''; ?>><?php echo app_h(app_db_access_parameter_data_type_caption($dataType)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-group" id="fixed_parameter_group">
                <label for="fixed_parameter">Fixed Parameter</label>
                <textarea id="fixed_parameter" name="fixed_parameter" placeholder="例: 1, NOW(), CURRENT_DATE"><?php echo app_h($input['fixed_parameter']); ?></textarea>
            </div>

            <div class="field-group" id="another_field_group">
                <label for="another_table_name">Another Table Name</label>
                <input id="another_table_name" name="another_table_name" value="<?php echo app_h($input['another_table_name']); ?>" placeholder="例: ProjectUser">

                <label for="another_table_alias_name">Another Table Alias Name</label>
                <input id="another_table_alias_name" name="another_table_alias_name" value="<?php echo app_h($input['another_table_alias_name']); ?>" placeholder="例: ProjectUser">

                <label for="another_field_name">Another Field Name</label>
                <input id="another_field_name" name="another_field_name" value="<?php echo app_h($input['another_field_name']); ?>" placeholder="例: ProjectId">
            </div>

            <label for="or_group">OR Group</label>
            <input id="or_group" name="or_group" value="<?php echo app_h($input['or_group']); ?>" placeholder="例: 1">

            <label for="where_order">Where Order</label>
            <input id="where_order" name="where_order" type="number" min="0" value="<?php echo app_h($input['where_order']); ?>">

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
                <li>join type: <code><?php echo app_h(app_db_access_select_where_join_type_caption($input['join_type'])); ?></code></li>
                <li>parameter type: <code><?php echo app_h(app_db_access_select_where_parameter_type_caption($input['parameter_type'])); ?></code></li>
                <li>parameter summary: <code><?php echo app_h(app_db_access_select_where_parameter_summary($input)); ?></code></li>
                <li>where order: <code><?php echo app_h($input['where_order']); ?></code></li>
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
            <h2>`data-dafuncselectwhere.php` Preview</h2>
            <pre><?php echo app_h($legacySchema['data_excerpt']); ?></pre>
        </section>
    <?php endif; ?>

    <p><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-where">Back to Select Where List</a></p>
</main>
<script>
function updateSelectWhereFormVisibility() {
    const parameterType = document.getElementById('parameter_type');
    if (!parameterType) {
        return;
    }

    const value = parameterType.value;
    const fixedGroup = document.getElementById('fixed_parameter_group');
    const anotherFieldGroup = document.getElementById('another_field_group');
    const dataTypeGroup = document.getElementById('parameter_data_type_group');

    if (fixedGroup) {
        fixedGroup.style.display = value === 'fixed' ? 'block' : 'none';
    }
    if (anotherFieldGroup) {
        anotherFieldGroup.style.display = value === 'anotherfield' ? 'block' : 'none';
    }
    if (dataTypeGroup) {
        dataTypeGroup.style.display = value === 'anotherfield' ? 'none' : 'block';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const parameterType = document.getElementById('parameter_type');
    if (parameterType) {
        parameterType.addEventListener('change', updateSelectWhereFormVisibility);
    }
    updateSelectWhereFormVisibility();
});
</script>
</body>
</html>
    <?php
}
