<?php

declare(strict_types=1);

require_once __DIR__ . '/data_class_repository.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/runtime_storage_paths.php';
require_once __DIR__ . '/source_output_repository.php';
require_once __DIR__ . '/table_metadata_repository.php';

function app_project_output_ai_context_strategy_is_supported(string $strategy): bool
{
    return $strategy === 'ai-context-md';
}

function app_project_output_ai_context_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_ai_context_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

function app_project_output_ai_context_file_name(string $name): string
{
    $normalized = trim($name);
    $normalized = preg_replace('/[^A-Za-z0-9._-]+/', '-', $normalized) ?? '';
    $normalized = trim($normalized, '-');

    return $normalized !== '' ? $normalized : 'unknown';
}

function app_project_output_ai_context_markdown_value(string $value): string
{
    $trimmed = trim($value);

    return $trimmed !== '' ? str_replace(["\r\n", "\r"], "\n", $trimmed) : 'unknown';
}

function app_project_output_ai_context_markdown_cell(string $value): string
{
    return str_replace(["\n", '|'], [' ', '\\|'], app_project_output_ai_context_markdown_value($value));
}

function app_project_output_ai_context_column_is_primary_key(array $column): bool
{
    $value = strtoupper(trim((string) ($column['is_key'] ?? '')));

    return in_array($value, ['1', 'PRI', 'PRIMARY', 'PRIMARY KEY'], true);
}

/**
 * @param array<mixed> $value
 * @return array<mixed>
 */
function app_project_output_ai_context_stable_metadata(array $value): array
{
    $volatileKeys = [
        'id' => true,
        'pid' => true,
        'project_pid' => true,
        'table_pid' => true,
        'column_project_pid' => true,
        'column_table_pid' => true,
        'column_pid' => true,
        'dbtable_pid' => true,
        'dataclass_pid' => true,
        'field_project_pid' => true,
        'field_dataclass_pid' => true,
        'field_pid' => true,
        'select_where_id' => true,
        'created_at' => true,
        'updated_at' => true,
        'last_modified_dt' => true,
    ];

    $stable = [];
    foreach ($value as $key => $item) {
        if (is_string($key) && isset($volatileKeys[$key])) {
            continue;
        }

        if (is_array($item)) {
            $stable[$key] = app_project_output_ai_context_stable_metadata($item);
            continue;
        }

        $stable[$key] = $item;
    }

    return $stable;
}

/**
 * @param array<string,mixed> $payload
 */
function app_project_output_ai_context_json_text(array $payload): string
{
    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );
    if (!is_string($json) || $json === '') {
        throw new RuntimeException('AI context JSON の生成に失敗しました。');
    }

    return $json . PHP_EOL;
}

/**
 * @param list<array<string,mixed>> $items
 * @return array<string,array<string,mixed>>
 */
function app_project_output_ai_context_index_by_name(array $items, string $fieldName = 'name'): array
{
    $index = [];
    foreach ($items as $item) {
        $name = trim((string) ($item[$fieldName] ?? ''));
        if ($name === '') {
            continue;
        }
        $index[$name] = $item;
    }

    ksort($index, SORT_STRING);

    return $index;
}

/**
 * @return array{
 *     ok:bool,
 *     context:array<string,mixed>|null,
 *     error:string
 * }
 */
function app_project_output_ai_context_build_context(array $app, string $projectKey, array $definition): array
{
    $projectResult = app_fetch_project_by_key($app, $projectKey);
    if (!$projectResult['ok'] || !is_array($projectResult['item'])) {
        return [
            'ok' => false,
            'context' => null,
            'error' => $projectResult['error'] !== ''
                ? $projectResult['error']
                : 'project が見つかりません: ' . $projectKey,
        ];
    }

    $tableResult = app_fetch_table_metadata_snapshot($app, $projectKey);
    if (!$tableResult['ok']) {
        return [
            'ok' => false,
            'context' => null,
            'error' => 'table metadata の読み込みに失敗しました: ' . $tableResult['error'],
        ];
    }

    $dataClassResult = app_fetch_data_class_metadata_snapshot($app, $projectKey);
    if (!$dataClassResult['ok']) {
        return [
            'ok' => false,
            'context' => null,
            'error' => 'data class metadata の読み込みに失敗しました: ' . $dataClassResult['error'],
        ];
    }

    $dbAccessClassResult = app_fetch_db_access_class_metadata_catalog($app, $projectKey);
    if (!$dbAccessClassResult['ok']) {
        return [
            'ok' => false,
            'context' => null,
            'error' => 'DBAccess class metadata の読み込みに失敗しました: ' . $dbAccessClassResult['error'],
        ];
    }

    $sourceOutputResult = app_fetch_project_source_output_catalog($app, $projectKey);
    if (!$sourceOutputResult['ok']) {
        return [
            'ok' => false,
            'context' => null,
            'error' => 'source output metadata の読み込みに失敗しました: ' . $sourceOutputResult['error'],
        ];
    }

    $dbAccessClasses = [];
    foreach ($dbAccessClassResult['items'] as $classItem) {
        $sourceName = trim((string) ($classItem['source_name'] ?? ''));
        if ($sourceName === '') {
            continue;
        }

        $functionResult = app_fetch_db_access_function_metadata_catalog($app, $projectKey, $sourceName);
        if (!$functionResult['ok']) {
            return [
                'ok' => false,
                'context' => null,
                'error' => 'DBAccess function metadata の読み込みに失敗しました: ' . $functionResult['error'],
            ];
        }

        $functions = [];
        foreach ($functionResult['items'] as $functionItem) {
            $functionName = trim((string) ($functionItem['function_name'] ?? ''));
            if ($functionName === '') {
                continue;
            }

            $selectWhereResult = app_fetch_db_access_function_select_where_catalog(
                $app,
                $projectKey,
                $sourceName,
                $functionName,
            );
            if (!$selectWhereResult['ok']) {
                return [
                    'ok' => false,
                    'context' => null,
                    'error' => 'DBAccess select where metadata の読み込みに失敗しました: ' . $selectWhereResult['error'],
                ];
            }

            $functionItem['select_wheres'] = $selectWhereResult['items'];
            $functions[] = $functionItem;
        }

        $classItem['functions'] = $functions;
        $dbAccessClasses[] = $classItem;
    }

    $tableIndex = app_project_output_ai_context_index_by_name($tableResult['items']);
    $dataClassIndex = app_project_output_ai_context_index_by_name($dataClassResult['items']);

    $relationships = [];
    foreach ($dataClassResult['items'] as $dataClass) {
        foreach (($dataClass['fields'] ?? []) as $field) {
            if (!is_array($field)) {
                continue;
            }
            $refClass = trim((string) ($field['ref_data_class_name'] ?? ''));
            $refField = trim((string) ($field['ref_data_class_field_name'] ?? ''));
            if ($refClass === '' && $refField === '') {
                continue;
            }
            $relationships[] = [
                'source' => 'dataclass-field-reference',
                'from_table' => (string) ($dataClass['name'] ?? ''),
                'from_column' => (string) ($field['name'] ?? ''),
                'to_table' => $refClass,
                'to_column' => $refField,
                'join_type' => '',
                'function_name' => '',
                'confidence' => 'declared-metadata',
            ];
        }
    }

    foreach ($dbAccessClasses as $classItem) {
        foreach (($classItem['functions'] ?? []) as $functionItem) {
            if (!is_array($functionItem)) {
                continue;
            }
            foreach (($functionItem['select_wheres'] ?? []) as $where) {
                if (!is_array($where)) {
                    continue;
                }
                $anotherTable = trim((string) ($where['another_table_name'] ?? ''));
                $anotherField = trim((string) ($where['another_field_name'] ?? ''));
                if ($anotherTable === '' && $anotherField === '') {
                    continue;
                }
                $relationships[] = [
                    'source' => 'dbaccess-select-where',
                    'from_table' => (string) ($where['target_table_name'] ?? ''),
                    'from_column' => (string) ($where['target_table_column_name'] ?? ''),
                    'to_table' => $anotherTable,
                    'to_column' => $anotherField,
                    'join_type' => (string) ($where['join_type'] ?? ''),
                    'function_name' => (string) ($functionItem['function_name'] ?? ''),
                    'confidence' => 'declared-metadata',
                ];
            }
        }
    }

    usort(
        $relationships,
        static fn (array $left, array $right): int => strcmp(
            implode('|', array_map('strval', $left)),
            implode('|', array_map('strval', $right)),
        ),
    );

    $context = [
        'schema_version' => 1,
        'artifact_type' => 'ai-context-md',
        'generation_rule' => [
            'author' => 'DegoDB/Mtool generator code',
            'ai_role' => 'reader-consumer',
            'deterministic' => true,
            'unknown_policy' => 'mark unknown instead of inventing intent',
        ],
        'project' => $projectResult['item'],
        'source_output' => [
            'source_output_key' => (string) ($definition['source_output_key'] ?? ''),
            'name' => (string) ($definition['name'] ?? ''),
            'artifact_strategy' => (string) ($definition['artifact_strategy'] ?? ''),
            'runtime_source_relative_path' => (string) ($definition['runtime_source_relative_path'] ?? ''),
        ],
        'tables' => array_values($tableIndex),
        'data_classes' => array_values($dataClassIndex),
        'db_access_classes' => $dbAccessClasses,
        'relationships' => $relationships,
        'source_outputs' => $sourceOutputResult['items'],
    ];

    return [
        'ok' => true,
        'context' => app_project_output_ai_context_stable_metadata($context),
        'error' => '',
    ];
}

/**
 * @param array<string,mixed> $context
 * @return array<string,string>
 */
function app_project_output_ai_context_build_emitted_files(array $context): array
{
    $project = is_array($context['project'] ?? null) ? $context['project'] : [];
    $projectKey = app_project_output_ai_context_markdown_value((string) ($project['project_key'] ?? ''));
    $projectName = app_project_output_ai_context_markdown_value((string) ($project['name'] ?? ''));
    $tables = is_array($context['tables'] ?? null) ? $context['tables'] : [];
    $dataClasses = is_array($context['data_classes'] ?? null) ? $context['data_classes'] : [];
    $dbAccessClasses = is_array($context['db_access_classes'] ?? null) ? $context['db_access_classes'] : [];
    $relationships = is_array($context['relationships'] ?? null) ? $context['relationships'] : [];
    $sourceOutputs = is_array($context['source_outputs'] ?? null) ? $context['source_outputs'] : [];
    $dataClassIndex = app_project_output_ai_context_index_by_name($dataClasses);

    $files = [];
    $files['README.md'] = "# AI Context\n\n"
        . "Project: `{$projectKey}` / {$projectName}\n\n"
        . "This package is generated by DegoDB / Mtool generator code from canonical metadata. "
        . "AI may read this context, but AI is not the author or source of truth.\n\n"
        . "- Tables: " . count($tables) . "\n"
        . "- Data classes: " . count($dataClasses) . "\n"
        . "- DBAccess classes: " . count($dbAccessClasses) . "\n"
        . "- Relationships: " . count($relationships) . "\n\n"
        . "Generated files:\n\n"
        . "- `schema-summary.md`\n"
        . "- `tables/*.md`\n"
        . "- `relationships.md`\n"
        . "- `risky-areas.md`\n"
        . "- `generation-map.md`\n"
        . "- `agent-instructions.md`\n"
        . "- `schema-context.json`\n";

    $schemaLines = [
        '# Schema Summary',
        '',
        '| Table | Physical Name | Generated Name | Columns | Primary Key Columns |',
        '| --- | --- | --- | ---: | --- |',
    ];
    foreach ($tables as $table) {
        if (!is_array($table)) {
            continue;
        }
        $keyColumns = [];
        foreach (($table['columns'] ?? []) as $column) {
            if (is_array($column) && app_project_output_ai_context_column_is_primary_key($column)) {
                $keyColumns[] = (string) ($column['name'] ?? '');
            }
        }
        $schemaLines[] = '| '
            . app_project_output_ai_context_markdown_cell((string) ($table['name'] ?? '')) . ' | '
            . app_project_output_ai_context_markdown_cell((string) ($table['physical_name'] ?? '')) . ' | '
            . app_project_output_ai_context_markdown_cell((string) ($table['generated_name'] ?? '')) . ' | '
            . (int) ($table['column_count'] ?? 0) . ' | '
            . app_project_output_ai_context_markdown_cell($keyColumns === [] ? 'unknown' : implode(', ', $keyColumns)) . ' |';
    }
    $files['schema-summary.md'] = implode("\n", $schemaLines) . "\n";

    foreach ($tables as $table) {
        if (!is_array($table)) {
            continue;
        }
        $tableName = (string) ($table['name'] ?? '');
        $lines = [
            '# Table: ' . app_project_output_ai_context_markdown_value($tableName),
            '',
            '- Physical name: `' . app_project_output_ai_context_markdown_value((string) ($table['physical_name'] ?? '')) . '`',
            '- Generated name: `' . app_project_output_ai_context_markdown_value((string) ($table['generated_name'] ?? '')) . '`',
            '- Column count: `' . (int) ($table['column_count'] ?? 0) . '`',
            '',
            '## Columns',
            '',
            '| Column | Physical Name | Generated Name | Type | Null | Key | Default | Extra | Memo |',
            '| --- | --- | --- | --- | --- | --- | --- | --- | --- |',
        ];
        foreach (($table['columns'] ?? []) as $column) {
            if (!is_array($column)) {
                continue;
            }
            $lines[] = '| '
                . app_project_output_ai_context_markdown_cell((string) ($column['name'] ?? '')) . ' | '
                . app_project_output_ai_context_markdown_cell((string) ($column['physical_name'] ?? '')) . ' | '
                . app_project_output_ai_context_markdown_cell((string) ($column['generated_name'] ?? '')) . ' | '
                . app_project_output_ai_context_markdown_cell((string) ($column['datatype'] ?? '')) . ' | '
                . app_project_output_ai_context_markdown_cell((string) ($column['is_null'] ?? '')) . ' | '
                . app_project_output_ai_context_markdown_cell((string) ($column['is_key'] ?? '')) . ' | '
                . app_project_output_ai_context_markdown_cell((string) ($column['is_default'] ?? '')) . ' | '
                . app_project_output_ai_context_markdown_cell((string) ($column['extra'] ?? '')) . ' | '
                . app_project_output_ai_context_markdown_cell((string) ($column['memo'] ?? '')) . ' |';
        }
        $files['tables/' . app_project_output_ai_context_file_name($tableName) . '.md'] = implode("\n", $lines) . "\n";
    }

    $relationshipLines = [
        '# Relationships',
        '',
        'Only relationships present in canonical metadata are listed. Unknown relationship intent is not invented.',
        '',
        '| Source | From | To | Join Type | Function | Confidence |',
        '| --- | --- | --- | --- | --- | --- |',
    ];
    foreach ($relationships as $relationship) {
        if (!is_array($relationship)) {
            continue;
        }
        $relationshipLines[] = '| '
            . app_project_output_ai_context_markdown_cell((string) ($relationship['source'] ?? '')) . ' | '
            . app_project_output_ai_context_markdown_cell((string) ($relationship['from_table'] ?? '') . '.' . (string) ($relationship['from_column'] ?? '')) . ' | '
            . app_project_output_ai_context_markdown_cell((string) ($relationship['to_table'] ?? '') . '.' . (string) ($relationship['to_column'] ?? '')) . ' | '
            . app_project_output_ai_context_markdown_cell((string) ($relationship['join_type'] ?? '')) . ' | '
            . app_project_output_ai_context_markdown_cell((string) ($relationship['function_name'] ?? '')) . ' | '
            . app_project_output_ai_context_markdown_cell((string) ($relationship['confidence'] ?? '')) . ' |';
    }
    if ($relationships === []) {
        $relationshipLines[] = '| unknown | unknown | unknown | unknown | unknown | no relationship metadata found |';
    }
    $files['relationships.md'] = implode("\n", $relationshipLines) . "\n";

    $riskLines = [
        '# Risky Areas',
        '',
        '- This file is generated from metadata; it does not infer business intent.',
        '- Do not rename, drop, or rewrite production tables without an explicit migration plan.',
        '- Prefer additive changes when current behavior is unclear.',
        '- Check generated DataClass and DBAccess artifacts before writing custom SQL.',
        '',
        '## Metadata-Derived Notes',
        '',
    ];
    foreach ($tables as $table) {
        if (!is_array($table)) {
            continue;
        }
        $keyCount = 0;
        foreach (($table['columns'] ?? []) as $column) {
            if (is_array($column) && app_project_output_ai_context_column_is_primary_key($column)) {
                $keyCount++;
            }
        }
        if ($keyCount === 0) {
            $riskLines[] = '- `' . app_project_output_ai_context_markdown_value((string) ($table['name'] ?? '')) . '`: primary key metadata is unknown.';
        }
    }
    if (count($riskLines) === 9) {
        $riskLines[] = '- No additional metadata-derived risk notes were found.';
    }
    $files['risky-areas.md'] = implode("\n", $riskLines) . "\n";

    $mapLines = [
        '# Generation Map',
        '',
        '## Source Outputs',
        '',
        '| Key | Strategy | Runtime Source Path | Class Type |',
        '| --- | --- | --- | --- |',
    ];
    foreach ($sourceOutputs as $sourceOutput) {
        if (!is_array($sourceOutput)) {
            continue;
        }
        $mapLines[] = '| '
            . app_project_output_ai_context_markdown_cell((string) ($sourceOutput['source_output_key'] ?? '')) . ' | '
            . app_project_output_ai_context_markdown_cell((string) ($sourceOutput['artifact_strategy'] ?? '')) . ' | '
            . app_project_output_ai_context_markdown_cell((string) ($sourceOutput['runtime_source_relative_path'] ?? '')) . ' | '
            . app_project_output_ai_context_markdown_cell((string) ($sourceOutput['class_type'] ?? '')) . ' |';
    }
    $mapLines[] = '';
    $mapLines[] = '## Table To Generated Artifacts';
    $mapLines[] = '';
    $mapLines[] = '| Table | DataClass | DBAccess Functions |';
    $mapLines[] = '| --- | --- | --- |';
    foreach ($tables as $table) {
        if (!is_array($table)) {
            continue;
        }
        $tableName = (string) ($table['name'] ?? '');
        $dataClass = $dataClassIndex[$tableName] ?? null;
        $functions = [];
        foreach ($dbAccessClasses as $classItem) {
            if (!is_array($classItem) || (string) ($classItem['source_name'] ?? '') !== $tableName) {
                continue;
            }
            foreach (($classItem['functions'] ?? []) as $functionItem) {
                if (is_array($functionItem)) {
                    $functions[] = (string) ($functionItem['function_name'] ?? '');
                }
            }
        }
        $mapLines[] = '| '
            . app_project_output_ai_context_markdown_cell($tableName) . ' | '
            . app_project_output_ai_context_markdown_cell(is_array($dataClass) ? (string) ($dataClass['name'] ?? '') : 'unknown') . ' | '
            . app_project_output_ai_context_markdown_cell($functions === [] ? 'unknown' : implode(', ', $functions)) . ' |';
    }
    $files['generation-map.md'] = implode("\n", $mapLines) . "\n";

    $files['agent-instructions.md'] = "# Agent Instructions\n\n"
        . "- Treat DB metadata and generated artifacts as source-of-truth inputs.\n"
        . "- Do not invent table meaning, relationship intent, or migration safety.\n"
        . "- Mark unknown meaning as unknown when metadata does not explain it.\n"
        . "- Review `risky-areas.md` before editing schema-dependent code.\n"
        . "- Prefer generated DataClass / DBAccess surfaces before custom SQL.\n";

    $files['schema-context.json'] = app_project_output_ai_context_json_text($context);

    ksort($files, SORT_STRING);

    return $files;
}

/**
 * @return array{
 *     ok:bool,
 *     runtime_source_relative_path:string,
 *     runtime_source_root:string,
 *     scan_result:array{
 *         ok:bool,
 *         files:list<array{relative_path:string,size:int}>,
 *         total_bytes:int,
 *         error:string
 *     }|null,
 *     error:string
 * }
 */
function app_project_output_prepare_ai_context_source_tree(array $app, string $projectKey, array $definition): array
{
    $strategy = (string) ($definition['artifact_strategy'] ?? '');
    if (!app_project_output_ai_context_strategy_is_supported($strategy)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => '未対応の AI context artifact strategy です。',
        ];
    }

    $programLanguage = trim((string) ($definition['program_language'] ?? ''));
    if ($programLanguage !== '' && $programLanguage !== 'md') {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'AI context artifact は現在 md のみ対応です。',
        ];
    }

    $runtimeSourceRelativePath = trim((string) ($definition['runtime_source_relative_path'] ?? ''));
    if ($runtimeSourceRelativePath === '') {
        $runtimeSourceRelativePath = app_project_output_ai_context_default_runtime_source_relative_path(
            $projectKey,
            (string) ($definition['source_output_key'] ?? ''),
        );
    }
    if (!app_project_output_relative_path_is_safe($runtimeSourceRelativePath)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'runtime source relative path の形式が不正です。',
        ];
    }

    $contextResult = app_project_output_ai_context_build_context($app, $projectKey, $definition);
    if (!$contextResult['ok'] || !is_array($contextResult['context'])) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $contextResult['error'],
        ];
    }

    $runtimeSourceRoot = app_runtime_storage_runtime_source_root($app, $runtimeSourceRelativePath);
    $files = app_project_output_ai_context_build_emitted_files($contextResult['context']);

    try {
        app_project_output_delete_tree($runtimeSourceRoot);
        app_project_output_ensure_directory($runtimeSourceRoot);

        foreach ($files as $relativePath => $contents) {
            app_project_output_write_text_file($runtimeSourceRoot . '/' . $relativePath, $contents);
        }
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'AI context staging tree の作成に失敗しました: ' . $throwable->getMessage(),
        ];
    }

    $scanResult = app_project_output_scan_tree($runtimeSourceRoot);
    if (!$scanResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $scanResult['error'],
        ];
    }

    return [
        'ok' => true,
        'runtime_source_relative_path' => $runtimeSourceRelativePath,
        'runtime_source_root' => $runtimeSourceRoot,
        'scan_result' => $scanResult,
        'error' => '',
    ];
}
