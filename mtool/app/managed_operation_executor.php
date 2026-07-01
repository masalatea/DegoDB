<?php

declare(strict_types=1);

require_once __DIR__ . '/managed_operation_policy.php';

/**
 * @param array<string,mixed> $principal
 * @param array<string,mixed> $operation
 * @param array<string,mixed> $contract
 * @param array<string,mixed> $input
 * @return array{
 *     ok:bool,
 *     allowed:bool,
 *     plan:array<string,mixed>|null,
 *     failed_checks:list<string>,
 *     error:string
 * }
 */
function app_managed_operation_execution_prepare(
    array $principal,
    array $operation,
    array $contract,
    array $input,
): array {
    $policy = app_managed_operation_policy_evaluate($principal, $operation, $contract);
    if (!$policy['ok'] || !$policy['allowed']) {
        return [
            'ok' => $policy['ok'],
            'allowed' => false,
            'plan' => null,
            'failed_checks' => $policy['failed_checks'],
            'error' => $policy['error'],
        ];
    }

    $fieldsByPhysicalName = app_managed_operation_execution_contract_fields_by_physical_name($contract);
    $operationFields = is_array($operation['fields'] ?? null) ? $operation['fields'] : [];
    $allowedInputNames = app_managed_operation_execution_allowed_input_names($operationFields, $fieldsByPhysicalName);
    $failed = app_managed_operation_execution_unknown_input_failures($input, $allowedInputNames);
    $sections = [
        'key' => [],
        'input' => [],
        'filter' => [],
        'output_fields' => [],
        'field_map' => [],
    ];

    foreach ($operationFields as $operationField) {
        if (!is_array($operationField)) {
            continue;
        }

        $physicalName = (string) ($operationField['field_physical_name'] ?? '');
        $contractField = $fieldsByPhysicalName[$physicalName] ?? null;
        if (!is_array($contractField)) {
            $failed[] = 'field.missing:' . $physicalName;
            continue;
        }

        $generatedName = (string) ($contractField['generated_name'] ?? $physicalName);
        $fieldRole = (string) ($operationField['field_role'] ?? '');
        $valueResult = app_managed_operation_execution_input_value($input, $contractField);
        $requiresInput = app_managed_operation_execution_field_requires_input($operation, $operationField);

        if (in_array($fieldRole, ['key', 'input', 'filter'], true)) {
            if (!$valueResult['present'] && $requiresInput) {
                $failed[] = 'input.missing:' . $generatedName;
            } elseif ($valueResult['present']) {
                $normalized = app_managed_operation_execution_normalize_value($contractField, $valueResult['value']);
                if (!$normalized['ok']) {
                    $failed[] = 'input.invalid:' . $generatedName;
                } elseif ($fieldRole === 'key') {
                    $sections['key'][$generatedName] = $normalized['value'];
                } elseif ($fieldRole === 'filter') {
                    $sections['filter'][$generatedName] = $normalized['value'];
                } else {
                    $sections['input'][$generatedName] = $normalized['value'];
                }
            }
        }

        if ($fieldRole === 'output') {
            $sections['output_fields'][] = $generatedName;
        }

        $sections['field_map'][] = [
            'physical_name' => $physicalName,
            'generated_name' => $generatedName,
            'field_role' => $fieldRole,
            'type' => (string) ($contractField['type'] ?? 'string'),
            'nullable' => (bool) ($contractField['nullable'] ?? false),
            'is_key' => (bool) ($contractField['is_key'] ?? false),
        ];
    }

    if ($failed !== []) {
        return [
            'ok' => true,
            'allowed' => false,
            'plan' => null,
            'failed_checks' => array_values(array_unique($failed)),
            'error' => '',
        ];
    }

    return [
        'ok' => true,
        'allowed' => true,
        'plan' => [
            'execution_mode' => 'plan-only',
            'project_key' => (string) ($operation['project_key'] ?? ''),
            'operation_key' => (string) ($operation['operation_key'] ?? ''),
            'operation_type' => (string) ($operation['operation_type'] ?? ''),
            'contract_key' => (string) ($operation['contract_key'] ?? ''),
            'storage_policy' => (string) ($operation['storage_policy'] ?? ''),
            'key' => $sections['key'],
            'input' => $sections['input'],
            'filter' => $sections['filter'],
            'output_fields' => $sections['output_fields'],
            'field_map' => $sections['field_map'],
        ],
        'failed_checks' => [],
        'error' => '',
    ];
}

/**
 * @param array<string,mixed> $contract
 * @return array<string,array<string,mixed>>
 */
function app_managed_operation_execution_contract_fields_by_physical_name(array $contract): array
{
    $fieldsByPhysicalName = [];
    foreach (($contract['fields'] ?? []) as $field) {
        if (!is_array($field)) {
            continue;
        }

        $physicalName = (string) ($field['physical_name'] ?? '');
        if ($physicalName !== '') {
            $fieldsByPhysicalName[$physicalName] = $field;
        }
    }

    return $fieldsByPhysicalName;
}

/**
 * @param list<array<string,mixed>> $operationFields
 * @param array<string,array<string,mixed>> $fieldsByPhysicalName
 * @return array<string,bool>
 */
function app_managed_operation_execution_allowed_input_names(array $operationFields, array $fieldsByPhysicalName): array
{
    $allowed = [];
    foreach ($operationFields as $operationField) {
        $fieldRole = (string) ($operationField['field_role'] ?? '');
        if (!in_array($fieldRole, ['key', 'input', 'filter'], true)) {
            continue;
        }

        $physicalName = (string) ($operationField['field_physical_name'] ?? '');
        $contractField = $fieldsByPhysicalName[$physicalName] ?? null;
        if (!is_array($contractField)) {
            continue;
        }

        $generatedName = (string) ($contractField['generated_name'] ?? '');
        if ($physicalName !== '') {
            $allowed[$physicalName] = true;
        }
        if ($generatedName !== '') {
            $allowed[$generatedName] = true;
        }
    }

    return $allowed;
}

/**
 * @param array<string,mixed> $input
 * @param array<string,bool> $allowedInputNames
 * @return list<string>
 */
function app_managed_operation_execution_unknown_input_failures(array $input, array $allowedInputNames): array
{
    $failed = [];
    foreach ($input as $key => $_value) {
        if (!is_string($key) || $key === '' || !isset($allowedInputNames[$key])) {
            $failed[] = 'input.unknown:' . (is_string($key) ? $key : 'non-string');
        }
    }

    return $failed;
}

/**
 * @param array<string,mixed> $input
 * @param array<string,mixed> $contractField
 * @return array{present:bool,value:mixed}
 */
function app_managed_operation_execution_input_value(array $input, array $contractField): array
{
    $generatedName = (string) ($contractField['generated_name'] ?? '');
    if ($generatedName !== '' && array_key_exists($generatedName, $input)) {
        return [
            'present' => true,
            'value' => $input[$generatedName],
        ];
    }

    $physicalName = (string) ($contractField['physical_name'] ?? '');
    if ($physicalName !== '' && array_key_exists($physicalName, $input)) {
        return [
            'present' => true,
            'value' => $input[$physicalName],
        ];
    }

    return [
        'present' => false,
        'value' => null,
    ];
}

/**
 * @param array<string,mixed> $operation
 * @param array<string,mixed> $operationField
 */
function app_managed_operation_execution_field_requires_input(array $operation, array $operationField): bool
{
    $fieldRole = (string) ($operationField['field_role'] ?? '');
    if ($fieldRole === 'key') {
        return in_array((string) ($operation['operation_type'] ?? ''), ['read', 'update', 'delete'], true);
    }

    return (bool) ($operationField['is_required'] ?? false);
}

/**
 * @param array<string,mixed> $field
 * @return array{ok:bool,value:mixed}
 */
function app_managed_operation_execution_normalize_value(array $field, mixed $value): array
{
    if ($value === null) {
        return [
            'ok' => (bool) ($field['nullable'] ?? false),
            'value' => null,
        ];
    }

    return match ((string) ($field['type'] ?? 'string')) {
        'integer' => is_numeric($value)
            ? ['ok' => true, 'value' => (int) $value]
            : ['ok' => false, 'value' => null],
        'boolean' => app_managed_operation_execution_normalize_boolean($value),
        'datetime', 'string', 'text' => is_scalar($value)
            ? ['ok' => true, 'value' => (string) $value]
            : ['ok' => false, 'value' => null],
        default => ['ok' => true, 'value' => $value],
    };
}

/**
 * @return array{ok:bool,value:bool|null}
 */
function app_managed_operation_execution_normalize_boolean(mixed $value): array
{
    if (is_bool($value)) {
        return [
            'ok' => true,
            'value' => $value,
        ];
    }

    if (is_int($value) || is_float($value)) {
        return [
            'ok' => true,
            'value' => ((int) $value) !== 0,
        ];
    }

    if (!is_string($value)) {
        return [
            'ok' => false,
            'value' => null,
        ];
    }

    $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    return [
        'ok' => is_bool($normalized),
        'value' => is_bool($normalized) ? $normalized : null,
    ];
}
