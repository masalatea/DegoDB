<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/compare_output_job_service.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';

/**
 * @param array{
 *     site:string,
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_lab_compare_output_job_page(array $app, array $request): void
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
        app_render_forbidden_page($app, $request, 'compare output job の閲覧には lab または admin role が必要です。');
        return;
    }

    if (!app_request_method_is($request, 'GET')) {
        app_render_method_not_allowed_page($app, $request, ['GET']);
        return;
    }

    $jobKey = trim(app_route_param($request, 'job_key'));
    if (!app_compare_output_job_key_is_valid($jobKey)) {
        app_render_bad_request_page($app, $request, 'job key の形式が不正です。');
        return;
    }

    $jobResult = app_compare_output_job_find($app, $jobKey);
    if (!$jobResult['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Compare Output Job</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>compare output job の読み込みに失敗しました。</p>
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

    $recentJobsResult = app_compare_output_job_list($app, $job['project_key'], $job['compare_output_key'], 10);
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
    <title><?php echo app_h($app['site_name']); ?> - Compare Output Job</title>
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
        .muted {
            color: #475569;
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
        / <a href="<?php echo app_h(app_lab_compare_output_path($job['project_key'], $job['compare_output_key'])); ?>"><code><?php echo app_h($job['project_key']); ?></code> / compare-output</a> / <code><?php echo app_h($job['job_key']); ?></code>
    </p>

    <h1><?php echo app_h($projectName); ?> Compare Output Job</h1>
    <p>compare output 実行結果の snapshot、warning、pair 情報を review する画面です。<code>admin</code> 側 definition から生成された結果をここで確認します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Job</h2>
            <ul>
                <li>job key: <code><?php echo app_h($job['job_key']); ?></code></li>
                <li>created at: <code><?php echo app_h($job['created_at']); ?></code></li>
                <li>requested by: <code><?php echo app_h($job['requested_by']); ?></code></li>
                <li>api: <code><?php echo app_h(app_lab_compare_output_job_api_path($job['job_key'])); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Definition</h2>
            <ul>
                <li>project key: <code><?php echo app_h($job['project_key']); ?></code></li>
                <li>compare output key: <code><?php echo app_h($job['compare_output_key']); ?></code></li>
                <li>name: <code><?php echo app_h($job['compare_output_name']); ?></code></li>
                <li>source of truth: <code><?php echo app_h($job['source_of_truth'] !== '' ? $job['source_of_truth'] : '(blank)'); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Result</h2>
            <ul>
                <li>deviation pairs: <code><?php echo app_h((string) $job['deviation_pair_count']); ?></code></li>
                <li>checked pairs: <code><?php echo app_h((string) $job['checked_pair_count']); ?></code></li>
                <li>warnings: <code><?php echo app_h((string) $job['warning_count']); ?></code></li>
                <li>bytes: <code><?php echo app_h((string) $job['output_bytes']); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>Snapshot</h2>
            <ul>
                <li>live output file: <code><?php echo app_h($job['output_file_absolute_path']); ?></code></li>
                <li>job snapshot: <code><?php echo app_h($job['output_snapshot_path']); ?></code></li>
                <li>manifest: <code><?php echo app_h($job['manifest_path']); ?></code></li>
                <li>snapshot size: <code><?php echo app_h((string) $job['output_snapshot_size']); ?></code></li>
            </ul>
        </section>
    </div>

    <div class="button-row">
        <a class="button" href="<?php echo app_h(app_lab_compare_output_path($job['project_key'], $job['compare_output_key'])); ?>">Back To Project Run</a>
        <a class="button" href="<?php echo app_h(app_lab_compare_output_job_api_path($job['job_key'])); ?>">Open JSON API</a>
    </div>

    <?php if ($job['warnings'] !== []): ?>
        <section class="warning-card">
            <h2>Warnings</h2>
            <ul>
                <?php foreach ($job['warnings'] as $warning): ?>
                    <li><?php echo app_h($warning); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <section>
        <h2 class="section-heading">Resolved Paths</h2>
        <ul>
            <li>storage base: <code><?php echo app_h($job['resolved_storage_base_path']); ?></code></li>
            <li>compare root: <code><?php echo app_h($job['compare_root_absolute_path']); ?></code></li>
            <li>output directory: <code><?php echo app_h($job['output_directory_absolute_path']); ?></code></li>
            <li>compare tool file path: <code><?php echo app_h($job['compare_tool_file_path'] !== '' ? $job['compare_tool_file_path'] : '(blank)'); ?></code></li>
        </ul>
    </section>

    <section>
        <h2 class="section-heading">Rendered Output</h2>
        <pre><?php echo app_h($job['rendered_content']); ?></pre>
    </section>

    <section>
        <h2 class="section-heading">Deviation Pairs</h2>
        <?php if ($job['pairs'] === []): ?>
            <p class="muted">deviation pair はありません。</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>source</th>
                    <th>pair key</th>
                    <th>path A</th>
                    <th>path B</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($job['pairs'] as $pair): ?>
                    <tr>
                        <td><code><?php echo app_h($pair['pair_source']); ?></code></td>
                        <td><code><?php echo app_h($pair['pair_key']); ?></code></td>
                        <td><code><?php echo app_h($pair['path_a']); ?></code></td>
                        <td><code><?php echo app_h($pair['path_b']); ?></code></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section>
        <h2 class="section-heading">Recent Jobs</h2>
        <?php if ($recentJobsError !== ''): ?>
            <p class="muted">recent jobs の読み込みに失敗しました: <code><?php echo app_h($recentJobsError); ?></code></p>
        <?php elseif ($recentJobs === []): ?>
            <p class="muted">同じ compare output key の過去 job はまだありません。</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>job</th>
                    <th>requested by</th>
                    <th>result</th>
                    <th>action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recentJobs as $recentJob): ?>
                    <tr>
                        <td>
                            <code><?php echo app_h($recentJob['job_key']); ?></code><br>
                            <span class="muted"><?php echo app_h($recentJob['created_at']); ?></span>
                        </td>
                        <td><code><?php echo app_h($recentJob['requested_by']); ?></code></td>
                        <td>
                            pairs <code><?php echo app_h((string) $recentJob['deviation_pair_count']); ?></code><br>
                            warnings <code><?php echo app_h((string) $recentJob['warning_count']); ?></code>
                        </td>
                        <td><a href="<?php echo app_h(app_lab_compare_output_job_path($recentJob['job_key'])); ?>">open</a></td>
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
