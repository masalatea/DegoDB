<?php

declare(strict_types=1);

function app_legacy_db_access_dump_sql_unescape(string $value): string
{
    return strtr(
        $value,
        [
            "\\0" => "\0",
            "\\n" => "\n",
            "\\r" => "\r",
            "\\t" => "\t",
            "\\Z" => chr(26),
            "\\'" => "'",
            '\\"' => '"',
            "\\\\" => "\\",
        ],
    );
}

function app_legacy_db_access_dump_map_function_name(string $legacyName, string $legacyActionType): string
{
    $normalizedName = trim($legacyName);
    if ($normalizedName === '') {
        return '';
    }

    return match (strtolower(trim($legacyActionType))) {
        'selectsingle' => 'Get' . $normalizedName,
        'selectlist' => 'Get' . $normalizedName . 'List',
        'insert' => 'Insert' . $normalizedName,
        'update' => 'Update' . $normalizedName,
        'delete' => 'Delete' . $normalizedName,
        default => $normalizedName,
    };
}

function app_legacy_db_access_dump_function_lookup_key(string $sourceName, string $functionName): string
{
    return strtolower(trim($sourceName)) . "\n" . strtolower(trim($functionName));
}

/**
 * @return array{
 *     ok:bool,
 *     db_access_classes:list<array{
 *         project_pid:int,
 *         legacy_da_pid:int,
 *         source_name:string,
 *         store_base_path:string,
 *         is_autoload:string,
 *         last_modified_dt:string
 *     }>,
 *     functions:list<array{
 *         project_pid:int,
 *         legacy_da_pid:int,
 *         legacy_function_pid:int,
 *         source_name:string,
 *         function_name:string,
 *         action_type:string,
 *         target_table_name:string,
 *         parameter_type:string,
 *         select_by_distinct:int,
 *         sort_order_columns:string,
 *         data_class_base_name:string,
 *         function_list_order:int,
 *         memo:string,
 *         limit_parameter_type:string,
 *         limit_fixed_parameter:string,
 *         or_group_type:string,
 *         single_proxy_auth_type:string,
 *         single_proxy_single_get_function_pid:int,
 *         is_blob_target:int
 *     }>,
 *     select_wheres:list<array{
 *         project_pid:int,
 *         legacy_da_pid:int,
 *         legacy_function_pid:int,
 *         source_name:string,
 *         function_name:string,
 *         function_list_order:int,
 *         target_table_name:string,
 *         target_table_alias_name:string,
 *         target_table_column_name:string,
 *         parameter_type:string,
 *         parameter_data_type:string,
 *         fixed_parameter:string,
 *         another_table_name:string,
 *         another_table_alias_name:string,
 *         another_field_name:string,
 *         join_type:string,
 *         or_group:string,
 *         relational_operator:string,
 *         where_order:int
 *     }>,
 *     select_target_fields:list<array{
 *         project_pid:int,
 *         legacy_da_pid:int,
 *         legacy_function_pid:int,
 *         source_name:string,
 *         function_name:string,
 *         function_list_order:int,
 *         target_table_name:string,
 *         target_table_alias_name:string,
 *         target_table_column_name:string,
 *         target_table_column_prefix:string,
 *         target_table_column_suffix:string,
 *         store_class_field_name:string,
 *         group_by_target:int,
 *         field_list_order:int
 *     }>,
 *     insert_target_fields:list<array{
 *         project_pid:int,
 *         legacy_da_pid:int,
 *         legacy_function_pid:int,
 *         source_name:string,
 *         function_name:string,
 *         function_list_order:int,
 *         target_table_column_name:string,
 *         parameter_type:string,
 *         parameter_data_type:string,
 *         fixed_parameter:string,
 *         field_list_order:int
 *     }>,
 *     update_target_fields:list<array{
 *         project_pid:int,
 *         legacy_da_pid:int,
 *         legacy_function_pid:int,
 *         source_name:string,
 *         function_name:string,
 *         function_list_order:int,
 *         target_table_column_name:string,
 *         parameter_type:string,
 *         parameter_data_type:string,
 *         fixed_parameter:string,
 *         field_list_order:int
 *     }>,
 *     update_delete_wheres:list<array{
 *         project_pid:int,
 *         legacy_da_pid:int,
 *         legacy_function_pid:int,
 *         source_name:string,
 *         function_name:string,
 *         function_list_order:int,
 *         target_table_column_name:string,
 *         parameter_type:string,
 *         parameter_data_type:string,
 *         fixed_parameter:string,
 *         or_group:string,
 *         relational_operator:string,
 *         where_order:int
 *     }>,
 *     select_havings:list<array{
 *         project_pid:int,
 *         legacy_da_pid:int,
 *         legacy_function_pid:int,
 *         source_name:string,
 *         function_name:string,
 *         function_list_order:int,
 *         having_list_order:int
 *     }>,
 *     error:string
 * }
 */
function app_legacy_db_access_extract_seed_export_data_from_dump(string $sqlDumpPath, int $projectPid): array
{
    if (!is_file($sqlDumpPath)) {
        return [
            'ok' => false,
            'db_access_classes' => [],
            'functions' => [],
            'select_wheres' => [],
            'select_target_fields' => [],
            'insert_target_fields' => [],
            'update_target_fields' => [],
            'update_delete_wheres' => [],
            'select_havings' => [],
            'error' => 'SQL dump が見つかりません: ' . $sqlDumpPath,
        ];
    }

    $handle = fopen($sqlDumpPath, 'rb');
    if (!is_resource($handle)) {
        return [
            'ok' => false,
            'db_access_classes' => [],
            'functions' => [],
            'select_wheres' => [],
            'select_target_fields' => [],
            'insert_target_fields' => [],
            'update_target_fields' => [],
            'update_delete_wheres' => [],
            'select_havings' => [],
            'error' => 'SQL dump を開けません: ' . $sqlDumpPath,
        ];
    }

    $dbAccessClasses = [];
    $functions = [];
    $selectWheres = [];
    $selectTargetFields = [];
    $insertTargetFields = [];
    $updateTargetFields = [];
    $updateDeleteWheres = [];
    $selectHavings = [];
    $sourceNameByLegacyDaPid = [];
    $functionContextByLegacyFunctionPid = [];
    $currentSection = '';
    $sectionHeaders = [
        'INSERT INTO `da` (`ProjectPID`, `PID`, `name`, `StoreBasePath`, `IsAutoload`, `LastModifiedDT`) VALUES' => 'da',
        'INSERT INTO `dafunc` (`ProjectPID`, `daPID`, `PID`, `name`, `ActionType`, `InsertUpdateDeleteTargetTable`, `InsertUpdateDeleteParamType`, `SelectByDistinct`, `SortOrderColumns`, `DataClassBaseNameForSelectAction`, `FunctionListOrder`, `memo`, `limitParameterType`, `limitFixedParameter`, `ORGroupType`, `SingleProxy_AuthType`, `SingleProxy_SingleGetFuncPID`, `IsBlobTarget`) VALUES' => 'dafunc',
        'INSERT INTO `dafuncselectwhere` (`ProjectPID`, `daPID`, `dafuncPID`, `PID`, `targetTableName`, `targetTableAliasName`, `targetTableColumnName`, `ParameterType`, `ParameterDataType`, `FixedParameter`, `AnotherTableName`, `AnotherTableAliasName`, `AnotherFieldName`, `JoinType`, `ORGroup`, `RelationalOperator`, `WhereOrder`) VALUES' => 'selectwhere',
        'INSERT INTO `dafuncselecttargetfields` (`ProjectPID`, `daPID`, `dafuncPID`, `PID`, `targetTableName`, `targetTableAliasName`, `targetTableColumnName`, `targetTableColumnPrefix`, `targetTableColumnSuffix`, `storeClassFieldName`, `GroupByTarget`, `FieldListOrder`) VALUES' => 'selecttargetfields',
        'INSERT INTO `dafuncinserttargetfields` (`ProjectPID`, `daPID`, `dafuncPID`, `PID`, `targetTableColumnName`, `ParameterType`, `ParameterDataType`, `FixedParameter`, `FieldListOrder`) VALUES' => 'inserttargetfields',
        'INSERT INTO `dafuncupdatetargetfields` (`ProjectPID`, `daPID`, `dafuncPID`, `PID`, `targetTableColumnName`, `ParameterType`, `ParameterDataType`, `FixedParameter`, `FieldListOrder`) VALUES' => 'updatetargetfields',
        'INSERT INTO `dafuncupdatedeletewhere` (`ProjectPID`, `daPID`, `dafuncPID`, `PID`, `targetTableColumnName`, `ParameterType`, `ParameterDataType`, `FixedParameter`, `ORGroup`, `RelationalOperator`, `WhereOrder`) VALUES' => 'updatedeletewhere',
        'INSERT INTO `dafuncselecthaving` (`ProjectPID`, `daPID`, `dafuncPID`, `PID`, `LeftTargetPrefix`, `LeftTargetFieldPID`, `LeftTargetSuffix`, `RelationalOperator`, `RightTargetPrefix`, `RightParameterType`, `RightParameterDataType`, `RightFixedParameter`, `RightTargetFieldPID`, `RightTargetSuffix`, `HavingListOrder`) VALUES' => 'selecthaving',
    ];

    try {
        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            if (isset($sectionHeaders[$trimmed])) {
                $currentSection = $sectionHeaders[$trimmed];
                continue;
            }

            if ($currentSection === '') {
                continue;
            }

            if ($currentSection === 'da') {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)'\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $rowProjectPid = (int) ($match[1] ?? 0);
                        if ($rowProjectPid !== $projectPid) {
                            continue;
                        }

                        $legacyDaPid = (int) ($match[2] ?? 0);
                        $sourceName = app_legacy_db_access_dump_sql_unescape((string) ($match[3] ?? ''));
                        if ($legacyDaPid <= 0 || $sourceName === '') {
                            continue;
                        }

                        $dbAccessClasses[] = [
                            'project_pid' => $rowProjectPid,
                            'legacy_da_pid' => $legacyDaPid,
                            'source_name' => $sourceName,
                            'store_base_path' => app_legacy_db_access_dump_sql_unescape((string) ($match[4] ?? '')),
                            'is_autoload' => ((int) ($match[5] ?? 0)) === 1 ? '1' : '0',
                            'last_modified_dt' => app_legacy_db_access_dump_sql_unescape((string) ($match[6] ?? '')),
                        ];
                        $sourceNameByLegacyDaPid[$legacyDaPid] = $sourceName;
                    }
                }
            } elseif ($currentSection === 'dafunc') {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(\\d+),\\s*(\\d+)\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $rowProjectPid = (int) ($match[1] ?? 0);
                        if ($rowProjectPid !== $projectPid) {
                            continue;
                        }

                        $legacyDaPid = (int) ($match[2] ?? 0);
                        $legacyFunctionPid = (int) ($match[3] ?? 0);
                        $sourceName = $sourceNameByLegacyDaPid[$legacyDaPid] ?? '';
                        $legacyFunctionName = app_legacy_db_access_dump_sql_unescape((string) ($match[4] ?? ''));
                        $legacyActionType = app_legacy_db_access_dump_sql_unescape((string) ($match[5] ?? ''));
                        $functionName = app_legacy_db_access_dump_map_function_name($legacyFunctionName, $legacyActionType);
                        if ($legacyDaPid <= 0 || $legacyFunctionPid <= 0 || $sourceName === '' || $functionName === '') {
                            continue;
                        }

                        $functionRow = [
                            'project_pid' => $rowProjectPid,
                            'legacy_da_pid' => $legacyDaPid,
                            'legacy_function_pid' => $legacyFunctionPid,
                            'source_name' => $sourceName,
                            'function_name' => $functionName,
                            'action_type' => strtoupper($legacyActionType),
                            'target_table_name' => app_legacy_db_access_dump_sql_unescape((string) ($match[6] ?? '')),
                            'parameter_type' => app_legacy_db_access_dump_sql_unescape((string) ($match[7] ?? '')),
                            'select_by_distinct' => (int) ($match[8] ?? 0),
                            'sort_order_columns' => app_legacy_db_access_dump_sql_unescape((string) ($match[9] ?? '')),
                            'data_class_base_name' => app_legacy_db_access_dump_sql_unescape((string) ($match[10] ?? '')),
                            'function_list_order' => (int) ($match[11] ?? 0),
                            'memo' => app_legacy_db_access_dump_sql_unescape((string) ($match[12] ?? '')),
                            'limit_parameter_type' => app_legacy_db_access_dump_sql_unescape((string) ($match[13] ?? '')),
                            'limit_fixed_parameter' => app_legacy_db_access_dump_sql_unescape((string) ($match[14] ?? '')),
                            'or_group_type' => app_legacy_db_access_dump_sql_unescape((string) ($match[15] ?? '')),
                            'single_proxy_auth_type' => app_legacy_db_access_dump_sql_unescape((string) ($match[16] ?? '')),
                            'single_proxy_single_get_function_pid' => (int) ($match[17] ?? 0),
                            'is_blob_target' => (int) ($match[18] ?? 0),
                        ];
                        $functions[] = $functionRow;
                        $functionContextByLegacyFunctionPid[$legacyFunctionPid] = $functionRow;
                    }
                }
            } elseif ($currentSection === 'selectwhere') {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*(\\d+),\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(\\d+)\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $rowProjectPid = (int) ($match[1] ?? 0);
                        $legacyFunctionPid = (int) ($match[3] ?? 0);
                        $context = $functionContextByLegacyFunctionPid[$legacyFunctionPid] ?? null;
                        if ($rowProjectPid !== $projectPid || !is_array($context)) {
                            continue;
                        }

                        $selectWheres[] = [
                            'project_pid' => $rowProjectPid,
                            'legacy_da_pid' => (int) ($match[2] ?? 0),
                            'legacy_function_pid' => $legacyFunctionPid,
                            'source_name' => $context['source_name'],
                            'function_name' => $context['function_name'],
                            'function_list_order' => $context['function_list_order'],
                            'target_table_name' => app_legacy_db_access_dump_sql_unescape((string) ($match[5] ?? '')),
                            'target_table_alias_name' => app_legacy_db_access_dump_sql_unescape((string) ($match[6] ?? '')),
                            'target_table_column_name' => app_legacy_db_access_dump_sql_unescape((string) ($match[7] ?? '')),
                            'parameter_type' => app_legacy_db_access_dump_sql_unescape((string) ($match[8] ?? '')),
                            'parameter_data_type' => app_legacy_db_access_dump_sql_unescape((string) ($match[9] ?? '')),
                            'fixed_parameter' => app_legacy_db_access_dump_sql_unescape((string) ($match[10] ?? '')),
                            'another_table_name' => app_legacy_db_access_dump_sql_unescape((string) ($match[11] ?? '')),
                            'another_table_alias_name' => app_legacy_db_access_dump_sql_unescape((string) ($match[12] ?? '')),
                            'another_field_name' => app_legacy_db_access_dump_sql_unescape((string) ($match[13] ?? '')),
                            'join_type' => app_legacy_db_access_dump_sql_unescape((string) ($match[14] ?? '')),
                            'or_group' => app_legacy_db_access_dump_sql_unescape((string) ($match[15] ?? '')),
                            'relational_operator' => app_legacy_db_access_dump_sql_unescape((string) ($match[16] ?? '')),
                            'where_order' => (int) ($match[17] ?? 0),
                        ];
                    }
                }
            } elseif ($currentSection === 'selecttargetfields') {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*(\\d+),\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(\\d+),\\s*(\\d+)\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $rowProjectPid = (int) ($match[1] ?? 0);
                        $legacyFunctionPid = (int) ($match[3] ?? 0);
                        $context = $functionContextByLegacyFunctionPid[$legacyFunctionPid] ?? null;
                        if ($rowProjectPid !== $projectPid || !is_array($context)) {
                            continue;
                        }

                        $selectTargetFields[] = [
                            'project_pid' => $rowProjectPid,
                            'legacy_da_pid' => (int) ($match[2] ?? 0),
                            'legacy_function_pid' => $legacyFunctionPid,
                            'source_name' => $context['source_name'],
                            'function_name' => $context['function_name'],
                            'function_list_order' => $context['function_list_order'],
                            'target_table_name' => app_legacy_db_access_dump_sql_unescape((string) ($match[5] ?? '')),
                            'target_table_alias_name' => app_legacy_db_access_dump_sql_unescape((string) ($match[6] ?? '')),
                            'target_table_column_name' => app_legacy_db_access_dump_sql_unescape((string) ($match[7] ?? '')),
                            'target_table_column_prefix' => app_legacy_db_access_dump_sql_unescape((string) ($match[8] ?? '')),
                            'target_table_column_suffix' => app_legacy_db_access_dump_sql_unescape((string) ($match[9] ?? '')),
                            'store_class_field_name' => app_legacy_db_access_dump_sql_unescape((string) ($match[10] ?? '')),
                            'group_by_target' => (int) ($match[11] ?? 0),
                            'field_list_order' => (int) ($match[12] ?? 0),
                        ];
                    }
                }
            } elseif ($currentSection === 'inserttargetfields' || $currentSection === 'updatetargetfields') {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*(\\d+),\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(\\d+)\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $rowProjectPid = (int) ($match[1] ?? 0);
                        $legacyFunctionPid = (int) ($match[3] ?? 0);
                        $context = $functionContextByLegacyFunctionPid[$legacyFunctionPid] ?? null;
                        if ($rowProjectPid !== $projectPid || !is_array($context)) {
                            continue;
                        }

                        $row = [
                            'project_pid' => $rowProjectPid,
                            'legacy_da_pid' => (int) ($match[2] ?? 0),
                            'legacy_function_pid' => $legacyFunctionPid,
                            'source_name' => $context['source_name'],
                            'function_name' => $context['function_name'],
                            'function_list_order' => $context['function_list_order'],
                            'target_table_column_name' => app_legacy_db_access_dump_sql_unescape((string) ($match[5] ?? '')),
                            'parameter_type' => app_legacy_db_access_dump_sql_unescape((string) ($match[6] ?? '')),
                            'parameter_data_type' => app_legacy_db_access_dump_sql_unescape((string) ($match[7] ?? '')),
                            'fixed_parameter' => app_legacy_db_access_dump_sql_unescape((string) ($match[8] ?? '')),
                            'field_list_order' => (int) ($match[9] ?? 0),
                        ];
                        if ($currentSection === 'inserttargetfields') {
                            $insertTargetFields[] = $row;
                        } else {
                            $updateTargetFields[] = $row;
                        }
                    }
                }
            } elseif ($currentSection === 'updatedeletewhere') {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*(\\d+),\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(\\d+)\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $rowProjectPid = (int) ($match[1] ?? 0);
                        $legacyFunctionPid = (int) ($match[3] ?? 0);
                        $context = $functionContextByLegacyFunctionPid[$legacyFunctionPid] ?? null;
                        if ($rowProjectPid !== $projectPid || !is_array($context)) {
                            continue;
                        }

                        $updateDeleteWheres[] = [
                            'project_pid' => $rowProjectPid,
                            'legacy_da_pid' => (int) ($match[2] ?? 0),
                            'legacy_function_pid' => $legacyFunctionPid,
                            'source_name' => $context['source_name'],
                            'function_name' => $context['function_name'],
                            'function_list_order' => $context['function_list_order'],
                            'target_table_column_name' => app_legacy_db_access_dump_sql_unescape((string) ($match[5] ?? '')),
                            'parameter_type' => app_legacy_db_access_dump_sql_unescape((string) ($match[6] ?? '')),
                            'parameter_data_type' => app_legacy_db_access_dump_sql_unescape((string) ($match[7] ?? '')),
                            'fixed_parameter' => app_legacy_db_access_dump_sql_unescape((string) ($match[8] ?? '')),
                            'or_group' => app_legacy_db_access_dump_sql_unescape((string) ($match[9] ?? '')),
                            'relational_operator' => app_legacy_db_access_dump_sql_unescape((string) ($match[10] ?? '')),
                            'where_order' => (int) ($match[11] ?? 0),
                        ];
                    }
                }
            } elseif ($currentSection === 'selecthaving') {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*(\\d+),\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(\\d+)\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $rowProjectPid = (int) ($match[1] ?? 0);
                        $legacyFunctionPid = (int) ($match[3] ?? 0);
                        $context = $functionContextByLegacyFunctionPid[$legacyFunctionPid] ?? null;
                        if ($rowProjectPid !== $projectPid || !is_array($context)) {
                            continue;
                        }

                        $selectHavings[] = [
                            'project_pid' => $rowProjectPid,
                            'legacy_da_pid' => (int) ($match[2] ?? 0),
                            'legacy_function_pid' => $legacyFunctionPid,
                            'source_name' => $context['source_name'],
                            'function_name' => $context['function_name'],
                            'function_list_order' => $context['function_list_order'],
                            'having_list_order' => (int) ($match[15] ?? 0),
                        ];
                    }
                }
            }

            if (str_ends_with($trimmed, ';')) {
                $currentSection = '';
            }
        }
    } finally {
        fclose($handle);
    }

    usort(
        $dbAccessClasses,
        static function (array $left, array $right): int {
            if ($left['legacy_da_pid'] !== $right['legacy_da_pid']) {
                return $left['legacy_da_pid'] <=> $right['legacy_da_pid'];
            }

            return strcmp($left['source_name'], $right['source_name']);
        },
    );
    usort(
        $functions,
        static function (array $left, array $right): int {
            if ($left['legacy_da_pid'] !== $right['legacy_da_pid']) {
                return $left['legacy_da_pid'] <=> $right['legacy_da_pid'];
            }
            if ($left['function_list_order'] !== $right['function_list_order']) {
                return $left['function_list_order'] <=> $right['function_list_order'];
            }

            return strcmp($left['function_name'], $right['function_name']);
        },
    );

    return [
        'ok' => true,
        'db_access_classes' => $dbAccessClasses,
        'functions' => $functions,
        'select_wheres' => $selectWheres,
        'select_target_fields' => $selectTargetFields,
        'insert_target_fields' => $insertTargetFields,
        'update_target_fields' => $updateTargetFields,
        'update_delete_wheres' => $updateDeleteWheres,
        'select_havings' => $selectHavings,
        'error' => '',
    ];
}

/**
 * @param list<array<string,mixed>> $canonicalFunctionRows
 * @param array{
 *     functions:list<array<string,mixed>>,
 *     select_wheres:list<array<string,mixed>>,
 *     select_target_fields:list<array<string,mixed>>,
 *     insert_target_fields:list<array<string,mixed>>,
 *     update_target_fields:list<array<string,mixed>>,
 *     update_delete_wheres:list<array<string,mixed>>,
 *     select_havings:list<array<string,mixed>>
 * } $legacyData
 * @return array{
 *     selectlist_sort_order_rows:list<array{source_name:string,function_name:string,sort_order_columns:string}>,
 *     designer_rows:array{
 *         select_wheres:list<array<string,mixed>>,
 *         select_target_fields:list<array<string,mixed>>,
 *         insert_target_fields:list<array<string,mixed>>,
 *         update_target_fields:list<array<string,mixed>>,
 *         update_delete_wheres:list<array<string,mixed>>
 *     },
 *     select_having_count:int
 * }
 */
function app_legacy_db_access_build_seed_export_rows_from_dump(array $legacyData, array $canonicalFunctionRows): array
{
    $canonicalLookup = [];
    foreach ($canonicalFunctionRows as $row) {
        $sourceName = trim((string) ($row['source_name'] ?? ''));
        $functionName = trim((string) ($row['function_name'] ?? ''));
        if ($sourceName === '' || $functionName === '') {
            continue;
        }

        $canonicalLookup[app_legacy_db_access_dump_function_lookup_key($sourceName, $functionName)] = true;
    }

    $sortOrderRows = [];
    foreach ($legacyData['functions'] as $row) {
        if (
            strtoupper((string) ($row['action_type'] ?? '')) !== 'SELECTLIST'
            || trim((string) ($row['sort_order_columns'] ?? '')) === ''
        ) {
            continue;
        }

        $sourceName = (string) ($row['source_name'] ?? '');
        $functionName = (string) ($row['function_name'] ?? '');
        if (!isset($canonicalLookup[app_legacy_db_access_dump_function_lookup_key($sourceName, $functionName)])) {
            continue;
        }

        $sortOrderRows[] = [
            'source_name' => $sourceName,
            'function_name' => $functionName,
            'function_list_order' => (int) ($row['function_list_order'] ?? 0),
            'sort_order_columns' => (string) ($row['sort_order_columns'] ?? ''),
        ];
    }
    usort(
        $sortOrderRows,
        static function (array $left, array $right): int {
            $sourceComparison = strcmp($left['source_name'], $right['source_name']);
            if ($sourceComparison !== 0) {
                return $sourceComparison;
            }
            if ($left['function_list_order'] !== $right['function_list_order']) {
                return $left['function_list_order'] <=> $right['function_list_order'];
            }

            return strcmp($left['function_name'], $right['function_name']);
        },
    );

    $designerRows = [
        'select_wheres' => app_legacy_db_access_filter_dump_designer_rows(
            $legacyData['select_wheres'],
            $canonicalLookup,
            ['function_list_order', 'legacy_da_pid', 'legacy_function_pid', 'project_pid'],
            ['where_order'],
        ),
        'select_target_fields' => app_legacy_db_access_filter_dump_designer_rows(
            $legacyData['select_target_fields'],
            $canonicalLookup,
            ['function_list_order', 'legacy_da_pid', 'legacy_function_pid', 'project_pid'],
            ['field_list_order'],
        ),
        'insert_target_fields' => app_legacy_db_access_filter_dump_designer_rows(
            $legacyData['insert_target_fields'],
            $canonicalLookup,
            ['function_list_order', 'legacy_da_pid', 'legacy_function_pid', 'project_pid'],
            ['field_list_order'],
        ),
        'update_target_fields' => app_legacy_db_access_filter_dump_designer_rows(
            $legacyData['update_target_fields'],
            $canonicalLookup,
            ['function_list_order', 'legacy_da_pid', 'legacy_function_pid', 'project_pid'],
            ['field_list_order'],
        ),
        'update_delete_wheres' => app_legacy_db_access_filter_dump_designer_rows(
            $legacyData['update_delete_wheres'],
            $canonicalLookup,
            ['function_list_order', 'legacy_da_pid', 'legacy_function_pid', 'project_pid'],
            ['where_order'],
        ),
    ];

    $selectHavingCount = 0;
    foreach ($legacyData['select_havings'] as $row) {
        $sourceName = (string) ($row['source_name'] ?? '');
        $functionName = (string) ($row['function_name'] ?? '');
        if (isset($canonicalLookup[app_legacy_db_access_dump_function_lookup_key($sourceName, $functionName)])) {
            $selectHavingCount++;
        }
    }

    return [
        'selectlist_sort_order_rows' => array_values(
            array_map(
                static function (array $row): array {
                    return [
                        'source_name' => $row['source_name'],
                        'function_name' => $row['function_name'],
                        'sort_order_columns' => $row['sort_order_columns'],
                    ];
                },
                $sortOrderRows,
            ),
        ),
        'designer_rows' => $designerRows,
        'select_having_count' => $selectHavingCount,
    ];
}

/**
 * @param list<array<string,mixed>> $rows
 * @param array<string,bool> $canonicalLookup
 * @param list<string> $dropKeys
 * @param list<string> $orderKeys
 * @return list<array<string,mixed>>
 */
function app_legacy_db_access_filter_dump_designer_rows(
    array $rows,
    array $canonicalLookup,
    array $dropKeys,
    array $orderKeys,
): array {
    $filtered = [];
    foreach ($rows as $row) {
        $sourceName = (string) ($row['source_name'] ?? '');
        $functionName = (string) ($row['function_name'] ?? '');
        if (!isset($canonicalLookup[app_legacy_db_access_dump_function_lookup_key($sourceName, $functionName)])) {
            continue;
        }

        foreach ($dropKeys as $dropKey) {
            unset($row[$dropKey]);
        }
        $row['source_of_truth'] = 'seed-legacy';
        $filtered[] = $row;
    }

    usort(
        $filtered,
        static function (array $left, array $right) use ($orderKeys): int {
            $sourceComparison = strcmp((string) ($left['source_name'] ?? ''), (string) ($right['source_name'] ?? ''));
            if ($sourceComparison !== 0) {
                return $sourceComparison;
            }
            $functionComparison = strcmp((string) ($left['function_name'] ?? ''), (string) ($right['function_name'] ?? ''));
            if ($functionComparison !== 0) {
                return $functionComparison;
            }

            foreach ($orderKeys as $orderKey) {
                $leftValue = (int) ($left[$orderKey] ?? 0);
                $rightValue = (int) ($right[$orderKey] ?? 0);
                if ($leftValue !== $rightValue) {
                    return $leftValue <=> $rightValue;
                }
            }

            return 0;
        },
    );

    return $filtered;
}
