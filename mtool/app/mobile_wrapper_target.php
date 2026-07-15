<?php

declare(strict_types=1);

require_once __DIR__ . '/mobile_app_handoff.php';

const APP_MOBILE_WRAPPER_TARGET_SCHEMA_VERSION = 'mobile-react-wrapper-target-v1';
const APP_MOBILE_REACT_WRAPPER_APP_HANDOFF_SCHEMA_VERSION = 'mobile-react-wrapper-app-handoff-v1';
const APP_MOBILE_EXTERNAL_OPTIONAL_OUTPUT_SCHEMA_VERSION = 'mobile-external-optional-output-v1';
const APP_MOBILE_EXTERNAL_AI_TASK_PACKET_SCHEMA_VERSION = 'mobile-external-ai-task-packet-v1';
const APP_MOBILE_OUTPUT_MODE_CONFIG_SCHEMA_VERSION = 'mobile-output-mode-config-v1';
const APP_MOBILE_PWA_READINESS_SCHEMA_VERSION = 'mobile-pwa-readiness-v1';
const APP_MOBILE_LATER_PLATFORM_INPUT_PACKET_SCHEMA_VERSION = 'mobile-later-platform-input-packet-v1';
const APP_MOBILE_WRAPPER_BUNDLE_MANIFEST_SCHEMA_VERSION = 'mobile-wrapper-bundle-manifest-v1';
const APP_MOBILE_OUTPUT_MODES = ['mtool_no_code', 'external_no_code', 'hybrid'];
const APP_MOBILE_APP_SURFACES = ['pwa', 'flutter_webview', 'react_web_capacitor'];
const APP_MOBILE_WRAPPER_TARGET_VERIFICATION_GATES = [
    'php -l mtool/app/mobile_app_handoff.php',
    'focused MobileAppHandoffTest',
    'make sample28-no-code-react-bridge-build-smoke',
    'make sample28-no-code-react-bridge-browser-smoke',
    'git diff --check',
];

/**
 * Build a small output-mode config packet so users and AI consumers can see
 * which surface Mtool is preparing before choosing artifact generation.
 *
 * @param array<string,mixed> $handoff
 * @return array{ok:bool,error:string,package:array<string,mixed>|null,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_build_output_mode_config(array $handoff, string $mode = 'hybrid'): array
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

    $mode = app_mobile_wrapper_target_normalize_output_mode($mode);
    if ($mode === '') {
        return [
            'ok' => false,
            'error' => 'unsupported output mode',
            'package' => null,
            'validation' => $validation,
        ];
    }

    $config = app_mobile_wrapper_target_output_mode_config_from_handoff($handoff, $mode);

    return [
        'ok' => true,
        'error' => '',
        'package' => [
            'path_hint' => 'output-mode-config/',
            'mutation_performed' => false,
            'files' => [
                'output-mode-config.json' => $config,
                'OUTPUT-MODE-CONFIG.md' => app_mobile_wrapper_target_output_mode_config_markdown($config),
            ],
        ],
        'validation' => $validation,
    ];
}

/**
 * @param array<string,mixed> $handoff
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_emit_output_mode_config(array $handoff, string $targetDir, string $mode = 'hybrid'): array
{
    $normalizedTargetDir = rtrim($targetDir, DIRECTORY_SEPARATOR);
    if ($normalizedTargetDir === '' || $normalizedTargetDir === '.' || $normalizedTargetDir === DIRECTORY_SEPARATOR) {
        return app_mobile_wrapper_target_emit_result(false, 'target directory is not a controlled artifact directory', $normalizedTargetDir, [], []);
    }

    $packageResult = app_mobile_wrapper_target_build_output_mode_config($handoff, $mode);
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
 * Build PWA readiness metadata/checklist for web/no-code and React/Web wrapper
 * consumers. This does not generate a manifest, service worker, offline sync, or
 * external app project.
 *
 * @param array<string,mixed> $handoff
 * @return array{ok:bool,error:string,package:array<string,mixed>|null,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_build_pwa_readiness(array $handoff): array
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

    $packet = app_mobile_wrapper_target_pwa_readiness_from_handoff($handoff);

    return [
        'ok' => true,
        'error' => '',
        'package' => [
            'path_hint' => 'pwa-readiness/',
            'mutation_performed' => false,
            'files' => [
                'pwa-readiness.json' => $packet,
                'PWA-READINESS.md' => app_mobile_wrapper_target_pwa_readiness_markdown($packet),
            ],
        ],
        'validation' => $validation,
    ];
}

/**
 * @param array<string,mixed> $handoff
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_emit_pwa_readiness(array $handoff, string $targetDir): array
{
    $normalizedTargetDir = rtrim($targetDir, DIRECTORY_SEPARATOR);
    if ($normalizedTargetDir === '' || $normalizedTargetDir === '.' || $normalizedTargetDir === DIRECTORY_SEPARATOR) {
        return app_mobile_wrapper_target_emit_result(false, 'target directory is not a controlled artifact directory', $normalizedTargetDir, [], []);
    }

    $packageResult = app_mobile_wrapper_target_build_pwa_readiness($handoff);
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
 * Build the EF-M2 optional external no-code/tool output packet.
 *
 * This packet is additive: it keeps mtool_no_code as the supported baseline and
 * describes how a React/Web + Capacitor-style external consumer can consume the
 * same contract. It does not create, overwrite, or initialize an external app.
 *
 * @param array<string,mixed> $handoff
 * @return array{ok:bool,error:string,package:array<string,mixed>|null,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_build_external_optional_output_packet(array $handoff): array
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
    $packet = app_mobile_wrapper_target_external_optional_output_from_react_proof($proof, $handoff);

    return [
        'ok' => true,
        'error' => '',
        'package' => [
            'path_hint' => 'react-web-capacitor-output/',
            'mutation_performed' => false,
            'files' => [
                'external-output.json' => $packet,
                'EXTERNAL-OUTPUT.md' => app_mobile_wrapper_target_external_optional_output_markdown($packet),
            ],
        ],
        'validation' => $reactProof['validation'],
    ];
}

/**
 * @param array<string,mixed> $handoff
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_emit_external_optional_output_packet(array $handoff, string $targetDir): array
{
    $normalizedTargetDir = rtrim($targetDir, DIRECTORY_SEPARATOR);
    if ($normalizedTargetDir === '' || $normalizedTargetDir === '.' || $normalizedTargetDir === DIRECTORY_SEPARATOR) {
        return app_mobile_wrapper_target_emit_result(false, 'target directory is not a controlled artifact directory', $normalizedTargetDir, [], []);
    }

    $packageResult = app_mobile_wrapper_target_build_external_optional_output_packet($handoff);
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
 * Build an AI/Codex/Claude-readable task packet around the optional
 * external-output contract.
 *
 * This is not an AI execution path. It emits only a pending task packet that
 * tells the agent what to read, what to explain, what confirmation to ask for,
 * and what it must not do without explicit user approval.
 *
 * @param array<string,mixed> $handoff
 * @return array{ok:bool,error:string,package:array<string,mixed>|null,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_build_external_ai_task_packet(array $handoff): array
{
    $external = app_mobile_wrapper_target_build_external_optional_output_packet($handoff);
    if (!$external['ok'] || !is_array($external['package'])) {
        return [
            'ok' => false,
            'error' => $external['error'],
            'package' => null,
            'validation' => $external['validation'],
        ];
    }

    $files = is_array($external['package']['files'] ?? null) ? $external['package']['files'] : [];
    $externalOutput = is_array($files['external-output.json'] ?? null) ? $files['external-output.json'] : [];
    $task = app_mobile_wrapper_target_external_ai_task_packet_from_external_output($externalOutput, $handoff);

    return [
        'ok' => true,
        'error' => '',
        'package' => [
            'path_hint' => 'ai-task-packet/',
            'mutation_performed' => false,
            'files' => [
                'task.json' => $task,
                'TASK.md' => app_mobile_wrapper_target_external_ai_task_markdown($task),
                'input/external-output.json' => $externalOutput,
                'input/mobile-app-handoff.json' => $handoff,
            ],
        ],
        'validation' => $external['validation'],
    ];
}

/**
 * @param array<string,mixed> $handoff
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_emit_external_ai_task_packet(array $handoff, string $targetDir): array
{
    $normalizedTargetDir = rtrim($targetDir, DIRECTORY_SEPARATOR);
    if ($normalizedTargetDir === '' || $normalizedTargetDir === '.' || $normalizedTargetDir === DIRECTORY_SEPARATOR) {
        return app_mobile_wrapper_target_emit_result(false, 'target directory is not a controlled artifact directory', $normalizedTargetDir, [], []);
    }

    $packageResult = app_mobile_wrapper_target_build_external_ai_task_packet($handoff);
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
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0777, true)) {
            return app_mobile_wrapper_target_emit_result(false, 'failed to create package directory: ' . dirname($relativePath), $normalizedTargetDir, $emitted, $packageResult['validation']);
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
function app_mobile_wrapper_target_build_bundle_manifest(array $handoff, string $mode = 'hybrid'): array
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

    $external = app_mobile_wrapper_target_build_external_optional_output_packet($handoff);
    if (!$external['ok'] || !is_array($external['package'])) {
        return [
            'ok' => false,
            'error' => $external['error'],
            'package' => null,
            'validation' => $external['validation'],
        ];
    }

    $aiTask = app_mobile_wrapper_target_build_external_ai_task_packet($handoff);
    if (!$aiTask['ok'] || !is_array($aiTask['package'])) {
        return [
            'ok' => false,
            'error' => $aiTask['error'],
            'package' => null,
            'validation' => $aiTask['validation'],
        ];
    }

    $outputMode = app_mobile_wrapper_target_build_output_mode_config($handoff, $mode);
    if (!$outputMode['ok'] || !is_array($outputMode['package'])) {
        return [
            'ok' => false,
            'error' => $outputMode['error'],
            'package' => null,
            'validation' => $outputMode['validation'],
        ];
    }

    $pwa = app_mobile_wrapper_target_build_pwa_readiness($handoff);
    if (!$pwa['ok'] || !is_array($pwa['package'])) {
        return [
            'ok' => false,
            'error' => $pwa['error'],
            'package' => null,
            'validation' => $pwa['validation'],
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

    $manifest = app_mobile_wrapper_target_bundle_manifest_from_packages($handoff, $c1['package'], $react['package'], $external['package'], $aiTask['package'], $outputMode['package'], $pwa['package'], $platforms['package']);

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
function app_mobile_wrapper_target_emit_bundle_manifest(array $handoff, string $targetDir, string $mode = 'hybrid'): array
{
    $normalizedTargetDir = rtrim($targetDir, DIRECTORY_SEPARATOR);
    if ($normalizedTargetDir === '' || $normalizedTargetDir === '.' || $normalizedTargetDir === DIRECTORY_SEPARATOR) {
        return app_mobile_wrapper_target_emit_result(false, 'target directory is not a controlled artifact directory', $normalizedTargetDir, [], []);
    }

    $packageResult = app_mobile_wrapper_target_build_bundle_manifest($handoff, $mode);
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
function app_mobile_wrapper_target_emit_sample28_external_optional_output_packet(string $targetDir): array
{
    return app_mobile_wrapper_target_emit_external_optional_output_packet(
        app_mobile_wrapper_target_sample28_c1_handoff(),
        $targetDir,
    );
}

/**
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_emit_sample28_external_ai_task_packet(string $targetDir): array
{
    return app_mobile_wrapper_target_emit_external_ai_task_packet(
        app_mobile_wrapper_target_sample28_c1_handoff(),
        $targetDir,
    );
}

/**
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_emit_sample28_output_mode_config(string $targetDir, string $mode = 'hybrid'): array
{
    return app_mobile_wrapper_target_emit_output_mode_config(
        app_mobile_wrapper_target_sample28_c1_handoff(),
        $targetDir,
        $mode,
    );
}

/**
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>}
 */
function app_mobile_wrapper_target_emit_sample28_pwa_readiness(string $targetDir): array
{
    return app_mobile_wrapper_target_emit_pwa_readiness(
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
function app_mobile_wrapper_target_emit_sample28_bundle_manifest(string $targetDir, string $mode = 'hybrid'): array
{
    return app_mobile_wrapper_target_emit_bundle_manifest(
        app_mobile_wrapper_target_sample28_c1_handoff(),
        $targetDir,
        $mode,
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
 * @param array<string,mixed> $handoff
 * @return array<string,mixed>
 */
function app_mobile_wrapper_target_external_optional_output_from_react_proof(array $reactProof, array $handoff): array
{
    return [
        'schema_version' => APP_MOBILE_EXTERNAL_OPTIONAL_OUTPUT_SCHEMA_VERSION,
        'mode' => 'external_no_code',
        'target' => 'react_web_capacitor',
        'target_label' => 'React/Web + Capacitor-style optional output',
        'mutation_performed' => false,
        'baseline' => [
            'keeps_mtool_no_code' => true,
            'mtool_runtime_role' => 'supported_baseline_reference_and_fallback',
            'replacement_claim' => false,
        ],
        'project_identity' => is_array($handoff['project'] ?? null) ? $handoff['project'] : [],
        'source_artifacts' => is_array($reactProof['source_artifacts'] ?? null) ? $reactProof['source_artifacts'] : [],
        'screens' => is_array($reactProof['screen_flow_mapping'] ?? null) ? $reactProof['screen_flow_mapping'] : [],
        'actions' => is_array($reactProof['action_mapping'] ?? null) ? $reactProof['action_mapping'] : [],
        'readiness' => [
            'source' => 'Mtool generated no-code metadata and server policy',
            'external_ui_may_render' => true,
            'server_remains_authoritative' => true,
        ],
        'server_authority' => [
            'authorization' => 'Mtool/server-owned',
            'csrf' => 'Mtool/server-owned',
            'idempotency' => 'Mtool/server-owned for mutating actions',
            'transaction_full' => 'Mtool/server-owned where same-DB composite operations require it',
            'audit' => 'Mtool/server-owned',
            'outbox_processing' => 'Mtool/server-owned',
            'approval_current_alias_policy' => 'Mtool-owned',
        ],
        'ownership_boundary' => [
            'contract_owned_core' => [
                'source artifact refs and hashes',
                'screen/action/readiness metadata',
                'validation map',
                'server authority boundary',
                'non-goals',
            ],
            'external_custom_extension_owned' => [
                'React app shell',
                'routing implementation',
                'component system',
                'form binding implementation',
                'API client and retry strategy',
                'Capacitor project and native preparation',
                'dependency installation',
                'native build, signing, and store submission',
            ],
        ],
        'requires_user_confirmation' => [
            'create external app project',
            'install dependencies',
            'initialize Capacitor',
            'create or overwrite files outside Mtool artifact roots',
            'choose persistent token storage',
            'add native plugins/modules',
            'enable offline storage or sync',
            'configure signing/store submission',
        ],
        'forbidden_without_artifact' => [
            'offline sync',
            'local persistent business-data storage',
            'refresh-token persistence',
            'native plugin selection',
            'app signing',
            'store submission',
            'production frontend architecture',
            'external app overwrite',
            'automatic dependency installation',
        ],
        'validation' => [
            'required_gates' => [
                'php -l mtool/app/mobile_wrapper_target.php',
                'focused MobileWrapperTargetTest',
                'git diff --check',
            ],
            'consumer_gates' => is_array($reactProof['verification']['required_before_capacitor'] ?? null)
                ? $reactProof['verification']['required_before_capacitor']
                : [],
        ],
        'non_goals' => array_values(array_unique(array_merge(
            array_map('strval', is_array($reactProof['non_goals'] ?? null) ? $reactProof['non_goals'] : []),
            [
                'replace_mtool_no_code_runtime',
                'initialize_capacitor_project',
                'install_dependencies',
                'generate_production_react_app',
                'native_build_or_signing',
            ],
        ))),
    ];
}

/** @param array<string,mixed> $packet */
function app_mobile_wrapper_target_external_optional_output_markdown(array $packet): string
{
    $lines = [
        '# External Optional Output',
        '',
        '- Schema: `' . (string) ($packet['schema_version'] ?? '') . '`',
        '- Mode: `' . (string) ($packet['mode'] ?? '') . '`',
        '- Target: `' . (string) ($packet['target'] ?? '') . '`',
        '',
        '## Boundary',
        '',
        '- This is optional external output, not a migration away from `mtool_no_code`.',
        '- Mtool owns the contract, validation, and server authority boundary.',
        '- The external owner owns React app shell, Capacitor setup, dependencies, native build, signing, and store submission.',
        '',
        '## Requires user confirmation',
        '',
    ];
    foreach (($packet['requires_user_confirmation'] ?? []) as $item) {
        $lines[] = '- ' . (string) $item;
    }
    $lines[] = '';
    $lines[] = '## Forbidden without explicit artifact';
    $lines[] = '';
    foreach (($packet['forbidden_without_artifact'] ?? []) as $item) {
        $lines[] = '- ' . (string) $item;
    }
    $lines[] = '';
    $lines[] = '## Non-goals';
    $lines[] = '';
    foreach (($packet['non_goals'] ?? []) as $item) {
        $lines[] = '- `' . (string) $item . '`';
    }
    $lines[] = '';
    return implode("\n", $lines);
}

/**
 * @param array<string,mixed> $externalOutput
 * @param array<string,mixed> $handoff
 * @return array<string,mixed>
 */
function app_mobile_wrapper_target_external_ai_task_packet_from_external_output(array $externalOutput, array $handoff): array
{
    $externalBytes = app_mobile_wrapper_target_json_text($externalOutput);
    $handoffBytes = app_mobile_wrapper_target_json_text($handoff);
    $externalHash = hash('sha256', $externalBytes);
    $handoffHash = hash('sha256', $handoffBytes);
    $project = is_array($handoff['project'] ?? null) ? $handoff['project'] : [];
    $projectKey = (string) ($project['project_key'] ?? 'PROJECT');
    $taskId = 'external-no-code-react-web-capacitor-' . app_mobile_wrapper_target_task_id_slug($projectKey) . '-' . substr($externalHash, 0, 12);

    return [
        'task_version' => APP_MOBILE_EXTERNAL_AI_TASK_PACKET_SCHEMA_VERSION,
        'task_id' => $taskId,
        'project_key' => $projectKey,
        'operation' => 'external_no_code_app_builder_task',
        'state' => 'pending_user_confirmation',
        'mode' => (string) ($externalOutput['mode'] ?? 'external_no_code'),
        'target' => (string) ($externalOutput['target'] ?? 'react_web_capacitor'),
        'inputs' => [
            'external_output' => [
                'path' => 'input/external-output.json',
                'media_type' => 'application/json',
                'sha256' => $externalHash,
                'authority' => 'mtool_external_output_contract',
            ],
            'mobile_app_handoff' => [
                'path' => 'input/mobile-app-handoff.json',
                'media_type' => 'application/json',
                'sha256' => $handoffHash,
                'authority' => 'mtool_handoff_context',
            ],
        ],
        'precedence' => ['external_output', 'mobile_app_handoff'],
        'allowed_reads' => ['task.json', 'TASK.md', 'input/external-output.json', 'input/mobile-app-handoff.json'],
        'allowed_writes_before_confirmation' => [],
        'allowed_writes_after_confirmation' => [
            'user_confirmed_external_app_project_or_user_confirmed_task_output_directory_only',
        ],
        'confirmation' => [
            'required' => true,
            'prompt' => 'Mtoolのexternal-outputとhandoffを読み、React/Web + Capacitor向け外部アプリ作成方針を説明します。依存install、Capacitor初期化、既存ファイル上書き、native build、signing、store submissionはまだ行いません。次に、ユーザが指定する外部アプリ用ディレクトリへ進めてよいですか？',
        ],
        'agent_instructions' => [
            'read_task_json_first' => true,
            'explain_inputs_and_boundaries_before_writing' => true,
            'ask_confirmation_once_with_declared_prompt' => true,
            'do_not_treat_previous_generic_continue_as_approval' => true,
            'keep_mtool_no_code_as_baseline' => true,
            'treat_external_no_code_as_optional_additive_output' => true,
            'server_authority_remains_mtool_owned' => true,
        ],
        'prohibitions_without_explicit_user_confirmation' => [
            'dependency_install',
            'network',
            'capacitor_init',
            'cap_sync',
            'native_project_generation',
            'overwrite_existing_external_app_files',
            'token_storage_choice',
            'offline_sync',
            'native_plugin_selection',
            'native_build',
            'signing',
            'store_submission',
            'mtool_metadata_write',
            'database_write',
        ],
        'suggested_first_response' => [
            'summarize_target' => 'React/Web + Capacitor optional external output',
            'summarize_boundary' => 'Mtool provides contracts and server authority boundaries; the external owner owns the app shell, dependencies, Capacitor/native project, build, signing, and store submission.',
            'ask_confirmation' => true,
        ],
        'validation' => [
            'mtool_static_gates' => [
                'php -l mtool/app/mobile_wrapper_target.php',
                'focused MobileWrapperTargetTest',
                'git diff --check',
            ],
            'consumer_reference_gates' => is_array($externalOutput['validation']['consumer_gates'] ?? null)
                ? $externalOutput['validation']['consumer_gates']
                : [],
            'external_app_gates_after_user_confirmation' => [
                'user_or_agent_defined_after_target_directory_selection',
                'do_not_run_dependency_installing_or_native_commands until user confirms',
            ],
        ],
        'completion_report' => [
            'task_id',
            'user_confirmed_target_directory',
            'files_created_or_changed',
            'validation_commands_run',
            'commands_not_run_because_confirmation_required',
            'mutation_performed',
        ],
        'mutation_performed' => false,
    ];
}

/** @param array<string,mixed> $task */
function app_mobile_wrapper_target_external_ai_task_markdown(array $task): string
{
    $lines = [
        '# Mtool AI Task: External No-Code React/Web + Capacitor',
        '',
        'Status: `pending_user_confirmation`',
        '',
        'Read `task.json` first. It is the machine-readable authority; this document cannot broaden it.',
        '',
        '## Before any write',
        '',
        'Read `input/external-output.json` and `input/mobile-app-handoff.json`, then explain:',
        '',
        '- `mtool_no_code` remains the supported baseline;',
        '- `external_no_code` is optional and additive;',
        '- Mtool/server remains authoritative for auth, CSRF, idempotency, Transaction Full, audit, and outbox policy;',
        '- the external owner owns React shell, routing, components, dependencies, Capacitor/native setup, build, signing, and store submission.',
        '',
        'Then ask exactly:',
        '',
        '> ' . (string) ($task['confirmation']['prompt'] ?? ''),
        '',
        'Do not continue until the user answers affirmatively in this task interaction. Earlier generic continuation messages do not count.',
        '',
        '## Allowed before confirmation',
        '',
        '- Read declared input files.',
        '- Explain the planned external-app boundary.',
        '- Ask the declared confirmation question.',
        '',
        '## Forbidden without explicit user confirmation',
        '',
    ];
    foreach (($task['prohibitions_without_explicit_user_confirmation'] ?? []) as $item) {
        $lines[] = '- `' . (string) $item . '`';
    }
    $lines[] = '';
    $lines[] = '## Validation reference';
    $lines[] = '';
    foreach (($task['validation']['consumer_reference_gates'] ?? []) as $gate) {
        $lines[] = '- `' . (string) $gate . '`';
    }
    $lines[] = '';
    $lines[] = 'Success means the external app task is ready to proceed after user confirmation. It does not mean Mtool initialized Capacitor, installed dependencies, or generated a production app.';
    $lines[] = '';
    return implode("\n", $lines);
}

function app_mobile_wrapper_target_normalize_output_mode(string $mode): string
{
    $mode = strtolower(trim($mode));
    return in_array($mode, APP_MOBILE_OUTPUT_MODES, true) ? $mode : '';
}

/**
 * @param array<string,mixed> $handoff
 * @return array<string,mixed>
 */
function app_mobile_wrapper_target_output_mode_config_from_handoff(array $handoff, string $mode): array
{
    $project = is_array($handoff['project'] ?? null) ? $handoff['project'] : [];
    $targetExtensions = [
        'react_web_capacitor' => [
            'status' => 'supported_first_external_target',
            'artifacts' => ['external_optional_output', 'ai_task_packet', 'pwa_readiness'],
            'native_project_generation' => false,
        ],
        'pwa_readiness' => [
            'status' => 'supported_delivery_runtime_metadata',
            'artifacts' => ['pwa_readiness'],
            'native_project_generation' => false,
        ],
        'flutter' => [
            'status' => 'later_input_packet_only',
            'artifacts' => ['later_platform_input_packets'],
            'native_project_generation' => false,
        ],
        'react_native' => [
            'status' => 'later_input_packet_only',
            'artifacts' => ['later_platform_input_packets'],
            'native_project_generation' => false,
        ],
    ];
    $appSurfaceConfig = app_mobile_wrapper_target_app_surface_config_from_handoff($handoff, $mode);

    return [
        'schema_version' => APP_MOBILE_OUTPUT_MODE_CONFIG_SCHEMA_VERSION,
        'selected_mode' => $mode,
        'supported_modes' => APP_MOBILE_OUTPUT_MODES,
        'supported_app_surfaces' => APP_MOBILE_APP_SURFACES,
        'mutation_performed' => false,
        'project' => $project,
        'surface_policy' => [
            'mtool_no_code' => [
                'primary_surface' => 'Mtool generated web/no-code/runtime output',
                'required_artifacts' => ['mobile_app_handoff', 'source_artifact_index', 'runtime_readiness_metadata', 'pwa_readiness'],
                'external_artifacts' => [],
            ],
            'external_no_code' => [
                'primary_surface' => 'external app/framework/code-builder handoff',
                'required_artifacts' => ['mobile_app_handoff', 'external_optional_output', 'ai_task_packet', 'pwa_readiness'],
                'external_artifacts' => ['react_web_capacitor', 'pwa_readiness'],
            ],
            'hybrid' => [
                'primary_surface' => 'Mtool output plus explicit external handoff',
                'required_artifacts' => ['mobile_app_handoff', 'runtime_readiness_metadata', 'external_optional_output', 'ai_task_packet', 'pwa_readiness'],
                'external_artifacts' => ['react_web_capacitor', 'pwa_readiness'],
            ],
        ],
        'selected_artifact_keys' => app_mobile_wrapper_target_output_mode_artifact_keys($mode),
        'target_extensions' => $targetExtensions,
        'app_surface_config' => $appSurfaceConfig,
        'warnings' => [
            'external_owner_owns_app_source_dependencies_native_build_signing_store_submission',
            'external_outputs_are_additive_to_mtool_no_code',
            'hybrid_mode_does_not_imply_automatic_frontend_synchronization',
            'offline_sync_requires_separate_sync_contract',
            'pwa_and_flutter_webview_share_backend_by_default_but_not_runtime_behavior',
        ],
        'forbidden_without_explicit_confirmation' => [
            'dependency_install',
            'capacitor_init',
            'cap_sync',
            'flutter_project_init',
            'react_native_project_init',
            'native_build',
            'signing',
            'store_submission',
            'offline_sync',
            'existing_app_overwrite',
        ],
        'validation' => [
            'mode_must_be_one_of' => APP_MOBILE_OUTPUT_MODES,
            'surface_must_be_one_of' => APP_MOBILE_APP_SURFACES,
            'selected_target_requires_extension_packet' => true,
            'separate_backend_endpoint_requires_explicit_reason' => true,
            'surface_specific_redirect_storage_cache_and_bridge_policy_required' => true,
            'ai_task_packet_requires_confirmation' => true,
            'native_project_generation_requires_explicit_user_confirmation' => true,
        ],
    ];
}

/**
 * @param array<string,mixed> $handoff
 * @return array<string,mixed>
 */
function app_mobile_wrapper_target_app_surface_config_from_handoff(array $handoff, string $mode): array
{
    $api = is_array($handoff['api'] ?? null) ? $handoff['api'] : [];
    $auth = is_array($handoff['auth'] ?? null) ? $handoff['auth'] : [];
    $endpoints = is_array($api['endpoints'] ?? null) ? $api['endpoints'] : [];

    $selectedSurfaces = match ($mode) {
        'mtool_no_code' => ['pwa'],
        'external_no_code' => ['pwa', 'flutter_webview', 'react_web_capacitor'],
        default => ['pwa', 'flutter_webview', 'react_web_capacitor'],
    };

    return [
        'schema_version' => 'mobile-app-surface-config-v1',
        'selected_surfaces' => $selectedSurfaces,
        'backend_endpoint' => [
            'sharing_policy' => 'shared_by_default',
            'api_base_url_policy' => (string) ($api['base_url_policy'] ?? ''),
            'api_endpoint_count' => count($endpoints),
            'auth_mode' => (string) ($auth['mode'] ?? ''),
            'auth_issuer_policy' => 'from_handoff_auth_policy',
            'server_authority' => true,
            'idempotency_required_for_mutations' => true,
            'separate_endpoint_allowed_only_with_explicit_reason' => [
                'staging_or_production_separation',
                'tenant_separation',
                'native_only_bff',
                'separate_sync_server',
            ],
        ],
        'surfaces' => [
            'pwa' => [
                'enabled' => in_array('pwa', $selectedSurfaces, true),
                'role' => 'browser_installable_or_browser_delivered_react_app',
                'source' => 'react_web_app',
                'app_url_policy' => 'explicit_required_for_production',
                'redirect_uri_policy' => 'https_callback_required_for_oidc',
                'storage_policy' => 'browser_storage_explicit',
                'offline_cache_policy' => 'explicit_no_offline_sync_without_contract',
                'native_bridge' => 'not_applicable',
                'distribution_owner' => 'external_owner_or_deployment_owner',
            ],
            'flutter_webview' => [
                'enabled' => in_array('flutter_webview', $selectedSurfaces, true),
                'role' => 'flutter_native_shell_wrapping_react_app_in_webview',
                'source_mode_options' => ['same_app_url', 'bundled_static_assets'],
                'default_source_mode' => 'same_app_url',
                'app_url_policy' => 'explicit_required_when_source_is_same_app_url',
                'redirect_uri_policy' => 'native_deep_link_or_web_callback_must_be_declared',
                'storage_policy' => 'webview_or_native_secure_storage_bridge_explicit',
                'offline_cache_policy' => 'webview_behavior_must_not_be_assumed_equal_to_browser_pwa',
                'native_bridge' => 'disabled_by_default',
                'distribution_owner' => 'external_flutter_owner',
            ],
            'react_web_capacitor' => [
                'enabled' => in_array('react_web_capacitor', $selectedSurfaces, true),
                'role' => 'react_web_app_wrapped_by_capacitor_style_tooling',
                'source' => 'react_web_app',
                'app_url_policy' => 'external_owner_choice',
                'redirect_uri_policy' => 'web_or_native_callback_must_be_declared',
                'storage_policy' => 'browser_or_native_storage_policy_explicit',
                'offline_cache_policy' => 'explicit_no_offline_sync_without_contract',
                'native_bridge' => 'capacitor_plugins_require_explicit_selection',
                'distribution_owner' => 'external_capacitor_owner',
            ],
        ],
        'surface_specific_policy_required' => [
            'app_url_or_bundled_asset_mode',
            'redirect_uri',
            'storage_token_policy',
            'offline_cache_policy',
            'navigation_allowlist',
            'native_bridge_policy',
        ],
    ];
}

/** @return list<string> */
function app_mobile_wrapper_target_output_mode_artifact_keys(string $mode): array
{
    return match ($mode) {
        'mtool_no_code' => ['c1_wrapper_readiness', 'react_wrapper_app_handoff', 'pwa_readiness'],
        'external_no_code' => ['external_optional_output', 'ai_task_packet', 'pwa_readiness'],
        default => ['c1_wrapper_readiness', 'react_wrapper_app_handoff', 'external_optional_output', 'ai_task_packet', 'output_mode_config', 'pwa_readiness'],
    };
}

/** @param array<string,mixed> $config */
function app_mobile_wrapper_target_output_mode_config_markdown(array $config): string
{
    $lines = [
        '# Mobile Output Mode Config',
        '',
        '- Schema: `' . (string) ($config['schema_version'] ?? '') . '`',
        '- Selected mode: `' . (string) ($config['selected_mode'] ?? '') . '`',
        '',
        '## Selected artifacts',
        '',
    ];
    foreach (($config['selected_artifact_keys'] ?? []) as $artifactKey) {
        $lines[] = '- `' . (string) $artifactKey . '`';
    }
    $lines[] = '';
    $appSurfaceConfig = is_array($config['app_surface_config'] ?? null) ? $config['app_surface_config'] : [];
    $selectedSurfaces = is_array($appSurfaceConfig['selected_surfaces'] ?? null) ? $appSurfaceConfig['selected_surfaces'] : [];
    if ($selectedSurfaces !== []) {
        $lines[] = '## Selected app surfaces';
        $lines[] = '';
        foreach ($selectedSurfaces as $surface) {
            $lines[] = '- `' . (string) $surface . '`';
        }
        $lines[] = '';
    }
    $lines[] = '## Warnings';
    $lines[] = '';
    foreach (($config['warnings'] ?? []) as $warning) {
        $lines[] = '- `' . (string) $warning . '`';
    }
    $lines[] = '';
    $lines[] = '## Forbidden without explicit confirmation';
    $lines[] = '';
    foreach (($config['forbidden_without_explicit_confirmation'] ?? []) as $item) {
        $lines[] = '- `' . (string) $item . '`';
    }
    $lines[] = '';
    return implode("\n", $lines);
}

/**
 * @param array<string,mixed> $handoff
 * @return array<string,mixed>
 */
function app_mobile_wrapper_target_pwa_readiness_from_handoff(array $handoff): array
{
    $project = is_array($handoff['project'] ?? null) ? $handoff['project'] : [];
    $sourceArtifacts = is_array($handoff['source_artifacts'] ?? null) ? $handoff['source_artifacts'] : [];
    $actions = is_array($handoff['actions'] ?? null) ? $handoff['actions'] : [];

    return [
        'schema_version' => APP_MOBILE_PWA_READINESS_SCHEMA_VERSION,
        'target' => 'pwa_readiness',
        'mutation_performed' => false,
        'project' => $project,
        'source_artifacts' => $sourceArtifacts,
        'readiness_modes' => [
            'pwa_disabled',
            'pwa_installable_online_only',
            'pwa_static_cache_only',
            'pwa_sync_contract_required',
        ],
        'recommended_mode' => 'pwa_static_cache_only',
        'app_manifest_requirements' => [
            'app_name_required' => true,
            'short_name_required' => true,
            'icons_required' => true,
            'theme_color_required' => true,
            'background_color_required' => true,
            'start_url_required' => true,
            'display_mode_required' => true,
            'generated_by_mtool' => false,
        ],
        'service_worker_policy' => [
            'generated_by_mtool' => false,
            'static_shell_cache_allowed' => true,
            'api_cache_default' => 'not_cacheable',
            'mutation_cache_allowed' => false,
            'update_strategy_required' => true,
            'scope_must_be_explicit' => true,
        ],
        'storage_policy' => [
            'browser_persistent_token_storage_allowed' => false,
            'refresh_token_browser_storage_allowed' => false,
            'business_data_persistent_storage_allowed' => false,
            'session_storage_allowed_for_session_state' => true,
            'native_secure_storage_delegated_to_external_wrapper' => true,
        ],
        'offline_policy' => [
            'offline_sync_default' => false,
            'offline_mutation_allowed' => false,
            'offline_business_data_read_allowed' => false,
            'sync_contract_required_for_offline_data' => true,
            'fallback_ui_required' => true,
        ],
        'api_cacheability' => [
            'read_only_endpoints_cacheable_only_when_explicit' => true,
            'mutating_actions_online_only' => true,
            'validation_errors_not_success_state' => true,
            'auth_failures_trigger_reauth' => true,
            'network_failures_map_to_offline_unavailable_state' => true,
        ],
        'action_summary' => [
            'action_count' => count($actions),
            'mutating_action_count' => count(array_filter($actions, static fn (array $action): bool => (bool) ($action['mutates'] ?? false))),
            'mutations_online_only' => true,
        ],
        'external_owner_owns' => [
            'web app manifest file',
            'service worker implementation',
            'cache strategy implementation',
            'PWA install QA',
            'browser storage implementation',
            'offline sync implementation if separately contracted',
        ],
        'mtool_owns' => [
            'readiness metadata',
            'source artifact refs',
            'server authority boundary',
            'offline/sync non-goals',
            'validation checklist',
        ],
        'forbidden_without_explicit_artifact' => [
            'service_worker_generation',
            'offline_sync',
            'business_data_cache',
            'refresh_token_persistence',
            'background_sync',
            'push_notifications',
            'automatic_install_prompt',
        ],
        'validation' => [
            'manifest_fields_reviewed' => false,
            'service_worker_scope_reviewed' => false,
            'cache_policy_reviewed' => false,
            'offline_sync_contract_present' => false,
            'safe_default' => 'online APIs plus optional static-shell cache only',
        ],
    ];
}

/** @param array<string,mixed> $packet */
function app_mobile_wrapper_target_pwa_readiness_markdown(array $packet): string
{
    $lines = [
        '# PWA Readiness',
        '',
        '- Schema: `' . (string) ($packet['schema_version'] ?? '') . '`',
        '- Recommended mode: `' . (string) ($packet['recommended_mode'] ?? '') . '`',
        '',
        '## Readiness modes',
        '',
    ];
    foreach (($packet['readiness_modes'] ?? []) as $mode) {
        $lines[] = '- `' . (string) $mode . '`';
    }
    $lines[] = '';
    $lines[] = '## External owner owns';
    $lines[] = '';
    foreach (($packet['external_owner_owns'] ?? []) as $item) {
        $lines[] = '- ' . (string) $item;
    }
    $lines[] = '';
    $lines[] = '## Forbidden without explicit artifact';
    $lines[] = '';
    foreach (($packet['forbidden_without_explicit_artifact'] ?? []) as $item) {
        $lines[] = '- `' . (string) $item . '`';
    }
    $lines[] = '';
    $lines[] = '## Safe default';
    $lines[] = '';
    $lines[] = (string) ($packet['validation']['safe_default'] ?? '');
    $lines[] = '';
    return implode("\n", $lines);
}

function app_mobile_wrapper_target_task_id_slug(string $value): string
{
    $slug = strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '-', $value));
    $slug = trim($slug, '-');
    return $slug !== '' ? $slug : 'project';
}

/**
 * @param array<string,mixed> $reactProof
 * @return array<string,mixed>
 */
function app_mobile_wrapper_target_later_platform_packet_from_react_proof(string $platform, array $reactProof): array
{
    $platformKey = $platform === 'flutter' ? 'flutter_input_packet' : 'react_native_input_packet';
    $builder = $platform === 'flutter' ? 'Flutter/Dart builder' : 'React Native builder';
    $packet = [
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

    if ($platform === 'react_native') {
        $packet['react_native_extension'] = app_mobile_wrapper_target_react_native_extension_metadata($reactProof);
    }

    return $packet;
}

/** @param array<string,mixed> $reactProof @return array<string,mixed> */
function app_mobile_wrapper_target_react_native_extension_metadata(array $reactProof): array
{
    return [
        'extension_version' => 'react-native-extension-v1',
        'scope' => 'metadata_only',
        'navigation' => [
            'model' => 'stack_plus_tab_allowed',
            'library_selection' => 'external_owner_choice',
            'required_routes_from_screen_flow' => is_array($reactProof['screen_flow_mapping']['screen_keys'] ?? null)
                ? $reactProof['screen_flow_mapping']['screen_keys']
                : [],
            'deep_link_policy' => 'external_owner_must_define_if_used',
        ],
        'state_management' => [
            'selection' => 'external_owner_choice',
            'minimum_state_classes' => ['auth_session', 'screen_query_state', 'form_draft_state', 'submit_status'],
            'server_state_authority' => true,
            'offline_state_sync' => false,
        ],
        'form_binding' => [
            'validation_source' => 'Mtool/server authoritative validation remains primary',
            'client_validation_role' => 'preflight_display_only',
            'required_field_binding_required' => true,
            'server_error_mapping_required' => true,
        ],
        'api_client' => [
            'package_selection' => 'external_owner_choice',
            'base_url_environment_matrix_required' => true,
            'idempotency_for_mutations_required' => true,
            'retry_policy_required' => true,
            'mutating_actions_online_only_without_sync_contract' => true,
        ],
        'auth' => [
            'oidc_client_selection' => 'external_owner_choice',
            'deep_link_callback_policy_required' => true,
            'refresh_token_policy_required' => true,
            'server_authority_preserved' => true,
        ],
        'secure_storage' => [
            'module_selection' => 'external_owner_choice',
            'browser_like_persistent_token_storage_allowed' => false,
            'refresh_token_storage_requires_explicit_policy' => true,
            'business_data_persistence_requires_sync_contract' => true,
        ],
        'native_modules' => [
            'native_module_policy' => 'deny_by_default_until_declared',
            'permission_mapping_required' => true,
            'expo_vs_bare_boundary' => 'must_be_chosen_by_external_owner',
        ],
        'environment_and_build' => [
            'environment_variant_mapping_required' => true,
            'app_id_bundle_id_owned_by_external_owner' => true,
            'signing_store_submission_owned_by_external_owner' => true,
        ],
        'test_expectations' => [
            'typecheck_command_defined_by_external_owner',
            'unit_or_component_smoke_defined_by_external_owner',
            'device_or_simulator_qa_owned_by_external_owner',
        ],
        'forbidden_without_explicit_confirmation' => [
            'react_native_project_init',
            'expo_project_init',
            'native_module_install',
            'dependency_install',
            'ios_android_project_write',
            'signing',
            'store_submission',
            'offline_sync',
        ],
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
 * @param array<string,mixed> $externalPackage
 * @param array<string,mixed> $aiTaskPackage
 * @param array<string,mixed> $outputModePackage
 * @param array<string,mixed> $pwaPackage
 * @param array<string,mixed> $platformsPackage
 * @return array<string,mixed>
 */
function app_mobile_wrapper_target_bundle_manifest_from_packages(
    array $handoff,
    array $c1Package,
    array $reactPackage,
    array $externalPackage,
    array $aiTaskPackage,
    array $outputModePackage,
    array $pwaPackage,
    array $platformsPackage,
): array {
    return [
        'schema_version' => APP_MOBILE_WRAPPER_BUNDLE_MANIFEST_SCHEMA_VERSION,
        'mutation_performed' => false,
        'project' => is_array($handoff['project'] ?? null) ? $handoff['project'] : [],
        'artifact_order' => [
            'c1_wrapper_readiness',
            'react_wrapper_app_handoff',
            'external_optional_output',
            'ai_task_packet',
            'output_mode_config',
            'pwa_readiness',
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
                'artifact_key' => 'external_optional_output',
                'path_hint' => (string) ($externalPackage['path_hint'] ?? ''),
                'files' => array_values(array_map('strval', array_keys(is_array($externalPackage['files'] ?? null) ? $externalPackage['files'] : []))),
                'purpose' => 'optional external_no_code packet for React/Web + Capacitor consumers',
            ],
            [
                'artifact_key' => 'ai_task_packet',
                'path_hint' => (string) ($aiTaskPackage['path_hint'] ?? ''),
                'files' => array_values(array_map('strval', array_keys(is_array($aiTaskPackage['files'] ?? null) ? $aiTaskPackage['files'] : []))),
                'purpose' => 'Codex/Claude-readable pending task packet for user-confirmed external app creation',
            ],
            [
                'artifact_key' => 'output_mode_config',
                'path_hint' => (string) ($outputModePackage['path_hint'] ?? ''),
                'files' => array_values(array_map('strval', array_keys(is_array($outputModePackage['files'] ?? null) ? $outputModePackage['files'] : []))),
                'purpose' => 'selected mobile output mode and artifact map for users and AI consumers',
            ],
            [
                'artifact_key' => 'pwa_readiness',
                'path_hint' => (string) ($pwaPackage['path_hint'] ?? ''),
                'files' => array_values(array_map('strval', array_keys(is_array($pwaPackage['files'] ?? null) ? $pwaPackage['files'] : []))),
                'purpose' => 'PWA installability, cache, storage, and offline readiness metadata/checklist',
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
