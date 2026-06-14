<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/html_template_route_common.php';
require_once __DIR__ . '/project_html_route_common.php';
require_once __DIR__ . '/project_htmls_page.php';

function app_project_html_parameter_form_defaults(): array
{
    return [
        'legacy_parameter_pid' => '',
        'parameter_name' => '',
        'parameter_value' => '',
        'data_type' => '',
        'data_class_pid' => '0',
        'da_pid' => '0',
    ];
}

/**
 * @param list<array{
 *     legacy_data_class_pid:int,
 *     name:string,
 *     caption:string
 * }> $dataClassCatalog
 * @param list<array{
 *     legacy_da_pid:int,
 *     name:string,
 *     caption:string
 * }> $dbAccessCatalog
 * @param array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     legacy_parameter_pid:int,
 *     parameter_name:string,
 *     parameter_value:string
 * } $parameter
 */
function app_project_html_parameter_form_from_item(
    array $parameter,
    string $dataType,
    array $dataClassCatalog,
    array $dbAccessCatalog,
): array {
    $dataClassPid = '0';
    if (strcasecmp($dataType, 'dataclassname') === 0) {
        foreach ($dataClassCatalog as $item) {
            if ((string) ($item['caption'] ?? '') === (string) ($parameter['parameter_value'] ?? '')) {
                $dataClassPid = (string) ($item['legacy_data_class_pid'] ?? 0);
                break;
            }
        }
    }

    $daPid = '0';
    if (strcasecmp($dataType, 'dbaccessclassname') === 0) {
        foreach ($dbAccessCatalog as $item) {
            if ((string) ($item['caption'] ?? '') === (string) ($parameter['parameter_value'] ?? '')) {
                $daPid = (string) ($item['legacy_da_pid'] ?? 0);
                break;
            }
        }
    }

    return [
        'legacy_parameter_pid' => (string) ($parameter['legacy_parameter_pid'] ?? ''),
        'parameter_name' => (string) ($parameter['parameter_name'] ?? ''),
        'parameter_value' => (string) ($parameter['parameter_value'] ?? ''),
        'data_type' => $dataType,
        'data_class_pid' => $dataClassPid,
        'da_pid' => $daPid,
    ];
}

function app_project_html_parameter_form_from_post(array $fallback = []): array
{
    return [
        'legacy_parameter_pid' => app_post_param(
            'parameter_pid',
            (string) ($fallback['legacy_parameter_pid'] ?? ''),
        ),
        'parameter_name' => app_post_param(
            'parameter_name',
            (string) ($fallback['parameter_name'] ?? ''),
        ),
        'parameter_value' => app_post_param(
            'parameter_value',
            (string) ($fallback['parameter_value'] ?? ''),
        ),
        'data_type' => app_post_param(
            'data_type',
            (string) ($fallback['data_type'] ?? ''),
        ),
        'data_class_pid' => app_post_param(
            'data_class_pid',
            (string) ($fallback['data_class_pid'] ?? '0'),
        ),
        'da_pid' => app_post_param(
            'da_pid',
            (string) ($fallback['da_pid'] ?? '0'),
        ),
    ];
}

/**
 * @param list<array{
 *     expected_rows:list<array{
 *         template_parameter:array{
 *             legacy_html_template_pid:int,
 *             legacy_template_parameter_pid:int,
 *             parameter_name:string,
 *             target_value_type:string,
 *             target_variable_or_class_object:string,
 *             target_property_of_class_object:string,
 *             another_template_pid:int,
 *             trim_last_space:int,
 *             trim_last_return:int,
 *             data_type:string
 *         },
 *         parameter_name:string,
 *         data_type:string,
 *         data_type_caption:string,
 *         actual_item:array{
 *             project_pid:int,
 *             legacy_html_pid:int,
 *             legacy_parameter_pid:int,
 *             parameter_name:string,
 *             parameter_value:string
 *         }|null,
 *         duplicate_items:list<array{
 *             project_pid:int,
 *             legacy_html_pid:int,
 *             legacy_parameter_pid:int,
 *             parameter_name:string,
 *             parameter_value:string
 *         }>
 *     }>
 * } $audit
 */
function app_project_html_parameter_expected_row_by_name(array $audit, string $parameterName): ?array
{
    $normalizedParameterName = trim($parameterName);
    if ($normalizedParameterName === '') {
        return null;
    }

    foreach ($audit['expected_rows'] as $row) {
        if (strcasecmp((string) $row['parameter_name'], $normalizedParameterName) === 0) {
            return $row;
        }
    }

    return null;
}

/**
 * @param list<array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     legacy_parameter_pid:int,
 *     parameter_name:string,
 *     parameter_value:string
 * }> $parameterCatalog
 * @return array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     legacy_parameter_pid:int,
 *     parameter_name:string,
 *     parameter_value:string
 * }|null
 */
function app_project_html_parameter_item_by_pid(array $parameterCatalog, int $legacyParameterPid): ?array
{
    if ($legacyParameterPid <= 0) {
        return null;
    }

    foreach ($parameterCatalog as $parameter) {
        if ((int) ($parameter['legacy_parameter_pid'] ?? 0) === $legacyParameterPid) {
            return $parameter;
        }
    }

    return null;
}

/**
 * @param list<array{
 *     legacy_data_class_pid:int,
 *     name:string,
 *     caption:string
 * }> $dataClassCatalog
 * @param list<array{
 *     legacy_da_pid:int,
 *     name:string,
 *     caption:string
 * }> $dbAccessCatalog
 */
function app_project_html_parameter_resolved_value(
    array $input,
    array $dataClassCatalog,
    array $dbAccessCatalog,
): string {
    $resolvedValue = (string) ($input['parameter_value'] ?? '');
    $dataType = trim((string) ($input['data_type'] ?? ''));

    if (strcasecmp($dataType, 'dataclassname') === 0) {
        $selectedPid = trim((string) ($input['data_class_pid'] ?? '0'));
        foreach ($dataClassCatalog as $item) {
            if ((string) ($item['legacy_data_class_pid'] ?? 0) === $selectedPid) {
                return (string) ($item['caption'] ?? '');
            }
        }
        return $resolvedValue;
    }

    if (strcasecmp($dataType, 'dbaccessclassname') === 0) {
        $selectedPid = trim((string) ($input['da_pid'] ?? '0'));
        foreach ($dbAccessCatalog as $item) {
            if ((string) ($item['legacy_da_pid'] ?? 0) === $selectedPid) {
                return (string) ($item['caption'] ?? '');
            }
        }
        return $resolvedValue;
    }

    return $resolvedValue;
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_project_html_parameters_page(array $app, array $request): void
{
    $bootstrap = app_project_html_item_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $project = $bootstrap['project'];
    $projectKey = $bootstrap['project_key'];
    $html = $bootstrap['html'];
    $reference = $bootstrap['reference'];

    $errors = app_project_html_bridge_errors_from_request();

    $templateCatalogResult = app_fetch_project_html_template_catalog($app, $projectKey);
    if (!$templateCatalogResult['ok']) {
        $errors[] = $templateCatalogResult['error'];
    }
    $templateByPid = app_project_html_template_catalog_by_pid($templateCatalogResult['items']);
    $template = $templateByPid[(string) $html['legacy_html_template_pid']] ?? null;

    $parameterCatalogResult = app_fetch_project_html_parameter_catalog(
        $app,
        $projectKey,
        (int) $reference['project_pid'],
        (int) $html['legacy_html_pid'],
    );
    if (!$parameterCatalogResult['ok']) {
        $errors[] = $parameterCatalogResult['error'];
    }
    $parameterCatalog = $parameterCatalogResult['items'];

    $dataClassCatalogResult = app_fetch_project_html_dataclass_catalog(
        $app,
        $projectKey,
        (int) $reference['project_pid'],
    );
    if (!$dataClassCatalogResult['ok']) {
        $errors[] = $dataClassCatalogResult['error'];
    }
    $dataClassCatalog = $dataClassCatalogResult['items'];

    $dbAccessCatalogResult = app_fetch_project_html_db_access_catalog(
        $app,
        $projectKey,
        (int) $reference['project_pid'],
    );
    if (!$dbAccessCatalogResult['ok']) {
        $errors[] = $dbAccessCatalogResult['error'];
    }
    $dbAccessCatalog = $dbAccessCatalogResult['items'];

    $templateParameterCatalogResult = app_fetch_project_html_template_parameter_catalog(
        $app,
        (int) $html['legacy_html_template_pid'],
    );
    if (!$templateParameterCatalogResult['ok']) {
        $errors[] = $templateParameterCatalogResult['error'];
    }
    $templateParameterCatalog = $templateParameterCatalogResult['items'];

    $audit = app_html_template_parameter_audit_with_actual_items(
        $templateCatalogResult['items'],
        $templateParameterCatalog,
        $html,
        $parameterCatalog,
    );

    $selectedParameterPid = (int) trim(app_query_param('parameter_pid'));
    $selectedParameterName = trim(app_query_param('parameter_name'));
    $selectedDataType = trim(app_query_param('data_type'));
    $intent = trim(app_query_param('intent'));
    if ($intent === '') {
        if ($selectedParameterPid > 0) {
            $intent = 'edit';
        } elseif ($selectedParameterName !== '') {
            $intent = 'create';
        }
    }

    $selectedParameter = app_project_html_parameter_item_by_pid(
        $parameterCatalog,
        $selectedParameterPid,
    );
    if ($selectedDataType === '') {
        $expectedRow = app_project_html_parameter_expected_row_by_name(
            $audit,
            $selectedParameterName !== '' ? $selectedParameterName : (string) ($selectedParameter['parameter_name'] ?? ''),
        );
        if ($expectedRow !== null) {
            $selectedDataType = (string) ($expectedRow['data_type'] ?? '');
        }
    }

    $input = app_project_html_parameter_form_defaults();
    if ($selectedParameter !== null) {
        $input = app_project_html_parameter_form_from_item(
            $selectedParameter,
            $selectedDataType,
            $dataClassCatalog,
            $dbAccessCatalog,
        );
    } elseif ($selectedParameterName !== '') {
        $input['parameter_name'] = $selectedParameterName;
        $input['data_type'] = $selectedDataType;
    }

    $created = app_query_param('created') === '1';
    $updated = app_query_param('updated') === '1';
    $deleted = app_query_param('deleted') === '1';
    $action = trim(app_post_param('action'));

    if (app_request_method_is($request, 'POST')) {
        $input = app_project_html_parameter_form_from_post($input);

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif (trim(app_post_param('html_key')) !== '' && trim(app_post_param('html_key')) !== $html['html_key']) {
            $errors[] = '更新対象の html key が route と一致しません。';
        } elseif ($action === 'delete-parameter') {
            $deletePid = (int) $input['legacy_parameter_pid'];
            if ($deletePid <= 0) {
                $errors[] = '削除対象の htmlParameter PID が指定されていません。';
            } else {
                $deleteResult = app_delete_project_html_parameter(
                    $app,
                    $projectKey,
                    (int) $reference['project_pid'],
                    (int) $html['legacy_html_pid'],
                    $deletePid,
                );
                if ($deleteResult['ok']) {
                    $redirectQuery = http_build_query(
                        [
                            'deleted' => '1',
                            'parameter_name' => $input['parameter_name'],
                        ],
                        '',
                        '&',
                        PHP_QUERY_RFC3986,
                    );
                    app_send_redirect_response(
                        $request,
                        app_project_html_parameters_path($projectKey, $html['html_key']) . '?' . $redirectQuery,
                    );
                    return;
                }

                $errors[] = $deleteResult['error'] !== ''
                    ? $deleteResult['error']
                    : 'html parameter の削除に失敗しました。';
            }
        } elseif ($action === 'create-parameter' || $action === 'update-parameter') {
            $resolvedValue = app_project_html_parameter_resolved_value(
                $input,
                $dataClassCatalog,
                $dbAccessCatalog,
            );

            if ($action === 'update-parameter' && (int) $input['legacy_parameter_pid'] <= 0) {
                $errors[] = '更新対象の htmlParameter PID が指定されていません。';
            } else {
                $writeResult = $action === 'create-parameter'
                    ? app_create_project_html_parameter(
                        $app,
                        $projectKey,
                        [
                            'project_pid' => (int) $reference['project_pid'],
                            'legacy_html_pid' => (int) $html['legacy_html_pid'],
                            'parameter_name' => $input['parameter_name'],
                            'parameter_value' => $resolvedValue,
                        ],
                    )
                    : app_update_project_html_parameter(
                        $app,
                        $projectKey,
                        [
                            'project_pid' => (int) $reference['project_pid'],
                            'legacy_html_pid' => (int) $html['legacy_html_pid'],
                            'legacy_parameter_pid' => (int) $input['legacy_parameter_pid'],
                            'parameter_name' => $input['parameter_name'],
                            'parameter_value' => $resolvedValue,
                        ],
                    );

                if ($writeResult['ok'] && is_array($writeResult['item'])) {
                    $redirectQuery = http_build_query(
                        [
                            $action === 'create-parameter' ? 'created' : 'updated' => '1',
                            'intent' => 'edit',
                            'parameter_pid' => (string) $writeResult['item']['legacy_parameter_pid'],
                            'parameter_name' => $writeResult['item']['parameter_name'],
                            'data_type' => $input['data_type'],
                        ],
                        '',
                        '&',
                        PHP_QUERY_RFC3986,
                    );
                    app_send_redirect_response(
                        $request,
                        app_project_html_parameters_path($projectKey, $html['html_key']) . '?' . $redirectQuery,
                    );
                    return;
                }

                $errors[] = $writeResult['error'] !== ''
                    ? $writeResult['error']
                    : 'html parameter の保存に失敗しました。';
            }
        } elseif ($action !== '' || $errors === []) {
            $errors[] = '未対応の操作です。';
        }
    }

    $showEditor = $intent !== '' || $action !== '' || ($errors !== [] && !app_request_method_is($request, 'GET'));
    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project HTML Parameters</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 96rem;
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
        .summary-card, .note-card, .warning-card, .error-card, .success-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
        }
        .summary-card {
            background: #f8fafc;
        }
        .note-card {
            background: #eff6ff;
            border-color: #93c5fd;
        }
        .warning-card {
            background: #fefce8;
            border-color: #facc15;
        }
        .error-card {
            background: #fef2f2;
            border-color: #fca5a5;
        }
        .success-card {
            background: #dcfce7;
            border-color: #86efac;
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
        input, select, button {
            font: inherit;
        }
        input, select {
            width: 100%;
            box-sizing: border-box;
            margin-top: 0.35rem;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
        }
        .button-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        button {
            margin-top: 1rem;
            padding: 0.7rem 1rem;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
        }
        button.danger {
            background: #991b1b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            border-bottom: 1px solid #d7dde5;
            padding: 0.75rem;
            vertical-align: top;
            text-align: left;
        }
        tr.is-selected {
            background: #eff6ff;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="<?php echo app_h(app_project_htmls_path($projectKey)); ?>">html</a> / <a href="<?php echo app_h(app_project_html_detail_path($projectKey, $html['html_key'])); ?>"><code><?php echo app_h($html['html_key']); ?></code></a> / parameters</p>

    <h1><?php echo app_h($project['name']); ?> HTML Parameters</h1>
    <p>legacy <code>html_parameter_edit.php</code> の create/update/delete を current route に吸収した画面です。template metadata は canonical <code>html_templates</code> / <code>html_template_parameters</code> を優先し、actual rows は canonical <code>project_html_parameters</code> から読みます。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>HTML</h2>
            <ul>
                <li>html key: <code><?php echo app_h($html['html_key']); ?></code></li>
                <li>name: <code><?php echo app_h($html['name']); ?></code></li>
                <li>legacy PID: <code><?php echo app_h((string) $html['legacy_html_pid']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Template</h2>
            <ul>
                <?php if ($template !== null): ?>
                    <li>name: <code><?php echo app_h($template['name']); ?></code></li>
                    <li>legacy PID: <code><?php echo app_h((string) $template['legacy_html_template_pid']); ?></code></li>
                    <li>file: <code><?php echo app_h($template['file_name']); ?></code></li>
                <?php else: ?>
                    <li class="muted">template not found</li>
                <?php endif; ?>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Audit</h2>
            <ul>
                <li>expected rows: <code><?php echo app_h((string) $audit['expected_count']); ?></code></li>
                <li>actual rows: <code><?php echo app_h((string) $audit['actual_count']); ?></code></li>
                <li>missing count: <code><?php echo app_h((string) count($audit['missing_parameter_names'])); ?></code></li>
                <li>duplicate count: <code><?php echo app_h((string) count($audit['duplicate_parameter_names'])); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>Actions</h2>
            <ul>
                <li><a href="<?php echo app_h(app_project_html_parameters_path($projectKey, $html['html_key']) . '?intent=create'); ?>">add manual parameter</a></li>
                <?php if ($template !== null): ?>
                    <li><a href="<?php echo app_h(app_html_template_parameters_path((int) $template['legacy_html_template_pid'])); ?>">template parameter settings</a></li>
                <?php endif; ?>
                <li><a href="<?php echo app_h(app_project_html_detail_path($projectKey, $html['html_key'])); ?>">html detail</a></li>
                <li><a href="<?php echo app_h(app_project_htmls_path($projectKey)); ?>">back to html list</a></li>
            </ul>
        </section>
    </div>

    <?php if ($created): ?>
        <section class="success-card">
            <h2>Created</h2>
            <p>html parameter を追加しました。</p>
        </section>
    <?php endif; ?>

    <?php if ($updated): ?>
        <section class="success-card">
            <h2>Updated</h2>
            <p>html parameter を更新しました。</p>
        </section>
    <?php endif; ?>

    <?php if ($deleted): ?>
        <section class="success-card">
            <h2>Deleted</h2>
            <p>html parameter を削除しました。</p>
        </section>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <section class="error-card">
            <h2>Errors</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if ($showEditor): ?>
        <form method="post" action="<?php echo app_h(app_project_html_parameters_path($projectKey, $html['html_key'])); ?>">
            <h2><?php echo app_h($intent === 'edit' ? 'Edit Parameter' : 'Create Parameter'); ?></h2>
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="html_key" value="<?php echo app_h($html['html_key']); ?>">
            <input type="hidden" name="parameter_pid" value="<?php echo app_h($input['legacy_parameter_pid']); ?>">

            <label for="parameter-name">Parameter Name</label>
            <input id="parameter-name" type="text" name="parameter_name" value="<?php echo app_h($input['parameter_name']); ?>">

            <label for="data-type">Data Type</label>
            <select id="data-type" name="data_type">
                <option value=""<?php echo $input['data_type'] === '' ? ' selected' : ''; ?>><?php echo app_h(app_html_template_parameter_data_type_caption('')); ?></option>
                <option value="dataclassname"<?php echo strcasecmp($input['data_type'], 'dataclassname') === 0 ? ' selected' : ''; ?>><?php echo app_h(app_html_template_parameter_data_type_caption('dataclassname')); ?></option>
                <option value="dbaccessclassname"<?php echo strcasecmp($input['data_type'], 'dbaccessclassname') === 0 ? ' selected' : ''; ?>><?php echo app_h(app_html_template_parameter_data_type_caption('dbaccessclassname')); ?></option>
            </select>

            <label for="parameter-value">Parameter Value</label>
            <input id="parameter-value" type="text" name="parameter_value" value="<?php echo app_h($input['parameter_value']); ?>">

            <label for="data-class-pid">Data Class</label>
            <select id="data-class-pid" name="data_class_pid">
                <option value="0">(none)</option>
                <?php foreach ($dataClassCatalog as $item): ?>
                    <?php $optionValue = (string) ($item['legacy_data_class_pid'] ?? 0); ?>
                    <option value="<?php echo app_h($optionValue); ?>"<?php echo $input['data_class_pid'] === $optionValue ? ' selected' : ''; ?>>
                        <?php echo app_h((string) ($item['caption'] ?? '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="da-pid">DB Access Class</label>
            <select id="da-pid" name="da_pid">
                <option value="0">(none)</option>
                <?php foreach ($dbAccessCatalog as $item): ?>
                    <?php $optionValue = (string) ($item['legacy_da_pid'] ?? 0); ?>
                    <option value="<?php echo app_h($optionValue); ?>"<?php echo $input['da_pid'] === $optionValue ? ' selected' : ''; ?>>
                        <?php echo app_h((string) ($item['caption'] ?? '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="button-row">
                <button type="submit" name="action" value="<?php echo app_h($intent === 'edit' ? 'update-parameter' : 'create-parameter'); ?>">
                    <?php echo app_h($intent === 'edit' ? 'Update Parameter' : 'Add Parameter'); ?>
                </button>
                <?php if ($intent === 'edit' && $input['legacy_parameter_pid'] !== ''): ?>
                    <button class="danger" type="submit" name="action" value="delete-parameter" onclick="return confirm('Delete this html parameter?');">Delete Parameter</button>
                <?php endif; ?>
            </div>
        </form>
    <?php endif; ?>

    <?php if ($audit['template_duplicate_data_type_names'] !== [] || $audit['unexpected_items'] !== []): ?>
        <section class="warning-card">
            <h2>Warnings</h2>
            <ul>
                <?php if ($audit['template_duplicate_data_type_names'] !== []): ?>
                    <li>template duplicate data type mismatch: <code><?php echo app_h(implode(', ', $audit['template_duplicate_data_type_names'])); ?></code></li>
                <?php endif; ?>
                <?php if ($audit['unexpected_items'] !== []): ?>
                    <li>unused actual parameter rows: <code><?php echo app_h((string) count($audit['unexpected_items'])); ?></code></li>
                <?php endif; ?>
            </ul>
        </section>
    <?php endif; ?>

    <section>
        <h2>Expected Parameter Rows</h2>
        <table>
            <thead>
            <tr>
                <th>parameter</th>
                <th>data type</th>
                <th>current value</th>
                <th>status</th>
                <th>legacy ref</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($audit['expected_rows'] as $row): ?>
                <?php
                $rowParameterName = (string) $row['parameter_name'];
                $editQuery = [
                    'intent' => $row['actual_item'] !== null ? 'edit' : 'create',
                    'parameter_name' => $rowParameterName,
                    'data_type' => (string) $row['data_type'],
                ];
                if ($row['actual_item'] !== null) {
                    $editQuery['parameter_pid'] = (string) $row['actual_item']['legacy_parameter_pid'];
                }
                $editPath = app_project_html_parameters_path($projectKey, $html['html_key'])
                    . '?'
                    . http_build_query($editQuery, '', '&', PHP_QUERY_RFC3986);

                $isSelected = false;
                if ($selectedParameterPid > 0 && $row['actual_item'] !== null) {
                    $isSelected = $row['actual_item']['legacy_parameter_pid'] === $selectedParameterPid;
                }
                if (!$isSelected && $selectedParameterName !== '') {
                    $isSelected = strcasecmp($rowParameterName, $selectedParameterName) === 0;
                }
                ?>
                <tr<?php echo $isSelected ? ' class="is-selected"' : ''; ?>>
                    <td><code><?php echo app_h($rowParameterName); ?></code></td>
                    <td><?php echo app_h($row['data_type_caption']); ?></td>
                    <td>
                        <?php if ($row['actual_item'] !== null): ?>
                            <code><?php echo app_h($row['actual_item']['parameter_value']); ?></code>
                        <?php else: ?>
                            <span class="muted">not set</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['actual_item'] === null): ?>
                            <span class="muted">missing</span>
                        <?php elseif ($row['duplicate_items'] !== []): ?>
                            <span class="muted">duplicated</span>
                        <?php else: ?>
                            <span class="muted">set</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        template parameter pid:
                        <code><?php echo app_h((string) $row['template_parameter']['legacy_template_parameter_pid']); ?></code>
                        <?php if ($row['actual_item'] !== null): ?>
                            <br>htmlParameter pid:
                            <code><?php echo app_h((string) $row['actual_item']['legacy_parameter_pid']); ?></code>
                        <?php endif; ?>
                        <?php if ($row['duplicate_items'] !== []): ?>
                            <br>duplicate htmlParameter pids:
                            <code><?php echo app_h(implode(', ', array_map(static fn (array $item): string => (string) $item['legacy_parameter_pid'], $row['duplicate_items']))); ?></code>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo app_h($editPath); ?>">edit</a>
                        <?php if ($row['duplicate_items'] !== []): ?>
                            <?php foreach ($row['duplicate_items'] as $duplicateItem): ?>
                                <?php
                                $duplicatePath = app_project_html_parameters_path($projectKey, $html['html_key'])
                                    . '?'
                                    . http_build_query(
                                        [
                                            'intent' => 'edit',
                                            'parameter_pid' => (string) $duplicateItem['legacy_parameter_pid'],
                                            'parameter_name' => $duplicateItem['parameter_name'],
                                            'data_type' => (string) $row['data_type'],
                                        ],
                                        '',
                                        '&',
                                        PHP_QUERY_RFC3986,
                                    );
                                ?>
                                <br><a href="<?php echo app_h($duplicatePath); ?>">edit duplicate <?php echo app_h((string) $duplicateItem['legacy_parameter_pid']); ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <?php if ($audit['unexpected_items'] !== []): ?>
        <section>
            <h2>Unused Actual Rows</h2>
            <table>
                <thead>
                <tr>
                    <th>parameter</th>
                    <th>value</th>
                    <th>legacy pid</th>
                    <th>action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($audit['unexpected_items'] as $item): ?>
                    <?php
                    $isSelected = $selectedParameterPid > 0
                        && $item['legacy_parameter_pid'] === $selectedParameterPid;
                    if (!$isSelected && $selectedParameterName !== '') {
                        $isSelected = strcasecmp($item['parameter_name'], $selectedParameterName) === 0;
                    }
                    $editPath = app_project_html_parameters_path($projectKey, $html['html_key'])
                        . '?'
                        . http_build_query(
                            [
                                'intent' => 'edit',
                                'parameter_pid' => (string) $item['legacy_parameter_pid'],
                                'parameter_name' => $item['parameter_name'],
                            ],
                            '',
                            '&',
                            PHP_QUERY_RFC3986,
                        );
                    ?>
                    <tr<?php echo $isSelected ? ' class="is-selected"' : ''; ?>>
                        <td><code><?php echo app_h($item['parameter_name']); ?></code></td>
                        <td><code><?php echo app_h($item['parameter_value']); ?></code></td>
                        <td><code><?php echo app_h((string) $item['legacy_parameter_pid']); ?></code></td>
                        <td><a href="<?php echo app_h($editPath); ?>">edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
