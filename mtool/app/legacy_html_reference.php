<?php

declare(strict_types=1);

/**
 * @return array{
 *     ok:bool,
 *     item:array{
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
 *     }|null,
 *     error:string
 * }
 */
function app_load_legacy_html_reference(string $projectKey): array
{
    $referencePath = app_legacy_html_reference_path($projectKey);
    if ($referencePath === '') {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'この project に対応する legacy html reference はまだありません。',
        ];
    }

    if (!is_file($referencePath)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy html reference が見つかりません: ' . $referencePath,
        ];
    }

    $contents = file_get_contents($referencePath);
    if (!is_string($contents)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy html reference を読み込めません。',
        ];
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy html reference の JSON が不正です。',
        ];
    }

    $htmls = $decoded['htmls'] ?? null;
    $parameters = $decoded['parameters'] ?? null;
    $templates = $decoded['templates'] ?? null;
    $templateParameters = $decoded['template_parameters'] ?? null;
    if (!is_array($htmls) || !is_array($parameters) || !is_array($templates) || !is_array($templateParameters)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy html reference の htmls / parameters / templates / template_parameters が不正です。',
        ];
    }

    $normalizedHtmls = [];
    foreach ($htmls as $html) {
        if (!is_array($html)) {
            continue;
        }

        $legacyHtmlPid = (int) ($html['legacy_html_pid'] ?? 0);
        $htmlKey = trim((string) ($html['html_key'] ?? ''));
        $name = trim((string) ($html['name'] ?? ''));
        if ($legacyHtmlPid <= 0 || $htmlKey === '' || $name === '') {
            continue;
        }

        $normalizedHtmls[] = [
            'project_pid' => (int) ($html['project_pid'] ?? 0),
            'legacy_html_pid' => $legacyHtmlPid,
            'html_key' => $htmlKey,
            'name' => $name,
            'legacy_project_source_output_pid' => (int) ($html['legacy_project_source_output_pid'] ?? 0),
            'legacy_html_template_pid' => (int) ($html['legacy_html_template_pid'] ?? 0),
            'last_modified_dt' => (string) ($html['last_modified_dt'] ?? ''),
        ];
    }

    $normalizedParameters = [];
    foreach ($parameters as $parameter) {
        if (!is_array($parameter)) {
            continue;
        }

        $legacyHtmlPid = (int) ($parameter['legacy_html_pid'] ?? 0);
        $legacyParameterPid = (int) ($parameter['legacy_parameter_pid'] ?? 0);
        $parameterName = trim((string) ($parameter['parameter_name'] ?? ''));
        if ($legacyHtmlPid <= 0 || $legacyParameterPid <= 0 || $parameterName === '') {
            continue;
        }

        $normalizedParameters[] = [
            'project_pid' => (int) ($parameter['project_pid'] ?? 0),
            'legacy_html_pid' => $legacyHtmlPid,
            'legacy_parameter_pid' => $legacyParameterPid,
            'parameter_name' => $parameterName,
            'parameter_value' => (string) ($parameter['parameter_value'] ?? ''),
        ];
    }

    $normalizedTemplates = [];
    foreach ($templates as $template) {
        if (!is_array($template)) {
            continue;
        }

        $legacyTemplatePid = (int) ($template['legacy_html_template_pid'] ?? 0);
        $name = trim((string) ($template['name'] ?? ''));
        if ($legacyTemplatePid <= 0 || $name === '') {
            continue;
        }

        $normalizedTemplates[] = [
            'legacy_html_template_pid' => $legacyTemplatePid,
            'target_type' => (string) ($template['target_type'] ?? ''),
            'parent_html_template_pid' => (int) ($template['parent_html_template_pid'] ?? 0),
            'name' => $name,
            'program_language' => (string) ($template['program_language'] ?? ''),
            'file_name' => (string) ($template['file_name'] ?? ''),
            'comment' => (string) ($template['comment'] ?? ''),
        ];
    }

    $normalizedTemplateParameters = [];
    foreach ($templateParameters as $templateParameter) {
        if (!is_array($templateParameter)) {
            continue;
        }

        $legacyTemplatePid = (int) ($templateParameter['legacy_html_template_pid'] ?? 0);
        $legacyTemplateParameterPid = (int) ($templateParameter['legacy_template_parameter_pid'] ?? 0);
        $parameterName = trim((string) ($templateParameter['parameter_name'] ?? ''));
        if ($legacyTemplatePid <= 0 || $legacyTemplateParameterPid <= 0 || $parameterName === '') {
            continue;
        }

        $normalizedTemplateParameters[] = [
            'legacy_html_template_pid' => $legacyTemplatePid,
            'legacy_template_parameter_pid' => $legacyTemplateParameterPid,
            'parameter_name' => $parameterName,
            'target_value_type' => (string) ($templateParameter['target_value_type'] ?? ''),
            'target_variable_or_class_object' => (string) ($templateParameter['target_variable_or_class_object'] ?? ''),
            'target_property_of_class_object' => (string) ($templateParameter['target_property_of_class_object'] ?? ''),
            'another_template_pid' => (int) ($templateParameter['another_template_pid'] ?? 0),
            'trim_last_space' => (int) ($templateParameter['trim_last_space'] ?? 0),
            'trim_last_return' => (int) ($templateParameter['trim_last_return'] ?? 0),
            'data_type' => (string) ($templateParameter['data_type'] ?? ''),
        ];
    }

    return [
        'ok' => true,
        'item' => [
            'project_key' => (string) ($decoded['project_key'] ?? ''),
            'project_pid' => (int) ($decoded['project_pid'] ?? 0),
            'source_dump_path' => (string) ($decoded['source_dump_path'] ?? ''),
            'generated_at' => (string) ($decoded['generated_at'] ?? ''),
            'html_count' => (int) ($decoded['html_count'] ?? count($normalizedHtmls)),
            'parameter_count' => (int) ($decoded['parameter_count'] ?? count($normalizedParameters)),
            'template_count' => (int) ($decoded['template_count'] ?? count($normalizedTemplates)),
            'template_parameter_count' => (int) ($decoded['template_parameter_count'] ?? count($normalizedTemplateParameters)),
            'htmls' => $normalizedHtmls,
            'parameters' => $normalizedParameters,
            'templates' => $normalizedTemplates,
            'template_parameters' => $normalizedTemplateParameters,
        ],
        'error' => '',
    ];
}

function app_legacy_html_reference_path(string $projectKey): string
{
    $normalizedProjectKey = strtoupper(trim($projectKey));
    if ($normalizedProjectKey === 'MTOOL') {
        return dirname(__DIR__) . '/reference/mtool-legacy-html-catalog.json';
    }

    return '';
}

function app_legacy_html_reference_html_key_candidate(string $name, int $legacyHtmlPid): string
{
    $candidate = strtolower(trim($name));
    $candidate = preg_replace('/[^a-z0-9_-]+/u', '-', $candidate) ?? '';
    $candidate = preg_replace('/-{2,}/', '-', $candidate) ?? '';
    $candidate = trim($candidate, '-');

    if ($candidate === '') {
        $candidate = 'html-' . $legacyHtmlPid;
    }

    return $candidate;
}

/**
 * @param array<string,bool> $usedKeys
 */
function app_legacy_html_reference_unique_html_key(
    string $baseKey,
    int $legacyHtmlPid,
    array $usedKeys,
): string {
    $candidate = trim($baseKey);
    if ($candidate === '') {
        $candidate = 'html-' . $legacyHtmlPid;
    }

    if (!array_key_exists($candidate, $usedKeys)) {
        return $candidate;
    }

    $fallback = $candidate . '-pid-' . $legacyHtmlPid;
    if (!array_key_exists($fallback, $usedKeys)) {
        return $fallback;
    }

    $suffix = 2;
    while (array_key_exists($fallback . '-' . $suffix, $usedKeys)) {
        $suffix++;
    }

    return $fallback . '-' . $suffix;
}

/**
 * @param list<array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int,
 *     last_modified_dt:string
 * }> $htmls
 * @param array<string,string> $preservedHtmlKeysByPid
 * @return list<array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     html_key:string,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int,
 *     last_modified_dt:string
 * }>
 */
function app_legacy_html_reference_assign_html_keys(
    array $htmls,
    array $preservedHtmlKeysByPid = [],
): array {
    $usedKeys = [];
    $assignedKeysByPid = [];

    foreach ($htmls as $html) {
        $legacyHtmlPid = (int) ($html['legacy_html_pid'] ?? 0);
        if ($legacyHtmlPid <= 0) {
            continue;
        }

        $preservedKey = trim((string) ($preservedHtmlKeysByPid[(string) $legacyHtmlPid] ?? ''));
        if ($preservedKey === '') {
            continue;
        }

        $htmlKey = app_legacy_html_reference_unique_html_key(
            $preservedKey,
            $legacyHtmlPid,
            $usedKeys,
        );
        $assignedKeysByPid[(string) $legacyHtmlPid] = $htmlKey;
        $usedKeys[$htmlKey] = true;
    }

    $normalized = [];
    foreach ($htmls as $html) {
        $legacyHtmlPid = (int) ($html['legacy_html_pid'] ?? 0);
        if ($legacyHtmlPid <= 0) {
            continue;
        }

        $assignedKey = $assignedKeysByPid[(string) $legacyHtmlPid] ?? '';
        if ($assignedKey === '') {
            $assignedKey = app_legacy_html_reference_unique_html_key(
                app_legacy_html_reference_html_key_candidate(
                    (string) ($html['name'] ?? ''),
                    $legacyHtmlPid,
                ),
                $legacyHtmlPid,
                $usedKeys,
            );
            $usedKeys[$assignedKey] = true;
        }

        $normalized[] = [
            'project_pid' => (int) ($html['project_pid'] ?? 0),
            'legacy_html_pid' => $legacyHtmlPid,
            'html_key' => $assignedKey,
            'name' => (string) ($html['name'] ?? ''),
            'legacy_project_source_output_pid' => (int) ($html['legacy_project_source_output_pid'] ?? 0),
            'legacy_html_template_pid' => (int) ($html['legacy_html_template_pid'] ?? 0),
            'last_modified_dt' => (string) ($html['last_modified_dt'] ?? ''),
        ];
    }

    usort(
        $normalized,
        static function (array $left, array $right): int {
            $nameOrder = strnatcasecmp($left['name'], $right['name']);
            if ($nameOrder !== 0) {
                return $nameOrder;
            }

            return $left['legacy_html_pid'] <=> $right['legacy_html_pid'];
        },
    );

    return $normalized;
}

/**
 * @return array<string,string>
 */
function app_legacy_html_reference_current_html_pid_map(string $projectKey): array
{
    $reference = app_load_legacy_html_reference($projectKey);
    if (!$reference['ok'] || $reference['item'] === null) {
        return [];
    }

    $map = [];
    foreach ($reference['item']['htmls'] as $html) {
        $map[(string) $html['legacy_html_pid']] = $html['html_key'];
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

/**
 * @param array{
 *     htmls:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         html_key:string,
 *         name:string,
 *         legacy_project_source_output_pid:int,
 *         legacy_html_template_pid:int,
 *         last_modified_dt:string
 *     }>
 * } $referenceItem
 * @return array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     html_key:string,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int,
 *     last_modified_dt:string
 * }|null
 */
function app_legacy_html_reference_find_html_by_key(array $referenceItem, string $htmlKey): ?array
{
    $normalizedHtmlKey = trim($htmlKey);
    if ($normalizedHtmlKey === '') {
        return null;
    }

    foreach ($referenceItem['htmls'] as $html) {
        if ($html['html_key'] === $normalizedHtmlKey) {
            return $html;
        }
    }

    return null;
}

/**
 * @param array{
 *     htmls:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         html_key:string,
 *         name:string,
 *         legacy_project_source_output_pid:int,
 *         legacy_html_template_pid:int,
 *         last_modified_dt:string
 *     }>
 * } $referenceItem
 * @return array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     html_key:string,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int,
 *     last_modified_dt:string
 * }|null
 */
function app_legacy_html_reference_find_html_by_legacy_pid(array $referenceItem, int $legacyHtmlPid): ?array
{
    if ($legacyHtmlPid <= 0) {
        return null;
    }

    foreach ($referenceItem['htmls'] as $html) {
        if ($html['legacy_html_pid'] === $legacyHtmlPid) {
            return $html;
        }
    }

    return null;
}

/**
 * @param array{
 *     templates:list<array{
 *         legacy_html_template_pid:int,
 *         target_type:string,
 *         parent_html_template_pid:int,
 *         name:string,
 *         program_language:string,
 *         file_name:string,
 *         comment:string
 *     }>
 * } $referenceItem
 * @return array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string
 * }|null
 */
function app_legacy_html_reference_find_template_by_pid(array $referenceItem, int $legacyTemplatePid): ?array
{
    if ($legacyTemplatePid <= 0) {
        return null;
    }

    foreach ($referenceItem['templates'] as $template) {
        if ($template['legacy_html_template_pid'] === $legacyTemplatePid) {
            return $template;
        }
    }

    return null;
}

function app_legacy_html_reference_parameter_data_type_caption(string $dataType): string
{
    return match (strtolower(trim($dataType))) {
        '', 'default' => 'Default',
        'dataclassname' => 'Data Class Name',
        'dbaccessclassname' => 'DB Access Class Name',
        default => trim($dataType) === '' ? 'Default' : trim($dataType),
    };
}

/**
 * @param array{
 *     template_parameters:list<array{
 *         legacy_html_template_pid:int,
 *         legacy_template_parameter_pid:int,
 *         parameter_name:string,
 *         target_value_type:string,
 *         target_variable_or_class_object:string,
 *         target_property_of_class_object:string,
 *         another_template_pid:int,
 *         trim_last_space:int,
 *         trim_last_return:int,
 *         data_type:string
 *     }>
 * } $referenceItem
 * @return array<string,list<array{
 *     legacy_html_template_pid:int,
 *     legacy_template_parameter_pid:int,
 *     parameter_name:string,
 *     target_value_type:string,
 *     target_variable_or_class_object:string,
 *     target_property_of_class_object:string,
 *     another_template_pid:int,
 *     trim_last_space:int,
 *     trim_last_return:int,
 *     data_type:string
 * }>>
 */
function app_legacy_html_reference_template_parameters_by_template_pid(array $referenceItem): array
{
    $map = [];
    foreach ($referenceItem['template_parameters'] as $templateParameter) {
        $templatePid = (string) $templateParameter['legacy_html_template_pid'];
        if (!array_key_exists($templatePid, $map)) {
            $map[$templatePid] = [];
        }

        $map[$templatePid][] = $templateParameter;
    }

    foreach ($map as &$items) {
        usort(
            $items,
            static fn (array $left, array $right): int
                => $left['legacy_template_parameter_pid'] <=> $right['legacy_template_parameter_pid'],
        );
    }
    unset($items);

    return $map;
}

/**
 * @param array{
 *     template_parameters:list<array{
 *         legacy_html_template_pid:int,
 *         legacy_template_parameter_pid:int,
 *         parameter_name:string,
 *         target_value_type:string,
 *         target_variable_or_class_object:string,
 *         target_property_of_class_object:string,
 *         another_template_pid:int,
 *         trim_last_space:int,
 *         trim_last_return:int,
 *         data_type:string
 *     }>
 * } $referenceItem
 * @param array<int,bool> $visitedTemplatePids
 * @return list<array{
 *     legacy_html_template_pid:int,
 *     legacy_template_parameter_pid:int,
 *     parameter_name:string,
 *     target_value_type:string,
 *     target_variable_or_class_object:string,
 *     target_property_of_class_object:string,
 *     another_template_pid:int,
 *     trim_last_space:int,
 *     trim_last_return:int,
 *     data_type:string
 * }>
 */
function app_legacy_html_reference_collect_template_parameters(
    array $referenceItem,
    int $legacyTemplatePid,
    array $visitedTemplatePids = [],
): array {
    if ($legacyTemplatePid <= 0 || array_key_exists($legacyTemplatePid, $visitedTemplatePids)) {
        return [];
    }

    $visitedTemplatePids[$legacyTemplatePid] = true;
    $templateParametersByTemplatePid = app_legacy_html_reference_template_parameters_by_template_pid($referenceItem);
    $directItems = $templateParametersByTemplatePid[(string) $legacyTemplatePid] ?? [];
    $flattened = [];

    foreach ($directItems as $item) {
        $flattened[] = $item;

        if (strcasecmp($item['target_value_type'], 'AnotherTemplate') !== 0) {
            continue;
        }

        $anotherTemplatePid = (int) $item['another_template_pid'];
        if ($anotherTemplatePid <= 0) {
            continue;
        }

        foreach (
            app_legacy_html_reference_collect_template_parameters(
                $referenceItem,
                $anotherTemplatePid,
                $visitedTemplatePids,
            ) as $childItem
        ) {
            $flattened[] = $childItem;
        }
    }

    return $flattened;
}

/**
 * @param array{
 *     parameters:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }>
 * } $referenceItem
 * @return list<array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     legacy_parameter_pid:int,
 *     parameter_name:string,
 *     parameter_value:string
 * }>
 */
function app_legacy_html_reference_parameters_for_html(array $referenceItem, int $legacyHtmlPid): array
{
    if ($legacyHtmlPid <= 0) {
        return [];
    }

    $items = [];
    foreach ($referenceItem['parameters'] as $parameter) {
        if ($parameter['legacy_html_pid'] !== $legacyHtmlPid) {
            continue;
        }

        $items[] = $parameter;
    }

    usort(
        $items,
        static fn (array $left, array $right): int
            => $left['legacy_parameter_pid'] <=> $right['legacy_parameter_pid'],
    );

    return $items;
}

/**
 * @param array{
 *     htmls:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         html_key:string,
 *         name:string,
 *         legacy_project_source_output_pid:int,
 *         legacy_html_template_pid:int,
 *         last_modified_dt:string
 *     }>,
 *     parameters:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }>,
 *     template_parameters:list<array{
 *         legacy_html_template_pid:int,
 *         legacy_template_parameter_pid:int,
 *         parameter_name:string,
 *         target_value_type:string,
 *         target_variable_or_class_object:string,
 *         target_property_of_class_object:string,
 *         another_template_pid:int,
 *         trim_last_space:int,
 *         trim_last_return:int,
 *         data_type:string
 *     }>
 * } $referenceItem
 * @param array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     html_key:string,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int,
 *     last_modified_dt:string
 * } $html
 * @return array{
 *     expected_rows:list<array{
 *         template_parameter:array{
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
 *         },
 *         parameter_name:string,
 *         data_type:string,
 *         data_type_caption:string,
 *         actual_item:array{
 *             project_pid:int,
 *             legacy_html_pid:int,
 *             legacy_parameter_pid:int,
 *             parameter_name:string,
 *             parameter_value:string
 *         }|null,
 *         duplicate_items:list<array{
 *             project_pid:int,
 *             legacy_html_pid:int,
 *             legacy_parameter_pid:int,
 *             parameter_name:string,
 *             parameter_value:string
 *         }>
 *     }>,
 *     actual_items:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }>,
 *     unexpected_items:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }>,
 *     missing_parameter_names:list<string>,
 *     duplicate_parameter_names:list<string>,
 *     template_duplicate_data_type_names:list<string>,
 *     expected_count:int,
 *     actual_count:int,
 *     is_complete:bool,
 *     has_duplicate_matches:bool
 * }
 */
function app_legacy_html_reference_parameter_audit_with_actual_items(
    array $referenceItem,
    array $html,
    array $actualItems,
): array
{
    $flattenedTemplateParameters = app_legacy_html_reference_collect_template_parameters(
        $referenceItem,
        $html['legacy_html_template_pid'],
    );

    $actualItemsByName = [];
    foreach ($actualItems as $actualItem) {
        $parameterName = $actualItem['parameter_name'];
        if (!array_key_exists($parameterName, $actualItemsByName)) {
            $actualItemsByName[$parameterName] = [];
        }

        $actualItemsByName[$parameterName][] = $actualItem;
    }

    $expectedTemplateParametersByName = [];
    $templateDuplicateDataTypeNames = [];
    foreach ($flattenedTemplateParameters as $templateParameter) {
        if (strcasecmp($templateParameter['target_value_type'], 'EachHTML') !== 0) {
            continue;
        }

        $parameterName = $templateParameter['parameter_name'];
        if ($parameterName === '') {
            continue;
        }

        if (array_key_exists($parameterName, $expectedTemplateParametersByName)) {
            if (
                strcasecmp(
                    (string) $expectedTemplateParametersByName[$parameterName]['data_type'],
                    (string) $templateParameter['data_type'],
                ) !== 0
            ) {
                $templateDuplicateDataTypeNames[$parameterName] = true;
            }
            continue;
        }

        $expectedTemplateParametersByName[$parameterName] = $templateParameter;
    }

    $matchedActualParameterPids = [];
    $expectedRows = [];
    $missingParameterNames = [];
    $duplicateParameterNames = [];
    foreach ($expectedTemplateParametersByName as $parameterName => $templateParameter) {
        $matchingActualItems = $actualItemsByName[$parameterName] ?? [];
        $actualItem = $matchingActualItems[0] ?? null;
        $duplicateItems = array_slice($matchingActualItems, 1);

        foreach ($matchingActualItems as $matchingActualItem) {
            $matchedActualParameterPids[$matchingActualItem['legacy_parameter_pid']] = true;
        }

        if ($actualItem === null) {
            $missingParameterNames[] = $parameterName;
        }
        if ($duplicateItems !== []) {
            $duplicateParameterNames[] = $parameterName;
        }

        $expectedRows[] = [
            'template_parameter' => $templateParameter,
            'parameter_name' => $parameterName,
            'data_type' => $templateParameter['data_type'],
            'data_type_caption' => app_legacy_html_reference_parameter_data_type_caption(
                $templateParameter['data_type'],
            ),
            'actual_item' => $actualItem,
            'duplicate_items' => $duplicateItems,
        ];
    }

    $unexpectedItems = [];
    foreach ($actualItems as $actualItem) {
        if (array_key_exists($actualItem['legacy_parameter_pid'], $matchedActualParameterPids)) {
            continue;
        }

        $unexpectedItems[] = $actualItem;
    }

    $templateDuplicateDataTypeNames = array_keys($templateDuplicateDataTypeNames);
    sort($missingParameterNames, SORT_NATURAL);
    sort($duplicateParameterNames, SORT_NATURAL);
    sort($templateDuplicateDataTypeNames, SORT_NATURAL);

    return [
        'expected_rows' => $expectedRows,
        'actual_items' => $actualItems,
        'unexpected_items' => $unexpectedItems,
        'missing_parameter_names' => $missingParameterNames,
        'duplicate_parameter_names' => $duplicateParameterNames,
        'template_duplicate_data_type_names' => $templateDuplicateDataTypeNames,
        'expected_count' => count($expectedRows),
        'actual_count' => count($actualItems),
        'is_complete' => $missingParameterNames === [] && $duplicateParameterNames === [],
        'has_duplicate_matches' => $duplicateParameterNames !== [],
    ];
}

/**
 * @param array{
 *     htmls:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         html_key:string,
 *         name:string,
 *         legacy_project_source_output_pid:int,
 *         legacy_html_template_pid:int,
 *         last_modified_dt:string
 *     }>,
 *     parameters:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }>,
 *     template_parameters:list<array{
 *         legacy_html_template_pid:int,
 *         legacy_template_parameter_pid:int,
 *         parameter_name:string,
 *         target_value_type:string,
 *         target_variable_or_class_object:string,
 *         target_property_of_class_object:string,
 *         another_template_pid:int,
 *         trim_last_space:int,
 *         trim_last_return:int,
 *         data_type:string
 *     }>
 * } $referenceItem
 * @param array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     html_key:string,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int,
 *     last_modified_dt:string
 * } $html
 * @return array{
 *     expected_rows:list<array{
 *         template_parameter:array{
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
 *         },
 *         parameter_name:string,
 *         data_type:string,
 *         data_type_caption:string,
 *         actual_item:array{
 *             project_pid:int,
 *             legacy_html_pid:int,
 *             legacy_parameter_pid:int,
 *             parameter_name:string,
 *             parameter_value:string
 *         }|null,
 *         duplicate_items:list<array{
 *             project_pid:int,
 *             legacy_html_pid:int,
 *             legacy_parameter_pid:int,
 *             parameter_name:string,
 *             parameter_value:string
 *         }>
 *     }>,
 *     actual_items:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }>,
 *     unexpected_items:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }>,
 *     missing_parameter_names:list<string>,
 *     duplicate_parameter_names:list<string>,
 *     template_duplicate_data_type_names:list<string>,
 *     expected_count:int,
 *     actual_count:int,
 *     is_complete:bool,
 *     has_duplicate_matches:bool
 * }
 */
function app_legacy_html_reference_parameter_audit(array $referenceItem, array $html): array
{
    return app_legacy_html_reference_parameter_audit_with_actual_items(
        $referenceItem,
        $html,
        app_legacy_html_reference_parameters_for_html(
            $referenceItem,
            $html['legacy_html_pid'],
        ),
    );
}
