<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/generated_catalog.php';
require_once __DIR__ . '/managed_operation_repository_pdo.php';
require_once __DIR__ . '/managed_operation_server_dbaccess_executor.php';
require_once __DIR__ . '/managed_operation_sync_outbox_repository_pdo.php';
require_once __DIR__ . '/managed_operation_sync_outbox_processor.php';
require_once __DIR__ . '/no_code_managed_operation_bridge.php';
require_once __DIR__ . '/no_code_publish_candidate_repository_pdo.php';
require_once __DIR__ . '/no_code_runtime.php';
require_once __DIR__ . '/project_db_access_bootstrap_service.php';
require_once __DIR__ . '/project_output_service.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/sql_dialect.php';

function app_no_code_public_runtime_preview_path(string $projectKey, string $artifactKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/'
        . rawurlencode($artifactKey)
        . '/runtime-preview.html';
}

function app_no_code_public_runtime_execution_path(string $projectKey, string $artifactKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/'
        . rawurlencode($artifactKey)
        . '/execute.json';
}

function app_no_code_public_runtime_current_preview_path(string $projectKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/current/runtime-preview.html';
}

function app_no_code_public_runtime_current_execution_path(string $projectKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/current/execute.json';
}

function app_no_code_public_runtime_current_data_path(string $projectKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/current/runtime-data.json';
}

function app_no_code_public_runtime_alias_preview_path(string $projectKey, string $aliasKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/alias/'
        . rawurlencode(app_no_code_public_runtime_normalize_alias_key($aliasKey))
        . '/runtime-preview.html';
}

function app_no_code_public_runtime_alias_execution_path(string $projectKey, string $aliasKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/alias/'
        . rawurlencode(app_no_code_public_runtime_normalize_alias_key($aliasKey))
        . '/execute.json';
}

function app_no_code_public_runtime_alias_data_path(string $projectKey, string $aliasKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/alias/'
        . rawurlencode(app_no_code_public_runtime_normalize_alias_key($aliasKey))
        . '/runtime-data.json';
}

function app_no_code_public_runtime_artifact_cache_control(): string
{
    return 'public, max-age=31536000, immutable';
}

function app_no_code_public_runtime_current_cache_control(): string
{
    return 'no-store';
}

/**
 * @param array<string,mixed> $candidate
 * @return array<string,string>
 */
function app_no_code_public_runtime_execution_binding(string $projectKey, array $candidate): array
{
    $binding = [
        'csrf_token' => app_csrf_token(),
        'project_key' => app_normalize_project_key($projectKey),
        'artifact_key' => (string) ($candidate['artifact_key'] ?? ''),
        'source_output_key' => APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY,
    ];

    $revisionId = trim((string) ($candidate['revision_id'] ?? ''));
    if ($revisionId !== '') {
        $binding['revision_id'] = $revisionId;
    }
    if (app_no_code_public_runtime_demo_processing_enabled()) {
        $binding['demo_processing'] = 'available';
    }

    return $binding;
}

function app_no_code_public_runtime_demo_processing_enabled(): bool
{
    $enabled = strtolower(trim((string) getenv('MTOOL_NO_CODE_RUNTIME_SYNC_DEMO')));
    if (!in_array($enabled, ['1', 'true', 'yes', 'on'], true)) {
        return false;
    }

    $sqlitePath = trim((string) getenv('MTOOL_RUNTIME_SQLITE_PATH'));
    return $sqlitePath !== '';
}

/**
 * @param array<string,mixed> $post
 */
function app_no_code_public_runtime_demo_processing_requested(array $post): bool
{
    $value = strtolower(trim((string) ($post['runtime_demo_process'] ?? '')));
    return in_array($value, ['1', 'true', 'yes', 'on'], true);
}

function app_no_code_public_runtime_data_contract_version(): string
{
    return 'no-code-runtime-data-v0';
}

/**
 * @return array{status_code:int,payload:array<string,mixed>}
 */
function app_no_code_public_runtime_data_error_response(string $error, int $statusCode = 422): array
{
    return [
        'status_code' => $statusCode,
        'payload' => [
            'ok' => false,
            'contract_version' => app_no_code_public_runtime_data_contract_version(),
            'project_key' => '',
            'selection' => [],
            'screen_definition_version' => '',
            'runtime_preview_version' => app_no_code_runtime_version(),
            'read_model' => app_no_code_public_runtime_data_empty_read_model_metadata(),
            'screens' => [],
            'error' => $error,
        ],
    ];
}

/**
 * @return array{contracts:array<string,mixed>}
 */
function app_no_code_public_runtime_data_empty_read_model_metadata(): array
{
    return [
        'contracts' => [],
    ];
}

function app_no_code_public_runtime_data_normalized_field_type(string $type): string
{
    $normalized = strtolower(trim($type));
    $aliases = [
        'int' => 'integer',
        'bigint' => 'integer',
        'smallint' => 'integer',
        'bool' => 'boolean',
        'double' => 'number',
        'float' => 'number',
        'decimal' => 'number',
    ];
    if (isset($aliases[$normalized])) {
        return $aliases[$normalized];
    }

    $knownTypes = [
        'string',
        'text',
        'integer',
        'number',
        'boolean',
        'date',
        'datetime',
        'time',
        'json',
        'array',
        'object',
    ];

    return in_array($normalized, $knownTypes, true) ? $normalized : 'string';
}

/**
 * @param array<string,mixed> $field
 * @return array{field_key:string,label:string,type:string}
 */
function app_no_code_public_runtime_data_field_metadata(array $field): array
{
    $fieldKey = (string) ($field['field_key'] ?? '');

    return [
        'field_key' => $fieldKey,
        'label' => (string) ($field['label'] ?? $fieldKey),
        'type' => app_no_code_public_runtime_data_normalized_field_type((string) ($field['type'] ?? 'string')),
    ];
}

/**
 * @param array<string,mixed> $definition
 * @return array{contracts:array<string,array{contract_key:string,fields:array<string,array{field_key:string,label:string,type:string}>}>}
 */
function app_no_code_public_runtime_data_read_model_metadata(array $definition): array
{
    $readModel = app_no_code_public_runtime_data_empty_read_model_metadata();
    foreach (($definition['contracts'] ?? []) as $contract) {
        if (!is_array($contract)) {
            continue;
        }

        $contractKey = (string) ($contract['contract_key'] ?? '');
        if ($contractKey === '') {
            continue;
        }

        $fields = [];
        foreach (($contract['screens'] ?? []) as $screen) {
            if (!is_array($screen)) {
                continue;
            }
            foreach (($screen['fields'] ?? []) as $field) {
                if (!is_array($field)) {
                    continue;
                }

                $fieldMetadata = app_no_code_public_runtime_data_field_metadata($field);
                if ($fieldMetadata['field_key'] === '' || isset($fields[$fieldMetadata['field_key']])) {
                    continue;
                }

                $fields[$fieldMetadata['field_key']] = $fieldMetadata;
            }
        }

        $readModel['contracts'][$contractKey] = [
            'contract_key' => $contractKey,
            'fields' => $fields,
        ];
    }

    return $readModel;
}

function app_no_code_public_runtime_data_row_from_value(mixed $value): array
{
    if (is_array($value)) {
        return $value;
    }

    if (is_object($value)) {
        return get_object_vars($value);
    }

    return [];
}

/**
 * @return array<string,string|false>
 */
function app_no_code_public_runtime_capture_runtime_db_env(): array
{
    $keys = [
        'MTOOL_RUNTIME_DB_DSN',
        'MTOOL_RUNTIME_DB_USER',
        'MTOOL_RUNTIME_DB_PASSWORD',
        'MTOOL_RUNTIME_DB_HOST',
        'MTOOL_RUNTIME_DB_PORT',
        'MTOOL_RUNTIME_DB_NAME',
        'MTOOL_RUNTIME_SQLITE_PATH',
    ];
    $previous = [];
    foreach ($keys as $key) {
        $previous[$key] = getenv($key);
    }

    return $previous;
}

/**
 * @param array<string,string|false> $previous
 */
function app_no_code_public_runtime_restore_runtime_db_env(array $previous): void
{
    foreach ($previous as $key => $value) {
        if ($value === false) {
            putenv($key);
            continue;
        }

        putenv($key . '=' . $value);
    }

    $GLOBALS['mtooldb'] = null;
}

function app_no_code_public_runtime_apply_runtime_db_env(array $app, string $dbConfigKey = 'config_db'): void
{
    $configDb = app_database_config($app, $dbConfigKey);
    $dialect = app_sql_dialect_from_db_config($configDb);

    $GLOBALS['mtooldb'] = null;
    if ($dialect === 'sqlite') {
        putenv('MTOOL_RUNTIME_DB_DSN=' . (string) ($configDb['dsn'] ?? ''));
        putenv('MTOOL_RUNTIME_DB_USER=');
        putenv('MTOOL_RUNTIME_DB_PASSWORD=');
        putenv('MTOOL_RUNTIME_DB_HOST=');
        putenv('MTOOL_RUNTIME_DB_PORT=');
        putenv('MTOOL_RUNTIME_DB_NAME=');
        putenv('MTOOL_RUNTIME_SQLITE_PATH=' . (string) ($configDb['name'] ?? ''));
        return;
    }

    putenv('MTOOL_RUNTIME_DB_DSN');
    putenv('MTOOL_RUNTIME_SQLITE_PATH');
    putenv('MTOOL_RUNTIME_DB_HOST=' . (string) ($configDb['host'] ?? ''));
    putenv('MTOOL_RUNTIME_DB_PORT=' . (string) ($configDb['port'] ?? ''));
    putenv('MTOOL_RUNTIME_DB_USER=' . (string) ($configDb['user'] ?? ''));
    putenv('MTOOL_RUNTIME_DB_PASSWORD=' . (string) ($configDb['password'] ?? ''));
    putenv('MTOOL_RUNTIME_DB_NAME=' . (string) ($configDb['name'] ?? ''));
}

/**
 * @template T
 * @param callable():T $callback
 * @return T
 */
function app_no_code_public_runtime_with_runtime_db_env(array $app, callable $callback): mixed
{
    $previous = app_no_code_public_runtime_capture_runtime_db_env();
    app_no_code_public_runtime_apply_runtime_db_env($app);
    try {
        return $callback();
    } finally {
        app_no_code_public_runtime_restore_runtime_db_env($previous);
    }
}

/**
 * @return list<array<string,mixed>>
 */
function app_no_code_public_runtime_data_rows_for_contract(array $app, string $projectKey, string $contractKey): array
{
    $runtimeEntity = app_project_db_access_bootstrap_materialize_runtime_entity($app, $projectKey, $contractKey);
    if (!$runtimeEntity['ok'] || !is_array($runtimeEntity['entity'] ?? null)) {
        throw new RuntimeException($runtimeEntity['error']);
    }

    $entity = $runtimeEntity['entity'];
    $dataPath = (string) ($entity['data_path'] ?? '');
    $dbaccessPath = (string) ($entity['dbaccess_path'] ?? '');
    if ($dataPath === '' || $dbaccessPath === '' || !is_file($dataPath) || !is_file($dbaccessPath)) {
        throw new RuntimeException('runtime DBAccess files were not materialized for fresh runtime data.');
    }

    require_once $dataPath;
    require_once $dbaccessPath;

    $dbAccessClass = (string) ($entity['dbaccess_class'] ?? '');
    if ($dbAccessClass === '' || !class_exists($dbAccessClass)) {
        throw new RuntimeException('runtime DBAccess class was not found for fresh runtime data: ' . $dbAccessClass);
    }

    $sourceName = (string) ($entity['source_name'] ?? $contractKey);
    $generatedSourceName = preg_replace('/DBAccess$/', '', $dbAccessClass);
    $methodCandidates = array_values(array_unique(array_filter([
        'Get' . $sourceName . 'List',
        is_string($generatedSourceName) ? 'Get' . $generatedSourceName . 'List' : '',
    ])));

    $dbAccess = new $dbAccessClass();
    $listMethod = '';
    foreach ($methodCandidates as $methodCandidate) {
        if (method_exists($dbAccess, $methodCandidate)) {
            $listMethod = $methodCandidate;
            break;
        }
    }
    if ($listMethod === '') {
        throw new RuntimeException('runtime DBAccess list method was not found for fresh runtime data: ' . $contractKey);
    }

    $result = $dbAccess->$listMethod();
    if (!is_array($result)) {
        throw new RuntimeException('runtime DBAccess list method did not return rows: ' . $listMethod);
    }

    return array_values(array_map(
        static fn (mixed $item): array => app_no_code_public_runtime_data_row_from_value($item),
        $result,
    ));
}

/**
 * @param array<string,mixed> $render
 * @param array<string,mixed> $currentItem
 * @return array{field_key:string,value:mixed,display_value:string}|array{}
 */
function app_no_code_public_runtime_data_selected_key(array $render, array $currentItem): array
{
    foreach (($render['actions'] ?? []) as $action) {
        if (!is_array($action)) {
            continue;
        }
        foreach (($action['fields'] ?? []) as $field) {
            if (!is_array($field) || (string) ($field['role'] ?? '') !== 'key') {
                continue;
            }

            $fieldKey = (string) ($field['field_key'] ?? '');
            if ($fieldKey === '' || !array_key_exists($fieldKey, $currentItem)) {
                continue;
            }

            $value = $currentItem[$fieldKey];
            return [
                'field_key' => $fieldKey,
                'value' => $value,
                'display_value' => app_no_code_runtime_display_value($value),
            ];
        }
    }

    return [];
}

function app_no_code_public_runtime_data_selected_key_query(): string
{
    $selectedKey = trim(app_query_param('selected_key'));
    if ($selectedKey === '') {
        return '';
    }
    if (strlen($selectedKey) > 128 || preg_match('/[\x00-\x1F\x7F]/', $selectedKey) === 1) {
        throw new InvalidArgumentException('runtime data selected key is invalid.');
    }

    return $selectedKey;
}

function app_no_code_public_runtime_data_search_query(): string
{
    $query = trim(app_query_param('q'));
    if ($query === '') {
        return '';
    }
    if (strlen($query) > 128 || preg_match('/[\x00-\x1F\x7F]/', $query) === 1) {
        throw new InvalidArgumentException('runtime data search query is invalid.');
    }

    return $query;
}

/**
 * @return array<string,string>
 */
function app_no_code_public_runtime_data_filter_query(): array
{
    $rawFilters = $_GET['filter'] ?? [];
    if ($rawFilters === '' || $rawFilters === []) {
        return [];
    }
    if (!is_array($rawFilters)) {
        throw new InvalidArgumentException('runtime data filter query is invalid.');
    }
    if (count($rawFilters) > 8) {
        throw new InvalidArgumentException('runtime data filter query accepts 8 fields or less.');
    }

    $filters = [];
    foreach ($rawFilters as $fieldKey => $rawValue) {
        if (!is_string($fieldKey) || preg_match('/^[A-Za-z0-9_]{1,64}$/', $fieldKey) !== 1) {
            throw new InvalidArgumentException('runtime data filter field is invalid.');
        }
        if (!is_string($rawValue)) {
            throw new InvalidArgumentException('runtime data filter value is invalid.');
        }

        $value = trim($rawValue);
        if ($value === '') {
            continue;
        }
        if (strlen($value) > 128 || preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
            throw new InvalidArgumentException('runtime data filter value is invalid.');
        }
        $filters[$fieldKey] = $value;
    }

    return $filters;
}

/**
 * @param array<string,string> $filters
 * @return array<string,string>
 */
function app_no_code_public_runtime_data_filter_operator_query(array $filters): array
{
    $rawOperators = $_GET['filter_op'] ?? [];
    if ($rawOperators === '' || $rawOperators === []) {
        $operators = [];
        foreach (array_keys($filters) as $fieldKey) {
            $operators[$fieldKey] = 'contains';
        }

        return $operators;
    }
    if (!is_array($rawOperators)) {
        throw new InvalidArgumentException('runtime data filter operator query is invalid.');
    }
    if (count($rawOperators) > 8) {
        throw new InvalidArgumentException('runtime data filter operator query accepts 8 fields or less.');
    }

    $operators = [];
    foreach ($rawOperators as $fieldKey => $rawOperator) {
        if (!is_string($fieldKey) || preg_match('/^[A-Za-z0-9_]{1,64}$/', $fieldKey) !== 1) {
            throw new InvalidArgumentException('runtime data filter operator field is invalid.');
        }
        if (!array_key_exists($fieldKey, $filters)) {
            throw new InvalidArgumentException('runtime data filter operator requires a matching filter value.');
        }
        if (!is_string($rawOperator)) {
            throw new InvalidArgumentException('runtime data filter operator is invalid.');
        }

        $operator = strtolower(trim($rawOperator));
        if (!in_array($operator, ['contains', 'eq', 'gt', 'gte', 'lt', 'lte'], true)) {
            throw new InvalidArgumentException('runtime data filter operator must be contains, eq, gt, gte, lt, or lte.');
        }
        $operators[$fieldKey] = $operator;
    }
    foreach (array_keys($filters) as $fieldKey) {
        $operators[$fieldKey] = $operators[$fieldKey] ?? 'contains';
    }

    return $operators;
}

/**
 * @return array{field:string,direction:string,fields:array<string,string>}
 */
function app_no_code_public_runtime_data_sort_query(): array
{
    $rawSort = $_GET['sort'] ?? [];
    if ($rawSort === '' || $rawSort === []) {
        return [
            'field' => '',
            'direction' => '',
            'fields' => [],
        ];
    }
    if (!is_array($rawSort)) {
        throw new InvalidArgumentException('runtime data sort query is invalid.');
    }
    if (count($rawSort) > 3) {
        throw new InvalidArgumentException('runtime data sort query accepts 3 fields or less.');
    }

    $fields = [];
    foreach ($rawSort as $fieldKey => $rawDirection) {
        if (!is_string($fieldKey) || preg_match('/^[A-Za-z0-9_]{1,64}$/', $fieldKey) !== 1) {
            throw new InvalidArgumentException('runtime data sort field is invalid.');
        }
        if (!is_string($rawDirection)) {
            throw new InvalidArgumentException('runtime data sort direction is invalid.');
        }

        $direction = strtolower(trim($rawDirection));
        if (!in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('runtime data sort direction must be asc or desc.');
        }
        $fields[$fieldKey] = $direction;
    }

    $firstField = array_key_first($fields);
    return [
        'field' => is_string($firstField) ? $firstField : '',
        'direction' => is_string($firstField) ? $fields[$firstField] : '',
        'fields' => $fields,
    ];
}

/**
 * @return array{enabled:bool,page:int,page_size:int}
 */
function app_no_code_public_runtime_data_pagination_query(): array
{
    $pageRequested = array_key_exists('page', $_GET);
    $pageSizeRequested = array_key_exists('page_size', $_GET);
    if (!$pageRequested && !$pageSizeRequested) {
        return [
            'enabled' => false,
            'page' => 1,
            'page_size' => 0,
        ];
    }

    $page = app_no_code_public_runtime_data_positive_query_int('page', $pageRequested ? null : '1');
    $pageSize = app_no_code_public_runtime_data_positive_query_int('page_size', $pageSizeRequested ? null : '50');
    if ($pageSize > 100) {
        throw new InvalidArgumentException('runtime data page_size must be 100 or less.');
    }

    return [
        'enabled' => true,
        'page' => $page,
        'page_size' => $pageSize,
    ];
}

function app_no_code_public_runtime_data_positive_query_int(string $name, ?string $default): int
{
    $rawValue = $_GET[$name] ?? $default;
    if (!is_string($rawValue) || $rawValue === '' || preg_match('/^[1-9][0-9]*$/', $rawValue) !== 1) {
        throw new InvalidArgumentException('runtime data ' . $name . ' must be a positive integer.');
    }

    return (int) $rawValue;
}

/**
 * @param list<array<string,mixed>> $rows
 * @return list<array<string,mixed>>
 */
function app_no_code_public_runtime_data_search_rows(array $rows, string $query): array
{
    if ($query === '') {
        return $rows;
    }

    return array_values(array_filter($rows, static function (array $row) use ($query): bool {
        foreach ($row as $value) {
            if (stripos(app_no_code_runtime_display_value($value), $query) !== false) {
                return true;
            }
        }

        return false;
    }));
}

/**
 * @param array<string,mixed> $contract
 * @return array<string,string>
 */
function app_no_code_public_runtime_data_contract_field_types(array $contract): array
{
    $types = [];
    foreach (($contract['screens'] ?? []) as $screen) {
        if (!is_array($screen)) {
            continue;
        }
        foreach (($screen['fields'] ?? []) as $field) {
            if (!is_array($field)) {
                continue;
            }

            $fieldKey = (string) ($field['field_key'] ?? '');
            if ($fieldKey === '' || isset($types[$fieldKey])) {
                continue;
            }

            $types[$fieldKey] = app_no_code_public_runtime_data_normalized_field_type((string) ($field['type'] ?? 'string'));
        }
    }

    return $types;
}

function app_no_code_public_runtime_data_filter_operator_is_numeric(string $operator): bool
{
    return in_array($operator, ['gt', 'gte', 'lt', 'lte'], true);
}

function app_no_code_public_runtime_data_filter_operator_is_ordered(string $operator): bool
{
    return in_array($operator, ['gt', 'gte', 'lt', 'lte'], true);
}

function app_no_code_public_runtime_data_field_type_is_numeric(string $fieldType): bool
{
    return in_array($fieldType, ['integer', 'number'], true);
}

function app_no_code_public_runtime_data_field_type_is_datetime(string $fieldType): bool
{
    return in_array($fieldType, ['date', 'datetime', 'time'], true);
}

function app_no_code_public_runtime_data_numeric_filter_value(mixed $value, string $fieldKey): float
{
    $displayValue = app_no_code_runtime_display_value($value);
    if (preg_match('/^-?[0-9]+(?:\.[0-9]+)?$/', $displayValue) !== 1) {
        throw new RuntimeException('runtime data numeric filter value was not numeric: ' . $fieldKey);
    }

    return (float) $displayValue;
}

function app_no_code_public_runtime_data_datetime_value(mixed $value, string $fieldKey, string $fieldType, string $context): string
{
    $displayValue = app_no_code_runtime_display_value($value);
    if ($fieldType === 'date') {
        if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $displayValue, $matches) !== 1) {
            throw new RuntimeException('runtime data date/time ' . $context . ' value was not parseable: ' . $fieldKey);
        }
        if (!checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1])) {
            throw new RuntimeException('runtime data date/time ' . $context . ' value was not parseable: ' . $fieldKey);
        }

        return $displayValue;
    }

    if ($fieldType === 'time') {
        if (preg_match('/^([0-9]{2}):([0-9]{2}):([0-9]{2})$/', $displayValue, $matches) !== 1) {
            throw new RuntimeException('runtime data date/time ' . $context . ' value was not parseable: ' . $fieldKey);
        }
        $hour = (int) $matches[1];
        $minute = (int) $matches[2];
        $second = (int) $matches[3];
        if ($hour > 23 || $minute > 59 || $second > 59) {
            throw new RuntimeException('runtime data date/time ' . $context . ' value was not parseable: ' . $fieldKey);
        }

        return $displayValue;
    }

    if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})(?:T| )([0-9]{2}):([0-9]{2}):([0-9]{2})$/', $displayValue, $matches) !== 1) {
        throw new RuntimeException('runtime data date/time ' . $context . ' value was not parseable: ' . $fieldKey);
    }
    if (!checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1])) {
        throw new RuntimeException('runtime data date/time ' . $context . ' value was not parseable: ' . $fieldKey);
    }
    $hour = (int) $matches[4];
    $minute = (int) $matches[5];
    $second = (int) $matches[6];
    if ($hour > 23 || $minute > 59 || $second > 59) {
        throw new RuntimeException('runtime data date/time ' . $context . ' value was not parseable: ' . $fieldKey);
    }

    return $matches[1] . '-' . $matches[2] . '-' . $matches[3] . 'T' . $matches[4] . ':' . $matches[5] . ':' . $matches[6];
}

/**
 * @param list<array<string,mixed>> $rows
 * @param array<string,string> $filters
 * @param array<string,string> $operators
 * @param array<string,string> $fieldTypes
 * @return list<array<string,mixed>>
 */
function app_no_code_public_runtime_data_filter_rows(array $rows, array $filters, array $operators = [], array $fieldTypes = []): array
{
    if ($filters === []) {
        return $rows;
    }

    foreach (array_keys($filters) as $fieldKey) {
        $fieldExists = false;
        foreach ($rows as $row) {
            if (array_key_exists($fieldKey, $row)) {
                $fieldExists = true;
                break;
            }
        }
        if (!$fieldExists) {
            throw new RuntimeException('runtime data filter field was not found.');
        }
    }

    foreach ($operators as $fieldKey => $operator) {
        if (!app_no_code_public_runtime_data_filter_operator_is_ordered($operator)) {
            continue;
        }

        $fieldType = $fieldTypes[$fieldKey] ?? 'string';
        if (app_no_code_public_runtime_data_field_type_is_numeric($fieldType)) {
            app_no_code_public_runtime_data_numeric_filter_value($filters[$fieldKey] ?? '', $fieldKey);
            continue;
        }
        if (app_no_code_public_runtime_data_field_type_is_datetime($fieldType)) {
            app_no_code_public_runtime_data_datetime_value($filters[$fieldKey] ?? '', $fieldKey, $fieldType, 'filter');
            continue;
        }

        throw new RuntimeException('runtime data ordered filter operator requires a numeric or date/time field: ' . $fieldKey);
    }

    return array_values(array_filter($rows, static function (array $row) use ($filters, $operators, $fieldTypes): bool {
        foreach ($filters as $fieldKey => $filterValue) {
            $displayValue = app_no_code_runtime_display_value($row[$fieldKey] ?? null);
            $operator = $operators[$fieldKey] ?? 'contains';
            if (app_no_code_public_runtime_data_filter_operator_is_ordered($operator)) {
                $fieldType = $fieldTypes[$fieldKey] ?? 'string';
                if (app_no_code_public_runtime_data_field_type_is_datetime($fieldType)) {
                    $rowValue = app_no_code_public_runtime_data_datetime_value($row[$fieldKey] ?? null, $fieldKey, $fieldType, 'filter');
                    $queryValue = app_no_code_public_runtime_data_datetime_value($filterValue, $fieldKey, $fieldType, 'filter');
                    $comparison = strcmp($rowValue, $queryValue);
                } else {
                    $rowValue = app_no_code_public_runtime_data_numeric_filter_value($row[$fieldKey] ?? null, $fieldKey);
                    $queryValue = app_no_code_public_runtime_data_numeric_filter_value($filterValue, $fieldKey);
                    $comparison = $rowValue <=> $queryValue;
                }
                if ($operator === 'gt' && $comparison <= 0) {
                    return false;
                }
                if ($operator === 'gte' && $comparison < 0) {
                    return false;
                }
                if ($operator === 'lt' && $comparison >= 0) {
                    return false;
                }
                if ($operator === 'lte' && $comparison > 0) {
                    return false;
                }
                continue;
            }
            if ($operator === 'eq' && $displayValue !== $filterValue) {
                return false;
            }
            if ($operator !== 'eq' && stripos($displayValue, $filterValue) === false) {
                return false;
            }
        }

        return true;
    }));
}

function app_no_code_public_runtime_data_numeric_sort_value(mixed $value, string $fieldKey): float
{
    $displayValue = app_no_code_runtime_display_value($value);
    if (preg_match('/^-?[0-9]+(?:\.[0-9]+)?$/', $displayValue) !== 1) {
        throw new RuntimeException('runtime data numeric sort value was not numeric: ' . $fieldKey);
    }

    return (float) $displayValue;
}

/**
 * @param list<array<string,mixed>> $rows
 * @param array{field:string,direction:string,fields?:array<string,string>} $sort
 * @param array<string,string> $fieldTypes
 * @return list<array<string,mixed>>
 */
function app_no_code_public_runtime_data_sort_rows(array $rows, array $sort, array $fieldTypes = []): array
{
    $sortFields = is_array($sort['fields'] ?? null) ? $sort['fields'] : [];
    if ($sortFields === [] && (string) ($sort['field'] ?? '') !== '') {
        $sortFields = [(string) $sort['field'] => (string) ($sort['direction'] ?? '')];
    }
    if ($sortFields === []) {
        return $rows;
    }

    foreach (array_keys($sortFields) as $fieldKey) {
        $fieldExists = false;
        foreach ($rows as $row) {
            if (array_key_exists($fieldKey, $row)) {
                $fieldExists = true;
                break;
            }
        }
        if (!$fieldExists) {
            throw new RuntimeException('runtime data sort field was not found.');
        }
    }

    foreach ($sortFields as $fieldKey => $_direction) {
        $fieldType = $fieldTypes[$fieldKey] ?? 'string';
        if (!app_no_code_public_runtime_data_field_type_is_numeric($fieldType) && !app_no_code_public_runtime_data_field_type_is_datetime($fieldType)) {
            continue;
        }
        foreach ($rows as $row) {
            if (app_no_code_public_runtime_data_field_type_is_datetime($fieldType)) {
                app_no_code_public_runtime_data_datetime_value($row[$fieldKey] ?? null, $fieldKey, $fieldType, 'sort');
            } else {
                app_no_code_public_runtime_data_numeric_sort_value($row[$fieldKey] ?? null, $fieldKey);
            }
        }
    }

    $indexedRows = [];
    foreach ($rows as $index => $row) {
        $indexedRows[] = [
            'index' => $index,
            'row' => $row,
        ];
    }

    usort($indexedRows, static function (array $left, array $right) use ($sortFields, $fieldTypes): int {
        foreach ($sortFields as $fieldKey => $direction) {
            $fieldType = $fieldTypes[$fieldKey] ?? 'string';
            if (app_no_code_public_runtime_data_field_type_is_numeric($fieldType)) {
                $leftValue = app_no_code_public_runtime_data_numeric_sort_value($left['row'][$fieldKey] ?? null, $fieldKey);
                $rightValue = app_no_code_public_runtime_data_numeric_sort_value($right['row'][$fieldKey] ?? null, $fieldKey);
                $comparison = $leftValue <=> $rightValue;
            } elseif (app_no_code_public_runtime_data_field_type_is_datetime($fieldType)) {
                $leftValue = app_no_code_public_runtime_data_datetime_value($left['row'][$fieldKey] ?? null, $fieldKey, $fieldType, 'sort');
                $rightValue = app_no_code_public_runtime_data_datetime_value($right['row'][$fieldKey] ?? null, $fieldKey, $fieldType, 'sort');
                $comparison = strcmp($leftValue, $rightValue);
            } else {
                $leftValue = app_no_code_runtime_display_value($left['row'][$fieldKey] ?? null);
                $rightValue = app_no_code_runtime_display_value($right['row'][$fieldKey] ?? null);
                $comparison = strnatcasecmp($leftValue, $rightValue);
            }
            if ($comparison !== 0 && $direction === 'desc') {
                $comparison = -$comparison;
            }
            if ($comparison !== 0) {
                return $comparison;
            }
        }
        return $left['index'] <=> $right['index'];
    });

    return array_values(array_map(static fn (array $entry): array => $entry['row'], $indexedRows));
}

/**
 * @param array<string,mixed> $contract
 */
function app_no_code_public_runtime_data_contract_key_field(array $contract): string
{
    foreach (($contract['actions'] ?? []) as $action) {
        if (!is_array($action)) {
            continue;
        }
        foreach (($action['fields'] ?? []) as $field) {
            if (!is_array($field) || (string) ($field['role'] ?? '') !== 'key') {
                continue;
            }

            $fieldKey = (string) ($field['field_key'] ?? '');
            if ($fieldKey !== '') {
                return $fieldKey;
            }
        }
    }

    return '';
}

/**
 * @param list<array<string,mixed>> $rows
 * @return array<string,mixed>
 */
function app_no_code_public_runtime_data_current_item(array $contract, array $rows, string $selectedKey): array
{
    if ($selectedKey === '') {
        return $rows[0] ?? [];
    }

    $keyField = app_no_code_public_runtime_data_contract_key_field($contract);
    if ($keyField === '') {
        throw new RuntimeException('runtime data selected key field was not found.');
    }

    foreach ($rows as $row) {
        if (app_no_code_runtime_display_value($row[$keyField] ?? null) === $selectedKey) {
            return $row;
        }
    }

    throw new RuntimeException('runtime data selected key was not found.');
}

/**
 * @param array<string,string> $filters
 * @param array{field:string,direction:string,fields?:array<string,string>} $sort
 * @return array<string,mixed>
 */
function app_no_code_public_runtime_data_selection_basis(
    array $currentItem,
    string $selectedKey,
    string $searchQuery,
    array $filters,
    array $sort,
): array {
    if ($currentItem === []) {
        return [
            'kind' => 'empty-result',
            'source' => 'none',
        ];
    }

    if ($selectedKey !== '') {
        return [
            'kind' => 'explicit-selected-key',
            'source' => 'selected_key',
        ];
    }

    if ($searchQuery !== '' || $filters !== [] || (string) ($sort['field'] ?? '') !== '') {
        return [
            'kind' => 'query-result-first-row',
            'source' => 'query',
        ];
    }

    return [
        'kind' => 'default-first-row',
        'source' => 'default',
    ];
}

/**
 * @param array<string,mixed> $render
 * @param list<array<string,mixed>> $renderRows
 * @param list<array<string,mixed>> $allRows
 * @param array<string,mixed> $currentItem
 * @param array{enabled:bool,page:int,page_size:int} $pagination
 * @param array<string,mixed> $selectionBasis
 * @return array<string,mixed>
 */
function app_no_code_public_runtime_data_screen_metadata(
    array $render,
    array $renderRows,
    array $allRows,
    array $currentItem,
    array $pagination,
    array $selectionBasis,
): array
{
    $metadata = [
        'row_count' => count($renderRows),
        'selected_key' => app_no_code_public_runtime_data_selected_key($render, $currentItem),
        'selection_basis' => $selectionBasis,
        'freshness' => 'live-read',
    ];

    if ((string) ($render['screen_type'] ?? '') === 'list' && $pagination['enabled']) {
        $totalRows = count($allRows);
        $pageSize = $pagination['page_size'];
        $pageCount = max(1, (int) ceil($totalRows / max(1, $pageSize)));
        $metadata['pagination'] = [
            'page' => $pagination['page'],
            'page_size' => $pageSize,
            'total_rows' => $totalRows,
            'page_count' => $pageCount,
            'has_previous_page' => $pagination['page'] > 1,
            'has_next_page' => $pagination['page'] < $pageCount,
        ];
    }

    return $metadata;
}

/**
 * @param list<array<string,mixed>> $rows
 * @param array{enabled:bool,page:int,page_size:int} $pagination
 * @return list<array<string,mixed>>
 */
function app_no_code_public_runtime_data_paginated_rows(array $rows, array $pagination): array
{
    if (!$pagination['enabled']) {
        return $rows;
    }

    return array_values(array_slice(
        $rows,
        ($pagination['page'] - 1) * $pagination['page_size'],
        $pagination['page_size'],
    ));
}

/**
 * @param array<string,mixed> $definition
 * @param array<string,string> $filters
 * @param array<string,string> $filterOperators
 * @param array{field:string,direction:string,fields?:array<string,string>} $sort
 * @return list<array<string,mixed>>
 */
function app_no_code_public_runtime_data_screens(
    array $app,
    string $projectKey,
    array $definition,
    string $selectedKey = '',
    array $pagination = ['enabled' => false, 'page' => 1, 'page_size' => 0],
    string $searchQuery = '',
    array $filters = [],
    array $filterOperators = [],
    array $sort = ['field' => '', 'direction' => '', 'fields' => []],
): array
{
    $screens = [];
    foreach (($definition['contracts'] ?? []) as $contract) {
        if (!is_array($contract)) {
            continue;
        }

        $contractKey = (string) ($contract['contract_key'] ?? '');
        if ($contractKey === '') {
            continue;
        }

        $rows = app_no_code_public_runtime_data_rows_for_contract($app, $projectKey, $contractKey);
        $fieldTypes = app_no_code_public_runtime_data_contract_field_types($contract);
        $rows = app_no_code_public_runtime_data_search_rows($rows, $searchQuery);
        $rows = app_no_code_public_runtime_data_filter_rows($rows, $filters, $filterOperators, $fieldTypes);
        $rows = app_no_code_public_runtime_data_sort_rows($rows, $sort, $fieldTypes);
        $currentItem = app_no_code_public_runtime_data_current_item($contract, $rows, $selectedKey);
        $selectionBasis = app_no_code_public_runtime_data_selection_basis($currentItem, $selectedKey, $searchQuery, $filters, $sort);
        foreach (($contract['screens'] ?? []) as $screen) {
            if (!is_array($screen)) {
                continue;
            }
            $screenKey = (string) ($screen['screen_key'] ?? '');
            if ($screenKey === '') {
                continue;
            }

            $screenRows = (string) ($screen['screen_type'] ?? '') === 'list'
                ? app_no_code_public_runtime_data_paginated_rows($rows, $pagination)
                : $rows;
            $renderResult = app_no_code_runtime_render_screen($definition, $screenKey, $screenRows, $currentItem);
            if (!$renderResult['ok']) {
                throw new RuntimeException($renderResult['error']);
            }

            $render = $renderResult['render'];
            $screens[] = [
                'screen_key' => (string) ($render['screen_key'] ?? ''),
                'screen_type' => (string) ($render['screen_type'] ?? ''),
                'contract_key' => (string) ($render['contract_key'] ?? ''),
                'data' => is_array($render['data'] ?? null) ? $render['data'] : [],
                'metadata' => app_no_code_public_runtime_data_screen_metadata($render, $screenRows, $rows, $currentItem, $pagination, $selectionBasis),
                'source' => [
                    'kind' => 'generated-dbaccess',
                    'contract_key' => $contractKey,
                ],
            ];
        }
    }

    return $screens;
}

/**
 * @param array<string,mixed> $candidate
 * @return array{status_code:int,payload:array<string,mixed>}
 */
function app_no_code_public_runtime_data_response_for_candidate(
    array $app,
    string $projectKey,
    array $candidate,
    string $selectionKind,
    string $aliasKey = '',
): array {
    $definitionResult = app_no_code_public_runtime_candidate_screen_definition($app, $projectKey, $candidate);
    if (!$definitionResult['ok']) {
        return app_no_code_public_runtime_data_error_response($definitionResult['error']);
    }

    try {
        $selectedKey = app_no_code_public_runtime_data_selected_key_query();
        $searchQuery = app_no_code_public_runtime_data_search_query();
        $filters = app_no_code_public_runtime_data_filter_query();
        $filterOperators = app_no_code_public_runtime_data_filter_operator_query($filters);
        $sort = app_no_code_public_runtime_data_sort_query();
        $pagination = app_no_code_public_runtime_data_pagination_query();
        $definition = $definitionResult['definition'];
        $screens = app_no_code_public_runtime_with_runtime_db_env(
            $app,
            static fn (): array => app_no_code_public_runtime_data_screens($app, $projectKey, $definition, $selectedKey, $pagination, $searchQuery, $filters, $filterOperators, $sort),
        );
    } catch (Throwable $throwable) {
        return app_no_code_public_runtime_data_error_response($throwable->getMessage());
    }

    return [
        'status_code' => 200,
        'payload' => [
            'ok' => true,
            'contract_version' => app_no_code_public_runtime_data_contract_version(),
            'project_key' => app_normalize_project_key($projectKey),
            'selection' => [
                'kind' => $selectionKind,
                'alias_key' => $aliasKey,
                'artifact_key' => (string) ($candidate['artifact_key'] ?? ''),
                'revision_id' => (string) ($candidate['revision_id'] ?? ''),
            ],
            'screen_definition_version' => (string) ($definition['definition_version'] ?? ''),
            'runtime_preview_version' => app_no_code_runtime_version(),
            'read_model' => app_no_code_public_runtime_data_read_model_metadata($definition),
            'query' => [
                'selected_key' => $selectedKey,
                'q' => $searchQuery,
                'filter' => $filters,
                'filter_op' => $filterOperators,
                'sort' => is_array($sort['fields'] ?? null) ? $sort['fields'] : ((string) ($sort['field'] ?? '') !== '' ? [$sort['field'] => $sort['direction']] : []),
                'page' => $pagination['enabled'] ? (string) $pagination['page'] : '',
                'page_size' => $pagination['enabled'] ? (string) $pagination['page_size'] : '',
            ],
            'screens' => $screens,
            'error' => '',
        ],
    ];
}

/**
 * @param array<string,mixed> $candidate
 * @return array{ok:bool,definition:array<string,mixed>,error:string}
 */
function app_no_code_public_runtime_candidate_screen_definition(
    array $app,
    string $projectKey,
    array $candidate,
): array {
    $artifactKey = (string) ($candidate['artifact_key'] ?? '');
    if (!app_project_output_artifact_key_is_valid($artifactKey)) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution artifact binding does not match',
        ];
    }

    $artifactResult = app_project_output_find($app, $projectKey, $artifactKey);
    if (!$artifactResult['ok'] || $artifactResult['item'] === null) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution artifact was not found',
        ];
    }

    $artifact = $artifactResult['item'];
    if ($artifact['source_output_key'] !== APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution artifact binding does not match',
        ];
    }

    try {
        $runtimeRoot = app_project_output_artifact_bundle_runtime_root($artifact);
    } catch (Throwable) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution artifact was not found',
        ];
    }

    $definitionPath = $runtimeRoot . '/screen-definition.json';
    if (!is_file($definitionPath)) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution screen definition is missing',
        ];
    }

    $definition = json_decode((string) file_get_contents($definitionPath), true);
    if (!is_array($definition)) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution screen definition is invalid',
        ];
    }

    return [
        'ok' => true,
        'definition' => $definition,
        'error' => '',
    ];
}

/**
 * @return callable(array<string,mixed>):array<string,mixed>
 */
function app_no_code_public_runtime_dispatcher(array $app): callable
{
    return app_no_code_managed_operation_dispatcher(
        [
            'origin' => 'public-runtime',
            'target' => 'server',
        ],
        static fn (array $intent): array => app_pdo_enqueue_managed_operation_sync_intent($app, $intent),
    );
}

/**
 * @param array<string,mixed> $payload
 * @return array{ok:bool,processed:bool,outcome:string,item:array<string,mixed>|null,handler_result:array<string,mixed>|null,error:string}
 */
function app_no_code_public_runtime_demo_process_execution_outbox(array $app, string $projectKey, array $payload): array
{
    if (!app_no_code_public_runtime_demo_processing_enabled()) {
        return [
            'ok' => false,
            'processed' => false,
            'outcome' => 'demo_processing_disabled',
            'item' => null,
            'handler_result' => null,
            'error' => 'no-code runtime synchronous demo processing is disabled',
        ];
    }

    $operationKey = (string) ($payload['result']['sync_intent']['operation_key'] ?? $payload['intent']['operation_key'] ?? '');
    if ($operationKey === '') {
        return [
            'ok' => false,
            'processed' => false,
            'outcome' => 'operation_missing',
            'item' => null,
            'handler_result' => null,
            'error' => 'no-code runtime synchronous demo processing requires an operation key',
        ];
    }

    $snapshot = app_pdo_fetch_managed_operation_snapshot($app, $projectKey);
    if (!$snapshot['ok']) {
        return [
            'ok' => false,
            'processed' => false,
            'outcome' => 'operation_snapshot_failed',
            'item' => null,
            'handler_result' => null,
            'error' => $snapshot['error'],
        ];
    }

    $operation = null;
    foreach ($snapshot['items'] as $item) {
        if ((string) ($item['operation_key'] ?? '') === $operationKey) {
            $operation = $item;
            break;
        }
    }
    if (!is_array($operation)) {
        return [
            'ok' => false,
            'processed' => false,
            'outcome' => 'operation_not_found',
            'item' => null,
            'handler_result' => null,
            'error' => 'managed operation was not found for synchronous demo processing: ' . $operationKey,
        ];
    }

    $contractKey = (string) ($operation['contract_key'] ?? '');
    if ($contractKey !== '') {
        $runtimeEntity = app_project_db_access_bootstrap_materialize_runtime_entity($app, $projectKey, $contractKey);
        if (!$runtimeEntity['ok'] || !is_array($runtimeEntity['entity'] ?? null)) {
            return [
                'ok' => false,
                'processed' => false,
                'outcome' => 'runtime_entity_failed',
                'item' => null,
                'handler_result' => null,
                'error' => $runtimeEntity['error'],
            ];
        }

        $entity = $runtimeEntity['entity'];
        $dataPath = (string) ($entity['data_path'] ?? '');
        $dbaccessPath = (string) ($entity['dbaccess_path'] ?? '');
        if ($dataPath === '' || $dbaccessPath === '' || !is_file($dataPath) || !is_file($dbaccessPath)) {
            return [
                'ok' => false,
                'processed' => false,
                'outcome' => 'runtime_entity_failed',
                'item' => null,
                'handler_result' => null,
                'error' => 'runtime DBAccess files were not materialized for synchronous demo processing',
            ];
        }

        require_once $dataPath;
        require_once $dbaccessPath;
    }

    $binding = app_managed_operation_server_dbaccess_binding_from_project_catalog($app, $projectKey, $operation);
    if (!$binding['ok'] || !is_array($binding['binding'] ?? null)) {
        return [
            'ok' => false,
            'processed' => false,
            'outcome' => 'binding_failed',
            'item' => null,
            'handler_result' => null,
            'error' => $binding['error'],
        ];
    }

    return app_managed_operation_sync_outbox_process_next(
        $app,
        $projectKey,
        app_managed_operation_server_dbaccess_outbox_handler($binding['binding']),
    );
}

/**
 * @param array<string,mixed> $candidate
 * @param array<string,mixed> $post
 * @param array<string,mixed>|null $principal
 * @param callable(array<string,mixed>):array<string,mixed> $dispatcher
 * @return array{status_code:int,payload:array<string,mixed>}
 */
function app_no_code_public_runtime_execution_response_for_candidate(
    array $app,
    string $projectKey,
    array $candidate,
    string $requestMethod,
    array $post,
    ?array $principal,
    callable $dispatcher,
): array {
    $definitionResult = app_no_code_public_runtime_candidate_screen_definition($app, $projectKey, $candidate);
    if (!$definitionResult['ok']) {
        return app_no_code_runtime_execution_endpoint_response(
            app_no_code_runtime_execution_response_error($definitionResult['error']),
        );
    }

    $definition = $definitionResult['definition'];
    if ($principal !== null) {
        $policyDefinitionResult = app_no_code_screen_definition_from_project($app, $projectKey, $principal);
        if (!$policyDefinitionResult['ok']) {
            return app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error($policyDefinitionResult['error']),
            );
        }
        $definition = app_no_code_runtime_definition_with_action_policy_overlay(
            $definition,
            $policyDefinitionResult['definition'],
        );
    }

    $execution = app_no_code_runtime_execute_request_from_post(
        $definition,
        $requestMethod,
        $post,
        app_no_code_public_runtime_execution_binding($projectKey, $candidate),
        $dispatcher,
    );

    $response = app_no_code_runtime_execution_endpoint_response($execution);
    if (($response['payload']['ok'] ?? false) && app_no_code_public_runtime_demo_processing_requested($post)) {
        $response['payload']['demo_processing'] = app_no_code_public_runtime_demo_process_execution_outbox(
            $app,
            $projectKey,
            $response['payload'],
        );
    }

    return $response;
}

/**
 * @param array{
 *     request_id:string
 * } $request
 */
/**
 * @param array<string,mixed>|null $executionBinding
 */
function app_send_no_code_public_runtime_file_response(
    array $request,
    string $filePath,
    string $cacheControl,
    ?array $executionBinding = null,
): void
{
    $body = null;
    if ($executionBinding !== null) {
        $html = (string) file_get_contents($filePath);
        $body = app_no_code_public_runtime_preview_html_with_execution_binding($html, $executionBinding);
    }

    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Length: ' . (string) ($body !== null ? strlen($body) : filesize($filePath)));
    header('Cache-Control: ' . $cacheControl);
    header('X-Content-Type-Options: nosniff');
    header('X-Request-Id: ' . $request['request_id']);

    if ($body !== null) {
        echo $body;
        return;
    }

    if (readfile($filePath) === false) {
        throw new RuntimeException('public runtime preview の送信に失敗しました。');
    }
}

/**
 * @param array<string,mixed> $executionBinding
 */
function app_no_code_public_runtime_preview_html_with_execution_binding(string $html, array $executionBinding): string
{
    $script = '<script type="application/json" id="no-code-runtime-execution-binding">'
        . app_no_code_runtime_json_script_text($executionBinding)
        . '</script>';

    if (str_contains($html, '<script>')) {
        return str_replace('<script>', $script . "\n<script>", $html);
    }

    if (str_contains($html, '</body>')) {
        return str_replace('</body>', $script . "\n</body>", $html);
    }

    return $html . "\n" . $script . "\n";
}

/**
 * @param array<string,mixed> $candidate
 * @return array<string,string>
 */
function app_no_code_public_runtime_preview_execution_binding(
    string $projectKey,
    array $candidate,
    string $executionPath,
    ?string $dataPath = null,
): array {
    $binding = app_no_code_public_runtime_execution_binding($projectKey, $candidate);
    $binding['execution_url'] = $executionPath;
    if ($dataPath !== null && $dataPath !== '') {
        $binding['runtime_data_url'] = $dataPath;
    }
    return $binding;
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string
 * } $request
 * @param array<string,mixed> $candidate
 */
function app_send_no_code_public_runtime_candidate_preview_response(
    array $app,
    array $request,
    string $projectKey,
    array $candidate,
    string $cacheControl,
    ?string $executionPath = null,
    ?string $dataPath = null,
): bool {
    $artifactKey = (string) ($candidate['artifact_key'] ?? '');
    if (!app_project_output_artifact_key_is_valid($artifactKey)) {
        return false;
    }

    $artifactResult = app_project_output_find($app, $projectKey, $artifactKey);
    if (!$artifactResult['ok'] || $artifactResult['item'] === null) {
        return false;
    }

    $artifact = $artifactResult['item'];
    if ($artifact['source_output_key'] !== APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY) {
        return false;
    }

    try {
        $runtimeRoot = app_project_output_artifact_bundle_runtime_root($artifact);
    } catch (Throwable) {
        return false;
    }

    $previewPath = $runtimeRoot . '/runtime-preview.html';
    if (!is_file($previewPath)) {
        return false;
    }

    $executionBinding = $executionPath !== null
        ? app_no_code_public_runtime_preview_execution_binding($projectKey, $candidate, $executionPath, $dataPath)
        : null;
    app_send_no_code_public_runtime_file_response($request, $previewPath, $cacheControl, $executionBinding);
    return true;
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_preview_page(array $app, array $request): void
{
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

    $candidateResult = app_pdo_find_approved_no_code_publish_candidate_for_artifact($app, $projectKey, $artifactKey);
    if (!$candidateResult['ok']) {
        app_render_not_found_page($app, $request);
        return;
    }
    if ($candidateResult['item'] === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    if (!app_send_no_code_public_runtime_candidate_preview_response(
        $app,
        $request,
        $projectKey,
        $candidateResult['item'],
        app_no_code_public_runtime_artifact_cache_control(),
        null,
    )) {
        app_render_not_found_page($app, $request);
        return;
    }
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_execution_page(array $app, array $request): void
{
    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution project binding does not match'),
            )['payload'],
            409,
        );
        return;
    }

    $artifactKey = trim(app_route_param($request, 'artifact_key'));
    if (!app_project_output_artifact_key_is_valid($artifactKey)) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution artifact binding does not match'),
            )['payload'],
            409,
        );
        return;
    }

    $candidateResult = app_pdo_find_approved_no_code_publish_candidate_for_artifact($app, $projectKey, $artifactKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution artifact was not found'),
            )['payload'],
            422,
        );
        return;
    }

    $response = app_no_code_public_runtime_execution_response_for_candidate(
        $app,
        $projectKey,
        $candidateResult['item'],
        $request['method'],
        $_POST,
        app_auth_principal(),
        app_no_code_public_runtime_dispatcher($app),
    );

    app_send_json_response($request, $response['payload'], $response['status_code']);
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_current_execution_page(array $app, array $request): void
{
    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution project binding does not match'),
            )['payload'],
            409,
        );
        return;
    }

    $candidateResult = app_pdo_find_current_approved_no_code_publish_candidate($app, $projectKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution artifact was not found'),
            )['payload'],
            422,
        );
        return;
    }

    $response = app_no_code_public_runtime_execution_response_for_candidate(
        $app,
        $projectKey,
        $candidateResult['item'],
        $request['method'],
        $_POST,
        app_auth_principal(),
        app_no_code_public_runtime_dispatcher($app),
    );

    app_send_json_response($request, $response['payload'], $response['status_code']);
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_current_data_page(array $app, array $request): void
{
    if (!app_request_method_is($request, 'GET')) {
        app_send_json_response(
            $request,
            app_no_code_public_runtime_data_error_response('runtime data endpoint requires GET.', 405)['payload'],
            405,
        );
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_send_json_response(
            $request,
            app_no_code_public_runtime_data_error_response('runtime data project binding does not match.', 409)['payload'],
            409,
        );
        return;
    }

    $candidateResult = app_pdo_find_current_approved_no_code_publish_candidate($app, $projectKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_send_json_response(
            $request,
            app_no_code_public_runtime_data_error_response('runtime data artifact was not found.', 422)['payload'],
            422,
        );
        return;
    }

    $response = app_no_code_public_runtime_data_response_for_candidate(
        $app,
        $projectKey,
        $candidateResult['item'],
        'current',
    );
    app_send_json_response($request, $response['payload'], $response['status_code']);
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_alias_execution_page(array $app, array $request): void
{
    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution project binding does not match'),
            )['payload'],
            409,
        );
        return;
    }

    $aliasKey = app_no_code_public_runtime_normalize_alias_key(app_route_param($request, 'alias_key'));
    if (!app_no_code_public_runtime_alias_key_is_valid($aliasKey)) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution alias binding does not match'),
            )['payload'],
            409,
        );
        return;
    }

    $candidateResult = app_pdo_find_approved_no_code_publish_candidate_for_alias($app, $projectKey, $aliasKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution artifact was not found'),
            )['payload'],
            422,
        );
        return;
    }

    $response = app_no_code_public_runtime_execution_response_for_candidate(
        $app,
        $projectKey,
        $candidateResult['item'],
        $request['method'],
        $_POST,
        app_auth_principal(),
        app_no_code_public_runtime_dispatcher($app),
    );

    app_send_json_response($request, $response['payload'], $response['status_code']);
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_alias_data_page(array $app, array $request): void
{
    if (!app_request_method_is($request, 'GET')) {
        app_send_json_response(
            $request,
            app_no_code_public_runtime_data_error_response('runtime data endpoint requires GET.', 405)['payload'],
            405,
        );
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_send_json_response(
            $request,
            app_no_code_public_runtime_data_error_response('runtime data project binding does not match.', 409)['payload'],
            409,
        );
        return;
    }

    $aliasKey = app_no_code_public_runtime_normalize_alias_key(app_route_param($request, 'alias_key'));
    if (!app_no_code_public_runtime_alias_key_is_valid($aliasKey)) {
        app_send_json_response(
            $request,
            app_no_code_public_runtime_data_error_response('runtime data alias binding does not match.', 409)['payload'],
            409,
        );
        return;
    }

    $candidateResult = app_pdo_find_approved_no_code_publish_candidate_for_alias($app, $projectKey, $aliasKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_send_json_response(
            $request,
            app_no_code_public_runtime_data_error_response('runtime data artifact was not found.', 422)['payload'],
            422,
        );
        return;
    }

    $response = app_no_code_public_runtime_data_response_for_candidate(
        $app,
        $projectKey,
        $candidateResult['item'],
        'alias',
        $aliasKey,
    );
    app_send_json_response($request, $response['payload'], $response['status_code']);
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_current_preview_page(array $app, array $request): void
{
    if (!app_request_method_is($request, 'GET')) {
        app_render_method_not_allowed_page($app, $request, ['GET']);
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return;
    }

    $candidateResult = app_pdo_find_current_approved_no_code_publish_candidate($app, $projectKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    if (!app_send_no_code_public_runtime_candidate_preview_response(
        $app,
        $request,
        $projectKey,
        $candidateResult['item'],
        app_no_code_public_runtime_current_cache_control(),
        app_no_code_public_runtime_current_execution_path($projectKey),
        app_no_code_public_runtime_current_data_path($projectKey),
    )) {
        app_render_not_found_page($app, $request);
        return;
    }
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_alias_preview_page(array $app, array $request): void
{
    if (!app_request_method_is($request, 'GET')) {
        app_render_method_not_allowed_page($app, $request, ['GET']);
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return;
    }

    $aliasKey = app_no_code_public_runtime_normalize_alias_key(app_route_param($request, 'alias_key'));
    if (!app_no_code_public_runtime_alias_key_is_valid($aliasKey)) {
        app_render_bad_request_page($app, $request, 'alias key の形式が不正です。');
        return;
    }

    $candidateResult = app_pdo_find_approved_no_code_publish_candidate_for_alias($app, $projectKey, $aliasKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    if (!app_send_no_code_public_runtime_candidate_preview_response(
        $app,
        $request,
        $projectKey,
        $candidateResult['item'],
        app_no_code_public_runtime_current_cache_control(),
        app_no_code_public_runtime_alias_execution_path($projectKey, $aliasKey),
        app_no_code_public_runtime_alias_data_path($projectKey, $aliasKey),
    )) {
        app_render_not_found_page($app, $request);
        return;
    }
}
