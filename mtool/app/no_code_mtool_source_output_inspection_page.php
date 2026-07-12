<?php

declare(strict_types=1);

require_once __DIR__ . '/no_code_mtool_dogfooding_probe.php';
require_once __DIR__ . '/no_code_runtime.php';
require_once __DIR__ . '/project_source_output_route_common.php';

function app_no_code_mtool_source_output_inspection_enabled(): bool
{
    return in_array(
        strtolower(trim((string) getenv('MTOOL_NO_CODE_SELF_INSPECTION_ENABLED'))),
        ['1', 'true', 'yes', 'on'],
        true,
    );
}

/**
 * @return array<string,mixed>
 */
function app_no_code_mtool_source_output_inspection_hybrid_contract(): array
{
    return [
        'contract_version' => 'no-code-mtool-source-output-inspection-hybrid-v0',
        'workflow_key' => 'mtool_source_output_inspection',
        'route' => [
            'method' => 'GET',
            'path' => '/projects/MTOOL/source-outputs/no-code-inspection',
            'feature_flag' => 'MTOOL_NO_CODE_SELF_INSPECTION_ENABLED',
            'default_state' => 'off',
            'project_scope' => 'MTOOL',
            'site' => 'admin',
        ],
        'generated_owns' => [
            'read-only list/detail screen rendering',
            'no-code runtime HTML markers',
            'selected-row detail handoff',
            'declared inspection field presentation',
            'disabled action visibility without execution binding',
        ],
        'custom_mtool_owns' => [
            'admin shell and route bootstrap',
            'authentication and project authorization',
            'Source Output repository reads',
            'canonical Source Output CRUD/build/publish pages',
            'feature flag rollout and rollback',
            'any future mutation, CSRF, audit, idempotency, or Transaction Full boundary',
        ],
        'row_adapter_fields' => [
            'source_output_key',
            'name',
            'class_type',
            'artifact_strategy',
            'target_binding_type',
            'spec_visibility',
            'source_output_dir',
        ],
        'selector_policy' => [
            'query_field' => 'source_output_key',
            'default_without_selector' => 'first_row',
            'unknown_selector' => 'missing_detail_no_fallback',
            'normalization' => 'app_normalize_source_output_key',
        ],
        'authority_boundary' => [
            'auth_bootstrap' => 'app_project_source_output_route_bootstrap',
            'allowed_methods' => ['GET'],
            'execution_controls' => 'not_rendered',
            'post_routes' => 'not_supported',
        ],
        'rollback_boundary' => [
            'disable_flag' => 'unset MTOOL_NO_CODE_SELF_INSPECTION_ENABLED',
            'state_change' => 'none',
            'canonical_fallback' => '/projects/MTOOL/source-outputs',
        ],
        'excluded_operations' => [
            'source_output_create',
            'source_output_edit',
            'source_output_delete',
            'source_output_reorder',
            'source_output_build',
            'source_output_publish',
            'review_request_persistence',
            'generated_post_execution',
        ],
        'verification' => [
            'fast_test' => 'NoCodeMtoolSourceOutputInspectionTest',
            'full_suite' => 'make test',
            'browser_evidence' => '2026-0712-live-mtool-inspection-http-browser-promotion',
        ],
    ];
}

/**
 * @param list<array<string,mixed>> $items
 * @return list<array<string,mixed>>
 */
function app_no_code_mtool_source_output_inspection_rows(array $items): array
{
    $fieldKeys = [
        'source_output_key',
        'name',
        'class_type',
        'artifact_strategy',
        'target_binding_type',
        'spec_visibility',
        'source_output_dir',
    ];
    $rows = [];
    foreach ($items as $item) {
        $row = [];
        foreach ($fieldKeys as $fieldKey) {
            $value = $item[$fieldKey] ?? '';
            $row[$fieldKey] = is_scalar($value) ? (string) $value : '';
        }
        $rows[] = $row;
    }

    return $rows;
}

/**
 * @param list<array<string,mixed>> $rows
 * @return array{item:array<string,mixed>,missing:bool,selector:string}
 */
function app_no_code_mtool_source_output_inspection_selection(array $rows, string $selector): array
{
    $rawSelector = trim($selector);
    $selector = app_normalize_source_output_key($rawSelector);
    if ($rawSelector === '') {
        return [
            'item' => is_array($rows[0] ?? null) ? $rows[0] : [],
            'missing' => false,
            'selector' => '',
        ];
    }
    if ($selector === '') {
        return ['item' => [], 'missing' => true, 'selector' => $rawSelector];
    }

    foreach ($rows as $row) {
        if ((string) ($row['source_output_key'] ?? '') === $selector) {
            return ['item' => $row, 'missing' => false, 'selector' => $selector];
        }
    }

    return ['item' => [], 'missing' => true, 'selector' => $selector];
}

/**
 * @param list<array<string,mixed>> $rows
 * @param array<string,mixed> $principal
 */
function app_no_code_mtool_source_output_inspection_html(
    array $rows,
    array $principal,
    string $selector = '',
): string {
    $definitionResult = app_no_code_mtool_dogfooding_probe_screen_definition($principal);
    if (!$definitionResult['ok']) {
        throw new RuntimeException((string) $definitionResult['error']);
    }

    $definition = $definitionResult['definition'];
    $selection = app_no_code_mtool_source_output_inspection_selection($rows, $selector);
    $list = app_no_code_runtime_render_screen(
        $definition,
        'mtool_source_output_review_list',
        $rows,
    );
    $detail = app_no_code_runtime_render_screen(
        $definition,
        'mtool_source_output_review_detail',
        [],
        $selection['item'],
    );
    if (!$list['ok'] || !$detail['ok']) {
        throw new RuntimeException((string) ($list['error'] ?: $detail['error']));
    }

    $listRender = $list['render'];
    $detailRender = $detail['render'];
    $listRender['actions'] = [];
    $detailRender['actions'] = [];
    $listHtml = app_no_code_runtime_render_screen_html($listRender);
    $detailHtml = app_no_code_runtime_render_screen_html($detailRender);
    $missingHtml = $selection['missing']
        ? '<p data-mtool-no-code-selection-missing="true">Selected Source Output was not found: <code>'
            . app_no_code_runtime_html_escape($selection['selector']) . '</code></p>'
        : '';
    $emptyDetailHtml = $selection['item'] === []
        ? '<p data-mtool-no-code-detail-empty="true">No preview data is available yet.</p>'
        : '';
    $contractJson = json_encode(
        app_no_code_mtool_source_output_inspection_hybrid_contract(),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
    );
    if (!is_string($contractJson)) {
        throw new RuntimeException('Mtool Source Output inspection hybrid contract JSON could not be encoded.');
    }

    return '<!doctype html><html lang="en"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Mtool Source Output No-Code Inspection</title>'
        . '<style>' . app_no_code_runtime_preview_css() . '</style></head><body>'
        . '<main class="no-code-preview" data-mtool-no-code-source-output-inspection="true">'
        . '<header class="no-code-preview-header"><div><h1>Mtool Source Output Inspection</h1>'
        . '<p>Read-only generated inspection backed by the current Mtool repository.</p></div></header>'
        . '<script type="application/json" data-mtool-no-code-hybrid-contract="true">' . $contractJson . '</script>'
        . '<p><a data-canonical-source-outputs-link href="/projects/MTOOL/source-outputs">Return to canonical Source Outputs</a></p>'
        . $missingHtml . $emptyDetailHtml . $listHtml . $detailHtml
        . '</main></body></html>';
}

function app_render_no_code_mtool_source_output_inspection_page(array $app, array $request): void
{
    if (!app_no_code_mtool_source_output_inspection_enabled()) {
        app_render_not_found_page($app, $request);
        return;
    }

    $bootstrap = app_project_source_output_route_bootstrap($app, $request, ['GET']);
    if ($bootstrap === null) {
        return;
    }
    if ($bootstrap['project_key'] !== 'MTOOL') {
        app_render_not_found_page($app, $request);
        return;
    }

    $catalog = app_fetch_project_source_output_catalog($app, 'MTOOL');
    if (!$catalog['ok']) {
        app_send_html_response_headers($request, 500);
        echo '<!doctype html><html lang="en"><body><main data-mtool-no-code-source-output-inspection-error="true">'
            . '<h1>Mtool Source Output Inspection</h1><p>Repository read failed.</p><code>'
            . app_no_code_runtime_html_escape((string) $catalog['error'])
            . '</code></main></body></html>';
        return;
    }

    try {
        $html = app_no_code_mtool_source_output_inspection_html(
            app_no_code_mtool_source_output_inspection_rows($catalog['items']),
            $bootstrap['principal'],
            app_query_param('source_output_key'),
        );
    } catch (Throwable $throwable) {
        app_send_html_response_headers($request, 500);
        echo '<!doctype html><html lang="en"><body><main data-mtool-no-code-source-output-inspection-error="true">'
            . '<h1>Mtool Source Output Inspection</h1><p>Generated inspection render failed.</p><code>'
            . app_no_code_runtime_html_escape($throwable->getMessage())
            . '</code></main></body></html>';
        return;
    }

    app_send_html_response_headers($request, 200);
    echo $html;
}
