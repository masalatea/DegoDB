<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/generated_runtime.php';
require_once __DIR__ . '/legacy_html_reference.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/project_html_source_binding_repository.php';
require_once __DIR__ . '/project_html_repository.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/source_output_repository.php';

function app_project_htmls_path(string $projectKey): string
{
    return '/projects/' . rawurlencode($projectKey) . '/html';
}

function app_project_html_detail_path(string $projectKey, string $htmlKey): string
{
    return app_project_htmls_path($projectKey) . '/' . rawurlencode($htmlKey);
}

function app_project_html_parameters_path(string $projectKey, string $htmlKey): string
{
    return app_project_html_detail_path($projectKey, $htmlKey) . '/parameters';
}

/**
 * @return list<string>
 */
function app_project_html_bridge_errors_from_request(): array
{
    $items = [];
    foreach ([($_GET['bridge_errors'] ?? null), ($_POST['bridge_errors'] ?? null)] as $rawValue) {
        if (is_array($rawValue)) {
            foreach ($rawValue as $rawItem) {
                if (!is_string($rawItem) && !is_numeric($rawItem)) {
                    continue;
                }

                $normalized = trim((string) $rawItem);
                if ($normalized === '') {
                    continue;
                }

                $items[$normalized] = $normalized;
            }
            continue;
        }

        if (!is_string($rawValue) && !is_numeric($rawValue)) {
            continue;
        }

        $normalized = trim((string) $rawValue);
        if ($normalized === '') {
            continue;
        }

        $items[$normalized] = $normalized;
    }

    return array_values($items);
}

function app_project_html_extract_legacy_source_output_pid(string $notes): int
{
    if (
        preg_match(
            '/\bProjectSourceOutput\.PID\s*=\s*(\d+)/u',
            $notes,
            $matches,
        ) !== 1
    ) {
        return 0;
    }

    return (int) ($matches[1] ?? 0);
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:array<string,array{
 *         source_output_key:string,
 *         name:string,
 *         source_output_dir:string,
 *         class_type:string,
 *         notes:string
 *     }>,
 *     error:string
 * }
 */
function app_project_html_source_output_catalog_by_legacy_pid(array $app, string $projectKey): array
{
    $catalogResult = app_fetch_project_source_output_catalog($app, $projectKey);
    if (!$catalogResult['ok']) {
        return [
            'ok' => false,
            'items' => [],
            'error' => $catalogResult['error'],
        ];
    }

    $items = [];
    $sourceOutputByKey = [];
    foreach ($catalogResult['items'] as $item) {
        $sourceOutputKey = app_normalize_source_output_key((string) ($item['source_output_key'] ?? ''));
        if ($sourceOutputKey !== '') {
            $sourceOutputByKey[$sourceOutputKey] = $item;
        }

        $legacyPid = app_project_html_extract_legacy_source_output_pid((string) ($item['notes'] ?? ''));
        if ($legacyPid <= 0) {
            continue;
        }

        $items[(string) $legacyPid] = [
            'source_output_key' => $sourceOutputKey,
            'name' => (string) ($item['name'] ?? ''),
            'source_output_dir' => (string) ($item['source_output_dir'] ?? ''),
            'class_type' => (string) ($item['class_type'] ?? ''),
            'notes' => (string) ($item['notes'] ?? ''),
        ];
    }

    $bindingResult = app_fetch_project_html_source_bindings($app, $projectKey);
    if ($bindingResult['ok']) {
        foreach ($bindingResult['items'] as $binding) {
            $legacyPid = (int) ($binding['legacy_project_source_output_pid'] ?? 0);
            if ($legacyPid <= 0) {
                continue;
            }

            $sourceOutputKey = app_normalize_source_output_key((string) ($binding['source_output_key'] ?? ''));
            if ($sourceOutputKey === '') {
                continue;
            }

            $sourceOutput = $sourceOutputByKey[$sourceOutputKey] ?? null;
            $items[(string) $legacyPid] = [
                'source_output_key' => $sourceOutputKey,
                'name' => (string) ($sourceOutput['name'] ?? ''),
                'source_output_dir' => (string) ($sourceOutput['source_output_dir'] ?? ''),
                'class_type' => (string) ($sourceOutput['class_type'] ?? ''),
                'notes' => (string) ($sourceOutput['notes'] ?? ''),
            ];
        }
    }

    ksort($items, SORT_NATURAL);

    return [
        'ok' => true,
        'items' => $items,
        'error' => '',
    ];
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 * @param list<string> $allowedMethods
 * @return array{
 *     app:array,
 *     request:array,
 *     principal:array{
 *         id:string,
 *         display_name:string,
 *         roles:list<string>
 *     },
 *     project:array{
 *         project_key:string,
 *         name:string,
 *         slug:string,
 *         lifecycle_status:string,
 *         owner_login_id:string,
 *         member_count:int,
 *         updated_at:string,
 *         description:string
 *     },
 *     project_key:string,
 *     generated_runtime:array,
 *     reference:array{
 *         project_key:string,
 *         project_pid:int,
 *         source_dump_path:string,
 *         generated_at:string,
 *         html_count:int,
 *         parameter_count:int,
 *         template_count:int,
 *         template_parameter_count:int,
 *         htmls:list<array{
 *             project_pid:int,
 *             legacy_html_pid:int,
 *             html_key:string,
 *             name:string,
 *             legacy_project_source_output_pid:int,
 *             legacy_html_template_pid:int,
 *             last_modified_dt:string
 *         }>,
 *         parameters:list<array{
 *             project_pid:int,
 *             legacy_html_pid:int,
 *             legacy_parameter_pid:int,
 *             parameter_name:string,
 *             parameter_value:string
 *         }>,
 *         templates:list<array{
 *             legacy_html_template_pid:int,
 *             target_type:string,
 *             parent_html_template_pid:int,
 *             name:string,
 *             program_language:string,
 *             file_name:string,
 *             comment:string
 *         }>,
 *         template_parameters:list<array{
 *             legacy_html_template_pid:int,
 *             legacy_template_parameter_pid:int,
 *             parameter_name:string,
 *             target_value_type:string,
 *             target_variable_or_class_object:string,
 *             target_property_of_class_object:string,
 *             another_template_pid:int,
 *             trim_last_space:int,
 *             trim_last_return:int,
 *             data_type:string
 *         }>
 *     }
 * }|null
 */
function app_project_html_route_bootstrap(array $app, array $request, array $allowedMethods = ['GET']): ?array
{
    if ($app['site'] !== 'admin') {
        app_render_forbidden_page($app, $request, 'この route は 設定変更用サイト でのみ利用します。');
        return null;
    }

    $principal = app_auth_principal();
    if ($principal === null) {
        app_send_redirect_response($request, app_auth_login_path());
        return null;
    }

    if (!app_auth_has_any_role(['admin', 'config'], $principal)) {
        app_render_forbidden_page($app, $request, 'HTML authoring の参照には admin または config role が必要です。');
        return null;
    }

    $normalizedAllowedMethods = array_values(
        array_filter(
            array_map(
                static fn (string $method): string => strtoupper(trim($method)),
                $allowedMethods,
            ),
            static fn (string $method): bool => $method !== '',
        ),
    );
    if ($normalizedAllowedMethods === []) {
        $normalizedAllowedMethods = ['GET'];
    }

    if (!in_array(strtoupper($request['method']), $normalizedAllowedMethods, true)) {
        app_render_method_not_allowed_page($app, $request, $normalizedAllowedMethods);
        return null;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return null;
    }

    $project = app_fetch_project_by_key($app, $projectKey);
    if (!$project['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project HTML</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>HTML authoring 情報の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($project['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return null;
    }

    if ($project['item'] === null) {
        app_render_not_found_page($app, $request);
        return null;
    }

    $referenceResult = app_load_legacy_html_reference($projectKey);
    if (!$referenceResult['ok'] || $referenceResult['item'] === null) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project HTML</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>legacy html reference の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($referenceResult['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return null;
    }

    return [
        'app' => $app,
        'request' => $request,
        'principal' => $principal,
        'project' => $project['item'],
        'project_key' => $projectKey,
        'generated_runtime' => app_generated_runtime_summary($app),
        'reference' => $referenceResult['item'],
    ];
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 * @param list<string> $allowedMethods
 * @return array{
 *     app:array,
 *     request:array,
 *     principal:array,
 *     project:array,
 *     project_key:string,
 *     generated_runtime:array,
 *     reference:array,
 *     html_catalog:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         html_key:string,
 *         name:string,
 *         legacy_project_source_output_pid:int,
 *         legacy_html_template_pid:int,
 *         last_modified_dt:string
 *     }>,
 *     html_key:string,
 *     html:array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         html_key:string,
 *         name:string,
 *         legacy_project_source_output_pid:int,
 *         legacy_html_template_pid:int,
 *         last_modified_dt:string
 *     }
 * }|null
 */
function app_project_html_item_route_bootstrap(
    array $app,
    array $request,
    array $allowedMethods = ['GET'],
): ?array {
    $bootstrap = app_project_html_route_bootstrap($app, $request, $allowedMethods);
    if ($bootstrap === null) {
        return null;
    }

    $htmlKey = trim(app_route_param($request, 'html_key'));
    if ($htmlKey === '') {
        app_render_bad_request_page($app, $request, 'html key が必要です。');
        return null;
    }

    $htmlCatalogResult = app_fetch_project_html_catalog(
        $app,
        $bootstrap['project_key'],
        (int) ($bootstrap['reference']['project_pid'] ?? 0),
    );
    if (!$htmlCatalogResult['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project HTML</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>live html catalog の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($bootstrap['project_key']); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($htmlCatalogResult['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return null;
    }

    $html = app_project_html_find_catalog_item_by_key($htmlCatalogResult['items'], $htmlKey);
    if ($html === null) {
        app_render_not_found_page($app, $request);
        return null;
    }

    $bootstrap['html_catalog'] = $htmlCatalogResult['items'];
    $bootstrap['html_key'] = $htmlKey;
    $bootstrap['html'] = $html;

    return $bootstrap;
}
