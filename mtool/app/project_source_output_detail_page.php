<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/custom_proxy_build_plan_service.php';
require_once __DIR__ . '/db_access_endpoint_policy.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/lab_swagger_service.php';
require_once __DIR__ . '/project_output_service.php';
require_once __DIR__ . '/project_source_output_route_common.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/runtime_reference_status.php';

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
function app_render_project_source_output_detail_page(array $app, array $request): void
{
    $bootstrap = app_project_source_output_item_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $principal = $bootstrap['principal'];
    $generatedRuntime = $bootstrap['generated_runtime'];
    $sourceOutputKey = $bootstrap['source_output_key'];
    $sourceOutput = $bootstrap['source_output'];
    $runtimeReferenceStatus = null;
    if (
        $projectKey === app_runtime_reference_status_default_project_key()
        && $sourceOutputKey === app_runtime_reference_status_default_source_output_key()
    ) {
        $runtimeReferenceStatus = app_runtime_reference_status($app, $projectKey, $sourceOutputKey);
    }
    $notesState = app_project_source_output_split_notes((string) ($sourceOutput['notes'] ?? ''));
    $legacyMetadata = $notesState['legacy_metadata'];
    $legacyMetadataRows = app_project_source_output_legacy_metadata_rows($legacyMetadata);
    $userNotes = $notesState['user_notes'];

    $errors = [];
    $createdArtifactKey = '';
    $publishedArtifactKey = '';
    $queryCreatedArtifactKey = trim(app_query_param('created'));
    if ($queryCreatedArtifactKey !== '' && app_project_output_artifact_key_is_valid($queryCreatedArtifactKey)) {
        $createdArtifactKey = $queryCreatedArtifactKey;
    }
    $queryPublishedArtifactKey = trim(app_query_param('published'));
    if ($queryPublishedArtifactKey !== '' && app_project_output_artifact_key_is_valid($queryPublishedArtifactKey)) {
        $publishedArtifactKey = $queryPublishedArtifactKey;
    }

    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $action = trim(app_post_param('action'));
            if ($action === 'create-artifact' || $action === 'create-and-publish') {
                if (!app_source_output_artifact_strategy_supports_generation($sourceOutput['artifact_strategy'])) {
                    $errors[] = 'この source output の artifact strategy は artifact 生成をサポートしていません。';
                } else {
                    $createResult = app_project_output_create_from_definition(
                        $app,
                        $projectKey,
                        $sourceOutput,
                        'admin-ui:' . $principal['id'],
                    );

                    if ($createResult['ok'] && $createResult['artifact'] !== null) {
                        $artifact = $createResult['artifact'];
                        if ($action === 'create-and-publish') {
                            $publishResult = app_project_output_publish_artifact($app, $artifact, $sourceOutput);
                            if ($publishResult['ok']) {
                                app_send_redirect_response(
                                    $request,
                                    app_project_source_output_detail_path($projectKey, $sourceOutputKey)
                                    . '?created=' . rawurlencode($artifact['artifact_key'])
                                    . '&published=' . rawurlencode($artifact['artifact_key']),
                                );
                                return;
                            }

                            $createdArtifactKey = $artifact['artifact_key'];
                            $errors[] = $publishResult['error'];
                        } else {
                            app_send_redirect_response(
                                $request,
                                app_project_source_output_detail_path($projectKey, $sourceOutputKey)
                                . '?created=' . rawurlencode($artifact['artifact_key']),
                            );
                            return;
                        }
                    } else {
                        $errors[] = $createResult['error'];
                    }
                }
            } elseif ($action === 'publish-artifact') {
                $artifactKey = trim(app_post_param('artifact_key'));
                if ($artifactKey === '' || !app_project_output_artifact_key_is_valid($artifactKey)) {
                    $errors[] = 'publish 対象 artifact key の形式が不正です。';
                } else {
                    $artifactResult = app_project_output_find($app, $projectKey, $artifactKey);
                    if (!$artifactResult['ok']) {
                        $errors[] = $artifactResult['error'];
                    } elseif ($artifactResult['item'] === null) {
                        $errors[] = '指定した artifact が見つかりません。';
                    } elseif ($artifactResult['item']['source_output_key'] !== $sourceOutputKey) {
                        $errors[] = '指定した artifact は現在の source output に属していません。';
                    } else {
                        $publishResult = app_project_output_publish_artifact(
                            $app,
                            $artifactResult['item'],
                            $sourceOutput,
                        );
                        if ($publishResult['ok']) {
                            app_send_redirect_response(
                                $request,
                                app_project_source_output_detail_path($projectKey, $sourceOutputKey)
                                . '?published=' . rawurlencode($artifactKey),
                            );
                            return;
                        }

                        $errors[] = $publishResult['error'];
                    }
                }
            } else {
                $errors[] = '未対応の操作です。';
            }
        }
    }

    $artifactResult = app_project_output_list($app, $projectKey, $sourceOutputKey);
    if (!$artifactResult['ok']) {
        $errors[] = $artifactResult['error'];
    }
    $artifacts = $artifactResult['items'];
    $latestArtifact = $artifacts[0] ?? null;

    $customProxyBuildPlanResult = app_custom_proxy_build_plan_for_source_output($app, $projectKey, $sourceOutputKey);
    if (!$customProxyBuildPlanResult['ok'] || $customProxyBuildPlanResult['plan'] === null) {
        $errors[] = $customProxyBuildPlanResult['error'];
    }
    $customProxyBuildPlan = $customProxyBuildPlanResult['plan'] ?? [
        'custom_proxy_count' => 0,
        'step_count' => 0,
        'unresolved_step_count' => 0,
        'generated_catalog_summary' => [
            'total_entities' => 0,
            'paired_count' => 0,
            'data_only_count' => 0,
            'dbaccess_only_count' => 0,
        ],
        'items' => [],
    ];

    $simpleProxyTargetCatalogResult = app_fetch_source_output_db_access_function_target_catalog(
        $app,
        $projectKey,
        $sourceOutputKey,
    );
    if (!$simpleProxyTargetCatalogResult['ok']) {
        $errors[] = $simpleProxyTargetCatalogResult['error'];
    }
    $simpleProxyTargets = [];
    $simpleProxyUnresolvedAuthCount = 0;
    foreach ($simpleProxyTargetCatalogResult['items'] ?? [] as $item) {
        if (!is_array($item)) {
            continue;
        }

        $item['auth_policy'] = app_resolve_db_access_single_proxy_auth_policy(
            (string) ($item['single_proxy_auth_type'] ?? ''),
            (string) ($item['single_proxy_single_get_function_name'] ?? ''),
            (int) ($item['auth_policy_version'] ?? 1),
            (string) ($item['auth_policy_json'] ?? ''),
        );
        if (!$item['auth_policy']['is_valid']) {
            $simpleProxyUnresolvedAuthCount++;
        }

        $simpleProxyTargets[] = $item;
    }

    $customLayerWorkspace = app_project_output_scan_custom_layer_workspace($projectKey, $sourceOutputKey);
    if ($customLayerWorkspace['error'] !== '') {
        $errors[] = $customLayerWorkspace['error'];
    }
    $outputRootStatus = app_project_output_output_root_status($sourceOutput);
    if (!$outputRootStatus['ok']) {
        $errors[] = $outputRootStatus['error'];
    }
    $artifactGenerationEnabled = app_source_output_artifact_strategy_supports_generation($sourceOutput['artifact_strategy']);
    $isProxyArtifactStrategy = app_project_output_proxy_strategy_is_supported($sourceOutput['artifact_strategy']);
    $isOpenApiArtifactStrategy = app_project_output_openapi_strategy_is_supported($sourceOutput['artifact_strategy']);
    $isLegacyMirrorArtifactStrategy = app_project_output_legacy_source_strategy_is_supported($sourceOutput['artifact_strategy']);
    $legacyTemplateSource = null;
    if ($isLegacyMirrorArtifactStrategy) {
        $legacyTemplateSource = app_project_output_legacy_source_resolve_root($sourceOutput['source_template_dir']);
        if (!$legacyTemplateSource['ok']) {
            $errors[] = $legacyTemplateSource['error'];
        }
    }
    $targetBindingScope = app_source_output_target_binding_scope($sourceOutput);
    $targetBindingType = (string) ($sourceOutput['target_binding_type'] ?? '');
    $specVisibility = app_source_output_effective_spec_visibility($sourceOutput);
    $supportsSingleFunctionProxyTargets = app_source_output_supports_single_function_proxy_targets($sourceOutput);
    $customLayerEntrypoints = app_project_output_custom_layer_entrypoints($sourceOutput);
    $customLayerScaffoldFiles = app_project_output_custom_layer_scaffold_relative_paths($sourceOutput);

    $fieldRows = [
        [
            'field' => 'source_output_key',
            'value' => $sourceOutput['source_output_key'],
            'note' => 'route key。現段階では rename は行わず固定です。',
        ],
        [
            'field' => 'name',
            'value' => $sourceOutput['name'],
            'note' => 'UI / manifest に出す display name です。',
        ],
        [
            'field' => 'program_language',
            'value' => app_source_output_program_language_caption($sourceOutput['program_language']),
            'note' => '旧 ProgramLanguage。現 MVP では metadata として保持します。',
        ],
        [
            'field' => 'class_type',
            'value' => app_source_output_class_type_caption($sourceOutput['class_type']),
            'note' => '旧 ClassType。現 MVP では metadata として保持します。',
        ],
        [
            'field' => 'release_target_type',
            'value' => app_source_output_release_target_type_caption($sourceOutput['release_target_type']),
            'note' => '旧 ReleaseTargetType。manifest に引き継ぎます。',
        ],
        [
            'field' => 'runtime_source_relative_path',
            'value' => $sourceOutput['runtime_source_relative_path'],
            'note' => '現在の generator が参照または staging に使う runtime source key です。dbclasses は reference、生成ツリーは work 側へ解決します。',
        ],
        [
            'field' => 'artifact_strategy',
            'value' => app_source_output_artifact_strategy_caption($sourceOutput['artifact_strategy']),
            'note' => !$artifactGenerationEnabled
                ? 'canonical metadata のみ保持し、artifact は生成しません。'
                : (
                    $isLegacyMirrorArtifactStrategy
                    ? 'artifact 生成対象の strategy です。catalog ref または curated copied snapshot / placeholder source tree を resolver 経由で bundle 化します。'
                    : 'artifact 生成対象の strategy です。runtime reference または custom proxy build plan から bundle を組み立てます。'
                ),
        ],
        [
            'field' => 'target_binding_type',
            'value' => app_source_output_target_binding_type_caption($targetBindingType),
            'note' => $targetBindingType === ''
                ? 'explicit metadata が空のため、effective scope は artifact strategy / class type の heuristic fallback で判定します。'
                : 'source output をどの target assignment から参照してよいかを明示する canonical metadata です。',
        ],
        [
            'field' => 'spec_visibility',
            'value' => app_source_output_spec_visibility_caption($specVisibility),
            'note' => $sourceOutput['artifact_strategy'] === 'openapi-json'
                ? (
                    $specVisibility === 'disabled'
                    ? 'OpenAPI spec を authenticated viewer からも隠します。internal artifact filename は固定のままで、public raw route や public alias key route は持ちません。共有は admin artifact download に限ります。'
                    : 'OpenAPI spec は authenticated viewer でのみ扱います。internal artifact filename は固定のままで、public raw route や public alias key route は持ちません。共有は admin artifact download に限ります。'
                )
                : '現時点では OpenAPI artifact に対して意味を持つ metadata です。',
        ],
        [
            'field' => 'target_binding_scope',
            'value' => app_source_output_target_binding_scope_caption($targetBindingScope),
            'note' => match ($targetBindingScope) {
                'runtime' => 'runtime bundle 本体。proxy target assignment には使いません。',
                'custom-proxy' => 'multi-step custom proxy build plan 用の target です。single-function proxy target には使いません。',
                'single-function-proxy' => 'single-function proxy target assignment 用です。',
                'proxy-metadata-only' => 'proxy 系 metadata は持つが、binding scope はまだ確定していません。',
                default => 'target binding の scope をまだ確定していません。',
            },
        ],
        [
            'field' => 'output_archive_format',
            'value' => $sourceOutput['output_archive_format'],
            'note' => $artifactGenerationEnabled
                ? '現在は Docker PHP 制約に合わせて tar.gz を出力します。'
                : 'artifact を生成しない definition では none 固定です。',
        ],
        [
            'field' => 'source_template_dir',
            'value' => $sourceOutput['source_template_dir'] !== '' ? $sourceOutput['source_template_dir'] : '(blank)',
            'note' => '旧 template dir または catalog ref。後続 generator / resolver が curated source tree を引くため DB に保持します。',
        ],
        [
            'field' => 'source_output_dir',
            'value' => $sourceOutput['source_output_dir'] !== '' ? $sourceOutput['source_output_dir'] : '(blank)',
            'note' => 'artifact から materialize した current raw output の配置先です。全 project で work を既定にし、sample は curated baseline として別管理します。',
        ],
        [
            'field' => 'source_temp_output_dir',
            'value' => $sourceOutput['source_temp_output_dir'] !== '' ? $sourceOutput['source_temp_output_dir'] : '(blank)',
            'note' => 'artifact staging や一時出力のための disposable path metadata です。work 配下を前提にします。',
        ],
        [
            'field' => 'proxy_base_url',
            'value' => $sourceOutput['proxy_base_url'] !== '' ? $sourceOutput['proxy_base_url'] : '(blank)',
            'note' => 'proxy 系 source output を扱うために保持します。',
        ],
        [
            'field' => 'autoload_filename_suffix',
            'value' => $sourceOutput['autoload_filename_suffix'] !== '' ? $sourceOutput['autoload_filename_suffix'] : '(blank)',
            'note' => 'autoload 関連の suffix。現 MVP は manifest へはまだ未反映です。',
        ],
        [
            'field' => 'source_text_char_code',
            'value' => $sourceOutput['source_text_char_code'] !== '' ? $sourceOutput['source_text_char_code'] : '(blank)',
            'note' => '旧 SourceTextCharCode。文字コード変換実装のために保持します。',
        ],
        [
            'field' => 'source_output_list_order',
            'value' => $sourceOutput['source_output_list_order'],
            'note' => '一覧表示順です。',
        ],
        [
            'field' => 'source_of_truth',
            'value' => $sourceOutput['source_of_truth'],
            'note' => 'seed 由来か手編集かを区別するために保持します。',
        ],
        [
            'field' => 'updated_at',
            'value' => $sourceOutput['updated_at'],
            'note' => 'canonical definition の最終更新時刻です。',
        ],
    ];

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Source Output Detail</title>
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
        .section-heading {
            margin-top: 2rem;
            margin-bottom: 0.25rem;
        }
        .muted {
            color: #475569;
        }
        .action-box {
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
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="<?php echo app_h(app_project_source_outputs_path($projectKey)); ?>">source-outputs</a> / <code><?php echo app_h($sourceOutputKey); ?></code></p>

    <h1><?php echo app_h($project['name']); ?> Source Output Detail</h1>
    <p><code>ProjectSourceOutput</code> definition の詳細と、この definition から生成された artifact 履歴を確認する画面です。artifact を持つ strategy だけ、ここから生成も実行できます。</p>

    <?php if ($createdArtifactKey !== ''): ?>
        <section class="success-card">
            <h2>Artifact Created</h2>
            <p><code><?php echo app_h($createdArtifactKey); ?></code> を生成しました。下の履歴または download から確認できます。</p>
        </section>
    <?php endif; ?>

    <?php if ($publishedArtifactKey !== ''): ?>
        <section class="success-card">
            <h2>Output Updated</h2>
            <p><code><?php echo app_h($publishedArtifactKey); ?></code> を <code><?php echo app_h($outputRootStatus['relative_path'] !== '' ? $outputRootStatus['relative_path'] : '(blank)'); ?></code> へ current raw output として書き出しました。</p>
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
                <li>name: <?php echo app_h($sourceOutput['name']); ?></li>
                <li>updated: <code><?php echo app_h($sourceOutput['updated_at']); ?></code></li>
                <li>source of truth: <code><?php echo app_h($sourceOutput['source_of_truth']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Build Profile</h2>
            <ul>
                <li>ProgramLanguage: <code><?php echo app_h(app_source_output_program_language_caption($sourceOutput['program_language'])); ?></code></li>
                <li>ClassType: <code><?php echo app_h(app_source_output_class_type_caption($sourceOutput['class_type'])); ?></code></li>
                <li>ReleaseTargetType: <code><?php echo app_h(app_source_output_release_target_type_caption($sourceOutput['release_target_type'])); ?></code></li>
                <li>binding type: <code><?php echo app_h(app_source_output_target_binding_type_caption($targetBindingType)); ?></code></li>
                <li>spec visibility: <code><?php echo app_h(app_source_output_spec_visibility_caption($specVisibility)); ?></code></li>
                <li>effective scope: <code><?php echo app_h(app_source_output_target_binding_scope_caption($targetBindingScope)); ?></code></li>
                <li>list order: <code><?php echo app_h($sourceOutput['source_output_list_order']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Generator</h2>
            <ul>
                <li>runtime source: <code><?php echo app_h($sourceOutput['runtime_source_relative_path']); ?></code></li>
                <li>strategy: <code><?php echo app_h(app_source_output_artifact_strategy_caption($sourceOutput['artifact_strategy'])); ?></code></li>
                <li>archive: <code><?php echo app_h($sourceOutput['output_archive_format']); ?></code></li>
                <li>storage root: <code><?php echo app_h(app_project_output_storage_root($app, $projectKey)); ?></code></li>
                <li>artifact generation: <code><?php echo app_h($artifactGenerationEnabled ? 'enabled' : 'disabled'); ?></code></li>
            </ul>
        </section>

        <?php if ($legacyTemplateSource !== null): ?>
            <section class="summary-card">
                <h2>Template Source</h2>
                <ul>
                    <li>template ref: <code><?php echo app_h($sourceOutput['source_template_dir']); ?></code></li>
                    <li>resolved kind: <code><?php echo app_h(
                        $legacyTemplateSource['source_kind'] === 'direct-path'
                            ? 'Direct Path'
                            : app_project_output_html_module_source_kind_caption($legacyTemplateSource['source_kind'])
                    ); ?></code></li>
                    <li>resolved root: <code><?php echo app_h($legacyTemplateSource['source_root_relative_path']); ?></code></li>
                </ul>
            </section>
        <?php endif; ?>

        <section class="summary-card">
            <h2>Custom Layer</h2>
            <ul>
                <li>model: <code><?php echo app_h(app_project_output_customization_model()); ?></code></li>
                <li>workspace path: <code><?php echo app_h($customLayerWorkspace['relative_path']); ?></code></li>
                <li>entrypoints:
                    <?php foreach ($customLayerEntrypoints as $entrypointIndex => $entrypoint): ?>
                        <?php if ($entrypointIndex > 0): ?>, <?php endif; ?><code><?php echo app_h($entrypoint); ?></code>
                    <?php endforeach; ?>
                </li>
                <li>workspace exists: <code><?php echo app_h($customLayerWorkspace['exists'] ? 'yes' : 'no'); ?></code></li>
                <li>workspace files: <code><?php echo app_h((string) $customLayerWorkspace['file_count']); ?></code> / <code><?php echo app_h(app_project_source_outputs_format_bytes($customLayerWorkspace['total_bytes'])); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Artifacts</h2>
            <ul>
                <li>artifact count: <code><?php echo app_h((string) count($artifacts)); ?></code></li>
                <li>latest: <code><?php echo app_h($latestArtifact !== null ? $latestArtifact['artifact_key'] : 'none'); ?></code></li>
                <li>requested by: <code><?php echo app_h($latestArtifact !== null ? $latestArtifact['requested_by'] : 'n/a'); ?></code></li>
                <li>latest archive size: <code><?php echo app_h($latestArtifact !== null && $latestArtifact['archive_exists'] ? app_project_source_outputs_format_bytes($latestArtifact['archive_size']) : 'n/a'); ?></code></li>
                <li>latest custom layer: <code><?php echo app_h($latestArtifact !== null ? $latestArtifact['custom_layer_source'] : 'n/a'); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Output Root</h2>
            <ul>
                <li>output root: <code><?php echo app_h($outputRootStatus['relative_path'] !== '' ? $outputRootStatus['relative_path'] : '(blank)'); ?></code></li>
                <li>exists: <code><?php echo app_h($outputRootStatus['exists'] ? 'yes' : 'no'); ?></code></li>
                <li>files: <code><?php echo app_h((string) $outputRootStatus['file_count']); ?></code></li>
                <li>size: <code><?php echo app_h(app_project_source_outputs_format_bytes($outputRootStatus['total_bytes'])); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Custom Proxy Plan</h2>
            <ul>
                <li>targeted proxies: <code><?php echo app_h((string) $customProxyBuildPlan['custom_proxy_count']); ?></code></li>
                <li>resolved steps: <code><?php echo app_h((string) ($customProxyBuildPlan['step_count'] - $customProxyBuildPlan['unresolved_step_count'])); ?></code> / <code><?php echo app_h((string) $customProxyBuildPlan['step_count']); ?></code></li>
                <li>unresolved steps: <code><?php echo app_h((string) $customProxyBuildPlan['unresolved_step_count']); ?></code></li>
                <li>generated dbaccess entities: <code><?php echo app_h((string) $customProxyBuildPlan['generated_catalog_summary']['paired_count']); ?></code> paired / <code><?php echo app_h((string) $customProxyBuildPlan['generated_catalog_summary']['total_entities']); ?></code> total</li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Single Function Proxy Targets</h2>
            <ul>
                <li>targeted functions: <code><?php echo app_h((string) count($simpleProxyTargets)); ?></code></li>
                <li>resolved auth: <code><?php echo app_h((string) (count($simpleProxyTargets) - $simpleProxyUnresolvedAuthCount)); ?></code> / <code><?php echo app_h((string) count($simpleProxyTargets)); ?></code></li>
                <li>unresolved auth: <code><?php echo app_h((string) $simpleProxyUnresolvedAuthCount); ?></code></li>
            </ul>
            <?php if ($supportsSingleFunctionProxyTargets): ?>
                <p class="muted">legacy <code>dafuncSimpleProxySourceOutputTarget</code> 相当の canonical metadata 一覧です。</p>
            <?php else: ?>
                <p class="muted">この source output は <code><?php echo app_h(app_source_output_target_binding_scope_caption($targetBindingScope)); ?></code> scope として扱っており、single-function proxy target 用には使いません。</p>
            <?php endif; ?>
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

        <?php if (is_array($runtimeReferenceStatus)): ?>
            <section class="summary-card">
                <h2>Runtime Reference Status</h2>
                <?php if (!$runtimeReferenceStatus['ok']): ?>
                    <p class="muted"><?php echo app_h($runtimeReferenceStatus['error']); ?></p>
                <?php else: ?>
                    <ul>
                        <li>status: <code><?php echo app_h($runtimeReferenceStatus['status']); ?></code></li>
                        <li>promoted artifact: <code><?php echo app_h($runtimeReferenceStatus['reference']['artifact_key'] !== '' ? $runtimeReferenceStatus['reference']['artifact_key'] : 'n/a'); ?></code></li>
                        <li>reference generated at: <code><?php echo app_h($runtimeReferenceStatus['reference']['generated_at'] !== '' ? $runtimeReferenceStatus['reference']['generated_at'] : 'n/a'); ?></code></li>
                        <li>latest artifact: <code><?php echo app_h($runtimeReferenceStatus['latest_artifact'] !== null ? $runtimeReferenceStatus['latest_artifact']['artifact_key'] : 'none'); ?></code></li>
                        <li>latest created at: <code><?php echo app_h($runtimeReferenceStatus['latest_artifact'] !== null ? $runtimeReferenceStatus['latest_artifact']['created_at'] : 'n/a'); ?></code></li>
                        <li>latest promoted: <code><?php echo app_h($runtimeReferenceStatus['is_latest_promoted'] ? 'yes' : 'no'); ?></code></li>
                        <li>durable recovery ready: <code><?php echo app_h($runtimeReferenceStatus['durable_recovery_ready'] ? 'yes' : 'no'); ?></code></li>
                        <li>snapshot artifact: <code><?php echo app_h(is_array($runtimeReferenceStatus['reference_snapshot']) && $runtimeReferenceStatus['reference_snapshot']['artifact_key'] !== '' ? $runtimeReferenceStatus['reference_snapshot']['artifact_key'] : 'n/a'); ?></code></li>
                        <li>snapshot captured at: <code><?php echo app_h(is_array($runtimeReferenceStatus['reference_snapshot']) && $runtimeReferenceStatus['reference_snapshot']['captured_at'] !== '' ? $runtimeReferenceStatus['reference_snapshot']['captured_at'] : 'n/a'); ?></code></li>
                    </ul>
                    <p class="muted"><?php echo app_h($runtimeReferenceStatus['note']); ?></p>
                    <p class="muted"><?php echo app_h($runtimeReferenceStatus['durable_recovery_note']); ?></p>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <section class="note-card">
            <h2>操作メモ</h2>
            <p class="muted">definition を更新しても、既に生成済み artifact は変わりません。変更を反映したい場合は save 後に新しい artifact を生成します。</p>
            <p class="muted">artifact 生成時は <code><?php echo app_h($customLayerWorkspace['relative_path']); ?></code> が存在すればその内容を同梱し、未作成なら strategy 別 scaffold
                <?php foreach ($customLayerScaffoldFiles as $scaffoldIndex => $scaffoldFile): ?>
                    <?php if ($scaffoldIndex > 0): ?>, <?php endif; ?><code><?php echo app_h($scaffoldFile); ?></code>
                <?php endforeach; ?>
                を同梱します。</p>
            <?php if (!$isProxyArtifactStrategy): ?>
                <p class="muted">runtime artifact では <code>_support/runtime-generation-manifest.json</code> に generation mode、件数、warning を記録します。</p>
            <?php endif; ?>
            <p class="muted">single-function proxy target metadata は別 table で保持します。<code>single-proxy-*</code> strategy はこの row を direct per-function artifact へ変換し、<code>custom-proxy-*</code> strategy は custom proxy build plan を入力にします。current <code>DBIMPORT-PROXY-*</code> は引き続き custom proxy 専用です。</p>
            <p class="muted">current schema 未移植の legacy field は <code>notes</code> の structured block に保持し、通常の notes 本文とは分離して扱います。</p>
            <p><a href="<?php echo app_h(app_project_source_output_edit_path($projectKey, $sourceOutputKey)); ?>">edit definition</a></p>
        </section>

        <?php if ($legacyMetadataRows !== []): ?>
            <section class="summary-card">
                <h2>Legacy Metadata</h2>
                <ul>
                    <li>stored fields: <code><?php echo app_h((string) count($legacyMetadataRows)); ?></code></li>
                    <li>storage: <code>notes</code> structured block</li>
                    <li>legacy PID: <code><?php echo app_h($legacyMetadata['ProjectSourceOutput.PID'] ?? 'n/a'); ?></code></li>
                </ul>
            </section>
        <?php endif; ?>
    </div>

    <section class="action-box">
        <h2>Generate Artifact</h2>
        <?php if ($artifactGenerationEnabled): ?>
            <?php if ($isOpenApiArtifactStrategy): ?>
                <p class="muted">現在の definition と single-function proxy target metadata、canonical data class metadata を使って minimal <code>openapi.json</code> / <code>build-plan.json</code> を生成します。生成後は <code>/runs/swagger/{project_key}</code> から viewer で確認できます。</p>
            <?php elseif ($isProxyArtifactStrategy): ?>
                <?php if ($targetBindingScope === 'single-function-proxy'): ?>
                    <p class="muted">現在の definition と single-function proxy target metadata、runtime dbclasses reference を使って direct per-function proxy source tree を staging し、新しい source output artifact を生成します。</p>
                <?php else: ?>
                    <p class="muted">現在の definition と custom proxy build plan、runtime dbclasses reference を使って proxy source tree を staging し、新しい source output artifact を生成します。</p>
                <?php endif; ?>
            <?php elseif ($isLegacyMirrorArtifactStrategy): ?>
                <p class="muted">現在の definition で指定した curated copied snapshot / placeholder source tree を staging し、新しい source output artifact を生成します。generator は <code>original-codes/</code> を直接読みません。</p>
            <?php else: ?>
                <p class="muted">現在の definition と runtime reference から作る prepared runtime source tree を使って新しい source output artifact を生成します。sync 済み canonical DB Access metadata があれば root <code>dbaccess-*.php</code> を wrapper/base contract へ再生成し、legacy delegate が残る場合だけ <code>_support/legacy-dbaccess/</code> を使います。</p>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
                <input type="hidden" name="action" value="create-artifact">
                <button class="button" type="submit">Generate Source Output Artifact</button>
            </form>
            <form method="post" style="margin-top: 0.75rem;">
                <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
                <input type="hidden" name="action" value="create-and-publish">
                <button class="button button-secondary" type="submit">Generate And Write Output</button>
                <a class="button button-secondary" href="<?php echo app_h(app_project_source_output_edit_path($projectKey, $sourceOutputKey)); ?>">Edit Definition</a>
                <?php if ($isOpenApiArtifactStrategy): ?>
                    <a class="button button-secondary" href="<?php echo app_h(app_lab_swagger_path($projectKey, ['source_output_key' => $sourceOutputKey])); ?>">Open Swagger Viewer</a>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <p class="muted">この definition は canonical metadata の保存先として使い、artifact は生成しません。</p>
            <p class="muted">代わりに下の build plan / target list で、この definition をどの proxy metadata が参照しているかを確認できます。CLI では <code>php mtool/scripts/show_source_output_build_plan.php --project-key=<?php echo app_h($projectKey); ?> --source-output-key=<?php echo app_h($sourceOutputKey); ?></code> を使います。</p>
            <p><a class="button button-secondary" href="<?php echo app_h(app_project_source_output_edit_path($projectKey, $sourceOutputKey)); ?>">Edit Definition</a></p>
        <?php endif; ?>
    </section>

    <?php if ($latestArtifact !== null): ?>
        <section class="action-box">
            <h2>Write Output Root</h2>
            <p class="muted">artifact 履歴とは別に、選択した artifact を canonical definition の current raw output root <code><?php echo app_h($outputRootStatus['relative_path'] !== '' ? $outputRootStatus['relative_path'] : '(blank)'); ?></code> へ materialize します。custom layer はここへ混ぜません。</p>
            <form method="post">
                <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
                <input type="hidden" name="action" value="publish-artifact">
                <input type="hidden" name="artifact_key" value="<?php echo app_h($latestArtifact['artifact_key']); ?>">
                <button class="button" type="submit">Write Latest Artifact</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($userNotes !== ''): ?>
        <section>
            <h2 class="section-heading">Notes</h2>
            <pre><?php echo app_h($userNotes); ?></pre>
        </section>
    <?php endif; ?>

    <?php if ($legacyMetadataRows !== []): ?>
        <section>
            <h2 class="section-heading">Legacy Metadata</h2>
            <table>
                <thead>
                <tr>
                    <th>field</th>
                    <th>value</th>
                    <th>note</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($legacyMetadataRows as $row): ?>
                    <tr>
                        <td><code><?php echo app_h($row['field']); ?></code></td>
                        <td><?php echo app_h($row['value']); ?></td>
                        <td class="muted"><?php echo app_h($row['note']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>

    <section>
        <h2 class="section-heading">Canonical Fields</h2>
        <table>
            <thead>
            <tr>
                <th>field</th>
                <th>value</th>
                <th>note</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($fieldRows as $row): ?>
                <tr>
                    <td><code><?php echo app_h($row['field']); ?></code></td>
                    <td><?php echo app_h($row['value']); ?></td>
                    <td class="muted"><?php echo app_h($row['note']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section>
        <h2 class="section-heading">Artifact History</h2>
        <?php if ($artifacts === []): ?>
            <p class="muted">この definition から生成された artifact はまだありません。</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>artifact</th>
                    <th>runtime source</th>
                    <th>archive</th>
                    <th>action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($artifacts as $artifact): ?>
                    <tr>
                        <td>
                            <strong><code><?php echo app_h($artifact['artifact_key']); ?></code></strong><br>
                            <span class="muted"><?php echo app_h($artifact['created_at']); ?></span><br>
                            <span class="muted">requested by: <?php echo app_h($artifact['requested_by']); ?></span>
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
                                <form method="post" style="margin-top: 0.5rem;">
                                    <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
                                    <input type="hidden" name="action" value="publish-artifact">
                                    <input type="hidden" name="artifact_key" value="<?php echo app_h($artifact['artifact_key']); ?>">
                                    <button class="button button-secondary" type="submit">publish</button>
                                </form>
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

    <section>
        <h2 class="section-heading">Single Function Proxy Targets</h2>
        <?php if (!$supportsSingleFunctionProxyTargets): ?>
            <p class="muted">この source output は single-function proxy target 用の binding scope をまだ持たないため、ここには canonical row を載せません。</p>
        <?php elseif ($simpleProxyTargets === []): ?>
            <p class="muted">この source output を target にする single-function proxy row はまだありません。</p>
        <?php else: ?>
            <p class="muted">legacy <code>dafuncSimpleProxySourceOutputTarget</code> 相当の canonical row 一覧です。<code>single-proxy-*</code> strategy ではこの row をそのまま build input に使い、direct per-function proxy artifact を組み立てます。</p>
            <table>
                <thead>
                <tr>
                    <th>dbaccess</th>
                    <th>function</th>
                    <th>auth policy</th>
                    <th>source of truth</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($simpleProxyTargets as $targetItem): ?>
                    <tr>
                        <td>
                            <code><?php echo app_h($targetItem['source_name']); ?></code>
                        </td>
                        <td>
                            <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($targetItem['source_name']); ?>/functions/<?php echo rawurlencode($targetItem['function_name']); ?>">
                                <code><?php echo app_h($targetItem['function_name']); ?></code>
                            </a><br>
                            <span class="muted">order: <?php echo app_h($targetItem['function_list_order']); ?> / action: <?php echo app_h($targetItem['action_type'] !== '' ? $targetItem['action_type'] : '(blank)'); ?></span>
                        </td>
                        <td>
                            <code><?php echo app_h($targetItem['auth_policy']['resolved_auth_type']); ?></code><br>
                            <span class="muted"><?php echo app_h($targetItem['auth_policy']['summary']); ?></span>
                        </td>
                        <td>
                            <code><?php echo app_h($targetItem['source_of_truth']); ?></code><br>
                            <span class="muted">function updated: <?php echo app_h($targetItem['function_updated_at']); ?></span><br>
                            <span class="muted">target updated: <?php echo app_h($targetItem['target_updated_at']); ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section>
        <h2 class="section-heading">Custom Proxy Build Plan</h2>
        <?php if ($customProxyBuildPlan['items'] === []): ?>
            <p class="muted">この source output を target にする custom proxy はまだありません。</p>
        <?php else: ?>
            <p class="muted">canonical custom proxy metadata と generated dbaccess catalog を照合した build plan です。custom-proxy 系 strategy の artifact 生成では、この計画をもとに staging tree を組み立てます。</p>
            <table>
                <thead>
                <tr>
                    <th>custom proxy</th>
                    <th>auth / transaction</th>
                    <th>steps</th>
                    <th>targets</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($customProxyBuildPlan['items'] as $planItem): ?>
                    <tr>
                        <td>
                            <strong><code><?php echo app_h($planItem['custom_proxy_key']); ?></code></strong><br>
                            <span><?php echo app_h($planItem['display_name']); ?></span><br>
                            <span class="muted">updated: <?php echo app_h($planItem['updated_at']); ?></span>
                        </td>
                        <td>
                            <code><?php echo app_h($planItem['auth_policy']['resolved_auth_type']); ?></code><br>
                            <span class="muted"><?php echo app_h($planItem['auth_policy']['summary']); ?></span><br>
                            <span class="muted">transaction: <?php echo app_h($planItem['in_transaction'] ? 'yes' : 'no'); ?></span><br>
                            <span class="muted">continue on insert failure: <?php echo app_h($planItem['continue_even_if_failed_to_insert'] ? 'yes' : 'no'); ?></span>
                        </td>
                        <td>
                            <strong><?php echo app_h((string) $planItem['step_count']); ?> step</strong><br>
                            <span class="muted">unresolved: <?php echo app_h((string) $planItem['unresolved_step_count']); ?></span>
                        </td>
                        <td>
                            <?php foreach ($planItem['target_source_output_keys'] as $targetKey): ?>
                                <code><?php echo app_h($targetKey); ?></code><br>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <table>
                                <thead>
                                <tr>
                                    <th>order</th>
                                    <th>dbaccess</th>
                                    <th>function</th>
                                    <th>list</th>
                                    <th>resolution</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($planItem['steps'] as $step): ?>
                                    <tr>
                                        <td><code><?php echo app_h($step['step_order']); ?></code></td>
                                        <td><code><?php echo app_h($step['db_access_source_name']); ?></code></td>
                                        <td><code><?php echo app_h($step['db_access_function_name']); ?></code></td>
                                        <td><code><?php echo app_h($step['is_list'] ? 'yes' : 'no'); ?></code></td>
                                        <td>
                                            <?php if ($step['resolved']): ?>
                                                <span>resolved</span><br>
                                                <span class="muted"><code><?php echo app_h($step['signature']); ?></code></span>
                                                <?php if ($step['line'] > 0): ?>
                                                    <br><span class="muted">line: <?php echo app_h((string) $step['line']); ?></span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span>unresolved</span><br>
                                                <span class="muted"><?php echo app_h($step['resolution_error']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
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
