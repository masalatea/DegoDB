<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/project_db_access_route_common.php';

/**
 * @param array{
 *     select_having_id:string,
 *     left_target_prefix:string,
 *     left_target_field_id:string,
 *     left_target_suffix:string,
 *     relational_operator:string,
 *     right_target_prefix:string,
 *     right_parameter_type:string,
 *     right_parameter_data_type:string,
 *     right_fixed_parameter:string,
 *     right_target_field_id:string,
 *     right_target_suffix:string,
 *     having_order:string,
 *     source_of_truth:string,
 *     updated_at:string
 * } $item
 * @return array{
 *     left_target_prefix:string,
 *     left_target_field_id:string,
 *     left_target_suffix:string,
 *     relational_operator:string,
 *     right_target_prefix:string,
 *     right_parameter_type:string,
 *     right_parameter_data_type:string,
 *     right_fixed_parameter:string,
 *     right_target_field_id:string,
 *     right_target_suffix:string,
 *     having_order:string,
 *     source_of_truth:string
 * }
 */
function app_project_db_access_function_select_having_form_from_item(array $item): array
{
    return [
        'left_target_prefix' => $item['left_target_prefix'],
        'left_target_field_id' => $item['left_target_field_id'],
        'left_target_suffix' => $item['left_target_suffix'],
        'relational_operator' => $item['relational_operator'],
        'right_target_prefix' => $item['right_target_prefix'],
        'right_parameter_type' => $item['right_parameter_type'],
        'right_parameter_data_type' => $item['right_parameter_data_type'],
        'right_fixed_parameter' => $item['right_fixed_parameter'],
        'right_target_field_id' => $item['right_target_field_id'],
        'right_target_suffix' => $item['right_target_suffix'],
        'having_order' => $item['having_order'],
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
function app_render_project_db_access_function_select_having_edit_page(array $app, array $request): void
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
    $selectHavingKey = trim(app_route_param($request, 'select_having_key'));
    $isNew = $selectHavingKey === '';

    if (!$isNew && !ctype_digit($selectHavingKey)) {
        app_render_bad_request_page($app, $request, 'select having key の形式が不正です。');
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

    $catalogResult = app_fetch_db_access_function_select_having_catalog(
        $app,
        $projectKey,
        $entity['source_name'],
        $method['name'],
    );
    $catalogError = $catalogResult['ok'] ? '' : $catalogResult['error'];
    $catalogItems = $catalogResult['ok'] ? $catalogResult['items'] : [];

    $targetFieldCatalogResult = app_fetch_db_access_function_select_target_field_catalog(
        $app,
        $projectKey,
        $entity['source_name'],
        $method['name'],
    );
    $targetFieldCatalogError = $targetFieldCatalogResult['ok'] ? '' : $targetFieldCatalogResult['error'];
    $targetFieldCatalog = $targetFieldCatalogResult['ok'] ? $targetFieldCatalogResult['items'] : [];
    $targetFieldById = [];
    foreach ($targetFieldCatalog as $targetField) {
        $targetFieldById[$targetField['select_target_field_id']] = $targetField;
    }

    $legacySchema = app_project_db_access_legacy_metadata_schema($app, 'dafuncselecthaving');

    $currentItem = null;
    $currentItemError = '';
    if (!$isNew) {
        $currentItemResult = app_fetch_db_access_function_select_having_item(
            $app,
            $projectKey,
            $entity['source_name'],
            $method['name'],
            $selectHavingKey,
        );
        $currentItemError = $currentItemResult['ok'] ? '' : $currentItemResult['error'];
        $currentItem = $currentItemResult['ok'] ? $currentItemResult['item'] : null;

        if ($currentItemError === '' && $currentItem === null) {
            app_render_not_found_page($app, $request);
            return;
        }
    }

    $input = $currentItem !== null
        ? app_project_db_access_function_select_having_form_from_item($currentItem)
        : app_db_access_function_select_having_form_defaults();
    if ($isNew && $catalogError === '' && $catalogItems !== []) {
        $input['having_order'] = (string) count($catalogItems);
    }

    $errors = [];
    $warnings = [];
    $updated = app_query_param('updated') === '1';

    if ($currentItem !== null && !array_key_exists($currentItem['left_target_field_id'], $targetFieldById)) {
        $warnings[] = 'Left Target Field の参照先が `select-target-fields` catalog に存在しません。';
    }
    if (
        $currentItem !== null
        && $currentItem['right_parameter_type'] === 'field'
        && !array_key_exists($currentItem['right_target_field_id'], $targetFieldById)
    ) {
        $warnings[] = 'Right Target Field の参照先が `select-target-fields` catalog に存在しません。';
    }

    if (app_request_method_is($request, 'POST')) {
        $formAction = app_post_param('form_action', 'save');

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif ($canonicalFunctionError !== '') {
            $errors[] = $canonicalFunctionError;
        } elseif ($canonicalFunctionItem === null) {
            $errors[] = '先に function detail で canonical metadata を保存してください。';
        } elseif (!$isNew && trim(app_post_param('select_having_id')) !== $selectHavingKey) {
            $errors[] = '更新対象の select having key が route と一致しません。';
        } elseif ($formAction === 'delete') {
            if ($isNew) {
                $errors[] = '新規 row は削除できません。';
            } else {
                $deleteResult = app_delete_db_access_function_select_having(
                    $app,
                    $projectKey,
                    $entity['source_name'],
                    $method['name'],
                    $selectHavingKey,
                );
                if ($deleteResult['ok']) {
                    app_send_redirect_response(
                        $request,
                        '/projects/' . rawurlencode($projectKey)
                        . '/db-access/' . rawurlencode($entity['source_name'])
                        . '/functions/' . rawurlencode($method['name'])
                        . '/select-having?deleted=1',
                    );
                    return;
                }

                $errors[] = $deleteResult['error'];
            }
        } else {
            $validation = app_validate_db_access_function_select_having_form([
                'left_target_prefix' => app_post_param('left_target_prefix'),
                'left_target_field_id' => app_post_param('left_target_field_id', '0'),
                'left_target_suffix' => app_post_param('left_target_suffix'),
                'relational_operator' => app_post_param('relational_operator', '='),
                'right_target_prefix' => app_post_param('right_target_prefix'),
                'right_parameter_type' => app_post_param('right_parameter_type', 'argument'),
                'right_parameter_data_type' => app_post_param('right_parameter_data_type'),
                'right_fixed_parameter' => app_post_param('right_fixed_parameter'),
                'right_target_field_id' => app_post_param('right_target_field_id', '0'),
                'right_target_suffix' => app_post_param('right_target_suffix'),
                'having_order' => app_post_param('having_order', '0'),
                'source_of_truth' => 'manual',
            ]);
            $input = $validation['input'];
            $errors = array_merge($errors, $validation['errors']);

            if ($targetFieldCatalogError !== '') {
                $errors[] = $targetFieldCatalogError;
            } elseif ($targetFieldCatalog === []) {
                $errors[] = '先に select target fields designer で row を保存してください。';
            } else {
                if (!array_key_exists($input['left_target_field_id'], $targetFieldById)) {
                    $errors[] = 'Left Target Field が select target fields catalog に存在しません。';
                }
                if (
                    $input['right_parameter_type'] === 'field'
                    && !array_key_exists($input['right_target_field_id'], $targetFieldById)
                ) {
                    $errors[] = 'Right Target Field が select target fields catalog に存在しません。';
                }
            }

            if ($errors === []) {
                if ($isNew) {
                    $createResult = app_create_db_access_function_select_having($app, [
                        'project_key' => $projectKey,
                        'source_name' => $entity['source_name'],
                        'function_name' => $method['name'],
                        'left_target_prefix' => $input['left_target_prefix'],
                        'left_target_field_id' => $input['left_target_field_id'],
                        'left_target_suffix' => $input['left_target_suffix'],
                        'relational_operator' => $input['relational_operator'],
                        'right_target_prefix' => $input['right_target_prefix'],
                        'right_parameter_type' => $input['right_parameter_type'],
                        'right_parameter_data_type' => $input['right_parameter_data_type'],
                        'right_fixed_parameter' => $input['right_fixed_parameter'],
                        'right_target_field_id' => $input['right_target_field_id'],
                        'right_target_suffix' => $input['right_target_suffix'],
                        'having_order' => $input['having_order'],
                        'source_of_truth' => $input['source_of_truth'],
                    ]);

                    if ($createResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            '/projects/' . rawurlencode($projectKey)
                            . '/db-access/' . rawurlencode($entity['source_name'])
                            . '/functions/' . rawurlencode($method['name'])
                            . '/select-having/' . rawurlencode($createResult['item_id'])
                            . '?updated=1',
                        );
                        return;
                    }

                    $errors[] = $createResult['error'];
                } else {
                    $updateResult = app_update_db_access_function_select_having($app, [
                        'project_key' => $projectKey,
                        'source_name' => $entity['source_name'],
                        'function_name' => $method['name'],
                        'select_having_id' => $selectHavingKey,
                        'left_target_prefix' => $input['left_target_prefix'],
                        'left_target_field_id' => $input['left_target_field_id'],
                        'left_target_suffix' => $input['left_target_suffix'],
                        'relational_operator' => $input['relational_operator'],
                        'right_target_prefix' => $input['right_target_prefix'],
                        'right_parameter_type' => $input['right_parameter_type'],
                        'right_parameter_data_type' => $input['right_parameter_data_type'],
                        'right_fixed_parameter' => $input['right_fixed_parameter'],
                        'right_target_field_id' => $input['right_target_field_id'],
                        'right_target_suffix' => $input['right_target_suffix'],
                        'having_order' => $input['having_order'],
                        'source_of_truth' => $input['source_of_truth'],
                    ]);

                    if ($updateResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            '/projects/' . rawurlencode($projectKey)
                            . '/db-access/' . rawurlencode($entity['source_name'])
                            . '/functions/' . rawurlencode($method['name'])
                            . '/select-having/' . rawurlencode($selectHavingKey)
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
    $effectiveSelectHavingKey = !$isNew ? $selectHavingKey : '';

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Function Select Having Edit</title>
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
        .field-group {
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">functions</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>"><code><?php echo app_h($method['name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-having">select-having</a> / <?php echo app_h($isNew ? 'new' : $effectiveSelectHavingKey); ?></p>

    <h1><?php echo app_h($project['name']); ?> Select Having Edit</h1>
    <p><code>select having</code> 条件行を編集する画面です。左右の field は <code>select-target-fields</code> catalog から選択し、<code>having_order</code> は当面ここで直接入力します。</p>

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
                <li>select target fields row がないと新規保存できない</li>
                <li>aggregate / group-by と having の意味的整合はまだ未検証</li>
                <li>change-order / sort UI はまだ未実装</li>
            </ul>
        </section>
    </div>

    <?php if ($updated): ?>
        <div class="success">select having row を保存しました。</div>
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

    <?php if ($warnings !== []): ?>
        <div class="warning">
            <ul>
                <?php foreach ($warnings as $warning): ?>
                    <li><?php echo app_h($warning); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($catalogError !== ''): ?>
        <div class="error"><?php echo app_h($catalogError); ?></div>
    <?php endif; ?>

    <?php if ($targetFieldCatalogError !== ''): ?>
        <div class="error"><?php echo app_h($targetFieldCatalogError); ?></div>
    <?php endif; ?>

    <?php if ($currentItemError !== ''): ?>
        <div class="error"><?php echo app_h($currentItemError); ?></div>
    <?php endif; ?>

    <?php if ($targetFieldCatalog === [] && $targetFieldCatalogError === ''): ?>
        <div class="warning">
            <p>select target fields がまだ 0 件です。</p>
            <p><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-target-fields">select target fields designer</a> で row を先に保存してください。</p>
        </div>
    <?php endif; ?>

    <?php if ($canonicalFunctionItem !== null && $catalogError === '' && $targetFieldCatalogError === '' && $currentItemError === ''): ?>
        <form method="post" action="<?php
        echo $isNew
            ? '/projects/' . rawurlencode($projectKey) . '/db-access/' . rawurlencode($entity['source_name']) . '/functions/' . rawurlencode($method['name']) . '/select-having/new'
            : '/projects/' . rawurlencode($projectKey) . '/db-access/' . rawurlencode($entity['source_name']) . '/functions/' . rawurlencode($method['name']) . '/select-having/' . rawurlencode($effectiveSelectHavingKey);
        ?>">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="select_having_id" value="<?php echo app_h($effectiveSelectHavingKey); ?>">

            <label for="left_target_prefix">Left Target Prefix</label>
            <input id="left_target_prefix" name="left_target_prefix" value="<?php echo app_h($input['left_target_prefix']); ?>" placeholder="例: SUM(">

            <label for="left_target_field_id">Left Target Field</label>
            <select id="left_target_field_id" name="left_target_field_id">
                <option value="0">Select field</option>
                <?php foreach ($targetFieldCatalog as $targetField): ?>
                    <option value="<?php echo app_h($targetField['select_target_field_id']); ?>"<?php echo $input['left_target_field_id'] === $targetField['select_target_field_id'] ? ' selected' : ''; ?>>
                        <?php echo app_h(app_db_access_select_target_field_caption($targetField)); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="left_target_suffix">Left Target Suffix</label>
            <input id="left_target_suffix" name="left_target_suffix" value="<?php echo app_h($input['left_target_suffix']); ?>" placeholder="例: )">

            <label for="relational_operator">Relational Operator</label>
            <select id="relational_operator" name="relational_operator">
                <?php foreach (app_allowed_db_access_relational_operators() as $operator): ?>
                    <option value="<?php echo app_h($operator); ?>"<?php echo $input['relational_operator'] === $operator ? ' selected' : ''; ?>><?php echo app_h($operator); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="right_target_prefix">Right Target Prefix</label>
            <input id="right_target_prefix" name="right_target_prefix" value="<?php echo app_h($input['right_target_prefix']); ?>" placeholder="例: COALESCE(">

            <label for="right_parameter_type">Right Parameter Type</label>
            <select id="right_parameter_type" name="right_parameter_type">
                <?php foreach (app_allowed_db_access_select_having_parameter_types() as $parameterType): ?>
                    <option value="<?php echo app_h($parameterType); ?>"<?php echo $input['right_parameter_type'] === $parameterType ? ' selected' : ''; ?>>
                        <?php echo app_h(app_db_access_select_having_parameter_type_caption($parameterType)); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="field-group" id="right_parameter_data_type_group">
                <label for="right_parameter_data_type">Right Parameter Data Type</label>
                <select id="right_parameter_data_type" name="right_parameter_data_type">
                    <?php foreach (app_allowed_db_access_select_where_parameter_data_types() as $dataType): ?>
                        <option value="<?php echo app_h($dataType); ?>"<?php echo $input['right_parameter_data_type'] === $dataType ? ' selected' : ''; ?>>
                            <?php echo app_h(app_db_access_parameter_data_type_caption($dataType)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-group" id="right_fixed_parameter_group">
                <label for="right_fixed_parameter">Right Fixed Parameter</label>
                <textarea id="right_fixed_parameter" name="right_fixed_parameter" placeholder="例: 1, NOW(), CURRENT_DATE"><?php echo app_h($input['right_fixed_parameter']); ?></textarea>
            </div>

            <div class="field-group" id="right_target_field_group">
                <label for="right_target_field_id">Right Target Field</label>
                <select id="right_target_field_id" name="right_target_field_id">
                    <option value="0">Select field</option>
                    <?php foreach ($targetFieldCatalog as $targetField): ?>
                        <option value="<?php echo app_h($targetField['select_target_field_id']); ?>"<?php echo $input['right_target_field_id'] === $targetField['select_target_field_id'] ? ' selected' : ''; ?>>
                            <?php echo app_h(app_db_access_select_target_field_caption($targetField)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <label for="right_target_suffix">Right Target Suffix</label>
            <input id="right_target_suffix" name="right_target_suffix" value="<?php echo app_h($input['right_target_suffix']); ?>" placeholder="例: )">

            <label for="having_order">Having Order</label>
            <input id="having_order" name="having_order" type="number" min="0" value="<?php echo app_h($input['having_order']); ?>">

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
                <li>left target: <code><?php echo app_h(app_db_access_select_having_left_summary($input, $targetFieldById)); ?></code></li>
                <li>relation: <code><?php echo app_h($input['relational_operator']); ?></code></li>
                <li>right type: <code><?php echo app_h(app_db_access_select_having_parameter_type_caption($input['right_parameter_type'])); ?></code></li>
                <li>right target: <code><?php echo app_h(app_db_access_select_having_right_summary($input, $targetFieldById)); ?></code></li>
                <li>having order: <code><?php echo app_h($input['having_order']); ?></code></li>
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
            <h2>`data-dafuncselecthaving.php` Preview</h2>
            <pre><?php echo app_h($legacySchema['data_excerpt']); ?></pre>
        </section>
    <?php endif; ?>

    <p><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>/select-having">Back to Select Having List</a></p>
</main>
<script>
function updateSelectHavingFormVisibility() {
    const parameterType = document.getElementById('right_parameter_type');
    if (!parameterType) {
        return;
    }

    const value = parameterType.value;
    const dataTypeGroup = document.getElementById('right_parameter_data_type_group');
    const fixedGroup = document.getElementById('right_fixed_parameter_group');
    const fieldGroup = document.getElementById('right_target_field_group');

    if (dataTypeGroup) {
        dataTypeGroup.style.display = value === 'field' ? 'none' : 'block';
    }
    if (fixedGroup) {
        fixedGroup.style.display = value === 'fixed' ? 'block' : 'none';
    }
    if (fieldGroup) {
        fieldGroup.style.display = value === 'field' ? 'block' : 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const parameterType = document.getElementById('right_parameter_type');
    if (parameterType) {
        parameterType.addEventListener('change', updateSelectHavingFormVisibility);
    }
    updateSelectHavingFormVisibility();
});
</script>
</body>
</html>
    <?php
}
