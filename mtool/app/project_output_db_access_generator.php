<?php

declare(strict_types=1);

require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/project_output_template_renderer.php';
require_once __DIR__ . '/project_output_runtime_sql_generator.php';
require_once __DIR__ . '/runtime_storage_paths.php';

function app_project_output_db_access_strategy_is_supported(string $strategy): bool
{
    return $strategy === 'canonical-dbaccess-php';
}

function app_project_output_db_access_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_db_access_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

function app_project_output_db_access_identifier_fragment(string $value): string
{
    $normalized = preg_replace('/[^A-Za-z0-9_]+/', '_', trim($value));
    if (!is_string($normalized)) {
        $normalized = '';
    }

    $normalized = trim($normalized, '_');
    if ($normalized === '') {
        return 'Value';
    }

    if (preg_match('/^[0-9]/', $normalized) === 1) {
        return '_' . $normalized;
    }

    return $normalized;
}

function app_project_output_db_access_argument_name(
    string $sourceName,
    string $columnName,
    string $suffix = '',
): string {
    $segments = [
        'param',
        app_project_output_db_access_identifier_fragment($sourceName),
    ];

    if ($columnName !== '') {
        $segments[] = app_project_output_db_access_identifier_fragment($columnName);
    }

    if ($suffix !== '') {
        $segments[] = app_project_output_db_access_identifier_fragment($suffix);
    }

    return '$' . implode('_', $segments);
}

function app_project_output_db_access_object_argument_name(string $sourceName): string
{
    return '$' . app_project_output_db_access_identifier_fragment($sourceName) . 'Obj';
}

function app_project_output_db_access_wrapper_relative_path(
    string $storeBasePath,
    string $sourceName,
): string {
    return ($storeBasePath !== '' ? $storeBasePath . '/' : '')
        . 'dbaccess-'
        . $sourceName
        . '.php';
}

function app_project_output_db_access_base_relative_path(
    string $storeBasePath,
    string $sourceName,
): string {
    return ($storeBasePath !== '' ? $storeBasePath . '/' : '')
        . 'base/dbaccess-'
        . $sourceName
        . 'Base.php';
}

/**
 * @param array<string,bool> $seen
 * @param list<string> $arguments
 */
function app_project_output_db_access_append_unique_argument(
    array &$arguments,
    array &$seen,
    string $argumentName,
): void {
    $normalizedArgumentName = trim($argumentName);
    if ($normalizedArgumentName === '' || isset($seen[$normalizedArgumentName])) {
        return;
    }

    $seen[$normalizedArgumentName] = true;
    $arguments[] = $normalizedArgumentName;
}

/**
 * @param list<array<string,string>> $rows
 * @param array<string,bool> $seen
 * @param list<string> $arguments
 */
function app_project_output_db_access_append_designer_argument_names(
    string $sourceName,
    array $rows,
    string $columnField,
    string $suffix,
    array &$arguments,
    array &$seen,
): void {
    foreach ($rows as $row) {
        $parameterType = strtolower(trim((string) ($row['parameter_type'] ?? 'argument')));
        if ($parameterType === 'fixed' || $parameterType === 'anotherfield') {
            continue;
        }

        $columnName = trim((string) ($row[$columnField] ?? ''));
        app_project_output_db_access_append_unique_argument(
            $arguments,
            $seen,
            app_project_output_db_access_argument_name($sourceName, $columnName, $suffix),
        );
    }
}

function app_project_output_db_access_normalize_detected_signature(
    string $functionName,
    string $detectedSignature,
): string {
    $signature = trim($detectedSignature);
    if ($signature === '') {
        return '';
    }

    $signature = preg_replace('/\s*\{\s*$/', '', $signature) ?? $signature;
    if (preg_match('/\bfunction\s+' . preg_quote($functionName, '/') . '\s*\(/', $signature) !== 1) {
        return '';
    }

    if (preg_match('/^(public|protected|private)\s+function\b/i', $signature) === 1) {
        return $signature;
    }

    if (preg_match('/^function\b/i', $signature) === 1) {
        return preg_replace('/^function\b/i', 'public function', $signature, 1) ?? $signature;
    }

    return 'public ' . ltrim($signature);
}

/**
 * @param array{
 *     function_name:string,
 *     action_type:string,
 *     parameter_type:string,
 *     limit_parameter_type:string,
 *     detected_signature:string
 * } $functionItem
 * @param array{
 *     select_wheres:list<array<string,string>>,
 *     insert_target_fields:list<array<string,string>>,
 *     update_target_fields:list<array<string,string>>,
 *     update_delete_wheres:list<array<string,string>>
 * } $designer
 */
function app_project_output_db_access_generated_method_signature(
    string $sourceName,
    array $functionItem,
    array $designer,
): string {
    $functionName = trim((string) ($functionItem['function_name'] ?? ''));
    $detectedSignature = app_project_output_db_access_normalize_detected_signature(
        $functionName,
        trim((string) ($functionItem['detected_signature'] ?? '')),
    );
    if ($detectedSignature !== '') {
        return $detectedSignature;
    }

    $arguments = [];
    $seen = [];
    $actionType = strtoupper(trim((string) ($functionItem['action_type'] ?? '')));
    $parameterType = strtolower(trim((string) ($functionItem['parameter_type'] ?? '')));

    if (in_array($actionType, ['SELECTSINGLE', 'SELECTLIST'], true)) {
        app_project_output_db_access_append_designer_argument_names(
            $sourceName,
            $designer['select_wheres'],
            'target_table_column_name',
            'where',
            $arguments,
            $seen,
        );
        if (strtolower(trim((string) ($functionItem['limit_parameter_type'] ?? ''))) === 'argument') {
            app_project_output_db_access_append_unique_argument($arguments, $seen, '$limit');
        }
    } elseif ($actionType === 'INSERT') {
        if ($parameterType === 'classobject') {
            app_project_output_db_access_append_unique_argument(
                $arguments,
                $seen,
                app_project_output_db_access_object_argument_name($sourceName),
            );
        } else {
            app_project_output_db_access_append_designer_argument_names(
                $sourceName,
                $designer['insert_target_fields'],
                'target_table_column_name',
                '',
                $arguments,
                $seen,
            );
        }
    } elseif ($actionType === 'UPDATE') {
        if ($parameterType === 'classobject') {
            app_project_output_db_access_append_unique_argument(
                $arguments,
                $seen,
                app_project_output_db_access_object_argument_name($sourceName),
            );
        } elseif ($parameterType === 'setbyclassobjectandwherebyvalforupdate') {
            app_project_output_db_access_append_unique_argument(
                $arguments,
                $seen,
                app_project_output_db_access_object_argument_name($sourceName),
            );
            app_project_output_db_access_append_designer_argument_names(
                $sourceName,
                $designer['update_delete_wheres'],
                'target_table_column_name',
                'where',
                $arguments,
                $seen,
            );
        } else {
            app_project_output_db_access_append_designer_argument_names(
                $sourceName,
                $designer['update_target_fields'],
                'target_table_column_name',
                'set',
                $arguments,
                $seen,
            );
            app_project_output_db_access_append_designer_argument_names(
                $sourceName,
                $designer['update_delete_wheres'],
                'target_table_column_name',
                'where',
                $arguments,
                $seen,
            );
        }
    } elseif ($actionType === 'DELETE') {
        if ($parameterType === 'classobject') {
            app_project_output_db_access_append_unique_argument(
                $arguments,
                $seen,
                app_project_output_db_access_object_argument_name($sourceName),
            );
        } else {
            app_project_output_db_access_append_designer_argument_names(
                $sourceName,
                $designer['update_delete_wheres'],
                'target_table_column_name',
                'where',
                $arguments,
                $seen,
            );
        }
    }

    return 'public function ' . $functionName . '(' . implode(', ', $arguments) . ')';
}

/**
 * @param list<string> $commentLines
 * @param list<string> $bodyLines
 */
function app_project_output_generated_db_access_method_block(
    string $signature,
    array $commentLines,
    array $bodyLines,
): string {
    $commentSection = '';
    foreach ($commentLines as $commentLine) {
        $commentSection .= '    // ' . $commentLine . "\n";
    }

    $bodySection = '';
    foreach ($bodyLines as $bodyLine) {
        $bodySection .= (string) $bodyLine . "\n";
    }

    return rtrim(
        app_project_output_render_reference_template(
            'canonical-dbaccess-php/method.php.tpl',
            [
                'METHOD_COMMENT_SECTION' => $commentSection,
                'METHOD_SIGNATURE' => $signature,
                'METHOD_BODY_SECTION' => $bodySection,
            ],
        ),
        "\n",
    );
}

/**
 * @param array{
 *     source_name:string,
 *     source_of_truth:string
 * } $classItem
 * @param list<array{
 *     function_name:string,
 *     function_list_order:string,
 *     action_type:string,
 *     source_of_truth:string
 * }> $functionItems
 * @param array<string,array{
 *     mode:string,
 *     body_lines:list<string>,
 *     reason:string
 * }> $generatedMethodResults
 * @param array<string,string> $signaturesByFunction
 * @param list<string> $extraClassLines
 */
function app_project_output_generated_db_access_base_php_text(
    array $classItem,
    array $functionItems,
    array $generatedMethodResults,
    array $signaturesByFunction,
    array $extraClassLines,
): string {
    $sourceName = trim((string) ($classItem['source_name'] ?? ''));
    $className = $sourceName . 'DBAccessBase';
    $classBody = '';

    if ($extraClassLines !== []) {
        $classBody .= implode("\n", $extraClassLines) . "\n\n";
    }

    $classBody .= implode("\n", [
        '    public function __construct()',
        '    {',
        '    }',
    ]);

    foreach ($functionItems as $functionItem) {
        $functionName = trim((string) ($functionItem['function_name'] ?? ''));
        if ($functionName === '' || $functionName === '__construct') {
            continue;
        }

        $generatedMethodResult = $generatedMethodResults[$functionName] ?? null;
        $signature = $signaturesByFunction[$functionName] ?? '';
        if ($generatedMethodResult === null || $signature === '') {
            continue;
        }

        $commentLines = [
            'source_of_truth=' . trim((string) ($functionItem['source_of_truth'] ?? ''))
            . ' class_source=' . trim((string) ($classItem['source_of_truth'] ?? ''))
            . ' action_type=' . trim((string) ($functionItem['action_type'] ?? ''))
            . ' order=' . trim((string) ($functionItem['function_list_order'] ?? '0'))
            . ' generation=' . trim((string) ($generatedMethodResult['mode'] ?? '')),
        ];
        $reason = trim((string) ($generatedMethodResult['reason'] ?? ''));
        if ($reason !== '') {
            $normalizedReason = preg_replace('/\s+/', ' ', $reason);
            if (!is_string($normalizedReason) || $normalizedReason === '') {
                $normalizedReason = $reason;
            }
            $commentLines[] = 'reason=' . $normalizedReason;
        }

        $classBody .= "\n\n" . app_project_output_generated_db_access_method_block(
            $signature,
            $commentLines,
            $generatedMethodResult['body_lines'] ?? [],
        );
    }

    $classBody .= "\n";

    return rtrim(
        app_project_output_render_reference_template(
            'canonical-dbaccess-php/base.php.tpl',
            [
                'CLASS_NAME' => $className,
                'CLASS_BODY_SECTION' => $classBody,
            ],
        ),
        "\r\n",
    );
}

function app_project_output_generated_db_access_wrapper_php_text(string $sourceName): string
{
    $className = $sourceName . 'DBAccess';
    $baseClassName = $className . 'Base';
    $baseRequirePath = var_export('/base/dbaccess-' . $sourceName . 'Base.php', true);

    return rtrim(
        app_project_output_render_reference_template(
            'canonical-dbaccess-php/wrapper.php.tpl',
            [
                'BASE_REQUIRE_PATH' => $baseRequirePath,
                'CLASS_NAME' => $className,
                'BASE_CLASS_NAME' => $baseClassName,
            ],
        ),
        "\r\n",
    );
}

/**
 * @param array{
 *     source_output_key:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     program_language:string
 * } $definition
 * @return array{
 *     ok:bool,
 *     runtime_source_relative_path:string,
 *     runtime_source_root:string,
 *     scan_result:array{
 *         ok:bool,
 *         files:list<array{
 *             relative_path:string,
 *             size:int
 *         }>,
 *         total_bytes:int,
 *         error:string
 *     }|null,
 *     error:string
 * }
 */
function app_project_output_prepare_db_access_source_tree(array $app, string $projectKey, array $definition): array
{
    $strategy = (string) ($definition['artifact_strategy'] ?? '');
    if (!app_project_output_db_access_strategy_is_supported($strategy)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => '未対応の db access artifact strategy です。',
        ];
    }

    $programLanguage = trim((string) ($definition['program_language'] ?? ''));
    if ($programLanguage !== '' && $programLanguage !== 'php') {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'canonical db access artifact は現在 php のみ対応です。',
        ];
    }

    $classCatalogResult = app_fetch_db_access_class_metadata_catalog($app, $projectKey);
    if (!$classCatalogResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'canonical db access class metadata の読み込みに失敗しました: '
                . $classCatalogResult['error'],
        ];
    }

    if ($classCatalogResult['items'] === []) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'canonical db access metadata がありません。先に db access class / function metadata を登録してください。',
        ];
    }

    $runtimeSourceRelativePath = trim((string) ($definition['runtime_source_relative_path'] ?? ''));
    if ($runtimeSourceRelativePath === '') {
        $runtimeSourceRelativePath = app_project_output_db_access_default_runtime_source_relative_path(
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

    $runtimeSourceRoot = app_runtime_storage_runtime_source_root($app, $runtimeSourceRelativePath);

    try {
        app_project_output_delete_tree($runtimeSourceRoot);
        app_project_output_ensure_directory($runtimeSourceRoot);

        foreach ($classCatalogResult['items'] as $classItem) {
            $sourceName = trim((string) ($classItem['source_name'] ?? ''));
            if ($sourceName === '') {
                continue;
            }

            $functionCatalogResult = app_fetch_db_access_function_metadata_catalog($app, $projectKey, $sourceName);
            if (!$functionCatalogResult['ok']) {
                throw new RuntimeException(
                    $sourceName . ' function catalog の読み込みに失敗しました: ' . $functionCatalogResult['error']
                );
            }

            $generatedMethodResults = [];
            $signaturesByFunction = [];
            foreach ($functionCatalogResult['items'] as $functionItem) {
                $functionName = trim((string) ($functionItem['function_name'] ?? ''));
                if ($functionName === '' || $functionName === '__construct') {
                    continue;
                }

                $designer = app_project_output_runtime_sql_fetch_designer_resources(
                    $app,
                    $projectKey,
                    $sourceName,
                    $functionName,
                );
                if (!$designer['ok']) {
                    throw new RuntimeException(
                        $sourceName . '::' . $functionName . ' designer metadata の読み込みに失敗しました: '
                        . $designer['error']
                    );
                }

                $signature = app_project_output_db_access_generated_method_signature(
                    $sourceName,
                    $functionItem,
                    $designer,
                );
                $method = [
                    'name' => $functionName,
                    'line' => (int) ($functionItem['detected_line'] ?? 0),
                    'end_line' => (int) ($functionItem['detected_line'] ?? 0),
                    'signature' => $signature,
                ];

                $generationResult = app_project_output_runtime_sql_try_generate_method(
                    $app,
                    $projectKey,
                    $sourceName,
                    $functionItem,
                    $method,
                );
                if (!$generationResult['ok']) {
                    throw new RuntimeException(
                        $sourceName . '::' . $functionName . ' method generation に失敗しました。'
                    );
                }

                $generationMode = trim((string) ($generationResult['result']['mode'] ?? ''));
                if (!in_array($generationMode, ['canonical-sql', 'canonical-helper'], true)) {
                    $reason = trim((string) ($generationResult['result']['reason'] ?? ''));
                    throw new RuntimeException(
                        $sourceName . '::' . $functionName
                        . ' は standalone db access として生成できません: '
                        . ($reason !== '' ? $reason : $generationMode)
                    );
                }

                $warning = trim((string) ($generationResult['result']['warning'] ?? ''));
                if ($warning !== '') {
                    throw new RuntimeException($warning);
                }

                $generatedMethodResults[$functionName] = $generationResult['result'];
                $signaturesByFunction[$functionName] = $signature;
            }

            $storeBasePath = trim(str_replace('\\', '/', (string) ($classItem['store_base_path'] ?? '')), '/');
            if ($storeBasePath !== '' && !app_project_output_relative_path_is_safe($storeBasePath)) {
                throw new RuntimeException(
                    'StoreBasePath の形式が不正です: '
                    . $sourceName
                    . ' -> '
                    . (string) ($classItem['store_base_path'] ?? '')
                );
            }

            $wrapperRelativePath = app_project_output_db_access_wrapper_relative_path($storeBasePath, $sourceName);
            $baseRelativePath = app_project_output_db_access_base_relative_path($storeBasePath, $sourceName);
            app_project_output_write_text_file(
                $runtimeSourceRoot . '/' . $baseRelativePath,
                app_project_output_generated_db_access_base_php_text(
                    $classItem,
                    $functionCatalogResult['items'],
                    $generatedMethodResults,
                    $signaturesByFunction,
                    app_project_output_runtime_sql_known_helper_class_lines($sourceName),
                ),
            );
            app_project_output_write_text_file(
                $runtimeSourceRoot . '/' . $wrapperRelativePath,
                app_project_output_generated_db_access_wrapper_php_text($sourceName),
            );
        }
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'db access staging tree の作成に失敗しました: ' . $throwable->getMessage(),
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
