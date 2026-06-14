<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/data_class_repository.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/project_data_class_route_common.php';

/**
 * @param array{
 *     name:string,
 *     store_base_path:string,
 *     is_autoload:string,
 *     inherit_parent_data_class_name:string
 * } $item
 * @return array{
 *     name:string,
 *     store_base_path:string,
 *     is_autoload:string,
 *     inherit_parent_data_class_name:string
 * }
 */
function app_project_data_class_form_from_item(array $item): array
{
    return [
        'name' => (string) ($item['name'] ?? ''),
        'store_base_path' => (string) ($item['store_base_path'] ?? ''),
        'is_autoload' => (string) ($item['is_autoload'] ?? '0'),
        'inherit_parent_data_class_name' => (string) ($item['inherit_parent_data_class_name'] ?? ''),
    ];
}

/**
 * @return array{
 *     name:string,
 *     store_base_path:string,
 *     is_autoload:string,
 *     inherit_parent_data_class_name:string
 * }
 */
function app_project_data_class_form_defaults(string $dataClassName = ''): array
{
    return [
        'name' => trim($dataClassName),
        'store_base_path' => '',
        'is_autoload' => '1',
        'inherit_parent_data_class_name' => '',
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
function app_render_project_data_class_edit_page(array $app, array $request): void
{
    $bootstrap = app_project_data_class_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $catalog = $bootstrap['generated_catalog'];
    $dataClassKey = trim(app_route_param($request, 'data_class_key'));
    if ($dataClassKey === '') {
        app_render_bad_request_page($app, $request, 'data class key が必要です。');
        return;
    }

    $canonicalItemResult = app_fetch_data_class_metadata_item($app, $projectKey, $dataClassKey);
    if (!$canonicalItemResult['ok']) {
        app_render_bad_request_page($app, $request, $canonicalItemResult['error']);
        return;
    }

    $canonicalItem = $canonicalItemResult['item'];
    $entity = app_generated_catalog_find_entity($catalog, $dataClassKey);
    if ($entity === null && $canonicalItem !== null) {
        $entity = app_generated_catalog_find_entity($catalog, $canonicalItem['name']);
    }

    if ($canonicalItem === null && $entity === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $isCreate = $canonicalItem === null;
    $effectiveDataClassName = $canonicalItem !== null
        ? $canonicalItem['name']
        : ($entity !== null ? $entity['source_name'] : $dataClassKey);
    $input = $canonicalItem !== null
        ? app_project_data_class_form_from_item($canonicalItem)
        : app_project_data_class_form_defaults($effectiveDataClassName);

    $errors = [];
    $updated = app_query_param('updated') === '1';
    $created = app_query_param('created') === '1';

    if (app_request_method_is($request, 'POST')) {
        $formAction = app_post_param('form_action', 'save');

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif (
            !$isCreate
            && trim(app_post_param('data_class_pid')) !== trim((string) $canonicalItem['pid'])
        ) {
            $errors[] = '更新対象の data class metadata が route と一致しません。';
        } elseif ($formAction === 'delete') {
            if ($isCreate) {
                $errors[] = '未作成の data class metadata は削除できません。';
            } else {
                $deleteResult = app_delete_data_class_metadata_item($app, $projectKey, (string) $canonicalItem['pid']);
                if ($deleteResult['ok']) {
                    $query = http_build_query([
                        'deleted' => '1',
                        'data_class' => $canonicalItem['name'],
                    ], '', '&', PHP_QUERY_RFC3986);
                    app_send_redirect_response($request, app_project_data_classes_path($projectKey) . '?' . $query);
                    return;
                }

                $errors[] = $deleteResult['error'];
            }
        } else {
            $validation = app_validate_data_class_metadata_item_form([
                'name' => app_post_param('name'),
                'store_base_path' => app_post_param('store_base_path'),
                'is_autoload' => app_post_param('is_autoload', '0'),
                'inherit_parent_data_class_name' => app_post_param('inherit_parent_data_class_name'),
            ]);
            $input = $validation['input'];
            $errors = array_merge($errors, $validation['errors']);

            if ($errors === []) {
                $duplicateCheck = app_fetch_data_class_metadata_item($app, $projectKey, $input['name']);
                if (!$duplicateCheck['ok']) {
                    $errors[] = $duplicateCheck['error'];
                } elseif (
                    $duplicateCheck['item'] !== null
                    && ($isCreate || $duplicateCheck['item']['pid'] !== $canonicalItem['pid'])
                ) {
                    $errors[] = '同名の data class metadata が既に存在します。';
                }
            }

            if ($errors === []) {
                if ($isCreate) {
                    $createResult = app_create_data_class_metadata_item($app, $projectKey, $input);
                    if ($createResult['ok'] && $createResult['item'] !== null) {
                        app_send_redirect_response(
                            $request,
                            app_project_data_class_edit_path($projectKey, $createResult['item']['name']) . '?created=1',
                        );
                        return;
                    }

                    $errors[] = $createResult['error'] !== ''
                        ? $createResult['error']
                        : 'data class metadata の作成に失敗しました。';
                } else {
                    $updateResult = app_update_data_class_metadata_item(
                        $app,
                        $projectKey,
                        (string) $canonicalItem['pid'],
                        $input,
                    );
                    if ($updateResult['ok'] && $updateResult['item'] !== null) {
                        app_send_redirect_response(
                            $request,
                            app_project_data_class_edit_path($projectKey, $updateResult['item']['name']) . '?updated=1',
                        );
                        return;
                    }

                    $errors[] = $updateResult['error'] !== ''
                        ? $updateResult['error']
                        : 'data class metadata の更新に失敗しました。';
                }
            }
        }
    }

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();
    $formActionPath = app_project_data_class_edit_path($projectKey, $dataClassKey);

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Data Class Edit</title>
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
            min-height: 6rem;
            resize: vertical;
        }
        .checkbox-row {
            display: flex;
            gap: 0.6rem;
            align-items: center;
            margin-top: 1rem;
        }
        .checkbox-row input {
            width: auto;
            margin: 0;
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
        .error-card {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .success-card {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="<?php echo app_h(app_project_data_classes_path($projectKey)); ?>">data-classes</a> / <code><?php echo app_h($effectiveDataClassName); ?></code> / edit</p>

    <h1><?php echo app_h($project['name']); ?> Data Class Edit</h1>
    <p>canonical <code>dataclass</code> を current route から直接編集します。bootstrap candidate がある場合は、それを参照しながら canonical row を作成・更新できます。</p>

    <?php if ($created): ?>
        <div class="success-card">data class metadata row を作成しました。</div>
    <?php elseif ($updated): ?>
        <div class="success-card">data class metadata row を更新しました。</div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="error-card">
            <h2>Validation Error</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Target</h2>
            <ul>
                <li>route key: <code><?php echo app_h($dataClassKey); ?></code></li>
                <li>mode: <code><?php echo app_h($isCreate ? 'create' : 'update'); ?></code></li>
                <?php if ($canonicalItem !== null): ?>
                    <li>PID: <code><?php echo app_h($canonicalItem['pid']); ?></code></li>
                    <li>field count: <code><?php echo app_h((string) count($canonicalItem['fields'])); ?></code></li>
                    <li>last modified: <code><?php echo app_h($canonicalItem['last_modified_dt']); ?></code></li>
                <?php else: ?>
                    <li>bootstrap candidate: <code><?php echo app_h($entity !== null ? 'yes' : 'no'); ?></code></li>
                <?php endif; ?>
            </ul>
        </section>

        <section class="note-card">
            <h2>Next</h2>
            <ul>
                <li><a href="<?php echo app_h(app_project_data_class_detail_path($projectKey, $effectiveDataClassName)); ?>">detail</a></li>
                <li><a href="<?php echo app_h(app_project_data_class_fields_path($projectKey, $effectiveDataClassName)); ?>">fields</a></li>
                <li><a href="<?php echo app_h(app_project_data_classes_sync_path($projectKey)); ?>">sync</a></li>
                <?php if ($canonicalItem !== null): ?>
                    <li><a href="<?php echo app_h(app_project_data_class_field_new_path($projectKey, $canonicalItem['name'])); ?>">new field</a></li>
                <?php endif; ?>
                <?php if ($entity !== null): ?>
                    <li><a href="<?php echo app_h(app_project_data_class_source_path($projectKey, $effectiveDataClassName)); ?>">source preview</a></li>
                <?php endif; ?>
            </ul>
        </section>

        <?php if ($entity !== null): ?>
            <section class="summary-card">
                <h2>Runtime Reference</h2>
                <ul>
                    <li>source name: <code><?php echo app_h($entity['source_name']); ?></code></li>
                    <li>data file: <?php echo $entity['has_data_file'] ? '<code>' . app_h($entity['data_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
                    <li>dbaccess file: <?php echo $entity['has_dbaccess_file'] ? '<code>' . app_h($entity['dbaccess_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
                </ul>
            </section>
        <?php endif; ?>
    </div>

    <form method="post" action="<?php echo app_h($formActionPath); ?>">
        <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
        <?php if ($canonicalItem !== null): ?>
            <input type="hidden" name="data_class_pid" value="<?php echo app_h($canonicalItem['pid']); ?>">
        <?php endif; ?>

        <label for="name">Data Class Name</label>
        <input id="name" name="name" type="text" value="<?php echo app_h($input['name']); ?>" required>

        <label for="inherit_parent_data_class_name">Inherit Parent Data Class Name</label>
        <input id="inherit_parent_data_class_name" name="inherit_parent_data_class_name" type="text" value="<?php echo app_h($input['inherit_parent_data_class_name']); ?>">

        <label for="store_base_path">StoreBasePath</label>
        <textarea id="store_base_path" name="store_base_path"><?php echo app_h($input['store_base_path']); ?></textarea>

        <div class="checkbox-row">
            <input id="is_autoload" name="is_autoload" type="checkbox" value="1"<?php echo $input['is_autoload'] === '1' ? ' checked' : ''; ?>>
            <label for="is_autoload">Include in Autoload</label>
        </div>

        <button type="submit" name="form_action" value="save"><?php echo app_h($isCreate ? 'Create Data Class' : 'Update Data Class'); ?></button>
        <?php if ($canonicalItem !== null): ?>
            <button class="danger" type="submit" name="form_action" value="delete" onclick="return confirm('この data class metadata row を削除します。よろしいですか。');">Delete</button>
        <?php endif; ?>
    </form>
</main>
</body>
</html>
    <?php
}
