<?php

declare(strict_types=1);

const APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY = 'NO-CODE-RUNTIME';

/**
 * @param list<array<string,mixed>> $sourceOutputs
 * @param list<array<string,mixed>> $artifacts
 * @return array{
 *     source_output_key:string,
 *     available:bool,
 *     source_output:array<string,mixed>|null,
 *     source_output_dir:string,
 *     latest_artifact:array<string,mixed>|null,
 *     artifact_count:int,
 *     health:array{
 *         state:string,
 *         label:string,
 *         reasons:list<string>
 *     },
 *     workflow_steps:list<array{key:string,label:string,state:string,detail:string}>,
 *     publish_readiness:array{
 *         state:string,
 *         label:string,
 *         source_output_key:string,
 *         source_output_dir:string,
 *         artifact_key:string,
 *         artifact_archive_exists:bool,
 *         preview_files_ready:bool,
 *         screen_count:int,
 *         action_count:int,
 *         blocking_reasons:list<string>
 *     },
 *     preview:array{
 *         source_root:string,
 *         screen_definition_path:string,
 *         runtime_preview_path:string,
 *         runtime_preview_html_path:string,
 *         screen_definition_exists:bool,
 *         runtime_preview_exists:bool,
 *         runtime_preview_html_exists:bool,
 *         definition_version:string,
 *         runtime_version:string,
 *         contract_count:int,
 *         screen_count:int,
 *         action_count:int,
 *         sync_hint_screen_count:int,
 *         screen_keys:list<string>,
 *         action_keys:list<string>,
 *         errors:list<string>
 *     }
 * }
 */
function app_no_code_operator_inspection_from_catalog(
    array $sourceOutputs,
    array $artifacts,
    string $projectKey,
    string $workspaceRoot,
    string $sourceOutputKey = APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY,
): array {
    $normalizedProjectKey = app_normalize_no_code_operator_project_key($projectKey);
    $normalizedSourceOutputKey = app_normalize_no_code_operator_source_output_key($sourceOutputKey);
    $workspaceRoot = rtrim(str_replace('\\', '/', $workspaceRoot), '/');

    $sourceOutput = app_no_code_operator_find_source_output($sourceOutputs, $normalizedSourceOutputKey);
    $sourceOutputDir = app_no_code_operator_source_output_dir($sourceOutput, $normalizedProjectKey, $normalizedSourceOutputKey);
    $sourceRoot = $workspaceRoot . '/' . $sourceOutputDir;

    $matchingArtifacts = app_no_code_operator_filter_artifacts($artifacts, $normalizedSourceOutputKey);
    $latestArtifact = $matchingArtifacts[0] ?? null;

    $preview = app_no_code_operator_preview_summary($sourceRoot);

    return [
        'source_output_key' => $normalizedSourceOutputKey,
        'available' => $sourceOutput !== null,
        'source_output' => $sourceOutput,
        'source_output_dir' => $sourceOutputDir,
        'latest_artifact' => $latestArtifact,
        'artifact_count' => count($matchingArtifacts),
        'health' => app_no_code_operator_health_summary($sourceOutput, $latestArtifact, $preview),
        'workflow_steps' => app_no_code_operator_workflow_steps($sourceOutput, $latestArtifact, $preview),
        'publish_readiness' => app_no_code_operator_publish_readiness(
            $normalizedSourceOutputKey,
            $sourceOutputDir,
            $sourceOutput,
            $latestArtifact,
            $preview,
        ),
        'preview' => $preview,
    ];
}

function app_normalize_no_code_operator_project_key(string $projectKey): string
{
    $normalized = strtoupper(trim($projectKey));
    $normalized = preg_replace('/[^A-Z0-9_-]+/', '-', $normalized) ?? '';
    return trim($normalized, '-_');
}

function app_normalize_no_code_operator_source_output_key(string $sourceOutputKey): string
{
    $normalized = strtoupper(trim($sourceOutputKey));
    $normalized = preg_replace('/[^A-Z0-9_-]+/', '-', $normalized) ?? '';
    return trim($normalized, '-_');
}

/**
 * @param list<array<string,mixed>> $sourceOutputs
 * @return array<string,mixed>|null
 */
function app_no_code_operator_find_source_output(array $sourceOutputs, string $sourceOutputKey): ?array
{
    foreach ($sourceOutputs as $sourceOutput) {
        if (($sourceOutput['source_output_key'] ?? '') === $sourceOutputKey) {
            return $sourceOutput;
        }
    }

    return null;
}

/**
 * @param array<string,mixed>|null $sourceOutput
 */
function app_no_code_operator_source_output_dir(?array $sourceOutput, string $projectKey, string $sourceOutputKey): string
{
    $sourceOutputDir = trim(str_replace('\\', '/', (string) ($sourceOutput['source_output_dir'] ?? '')));
    if ($sourceOutputDir !== '') {
        return trim($sourceOutputDir, '/');
    }

    return 'work/source-outputs/' . $projectKey . '/' . $sourceOutputKey;
}

/**
 * @param list<array<string,mixed>> $artifacts
 * @return list<array<string,mixed>>
 */
function app_no_code_operator_filter_artifacts(array $artifacts, string $sourceOutputKey): array
{
    $matches = [];
    foreach ($artifacts as $artifact) {
        if (($artifact['source_output_key'] ?? '') === $sourceOutputKey) {
            $matches[] = $artifact;
        }
    }

    usort(
        $matches,
        static fn (array $left, array $right): int => strcmp((string) ($right['artifact_key'] ?? ''), (string) ($left['artifact_key'] ?? '')),
    );

    return $matches;
}

/**
 * @param array<string,mixed>|null $sourceOutput
 * @param array<string,mixed>|null $latestArtifact
 * @param array{
 *     screen_definition_exists:bool,
 *     runtime_preview_exists:bool,
 *     runtime_preview_html_exists:bool,
 *     screen_count:int,
 *     errors:list<string>
 * } $preview
 * @return array{
 *     state:string,
 *     label:string,
 *     reasons:list<string>
 * }
 */
function app_no_code_operator_health_summary(?array $sourceOutput, ?array $latestArtifact, array $preview): array
{
    $missingReasons = [];
    $warningReasons = [];

    if ($sourceOutput === null) {
        $missingReasons[] = 'NO-CODE-RUNTIME definition is missing.';
    }

    if ($latestArtifact === null) {
        $warningReasons[] = 'No generated artifact is available yet.';
    } elseif (($latestArtifact['archive_exists'] ?? false) !== true) {
        $warningReasons[] = 'Latest artifact archive is missing.';
    }

    if (!$preview['screen_definition_exists']) {
        $missingReasons[] = 'screen-definition.json is missing.';
    }
    if (!$preview['runtime_preview_exists']) {
        $missingReasons[] = 'runtime-preview.json is missing.';
    }
    if (!$preview['runtime_preview_html_exists']) {
        $missingReasons[] = 'runtime-preview.html is missing.';
    }

    if ($preview['screen_count'] <= 0) {
        $warningReasons[] = 'No generated screens were found.';
    }

    foreach ($preview['errors'] as $error) {
        $warningReasons[] = $error;
    }

    if ($missingReasons !== []) {
        return [
            'state' => 'missing',
            'label' => 'Missing preview inputs',
            'reasons' => array_values(array_unique(array_merge($missingReasons, $warningReasons))),
        ];
    }

    if ($warningReasons !== []) {
        return [
            'state' => 'warning',
            'label' => 'Needs operator review',
            'reasons' => array_values(array_unique($warningReasons)),
        ];
    }

    return [
        'state' => 'ready',
        'label' => 'Preview ready',
        'reasons' => ['Generated preview metadata and latest artifact are available.'],
    ];
}

/**
 * @param array<string,mixed>|null $sourceOutput
 * @param array<string,mixed>|null $latestArtifact
 * @param array{
 *     screen_definition_exists:bool,
 *     runtime_preview_exists:bool,
 *     runtime_preview_html_exists:bool,
 *     screen_count:int,
 *     action_count:int
 * } $preview
 * @return list<array{key:string,label:string,state:string,detail:string}>
 */
function app_no_code_operator_workflow_steps(?array $sourceOutput, ?array $latestArtifact, array $preview): array
{
    $definitionReady = $sourceOutput !== null;
    $artifactReady = $latestArtifact !== null && ($latestArtifact['archive_exists'] ?? false) === true;
    $previewReady = $preview['screen_definition_exists']
        && $preview['runtime_preview_exists']
        && $preview['runtime_preview_html_exists']
        && $preview['screen_count'] > 0;

    return [
        [
            'key' => 'inspect-definition',
            'label' => 'Inspect generated runtime definition',
            'state' => $definitionReady ? 'ready' : 'blocked',
            'detail' => $definitionReady
                ? 'NO-CODE-RUNTIME definition is available.'
                : 'NO-CODE-RUNTIME definition is missing.',
        ],
        [
            'key' => 'inspect-artifact',
            'label' => 'Inspect latest generated artifact',
            'state' => $artifactReady ? 'ready' : 'blocked',
            'detail' => $artifactReady
                ? 'Latest artifact archive is available.'
                : 'Latest artifact archive is missing or not generated yet.',
        ],
        [
            'key' => 'review-preview',
            'label' => 'Review generated preview files',
            'state' => $previewReady ? 'ready' : 'blocked',
            'detail' => $previewReady
                ? 'Preview JSON and HTML are available with generated screens.'
                : 'Preview JSON/HTML files or generated screens are incomplete.',
        ],
        [
            'key' => 'check-actions',
            'label' => 'Check generated action surface',
            'state' => ((int) ($preview['action_count'] ?? 0)) > 0 ? 'ready' : 'blocked',
            'detail' => ((int) ($preview['action_count'] ?? 0)) > 0
                ? 'Generated runtime actions are present.'
                : 'No generated runtime actions are present.',
        ],
    ];
}

/**
 * @param array<string,mixed>|null $sourceOutput
 * @param array<string,mixed>|null $latestArtifact
 * @param array{
 *     screen_definition_exists:bool,
 *     runtime_preview_exists:bool,
 *     runtime_preview_html_exists:bool,
 *     screen_count:int,
 *     action_count:int
 * } $preview
 * @return array{
 *     state:string,
 *     label:string,
 *     source_output_key:string,
 *     source_output_dir:string,
 *     artifact_key:string,
 *     artifact_archive_exists:bool,
 *     preview_files_ready:bool,
 *     screen_count:int,
 *     action_count:int,
 *     blocking_reasons:list<string>
 * }
 */
function app_no_code_operator_publish_readiness(
    string $sourceOutputKey,
    string $sourceOutputDir,
    ?array $sourceOutput,
    ?array $latestArtifact,
    array $preview,
): array {
    $artifactArchiveExists = $latestArtifact !== null && ($latestArtifact['archive_exists'] ?? false) === true;
    $previewFilesReady = $preview['screen_definition_exists']
        && $preview['runtime_preview_exists']
        && $preview['runtime_preview_html_exists']
        && ((int) ($preview['screen_count'] ?? 0)) > 0;
    $actionCount = (int) ($preview['action_count'] ?? 0);
    $blockingReasons = [];

    if ($sourceOutput === null) {
        $blockingReasons[] = 'NO-CODE-RUNTIME definition is missing.';
    }
    if ($latestArtifact === null) {
        $blockingReasons[] = 'Latest generated artifact is missing.';
    } elseif (!$artifactArchiveExists) {
        $blockingReasons[] = 'Latest generated artifact archive is missing.';
    }
    if (!$previewFilesReady) {
        $blockingReasons[] = 'Generated preview files are incomplete.';
    }
    if ($actionCount <= 0) {
        $blockingReasons[] = 'Generated action surface is empty.';
    }

    $state = $blockingReasons === [] ? 'publishable' : 'blocked';

    return [
        'state' => $state,
        'label' => $state === 'publishable'
            ? 'Publish candidate ready'
            : 'Publish candidate blocked',
        'source_output_key' => $sourceOutputKey,
        'source_output_dir' => $sourceOutputDir,
        'artifact_key' => $latestArtifact !== null ? (string) ($latestArtifact['artifact_key'] ?? '') : '',
        'artifact_archive_exists' => $artifactArchiveExists,
        'preview_files_ready' => $previewFilesReady,
        'screen_count' => (int) ($preview['screen_count'] ?? 0),
        'action_count' => $actionCount,
        'blocking_reasons' => array_values(array_unique($blockingReasons)),
    ];
}

/**
 * @return array{
 *     source_root:string,
 *     screen_definition_path:string,
 *     runtime_preview_path:string,
 *     runtime_preview_html_path:string,
 *     screen_definition_exists:bool,
 *     runtime_preview_exists:bool,
 *     runtime_preview_html_exists:bool,
 *     definition_version:string,
 *     runtime_version:string,
 *     contract_count:int,
 *     screen_count:int,
 *     action_count:int,
 *     sync_hint_screen_count:int,
 *     screen_keys:list<string>,
 *     action_keys:list<string>,
 *     errors:list<string>
 * }
 */
function app_no_code_operator_preview_summary(string $sourceRoot): array
{
    $sourceRoot = rtrim(str_replace('\\', '/', $sourceRoot), '/');
    $screenDefinitionPath = $sourceRoot . '/screen-definition.json';
    $runtimePreviewPath = $sourceRoot . '/runtime-preview.json';
    $runtimePreviewHtmlPath = $sourceRoot . '/runtime-preview.html';

    $errors = [];
    $screenDefinition = app_no_code_operator_read_json_object($screenDefinitionPath, $errors);
    $runtimePreview = app_no_code_operator_read_json_object($runtimePreviewPath, $errors);

    $screenSummary = app_no_code_operator_screen_summary($screenDefinition);

    return [
        'source_root' => $sourceRoot,
        'screen_definition_path' => $screenDefinitionPath,
        'runtime_preview_path' => $runtimePreviewPath,
        'runtime_preview_html_path' => $runtimePreviewHtmlPath,
        'screen_definition_exists' => is_file($screenDefinitionPath),
        'runtime_preview_exists' => is_file($runtimePreviewPath),
        'runtime_preview_html_exists' => is_file($runtimePreviewHtmlPath),
        'definition_version' => is_array($screenDefinition) ? (string) ($screenDefinition['definition_version'] ?? '') : '',
        'runtime_version' => is_array($runtimePreview) ? (string) ($runtimePreview['runtime_version'] ?? '') : '',
        'contract_count' => is_array($screenDefinition['contracts'] ?? null) ? count($screenDefinition['contracts']) : 0,
        'screen_count' => $screenSummary['screen_count'],
        'action_count' => $screenSummary['action_count'],
        'sync_hint_screen_count' => $screenSummary['sync_hint_screen_count'],
        'screen_keys' => $screenSummary['screen_keys'],
        'action_keys' => $screenSummary['action_keys'],
        'errors' => $errors,
    ];
}

/**
 * @param list<string> $errors
 * @return array<string,mixed>|null
 */
function app_no_code_operator_read_json_object(string $path, array &$errors): ?array
{
    if (!is_file($path)) {
        return null;
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        $errors[] = basename($path) . ' could not be read.';
        return null;
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        $errors[] = basename($path) . ' is not a JSON object.';
        return null;
    }

    return $decoded;
}

/**
 * @param array<string,mixed>|null $screenDefinition
 * @return array{
 *     screen_count:int,
 *     action_count:int,
 *     sync_hint_screen_count:int,
 *     screen_keys:list<string>,
 *     action_keys:list<string>
 * }
 */
function app_no_code_operator_screen_summary(?array $screenDefinition): array
{
    $screenKeys = [];
    $actionKeys = [];
    $screenCount = 0;
    $actionCount = 0;
    $syncHintScreenCount = 0;

    $contracts = is_array($screenDefinition['contracts'] ?? null) ? $screenDefinition['contracts'] : [];
    foreach ($contracts as $contract) {
        if (!is_array($contract)) {
            continue;
        }

        $screens = is_array($contract['screens'] ?? null) ? $contract['screens'] : [];
        foreach ($screens as $screen) {
            if (!is_array($screen)) {
                continue;
            }

            $screenCount++;
            $screenKey = (string) ($screen['screen_key'] ?? '');
            if ($screenKey !== '') {
                $screenKeys[] = $screenKey;
            }

            if (($screen['sync_status_hint'] ?? false) === true) {
                $syncHintScreenCount++;
            }

            $actions = is_array($screen['actions'] ?? null) ? $screen['actions'] : [];
            foreach ($actions as $action) {
                if (!is_array($action)) {
                    continue;
                }

                $actionCount++;
                $actionKey = (string) ($action['action_key'] ?? '');
                if ($actionKey !== '') {
                    $actionKeys[] = $actionKey;
                }
            }
        }
    }

    return [
        'screen_count' => $screenCount,
        'action_count' => $actionCount,
        'sync_hint_screen_count' => $syncHintScreenCount,
        'screen_keys' => array_values(array_unique($screenKeys)),
        'action_keys' => array_values(array_unique($actionKeys)),
    ];
}
