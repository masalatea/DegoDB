<?php

declare(strict_types=1);

require_once __DIR__ . '/data_class_repository.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/generated_catalog.php';
require_once __DIR__ . '/project_db_access_metadata_helper.php';
require_once __DIR__ . '/project_output_data_class_generator.php';
require_once __DIR__ . '/project_output_db_access_generator.php';
require_once __DIR__ . '/runtime_storage_paths.php';
require_once __DIR__ . '/table_metadata_repository.php';

/**
 * @param array{
 *     name:string,
 *     columns:list<array{
 *         name:string,
 *         datatype:string,
 *         is_key:string,
 *         extra:string,
 *         column_list_order:int
 *     }>
 * } $table
 * @return list<array{
 *     name:string,
 *     datatype:string,
 *     is_key:string,
 *     extra:string,
 *     column_list_order:int
 * }>
 */
function app_project_db_access_bootstrap_primary_key_columns(array $table): array
{
    $columns = array_values(array_filter(
        $table['columns'] ?? [],
        static fn (mixed $column): bool => is_array($column),
    ));

    $primaryKeyColumns = array_values(array_filter(
        $columns,
        static function (array $column): bool {
            $isKey = strtoupper(trim((string) ($column['is_key'] ?? '')));
            return $isKey === 'PRI' || $isKey === 'PRIMARY';
        },
    ));

    usort(
        $primaryKeyColumns,
        static fn (array $left, array $right): int =>
            ((int) ($left['column_list_order'] ?? 0)) <=> ((int) ($right['column_list_order'] ?? 0)),
    );

    return $primaryKeyColumns;
}

/**
 * @param array{
 *     columns:list<array{
 *         name:string,
 *         datatype:string,
 *         is_key:string,
 *         extra:string,
 *         column_list_order:int
 *     }>
 * } $table
 * @return list<array{
 *     name:string,
 *     datatype:string,
 *     is_key:string,
 *     extra:string,
 *     column_list_order:int
 * }>
 */
function app_project_db_access_bootstrap_insert_columns(array $table): array
{
    $columns = array_values(array_filter(
        $table['columns'] ?? [],
        static fn (mixed $column): bool => is_array($column),
    ));

    return array_values(array_filter(
        $columns,
        static function (array $column): bool {
            $extra = strtolower(trim((string) ($column['extra'] ?? '')));
            return !str_contains($extra, 'auto_increment');
        },
    ));
}

/**
 * @param array{
 *     columns:list<array{
 *         name:string,
 *         datatype:string,
 *         is_key:string,
 *         extra:string,
 *         column_list_order:int
 *     }>
 * } $table
 * @return list<array{
 *     name:string,
 *     datatype:string,
 *     is_key:string,
 *     extra:string,
 *     column_list_order:int
 * }>
 */
function app_project_db_access_bootstrap_update_columns(array $table): array
{
    $columns = app_project_db_access_bootstrap_insert_columns($table);

    return array_values(array_filter(
        $columns,
        static function (array $column): bool {
            $isKey = strtoupper(trim((string) ($column['is_key'] ?? '')));
            return $isKey !== 'PRI' && $isKey !== 'PRIMARY';
        },
    ));
}

/**
 * @param array{
 *     columns:list<array{
 *         name:string,
 *         datatype:string,
 *         is_key:string,
 *         extra:string,
 *         column_list_order:int
 *     }>
 * } $table
 */
function app_project_db_access_bootstrap_default_sort_order_columns(array $table): string
{
    $primaryKeyColumns = app_project_db_access_bootstrap_primary_key_columns($table);
    if ($primaryKeyColumns !== []) {
        return implode(
            ',',
            array_map(
                static fn (array $column): string => (string) ($column['name'] ?? ''),
                $primaryKeyColumns,
            ),
        );
    }

    $columns = array_values(array_filter(
        $table['columns'] ?? [],
        static fn (mixed $column): bool => is_array($column),
    ));
    if ($columns === []) {
        return '';
    }

    usort(
        $columns,
        static fn (array $left, array $right): int =>
            ((int) ($left['column_list_order'] ?? 0)) <=> ((int) ($right['column_list_order'] ?? 0)),
    );

    return trim((string) ($columns[0]['name'] ?? ''));
}

function app_project_db_access_bootstrap_object_parameter_name(string $sourceName): string
{
    return ltrim(app_project_output_db_access_object_argument_name($sourceName), '$');
}

function app_project_db_access_bootstrap_scalar_parameter_name(string $sourceName, string $columnName): string
{
    return ltrim(app_project_output_db_access_argument_name($sourceName, $columnName, 'where'), '$');
}

function app_project_db_access_bootstrap_escape_php_string_literal(string $value): string
{
    return str_replace(
        ["\\", "'"],
        ["\\\\", "\\'"],
        $value,
    );
}

function app_project_db_access_bootstrap_sql_identifier(string $value): string
{
    $normalized = str_replace('`', '``', trim($value));
    return '`' . $normalized . '`';
}

function app_project_db_access_bootstrap_php_property_sql_value(string $objectVariableName, string $propertyName): string
{
    $normalizedPropertyName = trim($propertyName);
    $normalizedObjectVariableName = ltrim(trim($objectVariableName), '$');

    return '($'
        . $normalizedObjectVariableName
        . '->'
        . $normalizedPropertyName
        . " === null ? 'NULL' : '\\'' . \$mtooldb->real_escape_string((string) "
        . '($'
        . $normalizedObjectVariableName
        . '->'
        . $normalizedPropertyName
        . ")) . '\\'')";
}

function app_project_db_access_bootstrap_php_scalar_sql_value(string $parameterName): string
{
    $normalizedParameterName = ltrim(trim($parameterName), '$');

    return '($'
        . $normalizedParameterName
        . " === null ? 'NULL' : '\\'' . \$mtooldb->real_escape_string((string) "
        . '$'
        . $normalizedParameterName
        . ") . '\\'')";
}

function app_project_db_access_bootstrap_php_property_expression(string $objectVariableName, string $propertyName): string
{
    return '$'
        . ltrim(trim($objectVariableName), '$')
        . '->'
        . trim($propertyName);
}

function app_project_db_access_bootstrap_php_scalar_expression(string $parameterName): string
{
    return '$' . ltrim(trim($parameterName), '$');
}

/**
 * @return list<string>
 */
function app_project_db_access_bootstrap_method_parameter_names(string $signature): array
{
    if (preg_match('/\(([^)]*)\)/', $signature, $matches) !== 1) {
        return [];
    }

    $parameterList = trim((string) ($matches[1] ?? ''));
    if ($parameterList === '') {
        return [];
    }

    $names = [];
    foreach (explode(',', $parameterList) as $parameter) {
        if (preg_match('/\$([A-Za-z_][A-Za-z0-9_]*)/', $parameter, $parameterMatches) === 1) {
            $names[] = (string) ($parameterMatches[1] ?? '');
        }
    }

    return array_values(array_filter($names, static fn (string $name): bool => $name !== ''));
}

function app_project_db_access_bootstrap_output_source_name(string $physicalName): string
{
    if (!app_generated_name_policy_uses_physical_logical_names()) {
        return $physicalName;
    }

    return app_generated_name_map_for_physical_name($physicalName, 'class')['generated_name'];
}

function app_project_db_access_bootstrap_output_field_name(string $physicalName): string
{
    if (!app_generated_name_policy_uses_physical_logical_names()) {
        return $physicalName;
    }

    return app_generated_name_map_for_physical_name($physicalName, 'php-property')['generated_name'];
}

function app_project_db_access_bootstrap_php_string_literal_expression(string $value): string
{
    return "'" . app_project_db_access_bootstrap_escape_php_string_literal($value) . "'";
}

/**
 * @param list<string> $expressions
 */
function app_project_db_access_bootstrap_php_concat_expression(array $expressions): string
{
    $parts = array_values(array_filter(
        $expressions,
        static fn (mixed $expression): bool => is_string($expression) && $expression !== '',
    ));

    if ($parts === []) {
        return "''";
    }

    return implode(' . ', $parts);
}

/**
 * @param list<string> $expressions
 */
function app_project_db_access_bootstrap_php_join_expressions(array $expressions, string $separator): string
{
    $parts = [];
    $isFirst = true;

    foreach ($expressions as $expression) {
        if (!is_string($expression) || $expression === '') {
            continue;
        }

        if (!$isFirst) {
            $parts[] = app_project_db_access_bootstrap_php_string_literal_expression($separator);
        }

        $parts[] = $expression;
        $isFirst = false;
    }

    return app_project_db_access_bootstrap_php_concat_expression($parts);
}

/**
 * @param list<string> $paramExpressions
 * @return list<string>
 */
function app_project_db_access_bootstrap_prepared_write_body_lines(string $sql, array $paramExpressions): array
{
    return [
        '        global $mtooldb, $last_sql_command_for_mtooldb;',
        '        connect_mtooldb_if_not_yet();',
        '        reconnect_mtooldb_if_necessary();',
        '',
        '        $last_sql_command_for_mtooldb = ' . app_project_db_access_bootstrap_php_string_literal_expression($sql) . ';',
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
 * @param list<array{
 *     name:string,
 *     datatype:string,
 *     is_key:string,
 *     extra:string,
 *     column_list_order:int
 * }> $columns
 */
function app_project_db_access_bootstrap_select_base_sql(array $table, array $columns): string
{
    $tableName = trim((string) ($table['name'] ?? ''));
    $selectColumns = [];
    foreach ($columns as $column) {
        $columnName = trim((string) ($column['name'] ?? ''));
        if ($columnName === '') {
            continue;
        }

        $selectColumns[] = app_project_db_access_bootstrap_sql_identifier($tableName)
            . '.'
            . app_project_db_access_bootstrap_sql_identifier($columnName);
    }

    return 'select ' . implode(', ', $selectColumns)
        . ' from '
        . app_project_db_access_bootstrap_sql_identifier($tableName);
}

function app_project_db_access_bootstrap_select_sql(array $table, array $columns, array $whereColumns = []): string
{
    $tableName = trim((string) ($table['name'] ?? ''));
    $sql = app_project_db_access_bootstrap_select_base_sql($table, $columns);

    if ($whereColumns !== []) {
        $conditions = [];
        foreach ($whereColumns as $column) {
            $columnName = trim((string) ($column['name'] ?? ''));
            if ($columnName === '') {
                continue;
            }

            $parameterName = app_project_db_access_bootstrap_scalar_parameter_name($tableName, $columnName);
            $conditions[] = app_project_db_access_bootstrap_sql_identifier($tableName)
                . '.'
                . app_project_db_access_bootstrap_sql_identifier($columnName)
                . ' = '
                . app_project_db_access_bootstrap_php_scalar_sql_value($parameterName);
        }

        if ($conditions !== []) {
            $sql .= ' where ' . implode(' and ', $conditions);
        }
    }

    $sortOrderColumns = app_project_db_access_bootstrap_default_sort_order_columns($table);
    if ($sortOrderColumns !== '' && $whereColumns === []) {
        $sortColumns = array_values(array_filter(
            array_map('trim', explode(',', $sortOrderColumns)),
            static fn (string $columnName): bool => $columnName !== '',
        ));
        if ($sortColumns !== []) {
            $sql .= ' order by ' . implode(
                ', ',
                array_map(
                    static fn (string $columnName): string => app_project_db_access_bootstrap_sql_identifier($tableName)
                        . '.'
                        . app_project_db_access_bootstrap_sql_identifier($columnName),
                    $sortColumns,
                ),
            );
        }
    }

    return $sql;
}

/**
 * @param list<array{
 *     name:string,
 *     datatype:string,
 *     is_key:string,
 *     extra:string,
 *     column_list_order:int
 * }> $columns
 * @return list<string>
 */
function app_project_db_access_bootstrap_select_body_lines(
    array $table,
    array $columns,
    array $whereColumns,
    bool $returnsList,
    array $functionItem = [],
): array {
    $tableName = trim((string) ($table['name'] ?? ''));
    $dataClassName = app_project_db_access_bootstrap_output_source_name($tableName) . 'Data';
    $sql = app_project_db_access_bootstrap_select_base_sql($table, $columns);
    $paramExpressions = [];
    $signatureParameterNames = app_generated_name_policy_uses_physical_logical_names()
        ? app_project_db_access_bootstrap_method_parameter_names((string) ($functionItem['detected_signature'] ?? ''))
        : [];
    $signatureParameterIndex = 0;

    if ($whereColumns !== []) {
        $conditions = [];
        foreach ($whereColumns as $column) {
            $columnName = trim((string) ($column['name'] ?? ''));
            if ($columnName === '') {
                continue;
            }

            $parameterName = app_project_db_access_bootstrap_scalar_parameter_name($tableName, $columnName);
            $conditions[] = app_project_db_access_bootstrap_sql_identifier($tableName)
                . '.'
                . app_project_db_access_bootstrap_sql_identifier($columnName)
                . ' = ?';
            $signatureParameterName = $signatureParameterNames[$signatureParameterIndex] ?? '';
            $paramExpressions[] = app_project_db_access_bootstrap_php_scalar_expression(
                $signatureParameterName !== '' ? $signatureParameterName : $parameterName,
            );
            $signatureParameterIndex++;
        }

        if ($conditions !== []) {
            $sql .= ' where ' . implode(' and ', $conditions);
        }
    } else {
        $sortOrderColumns = app_project_db_access_bootstrap_default_sort_order_columns($table);
        if ($sortOrderColumns !== '') {
            $sortColumns = array_values(array_filter(
                array_map('trim', explode(',', $sortOrderColumns)),
                static fn (string $columnName): bool => $columnName !== '',
            ));
            if ($sortColumns !== []) {
                $sql .= ' order by ' . implode(
                    ', ',
                    array_map(
                        static fn (string $columnName): string => app_project_db_access_bootstrap_sql_identifier($tableName)
                            . '.'
                            . app_project_db_access_bootstrap_sql_identifier($columnName),
                        $sortColumns,
                    ),
                );
            }
        }
    }

    $lines = [
        '        global $mtooldb, $last_sql_command_for_mtooldb;',
        '        connect_mtooldb_if_not_yet();',
        '        reconnect_mtooldb_if_necessary();',
        '',
    ];

    if ($returnsList) {
        $lines[] = '        $result = array();';
        $lines[] = '';
    }

    $lines[] = '        $last_sql_command_for_mtooldb = ' . app_project_db_access_bootstrap_php_string_literal_expression($sql) . ';';
    $lines[] = '        $ret = $mtooldb->execute($last_sql_command_for_mtooldb, [';
    foreach ($paramExpressions as $paramExpression) {
        $lines[] = '            ' . $paramExpression . ',';
    }
    $lines[] = '        ]);';
    $lines[] = '        if ($mtooldb->errno != 0) {';
    $lines[] = '            error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);';
    $lines[] = '            return $ret;';
    $lines[] = '        }';
    $lines[] = '        while($thisline=$ret->fetch_row()) {';
    $lines[] = '            $thisresult = new ' . $dataClassName . '();';

    $columnIndex = 0;
    foreach ($columns as $column) {
        $columnName = trim((string) ($column['name'] ?? ''));
        if ($columnName === '') {
            continue;
        }

        $lines[] = '            $thisresult->' . app_project_db_access_bootstrap_output_field_name($columnName)
            . ' = $thisline[' . $columnIndex . '];';
        $columnIndex++;
    }

    if ($returnsList) {
        $lines[] = '            array_push($result, $thisresult);';
        $lines[] = '        }';
        $lines[] = '        return $result;';
    } else {
        $lines[] = '            return $thisresult;';
        $lines[] = '        }';
        $lines[] = '        return NULL;';
    }

    return $lines;
}

/**
 * @param list<array{
 *     name:string,
 *     datatype:string,
 *     is_key:string,
 *     extra:string,
 *     column_list_order:int
 * }> $columns
 * @return list<string>
 */
function app_project_db_access_bootstrap_insert_body_lines(array $table, array $columns): array
{
    $tableName = trim((string) ($table['name'] ?? ''));
    $objectParameterName = app_project_db_access_bootstrap_object_parameter_name($tableName);
    $columnNames = [];
    $valuePlaceholders = [];
    $paramExpressions = [];

    foreach ($columns as $column) {
        $columnName = trim((string) ($column['name'] ?? ''));
        if ($columnName === '') {
            continue;
        }

        $columnNames[] = app_project_db_access_bootstrap_sql_identifier($columnName);
        $valuePlaceholders[] = '?';
        $paramExpressions[] = app_project_db_access_bootstrap_php_property_expression(
            $objectParameterName,
            $columnName,
        );
    }

    if ($columnNames === []) {
        $sql = 'insert into ' . app_project_db_access_bootstrap_sql_identifier($tableName) . ' () values()';
    } else {
        $sql = 'insert into '
            . app_project_db_access_bootstrap_sql_identifier($tableName)
            . ' ('
            . implode(', ', $columnNames)
            . ') values('
            . implode(', ', $valuePlaceholders)
            . ')';
    }

    return app_project_db_access_bootstrap_prepared_write_body_lines($sql, $paramExpressions);
}

/**
 * @param list<array{
 *     name:string,
 *     datatype:string,
 *     is_key:string,
 *     extra:string,
 *     column_list_order:int
 * }> $updateColumns
 * @param list<array{
 *     name:string,
 *     datatype:string,
 *     is_key:string,
 *     extra:string,
 *     column_list_order:int
 * }> $keyColumns
 * @return list<string>
 */
function app_project_db_access_bootstrap_update_body_lines(array $table, array $updateColumns, array $keyColumns): array
{
    $tableName = trim((string) ($table['name'] ?? ''));
    $objectParameterName = app_project_db_access_bootstrap_object_parameter_name($tableName);
    $setFragments = [];
    $whereFragments = [];
    $paramExpressions = [];

    foreach ($updateColumns as $column) {
        $columnName = trim((string) ($column['name'] ?? ''));
        if ($columnName === '') {
            continue;
        }

        $setFragments[] = app_project_db_access_bootstrap_sql_identifier($columnName) . ' = ?';
        $paramExpressions[] = app_project_db_access_bootstrap_php_property_expression($objectParameterName, $columnName);
    }

    foreach ($keyColumns as $column) {
        $columnName = trim((string) ($column['name'] ?? ''));
        if ($columnName === '') {
            continue;
        }

        $whereFragments[] = app_project_db_access_bootstrap_sql_identifier($tableName)
            . '.'
            . app_project_db_access_bootstrap_sql_identifier($columnName)
            . ' = ?';
        $paramExpressions[] = app_project_db_access_bootstrap_php_property_expression($objectParameterName, $columnName);
    }

    $sql = 'update '
        . app_project_db_access_bootstrap_sql_identifier($tableName)
        . ' SET '
        . implode(', ', $setFragments)
        . ' where '
        . implode(' and ', $whereFragments);

    return app_project_db_access_bootstrap_prepared_write_body_lines($sql, $paramExpressions);
}

/**
 * @param list<array{
 *     name:string,
 *     datatype:string,
 *     is_key:string,
 *     extra:string,
 *     column_list_order:int
 * }> $keyColumns
 * @return list<string>
 */
function app_project_db_access_bootstrap_delete_body_lines(array $table, array $keyColumns): array
{
    $tableName = trim((string) ($table['name'] ?? ''));
    $whereFragments = [];
    $paramExpressions = [];

    foreach ($keyColumns as $column) {
        $columnName = trim((string) ($column['name'] ?? ''));
        if ($columnName === '') {
            continue;
        }

        $parameterName = app_project_db_access_bootstrap_scalar_parameter_name($tableName, $columnName);
        $whereFragments[] = app_project_db_access_bootstrap_sql_identifier($tableName)
            . '.'
            . app_project_db_access_bootstrap_sql_identifier($columnName)
            . ' = ?';
        $paramExpressions[] = app_project_db_access_bootstrap_php_scalar_expression($parameterName);
    }

    $sql = 'delete from '
        . app_project_db_access_bootstrap_sql_identifier($tableName)
        . ' where '
        . implode(' and ', $whereFragments);

    return app_project_db_access_bootstrap_prepared_write_body_lines($sql, $paramExpressions);
}

/**
 * @param array{
 *     function_name:string,
 *     action_type:string,
 *     detected_signature:string
 * } $functionItem
 * @param array{
 *     name:string,
 *     columns:list<array{
 *         name:string,
 *         datatype:string,
 *         is_key:string,
 *         extra:string,
 *         column_list_order:int
 *     }>
 * } $table
 * @return array{
 *     mode:string,
 *     body_lines:list<string>,
 *     reason:string
 * }
 */
function app_project_db_access_bootstrap_generated_method_result(array $functionItem, array $table): array
{
    $actionType = strtoupper(trim((string) ($functionItem['action_type'] ?? '')));
    $keyColumns = app_project_db_access_bootstrap_primary_key_columns($table);
    $insertColumns = app_project_db_access_bootstrap_insert_columns($table);
    $updateColumns = app_project_db_access_bootstrap_update_columns($table);
    $allColumns = array_values(array_filter(
        $table['columns'] ?? [],
        static fn (mixed $column): bool => is_array($column),
    ));

    $bodyLines = match ($actionType) {
        'SELECTLIST' => app_project_db_access_bootstrap_select_body_lines($table, $allColumns, [], true, $functionItem),
        'SELECTSINGLE' => app_project_db_access_bootstrap_select_body_lines($table, $allColumns, $keyColumns, false, $functionItem),
        'INSERT' => app_project_db_access_bootstrap_insert_body_lines($table, $insertColumns),
        'UPDATE' => app_project_db_access_bootstrap_update_body_lines($table, $updateColumns, $keyColumns),
        'DELETE' => app_project_db_access_bootstrap_delete_body_lines($table, $keyColumns),
        default => [
            '        throw new RuntimeException(\'unsupported bootstrap action: '
                . app_project_db_access_bootstrap_escape_php_string_literal($actionType)
                . '\');',
        ],
    };

    return [
        'mode' => 'canonical-bootstrap',
        'body_lines' => $bodyLines,
        'reason' => 'generated from canonical table metadata without legacy runtime source',
    ];
}

/**
 * @param array{
 *     name:string,
 *     columns:list<array{
 *         name:string,
 *         datatype:string,
 *         is_key:string,
 *         extra:string,
 *         column_list_order:int
 *     }>
 * } $table
 * @return list<array{
 *     name:string,
 *     line:int,
 *     end_line:int,
 *     signature:string
 * }>
 */
function app_project_db_access_bootstrap_method_catalog_from_table(array $table): array
{
    $sourceName = trim((string) ($table['name'] ?? ''));
    if ($sourceName === '') {
        return [];
    }

    $primaryKeyColumns = app_project_db_access_bootstrap_primary_key_columns($table);
    $methods = [
        [
            'name' => 'Get' . $sourceName . 'List',
            'line' => 10,
            'end_line' => 10,
            'signature' => 'public function Get' . $sourceName . 'List()',
        ],
        [
            'name' => 'Insert' . $sourceName,
            'line' => 30,
            'end_line' => 30,
            'signature' => 'public function Insert' . $sourceName . '($'
                . app_project_db_access_bootstrap_object_parameter_name($sourceName)
                . ')',
        ],
    ];

    if ($primaryKeyColumns !== []) {
        $scalarParameters = [];
        foreach ($primaryKeyColumns as $column) {
            $columnName = trim((string) ($column['name'] ?? ''));
            if ($columnName === '') {
                continue;
            }

            $scalarParameters[] = '$' . app_project_db_access_bootstrap_scalar_parameter_name($sourceName, $columnName);
        }

        $methods[] = [
            'name' => 'Get' . $sourceName,
            'line' => 20,
            'end_line' => 20,
            'signature' => 'public function Get' . $sourceName . '(' . implode(', ', $scalarParameters) . ')',
        ];

        if (app_project_db_access_bootstrap_update_columns($table) !== []) {
            $methods[] = [
                'name' => 'Update' . $sourceName,
                'line' => 40,
                'end_line' => 40,
                'signature' => 'public function Update' . $sourceName . '($'
                    . app_project_db_access_bootstrap_object_parameter_name($sourceName)
                    . ')',
            ];
        }

        $methods[] = [
            'name' => 'Delete' . $sourceName,
            'line' => 50,
            'end_line' => 50,
            'signature' => 'public function Delete' . $sourceName . '(' . implode(', ', $scalarParameters) . ')',
        ];
    }

    usort(
        $methods,
        static fn (array $left, array $right): int => ((int) ($left['line'] ?? 0)) <=> ((int) ($right['line'] ?? 0)),
    );

    return $methods;
}

/**
 * @param list<array{
 *     function_name:string,
 *     function_list_order:string,
 *     detected_signature:string,
 *     detected_line:string
 * }> $functionItems
 * @return list<array{
 *     name:string,
 *     line:int,
 *     end_line:int,
 *     signature:string
 * }>
 */
function app_project_db_access_bootstrap_method_catalog_from_function_items(array $functionItems): array
{
    $items = [];
    $fallbackLine = 10;

    foreach ($functionItems as $functionItem) {
        $functionName = trim((string) ($functionItem['function_name'] ?? ''));
        if ($functionName === '') {
            continue;
        }

        $line = (int) ($functionItem['detected_line'] ?? 0);
        if ($line <= 0) {
            $line = (int) ($functionItem['function_list_order'] ?? 0);
        }
        if ($line <= 0) {
            $line = $fallbackLine;
        }

        $signature = trim((string) ($functionItem['detected_signature'] ?? ''));
        if ($signature === '') {
            $signature = 'public function ' . $functionName . '()';
        }

        $items[] = [
            'name' => $functionName,
            'line' => $line,
            'end_line' => $line,
            'signature' => $signature,
        ];
        $fallbackLine += 10;
    }

    usort(
        $items,
        static function (array $left, array $right): int {
            $leftLine = (int) ($left['line'] ?? 0);
            $rightLine = (int) ($right['line'] ?? 0);
            if ($leftLine !== $rightLine) {
                return $leftLine <=> $rightLine;
            }

            return strcmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
        },
    );

    return $items;
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         source_name:string,
 *         data_file:string,
 *         dbaccess_file:string,
 *         data_path:string,
 *         dbaccess_path:string,
 *         has_data_file:bool,
 *         has_dbaccess_file:bool,
 *         source_kind:string,
 *         data_class:string,
 *         data_list_class:string,
 *         dbaccess_class:string,
 *         data_properties:list<string>,
 *         method_catalog:list<array{
 *             name:string,
 *             line:int,
 *             end_line:int,
 *             signature:string
 *         }>
 *     }>,
 *     error:string
 * }
 */
function app_project_db_access_bootstrap_candidate_catalog(array $app, string $projectKey): array
{
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        return [
            'ok' => false,
            'items' => [],
            'error' => 'project key の形式が不正です。',
        ];
    }

    $generatedCatalog = app_generated_entity_catalog($app);
    $tableSnapshot = app_fetch_table_metadata_snapshot($app, $normalizedProjectKey);
    if (!$tableSnapshot['ok']) {
        return [
            'ok' => false,
            'items' => [],
            'error' => 'table metadata の読み込みに失敗しました: ' . $tableSnapshot['error'],
        ];
    }

    $dataClassSnapshot = app_fetch_data_class_metadata_snapshot($app, $normalizedProjectKey);
    if (!$dataClassSnapshot['ok']) {
        return [
            'ok' => false,
            'items' => [],
            'error' => 'data class metadata の読み込みに失敗しました: ' . $dataClassSnapshot['error'],
        ];
    }

    $tableByName = [];
    foreach ($tableSnapshot['items'] as $table) {
        $sourceName = trim((string) ($table['name'] ?? ''));
        if ($sourceName !== '') {
            $tableByName[$sourceName] = $table;
        }
    }

    $dataClassByName = [];
    foreach ($dataClassSnapshot['items'] as $item) {
        $sourceName = trim((string) ($item['name'] ?? ''));
        if ($sourceName !== '') {
            $dataClassByName[$sourceName] = $item;
        }
    }

    $items = [];
    $seenSources = [];

    foreach ($generatedCatalog['entities'] as $entity) {
        if (!is_array($entity)) {
            continue;
        }

        $sourceName = trim((string) ($entity['source_name'] ?? ''));
        if ($sourceName === '') {
            continue;
        }

        $dataPath = (string) ($entity['data_path'] ?? '');
        $dbaccessPath = (string) ($entity['dbaccess_path'] ?? '');
        $dataFile = (string) ($entity['data_file'] ?? '');
        $dbaccessFile = (string) ($entity['dbaccess_file'] ?? '');
        $dataClasses = $dataPath !== '' && is_file($dataPath)
            ? app_generated_file_class_names($dataPath)
            : [];
        $dbaccessClasses = $dbaccessPath !== '' && is_file($dbaccessPath)
            ? app_generated_file_class_names($dbaccessPath)
            : [];
        $dataProperties = $dataPath !== '' && is_file($dataPath)
            ? app_generated_file_property_names($dataPath)
            : [];
        $methodCatalog = $dbaccessPath !== '' && is_file($dbaccessPath)
            ? app_generated_file_method_catalog($dbaccessPath)
            : [];

        if ($dataProperties === [] && isset($dataClassByName[$sourceName])) {
            foreach (($dataClassByName[$sourceName]['fields'] ?? []) as $field) {
                if (!is_array($field)) {
                    continue;
                }

                $fieldName = trim((string) ($field['name'] ?? ''));
                if ($fieldName !== '') {
                    $dataProperties[] = $fieldName;
                }
            }
        }

        $items[] = [
            'source_name' => $sourceName,
            'data_file' => $dataFile !== '' ? $dataFile : ('data-' . $sourceName . '.php'),
            'dbaccess_file' => $dbaccessFile !== '' ? $dbaccessFile : ('dbaccess-' . $sourceName . '.php'),
            'data_path' => $dataPath,
            'dbaccess_path' => $dbaccessPath,
            'has_data_file' => $dataPath !== '' && is_file($dataPath),
            'has_dbaccess_file' => $dbaccessPath !== '' && is_file($dbaccessPath),
            'source_kind' => 'generated',
            'data_class' => $dataClasses[0] ?? ($sourceName . 'Data'),
            'data_list_class' => ($dataClasses[0] ?? ($sourceName . 'Data')) . 'List',
            'dbaccess_class' => $dbaccessClasses[0] ?? ($sourceName . 'DBAccess'),
            'data_properties' => array_values(array_unique($dataProperties)),
            'method_catalog' => $methodCatalog,
        ];
        $seenSources[$sourceName] = true;
    }

    foreach ($tableByName as $sourceName => $table) {
        if (isset($seenSources[$sourceName]) || !isset($dataClassByName[$sourceName])) {
            continue;
        }

        $functionCatalogResult = app_fetch_db_access_function_metadata_catalog(
            $app,
            $normalizedProjectKey,
            $sourceName,
        );
        if (!$functionCatalogResult['ok']) {
            return [
                'ok' => false,
                'items' => [],
                'error' => 'db access function catalog の読み込みに失敗しました: ' . $functionCatalogResult['error'],
            ];
        }

        $dataProperties = [];
        foreach (($dataClassByName[$sourceName]['fields'] ?? []) as $field) {
            if (!is_array($field)) {
                continue;
            }

            $fieldName = trim((string) ($field['name'] ?? ''));
            if ($fieldName !== '') {
                $dataProperties[] = $fieldName;
            }
        }

        $items[] = [
            'source_name' => $sourceName,
            'data_file' => 'data-' . $sourceName . '.php',
            'dbaccess_file' => 'dbaccess-' . $sourceName . '.php',
            'data_path' => '',
            'dbaccess_path' => '',
            'has_data_file' => false,
            'has_dbaccess_file' => false,
            'source_kind' => 'canonical-bootstrap',
            'data_class' => $sourceName . 'Data',
            'data_list_class' => $sourceName . 'DataList',
            'dbaccess_class' => $sourceName . 'DBAccess',
            'data_properties' => array_values(array_unique($dataProperties)),
            'method_catalog' => $functionCatalogResult['items'] !== []
                ? app_project_db_access_bootstrap_method_catalog_from_function_items($functionCatalogResult['items'])
                : app_project_db_access_bootstrap_method_catalog_from_table($table),
        ];
    }

    usort(
        $items,
        static fn (array $left, array $right): int => strcmp(
            (string) ($left['source_name'] ?? ''),
            (string) ($right['source_name'] ?? ''),
        ),
    );

    return [
        'ok' => true,
        'items' => $items,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     entity:array{
 *         source_name:string,
 *         data_file:string,
 *         dbaccess_file:string,
 *         data_path:string,
 *         dbaccess_path:string,
 *         has_data_file:bool,
 *         has_dbaccess_file:bool,
 *         source_kind:string,
 *         data_class:string,
 *         data_list_class:string,
 *         dbaccess_class:string,
 *         data_properties:list<string>,
 *         method_catalog:list<array{
 *             name:string,
 *             line:int,
 *             end_line:int,
 *             signature:string
 *         }>
 *     }|null,
 *     error:string
 * }
 */
function app_project_db_access_bootstrap_candidate_entity(
    array $app,
    string $projectKey,
    string $sourceName,
): array {
    $catalogResult = app_project_db_access_bootstrap_candidate_catalog($app, $projectKey);
    if (!$catalogResult['ok']) {
        return [
            'ok' => false,
            'entity' => null,
            'error' => $catalogResult['error'],
        ];
    }

    $normalizedSourceName = trim($sourceName);
    foreach ($catalogResult['items'] as $entity) {
        if (strcasecmp((string) ($entity['source_name'] ?? ''), $normalizedSourceName) === 0) {
            return [
                'ok' => true,
                'entity' => $entity,
                'error' => '',
            ];
        }
    }

    return [
        'ok' => true,
        'entity' => null,
        'error' => '',
    ];
}

function app_project_db_access_bootstrap_runtime_root(array $app, string $projectKey): string
{
    return app_runtime_storage_source_output_temp_root(
        $app,
        $projectKey,
        'CANONICAL-PROXY-RUNTIME',
    ) . '/runtime-dbclasses';
}

function app_project_db_access_bootstrap_ensure_directory(string $directory): void
{
    if (is_dir($directory)) {
        return;
    }

    if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('directory を作成できませんでした: ' . $directory);
    }
}

function app_project_db_access_bootstrap_write_text_file(string $path, string $contents): void
{
    app_project_db_access_bootstrap_ensure_directory(dirname($path));
    if (file_put_contents($path, $contents) === false) {
        throw new RuntimeException('file を書き込めませんでした: ' . $path);
    }
}

/**
 * @param array<string,array<string,mixed>> $dataClassByName
 * @param array<string,string> $storeBasePathByClass
 * @param array<string,list<string>> $rawFieldNamesByClass
 * @param array<string,string> $parentByClass
 * @param array<string,list<string>> $effectiveFieldNameCache
 * @param array<string,bool> $effectiveFieldNameStack
 */
function app_project_db_access_bootstrap_generate_data_class_files(
    string $runtimeRoot,
    string $sourceName,
    array $dataClassByName,
    array $outputNameByClass,
    array $storeBasePathByClass,
    array $rawFieldNamesByClass,
    array $parentByClass,
    array &$effectiveFieldNameCache,
    array &$effectiveFieldNameStack,
): void {
    if (!isset($dataClassByName[$sourceName])) {
        throw new RuntimeException('canonical data class metadata が見つかりません: ' . $sourceName);
    }

    $item = $dataClassByName[$sourceName];
    $parentClassName = trim((string) ($item['inherit_parent_data_class_name'] ?? ''));
    if ($parentClassName !== '' && isset($dataClassByName[$parentClassName])) {
        app_project_db_access_bootstrap_generate_data_class_files(
            $runtimeRoot,
            $parentClassName,
            $dataClassByName,
            $outputNameByClass,
            $storeBasePathByClass,
            $rawFieldNamesByClass,
            $parentByClass,
            $effectiveFieldNameCache,
            $effectiveFieldNameStack,
        );
    }

    $storeBasePath = $storeBasePathByClass[$sourceName] ?? '';
    $outputSourceName = (string) ($outputNameByClass[$sourceName] ?? $sourceName);
    $wrapperRelativePath = app_project_output_data_class_wrapper_relative_path($storeBasePath, $outputSourceName);
    $baseRelativePath = app_project_output_data_class_base_relative_path($storeBasePath, $outputSourceName);

    $parentEffectiveFieldNames = [];
    if ($parentClassName !== '' && isset($rawFieldNamesByClass[$parentClassName])) {
        $parentEffectiveFieldNames = app_project_output_data_class_effective_field_names(
            $parentClassName,
            $rawFieldNamesByClass,
            $parentByClass,
            $effectiveFieldNameCache,
            $effectiveFieldNameStack,
        );
    }

    $declaredFieldNames = [];
    $parentFieldSet = array_fill_keys($parentEffectiveFieldNames, true);
    foreach (($rawFieldNamesByClass[$sourceName] ?? []) as $fieldName) {
        if (!isset($parentFieldSet[$fieldName])) {
            $declaredFieldNames[] = $fieldName;
        }
    }

    $parentWrapperRelativePath = '';
    $outputParentClassName = '';
    if ($parentClassName !== '') {
        $outputParentClassName = (string) ($outputNameByClass[$parentClassName] ?? $parentClassName);
        $parentWrapperRelativePath = app_project_output_data_class_wrapper_relative_path(
            $storeBasePathByClass[$parentClassName] ?? '',
            $outputParentClassName,
        );
    }

    app_project_db_access_bootstrap_write_text_file(
        $runtimeRoot . '/' . $baseRelativePath,
        app_project_output_generated_data_class_base_php_text(
            $outputSourceName . 'DataBase',
            $outputParentClassName !== '' ? ($outputParentClassName . 'Data') : '',
            $declaredFieldNames,
        ),
    );
    app_project_db_access_bootstrap_write_text_file(
        $runtimeRoot . '/' . $wrapperRelativePath,
        app_project_output_generated_data_class_wrapper_php_text(
            $outputSourceName,
            $wrapperRelativePath,
            $outputParentClassName,
            $parentWrapperRelativePath,
        ),
    );
}

/**
 * @param list<array{
 *     function_name:string,
 *     function_list_order:string,
 *     action_type:string,
 *     source_of_truth:string,
 *     detected_signature:string,
 *     detected_line:string
 * }> $functionItems
 */
function app_project_db_access_bootstrap_generate_dbaccess_files(
    string $runtimeRoot,
    array $table,
    array $classItem,
    array $functionItems,
): void {
    $sourceName = trim((string) ($table['name'] ?? ''));
    $outputSourceName = app_project_output_db_access_output_source_name($classItem);
    $storeBasePath = trim(str_replace('\\', '/', (string) ($classItem['store_base_path'] ?? '')), '/');
    $wrapperRelativePath = app_project_output_db_access_wrapper_relative_path($storeBasePath, $outputSourceName);
    $baseRelativePath = app_project_output_db_access_base_relative_path($storeBasePath, $outputSourceName);
    $runtimeDbSupportRequirePath = app_project_output_db_access_runtime_support_require_path($storeBasePath);

    $generatedMethodResults = [];
    $signaturesByFunction = [];
    foreach ($functionItems as $functionItem) {
        $functionName = trim((string) ($functionItem['function_name'] ?? ''));
        if ($functionName === '' || $functionName === '__construct') {
            continue;
        }

        $generatedMethodResults[$functionName] = app_project_db_access_bootstrap_generated_method_result(
            $functionItem,
            $table,
        );
        $signaturesByFunction[$functionName] = trim((string) ($functionItem['detected_signature'] ?? ''));
    }

    app_project_db_access_bootstrap_write_text_file(
        $runtimeRoot . '/' . app_project_output_db_access_runtime_support_relative_path(),
        app_project_output_db_access_runtime_support_php_text(),
    );
    app_project_db_access_bootstrap_write_text_file(
        $runtimeRoot . '/' . $baseRelativePath,
        app_project_output_generated_db_access_base_php_text(
            array_merge($classItem, ['source_name' => $outputSourceName]),
            $functionItems,
            $generatedMethodResults,
            $signaturesByFunction,
            [],
            $runtimeDbSupportRequirePath,
        ),
    );
    app_project_db_access_bootstrap_write_text_file(
        $runtimeRoot . '/' . $wrapperRelativePath,
        app_project_output_generated_db_access_wrapper_php_text($outputSourceName),
    );
}

/**
 * @return array{
 *     ok:bool,
 *     entity:array{
 *         source_name:string,
 *         data_file:string,
 *         dbaccess_file:string,
 *         data_path:string,
 *         dbaccess_path:string,
 *         has_data_file:bool,
 *         has_dbaccess_file:bool,
 *         source_kind:string,
 *         data_class:string,
 *         data_list_class:string,
 *         dbaccess_class:string,
 *         data_properties:list<string>
 *     }|null,
 *     error:string
 * }
 */
function app_project_db_access_bootstrap_materialize_runtime_entity(
    array $app,
    string $projectKey,
    string $sourceName,
): array {
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $normalizedSourceName = trim($sourceName);
    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey) || $normalizedSourceName === '') {
        return [
            'ok' => false,
            'entity' => null,
            'error' => 'project/source の形式が不正です。',
        ];
    }

    $tableItemResult = app_fetch_table_metadata_item($app, $normalizedProjectKey, $normalizedSourceName);
    if (!$tableItemResult['ok'] || !is_array($tableItemResult['item'] ?? null)) {
        return [
            'ok' => false,
            'entity' => null,
            'error' => 'canonical table metadata が見つかりません: ' . $normalizedSourceName,
        ];
    }

    $dataClassSnapshot = app_fetch_data_class_metadata_snapshot($app, $normalizedProjectKey);
    if (!$dataClassSnapshot['ok']) {
        return [
            'ok' => false,
            'entity' => null,
            'error' => 'canonical data class metadata の読み込みに失敗しました: ' . $dataClassSnapshot['error'],
        ];
    }

    $dataClassByName = [];
    $outputNameByClass = [];
    $storeBasePathByClass = [];
    foreach ($dataClassSnapshot['items'] as $item) {
        $className = trim((string) ($item['name'] ?? ''));
        if ($className === '') {
            continue;
        }

        $dataClassByName[$className] = $item;
        $outputNameByClass[$className] = app_project_output_data_class_output_class_name($item);
        $storeBasePathByClass[$className] = trim(str_replace('\\', '/', (string) ($item['store_base_path'] ?? '')), '/');
    }

    if (!isset($dataClassByName[$normalizedSourceName])) {
        return [
            'ok' => false,
            'entity' => null,
            'error' => 'source に対応する canonical data class がありません: ' . $normalizedSourceName,
        ];
    }

    $rawFieldNamesByClass = app_project_output_data_class_raw_field_names_by_class($dataClassSnapshot['items']);
    $parentByClass = app_project_output_data_class_parent_by_class($dataClassSnapshot['items']);
    $effectiveFieldNameCache = [];
    $effectiveFieldNameStack = [];

    $classItemResult = app_fetch_db_access_class_metadata($app, $normalizedProjectKey, $normalizedSourceName);
    if (!$classItemResult['ok']) {
        return [
            'ok' => false,
            'entity' => null,
            'error' => 'db access class metadata の読み込みに失敗しました: ' . $classItemResult['error'],
        ];
    }

    $classItem = $classItemResult['item'] ?? [
        'source_name' => $normalizedSourceName,
        'store_base_path' => '',
        'is_autoload' => '1',
        'notes' => '',
        'source_of_truth' => 'sync-bootstrap',
    ];

    $functionCatalogResult = app_fetch_db_access_function_metadata_catalog($app, $normalizedProjectKey, $normalizedSourceName);
    if (!$functionCatalogResult['ok']) {
        return [
            'ok' => false,
            'entity' => null,
            'error' => 'db access function metadata の読み込みに失敗しました: ' . $functionCatalogResult['error'],
        ];
    }

    $functionItems = $functionCatalogResult['items'];
    if ($functionItems === []) {
        $functionItems = [];
        foreach (app_project_db_access_bootstrap_method_catalog_from_table($tableItemResult['item']) as $method) {
            $functionProfile = app_project_db_access_guess_function_profile((string) ($method['name'] ?? ''));
            $functionItems[] = [
                'function_name' => (string) ($method['name'] ?? ''),
                'function_list_order' => (string) ((int) ($method['line'] ?? 0)),
                'function_suffix' => (string) ($functionProfile['function_suffix_candidate'] ?? ''),
                'action_type' => (string) ($functionProfile['legacy_action_type'] ?? ''),
                'data_class_base_name' => in_array(
                    (string) ($functionProfile['legacy_action_type'] ?? ''),
                    ['SELECTSINGLE', 'SELECTLIST'],
                    true,
                ) ? $normalizedSourceName : '',
                'target_table_name' => in_array(
                    (string) ($functionProfile['legacy_action_type'] ?? ''),
                    ['INSERT', 'UPDATE', 'DELETE'],
                    true,
                ) ? $normalizedSourceName : '',
                'parameter_type' => in_array(
                    (string) ($functionProfile['legacy_action_type'] ?? ''),
                    ['INSERT', 'UPDATE'],
                    true,
                ) ? 'classobject' : '',
                'select_by_distinct' => '0',
                'sort_order_columns' => app_project_db_access_bootstrap_default_sort_order_columns($tableItemResult['item']),
                'memo' => '',
                'limit_parameter_type' => '',
                'limit_fixed_parameter' => '',
                'or_group_type' => '',
                'single_proxy_auth_type' => 'NoSecurity',
                'single_proxy_single_get_function_name' => '',
                'is_blob_target' => '0',
                'detected_signature' => (string) ($method['signature'] ?? ''),
                'detected_line' => (string) ((int) ($method['line'] ?? 0)),
                'source_of_truth' => 'sync-bootstrap',
            ];
        }
    }

    $runtimeRoot = app_project_db_access_bootstrap_runtime_root($app, $normalizedProjectKey);

    try {
        app_project_db_access_bootstrap_generate_data_class_files(
            $runtimeRoot,
            $normalizedSourceName,
            $dataClassByName,
            $outputNameByClass,
            $storeBasePathByClass,
            $rawFieldNamesByClass,
            $parentByClass,
            $effectiveFieldNameCache,
            $effectiveFieldNameStack,
        );
        app_project_db_access_bootstrap_generate_dbaccess_files(
            $runtimeRoot,
            $tableItemResult['item'],
            $classItem,
            $functionItems,
        );
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'entity' => null,
            'error' => 'canonical runtime entity の生成に失敗しました: ' . $throwable->getMessage(),
        ];
    }

    $storeBasePath = trim(str_replace('\\', '/', (string) ($classItem['store_base_path'] ?? '')), '/');
    $outputSourceName = (string) ($outputNameByClass[$normalizedSourceName] ?? $normalizedSourceName);
    $dataWrapperRelativePath = app_project_output_data_class_wrapper_relative_path($storeBasePathByClass[$normalizedSourceName] ?? '', $outputSourceName);
    $dbAccessWrapperRelativePath = app_project_output_db_access_wrapper_relative_path($storeBasePath, $outputSourceName);
    $dataProperties = app_generated_file_property_names($runtimeRoot . '/' . $dataWrapperRelativePath);
    if ($dataProperties === []) {
        $dataProperties = array_values(array_unique(array_filter(
            array_map(
                static fn (array $field): string => app_project_output_data_class_output_field_name($field),
                array_values(array_filter(
                    $dataClassByName[$normalizedSourceName]['fields'] ?? [],
                    static fn (mixed $field): bool => is_array($field),
                )),
            ),
            static fn (string $fieldName): bool => $fieldName !== '',
        )));
    }

    return [
        'ok' => true,
        'entity' => [
            'source_name' => $normalizedSourceName,
            'data_file' => basename($dataWrapperRelativePath),
            'dbaccess_file' => basename($dbAccessWrapperRelativePath),
            'data_path' => $runtimeRoot . '/' . $dataWrapperRelativePath,
            'dbaccess_path' => $runtimeRoot . '/' . $dbAccessWrapperRelativePath,
            'has_data_file' => true,
            'has_dbaccess_file' => true,
            'source_kind' => 'canonical-bootstrap',
            'data_class' => $outputSourceName . 'Data',
            'data_list_class' => $outputSourceName . 'DataList',
            'dbaccess_class' => $outputSourceName . 'DBAccess',
            'data_properties' => $dataProperties,
        ],
        'error' => '',
    ];
}
