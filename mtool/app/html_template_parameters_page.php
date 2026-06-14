<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/html_template_route_common.php';

function app_html_template_parameter_form_defaults(string $targetType = 'html'): array
{
    return [
        'legacy_template_parameter_pid' => '',
        'parameter_name' => '',
        'target_value_type' => $targetType === 'html' ? 'EachHTML' : 'code',
        'target_variable_or_class_object' => '',
        'target_property_of_class_object' => '',
        'another_template_pid' => '0',
        'trim_last_space' => '0',
        'trim_last_return' => '0',
        'data_type' => '',
    ];
}

/**
 * @param array{
 *     legacy_html_template_pid:int,
 *     legacy_template_parameter_pid:int,
 *     parameter_name:string,
 *     target_value_type:string,
 *     target_variable_or_class_object:string,
 *     target_property_of_class_object:string,
 *     another_template_pid:int,
 *     trim_last_space:int,
 *     trim_last_return:int,
 *     data_type:string
 * } $parameter
 * @return array{
 *     legacy_template_parameter_pid:string,
 *     parameter_name:string,
 *     target_value_type:string,
 *     target_variable_or_class_object:string,
 *     target_property_of_class_object:string,
 *     another_template_pid:string,
 *     trim_last_space:string,
 *     trim_last_return:string,
 *     data_type:string
 * }
 */
function app_html_template_parameter_form_from_item(array $parameter): array
{
    return [
        'legacy_template_parameter_pid' => (string) ((int) ($parameter['legacy_template_parameter_pid'] ?? 0)),
        'parameter_name' => (string) ($parameter['parameter_name'] ?? ''),
        'target_value_type' => (string) ($parameter['target_value_type'] ?? ''),
        'target_variable_or_class_object' => (string) ($parameter['target_variable_or_class_object'] ?? ''),
        'target_property_of_class_object' => (string) ($parameter['target_property_of_class_object'] ?? ''),
        'another_template_pid' => (string) ((int) ($parameter['another_template_pid'] ?? 0)),
        'trim_last_space' => ((int) ($parameter['trim_last_space'] ?? 0)) === 1 ? '1' : '0',
        'trim_last_return' => ((int) ($parameter['trim_last_return'] ?? 0)) === 1 ? '1' : '0',
        'data_type' => (string) ($parameter['data_type'] ?? ''),
    ];
}

function app_html_template_parameter_form_from_post(array $fallback = []): array
{
    return [
        'legacy_template_parameter_pid' => app_post_param(
            'legacy_template_parameter_pid',
            (string) ($fallback['legacy_template_parameter_pid'] ?? ''),
        ),
        'parameter_name' => app_post_param('parameter_name', (string) ($fallback['parameter_name'] ?? '')),
        'target_value_type' => app_post_param(
            'target_value_type',
            (string) ($fallback['target_value_type'] ?? ''),
        ),
        'target_variable_or_class_object' => app_post_param(
            'target_variable_or_class_object',
            (string) ($fallback['target_variable_or_class_object'] ?? ''),
        ),
        'target_property_of_class_object' => app_post_param(
            'target_property_of_class_object',
            (string) ($fallback['target_property_of_class_object'] ?? ''),
        ),
        'another_template_pid' => app_post_param(
            'another_template_pid',
            (string) ($fallback['another_template_pid'] ?? '0'),
        ),
        'trim_last_space' => app_post_param('trim_last_space') !== '' ? '1' : '0',
        'trim_last_return' => app_post_param('trim_last_return') !== '' ? '1' : '0',
        'data_type' => app_post_param('data_type', (string) ($fallback['data_type'] ?? '')),
    ];
}

/**
 * @return list<string>
 */
function app_html_template_parameter_allowed_target_value_types(string $templateTargetType, string $currentValue = ''): array
{
    $items = ['code', 'AnotherTemplate'];
    if ($templateTargetType === 'html' || strcasecmp($currentValue, 'EachHTML') === 0) {
        array_unshift($items, 'EachHTML');
    }

    return $items;
}

/**
 * @param list<array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string
 * }> $templateCatalog
 * @return list<array{
 *     value:string,
 *     caption:string
 * }>
 */
function app_html_template_parameter_another_template_options(
    array $templateCatalog,
    int $currentTemplatePid,
    int $selectedTemplatePid = 0,
): array {
    $options = [
        [
            'value' => '0',
            'caption' => '(select template)',
        ],
    ];

    $templateByPid = app_html_template_catalog_by_pid($templateCatalog);
    foreach ($templateCatalog as $template) {
        if ((int) ($template['parent_html_template_pid'] ?? 0) !== $currentTemplatePid) {
            continue;
        }

        $options[] = [
            'value' => (string) ((int) ($template['legacy_html_template_pid'] ?? 0)),
            'caption' => (string) ($template['name'] ?? '') . ' / ' . (string) ($template['file_name'] ?? ''),
        ];
    }

    if (
        $selectedTemplatePid > 0
        && !array_filter(
            $options,
            static fn (array $option): bool => $option['value'] === (string) $selectedTemplatePid,
        )
    ) {
        $selectedTemplate = $templateByPid[(string) $selectedTemplatePid] ?? null;
        if ($selectedTemplate !== null) {
            $options[] = [
                'value' => (string) $selectedTemplatePid,
                'caption' => (string) ($selectedTemplate['name'] ?? '') . ' / ' . (string) ($selectedTemplate['file_name'] ?? '') . ' (existing)',
            ];
        }
    }

    return $options;
}

/**
 * @param list<array{
 *     value:string,
 *     caption:string
 * }> $anotherTemplateOptions
 * @return array{
 *     input:array{
 *         legacy_template_parameter_pid:string,
 *         parameter_name:string,
 *         target_value_type:string,
 *         target_variable_or_class_object:string,
 *         target_property_of_class_object:string,
 *         another_template_pid:string,
 *         trim_last_space:string,
 *         trim_last_return:string,
 *         data_type:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_html_template_parameter_form_input(
    array $input,
    string $templateTargetType,
    array $anotherTemplateOptions,
): array {
    $normalized = [
        'legacy_template_parameter_pid' => (string) max(0, (int) ($input['legacy_template_parameter_pid'] ?? 0)),
        'parameter_name' => trim((string) ($input['parameter_name'] ?? '')),
        'target_value_type' => trim((string) ($input['target_value_type'] ?? '')),
        'target_variable_or_class_object' => trim((string) ($input['target_variable_or_class_object'] ?? '')),
        'target_property_of_class_object' => trim((string) ($input['target_property_of_class_object'] ?? '')),
        'another_template_pid' => (string) max(0, (int) ($input['another_template_pid'] ?? 0)),
        'trim_last_space' => ((int) ($input['trim_last_space'] ?? 0)) === 1 ? '1' : '0',
        'trim_last_return' => ((int) ($input['trim_last_return'] ?? 0)) === 1 ? '1' : '0',
        'data_type' => trim((string) ($input['data_type'] ?? '')),
    ];

    $errors = [];
    if ($normalized['parameter_name'] === '') {
        $errors[] = 'ParameterName は必須です。';
    }

    $allowedTargetValueTypes = app_html_template_parameter_allowed_target_value_types(
        $templateTargetType,
        $normalized['target_value_type'],
    );
    if (!in_array($normalized['target_value_type'], $allowedTargetValueTypes, true)) {
        $errors[] = 'TargetValueType が不正です。';
    }

    if (!in_array($normalized['data_type'], app_allowed_html_template_parameter_data_types(), true)) {
        $errors[] = 'DataType が不正です。';
    }

    if (strcasecmp($normalized['target_value_type'], 'EachHTML') === 0) {
        $normalized['another_template_pid'] = '0';
        $normalized['target_variable_or_class_object'] = '';
        $normalized['target_property_of_class_object'] = '';
    } elseif (strcasecmp($normalized['target_value_type'], 'code') === 0) {
        if ($normalized['target_variable_or_class_object'] === '') {
            $errors[] = 'code の場合は Variable Name が必須です。';
        }
        $normalized['another_template_pid'] = '0';
        $normalized['data_type'] = '';
    } elseif (strcasecmp($normalized['target_value_type'], 'AnotherTemplate') === 0) {
        $allowedAnotherTemplatePids = array_map(
            static fn (array $option): string => (string) ($option['value'] ?? '0'),
            $anotherTemplateOptions,
        );
        if (
            $normalized['another_template_pid'] === '0'
            || !in_array($normalized['another_template_pid'], $allowedAnotherTemplatePids, true)
        ) {
            $errors[] = 'AnotherTemplatePID が不正です。';
        }
        $normalized['target_variable_or_class_object'] = '';
        $normalized['target_property_of_class_object'] = '';
        $normalized['data_type'] = '';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
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
function app_render_html_template_parameters_page(array $app, array $request): void
{
    $bootstrap = app_html_template_item_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $template = $bootstrap['template'];
    $legacyTemplatePid = (int) $template['legacy_html_template_pid'];

    $errors = [];

    $templateCatalogResult = app_fetch_html_template_catalog($app);
    if (!$templateCatalogResult['ok']) {
        $errors[] = $templateCatalogResult['error'];
    }
    $templateCatalog = $templateCatalogResult['items'];
    $templateByPid = app_html_template_catalog_by_pid($templateCatalog);

    $parameterCatalogResult = app_fetch_html_template_parameter_catalog($app, $legacyTemplatePid);
    if (!$parameterCatalogResult['ok']) {
        $errors[] = $parameterCatalogResult['error'];
    }
    $parameterCatalog = $parameterCatalogResult['items'];

    $selectedParameterPid = (int) trim(app_query_param('template_parameter_pid'));
    $selectedParameter = app_html_template_find_parameter_item_by_pid($parameterCatalog, $selectedParameterPid);
    $intent = trim(app_query_param('intent'));
    if ($intent === '' && $selectedParameter !== null) {
        $intent = 'edit';
    }

    $input = $selectedParameter !== null
        ? app_html_template_parameter_form_from_item($selectedParameter)
        : app_html_template_parameter_form_defaults((string) ($template['target_type'] ?? 'html'));

    $created = app_query_param('created') === '1';
    $updated = app_query_param('updated') === '1';
    $deleted = app_query_param('deleted') === '1';

    $anotherTemplateOptions = app_html_template_parameter_another_template_options(
        $templateCatalog,
        $legacyTemplatePid,
        (int) $input['another_template_pid'],
    );

    if (app_request_method_is($request, 'POST')) {
        $input = app_html_template_parameter_form_from_post($input);
        $action = trim(app_post_param('action'));
        $anotherTemplateOptions = app_html_template_parameter_another_template_options(
            $templateCatalog,
            $legacyTemplatePid,
            (int) $input['another_template_pid'],
        );

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif ((int) trim(app_post_param('legacy_html_template_pid')) !== $legacyTemplatePid) {
            $errors[] = '更新対象の htmlTemplate PID が route と一致しません。';
        } elseif ($action === 'delete-template-parameter') {
            $deletePid = (int) $input['legacy_template_parameter_pid'];
            if ($deletePid <= 0) {
                $errors[] = '削除対象の template parameter PID が指定されていません。';
            } else {
                $deleteResult = app_delete_html_template_parameter($app, $legacyTemplatePid, $deletePid);
                if ($deleteResult['ok']) {
                    app_send_redirect_response(
                        $request,
                        app_html_template_parameters_path($legacyTemplatePid) . '?deleted=1',
                    );
                    return;
                }

                $errors[] = $deleteResult['error'] !== ''
                    ? $deleteResult['error']
                    : 'template parameter の削除に失敗しました。';
            }
        } elseif ($action === 'create-template-parameter' || $action === 'update-template-parameter') {
            $validation = app_validate_html_template_parameter_form_input(
                $input,
                (string) ($template['target_type'] ?? ''),
                $anotherTemplateOptions,
            );
            $input = $validation['input'];
            $errors = array_merge($errors, $validation['errors']);

            if ($action === 'update-template-parameter' && (int) $input['legacy_template_parameter_pid'] <= 0) {
                $errors[] = '更新対象の template parameter PID が指定されていません。';
            }

            if ($errors === []) {
                $writeResult = $action === 'create-template-parameter'
                    ? app_create_html_template_parameter($app, [
                        'legacy_html_template_pid' => $legacyTemplatePid,
                        'parameter_name' => $input['parameter_name'],
                        'target_value_type' => $input['target_value_type'],
                        'target_variable_or_class_object' => $input['target_variable_or_class_object'],
                        'target_property_of_class_object' => $input['target_property_of_class_object'],
                        'another_template_pid' => (int) $input['another_template_pid'],
                        'trim_last_space' => (int) $input['trim_last_space'],
                        'trim_last_return' => (int) $input['trim_last_return'],
                        'data_type' => $input['data_type'],
                    ])
                    : app_update_html_template_parameter($app, [
                        'legacy_html_template_pid' => $legacyTemplatePid,
                        'legacy_template_parameter_pid' => (int) $input['legacy_template_parameter_pid'],
                        'parameter_name' => $input['parameter_name'],
                        'target_value_type' => $input['target_value_type'],
                        'target_variable_or_class_object' => $input['target_variable_or_class_object'],
                        'target_property_of_class_object' => $input['target_property_of_class_object'],
                        'another_template_pid' => (int) $input['another_template_pid'],
                        'trim_last_space' => (int) $input['trim_last_space'],
                        'trim_last_return' => (int) $input['trim_last_return'],
                        'data_type' => $input['data_type'],
                    ]);

                if ($writeResult['ok'] && is_array($writeResult['item'])) {
                    $redirectQuery = http_build_query(
                        [
                            $action === 'create-template-parameter' ? 'created' : 'updated' => '1',
                            'intent' => 'edit',
                            'template_parameter_pid' => (string) $writeResult['item']['legacy_template_parameter_pid'],
                        ],
                        '',
                        '&',
                        PHP_QUERY_RFC3986,
                    );
                    app_send_redirect_response(
                        $request,
                        app_html_template_parameters_path($legacyTemplatePid) . '?' . $redirectQuery,
                    );
                    return;
                }

                $errors[] = $writeResult['error'] !== ''
                    ? $writeResult['error']
                    : 'template parameter の保存に失敗しました。';
            }
        } elseif ($action !== '') {
            $errors[] = '未対応の操作です。';
        }
    }

    $showEditor = $intent !== '' || ($errors !== [] && app_request_method_is($request, 'POST'));
    $csrfToken = app_csrf_token();
    $statusCode = $errors === [] ? 200 : 422;
    $targetValueTypeOptions = app_html_template_parameter_allowed_target_value_types(
        (string) ($template['target_type'] ?? ''),
        (string) $input['target_value_type'],
    );

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - HTML Template Parameters</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 104rem;
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
        .summary-card, .note-card, .error-card, .success-card {
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
        .inline-checks {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        .inline-checks label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0;
            font-weight: 500;
        }
        .inline-checks input {
            width: auto;
            margin-top: 0;
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
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="<?php echo app_h(app_html_templates_path()); ?>">settings / html-templates</a> / <a href="<?php echo app_h(app_html_template_detail_path($legacyTemplatePid)); ?>"><code><?php echo app_h((string) $legacyTemplatePid); ?></code></a> / parameters</p>

    <h1>HTML Template Parameters</h1>
    <p>legacy <code>html_template_parameters.php</code> / <code>html_template_parameter_edit.php</code> を current route に移した画面です。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Template</h2>
            <ul>
                <li>legacy PID: <code><?php echo app_h((string) $legacyTemplatePid); ?></code></li>
                <li>name: <code><?php echo app_h((string) $template['name']); ?></code></li>
                <li>target: <code><?php echo app_h(app_html_template_target_type_caption((string) $template['target_type'])); ?></code></li>
                <li>file: <code><?php echo app_h((string) $template['file_name']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Rows</h2>
            <ul>
                <li>direct parameter rows: <code><?php echo app_h((string) count($parameterCatalog)); ?></code></li>
                <li>child templates: <code><?php echo app_h((string) count(array_filter($templateCatalog, static fn (array $item): bool => (int) ($item['parent_html_template_pid'] ?? 0) === $legacyTemplatePid))); ?></code></li>
                <li>current selected pid: <code><?php echo app_h((string) $selectedParameterPid); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>Actions</h2>
            <ul>
                <li><a href="<?php echo app_h(app_html_template_parameters_path($legacyTemplatePid) . '?intent=create'); ?>">add template parameter</a></li>
                <li><a href="<?php echo app_h(app_html_template_detail_path($legacyTemplatePid)); ?>">template detail</a></li>
                <li><a href="<?php echo app_h(app_html_templates_path()); ?>">back to template list</a></li>
            </ul>
        </section>
    </div>

    <?php if ($created): ?>
        <section class="success-card">
            <h2>Created</h2>
            <p>template parameter を追加しました。</p>
        </section>
    <?php endif; ?>

    <?php if ($updated): ?>
        <section class="success-card">
            <h2>Updated</h2>
            <p>template parameter を更新しました。</p>
        </section>
    <?php endif; ?>

    <?php if ($deleted): ?>
        <section class="success-card">
            <h2>Deleted</h2>
            <p>template parameter を削除しました。</p>
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
        <form method="post" action="<?php echo app_h(app_html_template_parameters_path($legacyTemplatePid)); ?>">
            <h2><?php echo app_h($selectedParameter !== null ? 'Edit Parameter' : 'Create Parameter'); ?></h2>
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="legacy_html_template_pid" value="<?php echo app_h((string) $legacyTemplatePid); ?>">
            <input type="hidden" name="legacy_template_parameter_pid" value="<?php echo app_h($input['legacy_template_parameter_pid']); ?>">

            <label for="parameter-name">Parameter Name</label>
            <input id="parameter-name" type="text" name="parameter_name" value="<?php echo app_h($input['parameter_name']); ?>">

            <label for="target-value-type">Target Value Type</label>
            <select id="target-value-type" name="target_value_type">
                <?php foreach ($targetValueTypeOptions as $targetValueType): ?>
                    <option value="<?php echo app_h($targetValueType); ?>"<?php echo $input['target_value_type'] === $targetValueType ? ' selected' : ''; ?>>
                        <?php echo app_h(app_html_template_parameter_target_value_type_caption($targetValueType)); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="data-type">Data Type</label>
            <select id="data-type" name="data_type">
                <?php foreach (app_allowed_html_template_parameter_data_types() as $dataType): ?>
                    <option value="<?php echo app_h($dataType); ?>"<?php echo $input['data_type'] === $dataType ? ' selected' : ''; ?>>
                        <?php echo app_h(app_html_template_parameter_data_type_caption($dataType)); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="target-variable">Variable Name</label>
            <input id="target-variable" type="text" name="target_variable_or_class_object" value="<?php echo app_h($input['target_variable_or_class_object']); ?>">

            <label for="target-property">Property Name</label>
            <input id="target-property" type="text" name="target_property_of_class_object" value="<?php echo app_h($input['target_property_of_class_object']); ?>">

            <label for="another-template">Another Template</label>
            <select id="another-template" name="another_template_pid">
                <?php foreach ($anotherTemplateOptions as $option): ?>
                    <option value="<?php echo app_h($option['value']); ?>"<?php echo $input['another_template_pid'] === $option['value'] ? ' selected' : ''; ?>>
                        <?php echo app_h($option['caption']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="inline-checks">
                <label><input type="checkbox" name="trim_last_space" value="1"<?php echo $input['trim_last_space'] === '1' ? ' checked' : ''; ?>>Trim Last Space</label>
                <label><input type="checkbox" name="trim_last_return" value="1"<?php echo $input['trim_last_return'] === '1' ? ' checked' : ''; ?>>Trim Last Return</label>
            </div>

            <div class="button-row">
                <button type="submit" name="action" value="<?php echo app_h($selectedParameter !== null ? 'update-template-parameter' : 'create-template-parameter'); ?>">
                    <?php echo app_h($selectedParameter !== null ? 'Update Parameter' : 'Add Parameter'); ?>
                </button>
                <?php if ($selectedParameter !== null): ?>
                    <button class="danger" type="submit" name="action" value="delete-template-parameter" onclick="return confirm('Delete this template parameter?');">Delete Parameter</button>
                <?php endif; ?>
            </div>
        </form>
    <?php endif; ?>

    <section>
        <h2>Direct Parameter Rows</h2>
        <?php if ($parameterCatalog === []): ?>
            <p class="muted">none</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>parameter</th>
                    <th>target</th>
                    <th>data type</th>
                    <th>code</th>
                    <th>template</th>
                    <th>trim</th>
                    <th>action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($parameterCatalog as $parameter): ?>
                    <?php
                    $parameterPid = (int) ($parameter['legacy_template_parameter_pid'] ?? 0);
                    $anotherTemplate = $templateByPid[(string) ((int) ($parameter['another_template_pid'] ?? 0))] ?? null;
                    $editPath = app_html_template_parameters_path($legacyTemplatePid)
                        . '?'
                        . http_build_query(
                            [
                                'intent' => 'edit',
                                'template_parameter_pid' => (string) $parameterPid,
                            ],
                            '',
                            '&',
                            PHP_QUERY_RFC3986,
                        );
                    ?>
                    <tr<?php echo $selectedParameterPid === $parameterPid ? ' class="is-selected"' : ''; ?>>
                        <td><code><?php echo app_h((string) $parameter['parameter_name']); ?></code><br><span class="muted">PID: <?php echo app_h((string) $parameterPid); ?></span></td>
                        <td><code><?php echo app_h(app_html_template_parameter_target_value_type_caption((string) $parameter['target_value_type'])); ?></code></td>
                        <td><code><?php echo app_h(app_html_template_parameter_data_type_caption((string) $parameter['data_type'])); ?></code></td>
                        <td>
                            <?php if ((string) ($parameter['target_variable_or_class_object'] ?? '') !== ''): ?>
                                <code><?php echo app_h((string) $parameter['target_variable_or_class_object']); ?></code>
                                <?php if ((string) ($parameter['target_property_of_class_object'] ?? '') !== ''): ?>
                                    <br><span class="muted">property: <code><?php echo app_h((string) $parameter['target_property_of_class_object']); ?></code></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="muted">n/a</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($anotherTemplate !== null): ?>
                                <code><?php echo app_h((string) $anotherTemplate['name']); ?></code><br>
                                <span class="muted">PID: <?php echo app_h((string) $anotherTemplate['legacy_html_template_pid']); ?></span>
                            <?php else: ?>
                                <span class="muted">n/a</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code><?php echo app_h(((int) ($parameter['trim_last_space'] ?? 0)) === 1 ? 'space' : '-'); ?></code>
                            /
                            <code><?php echo app_h(((int) ($parameter['trim_last_return'] ?? 0)) === 1 ? 'return' : '-'); ?></code>
                        </td>
                        <td><a href="<?php echo app_h($editPath); ?>">edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
    <?php
}
