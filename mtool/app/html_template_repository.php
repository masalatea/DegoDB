<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/legacy_html_reference.php';

/**
 * @param list<array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string
 * }> $templateCatalog
 * @return array<string,array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string
 * }>
 */
function app_html_template_catalog_by_pid(array $templateCatalog): array
{
    $map = [];
    foreach ($templateCatalog as $template) {
        $legacyTemplatePid = (int) ($template['legacy_html_template_pid'] ?? 0);
        if ($legacyTemplatePid <= 0) {
            continue;
        }

        $map[(string) $legacyTemplatePid] = $template;
    }

    return $map;
}

/**
 * @param list<array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string
 * }> $templateCatalog
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
function app_html_template_find_catalog_item_by_pid(array $templateCatalog, int $legacyTemplatePid): ?array
{
    if ($legacyTemplatePid <= 0) {
        return null;
    }

    $map = app_html_template_catalog_by_pid($templateCatalog);

    return $map[(string) $legacyTemplatePid] ?? null;
}

/**
 * @param list<array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string
 * }> $templateCatalog
 * @return list<array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string,
 *     depth:int,
 *     parent_name:string
 * }>
 */
function app_html_template_catalog_tree_rows(array $templateCatalog): array
{
    $templateByPid = app_html_template_catalog_by_pid($templateCatalog);
    $childrenByParentPid = [];

    foreach ($templateCatalog as $template) {
        $parentPid = (string) ((int) ($template['parent_html_template_pid'] ?? 0));
        if (!array_key_exists($parentPid, $childrenByParentPid)) {
            $childrenByParentPid[$parentPid] = [];
        }

        $childrenByParentPid[$parentPid][] = $template;
    }

    foreach ($childrenByParentPid as &$items) {
        usort(
            $items,
            static function (array $left, array $right): int {
                $nameOrder = strnatcasecmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
                if ($nameOrder !== 0) {
                    return $nameOrder;
                }

                return ((int) ($left['legacy_html_template_pid'] ?? 0))
                    <=> ((int) ($right['legacy_html_template_pid'] ?? 0));
            },
        );
    }
    unset($items);

    $rows = [];
    $visited = [];
    $appendChildren = static function (
        int $parentPid,
        int $depth
    ) use (&$appendChildren, &$rows, &$visited, $childrenByParentPid, $templateByPid): void {
        $items = $childrenByParentPid[(string) $parentPid] ?? [];
        foreach ($items as $template) {
            $legacyTemplatePid = (int) ($template['legacy_html_template_pid'] ?? 0);
            if ($legacyTemplatePid <= 0 || array_key_exists($legacyTemplatePid, $visited)) {
                continue;
            }

            $visited[$legacyTemplatePid] = true;
            $parentTemplate = $templateByPid[(string) ((int) ($template['parent_html_template_pid'] ?? 0))] ?? null;
            $rows[] = $template + [
                'depth' => $depth,
                'parent_name' => (string) ($parentTemplate['name'] ?? ''),
            ];

            $appendChildren($legacyTemplatePid, $depth + 1);
        }
    };

    $appendChildren(0, 0);

    foreach ($templateCatalog as $template) {
        $legacyTemplatePid = (int) ($template['legacy_html_template_pid'] ?? 0);
        if ($legacyTemplatePid <= 0 || array_key_exists($legacyTemplatePid, $visited)) {
            continue;
        }

        $parentTemplate = $templateByPid[(string) ((int) ($template['parent_html_template_pid'] ?? 0))] ?? null;
        $rows[] = $template + [
            'depth' => 0,
            'parent_name' => (string) ($parentTemplate['name'] ?? ''),
        ];
        $appendChildren($legacyTemplatePid, 1);
    }

    return $rows;
}

/**
 * @param list<array{
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
 * }> $parameterCatalog
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
function app_html_template_parameter_catalog_by_template_pid(array $parameterCatalog): array
{
    $map = [];
    foreach ($parameterCatalog as $parameter) {
        $templatePid = (string) ((int) ($parameter['legacy_html_template_pid'] ?? 0));
        if ($templatePid === '0') {
            continue;
        }

        if (!array_key_exists($templatePid, $map)) {
            $map[$templatePid] = [];
        }

        $map[$templatePid][] = $parameter;
    }

    foreach ($map as &$items) {
        usort(
            $items,
            static fn (array $left, array $right): int
                => ((int) ($left['legacy_template_parameter_pid'] ?? 0))
                <=> ((int) ($right['legacy_template_parameter_pid'] ?? 0)),
        );
    }
    unset($items);

    return $map;
}

/**
 * @param list<array{
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
 * }> $parameterCatalog
 * @return array{
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
 * }|null
 */
function app_html_template_find_parameter_item_by_pid(array $parameterCatalog, int $legacyTemplateParameterPid): ?array
{
    if ($legacyTemplateParameterPid <= 0) {
        return null;
    }

    foreach ($parameterCatalog as $parameter) {
        if ((int) ($parameter['legacy_template_parameter_pid'] ?? 0) === $legacyTemplateParameterPid) {
            return $parameter;
        }
    }

    return null;
}

function app_html_template_reference_source_project_key(): string
{
    return 'MTOOL';
}

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
function app_load_html_template_reference(): array
{
    return app_load_legacy_html_reference(app_html_template_reference_source_project_key());
}

function app_html_template_pdo_table_exists(PDO $pdo, string $tableName): bool
{
    $normalizedTableName = trim($tableName);
    if ($normalizedTableName === '') {
        return false;
    }

    static $cache = [];

    $cacheKey = spl_object_id($pdo) . ':' . $normalizedTableName;
    if (array_key_exists($cacheKey, $cache)) {
        return $cache[$cacheKey];
    }

    $cache[$cacheKey] = app_sql_table_exists($pdo, $normalizedTableName);

    return $cache[$cacheKey];
}

function app_html_template_canonical_tables_available(PDO $pdo): bool
{
    return app_html_template_pdo_table_exists($pdo, 'html_templates')
        && app_html_template_pdo_table_exists($pdo, 'html_template_parameters');
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         legacy_html_template_pid:int,
 *         target_type:string,
 *         parent_html_template_pid:int,
 *         name:string,
 *         program_language:string,
 *         file_name:string,
 *         comment:string
 *     }>,
 *     error:string
 * }
 */
function app_html_template_reference_catalog(): array
{
    $referenceResult = app_load_html_template_reference();
    if (!$referenceResult['ok'] || $referenceResult['item'] === null) {
        return [
            'ok' => true,
            'items' => [],
            'error' => '',
        ];
    }

    return [
        'ok' => true,
        'items' => $referenceResult['item']['templates'],
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
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
 *     }>,
 *     error:string
 * }
 */
function app_html_template_reference_parameter_catalog(int $legacyTemplatePid = 0): array
{
    $referenceResult = app_load_html_template_reference();
    if (!$referenceResult['ok'] || $referenceResult['item'] === null) {
        return [
            'ok' => true,
            'items' => [],
            'error' => '',
        ];
    }

    $items = [];
    foreach ($referenceResult['item']['template_parameters'] as $parameter) {
        if ($legacyTemplatePid > 0 && (int) ($parameter['legacy_html_template_pid'] ?? 0) !== $legacyTemplatePid) {
            continue;
        }

        $items[] = $parameter;
    }

    usort(
        $items,
        static fn (array $left, array $right): int
            => ((int) ($left['legacy_template_parameter_pid'] ?? 0))
            <=> ((int) ($right['legacy_template_parameter_pid'] ?? 0)),
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
 *     items:list<array{
 *         legacy_html_template_pid:int,
 *         target_type:string,
 *         parent_html_template_pid:int,
 *         name:string,
 *         program_language:string,
 *         file_name:string,
 *         comment:string
 *     }>,
 *     error:string
 * }
 */
function app_html_template_legacy_fetch_catalog(PDO $pdo): array
{
    if (!app_html_template_pdo_table_exists($pdo, 'htmlTemplate')) {
        return [
            'ok' => true,
            'items' => [],
            'error' => '',
        ];
    }

    $statement = $pdo->query(
        'SELECT
            PID,
            TargetType,
            ParentHtmlTemplatePID,
            name,
            ProgramLanguage,
            FileName,
            Comment
        FROM htmlTemplate
        ORDER BY TargetType, ParentHtmlTemplatePID, name, PID'
    );

    $items = [];
    foreach ($statement->fetchAll() as $row) {
        if (!is_array($row)) {
            continue;
        }

        $items[] = [
            'legacy_html_template_pid' => (int) ($row['PID'] ?? 0),
            'target_type' => (string) ($row['TargetType'] ?? ''),
            'parent_html_template_pid' => (int) ($row['ParentHtmlTemplatePID'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'program_language' => (string) ($row['ProgramLanguage'] ?? ''),
            'file_name' => (string) ($row['FileName'] ?? ''),
            'comment' => (string) ($row['Comment'] ?? ''),
        ];
    }

    return [
        'ok' => true,
        'items' => $items,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
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
 *     }>,
 *     error:string
 * }
 */
function app_html_template_legacy_fetch_parameter_catalog(PDO $pdo, int $legacyTemplatePid = 0): array
{
    if (!app_html_template_pdo_table_exists($pdo, 'htmlTemplateParameter')) {
        return [
            'ok' => true,
            'items' => [],
            'error' => '',
        ];
    }

    if ($legacyTemplatePid > 0) {
        $statement = $pdo->prepare(
            'SELECT
                htmlTemplatePID,
                PID,
                ParameterName,
                TargetValueType,
                TargetVariableOrClassObject,
                TargetPropertyOfClassObject,
                AnotherTemplatePID,
                TrimLastSpace,
                TrimLastReturn,
                DataType
            FROM htmlTemplateParameter
            WHERE htmlTemplatePID = :html_template_pid
            ORDER BY PID'
        );
        $statement->execute([
            ':html_template_pid' => $legacyTemplatePid,
        ]);
    } else {
        $statement = $pdo->query(
            'SELECT
                htmlTemplatePID,
                PID,
                ParameterName,
                TargetValueType,
                TargetVariableOrClassObject,
                TargetPropertyOfClassObject,
                AnotherTemplatePID,
                TrimLastSpace,
                TrimLastReturn,
                DataType
            FROM htmlTemplateParameter
            ORDER BY htmlTemplatePID, PID'
        );
    }

    $items = [];
    foreach ($statement->fetchAll() as $row) {
        if (!is_array($row)) {
            continue;
        }

        $items[] = [
            'legacy_html_template_pid' => (int) ($row['htmlTemplatePID'] ?? 0),
            'legacy_template_parameter_pid' => (int) ($row['PID'] ?? 0),
            'parameter_name' => (string) ($row['ParameterName'] ?? ''),
            'target_value_type' => (string) ($row['TargetValueType'] ?? ''),
            'target_variable_or_class_object' => (string) ($row['TargetVariableOrClassObject'] ?? ''),
            'target_property_of_class_object' => (string) ($row['TargetPropertyOfClassObject'] ?? ''),
            'another_template_pid' => (int) ($row['AnotherTemplatePID'] ?? 0),
            'trim_last_space' => (int) ($row['TrimLastSpace'] ?? 0),
            'trim_last_return' => (int) ($row['TrimLastReturn'] ?? 0),
            'data_type' => (string) ($row['DataType'] ?? ''),
        ];
    }

    return [
        'ok' => true,
        'items' => $items,
        'error' => '',
    ];
}

function app_html_template_bootstrap_notes(string $sourceOfTruth): string
{
    return match ($sourceOfTruth) {
        'bootstrap-legacy' => 'bootstrapped from legacy htmlTemplate/htmlTemplateParameter table',
        'bootstrap-reference' => 'bootstrapped from copied MTOOL html reference; source_dump_path stays provenance-only metadata',
        default => '',
    };
}

function app_html_template_canonical_next_template_pid(PDO $pdo): int
{
    $value = $pdo->query('SELECT COALESCE(MAX(legacy_html_template_pid), 0) + 1 FROM html_templates')->fetchColumn();

    return max(1, (int) $value);
}

function app_html_template_canonical_next_parameter_pid(PDO $pdo): int
{
    $value = $pdo->query('SELECT COALESCE(MAX(legacy_template_parameter_pid), 0) + 1 FROM html_template_parameters')->fetchColumn();

    return max(1, (int) $value);
}

function app_html_template_canonical_template_id_by_legacy_pid(PDO $pdo, int $legacyTemplatePid): ?int
{
    $statement = $pdo->prepare(
        'SELECT id
        FROM html_templates
        WHERE legacy_html_template_pid = :legacy_html_template_pid
        LIMIT 1'
    );
    $statement->execute([
        ':legacy_html_template_pid' => $legacyTemplatePid,
    ]);

    $value = $statement->fetchColumn();
    if (!is_numeric($value)) {
        return null;
    }

    return (int) $value;
}

function app_html_template_canonical_parameter_id_by_legacy_pid(PDO $pdo, int $legacyTemplateParameterPid): ?int
{
    $statement = $pdo->prepare(
        'SELECT id
        FROM html_template_parameters
        WHERE legacy_template_parameter_pid = :legacy_template_parameter_pid
        LIMIT 1'
    );
    $statement->execute([
        ':legacy_template_parameter_pid' => $legacyTemplateParameterPid,
    ]);

    $value = $statement->fetchColumn();
    if (!is_numeric($value)) {
        return null;
    }

    return (int) $value;
}

/**
 * @param array<string,mixed> $template
 */
function app_html_template_canonical_upsert_template_sqlite(
    PDO $pdo,
    array $template,
    string $notes,
    string $sourceOfTruth,
): void {
    $legacyTemplatePid = (int) ($template['legacy_html_template_pid'] ?? 0);
    $templateId = app_html_template_canonical_template_id_by_legacy_pid($pdo, $legacyTemplatePid);
    if ($templateId !== null) {
        $statement = $pdo->prepare(
            'UPDATE html_templates
            SET
                target_type = :target_type,
                parent_legacy_html_template_pid = :parent_legacy_html_template_pid,
                name = :name,
                program_language = :program_language,
                file_name = :file_name,
                comment = :comment,
                notes = :notes,
                source_of_truth = :source_of_truth,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id'
        );
        $statement->execute([
            ':id' => $templateId,
            ':target_type' => (string) ($template['target_type'] ?? ''),
            ':parent_legacy_html_template_pid' => (int) ($template['parent_html_template_pid'] ?? 0),
            ':name' => (string) ($template['name'] ?? ''),
            ':program_language' => (string) ($template['program_language'] ?? ''),
            ':file_name' => (string) ($template['file_name'] ?? ''),
            ':comment' => (string) ($template['comment'] ?? ''),
            ':notes' => $notes,
            ':source_of_truth' => $sourceOfTruth,
        ]);

        return;
    }

    $statement = $pdo->prepare(
        'INSERT INTO html_templates (
            legacy_html_template_pid,
            target_type,
            parent_legacy_html_template_pid,
            name,
            program_language,
            file_name,
            comment,
            notes,
            source_of_truth
        ) VALUES (
            :legacy_html_template_pid,
            :target_type,
            :parent_legacy_html_template_pid,
            :name,
            :program_language,
            :file_name,
            :comment,
            :notes,
            :source_of_truth
        )'
    );
    $statement->execute([
        ':legacy_html_template_pid' => $legacyTemplatePid,
        ':target_type' => (string) ($template['target_type'] ?? ''),
        ':parent_legacy_html_template_pid' => (int) ($template['parent_html_template_pid'] ?? 0),
        ':name' => (string) ($template['name'] ?? ''),
        ':program_language' => (string) ($template['program_language'] ?? ''),
        ':file_name' => (string) ($template['file_name'] ?? ''),
        ':comment' => (string) ($template['comment'] ?? ''),
        ':notes' => $notes,
        ':source_of_truth' => $sourceOfTruth,
    ]);
}

/**
 * @param array<string,mixed> $parameter
 */
function app_html_template_canonical_upsert_parameter_sqlite(
    PDO $pdo,
    array $parameter,
    string $notes,
    string $sourceOfTruth,
): void {
    $legacyParameterPid = (int) ($parameter['legacy_template_parameter_pid'] ?? 0);
    $parameterId = app_html_template_canonical_parameter_id_by_legacy_pid($pdo, $legacyParameterPid);
    if ($parameterId !== null) {
        $statement = $pdo->prepare(
            'UPDATE html_template_parameters
            SET
                legacy_html_template_pid = :legacy_html_template_pid,
                parameter_name = :parameter_name,
                target_value_type = :target_value_type,
                target_variable_or_class_object = :target_variable_or_class_object,
                target_property_of_class_object = :target_property_of_class_object,
                another_template_pid = :another_template_pid,
                trim_last_space = :trim_last_space,
                trim_last_return = :trim_last_return,
                data_type = :data_type,
                notes = :notes,
                source_of_truth = :source_of_truth,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id'
        );
        $statement->execute([
            ':id' => $parameterId,
            ':legacy_html_template_pid' => (int) ($parameter['legacy_html_template_pid'] ?? 0),
            ':parameter_name' => (string) ($parameter['parameter_name'] ?? ''),
            ':target_value_type' => (string) ($parameter['target_value_type'] ?? ''),
            ':target_variable_or_class_object' => (string) ($parameter['target_variable_or_class_object'] ?? ''),
            ':target_property_of_class_object' => (string) ($parameter['target_property_of_class_object'] ?? ''),
            ':another_template_pid' => (int) ($parameter['another_template_pid'] ?? 0),
            ':trim_last_space' => (int) ($parameter['trim_last_space'] ?? 0),
            ':trim_last_return' => (int) ($parameter['trim_last_return'] ?? 0),
            ':data_type' => (string) ($parameter['data_type'] ?? ''),
            ':notes' => $notes,
            ':source_of_truth' => $sourceOfTruth,
        ]);

        return;
    }

    $statement = $pdo->prepare(
        'INSERT INTO html_template_parameters (
            legacy_template_parameter_pid,
            legacy_html_template_pid,
            parameter_name,
            target_value_type,
            target_variable_or_class_object,
            target_property_of_class_object,
            another_template_pid,
            trim_last_space,
            trim_last_return,
            data_type,
            notes,
            source_of_truth
        ) VALUES (
            :legacy_template_parameter_pid,
            :legacy_html_template_pid,
            :parameter_name,
            :target_value_type,
            :target_variable_or_class_object,
            :target_property_of_class_object,
            :another_template_pid,
            :trim_last_space,
            :trim_last_return,
            :data_type,
            :notes,
            :source_of_truth
        )'
    );
    $statement->execute([
        ':legacy_template_parameter_pid' => $legacyParameterPid,
        ':legacy_html_template_pid' => (int) ($parameter['legacy_html_template_pid'] ?? 0),
        ':parameter_name' => (string) ($parameter['parameter_name'] ?? ''),
        ':target_value_type' => (string) ($parameter['target_value_type'] ?? ''),
        ':target_variable_or_class_object' => (string) ($parameter['target_variable_or_class_object'] ?? ''),
        ':target_property_of_class_object' => (string) ($parameter['target_property_of_class_object'] ?? ''),
        ':another_template_pid' => (int) ($parameter['another_template_pid'] ?? 0),
        ':trim_last_space' => (int) ($parameter['trim_last_space'] ?? 0),
        ':trim_last_return' => (int) ($parameter['trim_last_return'] ?? 0),
        ':data_type' => (string) ($parameter['data_type'] ?? ''),
        ':notes' => $notes,
        ':source_of_truth' => $sourceOfTruth,
    ]);
}

function app_html_template_bootstrap_into_canonical(PDO $pdo): void
{
    if (!app_html_template_canonical_tables_available($pdo)) {
        return;
    }

    $existingCount = (int) $pdo->query('SELECT COUNT(*) FROM html_templates')->fetchColumn();
    if ($existingCount > 0) {
        return;
    }

    $sourceOfTruth = 'bootstrap-reference';
    $templateCatalogResult = app_html_template_reference_catalog();
    $parameterCatalogResult = app_html_template_reference_parameter_catalog();

    if (app_html_template_pdo_table_exists($pdo, 'htmlTemplate')) {
        $legacyTemplateCatalogResult = app_html_template_legacy_fetch_catalog($pdo);
        if ($legacyTemplateCatalogResult['items'] !== []) {
            $sourceOfTruth = 'bootstrap-legacy';
            $templateCatalogResult = $legacyTemplateCatalogResult;
            $parameterCatalogResult = app_html_template_legacy_fetch_parameter_catalog($pdo);
        }
    }

    $templateCatalog = $templateCatalogResult['items'];
    if ($templateCatalog === []) {
        return;
    }

    $templateByPid = app_html_template_catalog_by_pid($templateCatalog);
    $parameterCatalog = array_values(
        array_filter(
            $parameterCatalogResult['items'],
            static fn (array $item): bool
                => array_key_exists((string) ((int) ($item['legacy_html_template_pid'] ?? 0)), $templateByPid),
        ),
    );

    $notes = app_html_template_bootstrap_notes($sourceOfTruth);
    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) {
        $pdo->beginTransaction();
    }

    try {
        $dialect = app_sql_dialect_from_pdo($pdo);
        $templateStatement = null;
        if ($dialect !== 'sqlite') {
            $templateStatement = $pdo->prepare(
                'INSERT INTO html_templates (
                    legacy_html_template_pid,
                    target_type,
                    parent_legacy_html_template_pid,
                    name,
                    program_language,
                    file_name,
                    comment,
                    notes,
                    source_of_truth
                ) VALUES (
                    :legacy_html_template_pid,
                    :target_type,
                    :parent_legacy_html_template_pid,
                    :name,
                    :program_language,
                    :file_name,
                    :comment,
                    :notes,
                    :source_of_truth
                )
                ON DUPLICATE KEY UPDATE
                    target_type = VALUES(target_type),
                    parent_legacy_html_template_pid = VALUES(parent_legacy_html_template_pid),
                    name = VALUES(name),
                    program_language = VALUES(program_language),
                    file_name = VALUES(file_name),
                    comment = VALUES(comment),
                    notes = VALUES(notes),
                    source_of_truth = VALUES(source_of_truth),
                    updated_at = CURRENT_TIMESTAMP'
            );
        }

        foreach ($templateCatalog as $template) {
            if ($dialect === 'sqlite') {
                app_html_template_canonical_upsert_template_sqlite($pdo, $template, $notes, $sourceOfTruth);
            } elseif ($templateStatement instanceof PDOStatement) {
                $templateStatement->execute([
                    ':legacy_html_template_pid' => (int) ($template['legacy_html_template_pid'] ?? 0),
                    ':target_type' => (string) ($template['target_type'] ?? ''),
                    ':parent_legacy_html_template_pid' => (int) ($template['parent_html_template_pid'] ?? 0),
                    ':name' => (string) ($template['name'] ?? ''),
                    ':program_language' => (string) ($template['program_language'] ?? ''),
                    ':file_name' => (string) ($template['file_name'] ?? ''),
                    ':comment' => (string) ($template['comment'] ?? ''),
                    ':notes' => $notes,
                    ':source_of_truth' => $sourceOfTruth,
                ]);
            }
        }

        $parameterStatement = null;
        if ($dialect !== 'sqlite') {
            $parameterStatement = $pdo->prepare(
                'INSERT INTO html_template_parameters (
                    legacy_template_parameter_pid,
                    legacy_html_template_pid,
                    parameter_name,
                    target_value_type,
                    target_variable_or_class_object,
                    target_property_of_class_object,
                    another_template_pid,
                    trim_last_space,
                    trim_last_return,
                    data_type,
                    notes,
                    source_of_truth
                ) VALUES (
                    :legacy_template_parameter_pid,
                    :legacy_html_template_pid,
                    :parameter_name,
                    :target_value_type,
                    :target_variable_or_class_object,
                    :target_property_of_class_object,
                    :another_template_pid,
                    :trim_last_space,
                    :trim_last_return,
                    :data_type,
                    :notes,
                    :source_of_truth
                )
                ON DUPLICATE KEY UPDATE
                    legacy_html_template_pid = VALUES(legacy_html_template_pid),
                    parameter_name = VALUES(parameter_name),
                    target_value_type = VALUES(target_value_type),
                    target_variable_or_class_object = VALUES(target_variable_or_class_object),
                    target_property_of_class_object = VALUES(target_property_of_class_object),
                    another_template_pid = VALUES(another_template_pid),
                    trim_last_space = VALUES(trim_last_space),
                    trim_last_return = VALUES(trim_last_return),
                    data_type = VALUES(data_type),
                    notes = VALUES(notes),
                    source_of_truth = VALUES(source_of_truth),
                    updated_at = CURRENT_TIMESTAMP'
            );
        }

        foreach ($parameterCatalog as $parameter) {
            if ($dialect === 'sqlite') {
                app_html_template_canonical_upsert_parameter_sqlite($pdo, $parameter, $notes, $sourceOfTruth);
            } elseif ($parameterStatement instanceof PDOStatement) {
                $parameterStatement->execute([
                    ':legacy_template_parameter_pid' => (int) ($parameter['legacy_template_parameter_pid'] ?? 0),
                    ':legacy_html_template_pid' => (int) ($parameter['legacy_html_template_pid'] ?? 0),
                    ':parameter_name' => (string) ($parameter['parameter_name'] ?? ''),
                    ':target_value_type' => (string) ($parameter['target_value_type'] ?? ''),
                    ':target_variable_or_class_object' => (string) ($parameter['target_variable_or_class_object'] ?? ''),
                    ':target_property_of_class_object' => (string) ($parameter['target_property_of_class_object'] ?? ''),
                    ':another_template_pid' => (int) ($parameter['another_template_pid'] ?? 0),
                    ':trim_last_space' => (int) ($parameter['trim_last_space'] ?? 0),
                    ':trim_last_return' => (int) ($parameter['trim_last_return'] ?? 0),
                    ':data_type' => (string) ($parameter['data_type'] ?? ''),
                    ':notes' => $notes,
                    ':source_of_truth' => $sourceOfTruth,
                ]);
            }
        }

        if ($ownsTransaction) {
            $pdo->commit();
        }
    } catch (Throwable $throwable) {
        if ($ownsTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $throwable;
    }
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         legacy_html_template_pid:int,
 *         target_type:string,
 *         parent_html_template_pid:int,
 *         name:string,
 *         program_language:string,
 *         file_name:string,
 *         comment:string
 *     }>,
 *     error:string
 * }
 */
function app_html_template_canonical_fetch_catalog(PDO $pdo): array
{
    app_html_template_bootstrap_into_canonical($pdo);

    $statement = $pdo->query(
        'SELECT
            legacy_html_template_pid,
            target_type,
            parent_legacy_html_template_pid,
            name,
            program_language,
            file_name,
            comment
        FROM html_templates
        ORDER BY target_type, parent_legacy_html_template_pid, name, legacy_html_template_pid'
    );

    $items = [];
    foreach ($statement->fetchAll() as $row) {
        if (!is_array($row)) {
            continue;
        }

        $items[] = [
            'legacy_html_template_pid' => (int) ($row['legacy_html_template_pid'] ?? 0),
            'target_type' => (string) ($row['target_type'] ?? ''),
            'parent_html_template_pid' => (int) ($row['parent_legacy_html_template_pid'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'program_language' => (string) ($row['program_language'] ?? ''),
            'file_name' => (string) ($row['file_name'] ?? ''),
            'comment' => (string) ($row['comment'] ?? ''),
        ];
    }

    return [
        'ok' => true,
        'items' => $items,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
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
 *     }>,
 *     error:string
 * }
 */
function app_html_template_canonical_fetch_parameter_catalog(PDO $pdo, int $legacyTemplatePid = 0): array
{
    app_html_template_bootstrap_into_canonical($pdo);

    if ($legacyTemplatePid > 0) {
        $statement = $pdo->prepare(
            'SELECT
                legacy_html_template_pid,
                legacy_template_parameter_pid,
                parameter_name,
                target_value_type,
                target_variable_or_class_object,
                target_property_of_class_object,
                another_template_pid,
                trim_last_space,
                trim_last_return,
                data_type
            FROM html_template_parameters
            WHERE legacy_html_template_pid = :legacy_html_template_pid
            ORDER BY legacy_template_parameter_pid'
        );
        $statement->execute([
            ':legacy_html_template_pid' => $legacyTemplatePid,
        ]);
    } else {
        $statement = $pdo->query(
            'SELECT
                legacy_html_template_pid,
                legacy_template_parameter_pid,
                parameter_name,
                target_value_type,
                target_variable_or_class_object,
                target_property_of_class_object,
                another_template_pid,
                trim_last_space,
                trim_last_return,
                data_type
            FROM html_template_parameters
            ORDER BY legacy_html_template_pid, legacy_template_parameter_pid'
        );
    }

    $items = [];
    foreach ($statement->fetchAll() as $row) {
        if (!is_array($row)) {
            continue;
        }

        $items[] = [
            'legacy_html_template_pid' => (int) ($row['legacy_html_template_pid'] ?? 0),
            'legacy_template_parameter_pid' => (int) ($row['legacy_template_parameter_pid'] ?? 0),
            'parameter_name' => (string) ($row['parameter_name'] ?? ''),
            'target_value_type' => (string) ($row['target_value_type'] ?? ''),
            'target_variable_or_class_object' => (string) ($row['target_variable_or_class_object'] ?? ''),
            'target_property_of_class_object' => (string) ($row['target_property_of_class_object'] ?? ''),
            'another_template_pid' => (int) ($row['another_template_pid'] ?? 0),
            'trim_last_space' => (int) ($row['trim_last_space'] ?? 0),
            'trim_last_return' => (int) ($row['trim_last_return'] ?? 0),
            'data_type' => (string) ($row['data_type'] ?? ''),
        ];
    }

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
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         legacy_html_template_pid:int,
 *         target_type:string,
 *         parent_html_template_pid:int,
 *         name:string,
 *         program_language:string,
 *         file_name:string,
 *         comment:string
 *     }>,
 *     error:string
 * }
 */
function app_fetch_html_template_catalog(array $app): array
{
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_html_template_canonical_tables_available($pdo)) {
            $canonicalResult = app_html_template_canonical_fetch_catalog($pdo);
            if (!$canonicalResult['ok']) {
                return $canonicalResult;
            }

            if ($canonicalResult['items'] !== [] || !app_html_template_pdo_table_exists($pdo, 'htmlTemplate')) {
                return $canonicalResult;
            }
        }

        if (app_html_template_pdo_table_exists($pdo, 'htmlTemplate')) {
            $legacyResult = app_html_template_legacy_fetch_catalog($pdo);
            if ($legacyResult['items'] !== []) {
                return $legacyResult;
            }
        }

        return app_html_template_reference_catalog();
    } catch (Throwable $throwable) {
        $fallback = app_html_template_reference_catalog();
        if ($fallback['items'] !== []) {
            return $fallback;
        }

        return [
            'ok' => false,
            'items' => [],
            'error' => $throwable->getMessage(),
        ];
    }
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
 *     item:array{
 *         legacy_html_template_pid:int,
 *         target_type:string,
 *         parent_html_template_pid:int,
 *         name:string,
 *         program_language:string,
 *         file_name:string,
 *         comment:string
 *     }|null,
 *     error:string
 * }
 */
function app_fetch_html_template_by_pid(array $app, int $legacyTemplatePid): array
{
    $catalogResult = app_fetch_html_template_catalog($app);
    if (!$catalogResult['ok']) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $catalogResult['error'],
        ];
    }

    return [
        'ok' => true,
        'item' => app_html_template_find_catalog_item_by_pid($catalogResult['items'], $legacyTemplatePid),
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
 * @return array{
 *     ok:bool,
 *     items:list<array{
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
 *     }>,
 *     error:string
 * }
 */
function app_fetch_html_template_parameter_catalog(array $app, int $legacyTemplatePid = 0): array
{
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_html_template_canonical_tables_available($pdo)) {
            $canonicalResult = app_html_template_canonical_fetch_parameter_catalog($pdo, $legacyTemplatePid);
            if (!$canonicalResult['ok']) {
                return $canonicalResult;
            }

            if ($canonicalResult['items'] !== [] || !app_html_template_pdo_table_exists($pdo, 'htmlTemplateParameter')) {
                return $canonicalResult;
            }
        }

        if (app_html_template_pdo_table_exists($pdo, 'htmlTemplateParameter')) {
            $legacyResult = app_html_template_legacy_fetch_parameter_catalog($pdo, $legacyTemplatePid);
            if ($legacyResult['items'] !== []) {
                return $legacyResult;
            }
        }

        return app_html_template_reference_parameter_catalog($legacyTemplatePid);
    } catch (Throwable $throwable) {
        $fallback = app_html_template_reference_parameter_catalog($legacyTemplatePid);
        if ($fallback['items'] !== []) {
            return $fallback;
        }

        return [
            'ok' => false,
            'items' => [],
            'error' => $throwable->getMessage(),
        ];
    }
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
 *     item:array{
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
 *     }|null,
 *     error:string
 * }
 */
function app_fetch_html_template_parameter_by_pid(
    array $app,
    int $legacyTemplatePid,
    int $legacyTemplateParameterPid,
): array {
    $catalogResult = app_fetch_html_template_parameter_catalog($app, $legacyTemplatePid);
    if (!$catalogResult['ok']) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $catalogResult['error'],
        ];
    }

    return [
        'ok' => true,
        'item' => app_html_template_find_parameter_item_by_pid(
            $catalogResult['items'],
            $legacyTemplateParameterPid,
        ),
        'error' => '',
    ];
}

/**
 * @param array{
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string
 * } $input
 * @return array{
 *     ok:bool,
 *     item:array{
 *         legacy_html_template_pid:int,
 *         target_type:string,
 *         parent_html_template_pid:int,
 *         name:string,
 *         program_language:string,
 *         file_name:string,
 *         comment:string
 *     }|null,
 *     error:string
 * }
 */
function app_create_html_template(array $app, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_html_template_canonical_tables_available($pdo)) {
            app_html_template_bootstrap_into_canonical($pdo);

            $legacyTemplatePid = app_html_template_canonical_next_template_pid($pdo);
            $statement = $pdo->prepare(
                'INSERT INTO html_templates (
                    legacy_html_template_pid,
                    target_type,
                    parent_legacy_html_template_pid,
                    name,
                    program_language,
                    file_name,
                    comment,
                    notes,
                    source_of_truth
                ) VALUES (
                    :legacy_html_template_pid,
                    :target_type,
                    :parent_legacy_html_template_pid,
                    :name,
                    :program_language,
                    :file_name,
                    :comment,
                    :notes,
                    :source_of_truth
                )'
            );
            $statement->execute([
                ':legacy_html_template_pid' => $legacyTemplatePid,
                ':target_type' => (string) ($input['target_type'] ?? ''),
                ':parent_legacy_html_template_pid' => (int) ($input['parent_html_template_pid'] ?? 0),
                ':name' => (string) ($input['name'] ?? ''),
                ':program_language' => (string) ($input['program_language'] ?? ''),
                ':file_name' => (string) ($input['file_name'] ?? ''),
                ':comment' => (string) ($input['comment'] ?? ''),
                ':notes' => '',
                ':source_of_truth' => 'manual',
            ]);

            return app_fetch_html_template_by_pid($app, $legacyTemplatePid);
        }

        if (!app_html_template_pdo_table_exists($pdo, 'htmlTemplate')) {
            return [
                'ok' => false,
                'item' => null,
                'error' => 'HTML template canonical table も legacy htmlTemplate table も存在しません。',
            ];
        }

        $legacyResult = app_html_template_legacy_fetch_catalog($pdo);
        $legacyTemplatePid = 1;
        foreach ($legacyResult['items'] as $item) {
            $legacyTemplatePid = max(
                $legacyTemplatePid,
                ((int) ($item['legacy_html_template_pid'] ?? 0)) + 1,
            );
        }

        $statement = $pdo->prepare(
            'INSERT INTO htmlTemplate (
                PID,
                TargetType,
                ParentHtmlTemplatePID,
                name,
                ProgramLanguage,
                FileName,
                Comment
            ) VALUES (
                :pid,
                :target_type,
                :parent_html_template_pid,
                :name,
                :program_language,
                :file_name,
                :comment
            )'
        );
        $statement->execute([
            ':pid' => $legacyTemplatePid,
            ':target_type' => (string) ($input['target_type'] ?? ''),
            ':parent_html_template_pid' => (int) ($input['parent_html_template_pid'] ?? 0),
            ':name' => (string) ($input['name'] ?? ''),
            ':program_language' => (string) ($input['program_language'] ?? ''),
            ':file_name' => (string) ($input['file_name'] ?? ''),
            ':comment' => (string) ($input['comment'] ?? ''),
        ]);

        return app_fetch_html_template_by_pid($app, $legacyTemplatePid);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string
 * } $input
 * @return array{
 *     ok:bool,
 *     item:array{
 *         legacy_html_template_pid:int,
 *         target_type:string,
 *         parent_html_template_pid:int,
 *         name:string,
 *         program_language:string,
 *         file_name:string,
 *         comment:string
 *     }|null,
 *     error:string
 * }
 */
function app_update_html_template(array $app, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_html_template_canonical_tables_available($pdo)) {
            app_html_template_bootstrap_into_canonical($pdo);

            $statement = $pdo->prepare(
                'UPDATE html_templates
                SET
                    target_type = :target_type,
                    parent_legacy_html_template_pid = :parent_legacy_html_template_pid,
                    name = :name,
                    program_language = :program_language,
                    file_name = :file_name,
                    comment = :comment,
                    notes = :notes,
                    source_of_truth = :source_of_truth,
                    updated_at = CURRENT_TIMESTAMP
                WHERE legacy_html_template_pid = :legacy_html_template_pid'
            );
            $statement->execute([
                ':target_type' => (string) ($input['target_type'] ?? ''),
                ':parent_legacy_html_template_pid' => (int) ($input['parent_html_template_pid'] ?? 0),
                ':name' => (string) ($input['name'] ?? ''),
                ':program_language' => (string) ($input['program_language'] ?? ''),
                ':file_name' => (string) ($input['file_name'] ?? ''),
                ':comment' => (string) ($input['comment'] ?? ''),
                ':notes' => '',
                ':source_of_truth' => 'manual',
                ':legacy_html_template_pid' => (int) ($input['legacy_html_template_pid'] ?? 0),
            ]);

            return app_fetch_html_template_by_pid($app, (int) ($input['legacy_html_template_pid'] ?? 0));
        }

        if (!app_html_template_pdo_table_exists($pdo, 'htmlTemplate')) {
            return [
                'ok' => false,
                'item' => null,
                'error' => 'HTML template canonical table も legacy htmlTemplate table も存在しません。',
            ];
        }

        $statement = $pdo->prepare(
            'UPDATE htmlTemplate
            SET
                TargetType = :target_type,
                ParentHtmlTemplatePID = :parent_html_template_pid,
                name = :name,
                ProgramLanguage = :program_language,
                FileName = :file_name,
                Comment = :comment
            WHERE PID = :pid'
        );
        $statement->execute([
            ':target_type' => (string) ($input['target_type'] ?? ''),
            ':parent_html_template_pid' => (int) ($input['parent_html_template_pid'] ?? 0),
            ':name' => (string) ($input['name'] ?? ''),
            ':program_language' => (string) ($input['program_language'] ?? ''),
            ':file_name' => (string) ($input['file_name'] ?? ''),
            ':comment' => (string) ($input['comment'] ?? ''),
            ':pid' => (int) ($input['legacy_html_template_pid'] ?? 0),
        ]);

        return app_fetch_html_template_by_pid($app, (int) ($input['legacy_html_template_pid'] ?? 0));
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return list<string>
 */
function app_html_template_delete_conflicts(PDO $pdo, int $legacyTemplatePid): array
{
    $conflicts = [];

    $childTemplateCount = 0;
    if (app_html_template_canonical_tables_available($pdo)) {
        app_html_template_bootstrap_into_canonical($pdo);

        $statement = $pdo->prepare(
            'SELECT COUNT(*)
            FROM html_templates
            WHERE parent_legacy_html_template_pid = :legacy_html_template_pid'
        );
        $statement->execute([
            ':legacy_html_template_pid' => $legacyTemplatePid,
        ]);
        $childTemplateCount = (int) $statement->fetchColumn();

        $statement = $pdo->prepare(
            'SELECT COUNT(*)
            FROM html_template_parameters
            WHERE another_template_pid = :another_template_pid
              AND legacy_html_template_pid <> :current_template_pid'
        );
        $statement->execute([
            ':another_template_pid' => $legacyTemplatePid,
            ':current_template_pid' => $legacyTemplatePid,
        ]);
        $anotherTemplateReferenceCount = (int) $statement->fetchColumn();

        $htmlUsageCount = 0;
        if (app_html_template_pdo_table_exists($pdo, 'project_html_definitions')) {
            $statement = $pdo->prepare(
                'SELECT COUNT(*)
                FROM project_html_definitions
                WHERE legacy_html_template_pid = :legacy_html_template_pid'
            );
            $statement->execute([
                ':legacy_html_template_pid' => $legacyTemplatePid,
            ]);
            $htmlUsageCount += (int) $statement->fetchColumn();
        }
        if (app_html_template_pdo_table_exists($pdo, 'html')) {
            $statement = $pdo->prepare(
                'SELECT COUNT(*)
                FROM html
                WHERE htmlTemplatePID = :legacy_html_template_pid'
            );
            $statement->execute([
                ':legacy_html_template_pid' => $legacyTemplatePid,
            ]);
            $htmlUsageCount += (int) $statement->fetchColumn();
        }

        if ($childTemplateCount > 0) {
            $conflicts[] = 'child template が ' . (string) $childTemplateCount . ' 件あるため削除できません。';
        }
        if ($anotherTemplateReferenceCount > 0) {
            $conflicts[] = '他 template parameter の AnotherTemplate 参照が '
                . (string) $anotherTemplateReferenceCount
                . ' 件あるため削除できません。';
        }
        if ($htmlUsageCount > 0) {
            $conflicts[] = 'HTML row からの参照が ' . (string) $htmlUsageCount . ' 件あるため削除できません。';
        }

        return $conflicts;
    }

    if (app_html_template_pdo_table_exists($pdo, 'htmlTemplate')) {
        $statement = $pdo->prepare(
            'SELECT COUNT(*)
            FROM htmlTemplate
            WHERE ParentHtmlTemplatePID = :legacy_html_template_pid'
        );
        $statement->execute([
            ':legacy_html_template_pid' => $legacyTemplatePid,
        ]);
        $childTemplateCount = (int) $statement->fetchColumn();

        if ($childTemplateCount > 0) {
            $conflicts[] = 'child template が ' . (string) $childTemplateCount . ' 件あるため削除できません。';
        }
    }

    if (app_html_template_pdo_table_exists($pdo, 'htmlTemplateParameter')) {
        $statement = $pdo->prepare(
            'SELECT COUNT(*)
            FROM htmlTemplateParameter
            WHERE AnotherTemplatePID = :another_template_pid
              AND htmlTemplatePID <> :current_template_pid'
        );
        $statement->execute([
            ':another_template_pid' => $legacyTemplatePid,
            ':current_template_pid' => $legacyTemplatePid,
        ]);
        $referenceCount = (int) $statement->fetchColumn();
        if ($referenceCount > 0) {
            $conflicts[] = '他 template parameter の AnotherTemplate 参照が '
                . (string) $referenceCount
                . ' 件あるため削除できません。';
        }
    }

    if (app_html_template_pdo_table_exists($pdo, 'html')) {
        $statement = $pdo->prepare(
            'SELECT COUNT(*)
            FROM html
            WHERE htmlTemplatePID = :legacy_html_template_pid'
        );
        $statement->execute([
            ':legacy_html_template_pid' => $legacyTemplatePid,
        ]);
        $htmlUsageCount = (int) $statement->fetchColumn();
        if ($htmlUsageCount > 0) {
            $conflicts[] = 'HTML row からの参照が ' . (string) $htmlUsageCount . ' 件あるため削除できません。';
        }
    }

    return $conflicts;
}

/**
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_delete_html_template(array $app, int $legacyTemplatePid): array
{
    try {
        $pdo = app_create_metadata_pdo($app);
        $conflicts = app_html_template_delete_conflicts($pdo, $legacyTemplatePid);
        if ($conflicts !== []) {
            return [
                'ok' => false,
                'error' => implode(' ', $conflicts),
            ];
        }

        if (app_html_template_canonical_tables_available($pdo)) {
            app_html_template_bootstrap_into_canonical($pdo);

            $ownsTransaction = !$pdo->inTransaction();
            if ($ownsTransaction) {
                $pdo->beginTransaction();
            }

            try {
                $statement = $pdo->prepare(
                    'DELETE FROM html_template_parameters
                    WHERE legacy_html_template_pid = :legacy_html_template_pid'
                );
                $statement->execute([
                    ':legacy_html_template_pid' => $legacyTemplatePid,
                ]);

                $statement = $pdo->prepare(
                    'DELETE FROM html_templates
                    WHERE legacy_html_template_pid = :legacy_html_template_pid'
                );
                $statement->execute([
                    ':legacy_html_template_pid' => $legacyTemplatePid,
                ]);

                if ($ownsTransaction) {
                    $pdo->commit();
                }
            } catch (Throwable $throwable) {
                if ($ownsTransaction && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                throw $throwable;
            }

            return [
                'ok' => true,
                'error' => '',
            ];
        }

        if (!app_html_template_pdo_table_exists($pdo, 'htmlTemplate')) {
            return [
                'ok' => false,
                'error' => 'HTML template canonical table も legacy htmlTemplate table も存在しません。',
            ];
        }

        $ownsTransaction = !$pdo->inTransaction();
        if ($ownsTransaction) {
            $pdo->beginTransaction();
        }

        try {
            if (app_html_template_pdo_table_exists($pdo, 'htmlTemplateParameter')) {
                $statement = $pdo->prepare(
                    'DELETE FROM htmlTemplateParameter
                    WHERE htmlTemplatePID = :html_template_pid'
                );
                $statement->execute([
                    ':html_template_pid' => $legacyTemplatePid,
                ]);
            }

            $statement = $pdo->prepare(
                'DELETE FROM htmlTemplate
                WHERE PID = :pid'
            );
            $statement->execute([
                ':pid' => $legacyTemplatePid,
            ]);

            if ($ownsTransaction) {
                $pdo->commit();
            }
        } catch (Throwable $throwable) {
            if ($ownsTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     legacy_html_template_pid:int,
 *     parameter_name:string,
 *     target_value_type:string,
 *     target_variable_or_class_object:string,
 *     target_property_of_class_object:string,
 *     another_template_pid:int,
 *     trim_last_space:int,
 *     trim_last_return:int,
 *     data_type:string
 * } $input
 * @return array{
 *     ok:bool,
 *     item:array{
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
 *     }|null,
 *     error:string
 * }
 */
function app_create_html_template_parameter(array $app, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_html_template_canonical_tables_available($pdo)) {
            app_html_template_bootstrap_into_canonical($pdo);

            $legacyTemplateParameterPid = app_html_template_canonical_next_parameter_pid($pdo);
            $statement = $pdo->prepare(
                'INSERT INTO html_template_parameters (
                    legacy_template_parameter_pid,
                    legacy_html_template_pid,
                    parameter_name,
                    target_value_type,
                    target_variable_or_class_object,
                    target_property_of_class_object,
                    another_template_pid,
                    trim_last_space,
                    trim_last_return,
                    data_type,
                    notes,
                    source_of_truth
                ) VALUES (
                    :legacy_template_parameter_pid,
                    :legacy_html_template_pid,
                    :parameter_name,
                    :target_value_type,
                    :target_variable_or_class_object,
                    :target_property_of_class_object,
                    :another_template_pid,
                    :trim_last_space,
                    :trim_last_return,
                    :data_type,
                    :notes,
                    :source_of_truth
                )'
            );
            $statement->execute([
                ':legacy_template_parameter_pid' => $legacyTemplateParameterPid,
                ':legacy_html_template_pid' => (int) ($input['legacy_html_template_pid'] ?? 0),
                ':parameter_name' => (string) ($input['parameter_name'] ?? ''),
                ':target_value_type' => (string) ($input['target_value_type'] ?? ''),
                ':target_variable_or_class_object' => (string) ($input['target_variable_or_class_object'] ?? ''),
                ':target_property_of_class_object' => (string) ($input['target_property_of_class_object'] ?? ''),
                ':another_template_pid' => (int) ($input['another_template_pid'] ?? 0),
                ':trim_last_space' => (int) ($input['trim_last_space'] ?? 0),
                ':trim_last_return' => (int) ($input['trim_last_return'] ?? 0),
                ':data_type' => (string) ($input['data_type'] ?? ''),
                ':notes' => '',
                ':source_of_truth' => 'manual',
            ]);

            return app_fetch_html_template_parameter_by_pid(
                $app,
                (int) ($input['legacy_html_template_pid'] ?? 0),
                $legacyTemplateParameterPid,
            );
        }

        if (!app_html_template_pdo_table_exists($pdo, 'htmlTemplateParameter')) {
            return [
                'ok' => false,
                'item' => null,
                'error' => 'HTML template parameter canonical table も legacy htmlTemplateParameter table も存在しません。',
            ];
        }

        $legacyResult = app_html_template_legacy_fetch_parameter_catalog($pdo);
        $legacyTemplateParameterPid = 1;
        foreach ($legacyResult['items'] as $item) {
            $legacyTemplateParameterPid = max(
                $legacyTemplateParameterPid,
                ((int) ($item['legacy_template_parameter_pid'] ?? 0)) + 1,
            );
        }

        $statement = $pdo->prepare(
            'INSERT INTO htmlTemplateParameter (
                htmlTemplatePID,
                PID,
                ParameterName,
                TargetValueType,
                TargetVariableOrClassObject,
                TargetPropertyOfClassObject,
                AnotherTemplatePID,
                TrimLastSpace,
                TrimLastReturn,
                DataType
            ) VALUES (
                :html_template_pid,
                :pid,
                :parameter_name,
                :target_value_type,
                :target_variable_or_class_object,
                :target_property_of_class_object,
                :another_template_pid,
                :trim_last_space,
                :trim_last_return,
                :data_type
            )'
        );
        $statement->execute([
            ':html_template_pid' => (int) ($input['legacy_html_template_pid'] ?? 0),
            ':pid' => $legacyTemplateParameterPid,
            ':parameter_name' => (string) ($input['parameter_name'] ?? ''),
            ':target_value_type' => (string) ($input['target_value_type'] ?? ''),
            ':target_variable_or_class_object' => (string) ($input['target_variable_or_class_object'] ?? ''),
            ':target_property_of_class_object' => (string) ($input['target_property_of_class_object'] ?? ''),
            ':another_template_pid' => (int) ($input['another_template_pid'] ?? 0),
            ':trim_last_space' => (int) ($input['trim_last_space'] ?? 0),
            ':trim_last_return' => (int) ($input['trim_last_return'] ?? 0),
            ':data_type' => (string) ($input['data_type'] ?? ''),
        ]);

        return app_fetch_html_template_parameter_by_pid(
            $app,
            (int) ($input['legacy_html_template_pid'] ?? 0),
            $legacyTemplateParameterPid,
        );
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
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
 * } $input
 * @return array{
 *     ok:bool,
 *     item:array{
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
 *     }|null,
 *     error:string
 * }
 */
function app_update_html_template_parameter(array $app, array $input): array
{
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_html_template_canonical_tables_available($pdo)) {
            app_html_template_bootstrap_into_canonical($pdo);

            $statement = $pdo->prepare(
                'UPDATE html_template_parameters
                SET
                    legacy_html_template_pid = :legacy_html_template_pid,
                    parameter_name = :parameter_name,
                    target_value_type = :target_value_type,
                    target_variable_or_class_object = :target_variable_or_class_object,
                    target_property_of_class_object = :target_property_of_class_object,
                    another_template_pid = :another_template_pid,
                    trim_last_space = :trim_last_space,
                    trim_last_return = :trim_last_return,
                    data_type = :data_type,
                    notes = :notes,
                    source_of_truth = :source_of_truth,
                    updated_at = CURRENT_TIMESTAMP
                WHERE legacy_template_parameter_pid = :legacy_template_parameter_pid'
            );
            $statement->execute([
                ':legacy_html_template_pid' => (int) ($input['legacy_html_template_pid'] ?? 0),
                ':parameter_name' => (string) ($input['parameter_name'] ?? ''),
                ':target_value_type' => (string) ($input['target_value_type'] ?? ''),
                ':target_variable_or_class_object' => (string) ($input['target_variable_or_class_object'] ?? ''),
                ':target_property_of_class_object' => (string) ($input['target_property_of_class_object'] ?? ''),
                ':another_template_pid' => (int) ($input['another_template_pid'] ?? 0),
                ':trim_last_space' => (int) ($input['trim_last_space'] ?? 0),
                ':trim_last_return' => (int) ($input['trim_last_return'] ?? 0),
                ':data_type' => (string) ($input['data_type'] ?? ''),
                ':notes' => '',
                ':source_of_truth' => 'manual',
                ':legacy_template_parameter_pid' => (int) ($input['legacy_template_parameter_pid'] ?? 0),
            ]);

            return app_fetch_html_template_parameter_by_pid(
                $app,
                (int) ($input['legacy_html_template_pid'] ?? 0),
                (int) ($input['legacy_template_parameter_pid'] ?? 0),
            );
        }

        if (!app_html_template_pdo_table_exists($pdo, 'htmlTemplateParameter')) {
            return [
                'ok' => false,
                'item' => null,
                'error' => 'HTML template parameter canonical table も legacy htmlTemplateParameter table も存在しません。',
            ];
        }

        $statement = $pdo->prepare(
            'UPDATE htmlTemplateParameter
            SET
                htmlTemplatePID = :html_template_pid,
                ParameterName = :parameter_name,
                TargetValueType = :target_value_type,
                TargetVariableOrClassObject = :target_variable_or_class_object,
                TargetPropertyOfClassObject = :target_property_of_class_object,
                AnotherTemplatePID = :another_template_pid,
                TrimLastSpace = :trim_last_space,
                TrimLastReturn = :trim_last_return,
                DataType = :data_type
            WHERE PID = :pid'
        );
        $statement->execute([
            ':html_template_pid' => (int) ($input['legacy_html_template_pid'] ?? 0),
            ':parameter_name' => (string) ($input['parameter_name'] ?? ''),
            ':target_value_type' => (string) ($input['target_value_type'] ?? ''),
            ':target_variable_or_class_object' => (string) ($input['target_variable_or_class_object'] ?? ''),
            ':target_property_of_class_object' => (string) ($input['target_property_of_class_object'] ?? ''),
            ':another_template_pid' => (int) ($input['another_template_pid'] ?? 0),
            ':trim_last_space' => (int) ($input['trim_last_space'] ?? 0),
            ':trim_last_return' => (int) ($input['trim_last_return'] ?? 0),
            ':data_type' => (string) ($input['data_type'] ?? ''),
            ':pid' => (int) ($input['legacy_template_parameter_pid'] ?? 0),
        ]);

        return app_fetch_html_template_parameter_by_pid(
            $app,
            (int) ($input['legacy_html_template_pid'] ?? 0),
            (int) ($input['legacy_template_parameter_pid'] ?? 0),
        );
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_delete_html_template_parameter(
    array $app,
    int $legacyHtmlTemplatePid,
    int $legacyTemplateParameterPid,
): array {
    try {
        $pdo = app_create_metadata_pdo($app);

        if (app_html_template_canonical_tables_available($pdo)) {
            app_html_template_bootstrap_into_canonical($pdo);

            $statement = $pdo->prepare(
                'DELETE FROM html_template_parameters
                WHERE legacy_html_template_pid = :legacy_html_template_pid
                  AND legacy_template_parameter_pid = :legacy_template_parameter_pid'
            );
            $statement->execute([
                ':legacy_html_template_pid' => $legacyHtmlTemplatePid,
                ':legacy_template_parameter_pid' => $legacyTemplateParameterPid,
            ]);

            return [
                'ok' => true,
                'error' => '',
            ];
        }

        if (!app_html_template_pdo_table_exists($pdo, 'htmlTemplateParameter')) {
            return [
                'ok' => false,
                'error' => 'HTML template parameter canonical table も legacy htmlTemplateParameter table も存在しません。',
            ];
        }

        $statement = $pdo->prepare(
            'DELETE FROM htmlTemplateParameter
            WHERE htmlTemplatePID = :html_template_pid
              AND PID = :pid'
        );
        $statement->execute([
            ':html_template_pid' => $legacyHtmlTemplatePid,
            ':pid' => $legacyTemplateParameterPid,
        ]);

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param list<array{
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
 * }> $templateParameterCatalog
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
function app_html_template_collect_template_parameters(
    array $templateParameterCatalog,
    int $legacyTemplatePid,
    array $visitedTemplatePids = [],
): array {
    if ($legacyTemplatePid <= 0 || array_key_exists($legacyTemplatePid, $visitedTemplatePids)) {
        return [];
    }

    $visitedTemplatePids[$legacyTemplatePid] = true;
    $parameterMap = app_html_template_parameter_catalog_by_template_pid($templateParameterCatalog);
    $directItems = $parameterMap[(string) $legacyTemplatePid] ?? [];
    $flattened = [];

    foreach ($directItems as $item) {
        $flattened[] = $item;

        if (strcasecmp((string) ($item['target_value_type'] ?? ''), 'AnotherTemplate') !== 0) {
            continue;
        }

        $anotherTemplatePid = (int) ($item['another_template_pid'] ?? 0);
        if ($anotherTemplatePid <= 0) {
            continue;
        }

        foreach (
            app_html_template_collect_template_parameters(
                $templateParameterCatalog,
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
 * @param list<array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string
 * }> $templateCatalog
 * @param list<array{
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
 * }> $templateParameterCatalog
 * @param array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     html_key:string,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int,
 *     last_modified_dt:string
 * } $html
 * @param list<array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     legacy_parameter_pid:int,
 *     parameter_name:string,
 *     parameter_value:string
 * }> $actualItems
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
function app_html_template_parameter_audit_with_actual_items(
    array $templateCatalog,
    array $templateParameterCatalog,
    array $html,
    array $actualItems,
): array {
    $template = app_html_template_find_catalog_item_by_pid(
        $templateCatalog,
        (int) ($html['legacy_html_template_pid'] ?? 0),
    );

    $flattenedTemplateParameters = $template === null
        ? []
        : app_html_template_collect_template_parameters(
            $templateParameterCatalog,
            (int) ($template['legacy_html_template_pid'] ?? 0),
        );

    $actualItemsByName = [];
    foreach ($actualItems as $actualItem) {
        $parameterName = (string) ($actualItem['parameter_name'] ?? '');
        if ($parameterName === '') {
            continue;
        }

        if (!array_key_exists($parameterName, $actualItemsByName)) {
            $actualItemsByName[$parameterName] = [];
        }

        $actualItemsByName[$parameterName][] = $actualItem;
    }

    $expectedTemplateParametersByName = [];
    $templateDuplicateDataTypeNames = [];
    foreach ($flattenedTemplateParameters as $templateParameter) {
        if (strcasecmp((string) ($templateParameter['target_value_type'] ?? ''), 'EachHTML') !== 0) {
            continue;
        }

        $parameterName = (string) ($templateParameter['parameter_name'] ?? '');
        if ($parameterName === '') {
            continue;
        }

        if (array_key_exists($parameterName, $expectedTemplateParametersByName)) {
            if (
                strcasecmp(
                    (string) ($expectedTemplateParametersByName[$parameterName]['data_type'] ?? ''),
                    (string) ($templateParameter['data_type'] ?? ''),
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
            $matchedActualParameterPids[(int) ($matchingActualItem['legacy_parameter_pid'] ?? 0)] = true;
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
            'data_type' => (string) ($templateParameter['data_type'] ?? ''),
            'data_type_caption' => app_html_template_parameter_data_type_caption(
                (string) ($templateParameter['data_type'] ?? ''),
            ),
            'actual_item' => $actualItem,
            'duplicate_items' => $duplicateItems,
        ];
    }

    $unexpectedItems = [];
    foreach ($actualItems as $actualItem) {
        $legacyParameterPid = (int) ($actualItem['legacy_parameter_pid'] ?? 0);
        if ($legacyParameterPid > 0 && array_key_exists($legacyParameterPid, $matchedActualParameterPids)) {
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
