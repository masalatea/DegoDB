<?php

declare(strict_types=1);

/**
 * @return list<string>
 */
function app_generated_name_words_from_physical_name(string $physicalName): array
{
    $trimmed = trim($physicalName);
    if ($trimmed === '') {
        return [];
    }

    $delimited = preg_split('/[^A-Za-z0-9]+/', $trimmed, -1, PREG_SPLIT_NO_EMPTY);
    if (is_array($delimited) && preg_match('/[^A-Za-z0-9]/', $trimmed) === 1) {
        return array_values($delimited);
    }

    return [$trimmed];
}

function app_generated_name_pascal_case(string $name): string
{
    $words = app_generated_name_words_from_physical_name($name);
    if ($words === []) {
        return '';
    }

    $segments = [];
    foreach ($words as $word) {
        $segment = trim($word);
        if ($segment === '') {
            continue;
        }

        $segments[] = strtoupper(substr($segment, 0, 1)) . substr($segment, 1);
    }

    return implode('', $segments);
}

function app_generated_name_camel_case(string $name): string
{
    $pascalCase = app_generated_name_pascal_case($name);
    if ($pascalCase === '') {
        return '';
    }

    return strtolower(substr($pascalCase, 0, 1)) . substr($pascalCase, 1);
}

function app_physical_name_to_logical_name(string $physicalName): string
{
    return app_generated_name_pascal_case($physicalName);
}

function app_logical_name_to_generated_name(string $logicalName, string $surface = 'class'): string
{
    $normalizedSurface = strtolower(trim($surface));
    return match ($normalizedSurface) {
        'property', 'variable', 'parameter', 'php-property', 'php-variable', 'php-parameter' => app_generated_name_camel_case($logicalName),
        default => app_generated_name_pascal_case($logicalName),
    };
}

/**
 * @return array{
 *     physical_name:string,
 *     logical_name:string,
 *     generated_name:string
 * }
 */
function app_generated_name_map_for_physical_name(string $physicalName, string $surface = 'class'): array
{
    $trimmedPhysicalName = trim($physicalName);
    $logicalName = app_physical_name_to_logical_name($trimmedPhysicalName);

    return [
        'physical_name' => $trimmedPhysicalName,
        'logical_name' => $logicalName,
        'generated_name' => app_logical_name_to_generated_name($logicalName, $surface),
    ];
}

function app_generated_name_policy_mode(): string
{
    return trim((string) getenv('MTOOL_GENERATED_NAME_POLICY'));
}

function app_generated_name_policy_uses_physical_logical_names(): bool
{
    return app_generated_name_policy_mode() === 'physical-logical-v1';
}

function app_physical_name_is_safe_unquoted_sql_identifier(string $physicalName): bool
{
    $trimmed = trim($physicalName);
    if (preg_match('/^[a-z][a-z0-9_]*$/', $trimmed) !== 1) {
        return false;
    }

    return !in_array($trimmed, app_physical_name_unquoted_sql_reserved_words(), true);
}

/**
 * @return list<string>
 */
function app_physical_name_unquoted_sql_reserved_words(): array
{
    return [
        'all',
        'and',
        'as',
        'by',
        'column',
        'create',
        'delete',
        'from',
        'group',
        'insert',
        'into',
        'join',
        'limit',
        'not',
        'null',
        'or',
        'order',
        'select',
        'table',
        'update',
        'user',
        'where',
    ];
}
