<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/project_table_route_common.php';
require_once __DIR__ . '/table_metadata_repository.php';

/**
 * @param array{
 *     name:string
 * } $item
 * @return array{
 *     name:string
 * }
 */
function app_project_table_form_from_item(array $item): array
{
    return [
        'name' => (string) ($item['name'] ?? ''),
    ];
}

/**
 * @return array{
 *     name:string
 * }
 */
function app_project_table_form_defaults(string $tableName = ''): array
{
    return [
        'name' => trim($tableName),
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
function app_render_project_table_edit_page(array $app, array $request): void
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

    $canonicalItemResult = app_fetch_table_metadata_item($app, $projectKey, $tableKey);
    if (!$canonicalItemResult['ok']) {
        app_render_bad_request_page($app, $request, $canonicalItemResult['error']);
        return;
    }

    $canonicalItem = $canonicalItemResult['item'];
    $entity = app_generated_catalog_find_entity($catalog, $tableKey);
    if ($entity === null && $canonicalItem !== null) {
        $entity = app_generated_catalog_find_entity($catalog, $canonicalItem['name']);
    }

    if ($canonicalItem === null && $entity === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $isCreate = $canonicalItem === null;
    $effectiveTableName = $canonicalItem !== null
        ? $canonicalItem['name']
        : ($entity !== null ? $entity['source_name'] : $tableKey);
    $input = $canonicalItem !== null
        ? app_project_table_form_from_item($canonicalItem)
        : app_project_table_form_defaults($effectiveTableName);

    $errors = [];
    $updated = app_query_param('updated') === '1';
    $created = app_query_param('created') === '1';

    if (app_request_method_is($request, 'POST')) {
        $formAction = app_post_param('form_action', 'save');

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif (!$isCreate && trim(app_post_param('table_pid')) !== trim((string) $canonicalItem['pid'])) {
            $errors[] = '更新対象の table metadata が route と一致しません。';
        } elseif ($formAction === 'delete') {
            if ($isCreate) {
                $errors[] = '未作成の table metadata は削除できません。';
            } else {
                $deleteResult = app_delete_table_metadata_item($app, $projectKey, (string) $canonicalItem['pid']);
                if ($deleteResult['ok']) {
                    $query = http_build_query([
                        'deleted' => '1',
                        'table' => $canonicalItem['name'],
                    ], '', '&', PHP_QUERY_RFC3986);
                    app_send_redirect_response($request, app_project_tables_path($projectKey) . '?' . $query);
                    return;
                }

                $errors[] = $deleteResult['error'];
            }
        } else {
            $validation = app_validate_table_metadata_item_form([
                'name' => app_post_param('name'),
            ]);
            $input = $validation['input'];
            $errors = array_merge($errors, $validation['errors']);

            if ($errors === []) {
                $duplicateCheck = app_fetch_table_metadata_item($app, $projectKey, $input['name']);
                if (!$duplicateCheck['ok']) {
                    $errors[] = $duplicateCheck['error'];
                } elseif (
                    $duplicateCheck['item'] !== null
                    && ($isCreate || $duplicateCheck['item']['pid'] !== $canonicalItem['pid'])
                ) {
                    $errors[] = '同名の table metadata が既に存在します。';
                }
            }

            if ($errors === []) {
                if ($isCreate) {
                    $createResult = app_create_table_metadata_item($app, $projectKey, $input['name']);
                    if ($createResult['ok'] && $createResult['item'] !== null) {
                        app_send_redirect_response(
                            $request,
                            app_project_table_edit_path($projectKey, $createResult['item']['name']) . '?created=1',
                        );
                        return;
                    }

                    $errors[] = $createResult['error'] !== ''
                        ? $createResult['error']
                        : 'table metadata の作成に失敗しました。';
                } else {
                    $updateResult = app_update_table_metadata_item(
                        $app,
                        $projectKey,
                        (string) $canonicalItem['pid'],
                        $input['name'],
                    );
                    if ($updateResult['ok'] && $updateResult['item'] !== null) {
                        app_send_redirect_response(
                            $request,
                            app_project_table_edit_path($projectKey, $updateResult['item']['name']) . '?updated=1',
                        );
                        return;
                    }

                    $errors[] = $updateResult['error'] !== ''
                        ? $updateResult['error']
                        : 'table metadata の更新に失敗しました。';
                }
            }
        }
    }

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();
    $formActionPath = app_project_table_edit_path($projectKey, $tableKey);

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Table Edit</title>
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
        input {
            width: 100%;
            box-sizing: border-box;
            margin-top: 0.35rem;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            font: inherit;
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
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="<?php echo app_h(app_project_tables_path($projectKey)); ?>">tables</a> / <a href="<?php echo app_h(app_project_table_detail_path($projectKey, $effectiveTableName)); ?>"><code><?php echo app_h($effectiveTableName); ?></code></a> / edit</p>

    <h1><?php echo app_h($project['name']); ?> Table Edit</h1>
    <p>canonical <code>dbtable</code> row を作成または編集する画面です。table 名の変更と削除はここで行い、column は別 route で管理します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Table Status</h2>
            <ul>
                <li>mode: <code><?php echo app_h($isCreate ? 'create canonical row' : 'edit canonical row'); ?></code></li>
                <li>route key: <code><?php echo app_h($tableKey); ?></code></li>
                <li>canonical PID: <?php echo $canonicalItem !== null ? '<code>' . app_h((string) $canonicalItem['pid']) . '</code>' : '<span class="muted">not created</span>'; ?></li>
                <li>column count: <code><?php echo app_h((string) count($canonicalItem['columns'] ?? [])); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Runtime Reference</h2>
            <?php if ($entity !== null): ?>
                <ul>
                    <li>source name: <code><?php echo app_h($entity['source_name']); ?></code></li>
                    <li>data file: <?php echo $entity['has_data_file'] ? '<code>' . app_h($entity['data_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
                    <li>dbaccess file: <?php echo $entity['has_dbaccess_file'] ? '<code>' . app_h($entity['dbaccess_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
                </ul>
            <?php else: ?>
                <p class="muted">この table に対応する runtime reference entry はありません。</p>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>Next</h2>
            <ul>
                <li><a href="<?php echo app_h(app_project_table_detail_path($projectKey, $effectiveTableName)); ?>">table detail</a></li>
                <li><a href="<?php echo app_h(app_project_table_columns_path($projectKey, $effectiveTableName)); ?>">column detail</a></li>
                <li><a href="<?php echo app_h(app_project_tables_path($projectKey) . '/import'); ?>">import</a></li>
            </ul>
        </section>
    </div>

    <?php if ($created): ?>
        <div class="success">table metadata row を作成しました。</div>
    <?php endif; ?>

    <?php if ($updated): ?>
        <div class="success">table metadata row を更新しました。</div>
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

    <form method="post" action="<?php echo app_h($formActionPath); ?>">
        <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
        <input type="hidden" name="table_pid" value="<?php echo app_h((string) ($canonicalItem['pid'] ?? '')); ?>">

        <label for="name">Table Name</label>
        <input id="name" name="name" value="<?php echo app_h($input['name']); ?>" placeholder="例: project_source_outputs">

        <button type="submit" name="form_action" value="save"><?php echo app_h($isCreate ? '作成' : '保存'); ?></button>
        <?php if (!$isCreate): ?>
            <button type="submit" name="form_action" value="delete" class="danger" onclick="return confirm('この table metadata を削除しますか？');">削除</button>
        <?php endif; ?>
    </form>
</main>
</body>
</html>
    <?php
}
