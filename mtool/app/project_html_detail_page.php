<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/html_template_route_common.php';
require_once __DIR__ . '/project_html_route_common.php';
require_once __DIR__ . '/project_htmls_page.php';

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
function app_render_project_html_detail_page(array $app, array $request): void
{
    $bootstrap = app_project_html_item_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $project = $bootstrap['project'];
    $projectKey = $bootstrap['project_key'];
    $reference = $bootstrap['reference'];
    $html = $bootstrap['html'];

    $errors = app_project_html_bridge_errors_from_request();
    $input = app_project_html_form_from_item($html);

    $sourceOutputCatalogResult = app_fetch_project_source_output_catalog($app, $projectKey);
    if (!$sourceOutputCatalogResult['ok']) {
        $errors[] = $sourceOutputCatalogResult['error'];
    }
    $sourceOutputCatalog = $sourceOutputCatalogResult['items'];
    $sourceOutputByKey = app_project_html_source_output_catalog_by_key($sourceOutputCatalog);

    $bootstrapSourceOutputCatalogResult = app_project_html_source_output_catalog_by_legacy_pid($app, $projectKey);
    if (!$bootstrapSourceOutputCatalogResult['ok']) {
        $errors[] = $bootstrapSourceOutputCatalogResult['error'];
    }
    $bootstrapSourceOutputByLegacyPid = $bootstrapSourceOutputCatalogResult['items'];

    $bindingResult = app_fetch_project_html_source_bindings($app, $projectKey);
    if (!$bindingResult['ok']) {
        $errors[] = $bindingResult['error'];
    }
    $bindingCatalog = app_project_html_source_binding_catalog(
        $projectKey,
        $bindingResult['items'],
        $bootstrapSourceOutputByLegacyPid,
        $sourceOutputByKey,
    );

    $templateCatalogResult = app_fetch_project_html_template_catalog($app, $projectKey);
    if (!$templateCatalogResult['ok']) {
        $errors[] = $templateCatalogResult['error'];
    }
    $templateCatalog = $templateCatalogResult['items'];
    $templateByPid = app_project_html_template_catalog_by_pid($templateCatalog);

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

    $created = app_query_param('created') === '1';
    $updated = app_query_param('updated') === '1';
    $intent = trim(app_query_param('intent'));
    $action = trim(app_post_param('action'));

    if (app_request_method_is($request, 'POST')) {
        $input = app_project_html_form_from_post($input);

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif (trim(app_post_param('html_key')) !== '' && trim(app_post_param('html_key')) !== $html['html_key']) {
            $errors[] = '更新対象の html key が route と一致しません。';
        } elseif ($action === 'delete-html') {
            $deleteResult = app_delete_project_html(
                $app,
                $projectKey,
                (int) $reference['project_pid'],
                (int) $html['legacy_html_pid'],
            );
            if ($deleteResult['ok']) {
                app_send_redirect_response(
                    $request,
                    app_project_htmls_path($projectKey) . '?deleted=1',
                );
                return;
            }

            $errors[] = $deleteResult['error'] !== ''
                ? $deleteResult['error']
                : 'html の削除に失敗しました。';
        } elseif ($action === 'update-html') {
            $updateResult = app_update_project_html(
                $app,
                $projectKey,
                [
                    'project_pid' => (int) $reference['project_pid'],
                    'legacy_html_pid' => (int) $html['legacy_html_pid'],
                    'name' => $input['name'],
                    'legacy_project_source_output_pid' => (int) $input['legacy_project_source_output_pid'],
                    'legacy_html_template_pid' => (int) $input['legacy_html_template_pid'],
                ],
            );

            if ($updateResult['ok'] && is_array($updateResult['item'])) {
                app_send_redirect_response(
                    $request,
                    app_project_html_detail_path($projectKey, $updateResult['item']['html_key']) . '?updated=1',
                );
                return;
            }

            $errors[] = $updateResult['error'] !== ''
                ? $updateResult['error']
                : 'html の更新に失敗しました。';
        } elseif ($action !== '' || $errors === []) {
            $errors[] = '未対応の操作です。';
        }
    }

    $sourceOutputBinding = $bindingCatalog[(string) $html['legacy_project_source_output_pid']] ?? null;
    $sourceOutput = is_array($sourceOutputBinding)
        ? ($sourceOutputByKey[$sourceOutputBinding['source_output_key']] ?? null)
        : null;
    $template = $templateByPid[(string) $html['legacy_html_template_pid']] ?? null;
    $audit = app_legacy_html_reference_parameter_audit_with_actual_items(
        $reference,
        $html,
        $parameterCatalog,
    );
    $sourceOutputOptions = app_project_html_source_output_options(
        $bindingCatalog,
        $input['legacy_project_source_output_pid'],
    );
    $templateOptions = app_project_html_template_options(
        $templateCatalog,
        $input['legacy_html_template_pid'],
    );

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project HTML Detail</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 88rem;
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
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="<?php echo app_h(app_project_htmls_path($projectKey)); ?>">html</a> / <code><?php echo app_h($html['html_key']); ?></code></p>

    <h1><?php echo app_h($project['name']); ?> HTML Detail</h1>
    <p>legacy <code>html_edit.php</code> の update/delete を current route で扱います。parameter 編集は別の parameter list 画面に分離し、template metadata は global settings 側の canonical <code>html_templates</code> / <code>html_template_parameters</code> から参照します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>HTML</h2>
            <ul>
                <li>html key: <code><?php echo app_h($html['html_key']); ?></code></li>
                <li>name: <code><?php echo app_h($html['name']); ?></code></li>
                <li>legacy PID: <code><?php echo app_h((string) $html['legacy_html_pid']); ?></code></li>
                <li>last modified: <code><?php echo app_h($html['last_modified_dt']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Template</h2>
            <ul>
                <?php if ($template !== null): ?>
                    <li>name: <code><?php echo app_h($template['name']); ?></code></li>
                    <li>legacy template PID: <code><?php echo app_h((string) $template['legacy_html_template_pid']); ?></code></li>
                    <li>program language: <code><?php echo app_h(app_html_template_program_language_caption((string) $template['program_language'])); ?></code></li>
                    <li>file: <code><?php echo app_h($template['file_name']); ?></code></li>
                    <li>comment: <?php echo $template['comment'] !== '' ? app_h($template['comment']) : '<span class="muted">none</span>'; ?></li>
                <?php else: ?>
                    <li class="muted">template not found</li>
                <?php endif; ?>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Source Output</h2>
            <ul>
                <li>legacy ProjectSourceOutputPID: <code><?php echo app_h((string) $html['legacy_project_source_output_pid']); ?></code></li>
                <?php if ($sourceOutputBinding !== null): ?>
                    <?php if ($sourceOutput !== null): ?>
                        <li>current key: <a href="/projects/<?php echo rawurlencode($projectKey); ?>/source-outputs/<?php echo rawurlencode((string) $sourceOutput['source_output_key']); ?>"><code><?php echo app_h((string) $sourceOutput['source_output_key']); ?></code></a></li>
                        <li>name: <?php echo app_h((string) ($sourceOutput['name'] ?? '')); ?></li>
                        <li>output dir: <code><?php echo app_h((string) ($sourceOutput['source_output_dir'] ?? '')); ?></code></li>
                    <?php elseif ($sourceOutputBinding['source_output_key'] !== ''): ?>
                        <li>current key: <code><?php echo app_h($sourceOutputBinding['source_output_key']); ?></code></li>
                        <li class="muted">current source output row not found</li>
                    <?php endif; ?>
                    <li>binding state: <code><?php echo app_h($sourceOutputBinding['binding_state']); ?></code></li>
                    <li>refresh policy: <code><?php echo app_h($sourceOutputBinding['refresh_policy']); ?></code></li>
                    <li>binding source: <code><?php echo app_h($sourceOutputBinding['source_of_truth']); ?></code></li>
                    <li>effective ref: <code><?php echo app_h($sourceOutputBinding['effective_source_ref']); ?></code></li>
                    <?php if (
                        $sourceOutputBinding['effective_source_output_key'] !== ''
                        && $sourceOutputBinding['effective_source_output_key'] !== $sourceOutputBinding['source_output_key']
                    ): ?>
                        <li>effective ref key: <code><?php echo app_h($sourceOutputBinding['effective_source_output_key']); ?></code></li>
                    <?php endif; ?>
                    <?php if ($sourceOutputBinding['source_root_ok']): ?>
                        <li>source root: <code><?php echo app_h($sourceOutputBinding['source_root_relative_path']); ?></code></li>
                        <li>source kind: <code><?php echo app_h(app_project_output_html_module_source_kind_caption($sourceOutputBinding['source_kind'])); ?></code></li>
                    <?php else: ?>
                        <li class="muted">source root unresolved: <?php echo app_h($sourceOutputBinding['source_root_error']); ?></li>
                    <?php endif; ?>
                    <?php if ($sourceOutputBinding['notes'] !== ''): ?>
                        <li>binding notes: <?php echo nl2br(app_h($sourceOutputBinding['notes'])); ?></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="muted">current HTML source binding not found</li>
                <?php endif; ?>
            </ul>
        </section>

        <section class="note-card">
            <h2>Next</h2>
            <ul>
                <li><a href="<?php echo app_h(app_project_html_parameters_path($projectKey, $html['html_key'])); ?>">parameter list</a></li>
                <?php if ($template !== null): ?>
                    <li><a href="<?php echo app_h(app_html_template_detail_path((int) $template['legacy_html_template_pid'])); ?>">template settings</a></li>
                <?php endif; ?>
                <li><a href="<?php echo app_h(app_project_htmls_path($projectKey)); ?>">back to html list</a></li>
                <li><a href="<?php echo app_h(app_project_htmls_path($projectKey) . '?binding_pid=' . rawurlencode((string) $html['legacy_project_source_output_pid'])); ?>">edit html source binding</a></li>
                <li><?php echo $intent === 'edit' ? 'legacy edit deep link から current form に着地しています' : 'current detail / edit page です'; ?></li>
            </ul>
        </section>
    </div>

    <?php if ($created): ?>
        <section class="success-card">
            <h2>Created</h2>
            <p>html row を追加しました。</p>
        </section>
    <?php endif; ?>

    <?php if ($updated): ?>
        <section class="success-card">
            <h2>Updated</h2>
            <p>html row を更新しました。</p>
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

    <form method="post" action="<?php echo app_h(app_project_html_detail_path($projectKey, $html['html_key'])); ?>">
        <h2>Edit Basic Info</h2>
        <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
        <input type="hidden" name="html_key" value="<?php echo app_h($html['html_key']); ?>">

        <label for="html-name">Name</label>
        <input id="html-name" type="text" name="name" value="<?php echo app_h($input['name']); ?>">

        <label for="html-source-output">Source Output</label>
        <select id="html-source-output" name="legacy_project_source_output_pid">
            <option value="0">(none)</option>
            <?php foreach ($sourceOutputOptions as $option): ?>
                <option value="<?php echo app_h($option['legacy_pid']); ?>"<?php echo $input['legacy_project_source_output_pid'] === $option['legacy_pid'] ? ' selected' : ''; ?>>
                    <?php echo app_h($option['caption']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="html-template">HTML Template</label>
        <select id="html-template" name="legacy_html_template_pid">
            <option value="0">(none)</option>
            <?php foreach ($templateOptions as $option): ?>
                <option value="<?php echo app_h($option['legacy_pid']); ?>"<?php echo $input['legacy_html_template_pid'] === $option['legacy_pid'] ? ' selected' : ''; ?>>
                    <?php echo app_h($option['caption']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="button-row">
            <button type="submit" name="action" value="update-html">Update HTML</button>
            <button class="danger" type="submit" name="action" value="delete-html" onclick="return confirm('Delete this html row?');">Delete HTML</button>
        </div>
    </form>

    <section class="warning-card">
        <h2>Parameter Status</h2>
        <ul>
            <li>expected each-html parameters: <code><?php echo app_h((string) $audit['expected_count']); ?></code></li>
            <li>actual parameter rows: <code><?php echo app_h((string) $audit['actual_count']); ?></code></li>
            <li>complete: <code><?php echo app_h($audit['is_complete'] ? 'yes' : 'no'); ?></code></li>
            <li>missing: <?php echo $audit['missing_parameter_names'] !== [] ? '<code>' . app_h(implode(', ', $audit['missing_parameter_names'])) . '</code>' : '<span class="muted">none</span>'; ?></li>
            <li>duplicate actual rows: <?php echo $audit['duplicate_parameter_names'] !== [] ? '<code>' . app_h(implode(', ', $audit['duplicate_parameter_names'])) . '</code>' : '<span class="muted">none</span>'; ?></li>
            <li>unused actual rows: <code><?php echo app_h((string) count($audit['unexpected_items'])); ?></code></li>
            <li>template duplicate data types: <?php echo $audit['template_duplicate_data_type_names'] !== [] ? '<code>' . app_h(implode(', ', $audit['template_duplicate_data_type_names'])) . '</code>' : '<span class="muted">none</span>'; ?></li>
        </ul>
    </section>

    <?php if ($audit['expected_rows'] !== []): ?>
        <section>
            <h2>Expected Parameters</h2>
            <table>
                <thead>
                <tr>
                    <th>parameter</th>
                    <th>data type</th>
                    <th>current value</th>
                    <th>note</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($audit['expected_rows'] as $row): ?>
                    <tr>
                        <td><code><?php echo app_h($row['parameter_name']); ?></code></td>
                        <td><?php echo app_h($row['data_type_caption']); ?></td>
                        <td>
                            <?php if ($row['actual_item'] !== null): ?>
                                <code><?php echo app_h($row['actual_item']['parameter_value']); ?></code>
                            <?php else: ?>
                                <span class="muted">not set</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['actual_item'] !== null): ?>
                                <span class="muted">legacy htmlParameter PID: <code><?php echo app_h((string) $row['actual_item']['legacy_parameter_pid']); ?></code></span>
                            <?php endif; ?>
                            <?php if ($row['duplicate_items'] !== []): ?>
                                <br><span class="muted">duplicate actual PIDs: <code><?php echo app_h(implode(', ', array_map(static fn (array $item): string => (string) $item['legacy_parameter_pid'], $row['duplicate_items']))); ?></code></span>
                            <?php endif; ?>
                        </td>
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
