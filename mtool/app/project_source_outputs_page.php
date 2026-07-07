<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/no_code_operator_inspection.php';
require_once __DIR__ . '/no_code_operator_sync_inspection.php';
require_once __DIR__ . '/managed_operation_sync_outbox_repository_pdo.php';
require_once __DIR__ . '/project_output_service.php';
require_once __DIR__ . '/project_source_output_route_common.php';
require_once __DIR__ . '/request.php';

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
function app_render_project_source_outputs_page(array $app, array $request): void
{
    $bootstrap = app_project_source_output_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $principal = $bootstrap['principal'];
    $generatedRuntime = $bootstrap['generated_runtime'];

    $defaults = app_source_output_form_defaults();
    $createInput = [
        'source_output_key' => $defaults['source_output_key'],
        'name' => $defaults['name'],
        'program_language' => $defaults['program_language'],
        'class_type' => $defaults['class_type'],
        'release_target_type' => $defaults['release_target_type'],
        'target_binding_type' => $defaults['target_binding_type'],
        'spec_visibility' => $defaults['spec_visibility'],
    ];

    $errors = app_project_source_output_bridge_errors_from_request();
    $deletedSourceOutputKey = app_normalize_source_output_key(app_query_param('deleted'));

    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $action = trim(app_post_param('action'));

            if ($action === 'create-source-output') {
                $createInput = [
                    'source_output_key' => app_normalize_source_output_key(app_post_param('source_output_key')),
                    'name' => app_post_param('name'),
                    'program_language' => app_post_param('program_language', $defaults['program_language']),
                    'class_type' => app_post_param('class_type', $defaults['class_type']),
                    'release_target_type' => app_post_param('release_target_type', $defaults['release_target_type']),
                    'target_binding_type' => $defaults['target_binding_type'],
                    'spec_visibility' => $defaults['spec_visibility'],
                ];

                $catalogForOrderResult = app_fetch_project_source_output_catalog($app, $projectKey);
                if (!$catalogForOrderResult['ok']) {
                    $errors[] = $catalogForOrderResult['error'];
                } else {
                    $nextOrder = 10;
                    foreach ($catalogForOrderResult['items'] as $item) {
                        $itemOrder = (int) $item['source_output_list_order'];
                        if ($itemOrder >= $nextOrder) {
                            $nextOrder = $itemOrder + 10;
                        }
                    }

                    $validation = app_validate_source_output_form([
                        'source_output_key' => $createInput['source_output_key'],
                        'name' => $createInput['name'],
                        'program_language' => $createInput['program_language'],
                        'class_type' => $createInput['class_type'],
                        'release_target_type' => $createInput['release_target_type'],
                        'source_template_dir' => $defaults['source_template_dir'],
                        'source_output_dir' => app_project_output_default_relative_path(
                            $projectKey,
                            $createInput['source_output_key'],
                        ),
                        'source_temp_output_dir' => app_project_output_default_temp_relative_path(
                            $projectKey,
                            $createInput['source_output_key'],
                        ),
                        'proxy_base_url' => $defaults['proxy_base_url'],
                        'autoload_filename_suffix' => $defaults['autoload_filename_suffix'],
                        'source_text_char_code' => $defaults['source_text_char_code'],
                        'runtime_source_relative_path' => app_project_output_runtime_source_relative_path(),
                        'artifact_strategy' => 'generated-bootstrap-dbclasses',
                        'target_binding_type' => $createInput['target_binding_type'],
                        'spec_visibility' => $createInput['spec_visibility'],
                        'output_archive_format' => 'tar.gz',
                        'source_output_list_order' => (string) $nextOrder,
                        'notes' => $defaults['notes'],
                        'source_of_truth' => 'manual',
                    ]);

                    $errors = array_merge($errors, $validation['errors']);
                    if ($errors === []) {
                        $createResult = app_create_project_source_output($app, array_merge(
                            ['project_key' => $projectKey],
                            $validation['input'],
                        ));

                        if ($createResult['ok']) {
                            app_send_redirect_response(
                                $request,
                                app_project_source_output_edit_path($projectKey, $validation['input']['source_output_key']),
                            );
                            return;
                        }

                        $errors[] = $createResult['error'];
                    }
                }
            } elseif ($action === 'create-artifact') {
                $sourceOutputKey = app_normalize_source_output_key(app_post_param('source_output_key'));
                if ($sourceOutputKey === '' || !app_source_output_key_is_valid($sourceOutputKey)) {
                    $errors[] = 'source output key の形式が不正です。';
                } else {
                    $sourceOutputResult = app_fetch_project_source_output_item($app, $projectKey, $sourceOutputKey);
                    if (!$sourceOutputResult['ok']) {
                        $errors[] = $sourceOutputResult['error'];
                    } elseif ($sourceOutputResult['item'] === null) {
                        $errors[] = '指定された source output が見つかりません。';
                    } elseif (!app_source_output_artifact_strategy_supports_generation($sourceOutputResult['item']['artifact_strategy'])) {
                        $errors[] = '指定された source output の artifact strategy は artifact 生成をサポートしていません。';
                    } else {
                        $createResult = app_project_output_create_from_definition(
                            $app,
                            $projectKey,
                            $sourceOutputResult['item'],
                            'admin-ui:' . $principal['id'],
                        );

                        if ($createResult['ok'] && $createResult['artifact'] !== null) {
                            app_send_redirect_response(
                                $request,
                                app_project_source_output_detail_path($projectKey, $sourceOutputKey)
                                . '?created=' . rawurlencode($createResult['artifact']['artifact_key']),
                            );
                            return;
                        }

                        $errors[] = $createResult['error'];
                    }
                }
            } else {
                $errors[] = '未対応の操作です。';
            }
        }
    }

    $catalogResult = app_fetch_project_source_output_catalog($app, $projectKey);
    if (!$catalogResult['ok']) {
        $errors[] = $catalogResult['error'];
    }
    $sourceOutputs = $catalogResult['items'];

    $artifactResult = app_project_output_list($app, $projectKey);
    if (!$artifactResult['ok']) {
        $errors[] = $artifactResult['error'];
    }
    $artifacts = $artifactResult['items'];

    $artifactCountBySourceOutput = [];
    $latestArtifactBySourceOutput = [];
    foreach ($artifacts as $artifact) {
        $sourceOutputKey = $artifact['source_output_key'];
        $artifactCountBySourceOutput[$sourceOutputKey] = ($artifactCountBySourceOutput[$sourceOutputKey] ?? 0) + 1;
        if (!array_key_exists($sourceOutputKey, $latestArtifactBySourceOutput)) {
            $latestArtifactBySourceOutput[$sourceOutputKey] = $artifact;
        }
    }
    $noCodeInspection = app_no_code_operator_inspection_from_catalog(
        $sourceOutputs,
        $artifacts,
        $projectKey,
        app_project_output_workspace_root(),
    );

    $syncOutboxResult = app_pdo_fetch_managed_operation_sync_outbox_catalog($app, $projectKey);
    if (!$syncOutboxResult['ok']) {
        $errors[] = $syncOutboxResult['error'];
    }
    $syncInspection = app_no_code_operator_sync_inspection_from_outbox_catalog($syncOutboxResult['items']);

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Source Outputs</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 84rem;
            background: #ffffff;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 2rem;
        }
        code, pre {
            background: #edf2f7;
            border-radius: 6px;
        }
        code {
            padding: 0.1rem 0.3rem;
        }
        pre {
            padding: 0.9rem 1rem;
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
        .summary-card, .note-card, .error-card {
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
        .section-heading {
            margin-top: 2rem;
            margin-bottom: 0.25rem;
        }
        .muted {
            color: #475569;
        }
        .action-form, .create-form {
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
        }
        .button-secondary {
            background: #475569;
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
            min-height: 6rem;
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
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo app_h(rawurlencode($projectKey)); ?>"><?php echo app_h($projectKey); ?></a> / source-outputs</p>

    <h1><?php echo app_h($project['name']); ?> Source Outputs</h1>
    <p><code>ProjectSourceOutput</code> definition を管理し、Data Class / DB Access metadata をどの形で artifact に出力するかを扱う入口です。現段階の <code>RUNTIME-DBCLASSES</code> generator は <code><?php echo app_h(app_runtime_storage_runtime_source_repo_relative_path(app_runtime_storage_runtime_dbclasses_relative_path())); ?></code> を runtime reference として参照し、sync 済み canonical DB Access metadata があれば root <code>dbaccess-*.php</code> を wrapper 再生成します。definition 自体は DB に保持します。</p>

    <?php if ($deletedSourceOutputKey !== ''): ?>
        <section class="note-card">
            <h2>削除しました</h2>
            <p><code><?php echo app_h($deletedSourceOutputKey); ?></code> を current canonical definition から削除しました。</p>
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
                <li>source root: <code><?php echo app_h($generatedRuntime['dbclasses_root']); ?></code></li>
                <li>loader exists: <code><?php echo app_h($generatedRuntime['dbclasses_loader_exists'] ? 'yes' : 'no'); ?></code></li>
                <li>file count: <code><?php echo app_h((string) $generatedRuntime['total_file_count']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Canonical Definitions</h2>
            <ul>
                <li>definition count: <code><?php echo app_h((string) count($sourceOutputs)); ?></code></li>
                <li>artifact count: <code><?php echo app_h((string) count($artifacts)); ?></code></li>
                <li>artifact root: <code><?php echo app_h(app_project_output_storage_root($app, $projectKey)); ?></code></li>
                <li>default output root: <code><?php echo app_h(app_project_output_default_relative_path($projectKey)); ?></code></li>
                <li>custom layer root: <code><?php echo app_h(app_runtime_storage_custom_source_outputs_relative_path($projectKey)); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>No-Code Runtime Inspection</h2>
            <?php $noCodePreview = $noCodeInspection['preview']; ?>
            <?php $noCodeHealth = $noCodeInspection['health']; ?>
            <?php $noCodeLatestArtifact = $noCodeInspection['latest_artifact']; ?>
            <?php $noCodePublishReadiness = $noCodeInspection['publish_readiness']; ?>
            <?php $noCodeDeliveryOverview = $noCodeInspection['delivery_overview']; ?>
            <?php $appLocalPackageDelivery = $noCodeDeliveryOverview['app_local_package']; ?>
            <ul>
                <li>health: <code><?php echo app_h($noCodeHealth['state']); ?></code> <?php echo app_h($noCodeHealth['label']); ?></li>
                <li>publish readiness: <code><?php echo app_h($noCodePublishReadiness['state']); ?></code> <?php echo app_h($noCodePublishReadiness['label']); ?></li>
                <li>delivery overview: <code><?php echo app_h($noCodeDeliveryOverview['state']); ?></code> <?php echo app_h($noCodeDeliveryOverview['label']); ?></li>
                <li>definition: <code><?php echo app_h($noCodeInspection['available'] ? 'available' : 'missing'); ?></code></li>
                <li>source output: <code><?php echo app_h($noCodeInspection['source_output_key']); ?></code></li>
                <li>artifact count: <code><?php echo app_h((string) $noCodeInspection['artifact_count']); ?></code></li>
                <li>latest artifact:
                    <?php if ($noCodeLatestArtifact !== null): ?>
                        <a href="<?php echo app_h(app_project_source_output_artifact_detail_path($projectKey, (string) $noCodeLatestArtifact['artifact_key'])); ?>"><code><?php echo app_h((string) $noCodeLatestArtifact['artifact_key']); ?></code></a>
                    <?php else: ?>
                        <code>none</code>
                    <?php endif; ?>
                </li>
                <li>preview html: <code><?php echo app_h($noCodePreview['runtime_preview_html_exists'] ? 'available' : 'missing'); ?></code></li>
                <li>screens/actions: <code><?php echo app_h((string) $noCodePreview['screen_count']); ?></code> / <code><?php echo app_h((string) $noCodePreview['action_count']); ?></code></li>
                <li>sync hints: <code><?php echo app_h((string) $noCodePreview['sync_hint_screen_count']); ?></code></li>
                <li>usage intents: <code><?php echo app_h(($noCodePreview['usage_intents'] ?? []) !== [] ? implode(', ', $noCodePreview['usage_intents']) : 'none'); ?></code></li>
                <li>view variants: <code><?php echo app_h(($noCodePreview['view_variants'] ?? []) !== [] ? implode(', ', $noCodePreview['view_variants']) : 'none'); ?></code></li>
                <li>traceability targets: <code><?php echo app_h((string) ($noCodePreview['traceability_target_count'] ?? 0)); ?></code></li>
            </ul>
            <h3>Publish Readiness</h3>
            <ul>
                <li>state: <code><?php echo app_h($noCodePublishReadiness['state']); ?></code></li>
                <li>artifact key: <code><?php echo app_h($noCodePublishReadiness['artifact_key'] !== '' ? $noCodePublishReadiness['artifact_key'] : 'none'); ?></code></li>
                <li>archive: <code><?php echo app_h($noCodePublishReadiness['artifact_archive_exists'] ? 'available' : 'missing'); ?></code></li>
                <li>preview files: <code><?php echo app_h($noCodePublishReadiness['preview_files_ready'] ? 'ready' : 'blocked'); ?></code></li>
                <li>screens/actions: <code><?php echo app_h((string) $noCodePublishReadiness['screen_count']); ?></code> / <code><?php echo app_h((string) $noCodePublishReadiness['action_count']); ?></code></li>
            </ul>
            <?php if (($noCodePublishReadiness['blocking_reasons'] ?? []) !== []): ?>
                <p class="muted">publish blockers: <code><?php echo app_h(implode(' ', $noCodePublishReadiness['blocking_reasons'])); ?></code></p>
            <?php endif; ?>
            <h3>Delivery Overview</h3>
            <p class="muted">Web no-code preview and App-local package readiness are separate delivery tracks. Continue the Web preview tryout through <code>NO-CODE-RUNTIME</code> even when the App-local package lane is blocked or not configured for this sample.</p>
            <ul>
                <li>public runtime: <code><?php echo app_h($noCodeDeliveryOverview['public_runtime']['state']); ?></code> <?php echo app_h($noCodeDeliveryOverview['public_runtime']['label']); ?></li>
                <li>public artifact: <code><?php echo app_h($noCodeDeliveryOverview['public_runtime']['artifact_key'] !== '' ? $noCodeDeliveryOverview['public_runtime']['artifact_key'] : 'none'); ?></code></li>
                <li>app-local package: <code><?php echo app_h($appLocalPackageDelivery['state']); ?></code> <?php echo app_h($appLocalPackageDelivery['label']); ?></li>
                <li>package artifact: <code><?php echo app_h($appLocalPackageDelivery['artifact_key'] !== '' ? $appLocalPackageDelivery['artifact_key'] : 'none'); ?></code></li>
                <li>package manifest/summary: <code><?php echo app_h($appLocalPackageDelivery['manifest_available'] ? 'ready' : 'missing'); ?></code> / <code><?php echo app_h($appLocalPackageDelivery['summary_available'] ? 'ready' : 'missing'); ?></code></li>
            </ul>
            <?php if (($noCodeDeliveryOverview['blockers'] ?? []) !== []): ?>
                <p class="muted">delivery blockers: <code><?php echo app_h(implode(' ', $noCodeDeliveryOverview['blockers'])); ?></code></p>
            <?php endif; ?>
            <?php if ($appLocalPackageDelivery['source_output_key'] !== ''): ?>
                <p><a href="<?php echo app_h(app_project_source_output_detail_path($projectKey, $appLocalPackageDelivery['source_output_key'])); ?>">Inspect App-local package definition</a></p>
            <?php endif; ?>
            <p class="muted">current output: <code><?php echo app_h($noCodeInspection['source_output_dir']); ?></code></p>
            <p class="muted">preview files: <code><?php echo app_h($noCodeInspection['source_output_dir'] . '/screen-definition.json'); ?></code>, <code><?php echo app_h($noCodeInspection['source_output_dir'] . '/runtime-preview.json'); ?></code>, <code><?php echo app_h($noCodeInspection['source_output_dir'] . '/runtime-preview.html'); ?></code></p>
            <?php if ($noCodeHealth['reasons'] !== []): ?>
                <p class="muted">health detail: <code><?php echo app_h(implode(' ', $noCodeHealth['reasons'])); ?></code></p>
            <?php endif; ?>
            <?php if (($noCodeInspection['workflow_steps'] ?? []) !== []): ?>
                <h3>Operator Workflow Checklist</h3>
                <ol>
                    <?php foreach ($noCodeInspection['workflow_steps'] as $workflowStep): ?>
                        <li>
                            <code><?php echo app_h((string) ($workflowStep['state'] ?? '')); ?></code>
                            <?php echo app_h((string) ($workflowStep['label'] ?? '')); ?>
                            <span class="muted"><?php echo app_h((string) ($workflowStep['detail'] ?? '')); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
            <?php if ($noCodeInspection['available']): ?>
                <p><a href="<?php echo app_h(app_project_source_output_detail_path($projectKey, $noCodeInspection['source_output_key'])); ?>">Inspect NO-CODE-RUNTIME definition</a></p>
            <?php endif; ?>
            <?php if ($noCodeLatestArtifact !== null && ($noCodeLatestArtifact['archive_exists'] ?? false)): ?>
                <p><a href="<?php echo app_h(app_project_source_output_download_path($projectKey, (string) $noCodeLatestArtifact['artifact_key'])); ?>">Download latest NO-CODE-RUNTIME artifact</a></p>
            <?php endif; ?>
            <?php if ($noCodePreview['screen_keys'] !== []): ?>
                <p class="muted">screens: <code><?php echo app_h(implode(', ', array_slice($noCodePreview['screen_keys'], 0, 6))); ?></code></p>
            <?php endif; ?>
            <?php if ($noCodePreview['action_keys'] !== []): ?>
                <p class="muted">actions: <code><?php echo app_h(implode(', ', array_slice($noCodePreview['action_keys'], 0, 6))); ?></code></p>
            <?php endif; ?>
            <?php if (($noCodePreview['usage_intents'] ?? []) !== [] || ($noCodePreview['view_variants'] ?? []) !== []): ?>
                <p class="muted">interface/view layer: <code><?php echo app_h(implode(', ', array_merge($noCodePreview['usage_intents'] ?? [], $noCodePreview['view_variants'] ?? []))); ?></code></p>
            <?php endif; ?>
            <?php if (($noCodePreview['interface_profiles'] ?? []) !== []): ?>
                <h3>Interface Profiles</h3>
                <p class="muted">Edit contract-level usage intent in <a href="/projects/<?php echo rawurlencode($projectKey); ?>/shared-contracts">Shared Contracts</a>.</p>
                <ul>
                    <?php foreach (array_slice($noCodePreview['interface_profiles'], 0, 5) as $interfaceProfile): ?>
                        <li>
                            <code><?php echo app_h((string) ($interfaceProfile['contract_key'] ?? '')); ?></code>
                            intent <code><?php echo app_h((string) ($interfaceProfile['intent'] ?? '')); ?></code>
                            <span class="muted">from <?php echo app_h((string) ($interfaceProfile['source'] ?? '')); ?></span>
                            <?php $profileVariants = $interfaceProfile['view_variants'] ?? []; ?>
                            <?php if (is_array($profileVariants) && $profileVariants !== []): ?>
                                <span class="muted">views <code><?php echo app_h(implode(', ', $profileVariants)); ?></code></span>
                            <?php endif; ?>
                            <span class="muted">preferred <code><?php echo app_h((string) ($interfaceProfile['preferred_view_variant'] ?? '')); ?></code></span>
                            <span class="muted">trace targets <?php echo app_h((string) ($interfaceProfile['traceability_target_count'] ?? 0)); ?></span>
                            <?php $relatedSettings = $interfaceProfile['related_settings'] ?? []; ?>
                            <?php if (is_array($relatedSettings) && $relatedSettings !== []): ?>
                                <br>
                                <span class="muted">settings:</span>
                                <?php foreach (array_slice($relatedSettings, 0, 6) as $relatedSetting): ?>
                                    <a href="<?php echo app_h((string) ($relatedSetting['path'] ?? '')); ?>"><?php echo app_h((string) ($relatedSetting['label'] ?? '')); ?></a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if ($noCodePreview['errors'] !== []): ?>
                <p class="muted">preview metadata: <code><?php echo app_h(implode(' ', $noCodePreview['errors'])); ?></code></p>
            <?php endif; ?>
        </section>

        <section class="summary-card">
            <h2>Sync Outbox Inspection</h2>
            <?php $syncHealth = $syncInspection['health']; ?>
            <?php $latestFailedSync = $syncInspection['latest_failed_item']; ?>
            <ul>
                <li>health: <code><?php echo app_h($syncHealth['state']); ?></code> <?php echo app_h($syncHealth['label']); ?></li>
                <li>total: <code><?php echo app_h((string) $syncInspection['total_count']); ?></code></li>
                <li>failed: <code><?php echo app_h((string) $syncInspection['failed_count']); ?></code></li>
                <li>pending/running/done: <code><?php echo app_h((string) $syncInspection['pending_count']); ?></code> / <code><?php echo app_h((string) $syncInspection['running_count']); ?></code> / <code><?php echo app_h((string) $syncInspection['done_count']); ?></code></li>
                <li>latest failed:
                    <?php if ($latestFailedSync !== null): ?>
                        <code><?php echo app_h((string) $latestFailedSync['operation_key']); ?></code>
                        <span class="muted">attempts <?php echo app_h((string) $latestFailedSync['attempts']); ?></span>
                    <?php else: ?>
                        <code>none</code>
                    <?php endif; ?>
                </li>
            </ul>
            <?php if ($syncHealth['reasons'] !== []): ?>
                <p class="muted">health detail: <code><?php echo app_h(implode(' ', $syncHealth['reasons'])); ?></code></p>
            <?php endif; ?>
            <?php if ($syncInspection['failed_items'] !== []): ?>
                <table>
                    <thead>
                    <tr>
                        <th>operation</th>
                        <th>attempts</th>
                        <th>last error</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($syncInspection['failed_items'] as $failedSyncItem): ?>
                        <tr>
                            <td>
                                <a href="<?php echo app_h(app_project_sync_outbox_detail_path($projectKey, (string) $failedSyncItem['dedupe_key'])); ?>"><code><?php echo app_h((string) $failedSyncItem['operation_key']); ?></code></a><br>
                                <span class="muted"><?php echo app_h((string) $failedSyncItem['contract_key']); ?> / <?php echo app_h((string) $failedSyncItem['origin']); ?> -> <?php echo app_h((string) $failedSyncItem['target']); ?></span><br>
                                <span class="muted">updated: <?php echo app_h((string) $failedSyncItem['updated_at']); ?></span>
                            </td>
                            <td><code><?php echo app_h((string) $failedSyncItem['attempts']); ?></code></td>
                            <td><code><?php echo app_h((string) $failedSyncItem['last_error']); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="note-card">
            <h2>現段階の責務</h2>
            <p class="muted"><code>ProgramLanguage</code> や <code>ClassType</code> などの canonical field はここで保持します。現時点では <code>generated-bootstrap-dbclasses</code>、<code>html-module-catalog</code>、<code>legacy-directory-mirror</code>、<code>single-proxy-server</code> / <code>single-proxy-client</code> / <code>custom-proxy-server</code> / <code>custom-proxy-client</code> が artifact 生成対象です。</p>
            <p class="muted">artifact 履歴は <code>work/artifacts/source-outputs/{project_key}/{artifact_key}</code> に残します。current raw output は全 project で <code>work/source-outputs/{project_key}/{source_output_key}</code> を使い、repo に残す sample asset は対応する pack の <code>sample/&lt;category&gt;/&lt;pack&gt;/reference/&lt;source_output_key&gt;/</code> へ別管理します。</p>
            <p class="muted">proxy 向け definition は strategy ごとに別経路で staging tree を組み立てます。single-proxy 系は function target assignment、custom-proxy 系は custom proxy build plan を使います。custom layer は <code>mtool/extensions/{project_key}/{source_output_key}</code> 規約で扱い、artifact 生成時に workspace 内容または strategy 別 scaffold を同梱します。</p>
        </section>

        <section class="summary-card warning-card">
            <h2>上流 metadata を先に確認します</h2>
            <p class="muted">artifact 生成は DB import -> Data Class sync -> DB Access sync の上に載る前提です。外部 DB schema に差分がある場合は、source output 生成より先に upstream を更新します。</p>
            <ul>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/tables/import"><code>/projects/<?php echo app_h($projectKey); ?>/tables/import</code></a> で DB import 状態を確認する</li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/data-classes/sync"><code>/projects/<?php echo app_h($projectKey); ?>/data-classes/sync</code></a> で Data Class sync 状態を確認する</li>
                <li><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/sync"><code>/projects/<?php echo app_h($projectKey); ?>/db-access/sync</code></a> で DB Access metadata を揃える</li>
            </ul>
        </section>
    </div>

    <section>
        <h2 class="section-heading">Create Definition</h2>
        <p class="muted">詳細項目まで一度に入れる場合は <a href="<?php echo app_h(app_project_source_output_new_path($projectKey)); ?>">advanced create form</a> を使います。</p>
        <form class="create-form" method="post">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="action" value="create-source-output">

            <div class="form-grid">
                <label>
                    source output key
                    <input name="source_output_key" value="<?php echo app_h($createInput['source_output_key']); ?>" placeholder="RUNTIME-DBCLASSES">
                </label>

                <label>
                    name
                    <input name="name" value="<?php echo app_h($createInput['name']); ?>" placeholder="Mtool Runtime DBClasses">
                </label>

                <label>
                    ProgramLanguage
                    <select name="program_language">
                        <?php foreach (app_allowed_source_output_program_languages() as $programLanguage): ?>
                            <option value="<?php echo app_h($programLanguage); ?>"<?php echo $createInput['program_language'] === $programLanguage ? ' selected' : ''; ?>><?php echo app_h(app_source_output_program_language_caption($programLanguage)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    ClassType
                    <select name="class_type">
                        <?php foreach (app_allowed_source_output_class_types() as $classType): ?>
                            <option value="<?php echo app_h($classType); ?>"<?php echo $createInput['class_type'] === $classType ? ' selected' : ''; ?>><?php echo app_h(app_source_output_class_type_caption($classType)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    ReleaseTargetType
                    <select name="release_target_type">
                        <?php foreach (app_allowed_source_output_release_target_types() as $releaseTargetType): ?>
                            <option value="<?php echo app_h($releaseTargetType); ?>"<?php echo $createInput['release_target_type'] === $releaseTargetType ? ' selected' : ''; ?>><?php echo app_h(app_source_output_release_target_type_caption($releaseTargetType)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <p class="muted">作成直後は runtime source を指す最小設定で保存し、詳細値は edit 画面で詰めます。</p>
            <button class="button" type="submit">Create Source Output Definition</button>
        </form>
    </section>

    <section>
        <h2 class="section-heading">Definitions</h2>
        <p><a href="<?php echo app_h(app_project_source_output_change_order_path($projectKey)); ?>">Change Order of Source Output Definitions</a></p>
        <?php if ($sourceOutputs === []): ?>
            <p class="muted">まだ canonical source output definition はありません。</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>definition</th>
                    <th>build profile</th>
                    <th>runtime source</th>
                    <th>artifacts</th>
                    <th>action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($sourceOutputs as $sourceOutput): ?>
                    <?php $artifactCount = $artifactCountBySourceOutput[$sourceOutput['source_output_key']] ?? 0; ?>
                    <?php $latestArtifact = $latestArtifactBySourceOutput[$sourceOutput['source_output_key']] ?? null; ?>
                    <tr>
                        <td>
                            <strong><code><?php echo app_h($sourceOutput['source_output_key']); ?></code></strong><br>
                            <?php echo app_h($sourceOutput['name']); ?><br>
                            <span class="muted">updated: <?php echo app_h($sourceOutput['updated_at']); ?></span>
                        </td>
                        <td>
                            <code><?php echo app_h(app_source_output_program_language_caption($sourceOutput['program_language'])); ?></code><br>
                            <code><?php echo app_h(app_source_output_class_type_caption($sourceOutput['class_type'])); ?></code><br>
                            <code><?php echo app_h(app_source_output_release_target_type_caption($sourceOutput['release_target_type'])); ?></code><br>
                            <span class="muted">binding: <?php echo app_h(app_source_output_target_binding_scope_caption(app_source_output_target_binding_scope($sourceOutput))); ?></span>
                        </td>
                        <td>
                            <code><?php echo app_h($sourceOutput['runtime_source_relative_path']); ?></code><br>
                            <span class="muted"><?php echo app_h(app_source_output_artifact_strategy_caption($sourceOutput['artifact_strategy'])); ?></span><br>
                            <span class="muted">archive: <?php echo app_h($sourceOutput['output_archive_format']); ?></span><br>
                            <span class="muted">custom: <?php echo app_h(app_project_output_custom_layer_relative_path($projectKey, $sourceOutput['source_output_key'])); ?></span>
                        </td>
                        <td>
                            <code><?php echo app_h((string) $artifactCount); ?></code><br>
                            <?php if ($latestArtifact !== null): ?>
                                <span class="muted">latest: <?php echo app_h($latestArtifact['artifact_key']); ?></span>
                            <?php else: ?>
                                <span class="muted">none</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo app_h(app_project_source_output_detail_path($projectKey, $sourceOutput['source_output_key'])); ?>">detail</a><br>
                            <a href="<?php echo app_h(app_project_source_output_edit_path($projectKey, $sourceOutput['source_output_key'])); ?>">edit</a>
                            <?php if (app_source_output_artifact_strategy_supports_generation($sourceOutput['artifact_strategy'])): ?>
                                <form class="inline-form" method="post">
                                    <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
                                    <input type="hidden" name="action" value="create-artifact">
                                    <input type="hidden" name="source_output_key" value="<?php echo app_h($sourceOutput['source_output_key']); ?>">
                                    <button class="button button-secondary" type="submit">generate</button>
                                </form>
                            <?php else: ?>
                                <span class="muted">no artifact</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section>
        <h2 class="section-heading">Artifacts</h2>
        <?php if ($artifacts === []): ?>
            <p class="muted">まだ生成済み artifact はありません。</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>artifact</th>
                    <th>definition</th>
                    <th>source</th>
                    <th>archive</th>
                    <th>action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($artifacts as $artifact): ?>
                    <tr>
                        <td>
                            <strong><a href="<?php echo app_h(app_project_source_output_artifact_detail_path($projectKey, $artifact['artifact_key'])); ?>"><code><?php echo app_h($artifact['artifact_key']); ?></code></a></strong><br>
                            <span class="muted"><?php echo app_h($artifact['created_at']); ?></span><br>
                            <span class="muted">requested by: <?php echo app_h($artifact['requested_by']); ?></span>
                        </td>
                        <td>
                            <a href="<?php echo app_h(app_project_source_output_detail_path($projectKey, $artifact['source_output_key'])); ?>"><code><?php echo app_h($artifact['source_output_key']); ?></code></a><br>
                            <?php echo app_h($artifact['source_output_name']); ?><br>
                            <span class="muted"><?php echo app_h(app_source_output_program_language_caption($artifact['source_output_program_language'])); ?> / <?php echo app_h(app_source_output_class_type_caption($artifact['source_output_class_type'])); ?></span>
                        </td>
                        <td>
                            <code><?php echo app_h($artifact['runtime_source_relative_path']); ?></code><br>
                            <?php echo app_h(number_format($artifact['source_file_count'])); ?> files / <?php echo app_h(app_project_source_outputs_format_bytes($artifact['source_total_bytes'])); ?><br>
                            <span class="muted">custom: <?php echo app_h($artifact['custom_layer_source']); ?></span><br>
                            <span class="muted"><?php echo app_h(number_format($artifact['custom_layer_file_count'])); ?> files / <?php echo app_h(app_project_source_outputs_format_bytes($artifact['custom_layer_total_bytes'])); ?></span>
                        </td>
                        <td>
                            <code><?php echo app_h($artifact['archive_filename']); ?></code><br>
                            <?php echo app_h($artifact['archive_exists'] ? app_project_source_outputs_format_bytes($artifact['archive_size']) : 'missing'); ?>
                        </td>
                        <td>
                            <?php if ($artifact['archive_exists']): ?>
                                <a href="<?php echo app_h(app_project_source_output_download_path($projectKey, $artifact['artifact_key'])); ?>">download</a>
                            <?php else: ?>
                                <span class="muted">archive missing</span>
                            <?php endif; ?>
                        </td>
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
