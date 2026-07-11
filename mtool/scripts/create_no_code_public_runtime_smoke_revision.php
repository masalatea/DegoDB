#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/no_code_operator_inspection.php';
require_once dirname(__DIR__) . '/app/no_code_public_runtime_page.php';
require_once dirname(__DIR__) . '/app/no_code_publish_candidate_repository_pdo.php';
require_once dirname(__DIR__) . '/app/project_output_service.php';
require_once dirname(__DIR__) . '/app/source_output_repository.php';

function app_cli_no_code_public_runtime_smoke_usage(): string
{
    return <<<'TEXT'
Usage:
  php mtool/scripts/create_no_code_public_runtime_smoke_revision.php --project-key=SAMPLE28 --artifact-key=ARTIFACT [--alias-key=stable] [--requested-by=smoke]

Options:
  --project-key=KEY       Project key that owns the NO-CODE-RUNTIME artifact
  --artifact-key=KEY      Published source output artifact key
  --alias-key=KEY         Public runtime alias key to set (default: stable)
  --requested-by=NAME     Actor id recorded in candidate/current/alias rows
  --allow-empty-action-surface-for-dom-preflight
                          Smoke-only override for readonly filter DOM preflights
  --help                  Show this help
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{help:bool,project_key:string,artifact_key:string,alias_key:string,requested_by:string,allow_empty_action_surface_for_dom_preflight:bool}
 */
function app_cli_no_code_public_runtime_smoke_parse_args(array $argv): array
{
    $parsed = [
        'help' => false,
        'project_key' => '',
        'artifact_key' => '',
        'alias_key' => 'stable',
        'requested_by' => 'public-runtime-smoke',
        'allow_empty_action_surface_for_dom_preflight' => false,
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            $parsed['help'] = true;
            return $parsed;
        }
        if ($argument === '--allow-empty-action-surface-for-dom-preflight') {
            $parsed['allow_empty_action_surface_for_dom_preflight'] = true;
            continue;
        }
        if (!str_starts_with($argument, '--') || !str_contains($argument, '=')) {
            throw new InvalidArgumentException('未対応の引数です: ' . $argument);
        }

        [$name, $value] = explode('=', substr($argument, 2), 2);
        match ($name) {
            'project-key' => $parsed['project_key'] = app_normalize_project_key($value),
            'artifact-key' => $parsed['artifact_key'] = trim($value),
            'alias-key' => $parsed['alias_key'] = app_no_code_public_runtime_normalize_alias_key($value),
            'requested-by' => $parsed['requested_by'] = trim($value),
            default => throw new InvalidArgumentException('未対応の option です: --' . $name),
        };
    }

    if ($parsed['project_key'] === '' || !app_project_key_is_valid($parsed['project_key'])) {
        throw new InvalidArgumentException('有効な --project-key=... を指定してください。');
    }
    if (!app_project_output_artifact_key_is_valid($parsed['artifact_key'])) {
        throw new InvalidArgumentException('有効な --artifact-key=... を指定してください。');
    }
    if (!app_no_code_public_runtime_alias_key_is_valid($parsed['alias_key'])) {
        throw new InvalidArgumentException('有効な --alias-key=... を指定してください。');
    }
    if ($parsed['requested_by'] === '') {
        throw new InvalidArgumentException('有効な --requested-by=... を指定してください。');
    }

    return $parsed;
}

try {
    $parsed = app_cli_no_code_public_runtime_smoke_parse_args($argv);
    if ($parsed['help']) {
        fwrite(STDOUT, app_cli_no_code_public_runtime_smoke_usage() . PHP_EOL);
        exit(0);
    }

    $app = app_bootstrap();
    $projectKey = $parsed['project_key'];
    $sourceOutputKey = APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY;

    $sourceOutputResult = app_fetch_project_source_output_item($app, $projectKey, $sourceOutputKey);
    if (!$sourceOutputResult['ok'] || $sourceOutputResult['item'] === null) {
        throw new RuntimeException($sourceOutputResult['error'] !== '' ? $sourceOutputResult['error'] : 'NO-CODE-RUNTIME source output が見つかりません。');
    }

    $artifactResult = app_project_output_find($app, $projectKey, $parsed['artifact_key']);
    if (!$artifactResult['ok'] || $artifactResult['item'] === null) {
        throw new RuntimeException($artifactResult['error'] !== '' ? $artifactResult['error'] : 'artifact が見つかりません。');
    }

    $artifact = $artifactResult['item'];
    if (($artifact['source_output_key'] ?? '') !== $sourceOutputKey) {
        throw new RuntimeException('artifact は NO-CODE-RUNTIME ではありません。');
    }

    $artifactsResult = app_project_output_list($app, $projectKey, $sourceOutputKey);
    if (!$artifactsResult['ok']) {
        throw new RuntimeException($artifactsResult['error']);
    }

    $inspection = app_no_code_operator_inspection_from_catalog(
        [$sourceOutputResult['item']],
        $artifactsResult['items'],
        $projectKey,
        app_project_output_workspace_root(),
    );
    $readiness = $inspection['publish_readiness'];
    $readiness['artifact_key'] = $parsed['artifact_key'];
    if ($parsed['allow_empty_action_surface_for_dom_preflight']) {
        $blockingReasons = $readiness['blocking_reasons'] ?? [];
        if (
            ($readiness['state'] ?? '') === 'blocked'
            && $blockingReasons === ['Generated action surface is empty.']
            && ($readiness['preview_files_ready'] ?? false) === true
            && ((int) ($readiness['screen_count'] ?? 0)) > 0
        ) {
            $readiness['state'] = 'publishable';
            $readiness['label'] = 'Publish candidate ready';
            $readiness['blocking_reasons'] = [];
            $readiness['smoke_readiness_override'] = 'empty_action_surface_for_dom_preflight';
        }
    }

    $actor = [
        'id' => $parsed['requested_by'],
        'roles' => ['operator'],
    ];

    $createResult = app_pdo_create_no_code_publish_candidate_from_readiness_snapshot($app, [
        'project_key' => $projectKey,
        'source_output_key' => $sourceOutputKey,
        'artifact_key' => $parsed['artifact_key'],
        'artifact_archive_path' => (string) ($artifact['archive_path'] ?? ''),
        'artifact_checksum' => (string) ($artifact['archive_checksum'] ?? ''),
        'actor' => $actor,
        'readiness_snapshot' => $readiness,
    ]);
    if (!$createResult['ok'] || $createResult['item'] === null) {
        throw new RuntimeException($createResult['error']);
    }

    $revisionId = (string) $createResult['item']['revision_id'];
    $requestReviewResult = app_pdo_transition_no_code_publish_candidate($app, [
        'project_key' => $projectKey,
        'source_output_key' => $sourceOutputKey,
        'revision_id' => $revisionId,
        'transition' => 'request_review',
        'expected_status' => 'draft_candidate',
        'reason' => 'public runtime browser smoke',
        'actor' => $actor,
        'metadata' => ['ui_source' => 'public-runtime-browser-smoke'],
    ]);
    if (!$requestReviewResult['ok']) {
        throw new RuntimeException($requestReviewResult['error']);
    }

    $approveResult = app_pdo_transition_no_code_publish_candidate($app, [
        'project_key' => $projectKey,
        'source_output_key' => $sourceOutputKey,
        'revision_id' => $revisionId,
        'transition' => 'approve',
        'expected_status' => 'review_requested',
        'reason' => 'public runtime browser smoke',
        'actor' => $actor,
        'metadata' => ['ui_source' => 'public-runtime-browser-smoke'],
    ]);
    if (!$approveResult['ok']) {
        throw new RuntimeException($approveResult['error']);
    }

    $currentResult = app_pdo_select_current_no_code_publish_candidate($app, [
        'project_key' => $projectKey,
        'source_output_key' => $sourceOutputKey,
        'revision_id' => $revisionId,
        'actor' => $actor,
    ]);
    if (!$currentResult['ok']) {
        throw new RuntimeException($currentResult['error']);
    }

    $aliasResult = app_pdo_set_no_code_public_runtime_alias($app, [
        'project_key' => $projectKey,
        'source_output_key' => $sourceOutputKey,
        'revision_id' => $revisionId,
        'alias_key' => $parsed['alias_key'],
        'actor' => $actor,
    ]);
    if (!$aliasResult['ok']) {
        throw new RuntimeException($aliasResult['error']);
    }

    fwrite(STDOUT, json_encode([
        'ok' => true,
        'project_key' => $projectKey,
        'source_output_key' => $sourceOutputKey,
        'artifact_key' => $parsed['artifact_key'],
        'revision_id' => $revisionId,
        'alias_key' => $parsed['alias_key'],
        'artifact_url' => app_no_code_public_runtime_preview_path($projectKey, $parsed['artifact_key']),
        'current_url' => app_no_code_public_runtime_current_preview_path($projectKey),
        'alias_url' => app_no_code_public_runtime_alias_preview_path($projectKey, $parsed['alias_key']),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL);
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL . PHP_EOL . app_cli_no_code_public_runtime_smoke_usage() . PHP_EOL);
    exit(1);
}
