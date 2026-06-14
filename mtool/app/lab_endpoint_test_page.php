<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/endpoint_test_job_service.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';

function app_lab_endpoint_test_candidate_ref(array $candidate): string
{
    return app_normalize_source_output_key((string) ($candidate['source_output_key'] ?? ''))
        . '|'
        . trim((string) ($candidate['source_name'] ?? ''))
        . '|'
        . trim((string) ($candidate['function_name'] ?? ''));
}

/**
 * @return array{
 *     source_output_key:string,
 *     db_access_key:string,
 *     function_key:string
 * }
 */
function app_lab_endpoint_test_parse_candidate_ref(string $value): array
{
    $parts = explode('|', trim($value), 3);

    return [
        'source_output_key' => isset($parts[0]) ? app_normalize_source_output_key($parts[0]) : '',
        'db_access_key' => isset($parts[1]) ? trim($parts[1]) : '',
        'function_key' => isset($parts[2]) ? trim($parts[2]) : '',
    ];
}

function app_lab_endpoint_test_format_bytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $value = (float) $bytes;
    $unitIndex = 0;

    while ($value >= 1024 && $unitIndex < count($units) - 1) {
        $value /= 1024;
        $unitIndex++;
    }

    if ($unitIndex === 0) {
        return (string) $bytes . ' ' . $units[$unitIndex];
    }

    return number_format($value, 1) . ' ' . $units[$unitIndex];
}

function app_render_lab_endpoint_test_page(array $app, array $request): void
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
        app_render_forbidden_page($app, $request, 'endpoint test 実行には lab または admin role が必要です。');
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
    <title><?php echo app_h($app['site_name']); ?> - Endpoint Test</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>endpoint test の読み込みに失敗しました。</p>
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
    $notices = [];

    $catalogResult = app_endpoint_test_single_proxy_candidate_catalog($app, $projectKey);
    $candidateSourceOutputs = $catalogResult['source_outputs'];
    $candidates = $catalogResult['items'];
    if (!$catalogResult['ok']) {
        $errors[] = $catalogResult['error'];
    }

    $candidatesByRef = [];
    $candidatesBySourceOutputKey = [];
    $candidatesByFunction = [];
    foreach ($candidates as $candidate) {
        $candidateRef = app_lab_endpoint_test_candidate_ref($candidate);
        $candidatesByRef[$candidateRef] = $candidate;
        $sourceOutputKey = (string) $candidate['source_output_key'];
        if (!array_key_exists($sourceOutputKey, $candidatesBySourceOutputKey)) {
            $candidatesBySourceOutputKey[$sourceOutputKey] = [];
        }
        $candidatesBySourceOutputKey[$sourceOutputKey][] = $candidate;

        $functionRef = trim((string) $candidate['source_name']) . '|' . trim((string) $candidate['function_name']);
        if (!array_key_exists($functionRef, $candidatesByFunction)) {
            $candidatesByFunction[$functionRef] = [];
        }
        $candidatesByFunction[$functionRef][] = $candidate;
    }

    $selectedCandidate = null;
    $selectedCandidateRef = '';
    $selectedSourceOutputKey = '';
    $selectedDbAccessKey = '';
    $selectedFunctionKey = '';
    $selectedJob = null;
    $requestJsonInput = app_request_method_is($request, 'POST')
        ? app_post_param('request_json', "{}\n")
        : app_query_param('request_json', "{}\n");
    $endpointUrlInput = app_request_method_is($request, 'POST')
        ? app_post_param('endpoint_url')
        : app_query_param('endpoint_url');
    $baseUrlInput = app_request_method_is($request, 'POST')
        ? app_post_param('base_url')
        : app_query_param('base_url');
    $endpointFilenameInput = app_request_method_is($request, 'POST')
        ? app_post_param('endpoint_filename')
        : app_query_param('endpoint_filename');
    $action = app_request_method_is($request, 'POST') ? trim(app_post_param('action')) : '';

    if (app_request_method_is($request, 'POST')) {
        $selectedCandidateRef = trim(app_post_param('candidate_ref'));
        $parsedCandidateRef = app_lab_endpoint_test_parse_candidate_ref($selectedCandidateRef);
        $selectedSourceOutputKey = $parsedCandidateRef['source_output_key'];
        $selectedDbAccessKey = $parsedCandidateRef['db_access_key'];
        $selectedFunctionKey = $parsedCandidateRef['function_key'];
    } else {
        $selectedSourceOutputKey = app_normalize_source_output_key(app_query_param('source_output_key'));
        $selectedDbAccessKey = trim(app_query_param('db_access_key'));
        $selectedFunctionKey = trim(app_query_param('function_key'));
    }

    if ($selectedSourceOutputKey !== '' || $selectedDbAccessKey !== '' || $selectedFunctionKey !== '') {
        if (
            $selectedSourceOutputKey !== ''
            && $selectedDbAccessKey !== ''
            && $selectedFunctionKey !== ''
        ) {
            $selectedCandidate = app_endpoint_test_find_candidate(
                $candidates,
                $selectedSourceOutputKey,
                $selectedDbAccessKey,
                $selectedFunctionKey,
            );
            if ($selectedCandidate === null && !app_request_method_is($request, 'POST')) {
                $notices[] = '指定された single-function proxy 候補は current build plan 上で見つかりませんでした。manual URL 入力で続行できます。';
            }
        } elseif ($selectedSourceOutputKey !== '' && array_key_exists($selectedSourceOutputKey, $candidatesBySourceOutputKey)) {
            $selectedCandidate = $candidatesBySourceOutputKey[$selectedSourceOutputKey][0];
        } elseif ($selectedDbAccessKey !== '' && $selectedFunctionKey !== '') {
            $functionRef = $selectedDbAccessKey . '|' . $selectedFunctionKey;
            if (array_key_exists($functionRef, $candidatesByFunction)) {
                $selectedCandidate = $candidatesByFunction[$functionRef][0];
                if (count($candidatesByFunction[$functionRef]) > 1) {
                    $notices[] = '同じ function を持つ proxy candidate が複数あるため、先頭の source output を選択しています。必要なら候補を切り替えてください。';
                }
            }
        }
    } elseif ($candidates !== []) {
        $selectedCandidate = $candidates[0];
    }

    if ($selectedCandidate !== null) {
        $selectedCandidateRef = app_lab_endpoint_test_candidate_ref($selectedCandidate);
        $selectedSourceOutputKey = (string) $selectedCandidate['source_output_key'];
        $selectedDbAccessKey = (string) $selectedCandidate['source_name'];
        $selectedFunctionKey = (string) $selectedCandidate['function_name'];

        if (!app_request_method_is($request, 'POST') && $baseUrlInput === '') {
            $baseUrlInput = trim((string) ($selectedCandidate['proxy_base_url'] ?? ''));
        }
        if (!app_request_method_is($request, 'POST') && $endpointFilenameInput === '') {
            $endpointFilenameInput = trim((string) ($selectedCandidate['endpoint_filename'] ?? ''));
        }
    }

    if ($action !== '' && $action !== 'run-endpoint-test') {
        $errors[] = '未対応の操作です。';
    }

    if (app_request_method_is($request, 'POST') && $action === 'run-endpoint-test') {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif ($errors === []) {
            $requestDefinition = [
                'source_output_key' => $selectedCandidate !== null ? (string) $selectedCandidate['source_output_key'] : '',
                'source_output_name' => $selectedCandidate !== null ? (string) $selectedCandidate['source_output_name'] : '',
                'source_name' => $selectedCandidate !== null ? (string) $selectedCandidate['source_name'] : '',
                'function_name' => $selectedCandidate !== null ? (string) $selectedCandidate['function_name'] : '',
                'endpoint_filename' => trim($endpointFilenameInput) !== ''
                    ? trim($endpointFilenameInput)
                    : ($selectedCandidate !== null ? (string) $selectedCandidate['endpoint_filename'] : ''),
                'endpoint_url' => trim($endpointUrlInput),
                'base_url' => trim($baseUrlInput) !== ''
                    ? trim($baseUrlInput)
                    : ($selectedCandidate !== null ? trim((string) $selectedCandidate['proxy_base_url']) : ''),
                'request_json' => $requestJsonInput,
                'endpoint_label' => $selectedCandidate !== null
                    ? (string) ($selectedCandidate['display_name'] !== '' ? $selectedCandidate['display_name'] : $selectedCandidate['source_name'] . '.' . $selectedCandidate['function_name'])
                    : '',
            ];

            $jobResult = app_endpoint_test_job_create(
                $app,
                $projectKey,
                $requestDefinition,
                'lab-ui:' . $principal['id'],
            );
            if ($jobResult['ok'] && $jobResult['job'] !== null) {
                $redirectQuery = [
                    'job_key' => $jobResult['job']['job_key'],
                ];
                if ($selectedCandidate !== null) {
                    $redirectQuery['source_output_key'] = $selectedCandidate['source_output_key'];
                    $redirectQuery['db_access_key'] = $selectedCandidate['source_name'];
                    $redirectQuery['function_key'] = $selectedCandidate['function_name'];
                }

                app_send_redirect_response($request, app_lab_endpoint_test_path($projectKey, $redirectQuery));
                return;
            }

            $errors[] = $jobResult['error'];
        }
    }

    $jobKey = trim(app_query_param('job_key'));
    if ($jobKey !== '') {
        if (!app_endpoint_test_job_key_is_valid($jobKey)) {
            $errors[] = 'job key の形式が不正です。';
        } else {
            $jobResult = app_endpoint_test_job_find($app, $jobKey);
            if (!$jobResult['ok']) {
                $errors[] = $jobResult['error'];
            } elseif ($jobResult['item'] === null) {
                $errors[] = '指定された endpoint test job が見つかりません。';
            } elseif ($jobResult['item']['project_key'] !== $projectKey) {
                $errors[] = '指定された endpoint test job は別 project のものです。';
            } else {
                $selectedJob = $jobResult['item'];
            }
        }
    }

    $recentJobsResult = app_endpoint_test_job_list($app, $projectKey, 10);
    $recentJobs = [];
    if ($recentJobsResult['ok']) {
        $recentJobs = $recentJobsResult['items'];
        if ($selectedJob === null && $jobKey === '' && $recentJobs !== []) {
            $selectedJob = $recentJobs[0];
        }
    } else {
        $errors[] = $recentJobsResult['error'];
    }

    $effectiveBaseUrl = trim($baseUrlInput) !== ''
        ? trim($baseUrlInput)
        : ($selectedCandidate !== null ? trim((string) $selectedCandidate['proxy_base_url']) : '');
    $effectiveEndpointFilename = trim($endpointFilenameInput) !== ''
        ? trim($endpointFilenameInput)
        : ($selectedCandidate !== null ? trim((string) $selectedCandidate['endpoint_filename']) : '');
    $resolvedEndpointUrl = app_endpoint_test_resolve_endpoint_url(
        trim($endpointUrlInput),
        $effectiveBaseUrl,
        $effectiveEndpointFilename,
    );
    $requestJsonValidation = app_endpoint_test_validate_request_json($requestJsonInput);
    $statusCode = $errors === [] ? 200 : 422;
    $primaryDatabaseStatus = app_probe_database($app);
    $configDatabaseStatus = app_probe_config_database($app);

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Endpoint Test</title>
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
            background: #fefce8;
            border-color: #facc15;
        }
        .error-list {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 12px;
            padding: 1rem 1.25rem;
        }
        .notice-list {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            border-radius: 12px;
            padding: 1rem 1.25rem;
        }
        .muted {
            color: #475569;
        }
        .form-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.35rem;
        }
        input[type="text"],
        input[type="url"],
        select,
        textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0.7rem 0.85rem;
            font: inherit;
            background: #ffffff;
        }
        textarea {
            min-height: 16rem;
            resize: vertical;
            font-family: ui-monospace, SFMono-Regular, SFMono-Regular, Menlo, Consolas, monospace;
        }
        .actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
        }
        button {
            border: 1px solid #0f172a;
            border-radius: 999px;
            background: #0f172a;
            color: #ffffff;
            padding: 0.65rem 1.2rem;
            font: inherit;
            cursor: pointer;
        }
        button:hover {
            background: #1e293b;
        }
        .status-pill {
            display: inline-block;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 700;
        }
        .status-completed {
            background: #dcfce7;
            color: #166534;
        }
        .status-failed {
            background: #fee2e2;
            color: #b91c1c;
        }
        .status-yes {
            background: #dcfce7;
            color: #166534;
        }
        .status-no {
            background: #e2e8f0;
            color: #334155;
        }
        .job-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .job-table th,
        .job-table td {
            border-bottom: 1px solid #d7dde5;
            padding: 0.75rem;
            text-align: left;
            vertical-align: top;
        }
        .job-table td a {
            white-space: nowrap;
        }
        .stack > * + * {
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / endpoint test</p>

    <h1><?php echo app_h($project['name']); ?> Endpoint Test</h1>
    <p>single-function proxy server の build plan から candidate を選ぶか、manual absolute URL を指定して JSON endpoint test を実行します。結果は file-based job manifest と snapshot として保存されます。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Runtime</h2>
            <ul>
                <li>site: <code><?php echo app_h($app['site']); ?></code></li>
                <li>login user: <code><?php echo app_h($principal['id']); ?></code></li>
                <li>roles: <code><?php echo app_h(implode(', ', $principal['roles'])); ?></code></li>
                <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
                <li>route: <code>/runs/endpoints/<?php echo app_h($projectKey); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Candidate Catalog</h2>
            <ul>
                <li>single-proxy server outputs: <code><?php echo app_h((string) count($candidateSourceOutputs)); ?></code></li>
                <li>endpoint candidates: <code><?php echo app_h((string) count($candidates)); ?></code></li>
                <li>storage root: <code><?php echo app_h(app_endpoint_test_job_storage_root($app, $projectKey)); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Database Probe</h2>
            <ul>
                <li>main db: <code><?php echo app_h($primaryDatabaseStatus['label']); ?></code> / <?php echo app_h($primaryDatabaseStatus['detail']); ?></li>
                <li>config db: <code><?php echo app_h($configDatabaseStatus['label']); ?></code> / <?php echo app_h($configDatabaseStatus['detail']); ?></li>
            </ul>
        </section>

        <section class="<?php echo $resolvedEndpointUrl['ok'] ? 'note-card' : 'warning-card'; ?>">
            <h2>Resolved Endpoint</h2>
            <?php if ($resolvedEndpointUrl['ok']): ?>
                <p><code><?php echo app_h($resolvedEndpointUrl['endpoint_url']); ?></code></p>
            <?php else: ?>
                <p class="muted"><?php echo app_h($resolvedEndpointUrl['error']); ?></p>
            <?php endif; ?>
            <ul>
                <li>request JSON: <span class="status-pill status-<?php echo app_h($requestJsonValidation['ok'] ? 'yes' : 'no'); ?>"><?php echo app_h($requestJsonValidation['ok'] ? 'valid' : 'invalid'); ?></span></li>
                <li>manual URL priority: <code>endpoint_url</code> が入っていれば最優先です。</li>
            </ul>
        </section>
    </div>

    <?php if ($errors !== []): ?>
        <section class="error-list">
            <h2>Errors</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if ($notices !== []): ?>
        <section class="notice-list">
            <h2>Notes</h2>
            <ul>
                <?php foreach ($notices as $notice): ?>
                    <li><?php echo app_h($notice); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <section class="stack">
        <h2>Run Test</h2>
        <form method="post" action="<?php echo app_h(app_lab_endpoint_test_path($projectKey)); ?>">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="action" value="run-endpoint-test">

            <div>
                <label for="candidate_ref">single-function proxy candidate</label>
                <select id="candidate_ref" name="candidate_ref">
                    <option value="">manual URL only</option>
                    <?php foreach ($candidatesBySourceOutputKey as $sourceOutputKey => $groupCandidates): ?>
                        <optgroup label="<?php echo app_h($sourceOutputKey); ?>">
                            <?php foreach ($groupCandidates as $candidate): ?>
                                <?php $candidateRef = app_lab_endpoint_test_candidate_ref($candidate); ?>
                                <option value="<?php echo app_h($candidateRef); ?>"<?php echo $candidateRef === $selectedCandidateRef ? ' selected' : ''; ?>>
                                    <?php
                                    echo app_h(
                                        (string) $candidate['source_name']
                                        . '.'
                                        . (string) $candidate['function_name']
                                        . ' -> '
                                        . (string) $candidate['endpoint_filename']
                                    );
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
                <p class="muted">current `single-proxy-server` build plan から選びます。blank の `proxy_base_url` は manual 入力してください。</p>
            </div>

            <div class="form-grid">
                <div>
                    <label for="endpoint_url">endpoint URL</label>
                    <input id="endpoint_url" type="url" name="endpoint_url" value="<?php echo app_h($endpointUrlInput); ?>" placeholder="http://127.0.0.1/proxyserver-*.php">
                    <p class="muted">absolute URL を直接指定する場合に使います。</p>
                </div>
                <div>
                    <label for="base_url">base URL</label>
                    <input id="base_url" type="url" name="base_url" value="<?php echo app_h($baseUrlInput); ?>" placeholder="http://127.0.0.1">
                    <p class="muted">candidate の `proxy_base_url` を上書きできます。</p>
                </div>
                <div>
                    <label for="endpoint_filename">endpoint filename</label>
                    <input id="endpoint_filename" type="text" name="endpoint_filename" value="<?php echo app_h($endpointFilenameInput); ?>" placeholder="proxyserver-dbtable-GetdbtableList.php">
                    <p class="muted">`base_url + endpoint_filename` で URL を組み立てます。</p>
                </div>
            </div>

            <div>
                <label for="request_json">request JSON</label>
                <textarea id="request_json" name="request_json"><?php echo app_h($requestJsonInput); ?></textarea>
            </div>

            <div class="actions">
                <button type="submit">Run Endpoint Test</button>
                <span class="muted">POST 実行後は同じ画面へ戻り、job detail を inline 表示します。</span>
            </div>
        </form>
    </section>

    <?php if ($selectedCandidate !== null): ?>
        <section class="summary-card">
            <h2>Selected Candidate</h2>
            <ul>
                <li>source output: <code><?php echo app_h((string) $selectedCandidate['source_output_key']); ?></code> / <?php echo app_h((string) $selectedCandidate['source_output_name']); ?></li>
                <li>function: <code><?php echo app_h((string) $selectedCandidate['source_name']); ?>.<?php echo app_h((string) $selectedCandidate['function_name']); ?></code></li>
                <li>endpoint filename: <code><?php echo app_h((string) $selectedCandidate['endpoint_filename']); ?></code></li>
                <li>release target: <code><?php echo app_h((string) $selectedCandidate['release_target_type']); ?></code></li>
                <li>proxy base URL: <code><?php echo app_h((string) ($selectedCandidate['proxy_base_url'] !== '' ? $selectedCandidate['proxy_base_url'] : '(blank)')); ?></code></li>
                <li>resolved: <span class="status-pill status-<?php echo app_h($selectedCandidate['resolved'] ? 'yes' : 'no'); ?>"><?php echo app_h($selectedCandidate['resolved'] ? 'yes' : 'no'); ?></span></li>
                <li>auth policy: <code><?php echo app_h((string) $selectedCandidate['auth_policy']['resolved_auth_type']); ?></code> / <?php echo app_h((string) $selectedCandidate['auth_policy']['strategy_caption']); ?></li>
            </ul>
            <p class="muted"><?php echo app_h((string) $selectedCandidate['auth_policy']['summary']); ?></p>
            <?php if ((string) $selectedCandidate['resolution_error'] !== ''): ?>
                <p class="muted">resolution error: <?php echo app_h((string) $selectedCandidate['resolution_error']); ?></p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <section>
        <h2>Recent Jobs</h2>
        <?php if ($recentJobs === []): ?>
            <p class="muted">まだ endpoint test job はありません。</p>
        <?php else: ?>
            <table class="job-table">
                <thead>
                <tr>
                    <th>created</th>
                    <th>status</th>
                    <th>endpoint</th>
                    <th>HTTP</th>
                    <th>bytes</th>
                    <th>job</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recentJobs as $job): ?>
                    <?php
                    $jobLink = app_lab_endpoint_test_path($projectKey, [
                        'job_key' => $job['job_key'],
                        'source_output_key' => $job['source_output_key'],
                        'db_access_key' => $job['source_name'],
                        'function_key' => $job['function_name'],
                    ]);
                    ?>
                    <tr>
                        <td><code><?php echo app_h($job['created_at']); ?></code></td>
                        <td>
                            <span class="status-pill status-<?php echo app_h($job['status']); ?>">
                                <?php echo app_h($job['status']); ?>
                            </span>
                            <?php if ($job['error_message'] !== ''): ?>
                                <br><span class="muted"><?php echo app_h($job['error_message']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code><?php echo app_h($job['endpoint_label']); ?></code><br>
                            <span class="muted"><?php echo app_h($job['endpoint_url']); ?></span>
                        </td>
                        <td><code><?php echo app_h((string) $job['http_code']); ?></code></td>
                        <td>
                            <code><?php echo app_h(app_lab_endpoint_test_format_bytes((int) $job['request_bytes'])); ?></code> /
                            <code><?php echo app_h(app_lab_endpoint_test_format_bytes((int) $job['response_bytes'])); ?></code>
                        </td>
                        <td><a href="<?php echo app_h($jobLink); ?>">open</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <?php if ($selectedJob !== null): ?>
        <section class="stack">
            <h2>Selected Job</h2>
            <div class="summary-grid">
                <section class="summary-card">
                    <h3>Summary</h3>
                    <ul>
                        <li>job key: <code><?php echo app_h($selectedJob['job_key']); ?></code></li>
                        <li>status: <span class="status-pill status-<?php echo app_h($selectedJob['status']); ?>"><?php echo app_h($selectedJob['status']); ?></span></li>
                        <li>http code: <code><?php echo app_h((string) $selectedJob['http_code']); ?></code></li>
                        <li>response JSON: <span class="status-pill status-<?php echo app_h($selectedJob['response_json_valid'] ? 'yes' : 'no'); ?>"><?php echo app_h($selectedJob['response_json_valid'] ? 'yes' : 'no'); ?></span></li>
                        <li>api: <code><?php echo app_h(app_lab_endpoint_test_job_api_path($selectedJob['job_key'])); ?></code></li>
                    </ul>
                </section>

                <section class="summary-card">
                    <h3>Target</h3>
                    <ul>
                        <li>endpoint label: <code><?php echo app_h($selectedJob['endpoint_label']); ?></code></li>
                        <li>source output: <code><?php echo app_h($selectedJob['source_output_key'] !== '' ? $selectedJob['source_output_key'] : '(manual)'); ?></code></li>
                        <li>function: <code><?php echo app_h($selectedJob['source_name']); ?>.<?php echo app_h($selectedJob['function_name']); ?></code></li>
                        <li>endpoint filename: <code><?php echo app_h($selectedJob['endpoint_filename']); ?></code></li>
                        <li>endpoint URL: <code><?php echo app_h($selectedJob['endpoint_url']); ?></code></li>
                    </ul>
                </section>

                <section class="<?php echo $selectedJob['status'] === 'completed' ? 'note-card' : 'warning-card'; ?>">
                    <h3>Stored Snapshots</h3>
                    <ul>
                        <li>manifest: <code><?php echo app_h($selectedJob['manifest_path']); ?></code></li>
                        <li>request: <code><?php echo app_h($selectedJob['request_snapshot_path']); ?></code></li>
                        <li>response: <code><?php echo app_h($selectedJob['response_snapshot_path']); ?></code></li>
                        <?php if ($selectedJob['response_pretty_snapshot_path'] !== ''): ?>
                            <li>pretty response: <code><?php echo app_h($selectedJob['response_pretty_snapshot_path']); ?></code></li>
                        <?php endif; ?>
                    </ul>
                </section>
            </div>

            <?php if ($selectedJob['error_message'] !== ''): ?>
                <section class="warning-card">
                    <h3>Error Message</h3>
                    <p><?php echo app_h($selectedJob['error_message']); ?></p>
                </section>
            <?php endif; ?>

            <section class="summary-card">
                <h3>Request JSON</h3>
                <pre><?php echo app_h($selectedJob['request_body'] !== '' ? $selectedJob['request_body'] : $selectedJob['request_json_pretty']); ?></pre>
            </section>

            <section class="summary-card">
                <h3>Response</h3>
                <pre><?php echo app_h($selectedJob['response_pretty'] !== '' ? $selectedJob['response_pretty'] : $selectedJob['response_body']); ?></pre>
            </section>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
