<?php

declare(strict_types=1);

require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/generated_name.php';

/**
 * @return list<string>
 */
function app_project_output_runtime_sql_signature_parameter_names(string $signature): array
{
    if ($signature === '') {
        return [];
    }

    preg_match_all('/\$[A-Za-z_][A-Za-z0-9_]*/', $signature, $matches);
    if (!isset($matches[0]) || !is_array($matches[0])) {
        return [];
    }

    return array_values(
        array_map(
            static fn (string $value): string => trim($value),
            $matches[0],
        ),
    );
}

function app_project_output_runtime_sql_parameter_basename(string $parameterName): string
{
    return ltrim(trim($parameterName), '$');
}

function app_project_output_runtime_sql_parameter_name_matches_column(string $parameterName, string $columnName): bool
{
    $normalizedParameterName = strtolower(app_project_output_runtime_sql_parameter_basename($parameterName));
    $normalizedColumnName = strtolower(trim($columnName));
    if ($normalizedParameterName === '' || $normalizedColumnName === '') {
        return true;
    }

    $columnNameMap = app_generated_name_map_for_physical_name($columnName, 'class');
    $columnPropertyNameMap = app_generated_name_map_for_physical_name($columnName, 'php-property');
    foreach (array_unique([
        $normalizedColumnName,
        str_replace('_', '', $normalizedColumnName),
        strtolower($columnNameMap['generated_name']),
        strtolower($columnPropertyNameMap['generated_name']),
    ]) as $candidate) {
        if ($candidate !== '' && str_contains($normalizedParameterName, $candidate)) {
            return true;
        }
    }

    return false;
}

function app_project_output_runtime_sql_output_property_name(string $physicalColumnName): string
{
    $columnName = trim($physicalColumnName);
    if ($columnName === '' || !app_generated_name_policy_uses_physical_logical_names()) {
        return $columnName;
    }

    return app_generated_name_map_for_physical_name($columnName, 'php-property')['generated_name'];
}

function app_project_output_runtime_sql_output_data_class_base_name(string $physicalOrLogicalName): string
{
    $name = trim($physicalOrLogicalName);
    if ($name === '' || !app_generated_name_policy_uses_physical_logical_names()) {
        return $name;
    }

    return app_generated_name_map_for_physical_name($name, 'class')['generated_name'];
}

/**
 * @return list<string>
 */
function app_project_output_runtime_sql_known_helper_class_lines(string $sourceName): array
{
    return match ($sourceName) {
        'MySQLShowColumn' => [
            '    private $runtimeMySQLiObj = null;',
        ],
        default => [],
    };
}

function app_project_output_runtime_sql_normalize_token(string $value): string
{
    return strtolower(trim($value));
}

function app_project_output_runtime_sql_is_truthy_flag(string $value): bool
{
    return trim($value) === '1';
}

/**
 * @return array{
 *     ok:bool,
 *     value:string,
 *     reason:string
 * }
 */
function app_project_output_runtime_sql_resolve_or_group_type(string $value): array
{
    $normalizedValue = app_project_output_runtime_sql_normalize_token($value);

    return match ($normalizedValue) {
        '', 'default', 'orandor' => [
            'ok' => true,
            'value' => 'orandor',
            'reason' => '',
        ],
        'andorand' => [
            'ok' => true,
            'value' => 'andorand',
            'reason' => '',
        ],
        default => [
            'ok' => false,
            'value' => '',
            'reason' => 'or group type is not supported yet: ' . $value,
        ],
    };
}

/**
 * @param list<array{
 *     type:string,
 *     value:string
 * }> $parts
 */
function app_project_output_runtime_sql_concat_expression(array $parts): string
{
    $compiledParts = [];

    foreach ($parts as $part) {
        $type = $part['type'] ?? 'string';
        $value = (string) ($part['value'] ?? '');
        if ($type === 'code') {
            $compiledParts[] = $value;
            continue;
        }

        $compiledParts[] = var_export($value, true);
    }

    if ($compiledParts === []) {
        return "''";
    }

    return implode(' . ', $compiledParts);
}

/**
 * @param list<array<string,string>> $rows
 * @return list<array{
 *     or_group:string,
 *     rows:list<array<string,string>>
 * }>
 */
function app_project_output_runtime_sql_partition_where_rows_by_or_group(array $rows): array
{
    $partitions = [];
    $groupIndexes = [];

    foreach ($rows as $row) {
        $orGroup = trim((string) ($row['or_group'] ?? ''));
        if ($orGroup === '') {
            $partitions[] = [
                'or_group' => '',
                'rows' => [$row],
            ];
            continue;
        }

        if (!array_key_exists($orGroup, $groupIndexes)) {
            $groupIndexes[$orGroup] = count($partitions);
            $partitions[] = [
                'or_group' => $orGroup,
                'rows' => [],
            ];
        }

        $partitions[$groupIndexes[$orGroup]]['rows'][] = $row;
    }

    return $partitions;
}

/**
 * @param list<array<string,string>> $rows
 * @param callable(array<string,string>):array{
 *     ok:bool,
 *     parts:list<array{type:string,value:string}>,
 *     reason:string
 * } $compileRow
 * @param string $orGroupType
 * @return array{
 *     ok:bool,
 *     parts:list<array{type:string,value:string}>,
 *     reason:string
 * }
 */
function app_project_output_runtime_sql_grouped_condition_parts(
    array $rows,
    callable $compileRow,
    string $orGroupType = '',
): array
{
    $resolvedOrGroupType = app_project_output_runtime_sql_resolve_or_group_type($orGroupType);
    if (!$resolvedOrGroupType['ok']) {
        return [
            'ok' => false,
            'parts' => [],
            'reason' => $resolvedOrGroupType['reason'],
        ];
    }

    $blankRows = [];
    $groupedRowsByGroup = [];
    $groupOrder = [];

    foreach ($rows as $row) {
        $orGroup = trim((string) ($row['or_group'] ?? ''));
        if ($orGroup === '') {
            $blankRows[] = $row;
            continue;
        }

        if (!array_key_exists($orGroup, $groupedRowsByGroup)) {
            $groupedRowsByGroup[$orGroup] = [];
            $groupOrder[] = $orGroup;
        }

        $groupedRowsByGroup[$orGroup][] = $row;
    }

    $compileSequence = static function (array $sequenceRows, string $separator) use ($compileRow): array {
        $compiledParts = [];

        foreach ($sequenceRows as $rowIndex => $row) {
            if ($rowIndex > 0) {
                $compiledParts[] = [
                    'type' => 'string',
                    'value' => $separator,
                ];
            }

            $compiledRow = $compileRow($row);
            if (!$compiledRow['ok']) {
                return [
                    'ok' => false,
                    'parts' => [],
                    'reason' => $compiledRow['reason'],
                ];
            }

            foreach ($compiledRow['parts'] as $part) {
                $compiledParts[] = $part;
            }
        }

        return [
            'ok' => true,
            'parts' => $compiledParts,
            'reason' => '',
        ];
    };

    $parts = [];

    $blankPartsResult = $compileSequence($blankRows, ' and ');
    if (!$blankPartsResult['ok']) {
        return $blankPartsResult;
    }
    foreach ($blankPartsResult['parts'] as $part) {
        $parts[] = $part;
    }

    $groupedGroupParts = [];
    foreach ($groupOrder as $groupIndex => $groupKey) {
        $groupPartsResult = $compileSequence(
            $groupedRowsByGroup[$groupKey],
            $resolvedOrGroupType['value'] === 'andorand' ? ' and ' : ' or ',
        );
        if (!$groupPartsResult['ok']) {
            return $groupPartsResult;
        }

        if ($groupPartsResult['parts'] === []) {
            continue;
        }

        if ($groupIndex > 0) {
            $groupedGroupParts[] = [
                'type' => 'string',
                'value' => $resolvedOrGroupType['value'] === 'andorand' ? ' or ' : ' and ',
            ];
        }

        $groupedGroupParts[] = [
            'type' => 'string',
            'value' => '(',
        ];
        foreach ($groupPartsResult['parts'] as $part) {
            $groupedGroupParts[] = $part;
        }
        $groupedGroupParts[] = [
            'type' => 'string',
            'value' => ')',
        ];
    }

    if ($groupedGroupParts !== []) {
        if ($parts !== []) {
            $parts[] = [
                'type' => 'string',
                'value' => ' and ',
            ];
            if ($resolvedOrGroupType['value'] === 'andorand' && count($groupOrder) > 1) {
                $parts[] = [
                    'type' => 'string',
                    'value' => '(',
                ];
            }
        }

        foreach ($groupedGroupParts as $part) {
            $parts[] = $part;
        }

        if (
            $blankRows !== []
            && $resolvedOrGroupType['value'] === 'andorand'
            && count($groupOrder) > 1
        ) {
            $parts[] = [
                'type' => 'string',
                'value' => ')',
            ];
        }
    }

    return [
        'ok' => true,
        'parts' => $parts,
        'reason' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     parts:list<array{
 *         type:string,
 *         value:string
 *     }>,
 *     reason:string
 * }
 */
function app_project_output_runtime_sql_value_parts(
    string $parameterType,
    string $parameterDataType,
    string $valueExpression,
    string $fixedParameter = '',
): array {
    $normalizedParameterType = app_project_output_runtime_sql_normalize_token($parameterType);
    if ($normalizedParameterType === '') {
        $normalizedParameterType = 'argument';
    }

    $normalizedParameterDataType = app_project_output_runtime_sql_normalize_token($parameterDataType);
    if ($normalizedParameterDataType === '') {
        $normalizedParameterDataType = 'default';
    }

    if ($normalizedParameterDataType === 'file') {
        // Legacy blob generation paired a bare "?" with prepare/bind_param("b")
        // and send_long_data() against a file path stored on the DTO property.
        // Keep this defensive guard even though IsBlobTarget functions delegate
        // earlier, because a partial metadata import should not fall through into
        // plain string concatenation for blob writes.
        return [
            'ok' => false,
            'parts' => [],
            'reason' => 'file parameter data type is not supported yet',
        ];
    }

    if ($normalizedParameterType === 'argument') {
        if ($valueExpression === '') {
            return [
                'ok' => false,
                'parts' => [],
                'reason' => 'argument value expression is empty',
            ];
        }

        if ($normalizedParameterDataType === 'raw') {
            return [
                'ok' => true,
                'parts' => [
                    [
                        'type' => 'code',
                        'value' => $valueExpression,
                    ],
                ],
                'reason' => '',
            ];
        }

        return [
            'ok' => true,
            'parts' => [
                [
                    'type' => 'string',
                    'value' => "'",
                ],
                [
                    'type' => 'code',
                    'value' => '$mtooldb->real_escape_string(' . $valueExpression . ')',
                ],
                [
                    'type' => 'string',
                    'value' => "'",
                ],
            ],
            'reason' => '',
        ];
    }

    if ($normalizedParameterType === 'fixed') {
        if ($normalizedParameterDataType === 'raw') {
            return [
                'ok' => true,
                'parts' => [
                    [
                        'type' => 'string',
                        'value' => $fixedParameter,
                    ],
                ],
                'reason' => '',
            ];
        }

        $fixedLiteral = var_export($fixedParameter, true);

        return [
            'ok' => true,
            'parts' => [
                [
                    'type' => 'string',
                    'value' => "'",
                ],
                [
                    'type' => 'code',
                    'value' => '$mtooldb->real_escape_string(' . $fixedLiteral . ')',
                ],
                [
                    'type' => 'string',
                    'value' => "'",
                ],
            ],
            'reason' => '',
        ];
    }

    return [
        'ok' => false,
        'parts' => [],
        'reason' => 'unsupported parameter type: ' . $parameterType,
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     sql_fragment:string,
 *     param_expression:string,
 *     uses_param:bool,
 *     reason:string
 * }
 */
function app_project_output_runtime_sql_prepared_value(
    string $parameterType,
    string $parameterDataType,
    string $valueExpression,
    string $fixedParameter = '',
): array {
    $normalizedParameterType = app_project_output_runtime_sql_normalize_token($parameterType);
    if ($normalizedParameterType === '') {
        $normalizedParameterType = 'argument';
    }

    $normalizedParameterDataType = app_project_output_runtime_sql_normalize_token($parameterDataType);
    if ($normalizedParameterDataType === '') {
        $normalizedParameterDataType = 'default';
    }

    if ($normalizedParameterDataType === 'file') {
        return [
            'ok' => false,
            'sql_fragment' => '',
            'param_expression' => '',
            'uses_param' => false,
            'reason' => 'file parameter data type is not supported yet',
        ];
    }

    if ($normalizedParameterDataType === 'raw') {
        if ($normalizedParameterType === 'fixed') {
            return [
                'ok' => true,
                'sql_fragment' => $fixedParameter,
                'param_expression' => '',
                'uses_param' => false,
                'reason' => '',
            ];
        }

        return [
            'ok' => false,
            'sql_fragment' => '',
            'param_expression' => '',
            'uses_param' => false,
            'reason' => 'raw argument data type is not supported by prepared SQL generation yet',
        ];
    }

    if ($normalizedParameterType === 'argument') {
        if ($valueExpression === '') {
            return [
                'ok' => false,
                'sql_fragment' => '',
                'param_expression' => '',
                'uses_param' => false,
                'reason' => 'argument value expression is empty',
            ];
        }

        return [
            'ok' => true,
            'sql_fragment' => '?',
            'param_expression' => $valueExpression,
            'uses_param' => true,
            'reason' => '',
        ];
    }

    if ($normalizedParameterType === 'fixed') {
        return [
            'ok' => true,
            'sql_fragment' => '?',
            'param_expression' => var_export($fixedParameter, true),
            'uses_param' => true,
            'reason' => '',
        ];
    }

    return [
        'ok' => false,
        'sql_fragment' => '',
        'param_expression' => '',
        'uses_param' => false,
        'reason' => 'unsupported parameter type: ' . $parameterType,
    ];
}

/**
 * @param list<string> $paramExpressions
 * @return list<string>
 */
function app_project_output_runtime_sql_prepared_write_method_body_lines(
    string $sql,
    array $paramExpressions,
): array {
    return [
        '        global $mtooldb, $last_sql_command_for_mtooldb;',
        '        connect_mtooldb_if_not_yet();',
        '        reconnect_mtooldb_if_necessary();',
        '',
        '        $last_sql_command_for_mtooldb = ' . var_export($sql, true) . ';',
        '        $result = $mtooldb->execute($last_sql_command_for_mtooldb, [',
        ...array_map(
            static fn (string $paramExpression): string => '            ' . $paramExpression . ',',
            $paramExpressions,
        ),
        '        ]);',
        '        if ($mtooldb->errno != 0) {',
        '            error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);',
        '        }',
        '        return $result;',
    ];
}

/**
 * @param list<string> $paramExpressions
 * @param list<array<string,string>> $selectTargetFields
 * @return list<string>
 */
function app_project_output_runtime_sql_prepared_select_method_body_lines(
    string $sql,
    array $paramExpressions,
    bool $isList,
    string $dataClassName,
    array $selectTargetFields,
): array {
    $bodyLines = [
        '        global $mtooldb, $last_sql_command_for_mtooldb;',
        '        connect_mtooldb_if_not_yet();',
        '        reconnect_mtooldb_if_necessary();',
        '',
    ];

    if ($isList) {
        $bodyLines[] = '        $result = array();';
        $bodyLines[] = '';
    }

    $bodyLines[] = '        $last_sql_command_for_mtooldb = ' . var_export($sql, true) . ';';
    $bodyLines[] = '        $ret = $mtooldb->execute($last_sql_command_for_mtooldb, [';
    foreach ($paramExpressions as $paramExpression) {
        $bodyLines[] = '            ' . $paramExpression . ',';
    }
    $bodyLines[] = '        ]);';
    $bodyLines[] = '        if ($mtooldb->errno != 0) {';
    $bodyLines[] = '            error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);';
    $bodyLines[] = '            return $ret;';
    $bodyLines[] = '        }';
    $bodyLines[] = '        while($thisline=$ret->fetch_row()) {';
    $bodyLines[] = '            $thisresult = new ' . $dataClassName . '();';

    foreach (array_values($selectTargetFields) as $index => $field) {
        $storeClassFieldName = trim((string) ($field['store_class_field_name'] ?? ''));
        if ($storeClassFieldName === '') {
            $storeClassFieldName = app_project_output_runtime_sql_output_property_name(
                trim((string) ($field['target_table_column_name'] ?? '')),
            );
        }

        $bodyLines[] = '            $thisresult->' . $storeClassFieldName . ' = $thisline[' . $index . '];';
    }

    if ($isList) {
        $bodyLines[] = '            array_push($result, $thisresult);';
        $bodyLines[] = '        }';
        $bodyLines[] = '        return $result;';
    } else {
        $bodyLines[] = '            return $thisresult;';
        $bodyLines[] = '        }';
        $bodyLines[] = '        return NULL;';
    }

    return $bodyLines;
}

/**
 * @param list<array{type:string,value:string}> $parts
 * @return array{ok:bool,sql:string,reason:string}
 */
function app_project_output_runtime_sql_static_sql_from_parts(array $parts): array
{
    $sql = '';
    foreach ($parts as $part) {
        if (($part['type'] ?? 'string') !== 'string') {
            return [
                'ok' => false,
                'sql' => '',
                'reason' => 'prepared SQL contains dynamic fragments',
            ];
        }

        $sql .= (string) ($part['value'] ?? '');
    }

    return [
        'ok' => true,
        'sql' => $sql,
        'reason' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     mode:string,
 *     next_scalar_index:int,
 *     value_expression:string,
 *     reason:string
 * }
 */
function app_project_output_runtime_sql_resolve_write_argument_expression(
    array $parameterNames,
    string $bindingMode,
    int $scalarIndex,
    string $columnName,
    bool $forWhere,
): array {
    if ($bindingMode === 'object') {
        if (!isset($parameterNames[0])) {
            return [
                'ok' => false,
                'mode' => $bindingMode,
                'next_scalar_index' => $scalarIndex,
                'value_expression' => '',
                'reason' => 'object parameter is missing',
            ];
        }

        return [
            'ok' => true,
            'mode' => $bindingMode,
            'next_scalar_index' => $scalarIndex,
            'value_expression' => $parameterNames[0] . '->' . app_project_output_runtime_sql_output_property_name($columnName),
            'reason' => '',
        ];
    }

    if ($bindingMode === 'object-set-where-values' && !$forWhere) {
        if (!isset($parameterNames[0])) {
            return [
                'ok' => false,
                'mode' => $bindingMode,
                'next_scalar_index' => $scalarIndex,
                'value_expression' => '',
                'reason' => 'set-object parameter is missing',
            ];
        }

        return [
            'ok' => true,
            'mode' => $bindingMode,
            'next_scalar_index' => $scalarIndex,
            'value_expression' => $parameterNames[0] . '->' . app_project_output_runtime_sql_output_property_name($columnName),
            'reason' => '',
        ];
    }

    if (!isset($parameterNames[$scalarIndex])) {
        return [
            'ok' => false,
            'mode' => $bindingMode,
            'next_scalar_index' => $scalarIndex,
            'value_expression' => '',
            'reason' => 'not enough scalar arguments',
        ];
    }

    $parameterName = $parameterNames[$scalarIndex];
    if (
        $columnName !== ''
        && count($parameterNames) > 1
        && !app_project_output_runtime_sql_parameter_name_matches_column($parameterName, $columnName)
    ) {
        return [
            'ok' => false,
            'mode' => $bindingMode,
            'next_scalar_index' => $scalarIndex,
            'value_expression' => '',
            'reason' => 'argument name does not match target column: '
                . app_project_output_runtime_sql_parameter_basename($parameterName)
                . ' vs '
                . $columnName,
        ];
    }

    return [
        'ok' => true,
        'mode' => $bindingMode,
        'next_scalar_index' => $scalarIndex + 1,
        'value_expression' => $parameterName,
        'reason' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     mode:string,
 *     initial_scalar_index:int,
 *     reason:string
 * }
 */
function app_project_output_runtime_sql_resolve_write_binding_mode(
    string $actionType,
    string $parameterType,
    array $parameterNames,
): array {
    $normalizedParameterType = app_project_output_runtime_sql_normalize_token($parameterType);
    if ($normalizedParameterType === 'classobject') {
        return [
            'ok' => isset($parameterNames[0]),
            'mode' => 'object',
            'initial_scalar_index' => 0,
            'reason' => isset($parameterNames[0]) ? '' : 'classobject parameter is missing',
        ];
    }

    if ($normalizedParameterType === 'val') {
        return [
            'ok' => true,
            'mode' => 'values',
            'initial_scalar_index' => 0,
            'reason' => '',
        ];
    }

    if ($normalizedParameterType === 'setbyclassobjectandwherebyvalforupdate') {
        if (!isset($parameterNames[0]) || count($parameterNames) < 2) {
            return [
                'ok' => false,
                'mode' => 'object-set-where-values',
                'initial_scalar_index' => 1,
                'reason' => 'set-by-object update requires object + scalar where arguments',
            ];
        }

        return [
            'ok' => true,
            'mode' => 'object-set-where-values',
            'initial_scalar_index' => 1,
            'reason' => '',
        ];
    }

    if ($parameterNames === []) {
        return [
            'ok' => true,
            'mode' => 'values',
            'initial_scalar_index' => 0,
            'reason' => '',
        ];
    }

    $firstParameterBaseName = app_project_output_runtime_sql_parameter_basename($parameterNames[0]);
    $looksLikeObjectParameter = preg_match('/obj$/i', $firstParameterBaseName) === 1;

    if ($looksLikeObjectParameter && count($parameterNames) === 1) {
        return [
            'ok' => true,
            'mode' => 'object',
            'initial_scalar_index' => 0,
            'reason' => '',
        ];
    }

    if ($looksLikeObjectParameter && strtoupper($actionType) === 'UPDATE' && count($parameterNames) > 1) {
        return [
            'ok' => true,
            'mode' => 'object-set-where-values',
            'initial_scalar_index' => 1,
            'reason' => '',
        ];
    }

    return [
        'ok' => true,
        'mode' => 'values',
        'initial_scalar_index' => 0,
        'reason' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     select_wheres:list<array<string,string>>,
 *     select_target_fields:list<array<string,string>>,
 *     select_havings:list<array<string,string>>,
 *     insert_target_fields:list<array<string,string>>,
 *     update_target_fields:list<array<string,string>>,
 *     update_delete_wheres:list<array<string,string>>,
 *     error:string
 * }
 */
function app_project_output_runtime_sql_fetch_designer_resources(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    $selectWhereResult = app_fetch_db_access_function_select_where_catalog($app, $projectKey, $sourceName, $functionName);
    if (!$selectWhereResult['ok']) {
        return [
            'ok' => false,
            'select_wheres' => [],
            'select_target_fields' => [],
            'select_havings' => [],
            'insert_target_fields' => [],
            'update_target_fields' => [],
            'update_delete_wheres' => [],
            'error' => 'select where catalog: ' . $selectWhereResult['error'],
        ];
    }

    $selectTargetFieldResult = app_fetch_db_access_function_select_target_field_catalog(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
    );
    if (!$selectTargetFieldResult['ok']) {
        return [
            'ok' => false,
            'select_wheres' => [],
            'select_target_fields' => [],
            'select_havings' => [],
            'insert_target_fields' => [],
            'update_target_fields' => [],
            'update_delete_wheres' => [],
            'error' => 'select target field catalog: ' . $selectTargetFieldResult['error'],
        ];
    }

    $selectHavingResult = app_fetch_db_access_function_select_having_catalog($app, $projectKey, $sourceName, $functionName);
    if (!$selectHavingResult['ok']) {
        return [
            'ok' => false,
            'select_wheres' => [],
            'select_target_fields' => [],
            'select_havings' => [],
            'insert_target_fields' => [],
            'update_target_fields' => [],
            'update_delete_wheres' => [],
            'error' => 'select having catalog: ' . $selectHavingResult['error'],
        ];
    }

    $insertTargetFieldResult = app_fetch_db_access_function_insert_target_field_catalog(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
    );
    if (!$insertTargetFieldResult['ok']) {
        return [
            'ok' => false,
            'select_wheres' => [],
            'select_target_fields' => [],
            'select_havings' => [],
            'insert_target_fields' => [],
            'update_target_fields' => [],
            'update_delete_wheres' => [],
            'error' => 'insert target field catalog: ' . $insertTargetFieldResult['error'],
        ];
    }

    $updateTargetFieldResult = app_fetch_db_access_function_update_target_field_catalog(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
    );
    if (!$updateTargetFieldResult['ok']) {
        return [
            'ok' => false,
            'select_wheres' => [],
            'select_target_fields' => [],
            'select_havings' => [],
            'insert_target_fields' => [],
            'update_target_fields' => [],
            'update_delete_wheres' => [],
            'error' => 'update target field catalog: ' . $updateTargetFieldResult['error'],
        ];
    }

    $updateDeleteWhereResult = app_fetch_db_access_function_update_delete_where_catalog(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
    );
    if (!$updateDeleteWhereResult['ok']) {
        return [
            'ok' => false,
            'select_wheres' => [],
            'select_target_fields' => [],
            'select_havings' => [],
            'insert_target_fields' => [],
            'update_target_fields' => [],
            'update_delete_wheres' => [],
            'error' => 'update/delete where catalog: ' . $updateDeleteWhereResult['error'],
        ];
    }

    return [
        'ok' => true,
        'select_wheres' => $selectWhereResult['items'],
        'select_target_fields' => $selectTargetFieldResult['items'],
        'select_havings' => $selectHavingResult['items'],
        'insert_target_fields' => $insertTargetFieldResult['items'],
        'update_target_fields' => $updateTargetFieldResult['items'],
        'update_delete_wheres' => $updateDeleteWhereResult['items'],
        'error' => '',
    ];
}

function app_project_output_runtime_sql_base_table_reference(array $field): string
{
    $tableName = trim((string) ($field['target_table_name'] ?? ''));
    $aliasName = trim((string) ($field['target_table_alias_name'] ?? ''));
    if ($tableName === '') {
        return '';
    }

    if ($aliasName === '') {
        return $tableName;
    }

    return $tableName . ' as ' . $aliasName;
}

function app_project_output_runtime_sql_table_qualifier(array $field): string
{
    $tableName = trim((string) ($field['target_table_name'] ?? ''));
    $aliasName = trim((string) ($field['target_table_alias_name'] ?? ''));

    return $aliasName !== '' ? $aliasName : $tableName;
}

function app_project_output_runtime_sql_select_field_expression(array $field): string
{
    $prefix = (string) ($field['target_table_column_prefix'] ?? '');
    $suffix = (string) ($field['target_table_column_suffix'] ?? '');
    $columnName = trim((string) ($field['target_table_column_name'] ?? ''));
    $qualifier = app_project_output_runtime_sql_table_qualifier($field);

    return $prefix . $qualifier . '.' . $columnName . $suffix;
}

/**
 * @param list<array<string,string>> $selectTargetFields
 * @return array<string,array<string,string>>
 */
function app_project_output_runtime_sql_select_target_field_map(array $selectTargetFields): array
{
    $fieldsById = [];

    foreach ($selectTargetFields as $field) {
        $fieldId = trim((string) ($field['select_target_field_id'] ?? ''));
        if ($fieldId === '' || $fieldId === '0') {
            continue;
        }

        $fieldsById[$fieldId] = $field;
    }

    return $fieldsById;
}

/**
 * @param array<string,array<string,string>> $selectTargetFieldById
 * @return array{
 *     ok:bool,
 *     expression:string,
 *     reason:string
 * }
 */
function app_project_output_runtime_sql_select_target_field_expression_by_id(
    array $selectTargetFieldById,
    string $fieldId,
    string $label = 'select target field',
): array {
    $normalizedFieldId = trim($fieldId);
    if ($normalizedFieldId === '' || $normalizedFieldId === '0') {
        return [
            'ok' => false,
            'expression' => '',
            'reason' => $label . ' is blank',
        ];
    }

    $field = $selectTargetFieldById[$normalizedFieldId] ?? null;
    if (!is_array($field)) {
        return [
            'ok' => false,
            'expression' => '',
            'reason' => $label . ' is unknown: ' . $normalizedFieldId,
        ];
    }

    if (trim((string) ($field['target_table_name'] ?? '')) === '') {
        return [
            'ok' => false,
            'expression' => '',
            'reason' => $label . ' table is blank',
        ];
    }

    if (trim((string) ($field['target_table_column_name'] ?? '')) === '') {
        return [
            'ok' => false,
            'expression' => '',
            'reason' => $label . ' column is blank',
        ];
    }

    return [
        'ok' => true,
        'expression' => app_project_output_runtime_sql_select_field_expression($field),
        'reason' => '',
    ];
}

/**
 * @param array<string,array<string,string>> $selectTargetFieldById
 * @param list<string> $parameterNames
 * @return array{
 *     ok:bool,
 *     parts:list<array{type:string,value:string}>,
 *     reason:string
 * }
 */
function app_project_output_runtime_sql_compile_select_having_parts(
    array $row,
    array $selectTargetFieldById,
    array $parameterNames,
    int &$argumentIndex,
): array {
    $leftExpressionResult = app_project_output_runtime_sql_select_target_field_expression_by_id(
        $selectTargetFieldById,
        (string) ($row['left_target_field_id'] ?? ''),
        'select having left target field',
    );
    if (!$leftExpressionResult['ok']) {
        return [
            'ok' => false,
            'parts' => [],
            'reason' => $leftExpressionResult['reason'],
        ];
    }

    $operator = trim((string) ($row['relational_operator'] ?? ''));
    if ($operator === '') {
        $operator = '=';
    }

    $parts = [
        [
            'type' => 'string',
            'value' => (string) ($row['left_target_prefix'] ?? '')
                . $leftExpressionResult['expression']
                . (string) ($row['left_target_suffix'] ?? '')
                . ' '
                . $operator
                . ' '
                . (string) ($row['right_target_prefix'] ?? ''),
        ],
    ];

    $parameterType = app_project_output_runtime_sql_normalize_token((string) ($row['right_parameter_type'] ?? ''));
    if ($parameterType === '') {
        $parameterType = 'argument';
    }

    if ($parameterType === 'field') {
        $rightExpressionResult = app_project_output_runtime_sql_select_target_field_expression_by_id(
            $selectTargetFieldById,
            (string) ($row['right_target_field_id'] ?? ''),
            'select having right target field',
        );
        if (!$rightExpressionResult['ok']) {
            return [
                'ok' => false,
                'parts' => [],
                'reason' => $rightExpressionResult['reason'],
            ];
        }

        $parts[] = [
            'type' => 'string',
            'value' => $rightExpressionResult['expression'],
        ];
    } else {
        if ($parameterType === 'fixed') {
            $valuePartsResult = app_project_output_runtime_sql_value_parts(
                'fixed',
                (string) ($row['right_parameter_data_type'] ?? ''),
                '',
                (string) ($row['right_fixed_parameter'] ?? ''),
            );
        } elseif ($parameterType === 'argument') {
            if (!isset($parameterNames[$argumentIndex])) {
                return [
                    'ok' => false,
                    'parts' => [],
                    'reason' => 'select having argument count does not match signature',
                ];
            }

            $parameterName = $parameterNames[$argumentIndex];
            $argumentIndex++;
            $valuePartsResult = app_project_output_runtime_sql_value_parts(
                'argument',
                (string) ($row['right_parameter_data_type'] ?? ''),
                $parameterName,
            );
        } else {
            return [
                'ok' => false,
                'parts' => [],
                'reason' => 'select having parameter type is not supported yet: ' . $parameterType,
            ];
        }

        if (!$valuePartsResult['ok']) {
            return [
                'ok' => false,
                'parts' => [],
                'reason' => $valuePartsResult['reason'],
            ];
        }

        foreach ($valuePartsResult['parts'] as $part) {
            $parts[] = $part;
        }
    }

    $rightTargetSuffix = (string) ($row['right_target_suffix'] ?? '');
    if ($rightTargetSuffix !== '') {
        $parts[] = [
            'type' => 'string',
            'value' => $rightTargetSuffix,
        ];
    }

    return [
        'ok' => true,
        'parts' => $parts,
        'reason' => '',
    ];
}

/**
 * @param array<string,array<string,string>> $selectTargetFieldById
 * @param list<string> $parameterNames
 * @param list<string> $paramExpressions
 * @return array{
 *     ok:bool,
 *     parts:list<array{type:string,value:string}>,
 *     reason:string
 * }
 */
function app_project_output_runtime_sql_compile_select_having_prepared_parts(
    array $row,
    array $selectTargetFieldById,
    array $parameterNames,
    int &$argumentIndex,
    array &$paramExpressions,
): array {
    $leftExpressionResult = app_project_output_runtime_sql_select_target_field_expression_by_id(
        $selectTargetFieldById,
        (string) ($row['left_target_field_id'] ?? ''),
        'select having left target field',
    );
    if (!$leftExpressionResult['ok']) {
        return [
            'ok' => false,
            'parts' => [],
            'reason' => $leftExpressionResult['reason'],
        ];
    }

    $operator = trim((string) ($row['relational_operator'] ?? ''));
    if ($operator === '') {
        $operator = '=';
    }

    $parts = [
        [
            'type' => 'string',
            'value' => (string) ($row['left_target_prefix'] ?? '')
                . $leftExpressionResult['expression']
                . (string) ($row['left_target_suffix'] ?? '')
                . ' '
                . $operator
                . ' '
                . (string) ($row['right_target_prefix'] ?? ''),
        ],
    ];

    $parameterType = app_project_output_runtime_sql_normalize_token((string) ($row['right_parameter_type'] ?? ''));
    if ($parameterType === '') {
        $parameterType = 'argument';
    }

    if ($parameterType === 'field') {
        $rightExpressionResult = app_project_output_runtime_sql_select_target_field_expression_by_id(
            $selectTargetFieldById,
            (string) ($row['right_target_field_id'] ?? ''),
            'select having right target field',
        );
        if (!$rightExpressionResult['ok']) {
            return [
                'ok' => false,
                'parts' => [],
                'reason' => $rightExpressionResult['reason'],
            ];
        }

        $parts[] = [
            'type' => 'string',
            'value' => $rightExpressionResult['expression'],
        ];
    } elseif ($parameterType === 'fixed') {
        $resolvedValue = app_project_output_runtime_sql_prepared_value(
            'fixed',
            (string) ($row['right_parameter_data_type'] ?? ''),
            '',
            (string) ($row['right_fixed_parameter'] ?? ''),
        );
        if (!$resolvedValue['ok']) {
            return [
                'ok' => false,
                'parts' => [],
                'reason' => $resolvedValue['reason'],
            ];
        }

        $parts[] = [
            'type' => 'string',
            'value' => $resolvedValue['sql_fragment'],
        ];
        if ($resolvedValue['uses_param']) {
            $paramExpressions[] = $resolvedValue['param_expression'];
        }
    } elseif ($parameterType === 'argument') {
        if (!isset($parameterNames[$argumentIndex])) {
            return [
                'ok' => false,
                'parts' => [],
                'reason' => 'select having argument count does not match signature',
            ];
        }

        $parameterName = $parameterNames[$argumentIndex];
        $argumentIndex++;
        $resolvedValue = app_project_output_runtime_sql_prepared_value(
            'argument',
            (string) ($row['right_parameter_data_type'] ?? ''),
            $parameterName,
        );
        if (!$resolvedValue['ok']) {
            return [
                'ok' => false,
                'parts' => [],
                'reason' => $resolvedValue['reason'],
            ];
        }

        $parts[] = [
            'type' => 'string',
            'value' => $resolvedValue['sql_fragment'],
        ];
        if ($resolvedValue['uses_param']) {
            $paramExpressions[] = $resolvedValue['param_expression'];
        }
    } else {
        return [
            'ok' => false,
            'parts' => [],
            'reason' => 'select having parameter type is not supported yet: ' . $parameterType,
        ];
    }

    $rightTargetSuffix = (string) ($row['right_target_suffix'] ?? '');
    if ($rightTargetSuffix !== '') {
        $parts[] = [
            'type' => 'string',
            'value' => $rightTargetSuffix,
        ];
    }

    return [
        'ok' => true,
        'parts' => $parts,
        'reason' => '',
    ];
}

function app_project_output_runtime_sql_table_reference_key(string $tableName, string $aliasName): string
{
    return strtolower(trim($tableName)) . '|' . strtolower(trim($aliasName));
}

function app_project_output_runtime_sql_table_reference_sql(string $tableName, string $aliasName): string
{
    $normalizedTableName = trim($tableName);
    $normalizedAliasName = trim($aliasName);
    if ($normalizedTableName === '') {
        return '';
    }

    if ($normalizedAliasName === '') {
        return $normalizedTableName;
    }

    return $normalizedTableName . ' as ' . $normalizedAliasName;
}

function app_project_output_runtime_sql_table_reference_qualifier(string $tableName, string $aliasName): string
{
    $normalizedAliasName = trim($aliasName);
    if ($normalizedAliasName !== '') {
        return $normalizedAliasName;
    }

    return trim($tableName);
}

/**
 * @return array{
 *     ok:bool,
 *     key:string,
 *     sql:string,
 *     qualifier:string,
 *     table_name:string,
 *     alias_name:string,
 *     reason:string
 * }
 */
function app_project_output_runtime_sql_target_table_reference(array $field): array
{
    $tableName = trim((string) ($field['target_table_name'] ?? ''));
    $aliasName = trim((string) ($field['target_table_alias_name'] ?? ''));
    if ($tableName === '') {
        return [
            'ok' => false,
            'key' => '',
            'sql' => '',
            'qualifier' => '',
            'table_name' => '',
            'alias_name' => '',
            'reason' => 'target table is not defined',
        ];
    }

    return [
        'ok' => true,
        'key' => app_project_output_runtime_sql_table_reference_key($tableName, $aliasName),
        'sql' => app_project_output_runtime_sql_table_reference_sql($tableName, $aliasName),
        'qualifier' => app_project_output_runtime_sql_table_reference_qualifier($tableName, $aliasName),
        'table_name' => $tableName,
        'alias_name' => $aliasName,
        'reason' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     key:string,
 *     sql:string,
 *     qualifier:string,
 *     table_name:string,
 *     alias_name:string,
 *     reason:string
 * }
 */
function app_project_output_runtime_sql_another_table_reference(array $field): array
{
    $tableName = trim((string) ($field['another_table_name'] ?? ''));
    $aliasName = trim((string) ($field['another_table_alias_name'] ?? ''));
    if ($tableName === '') {
        return [
            'ok' => false,
            'key' => '',
            'sql' => '',
            'qualifier' => '',
            'table_name' => '',
            'alias_name' => '',
            'reason' => 'another table is not defined',
        ];
    }

    return [
        'ok' => true,
        'key' => app_project_output_runtime_sql_table_reference_key($tableName, $aliasName),
        'sql' => app_project_output_runtime_sql_table_reference_sql($tableName, $aliasName),
        'qualifier' => app_project_output_runtime_sql_table_reference_qualifier($tableName, $aliasName),
        'table_name' => $tableName,
        'alias_name' => $aliasName,
        'reason' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     sql:string,
 *     reason:string
 * }
 */
function app_project_output_runtime_sql_join_keyword(string $joinType): array
{
    $normalizedJoinType = app_project_output_runtime_sql_normalize_token($joinType);

    return match ($normalizedJoinType) {
        '', 'inner' => [
            'ok' => true,
            'sql' => 'join',
            'reason' => '',
        ],
        'left' => [
            'ok' => true,
            'sql' => 'left outer join',
            'reason' => '',
        ],
        'right' => [
            'ok' => true,
            'sql' => 'right outer join',
            'reason' => '',
        ],
        default => [
            'ok' => false,
            'sql' => '',
            'reason' => 'unsupported join type: ' . $joinType,
        ],
    };
}

function app_project_output_runtime_sql_swap_join_type(string $joinType): string
{
    $normalizedJoinType = app_project_output_runtime_sql_normalize_token($joinType);

    return match ($normalizedJoinType) {
        'left' => 'right',
        'right' => 'left',
        default => $normalizedJoinType,
    };
}

/**
 * @return array{
 *     ok:bool,
 *     sql:string,
 *     reason:string
 * }
 */
function app_project_output_runtime_sql_join_keyword_for_row(array $row, bool $newSideIsAnother): array
{
    $joinType = (string) ($row['join_type'] ?? '');
    if ($newSideIsAnother) {
        return app_project_output_runtime_sql_join_keyword($joinType);
    }

    return app_project_output_runtime_sql_join_keyword(
        app_project_output_runtime_sql_swap_join_type($joinType),
    );
}

function app_project_output_runtime_sql_anotherfield_condition_sql(array $row): string
{
    $targetQualifier = app_project_output_runtime_sql_table_reference_qualifier(
        (string) ($row['target_table_name'] ?? ''),
        (string) ($row['target_table_alias_name'] ?? ''),
    );
    $anotherQualifier = app_project_output_runtime_sql_table_reference_qualifier(
        (string) ($row['another_table_name'] ?? ''),
        (string) ($row['another_table_alias_name'] ?? ''),
    );
    $operator = trim((string) ($row['relational_operator'] ?? ''));
    if ($operator === '') {
        $operator = '=';
    }

    return $targetQualifier
        . '.'
        . trim((string) ($row['target_table_column_name'] ?? ''))
        . ' '
        . $operator
        . ' '
        . $anotherQualifier
        . '.'
        . trim((string) ($row['another_field_name'] ?? ''));
}

/**
 * @param list<array<string,string>> $selectTargetFields
 * @param list<array<string,string>> $selectWheres
 * @param string $orGroupType
 * @return array{
 *     ok:bool,
 *     from_sql:string,
 *     where_rows:list<array<string,string>>,
 *     reason:string
 * }
 */
function app_project_output_runtime_sql_build_select_from_sql(
    array $selectTargetFields,
    array $selectWheres,
    string $orGroupType = '',
): array {
    if ($selectTargetFields === []) {
        return [
            'ok' => false,
            'from_sql' => '',
            'where_rows' => [],
            'reason' => 'select target fields are empty',
        ];
    }

    $baseReference = app_project_output_runtime_sql_target_table_reference($selectTargetFields[0]);
    if (!$baseReference['ok']) {
        return [
            'ok' => false,
            'from_sql' => '',
            'where_rows' => [],
            'reason' => $baseReference['reason'],
        ];
    }

    $requiredReferences = [
        $baseReference['key'] => $baseReference,
    ];

    foreach ($selectTargetFields as $field) {
        $targetReference = app_project_output_runtime_sql_target_table_reference($field);
        if (!$targetReference['ok']) {
            return [
                'ok' => false,
                'from_sql' => '',
                'where_rows' => [],
                'reason' => $targetReference['reason'],
            ];
        }

        if (trim((string) ($field['target_table_column_name'] ?? '')) === '') {
            return [
                'ok' => false,
                'from_sql' => '',
                'where_rows' => [],
                'reason' => 'select target column is blank',
            ];
        }

        $requiredReferences[$targetReference['key']] = $targetReference;
    }

    $joinedReferences = [
        $baseReference['key'] => $baseReference,
    ];
    $whereRows = [];
    $pendingJoinRows = [];

    foreach ($selectWheres as $row) {
        $parameterType = app_project_output_runtime_sql_normalize_token((string) ($row['parameter_type'] ?? ''));

        $targetReference = app_project_output_runtime_sql_target_table_reference($row);
        if (!$targetReference['ok']) {
            return [
                'ok' => false,
                'from_sql' => '',
                'where_rows' => [],
                'reason' => $targetReference['reason'],
            ];
        }

        $requiredReferences[$targetReference['key']] = $targetReference;

        if ($parameterType !== 'anotherfield') {
            $whereRows[] = $row;
            continue;
        }

        if (trim((string) ($row['target_table_column_name'] ?? '')) === '') {
            return [
                'ok' => false,
                'from_sql' => '',
                'where_rows' => [],
                'reason' => 'anotherfield target column is blank',
            ];
        }

        $anotherReference = app_project_output_runtime_sql_another_table_reference($row);
        if (!$anotherReference['ok']) {
            return [
                'ok' => false,
                'from_sql' => '',
                'where_rows' => [],
                'reason' => $anotherReference['reason'],
            ];
        }

        if (trim((string) ($row['another_field_name'] ?? '')) === '') {
            return [
                'ok' => false,
                'from_sql' => '',
                'where_rows' => [],
                'reason' => 'anotherfield another field is blank',
            ];
        }

        $requiredReferences[$anotherReference['key']] = $anotherReference;
        $pendingJoinRows[] = [
            'row' => $row,
            'target_reference' => $targetReference,
            'another_reference' => $anotherReference,
        ];
    }

    $joinClauses = [];

    while ($pendingJoinRows !== []) {
        $candidateNewReference = null;
        $candidateJoinSql = '';

        foreach ($pendingJoinRows as $pendingJoinRow) {
            $targetJoined = isset($joinedReferences[$pendingJoinRow['target_reference']['key']]);
            $anotherJoined = isset($joinedReferences[$pendingJoinRow['another_reference']['key']]);
            if ($targetJoined === $anotherJoined) {
                continue;
            }

            $newSideIsAnother = $targetJoined;
            $joinKeywordResult = app_project_output_runtime_sql_join_keyword_for_row(
                $pendingJoinRow['row'],
                $newSideIsAnother,
            );
            if (!$joinKeywordResult['ok']) {
                return [
                    'ok' => false,
                    'from_sql' => '',
                    'where_rows' => [],
                    'reason' => $joinKeywordResult['reason'],
                ];
            }

            $candidateNewReference = $newSideIsAnother
                ? $pendingJoinRow['another_reference']
                : $pendingJoinRow['target_reference'];
            $candidateJoinSql = $joinKeywordResult['sql'];
            break;
        }

        if ($candidateNewReference === null) {
            break;
        }

        $joinConditionRows = [];
        $remainingJoinRows = [];

        foreach ($pendingJoinRows as $pendingJoinRow) {
            $targetKey = $pendingJoinRow['target_reference']['key'];
            $anotherKey = $pendingJoinRow['another_reference']['key'];
            $targetJoined = isset($joinedReferences[$targetKey]);
            $anotherJoined = isset($joinedReferences[$anotherKey]);

            $connectsNewReference = (
                $targetKey === $candidateNewReference['key']
                && $anotherJoined
            ) || (
                $anotherKey === $candidateNewReference['key']
                && $targetJoined
            );
            if (!$connectsNewReference) {
                $remainingJoinRows[] = $pendingJoinRow;
                continue;
            }

            $newSideIsAnother = $targetJoined;
            $joinKeywordResult = app_project_output_runtime_sql_join_keyword_for_row(
                $pendingJoinRow['row'],
                $newSideIsAnother,
            );
            if (!$joinKeywordResult['ok']) {
                return [
                    'ok' => false,
                    'from_sql' => '',
                    'where_rows' => [],
                    'reason' => $joinKeywordResult['reason'],
                ];
            }

            if ($joinKeywordResult['sql'] !== $candidateJoinSql) {
                return [
                    'ok' => false,
                    'from_sql' => '',
                    'where_rows' => [],
                    'reason' => 'join type is inconsistent for joined table: '
                        . $candidateNewReference['qualifier'],
                ];
            }

            $joinConditionRows[] = $pendingJoinRow['row'];
        }

        if ($joinConditionRows === []) {
            return [
                'ok' => false,
                'from_sql' => '',
                'where_rows' => [],
                'reason' => 'join condition could not be resolved',
            ];
        }

        $onConditionPartsResult = app_project_output_runtime_sql_grouped_condition_parts(
            $joinConditionRows,
            static fn (array $row): array => [
                'ok' => true,
                'parts' => [
                    [
                        'type' => 'string',
                        'value' => app_project_output_runtime_sql_anotherfield_condition_sql($row),
                    ],
                ],
                'reason' => '',
            ],
            $orGroupType,
        );
        if (!$onConditionPartsResult['ok']) {
            return [
                'ok' => false,
                'from_sql' => '',
                'where_rows' => [],
                'reason' => $onConditionPartsResult['reason'],
            ];
        }

        $joinedReferences[$candidateNewReference['key']] = $candidateNewReference;
        $joinClauses[] = ' '
            . $candidateJoinSql
            . ' '
            . $candidateNewReference['sql']
            . ' on '
            . implode(
                '',
                array_map(
                    static fn (array $part): string => (string) ($part['value'] ?? ''),
                    $onConditionPartsResult['parts'],
                ),
            );
        $pendingJoinRows = $remainingJoinRows;
    }

    foreach ($pendingJoinRows as $pendingJoinRow) {
        $targetJoined = isset($joinedReferences[$pendingJoinRow['target_reference']['key']]);
        $anotherJoined = isset($joinedReferences[$pendingJoinRow['another_reference']['key']]);
        if (!$targetJoined || !$anotherJoined) {
            return [
                'ok' => false,
                'from_sql' => '',
                'where_rows' => [],
                'reason' => 'join graph could not be resolved from canonical metadata',
            ];
        }

        $normalizedJoinType = app_project_output_runtime_sql_normalize_token(
            (string) ($pendingJoinRow['row']['join_type'] ?? ''),
        );
        if (!in_array($normalizedJoinType, ['', 'inner'], true)) {
            return [
                'ok' => false,
                'from_sql' => '',
                'where_rows' => [],
                'reason' => 'residual outer join conditions are not supported yet',
            ];
        }

        $whereRows[] = $pendingJoinRow['row'];
    }

    foreach (array_keys($requiredReferences) as $referenceKey) {
        if (isset($joinedReferences[$referenceKey])) {
            continue;
        }

        return [
            'ok' => false,
            'from_sql' => '',
            'where_rows' => [],
            'reason' => 'select targets reference an unresolved joined table',
        ];
    }

    return [
        'ok' => true,
        'from_sql' => 'from ' . $baseReference['sql'] . implode('', $joinClauses),
        'where_rows' => $whereRows,
        'reason' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     result:array{
 *         mode:string,
 *         body_lines:list<string>,
 *         reason:string,
 *         warning:string
 *     }
 * }
 */
function app_project_output_runtime_sql_try_generate_method(
    array $app,
    string $projectKey,
    string $sourceName,
    array $functionItem,
    array $method,
): array {
    $functionName = (string) ($functionItem['function_name'] ?? '');
    $actionType = strtoupper(trim((string) ($functionItem['action_type'] ?? '')));
    $signature = trim((string) ($method['signature'] ?? ''));
    $parameterNames = app_project_output_runtime_sql_signature_parameter_names($signature);

    if ($functionName === '__construct') {
        return [
            'ok' => true,
            'result' => [
                'mode' => 'canonical-constructor',
                'body_lines' => [],
                'reason' => 'bootstrap constructor is empty, so runtime owns the no-op constructor directly',
                'warning' => '',
            ],
        ];
    }

    $knownHelperResult = app_project_output_runtime_sql_try_generate_known_helper_method(
        $sourceName,
        $functionName,
        $parameterNames,
    );
    if ($knownHelperResult !== null) {
        return $knownHelperResult;
    }

    if (!in_array($actionType, ['SELECTLIST', 'SELECTSINGLE', 'INSERT', 'UPDATE', 'DELETE'], true)) {
        return [
            'ok' => true,
            'result' => [
                'mode' => 'legacy-delegate',
                'body_lines' => [
                    '        return parent::' . $functionName . '(...func_get_args());',
                ],
                'reason' => 'unsupported action type: ' . ($actionType !== '' ? $actionType : '(blank)'),
                'warning' => '',
            ],
        ];
    }

    $designer = app_project_output_runtime_sql_fetch_designer_resources($app, $projectKey, $sourceName, $functionName);
    if (!$designer['ok']) {
        return [
            'ok' => true,
            'result' => [
                'mode' => 'legacy-delegate',
                'body_lines' => [
                    '        return parent::' . $functionName . '(...func_get_args());',
                ],
                'reason' => 'designer catalog read failed',
                'warning' => $sourceName . '::' . $functionName . ' ' . $designer['error'],
            ],
        ];
    }

    return match ($actionType) {
        'SELECTLIST' => app_project_output_runtime_sql_try_generate_select_method(
            $functionName,
            true,
            $sourceName,
            $functionItem,
            $parameterNames,
            $designer,
        ),
        'SELECTSINGLE' => app_project_output_runtime_sql_try_generate_select_method(
            $functionName,
            false,
            $sourceName,
            $functionItem,
            $parameterNames,
            $designer,
        ),
        'INSERT' => app_project_output_runtime_sql_try_generate_insert_method(
            $functionName,
            $sourceName,
            $functionItem,
            $parameterNames,
            $designer,
        ),
        'UPDATE' => app_project_output_runtime_sql_try_generate_update_method(
            $functionName,
            $sourceName,
            $functionItem,
            $parameterNames,
            $designer,
        ),
        'DELETE' => app_project_output_runtime_sql_try_generate_delete_method(
            $functionName,
            $sourceName,
            $functionItem,
            $parameterNames,
            $designer,
        ),
        default => [
            'ok' => true,
            'result' => [
                'mode' => 'legacy-delegate',
                'body_lines' => [
                    '        return parent::' . $functionName . '(...func_get_args());',
                ],
                'reason' => 'unsupported action type',
                'warning' => '',
            ],
        ],
    };
}

/**
 * @return array{
 *     ok:bool,
 *     result:array{
 *         mode:string,
 *         body_lines:list<string>,
 *         reason:string,
 *         warning:string
 *     }
 * }|null
 */
function app_project_output_runtime_sql_try_generate_known_helper_method(
    string $sourceName,
    string $functionName,
    array $parameterNames,
): ?array {
    if ($sourceName === 'MySQLShowColumn') {
        return app_project_output_runtime_sql_try_generate_mysql_show_column_helper(
            $functionName,
            $parameterNames,
        );
    }

    if ($sourceName === 'htmlTemplateParameter_leftouterjoin_AnotherHtmlTemplate') {
        return app_project_output_runtime_sql_try_generate_html_template_parameter_helper(
            $functionName,
            $parameterNames,
        );
    }

    if ($sourceName === 'LanguageResourceCaption') {
        return app_project_output_runtime_sql_try_generate_language_resource_caption_helper(
            $functionName,
            $parameterNames,
        );
    }

    return null;
}

/**
 * @return array{
 *     ok:bool,
 *     result:array{
 *         mode:string,
 *         body_lines:list<string>,
 *         reason:string,
 *         warning:string
 *     }
 * }|null
 */
function app_project_output_runtime_sql_try_generate_mysql_show_column_helper(
    string $functionName,
    array $parameterNames,
): ?array {
    if ($functionName === 'Initialize') {
        if (!isset($parameterNames[0])) {
            return app_project_output_runtime_sql_delegate_result(
                $functionName,
                'known helper signature does not match bootstrap method',
            );
        }

        return app_project_output_runtime_sql_helper_result([
            '        $this->runtimeMySQLiObj = ' . $parameterNames[0] . ';',
        ]);
    }

    if ($functionName === 'GetTables') {
        return app_project_output_runtime_sql_helper_result([
            '        $result = array();',
            '',
            '        $ret = $this->runtimeMySQLiObj->query("show tables");',
            '        while($thisline=$ret->fetch_row()) {',
            '            $tablename = $thisline[0];',
            '            array_push($result, $tablename);',
            '        }',
            '        return $result;',
        ]);
    }

    if ($functionName === 'GetTableColumns') {
        if (!isset($parameterNames[0])) {
            return app_project_output_runtime_sql_delegate_result(
                $functionName,
                'known helper signature does not match bootstrap method',
            );
        }

        return app_project_output_runtime_sql_helper_result([
            '        $result = array();',
            '',
            '        // NOTE: legacy behavior keeps the identifier unquoted after escaping.',
            '        $ret = $this->runtimeMySQLiObj->query("show columns from " . $this->runtimeMySQLiObj->real_escape_string('
                . $parameterNames[0]
                . '));',
            '        while($thisline=$ret->fetch_row()) {',
            '            $thisresult = new MySQLShowColumnData();',
            '            $thisresult->Field = $thisline[0];',
            '            $thisresult->Type = $thisline[1];',
            '            $thisresult->IsNull = $thisline[2];',
            '            $thisresult->IsKey = $thisline[3];',
            '            $thisresult->IsDefault = $thisline[4];',
            '            $thisresult->Extra = $thisline[5];',
            '            array_push($result, $thisresult);',
            '        }',
            '        return $result;',
        ]);
    }

    return null;
}

/**
 * @return array{
 *     ok:bool,
 *     result:array{
 *         mode:string,
 *         body_lines:list<string>,
 *         reason:string,
 *         warning:string
 *     }
 * }|null
 */
function app_project_output_runtime_sql_try_generate_html_template_parameter_helper(
    string $functionName,
    array $parameterNames,
): ?array {
    if ($functionName === 'GethtmlTemplateParameterListMostDeep') {
        if (!isset($parameterNames[0])) {
            return app_project_output_runtime_sql_delegate_result(
                $functionName,
                'known helper signature does not match bootstrap method',
            );
        }

        return app_project_output_runtime_sql_helper_result([
            '        $htmlTemplateParameterList = array();',
            '        $this->GethtmlTemplateParameterListMostDeepSub($htmlTemplateParameterList, ' . $parameterNames[0] . ');',
            '        return $htmlTemplateParameterList;',
        ]);
    }

    if ($functionName === 'GethtmlTemplateParameterListMostDeepSub') {
        if (!isset($parameterNames[0], $parameterNames[1])) {
            return app_project_output_runtime_sql_delegate_result(
                $functionName,
                'known helper signature does not match bootstrap method',
            );
        }

        return app_project_output_runtime_sql_helper_result([
            '        $thisHtmlTemplateParameterList = $this->GethtmlTemplateParameterList(' . $parameterNames[1] . ');',
            '',
            '        for($i = 0 ; $i < count($thisHtmlTemplateParameterList) ; $i++) {',
            '            $thisHtmlTemplateParameter = $thisHtmlTemplateParameterList[$i];',
            '            array_push(' . $parameterNames[0] . ', $thisHtmlTemplateParameter);',
            '',
            '            switch($thisHtmlTemplateParameter->TargetValueType)',
            '            {',
            '                case htmlTemplateParameterTargetValueTypeEnum::$EACHHTML:',
            '                    break;',
            '                case htmlTemplateParameterTargetValueTypeEnum::$CODE:',
            '                    break;',
            '                case htmlTemplateParameterTargetValueTypeEnum::$ANOTHERTEMPLATE:',
            '                    $isAlreadyExist = $this->CheckhtmlTemplateParameterIsAlreadyExist('
                . $parameterNames[0]
                . ', $thisHtmlTemplateParameter->AnotherTemplatePID);',
            '                    if (!$isAlreadyExist) {',
            '                        $this->GethtmlTemplateParameterListMostDeepSub('
                . $parameterNames[0]
                . ', $thisHtmlTemplateParameter->AnotherTemplatePID);',
            '                    }',
            '                    break;',
            '            }',
            '        }',
        ]);
    }

    if ($functionName === 'CheckhtmlTemplateParameterIsAlreadyExist') {
        if (!isset($parameterNames[0], $parameterNames[1])) {
            return app_project_output_runtime_sql_delegate_result(
                $functionName,
                'known helper signature does not match bootstrap method',
            );
        }

        return app_project_output_runtime_sql_helper_result([
            '        $isAlreadyExist = false;',
            '        for($j = 0 ; $j < count(' . $parameterNames[0] . ') ; $j++) {',
            '            $htmlTemplateParameter = ' . $parameterNames[0] . '[$j];',
            '            if ($htmlTemplateParameter->PID == ' . $parameterNames[1] . ') {',
            '                $isAlreadyExist = true;',
            '                break;',
            '            }',
            '        }',
            '        return $isAlreadyExist;',
        ]);
    }

    return null;
}

/**
 * @return array{
 *     ok:bool,
 *     result:array{
 *         mode:string,
 *         body_lines:list<string>,
 *         reason:string,
 *         warning:string
 *     }
 * }|null
 */
function app_project_output_runtime_sql_try_generate_language_resource_caption_helper(
    string $functionName,
    array $parameterNames,
): ?array {
    if ($functionName !== 'GetCaptionBasedOnResouceKey') {
        return null;
    }

    if (!isset($parameterNames[0], $parameterNames[1], $parameterNames[2])) {
        return app_project_output_runtime_sql_delegate_result(
            $functionName,
            'known helper signature does not match bootstrap method',
        );
    }

    return app_project_output_runtime_sql_helper_result([
        '        $DALanguageResource = new LanguageResourceDBAccess();',
        '        $LanguageResource = $DALanguageResource->GetLanguageResourceByKeyName('
            . $parameterNames[0]
            . ', '
            . $parameterNames[2]
            . ');',
        '        if ($LanguageResource) {',
        '            $LanguageResourceCaption = $this->GetLanguageResourceCaption('
            . '$LanguageResource->ProjectPID, '
            . '$LanguageResource->PID, '
            . '$LanguageResource->LanguageResourceGroupPID, '
            . $parameterNames[1]
            . ');',
        '            if ($LanguageResourceCaption) {',
        '                return $LanguageResourceCaption->Caption;',
        '            }',
        '        }',
        '        return "";',
    ]);
}

/**
 * @return array{
 *     ok:bool,
 *     result:array{
 *         mode:string,
 *         body_lines:list<string>,
 *         reason:string,
 *         warning:string
 *     }
 * }
 */
function app_project_output_runtime_sql_try_generate_select_method(
    string $functionName,
    bool $isList,
    string $sourceName,
    array $functionItem,
    array $parameterNames,
    array $designer,
): array {
    $selectTargetFields = $designer['select_target_fields'];
    $selectHavings = $designer['select_havings'];

    if ($selectTargetFields === []) {
        return app_project_output_runtime_sql_delegate_result($functionName, 'select target fields are empty');
    }

    foreach ($selectTargetFields as $field) {
        if (trim((string) ($field['target_table_name'] ?? '')) === '') {
            return app_project_output_runtime_sql_delegate_result($functionName, 'select target table is blank');
        }

        if (trim((string) ($field['target_table_column_name'] ?? '')) === '') {
            return app_project_output_runtime_sql_delegate_result($functionName, 'select target column is blank');
        }
    }

    $selectTargetFieldById = app_project_output_runtime_sql_select_target_field_map($selectTargetFields);

    $fromBuildResult = app_project_output_runtime_sql_build_select_from_sql(
        $selectTargetFields,
        $designer['select_wheres'],
        (string) ($functionItem['or_group_type'] ?? ''),
    );
    if (!$fromBuildResult['ok']) {
        return app_project_output_runtime_sql_delegate_result($functionName, $fromBuildResult['reason']);
    }

    $queryParts = [
        [
            'type' => 'string',
            'value' => 'select '
                . (app_project_output_runtime_sql_is_truthy_flag((string) ($functionItem['select_by_distinct'] ?? '0'))
                    ? 'distinct '
                    : '')
                . implode(
                    ', ',
                    array_map(
                        static fn (array $field): string => app_project_output_runtime_sql_select_field_expression($field),
                        $selectTargetFields,
                    ),
                )
                . ' '
                . $fromBuildResult['from_sql'],
        ],
    ];

    $selectWheres = $fromBuildResult['where_rows'];
    $argumentIndex = 0;
    $paramExpressions = [];
    if ($selectWheres !== []) {
        $queryParts[] = [
            'type' => 'string',
            'value' => ' where ',
        ];

        $wherePartsResult = app_project_output_runtime_sql_grouped_condition_parts(
            $selectWheres,
            static function (array $row) use (
                $parameterNames,
                &$argumentIndex,
                &$paramExpressions
            ): array {
                $parameterType = app_project_output_runtime_sql_normalize_token((string) ($row['parameter_type'] ?? ''));
                if (!in_array($parameterType, ['', 'argument', 'fixed', 'anotherfield'], true)) {
                    return [
                        'ok' => false,
                        'parts' => [],
                        'reason' => 'select where parameter type is not supported yet: ' . $parameterType,
                    ];
                }

                if ($parameterType === 'anotherfield') {
                    return [
                        'ok' => true,
                        'parts' => [
                            [
                                'type' => 'string',
                                'value' => app_project_output_runtime_sql_anotherfield_condition_sql($row),
                            ],
                        ],
                        'reason' => '',
                    ];
                }

                $operator = trim((string) ($row['relational_operator'] ?? ''));
                if ($operator === '') {
                    $operator = '=';
                }

                $parts = [
                    [
                        'type' => 'string',
                        'value' => app_project_output_runtime_sql_table_qualifier($row)
                            . '.'
                            . trim((string) ($row['target_table_column_name'] ?? ''))
                            . ' '
                            . $operator
                            . ' ',
                    ],
                ];

                if ($parameterType === 'fixed') {
                    $resolvedValue = app_project_output_runtime_sql_prepared_value(
                        'fixed',
                        (string) ($row['parameter_data_type'] ?? ''),
                        '',
                        (string) ($row['fixed_parameter'] ?? ''),
                    );
                } else {
                    if (!isset($parameterNames[$argumentIndex])) {
                        return [
                            'ok' => false,
                            'parts' => [],
                            'reason' => 'select where argument count does not match signature',
                        ];
                    }

                    $parameterName = $parameterNames[$argumentIndex];
                    if (
                        count($parameterNames) > 1
                        && !app_project_output_runtime_sql_parameter_name_matches_column(
                            $parameterName,
                            trim((string) ($row['target_table_column_name'] ?? '')),
                        )
                    ) {
                        return [
                            'ok' => false,
                            'parts' => [],
                            'reason' => 'select where argument order is ambiguous',
                        ];
                    }

                    $argumentIndex++;
                    $resolvedValue = app_project_output_runtime_sql_prepared_value(
                        'argument',
                        (string) ($row['parameter_data_type'] ?? ''),
                        $parameterName,
                    );
                }

                if (!$resolvedValue['ok']) {
                    return [
                        'ok' => false,
                        'parts' => [],
                        'reason' => $resolvedValue['reason'],
                    ];
                }

                $parts[] = [
                    'type' => 'string',
                    'value' => $resolvedValue['sql_fragment'],
                ];
                if ($resolvedValue['uses_param']) {
                    $paramExpressions[] = $resolvedValue['param_expression'];
                }

                return [
                    'ok' => true,
                    'parts' => $parts,
                    'reason' => '',
                ];
            },
            (string) ($functionItem['or_group_type'] ?? ''),
        );
        if (!$wherePartsResult['ok']) {
            return app_project_output_runtime_sql_delegate_result($functionName, $wherePartsResult['reason']);
        }

        foreach ($wherePartsResult['parts'] as $part) {
            $queryParts[] = $part;
        }
    }

    $groupByExpressions = [];
    foreach ($selectTargetFields as $field) {
        if (!app_project_output_runtime_sql_is_truthy_flag((string) ($field['group_by_target'] ?? '0'))) {
            continue;
        }

        $groupByExpressions[] = app_project_output_runtime_sql_select_field_expression($field);
    }
    if ($groupByExpressions !== []) {
        $queryParts[] = [
            'type' => 'string',
            'value' => ' group by ' . implode(', ', $groupByExpressions),
        ];
    }

    if ($selectHavings !== []) {
        $queryParts[] = [
            'type' => 'string',
            'value' => ' having ',
        ];

        $havingPartsResult = app_project_output_runtime_sql_grouped_condition_parts(
            $selectHavings,
            static function (array $row) use (
                $selectTargetFieldById,
                $parameterNames,
                &$argumentIndex,
                &$paramExpressions
            ): array {
                return app_project_output_runtime_sql_compile_select_having_prepared_parts(
                    $row,
                    $selectTargetFieldById,
                    $parameterNames,
                    $argumentIndex,
                    $paramExpressions,
                );
            },
        );
        if (!$havingPartsResult['ok']) {
            return app_project_output_runtime_sql_delegate_result($functionName, $havingPartsResult['reason']);
        }

        foreach ($havingPartsResult['parts'] as $part) {
            $queryParts[] = $part;
        }
    }

    if ($isList) {
        $sortOrderColumns = trim((string) ($functionItem['sort_order_columns'] ?? ''));
        if ($sortOrderColumns !== '') {
            $queryParts[] = [
                'type' => 'string',
                'value' => ' order by ' . $sortOrderColumns,
            ];
        }
    }

    $limitParameterType = app_project_output_runtime_sql_normalize_token((string) ($functionItem['limit_parameter_type'] ?? ''));
    if ($limitParameterType !== '') {
        if (!in_array($limitParameterType, ['argument', 'fixed'], true)) {
            return app_project_output_runtime_sql_delegate_result(
                $functionName,
                'limit parameter type is not supported yet: ' . $limitParameterType,
            );
        }

        $queryParts[] = [
            'type' => 'string',
            'value' => ' limit ',
        ];

        if ($limitParameterType === 'fixed') {
            $limitFixedParameter = trim((string) ($functionItem['limit_fixed_parameter'] ?? ''));
            if ($limitFixedParameter === '') {
                return app_project_output_runtime_sql_delegate_result(
                    $functionName,
                    'limit fixed parameter is empty',
                );
            }

            $queryParts[] = [
                'type' => 'string',
                'value' => $limitFixedParameter,
            ];
        } else {
            if (!isset($parameterNames[$argumentIndex])) {
                return app_project_output_runtime_sql_delegate_result(
                    $functionName,
                    'limit argument is missing from signature',
                );
            }

            $limitParameterName = $parameterNames[$argumentIndex];
            $argumentIndex++;
            $queryParts[] = [
                'type' => 'string',
                'value' => '?',
            ];
            $paramExpressions[] = $limitParameterName;
        }
    }

    if ($argumentIndex !== count($parameterNames)) {
        return app_project_output_runtime_sql_delegate_result(
            $functionName,
            'unused method arguments remain after canonical mapping',
        );
    }

    $staticSqlResult = app_project_output_runtime_sql_static_sql_from_parts($queryParts);
    if (!$staticSqlResult['ok']) {
        return app_project_output_runtime_sql_delegate_result($functionName, $staticSqlResult['reason']);
    }

    $dataClassBaseName = trim((string) ($functionItem['data_class_base_name'] ?? ''));
    if ($dataClassBaseName === '') {
        $dataClassBaseName = $sourceName;
    }
    $dataClassBaseName = app_project_output_runtime_sql_output_data_class_base_name($dataClassBaseName);
    $dataClassName = $dataClassBaseName . 'Data';
    $bodyLines = app_project_output_runtime_sql_prepared_select_method_body_lines(
        $staticSqlResult['sql'],
        $paramExpressions,
        $isList,
        $dataClassName,
        $selectTargetFields,
    );

    return [
        'ok' => true,
        'result' => [
            'mode' => 'canonical-sql',
            'body_lines' => $bodyLines,
            'reason' => '',
            'warning' => '',
        ],
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     result:array{
 *         mode:string,
 *         body_lines:list<string>,
 *         reason:string,
 *         warning:string
 *     }
 * }
 */
function app_project_output_runtime_sql_try_generate_insert_method(
    string $functionName,
    string $sourceName,
    array $functionItem,
    array $parameterNames,
    array $designer,
): array {
    $targetTableName = trim((string) ($functionItem['target_table_name'] ?? ''));
    if ($targetTableName === '') {
        $targetTableName = $sourceName;
    }

    if (app_project_output_runtime_sql_is_truthy_flag((string) ($functionItem['is_blob_target'] ?? '0'))) {
        return app_project_output_runtime_sql_delegate_result(
            $functionName,
            'blob target requires prepared statement send_long_data handling',
        );
    }

    $insertTargetFields = $designer['insert_target_fields'];
    if ($insertTargetFields === []) {
        return app_project_output_runtime_sql_delegate_result($functionName, 'insert target fields are empty');
    }

    $bindingModeResult = app_project_output_runtime_sql_resolve_write_binding_mode(
        'INSERT',
        (string) ($functionItem['parameter_type'] ?? ''),
        $parameterNames,
    );
    if (!$bindingModeResult['ok']) {
        return app_project_output_runtime_sql_delegate_result($functionName, $bindingModeResult['reason']);
    }

    $columns = [];
    $valueSqlFragments = [];
    $paramExpressions = [];
    $scalarIndex = $bindingModeResult['initial_scalar_index'];

    foreach ($insertTargetFields as $field) {
        $columnName = trim((string) ($field['target_table_column_name'] ?? ''));
        if ($columnName === '') {
            return app_project_output_runtime_sql_delegate_result($functionName, 'insert target column is blank');
        }

        $columns[] = $columnName;

        $parameterType = app_project_output_runtime_sql_normalize_token((string) ($field['parameter_type'] ?? ''));
        if ($parameterType === 'fixed') {
            $resolvedValue = app_project_output_runtime_sql_prepared_value(
                'fixed',
                (string) ($field['parameter_data_type'] ?? ''),
                '',
                (string) ($field['fixed_parameter'] ?? ''),
            );
        } else {
            $argumentExpressionResult = app_project_output_runtime_sql_resolve_write_argument_expression(
                $parameterNames,
                $bindingModeResult['mode'],
                $scalarIndex,
                $columnName,
                false,
            );
            if (!$argumentExpressionResult['ok']) {
                return app_project_output_runtime_sql_delegate_result($functionName, $argumentExpressionResult['reason']);
            }

            $scalarIndex = $argumentExpressionResult['next_scalar_index'];
            $resolvedValue = app_project_output_runtime_sql_prepared_value(
                'argument',
                (string) ($field['parameter_data_type'] ?? ''),
                $argumentExpressionResult['value_expression'],
            );
        }

        if (!$resolvedValue['ok']) {
            return app_project_output_runtime_sql_delegate_result($functionName, $resolvedValue['reason']);
        }

        $valueSqlFragments[] = $resolvedValue['sql_fragment'];
        if ($resolvedValue['uses_param']) {
            $paramExpressions[] = $resolvedValue['param_expression'];
        }
    }

    if ($bindingModeResult['mode'] !== 'object' && $scalarIndex !== count($parameterNames)) {
        return app_project_output_runtime_sql_delegate_result(
            $functionName,
            'unused insert arguments remain after canonical mapping',
        );
    }

    $sql = 'insert into '
        . $targetTableName
        . ' ('
        . implode(', ', $columns)
        . ') values('
        . implode(', ', $valueSqlFragments)
        . ')';

    return [
        'ok' => true,
        'result' => [
            'mode' => 'canonical-sql',
            'body_lines' => app_project_output_runtime_sql_prepared_write_method_body_lines($sql, $paramExpressions),
            'reason' => '',
            'warning' => '',
        ],
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     result:array{
 *         mode:string,
 *         body_lines:list<string>,
 *         reason:string,
 *         warning:string
 *     }
 * }
 */
function app_project_output_runtime_sql_try_generate_update_method(
    string $functionName,
    string $sourceName,
    array $functionItem,
    array $parameterNames,
    array $designer,
): array {
    $targetTableName = trim((string) ($functionItem['target_table_name'] ?? ''));
    if ($targetTableName === '') {
        $targetTableName = $sourceName;
    }

    if (app_project_output_runtime_sql_is_truthy_flag((string) ($functionItem['is_blob_target'] ?? '0'))) {
        return app_project_output_runtime_sql_delegate_result(
            $functionName,
            'blob target requires prepared statement send_long_data handling',
        );
    }

    $updateTargetFields = $designer['update_target_fields'];
    $updateDeleteWheres = $designer['update_delete_wheres'];
    if ($updateTargetFields === []) {
        return app_project_output_runtime_sql_delegate_result($functionName, 'update target fields are empty');
    }
    if ($updateDeleteWheres === []) {
        return app_project_output_runtime_sql_delegate_result($functionName, 'update/delete where rows are empty');
    }

    $bindingModeResult = app_project_output_runtime_sql_resolve_write_binding_mode(
        'UPDATE',
        (string) ($functionItem['parameter_type'] ?? ''),
        $parameterNames,
    );
    if (!$bindingModeResult['ok']) {
        return app_project_output_runtime_sql_delegate_result($functionName, $bindingModeResult['reason']);
    }

    $setSqlFragments = [];
    $paramExpressions = [];
    $scalarIndex = $bindingModeResult['initial_scalar_index'];

    foreach ($updateTargetFields as $field) {
        $columnName = trim((string) ($field['target_table_column_name'] ?? ''));
        if ($columnName === '') {
            return app_project_output_runtime_sql_delegate_result($functionName, 'update target column is blank');
        }

        $parameterType = app_project_output_runtime_sql_normalize_token((string) ($field['parameter_type'] ?? ''));
        if ($parameterType === 'fixed') {
            $resolvedValue = app_project_output_runtime_sql_prepared_value(
                'fixed',
                (string) ($field['parameter_data_type'] ?? ''),
                '',
                (string) ($field['fixed_parameter'] ?? ''),
            );
        } else {
            $argumentExpressionResult = app_project_output_runtime_sql_resolve_write_argument_expression(
                $parameterNames,
                $bindingModeResult['mode'],
                $scalarIndex,
                $columnName,
                false,
            );
            if (!$argumentExpressionResult['ok']) {
                return app_project_output_runtime_sql_delegate_result($functionName, $argumentExpressionResult['reason']);
            }

            $scalarIndex = $argumentExpressionResult['next_scalar_index'];
            $resolvedValue = app_project_output_runtime_sql_prepared_value(
                'argument',
                (string) ($field['parameter_data_type'] ?? ''),
                $argumentExpressionResult['value_expression'],
            );
        }

        if (!$resolvedValue['ok']) {
            return app_project_output_runtime_sql_delegate_result($functionName, $resolvedValue['reason']);
        }

        $setSqlFragments[] = $columnName . ' = ' . $resolvedValue['sql_fragment'];
        if ($resolvedValue['uses_param']) {
            $paramExpressions[] = $resolvedValue['param_expression'];
        }
    }

    $wherePartsResult = app_project_output_runtime_sql_grouped_condition_parts(
        $updateDeleteWheres,
        static function (array $row) use (
            $targetTableName,
            $parameterNames,
            $bindingModeResult,
            &$paramExpressions,
            &$scalarIndex
        ): array {
            $columnName = trim((string) ($row['target_table_column_name'] ?? ''));
            if ($columnName === '') {
                return [
                    'ok' => false,
                    'parts' => [],
                    'reason' => 'update where column is blank',
                ];
            }

            $operator = trim((string) ($row['relational_operator'] ?? ''));
            if ($operator === '') {
                $operator = '=';
            }

            $parts = [
                [
                    'type' => 'string',
                    'value' => $targetTableName . '.' . $columnName . ' ' . $operator . ' ',
                ],
            ];

            $parameterType = app_project_output_runtime_sql_normalize_token((string) ($row['parameter_type'] ?? ''));
            if ($parameterType === 'fixed') {
                $resolvedValue = app_project_output_runtime_sql_prepared_value(
                    'fixed',
                    (string) ($row['parameter_data_type'] ?? ''),
                    '',
                    (string) ($row['fixed_parameter'] ?? ''),
                );
            } else {
                $argumentExpressionResult = app_project_output_runtime_sql_resolve_write_argument_expression(
                    $parameterNames,
                    $bindingModeResult['mode'],
                    $scalarIndex,
                    $columnName,
                    true,
                );
                if (!$argumentExpressionResult['ok']) {
                    return [
                        'ok' => false,
                        'parts' => [],
                        'reason' => $argumentExpressionResult['reason'],
                    ];
                }

                $scalarIndex = $argumentExpressionResult['next_scalar_index'];
                $resolvedValue = app_project_output_runtime_sql_prepared_value(
                    'argument',
                    (string) ($row['parameter_data_type'] ?? ''),
                    $argumentExpressionResult['value_expression'],
                );
            }

            if (!$resolvedValue['ok']) {
                return [
                    'ok' => false,
                    'parts' => [],
                    'reason' => $resolvedValue['reason'],
                ];
            }

            $parts[] = [
                'type' => 'string',
                'value' => $resolvedValue['sql_fragment'],
            ];
            if ($resolvedValue['uses_param']) {
                $paramExpressions[] = $resolvedValue['param_expression'];
            }

                return [
                    'ok' => true,
                    'parts' => $parts,
                    'reason' => '',
                ];
            },
        (string) ($functionItem['or_group_type'] ?? ''),
    );
    if (!$wherePartsResult['ok']) {
        return app_project_output_runtime_sql_delegate_result($functionName, $wherePartsResult['reason']);
    }
    $whereSqlResult = app_project_output_runtime_sql_static_sql_from_parts($wherePartsResult['parts']);
    if (!$whereSqlResult['ok']) {
        return app_project_output_runtime_sql_delegate_result(
            $functionName,
            $whereSqlResult['reason'],
        );
    }
    $whereSql = $whereSqlResult['sql'];

    if (
        !in_array($bindingModeResult['mode'], ['object'], true)
        && $scalarIndex !== count($parameterNames)
    ) {
        return app_project_output_runtime_sql_delegate_result(
            $functionName,
            'unused update arguments remain after canonical mapping',
        );
    }

    $sql = 'update ' . $targetTableName . ' SET ' . implode(', ', $setSqlFragments) . ' where ' . $whereSql;

    return [
        'ok' => true,
        'result' => [
            'mode' => 'canonical-sql',
            'body_lines' => app_project_output_runtime_sql_prepared_write_method_body_lines($sql, $paramExpressions),
            'reason' => '',
            'warning' => '',
        ],
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     result:array{
 *         mode:string,
 *         body_lines:list<string>,
 *         reason:string,
 *         warning:string
 *     }
 * }
 */
function app_project_output_runtime_sql_try_generate_delete_method(
    string $functionName,
    string $sourceName,
    array $functionItem,
    array $parameterNames,
    array $designer,
): array {
    $targetTableName = trim((string) ($functionItem['target_table_name'] ?? ''));
    if ($targetTableName === '') {
        $targetTableName = $sourceName;
    }

    $updateDeleteWheres = $designer['update_delete_wheres'];
    if ($updateDeleteWheres === []) {
        return app_project_output_runtime_sql_delegate_result($functionName, 'update/delete where rows are empty');
    }

    $bindingModeResult = app_project_output_runtime_sql_resolve_write_binding_mode(
        'DELETE',
        (string) ($functionItem['parameter_type'] ?? ''),
        $parameterNames,
    );
    if (!$bindingModeResult['ok']) {
        return app_project_output_runtime_sql_delegate_result($functionName, $bindingModeResult['reason']);
    }

    $paramExpressions = [];
    $scalarIndex = $bindingModeResult['initial_scalar_index'];

    $wherePartsResult = app_project_output_runtime_sql_grouped_condition_parts(
        $updateDeleteWheres,
        static function (array $row) use (
            $targetTableName,
            $parameterNames,
            $bindingModeResult,
            &$paramExpressions,
            &$scalarIndex
        ): array {
            $columnName = trim((string) ($row['target_table_column_name'] ?? ''));
            if ($columnName === '') {
                return [
                    'ok' => false,
                    'parts' => [],
                    'reason' => 'delete where column is blank',
                ];
            }

            $operator = trim((string) ($row['relational_operator'] ?? ''));
            if ($operator === '') {
                $operator = '=';
            }

            $parts = [
                [
                    'type' => 'string',
                    'value' => $targetTableName . '.' . $columnName . ' ' . $operator . ' ',
                ],
            ];

            $parameterType = app_project_output_runtime_sql_normalize_token((string) ($row['parameter_type'] ?? ''));
            if ($parameterType === 'fixed') {
                $resolvedValue = app_project_output_runtime_sql_prepared_value(
                    'fixed',
                    (string) ($row['parameter_data_type'] ?? ''),
                    '',
                    (string) ($row['fixed_parameter'] ?? ''),
                );
            } else {
                $argumentExpressionResult = app_project_output_runtime_sql_resolve_write_argument_expression(
                    $parameterNames,
                    $bindingModeResult['mode'],
                    $scalarIndex,
                    $columnName,
                    true,
                );
                if (!$argumentExpressionResult['ok']) {
                    return [
                        'ok' => false,
                        'parts' => [],
                        'reason' => $argumentExpressionResult['reason'],
                    ];
                }

                $scalarIndex = $argumentExpressionResult['next_scalar_index'];
                $resolvedValue = app_project_output_runtime_sql_prepared_value(
                    'argument',
                    (string) ($row['parameter_data_type'] ?? ''),
                    $argumentExpressionResult['value_expression'],
                );
            }

            if (!$resolvedValue['ok']) {
                return [
                    'ok' => false,
                    'parts' => [],
                    'reason' => $resolvedValue['reason'],
                ];
            }

            $parts[] = [
                'type' => 'string',
                'value' => $resolvedValue['sql_fragment'],
            ];
            if ($resolvedValue['uses_param']) {
                $paramExpressions[] = $resolvedValue['param_expression'];
            }

                return [
                    'ok' => true,
                    'parts' => $parts,
                    'reason' => '',
                ];
            },
        (string) ($functionItem['or_group_type'] ?? ''),
    );
    if (!$wherePartsResult['ok']) {
        return app_project_output_runtime_sql_delegate_result($functionName, $wherePartsResult['reason']);
    }
    $whereSqlResult = app_project_output_runtime_sql_static_sql_from_parts($wherePartsResult['parts']);
    if (!$whereSqlResult['ok']) {
        return app_project_output_runtime_sql_delegate_result(
            $functionName,
            $whereSqlResult['reason'],
        );
    }
    $whereSql = $whereSqlResult['sql'];

    if (
        !in_array($bindingModeResult['mode'], ['object'], true)
        && $scalarIndex !== count($parameterNames)
    ) {
        return app_project_output_runtime_sql_delegate_result(
            $functionName,
            'unused delete arguments remain after canonical mapping',
        );
    }

    $sql = 'delete from ' . $targetTableName . ' where ' . $whereSql;

    return [
        'ok' => true,
        'result' => [
            'mode' => 'canonical-sql',
            'body_lines' => app_project_output_runtime_sql_prepared_write_method_body_lines($sql, $paramExpressions),
            'reason' => '',
            'warning' => '',
        ],
    ];
}

/**
 * @return list<string>
 */
function app_project_output_runtime_sql_write_method_body_lines(string $queryExpression): array
{
    return [
        '        global $mtooldb, $last_sql_command_for_mtooldb;',
        '        connect_mtooldb_if_not_yet();',
        '        reconnect_mtooldb_if_necessary();',
        '',
        '        $last_sql_command_for_mtooldb = ' . $queryExpression . ';',
        '        $result = $mtooldb->query($last_sql_command_for_mtooldb);',
        '        if ($mtooldb->errno != 0) {',
        '            error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);',
        '        }',
        '        return $result;',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     result:array{
 *         mode:string,
 *         body_lines:list<string>,
 *         reason:string,
 *         warning:string
 *     }
 * }
 */
function app_project_output_runtime_sql_delegate_result(string $functionName, string $reason): array
{
    return [
        'ok' => true,
        'result' => [
            'mode' => 'legacy-delegate',
            'body_lines' => [
                '        return parent::' . $functionName . '(...func_get_args());',
            ],
            'reason' => $reason,
            'warning' => '',
        ],
    ];
}

/**
 * @param list<string> $bodyLines
 * @return array{
 *     ok:bool,
 *     result:array{
 *         mode:string,
 *         body_lines:list<string>,
 *         reason:string,
 *         warning:string
 *     }
 * }
 */
function app_project_output_runtime_sql_helper_result(array $bodyLines): array
{
    return [
        'ok' => true,
        'result' => [
            'mode' => 'canonical-helper',
            'body_lines' => $bodyLines,
            'reason' => '',
            'warning' => '',
        ],
    ];
}
