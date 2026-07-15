<?php

declare(strict_types=1);

const APP_SHARED_STATE_SYNC_SERVER_INPUT_SCHEMA_VERSION = 'shared_state_sync_server_input.v1';

/**
 * Build the Mtool-emitted input packet for an external Node.js shared-state
 * sync server.
 *
 * This emits contract metadata only. It does not install dependencies,
 * initialize a Node.js project, start a server, open ports, or implement SSO.
 *
 * @param array<string,mixed> $options
 * @return array<string,mixed>
 */
function app_shared_state_sync_server_input_build(array $options = []): array
{
    $projectKey = trim((string) ($options['project_key'] ?? 'PROJECT'));
    if ($projectKey === '') {
        $projectKey = 'PROJECT';
    }
    $backendBaseUrlEnv = trim((string) ($options['backend_base_url_env'] ?? 'APP_BACKEND_BASE_URL'));
    if ($backendBaseUrlEnv === '') {
        $backendBaseUrlEnv = 'APP_BACKEND_BASE_URL';
    }

    return [
        'schema_version' => APP_SHARED_STATE_SYNC_SERVER_INPUT_SCHEMA_VERSION,
        'generated_by' => [
            'tool' => 'mtool',
            'artifact' => 'shared_state_sync_server_input',
        ],
        'bundle_manifest_key' => 'shared_state_sync_server_input',
        'project' => [
            'project_key' => $projectKey,
        ],
        'contracts' => [
            'shared_state_sync_contract' => 'docs/shared-state-sync-contract.md',
            'schema_api_contract' => 'docs/shared-state-sync-schema-api-contract.md',
            'realtime_contract' => 'docs/shared-state-sync-realtime-contract.md',
            'node_server_input_contract' => 'docs/shared-state-sync-node-server-input-packet.md',
        ],
        'server' => [
            'runtime' => 'nodejs',
            'ownership' => 'external_runtime_owner',
            'transport_profiles' => ['websocket', 'sse_http', 'polling'],
            'production_runtime_generated' => false,
        ],
        'backend_integration' => [
            'authority' => 'app_backend',
            'base_url_env' => $backendBaseUrlEnv,
            'auth_context' => [
                'app_user_id_source' => 'verified_backend_session',
                'sso_token_broadcast_allowed' => false,
            ],
            'required_backend_operations' => [
                'verify_session',
                'check_room_membership',
                'read_state',
                'update_state',
                'read_latest_revision',
                'record_event',
            ],
            'conflict_policy' => 'reject_stale_revision',
        ],
        'routes' => app_shared_state_sync_server_input_routes(),
        'auth' => [
            'required' => true,
            'session_verification' => 'delegate_to_app_backend',
            'connection_identity' => 'app_user_id',
            'room_authorization' => 'active_membership_required',
            'forbidden_in_events' => ['sso_token', 'refresh_token', 'raw_invite_token', 'secret'],
        ],
        'rooms' => [
            'subscription_command' => 'room.subscribe',
            'unsubscribe_command' => 'room.unsubscribe',
            'membership_required' => true,
            'subscribe_result_includes_latest_revision_summary' => true,
            'cross_room_broadcast_allowed' => false,
        ],
        'state' => [
            'update_command' => 'state.update',
            'http_update_method' => 'PUT',
            'state_body_type' => 'json',
            'expected_revision_required' => true,
            'accepted_update_event' => 'state.updated',
            'large_binary_payload_allowed' => false,
        ],
        'events' => [
            'envelope' => 'shared_state_sync_realtime_event.v1',
            'fanout_scope' => 'room',
            'delivery_guarantee' => 'best_effort_realtime_plus_latest_fetch',
            'replay_required' => false,
            'dedupe_fields' => ['message_id', 'room_id', 'state_key', 'revision'],
            'heartbeat' => [
                'enabled' => true,
                'event_type' => 'heartbeat',
                'timeout_action' => 'reconnect_and_latest_fetch',
            ],
            'reconnect' => [
                'event_type' => 'reconnect.required',
                'client_action' => 'reconnect_resubscribe_latest_fetch',
            ],
        ],
        'fallbacks' => [
            'sse_http' => [
                'enabled' => true,
                'event_stream' => '/sync/rooms/{room_id}/events',
                'update_path' => '/sync/rooms/{room_id}/states/{state_key}',
                'update_transport' => 'http_put',
            ],
            'polling' => [
                'enabled' => true,
                'revision_path' => '/sync/rooms/{room_id}/states/{state_key}/revision',
                'state_path' => '/sync/rooms/{room_id}/states/{state_key}',
                'realtime_claim_allowed' => false,
            ],
        ],
        'validation' => [
            'required_checks' => [
                'authenticated_member_can_subscribe',
                'non_member_cannot_subscribe',
                'viewer_cannot_update',
                'editor_can_update',
                'accepted_update_emits_state_updated',
                'other_room_does_not_receive_event',
                'stale_revision_returns_conflict_when_enabled',
                'heartbeat_timeout_triggers_reconnect',
                'reconnect_fetches_latest_state',
                'events_do_not_contain_tokens_or_secrets',
            ],
            'implementation_required_before_production' => true,
        ],
        'forbidden_actions' => [
            'install_node_dependencies',
            'initialize_node_project',
            'start_production_server',
            'open_public_port',
            'store_raw_sso_token',
            'broadcast_sso_token',
            'enable_cross_room_broadcast',
            'claim_guaranteed_event_replay',
            'claim_crdt_or_game_loop_support',
        ],
        'mutation_performed' => false,
    ];
}

/** @return array<string,mixed> */
function app_shared_state_sync_server_input_routes(): array
{
    return [
        'websocket' => [
            'path' => '/sync/ws',
            'enabled' => true,
            'commands' => ['room.subscribe', 'room.unsubscribe', 'state.update', 'ping'],
            'events' => ['state.updated', 'membership.changed', 'room.closed', 'heartbeat', 'reconnect.required'],
        ],
        'sse' => [
            'path' => '/sync/rooms/{room_id}/events',
            'enabled' => true,
            'server_to_client_only' => true,
        ],
        'http_update' => [
            'path' => '/sync/rooms/{room_id}/states/{state_key}',
            'method' => 'PUT',
            'authority' => 'app_backend_or_shared_authority',
        ],
        'polling' => [
            'state_path' => '/sync/rooms/{room_id}/states/{state_key}',
            'revision_path' => '/sync/rooms/{room_id}/states/{state_key}/revision',
        ],
    ];
}

/**
 * @param array<string,mixed> $packet
 * @return list<string>
 */
function app_shared_state_sync_server_input_contract_errors(array $packet): array
{
    $errors = [];
    if (($packet['schema_version'] ?? '') !== APP_SHARED_STATE_SYNC_SERVER_INPUT_SCHEMA_VERSION) {
        $errors[] = 'schema_version';
    }
    if (($packet['generated_by']['tool'] ?? '') !== 'mtool') {
        $errors[] = 'generated_by_tool';
    }
    if (($packet['bundle_manifest_key'] ?? '') !== 'shared_state_sync_server_input') {
        $errors[] = 'bundle_manifest_key';
    }
    foreach (['shared_state_sync_contract', 'schema_api_contract', 'realtime_contract', 'node_server_input_contract'] as $contract) {
        if (!isset($packet['contracts'][$contract]) || !str_starts_with((string) $packet['contracts'][$contract], 'docs/')) {
            $errors[] = 'contract_' . $contract;
        }
    }
    if (($packet['server']['runtime'] ?? '') !== 'nodejs') {
        $errors[] = 'server_runtime';
    }
    if (($packet['server']['ownership'] ?? '') !== 'external_runtime_owner') {
        $errors[] = 'server_ownership';
    }
    if (($packet['server']['production_runtime_generated'] ?? true) !== false) {
        $errors[] = 'production_runtime_generated';
    }
    if (($packet['backend_integration']['authority'] ?? '') !== 'app_backend') {
        $errors[] = 'backend_authority';
    }
    if (($packet['backend_integration']['auth_context']['sso_token_broadcast_allowed'] ?? true) !== false) {
        $errors[] = 'sso_token_broadcast';
    }
    if (($packet['routes']['websocket']['path'] ?? '') !== '/sync/ws') {
        $errors[] = 'websocket_path';
    }
    if (($packet['routes']['sse']['server_to_client_only'] ?? false) !== true) {
        $errors[] = 'sse_server_to_client_only';
    }
    if (($packet['auth']['room_authorization'] ?? '') !== 'active_membership_required') {
        $errors[] = 'room_authorization';
    }
    if (($packet['rooms']['cross_room_broadcast_allowed'] ?? true) !== false) {
        $errors[] = 'cross_room_broadcast';
    }
    if (($packet['events']['replay_required'] ?? true) !== false) {
        $errors[] = 'event_replay_required';
    }
    if (($packet['fallbacks']['polling']['realtime_claim_allowed'] ?? true) !== false) {
        $errors[] = 'polling_realtime_claim';
    }
    foreach (['install_node_dependencies', 'start_production_server', 'open_public_port', 'broadcast_sso_token', 'claim_crdt_or_game_loop_support'] as $action) {
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
function app_shared_state_sync_server_input_markdown(array $packet): string
{
    $lines = [
        '# Shared State Sync Server Input',
        '',
        '- Schema: `' . (string) ($packet['schema_version'] ?? '') . '`',
        '- Bundle manifest key: `' . (string) ($packet['bundle_manifest_key'] ?? '') . '`',
        '- Runtime: `' . (string) ($packet['server']['runtime'] ?? '') . '`',
        '- Ownership: `' . (string) ($packet['server']['ownership'] ?? '') . '`',
        '',
        '## Boundary',
        '',
        '- This packet is an input for an external Node.js sync server owner.',
        '- Mtool does not install dependencies, initialize a Node.js project, start a production server, or open ports.',
        '- App/backend remains authoritative for SSO verification, room membership, state persistence, conflict policy, and audit.',
        '',
        '## Routes',
        '',
        '- WebSocket: `' . (string) ($packet['routes']['websocket']['path'] ?? '') . '`',
        '- SSE: `' . (string) ($packet['routes']['sse']['path'] ?? '') . '`',
        '- HTTP update: `' . (string) ($packet['routes']['http_update']['method'] ?? '') . ' ' . (string) ($packet['routes']['http_update']['path'] ?? '') . '`',
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
function app_shared_state_sync_server_input_emit(array $options, string $targetDir): array
{
    $normalizedTargetDir = rtrim($targetDir, DIRECTORY_SEPARATOR);
    if ($normalizedTargetDir === '' || $normalizedTargetDir === '.' || $normalizedTargetDir === DIRECTORY_SEPARATOR) {
        return app_shared_state_sync_server_input_emit_result(false, 'target directory is not a controlled artifact directory', $normalizedTargetDir, [], []);
    }

    $packet = app_shared_state_sync_server_input_build($options);
    $contractErrors = app_shared_state_sync_server_input_contract_errors($packet);
    if ($contractErrors !== []) {
        return app_shared_state_sync_server_input_emit_result(false, 'packet contract validation failed', $normalizedTargetDir, [], $contractErrors);
    }
    if (file_exists($normalizedTargetDir) && !is_dir($normalizedTargetDir)) {
        return app_shared_state_sync_server_input_emit_result(false, 'target path exists and is not a directory', $normalizedTargetDir, [], []);
    }
    if (!is_dir($normalizedTargetDir) && !mkdir($normalizedTargetDir, 0777, true)) {
        return app_shared_state_sync_server_input_emit_result(false, 'failed to create target directory', $normalizedTargetDir, [], []);
    }

    $files = [
        'sync-server-input.json' => app_shared_state_sync_server_input_json_text($packet),
        'SYNC-SERVER-INPUT.md' => app_shared_state_sync_server_input_markdown($packet),
    ];
    $emitted = [];
    foreach ($files as $relativePath => $text) {
        $path = $normalizedTargetDir . DIRECTORY_SEPARATOR . $relativePath;
        if (file_exists($path)) {
            return app_shared_state_sync_server_input_emit_result(false, 'package file already exists: ' . $relativePath, $normalizedTargetDir, $emitted, []);
        }
        if (file_put_contents($path, $text) === false) {
            return app_shared_state_sync_server_input_emit_result(false, 'failed to write package file: ' . $relativePath, $normalizedTargetDir, $emitted, []);
        }
        $emitted[] = $relativePath;
    }
    sort($emitted, SORT_STRING);
    return app_shared_state_sync_server_input_emit_result(true, '', $normalizedTargetDir, $emitted, []);
}

/** @param array<string,mixed> $value */
function app_shared_state_sync_server_input_json_text(array $value): string
{
    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
}

/**
 * @param list<string> $files
 * @param list<string> $contractErrors
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,contract_errors:list<string>}
 */
function app_shared_state_sync_server_input_emit_result(bool $ok, string $error, string $targetDir, array $files, array $contractErrors): array
{
    return [
        'ok' => $ok,
        'error' => $error,
        'target_dir' => $targetDir,
        'files' => $files,
        'contract_errors' => $contractErrors,
    ];
}
