<?php

declare(strict_types=1);

require_once __DIR__ . '/project_db_access_bootstrap_service.php';

/**
 * @param array<string,mixed> $operation
 * @param array<string,mixed> $options
 * @return array{ok:bool,binding:array<string,mixed>|null,error:string}
 */
function app_managed_operation_server_dbaccess_binding_from_project_catalog(
    array $app,
    string $projectKey,
    array $operation,
    array $options = [],
): array {
    $catalog = app_project_db_access_bootstrap_candidate_catalog($app, $projectKey);
    if (!$catalog['ok']) {
        return [
            'ok' => false,
            'binding' => null,
            'error' => $catalog['error'],
        ];
    }

    return app_managed_operation_server_dbaccess_binding_from_candidates($catalog['items'], $operation, $options);
}

/**
 * @param list<array<string,mixed>> $candidates
 * @param array<string,mixed> $operation
 * @param array<string,mixed> $options
 * @return array{ok:bool,binding:array<string,mixed>|null,error:string}
 */
function app_managed_operation_server_dbaccess_binding_from_candidates(
    array $candidates,
    array $operation,
    array $options = [],
): array {
    $lastError = '';
    foreach ($candidates as $candidate) {
        if (!app_managed_operation_server_dbaccess_candidate_matches_operation($candidate, $operation, $options)) {
            continue;
        }

        $binding = app_managed_operation_server_dbaccess_binding_from_candidate($candidate, $operation, $options);
        if ($binding['ok']) {
            return $binding;
        }

        $lastError = $binding['error'];
    }

    return [
        'ok' => false,
        'binding' => null,
        'error' => $lastError !== ''
            ? $lastError
            : 'managed operation server DBAccess candidate was not found.',
    ];
}

/**
 * @param array<string,mixed> $candidate
 * @param array<string,mixed> $operation
 * @param array<string,mixed> $options
 * @return array{ok:bool,binding:array<string,mixed>|null,error:string}
 */
function app_managed_operation_server_dbaccess_binding_from_candidate(
    array $candidate,
    array $operation,
    array $options = [],
): array {
    try {
        $operationType = (string) ($operation['operation_type'] ?? '');
        $sourceName = (string) ($options['source_name'] ?? $candidate['generated_name'] ?? $candidate['source_name'] ?? '');
        $methodName = app_managed_operation_server_dbaccess_method_name_from_catalog(
            is_array($candidate['method_catalog'] ?? null) ? $candidate['method_catalog'] : [],
            $operationType,
            $sourceName,
        );
        if ($methodName === '') {
            throw new RuntimeException('managed operation server DBAccess binding method was not found: ' . $operationType);
        }

        return [
            'ok' => true,
            'binding' => [
                'endpoint' => (string) ($options['endpoint'] ?? 'server'),
                'source_name' => $sourceName,
                'data_class' => (string) ($candidate['data_class'] ?? ''),
                'dbaccess_class' => (string) ($candidate['dbaccess_class'] ?? ''),
                'method_map' => [
                    $operationType => $methodName,
                ],
            ],
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'binding' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array<string,mixed> $binding
 * @return callable(array<string,mixed>):array<string,mixed>
 */
function app_managed_operation_server_dbaccess_outbox_handler(array $binding): callable
{
    return static function (array $outboxItem) use ($binding): array {
        $intent = is_array($outboxItem['intent'] ?? null) ? $outboxItem['intent'] : [];
        if ($intent === []) {
            return app_managed_operation_server_dbaccess_executor_result(
                false,
                false,
                (string) ($outboxItem['operation_type'] ?? ''),
                '',
                null,
                'managed operation server DBAccess outbox handler requires intent payload.',
            );
        }

        return app_managed_operation_server_dbaccess_execute_intent($intent, $binding);
    };
}

/**
 * @param array<string,mixed> $intent
 * @param array<string,mixed> $binding
 * @return array{
 *     ok:bool,
 *     executed:bool,
 *     operation_type:string,
 *     method_name:string,
 *     result:mixed,
 *     error:string
 * }
 */
function app_managed_operation_server_dbaccess_execute_intent(array $intent, array $binding): array
{
    try {
        if ((string) ($intent['intent_version'] ?? '') !== 'managed-operation-sync-intent-v0') {
            throw new RuntimeException('managed operation server DBAccess executor requires sync intent v0.');
        }

        $endpoint = (string) ($binding['endpoint'] ?? 'server');
        if (!in_array($endpoint, [(string) ($intent['origin'] ?? ''), (string) ($intent['target'] ?? '')], true)) {
            throw new RuntimeException('managed operation server DBAccess executor endpoint is not part of the intent.');
        }

        $operationType = (string) ($intent['operation_type'] ?? '');
        $methodMap = is_array($binding['method_map'] ?? null) ? $binding['method_map'] : [];
        $methodName = (string) ($methodMap[$operationType] ?? '');
        if ($methodName === '') {
            throw new RuntimeException('managed operation server DBAccess method is not configured: ' . $operationType);
        }

        $dbAccessClass = (string) ($binding['dbaccess_class'] ?? '');
        if ($dbAccessClass === '' || !class_exists($dbAccessClass)) {
            throw new RuntimeException('managed operation server DBAccess class was not found: ' . $dbAccessClass);
        }

        $dbAccess = new $dbAccessClass();
        if (!method_exists($dbAccess, $methodName)) {
            throw new RuntimeException('managed operation server DBAccess method was not found: ' . $methodName);
        }

        $payload = is_array($intent['payload'] ?? null) ? $intent['payload'] : [];
        $key = is_array($payload['key'] ?? null) ? $payload['key'] : [];
        $input = is_array($payload['input'] ?? null) ? $payload['input'] : [];
        $arguments = app_managed_operation_server_dbaccess_arguments($operationType, $binding, $key, $input);
        $result = $dbAccess->$methodName(...$arguments);
        if ($result === false) {
            throw new RuntimeException('managed operation server DBAccess method failed: ' . $methodName);
        }

        return app_managed_operation_server_dbaccess_executor_result(true, true, $operationType, $methodName, $result, '');
    } catch (Throwable $throwable) {
        return app_managed_operation_server_dbaccess_executor_result(
            false,
            false,
            (string) ($intent['operation_type'] ?? ''),
            '',
            null,
            $throwable->getMessage(),
        );
    }
}

/**
 * @param array<string,mixed> $binding
 * @param array<string,mixed> $key
 * @param array<string,mixed> $input
 * @return list<mixed>
 */
function app_managed_operation_server_dbaccess_arguments(
    string $operationType,
    array $binding,
    array $key,
    array $input,
): array {
    if (in_array($operationType, ['create', 'update', 'delete'], true)) {
        return [
            app_managed_operation_server_dbaccess_hydrate_data_object(
                (string) ($binding['data_class'] ?? ''),
                $key + $input,
            ),
        ];
    }

    if ($operationType === 'read') {
        return array_values($key);
    }

    throw new RuntimeException('managed operation server DBAccess executor does not support operation type: ' . $operationType);
}

/**
 * @param list<array<string,mixed>> $methodCatalog
 */
function app_managed_operation_server_dbaccess_method_name_from_catalog(
    array $methodCatalog,
    string $operationType,
    string $sourceName,
): string {
    $expected = app_managed_operation_server_dbaccess_expected_method_name($operationType, $sourceName);
    if ($expected === '') {
        return '';
    }

    foreach ($methodCatalog as $method) {
        $name = (string) ($method['name'] ?? '');
        if ($name === $expected) {
            return $name;
        }
    }

    return '';
}

function app_managed_operation_server_dbaccess_expected_method_name(string $operationType, string $sourceName): string
{
    if ($sourceName === '') {
        return '';
    }

    return match ($operationType) {
        'create' => 'Insert' . $sourceName,
        'read' => 'Get' . $sourceName,
        'update' => 'Update' . $sourceName,
        'delete' => 'Delete' . $sourceName,
        default => '',
    };
}

/**
 * @param array<string,mixed> $candidate
 * @param array<string,mixed> $operation
 * @param array<string,mixed> $options
 */
function app_managed_operation_server_dbaccess_candidate_matches_operation(
    array $candidate,
    array $operation,
    array $options,
): bool {
    $expectedSourceName = (string) ($options['source_name'] ?? '');
    if ($expectedSourceName !== '') {
        return (string) ($candidate['source_name'] ?? '') === $expectedSourceName;
    }

    $contractKey = app_managed_operation_server_dbaccess_match_key((string) ($operation['contract_key'] ?? ''));
    if ($contractKey === '') {
        return true;
    }

    $candidateKeys = [
        $candidate['contract_key'] ?? '',
        $candidate['source_name'] ?? '',
        $candidate['generated_name'] ?? '',
    ];
    foreach ($candidateKeys as $candidateKey) {
        if ($contractKey === app_managed_operation_server_dbaccess_match_key((string) $candidateKey)) {
            return true;
        }
    }

    return false;
}

function app_managed_operation_server_dbaccess_match_key(string $value): string
{
    return strtolower(str_replace(['_', '-'], '', $value));
}

/**
 * @param array<string,mixed> $payload
 */
function app_managed_operation_server_dbaccess_hydrate_data_object(string $className, array $payload): object
{
    if ($className === '' || !class_exists($className)) {
        throw new RuntimeException('managed operation server DBAccess data class was not found: ' . $className);
    }

    $object = new $className();
    foreach ($payload as $name => $value) {
        if (is_string($name) && property_exists($object, $name)) {
            $object->$name = $value;
        }
    }

    return $object;
}

/**
 * @return array{
 *     ok:bool,
 *     executed:bool,
 *     operation_type:string,
 *     method_name:string,
 *     result:mixed,
 *     error:string
 * }
 */
function app_managed_operation_server_dbaccess_executor_result(
    bool $ok,
    bool $executed,
    string $operationType,
    string $methodName,
    mixed $result,
    string $error,
): array {
    return [
        'ok' => $ok,
        'executed' => $executed,
        'operation_type' => $operationType,
        'method_name' => $methodName,
        'result' => $result,
        'error' => $error,
    ];
}
