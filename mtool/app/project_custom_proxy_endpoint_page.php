<?php

declare(strict_types=1);

require_once __DIR__ . '/custom_proxy_build_plan_service.php';
require_once __DIR__ . '/endpoint_test_job_service.php';
require_once __DIR__ . '/project_custom_proxy_route_common.php';
require_once __DIR__ . '/project_output_proxy_generator.php';
require_once __DIR__ . '/project_proxy_route_common.php';

function app_project_custom_proxy_endpoint_empty_object(): object
{
    return (object) [];
}

function app_project_custom_proxy_endpoint_supports_server_preview(array $sourceOutput): bool
{
    return app_source_output_supports_custom_proxy_targets($sourceOutput)
        && (string) ($sourceOutput['artifact_strategy'] ?? '') === 'custom-proxy-server';
}

function app_project_custom_proxy_endpoint_request_shape_for_step(
    array $step,
    array $sourceEntities,
) {
    $payload = [];

    if ((string) ($step['input_kind'] ?? '') === 'object') {
        $paramName = trim((string) ($step['object_param_name'] ?? ''));
        $sourceName = trim((string) ($step['source_name'] ?? ''));
        $properties = is_array($sourceEntities[$sourceName]['data_properties'] ?? null)
            ? $sourceEntities[$sourceName]['data_properties']
            : [];

        $objectPayload = [];
        foreach ($properties as $property) {
            if (!is_string($property) || trim($property) === '') {
                continue;
            }
            $objectPayload[trim($property)] = null;
        }

        if ($paramName !== '') {
            $payload[$paramName] = $objectPayload === []
                ? app_project_custom_proxy_endpoint_empty_object()
                : $objectPayload;
        }
    } else {
        foreach ((array) ($step['parameter_names'] ?? []) as $parameterName) {
            if (!is_string($parameterName) || trim($parameterName) === '') {
                continue;
            }
            $payload[trim($parameterName)] = null;
        }
    }

    $normalizedPayload = $payload === []
        ? app_project_custom_proxy_endpoint_empty_object()
        : $payload;

    if (!empty($step['is_list'])) {
        return [$normalizedPayload];
    }

    return $normalizedPayload;
}

function app_project_custom_proxy_endpoint_request_skeleton(
    array $previewItem,
    array $sourceEntities,
): array {
    $payload = [];
    $strategy = (string) ($previewItem['auth_policy']['strategy_key'] ?? '');

    if (in_array($strategy, ['project-token', 'project-token-or-get-function'], true)) {
        $payload['TOKEN'] = '';
    }
    if ($strategy === 'login-cookie-token') {
        $payload['LOGIN_COOKIE_TOKEN'] = '';
    }

    foreach ($previewItem['steps'] as $step) {
        $requestKey = trim((string) ($step['request_key'] ?? ''));
        if ($requestKey === '') {
            continue;
        }

        $payload[$requestKey] = app_project_custom_proxy_endpoint_request_shape_for_step(
            $step,
            $sourceEntities,
        );
    }

    return $payload;
}

function app_project_custom_proxy_endpoint_response_shape_rows(array $previewItem): array
{
    $rows = [
        [
            'field' => '_status',
            'type' => 'string',
            'note' => 'runtime status。成功時は `OK`、失敗時は `NG`。',
        ],
        [
            'field' => 'Message',
            'type' => 'string',
            'note' => 'proxy 実行結果のメッセージ。',
        ],
    ];

    foreach ($previewItem['steps'] as $step) {
        $responseKey = trim((string) ($step['response_key'] ?? ''));
        if ($responseKey === '') {
            continue;
        }

        $responseMode = (string) ($step['response_mode'] ?? '');
        $resultDataType = trim((string) ($step['result_data_type'] ?? ''));
        $type = 'mixed';
        $note = 'step response';

        if ($responseMode === 'insert-id-single') {
            $type = 'int|null';
            $note = 'insert 成功時の insert id。';
        } elseif ($responseMode === 'insert-id-list') {
            $type = 'list<int|null>';
            $note = 'list step の各 insert id。';
        } elseif ($responseMode === 'step-result-single') {
            $type = '{Result: ' . ($resultDataType !== '' ? $resultDataType : 'mixed') . '}';
            $note = 'single step result を wrapper object で返す。';
        } elseif ($responseMode === 'step-result-list') {
            $type = 'list<{Result: ' . ($resultDataType !== '' ? $resultDataType : 'mixed') . '}>';
            $note = 'list step result を配列で返す。';
        } elseif ($responseMode === 'direct-result') {
            $type = $resultDataType !== '' ? $resultDataType : 'mixed';
            $note = 'direct result。';
        }

        $rows[] = [
            'field' => $responseKey,
            'type' => $type,
            'note' => $note,
        ];
    }

    return $rows;
}

function app_project_custom_proxy_endpoint_test_query(
    array $sourceOutput,
    array $previewItem,
    string $requestJson,
): array {
    $query = [];
    $baseUrl = trim((string) ($sourceOutput['proxy_base_url'] ?? ''));
    if ($baseUrl !== '') {
        $query['base_url'] = $baseUrl;
    }

    $endpointFilename = trim((string) ($previewItem['endpoint_filename'] ?? ''));
    if ($endpointFilename !== '') {
        $query['endpoint_filename'] = $endpointFilename;
    }

    if ($requestJson !== '' && strlen($requestJson) <= 1800) {
        $query['request_json'] = $requestJson;
    }

    return $query;
}

function app_render_project_custom_proxy_endpoint_page(array $app, array $request): void
{
    $bootstrap = app_project_custom_proxy_item_route_bootstrap($app, $request, ['GET']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $customProxyKey = $bootstrap['custom_proxy_key'];
    $customProxy = $bootstrap['custom_proxy'];
    $sourceOutputCatalog = $bootstrap['source_output_catalog'];
    $errors = [];
    $notices = [];

    $targetKeysResult = app_fetch_project_custom_proxy_target_keys($app, $projectKey, $customProxyKey);
    $selectedTargetKeys = $targetKeysResult['ok'] ? $targetKeysResult['items'] : [];
    if (!$targetKeysResult['ok']) {
        $errors[] = $targetKeysResult['error'];
    }

    $targetSourceOutputs = [];
    $targetSourceOutputByKey = [];
    foreach ($sourceOutputCatalog as $sourceOutput) {
        if (
            !is_array($sourceOutput)
            || !app_project_custom_proxy_endpoint_supports_server_preview($sourceOutput)
        ) {
            continue;
        }

        $sourceOutputKey = app_normalize_source_output_key((string) ($sourceOutput['source_output_key'] ?? ''));
        if ($sourceOutputKey === '' || !in_array($sourceOutputKey, $selectedTargetKeys, true)) {
            continue;
        }

        $targetSourceOutputs[] = $sourceOutput;
        $targetSourceOutputByKey[$sourceOutputKey] = $sourceOutput;
    }
    usort(
        $targetSourceOutputs,
        static fn (array $left, array $right): int => strcmp(
            (string) ($left['source_output_key'] ?? ''),
            (string) ($right['source_output_key'] ?? ''),
        ),
    );

    $selectedSourceOutputKey = app_normalize_source_output_key(app_query_param('source_output_key'));
    if ($selectedSourceOutputKey !== '' && !isset($targetSourceOutputByKey[$selectedSourceOutputKey])) {
        $notices[] = '指定された source output はこの endpoint preview では利用できません。server target の先頭を表示します。';
        $selectedSourceOutputKey = '';
    }
    if ($selectedSourceOutputKey === '' && $targetSourceOutputs !== []) {
        $selectedSourceOutputKey = app_normalize_source_output_key((string) ($targetSourceOutputs[0]['source_output_key'] ?? ''));
    }

    $selectedSourceOutput = $selectedSourceOutputKey !== ''
        ? ($targetSourceOutputByKey[$selectedSourceOutputKey] ?? null)
        : null;
    $authPolicy = app_resolve_custom_proxy_auth_policy(
        (string) ($customProxy['auth_type'] ?? ''),
        (string) ($customProxy['single_get_function_name'] ?? ''),
    );
    $selectedPlanItem = null;
    $previewItem = null;
    $sourceEntities = [];
    $resolvedEndpoint = [
        'ok' => false,
        'endpoint_url' => '',
        'error' => 'source output が未選択です。',
    ];
    $requestJson = '';
    $requestJsonInLink = true;
    $responseShapeRows = [];

    if ($selectedSourceOutput !== null) {
        $planResult = app_custom_proxy_build_plan_for_source_output(
            $app,
            $projectKey,
            (string) $selectedSourceOutput['source_output_key'],
        );
        if (!$planResult['ok'] || $planResult['plan'] === null) {
            $errors[] = $planResult['error'] !== '' ? $planResult['error'] : 'custom proxy build plan を取得できませんでした。';
        } else {
            foreach ($planResult['plan']['items'] as $planItem) {
                if ((string) ($planItem['custom_proxy_key'] ?? '') === $customProxyKey) {
                    $selectedPlanItem = $planItem;
                    break;
                }
            }

            if ($selectedPlanItem === null) {
                $notices[] = '選択中の source output はこの custom proxy を target にしていません。';
            } else {
                $authPolicy = is_array($selectedPlanItem['auth_policy'] ?? null)
                    ? $selectedPlanItem['auth_policy']
                    : $authPolicy;
                $requiredSourceNames = [];
                foreach ($selectedPlanItem['steps'] as $step) {
                    $sourceName = trim((string) ($step['db_access_source_name'] ?? ''));
                    if ($sourceName !== '') {
                        $requiredSourceNames[$sourceName] = $sourceName;
                    }
                }

                foreach ($requiredSourceNames as $sourceName) {
                    $entityResult = app_project_output_proxy_load_source_entity($app, $sourceName);
                    if (!$entityResult['ok'] || $entityResult['entity'] === null) {
                        $errors[] = $entityResult['error'];
                        continue;
                    }

                    $sourceEntities[$sourceName] = $entityResult['entity'];
                }

                if (count($sourceEntities) === count($requiredSourceNames)) {
                    $clientPrefix = app_project_output_proxy_client_prefix(
                        app_project_output_proxy_common_basename($planResult['plan']['items']),
                    );
                    $previewItem = app_project_output_proxy_enrich_item(
                        $selectedPlanItem,
                        $sourceEntities,
                        $clientPrefix,
                    );
                    $requestPayload = app_project_custom_proxy_endpoint_request_skeleton(
                        $previewItem,
                        $sourceEntities,
                    );
                    $requestJson = (string) json_encode(
                        $requestPayload,
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
                    );
                    if ($requestJson === '') {
                        $requestJson = "{}\n";
                    }
                    $requestJsonInLink = strlen($requestJson) <= 1800;
                    $responseShapeRows = app_project_custom_proxy_endpoint_response_shape_rows($previewItem);
                    $resolvedEndpoint = app_endpoint_test_resolve_endpoint_url(
                        '',
                        (string) ($selectedSourceOutput['proxy_base_url'] ?? ''),
                        (string) ($previewItem['endpoint_filename'] ?? ''),
                    );
                }
            }
        }
    }

    $statusCode = $errors === [] ? 200 : 422;
    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Custom Proxy Endpoint</title>
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
            padding: 1rem;
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
        .stack > * + * {
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="<?php echo app_h(app_project_custom_proxies_path($projectKey)); ?>">proxy/custom</a> / <a href="<?php echo app_h(app_project_custom_proxy_detail_path($projectKey, $customProxyKey)); ?>"><code><?php echo app_h($customProxyKey); ?></code></a> / endpoint</p>

    <h1><?php echo app_h($project['name']); ?> Custom Proxy Endpoint</h1>
    <p>旧 <code>da_proxy_custom_endpoint.php</code> の current preview です。legacy と同じく custom proxy server target を前提に、endpoint filename、auth policy、step ごとの request/response shape を確認できます。実行は current <code>/runs/endpoints/{project_key}</code> に manual URL として handoff します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Proxy</h2>
            <ul>
                <li>key: <code><?php echo app_h($customProxyKey); ?></code></li>
                <li>display: <code><?php echo app_h(app_custom_proxy_display_name((string) ($customProxy['basename'] ?? ''), (string) ($customProxy['name'] ?? ''))); ?></code></li>
                <li>target outputs: <code><?php echo app_h((string) count($targetSourceOutputs)); ?></code></li>
                <li>selected output: <code><?php echo app_h($selectedSourceOutputKey !== '' ? $selectedSourceOutputKey : '(none)'); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Endpoint</h2>
            <ul>
                <li>filename: <code><?php echo app_h($previewItem !== null ? (string) $previewItem['endpoint_filename'] : '(unresolved)'); ?></code></li>
                <li>base URL: <code><?php echo app_h($selectedSourceOutput !== null && (string) ($selectedSourceOutput['proxy_base_url'] ?? '') !== '' ? (string) $selectedSourceOutput['proxy_base_url'] : '(blank)'); ?></code></li>
                <li>resolved: <code><?php echo app_h($resolvedEndpoint['ok'] ? $resolvedEndpoint['endpoint_url'] : '(manual)'); ?></code></li>
                <li>status: <code><?php echo app_h($resolvedEndpoint['ok'] ? 'resolved' : 'manual'); ?></code></li>
            </ul>
            <?php if (!$resolvedEndpoint['ok'] && $resolvedEndpoint['error'] !== ''): ?>
                <p class="muted"><?php echo app_h($resolvedEndpoint['error']); ?></p>
            <?php endif; ?>
        </section>

        <section class="<?php echo $authPolicy['is_valid'] ? 'note-card' : 'warning-card'; ?>">
            <h2>Auth Policy</h2>
            <ul>
                <li>resolved auth type: <code><?php echo app_h((string) $authPolicy['resolved_auth_type']); ?></code></li>
                <li>strategy: <code><?php echo app_h((string) $authPolicy['strategy_caption']); ?></code></li>
                <li>single get function: <code><?php echo app_h((string) ($authPolicy['single_get_function_name'] !== '' ? $authPolicy['single_get_function_name'] : '(blank)')); ?></code></li>
                <li>status: <code><?php echo app_h($authPolicy['is_valid'] ? 'resolved' : 'incomplete'); ?></code></li>
            </ul>
            <p class="muted"><?php echo app_h((string) $authPolicy['summary']); ?></p>
        </section>

        <section class="<?php echo $selectedPlanItem !== null && (int) ($selectedPlanItem['unresolved_step_count'] ?? 0) === 0 ? 'note-card' : 'warning-card'; ?>">
            <h2>Build Plan</h2>
            <ul>
                <li>steps: <code><?php echo app_h($selectedPlanItem !== null ? (string) ($selectedPlanItem['step_count'] ?? 0) : '0'); ?></code></li>
                <li>unresolved steps: <code><?php echo app_h($selectedPlanItem !== null ? (string) ($selectedPlanItem['unresolved_step_count'] ?? 0) : '0'); ?></code></li>
                <li>in transaction: <code><?php echo app_h((string) (($selectedPlanItem['in_transaction'] ?? false) ? 'yes' : 'no')); ?></code></li>
                <li>continue insert errors: <code><?php echo app_h((string) (($selectedPlanItem['continue_even_if_failed_to_insert'] ?? false) ? 'yes' : 'no')); ?></code></li>
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
        <h2>Target Source Outputs (Server)</h2>
        <?php if ($targetSourceOutputs === []): ?>
            <p class="muted">この custom proxy に割り当てられた server target source output はありません。</p>
        <?php else: ?>
            <div class="tag-list">
                <?php foreach ($targetSourceOutputs as $targetSourceOutput): ?>
                    <?php $targetKey = (string) $targetSourceOutput['source_output_key']; ?>
                    <a class="tag<?php echo $targetKey === $selectedSourceOutputKey ? ' tag-selected' : ''; ?>" href="<?php echo app_h(app_project_custom_proxy_endpoint_path($projectKey, $customProxyKey, ['source_output_key' => $targetKey])); ?>">
                        <code><?php echo app_h($targetKey); ?></code>
                        / <?php echo app_h(app_source_output_release_target_type_caption((string) ($targetSourceOutput['release_target_type'] ?? ''))); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($selectedSourceOutput !== null): ?>
        <section class="summary-card">
            <h2>Selected Source Output</h2>
            <ul>
                <li>name: <code><?php echo app_h((string) ($selectedSourceOutput['name'] ?? '')); ?></code></li>
                <li>release target: <code><?php echo app_h(app_source_output_release_target_type_caption((string) ($selectedSourceOutput['release_target_type'] ?? ''))); ?></code></li>
                <li>binding: <code><?php echo app_h(app_source_output_target_binding_scope_caption(app_source_output_target_binding_scope($selectedSourceOutput))); ?></code></li>
                <li>detail: <a href="/projects/<?php echo rawurlencode($projectKey); ?>/source-outputs/<?php echo rawurlencode((string) $selectedSourceOutput['source_output_key']); ?>">open source output</a></li>
            </ul>
        </section>
    <?php endif; ?>

    <?php if ($previewItem !== null && $selectedSourceOutput !== null): ?>
        <section class="summary-card">
            <h2>Endpoint Test Handoff</h2>
            <p><a href="<?php echo app_h(app_lab_endpoint_test_path($projectKey, app_project_custom_proxy_endpoint_test_query($selectedSourceOutput, $previewItem, $requestJson))); ?>">manual endpoint test を開く</a></p>
            <p class="muted">current endpoint test route は manual URL 実行として使います。<?php echo $requestJsonInLink ? 'request JSON も prefill します。' : 'request JSON は URL 長を抑えるため prefill せず、この画面の preview を参照します。'; ?></p>
        </section>

        <section class="stack">
            <h2>Request JSON Skeleton</h2>
            <pre><?php echo app_h($requestJson); ?></pre>
        </section>

        <section class="stack">
            <h2>Response Shape</h2>
            <table>
                <thead>
                <tr>
                    <th>field</th>
                    <th>type</th>
                    <th>note</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($responseShapeRows as $row): ?>
                    <tr>
                        <td><code><?php echo app_h($row['field']); ?></code></td>
                        <td><code><?php echo app_h($row['type']); ?></code></td>
                        <td><?php echo app_h($row['note']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="stack">
            <h2>Steps</h2>
            <table>
                <thead>
                <tr>
                    <th>step</th>
                    <th>request</th>
                    <th>function</th>
                    <th>input</th>
                    <th>response</th>
                    <th>open</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($previewItem['steps'] as $step): ?>
                    <tr>
                        <td>
                            <code><?php echo app_h((string) $step['step_no']); ?></code><br>
                            <span class="muted">order <?php echo app_h((string) $step['step_order']); ?></span>
                        </td>
                        <td>
                            <code><?php echo app_h((string) $step['request_key']); ?></code><br>
                            <span class="muted"><?php echo app_h((bool) $step['is_list'] ? 'list' : 'single'); ?></span>
                        </td>
                        <td>
                            <code><?php echo app_h((string) $step['source_name']); ?>.<?php echo app_h((string) $step['function_name']); ?></code><br>
                            <span class="muted"><?php echo app_h((string) $step['signature']); ?></span>
                        </td>
                        <td>
                            <code><?php echo app_h((string) $step['input_kind']); ?></code><br>
                            <?php if ((string) $step['input_kind'] === 'object'): ?>
                                <span class="muted"><?php echo app_h((string) $step['object_param_name']); ?> / <?php echo app_h((string) $step['object_class']); ?></span>
                            <?php else: ?>
                                <span class="muted"><?php echo app_h(implode(', ', array_map('strval', (array) ($step['parameter_names'] ?? [])))); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code><?php echo app_h((string) $step['response_key']); ?></code><br>
                            <span class="muted"><?php echo app_h((string) $step['response_mode']); ?></span><br>
                            <?php if ((string) $step['result_data_type'] !== ''): ?>
                                <span class="muted"><?php echo app_h((string) $step['result_data_type']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode((string) $step['source_name']); ?>/functions/<?php echo rawurlencode((string) $step['function_name']); ?>">detail</a><br>
                            <a href="/projects/<?php echo rawurlencode($projectKey); ?>/db-access/<?php echo rawurlencode((string) $step['source_name']); ?>/functions/<?php echo rawurlencode((string) $step['function_name']); ?>/endpoint">endpoint draft</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
