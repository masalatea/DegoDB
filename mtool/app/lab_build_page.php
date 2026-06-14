<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/build_job_service.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/source_output_repository.php';

function app_render_lab_build_page(array $app, array $request): void
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
        app_render_forbidden_page($app, $request, 'build 実行には lab または admin role が必要です。');
        return;
    }

    if (!app_request_method_is($request, 'GET') && !app_request_method_is($request, 'POST')) {
        app_render_method_not_allowed_page($app, $request, ['GET', 'POST']);
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
    <title><?php echo app_h($app['site_name']); ?> - Build Run</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>build run の読み込みに失敗しました。</p>
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
    $csrfToken = app_csrf_token();
    $errors = [];

    $selectedReleaseTargetType = app_request_method_is($request, 'POST')
        ? app_build_job_normalize_release_target_filter(app_post_param('release_target_type'))
        : app_build_job_normalize_release_target_filter(app_query_param('release_target_type', 'Release'));
    $view = app_request_method_is($request, 'POST')
        ? app_build_job_requested_view(app_post_param('view', 'summary'))
        : app_build_job_requested_view(app_query_param('view', 'summary'));

    $catalogResult = app_fetch_project_source_output_catalog($app, $projectKey);
    $sourceOutputs = $catalogResult['items'];
    if (!$catalogResult['ok']) {
        $errors[] = $catalogResult['error'];
    }

    $sourceOutputsByKey = [];
    foreach ($sourceOutputs as $sourceOutput) {
        $sourceOutputKey = is_string($sourceOutput['source_output_key'] ?? null)
            ? app_normalize_source_output_key($sourceOutput['source_output_key'])
            : '';
        if ($sourceOutputKey === '' || !app_source_output_key_is_valid($sourceOutputKey)) {
            continue;
        }

        $sourceOutputsByKey[$sourceOutputKey] = $sourceOutput;
    }

    $buildableSourceOutputs = app_build_job_supported_source_outputs($sourceOutputs);
    $buildableSourceOutputsByKey = [];
    foreach ($buildableSourceOutputs as $sourceOutput) {
        $sourceOutputKey = app_normalize_source_output_key((string) ($sourceOutput['source_output_key'] ?? ''));
        if ($sourceOutputKey === '') {
            continue;
        }

        $buildableSourceOutputsByKey[$sourceOutputKey] = $sourceOutput;
    }

    $unsupportedSourceOutputs = [];
    foreach ($sourceOutputsByKey as $sourceOutputKey => $sourceOutput) {
        if (!array_key_exists($sourceOutputKey, $buildableSourceOutputsByKey)) {
            $unsupportedSourceOutputs[] = $sourceOutput;
        }
    }

    $selectedSourceOutputKeys = [];
    if (app_request_method_is($request, 'POST')) {
        $selectedSourceOutputKeys = app_build_job_normalize_selected_source_output_keys($_POST['source_output_keys'] ?? null);
    } else {
        $singleSelectedSourceOutputKey = app_normalize_source_output_key(app_query_param('source_output_key'));
        if (
            $singleSelectedSourceOutputKey !== ''
            && array_key_exists($singleSelectedSourceOutputKey, $buildableSourceOutputsByKey)
        ) {
            $selectedSourceOutputKeys = [$singleSelectedSourceOutputKey];
        } else {
            $selectedSourceOutputKeys = app_build_job_default_selected_source_output_keys(
                $buildableSourceOutputs,
                $selectedReleaseTargetType,
            );
        }
    }

    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif (trim(app_post_param('action')) !== 'run-build') {
            $errors[] = '未対応の操作です。';
        } elseif ($selectedSourceOutputKeys === []) {
            $errors[] = 'build 対象の source output を 1 件以上選択してください。';
        } else {
            $selectedSourceOutputs = [];
            foreach ($selectedSourceOutputKeys as $sourceOutputKey) {
                if (!array_key_exists($sourceOutputKey, $sourceOutputsByKey)) {
                    $errors[] = '存在しない source output key が含まれています: ' . $sourceOutputKey;
                    continue;
                }

                if (!array_key_exists($sourceOutputKey, $buildableSourceOutputsByKey)) {
                    $errors[] = 'artifact 生成に未対応の source output が選択されています: ' . $sourceOutputKey;
                    continue;
                }

                $selectedSourceOutputs[] = $buildableSourceOutputsByKey[$sourceOutputKey];
            }

            if ($errors === []) {
                $jobResult = app_build_job_create(
                    $app,
                    $projectKey,
                    $selectedSourceOutputs,
                    'lab-ui:' . $principal['id'],
                    [
                        'publish_requested' => true,
                        'view' => $view,
                        'release_target_type_filter' => $selectedReleaseTargetType,
                    ],
                );
                if ($jobResult['ok'] && $jobResult['job'] !== null) {
                    app_send_redirect_response($request, app_lab_build_job_path($jobResult['job']['job_key']));
                    return;
                }

                $errors[] = $jobResult['error'];
            }
        }
    }

    $recentJobsResult = app_build_job_list($app, $projectKey, 10);
    $recentJobs = [];
    $recentJobsError = '';
    if ($recentJobsResult['ok']) {
        $recentJobs = $recentJobsResult['items'];
    } else {
        $recentJobsError = $recentJobsResult['error'];
    }

    $selectedCliCommands = [];
    foreach ($selectedSourceOutputKeys as $sourceOutputKey) {
        if (!array_key_exists($sourceOutputKey, $buildableSourceOutputsByKey)) {
            continue;
        }

        $selectedCliCommands[] = app_build_job_cli_command($projectKey, $sourceOutputKey);
    }

    $statusCode = $errors === [] ? 200 : 422;
    $primaryDatabaseStatus = app_probe_database($app);
    $configDatabaseStatus = app_probe_config_database($app);
    $isLabSite = $app['site'] === 'lab';

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Build Run</title>
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
        pre {
            background: #edf2f7;
            padding: 0.9rem 1rem;
            border-radius: 8px;
            overflow-x: auto;
            white-space: pre-wrap;
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
        .summary-card, .note-card, .warning-card {
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
            background: #fff7ed;
            border-color: #fdba74;
        }
        .status-ok {
            color: #166534;
        }
        .status-error {
            color: #b91c1c;
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
        .section-heading {
            margin-top: 2rem;
            margin-bottom: 0.25rem;
        }
        .button-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        .button {
            display: inline-block;
            border: 0;
            border-radius: 8px;
            background: #0f172a;
            color: #ffffff;
            padding: 0.65rem 1rem;
            font: inherit;
            text-decoration: none;
            cursor: pointer;
        }
        .button-secondary {
            background: #e2e8f0;
            color: #0f172a;
        }
        .muted {
            color: #475569;
        }
        .tag {
            display: inline-block;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
            background: #e2e8f0;
            margin-right: 0.35rem;
            margin-bottom: 0.25rem;
        }
        .error-list {
            color: #b91c1c;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs">
        <a href="/dashboard">dashboard</a>
        <?php if ($isLabSite): ?>
            / <a href="/experiments">experiments</a>
        <?php else: ?>
            / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a>
        <?php endif; ?>
        / <code><?php echo app_h($projectKey); ?></code> / build
    </p>

    <h1><?php echo app_h($project['name']); ?> Build Run</h1>
    <p>current build run は選択した <code>ProjectSourceOutput</code> definition を順に <code>generate + write output</code> し、結果を file-based job manifest として残します。旧 <code>BuildToken</code> のポーリングは使わず、artifact 履歴は <code>work/artifacts/source-outputs/</code> に残し、current raw output は全 project で <code>work/source-outputs/</code> に materialize します。repo に残す sample asset が必要な場合だけ、対応する pack の <code>sample/&lt;category&gt;/&lt;pack&gt;/reference/</code> に curated に置きます。</p>

    <?php if ($errors !== []): ?>
        <section class="warning-card">
            <h2>Build Errors</h2>
            <ul class="error-list">
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
                <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
                <li>lifecycle: <code><?php echo app_h($project['lifecycle_status']); ?></code></li>
                <li>release filter: <code><?php echo app_h($selectedReleaseTargetType); ?></code></li>
                <li>view: <code><?php echo app_h($view); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Source Outputs</h2>
            <ul>
                <li>buildable: <code><?php echo app_h((string) count($buildableSourceOutputs)); ?></code></li>
                <li>selected: <code><?php echo app_h((string) count($selectedCliCommands)); ?></code></li>
                <li>unsupported: <code><?php echo app_h((string) count($unsupportedSourceOutputs)); ?></code></li>
                <li>default output root: <code><?php echo app_h(app_project_output_default_relative_path($projectKey)); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Storage</h2>
            <ul>
                <li>job storage: <code><?php echo app_h(app_build_job_storage_root($app, $projectKey)); ?></code></li>
                <li>artifact storage: <code><?php echo app_h(app_project_output_storage_root($app, $projectKey)); ?></code></li>
                <li>reference root: <code><?php echo app_h($app['generated']['root']); ?></code></li>
                <li>work root: <code><?php echo app_h($app['work']['root']); ?></code></li>
                <li>primary DB: <span class="<?php echo $primaryDatabaseStatus['ok'] ? 'status-ok' : 'status-error'; ?>"><?php echo app_h($primaryDatabaseStatus['summary']); ?></span></li>
                <li>config DB: <span class="<?php echo $configDatabaseStatus['ok'] ? 'status-ok' : 'status-error'; ?>"><?php echo app_h($configDatabaseStatus['summary']); ?></span></li>
            </ul>
        </section>
    </div>

    <section class="note-card">
        <h2>Selection Presets</h2>
        <p class="muted">旧 <code>build_project.php</code> の Main / Beta と detailed build は、current では query preset と source output 選択へ畳み込んでいます。</p>
        <div class="button-row">
            <a class="button button-secondary" href="<?php echo app_h(app_lab_build_path($projectKey, 'Release', $view)); ?>">Release Preset</a>
            <a class="button button-secondary" href="<?php echo app_h(app_lab_build_path($projectKey, 'Beta', $view)); ?>">Beta Preset</a>
            <a class="button button-secondary" href="<?php echo app_h(app_lab_build_path($projectKey, $selectedReleaseTargetType, 'summary')); ?>">Summary View</a>
            <a class="button button-secondary" href="<?php echo app_h(app_lab_build_path($projectKey, $selectedReleaseTargetType, 'detailed')); ?>">Detailed View</a>
        </div>
    </section>

    <h2 class="section-heading">Buildable Outputs</h2>
    <?php if ($buildableSourceOutputs === []): ?>
        <p class="muted">artifact 生成に対応した source output definition はまだありません。</p>
    <?php else: ?>
        <form method="post" action="<?php echo app_h(app_lab_build_path($projectKey, $selectedReleaseTargetType, $view)); ?>">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="action" value="run-build">
            <input type="hidden" name="release_target_type" value="<?php echo app_h($selectedReleaseTargetType); ?>">
            <input type="hidden" name="view" value="<?php echo app_h($view); ?>">

            <table>
                <thead>
                <tr>
                    <th>select</th>
                    <th>source output</th>
                    <th>type</th>
                    <th>strategy</th>
                    <th>publish target</th>
                    <?php if ($view === 'detailed'): ?>
                        <th>notes</th>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($buildableSourceOutputs as $sourceOutput): ?>
                    <?php $sourceOutputKey = app_normalize_source_output_key((string) ($sourceOutput['source_output_key'] ?? '')); ?>
                    <tr>
                        <td>
                            <input
                                type="checkbox"
                                name="source_output_keys[]"
                                value="<?php echo app_h($sourceOutputKey); ?>"
                                <?php echo in_array($sourceOutputKey, $selectedSourceOutputKeys, true) ? 'checked' : ''; ?>
                            >
                        </td>
                        <td>
                            <strong><code><?php echo app_h($sourceOutputKey); ?></code></strong><br>
                            <?php echo app_h((string) ($sourceOutput['name'] ?? '')); ?>
                        </td>
                        <td>
                            <span class="tag"><?php echo app_h(app_source_output_program_language_caption((string) ($sourceOutput['program_language'] ?? ''))); ?></span>
                            <span class="tag"><?php echo app_h(app_source_output_class_type_caption((string) ($sourceOutput['class_type'] ?? ''))); ?></span>
                            <span class="tag"><?php echo app_h(app_source_output_release_target_type_caption((string) ($sourceOutput['release_target_type'] ?? ''))); ?></span>
                        </td>
                        <td>
                            <code><?php echo app_h(app_source_output_artifact_strategy_caption((string) ($sourceOutput['artifact_strategy'] ?? ''))); ?></code>
                        </td>
                        <td>
                            <code><?php echo app_h((string) ($sourceOutput['source_output_dir'] ?? '')); ?></code>
                        </td>
                        <?php if ($view === 'detailed'): ?>
                            <td class="muted">
                                binding: <code><?php echo app_h(app_source_output_target_binding_scope_caption(app_source_output_target_binding_scope($sourceOutput))); ?></code><br>
                                <?php echo app_h((string) ($sourceOutput['notes'] ?? '')); ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="button-row">
                <button class="button" type="submit">Build Selected Outputs</button>
                <a class="button button-secondary" href="/projects/<?php echo rawurlencode($projectKey); ?>/source-outputs">Admin Source Outputs</a>
            </div>
        </form>
    <?php endif; ?>

    <?php if ($selectedCliCommands !== []): ?>
        <h2 class="section-heading">CLI</h2>
        <p class="muted">UI と同じ処理を CLI で再実行する場合のコマンドです。</p>
        <pre><?php echo app_h(implode(PHP_EOL, $selectedCliCommands)); ?></pre>
    <?php endif; ?>

    <h2 class="section-heading">Recent Jobs</h2>
    <?php if ($recentJobsError !== ''): ?>
        <p class="error-list"><?php echo app_h($recentJobsError); ?></p>
    <?php elseif ($recentJobs === []): ?>
        <p class="muted">まだ build job はありません。</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>job</th>
                <th>status</th>
                <th>summary</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($recentJobs as $job): ?>
                <tr>
                    <td>
                        <a href="<?php echo app_h(app_lab_build_job_path($job['job_key'])); ?>"><code><?php echo app_h($job['job_key']); ?></code></a><br>
                        <span class="muted"><?php echo app_h($job['created_at']); ?></span>
                    </td>
                    <td>
                        <code><?php echo app_h(app_build_job_status_caption($job['status'])); ?></code><br>
                        <span class="muted">requested by: <?php echo app_h($job['requested_by']); ?></span>
                    </td>
                    <td>
                        selected <code><?php echo app_h((string) $job['selected_source_output_count']); ?></code>,
                        success <code><?php echo app_h((string) $job['successful_count']); ?></code>,
                        failed <code><?php echo app_h((string) $job['failed_count']); ?></code>,
                        written <code><?php echo app_h((string) $job['published_count']); ?></code>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($unsupportedSourceOutputs !== []): ?>
        <h2 class="section-heading">Unsupported Definitions</h2>
        <p class="muted">以下は current build run ではまだ artifact 生成を持たない definition です。</p>
        <table>
            <thead>
            <tr>
                <th>source output</th>
                <th>type</th>
                <th>strategy</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($unsupportedSourceOutputs as $sourceOutput): ?>
                <tr>
                    <td>
                        <code><?php echo app_h((string) ($sourceOutput['source_output_key'] ?? '')); ?></code><br>
                        <?php echo app_h((string) ($sourceOutput['name'] ?? '')); ?>
                    </td>
                    <td>
                        <span class="tag"><?php echo app_h(app_source_output_class_type_caption((string) ($sourceOutput['class_type'] ?? ''))); ?></span>
                        <span class="tag"><?php echo app_h(app_source_output_release_target_type_caption((string) ($sourceOutput['release_target_type'] ?? ''))); ?></span>
                    </td>
                    <td><code><?php echo app_h((string) ($sourceOutput['artifact_strategy'] ?? '')); ?></code></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
