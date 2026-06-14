<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/build_job_service.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';

function app_render_lab_build_job_page(array $app, array $request): void
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
        app_render_forbidden_page($app, $request, 'build job の閲覧には lab または admin role が必要です。');
        return;
    }

    if (!app_request_method_is($request, 'GET')) {
        app_render_method_not_allowed_page($app, $request, ['GET']);
        return;
    }

    $jobKey = trim(app_route_param($request, 'job_key'));
    if (!app_build_job_key_is_valid($jobKey)) {
        app_render_bad_request_page($app, $request, 'job key の形式が不正です。');
        return;
    }

    $jobResult = app_build_job_find($app, $jobKey);
    if (!$jobResult['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Build Job</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>build job の読み込みに失敗しました。</p>
    <ul>
        <li>job key: <code><?php echo app_h($jobKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($jobResult['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return;
    }

    if ($jobResult['item'] === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $job = $jobResult['item'];
    $projectResult = app_fetch_project_by_key($app, $job['project_key']);
    $projectName = $job['project_key'];
    if ($projectResult['ok'] && $projectResult['item'] !== null) {
        $projectName = $projectResult['item']['name'];
    }

    $recentJobsResult = app_build_job_list($app, $job['project_key'], 10);
    $recentJobs = [];
    $recentJobsError = '';
    if ($recentJobsResult['ok']) {
        foreach ($recentJobsResult['items'] as $recentJob) {
            if ($recentJob['job_key'] === $job['job_key']) {
                continue;
            }

            $recentJobs[] = $recentJob;
        }
    } else {
        $recentJobsError = $recentJobsResult['error'];
    }

    app_send_html_response_headers($request, 200);
    $isLabSite = $app['site'] === 'lab';
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Build Job</title>
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
        }
        .muted {
            color: #475569;
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
            / <a href="/projects/<?php echo rawurlencode($job['project_key']); ?>"><code><?php echo app_h($job['project_key']); ?></code></a>
        <?php endif; ?>
        / <a href="<?php echo app_h(app_lab_build_path($job['project_key'], $job['release_target_type_filter'], $job['view'])); ?>"><code><?php echo app_h($job['project_key']); ?></code> / build</a> / <code><?php echo app_h($job['job_key']); ?></code>
    </p>

    <h1><?php echo app_h($projectName); ?> Build Job</h1>
    <p>build job は selected <code>ProjectSourceOutput</code> definition ごとに artifact 生成と publish を順に実行した結果を保持します。旧 <code>BuildToken</code> の逐次ポーリングではなく、completed snapshot をこの job manifest で確認します。</p>

    <?php if ($job['errors'] !== []): ?>
        <section class="warning-card">
            <h2>Job Errors</h2>
            <ul class="error-list">
                <?php foreach ($job['errors'] as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Job</h2>
            <ul>
                <li>job key: <code><?php echo app_h($job['job_key']); ?></code></li>
                <li>status: <code><?php echo app_h(app_build_job_status_caption($job['status'])); ?></code></li>
                <li>created: <code><?php echo app_h($job['created_at']); ?></code></li>
                <li>requested by: <code><?php echo app_h($job['requested_by']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Summary</h2>
            <ul>
                <li>selected: <code><?php echo app_h((string) $job['selected_source_output_count']); ?></code></li>
                <li>success: <code><?php echo app_h((string) $job['successful_count']); ?></code></li>
                <li>failed: <code><?php echo app_h((string) $job['failed_count']); ?></code></li>
                <li>written: <code><?php echo app_h((string) $job['published_count']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Manifest</h2>
            <ul>
                <li>write requested: <code><?php echo app_h($job['publish_requested'] ? 'yes' : 'no'); ?></code></li>
                <li>release filter: <code><?php echo app_h($job['release_target_type_filter']); ?></code></li>
                <li>view: <code><?php echo app_h($job['view']); ?></code></li>
                <li>manifest: <code><?php echo app_h($job['manifest_path']); ?></code></li>
            </ul>
        </section>
    </div>

    <div class="button-row">
        <a class="button" href="<?php echo app_h(app_lab_build_path($job['project_key'], $job['release_target_type_filter'], $job['view'])); ?>">Run Another Build</a>
        <a class="button" href="<?php echo app_h(app_lab_build_job_api_path($job['job_key'])); ?>">JSON API</a>
        <a class="button" href="/projects/<?php echo rawurlencode($job['project_key']); ?>/source-outputs">Admin Source Outputs</a>
    </div>

    <h2 class="section-heading">Outputs</h2>
    <table>
        <thead>
        <tr>
            <th>source output</th>
            <th>status</th>
            <th>artifact</th>
            <th>publish</th>
            <th>cli</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($job['entries'] as $entry): ?>
            <tr>
                <td>
                    <strong><code><?php echo app_h($entry['source_output_key']); ?></code></strong><br>
                    <?php echo app_h($entry['source_output_name']); ?><br>
                    <span class="muted"><?php echo app_h(app_source_output_program_language_caption($entry['source_output_program_language'])); ?> / <?php echo app_h(app_source_output_class_type_caption($entry['class_type'])); ?> / <?php echo app_h(app_source_output_release_target_type_caption($entry['release_target_type'])); ?></span>
                </td>
                <td>
                    <code><?php echo app_h(app_build_job_entry_status_caption($entry['status'])); ?></code><br>
                    <?php if ($entry['error'] !== ''): ?>
                        <span class="error-list"><?php echo app_h($entry['error']); ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($entry['artifact_key'] !== ''): ?>
                        <code><?php echo app_h($entry['artifact_key']); ?></code><br>
                        <span class="muted"><?php echo app_h(number_format($entry['source_file_count'])); ?> files / <?php echo app_h(app_build_job_format_bytes($entry['source_total_bytes'])); ?></span><br>
                        <span class="muted"><?php echo app_h($entry['artifact_created_at']); ?></span>
                    <?php else: ?>
                        <span class="muted">artifact not created</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($entry['published_root'] !== ''): ?>
                        <code><?php echo app_h($entry['source_output_dir']); ?></code><br>
                        <span class="muted"><?php echo app_h(number_format($entry['published_file_count'])); ?> files / <?php echo app_h(app_build_job_format_bytes($entry['published_total_bytes'])); ?></span><br>
                        <span class="muted"><?php echo app_h($entry['published_at']); ?></span>
                    <?php else: ?>
                        <span class="muted">not written</span>
                    <?php endif; ?>
                </td>
                <td>
                    <code><?php echo app_h(app_build_job_cli_command($job['project_key'], $entry['source_output_key'])); ?></code>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2 class="section-heading">Recent Jobs</h2>
    <?php if ($recentJobsError !== ''): ?>
        <p class="error-list"><?php echo app_h($recentJobsError); ?></p>
    <?php elseif ($recentJobs === []): ?>
        <p class="muted">他の build job はまだありません。</p>
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
            <?php foreach ($recentJobs as $recentJob): ?>
                <tr>
                    <td>
                        <a href="<?php echo app_h(app_lab_build_job_path($recentJob['job_key'])); ?>"><code><?php echo app_h($recentJob['job_key']); ?></code></a><br>
                        <span class="muted"><?php echo app_h($recentJob['created_at']); ?></span>
                    </td>
                    <td>
                        <code><?php echo app_h(app_build_job_status_caption($recentJob['status'])); ?></code><br>
                        <span class="muted">requested by: <?php echo app_h($recentJob['requested_by']); ?></span>
                    </td>
                    <td>
                        selected <code><?php echo app_h((string) $recentJob['selected_source_output_count']); ?></code>,
                        success <code><?php echo app_h((string) $recentJob['successful_count']); ?></code>,
                        failed <code><?php echo app_h((string) $recentJob['failed_count']); ?></code>
                    </td>
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
