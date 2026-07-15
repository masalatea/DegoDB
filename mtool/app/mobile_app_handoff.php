<?php

declare(strict_types=1);

const APP_MOBILE_APP_HANDOFF_SCHEMA_VERSION = 'mobile-app-handoff-v1';

const APP_MOBILE_APP_HANDOFF_REQUIRED_TOP_LEVEL = [
    'schema_version',
    'mutation_performed',
    'project',
    'source_artifacts',
    'platform_targets',
    'app_identity',
    'auth',
    'api',
    'screens',
    'navigation',
    'actions',
    'validation',
    'error_states',
    'native_capabilities',
    'offline_and_local_storage',
    'security_and_privacy',
    'build_handoff',
    'verification_checklist',
    'non_goals',
];

const APP_MOBILE_APP_HANDOFF_REQUIRED_ERROR_STATES = [
    'success',
    'validation_failure',
    'auth_failure',
    'network_failure',
    'unavailable_action',
];

const APP_MOBILE_APP_HANDOFF_REQUIRED_NON_GOALS = [
    'direct_native_generation',
    'app_store_signing',
    'offline_sync_by_default',
    'production_user_data_in_packet',
];

/**
 * Validate whether a mobile app handoff packet is complete enough for an app
 * creator, Codex/Claude, or a downstream mobile builder to proceed without
 * guessing core app behavior.
 *
 * @param array<string,mixed> $packet
 * @return array{ready:bool,validation_version:string,mutation_performed:bool,blockers:list<array{code:string,path:string}>,warnings:list<array{code:string,path:string}>}
 */
function app_mobile_app_handoff_validate(array $packet): array
{
    $blockers = [];
    $warnings = [];

    if (app_mobile_app_handoff_contains_secret($packet)) {
        $blockers[] = app_mobile_app_handoff_issue('secret_in_packet', '/');
    }

    foreach (APP_MOBILE_APP_HANDOFF_REQUIRED_TOP_LEVEL as $key) {
        if (!array_key_exists($key, $packet)) $blockers[] = app_mobile_app_handoff_issue('missing_required_top_level', '/' . $key);
    }

    if (($packet['schema_version'] ?? '') !== APP_MOBILE_APP_HANDOFF_SCHEMA_VERSION) {
        $blockers[] = app_mobile_app_handoff_issue('invalid_schema_version', '/schema_version');
    }
    if (($packet['mutation_performed'] ?? null) !== false) {
        $blockers[] = app_mobile_app_handoff_issue('mutation_must_be_false', '/mutation_performed');
    }

    app_mobile_app_handoff_validate_project($packet['project'] ?? null, $blockers);
    app_mobile_app_handoff_validate_source_artifacts($packet['source_artifacts'] ?? null, $blockers);
    app_mobile_app_handoff_validate_platform_targets($packet['platform_targets'] ?? null, $blockers, $warnings);
    app_mobile_app_handoff_validate_app_identity($packet['app_identity'] ?? null, $blockers);
    app_mobile_app_handoff_validate_auth($packet['auth'] ?? null, $blockers);
    app_mobile_app_handoff_validate_api($packet['api'] ?? null, $blockers);
    app_mobile_app_handoff_validate_screens($packet['screens'] ?? null, $blockers);
    app_mobile_app_handoff_validate_navigation($packet['navigation'] ?? null, $blockers);
    app_mobile_app_handoff_validate_actions($packet['actions'] ?? null, $blockers);
    app_mobile_app_handoff_validate_validation($packet['validation'] ?? null, $blockers);
    app_mobile_app_handoff_validate_error_states($packet['error_states'] ?? null, $blockers);
    app_mobile_app_handoff_validate_native_capabilities($packet['native_capabilities'] ?? null, $blockers);
    app_mobile_app_handoff_validate_offline_storage($packet['offline_and_local_storage'] ?? null, $blockers);
    app_mobile_app_handoff_validate_security($packet['security_and_privacy'] ?? null, $blockers);
    app_mobile_app_handoff_validate_build_handoff($packet['build_handoff'] ?? null, $blockers);
    app_mobile_app_handoff_validate_verification_checklist($packet['verification_checklist'] ?? null, $blockers);
    app_mobile_app_handoff_validate_non_goals($packet['non_goals'] ?? null, $blockers);

    return [
        'ready' => $blockers === [],
        'validation_version' => APP_MOBILE_APP_HANDOFF_SCHEMA_VERSION . '-validation-v1',
        'mutation_performed' => false,
        'blockers' => app_mobile_app_handoff_sorted_issues($blockers),
        'warnings' => app_mobile_app_handoff_sorted_issues($warnings),
    ];
}

/** @param list<array{code:string,path:string}> $blockers */
function app_mobile_app_handoff_validate_project(mixed $project, array &$blockers): void
{
    if (!is_array($project)) {
        $blockers[] = app_mobile_app_handoff_issue('project_required', '/project');
        return;
    }
    foreach (['project_key', 'name', 'title'] as $key) {
        if (trim((string) ($project[$key] ?? '')) === '') $blockers[] = app_mobile_app_handoff_issue('project_field_required', '/project/' . $key);
    }
}

/** @param list<array{code:string,path:string}> $blockers */
function app_mobile_app_handoff_validate_source_artifacts(mixed $sourceArtifacts, array &$blockers): void
{
    if (!is_array($sourceArtifacts)) {
        $blockers[] = app_mobile_app_handoff_issue('source_artifacts_required', '/source_artifacts');
        return;
    }
    foreach (['openapi', 'no_code_runtime', 'screen_metadata', 'auth_policy'] as $key) {
        $artifact = $sourceArtifacts[$key] ?? null;
        if (!is_array($artifact)) {
            $blockers[] = app_mobile_app_handoff_issue('source_artifact_required', '/source_artifacts/' . $key);
            continue;
        }
        if (trim((string) ($artifact['ref'] ?? '')) === '') $blockers[] = app_mobile_app_handoff_issue('source_artifact_ref_required', '/source_artifacts/' . $key . '/ref');
        $hash = (string) ($artifact['sha256'] ?? '');
        if (preg_match('/^[a-f0-9]{64}$/', $hash) !== 1) $blockers[] = app_mobile_app_handoff_issue('source_artifact_sha256_required', '/source_artifacts/' . $key . '/sha256');
    }
}

/** @param list<array{code:string,path:string}> $blockers @param list<array{code:string,path:string}> $warnings */
function app_mobile_app_handoff_validate_platform_targets(mixed $targets, array &$blockers, array &$warnings): void
{
    if (!is_array($targets) || $targets === []) {
        $blockers[] = app_mobile_app_handoff_issue('platform_targets_required', '/platform_targets');
        return;
    }
    $first = $targets[0] ?? null;
    if (!is_array($first) || ($first['target_key'] ?? '') !== 'react_web_capacitor_ios_android' || ($first['required_now'] ?? null) !== true) {
        $blockers[] = app_mobile_app_handoff_issue('first_platform_target_must_be_react_web_capacitor', '/platform_targets/0');
    }
    $keys = [];
    foreach ($targets as $index => $target) {
        if (!is_array($target)) {
            $blockers[] = app_mobile_app_handoff_issue('platform_target_invalid', '/platform_targets/' . $index);
            continue;
        }
        $key = (string) ($target['target_key'] ?? '');
        if ($key === '') $blockers[] = app_mobile_app_handoff_issue('platform_target_key_required', '/platform_targets/' . $index . '/target_key');
        if (isset($keys[$key])) $blockers[] = app_mobile_app_handoff_issue('duplicate_platform_target', '/platform_targets/' . $index);
        $keys[$key] = true;
    }
    foreach (['flutter_input_packet', 'react_native_input_packet', 'direct_native_generation'] as $laterTarget) {
        if (!isset($keys[$laterTarget])) $warnings[] = app_mobile_app_handoff_issue('later_platform_target_not_declared', '/platform_targets/' . $laterTarget);
    }
}

/** @param list<array{code:string,path:string}> $blockers */
function app_mobile_app_handoff_validate_app_identity(mixed $identity, array &$blockers): void
{
    if (!is_array($identity)) {
        $blockers[] = app_mobile_app_handoff_issue('app_identity_required', '/app_identity');
        return;
    }
    foreach (['display_name', 'bundle_id_placeholder', 'package_id_placeholder', 'environment'] as $key) {
        if (trim((string) ($identity[$key] ?? '')) === '') $blockers[] = app_mobile_app_handoff_issue('app_identity_field_required', '/app_identity/' . $key);
    }
}

/** @param list<array{code:string,path:string}> $blockers */
function app_mobile_app_handoff_validate_auth(mixed $auth, array &$blockers): void
{
    if (!is_array($auth)) {
        $blockers[] = app_mobile_app_handoff_issue('auth_required', '/auth');
        return;
    }
    foreach (['mode', 'login_route', 'logout_route', 'token_storage_policy', 'redirect_or_deep_link_policy'] as $key) {
        if (trim((string) ($auth[$key] ?? '')) === '') $blockers[] = app_mobile_app_handoff_issue('auth_field_required', '/auth/' . $key);
    }
    if (!in_array((string) ($auth['mode'] ?? ''), ['oidc', 'sso', 'session', 'bearer', 'none'], true)) {
        $blockers[] = app_mobile_app_handoff_issue('auth_mode_invalid', '/auth/mode');
    }
}

/** @param list<array{code:string,path:string}> $blockers */
function app_mobile_app_handoff_validate_api(mixed $api, array &$blockers): void
{
    if (!is_array($api)) {
        $blockers[] = app_mobile_app_handoff_issue('api_required', '/api');
        return;
    }
    foreach (['base_url_policy', 'error_envelope'] as $key) {
        if (trim((string) ($api[$key] ?? '')) === '') $blockers[] = app_mobile_app_handoff_issue('api_field_required', '/api/' . $key);
    }
    $endpoints = $api['endpoints'] ?? null;
    if (!is_array($endpoints) || $endpoints === []) {
        $blockers[] = app_mobile_app_handoff_issue('api_endpoint_required', '/api/endpoints');
        return;
    }
    foreach ($endpoints as $index => $endpoint) {
        if (!is_array($endpoint)) {
            $blockers[] = app_mobile_app_handoff_issue('api_endpoint_invalid', '/api/endpoints/' . $index);
            continue;
        }
        foreach (['endpoint_key', 'method', 'path', 'response_ref'] as $key) {
            if (trim((string) ($endpoint[$key] ?? '')) === '') $blockers[] = app_mobile_app_handoff_issue('api_endpoint_field_required', '/api/endpoints/' . $index . '/' . $key);
        }
    }
}

/** @param list<array{code:string,path:string}> $blockers */
function app_mobile_app_handoff_validate_screens(mixed $screens, array &$blockers): void
{
    if (!is_array($screens) || $screens === []) {
        $blockers[] = app_mobile_app_handoff_issue('screens_required', '/screens');
        return;
    }
    $types = [];
    foreach ($screens as $index => $screen) {
        if (!is_array($screen)) {
            $blockers[] = app_mobile_app_handoff_issue('screen_invalid', '/screens/' . $index);
            continue;
        }
        foreach (['screen_key', 'screen_type', 'title'] as $key) {
            if (trim((string) ($screen[$key] ?? '')) === '') $blockers[] = app_mobile_app_handoff_issue('screen_field_required', '/screens/' . $index . '/' . $key);
        }
        $types[(string) ($screen['screen_type'] ?? '')] = true;
        if (!is_array($screen['states'] ?? null) || $screen['states'] === []) $blockers[] = app_mobile_app_handoff_issue('screen_states_required', '/screens/' . $index . '/states');
    }
    if (!isset($types['list'])) $blockers[] = app_mobile_app_handoff_issue('list_screen_required', '/screens');
    if (!isset($types['detail']) && !isset($types['form'])) $blockers[] = app_mobile_app_handoff_issue('detail_or_form_screen_required', '/screens');
}

/** @param list<array{code:string,path:string}> $blockers */
function app_mobile_app_handoff_validate_navigation(mixed $navigation, array &$blockers): void
{
    if (!is_array($navigation) || $navigation === []) {
        $blockers[] = app_mobile_app_handoff_issue('navigation_required', '/navigation');
        return;
    }
    foreach ($navigation as $index => $edge) {
        if (!is_array($edge)) {
            $blockers[] = app_mobile_app_handoff_issue('navigation_edge_invalid', '/navigation/' . $index);
            continue;
        }
        foreach (['from', 'to', 'trigger'] as $key) {
            if (trim((string) ($edge[$key] ?? '')) === '') $blockers[] = app_mobile_app_handoff_issue('navigation_edge_field_required', '/navigation/' . $index . '/' . $key);
        }
    }
}

/** @param list<array{code:string,path:string}> $blockers */
function app_mobile_app_handoff_validate_actions(mixed $actions, array &$blockers): void
{
    if (!is_array($actions) || $actions === []) {
        $blockers[] = app_mobile_app_handoff_issue('actions_required', '/actions');
        return;
    }
    $safeSubmit = false;
    foreach ($actions as $index => $action) {
        if (!is_array($action)) {
            $blockers[] = app_mobile_app_handoff_issue('action_invalid', '/actions/' . $index);
            continue;
        }
        foreach (['action_key', 'kind', 'endpoint_key', 'availability', 'success_state', 'failure_state'] as $key) {
            if (trim((string) ($action[$key] ?? '')) === '') $blockers[] = app_mobile_app_handoff_issue('action_field_required', '/actions/' . $index . '/' . $key);
        }
        if (in_array((string) ($action['kind'] ?? ''), ['submit', 'custom'], true)
            && in_array((string) ($action['safety'] ?? ''), ['safe_submit', 'dry_run', 'default_off'], true)) {
            $safeSubmit = true;
        }
        if (($action['mutates'] ?? null) === true && trim((string) ($action['idempotency'] ?? '')) === '') {
            $blockers[] = app_mobile_app_handoff_issue('mutating_action_idempotency_required', '/actions/' . $index . '/idempotency');
        }
    }
    if (!$safeSubmit) $blockers[] = app_mobile_app_handoff_issue('safe_submit_or_custom_action_required', '/actions');
}

/** @param list<array{code:string,path:string}> $blockers */
function app_mobile_app_handoff_validate_validation(mixed $validation, array &$blockers): void
{
    if (!is_array($validation)) {
        $blockers[] = app_mobile_app_handoff_issue('validation_required', '/validation');
        return;
    }
    if (!is_array($validation['field_rules'] ?? null) || $validation['field_rules'] === []) $blockers[] = app_mobile_app_handoff_issue('field_validation_rules_required', '/validation/field_rules');
    if (!is_array($validation['action_rules'] ?? null) || $validation['action_rules'] === []) $blockers[] = app_mobile_app_handoff_issue('action_validation_rules_required', '/validation/action_rules');
    if (trim((string) ($validation['enforcement'] ?? '')) === '') $blockers[] = app_mobile_app_handoff_issue('validation_enforcement_required', '/validation/enforcement');
}

/** @param list<array{code:string,path:string}> $blockers */
function app_mobile_app_handoff_validate_error_states(mixed $errorStates, array &$blockers): void
{
    if (!is_array($errorStates) || $errorStates === []) {
        $blockers[] = app_mobile_app_handoff_issue('error_states_required', '/error_states');
        return;
    }
    $keys = [];
    foreach ($errorStates as $index => $state) {
        if (!is_array($state)) {
            $blockers[] = app_mobile_app_handoff_issue('error_state_invalid', '/error_states/' . $index);
            continue;
        }
        $key = (string) ($state['state_key'] ?? '');
        if ($key === '') $blockers[] = app_mobile_app_handoff_issue('error_state_key_required', '/error_states/' . $index . '/state_key');
        if (trim((string) ($state['user_message'] ?? '')) === '') $blockers[] = app_mobile_app_handoff_issue('error_state_message_required', '/error_states/' . $index . '/user_message');
        $keys[$key] = true;
    }
    foreach (APP_MOBILE_APP_HANDOFF_REQUIRED_ERROR_STATES as $required) {
        if (!isset($keys[$required])) $blockers[] = app_mobile_app_handoff_issue('required_error_state_missing', '/error_states/' . $required);
    }
}

/** @param list<array{code:string,path:string}> $blockers */
function app_mobile_app_handoff_validate_native_capabilities(mixed $capabilities, array &$blockers): void
{
    if (!is_array($capabilities) || $capabilities === []) {
        $blockers[] = app_mobile_app_handoff_issue('native_capability_declaration_required', '/native_capabilities');
        return;
    }
    foreach ($capabilities as $index => $capability) {
        if (!is_array($capability)) {
            $blockers[] = app_mobile_app_handoff_issue('native_capability_invalid', '/native_capabilities/' . $index);
            continue;
        }
        foreach (['capability_key', 'required', 'reason'] as $key) {
            if (!array_key_exists($key, $capability) || (is_string($capability[$key]) && trim($capability[$key]) === '')) {
                $blockers[] = app_mobile_app_handoff_issue('native_capability_field_required', '/native_capabilities/' . $index . '/' . $key);
            }
        }
    }
}

/** @param list<array{code:string,path:string}> $blockers */
function app_mobile_app_handoff_validate_offline_storage(mixed $storage, array &$blockers): void
{
    if (!is_array($storage)) {
        $blockers[] = app_mobile_app_handoff_issue('offline_storage_required', '/offline_and_local_storage');
        return;
    }
    foreach (['offline_sync', 'local_draft_policy', 'cache_policy'] as $key) {
        if (!array_key_exists($key, $storage)) $blockers[] = app_mobile_app_handoff_issue('offline_storage_field_required', '/offline_and_local_storage/' . $key);
    }
    if (($storage['offline_sync'] ?? null) === true && trim((string) ($storage['sync_contract_ref'] ?? '')) === '') {
        $blockers[] = app_mobile_app_handoff_issue('offline_sync_contract_required', '/offline_and_local_storage/sync_contract_ref');
    }
}

/** @param list<array{code:string,path:string}> $blockers */
function app_mobile_app_handoff_validate_security(mixed $security, array &$blockers): void
{
    if (!is_array($security)) {
        $blockers[] = app_mobile_app_handoff_issue('security_required', '/security_and_privacy');
        return;
    }
    foreach (['secret_policy', 'pii_policy', 'token_persistence_policy', 'logging_policy'] as $key) {
        if (trim((string) ($security[$key] ?? '')) === '') $blockers[] = app_mobile_app_handoff_issue('security_field_required', '/security_and_privacy/' . $key);
    }
}

/** @param list<array{code:string,path:string}> $blockers */
function app_mobile_app_handoff_validate_build_handoff(mixed $build, array &$blockers): void
{
    if (!is_array($build)) {
        $blockers[] = app_mobile_app_handoff_issue('build_handoff_required', '/build_handoff');
        return;
    }
    foreach (['owned_by', 'capacitor_setup_owner', 'ios_build_owner', 'android_build_owner', 'signing_owner', 'store_submission_owner'] as $key) {
        if (trim((string) ($build[$key] ?? '')) === '') $blockers[] = app_mobile_app_handoff_issue('build_handoff_field_required', '/build_handoff/' . $key);
    }
}

/** @param list<array{code:string,path:string}> $blockers */
function app_mobile_app_handoff_validate_verification_checklist(mixed $checklist, array &$blockers): void
{
    if (!is_array($checklist) || count($checklist) < 5) {
        $blockers[] = app_mobile_app_handoff_issue('verification_checklist_incomplete', '/verification_checklist');
    }
}

/** @param list<array{code:string,path:string}> $blockers */
function app_mobile_app_handoff_validate_non_goals(mixed $nonGoals, array &$blockers): void
{
    if (!is_array($nonGoals)) {
        $blockers[] = app_mobile_app_handoff_issue('non_goals_required', '/non_goals');
        return;
    }
    $set = array_fill_keys(array_map('strval', $nonGoals), true);
    foreach (APP_MOBILE_APP_HANDOFF_REQUIRED_NON_GOALS as $required) {
        if (!isset($set[$required])) $blockers[] = app_mobile_app_handoff_issue('required_non_goal_missing', '/non_goals/' . $required);
    }
}

/** @return array{code:string,path:string} */
function app_mobile_app_handoff_issue(string $code, string $path): array
{
    return ['code' => $code, 'path' => $path];
}

/** @param list<array{code:string,path:string}> $issues @return list<array{code:string,path:string}> */
function app_mobile_app_handoff_sorted_issues(array $issues): array
{
    $unique = [];
    foreach ($issues as $issue) $unique[$issue['code'] . "\n" . $issue['path']] = $issue;
    ksort($unique, SORT_STRING);
    return array_values($unique);
}

function app_mobile_app_handoff_contains_secret(mixed $value, string $key = ''): bool
{
    $normalizedKey = strtolower($key);
    if (!is_array($value) && str_ends_with($normalizedKey, '_policy')) return false;
    $sensitiveExactKeys = [
        'password', 'passwd', 'secret', 'credential', 'credentials', 'dsn',
        'access_token', 'refresh_token', 'id_token', 'signing_key', 'certificate',
    ];
    if (in_array($normalizedKey, $sensitiveExactKeys, true)) return true;
    if (preg_match('/(^|_)(password|passwd|secret|credential|credentials|dsn|signing_key|certificate)($|_)/i', $key) === 1) return true;
    if (preg_match('/(^|_)(access|refresh|id)_token($|_)/i', $key) === 1) return true;
    if (!is_array($value)) return false;
    foreach ($value as $childKey => $child) {
        if (app_mobile_app_handoff_contains_secret($child, (string) $childKey)) return true;
    }
    return false;
}
