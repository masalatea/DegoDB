<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/build_job_service.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/lab_swagger_service.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/project_source_output_route_common.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/source_output_repository.php';

function app_render_lab_swagger_page(array $app, array $request): void
{
    if ($app['site'] !== 'lab' && $app['site'] !== 'admin') {
        app_render_forbidden_page($app, $request, 'この route は 実験用サイト または 設定変更用サイト でのみ利用します。');
        return;
    }

    $principal = app_auth_principal();
    if ($principal === null) {
        app_send_redirect_response($request, app_auth_login_path());
        return;
    }

    if (!app_auth_has_any_role(['lab', 'admin'], $principal)) {
        app_render_forbidden_page($app, $request, 'Swagger viewer には lab または admin role が必要です。');
        return;
    }

    if (!app_request_method_is($request, 'GET')) {
        app_render_method_not_allowed_page($app, $request, ['GET']);
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return;
    }

    $projectResult = app_fetch_project_by_key($app, $projectKey);
    if (!$projectResult['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Swagger Viewer</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>Swagger viewer の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($projectResult['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return;
    }

    if ($projectResult['item'] === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $project = $projectResult['item'];
    $errors = [];
    $notices = [];

    $catalogResult = app_fetch_project_source_output_catalog($app, $projectKey);
    $allOpenApiSourceOutputs = array_values(array_filter(
        $catalogResult['items'] ?? [],
        static fn (array $sourceOutput): bool => trim((string) ($sourceOutput['artifact_strategy'] ?? '')) === 'openapi-json',
    ));
    $openApiSourceOutputs = app_lab_swagger_supported_source_outputs($catalogResult['items'] ?? []);
    if (!$catalogResult['ok']) {
        $errors[] = $catalogResult['error'];
    }

    $sourceOutputsByKey = [];
    $allOpenApiSourceOutputsByKey = [];
    foreach ($allOpenApiSourceOutputs as $sourceOutput) {
        $sourceOutputKey = app_normalize_source_output_key((string) ($sourceOutput['source_output_key'] ?? ''));
        if ($sourceOutputKey === '') {
            continue;
        }

        $allOpenApiSourceOutputsByKey[$sourceOutputKey] = $sourceOutput;
    }
    foreach ($openApiSourceOutputs as $sourceOutput) {
        $sourceOutputKey = app_normalize_source_output_key((string) ($sourceOutput['source_output_key'] ?? ''));
        if ($sourceOutputKey === '') {
            continue;
        }

        $sourceOutputsByKey[$sourceOutputKey] = $sourceOutput;
    }

    $selectedSourceOutputKey = app_normalize_source_output_key(app_query_param('source_output_key'));
    if ($selectedSourceOutputKey === '' && $openApiSourceOutputs !== []) {
        $selectedSourceOutputKey = app_normalize_source_output_key(
            (string) ($openApiSourceOutputs[0]['source_output_key'] ?? ''),
        );
    }

    $selectedSourceOutput = $sourceOutputsByKey[$selectedSourceOutputKey] ?? null;
    if ($selectedSourceOutputKey !== '' && $selectedSourceOutput === null && $openApiSourceOutputs !== []) {
        $disabledSourceOutput = $allOpenApiSourceOutputsByKey[$selectedSourceOutputKey] ?? null;
        if (
            is_array($disabledSourceOutput)
            && app_source_output_effective_spec_visibility($disabledSourceOutput) === 'disabled'
        ) {
            $notices[] = '指定した OpenAPI source output は viewer で disabled です。先頭の enabled definition を表示します。';
        } else {
            $notices[] = '指定した OpenAPI source output は見つかりませんでした。先頭の definition を表示します。';
        }
        $selectedSourceOutput = $openApiSourceOutputs[0];
        $selectedSourceOutputKey = app_normalize_source_output_key(
            (string) ($selectedSourceOutput['source_output_key'] ?? ''),
        );
    }

    $runtimeDatabaseSourceOptions = app_lab_swagger_runtime_database_source_options($app);
    $runtimeDatabaseSourceSelection = app_lab_swagger_resolve_runtime_database_source_selection(
        $app,
        trim(app_query_param('db_source_key')),
        $runtimeDatabaseSourceOptions,
    );
    $selectedRuntimeDatabaseSourceKey = $runtimeDatabaseSourceSelection['selected_key'];
    $selectedRuntimeDatabaseSource = is_array($runtimeDatabaseSourceSelection['selected_source'] ?? null)
        ? $runtimeDatabaseSourceSelection['selected_source']
        : null;
    if ($runtimeDatabaseSourceSelection['notice'] !== '') {
        $notices[] = $runtimeDatabaseSourceSelection['notice'];
    }

    $runtimeDatabaseSourceOptionKeys = [];
    foreach ($runtimeDatabaseSourceOptions as $runtimeDatabaseSourceOption) {
        $runtimeDatabaseSourceOptionKey = trim((string) ($runtimeDatabaseSourceOption['key'] ?? ''));
        if ($runtimeDatabaseSourceOptionKey === '') {
            continue;
        }

        $runtimeDatabaseSourceOptionKeys[$runtimeDatabaseSourceOptionKey] = true;
    }

    if (
        $selectedRuntimeDatabaseSource !== null
        && $selectedRuntimeDatabaseSourceKey !== ''
        && !isset($runtimeDatabaseSourceOptionKeys[$selectedRuntimeDatabaseSourceKey])
    ) {
        $runtimeDatabaseSourceOptions[] = $selectedRuntimeDatabaseSource;
    }

    $artifactList = [];
    $selectedArtifactKey = trim(app_query_param('artifact_key'));
    $specResult = [
        'ok' => false,
        'spec' => null,
        'spec_json' => '',
        'spec_path' => '',
        'spec_source' => '',
        'artifact' => null,
        'error' => '',
    ];
    $operations = [];
    $baseUrlInput = trim(app_query_param('base_url'));

    if ($selectedSourceOutput !== null) {
        $artifactListResult = app_project_output_list($app, $projectKey, $selectedSourceOutputKey);
        if (!$artifactListResult['ok']) {
            $errors[] = $artifactListResult['error'];
        } else {
            $artifactList = $artifactListResult['items'];
        }

        $specResult = app_lab_swagger_resolve_spec(
            $app,
            $projectKey,
            $selectedSourceOutput,
            $selectedArtifactKey,
        );
        if (!$specResult['ok']) {
            $notices[] = $specResult['error'];
        } elseif (is_array($specResult['spec'])) {
            $operations = app_lab_swagger_operation_catalog($specResult['spec']);
            if ($baseUrlInput === '') {
                $baseUrlInput = app_lab_swagger_default_base_url($specResult['spec']);
            }

            if ($operations === []) {
                $notices[] = 'openapi.json は読めましたが、操作が 0 件でした。target metadata を確認してください。';
            }
        }
    } elseif ($openApiSourceOutputs === []) {
        if ($allOpenApiSourceOutputs !== []) {
            $notices[] = 'OpenAPI source output definition はありますが、すべて `spec_visibility=disabled` です。admin 側で `internal-only` に戻すと viewer に表示されます。';
        } else {
            $notices[] = 'OpenAPI source output definition がまだありません。admin 側の source output で `artifact_strategy=openapi-json` を追加してください。';
        }
    }

    $authHelperSummary = app_lab_swagger_auth_helper_summary($operations);
    $statusCode = $errors === [] ? 200 : 422;

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Swagger Viewer</title>
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
            white-space: pre-wrap;
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
        textarea {
            min-height: 12rem;
            resize: vertical;
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
        .summary-card, .note-card, .error-card, .operation-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
            background: #ffffff;
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
        .toolbar {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            margin: 1.5rem 0;
            padding: 1.25rem;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            background: #f8fafc;
        }
        .button-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
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
        .muted {
            color: #475569;
        }
        .operations {
            display: grid;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .operation-heading {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }
        .method-badge {
            display: inline-block;
            min-width: 4.5rem;
            text-align: center;
            border-radius: 999px;
            padding: 0.2rem 0.75rem;
            font-weight: 700;
            color: #ffffff;
        }
        .method-post {
            background: #0f766e;
        }
        .method-get {
            background: #1d4ed8;
        }
        .method-put {
            background: #b45309;
        }
        .method-patch {
            background: #7c3aed;
        }
        .method-delete {
            background: #b91c1c;
        }
        .meta-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem 1rem;
            margin: 0.75rem 0;
        }
        .meta-list code {
            display: inline-block;
        }
        .auth-helper-panel {
            margin-top: 1rem;
        }
        .auth-inline-note {
            margin: 0.75rem 0;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            background: #fff7ed;
            border: 1px solid #fdba74;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / swagger</p>

    <h1><?php echo app_h($project['name']); ?> Swagger Viewer</h1>
    <p>generated <code>openapi.json</code> を読み、single-function proxy contract を viewer 形式で確認する route です。Try it out は browser から直接実行するため、到達性や CORS 制約は target endpoint 側に従います。</p>

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

    <?php if ($notices !== []): ?>
        <section class="note-card">
            <h2>Notice</h2>
            <ul>
                <?php foreach ($notices as $notice): ?>
                    <li><?php echo app_h($notice); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Project</h2>
            <ul>
                <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
                <li>site: <code><?php echo app_h($app['site']); ?></code></li>
                <li>available source outputs: <code><?php echo app_h((string) count($openApiSourceOutputs)); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Selected Source Output</h2>
            <ul>
                <li>key: <code><?php echo app_h($selectedSourceOutputKey !== '' ? $selectedSourceOutputKey : 'none'); ?></code></li>
                <li>name: <?php echo app_h($selectedSourceOutput !== null ? (string) ($selectedSourceOutput['name'] ?? '') : 'n/a'); ?></li>
                <li>spec visibility: <code><?php echo app_h($selectedSourceOutput !== null ? app_source_output_spec_visibility_caption(app_source_output_effective_spec_visibility($selectedSourceOutput)) : 'n/a'); ?></code></li>
                <li>published dir: <code><?php echo app_h($selectedSourceOutput !== null ? (string) ($selectedSourceOutput['source_output_dir'] ?? '') : ''); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Spec</h2>
            <ul>
                <li>source: <code><?php echo app_h($specResult['ok'] ? $specResult['spec_source'] : 'unavailable'); ?></code></li>
                <li>path: <code><?php echo app_h($specResult['ok'] ? $specResult['spec_path'] : ''); ?></code></li>
                <li>artifact: <code><?php echo app_h(is_array($specResult['artifact']) ? (string) ($specResult['artifact']['artifact_key'] ?? '') : ($selectedArtifactKey !== '' ? $selectedArtifactKey : 'published/current')); ?></code></li>
                <li>operations: <code><?php echo app_h((string) count($operations)); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Try It Out</h2>
            <ul>
                <li>transport: <code>browser fetch</code></li>
                <li>request body: <code>application/json</code></li>
                <li>base URL: <code><?php echo app_h($baseUrlInput !== '' ? $baseUrlInput : '(manual input)'); ?></code></li>
                <li>runtime DB source: <code><?php echo app_h($selectedRuntimeDatabaseSourceKey !== '' ? $selectedRuntimeDatabaseSourceKey : 'auto-select'); ?></code></li>
                <li>runtime source options: <code><?php echo app_h((string) count($runtimeDatabaseSourceOptions)); ?></code></li>
            </ul>
            <p class="muted">proxy endpoint が cross-origin の場合は browser 側で CORS 制約を受けます。到達しない場合は accessible base URL を入れ直してください。`db_source_key` は Try It Out request の query にだけ付け、canonical metadata や openapi.json 自体は変更しません。</p>
        </section>

        <?php if ($operations !== []): ?>
            <section class="summary-card">
                <h2>Auth Helper</h2>
                <ul>
                    <li>auth helper ops: <code><?php echo app_h((string) $authHelperSummary['auth_operation_count']); ?></code></li>
                    <li>project token required: <code><?php echo app_h((string) $authHelperSummary['project_token_required_count']); ?></code></li>
                    <li>project token optional: <code><?php echo app_h((string) $authHelperSummary['project_token_optional_count']); ?></code></li>
                    <li>login cookie required: <code><?php echo app_h((string) $authHelperSummary['login_cookie_token_required_count']); ?></code></li>
                </ul>
                <p class="muted">helper 値は browser 内だけで使い、URL query や canonical metadata には保存しません。request textarea に明示した auth field があればそちらを優先します。</p>
            </section>
        <?php endif; ?>
    </div>

    <?php if ($openApiSourceOutputs === []): ?>
        <section class="toolbar">
            <div>
                <h2>Next Step</h2>
                <p class="muted">admin 側で OpenAPI source output definition を作成し、artifact を generate/publish するとここで閲覧できます。</p>
                <div class="button-row">
                    <a class="button" href="<?php echo app_h(app_project_source_outputs_path($projectKey)); ?>">Open Source Outputs</a>
                    <a class="button button-secondary" href="<?php echo app_h(app_lab_build_path($projectKey)); ?>">Open Build Run</a>
                </div>
            </div>
        </section>
    <?php else: ?>
        <form method="get" action="<?php echo app_h(app_lab_swagger_path($projectKey)); ?>" class="toolbar">
            <div>
                <label>
                    source_output_key
                    <select name="source_output_key">
                        <?php foreach ($openApiSourceOutputs as $sourceOutput): ?>
                            <?php $sourceOutputKey = app_normalize_source_output_key((string) ($sourceOutput['source_output_key'] ?? '')); ?>
                            <option value="<?php echo app_h($sourceOutputKey); ?>"<?php echo $sourceOutputKey === $selectedSourceOutputKey ? ' selected' : ''; ?>>
                                <?php echo app_h($sourceOutputKey . ' - ' . (string) ($sourceOutput['name'] ?? '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div>
                <label>
                    artifact_key
                    <select name="artifact_key">
                        <option value="">published current output / latest artifact fallback</option>
                        <?php foreach ($artifactList as $artifact): ?>
                            <?php $artifactKey = trim((string) ($artifact['artifact_key'] ?? '')); ?>
                            <option value="<?php echo app_h($artifactKey); ?>"<?php echo $artifactKey === $selectedArtifactKey ? ' selected' : ''; ?>>
                                <?php echo app_h($artifactKey . ' - ' . (string) ($artifact['created_at'] ?? '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div>
                <label>
                    base_url
                    <input name="base_url" value="<?php echo app_h($baseUrlInput); ?>" placeholder="http://127.0.0.1:8081">
                </label>
            </div>
            <div>
                <label>
                    db_source_key
                    <select name="db_source_key">
                        <option value="">auto-select (proxy runtime priority / canonical fallback)</option>
                        <?php foreach ($runtimeDatabaseSourceOptions as $runtimeDatabaseSource): ?>
                            <?php
                            $runtimeDatabaseSourceKey = trim((string) ($runtimeDatabaseSource['key'] ?? ''));
                            $runtimeDatabaseSourceLabel = trim((string) ($runtimeDatabaseSource['label'] ?? ''));
                            $runtimeDatabaseSourceCaptionParts = [$runtimeDatabaseSourceKey];
                            if ($runtimeDatabaseSourceLabel !== '' && $runtimeDatabaseSourceLabel !== $runtimeDatabaseSourceKey) {
                                $runtimeDatabaseSourceCaptionParts[] = $runtimeDatabaseSourceLabel;
                            }
                            $runtimeDatabaseSourceCaptionParts[] = 'priority=' . (string) ((int) ($runtimeDatabaseSource['proxy_runtime_priority'] ?? 0));
                            if (!empty($runtimeDatabaseSource['is_canonical_store'])) {
                                $runtimeDatabaseSourceCaptionParts[] = 'canonical';
                            }
                            ?>
                            <option value="<?php echo app_h($runtimeDatabaseSourceKey); ?>"<?php echo $runtimeDatabaseSourceKey === $selectedRuntimeDatabaseSourceKey ? ' selected' : ''; ?>>
                                <?php echo app_h(implode(' / ', $runtimeDatabaseSourceCaptionParts)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <p class="muted">external named source を明示したい時の selector です。viewer は legacy `db_config_key` ではなく `db_source_key` query を付けます。</p>
            </div>
            <div class="button-row">
                <button class="button" type="submit">Reload Viewer</button>
                <?php if ($selectedSourceOutput !== null): ?>
                    <a class="button button-secondary" href="<?php echo app_h(app_project_source_output_detail_path($projectKey, $selectedSourceOutputKey)); ?>">Source Output Detail</a>
                <?php endif; ?>
                <a class="button button-secondary" href="<?php echo app_h(app_lab_build_path($projectKey)); ?>">Build Run</a>
            </div>
        </form>

        <?php if ($authHelperSummary['requires_auth_helper']): ?>
            <section class="toolbar auth-helper-panel">
                <div>
                    <h2>Auth Helper Inputs</h2>
                    <p class="muted">auth-required endpoint 用の補助入力です。body auth field または Authorization header を送信時に補完します。</p>
                </div>
                <?php if ($authHelperSummary['project_token_required_count'] > 0 || $authHelperSummary['project_token_optional_count'] > 0): ?>
                    <div>
                        <label>
                            project_token
                            <input type="password" data-auth-helper="project-token" autocomplete="off" placeholder="project token">
                        </label>
                        <p class="muted">required: <code><?php echo app_h((string) $authHelperSummary['project_token_required_count']); ?></code> / optional: <code><?php echo app_h((string) $authHelperSummary['project_token_optional_count']); ?></code></p>
                    </div>
                <?php endif; ?>
                <?php if ($authHelperSummary['login_cookie_token_required_count'] > 0): ?>
                    <div>
                        <label>
                            login_cookie_token
                            <input type="password" data-auth-helper="login-cookie-token" autocomplete="off" placeholder="login cookie token">
                        </label>
                        <p class="muted">required: <code><?php echo app_h((string) $authHelperSummary['login_cookie_token_required_count']); ?></code></p>
                    </div>
                <?php endif; ?>
                <?php if ($authHelperSummary['static_bearer_required_count'] > 0): ?>
                    <div>
                        <label>
                            bearer_token
                            <input type="password" data-auth-helper="bearer-token" autocomplete="off" placeholder="bearer token">
                        </label>
                        <p class="muted">required: <code><?php echo app_h((string) $authHelperSummary['static_bearer_required_count']); ?></code></p>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($specResult['ok'] && is_array($specResult['spec']) && $operations !== []): ?>
        <section>
            <h2>Operations</h2>
            <div class="operations">
                <?php foreach ($operations as $index => $operation): ?>
                    <?php
                    $methodClass = 'method-' . strtolower($operation['method']);
                    $operationId = $operation['operation_id'] !== '' ? $operation['operation_id'] : ('operation-' . $index);
                    ?>
                    <section class="operation-card" data-method="<?php echo app_h($operation['method']); ?>" data-path="<?php echo app_h($operation['path']); ?>" data-auth-strategy="<?php echo app_h($operation['auth_strategy']); ?>">
                        <div class="operation-heading">
                            <span class="method-badge <?php echo app_h($methodClass); ?>"><?php echo app_h($operation['method']); ?></span>
                            <code><?php echo app_h($operation['path']); ?></code>
                            <strong><?php echo app_h($operation['summary'] !== '' ? $operation['summary'] : $operationId); ?></strong>
                        </div>
                        <div class="meta-list">
                            <?php if ($operation['source_name'] !== '' || $operation['function_name'] !== ''): ?>
                                <span>source/function: <code><?php echo app_h($operation['source_name'] . '.' . $operation['function_name']); ?></code></span>
                            <?php endif; ?>
                            <?php if ($operation['auth_strategy'] !== ''): ?>
                                <span>auth: <code><?php echo app_h($operation['auth_strategy']); ?></code></span>
                            <?php endif; ?>
                            <?php if ($operation['auth_required_fields'] !== []): ?>
                                <span>required auth fields: <code><?php echo app_h(implode(', ', $operation['auth_required_fields'])); ?></code></span>
                            <?php endif; ?>
                            <?php if ($operation['auth_optional_fields'] !== []): ?>
                                <span>optional auth fields: <code><?php echo app_h(implode(', ', $operation['auth_optional_fields'])); ?></code></span>
                            <?php endif; ?>
                            <?php if ($operation['input_kind'] !== ''): ?>
                                <span>input: <code><?php echo app_h($operation['input_kind']); ?></code></span>
                            <?php endif; ?>
                            <?php if ($operation['response_mode'] !== ''): ?>
                                <span>response: <code><?php echo app_h($operation['response_mode']); ?></code></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($operation['description'] !== ''): ?>
                            <p class="muted"><?php echo nl2br(app_h($operation['description'])); ?></p>
                        <?php endif; ?>
                        <?php if ($operation['auth_notice'] !== ''): ?>
                            <p class="auth-inline-note"><?php echo app_h($operation['auth_notice']); ?></p>
                        <?php endif; ?>
                        <p>Resolved URL: <code class="resolved-url"></code></p>
                        <label>
                            Request JSON
                            <textarea class="request-input"><?php echo app_h($operation['request_example_pretty']); ?></textarea>
                        </label>
                        <textarea class="request-example-source" hidden><?php echo app_h($operation['request_example_pretty']); ?></textarea>
                        <div class="button-row">
                            <button type="button" class="button button-secondary reset-request">Reset Example</button>
                            <button type="button" class="button try-request">Try It Out</button>
                        </div>
                        <label>
                            Response
                            <pre class="response-output"><?php echo app_h($operation['success_example_pretty']); ?></pre>
                        </label>
                    </section>
                <?php endforeach; ?>
            </div>
        </section>

        <section style="margin-top: 1.5rem;">
            <h2>Raw Spec</h2>
            <pre><?php echo app_h($specResult['spec_json']); ?></pre>
        </section>

        <script>
        (() => {
            const baseUrlInput = document.querySelector('input[name="base_url"]');
            const dbSourceKeyInput = document.querySelector('[name="db_source_key"]');
            const projectTokenInput = document.querySelector('input[data-auth-helper="project-token"]');
            const loginCookieTokenInput = document.querySelector('input[data-auth-helper="login-cookie-token"]');
            const bearerTokenInput = document.querySelector('input[data-auth-helper="bearer-token"]');
            const joinUrl = (baseUrl, path) => {
                const normalizedBaseUrl = (baseUrl || '').trim();
                const normalizedPath = (path || '').trim();
                if (normalizedBaseUrl === '') {
                    return normalizedPath;
                }
                if (/^https?:\/\//i.test(normalizedPath)) {
                    return normalizedPath;
                }
                return normalizedBaseUrl.replace(/\/+$/, '') + '/' + normalizedPath.replace(/^\/+/, '');
            };
            const readFieldValue = (field) => {
                if (
                    !(field instanceof HTMLInputElement)
                    && !(field instanceof HTMLSelectElement)
                    && !(field instanceof HTMLTextAreaElement)
                ) {
                    return '';
                }

                return field.value.trim();
            };
            const readAuthHelperValue = (input) => {
                return readFieldValue(input);
            };
            const updateUrlQueryParam = (url, key, value) => {
                const normalizedUrl = (url || '').trim();
                const normalizedKey = (key || '').trim();
                if (normalizedUrl === '' || normalizedKey === '') {
                    return normalizedUrl;
                }

                const hashIndex = normalizedUrl.indexOf('#');
                const urlHash = hashIndex === -1 ? '' : normalizedUrl.slice(hashIndex);
                const withoutHash = hashIndex === -1 ? normalizedUrl : normalizedUrl.slice(0, hashIndex);
                const queryIndex = withoutHash.indexOf('?');
                const urlPath = queryIndex === -1 ? withoutHash : withoutHash.slice(0, queryIndex);
                const urlQuery = queryIndex === -1 ? '' : withoutHash.slice(queryIndex + 1);
                const params = new URLSearchParams(urlQuery);
                const normalizedValue = (value || '').trim();

                if (normalizedValue === '') {
                    params.delete(normalizedKey);
                } else {
                    params.set(normalizedKey, normalizedValue);
                }

                const nextQuery = params.toString();

                return urlPath + (nextQuery !== '' ? '?' + nextQuery : '') + urlHash;
            };
            const payloadHasNonEmptyString = (payload, fieldName) => Object.prototype.hasOwnProperty.call(payload, fieldName)
                && typeof payload[fieldName] === 'string'
                && payload[fieldName].trim() !== '';
            const decodeRequestPayload = (rawText) => {
                const normalizedText = (rawText || '').trim();
                if (normalizedText === '') {
                    return {};
                }

                const decoded = JSON.parse(normalizedText);
                if (!decoded || Array.isArray(decoded) || typeof decoded !== 'object') {
                    throw new Error('Request JSON は JSON object である必要があります。');
                }

                return decoded;
            };
            const applyAuthHelperPayload = (payload, authStrategy) => {
                if ((authStrategy === 'project-token' || authStrategy === 'project-token-or-get-function')
                    && !payloadHasNonEmptyString(payload, 'TOKEN')) {
                    const projectToken = readAuthHelperValue(projectTokenInput);
                    if (projectToken !== '') {
                        payload.TOKEN = projectToken;
                    }
                }

                if (authStrategy === 'login-cookie-token' && !payloadHasNonEmptyString(payload, 'LOGIN_COOKIE_TOKEN')) {
                    const loginCookieToken = readAuthHelperValue(loginCookieTokenInput);
                    if (loginCookieToken !== '') {
                        payload.LOGIN_COOKIE_TOKEN = loginCookieToken;
                    }
                }

                if (authStrategy === 'project-token' && !payloadHasNonEmptyString(payload, 'TOKEN')) {
                    throw new Error('この operation は TOKEN が必要です。Auth Helper の project token か request JSON の TOKEN を設定してください。');
                }

                if (authStrategy === 'login-cookie-token' && !payloadHasNonEmptyString(payload, 'LOGIN_COOKIE_TOKEN')) {
                    throw new Error('この operation は LOGIN_COOKIE_TOKEN が必要です。Auth Helper か request JSON で指定してください。');
                }
            };
            const applyAuthHelperHeaders = (headers, authStrategy) => {
                if (authStrategy !== 'static-bearer') {
                    return;
                }

                const bearerToken = readAuthHelperValue(bearerTokenInput);
                if (bearerToken === '') {
                    throw new Error('この operation は Authorization: Bearer token が必要です。Auth Helper の bearer_token を設定してください。');
                }

                headers.Authorization = 'Bearer ' + bearerToken;
            };
            const resolveRequestUrl = (card) => {
                return updateUrlQueryParam(
                    joinUrl(baseUrlInput ? baseUrlInput.value : '', card.dataset.path || ''),
                    'db_source_key',
                    readFieldValue(dbSourceKeyInput),
                );
            };

            const updateResolvedUrls = () => {
                document.querySelectorAll('.operation-card').forEach((card) => {
                    const resolvedUrl = resolveRequestUrl(card);
                    const target = card.querySelector('.resolved-url');
                    if (target) {
                        target.textContent = resolvedUrl;
                    }
                });
            };

            if (baseUrlInput) {
                baseUrlInput.addEventListener('input', updateResolvedUrls);
            }
            if (dbSourceKeyInput instanceof HTMLInputElement || dbSourceKeyInput instanceof HTMLSelectElement) {
                dbSourceKeyInput.addEventListener('input', updateResolvedUrls);
                dbSourceKeyInput.addEventListener('change', updateResolvedUrls);
            }

            updateResolvedUrls();

            document.querySelectorAll('.operation-card').forEach((card) => {
                const requestInput = card.querySelector('.request-input');
                const requestExample = card.querySelector('.request-example-source');
                const responseOutput = card.querySelector('.response-output');
                const resetButton = card.querySelector('.reset-request');
                const tryButton = card.querySelector('.try-request');

                if (!(requestInput instanceof HTMLTextAreaElement) || !(responseOutput instanceof HTMLElement)) {
                    return;
                }

                if (resetButton instanceof HTMLButtonElement && requestExample instanceof HTMLTextAreaElement) {
                    resetButton.addEventListener('click', () => {
                        requestInput.value = requestExample.value;
                    });
                }

                if (!(tryButton instanceof HTMLButtonElement)) {
                    return;
                }

                tryButton.addEventListener('click', async () => {
                    const requestUrl = resolveRequestUrl(card);
                    if (!/^https?:\/\//i.test(requestUrl)) {
                        responseOutput.textContent = 'Base URL を http:// または https:// で入力してください。';
                        return;
                    }

                    const method = (card.dataset.method || 'POST').toUpperCase();
                    const authStrategy = (card.dataset.authStrategy || '').trim();
                    let requestBody = undefined;
                    const headers = {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    };

                    if (method !== 'GET' && method !== 'HEAD') {
                        try {
                            const payload = decodeRequestPayload(requestInput.value);
                            applyAuthHelperPayload(payload, authStrategy);
                            requestBody = JSON.stringify(payload);
                        } catch (error) {
                            responseOutput.textContent = 'Request payload を準備できませんでした: ' + (error instanceof Error ? error.message : String(error));
                            return;
                        }
                    } else if (authStrategy === 'project-token' || authStrategy === 'login-cookie-token') {
                        responseOutput.textContent = 'この operation は request body auth field が必要ですが、viewer の GET/HEAD 実装では送れません。POST 系 endpoint を使うか viewer を拡張してください。';
                        return;
                    }

                    try {
                        applyAuthHelperHeaders(headers, authStrategy);
                    } catch (error) {
                        responseOutput.textContent = 'Request headers を準備できませんでした: ' + (error instanceof Error ? error.message : String(error));
                        return;
                    }

                    responseOutput.textContent = 'Loading...';

                    try {
                        const response = await fetch(requestUrl, {
                            method,
                            headers,
                            body: requestBody,
                        });
                        const rawText = await response.text();
                        let prettyText = rawText;

                        try {
                            prettyText = JSON.stringify(JSON.parse(rawText), null, 2);
                        } catch (_error) {
                            // keep raw text as-is
                        }

                        responseOutput.textContent = 'HTTP ' + response.status + ' ' + response.statusText + '\n\n' + prettyText;
                    } catch (error) {
                        responseOutput.textContent = 'Request failed. Browser から endpoint に到達できないか、CORS により拒否された可能性があります。\n\n'
                            + (error instanceof Error ? error.message : String(error));
                    }
                });
            });
        })();
        </script>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
