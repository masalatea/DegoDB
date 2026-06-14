<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db_access_endpoint_policy.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/endpoint_test_job_service.php';
require_once __DIR__ . '/project_db_access_bootstrap_service.php';
require_once __DIR__ . '/project_db_access_metadata_helper.php';
require_once __DIR__ . '/project_db_access_route_common.php';
require_once __DIR__ . '/project_proxy_route_common.php';
require_once __DIR__ . '/source_output_repository.php';

function app_project_single_proxy_function_effective_order(array $method, ?array $canonicalItem): int
{
    if (
        $canonicalItem !== null
        && ctype_digit((string) ($canonicalItem['function_list_order'] ?? ''))
        && (int) $canonicalItem['function_list_order'] > 0
    ) {
        return (int) $canonicalItem['function_list_order'];
    }

    if (
        $canonicalItem !== null
        && ctype_digit((string) ($canonicalItem['detected_line'] ?? ''))
        && (int) $canonicalItem['detected_line'] > 0
    ) {
        return (int) $canonicalItem['detected_line'];
    }

    return (int) ($method['line'] ?? 0);
}

function app_project_single_proxy_bulk_target_assignments_from_post(array $availableSourceOutputs): array
{
    $rawAssignments = $_POST['source_output_keys_by_function'] ?? [];
    if (!is_array($rawAssignments)) {
        return [];
    }

    $normalizedAssignments = [];
    foreach ($rawAssignments as $functionName => $requestedKeys) {
        if (!is_string($functionName)) {
            continue;
        }

        $normalizedFunctionName = trim($functionName);
        if ($normalizedFunctionName === '') {
            continue;
        }

        $normalizedAssignments[$normalizedFunctionName] = is_array($requestedKeys)
            ? app_project_db_access_normalize_target_source_output_keys($requestedKeys, $availableSourceOutputs)
            : [];
    }

    return $normalizedAssignments;
}

function app_project_single_proxy_prepare_bulk_target_save_entries(
    string $projectKey,
    array $entity,
    array $methods,
    array $canonicalByFunction,
    array $requestedTargetKeysByFunction,
): array {
    $entries = [];
    $errors = app_project_proxy_bridge_errors_from_request();
    $detectedDbAccessFile = (string) ($entity['dbaccess_file'] ?? basename((string) ($entity['dbaccess_path'] ?? '')));
    $detectedDataFile = (string) ($entity['data_file'] ?? '');

    foreach ($methods as $method) {
        if (!is_array($method)) {
            continue;
        }

        $functionName = trim((string) ($method['name'] ?? ''));
        if ($functionName === '') {
            continue;
        }

        $canonicalItem = $canonicalByFunction[$functionName] ?? null;
        $requestedTargetKeys = array_values($requestedTargetKeysByFunction[$functionName] ?? []);
        if ($canonicalItem === null && $requestedTargetKeys === []) {
            continue;
        }

        $upsertInput = null;
        if ($canonicalItem === null) {
            $formInput = app_project_db_access_function_form_from_preview(
                $entity,
                $method,
                app_project_db_access_guess_function_profile($functionName),
            );
            $validation = app_validate_db_access_function_form($formInput);
            if ($validation['errors'] !== []) {
                foreach ($validation['errors'] as $error) {
                    $errors[] = $functionName . ': ' . $error;
                }
                continue;
            }

            $upsertInput = array_merge($validation['input'], [
                'project_key' => $projectKey,
                'last_detected_dbaccess_file' => $detectedDbAccessFile,
                'last_detected_data_file' => $detectedDataFile,
            ]);
        }

        $entries[] = [
            'function_name' => $functionName,
            'upsert_input' => $upsertInput,
            'target_keys' => $requestedTargetKeys,
        ];
    }

    return [
        'ok' => $errors === [],
        'entries' => $entries,
        'errors' => $errors,
    ];
}

function app_project_single_proxy_save_bulk_target_assignments(
    array $app,
    string $projectKey,
    array $entity,
    array $methods,
    array $canonicalByFunction,
    array $requestedTargetKeysByFunction,
): array {
    $prepared = app_project_single_proxy_prepare_bulk_target_save_entries(
        $projectKey,
        $entity,
        $methods,
        $canonicalByFunction,
        $requestedTargetKeysByFunction,
    );
    if (!$prepared['ok']) {
        return [
            'ok' => false,
            'errors' => $prepared['errors'],
        ];
    }

    foreach ($prepared['entries'] as $entry) {
        if (is_array($entry['upsert_input'])) {
            $updateResult = app_upsert_db_access_function_metadata($app, $entry['upsert_input']);
            if (!$updateResult['ok']) {
                return [
                    'ok' => false,
                    'errors' => [$entry['function_name'] . ': ' . $updateResult['error']],
                ];
            }
        }

        $replaceTargetsResult = app_replace_db_access_function_source_output_target_keys(
            $app,
            $projectKey,
            (string) ($entity['source_name'] ?? ''),
            $entry['function_name'],
            $entry['target_keys'],
        );
        if (!$replaceTargetsResult['ok']) {
            return [
                'ok' => false,
                'errors' => [$entry['function_name'] . ': ' . $replaceTargetsResult['error']],
            ];
        }
    }

    return [
        'ok' => true,
        'errors' => [],
    ];
}

function app_render_project_single_proxy_page(array $app, array $request): void
{
    $bootstrap = app_project_db_access_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $requestedDbAccessKey = trim(app_query_param('db_access_key'));
    $errors = app_project_proxy_bridge_errors_from_request();
    $notices = [];
    $submittedDbAccessKey = '';
    $submittedTargetKeysByFunction = [];

    if (app_request_method_is($request, 'POST')) {
        $submittedDbAccessKey = trim(app_post_param('db_access_key', $requestedDbAccessKey));
        if ($submittedDbAccessKey !== '') {
            $requestedDbAccessKey = $submittedDbAccessKey;
        }
    }
    if (app_query_param('updated') === '1') {
        $notices[] = 'single proxy target assignments を更新しました。';
    }

    $sourceOutputCatalogResult = app_fetch_project_source_output_catalog($app, $projectKey);
    $sourceOutputCatalog = $sourceOutputCatalogResult['ok'] ? $sourceOutputCatalogResult['items'] : [];
    if (!$sourceOutputCatalogResult['ok']) {
        $errors[] = $sourceOutputCatalogResult['error'];
    }

    $singleProxySourceOutputs = array_values(array_filter(
        $sourceOutputCatalog,
        static fn (array $sourceOutput): bool => app_source_output_supports_single_function_proxy_targets($sourceOutput),
    ));
    usort(
        $singleProxySourceOutputs,
        static fn (array $left, array $right): int => strcmp(
            (string) ($left['source_output_key'] ?? ''),
            (string) ($right['source_output_key'] ?? ''),
        ),
    );

    $targetsByFunction = [];
    foreach ($singleProxySourceOutputs as $sourceOutput) {
        $sourceOutputKey = app_normalize_source_output_key((string) ($sourceOutput['source_output_key'] ?? ''));
        if ($sourceOutputKey === '') {
            continue;
        }

        $targetCatalogResult = app_fetch_source_output_db_access_function_target_catalog(
            $app,
            $projectKey,
            $sourceOutputKey,
        );
        if (!$targetCatalogResult['ok']) {
            $errors[] = $targetCatalogResult['error'];
            continue;
        }

        foreach ($targetCatalogResult['items'] as $targetItem) {
            $sourceName = trim((string) ($targetItem['source_name'] ?? ''));
            $functionName = trim((string) ($targetItem['function_name'] ?? ''));
            if ($sourceName === '' || $functionName === '') {
                continue;
            }

            $functionRef = $sourceName . '|' . $functionName;
            if (!isset($targetsByFunction[$functionRef])) {
                $targetsByFunction[$functionRef] = [];
            }
            $targetsByFunction[$functionRef][$sourceOutputKey] = $sourceOutputKey;
        }
    }

    $candidateCatalogResult = app_project_db_access_bootstrap_candidate_catalog($app, $projectKey);
    $entities = $candidateCatalogResult['ok'] ? $candidateCatalogResult['items'] : [];
    $entityBySourceName = [];
    if (!$candidateCatalogResult['ok']) {
        $errors[] = $candidateCatalogResult['error'];
    }
    foreach ($entities as $entity) {
        if (!is_array($entity)) {
            continue;
        }

        $sourceName = trim((string) ($entity['source_name'] ?? ''));
        if ($sourceName !== '') {
            $entityBySourceName[$sourceName] = $entity;
        }
    }

    if (app_request_method_is($request, 'POST')) {
        $submittedTargetKeysByFunction = app_project_single_proxy_bulk_target_assignments_from_post(
            $singleProxySourceOutputs,
        );
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif ($submittedDbAccessKey === '') {
            $errors[] = '更新対象の db access key が必要です。';
        } elseif (!isset($entityBySourceName[$submittedDbAccessKey])) {
            $errors[] = '更新対象の db access key は current candidate catalog 上で見つかりませんでした。';
        } elseif ($singleProxySourceOutputs === []) {
            $errors[] = 'single-function proxy target に使える source output がまだありません。';
        } else {
            $entity = $entityBySourceName[$submittedDbAccessKey];
            $methods = array_values(array_filter(
                $entity['method_catalog'] ?? [],
                static fn (mixed $method): bool => is_array($method),
            ));
            $canonicalCatalogResult = app_fetch_db_access_function_metadata_catalog(
                $app,
                $projectKey,
                $submittedDbAccessKey,
            );
            $canonicalByFunction = [];
            if ($canonicalCatalogResult['ok']) {
                foreach ($canonicalCatalogResult['items'] as $item) {
                    $canonicalByFunction[(string) ($item['function_name'] ?? '')] = $item;
                }
            } else {
                $errors[] = $canonicalCatalogResult['error'];
            }

            if ($errors === []) {
                $saveResult = app_project_single_proxy_save_bulk_target_assignments(
                    $app,
                    $projectKey,
                    $entity,
                    $methods,
                    $canonicalByFunction,
                    $submittedTargetKeysByFunction,
                );
                if ($saveResult['ok']) {
                    app_send_redirect_response(
                        $request,
                        app_project_single_proxy_path($projectKey, [
                            'db_access_key' => $submittedDbAccessKey,
                            'updated' => '1',
                        ]),
                    );
                    return;
                }

                $errors = array_merge($errors, $saveResult['errors']);
            }
        }
    }

    $dbAccessRows = [];
    $dbAccessRowByKey = [];
    $totalFunctionCount = 0;
    $totalCanonicalFunctionCount = 0;
    $totalTargetedFunctionCount = 0;
    $totalInvalidAuthCount = 0;

    foreach ($entities as $entity) {
        $sourceName = (string) $entity['source_name'];
        $methods = array_values(array_filter(
            $entity['method_catalog'] ?? [],
            static fn (mixed $method): bool => is_array($method),
        ));

        $canonicalCatalogResult = app_fetch_db_access_function_metadata_catalog($app, $projectKey, $sourceName);
        $canonicalByFunction = [];
        if ($canonicalCatalogResult['ok']) {
            foreach ($canonicalCatalogResult['items'] as $item) {
                $canonicalByFunction[(string) ($item['function_name'] ?? '')] = $item;
            }
        } else {
            $errors[] = $canonicalCatalogResult['error'];
        }

        usort(
            $methods,
            static function (array $left, array $right) use ($canonicalByFunction): int {
                $leftOrder = app_project_single_proxy_function_effective_order(
                    $left,
                    $canonicalByFunction[$left['name']] ?? null,
                );
                $rightOrder = app_project_single_proxy_function_effective_order(
                    $right,
                    $canonicalByFunction[$right['name']] ?? null,
                );

                if ($leftOrder !== $rightOrder) {
                    return $leftOrder <=> $rightOrder;
                }

                if ((int) ($left['line'] ?? 0) !== (int) ($right['line'] ?? 0)) {
                    return (int) ($left['line'] ?? 0) <=> (int) ($right['line'] ?? 0);
                }

                return strcmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
            },
        );

        $functionRows = [];
        $canonicalFunctionCount = 0;
        $targetedFunctionCount = 0;
        $invalidAuthCount = 0;

        foreach ($methods as $method) {
            $functionName = (string) ($method['name'] ?? '');
            if ($functionName === '') {
                continue;
            }

            $canonicalItem = $canonicalByFunction[$functionName] ?? null;
            if ($canonicalItem !== null) {
                $canonicalFunctionCount++;
            }

            $functionProfile = app_project_db_access_guess_function_profile($functionName);
            $authPolicy = app_resolve_db_access_single_proxy_auth_policy(
                $canonicalItem !== null ? (string) ($canonicalItem['single_proxy_auth_type'] ?? '') : '',
                $canonicalItem !== null ? (string) ($canonicalItem['single_proxy_single_get_function_name'] ?? '') : '',
            );
            if (!$authPolicy['is_valid']) {
                $invalidAuthCount++;
            }

            $functionRef = $sourceName . '|' . $functionName;
            $targetKeys = $submittedDbAccessKey !== '' && $submittedDbAccessKey === $sourceName
                ? array_values($submittedTargetKeysByFunction[$functionName] ?? [])
                : array_values($targetsByFunction[$functionRef] ?? []);
            sort($targetKeys, SORT_NATURAL);
            if ($targetKeys !== []) {
                $targetedFunctionCount++;
            }

            $functionRows[] = [
                'function_name' => $functionName,
                'signature' => (string) ($method['signature'] ?? ''),
                'line' => (int) ($method['line'] ?? 0),
                'effective_order' => app_project_single_proxy_function_effective_order($method, $canonicalItem),
                'action_type' => $canonicalItem !== null && (string) ($canonicalItem['action_type'] ?? '') !== ''
                    ? (string) $canonicalItem['action_type']
                    : $functionProfile['legacy_action_type'],
                'http_method' => $functionProfile['http_method'],
                'auth_policy' => $authPolicy,
                'target_keys' => $targetKeys,
                'canonical_item' => $canonicalItem,
                'detail_path' => '/projects/' . rawurlencode($projectKey)
                    . '/db-access/' . rawurlencode($sourceName)
                    . '/functions/' . rawurlencode($functionName),
                'endpoint_path' => '/projects/' . rawurlencode($projectKey)
                    . '/db-access/' . rawurlencode($sourceName)
                    . '/functions/' . rawurlencode($functionName)
                    . '/endpoint',
                'endpoint_test_path' => $targetKeys !== []
                    ? app_lab_endpoint_test_path($projectKey, [
                        'source_output_key' => $targetKeys[0],
                        'db_access_key' => $sourceName,
                        'function_key' => $functionName,
                    ])
                    : app_lab_endpoint_test_path($projectKey, [
                        'db_access_key' => $sourceName,
                        'function_key' => $functionName,
                    ]),
            ];
        }

        $row = [
            'source_name' => $sourceName,
            'dbaccess_file' => (string) ($entity['dbaccess_file'] ?? basename((string) $entity['dbaccess_path'])),
            'function_count' => count($functionRows),
            'canonical_function_count' => $canonicalFunctionCount,
            'targeted_function_count' => $targetedFunctionCount,
            'invalid_auth_count' => $invalidAuthCount,
            'function_rows' => $functionRows,
        ];
        $dbAccessRows[] = $row;
        $dbAccessRowByKey[$sourceName] = $row;

        $totalFunctionCount += count($functionRows);
        $totalCanonicalFunctionCount += $canonicalFunctionCount;
        $totalTargetedFunctionCount += $targetedFunctionCount;
        $totalInvalidAuthCount += $invalidAuthCount;
    }

    $selectedDbAccessKey = $requestedDbAccessKey;
    if ($selectedDbAccessKey !== '' && !isset($dbAccessRowByKey[$selectedDbAccessKey])) {
        $notices[] = '指定された db access key は current generated catalog 上で見つかりませんでした。';
        $selectedDbAccessKey = '';
    }
    if ($selectedDbAccessKey === '' && $dbAccessRows !== []) {
        $selectedDbAccessKey = (string) ($dbAccessRows[0]['source_name'] ?? '');
    }
    $selectedDbAccessRow = $selectedDbAccessKey !== '' ? ($dbAccessRowByKey[$selectedDbAccessKey] ?? null) : null;

    $statusCode = $errors === [] ? 200 : 422;
    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Single Proxy</title>
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
        .error-list, .notice-list {
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-top: 1rem;
        }
        .error-list {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .notice-list {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
        }
        .muted {
            color: #475569;
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
        .tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .tag {
            display: inline-block;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            background: #e2e8f0;
            color: #0f172a;
            font-size: 0.875rem;
        }
        .tag-selected {
            background: #0f172a;
            color: #ffffff;
        }
        .proxy-target-list {
            display: grid;
            gap: 0.5rem;
        }
        .target-option {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }
        .section-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            margin-top: 1rem;
        }
        button {
            border: 1px solid #0f172a;
            border-radius: 8px;
            background: #0f172a;
            color: #ffffff;
            padding: 0.6rem 1rem;
            cursor: pointer;
        }
        .stack > * + * {
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / proxy/single</p>

    <h1><?php echo app_h($project['name']); ?> Single Proxy</h1>
    <p>旧 <code>da_edit_proxy_single_target.php</code> / <code>da_funcs_edit_proxy_single_target.php</code> / <code>da_funcs_edit_proxy_single_setting*.php</code> の current ナビゲータです。function ごとの canonical metadata は <code>function detail</code> を正として編集し、この画面では project 単位の coverage と target/auth 状況を確認します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Coverage</h2>
            <ul>
                <li>db access classes: <code><?php echo app_h((string) count($dbAccessRows)); ?></code></li>
                <li>functions: <code><?php echo app_h((string) $totalFunctionCount); ?></code></li>
                <li>canonical rows: <code><?php echo app_h((string) $totalCanonicalFunctionCount); ?></code></li>
                <li>targeted functions: <code><?php echo app_h((string) $totalTargetedFunctionCount); ?></code></li>
            </ul>
        </section>

        <section class="<?php echo $totalInvalidAuthCount === 0 ? 'note-card' : 'warning-card'; ?>">
            <h2>Auth Status</h2>
            <ul>
                <li>invalid/incomplete auth rows: <code><?php echo app_h((string) $totalInvalidAuthCount); ?></code></li>
                <li>single-proxy target outputs: <code><?php echo app_h((string) count($singleProxySourceOutputs)); ?></code></li>
                <li>selected db access: <code><?php echo app_h($selectedDbAccessKey !== '' ? $selectedDbAccessKey : '(none)'); ?></code></li>
            </ul>
            <p class="muted">target assignment はこの current route で bulk save できます。auth policy の編集は引き続き <code>function detail</code> を正とします。</p>
        </section>

        <section class="summary-card">
            <h2>Target Outputs</h2>
            <?php if ($singleProxySourceOutputs === []): ?>
                <p class="muted">single-function proxy target にできる source output はまだありません。</p>
            <?php else: ?>
                <div class="tag-list">
                    <?php foreach ($singleProxySourceOutputs as $sourceOutput): ?>
                        <span class="tag">
                            <code><?php echo app_h((string) $sourceOutput['source_output_key']); ?></code>
                            / <?php echo app_h(app_source_output_release_target_type_caption((string) ($sourceOutput['release_target_type'] ?? ''))); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
        <h2>DB Access Classes</h2>
        <?php if ($dbAccessRows === []): ?>
            <p class="muted">generated db access class はまだありません。</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>db access</th>
                    <th>file</th>
                    <th>functions</th>
                    <th>targeted</th>
                    <th>auth</th>
                    <th>open</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($dbAccessRows as $row): ?>
                    <tr>
                        <td><code><?php echo app_h($row['source_name']); ?></code></td>
                        <td><code><?php echo app_h($row['dbaccess_file']); ?></code></td>
                        <td>
                            <code><?php echo app_h((string) $row['function_count']); ?></code><br>
                            <span class="muted">canonical: <?php echo app_h((string) $row['canonical_function_count']); ?></span>
                        </td>
                        <td><code><?php echo app_h((string) $row['targeted_function_count']); ?></code></td>
                        <td>
                            <code><?php echo app_h((string) $row['invalid_auth_count']); ?></code><br>
                            <span class="muted">invalid/incomplete</span>
                        </td>
                        <td>
                            <a href="<?php echo app_h(app_project_single_proxy_path($projectKey, ['db_access_key' => $row['source_name']])); ?>">proxy/single</a><br>
                            <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode($row['source_name']); ?>/functions">functions</a>
                            <?php if ($selectedDbAccessKey === $row['source_name']): ?>
                                <br><span class="muted">selected</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="stack">
        <h2>Function Coverage</h2>
        <?php if ($selectedDbAccessRow === null): ?>
            <p class="muted">表示できる db access class がありません。</p>
        <?php else: ?>
            <p><code><?php echo app_h($selectedDbAccessRow['source_name']); ?></code> の single proxy 対象状況です。</p>
            <p class="muted">bulk target save は function ごとの canonical row を必要に応じて current preview default で upsert してから、target source output assignment を置き換えます。</p>
            <?php if ($selectedDbAccessRow['function_rows'] === []): ?>
                <p class="muted">function candidate はまだ見つかっていません。</p>
            <?php elseif ($singleProxySourceOutputs === []): ?>
                <p class="muted">single-function proxy target に使える source output がないため、この db access class では bulk save を表示しません。</p>
            <?php else: ?>
                <form method="post" action="<?php echo app_h(app_project_single_proxy_path($projectKey, ['db_access_key' => $selectedDbAccessRow['source_name']])); ?>">
                    <input type="hidden" name="_csrf" value="<?php echo app_h(app_csrf_token()); ?>">
                    <input type="hidden" name="db_access_key" value="<?php echo app_h($selectedDbAccessRow['source_name']); ?>">
                <table>
                    <thead>
                    <tr>
                        <th>order</th>
                        <th>function</th>
                        <th>action</th>
                        <th>auth</th>
                        <th>targets</th>
                        <th>open</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($selectedDbAccessRow['function_rows'] as $functionRow): ?>
                        <tr>
                            <td><code><?php echo app_h((string) $functionRow['effective_order']); ?></code><br><span class="muted">line <?php echo app_h((string) $functionRow['line']); ?></span></td>
                            <td>
                                <code><?php echo app_h($functionRow['function_name']); ?></code><br>
                                <span class="muted"><?php echo app_h($functionRow['signature']); ?></span>
                            </td>
                            <td>
                                <code><?php echo app_h($functionRow['action_type']); ?></code><br>
                                <span class="muted"><?php echo app_h($functionRow['http_method']); ?></span>
                            </td>
                            <td>
                                <code><?php echo app_h((string) $functionRow['auth_policy']['resolved_auth_type']); ?></code><br>
                                <span class="muted"><?php echo app_h((string) $functionRow['auth_policy']['strategy_caption']); ?></span><br>
                                <?php if ((string) $functionRow['auth_policy']['single_get_function_name'] !== ''): ?>
                                    <span class="muted">get: <?php echo app_h((string) $functionRow['auth_policy']['single_get_function_name']); ?></span><br>
                                <?php endif; ?>
                                <span class="muted"><?php echo app_h((string) $functionRow['auth_policy']['is_valid'] ? 'resolved' : 'incomplete'); ?></span>
                            </td>
                            <td>
                                <div class="proxy-target-list">
                                    <?php foreach ($singleProxySourceOutputs as $sourceOutput): ?>
                                        <?php $sourceOutputKey = app_normalize_source_output_key((string) ($sourceOutput['source_output_key'] ?? '')); ?>
                                        <?php if ($sourceOutputKey === '') { continue; } ?>
                                        <label class="target-option">
                                            <input
                                                type="checkbox"
                                                name="source_output_keys_by_function[<?php echo app_h($functionRow['function_name']); ?>][]"
                                                value="<?php echo app_h($sourceOutputKey); ?>"
                                                <?php echo in_array($sourceOutputKey, $functionRow['target_keys'], true) ? ' checked' : ''; ?>
                                            >
                                            <span>
                                                <code><?php echo app_h($sourceOutputKey); ?></code><br>
                                                <span class="muted"><?php echo app_h(app_source_output_release_target_type_caption((string) ($sourceOutput['release_target_type'] ?? ''))); ?></span>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td>
                                <a href="<?php echo app_h($functionRow['detail_path']); ?>">detail</a><br>
                                <a href="<?php echo app_h($functionRow['endpoint_path']); ?>">endpoint draft</a><br>
                                <a href="<?php echo app_h($functionRow['endpoint_test_path']); ?>">endpoint test</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="section-actions">
                    <button type="submit">Save Bulk Targets</button>
                    <span class="muted">legacy <code>da_funcs_edit_proxy_single_target.php</code> POST bridge もこの保存フローへ着地します。</span>
                </div>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
    <?php
}
