<?php

declare(strict_types=1);

require_once __DIR__ . '/material_insight_no_code_handoff.php';
require_once __DIR__ . '/material_insight_preview_page.php';

function app_material_insight_no_code_handoff_preview_enabled(): bool
{
    return in_array(
        strtolower(trim((string) getenv('MTOOL_SAMPLE19_MATERIAL_INSIGHT_NO_CODE_HANDOFF_PREVIEW_ENABLED'))),
        ['1', 'true', 'yes', 'on'],
        true,
    );
}

/** @return array<string,mixed> */
function app_material_insight_no_code_handoff_preview_load(): array
{
    $preview = app_material_insight_preview_load();
    if (!($preview['ok'] ?? false)) {
        return $preview;
    }
    $handoff = app_material_insight_no_code_handoff(
        is_array($preview['artifact'] ?? null) ? $preview['artifact'] : [],
    );
    if (!$handoff['ok']) {
        return app_schema_proposal_review_error(500, 'no_code_handoff_invalid:' . $handoff['error']);
    }

    return [
        'ok' => true,
        'status_code' => 200,
        'error' => '',
        'artifact' => $preview['artifact'],
        'screen_definition' => $handoff['screen_definition'],
        'runtime_preview' => $handoff['runtime_preview'],
    ];
}

/** @param array<string,mixed> $payload */
function app_material_insight_no_code_handoff_preview_html(array $payload): string
{
    if (!($payload['ok'] ?? false)) {
        throw new InvalidArgumentException('A valid material insight no-code handoff payload is required.');
    }
    $screenDefinition = is_array($payload['screen_definition'] ?? null) ? $payload['screen_definition'] : [];
    $runtimePreview = is_array($payload['runtime_preview'] ?? null) ? $payload['runtime_preview'] : [];
    $contract = is_array($screenDefinition['contracts'][0] ?? null) ? $screenDefinition['contracts'][0] : [];
    $screensHtml = '';
    foreach (is_array($runtimePreview['screens'] ?? null) ? $runtimePreview['screens'] : [] as $screen) {
        if (!is_array($screen)) {
            continue;
        }
        $screensHtml .= '<li data-no-code-handoff-screen="' . app_material_insight_preview_h((string) ($screen['screen_key'] ?? '')) . '">'
            . '<code>' . app_material_insight_preview_h((string) ($screen['screen_key'] ?? '')) . '</code>'
            . ' — ' . app_material_insight_preview_h((string) ($screen['screen_title'] ?? ''))
            . '</li>';
    }
    $actionCount = count(is_array($contract['actions'] ?? null) ? $contract['actions'] : []);
    $customOperationCount = count(is_array($contract['custom_operations'] ?? null) ? $contract['custom_operations'] : []);
    $traceability = is_array($runtimePreview['material_insight_traceability'] ?? null)
        ? $runtimePreview['material_insight_traceability']
        : [];

    return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Sample19 No-Code Handoff Preview</title></head><body><main data-material-insight-no-code-handoff="true">'
        . '<h1>Sample19 No-Code Handoff Preview</h1>'
        . '<section><h2>Safety</h2><ul><li data-no-code-handoff-mutation="false">Read-only; no mutation</li>'
        . '<li data-no-code-handoff-ai-call="false">No AI call</li></ul></section>'
        . '<section><h2>Versions</h2><dl><dt>Screen definition</dt><dd><code data-no-code-screen-definition-version="'
        . app_material_insight_preview_h((string) ($screenDefinition['definition_version'] ?? ''))
        . '">' . app_material_insight_preview_h((string) ($screenDefinition['definition_version'] ?? '')) . '</code></dd>'
        . '<dt>Runtime preview</dt><dd><code data-no-code-runtime-version="'
        . app_material_insight_preview_h((string) ($runtimePreview['runtime_version'] ?? ''))
        . '">' . app_material_insight_preview_h((string) ($runtimePreview['runtime_version'] ?? '')) . '</code></dd></dl></section>'
        . '<section><h2>Traceability</h2><dl><dt>Source version</dt><dd><code>'
        . app_material_insight_preview_h((string) ($traceability['source_version'] ?? ''))
        . '</code></dd><dt>Proposal</dt><dd><code>'
        . app_material_insight_preview_h((string) ($traceability['proposal_id'] ?? ''))
        . '</code></dd></dl></section>'
        . '<section><h2>Screens</h2><ul>' . $screensHtml . '</ul></section>'
        . '<section><h2>Generated actions</h2><p data-no-code-handoff-actions="' . $actionCount . '">' . $actionCount . '</p>'
        . '<p data-no-code-handoff-custom-operations="' . $customOperationCount . '">' . $customOperationCount . '</p></section>'
        . '<p><a data-no-code-handoff-return href="/projects/SAMPLE19/material-insight">Return to material insight preview</a></p>'
        . '</main></body></html>';
}

function app_render_material_insight_no_code_handoff_preview_page(array $app, array $request): void
{
    if (!app_material_insight_no_code_handoff_preview_enabled()) {
        app_render_not_found_page($app, $request);
        return;
    }
    $bootstrap = app_project_shared_contract_route_bootstrap($app, $request, ['GET']);
    if ($bootstrap === null) {
        return;
    }
    if ($bootstrap['project_key'] !== 'SAMPLE19') {
        app_render_not_found_page($app, $request);
        return;
    }
    $payload = app_material_insight_no_code_handoff_preview_load();
    if (!$payload['ok']) {
        app_send_html_response_headers($request, $payload['status_code']);
        echo '<!doctype html><html lang="en"><body><main data-material-insight-no-code-handoff-error="true"><h1>No-Code Handoff Preview Error</h1><code>'
            . app_material_insight_preview_h((string) $payload['error'])
            . '</code></main></body></html>';
        return;
    }
    app_send_html_response_headers($request, 200);
    echo app_material_insight_no_code_handoff_preview_html($payload);
}
