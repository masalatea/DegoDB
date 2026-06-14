<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/compare_output_asset_service.php';
require_once __DIR__ . '/compare_output_service.php';
require_once __DIR__ . '/project_compare_output_route_common.php';
require_once __DIR__ . '/request.php';

/**
 * @param array{
 *     compare_output_key:string,
 *     name:string,
 *     storage_base_path:string,
 *     output_file_path:string,
 *     output_file_type:string,
 *     compare_path:string,
 *     compare_tool_file_path:string,
 *     compare_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at:string
 * } $item
 * @return array{
 *     compare_output_key:string,
 *     name:string,
 *     storage_base_path:string,
 *     output_file_path:string,
 *     output_file_type:string,
 *     compare_path:string,
 *     compare_tool_file_path:string,
 *     compare_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * }
 */
function app_project_compare_output_form_from_item(array $item): array
{
    return [
        'compare_output_key' => $item['compare_output_key'],
        'name' => $item['name'],
        'storage_base_path' => $item['storage_base_path'],
        'output_file_path' => $item['output_file_path'],
        'output_file_type' => $item['output_file_type'],
        'compare_path' => $item['compare_path'],
        'compare_tool_file_path' => $item['compare_tool_file_path'],
        'compare_output_list_order' => $item['compare_output_list_order'],
        'notes' => $item['notes'],
        'source_of_truth' => $item['source_of_truth'],
    ];
}

function app_project_compare_output_settings_redirect_path(
    string $projectKey,
    string $compareOutputKey,
    string $flagKey,
): string {
    $path = app_project_compare_output_settings_path($projectKey, $compareOutputKey);
    return $path . (str_contains($path, '?') ? '&' : '?') . rawurlencode($flagKey) . '=1';
}

/**
 * @param list<array{
 *     form_key:string,
 *     current_content:string
 * }> $assetCatalog
 * @return array<string,string>
 */
function app_project_compare_output_asset_input_from_catalog(array $assetCatalog): array
{
    $input = [];
    foreach ($assetCatalog as $assetItem) {
        $input[$assetItem['form_key']] = $assetItem['current_content'];
    }

    return $input;
}

/**
 * @return array<string,string>
 */
function app_project_compare_output_asset_post_input(): array
{
    $input = [];
    foreach (app_compare_output_asset_specs() as $spec) {
        $input[$spec['form_key']] = app_post_param('asset_' . $spec['form_key']);
    }

    return $input;
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     },
 *     generated:array{
 *         root:string,
 *         dbclasses_root:string,
 *         dbclasses_loader:string,
 *         dbclasses_mode:string
 *     }
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_project_compare_output_settings_page(array $app, array $request): void
{
    $bootstrap = app_project_compare_output_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $principal = $bootstrap['principal'];
    $generatedRuntime = $bootstrap['generated_runtime'];
    $assetSpecs = app_compare_output_asset_specs();

    $defaults = app_compare_output_form_defaults();
    $createInput = $defaults;
    $selectedCompareOutputKey = '';
    $selectedCompareOutput = null;
    $selectedInput = $defaults;
    $selectedInputFromPost = false;
    $selectedAdditionalPathCount = 0;
    $selectedAdditionalPaths = [];
    $selectedResolvedPaths = null;
    $generationResult = null;
    $generateRequested = false;
    $assetCatalog = [];
    $assetCustomCount = 0;
    $assetStorageRoot = '';
    $assetInput = [];
    $assetInputFromPost = false;
    $errors = [];

    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $action = trim(app_post_param('action'));

            if ($action === 'create-compare-output') {
                $createInput = [
                    'compare_output_key' => app_normalize_compare_output_key(app_post_param('compare_output_key')),
                    'name' => app_post_param('name'),
                    'storage_base_path' => app_post_param('storage_base_path'),
                    'output_file_path' => app_post_param('output_file_path'),
                    'output_file_type' => app_post_param('output_file_type', $defaults['output_file_type']),
                    'compare_path' => app_post_param('compare_path'),
                    'compare_tool_file_path' => app_post_param('compare_tool_file_path'),
                    'compare_output_list_order' => app_post_param('compare_output_list_order'),
                    'notes' => app_post_param('notes'),
                    'source_of_truth' => $defaults['source_of_truth'],
                ];

                $catalogForOrderResult = app_fetch_project_compare_output_catalog($app, $projectKey);
                if (!$catalogForOrderResult['ok']) {
                    $errors[] = $catalogForOrderResult['error'];
                } else {
                    $nextOrder = 10;
                    foreach ($catalogForOrderResult['items'] as $item) {
                        $itemOrder = (int) $item['compare_output_list_order'];
                        if ($itemOrder >= $nextOrder) {
                            $nextOrder = $itemOrder + 10;
                        }
                    }

                    if ($createInput['compare_output_list_order'] === '') {
                        $createInput['compare_output_list_order'] = (string) $nextOrder;
                    }

                    $validation = app_validate_compare_output_form($createInput);
                    $createInput = $validation['input'];
                    $errors = array_merge($errors, $validation['errors']);

                    if ($errors === []) {
                        $createResult = app_create_project_compare_output($app, array_merge(
                            ['project_key' => $projectKey],
                            $validation['input'],
                        ));

                        if ($createResult['ok']) {
                            app_send_redirect_response(
                                $request,
                                app_project_compare_output_settings_path($projectKey, $validation['input']['compare_output_key']) . '&created=1',
                            );
                            return;
                        }

                        $errors[] = $createResult['error'];
                    }
                }
            } elseif ($action === 'update-compare-output') {
                $selectedCompareOutputKey = app_normalize_compare_output_key(app_post_param('compare_output_key'));
                $selectedInput = [
                    'compare_output_key' => $selectedCompareOutputKey,
                    'name' => app_post_param('name'),
                    'storage_base_path' => app_post_param('storage_base_path'),
                    'output_file_path' => app_post_param('output_file_path'),
                    'output_file_type' => app_post_param('output_file_type', $defaults['output_file_type']),
                    'compare_path' => app_post_param('compare_path'),
                    'compare_tool_file_path' => app_post_param('compare_tool_file_path'),
                    'compare_output_list_order' => app_post_param('compare_output_list_order'),
                    'notes' => app_post_param('notes'),
                    'source_of_truth' => app_post_param('source_of_truth', $defaults['source_of_truth']),
                ];
                $selectedInputFromPost = true;

                $validation = app_validate_compare_output_form($selectedInput);
                $selectedInput = $validation['input'];
                $errors = array_merge($errors, $validation['errors']);

                if ($errors === []) {
                    $updateResult = app_update_project_compare_output($app, array_merge(
                        ['project_key' => $projectKey],
                        $validation['input'],
                    ));

                    if ($updateResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_compare_output_settings_path($projectKey, $selectedCompareOutputKey) . '&updated=1',
                        );
                        return;
                    }

                    $errors[] = $updateResult['error'];
                }
            } elseif ($action === 'delete-compare-output') {
                $selectedCompareOutputKey = app_normalize_compare_output_key(app_post_param('compare_output_key'));
                if ($selectedCompareOutputKey === '' || !app_compare_output_key_is_valid($selectedCompareOutputKey)) {
                    $errors[] = '削除対象の compare output key が不正です。';
                } else {
                    $deleteResult = app_delete_project_compare_output($app, $projectKey, $selectedCompareOutputKey);
                    if ($deleteResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_compare_output_settings_path($projectKey) . '?deleted=1',
                        );
                        return;
                    }

                    $errors[] = $deleteResult['error'];
                }
            } elseif ($action === 'generate-compare-output-file') {
                $selectedCompareOutputKey = app_normalize_compare_output_key(app_post_param('compare_output_key'));
                if ($selectedCompareOutputKey === '' || !app_compare_output_key_is_valid($selectedCompareOutputKey)) {
                    $errors[] = '出力対象の compare output key が不正です。';
                } else {
                    $generateRequested = true;
                }
            } elseif ($action === 'save-compare-output-assets') {
                $selectedCompareOutputKey = app_normalize_compare_output_key(app_post_param('compare_output_key'));
                $assetInput = app_project_compare_output_asset_post_input();
                $assetInputFromPost = true;

                $validation = app_validate_compare_output_asset_submission($assetInput);
                $assetInput = $validation['input_by_form_key'];
                $errors = array_merge($errors, $validation['errors']);

                if ($errors === []) {
                    $saveAssetsResult = app_save_compare_output_assets(
                        $app,
                        $projectKey,
                        $validation['contents_by_filename'],
                    );
                    if ($saveAssetsResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_compare_output_settings_redirect_path(
                                $projectKey,
                                $selectedCompareOutputKey,
                                'assets_updated',
                            ),
                        );
                        return;
                    }

                    $errors[] = $saveAssetsResult['error'];
                }
            } elseif ($action === 'reset-compare-output-assets') {
                $selectedCompareOutputKey = app_normalize_compare_output_key(app_post_param('compare_output_key'));
                $resetAssetsResult = app_reset_compare_output_assets($app, $projectKey);
                if ($resetAssetsResult['ok']) {
                    app_send_redirect_response(
                        $request,
                        app_project_compare_output_settings_redirect_path(
                            $projectKey,
                            $selectedCompareOutputKey,
                            'assets_reset',
                        ),
                    );
                    return;
                }

                $errors[] = $resetAssetsResult['error'];
            } else {
                $errors[] = '未対応の操作です。';
            }
        }
    }

    $catalogResult = app_fetch_project_compare_output_catalog($app, $projectKey);
    if (!$catalogResult['ok']) {
        $errors[] = $catalogResult['error'];
    }
    $compareOutputs = $catalogResult['items'];

    $assetCatalogResult = app_compare_output_asset_catalog($app, $projectKey);
    if (!$assetCatalogResult['ok']) {
        $errors[] = $assetCatalogResult['error'];
    } else {
        $assetCatalog = $assetCatalogResult['items'];
        $assetCustomCount = $assetCatalogResult['custom_count'];
        $assetStorageRoot = $assetCatalogResult['storage_root'];
        if (!$assetInputFromPost) {
            $assetInput = app_project_compare_output_asset_input_from_catalog($assetCatalog);
        }
    }
    if ($assetInput === []) {
        foreach ($assetSpecs as $spec) {
            $assetInput[$spec['form_key']] = $spec['default_content'];
        }
    }

    if ($selectedCompareOutputKey === '') {
        $selectedCompareOutputKey = app_normalize_compare_output_key(app_query_param('compare_output_key'));
    }

    if ($selectedCompareOutputKey !== '') {
        if (!app_compare_output_key_is_valid($selectedCompareOutputKey)) {
            $errors[] = 'compare output key の形式が不正です。';
        } else {
            $itemResult = app_fetch_project_compare_output_item($app, $projectKey, $selectedCompareOutputKey);
            if (!$itemResult['ok']) {
                $errors[] = $itemResult['error'];
            } elseif ($itemResult['item'] === null) {
                $errors[] = '指定された compare output が見つかりません。';
            } else {
                $selectedCompareOutput = $itemResult['item'];
                if (!$selectedInputFromPost) {
                    $selectedInput = app_project_compare_output_form_from_item($selectedCompareOutput);
                }

                $additionalPathResult = app_fetch_project_compare_output_additional_path_catalog(
                    $app,
                    $projectKey,
                    $selectedCompareOutputKey,
                );
                if (!$additionalPathResult['ok']) {
                    $errors[] = $additionalPathResult['error'];
                } else {
                    $selectedAdditionalPaths = $additionalPathResult['items'];
                    $selectedAdditionalPathCount = count($additionalPathResult['items']);
                }
            }
        }
    }

    if ($selectedCompareOutput !== null) {
        $selectedResolvedPaths = app_compare_output_resolve_definition_paths($selectedCompareOutput);
    }

    if ($generateRequested && $selectedCompareOutput !== null && $errors === []) {
        $generationServiceResult = app_compare_output_generate_output_file(
            $app,
            $projectKey,
            $selectedCompareOutput,
            $selectedAdditionalPaths,
            'admin-ui:' . $principal['id'],
        );
        if ($generationServiceResult['ok'] && $generationServiceResult['output'] !== null) {
            $generationResult = $generationServiceResult['output'];
        } else {
            $errors[] = $generationServiceResult['error'];
        }
    }

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();
    $created = app_query_param('created') === '1';
    $updated = app_query_param('updated') === '1';
    $deleted = app_query_param('deleted') === '1';
    $assetsUpdated = app_query_param('assets_updated') === '1';
    $assetsReset = app_query_param('assets_reset') === '1';
    $assetItemsByFormKey = [];
    foreach ($assetCatalog as $assetItem) {
        $assetItemsByFormKey[$assetItem['form_key']] = $assetItem;
    }

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Compare Output Settings</title>
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
        pre {
            background: #edf2f7;
            padding: 0.9rem 1rem;
            border-radius: 8px;
            overflow-x: auto;
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
            background: #ecfdf5;
            border-color: #86efac;
        }
        .section-heading {
            margin-top: 2rem;
            margin-bottom: 0.25rem;
        }
        .muted {
            color: #475569;
        }
        .create-form, .edit-form {
            margin-top: 1rem;
            padding: 1.25rem;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            background: #f8fafc;
        }
        .button {
            display: inline-block;
            border: 0;
            border-radius: 8px;
            background: #0f172a;
            color: #ffffff;
            padding: 0.65rem 1rem;
            font: inherit;
            cursor: pointer;
            text-decoration: none;
        }
        .button-secondary {
            background: #475569;
        }
        .button-danger {
            background: #b91c1c;
        }
        .button-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            border-bottom: 1px solid #d7dde5;
            padding: 0.75rem;
            text-align: left;
            vertical-align: top;
        }
        .selected-row {
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
            font: inherit;
            background: #ffffff;
        }
        textarea {
            min-height: 7rem;
        }
        .form-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }
        .inline-form {
            margin-top: 0.5rem;
        }
        .asset-card {
            margin-top: 1rem;
            padding: 1.25rem;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            background: #f8fafc;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / compare-output-settings</p>

    <h1><?php echo app_h($project['name']); ?> Compare Output 設定</h1>
    <p>compare output definition、additional paths、template asset、ignore rule asset を <code>admin</code> 側で管理する画面です。<code>lab</code> 側では、ここで保存した canonical definition を read-only 参照して実行と review を行います。</p>

    <?php if ($created || $updated || $deleted || $assetsUpdated || $assetsReset || $generationResult !== null): ?>
        <section class="success-card">
            <h2>更新結果</h2>
            <ul>
                <?php if ($created): ?>
                    <li>compare output definition を作成しました。</li>
                <?php endif; ?>
                <?php if ($updated): ?>
                    <li>compare output definition を更新しました。</li>
                <?php endif; ?>
                <?php if ($deleted): ?>
                    <li>compare output definition を削除しました。</li>
                <?php endif; ?>
                <?php if ($assetsUpdated): ?>
                    <li>compare output asset を保存しました。</li>
                <?php endif; ?>
                <?php if ($assetsReset): ?>
                    <li>compare output asset の project override を削除し、default に戻しました。</li>
                <?php endif; ?>
                <?php if ($generationResult !== null): ?>
                    <li>compare output file を生成しました: <code><?php echo app_h($generationResult['output_file_absolute_path']); ?></code></li>
                <?php endif; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <section class="error-card">
            <h2>エラー</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Project</h2>
            <ul>
                <li>project key: <code><?php echo app_h($project['project_key']); ?></code></li>
                <li>slug: <code><?php echo app_h($project['slug']); ?></code></li>
                <li>status: <code><?php echo app_h($project['lifecycle_status']); ?></code></li>
                <li>login user: <code><?php echo app_h($principal['id']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Current Runtime</h2>
            <ul>
                <li>mode: <code><?php echo app_h($generatedRuntime['dbclasses_mode']); ?></code></li>
                <li>runtime root: <code><?php echo app_h($generatedRuntime['dbclasses_root']); ?></code></li>
                <li>loader exists: <code><?php echo app_h($generatedRuntime['dbclasses_loader_exists'] ? 'yes' : 'no'); ?></code></li>
                <li>file count: <code><?php echo app_h((string) $generatedRuntime['total_file_count']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Canonical Definitions</h2>
            <ul>
                <li>compare outputs: <code><?php echo app_h((string) count($compareOutputs)); ?></code></li>
                <li>selected: <code><?php echo app_h($selectedCompareOutputKey !== '' ? $selectedCompareOutputKey : 'none'); ?></code></li>
                <li>selected additional paths: <code><?php echo app_h((string) $selectedAdditionalPathCount); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Compare Assets</h2>
            <ul>
                <li>asset files: <code><?php echo app_h((string) count($assetSpecs)); ?></code></li>
                <li>custom overrides: <code><?php echo app_h((string) $assetCustomCount); ?></code></li>
                <li>storage root: <code><?php echo app_h($assetStorageRoot !== '' ? $assetStorageRoot : '(unavailable)'); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>現段階の制約</h2>
            <p class="muted">definition と asset はここで保持し、compare 実行 job の生成と review は <code>lab:/runs/compare-output/*</code> 側で扱います。job 履歴は現時点では file-based です。</p>
        </section>
    </div>

    <section>
        <h2 class="section-heading">Template / Ignore Assets</h2>
        <p class="muted">未保存の asset は built-in default を使います。project override を保存すると <code><?php echo app_h(app_runtime_storage_work_repo_relative_path(app_runtime_storage_compare_output_assets_relative_path($projectKey))); ?></code> 配下の file が優先されます。</p>

        <form class="edit-form" method="post">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="action" value="save-compare-output-assets">
            <input type="hidden" name="compare_output_key" value="<?php echo app_h($selectedCompareOutputKey); ?>">

            <?php foreach ($assetSpecs as $spec): ?>
                <?php $assetItem = $assetItemsByFormKey[$spec['form_key']] ?? null; ?>
                <section class="asset-card">
                    <h3><?php echo app_h($spec['label']); ?></h3>
                    <p class="muted">
                        filename: <code><?php echo app_h($spec['filename']); ?></code>
                        /
                        source: <code><?php echo app_h($assetItem !== null && $assetItem['is_custom'] ? 'project-custom' : 'built-in-default'); ?></code>
                    </p>
                    <p class="muted"><?php echo app_h($spec['description']); ?></p>
                    <textarea name="asset_<?php echo app_h($spec['form_key']); ?>"><?php echo app_h($assetInput[$spec['form_key']] ?? $spec['default_content']); ?></textarea>
                </section>
            <?php endforeach; ?>

            <div class="button-row">
                <button class="button" type="submit">Save Compare Output Assets</button>
            </div>
        </form>

        <form class="inline-form" method="post">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="action" value="reset-compare-output-assets">
            <input type="hidden" name="compare_output_key" value="<?php echo app_h($selectedCompareOutputKey); ?>">
            <button class="button button-secondary" type="submit">Reset Project Asset Overrides</button>
        </form>
    </section>

    <section>
        <h2 class="section-heading">Create Definition</h2>
        <form class="create-form" method="post">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="action" value="create-compare-output">

            <div class="form-grid">
                <label>
                    compare_output_key
                    <input name="compare_output_key" value="<?php echo app_h($createInput['compare_output_key']); ?>" placeholder="MAIN">
                </label>

                <label>
                    name
                    <input name="name" value="<?php echo app_h($createInput['name']); ?>" placeholder="Mtool Main Compare Output">
                </label>

                <label>
                    output_file_type
                    <select name="output_file_type">
                        <?php foreach (app_allowed_compare_output_file_types() as $fileType): ?>
                            <option value="<?php echo app_h($fileType); ?>"<?php echo $createInput['output_file_type'] === $fileType ? ' selected' : ''; ?>><?php echo app_h(app_compare_output_file_type_caption($fileType)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    compare_output_list_order
                    <input name="compare_output_list_order" value="<?php echo app_h($createInput['compare_output_list_order']); ?>" inputmode="numeric" pattern="[0-9]*" placeholder="100">
                </label>
            </div>

            <label>
                storage_base_path
                <input name="storage_base_path" value="<?php echo app_h($createInput['storage_base_path']); ?>" placeholder="<?php echo app_h(app_runtime_storage_work_repo_relative_path(app_runtime_storage_compare_output_workspace_relative_path('MTOOL', 'MAIN'))); ?>">
            </label>

            <label>
                output_file_path
                <input name="output_file_path" value="<?php echo app_h($createInput['output_file_path']); ?>" placeholder="output/exec_compare_difference_Main.command">
            </label>

            <label>
                compare_path
                <input name="compare_path" value="<?php echo app_h($createInput['compare_path']); ?>" placeholder="compare-root">
            </label>

            <label>
                compare_tool_file_path
                <input name="compare_tool_file_path" value="<?php echo app_h($createInput['compare_tool_file_path']); ?>" placeholder="/Applications/Beyond\\ Compare.app/Contents/MacOS/bcomp">
            </label>

            <label>
                notes
                <textarea name="notes" placeholder="compare output definition memo"><?php echo app_h($createInput['notes']); ?></textarea>
            </label>

            <p class="muted"><code>Windows Batch</code> または <code>Mac Command</code> を選ぶ場合は <code>compare_tool_file_path</code> が必須です。</p>
            <button class="button" type="submit">Create Compare Output Definition</button>
        </form>
    </section>

    <section>
        <h2 class="section-heading">Definitions</h2>
        <?php if ($compareOutputs === []): ?>
            <p class="muted">まだ compare output definition はありません。</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>definition</th>
                    <th>output target</th>
                    <th>compare target</th>
                    <th>action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($compareOutputs as $compareOutput): ?>
                    <?php $isSelected = $selectedCompareOutputKey !== '' && $selectedCompareOutputKey === $compareOutput['compare_output_key']; ?>
                    <tr<?php echo $isSelected ? ' class="selected-row"' : ''; ?>>
                        <td>
                            <strong><code><?php echo app_h($compareOutput['compare_output_key']); ?></code></strong><br>
                            <?php echo app_h($compareOutput['name']); ?><br>
                            <span class="muted">updated: <?php echo app_h($compareOutput['updated_at']); ?></span><br>
                            <span class="muted">source: <?php echo app_h($compareOutput['source_of_truth']); ?></span>
                        </td>
                        <td>
                            <code><?php echo app_h(app_compare_output_file_type_caption($compareOutput['output_file_type'])); ?></code><br>
                            <code><?php echo app_h($compareOutput['output_file_path']); ?></code><br>
                            <?php if ($compareOutput['storage_base_path'] !== ''): ?>
                                <span class="muted">base: <?php echo app_h($compareOutput['storage_base_path']); ?></span>
                            <?php else: ?>
                                <span class="muted">base: (blank)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code><?php echo app_h($compareOutput['compare_path']); ?></code><br>
                            <?php if ($compareOutput['compare_tool_file_path'] !== ''): ?>
                                <span class="muted">tool: <?php echo app_h($compareOutput['compare_tool_file_path']); ?></span>
                            <?php else: ?>
                                <span class="muted">tool: (blank)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo app_h(app_project_compare_output_settings_path($projectKey, $compareOutput['compare_output_key'])); ?>">select</a><br>
                            <a href="<?php echo app_h(app_project_compare_output_additional_paths_path($projectKey, $compareOutput['compare_output_key'])); ?>">additional paths</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <?php if ($selectedCompareOutput !== null): ?>
        <section>
            <h2 class="section-heading">Edit Selected Definition</h2>
            <p>現在選択中: <code><?php echo app_h($selectedCompareOutput['compare_output_key']); ?></code></p>

            <?php if ($selectedResolvedPaths !== null): ?>
                <div class="summary-grid">
                    <section class="summary-card">
                        <h3>Resolved Paths</h3>
                        <?php if ($selectedResolvedPaths['ok']): ?>
                            <ul>
                                <li>storage base: <code><?php echo app_h($selectedResolvedPaths['resolved_storage_base_path']); ?></code></li>
                                <li>compare root: <code><?php echo app_h($selectedResolvedPaths['compare_root_absolute_path']); ?></code></li>
                                <li>output file: <code><?php echo app_h($selectedResolvedPaths['output_file_absolute_path']); ?></code></li>
                                <li>output exists: <code><?php echo app_h(is_file($selectedResolvedPaths['output_file_absolute_path']) ? 'yes' : 'no'); ?></code></li>
                            </ul>
                        <?php else: ?>
                            <p class="muted"><?php echo app_h($selectedResolvedPaths['error']); ?></p>
                        <?php endif; ?>
                    </section>

                    <section class="note-card">
                        <h3>Generate</h3>
                        <p class="muted">現在は local filesystem 上で <code>compare_path</code> 配下の <code>- tmp output</code> folder と対応 folder を比較し、additional path の差分行も合わせて <code>output_file_path</code> へ出力します。</p>
                        <p class="muted">CLI: <code>php mtool/scripts/create_compare_output.php --project-key=<?php echo app_h($projectKey); ?> --compare-output-key=<?php echo app_h($selectedCompareOutput['compare_output_key']); ?></code></p>
                        <form class="inline-form" method="post">
                            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
                            <input type="hidden" name="action" value="generate-compare-output-file">
                            <input type="hidden" name="compare_output_key" value="<?php echo app_h($selectedCompareOutput['compare_output_key']); ?>">
                            <button class="button button-secondary" type="submit">Generate Output File</button>
                        </form>
                    </section>
                </div>
            <?php endif; ?>

            <?php if ($generationResult !== null): ?>
                <section class="summary-card">
                    <h3>Latest Generation</h3>
                    <ul>
                        <li>deviation pairs: <code><?php echo app_h((string) $generationResult['deviation_pair_count']); ?></code></li>
                        <li>checked pairs: <code><?php echo app_h((string) $generationResult['checked_pair_count']); ?></code></li>
                        <li>output bytes: <code><?php echo app_h((string) $generationResult['output_bytes']); ?></code></li>
                        <li>requested by: <code><?php echo app_h($generationResult['requested_by']); ?></code></li>
                        <li>created at: <code><?php echo app_h($generationResult['created_at']); ?></code></li>
                    </ul>
                    <?php if ($generationResult['warnings'] !== []): ?>
                        <h4>Warnings</h4>
                        <ul>
                            <?php foreach ($generationResult['warnings'] as $warning): ?>
                                <li><?php echo app_h($warning); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <h4>Preview</h4>
                    <pre><?php echo app_h($generationResult['rendered_content']); ?></pre>
                </section>
            <?php endif; ?>

            <form class="edit-form" method="post">
                <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
                <input type="hidden" name="action" value="update-compare-output">
                <input type="hidden" name="compare_output_key" value="<?php echo app_h($selectedCompareOutput['compare_output_key']); ?>">

                <div class="form-grid">
                    <label>
                        compare_output_key
                        <input value="<?php echo app_h($selectedCompareOutput['compare_output_key']); ?>" disabled>
                    </label>

                    <label>
                        name
                        <input name="name" value="<?php echo app_h($selectedInput['name']); ?>">
                    </label>

                    <label>
                        output_file_type
                        <select name="output_file_type">
                            <?php foreach (app_allowed_compare_output_file_types() as $fileType): ?>
                                <option value="<?php echo app_h($fileType); ?>"<?php echo $selectedInput['output_file_type'] === $fileType ? ' selected' : ''; ?>><?php echo app_h(app_compare_output_file_type_caption($fileType)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        compare_output_list_order
                        <input name="compare_output_list_order" value="<?php echo app_h($selectedInput['compare_output_list_order']); ?>" inputmode="numeric" pattern="[0-9]*">
                    </label>
                </div>

                <label>
                    storage_base_path
                    <input name="storage_base_path" value="<?php echo app_h($selectedInput['storage_base_path']); ?>">
                </label>

                <label>
                    output_file_path
                    <input name="output_file_path" value="<?php echo app_h($selectedInput['output_file_path']); ?>">
                </label>

                <label>
                    compare_path
                    <input name="compare_path" value="<?php echo app_h($selectedInput['compare_path']); ?>">
                </label>

                <label>
                    compare_tool_file_path
                    <input name="compare_tool_file_path" value="<?php echo app_h($selectedInput['compare_tool_file_path']); ?>">
                </label>

                <label>
                    source_of_truth
                    <select name="source_of_truth">
                        <?php foreach (app_allowed_compare_output_source_of_truths() as $sourceOfTruth): ?>
                            <option value="<?php echo app_h($sourceOfTruth); ?>"<?php echo $selectedInput['source_of_truth'] === $sourceOfTruth ? ' selected' : ''; ?>><?php echo app_h($sourceOfTruth); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    notes
                    <textarea name="notes"><?php echo app_h($selectedInput['notes']); ?></textarea>
                </label>

                <div class="button-row">
                    <button class="button" type="submit">Save Definition</button>
                    <a class="button button-secondary" href="<?php echo app_h(app_project_compare_output_additional_paths_path($projectKey, $selectedCompareOutput['compare_output_key'])); ?>">Open Additional Paths</a>
                </div>
            </form>

            <form class="inline-form" method="post">
                <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
                <input type="hidden" name="action" value="delete-compare-output">
                <input type="hidden" name="compare_output_key" value="<?php echo app_h($selectedCompareOutput['compare_output_key']); ?>">
                <button class="button button-danger" type="submit">Delete Definition</button>
            </form>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
