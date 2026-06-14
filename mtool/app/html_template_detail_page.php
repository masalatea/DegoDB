<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/html_templates_page.php';

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
 *     legacy_html_template_pid:string,
 *     target_type:string,
 *     parent_html_template_pid:string,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string
 * }
 */
function app_html_template_detail_form_from_item(array $template): array
{
    return [
        'legacy_html_template_pid' => (string) ((int) ($template['legacy_html_template_pid'] ?? 0)),
        'target_type' => (string) ($template['target_type'] ?? ''),
        'parent_html_template_pid' => (string) ((int) ($template['parent_html_template_pid'] ?? 0)),
        'name' => (string) ($template['name'] ?? ''),
        'program_language' => (string) ($template['program_language'] ?? ''),
        'file_name' => (string) ($template['file_name'] ?? ''),
        'comment' => (string) ($template['comment'] ?? ''),
    ];
}

function app_html_template_detail_form_from_post(array $fallback = []): array
{
    return [
        'legacy_html_template_pid' => app_post_param(
            'legacy_html_template_pid',
            (string) ($fallback['legacy_html_template_pid'] ?? ''),
        ),
        'target_type' => app_post_param('target_type', (string) ($fallback['target_type'] ?? '')),
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
 *     comment:string
 * }> $templateCatalog
 * @return array{
 *     input:array{
 *         legacy_html_template_pid:string,
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
function app_validate_html_template_detail_form_input(array $input, array $templateCatalog): array
{
    $normalized = [
        'legacy_html_template_pid' => (string) max(0, (int) ($input['legacy_html_template_pid'] ?? 0)),
        'target_type' => trim((string) ($input['target_type'] ?? '')),
        'parent_html_template_pid' => (string) max(0, (int) ($input['parent_html_template_pid'] ?? 0)),
        'name' => trim((string) ($input['name'] ?? '')),
        'program_language' => trim((string) ($input['program_language'] ?? '')),
        'file_name' => trim((string) ($input['file_name'] ?? '')),
        'comment' => trim((string) ($input['comment'] ?? '')),
    ];

    $errors = [];
    $legacyTemplatePid = (int) $normalized['legacy_html_template_pid'];

    if ($legacyTemplatePid <= 0) {
        $errors[] = 'legacy htmlTemplate PID が不正です。';
    }
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
    if ($parentTemplatePid === $legacyTemplatePid) {
        $errors[] = 'ParentHtmlTemplatePID に自分自身は指定できません。';
    } elseif (
        $parentTemplatePid > 0
        && app_html_template_find_catalog_item_by_pid($templateCatalog, $parentTemplatePid) === null
    ) {
        $errors[] = 'ParentHtmlTemplatePID が存在しません。';
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
function app_render_html_template_detail_page(array $app, array $request): void
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
    $treeRows = app_html_template_catalog_tree_rows($templateCatalog);
    $templateByPid = app_html_template_catalog_by_pid($templateCatalog);

    $parameterCatalogResult = app_fetch_html_template_parameter_catalog($app, $legacyTemplatePid);
    if (!$parameterCatalogResult['ok']) {
        $errors[] = $parameterCatalogResult['error'];
    }
    $parameterCatalog = $parameterCatalogResult['items'];

    $childTemplates = array_values(
        array_filter(
            $templateCatalog,
            static fn (array $item): bool
                => (int) ($item['parent_html_template_pid'] ?? 0) === $legacyTemplatePid,
        ),
    );
    $anotherTemplateReferenceCount = 0;
    $allParameterCatalogResult = app_fetch_html_template_parameter_catalog($app);
    if (!$allParameterCatalogResult['ok']) {
        $errors[] = $allParameterCatalogResult['error'];
    }
    foreach ($allParameterCatalogResult['items'] as $parameter) {
        if (
            (int) ($parameter['another_template_pid'] ?? 0) === $legacyTemplatePid
            && (int) ($parameter['legacy_html_template_pid'] ?? 0) !== $legacyTemplatePid
        ) {
            $anotherTemplateReferenceCount++;
        }
    }

    $input = app_html_template_detail_form_from_item($template);
    $created = app_query_param('created') === '1';
    $updated = app_query_param('updated') === '1';

    if (app_request_method_is($request, 'POST')) {
        $input = app_html_template_detail_form_from_post($input);
        $action = trim(app_post_param('action'));

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif ((int) $input['legacy_html_template_pid'] !== $legacyTemplatePid) {
            $errors[] = '更新対象の htmlTemplate PID が route と一致しません。';
        } elseif ($action === 'delete-template') {
            $deleteResult = app_delete_html_template($app, $legacyTemplatePid);
            if ($deleteResult['ok']) {
                app_send_redirect_response(
                    $request,
                    app_html_templates_path() . '?deleted=1',
                );
                return;
            }

            $errors[] = $deleteResult['error'] !== ''
                ? $deleteResult['error']
                : 'html template の削除に失敗しました。';
        } elseif ($action === 'update-template') {
            $validation = app_validate_html_template_detail_form_input($input, $templateCatalog);
            $input = $validation['input'];
            $errors = array_merge($errors, $validation['errors']);

            if ($errors === []) {
                $updateResult = app_update_html_template($app, [
                    'legacy_html_template_pid' => $legacyTemplatePid,
                    'target_type' => $input['target_type'],
                    'parent_html_template_pid' => (int) $input['parent_html_template_pid'],
                    'name' => $input['name'],
                    'program_language' => $input['program_language'],
                    'file_name' => $input['file_name'],
                    'comment' => $input['comment'],
                ]);

                if ($updateResult['ok'] && is_array($updateResult['item'])) {
                    app_send_redirect_response(
                        $request,
                        app_html_template_detail_path($legacyTemplatePid) . '?updated=1',
                    );
                    return;
                }

                $errors[] = $updateResult['error'] !== ''
                    ? $updateResult['error']
                    : 'html template の更新に失敗しました。';
            }
        } elseif ($action !== '') {
            $errors[] = '未対応の操作です。';
        }
    }

    $parentTemplate = $templateByPid[(string) ((int) ($template['parent_html_template_pid'] ?? 0))] ?? null;
    $csrfToken = app_csrf_token();
    $statusCode = $errors === [] ? 200 : 422;
    $duplicatePath = app_html_templates_path()
        . '?'
        . http_build_query(
            [
                'intent' => 'create',
                'duplicate_template_pid' => (string) $legacyTemplatePid,
            ],
            '',
            '&',
            PHP_QUERY_RFC3986,
        );

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - HTML Template Detail</title>
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
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="<?php echo app_h(app_html_templates_path()); ?>">settings / html-templates</a> / <code><?php echo app_h((string) $legacyTemplatePid); ?></code></p>

    <h1>HTML Template Detail</h1>
    <p>legacy <code>html_template_edit.php</code> の update/delete を current route で扱います。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Template</h2>
            <ul>
                <li>legacy PID: <code><?php echo app_h((string) $legacyTemplatePid); ?></code></li>
                <li>name: <code><?php echo app_h((string) $template['name']); ?></code></li>
                <li>target: <code><?php echo app_h(app_html_template_target_type_caption((string) $template['target_type'])); ?></code></li>
                <li>language: <code><?php echo app_h(app_html_template_program_language_caption((string) $template['program_language'])); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Relations</h2>
            <ul>
                <li>parent: <?php echo $parentTemplate !== null ? '<code>' . app_h((string) $parentTemplate['name']) . '</code>' : '<span class="muted">(top)</span>'; ?></li>
                <li>child templates: <code><?php echo app_h((string) count($childTemplates)); ?></code></li>
                <li>parameter rows: <code><?php echo app_h((string) count($parameterCatalog)); ?></code></li>
                <li>AnotherTemplate refs: <code><?php echo app_h((string) $anotherTemplateReferenceCount); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>Actions</h2>
            <ul>
                <li><a href="<?php echo app_h(app_html_template_parameters_path($legacyTemplatePid)); ?>">parameters</a></li>
                <li><a href="<?php echo app_h($duplicatePath); ?>">duplicate this template</a></li>
                <li><a href="<?php echo app_h(app_html_templates_path()); ?>">back to template list</a></li>
            </ul>
        </section>
    </div>

    <?php if ($created): ?>
        <section class="success-card">
            <h2>Created</h2>
            <p>html template を追加しました。</p>
        </section>
    <?php endif; ?>

    <?php if ($updated): ?>
        <section class="success-card">
            <h2>Updated</h2>
            <p>html template を更新しました。</p>
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

    <form method="post" action="<?php echo app_h(app_html_template_detail_path($legacyTemplatePid)); ?>">
        <h2>Edit Template</h2>
        <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
        <input type="hidden" name="legacy_html_template_pid" value="<?php echo app_h($input['legacy_html_template_pid']); ?>">

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
            <?php foreach (app_html_template_parent_options($treeRows, $legacyTemplatePid) as $option): ?>
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

        <div class="button-row">
            <button type="submit" name="action" value="update-template">Update Template</button>
            <button class="danger" type="submit" name="action" value="delete-template" onclick="return confirm('Delete this html template?');">Delete Template</button>
        </div>
    </form>
</main>
</body>
</html>
    <?php
}
