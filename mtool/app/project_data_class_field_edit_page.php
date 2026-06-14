<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/data_class_repository.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/project_data_class_route_common.php';

/**
 * @param array{
 *     project_pid:string,
 *     dataclass_pid:string,
 *     pid:string,
 *     name:string,
 *     datatype:string,
 *     field_list_order:int,
 *     ref_data_class_name:string,
 *     ref_data_class_field_name:string
 * } $item
 * @return array{
 *     name:string,
 *     datatype:string,
 *     ref_data_class_name:string,
 *     ref_data_class_field_name:string
 * }
 */
function app_project_data_class_field_form_from_item(array $item): array
{
    return [
        'name' => (string) ($item['name'] ?? ''),
        'datatype' => (string) ($item['datatype'] ?? ''),
        'ref_data_class_name' => (string) ($item['ref_data_class_name'] ?? ''),
        'ref_data_class_field_name' => (string) ($item['ref_data_class_field_name'] ?? ''),
    ];
}

/**
 * @return array{
 *     name:string,
 *     datatype:string,
 *     ref_data_class_name:string,
 *     ref_data_class_field_name:string
 * }
 */
function app_project_data_class_field_form_defaults(): array
{
    return [
        'name' => '',
        'datatype' => '',
        'ref_data_class_name' => '',
        'ref_data_class_field_name' => '',
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
function app_render_project_data_class_field_edit_page(array $app, array $request): void
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

    $fieldKey = trim(app_route_param($request, 'field_key'));
    $isNew = $fieldKey === '';

    $canonicalItemResult = app_fetch_data_class_metadata_item($app, $projectKey, $dataClassKey);
    if (!$canonicalItemResult['ok']) {
        app_render_bad_request_page($app, $request, $canonicalItemResult['error']);
        return;
    }

    $canonicalDataClass = $canonicalItemResult['item'];
    $entity = app_generated_catalog_find_entity($catalog, $dataClassKey);
    if ($entity === null && $canonicalDataClass !== null) {
        $entity = app_generated_catalog_find_entity($catalog, $canonicalDataClass['name']);
    }

    if ($canonicalDataClass === null && $entity === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $currentItem = null;
    $currentItemError = '';
    if ($canonicalDataClass !== null && !$isNew) {
        $currentItemResult = app_fetch_data_class_metadata_field_item(
            $app,
            $projectKey,
            $canonicalDataClass['name'],
            $fieldKey,
        );
        $currentItemError = $currentItemResult['ok'] ? '' : $currentItemResult['error'];
        $currentItem = $currentItemResult['ok'] ? $currentItemResult['item'] : null;

        if ($currentItemError === '' && $currentItem === null) {
            app_render_not_found_page($app, $request);
            return;
        }
    }

    $input = $currentItem !== null
        ? app_project_data_class_field_form_from_item($currentItem)
        : app_project_data_class_field_form_defaults();

    $errors = [];
    $updated = app_query_param('updated') === '1';
    $created = app_query_param('created') === '1';

    if (app_request_method_is($request, 'POST')) {
        $formAction = app_post_param('form_action', 'save');

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif ($canonicalDataClass === null) {
            $errors[] = '先に data class metadata を作成してから field を編集してください。';
        } elseif (
            !$isNew
            && trim(app_post_param('field_pid')) !== trim((string) ($currentItem['pid'] ?? ''))
        ) {
            $errors[] = '更新対象の data class field metadata が route と一致しません。';
        } elseif ($currentItemError !== '') {
            $errors[] = $currentItemError;
        } elseif ($formAction === 'delete') {
            if ($isNew) {
                $errors[] = '未作成の data class field metadata は削除できません。';
            } else {
                $deleteResult = app_delete_data_class_metadata_field(
                    $app,
                    $projectKey,
                    (string) $currentItem['pid'],
                );
                if ($deleteResult['ok']) {
                    app_send_redirect_response(
                        $request,
                        app_project_data_class_fields_path($projectKey, $canonicalDataClass['name']) . '?deleted=1',
                    );
                    return;
                }

                $errors[] = $deleteResult['error'];
            }
        } else {
            $validation = app_validate_data_class_metadata_field_form([
                'name' => app_post_param('name'),
                'datatype' => app_post_param('datatype'),
                'ref_data_class_name' => app_post_param('ref_data_class_name'),
                'ref_data_class_field_name' => app_post_param('ref_data_class_field_name'),
            ]);
            $input = $validation['input'];
            $errors = array_merge($errors, $validation['errors']);

            if ($errors === []) {
                $duplicateCheck = app_fetch_data_class_metadata_field_item(
                    $app,
                    $projectKey,
                    $canonicalDataClass['name'],
                    $input['name'],
                );
                if (!$duplicateCheck['ok']) {
                    $errors[] = $duplicateCheck['error'];
                } elseif (
                    $duplicateCheck['item'] !== null
                    && ($isNew || $duplicateCheck['item']['pid'] !== $currentItem['pid'])
                ) {
                    $errors[] = '同名の data class field metadata が既に存在します。';
                }
            }

            if ($errors === []) {
                if ($isNew) {
                    $createResult = app_create_data_class_metadata_field(
                        $app,
                        $projectKey,
                        (string) $canonicalDataClass['pid'],
                        $input,
                    );
                    if ($createResult['ok'] && $createResult['item'] !== null) {
                        app_send_redirect_response(
                            $request,
                            app_project_data_class_field_edit_path(
                                $projectKey,
                                $canonicalDataClass['name'],
                                $createResult['item']['name'],
                            ) . '?created=1',
                        );
                        return;
                    }

                    $errors[] = $createResult['error'] !== ''
                        ? $createResult['error']
                        : 'data class field metadata の作成に失敗しました。';
                } else {
                    $updateResult = app_update_data_class_metadata_field(
                        $app,
                        $projectKey,
                        (string) $currentItem['pid'],
                        $input,
                    );
                    if ($updateResult['ok'] && $updateResult['item'] !== null) {
                        app_send_redirect_response(
                            $request,
                            app_project_data_class_field_edit_path(
                                $projectKey,
                                $canonicalDataClass['name'],
                                $updateResult['item']['name'],
                            ) . '?updated=1',
                        );
                        return;
                    }

                    $errors[] = $updateResult['error'] !== ''
                        ? $updateResult['error']
                        : 'data class field metadata の更新に失敗しました。';
                }
            }
        }
    }

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();
    $effectiveDataClassName = $canonicalDataClass !== null ? $canonicalDataClass['name'] : $dataClassKey;
    $effectiveFieldKey = !$isNew ? $fieldKey : 'new';
    $fieldListOrder = $currentItem !== null
        ? (string) $currentItem['field_list_order']
        : (string) (($canonicalDataClass !== null ? count($canonicalDataClass['fields']) : 0) + 1);
    $formActionPath = $isNew
        ? app_project_data_class_field_new_path($projectKey, $effectiveDataClassName)
        : app_project_data_class_field_edit_path($projectKey, $effectiveDataClassName, $fieldKey);

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Data Class Field Edit</title>
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
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="<?php echo app_h(app_project_data_classes_path($projectKey)); ?>">data-classes</a> / <a href="<?php echo app_h(app_project_data_class_detail_path($projectKey, $effectiveDataClassName)); ?>"><code><?php echo app_h($effectiveDataClassName); ?></code></a> / <a href="<?php echo app_h(app_project_data_class_fields_path($projectKey, $effectiveDataClassName)); ?>">fields</a> / <code><?php echo app_h($effectiveFieldKey); ?></code> / edit</p>

    <h1><?php echo app_h($project['name']); ?> Data Class Field Edit</h1>
    <p>canonical <code>dataclassfields</code> を current route から直接編集します。field の create/update/delete ごとに親 data class の <code>LastModifiedDT</code> も更新します。</p>

    <?php if ($created): ?>
        <div class="success-card">data class field metadata row を作成しました。</div>
    <?php elseif ($updated): ?>
        <div class="success-card">data class field metadata row を更新しました。</div>
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
                <li>data class: <code><?php echo app_h($effectiveDataClassName); ?></code></li>
                <li>mode: <code><?php echo app_h($isNew ? 'create' : 'update'); ?></code></li>
                <li>field order: <code><?php echo app_h($fieldListOrder); ?></code></li>
                <?php if ($currentItem !== null): ?>
                    <li>field PID: <code><?php echo app_h($currentItem['pid']); ?></code></li>
                <?php endif; ?>
            </ul>
        </section>

        <section class="note-card">
            <h2>Next</h2>
            <ul>
                <li><a href="<?php echo app_h(app_project_data_class_fields_path($projectKey, $effectiveDataClassName)); ?>">fields list</a></li>
                <li><a href="<?php echo app_h(app_project_data_class_edit_path($projectKey, $effectiveDataClassName)); ?>">data class edit</a></li>
                <li><a href="<?php echo app_h(app_project_data_classes_sync_path($projectKey)); ?>">sync</a></li>
                <?php if ($canonicalDataClass === null): ?>
                    <li><a href="<?php echo app_h(app_project_data_class_edit_path($projectKey, $effectiveDataClassName)); ?>">create canonical row first</a></li>
                <?php endif; ?>
            </ul>
        </section>

        <?php if ($entity !== null): ?>
            <section class="summary-card">
                <h2>Runtime Reference</h2>
                <ul>
                    <li>source name: <code><?php echo app_h($entity['source_name']); ?></code></li>
                    <li>data file: <?php echo $entity['has_data_file'] ? '<code>' . app_h($entity['data_file']) . '</code>' : '<span class="muted">none</span>'; ?></li>
                    <li>canonical row: <code><?php echo app_h($canonicalDataClass !== null ? 'yes' : 'no'); ?></code></li>
                </ul>
            </section>
        <?php endif; ?>
    </div>

    <form method="post" action="<?php echo app_h($formActionPath); ?>">
        <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
        <?php if ($currentItem !== null): ?>
            <input type="hidden" name="field_pid" value="<?php echo app_h($currentItem['pid']); ?>">
        <?php endif; ?>

        <label for="name">Field Name</label>
        <input id="name" name="name" type="text" value="<?php echo app_h($input['name']); ?>" required>

        <label for="datatype">Data Type</label>
        <input id="datatype" name="datatype" type="text" value="<?php echo app_h($input['datatype']); ?>" required>

        <label for="ref_data_class_name">RefDataClassName</label>
        <input id="ref_data_class_name" name="ref_data_class_name" type="text" value="<?php echo app_h($input['ref_data_class_name']); ?>">

        <label for="ref_data_class_field_name">RefDataClassFieldName</label>
        <input id="ref_data_class_field_name" name="ref_data_class_field_name" type="text" value="<?php echo app_h($input['ref_data_class_field_name']); ?>">

        <button type="submit" name="form_action" value="save"><?php echo app_h($isNew ? 'Create Field' : 'Update Field'); ?></button>
        <?php if ($currentItem !== null): ?>
            <button class="danger" type="submit" name="form_action" value="delete" onclick="return confirm('この data class field metadata row を削除します。よろしいですか。');">Delete</button>
        <?php endif; ?>
    </form>
</main>
</body>
</html>
    <?php
}
