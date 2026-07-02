<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/project_output_service.php';
require_once __DIR__ . '/project_permission.php';
require_once __DIR__ . '/project_source_output_route_common.php';
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
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_project_source_output_artifact_detail_page(array $app, array $request): void
{
    if ($app['site'] !== 'admin') {
        app_render_forbidden_page($app, $request, 'この route は 設定変更用サイト でのみ利用します。');
        return;
    }

    $principal = app_auth_principal();
    if ($principal === null) {
        app_send_redirect_response($request, app_auth_login_path());
        return;
    }

    if (!app_auth_has_any_role(['admin', 'config'], $principal)) {
        app_render_forbidden_page($app, $request, 'source output artifact の確認には admin または config role が必要です。');
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

    $artifactKey = trim(app_route_param($request, 'artifact_key'));
    if (!app_project_output_artifact_key_is_valid($artifactKey)) {
        app_render_bad_request_page($app, $request, 'artifact key の形式が不正です。');
        return;
    }

    $permission = app_project_permission_can_with_audit(
        $app,
        $projectKey,
        $principal,
        'source_output.download',
        'source_output_artifact',
        $artifactKey,
    );
    if (!$permission['ok']) {
        app_render_internal_error_page($app, $request);
        return;
    }
    if (!$permission['allowed']) {
        app_render_forbidden_page($app, $request, 'source output artifact の確認には project publisher 以上の権限が必要です。');
        return;
    }

    $artifactResult = app_project_output_find($app, $projectKey, $artifactKey);
    if (!$artifactResult['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Source Output Artifact</title>
</head>
<body>
<main>
    <h1>Source Output Artifact</h1>
    <p>artifact の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>artifact key: <code><?php echo app_h($artifactKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($artifactResult['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return;
    }

    $artifact = $artifactResult['item'];
    if ($artifact === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $summaryRows = [
        ['field' => 'artifact_key', 'value' => $artifact['artifact_key']],
        ['field' => 'project_key', 'value' => $artifact['project_key']],
        ['field' => 'source_output_key', 'value' => $artifact['source_output_key']],
        ['field' => 'source_output_name', 'value' => $artifact['source_output_name']],
        ['field' => 'artifact_strategy', 'value' => $artifact['artifact_strategy']],
        ['field' => 'created_at', 'value' => $artifact['created_at']],
        ['field' => 'requested_by', 'value' => $artifact['requested_by']],
        ['field' => 'archive_format', 'value' => $artifact['archive_format']],
        ['field' => 'archive_filename', 'value' => $artifact['archive_filename']],
        ['field' => 'archive_exists', 'value' => $artifact['archive_exists'] ? 'yes' : 'no'],
        ['field' => 'archive_size', 'value' => app_project_source_outputs_format_bytes($artifact['archive_size'])],
        ['field' => 'runtime_source_relative_path', 'value' => $artifact['runtime_source_relative_path']],
        ['field' => 'source_files', 'value' => number_format($artifact['source_file_count']) . ' / ' . app_project_source_outputs_format_bytes($artifact['source_total_bytes'])],
        ['field' => 'custom_layer_source', 'value' => $artifact['custom_layer_source']],
        ['field' => 'custom_layer_files', 'value' => number_format($artifact['custom_layer_file_count']) . ' / ' . app_project_source_outputs_format_bytes($artifact['custom_layer_total_bytes'])],
        ['field' => 'bundle_entry_root', 'value' => $artifact['bundle_entry_root']],
        ['field' => 'manifest_path', 'value' => $artifact['manifest_path']],
        ['field' => 'bundle_manifest_path', 'value' => $artifact['bundle_manifest_path']],
        ['field' => 'artifact_dir', 'value' => $artifact['artifact_dir']],
        ['field' => 'bundle_root', 'value' => $artifact['bundle_root']],
    ];

    app_send_html_response_headers($request, 200);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Source Output Artifact</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 76rem;
            background: #ffffff;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 2rem;
        }
        code {
            background: #edf2f7;
            border-radius: 6px;
            padding: 0.1rem 0.3rem;
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
        .summary-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
            background: #f8fafc;
        }
        .muted {
            color: #475569;
        }
        .button {
            display: inline-block;
            border-radius: 8px;
            background: #0f172a;
            color: #ffffff;
            padding: 0.65rem 1rem;
            text-decoration: none;
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
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo app_h(rawurlencode($projectKey)); ?>"><?php echo app_h($projectKey); ?></a> / <a href="<?php echo app_h(app_project_source_outputs_path($projectKey)); ?>">source-outputs</a> / artifact</p>

    <h1>Source Output Artifact</h1>
    <p class="muted">生成済み source output artifact の manifest と archive 状態を読み取り専用で確認します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Artifact</h2>
            <ul>
                <li>artifact key: <code><?php echo app_h($artifact['artifact_key']); ?></code></li>
                <li>created: <code><?php echo app_h($artifact['created_at']); ?></code></li>
                <li>requested by: <code><?php echo app_h($artifact['requested_by']); ?></code></li>
                <li>strategy: <code><?php echo app_h($artifact['artifact_strategy']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Source Output</h2>
            <ul>
                <li>key: <a href="<?php echo app_h(app_project_source_output_detail_path($projectKey, $artifact['source_output_key'])); ?>"><code><?php echo app_h($artifact['source_output_key']); ?></code></a></li>
                <li>name: <code><?php echo app_h($artifact['source_output_name']); ?></code></li>
                <li>program: <code><?php echo app_h(app_source_output_program_language_caption($artifact['source_output_program_language'])); ?></code></li>
                <li>class: <code><?php echo app_h(app_source_output_class_type_caption($artifact['source_output_class_type'])); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Archive</h2>
            <ul>
                <li>filename: <code><?php echo app_h($artifact['archive_filename']); ?></code></li>
                <li>status: <code><?php echo app_h($artifact['archive_exists'] ? 'available' : 'missing'); ?></code></li>
                <li>size: <code><?php echo app_h(app_project_source_outputs_format_bytes($artifact['archive_size'])); ?></code></li>
            </ul>
            <?php if ($artifact['archive_exists']): ?>
                <p><a class="button" href="<?php echo app_h(app_project_source_output_download_path($projectKey, $artifact['artifact_key'])); ?>">Download Artifact</a></p>
            <?php endif; ?>
        </section>
    </div>

    <section>
        <h2>Manifest Summary</h2>
        <table>
            <thead>
            <tr>
                <th>field</th>
                <th>value</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($summaryRows as $row): ?>
                <tr>
                    <td><code><?php echo app_h($row['field']); ?></code></td>
                    <td><?php echo app_h((string) $row['value']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
    <?php
}
