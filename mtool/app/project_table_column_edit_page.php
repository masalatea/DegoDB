<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/project_table_route_common.php';
require_once __DIR__ . '/table_metadata_repository.php';

/**
 * @param array{
 *     project_pid:string,
 *     dbtable_pid:string,
 *     pid:string,
 *     name:string,
 *     datatype:string,
 *     is_null:string,
 *     is_key:string,
 *     is_default:string,
 *     extra:string,
 *     column_list_order:int,
 *     memo:string
 * } $item
 * @return array{
 *     name:string,
 *     datatype:string,
 *     is_null:string,
 *     is_key:string,
 *     is_default:string,
 *     extra:string,
 *     memo:string
 * }
 */
function app_project_table_column_form_from_item(array $item): array
{
    return [
        'name' => (string) ($item['name'] ?? ''),
        'datatype' => (string) ($item['datatype'] ?? ''),
        'is_null' => (string) ($item['is_null'] ?? ''),
        'is_key' => (string) ($item['is_key'] ?? ''),
        'is_default' => (string) ($item['is_default'] ?? ''),
        'extra' => (string) ($item['extra'] ?? ''),
        'memo' => (string) ($item['memo'] ?? ''),
    ];
}

/**
 * @return array{
 *     name:string,
 *     datatype:string,
 *     is_null:string,
 *     is_key:string,
 *     is_default:string,
 *     extra:string,
 *     memo:string
 * }
 */
function app_project_table_column_form_defaults(): array
{
    return [
        'name' => '',
        'datatype' => '',
        'is_null' => '',
        'is_key' => '',
        'is_default' => '',
        'extra' => '',
        'memo' => '',
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
function app_render_project_table_column_edit_page(array $app, array $request): void
{
    $bootstrap = app_project_table_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $catalog = $bootstrap['generated_catalog'];
    $tableKey = trim(app_route_param($request, 'table_key'));
    if ($tableKey === '') {
        app_render_bad_request_page($app, $request, 'table key が必要です。');
        return;
    }

    $columnKey = trim(app_route_param($request, 'column_key'));
    $isNew = $columnKey === '';

    $canonicalItemResult = app_fetch_table_metadata_item($app, $projectKey, $tableKey);
    if (!$canonicalItemResult['ok']) {
        app_render_bad_request_page($app, $request, $canonicalItemResult['error']);
        return;
    }

    $canonicalTable = $canonicalItemResult['item'];
    $entity = app_generated_catalog_find_entity($catalog, $tableKey);
    if ($entity === null && $canonicalTable !== null) {
        $entity = app_generated_catalog_find_entity($catalog, $canonicalTable['name']);
    }

    if ($canonicalTable === null && $entity === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $currentItem = null;
    $currentItemError = '';
    if ($canonicalTable !== null && !$isNew) {
        $currentItemResult = app_fetch_table_metadata_column_item($app, $projectKey, $canonicalTable['name'], $columnKey);
        $currentItemError = $currentItemResult['ok'] ? '' : $currentItemResult['error'];
        $currentItem = $currentItemResult['ok'] ? $currentItemResult['item'] : null;

        if ($currentItemError === '' && $currentItem === null) {
            app_render_not_found_page($app, $request);
            return;
        }
    }

    $input = $currentItem !== null
        ? app_project_table_column_form_from_item($currentItem)
        : app_project_table_column_form_defaults();

    $errors = [];
    $updated = app_query_param('updated') === '1';
    $created = app_query_param('created') === '1';

    if (app_request_method_is($request, 'POST')) {
        $formAction = app_post_param('form_action', 'save');

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif ($canonicalTable === null) {
            $errors[] = '先に table metadata を作成するか import を実行してください。';
        } elseif (!$isNew && trim(app_post_param('column_pid')) !== trim((string) ($currentItem['pid'] ?? ''))) {
            $errors[] = '更新対象の column metadata が route と一致しません。';
        } elseif ($currentItemError !== '') {
            $errors[] = $currentItemError;
        } elseif ($formAction === 'delete') {
            if ($isNew) {
                $errors[] = '未作成の column metadata は削除できません。';
            } else {
                $deleteResult = app_delete_table_metadata_column(
                    $app,
                    $projectKey,
                    (string) $currentItem['pid'],
                );
                if ($deleteResult['ok']) {
                    app_send_redirect_response(
                        $request,
                        app_project_table_columns_path($projectKey, $canonicalTable['name']) . '?deleted=1',
                    );
                    return;
                }

                $errors[] = $deleteResult['error'];
            }
        } else {
            $validation = app_validate_table_metadata_column_form([
                'name' => app_post_param('name'),
                'datatype' => app_post_param('datatype'),
                'is_null' => app_post_param('is_null'),
                'is_key' => app_post_param('is_key'),
                'is_default' => app_post_param('is_default'),
                'extra' => app_post_param('extra'),
                'memo' => app_post_param('memo'),
            ]);
            $input = $validation['input'];
            $errors = array_merge($errors, $validation['errors']);

            if ($errors === []) {
                $duplicateCheck = app_fetch_table_metadata_column_item(
                    $app,
                    $projectKey,
                    $canonicalTable['name'],
                    $input['name'],
                );
                if (!$duplicateCheck['ok']) {
                    $errors[] = $duplicateCheck['error'];
                } elseif (
                    $duplicateCheck['item'] !== null
                    && ($isNew || $duplicateCheck['item']['pid'] !== $currentItem['pid'])
                ) {
                    $errors[] = '同名の column metadata が既に存在します。';
                }
            }

            if ($errors === []) {
                if ($isNew) {
                    $createResult = app_create_table_metadata_column(
                        $app,
                        $projectKey,
                        (string) $canonicalTable['pid'],
                        $input,
                    );
                    if ($createResult['ok'] && $createResult['item'] !== null) {
                        app_send_redirect_response(
                            $request,
                            app_project_table_column_edit_path(
                                $projectKey,
                                $canonicalTable['name'],
                                $createResult['item']['name'],
                            ) . '?created=1',
                        );
                        return;
                    }

                    $errors[] = $createResult['error'] !== ''
                        ? $createResult['error']
                        : 'column metadata の作成に失敗しました。';
                } else {
                    $updateResult = app_update_table_metadata_column(
                        $app,
                        $projectKey,
                        (string) $currentItem['pid'],
                        $input,
                    );
                    if ($updateResult['ok'] && $updateResult['item'] !== null) {
                        app_send_redirect_response(
                            $request,
                            app_project_table_column_edit_path(
                                $projectKey,
                                $canonicalTable['name'],
                                $updateResult['item']['name'],
                            ) . '?updated=1',
                        );
                        return;
                    }

                    $errors[] = $updateResult['error'] !== ''
                        ? $updateResult['error']
                        : 'column metadata の更新に失敗しました。';
                }
            }
        }
    }

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();
    $effectiveTableName = $canonicalTable !== null ? $canonicalTable['name'] : $tableKey;
    $effectiveColumnKey = !$isNew ? $columnKey : 'new';
    $columnListOrder = $currentItem !== null
        ? (string) $currentItem['column_list_order']
        : (string) (($canonicalTable !== null ? count($canonicalTable['columns']) : 0) + 1);
    $formActionPath = $isNew
        ? app_project_table_column_new_path($projectKey, $effectiveTableName)
        : app_project_table_column_edit_path($projectKey, $effectiveTableName, $columnKey);

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Table Column Edit</title>
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
        input, textarea {
            width: 100%;
            box-sizing: border-box;
            margin-top: 0.35rem;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            font: inherit;
        }
        textarea {
            min-height: 7rem;
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
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="<?php echo app_h(app_project_tables_path($projectKey)); ?>">tables</a> / <a href="<?php echo app_h(app_project_table_detail_path($projectKey, $effectiveTableName)); ?>"><code><?php echo app_h($effectiveTableName); ?></code></a> / <a href="<?php echo app_h(app_project_table_columns_path($projectKey, $effectiveTableName)); ?>">columns</a> / <?php echo app_h($effectiveColumnKey); ?></p>

    <h1><?php echo app_h($project['name']); ?> Table Column Edit</h1>
    <p>canonical <code>dbtablecolumns</code> row を作成または編集する画面です。<code>ColumnListOrder</code> は現在 append-only とし、この画面では直接変更しません。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Table</h2>
            <ul>
                <li>name: <code><?php echo app_h($effectiveTableName); ?></code></li>
                <li>canonical PID: <?php echo $canonicalTable !== null ? '<code>' . app_h((string) $canonicalTable['pid']) . '</code>' : '<span class="muted">not created</span>'; ?></li>
                <li>column count: <code><?php echo app_h((string) count($canonicalTable['columns'] ?? [])); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Column Status</h2>
            <ul>
                <li>mode: <code><?php echo app_h($isNew ? 'new column' : 'edit column'); ?></code></li>
                <li>route key: <code><?php echo app_h($columnKey !== '' ? $columnKey : 'new'); ?></code></li>
                <li>canonical PID: <?php echo $currentItem !== null ? '<code>' . app_h((string) $currentItem['pid']) . '</code>' : '<span class="muted">not created</span>'; ?></li>
                <li>column order: <code><?php echo app_h($columnListOrder); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>Next</h2>
            <ul>
                <li><a href="<?php echo app_h(app_project_table_columns_path($projectKey, $effectiveTableName)); ?>">column detail</a></li>
                <li><a href="<?php echo app_h(app_project_table_edit_path($projectKey, $effectiveTableName)); ?>">table edit</a></li>
                <li><a href="<?php echo app_h(app_project_tables_path($projectKey) . '/import'); ?>">import</a></li>
            </ul>
        </section>
    </div>

    <?php if ($created): ?>
        <div class="success">column metadata row を作成しました。</div>
    <?php endif; ?>

    <?php if ($updated): ?>
        <div class="success">column metadata row を更新しました。</div>
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

    <?php if ($currentItemError !== ''): ?>
        <div class="error"><?php echo app_h($currentItemError); ?></div>
    <?php endif; ?>

    <?php if ($canonicalTable === null): ?>
        <section class="note-card">
            <h2>Canonical Table Required</h2>
            <p>この table にはまだ canonical <code>dbtable</code> row がありません。先に table edit で row を作成するか、import で canonical metadata を投入してください。</p>
            <ul>
                <li><a href="<?php echo app_h(app_project_table_edit_path($projectKey, $effectiveTableName)); ?>">create canonical row</a></li>
                <li><a href="<?php echo app_h(app_project_tables_path($projectKey) . '/import'); ?>">import</a></li>
                <li><a href="<?php echo app_h(app_project_table_columns_path($projectKey, $effectiveTableName)); ?>">column preview</a></li>
            </ul>
        </section>
    <?php else: ?>
        <form method="post" action="<?php echo app_h($formActionPath); ?>">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="column_pid" value="<?php echo app_h((string) ($currentItem['pid'] ?? '')); ?>">

            <label for="name">Column Name</label>
            <input id="name" name="name" value="<?php echo app_h($input['name']); ?>" placeholder="例: project_key">

            <label for="datatype">Datatype</label>
            <input id="datatype" name="datatype" value="<?php echo app_h($input['datatype']); ?>" placeholder="例: varchar(255)">

            <label for="is_null">IsNull</label>
            <input id="is_null" name="is_null" value="<?php echo app_h($input['is_null']); ?>" placeholder="例: YES / NO">

            <label for="is_key">IsKey</label>
            <input id="is_key" name="is_key" value="<?php echo app_h($input['is_key']); ?>" placeholder="例: PRI">

            <label for="is_default">IsDefault</label>
            <input id="is_default" name="is_default" value="<?php echo app_h($input['is_default']); ?>" placeholder="例: CURRENT_TIMESTAMP">

            <label for="extra">Extra</label>
            <input id="extra" name="extra" value="<?php echo app_h($input['extra']); ?>" placeholder="例: auto_increment">

            <label for="memo">Memo</label>
            <textarea id="memo" name="memo" placeholder="補足メモ"><?php echo app_h($input['memo']); ?></textarea>

            <button type="submit" name="form_action" value="save"><?php echo app_h($isNew ? '作成' : '保存'); ?></button>
            <?php if (!$isNew): ?>
                <button type="submit" name="form_action" value="delete" class="danger" onclick="return confirm('この column metadata を削除しますか？');">削除</button>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
