<?php

declare(strict_types=1);

require_once __DIR__ . '/material_insight.php';
require_once __DIR__ . '/project_shared_contract_route_common.php';
require_once __DIR__ . '/schema_proposal_review_page.php';

function app_material_insight_preview_enabled(): bool
{
    return in_array(
        strtolower(trim((string) getenv('MTOOL_SAMPLE19_MATERIAL_INSIGHT_PREVIEW_ENABLED'))),
        ['1', 'true', 'yes', 'on'],
        true,
    );
}

/** @param array{source:string,proposal:string,snapshot:string}|null $paths @return array<string,mixed> */
function app_material_insight_preview_load(?array $paths = null): array
{
    $paths ??= app_schema_proposal_sample19_paths();
    foreach (['source', 'proposal', 'snapshot'] as $key) {
        if (!is_readable($paths[$key] ?? '')) {
            return app_schema_proposal_review_error(500, 'fixture_unreadable:' . $key);
        }
    }
    $source = file_get_contents($paths['source']);
    $proposalJson = file_get_contents($paths['proposal']);
    $snapshotJson = file_get_contents($paths['snapshot']);
    if (!is_string($source) || !is_string($proposalJson) || !is_string($snapshotJson)) {
        return app_schema_proposal_review_error(500, 'fixture_read_failed');
    }
    $decoded = app_schema_proposal_decode($proposalJson);
    if (!$decoded['ok']) {
        return app_schema_proposal_review_error(500, 'proposal_invalid:' . implode(',', $decoded['errors']));
    }
    try {
        $artifact = app_material_insight_from_schema_proposal($decoded['proposal'], $source, $snapshotJson);
    } catch (Throwable $throwable) {
        return app_schema_proposal_review_error(500, 'material_insight_build_failed:' . $throwable->getMessage());
    }
    $validation = app_material_insight_validate($artifact, $source, $snapshotJson);
    if (!$validation['ok']) {
        return app_schema_proposal_review_error(500, 'material_insight_invalid:' . implode(',', $validation['errors']));
    }

    return [
        'ok' => true,
        'status_code' => 200,
        'error' => '',
        'artifact' => $artifact,
        'source_hash' => (string) $artifact['source']['sha256'],
    ];
}

/** @param array<string,mixed> $preview */
function app_material_insight_preview_html(array $preview): string
{
    if (!($preview['ok'] ?? false)) {
        throw new InvalidArgumentException('A valid material insight preview payload is required.');
    }
    $artifact = $preview['artifact'];
    $qaHtml = '';
    foreach ($artifact['qa_cards'] as $card) {
        $evidenceHtml = '';
        foreach (is_array($card['evidence_refs'] ?? null) ? $card['evidence_refs'] : [] as $evidenceRef) {
            $evidenceHtml .= '<li data-material-insight-qa-evidence="' . app_material_insight_preview_h((string) $evidenceRef) . '"><code>'
                . app_material_insight_preview_h((string) $evidenceRef) . '</code></li>';
        }
        $qaHtml .= '<article data-material-insight-qa-card="' . app_material_insight_preview_h((string) $card['question_key']) . '">'
            . '<p><code data-material-insight-qa-category="' . app_material_insight_preview_h((string) ($card['answer_category'] ?? '')) . '">'
            . app_material_insight_preview_h((string) ($card['answer_category'] ?? '')) . '</code></p>'
            . '<h3>' . app_material_insight_preview_h((string) $card['question']) . '</h3>'
            . '<p>' . app_material_insight_preview_h((string) $card['answer']) . '</p>'
            . '<ul>' . $evidenceHtml . '</ul>'
            . '</article>';
    }
    $screenHtml = '';
    foreach ($artifact['ui_outline']['screens'] as $screen) {
        $screenHtml .= '<li data-material-insight-ui-screen="' . app_material_insight_preview_h((string) $screen['screen_key']) . '">'
            . '<span data-material-insight-ui-section="' . app_material_insight_preview_h((string) ($screen['section'] ?? '')) . '">'
            . app_material_insight_preview_h((string) ($screen['section'] ?? '')) . '</span> '
            . '<code>' . app_material_insight_preview_h((string) $screen['screen_key']) . '</code> — '
            . app_material_insight_preview_h((string) $screen['purpose'])
            . '</li>';
    }
    $prohibitedHtml = '';
    foreach ($artifact['prohibited_actions'] as $action) {
        $prohibitedHtml .= '<li data-material-insight-prohibited-action="' . app_material_insight_preview_h((string) $action) . '"><code>'
            . app_material_insight_preview_h((string) $action) . '</code></li>';
    }

    return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Sample19 Material Insight Preview</title></head><body><main data-material-insight-preview="true">'
        . '<h1>Sample19 Material Insight Preview</h1>'
        . '<section><h2>Safety</h2><ul><li data-material-insight-mutation="false">Read-only; no mutation</li>'
        . '<li data-material-insight-ai-call="false">No AI call</li></ul></section>'
        . '<section><h2>Source</h2><dl><dt>Project</dt><dd><code>SAMPLE19</code></dd><dt>Version</dt><dd><code>'
        . app_material_insight_preview_h((string) $artifact['version'])
        . '</code></dd><dt>SHA-256</dt><dd><code data-material-insight-source-hash="true">'
        . app_material_insight_preview_h((string) $artifact['source']['sha256'])
        . '</code></dd></dl></section>'
        . '<section data-material-insight-basis="true"><h2>Basis</h2><dl><dt>Kind</dt><dd><code>'
        . app_material_insight_preview_h((string) $artifact['basis']['kind'])
        . '</code></dd><dt>Proposal</dt><dd><code>'
        . app_material_insight_preview_h((string) $artifact['basis']['proposal_id'])
        . '</code></dd><dt>Canonical Snapshot</dt><dd><code>'
        . app_material_insight_preview_h((string) $artifact['basis']['canonical_snapshot_sha256'])
        . '</code></dd></dl></section>'
        . '<section><h2>Q&amp;A cards</h2>' . $qaHtml . '</section>'
        . '<section><h2>Read-only UI outline</h2><ul>' . $screenHtml . '</ul></section>'
        . '<section><h2>Prohibited actions</h2><ul>' . $prohibitedHtml . '</ul></section>'
        . '<p><a data-material-insight-return href="/projects/SAMPLE19">Return to Sample19 project</a></p>'
        . '</main></body></html>';
}

function app_material_insight_preview_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_render_material_insight_preview_page(array $app, array $request): void
{
    if (!app_material_insight_preview_enabled()) {
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
    $preview = app_material_insight_preview_load();
    if (!$preview['ok']) {
        app_send_html_response_headers($request, $preview['status_code']);
        echo '<!doctype html><html lang="en"><body><main data-material-insight-preview-error="true"><h1>Material Insight Preview Error</h1><code>'
            . app_material_insight_preview_h((string) $preview['error'])
            . '</code></main></body></html>';
        return;
    }
    app_send_html_response_headers($request, 200);
    echo app_material_insight_preview_html($preview);
}
