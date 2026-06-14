<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/html_template_route_common.php';

function app_html_template_form_defaults(): array
{
    return [
        'target_type' => 'html',
        'parent_html_template_pid' => '0',
        'name' => '',
        'program_language' => 'php',
        'file_name' => '',
        'comment' => '',
    ];
}

/**
 * @param array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string
 * } $template
 * @return array{
 *     target_type:string,
 *     parent_html_template_pid:string,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string
 * }
 */
function app_html_template_form_from_item(array $template): array
{
    return [
        'target_type' => (string) ($template['target_type'] ?? ''),
        'parent_html_template_pid' => (string) ((int) ($template['parent_html_template_pid'] ?? 0)),
        'name' => (string) ($template['name'] ?? ''),
        'program_language' => (string) ($template['program_language'] ?? ''),
        'file_name' => (string) ($template['file_name'] ?? ''),
        'comment' => (string) ($template['comment'] ?? ''),
    ];
}

function app_html_template_form_from_post(array $fallback = []): array
{
    return [
        'target_type' => app_post_param('target_type', (string) ($fallback['target_type'] ?? 'html')),
        'parent_html_template_pid' => app_post_param(
            'parent_html_template_pid',
            (string) ($fallback['parent_html_template_pid'] ?? '0'),
        ),
        'name' => app_post_param('name', (string) ($fallback['name'] ?? '')),
        'program_language' => app_post_param(
            'program_language',
            (string) ($fallback['program_language'] ?? 'php'),
        ),
        'file_name' => app_post_param('file_name', (string) ($fallback['file_name'] ?? '')),
        'comment' => app_post_param('comment', (string) ($fallback['comment'] ?? '')),
    ];
}

/**
 * @param list<array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string,
 *     depth:int,
 *     parent_name:string
 * }> $treeRows
 * @return list<array{
 *     value:string,
 *     caption:string
 * }>
 */
function app_html_template_parent_options(array $treeRows, int $excludeLegacyTemplatePid = 0): array
{
    $options = [
        [
            'value' => '0',
            'caption' => '(top level)',
        ],
    ];

    foreach ($treeRows as $row) {
        $legacyTemplatePid = (int) ($row['legacy_html_template_pid'] ?? 0);
        if ($legacyTemplatePid <= 0 || $legacyTemplatePid === $excludeLegacyTemplatePid) {
            continue;
        }

        $prefix = str_repeat('. ', max(0, (int) ($row['depth'] ?? 0)));
        $options[] = [
            'value' => (string) $legacyTemplatePid,
            'caption' => $prefix . (string) ($row['name'] ?? '')
                . ' [' . app_html_template_target_type_caption((string) ($row['target_type'] ?? '')) . ']',
        ];
    }

    return $options;
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
 * @return array{
 *     input:array{
 *         target_type:string,
 *         parent_html_template_pid:string,
 *         name:string,
 *         program_language:string,
 *         file_name:string,
 *         comment:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_html_template_form_input(array $input, array $templateCatalog): array
{
    $normalized = [
        'target_type' => trim((string) ($input['target_type'] ?? '')),
        'parent_html_template_pid' => (string) max(0, (int) ($input['parent_html_template_pid'] ?? 0)),
        'name' => trim((string) ($input['name'] ?? '')),
        'program_language' => trim((string) ($input['program_language'] ?? '')),
        'file_name' => trim((string) ($input['file_name'] ?? '')),
        'comment' => trim((string) ($input['comment'] ?? '')),
    ];

    $errors = [];

    if (!in_array($normalized['target_type'], app_allowed_html_template_target_types(), true)) {
        $errors[] = 'TargetType が不正です。';
    }
    if (!in_array($normalized['program_language'], app_allowed_html_template_program_languages(), true)) {
        $errors[] = 'ProgramLanguage が不正です。';
    }
    if ($normalized['name'] === '') {
        $errors[] = 'Name は必須です。';
    }
    if ($normalized['file_name'] === '') {
        $errors[] = 'FileName は必須です。';
    }

    $parentTemplatePid = (int) $normalized['parent_html_template_pid'];
    if ($parentTemplatePid > 0 && app_html_template_find_catalog_item_by_pid($templateCatalog, $parentTemplatePid) === null) {
        $errors[] = 'ParentHtmlTemplatePID が存在しません。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param list<array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string,
 *     depth:int,
 *     parent_name:string
 * }> $treeRows
 * @return array<string,list<array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string,
 *     depth:int,
 *     parent_name:string
 * }>>
 */
function app_html_template_group_rows_by_target_type(array $treeRows): array
{
    $grouped = [];
    foreach ($treeRows as $row) {
        $targetType = (string) ($row['target_type'] ?? '');
        if (!array_key_exists($targetType, $grouped)) {
            $grouped[$targetType] = [];
        }

        $grouped[$targetType][] = $row;
    }

    return $grouped;
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
function app_render_html_templates_page(array $app, array $request): void
{
    $bootstrap = app_html_template_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $errors = [];

    $templateCatalogResult = app_fetch_html_template_catalog($app);
    if (!$templateCatalogResult['ok']) {
        $errors[] = $templateCatalogResult['error'];
    }
    $templateCatalog = $templateCatalogResult['items'];
    $treeRows = app_html_template_catalog_tree_rows($templateCatalog);
    $groupedRows = app_html_template_group_rows_by_target_type($treeRows);
    $templateByPid = app_html_template_catalog_by_pid($templateCatalog);

    $templateParameterCatalogResult = app_fetch_html_template_parameter_catalog($app);
    if (!$templateParameterCatalogResult['ok']) {
        $errors[] = $templateParameterCatalogResult['error'];
    }
    $templateParameterCatalog = $templateParameterCatalogResult['items'];

    $duplicateTemplatePid = (int) trim(app_query_param('duplicate_template_pid'));
    $duplicateTemplate = $templateByPid[(string) $duplicateTemplatePid] ?? null;

    $intent = trim(app_query_param('intent'));
    if ($intent === '' && $duplicateTemplate !== null) {
        $intent = 'create';
    }

    $input = $duplicateTemplate !== null
        ? app_html_template_form_from_item($duplicateTemplate)
        : app_html_template_form_defaults();
    $created = app_query_param('created') === '1';
    $deleted = app_query_param('deleted') === '1';

    if (app_request_method_is($request, 'POST')) {
        $action = trim(app_post_param('action'));
        $input = app_html_template_form_from_post($input);
        $intent = 'create';

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif ($action === 'create-template') {
            $validation = app_validate_html_template_form_input($input, $templateCatalog);
            $input = $validation['input'];
            $errors = array_merge($errors, $validation['errors']);

            if ($errors === []) {
                $createResult = app_create_html_template($app, [
                    'target_type' => $input['target_type'],
                    'parent_html_template_pid' => (int) $input['parent_html_template_pid'],
                    'name' => $input['name'],
                    'program_language' => $input['program_language'],
                    'file_name' => $input['file_name'],
                    'comment' => $input['comment'],
                ]);

                if ($createResult['ok'] && is_array($createResult['item'])) {
                    app_send_redirect_response(
                        $request,
                        app_html_template_detail_path((int) $createResult['item']['legacy_html_template_pid']) . '?created=1',
                    );
                    return;
                }

                $errors[] = $createResult['error'] !== ''
                    ? $createResult['error']
                    : 'html template の追加に失敗しました。';
            }
        } elseif ($action !== '') {
            $errors[] = '未対応の操作です。';
        }
    }

    $showCreateForm = $intent === 'create' || ($errors !== [] && app_request_method_is($request, 'POST'));
    $csrfToken = app_csrf_token();
    $statusCode = $errors === [] ? 200 : 422;

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - HTML Template Settings</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 110rem;
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
        input, select, textarea, button {
            font: inherit;
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
            min-height: 8rem;
            resize: vertical;
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
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / settings / html-templates</p>

    <h1>HTML Template Settings</h1>
    <p>legacy <code>systemsettings/htmltemplate</code> を current route に移した画面です。template metadata は canonical <code>html_templates</code> / <code>html_template_parameters</code> を優先し、初回だけ legacy table または MTOOL reference から bootstrap します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Catalog</h2>
            <ul>
                <li>template rows: <code><?php echo app_h((string) count($templateCatalog)); ?></code></li>
                <li>parameter rows: <code><?php echo app_h((string) count($templateParameterCatalog)); ?></code></li>
                <li>html target templates: <code><?php echo app_h((string) count($groupedRows['html'] ?? [])); ?></code></li>
                <li>db name: <code><?php echo app_h($app['db']['name']); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>Actions</h2>
            <ul>
                <li><a href="<?php echo app_h(app_html_templates_path() . '?intent=create'); ?>">add html template</a></li>
                <li>existing row の basic info は detail 画面で更新します</li>
                <li>parameter editor は template detail から開けます</li>
            </ul>
        </section>
    </div>

    <?php if ($created): ?>
        <section class="success-card">
            <h2>Created</h2>
            <p>html template を追加しました。</p>
        </section>
    <?php endif; ?>

    <?php if ($deleted): ?>
        <section class="success-card">
            <h2>Deleted</h2>
            <p>html template を削除しました。</p>
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

    <?php if ($showCreateForm): ?>
        <form method="post" action="<?php echo app_h(app_html_templates_path()); ?>">
            <h2><?php echo app_h($duplicateTemplate !== null ? 'Duplicate Template' : 'Create Template'); ?></h2>
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">

            <label for="target-type">Target Type</label>
            <select id="target-type" name="target_type">
                <?php foreach (app_allowed_html_template_target_types() as $targetType): ?>
                    <option value="<?php echo app_h($targetType); ?>"<?php echo $input['target_type'] === $targetType ? ' selected' : ''; ?>>
                        <?php echo app_h(app_html_template_target_type_caption($targetType)); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="parent-template">Parent Template</label>
            <select id="parent-template" name="parent_html_template_pid">
                <?php foreach (app_html_template_parent_options($treeRows) as $option): ?>
                    <option value="<?php echo app_h($option['value']); ?>"<?php echo $input['parent_html_template_pid'] === $option['value'] ? ' selected' : ''; ?>>
                        <?php echo app_h($option['caption']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="template-name">Name</label>
            <input id="template-name" type="text" name="name" value="<?php echo app_h($input['name']); ?>">

            <label for="program-language">Program Language</label>
            <select id="program-language" name="program_language">
                <?php foreach (app_allowed_html_template_program_languages() as $programLanguage): ?>
                    <option value="<?php echo app_h($programLanguage); ?>"<?php echo $input['program_language'] === $programLanguage ? ' selected' : ''; ?>>
                        <?php echo app_h(app_html_template_program_language_caption($programLanguage)); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="file-name">File Name</label>
            <input id="file-name" type="text" name="file_name" value="<?php echo app_h($input['file_name']); ?>">

            <label for="comment">Comment</label>
            <textarea id="comment" name="comment"><?php echo app_h($input['comment']); ?></textarea>

            <button type="submit" name="action" value="create-template">
                <?php echo app_h($duplicateTemplate !== null ? 'Create Duplicate Template' : 'Add Template'); ?>
            </button>
        </form>
    <?php endif; ?>

    <?php foreach (app_allowed_html_template_target_types() as $targetType): ?>
        <?php $rows = $groupedRows[$targetType] ?? []; ?>
        <?php if ($rows === []): ?>
            <?php continue; ?>
        <?php endif; ?>
        <section>
            <h2><?php echo app_h(app_html_template_target_type_caption($targetType)); ?></h2>
            <table>
                <thead>
                <tr>
                    <th>parent</th>
                    <th>name</th>
                    <th>language</th>
                    <th>file</th>
                    <th>comment</th>
                    <th>parameters</th>
                    <th>detail</th>
                    <th>duplicate</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <?php
                    $detailPath = app_html_template_detail_path((int) $row['legacy_html_template_pid']);
                    $duplicatePath = app_html_templates_path()
                        . '?'
                        . http_build_query(
                            [
                                'intent' => 'create',
                                'duplicate_template_pid' => (string) $row['legacy_html_template_pid'],
                            ],
                            '',
                            '&',
                            PHP_QUERY_RFC3986,
                        );
                    $parameterPath = app_html_template_parameters_path((int) $row['legacy_html_template_pid']);
                    $namePrefix = str_repeat('. ', max(0, (int) ($row['depth'] ?? 0)));
                    ?>
                    <tr>
                        <td><?php echo $row['parent_name'] !== '' ? '<code>' . app_h($row['parent_name']) . '</code>' : '<span class="muted">(top)</span>'; ?></td>
                        <td>
                            <code><?php echo app_h($namePrefix . (string) $row['name']); ?></code><br>
                            <span class="muted">legacy PID: <?php echo app_h((string) $row['legacy_html_template_pid']); ?></span>
                        </td>
                        <td><code><?php echo app_h(app_html_template_program_language_caption((string) $row['program_language'])); ?></code></td>
                        <td><code><?php echo app_h((string) $row['file_name']); ?></code></td>
                        <td><?php echo (string) $row['comment'] !== '' ? app_h((string) $row['comment']) : '<span class="muted">none</span>'; ?></td>
                        <td><a href="<?php echo app_h($parameterPath); ?>">parameters</a></td>
                        <td><a href="<?php echo app_h($detailPath); ?>">detail</a></td>
                        <td><a href="<?php echo app_h($duplicatePath); ?>">duplicate</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endforeach; ?>
</main>
</body>
</html>
    <?php
}
