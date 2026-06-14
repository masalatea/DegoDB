<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/project_output_service.php';
require_once __DIR__ . '/project_compare_output_route_common.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/runtime_storage_paths.php';

/**
 * @param array{
 *     additional_path_key:string,
 *     path_a_base_path:string,
 *     path_a:string,
 *     path_b_base_path:string,
 *     path_b:string,
 *     is_same_filename_only:string,
 *     additional_path_list_order:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at:string
 * } $item
 * @return array{
 *     additional_path_key:string,
 *     path_a_base_path:string,
 *     path_a:string,
 *     path_b_base_path:string,
 *     path_b:string,
 *     is_same_filename_only:string,
 *     additional_path_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * }
 */
function app_project_compare_output_additional_path_form_from_item(array $item): array
{
    return [
        'additional_path_key' => $item['additional_path_key'],
        'path_a_base_path' => $item['path_a_base_path'],
        'path_a' => $item['path_a'],
        'path_b_base_path' => $item['path_b_base_path'],
        'path_b' => $item['path_b'],
        'is_same_filename_only' => $item['is_same_filename_only'],
        'additional_path_list_order' => $item['additional_path_list_order'],
        'notes' => $item['notes'],
        'source_of_truth' => $item['source_of_truth'],
    ];
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
function app_render_project_compare_output_additional_paths_page(array $app, array $request): void
{
    $bootstrap = app_project_compare_output_item_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $principal = $bootstrap['principal'];
    $compareOutputKey = $bootstrap['compare_output_key'];
    $compareOutput = $bootstrap['compare_output'];

    $defaults = app_compare_output_additional_path_form_defaults();
    $createInput = $defaults;
    $selectedAdditionalPathKey = '';
    $selectedAdditionalPath = null;
    $selectedInput = $defaults;
    $selectedInputFromPost = false;
    $errors = [];

    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $action = trim(app_post_param('action'));

            if ($action === 'create-additional-path') {
                $createInput = [
                    'additional_path_key' => app_normalize_compare_output_additional_path_key(app_post_param('additional_path_key')),
                    'path_a_base_path' => app_post_param('path_a_base_path'),
                    'path_a' => app_post_param('path_a'),
                    'path_b_base_path' => app_post_param('path_b_base_path'),
                    'path_b' => app_post_param('path_b'),
                    'is_same_filename_only' => app_post_param('is_same_filename_only', $defaults['is_same_filename_only']),
                    'additional_path_list_order' => app_post_param('additional_path_list_order'),
                    'notes' => app_post_param('notes'),
                    'source_of_truth' => $defaults['source_of_truth'],
                ];

                $catalogForOrderResult = app_fetch_project_compare_output_additional_path_catalog(
                    $app,
                    $projectKey,
                    $compareOutputKey,
                );
                if (!$catalogForOrderResult['ok']) {
                    $errors[] = $catalogForOrderResult['error'];
                } else {
                    $nextOrder = 10;
                    foreach ($catalogForOrderResult['items'] as $item) {
                        $itemOrder = (int) $item['additional_path_list_order'];
                        if ($itemOrder >= $nextOrder) {
                            $nextOrder = $itemOrder + 10;
                        }
                    }

                    if ($createInput['additional_path_list_order'] === '') {
                        $createInput['additional_path_list_order'] = (string) $nextOrder;
                    }

                    $validation = app_validate_compare_output_additional_path_form($createInput);
                    $createInput = $validation['input'];
                    $errors = array_merge($errors, $validation['errors']);

                    if ($errors === []) {
                        $createResult = app_create_project_compare_output_additional_path($app, array_merge(
                            [
                                'project_key' => $projectKey,
                                'compare_output_key' => $compareOutputKey,
                            ],
                            $validation['input'],
                        ));

                        if ($createResult['ok']) {
                            app_send_redirect_response(
                                $request,
                                app_project_compare_output_additional_paths_path(
                                    $projectKey,
                                    $compareOutputKey,
                                    $validation['input']['additional_path_key'],
                                ) . '&created=1',
                            );
                            return;
                        }

                        $errors[] = $createResult['error'];
                    }
                }
            } elseif ($action === 'update-additional-path') {
                $selectedAdditionalPathKey = app_normalize_compare_output_additional_path_key(app_post_param('additional_path_key'));
                $selectedInput = [
                    'additional_path_key' => $selectedAdditionalPathKey,
                    'path_a_base_path' => app_post_param('path_a_base_path'),
                    'path_a' => app_post_param('path_a'),
                    'path_b_base_path' => app_post_param('path_b_base_path'),
                    'path_b' => app_post_param('path_b'),
                    'is_same_filename_only' => app_post_param('is_same_filename_only', $defaults['is_same_filename_only']),
                    'additional_path_list_order' => app_post_param('additional_path_list_order'),
                    'notes' => app_post_param('notes'),
                    'source_of_truth' => app_post_param('source_of_truth', $defaults['source_of_truth']),
                ];
                $selectedInputFromPost = true;

                $validation = app_validate_compare_output_additional_path_form($selectedInput);
                $selectedInput = $validation['input'];
                $errors = array_merge($errors, $validation['errors']);

                if ($errors === []) {
                    $updateResult = app_update_project_compare_output_additional_path($app, array_merge(
                        [
                            'project_key' => $projectKey,
                            'compare_output_key' => $compareOutputKey,
                        ],
                        $validation['input'],
                    ));

                    if ($updateResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_compare_output_additional_paths_path(
                                $projectKey,
                                $compareOutputKey,
                                $selectedAdditionalPathKey,
                            ) . '&updated=1',
                        );
                        return;
                    }

                    $errors[] = $updateResult['error'];
                }
            } elseif ($action === 'delete-additional-path') {
                $selectedAdditionalPathKey = app_normalize_compare_output_additional_path_key(app_post_param('additional_path_key'));
                if ($selectedAdditionalPathKey === '' || !app_compare_output_additional_path_key_is_valid($selectedAdditionalPathKey)) {
                    $errors[] = '削除対象の additional_path_key が不正です。';
                } else {
                    $deleteResult = app_delete_project_compare_output_additional_path(
                        $app,
                        $projectKey,
                        $compareOutputKey,
                        $selectedAdditionalPathKey,
                    );
                    if ($deleteResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_compare_output_additional_paths_path($projectKey, $compareOutputKey) . '&deleted=1',
                        );
                        return;
                    }

                    $errors[] = $deleteResult['error'];
                }
            } else {
                $errors[] = '未対応の操作です。';
            }
        }
    }

    $catalogResult = app_fetch_project_compare_output_additional_path_catalog($app, $projectKey, $compareOutputKey);
    if (!$catalogResult['ok']) {
        $errors[] = $catalogResult['error'];
    }
    $additionalPaths = $catalogResult['items'];

    if ($selectedAdditionalPathKey === '') {
        $selectedAdditionalPathKey = app_normalize_compare_output_additional_path_key(app_query_param('additional_path_key'));
    }

    if ($selectedAdditionalPathKey !== '') {
        if (!app_compare_output_additional_path_key_is_valid($selectedAdditionalPathKey)) {
            $errors[] = 'additional_path_key の形式が不正です。';
        } else {
            $itemResult = app_fetch_project_compare_output_additional_path_item(
                $app,
                $projectKey,
                $compareOutputKey,
                $selectedAdditionalPathKey,
            );
            if (!$itemResult['ok']) {
                $errors[] = $itemResult['error'];
            } elseif ($itemResult['item'] === null) {
                $errors[] = '指定された additional path が見つかりません。';
            } else {
                $selectedAdditionalPath = $itemResult['item'];
                if (!$selectedInputFromPost) {
                    $selectedInput = app_project_compare_output_additional_path_form_from_item($selectedAdditionalPath);
                }
            }
        }
    }

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();
    $created = app_query_param('created') === '1';
    $updated = app_query_param('updated') === '1';
    $deleted = app_query_param('deleted') === '1';

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Compare Output Additional Paths</title>
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
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="<?php echo app_h(app_project_compare_output_settings_path($projectKey, $compareOutputKey)); ?>">compare-output-settings</a> / additional-paths</p>

    <h1><?php echo app_h($project['name']); ?> Compare Output Additional Paths</h1>
    <p>選択中の compare output definition に対して、<code>lab</code> 実行時に追加で比較する path 組を管理する画面です。旧実装の PID ではなく、base path と相対 path の組をそのまま保持する形に寄せています。</p>

    <?php if ($created || $updated || $deleted): ?>
        <section class="success-card">
            <h2>更新結果</h2>
            <ul>
                <?php if ($created): ?>
                    <li>additional path を作成しました。</li>
                <?php endif; ?>
                <?php if ($updated): ?>
                    <li>additional path を更新しました。</li>
                <?php endif; ?>
                <?php if ($deleted): ?>
                    <li>additional path を削除しました。</li>
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
                <li>login user: <code><?php echo app_h($principal['id']); ?></code></li>
                <li>compare output key: <code><?php echo app_h($compareOutput['compare_output_key']); ?></code></li>
                <li>selected additional path: <code><?php echo app_h($selectedAdditionalPathKey !== '' ? $selectedAdditionalPathKey : 'none'); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Compare Output</h2>
            <ul>
                <li>name: <code><?php echo app_h($compareOutput['name']); ?></code></li>
                <li>file type: <code><?php echo app_h(app_compare_output_file_type_caption($compareOutput['output_file_type'])); ?></code></li>
                <li>output path: <code><?php echo app_h($compareOutput['output_file_path']); ?></code></li>
                <li>compare path: <code><?php echo app_h($compareOutput['compare_path']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Additional Paths</h2>
            <ul>
                <li>count: <code><?php echo app_h((string) count($additionalPaths)); ?></code></li>
                <li>source of truth: <code><?php echo app_h($compareOutput['source_of_truth']); ?></code></li>
                <li>updated: <code><?php echo app_h($compareOutput['updated_at']); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>現段階の制約</h2>
            <p class="muted">ここで保存した追加 path は <code>lab</code> 側の compare output job に取り込まれます。結果ファイルと review は <code>/runs/compare-output/{project_key}</code> から確認します。</p>
        </section>
    </div>

    <section>
        <h2 class="section-heading">Create Additional Path</h2>
        <form class="create-form" method="post">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="action" value="create-additional-path">

            <div class="form-grid">
                <label>
                    additional_path_key
                    <input name="additional_path_key" value="<?php echo app_h($createInput['additional_path_key']); ?>" placeholder="SRC-AND-DST">
                </label>

                <label>
                    additional_path_list_order
                    <input name="additional_path_list_order" value="<?php echo app_h($createInput['additional_path_list_order']); ?>" inputmode="numeric" pattern="[0-9]*" placeholder="100">
                </label>

                <label>
                    is_same_filename_only
                    <select name="is_same_filename_only">
                        <option value="0"<?php echo $createInput['is_same_filename_only'] === '0' ? ' selected' : ''; ?>>0: compare explicit path pair</option>
                        <option value="1"<?php echo $createInput['is_same_filename_only'] === '1' ? ' selected' : ''; ?>>1: same filename only</option>
                    </select>
                </label>
            </div>

            <label>
                path_a_base_path
                <input name="path_a_base_path" value="<?php echo app_h($createInput['path_a_base_path']); ?>" placeholder="<?php echo app_h(app_project_output_default_relative_path('MTOOL')); ?>">
            </label>

            <label>
                path_a
                <input name="path_a" value="<?php echo app_h($createInput['path_a']); ?>" placeholder="source/path/a">
            </label>

            <label>
                path_b_base_path
                <input name="path_b_base_path" value="<?php echo app_h($createInput['path_b_base_path']); ?>" placeholder="<?php echo app_h(app_runtime_storage_work_repo_relative_path(app_runtime_storage_compare_output_workspace_relative_path('MTOOL'))); ?>">
            </label>

            <label>
                path_b
                <input name="path_b" value="<?php echo app_h($createInput['path_b']); ?>" placeholder="target/path/b">
            </label>

            <label>
                notes
                <textarea name="notes" placeholder="additional path memo"><?php echo app_h($createInput['notes']); ?></textarea>
            </label>

            <p class="muted">raw generated output は全 project で <code>work/source-outputs/{project_key}/{source_output_key}</code> を既定にします。companion layer は <code>mtool/extensions/{project_key}/{source_output_key}</code>、artifact snapshot 比較は <code>work/artifacts/source-outputs/{project_key}/{artifact_key}</code>、repo に残す sample reference 比較は対応する pack の <code>sample/&lt;category&gt;/&lt;pack&gt;/reference/&lt;source_output_key&gt;/</code> を使います。</p>

            <button class="button" type="submit">Create Additional Path</button>
        </form>
    </section>

    <section>
        <h2 class="section-heading">Additional Path List</h2>
        <?php if ($additionalPaths === []): ?>
            <p class="muted">まだ additional path はありません。</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>key</th>
                    <th>path A</th>
                    <th>path B</th>
                    <th>rule</th>
                    <th>action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($additionalPaths as $additionalPath): ?>
                    <?php $isSelected = $selectedAdditionalPathKey !== '' && $selectedAdditionalPathKey === $additionalPath['additional_path_key']; ?>
                    <tr<?php echo $isSelected ? ' class="selected-row"' : ''; ?>>
                        <td>
                            <strong><code><?php echo app_h($additionalPath['additional_path_key']); ?></code></strong><br>
                            <span class="muted">updated: <?php echo app_h($additionalPath['updated_at']); ?></span><br>
                            <span class="muted">source: <?php echo app_h($additionalPath['source_of_truth']); ?></span>
                        </td>
                        <td>
                            <?php if ($additionalPath['path_a_base_path'] !== ''): ?>
                                <span class="muted">base: <?php echo app_h($additionalPath['path_a_base_path']); ?></span><br>
                            <?php endif; ?>
                            <code><?php echo app_h($additionalPath['path_a']); ?></code>
                        </td>
                        <td>
                            <?php if ($additionalPath['path_b_base_path'] !== ''): ?>
                                <span class="muted">base: <?php echo app_h($additionalPath['path_b_base_path']); ?></span><br>
                            <?php endif; ?>
                            <code><?php echo app_h($additionalPath['path_b']); ?></code>
                        </td>
                        <td>
                            <code><?php echo app_h($additionalPath['is_same_filename_only'] === '1' ? 'same filename only' : 'explicit path pair'); ?></code><br>
                            <span class="muted">order: <?php echo app_h($additionalPath['additional_path_list_order']); ?></span>
                        </td>
                        <td>
                            <a href="<?php echo app_h(app_project_compare_output_additional_paths_path($projectKey, $compareOutputKey, $additionalPath['additional_path_key'])); ?>">select</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <?php if ($selectedAdditionalPath !== null): ?>
        <section>
            <h2 class="section-heading">Edit Selected Additional Path</h2>
            <p>現在選択中: <code><?php echo app_h($selectedAdditionalPath['additional_path_key']); ?></code></p>

            <form class="edit-form" method="post">
                <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
                <input type="hidden" name="action" value="update-additional-path">
                <input type="hidden" name="additional_path_key" value="<?php echo app_h($selectedAdditionalPath['additional_path_key']); ?>">

                <div class="form-grid">
                    <label>
                        additional_path_key
                        <input value="<?php echo app_h($selectedAdditionalPath['additional_path_key']); ?>" disabled>
                    </label>

                    <label>
                        additional_path_list_order
                        <input name="additional_path_list_order" value="<?php echo app_h($selectedInput['additional_path_list_order']); ?>" inputmode="numeric" pattern="[0-9]*">
                    </label>

                    <label>
                        is_same_filename_only
                        <select name="is_same_filename_only">
                            <option value="0"<?php echo $selectedInput['is_same_filename_only'] === '0' ? ' selected' : ''; ?>>0: compare explicit path pair</option>
                            <option value="1"<?php echo $selectedInput['is_same_filename_only'] === '1' ? ' selected' : ''; ?>>1: same filename only</option>
                        </select>
                    </label>
                </div>

                <label>
                    path_a_base_path
                    <input name="path_a_base_path" value="<?php echo app_h($selectedInput['path_a_base_path']); ?>">
                </label>

                <label>
                    path_a
                    <input name="path_a" value="<?php echo app_h($selectedInput['path_a']); ?>">
                </label>

                <label>
                    path_b_base_path
                    <input name="path_b_base_path" value="<?php echo app_h($selectedInput['path_b_base_path']); ?>">
                </label>

                <label>
                    path_b
                    <input name="path_b" value="<?php echo app_h($selectedInput['path_b']); ?>">
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
                    <button class="button" type="submit">Save Additional Path</button>
                    <a class="button button-secondary" href="<?php echo app_h(app_project_compare_output_settings_path($projectKey, $compareOutputKey)); ?>">Back To Compare Output</a>
                </div>
            </form>

            <form class="inline-form" method="post">
                <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
                <input type="hidden" name="action" value="delete-additional-path">
                <input type="hidden" name="additional_path_key" value="<?php echo app_h($selectedAdditionalPath['additional_path_key']); ?>">
                <button class="button button-danger" type="submit">Delete Additional Path</button>
            </form>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
