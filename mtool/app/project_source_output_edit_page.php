<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/project_output_service.php';
require_once __DIR__ . '/project_source_output_route_common.php';
require_once __DIR__ . '/request.php';

/**
 * @param array{
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     target_binding_type:string,
 *     spec_visibility:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at:string
 * } $item
 * @return array{
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     target_binding_type:string,
 *     spec_visibility:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * }
 */
function app_project_source_output_form_from_item(array $item): array
{
    return [
        'source_output_key' => $item['source_output_key'],
        'name' => $item['name'],
        'program_language' => $item['program_language'],
        'class_type' => $item['class_type'],
        'release_target_type' => $item['release_target_type'],
        'source_template_dir' => $item['source_template_dir'],
        'source_output_dir' => $item['source_output_dir'],
        'source_temp_output_dir' => $item['source_temp_output_dir'],
        'proxy_base_url' => $item['proxy_base_url'],
        'autoload_filename_suffix' => $item['autoload_filename_suffix'],
        'source_text_char_code' => $item['source_text_char_code'],
        'runtime_source_relative_path' => $item['runtime_source_relative_path'],
        'artifact_strategy' => $item['artifact_strategy'],
        'target_binding_type' => $item['target_binding_type'],
        'spec_visibility' => app_source_output_effective_spec_visibility($item),
        'output_archive_format' => $item['output_archive_format'],
        'source_output_list_order' => $item['source_output_list_order'],
        'notes' => $item['notes'],
        'source_of_truth' => $item['source_of_truth'],
    ];
}

function app_project_source_output_form_post_value(string $name, string $fallback = ''): string
{
    $value = $_POST[$name] ?? null;
    if (is_array($value)) {
        return $fallback;
    }

    if (is_string($value) || is_numeric($value)) {
        return trim((string) $value);
    }

    return $fallback;
}

/**
 * @param array{
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     target_binding_type:string,
 *     spec_visibility:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * } $fallback
 * @return array{
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     target_binding_type:string,
 *     spec_visibility:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * }
 */
function app_project_source_output_form_from_post(array $fallback): array
{
    return [
        'source_output_key' => app_normalize_source_output_key(
            app_project_source_output_form_post_value(
                'source_output_key',
                (string) ($fallback['source_output_key'] ?? ''),
            ),
        ),
        'name' => app_project_source_output_form_post_value(
            'name',
            (string) ($fallback['name'] ?? ''),
        ),
        'program_language' => app_project_source_output_form_post_value(
            'program_language',
            (string) ($fallback['program_language'] ?? ''),
        ),
        'class_type' => app_project_source_output_form_post_value(
            'class_type',
            (string) ($fallback['class_type'] ?? ''),
        ),
        'release_target_type' => app_project_source_output_form_post_value(
            'release_target_type',
            (string) ($fallback['release_target_type'] ?? ''),
        ),
        'source_template_dir' => app_project_source_output_form_post_value(
            'source_template_dir',
            (string) ($fallback['source_template_dir'] ?? ''),
        ),
        'source_output_dir' => app_project_source_output_form_post_value(
            'source_output_dir',
            (string) ($fallback['source_output_dir'] ?? ''),
        ),
        'source_temp_output_dir' => app_project_source_output_form_post_value(
            'source_temp_output_dir',
            (string) ($fallback['source_temp_output_dir'] ?? ''),
        ),
        'proxy_base_url' => app_project_source_output_form_post_value(
            'proxy_base_url',
            (string) ($fallback['proxy_base_url'] ?? ''),
        ),
        'autoload_filename_suffix' => app_project_source_output_form_post_value(
            'autoload_filename_suffix',
            (string) ($fallback['autoload_filename_suffix'] ?? ''),
        ),
        'source_text_char_code' => app_project_source_output_form_post_value(
            'source_text_char_code',
            (string) ($fallback['source_text_char_code'] ?? ''),
        ),
        'runtime_source_relative_path' => app_project_source_output_form_post_value(
            'runtime_source_relative_path',
            (string) ($fallback['runtime_source_relative_path'] ?? ''),
        ),
        'artifact_strategy' => app_project_source_output_form_post_value(
            'artifact_strategy',
            (string) ($fallback['artifact_strategy'] ?? ''),
        ),
        'target_binding_type' => app_project_source_output_form_post_value(
            'target_binding_type',
            (string) ($fallback['target_binding_type'] ?? ''),
        ),
        'spec_visibility' => app_project_source_output_form_post_value(
            'spec_visibility',
            (string) ($fallback['spec_visibility'] ?? 'internal-only'),
        ),
        'output_archive_format' => app_project_source_output_form_post_value(
            'output_archive_format',
            (string) ($fallback['output_archive_format'] ?? ''),
        ),
        'source_output_list_order' => app_project_source_output_form_post_value(
            'source_output_list_order',
            (string) ($fallback['source_output_list_order'] ?? ''),
        ),
        'notes' => app_project_source_output_form_post_value(
            'notes',
            (string) ($fallback['notes'] ?? ''),
        ),
        'source_of_truth' => app_project_source_output_form_post_value(
            'source_of_truth',
            'manual',
        ),
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
function app_render_project_source_output_edit_page(array $app, array $request): void
{
    $bootstrap = app_project_source_output_item_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $generatedRuntime = $bootstrap['generated_runtime'];
    $sourceOutputKey = $bootstrap['source_output_key'];
    $sourceOutput = $bootstrap['source_output'];
    $notesState = app_project_source_output_split_notes((string) ($sourceOutput['notes'] ?? ''));
    $legacyMetadata = app_project_source_output_legacy_metadata_from_request();
    if ($legacyMetadata === []) {
        $legacyMetadata = $notesState['legacy_metadata'];
    }
    $legacyMetadataRows = app_project_source_output_legacy_metadata_rows($legacyMetadata);

    $input = app_project_source_output_form_from_item(array_merge(
        $sourceOutput,
        ['notes' => $notesState['user_notes']],
    ));
    $errors = app_project_source_output_bridge_errors_from_request();
    $created = app_query_param('created') === '1';
    $updated = app_query_param('updated') === '1';

    $artifactResult = app_project_output_list($app, $projectKey, $sourceOutputKey);
    if (!$artifactResult['ok']) {
        $errors[] = $artifactResult['error'];
    }
    $artifacts = $artifactResult['items'];
    $latestArtifact = $artifacts[0] ?? null;

    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $action = trim(app_post_param('action', 'save-source-output'));
            $postedSourceOutputKey = app_normalize_source_output_key(app_post_param('source_output_key'));
            if ($postedSourceOutputKey !== $sourceOutputKey) {
                $errors[] = '更新対象の source output key が route と一致しません。';
            } elseif ($action === 'delete-source-output') {
                $deleteResult = app_delete_project_source_output($app, [
                    'project_key' => $projectKey,
                    'source_output_key' => $sourceOutputKey,
                ]);

                if ($deleteResult['ok']) {
                    app_send_redirect_response(
                        $request,
                        app_project_source_outputs_path($projectKey)
                        . '?deleted=' . rawurlencode($sourceOutputKey),
                    );
                    return;
                }

                $errors[] = $deleteResult['error'] !== ''
                    ? $deleteResult['error']
                    : 'source output definition の削除に失敗しました。';
            } elseif ($action === '' || $action === 'save-source-output') {
                $validation = app_validate_source_output_form(app_project_source_output_form_from_post($input));
                $input = $validation['input'];
                $errors = array_merge($errors, $validation['errors']);

                if ($errors === []) {
                    $persistInput = $input;
                    $persistInput['notes'] = app_project_source_output_notes_with_legacy_metadata(
                        $input['notes'],
                        $legacyMetadata,
                    );
                    $updateResult = app_update_project_source_output($app, array_merge(
                        ['project_key' => $projectKey],
                        $persistInput,
                    ));

                    if ($updateResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_source_output_edit_path($projectKey, $sourceOutputKey) . '?updated=1',
                        );
                        return;
                    }

                    $errors[] = $updateResult['error'];
                }
            } else {
                $errors[] = '未対応の操作です。';
            }
        }
    }

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Source Output Edit</title>
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
        input, select, textarea {
            width: 100%;
            box-sizing: border-box;
            margin-top: 0.35rem;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            font: inherit;
        }
        input[readonly] {
            background: #e2e8f0;
        }
        textarea {
            min-height: 8rem;
            resize: vertical;
        }
        .form-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }
        .button-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.25rem;
        }
        .button {
            display: inline-block;
            border: 0;
            border-radius: 8px;
            background: #0f172a;
            color: #ffffff;
            padding: 0.75rem 1rem;
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
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="<?php echo app_h(app_project_source_outputs_path($projectKey)); ?>">source-outputs</a> / <a href="<?php echo app_h(app_project_source_output_detail_path($projectKey, $sourceOutputKey)); ?>"><code><?php echo app_h($sourceOutputKey); ?></code></a> / edit</p>

    <h1><?php echo app_h($project['name']); ?> Source Output Edit</h1>
    <p><code>ProjectSourceOutput</code> definition を編集し、どの metadata をどの strategy で artifact 化するかを調整する画面です。canonical metadata を新 DB に保持し、artifact 生成 service はその定義を参照します。</p>

    <?php if ($created): ?>
        <section class="success-card">
            <h2>Created</h2>
            <p>definition を作成しました。必要ならこのまま追加調整して保存してください。</p>
        </section>
    <?php endif; ?>

    <?php if ($updated): ?>
        <section class="success-card">
            <h2>Saved</h2>
            <p>definition を更新しました。次に生成する artifact から変更が反映されます。</p>
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
            <h2>Definition</h2>
            <ul>
                <li>source output key: <code><?php echo app_h($sourceOutput['source_output_key']); ?></code></li>
                <li>current source of truth: <code><?php echo app_h($sourceOutput['source_of_truth']); ?></code></li>
                <li>updated at: <code><?php echo app_h($sourceOutput['updated_at']); ?></code></li>
                <li>name: <?php echo app_h($sourceOutput['name']); ?></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Generator Impact</h2>
            <ul>
                <li>runtime source: <code><?php echo app_h($input['runtime_source_relative_path']); ?></code></li>
                <li>strategy: <code><?php echo app_h($input['artifact_strategy']); ?></code></li>
                <li>binding type: <code><?php echo app_h(app_source_output_target_binding_type_caption($input['target_binding_type'])); ?></code></li>
                <li>spec visibility: <code><?php echo app_h(app_source_output_spec_visibility_caption($input['spec_visibility'])); ?></code></li>
                <li>effective scope: <code><?php echo app_h(app_source_output_target_binding_scope_caption(app_source_output_target_binding_scope($input))); ?></code></li>
                <li>archive: <code><?php echo app_h($input['output_archive_format']); ?></code></li>
                <li>storage root: <code><?php echo app_h(app_project_output_storage_root($app, $projectKey)); ?></code></li>
                <li>custom layer: <code><?php echo app_h(app_project_output_custom_layer_relative_path($projectKey, $sourceOutputKey)); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Current Runtime</h2>
            <ul>
                <li>mode: <code><?php echo app_h($generatedRuntime['dbclasses_mode']); ?></code></li>
                <li>source root: <code><?php echo app_h($generatedRuntime['dbclasses_root']); ?></code></li>
                <li>loader exists: <code><?php echo app_h($generatedRuntime['dbclasses_loader_exists'] ? 'yes' : 'no'); ?></code></li>
                <li>file count: <code><?php echo app_h((string) $generatedRuntime['total_file_count']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Artifacts</h2>
            <ul>
                <li>artifact count: <code><?php echo app_h((string) count($artifacts)); ?></code></li>
                <li>latest: <code><?php echo app_h($latestArtifact !== null ? $latestArtifact['artifact_key'] : 'none'); ?></code></li>
                <li>latest requested by: <code><?php echo app_h($latestArtifact !== null ? $latestArtifact['requested_by'] : 'n/a'); ?></code></li>
                <li>latest size: <code><?php echo app_h($latestArtifact !== null && $latestArtifact['archive_exists'] ? app_project_source_outputs_format_bytes($latestArtifact['archive_size']) : 'n/a'); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>保存時の扱い</h2>
            <p class="muted">この画面から保存した definition は <code>manual</code> 扱いに寄せます。seed 由来の <code>bootstrap-default</code> をそのまま保持したい場合は migration seed を更新します。</p>
            <p class="muted">artifact 履歴は <code>work/artifacts/source-outputs/{project_key}/{artifact_key}</code> に残します。current raw output は全 project で <code>work/source-outputs/{project_key}/{source_output_key}</code> を使い、repo に残す sample asset は対応する pack の <code>sample/&lt;category&gt;/&lt;pack&gt;/reference/&lt;source_output_key&gt;/</code> へ別管理します。</p>
            <p class="muted">custom layer の保存先は定義項目ではなく規約パスで固定し、<code>mtool/extensions/{project_key}/{source_output_key}</code> を使います。raw output はここへ混ぜません。</p>
            <p class="muted">html は <code>artifact_strategy=html-module-catalog</code> とし、<code>source_template_dir</code> に <code>catalog://html-module/{project_key}/{source_output_key}</code> を入れます。resolver は canonical module / copied snapshot / placeholder を順に解決します。</p>
            <p class="muted">custom layer では <code>bootstrap.php</code> で helper / collaborator を読み込み、必要な wrapper だけ <code>data-*.php</code> または <code>dbaccess-*.php</code> として追加します。</p>
            <p class="muted">current schema 未移植の legacy field がある場合は <code>notes</code> の structured block に保持し、通常のメモ本文とは分けて保存します。</p>
        </section>

        <?php if ($legacyMetadataRows !== []): ?>
            <section class="summary-card">
                <h2>Legacy Metadata</h2>
                <ul>
                    <li>stored fields: <code><?php echo app_h((string) count($legacyMetadataRows)); ?></code></li>
                    <li>storage: <code>notes</code> structured block</li>
                    <li>legacy PID: <code><?php echo app_h($legacyMetadata['ProjectSourceOutput.PID'] ?? 'n/a'); ?></code></li>
                    <?php foreach ($legacyMetadataRows as $row): ?>
                        <li><code><?php echo app_h($row['field']); ?></code>: <?php echo app_h($row['value']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>
    </div>

    <form method="post">
        <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
        <input type="hidden" name="action" value="save-source-output">
        <input type="hidden" name="source_output_key" value="<?php echo app_h($sourceOutputKey); ?>">
        <input type="hidden" name="source_of_truth" value="manual">
        <?php app_project_source_output_render_legacy_metadata_hidden_inputs($legacyMetadata); ?>

        <div class="form-grid">
            <label>
                source output key
                <input value="<?php echo app_h($sourceOutputKey); ?>" readonly>
            </label>

            <label>
                name
                <input name="name" value="<?php echo app_h($input['name']); ?>" placeholder="Mtool Runtime DBClasses">
            </label>

            <label>
                ProgramLanguage
                <select name="program_language">
                    <?php foreach (app_allowed_source_output_program_languages() as $programLanguage): ?>
                        <option value="<?php echo app_h($programLanguage); ?>"<?php echo $input['program_language'] === $programLanguage ? ' selected' : ''; ?>><?php echo app_h(app_source_output_program_language_caption($programLanguage)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                ClassType
                <select name="class_type">
                    <?php foreach (app_allowed_source_output_class_types() as $classType): ?>
                        <option value="<?php echo app_h($classType); ?>"<?php echo $input['class_type'] === $classType ? ' selected' : ''; ?>><?php echo app_h(app_source_output_class_type_caption($classType)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                ReleaseTargetType
                <select name="release_target_type">
                    <?php foreach (app_allowed_source_output_release_target_types() as $releaseTargetType): ?>
                        <option value="<?php echo app_h($releaseTargetType); ?>"<?php echo $input['release_target_type'] === $releaseTargetType ? ' selected' : ''; ?>><?php echo app_h(app_source_output_release_target_type_caption($releaseTargetType)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                source_template_dir
                <input name="source_template_dir" value="<?php echo app_h($input['source_template_dir']); ?>" placeholder="catalog://html-module/MTOOL/HTML-DB">
            </label>

            <label>
                source_output_dir
                <input name="source_output_dir" value="<?php echo app_h($input['source_output_dir']); ?>" placeholder="<?php echo app_h(app_project_output_default_relative_path('MTOOL', 'RUNTIME-DBCLASSES')); ?>">
            </label>

            <label>
                source_temp_output_dir
                <input name="source_temp_output_dir" value="<?php echo app_h($input['source_temp_output_dir']); ?>" placeholder="<?php echo app_h(app_project_output_default_temp_relative_path('MTOOL', 'RUNTIME-DBCLASSES')); ?>">
            </label>

            <label>
                proxy_base_url
                <input name="proxy_base_url" value="<?php echo app_h($input['proxy_base_url']); ?>" placeholder="https://example.invalid/api">
            </label>

            <label>
                autoload_filename_suffix
                <input name="autoload_filename_suffix" value="<?php echo app_h($input['autoload_filename_suffix']); ?>" placeholder="mtool">
            </label>

            <label>
                source_text_char_code
                <input name="source_text_char_code" value="<?php echo app_h($input['source_text_char_code']); ?>" placeholder="UTF-8">
            </label>

            <label>
                runtime_source_relative_path
                <input name="runtime_source_relative_path" value="<?php echo app_h($input['runtime_source_relative_path']); ?>" placeholder="<?php echo app_h(app_project_output_runtime_source_relative_path()); ?>">
            </label>

            <label>
                artifact_strategy
                <select name="artifact_strategy">
                    <?php foreach (app_allowed_source_output_artifact_strategies() as $artifactStrategy): ?>
                        <option value="<?php echo app_h($artifactStrategy); ?>"<?php echo $input['artifact_strategy'] === $artifactStrategy ? ' selected' : ''; ?>><?php echo app_h(app_source_output_artifact_strategy_caption($artifactStrategy)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                target_binding_type
                <select name="target_binding_type">
                    <?php foreach (app_allowed_source_output_target_binding_types() as $targetBindingType): ?>
                        <option value="<?php echo app_h($targetBindingType); ?>"<?php echo $input['target_binding_type'] === $targetBindingType ? ' selected' : ''; ?>><?php echo app_h(app_source_output_target_binding_type_caption($targetBindingType)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                spec_visibility
                <select name="spec_visibility">
                    <?php foreach (app_allowed_source_output_spec_visibilities() as $specVisibility): ?>
                        <option value="<?php echo app_h($specVisibility); ?>"<?php echo $input['spec_visibility'] === $specVisibility ? ' selected' : ''; ?>><?php echo app_h(app_source_output_spec_visibility_caption($specVisibility)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                output_archive_format
                <select name="output_archive_format">
                    <?php foreach (app_allowed_source_output_archive_formats() as $archiveFormat): ?>
                        <option value="<?php echo app_h($archiveFormat); ?>"<?php echo $input['output_archive_format'] === $archiveFormat ? ' selected' : ''; ?>><?php echo app_h($archiveFormat); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                source_output_list_order
                <input name="source_output_list_order" value="<?php echo app_h($input['source_output_list_order']); ?>" inputmode="numeric" pattern="[0-9]*">
            </label>
        </div>

        <label>
            notes
            <textarea name="notes"><?php echo app_h($input['notes']); ?></textarea>
        </label>

        <div class="button-row">
            <button class="button" type="submit">Save Definition</button>
            <button class="button button-danger" type="submit" name="action" value="delete-source-output" onclick="return confirm('Delete this source output definition?');">Delete Definition</button>
            <a class="button button-secondary" href="<?php echo app_h(app_project_source_output_detail_path($projectKey, $sourceOutputKey)); ?>">Back To Detail</a>
        </div>
    </form>
</main>
</body>
</html>
    <?php
}
