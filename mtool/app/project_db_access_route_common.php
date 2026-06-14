<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/generated_catalog.php';
require_once __DIR__ . '/generated_runtime.php';
require_once __DIR__ . '/project_db_access_metadata_helper.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/response.php';

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
 *     generated_catalog:array
 * }|null
 */
function app_project_db_access_route_bootstrap(array $app, array $request, array $allowedMethods = ['GET']): ?array
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
        app_render_forbidden_page($app, $request, 'db access の参照には admin または config role が必要です。');
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
    <title><?php echo app_h($app['site_name']); ?> - Project DB Access</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>db access の読み込みに失敗しました。</p>
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

    return [
        'app' => $app,
        'request' => $request,
        'principal' => $principal,
        'project' => $project['item'],
        'project_key' => $projectKey,
        'generated_runtime' => app_generated_runtime_summary($app),
        'generated_catalog' => app_generated_entity_catalog($app),
    ];
}

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
 * @param list<string> $allowedMethods
 * @return array{
 *     app:array,
 *     request:array,
 *     principal:array,
 *     project:array,
 *     project_key:string,
 *     generated_runtime:array,
 *     generated_catalog:array,
 *     db_access_key:string,
 *     function_key:string,
 *     entity:array,
 *     method_catalog:array,
 *     method:array,
 *     function_profile:array{
 *         action:string,
 *         http_method:string,
 *         endpoint_slug:string,
 *         legacy_action_type:string,
 *         function_suffix_candidate:string
 *     }
 * }|null
 */
function app_project_db_access_function_route_bootstrap(
    array $app,
    array $request,
    array $allowedMethods = ['GET'],
): ?array {
    $bootstrap = app_project_db_access_route_bootstrap($app, $request, $allowedMethods);
    if ($bootstrap === null) {
        return null;
    }

    $catalog = $bootstrap['generated_catalog'];
    $dbAccessKey = trim(app_route_param($request, 'db_access_key'));
    $functionKey = trim(app_route_param($request, 'function_key'));
    if ($dbAccessKey === '' || $functionKey === '') {
        app_render_bad_request_page($app, $request, 'db access key と function key が必要です。');
        return null;
    }

    $entity = app_generated_catalog_find_entity($catalog, $dbAccessKey);
    if ($entity === null) {
        app_render_not_found_page($app, $request);
        return null;
    }

    $methodCatalog = app_generated_file_method_catalog($entity['dbaccess_path']);
    $method = app_generated_file_find_method($methodCatalog, $functionKey);
    if ($method === null) {
        app_render_not_found_page($app, $request);
        return null;
    }

    $bootstrap['db_access_key'] = $dbAccessKey;
    $bootstrap['function_key'] = $functionKey;
    $bootstrap['entity'] = $entity;
    $bootstrap['method_catalog'] = $methodCatalog;
    $bootstrap['method'] = $method;
    $bootstrap['function_profile'] = app_project_db_access_guess_function_profile($method['name']);

    return $bootstrap;
}

/**
 * @param array{
 *     generated?:array{
 *         dbclasses_root?:string
 *     }
 * } $app
 */
function app_project_db_access_generated_file_path(array $app, string $basename): string
{
    $dbclassesRoot = $app['generated']['dbclasses_root'] ?? '';
    if (!is_string($dbclassesRoot) || $dbclassesRoot === '') {
        return '';
    }

    $path = rtrim($dbclassesRoot, '/') . '/' . ltrim($basename, '/');
    if (!is_file($path) || !is_readable($path)) {
        return '';
    }

    return $path;
}

/**
 * @param array{
 *     generated?:array{
 *         dbclasses_root?:string
 *     }
 * } $app
 * @return array{
 *     data_file:string,
 *     data_path:string,
 *     data_classes:list<string>,
 *     field_names:list<string>,
 *     dbaccess_file:string,
 *     dbaccess_path:string,
 *     dbaccess_methods:list<string>,
 *     dbaccess_method_catalog:list<array{
 *         name:string,
 *         line:int,
 *         end_line:int,
 *         signature:string
 *     }>,
 *     data_excerpt:string,
 *     dbaccess_excerpt:string
 * }
 */
function app_project_db_access_legacy_metadata_schema(array $app, string $entityName): array
{
    $dataFile = 'data-' . $entityName . '.php';
    $dbaccessFile = 'dbaccess-' . $entityName . '.php';
    $dataPath = app_project_db_access_generated_file_path($app, $dataFile);
    $dbaccessPath = app_project_db_access_generated_file_path($app, $dbaccessFile);

    return [
        'data_file' => $dataFile,
        'data_path' => $dataPath,
        'data_classes' => app_generated_file_class_names($dataPath),
        'field_names' => app_generated_file_property_names($dataPath),
        'dbaccess_file' => $dbaccessFile,
        'dbaccess_path' => $dbaccessPath,
        'dbaccess_methods' => app_generated_file_method_names($dbaccessPath),
        'dbaccess_method_catalog' => app_generated_file_method_catalog($dbaccessPath),
        'data_excerpt' => app_generated_file_excerpt($dataPath, 40),
        'dbaccess_excerpt' => app_generated_file_excerpt($dbaccessPath, 48),
    ];
}

function app_db_access_select_where_parameter_type_caption(string $value): string
{
    return match ($value) {
        'argument' => 'Argument',
        'fixed' => 'Fixed',
        'anotherfield' => 'Another Field',
        default => $value !== '' ? $value : '(blank)',
    };
}

function app_db_access_parameter_data_type_caption(string $value): string
{
    return match ($value) {
        '' => 'Default',
        'raw' => 'Raw',
        'file' => 'File',
        default => $value,
    };
}

function app_db_access_select_where_join_type_caption(string $value): string
{
    return match ($value) {
        '' => 'Where',
        'inner' => 'Inner Join',
        'left' => 'Left Outer Join',
        'right' => 'Right Outer Join',
        default => $value,
    };
}

/**
 * @param array{
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string,
 *     another_table_name:string,
 *     another_table_alias_name:string,
 *     another_field_name:string
 * } $item
 */
function app_db_access_select_where_parameter_summary(array $item): string
{
    if ($item['parameter_type'] === 'fixed') {
        return 'Fixed: ' . ($item['fixed_parameter'] !== '' ? $item['fixed_parameter'] : '(blank)')
            . ' / ' . app_db_access_parameter_data_type_caption($item['parameter_data_type']);
    }

    if ($item['parameter_type'] === 'anotherfield') {
        $anotherTarget = $item['another_table_name'];
        if ($item['another_table_alias_name'] !== '') {
            $anotherTarget .= ' as ' . $item['another_table_alias_name'];
        }
        if ($item['another_field_name'] !== '') {
            $anotherTarget .= '.' . $item['another_field_name'];
        }

        return 'Another Field: ' . ($anotherTarget !== '' ? $anotherTarget : '(blank)');
    }

    return 'Argument / ' . app_db_access_parameter_data_type_caption($item['parameter_data_type']);
}

function app_db_access_group_by_target_caption(string $value): string
{
    return trim($value) === '1' ? 'Yes' : 'No';
}

/**
 * @param array{
 *     target_table_name:string,
 *     target_table_alias_name:string
 * } $item
 */
function app_db_access_target_table_reference_label(array $item): string
{
    $label = $item['target_table_name'];
    if ($item['target_table_alias_name'] !== '') {
        $label .= ' as ' . $item['target_table_alias_name'];
    }

    return $label !== '' ? $label : '(blank)';
}

/**
 * @param array{
 *     target_table_column_name:string,
 *     target_table_column_prefix:string,
 *     target_table_column_suffix:string
 * } $item
 */
function app_db_access_select_target_field_column_expression(array $item): string
{
    $expression = '';

    if ($item['target_table_column_prefix'] !== '') {
        $expression .= $item['target_table_column_prefix'];
    }

    if ($item['target_table_column_name'] !== '') {
        $expression .= $item['target_table_column_name'];
    }

    if ($item['target_table_column_suffix'] !== '') {
        $expression .= $item['target_table_column_suffix'];
    }

    return $expression !== '' ? $expression : '(blank)';
}

function app_db_access_select_target_field_caption(array $item): string
{
    $parts = [];
    $storeFieldName = trim((string) ($item['store_class_field_name'] ?? ''));
    if ($storeFieldName !== '') {
        $parts[] = $storeFieldName;
    }

    $parts[] = app_db_access_target_table_reference_label($item);
    $parts[] = app_db_access_select_target_field_column_expression($item);

    return implode(' / ', $parts);
}

function app_db_access_select_having_parameter_type_caption(string $value): string
{
    return match ($value) {
        'argument' => 'Argument',
        'fixed' => 'Fixed',
        'field' => 'Field',
        default => $value !== '' ? $value : '(blank)',
    };
}

function app_db_access_update_delete_where_parameter_type_caption(string $value): string
{
    return match ($value) {
        'argument' => 'Argument',
        'fixed' => 'Fixed',
        default => $value !== '' ? $value : '(blank)',
    };
}

/**
 * @param array<string,array{
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     target_table_column_prefix:string,
 *     target_table_column_suffix:string,
 *     store_class_field_name:string
 * }> $targetFieldById
 */
function app_db_access_select_having_target_field_label(string $fieldId, array $targetFieldById): string
{
    $normalizedFieldId = trim($fieldId);
    if ($normalizedFieldId === '' || $normalizedFieldId === '0') {
        return '(blank)';
    }

    $targetField = $targetFieldById[$normalizedFieldId] ?? null;
    if ($targetField === null) {
        return '#' . $normalizedFieldId . ' (missing)';
    }

    return app_db_access_select_target_field_caption($targetField);
}

/**
 * @param array{
 *     left_target_prefix:string,
 *     left_target_field_id:string,
 *     left_target_suffix:string
 * } $item
 * @param array<string,array{
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     target_table_column_prefix:string,
 *     target_table_column_suffix:string,
 *     store_class_field_name:string
 * }> $targetFieldById
 */
function app_db_access_select_having_left_summary(array $item, array $targetFieldById): string
{
    return $item['left_target_prefix']
        . app_db_access_select_having_target_field_label($item['left_target_field_id'], $targetFieldById)
        . $item['left_target_suffix'];
}

/**
 * @param array{
 *     right_target_prefix:string,
 *     right_parameter_type:string,
 *     right_parameter_data_type:string,
 *     right_fixed_parameter:string,
 *     right_target_field_id:string,
 *     right_target_suffix:string
 * } $item
 * @param array<string,array{
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     target_table_column_prefix:string,
 *     target_table_column_suffix:string,
 *     store_class_field_name:string
 * }> $targetFieldById
 */
function app_db_access_select_having_right_summary(array $item, array $targetFieldById): string
{
    $base = '';
    if ($item['right_parameter_type'] === 'fixed') {
        $base = 'Fixed: '
            . ($item['right_fixed_parameter'] !== '' ? $item['right_fixed_parameter'] : '(blank)')
            . ' / '
            . app_db_access_parameter_data_type_caption($item['right_parameter_data_type']);
    } elseif ($item['right_parameter_type'] === 'field') {
        $base = app_db_access_select_having_target_field_label($item['right_target_field_id'], $targetFieldById);
    } else {
        $base = 'Argument / ' . app_db_access_parameter_data_type_caption($item['right_parameter_data_type']);
    }

    return $item['right_target_prefix'] . $base . $item['right_target_suffix'];
}

/**
 * @param array{
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string
 * } $item
 */
function app_db_access_update_delete_where_parameter_summary(array $item): string
{
    if ($item['parameter_type'] === 'fixed') {
        return 'Fixed: ' . ($item['fixed_parameter'] !== '' ? $item['fixed_parameter'] : '(blank)')
            . ' / ' . app_db_access_parameter_data_type_caption($item['parameter_data_type']);
    }

    return 'Argument / ' . app_db_access_parameter_data_type_caption($item['parameter_data_type']);
}

/**
 * @param array{
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string
 * } $item
 */
function app_db_access_insert_update_target_field_parameter_summary(array $item): string
{
    return app_db_access_update_delete_where_parameter_summary($item);
}
