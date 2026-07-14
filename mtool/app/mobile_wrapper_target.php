<?php

declare(strict_types=1);

require_once __DIR__ . '/mobile_app_handoff.php';

const APP_MOBILE_WRAPPER_TARGET_SCHEMA_VERSION = 'mobile-react-wrapper-target-v1';
const APP_MOBILE_REACT_WRAPPER_APP_HANDOFF_SCHEMA_VERSION = 'mobile-react-wrapper-app-handoff-v1';
const APP_MOBILE_LATER_PLATFORM_INPUT_PACKET_SCHEMA_VERSION = 'mobile-later-platform-input-packet-v1';
const APP_MOBILE_WRAPPER_BUNDLE_MANIFEST_SCHEMA_VERSION = 'mobile-wrapper-bundle-manifest-v1';
const APP_MOBILE_WRAPPER_TARGET_VERIFICATION_GATES = [
    'php -l mtool/app/mobile_app_handoff.php',
    'focused MobileAppHandoffTest',
    'make sample28-no-code-react-bridge-build-smoke',
    'make sample28-no-code-react-bridge-browser-smoke',
    'git diff --check',
];

/**
 * Build the first C1 wrapper-readiness package in memory.
 *
 * This does not create a React app, initialize Capacitor, build native targets,
 * sign apps, or write files. File emission belongs to a later artifact slice.
 *
 * @param array<string,mixed> $handoff
 * @return array{ok:bool,error:string,package:array<string,mixed>|null,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_build_c1_package(array $handoff): array
{
    $validation = app_mobile_app_handoff_validate($handoff);
    if (($validation['ready'] ?? false) !== true) {
        return [
            'ok' => false,
            'error' => 'mobile app handoff packet is not ready',
            'package' => null,
            'validation' => $validation,
        ];
    }

    $contract = app_mobile_wrapper_target_contract_from_handoff($handoff);
    return [
        'ok' => true,
        'error' => '',
        'package' => [
            'path_hint' => 'mobile-wrapper-target/',
            'mutation_performed' => false,
            'files' => [
                'wrapper-target-contract.json' => $contract,
                'WRAPPER-CONSUMER-NOTES.md' => app_mobile_wrapper_target_consumer_notes_markdown($contract),
            ],
        ],
        'validation' => $validation,
    ];
}

/**
 * Emit the C1 wrapper-readiness package to a controlled artifact directory.
 *
 * This only writes the two package files produced by
 * app_mobile_wrapper_target_build_c1_package(). It refuses to overwrite existing
 * files and does not create or modify a React, Capacitor, iOS, or Android
 * project.
 *
 * @param array<string,mixed> $handoff
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_emit_c1_package(array $handoff, string $targetDir): array
{
    $normalizedTargetDir = rtrim($targetDir, DIRECTORY_SEPARATOR);
    if ($normalizedTargetDir === '' || $normalizedTargetDir === '.' || $normalizedTargetDir === DIRECTORY_SEPARATOR) {
        return app_mobile_wrapper_target_emit_result(false, 'target directory is not a controlled artifact directory', $normalizedTargetDir, [], []);
    }

    $packageResult = app_mobile_wrapper_target_build_c1_package($handoff);
    if (!$packageResult['ok'] || !is_array($packageResult['package'])) {
        return app_mobile_wrapper_target_emit_result(false, $packageResult['error'], $normalizedTargetDir, [], $packageResult['validation']);
    }

    if (file_exists($normalizedTargetDir) && !is_dir($normalizedTargetDir)) {
        return app_mobile_wrapper_target_emit_result(false, 'target path exists and is not a directory', $normalizedTargetDir, [], $packageResult['validation']);
    }
    if (!is_dir($normalizedTargetDir) && !mkdir($normalizedTargetDir, 0777, true)) {
        return app_mobile_wrapper_target_emit_result(false, 'failed to create target directory', $normalizedTargetDir, [], $packageResult['validation']);
    }

    $files = $packageResult['package']['files'] ?? [];
    if (!is_array($files)) {
        return app_mobile_wrapper_target_emit_result(false, 'package files are invalid', $normalizedTargetDir, [], $packageResult['validation']);
    }

    $emitted = [];
    foreach ($files as $relativePath => $content) {
        $relativePath = (string) $relativePath;
        if ($relativePath === '' || str_contains($relativePath, '..') || str_starts_with($relativePath, DIRECTORY_SEPARATOR)) {
            return app_mobile_wrapper_target_emit_result(false, 'package file path is not safe', $normalizedTargetDir, $emitted, $packageResult['validation']);
        }
        $path = $normalizedTargetDir . DIRECTORY_SEPARATOR . $relativePath;
        if (file_exists($path)) {
            return app_mobile_wrapper_target_emit_result(false, 'package file already exists: ' . $relativePath, $normalizedTargetDir, $emitted, $packageResult['validation']);
        }
        $text = is_array($content)
            ? app_mobile_wrapper_target_json_text($content)
            : (string) $content;
        if (file_put_contents($path, $text) === false) {
            return app_mobile_wrapper_target_emit_result(false, 'failed to write package file: ' . $relativePath, $normalizedTargetDir, $emitted, $packageResult['validation']);
        }
        $emitted[] = $relativePath;
    }

    sort($emitted, SORT_STRING);
    return app_mobile_wrapper_target_emit_result(true, '', $normalizedTargetDir, $emitted, $packageResult['validation']);
}

/**
 * Build the MW-2 React/Web wrapper app handoff proof in memory.
 *
 * This consumes the C1 wrapper-readiness contract and turns it into a
 * creator/builder-facing handoff for a React/Web app shell and
 * Capacitor-style preparation boundary. It still does not create or mutate a
 * React, Capacitor, iOS, or Android project.
 *
 * @param array<string,mixed> $handoff
 * @return array{ok:bool,error:string,package:array<string,mixed>|null,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_build_react_app_handoff_proof(array $handoff): array
{
    $c1 = app_mobile_wrapper_target_build_c1_package($handoff);
    if (!$c1['ok'] || !is_array($c1['package'])) {
        return [
            'ok' => false,
            'error' => $c1['error'],
            'package' => null,
            'validation' => $c1['validation'],
        ];
    }

    $files = is_array($c1['package']['files'] ?? null) ? $c1['package']['files'] : [];
    $contract = is_array($files['wrapper-target-contract.json'] ?? null) ? $files['wrapper-target-contract.json'] : [];
    $proof = app_mobile_wrapper_target_react_app_handoff_from_contract($contract, $handoff);

    return [
        'ok' => true,
        'error' => '',
        'package' => [
            'path_hint' => 'react-wrapper-app-handoff/',
            'mutation_performed' => false,
            'files' => [
                'react-wrapper-app-handoff.json' => $proof,
                'REACT-WRAPPER-APP-HANDOFF.md' => app_mobile_wrapper_target_react_app_handoff_markdown($proof),
            ],
        ],
        'validation' => $c1['validation'],
    ];
}

/**
 * @param array<string,mixed> $handoff
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_emit_react_app_handoff_proof(array $handoff, string $targetDir): array
{
    $normalizedTargetDir = rtrim($targetDir, DIRECTORY_SEPARATOR);
    if ($normalizedTargetDir === '' || $normalizedTargetDir === '.' || $normalizedTargetDir === DIRECTORY_SEPARATOR) {
        return app_mobile_wrapper_target_emit_result(false, 'target directory is not a controlled artifact directory', $normalizedTargetDir, [], []);
    }

    $packageResult = app_mobile_wrapper_target_build_react_app_handoff_proof($handoff);
    if (!$packageResult['ok'] || !is_array($packageResult['package'])) {
        return app_mobile_wrapper_target_emit_result(false, $packageResult['error'], $normalizedTargetDir, [], $packageResult['validation']);
    }

    if (file_exists($normalizedTargetDir) && !is_dir($normalizedTargetDir)) {
        return app_mobile_wrapper_target_emit_result(false, 'target path exists and is not a directory', $normalizedTargetDir, [], $packageResult['validation']);
    }
    if (!is_dir($normalizedTargetDir) && !mkdir($normalizedTargetDir, 0777, true)) {
        return app_mobile_wrapper_target_emit_result(false, 'failed to create target directory', $normalizedTargetDir, [], $packageResult['validation']);
    }

    $files = $packageResult['package']['files'] ?? [];
    if (!is_array($files)) {
        return app_mobile_wrapper_target_emit_result(false, 'package files are invalid', $normalizedTargetDir, [], $packageResult['validation']);
    }

    $emitted = [];
    foreach ($files as $relativePath => $content) {
        $relativePath = (string) $relativePath;
        if ($relativePath === '' || str_contains($relativePath, '..') || str_starts_with($relativePath, DIRECTORY_SEPARATOR)) {
            return app_mobile_wrapper_target_emit_result(false, 'package file path is not safe', $normalizedTargetDir, $emitted, $packageResult['validation']);
        }
        $path = $normalizedTargetDir . DIRECTORY_SEPARATOR . $relativePath;
        if (file_exists($path)) {
            return app_mobile_wrapper_target_emit_result(false, 'package file already exists: ' . $relativePath, $normalizedTargetDir, $emitted, $packageResult['validation']);
        }
        $text = is_array($content)
            ? app_mobile_wrapper_target_json_text($content)
            : (string) $content;
        if (file_put_contents($path, $text) === false) {
            return app_mobile_wrapper_target_emit_result(false, 'failed to write package file: ' . $relativePath, $normalizedTargetDir, $emitted, $packageResult['validation']);
        }
        $emitted[] = $relativePath;
    }

    sort($emitted, SORT_STRING);
    return app_mobile_wrapper_target_emit_result(true, '', $normalizedTargetDir, $emitted, $packageResult['validation']);
}

/**
 * Build MW-4 later platform input packets for Flutter and React Native.
 *
 * These packets are structured handoff inputs for downstream builders. They do
 * not generate Dart, React Native source, native projects, signing files, or
 * store submission artifacts.
 *
 * @param array<string,mixed> $handoff
 * @return array{ok:bool,error:string,package:array<string,mixed>|null,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_build_later_platform_input_packets(array $handoff): array
{
    $reactProof = app_mobile_wrapper_target_build_react_app_handoff_proof($handoff);
    if (!$reactProof['ok'] || !is_array($reactProof['package'])) {
        return [
            'ok' => false,
            'error' => $reactProof['error'],
            'package' => null,
            'validation' => $reactProof['validation'],
        ];
    }

    $files = is_array($reactProof['package']['files'] ?? null) ? $reactProof['package']['files'] : [];
    $proof = is_array($files['react-wrapper-app-handoff.json'] ?? null) ? $files['react-wrapper-app-handoff.json'] : [];
    $flutter = app_mobile_wrapper_target_later_platform_packet_from_react_proof('flutter', $proof);
    $reactNative = app_mobile_wrapper_target_later_platform_packet_from_react_proof('react_native', $proof);

    return [
        'ok' => true,
        'error' => '',
        'package' => [
            'path_hint' => 'later-platform-input-packets/',
            'mutation_performed' => false,
            'files' => [
                'flutter-input-packet.json' => $flutter,
                'react-native-input-packet.json' => $reactNative,
                'LATER-PLATFORM-INPUT-PACKETS.md' => app_mobile_wrapper_target_later_platform_packets_markdown([$flutter, $reactNative]),
            ],
        ],
        'validation' => $reactProof['validation'],
    ];
}

/**
 * @param array<string,mixed> $handoff
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_emit_later_platform_input_packets(array $handoff, string $targetDir): array
{
    $normalizedTargetDir = rtrim($targetDir, DIRECTORY_SEPARATOR);
    if ($normalizedTargetDir === '' || $normalizedTargetDir === '.' || $normalizedTargetDir === DIRECTORY_SEPARATOR) {
        return app_mobile_wrapper_target_emit_result(false, 'target directory is not a controlled artifact directory', $normalizedTargetDir, [], []);
    }

    $packageResult = app_mobile_wrapper_target_build_later_platform_input_packets($handoff);
    if (!$packageResult['ok'] || !is_array($packageResult['package'])) {
        return app_mobile_wrapper_target_emit_result(false, $packageResult['error'], $normalizedTargetDir, [], $packageResult['validation']);
    }

    if (file_exists($normalizedTargetDir) && !is_dir($normalizedTargetDir)) {
        return app_mobile_wrapper_target_emit_result(false, 'target path exists and is not a directory', $normalizedTargetDir, [], $packageResult['validation']);
    }
    if (!is_dir($normalizedTargetDir) && !mkdir($normalizedTargetDir, 0777, true)) {
        return app_mobile_wrapper_target_emit_result(false, 'failed to create target directory', $normalizedTargetDir, [], $packageResult['validation']);
    }

    $files = $packageResult['package']['files'] ?? [];
    if (!is_array($files)) {
        return app_mobile_wrapper_target_emit_result(false, 'package files are invalid', $normalizedTargetDir, [], $packageResult['validation']);
    }

    $emitted = [];
    foreach ($files as $relativePath => $content) {
        $relativePath = (string) $relativePath;
        if ($relativePath === '' || str_contains($relativePath, '..') || str_starts_with($relativePath, DIRECTORY_SEPARATOR)) {
            return app_mobile_wrapper_target_emit_result(false, 'package file path is not safe', $normalizedTargetDir, $emitted, $packageResult['validation']);
        }
        $path = $normalizedTargetDir . DIRECTORY_SEPARATOR . $relativePath;
        if (file_exists($path)) {
            return app_mobile_wrapper_target_emit_result(false, 'package file already exists: ' . $relativePath, $normalizedTargetDir, $emitted, $packageResult['validation']);
        }
        $text = is_array($content)
            ? app_mobile_wrapper_target_json_text($content)
            : (string) $content;
        if (file_put_contents($path, $text) === false) {
            return app_mobile_wrapper_target_emit_result(false, 'failed to write package file: ' . $relativePath, $normalizedTargetDir, $emitted, $packageResult['validation']);
        }
        $emitted[] = $relativePath;
    }

    sort($emitted, SORT_STRING);
    return app_mobile_wrapper_target_emit_result(true, '', $normalizedTargetDir, $emitted, $packageResult['validation']);
}

/**
 * Build a bundled manifest that indexes the mobile wrapper artifact set.
 *
 * This is an index/checklist only. It does not duplicate the artifact files and
 * does not create React, Capacitor, Flutter, React Native, iOS, or Android
 * projects.
 *
 * @param array<string,mixed> $handoff
 * @return array{ok:bool,error:string,package:array<string,mixed>|null,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_build_bundle_manifest(array $handoff): array
{
    $c1 = app_mobile_wrapper_target_build_c1_package($handoff);
    if (!$c1['ok'] || !is_array($c1['package'])) {
        return [
            'ok' => false,
            'error' => $c1['error'],
            'package' => null,
            'validation' => $c1['validation'],
        ];
    }

    $react = app_mobile_wrapper_target_build_react_app_handoff_proof($handoff);
    if (!$react['ok'] || !is_array($react['package'])) {
        return [
            'ok' => false,
            'error' => $react['error'],
            'package' => null,
            'validation' => $react['validation'],
        ];
    }

    $platforms = app_mobile_wrapper_target_build_later_platform_input_packets($handoff);
    if (!$platforms['ok'] || !is_array($platforms['package'])) {
        return [
            'ok' => false,
            'error' => $platforms['error'],
            'package' => null,
            'validation' => $platforms['validation'],
        ];
    }

    $manifest = app_mobile_wrapper_target_bundle_manifest_from_packages($handoff, $c1['package'], $react['package'], $platforms['package']);

    return [
        'ok' => true,
        'error' => '',
        'package' => [
            'path_hint' => 'mobile-wrapper-bundle/',
            'mutation_performed' => false,
            'files' => [
                'mobile-wrapper-bundle-manifest.json' => $manifest,
                'MOBILE-WRAPPER-BUNDLE.md' => app_mobile_wrapper_target_bundle_manifest_markdown($manifest),
            ],
        ],
        'validation' => $c1['validation'],
    ];
}

/**
 * @param array<string,mixed> $handoff
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_emit_bundle_manifest(array $handoff, string $targetDir): array
{
    $normalizedTargetDir = rtrim($targetDir, DIRECTORY_SEPARATOR);
    if ($normalizedTargetDir === '' || $normalizedTargetDir === '.' || $normalizedTargetDir === DIRECTORY_SEPARATOR) {
        return app_mobile_wrapper_target_emit_result(false, 'target directory is not a controlled artifact directory', $normalizedTargetDir, [], []);
    }

    $packageResult = app_mobile_wrapper_target_build_bundle_manifest($handoff);
    if (!$packageResult['ok'] || !is_array($packageResult['package'])) {
        return app_mobile_wrapper_target_emit_result(false, $packageResult['error'], $normalizedTargetDir, [], $packageResult['validation']);
    }

    if (file_exists($normalizedTargetDir) && !is_dir($normalizedTargetDir)) {
        return app_mobile_wrapper_target_emit_result(false, 'target path exists and is not a directory', $normalizedTargetDir, [], $packageResult['validation']);
    }
    if (!is_dir($normalizedTargetDir) && !mkdir($normalizedTargetDir, 0777, true)) {
        return app_mobile_wrapper_target_emit_result(false, 'failed to create target directory', $normalizedTargetDir, [], $packageResult['validation']);
    }

    $files = $packageResult['package']['files'] ?? [];
    if (!is_array($files)) {
        return app_mobile_wrapper_target_emit_result(false, 'package files are invalid', $normalizedTargetDir, [], $packageResult['validation']);
    }

    $emitted = [];
    foreach ($files as $relativePath => $content) {
        $relativePath = (string) $relativePath;
        if ($relativePath === '' || str_contains($relativePath, '..') || str_starts_with($relativePath, DIRECTORY_SEPARATOR)) {
            return app_mobile_wrapper_target_emit_result(false, 'package file path is not safe', $normalizedTargetDir, $emitted, $packageResult['validation']);
        }
        $path = $normalizedTargetDir . DIRECTORY_SEPARATOR . $relativePath;
        if (file_exists($path)) {
            return app_mobile_wrapper_target_emit_result(false, 'package file already exists: ' . $relativePath, $normalizedTargetDir, $emitted, $packageResult['validation']);
        }
        $text = is_array($content)
            ? app_mobile_wrapper_target_json_text($content)
            : (string) $content;
        if (file_put_contents($path, $text) === false) {
            return app_mobile_wrapper_target_emit_result(false, 'failed to write package file: ' . $relativePath, $normalizedTargetDir, $emitted, $packageResult['validation']);
        }
        $emitted[] = $relativePath;
    }

    sort($emitted, SORT_STRING);
    return app_mobile_wrapper_target_emit_result(true, '', $normalizedTargetDir, $emitted, $packageResult['validation']);
}

/**
 * Build the first sample28 mobile wrapper C1 handoff packet.
 *
 * This is a sample-oriented proof packet, not a production mobile app contract.
 * Hashes are deterministic placeholders for the named source refs; a later
 * live artifact slice may replace them with file-content hashes.
 *
 * @return array<string,mixed>
 */
function app_mobile_wrapper_target_sample28_c1_handoff(): array
{
    $refs = [
        'openapi' => 'work/source-outputs/SAMPLE28/OPENAPI/openapi.json',
        'no_code_runtime' => 'work/source-outputs/SAMPLE28/NO-CODE-RUNTIME/runtime-preview.json',
        'screen_metadata' => 'work/source-outputs/SAMPLE28/NO-CODE-RUNTIME/screen-definition.json',
        'auth_policy' => 'docs/sso-application-user-standard.md',
        'react_bridge' => 'work/source-outputs/SAMPLE28/NO-CODE-REACT-BRIDGE/bridge-contract.json',
    ];

    return [
        'schema_version' => APP_MOBILE_APP_HANDOFF_SCHEMA_VERSION,
        'mutation_performed' => false,
        'project' => [
            'project_key' => 'SAMPLE28',
            'name' => 'Sample28 No-Code Data App MVP',
            'title' => 'Sample28 Mobile Wrapper C1 Proof',
        ],
        'source_artifacts' => app_mobile_wrapper_target_sample28_source_artifacts($refs),
        'platform_targets' => [
            ['target_key' => 'react_web_capacitor_ios_android', 'required_now' => true, 'role' => 'first C1 proof target'],
            ['target_key' => 'pwa', 'required_now' => false, 'role' => 'optional shared web target'],
            ['target_key' => 'flutter_input_packet', 'required_now' => false, 'role' => 'later input packet'],
            ['target_key' => 'react_native_input_packet', 'required_now' => false, 'role' => 'later input packet'],
            ['target_key' => 'direct_native_generation', 'required_now' => false, 'role' => 'non-goal'],
        ],
        'app_identity' => [
            'display_name' => 'Sample28',
            'bundle_id_placeholder' => 'com.example.sample28',
            'package_id_placeholder' => 'com.example.sample28',
            'environment' => 'local-proof',
        ],
        'auth' => [
            'mode' => 'oidc',
            'login_route' => '/login',
            'logout_route' => '/logout',
            'token_storage_policy' => 'do_not_store_tokens_in_handoff_packet',
            'redirect_or_deep_link_policy' => 'external owner configures callback/deep link',
        ],
        'api' => [
            'base_url_policy' => 'runtime configurable per environment',
            'error_envelope' => 'standard JSON error envelope',
            'endpoints' => [
                ['endpoint_key' => 'no_code_ticket.list', 'method' => 'GET', 'path' => '/runs/no-code/SAMPLE28/current/runtime-preview.json', 'response_ref' => 'runtime_preview.screens'],
                ['endpoint_key' => 'no_code_ticket.update', 'method' => 'POST', 'path' => '/runs/no-code/SAMPLE28/current/actions/update_no_code_ticket', 'response_ref' => 'no-code-runtime-action-intent-v0 result'],
            ],
        ],
        'screens' => [
            ['screen_key' => 'no_code_ticket_list', 'screen_type' => 'list', 'title' => 'Tickets', 'states' => ['loading', 'empty', 'error']],
            ['screen_key' => 'no_code_ticket_detail', 'screen_type' => 'detail', 'title' => 'Ticket Detail', 'states' => ['loading', 'not_found', 'error']],
            ['screen_key' => 'no_code_ticket_form', 'screen_type' => 'form', 'title' => 'Edit Ticket', 'states' => ['draft', 'submitting', 'validation_error', 'submitted']],
        ],
        'navigation' => [
            ['from' => 'no_code_ticket_list', 'to' => 'no_code_ticket_detail', 'trigger' => 'select_row'],
            ['from' => 'no_code_ticket_detail', 'to' => 'no_code_ticket_form', 'trigger' => 'edit'],
        ],
        'actions' => [
            [
                'action_key' => 'update_no_code_ticket',
                'kind' => 'submit',
                'endpoint_key' => 'no_code_ticket.update',
                'availability' => 'enabled_after_validation_and_policy',
                'safety' => 'safe_submit',
                'mutates' => true,
                'idempotency' => 'client_generated_request_key',
                'success_state' => 'success',
                'failure_state' => 'validation_failure',
            ],
        ],
        'validation' => [
            'field_rules' => [
                ['field_key' => 'title', 'required' => true],
                ['field_key' => 'body', 'required' => true],
            ],
            'action_rules' => [
                ['action_key' => 'update_no_code_ticket', 'rule' => 'required fields and server policy must pass'],
            ],
            'enforcement' => 'client displays server-authoritative validation and policy errors',
        ],
        'error_states' => [
            ['state_key' => 'success', 'user_message' => 'Saved.'],
            ['state_key' => 'validation_failure', 'user_message' => 'Please fix the highlighted fields.'],
            ['state_key' => 'auth_failure', 'user_message' => 'Please sign in again.'],
            ['state_key' => 'network_failure', 'user_message' => 'Network unavailable. Try again.'],
            ['state_key' => 'unavailable_action', 'user_message' => 'This action is not available.'],
        ],
        'native_capabilities' => [
            ['capability_key' => 'none', 'required' => false, 'reason' => 'C1 proof uses existing web/API behavior only.'],
        ],
        'offline_and_local_storage' => [
            'offline_sync' => false,
            'local_draft_policy' => 'generated React/Web preview keeps local form state only',
            'cache_policy' => 'short-lived runtime metadata cache only',
        ],
        'security_and_privacy' => [
            'secret_policy' => 'no secrets in packet',
            'pii_policy' => 'no production user data in packet',
            'token_persistence_policy' => 'external wrapper owner must choose secure token storage',
            'logging_policy' => 'do not log tokens or personal data',
        ],
        'build_handoff' => [
            'owned_by' => 'external mobile builder',
            'capacitor_setup_owner' => 'external mobile builder',
            'ios_build_owner' => 'external mobile builder',
            'android_build_owner' => 'external mobile builder',
            'signing_owner' => 'app owner',
            'store_submission_owner' => 'app owner',
        ],
        'verification_checklist' => [
            'source artifact refs are reviewed',
            'login flow understood',
            'list/detail/form routes mapped',
            'update action validation mapped',
            'React bridge build/browser smokes pass',
            'native capability declaration reviewed',
        ],
        'non_goals' => [
            'direct_native_generation',
            'app_store_signing',
            'offline_sync_by_default',
            'production_user_data_in_packet',
        ],
    ];
}

/** @return array<string,array{ref:string,sha256:string}> */
function app_mobile_wrapper_target_sample28_source_artifacts(array $refs): array
{
    $artifacts = [];
    foreach ($refs as $key => $ref) {
        $ref = (string) $ref;
        $artifacts[(string) $key] = [
            'ref' => $ref,
            'sha256' => hash('sha256', $ref),
        ];
    }
    return $artifacts;
}

/**
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_emit_sample28_c1_package(string $targetDir): array
{
    return app_mobile_wrapper_target_emit_c1_package(
        app_mobile_wrapper_target_sample28_c1_handoff(),
        $targetDir,
    );
}

/**
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_emit_sample28_react_app_handoff_proof(string $targetDir): array
{
    return app_mobile_wrapper_target_emit_react_app_handoff_proof(
        app_mobile_wrapper_target_sample28_c1_handoff(),
        $targetDir,
    );
}

/**
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_emit_sample28_later_platform_input_packets(string $targetDir): array
{
    return app_mobile_wrapper_target_emit_later_platform_input_packets(
        app_mobile_wrapper_target_sample28_c1_handoff(),
        $targetDir,
    );
}

/**
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_emit_sample28_bundle_manifest(string $targetDir): array
{
    return app_mobile_wrapper_target_emit_bundle_manifest(
        app_mobile_wrapper_target_sample28_c1_handoff(),
        $targetDir,
    );
}

/** @param array<string,mixed> $handoff @return array<string,mixed> */
function app_mobile_wrapper_target_contract_from_handoff(array $handoff): array
{
    $sourceArtifacts = is_array($handoff['source_artifacts'] ?? null) ? $handoff['source_artifacts'] : [];
    $screens = is_array($handoff['screens'] ?? null) ? $handoff['screens'] : [];
    $actions = is_array($handoff['actions'] ?? null) ? $handoff['actions'] : [];
    $nativeCapabilities = is_array($handoff['native_capabilities'] ?? null) ? $handoff['native_capabilities'] : [];

    return [
        'contract_schema_version' => APP_MOBILE_WRAPPER_TARGET_SCHEMA_VERSION,
        'target_key' => 'react_web_capacitor_ios_android',
        'mutation_performed' => false,
        'proof_stage' => 'C1_WRAPPER_READINESS',
        'input_handoff_schema_version' => (string) ($handoff['schema_version'] ?? ''),
        'source_artifacts' => $sourceArtifacts,
        'web_runtime' => [
            'no_code_runtime_ref' => (string) (($sourceArtifacts['no_code_runtime']['ref'] ?? '')),
            'screen_metadata_ref' => (string) (($sourceArtifacts['screen_metadata']['ref'] ?? '')),
            'react_bridge_available' => isset($sourceArtifacts['react_bridge']) && is_array($sourceArtifacts['react_bridge']),
            'react_bridge_ref' => (string) (($sourceArtifacts['react_bridge']['ref'] ?? '')),
        ],
        'react_adapter' => [
            'role' => 'reference_adapter_not_production_app_shell',
            'existing_bridge_contract_ref' => (string) (($sourceArtifacts['react_bridge']['ref'] ?? '')),
            'action_intent_authority' => 'server routes retain execution authority',
        ],
        'capacitor_boundary' => [
            'mtool_stage' => 'C1 only',
            'external_owner_stage' => 'C2/C3',
            'mtool_does_not_initialize_capacitor_project' => true,
            'mtool_does_not_build_native_targets' => true,
            'mtool_does_not_manage_signing_or_store_submission' => true,
        ],
        'auth_boundary' => app_mobile_wrapper_target_auth_boundary($handoff['auth'] ?? []),
        'api_boundary' => app_mobile_wrapper_target_api_boundary($handoff['api'] ?? []),
        'screen_flow_boundary' => [
            'screen_count' => count($screens),
            'screen_keys' => array_values(array_filter(array_map(
                static fn($screen): string => is_array($screen) ? (string) ($screen['screen_key'] ?? '') : '',
                $screens,
            ))),
            'screen_types' => array_values(array_unique(array_filter(array_map(
                static fn($screen): string => is_array($screen) ? (string) ($screen['screen_type'] ?? '') : '',
                $screens,
            )))),
            'navigation_count' => is_array($handoff['navigation'] ?? null) ? count($handoff['navigation']) : 0,
        ],
        'action_boundary' => [
            'action_count' => count($actions),
            'actions' => array_values(array_map('app_mobile_wrapper_target_action_summary', $actions)),
            'idempotency_required_for_mutation' => true,
            'mutation_authority' => 'server route authorization, CSRF, idempotency, and Transaction Full gates stay server-side',
        ],
        'native_capability_boundary' => [
            'capabilities' => array_values(array_map('app_mobile_wrapper_target_native_capability_summary', $nativeCapabilities)),
            'plugins_are_external_owner_choice' => true,
        ],
        'offline_boundary' => [
            'offline_sync' => (bool) (($handoff['offline_and_local_storage']['offline_sync'] ?? false)),
            'sync_contract_ref' => (string) (($handoff['offline_and_local_storage']['sync_contract_ref'] ?? '')),
            'offline_sync_default' => 'disabled_without_explicit_sync_contract',
        ],
        'security_boundary' => [
            'secret_policy' => (string) (($handoff['security_and_privacy']['secret_policy'] ?? '')),
            'token_persistence_policy' => (string) (($handoff['security_and_privacy']['token_persistence_policy'] ?? '')),
            'signing_and_certificates_owner' => 'external app owner',
        ],
        'verification' => [
            'stage' => 'C1',
            'gates' => APP_MOBILE_WRAPPER_TARGET_VERIFICATION_GATES,
        ],
        'non_goals' => array_values(array_map('strval', is_array($handoff['non_goals'] ?? null) ? $handoff['non_goals'] : [])),
    ];
}

/** @param mixed $auth @return array<string,mixed> */
function app_mobile_wrapper_target_auth_boundary(mixed $auth): array
{
    $auth = is_array($auth) ? $auth : [];
    return [
        'mode' => (string) ($auth['mode'] ?? ''),
        'login_route' => (string) ($auth['login_route'] ?? ''),
        'logout_route' => (string) ($auth['logout_route'] ?? ''),
        'token_storage_policy' => (string) ($auth['token_storage_policy'] ?? ''),
        'redirect_or_deep_link_policy' => (string) ($auth['redirect_or_deep_link_policy'] ?? ''),
        'secure_token_storage_implementation_owner' => 'external wrapper owner',
    ];
}

/** @param mixed $api @return array<string,mixed> */
function app_mobile_wrapper_target_api_boundary(mixed $api): array
{
    $api = is_array($api) ? $api : [];
    return [
        'base_url_policy' => (string) ($api['base_url_policy'] ?? ''),
        'error_envelope' => (string) ($api['error_envelope'] ?? ''),
        'endpoint_count' => is_array($api['endpoints'] ?? null) ? count($api['endpoints']) : 0,
        'api_client_implementation_owner' => 'external wrapper owner',
    ];
}

/** @param mixed $action @return array<string,mixed> */
function app_mobile_wrapper_target_action_summary(mixed $action): array
{
    $action = is_array($action) ? $action : [];
    return [
        'action_key' => (string) ($action['action_key'] ?? ''),
        'kind' => (string) ($action['kind'] ?? ''),
        'endpoint_key' => (string) ($action['endpoint_key'] ?? ''),
        'mutates' => (bool) ($action['mutates'] ?? false),
        'idempotency' => (string) ($action['idempotency'] ?? ''),
        'availability' => (string) ($action['availability'] ?? ''),
    ];
}

/** @param mixed $capability @return array<string,mixed> */
function app_mobile_wrapper_target_native_capability_summary(mixed $capability): array
{
    $capability = is_array($capability) ? $capability : [];
    return [
        'capability_key' => (string) ($capability['capability_key'] ?? ''),
        'required' => (bool) ($capability['required'] ?? false),
        'reason' => (string) ($capability['reason'] ?? ''),
    ];
}

/**
 * @param array<string,mixed> $contract
 * @param array<string,mixed> $handoff
 * @return array<string,mixed>
 */
function app_mobile_wrapper_target_react_app_handoff_from_contract(array $contract, array $handoff): array
{
    return [
        'schema_version' => APP_MOBILE_REACT_WRAPPER_APP_HANDOFF_SCHEMA_VERSION,
        'target_key' => 'react_web_capacitor_ios_android',
        'proof_stage' => 'MW2_REACT_WRAPPER_APP_HANDOFF',
        'mutation_performed' => false,
        'project' => is_array($handoff['project'] ?? null) ? $handoff['project'] : [],
        'source_contract' => [
            'contract_schema_version' => (string) ($contract['contract_schema_version'] ?? ''),
            'proof_stage' => (string) ($contract['proof_stage'] ?? ''),
            'input_handoff_schema_version' => (string) ($contract['input_handoff_schema_version'] ?? ''),
        ],
        'source_artifacts' => is_array($contract['source_artifacts'] ?? null) ? $contract['source_artifacts'] : [],
        'react_app_boundary' => [
            'mtool_provides' => [
                'validated mobile handoff metadata',
                'no-code runtime and screen metadata refs',
                'React bridge reference adapter contract when available',
                'auth/API/action/error/native capability boundaries',
            ],
            'external_wrapper_owner_owns' => [
                'React app shell and routing',
                'durable frontend state management',
                'SSO/OIDC client configuration',
                'secure token storage implementation',
                'API client and retry strategy',
                'visual design and production component system',
            ],
            'not_a_generated_production_app' => true,
        ],
        'capacitor_preparation_boundary' => [
            'first_ios_android_route' => 'Capacitor-style wrapper around React/Web output',
            'mtool_does_not_initialize_capacitor_project' => true,
            'mtool_does_not_create_native_project_files' => true,
            'mtool_does_not_manage_signing_or_store_submission' => true,
            'external_owner_next_checks' => [
                'choose web build output directory',
                'choose app IDs and deep link policy',
                'choose secure storage plugin if tokens are persisted',
                'run web build and capacitor sync in the external project',
                'perform simulator/device QA',
            ],
        ],
        'auth_mapping' => is_array($contract['auth_boundary'] ?? null) ? $contract['auth_boundary'] : [],
        'api_mapping' => is_array($contract['api_boundary'] ?? null) ? $contract['api_boundary'] : [],
        'screen_flow_mapping' => is_array($contract['screen_flow_boundary'] ?? null) ? $contract['screen_flow_boundary'] : [],
        'action_mapping' => is_array($contract['action_boundary'] ?? null) ? $contract['action_boundary'] : [],
        'implementation_checklist' => [
            'review source artifact refs and hashes',
            'map login/logout and callback/deep-link behavior',
            'map API base URL per environment',
            'map list/detail/form screens and navigation',
            'wire action intents to server-authoritative endpoints',
            'preserve idempotency for mutating actions',
            'display validation/auth/network/unavailable-action errors',
            'keep server-side authorization, CSRF, and Transaction Full gates authoritative',
            'run React bridge build/browser smokes before Capacitor wrapping',
        ],
        'verification' => [
            'required_before_capacitor' => [
                'make sample28-no-code-react-bridge-build-smoke',
                'make sample28-no-code-react-bridge-browser-smoke',
            ],
            'native_proof_not_claimed' => true,
        ],
        'later_targets' => [
            'flutter_input_packet',
            'react_native_input_packet',
        ],
        'non_goals' => array_values(array_map('strval', is_array($contract['non_goals'] ?? null) ? $contract['non_goals'] : [])),
    ];
}

/** @param array<string,mixed> $proof */
function app_mobile_wrapper_target_react_app_handoff_markdown(array $proof): string
{
    $lines = [
        '# React/Web Wrapper App Handoff',
        '',
        '## Target',
        '',
        '- Schema: `' . (string) ($proof['schema_version'] ?? '') . '`',
        '- Target: `' . (string) ($proof['target_key'] ?? '') . '`',
        '- Proof stage: `' . (string) ($proof['proof_stage'] ?? '') . '`',
        '',
        '## Boundary',
        '',
        '- Mtool provides validated handoff metadata and reference artifacts.',
        '- The external wrapper owner builds the React app shell and Capacitor project.',
        '- Mtool does not create native project files, signing files, or store submission files.',
        '',
        '## Implementation checklist',
        '',
    ];
    foreach (($proof['implementation_checklist'] ?? []) as $item) {
        $lines[] = '- ' . (string) $item;
    }
    $lines[] = '';
    $lines[] = '## Required before Capacitor';
    $lines[] = '';
    foreach (($proof['verification']['required_before_capacitor'] ?? []) as $gate) {
        $lines[] = '- `' . (string) $gate . '`';
    }
    $lines[] = '';
    $lines[] = '## Later targets';
    $lines[] = '';
    foreach (($proof['later_targets'] ?? []) as $target) {
        $lines[] = '- `' . (string) $target . '`';
    }
    $lines[] = '';
    return implode("\n", $lines);
}

/**
 * @param array<string,mixed> $reactProof
 * @return array<string,mixed>
 */
function app_mobile_wrapper_target_later_platform_packet_from_react_proof(string $platform, array $reactProof): array
{
    $platformKey = $platform === 'flutter' ? 'flutter_input_packet' : 'react_native_input_packet';
    $builder = $platform === 'flutter' ? 'Flutter/Dart builder' : 'React Native builder';
    return [
        'schema_version' => APP_MOBILE_LATER_PLATFORM_INPUT_PACKET_SCHEMA_VERSION,
        'platform_key' => $platformKey,
        'source_react_wrapper_handoff_schema_version' => (string) ($reactProof['schema_version'] ?? ''),
        'mutation_performed' => false,
        'project' => is_array($reactProof['project'] ?? null) ? $reactProof['project'] : [],
        'source_artifacts' => is_array($reactProof['source_artifacts'] ?? null) ? $reactProof['source_artifacts'] : [],
        'builder_owner' => $builder,
        'mtool_role' => 'structured_input_packet_only',
        'shared_app_contract' => [
            'auth_mapping' => is_array($reactProof['auth_mapping'] ?? null) ? $reactProof['auth_mapping'] : [],
            'api_mapping' => is_array($reactProof['api_mapping'] ?? null) ? $reactProof['api_mapping'] : [],
            'screen_flow_mapping' => is_array($reactProof['screen_flow_mapping'] ?? null) ? $reactProof['screen_flow_mapping'] : [],
            'action_mapping' => is_array($reactProof['action_mapping'] ?? null) ? $reactProof['action_mapping'] : [],
        ],
        'implementation_guidance' => [
            'preserve server-authoritative auth, validation, and mutation policy',
            'preserve idempotency for mutating actions',
            'map list/detail/form screens before adding platform-specific UI polish',
            'keep secure token storage choice in the downstream app owner scope',
            'treat offline sync as disabled unless a sync contract is explicitly present',
        ],
        'not_generated_by_mtool' => [
            'Dart source code',
            'React Native source code',
            'iOS project',
            'Android project',
            'signing files',
            'store submission files',
        ],
        'non_goals' => is_array($reactProof['non_goals'] ?? null) ? $reactProof['non_goals'] : [],
    ];
}

/** @param list<array<string,mixed>> $packets */
function app_mobile_wrapper_target_later_platform_packets_markdown(array $packets): string
{
    $lines = [
        '# Later Platform Input Packets',
        '',
        'These packets are structured inputs for downstream Flutter and React Native builders.',
        'Mtool does not generate or own Flutter, React Native, iOS, Android, signing, or store-submission projects here.',
        '',
        '## Packets',
        '',
    ];
    foreach ($packets as $packet) {
        $lines[] = '- `' . (string) ($packet['platform_key'] ?? '') . '` for ' . (string) ($packet['builder_owner'] ?? '');
    }
    $lines[] = '';
    $lines[] = '## Shared rules';
    $lines[] = '';
    $lines[] = '- Preserve server-authoritative auth, validation, mutation policy, CSRF/idempotency where applicable.';
    $lines[] = '- Keep offline sync disabled unless an explicit sync contract exists.';
    $lines[] = '- Do not put secrets or production user data into generated packets.';
    $lines[] = '';
    return implode("\n", $lines);
}

/**
 * @param array<string,mixed> $handoff
 * @param array<string,mixed> $c1Package
 * @param array<string,mixed> $reactPackage
 * @param array<string,mixed> $platformsPackage
 * @return array<string,mixed>
 */
function app_mobile_wrapper_target_bundle_manifest_from_packages(
    array $handoff,
    array $c1Package,
    array $reactPackage,
    array $platformsPackage,
): array {
    return [
        'schema_version' => APP_MOBILE_WRAPPER_BUNDLE_MANIFEST_SCHEMA_VERSION,
        'mutation_performed' => false,
        'project' => is_array($handoff['project'] ?? null) ? $handoff['project'] : [],
        'artifact_order' => [
            'c1_wrapper_readiness',
            'react_wrapper_app_handoff',
            'later_platform_input_packets',
        ],
        'artifacts' => [
            [
                'artifact_key' => 'c1_wrapper_readiness',
                'path_hint' => (string) ($c1Package['path_hint'] ?? ''),
                'files' => array_values(array_map('strval', array_keys(is_array($c1Package['files'] ?? null) ? $c1Package['files'] : []))),
                'purpose' => 'reviewable wrapper-readiness contract and consumer notes',
            ],
            [
                'artifact_key' => 'react_wrapper_app_handoff',
                'path_hint' => (string) ($reactPackage['path_hint'] ?? ''),
                'files' => array_values(array_map('strval', array_keys(is_array($reactPackage['files'] ?? null) ? $reactPackage['files'] : []))),
                'purpose' => 'React/Web wrapper app handoff before Capacitor preparation',
            ],
            [
                'artifact_key' => 'later_platform_input_packets',
                'path_hint' => (string) ($platformsPackage['path_hint'] ?? ''),
                'files' => array_values(array_map('strval', array_keys(is_array($platformsPackage['files'] ?? null) ? $platformsPackage['files'] : []))),
                'purpose' => 'structured Flutter and React Native input packets after React/Web proof',
            ],
        ],
        'ownership_boundary' => [
            'mtool_owns' => 'metadata, validation, package manifests, and structured handoff packets',
            'external_owner_owns' => 'React app shell, Capacitor project, Flutter app, React Native app, native builds, signing, and store submission',
        ],
        'not_generated_by_mtool' => [
            'production React app',
            'Capacitor project',
            'Flutter project',
            'React Native project',
            'iOS project',
            'Android project',
            'signing files',
            'store submission files',
        ],
    ];
}

/** @param array<string,mixed> $manifest */
function app_mobile_wrapper_target_bundle_manifest_markdown(array $manifest): string
{
    $lines = [
        '# Mobile Wrapper Bundle Manifest',
        '',
        '- Schema: `' . (string) ($manifest['schema_version'] ?? '') . '`',
        '- Mutation performed: `' . (($manifest['mutation_performed'] ?? true) ? 'true' : 'false') . '`',
        '',
        '## Artifact order',
        '',
    ];
    foreach (($manifest['artifact_order'] ?? []) as $item) {
        $lines[] = '- `' . (string) $item . '`';
    }
    $lines[] = '';
    $lines[] = '## Boundary';
    $lines[] = '';
    $lines[] = '- Mtool owns metadata, validation, manifests, and structured handoff packets.';
    $lines[] = '- External builders own app projects, native builds, signing, and store distribution.';
    $lines[] = '';
    return implode("\n", $lines);
}

/** @param array<string,mixed> $contract */
function app_mobile_wrapper_target_consumer_notes_markdown(array $contract): string
{
    $lines = [
        '# Mobile Wrapper Target Consumer Notes',
        '',
        '## Target',
        '',
        '- Contract schema: `' . (string) ($contract['contract_schema_version'] ?? '') . '`',
        '- Target key: `' . (string) ($contract['target_key'] ?? '') . '`',
        '- Proof stage: `' . (string) ($contract['proof_stage'] ?? '') . '`',
        '',
        '## Ownership Boundary',
        '',
        '- Mtool owns C1 wrapper-readiness metadata and source artifact references.',
        '- The external wrapper owner owns React app shell, Capacitor setup, native build, signing, device QA, and store distribution.',
        '- Server routes retain mutation authority, authorization, CSRF, idempotency, and Transaction Full gates.',
        '',
        '## Verification Gates',
        '',
    ];
    foreach (($contract['verification']['gates'] ?? []) as $gate) {
        $lines[] = '- `' . (string) $gate . '`';
    }
    $lines[] = '';
    $lines[] = '## Non-goals';
    $lines[] = '';
    foreach (($contract['non_goals'] ?? []) as $nonGoal) {
        $lines[] = '- `' . (string) $nonGoal . '`';
    }
    $lines[] = '';
    return implode("\n", $lines);
}

/** @param array<string,mixed> $value */
function app_mobile_wrapper_target_json_text(array $value): string
{
    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
}

/**
 * @param list<string> $files
 * @param array<string,mixed> $validation
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_emit_result(bool $ok, string $error, string $targetDir, array $files, array $validation): array
{
    return [
        'ok' => $ok,
        'error' => $error,
        'target_dir' => $targetDir,
        'files' => $files,
        'validation' => $validation,
    ];
}
