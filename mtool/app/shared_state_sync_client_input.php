<?php

declare(strict_types=1);

const APP_SHARED_STATE_SYNC_CLIENT_INPUT_SCHEMA_VERSION = 'shared_state_sync_client_input.v1';

/**
 * Build the Mtool-emitted input packet for an external app client owner.
 *
 * This emits contract metadata only. It does not generate SDKs, app source,
 * token storage choices, SSO provider setup, or realtime runtime code.
 *
 * @param array<string,mixed> $options
 * @return array<string,mixed>
 */
function app_shared_state_sync_client_input_build(array $options = []): array
{
    $projectKey = trim((string) ($options['project_key'] ?? 'PROJECT'));
    if ($projectKey === '') {
        $projectKey = 'PROJECT';
    }
    $apiBaseUrlEnv = trim((string) ($options['api_base_url_env'] ?? 'APP_BACKEND_BASE_URL'));
    if ($apiBaseUrlEnv === '') {
        $apiBaseUrlEnv = 'APP_BACKEND_BASE_URL';
    }

    return [
        'schema_version' => APP_SHARED_STATE_SYNC_CLIENT_INPUT_SCHEMA_VERSION,
        'generated_by' => [
            'tool' => 'mtool',
            'artifact' => 'shared_state_sync_client_input',
        ],
        'bundle_manifest_key' => 'shared_state_sync_client_input',
        'project' => [
            'project_key' => $projectKey,
        ],
        'contracts' => [
            'shared_state_sync_contract' => 'docs/shared-state-sync-contract.md',
            'schema_api_contract' => 'docs/shared-state-sync-schema-api-contract.md',
            'realtime_contract' => 'docs/shared-state-sync-realtime-contract.md',
            'node_server_input_contract' => 'docs/shared-state-sync-node-server-input-packet.md',
            'client_input_contract' => 'docs/shared-state-sync-app-client-input-packet.md',
        ],
        'client' => [
            'ownership' => 'external_app_client_owner',
            'source_generation' => false,
            'sdk_generation' => false,
        ],
        'backend' => [
            'api_base_url_env' => $apiBaseUrlEnv,
            'auth' => [
                'mode' => 'app_owned_sso_session',
                'token_storage_owner' => 'app_client_owner',
                'do_not_store_tokens_in_packet' => true,
            ],
            'authority' => [
                'session_verification' => 'app_backend',
                'membership' => 'app_backend',
                'state_persistence' => 'app_backend',
                'conflict_policy' => 'app_backend',
            ],
        ],
        'room_flow' => [
            'create_room' => ['method' => 'POST', 'path' => '/sync/rooms'],
            'list_rooms' => ['method' => 'GET', 'path' => '/sync/rooms'],
            'join_by_invite' => [
                'method' => 'POST',
                'path' => '/sync/room-joins',
                'raw_invite_token_storage' => 'do_not_persist_after_join',
            ],
            'membership_required_after_join' => true,
        ],
        'state_flow' => [
            'get_state' => ['method' => 'GET', 'path' => '/sync/rooms/{room_id}/states/{state_key}'],
            'update_state' => [
                'method' => 'PUT',
                'path' => '/sync/rooms/{room_id}/states/{state_key}',
                'expected_revision_required' => true,
            ],
            'latest_revision' => ['method' => 'GET', 'path' => '/sync/rooms/{room_id}/states/{state_key}/revision'],
            'conflict_error' => 'stale_revision',
        ],
        'realtime_flow' => [
            'primary_transport' => 'websocket',
            'websocket' => [
                'path' => '/sync/ws',
                'subscribe_command' => 'room.subscribe',
                'unsubscribe_command' => 'room.unsubscribe',
                'update_command' => 'state.update',
                'ping_command' => 'ping',
            ],
            'events' => ['state.updated', 'membership.changed', 'room.closed', 'heartbeat', 'reconnect.required'],
            'event_application_policy' => 'apply_next_revision_or_fetch_latest',
        ],
        'fallbacks' => [
            'sse_http' => [
                'enabled' => true,
                'event_stream' => '/sync/rooms/{room_id}/events',
                'update_transport' => 'http_put',
            ],
            'polling' => [
                'enabled' => true,
                'revision_path' => '/sync/rooms/{room_id}/states/{state_key}/revision',
                'state_path' => '/sync/rooms/{room_id}/states/{state_key}',
                'realtime_claim_allowed' => false,
            ],
        ],
        'reconnect' => [
            'strategy' => 'backoff_resubscribe_latest_fetch',
            'steps' => [
                'detect_disconnect_or_heartbeat_timeout',
                'reconnect_with_backoff',
                'resubscribe_rooms',
                'fetch_latest_revision',
                'fetch_latest_state_when_revision_changed_or_unknown',
            ],
            'event_replay_required' => false,
        ],
        'validation' => [
            'required_checks' => [
                'join_room_by_invite_discards_raw_token_after_join',
                'subscribe_requires_authenticated_session',
                'state_update_sends_expected_revision',
                'stale_revision_fetches_latest_state',
                'heartbeat_timeout_reconnects',
                'reconnect_resubscribes_and_fetches_latest',
                'membership_loss_unsubscribes_room',
                'polling_does_not_claim_realtime',
                'packet_contains_no_tokens_or_secrets',
            ],
        ],
        'forbidden_actions' => [
            'generate_client_sdk',
            'generate_react_source',
            'generate_flutter_source',
            'generate_react_native_source',
            'install_dependencies',
            'choose_token_storage',
            'persist_raw_invite_token',
            'enable_offline_sync',
            'claim_realtime_when_polling',
            'claim_crdt_or_game_loop_support',
        ],
        'mutation_performed' => false,
    ];
}

/**
 * @param array<string,mixed> $packet
 * @return list<string>
 */
function app_shared_state_sync_client_input_contract_errors(array $packet): array
{
    $errors = [];
    if (($packet['schema_version'] ?? '') !== APP_SHARED_STATE_SYNC_CLIENT_INPUT_SCHEMA_VERSION) {
        $errors[] = 'schema_version';
    }
    if (($packet['bundle_manifest_key'] ?? '') !== 'shared_state_sync_client_input') {
        $errors[] = 'bundle_manifest_key';
    }
    foreach (['shared_state_sync_contract', 'schema_api_contract', 'realtime_contract', 'node_server_input_contract', 'client_input_contract'] as $contract) {
        if (!isset($packet['contracts'][$contract]) || !str_starts_with((string) $packet['contracts'][$contract], 'docs/')) {
            $errors[] = 'contract_' . $contract;
        }
    }
    if (($packet['client']['ownership'] ?? '') !== 'external_app_client_owner') {
        $errors[] = 'client_ownership';
    }
    if (($packet['client']['source_generation'] ?? true) !== false) {
        $errors[] = 'source_generation';
    }
    if (($packet['client']['sdk_generation'] ?? true) !== false) {
        $errors[] = 'sdk_generation';
    }
    if (($packet['backend']['auth']['do_not_store_tokens_in_packet'] ?? false) !== true) {
        $errors[] = 'token_in_packet_boundary';
    }
    if (($packet['room_flow']['join_by_invite']['raw_invite_token_storage'] ?? '') !== 'do_not_persist_after_join') {
        $errors[] = 'raw_invite_token_storage';
    }
    if (($packet['state_flow']['update_state']['expected_revision_required'] ?? false) !== true) {
        $errors[] = 'expected_revision_required';
    }
    if (($packet['realtime_flow']['primary_transport'] ?? '') !== 'websocket') {
        $errors[] = 'primary_transport';
    }
    if (($packet['fallbacks']['polling']['realtime_claim_allowed'] ?? true) !== false) {
        $errors[] = 'polling_realtime_claim';
    }
    if (($packet['reconnect']['event_replay_required'] ?? true) !== false) {
        $errors[] = 'event_replay_required';
    }
    foreach (['generate_client_sdk', 'generate_react_source', 'install_dependencies', 'choose_token_storage', 'enable_offline_sync', 'claim_realtime_when_polling'] as $action) {
        if (!in_array($action, is_array($packet['forbidden_actions'] ?? null) ? $packet['forbidden_actions'] : [], true)) {
            $errors[] = 'forbidden_' . $action;
        }
    }
    if (($packet['mutation_performed'] ?? true) !== false) {
        $errors[] = 'mutation_performed';
    }
    return $errors;
}

/** @param array<string,mixed> $packet */
function app_shared_state_sync_client_input_markdown(array $packet): string
{
    $lines = [
        '# Shared State Sync Client Input',
        '',
        '- Schema: `' . (string) ($packet['schema_version'] ?? '') . '`',
        '- Bundle manifest key: `' . (string) ($packet['bundle_manifest_key'] ?? '') . '`',
        '- Ownership: `' . (string) ($packet['client']['ownership'] ?? '') . '`',
        '',
        '## Boundary',
        '',
        '- This packet is an input for an external app client owner.',
        '- Mtool does not generate SDKs, React/Flutter/React Native source, token storage choices, SSO provider setup, or offline sync.',
        '- Backend remains authoritative for session, membership, state persistence, and conflict policy.',
        '',
        '## Primary routes',
        '',
        '- Join by invite: `' . (string) ($packet['room_flow']['join_by_invite']['method'] ?? '') . ' ' . (string) ($packet['room_flow']['join_by_invite']['path'] ?? '') . '`',
        '- Update state: `' . (string) ($packet['state_flow']['update_state']['method'] ?? '') . ' ' . (string) ($packet['state_flow']['update_state']['path'] ?? '') . '`',
        '- WebSocket: `' . (string) ($packet['realtime_flow']['websocket']['path'] ?? '') . '`',
        '',
        '## Required validation',
        '',
    ];
    foreach (($packet['validation']['required_checks'] ?? []) as $check) {
        $lines[] = '- `' . (string) $check . '`';
    }
    $lines[] = '';
    $lines[] = '## Forbidden implicit actions';
    $lines[] = '';
    foreach (($packet['forbidden_actions'] ?? []) as $action) {
        $lines[] = '- `' . (string) $action . '`';
    }
    $lines[] = '';
    return implode("\n", $lines);
}

/**
 * @param array<string,mixed> $options
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,contract_errors:list<string>}
 */
function app_shared_state_sync_client_input_emit(array $options, string $targetDir): array
{
    $normalizedTargetDir = rtrim($targetDir, DIRECTORY_SEPARATOR);
    if ($normalizedTargetDir === '' || $normalizedTargetDir === '.' || $normalizedTargetDir === DIRECTORY_SEPARATOR) {
        return app_shared_state_sync_client_input_emit_result(false, 'target directory is not a controlled artifact directory', $normalizedTargetDir, [], []);
    }

    $packet = app_shared_state_sync_client_input_build($options);
    $contractErrors = app_shared_state_sync_client_input_contract_errors($packet);
    if ($contractErrors !== []) {
        return app_shared_state_sync_client_input_emit_result(false, 'packet contract validation failed', $normalizedTargetDir, [], $contractErrors);
    }
    if (file_exists($normalizedTargetDir) && !is_dir($normalizedTargetDir)) {
        return app_shared_state_sync_client_input_emit_result(false, 'target path exists and is not a directory', $normalizedTargetDir, [], []);
    }
    if (!is_dir($normalizedTargetDir) && !mkdir($normalizedTargetDir, 0777, true)) {
        return app_shared_state_sync_client_input_emit_result(false, 'failed to create target directory', $normalizedTargetDir, [], []);
    }

    $files = [
        'sync-client-input.json' => app_shared_state_sync_client_input_json_text($packet),
        'SYNC-CLIENT-INPUT.md' => app_shared_state_sync_client_input_markdown($packet),
    ];
    $emitted = [];
    foreach ($files as $relativePath => $text) {
        $path = $normalizedTargetDir . DIRECTORY_SEPARATOR . $relativePath;
        if (file_exists($path)) {
            return app_shared_state_sync_client_input_emit_result(false, 'package file already exists: ' . $relativePath, $normalizedTargetDir, $emitted, []);
        }
        if (file_put_contents($path, $text) === false) {
            return app_shared_state_sync_client_input_emit_result(false, 'failed to write package file: ' . $relativePath, $normalizedTargetDir, $emitted, []);
        }
        $emitted[] = $relativePath;
    }
    sort($emitted, SORT_STRING);
    return app_shared_state_sync_client_input_emit_result(true, '', $normalizedTargetDir, $emitted, []);
}

/** @param array<string,mixed> $value */
function app_shared_state_sync_client_input_json_text(array $value): string
{
    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
}

/**
 * @param list<string> $files
 * @param list<string> $contractErrors
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,contract_errors:list<string>}
 */
function app_shared_state_sync_client_input_emit_result(bool $ok, string $error, string $targetDir, array $files, array $contractErrors): array
{
    return [
        'ok' => $ok,
        'error' => $error,
        'target_dir' => $targetDir,
        'files' => $files,
        'contract_errors' => $contractErrors,
    ];
}
