<?php

declare(strict_types=1);

require_once __DIR__ . '/app_local_sqlite_dbaccess.php';

/**
 * @param array<string,mixed> $manifest
 * @param array<string,mixed> $options
 * @return callable(array<string,mixed>):array<string,mixed>
 */
function app_managed_operation_app_local_outbox_handler(PDO $pdo, array $manifest, array $options = []): callable
{
    return static function (array $outboxItem) use ($pdo, $manifest, $options): array {
        $intent = is_array($outboxItem['intent'] ?? null) ? $outboxItem['intent'] : [];
        if ($intent === []) {
            return [
                'ok' => false,
                'executed' => false,
                'operation_type' => (string) ($outboxItem['operation_type'] ?? ''),
                'result' => null,
                'error' => 'managed operation App-local outbox handler requires intent payload.',
            ];
        }

        return app_managed_operation_app_local_execute_intent($pdo, $manifest, $intent, $options);
    };
}

/**
 * @param array<string,mixed> $manifest
 * @param array<string,mixed> $intent
 * @param array<string,mixed> $options
 * @return array{
 *     ok:bool,
 *     executed:bool,
 *     operation_type:string,
 *     result:array<string,mixed>|null,
 *     error:string
 * }
 */
function app_managed_operation_app_local_execute_intent(
    PDO $pdo,
    array $manifest,
    array $intent,
    array $options = [],
): array {
    try {
        if ((string) ($intent['intent_version'] ?? '') !== 'managed-operation-sync-intent-v0') {
            throw new RuntimeException('managed operation App-local executor requires sync intent v0.');
        }

        $endpoint = (string) ($options['endpoint'] ?? 'app-local');
        if (!in_array($endpoint, [(string) ($intent['origin'] ?? ''), (string) ($intent['target'] ?? '')], true)) {
            throw new RuntimeException('managed operation App-local executor endpoint is not part of the intent.');
        }

        $contractKey = (string) ($intent['contract_key'] ?? '');
        $operationType = (string) ($intent['operation_type'] ?? '');
        $payload = is_array($intent['payload'] ?? null) ? $intent['payload'] : [];
        $key = is_array($payload['key'] ?? null) ? $payload['key'] : [];
        $input = is_array($payload['input'] ?? null) ? $payload['input'] : [];

        if ($contractKey === '') {
            throw new RuntimeException('managed operation App-local executor requires contract_key.');
        }

        if ($operationType === 'read') {
            $read = app_local_sqlite_dbaccess_read_dto($pdo, $manifest, $contractKey, $key);

            return app_managed_operation_app_local_executor_result($read['ok'], true, $operationType, $read, $read['error']);
        }

        if (in_array($operationType, ['create', 'update'], true)) {
            $dto = app_managed_operation_app_local_executor_save_dto(
                $pdo,
                $manifest,
                $contractKey,
                $operationType,
                $key,
                $input,
            );
            if (!$dto['ok']) {
                return app_managed_operation_app_local_executor_result(false, false, $operationType, null, $dto['error']);
            }

            $save = app_local_sqlite_dbaccess_save_dto($pdo, $manifest, $contractKey, $dto['dto'], [
                'dirty' => true,
                'sync_status' => 'dirty',
            ]);

            return app_managed_operation_app_local_executor_result($save['ok'], true, $operationType, $save, $save['error']);
        }

        throw new RuntimeException('managed operation App-local executor does not support operation type: ' . $operationType);
    } catch (Throwable $throwable) {
        return app_managed_operation_app_local_executor_result(false, false, (string) ($intent['operation_type'] ?? ''), null, $throwable->getMessage());
    }
}

/**
 * @param array<string,mixed> $key
 * @param array<string,mixed> $input
 * @return array{ok:bool,dto:array<string,mixed>,error:string}
 */
function app_managed_operation_app_local_executor_save_dto(
    PDO $pdo,
    array $manifest,
    string $contractKey,
    string $operationType,
    array $key,
    array $input,
): array {
    $dto = $key + $input;
    if ($operationType === 'update') {
        $read = app_local_sqlite_dbaccess_read_dto($pdo, $manifest, $contractKey, $key);
        if (!$read['ok']) {
            return [
                'ok' => false,
                'dto' => [],
                'error' => $read['error'],
            ];
        }
        if ($read['dto'] === null) {
            return [
                'ok' => false,
                'dto' => [],
                'error' => 'managed operation App-local update target was not found.',
            ];
        }
        $dto = $read['dto'];
        foreach ($input as $name => $value) {
            if (is_string($name)) {
                $dto[$name] = $value;
            }
        }
    }

    return [
        'ok' => true,
        'dto' => $dto,
        'error' => '',
    ];
}

/**
 * @param array<string,mixed>|null $result
 * @return array{
 *     ok:bool,
 *     executed:bool,
 *     operation_type:string,
 *     result:array<string,mixed>|null,
 *     error:string
 * }
 */
function app_managed_operation_app_local_executor_result(
    bool $ok,
    bool $executed,
    string $operationType,
    ?array $result,
    string $error,
): array {
    return [
        'ok' => $ok,
        'executed' => $executed,
        'operation_type' => $operationType,
        'result' => $result,
        'error' => $error,
    ];
}
