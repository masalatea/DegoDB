<?php

declare(strict_types=1);

require_once __DIR__ . '/db_access_endpoint_policy.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/endpoint_test_job_service.php';
require_once __DIR__ . '/project_db_access_route_common.php';

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
function app_render_project_db_access_function_endpoint_page(array $app, array $request): void
{
    $bootstrap = app_project_db_access_route_bootstrap($app, $request);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $catalog = $bootstrap['generated_catalog'];
    $dbAccessKey = trim(app_route_param($request, 'db_access_key'));
    $functionKey = trim(app_route_param($request, 'function_key'));
    if ($dbAccessKey === '' || $functionKey === '') {
        app_render_bad_request_page($app, $request, 'db access key と function key が必要です。');
        return;
    }

    $entity = app_generated_catalog_find_entity($catalog, $dbAccessKey);
    if ($entity === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $methodCatalog = app_generated_file_method_catalog($entity['dbaccess_path']);
    $method = app_generated_file_find_method($methodCatalog, $functionKey);
    if ($method === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $functionProfile = app_project_db_access_guess_function_profile($method['name']);
    $legacyDafuncSchema = app_project_db_access_legacy_metadata_schema($app, 'dafunc');
    $canonicalResult = app_fetch_db_access_function_metadata($app, $projectKey, $entity['source_name'], $method['name']);
    $canonicalError = $canonicalResult['ok'] ? '' : $canonicalResult['error'];
    $canonicalItem = $canonicalResult['ok'] ? $canonicalResult['item'] : null;
    $authPolicy = app_resolve_db_access_single_proxy_auth_policy(
        $canonicalItem !== null ? $canonicalItem['single_proxy_auth_type'] : '',
        $canonicalItem !== null ? $canonicalItem['single_proxy_single_get_function_name'] : '',
    );
    $endpointPath = '/api/projects/' . rawurlencode($projectKey)
        . '/db-access/' . rawurlencode($entity['source_name'])
        . '/' . rawurlencode($functionProfile['endpoint_slug']);
    $endpointTestCatalogResult = app_endpoint_test_single_proxy_candidate_catalog($app, $projectKey);
    $endpointTestCandidates = [];
    $endpointTestCatalogError = '';
    if ($endpointTestCatalogResult['ok']) {
        foreach ($endpointTestCatalogResult['items'] as $candidate) {
            if (
                (string) ($candidate['source_name'] ?? '') === $entity['source_name']
                && (string) ($candidate['function_name'] ?? '') === $method['name']
            ) {
                $endpointTestCandidates[] = $candidate;
            }
        }
    } else {
        $endpointTestCatalogError = $endpointTestCatalogResult['error'];
    }
    $unresolvedItems = [
        'request schema',
        'response schema',
    ];
    if (!$authPolicy['is_valid']) {
        $unresolvedItems[] = 'single-function proxy auth policy (`SingleProxy_AuthType`, `SingleProxy_SingleGetFuncPID`)';
    }
    $unresolvedItems[] = 'multi-step custom proxy と direct endpoint の切り分け';

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access Function Endpoint</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 78rem;
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
            padding: 1rem;
            overflow-x: auto;
            white-space: pre-wrap;
        }
        .breadcrumbs {
            margin-bottom: 1rem;
        }
        .summary-card, .note-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .summary-card {
            background: #f8fafc;
        }
        .note-card {
            background: #fefce8;
            border-color: #facc15;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access">db-access</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>"><code><?php echo app_h($entity['source_name']); ?></code></a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions">functions</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>"><code><?php echo app_h($method['name']); ?></code></a> / endpoint</p>

    <h1><?php echo app_h($project['name']); ?> Endpoint Preview</h1>
    <p>function の endpoint contract を確認する preview 画面です。実 endpoint はまだ作らず、generated method と保存済み canonical metadata を合わせて endpoint contract を表示します。</p>

    <section class="summary-card">
        <h2>Endpoint Draft</h2>
        <ul>
            <li>db access: <code><?php echo app_h($entity['source_name']); ?></code></li>
            <li>function: <code><?php echo app_h($method['name']); ?></code></li>
            <li>HTTP method guess: <code><?php echo app_h($functionProfile['http_method']); ?></code></li>
            <li>action type: <code><?php echo app_h($canonicalItem !== null && $canonicalItem['action_type'] !== '' ? $canonicalItem['action_type'] : $functionProfile['legacy_action_type']); ?></code></li>
            <li>function suffix: <code><?php echo app_h($canonicalItem !== null && $canonicalItem['function_suffix'] !== '' ? $canonicalItem['function_suffix'] : $functionProfile['function_suffix_candidate']); ?></code></li>
            <li>path draft: <code><?php echo app_h($endpointPath); ?></code></li>
        </ul>
    </section>

    <section class="summary-card">
        <h2>Canonical Metadata</h2>
        <?php if ($canonicalError !== ''): ?>
            <p class="muted">未接続</p>
            <p class="muted"><?php echo app_h($canonicalError); ?></p>
        <?php elseif ($canonicalItem === null): ?>
            <p class="muted">未保存</p>
            <p class="muted"><a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($entity['source_name']); ?>/functions/<?php echo rawurlencode($method['name']); ?>">function detail</a> から canonical row を作成できます。</p>
        <?php else: ?>
            <ul>
                <li>source of truth: <code><?php echo app_h($canonicalItem['source_of_truth']); ?></code></li>
                <li>select_by_distinct: <code><?php echo app_h($canonicalItem['select_by_distinct']); ?></code></li>
                <li>is_blob_target: <code><?php echo app_h($canonicalItem['is_blob_target']); ?></code></li>
                <li>updated: <code><?php echo app_h($canonicalItem['updated_at']); ?></code></li>
            </ul>
        <?php endif; ?>
    </section>

    <section class="summary-card">
        <h2>Auth Policy (Single Function)</h2>
        <ul>
            <li>scope: <code>single-function proxy</code></li>
            <li>raw auth type: <code><?php echo app_h($authPolicy['raw_auth_type'] !== '' ? $authPolicy['raw_auth_type'] : '(blank)'); ?></code> / <?php echo app_h($authPolicy['raw_auth_type_caption']); ?></li>
            <li>resolved auth type: <code><?php echo app_h($authPolicy['resolved_auth_type']); ?></code> / <?php echo app_h($authPolicy['resolved_auth_type_caption']); ?></li>
            <li>strategy: <code><?php echo app_h($authPolicy['strategy_key']); ?></code> / <?php echo app_h($authPolicy['strategy_caption']); ?></li>
            <li>single get function: <code><?php echo app_h($authPolicy['single_get_function_name'] !== '' ? $authPolicy['single_get_function_name'] : '(blank)'); ?></code></li>
            <li>status: <code><?php echo app_h($authPolicy['is_valid'] ? 'resolved' : 'incomplete'); ?></code></li>
        </ul>
        <p class="muted"><?php echo app_h($authPolicy['summary']); ?></p>
        <?php if ($authPolicy['notes'] !== []): ?>
            <ul>
                <?php foreach ($authPolicy['notes'] as $note): ?>
                    <li class="muted"><?php echo app_h($note); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="summary-card">
        <h2>Endpoint Test Runner</h2>
        <?php if ($endpointTestCatalogError !== ''): ?>
            <p class="muted"><?php echo app_h($endpointTestCatalogError); ?></p>
        <?php elseif ($endpointTestCandidates === []): ?>
            <p class="muted">current single-proxy build plan では、この function に対応する endpoint test candidate は見つかりませんでした。</p>
            <p class="muted"><a href="<?php echo app_h(app_lab_endpoint_test_path($projectKey, [
                'db_access_key' => $entity['source_name'],
                'function_key' => $method['name'],
            ])); ?>">manual / current candidate search を開く</a></p>
        <?php else: ?>
            <p class="muted">current `/runs/endpoints/{project_key}` で single-function proxy candidate を選んだ状態で開けます。</p>
            <ul>
                <?php foreach ($endpointTestCandidates as $candidate): ?>
                    <li>
                        <a href="<?php echo app_h(app_lab_endpoint_test_path($projectKey, [
                            'source_output_key' => (string) $candidate['source_output_key'],
                            'db_access_key' => (string) $candidate['source_name'],
                            'function_key' => (string) $candidate['function_name'],
                        ])); ?>">
                            <code><?php echo app_h((string) $candidate['source_output_key']); ?></code>
                            / <code><?php echo app_h((string) $candidate['endpoint_filename']); ?></code>
                        </a>
                        <br><span class="muted"><?php echo app_h((string) $candidate['display_name']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="note-card">
        <h2>未確定項目</h2>
        <ul>
            <?php foreach ($unresolvedItems as $unresolvedItem): ?>
                <li><?php echo app_h($unresolvedItem); ?></li>
            <?php endforeach; ?>
        </ul>
        <p class="muted">multi-step custom proxy の auth は `project_custom_proxies.auth_type` / `single_get_function_name` で別管理します。</p>
        <p class="muted">legacy `dafunc` schema field count: <code><?php echo app_h((string) count($legacyDafuncSchema['field_names'])); ?></code></p>
    </section>

    <section class="summary-card">
        <h2>Reasoning</h2>
        <pre><?php echo app_h(json_encode([
            'source_name' => $entity['source_name'],
            'function_name' => $method['name'],
            'signature' => $method['signature'],
            'line' => $method['line'],
            'http_method_guess' => $functionProfile['http_method'],
            'action_guess' => $functionProfile['action'],
            'legacy_action_type' => $functionProfile['legacy_action_type'],
            'function_suffix_candidate' => $functionProfile['function_suffix_candidate'],
            'canonical_metadata' => $canonicalItem,
            'canonical_error' => $canonicalError,
            'auth_policy' => $authPolicy,
            'legacy_dafunc_fields' => $legacyDafuncSchema['field_names'],
            'endpoint_path_draft' => $endpointPath,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
    </section>
</main>
</body>
</html>
    <?php
}
