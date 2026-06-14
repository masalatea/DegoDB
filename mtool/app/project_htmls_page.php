<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/html_template_route_common.php';
require_once __DIR__ . '/project_html_source_binding_repository.php';
require_once __DIR__ . '/project_html_source_binding_service.php';
require_once __DIR__ . '/project_html_route_common.php';

function app_project_html_form_defaults(): array
{
    return [
        'name' => '',
        'legacy_project_source_output_pid' => '0',
        'legacy_html_template_pid' => '0',
    ];
}

function app_project_html_form_from_item(array $html): array
{
    return [
        'name' => (string) ($html['name'] ?? ''),
        'legacy_project_source_output_pid' => (string) ($html['legacy_project_source_output_pid'] ?? '0'),
        'legacy_html_template_pid' => (string) ($html['legacy_html_template_pid'] ?? '0'),
    ];
}

function app_project_html_form_from_post(array $fallback = []): array
{
    return [
        'name' => app_post_param('name', (string) ($fallback['name'] ?? '')),
        'legacy_project_source_output_pid' => app_post_param(
            'legacy_project_source_output_pid',
            (string) ($fallback['legacy_project_source_output_pid'] ?? '0'),
        ),
        'legacy_html_template_pid' => app_post_param(
            'legacy_html_template_pid',
            (string) ($fallback['legacy_html_template_pid'] ?? '0'),
        ),
    ];
}

/**
 * @param array<string,array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     source_output_name:string,
 *     source_output_dir:string,
 *     module_source_ref:string,
 *     effective_source_ref:string,
 *     effective_source_output_key:string,
 *     refresh_policy:string,
 *     notes:string,
 *     source_of_truth:string,
 *     binding_state:string,
 *     is_persisted:bool,
 *     source_root_ok:bool,
 *     source_root_relative_path:string,
 *     source_kind:string,
 *     source_root_error:string,
 *     updated_at:string
 * }> $sourceOutputByLegacyPid
 * @return list<array{legacy_pid:string,caption:string}>
 */
function app_project_html_source_output_options(array $sourceOutputByLegacyPid, string $selectedLegacyPid): array
{
    $options = [];
    foreach ($sourceOutputByLegacyPid as $legacyPid => $sourceOutput) {
        $caption = (string) ($sourceOutput['source_output_key'] ?? '');
        $name = trim((string) ($sourceOutput['source_output_name'] ?? ''));
        if ($name !== '') {
            $caption .= ' / ' . $name;
        }
        if ($caption === '') {
            $caption = 'Legacy ProjectSourceOutputPID ' . $legacyPid . ' (unmapped)';
        }

        $options[] = [
            'legacy_pid' => (string) $legacyPid,
            'caption' => $caption,
        ];
    }

    usort(
        $options,
        static fn (array $left, array $right): int
            => strnatcasecmp($left['caption'], $right['caption']),
    );

    if ($selectedLegacyPid !== '' && $selectedLegacyPid !== '0') {
        $found = false;
        foreach ($options as $option) {
            if ($option['legacy_pid'] === $selectedLegacyPid) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $options[] = [
                'legacy_pid' => $selectedLegacyPid,
                'caption' => 'Legacy ProjectSourceOutputPID ' . $selectedLegacyPid . ' (unmapped)',
            ];
        }
    }

    return $options;
}

/**
 * @param list<array{
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     target_binding_type:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at:string
 * }> $sourceOutputCatalog
 * @return array<string,array{
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     target_binding_type:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at:string
 * }>
 */
function app_project_html_source_output_catalog_by_key(array $sourceOutputCatalog): array
{
    $indexed = [];
    foreach ($sourceOutputCatalog as $sourceOutput) {
        $sourceOutputKey = trim((string) ($sourceOutput['source_output_key'] ?? ''));
        if ($sourceOutputKey === '') {
            continue;
        }

        $indexed[$sourceOutputKey] = $sourceOutput;
    }

    return $indexed;
}

/**
 * @return array{
 *     legacy_project_source_output_pid:string,
 *     source_output_key:string,
 *     refresh_policy:string,
 *     module_source_ref:string,
 *     notes:string
 * }
 */
function app_project_html_binding_form_defaults(): array
{
    return [
        'legacy_project_source_output_pid' => '0',
        'source_output_key' => '',
        'refresh_policy' => 'follow-source-output',
        'module_source_ref' => '',
        'notes' => '',
    ];
}

/**
 * @param array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     module_source_ref:string,
 *     refresh_policy:string,
 *     notes:string
 * } $binding
 * @return array{
 *     legacy_project_source_output_pid:string,
 *     source_output_key:string,
 *     refresh_policy:string,
 *     module_source_ref:string,
 *     notes:string
 * }
 */
function app_project_html_binding_form_from_item(array $binding): array
{
    return [
        'legacy_project_source_output_pid' => (string) ($binding['legacy_project_source_output_pid'] ?? 0),
        'source_output_key' => (string) ($binding['source_output_key'] ?? ''),
        'refresh_policy' => (string) ($binding['refresh_policy'] ?? 'follow-source-output'),
        'module_source_ref' => (string) ($binding['module_source_ref'] ?? ''),
        'notes' => (string) ($binding['notes'] ?? ''),
    ];
}

/**
 * @param array{
 *     legacy_project_source_output_pid:string,
 *     source_output_key:string,
 *     refresh_policy:string,
 *     module_source_ref:string,
 *     notes:string
 * } $fallback
 * @return array{
 *     legacy_project_source_output_pid:string,
 *     source_output_key:string,
 *     refresh_policy:string,
 *     module_source_ref:string,
 *     notes:string
 * }
 */
function app_project_html_binding_form_from_post(array $fallback = []): array
{
    return [
        'legacy_project_source_output_pid' => app_post_param(
            'binding_legacy_project_source_output_pid',
            (string) ($fallback['legacy_project_source_output_pid'] ?? '0'),
        ),
        'source_output_key' => app_post_param(
            'binding_source_output_key',
            (string) ($fallback['source_output_key'] ?? ''),
        ),
        'refresh_policy' => app_post_param(
            'binding_refresh_policy',
            (string) ($fallback['refresh_policy'] ?? 'follow-source-output'),
        ),
        'module_source_ref' => app_post_param(
            'binding_module_source_ref',
            (string) ($fallback['module_source_ref'] ?? ''),
        ),
        'notes' => app_post_param(
            'binding_notes',
            (string) ($fallback['notes'] ?? ''),
        ),
    ];
}

/**
 * @param array{
 *     legacy_project_source_output_pid:string,
 *     source_output_key:string,
 *     refresh_policy:string,
 *     module_source_ref:string,
 *     notes:string
 * } $input
 * @param array<string,array{
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     target_binding_type:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at:string
 * }> $sourceOutputByKey
 * @return array{
 *     input:array{
 *         legacy_project_source_output_pid:string,
 *         source_output_key:string,
 *         refresh_policy:string,
 *         module_source_ref:string,
 *         notes:string
 *     },
 *     errors:list<string>
 * }
 */
function app_project_html_binding_validate_form_input(
    string $projectKey,
    array $input,
    array $sourceOutputByKey,
): array {
    $normalized = [
        'legacy_project_source_output_pid' => (string) max(
            0,
            (int) trim((string) ($input['legacy_project_source_output_pid'] ?? '0')),
        ),
        'source_output_key' => app_normalize_source_output_key(
            (string) ($input['source_output_key'] ?? ''),
        ),
        'refresh_policy' => trim((string) ($input['refresh_policy'] ?? 'follow-source-output')),
        'module_source_ref' => trim((string) ($input['module_source_ref'] ?? '')),
        'notes' => trim((string) ($input['notes'] ?? '')),
    ];
    $errors = [];

    if ((int) $normalized['legacy_project_source_output_pid'] <= 0) {
        $errors[] = 'legacy ProjectSourceOutputPID を選択してください。';
    }

    if ($normalized['source_output_key'] === '' || !app_source_output_key_is_valid($normalized['source_output_key'])) {
        $errors[] = 'current source output key の形式が不正です。';
    } elseif (!array_key_exists($normalized['source_output_key'], $sourceOutputByKey)) {
        $errors[] = '選択した current source output が見つかりません。';
    } elseif (strcasecmp((string) ($sourceOutputByKey[$normalized['source_output_key']]['class_type'] ?? ''), 'html') !== 0) {
        $errors[] = 'HTML binding には class_type=html の source output を選択してください。';
    }

    if (!in_array($normalized['refresh_policy'], app_allowed_project_html_source_binding_refresh_policies(), true)) {
        $errors[] = '未対応の refresh policy です。';
    }

    if (mb_strlen($normalized['notes']) > 4000) {
        $errors[] = 'notes は 4000 文字以内にしてください。';
    }

    if ($normalized['module_source_ref'] === '' && $normalized['source_output_key'] !== '') {
        $normalized['module_source_ref'] = app_project_html_source_binding_default_source_ref(
            $projectKey,
            $normalized['source_output_key'],
        );
    }

    if ($normalized['module_source_ref'] === '') {
        $errors[] = 'module source ref が必要です。';
    } else {
        $parsedRef = app_project_output_html_module_source_ref_parse($normalized['module_source_ref']);
        if (!$parsedRef['ok']) {
            $errors[] = $parsedRef['error'];
        } else {
            if ($parsedRef['project_key'] !== $projectKey) {
                $errors[] = 'module source ref の project key は current project と一致させてください。';
            }

            $effectiveSourceOutput = $sourceOutputByKey[$parsedRef['source_output_key']] ?? null;
            if ($effectiveSourceOutput === null) {
                $errors[] = 'module source ref が指す current source output が見つかりません。';
            } elseif (strcasecmp((string) ($effectiveSourceOutput['class_type'] ?? ''), 'html') !== 0) {
                $errors[] = 'module source ref は class_type=html の source output を指してください。';
            }
        }
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param list<array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     html_key:string,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int,
 *     last_modified_dt:string
 * }> $htmlCatalog
 * @param array<string,array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     source_output_name:string,
 *     source_output_dir:string,
 *     module_source_ref:string,
 *     effective_source_ref:string,
 *     effective_source_output_key:string,
 *     refresh_policy:string,
 *     notes:string,
 *     source_of_truth:string,
 *     binding_state:string,
 *     is_persisted:bool,
 *     source_root_ok:bool,
 *     source_root_relative_path:string,
 *     source_kind:string,
 *     source_root_error:string,
 *     updated_at:string
 * }> $bindingCatalog
 * @return list<array{legacy_pid:string,caption:string}>
 */
function app_project_html_binding_legacy_pid_options(
    array $htmlCatalog,
    array $bindingCatalog,
    string $selectedLegacyPid,
): array {
    $counts = [];
    foreach ($htmlCatalog as $html) {
        $legacyPid = (int) ($html['legacy_project_source_output_pid'] ?? 0);
        if ($legacyPid <= 0) {
            continue;
        }

        if (!array_key_exists((string) $legacyPid, $counts)) {
            $counts[(string) $legacyPid] = 0;
        }
        $counts[(string) $legacyPid]++;
    }

    foreach ($bindingCatalog as $legacyPid => $binding) {
        if (!array_key_exists((string) $legacyPid, $counts)) {
            $counts[(string) $legacyPid] = 0;
        }
    }

    if ($selectedLegacyPid !== '' && $selectedLegacyPid !== '0' && !array_key_exists($selectedLegacyPid, $counts)) {
        $counts[$selectedLegacyPid] = 0;
    }

    ksort($counts, SORT_NUMERIC);

    $options = [];
    foreach ($counts as $legacyPid => $count) {
        $caption = 'Legacy ProjectSourceOutputPID ' . $legacyPid;
        $binding = $bindingCatalog[$legacyPid] ?? null;
        if (is_array($binding) && $binding['source_output_key'] !== '') {
            $caption .= ' -> ' . $binding['source_output_key'];
        }
        $caption .= ' (' . (string) $count . ' html)';

        $options[] = [
            'legacy_pid' => (string) $legacyPid,
            'caption' => $caption,
        ];
    }

    return $options;
}

/**
 * @param list<array{
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     target_binding_type:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at:string
 * }> $sourceOutputCatalog
 * @return list<array{source_output_key:string,caption:string}>
 */
function app_project_html_binding_source_output_options(array $sourceOutputCatalog, string $selectedSourceOutputKey): array
{
    $options = [];
    foreach ($sourceOutputCatalog as $sourceOutput) {
        if (strcasecmp((string) ($sourceOutput['class_type'] ?? ''), 'html') !== 0) {
            continue;
        }

        $caption = (string) ($sourceOutput['source_output_key'] ?? '');
        $name = trim((string) ($sourceOutput['name'] ?? ''));
        if ($name !== '') {
            $caption .= ' / ' . $name;
        }

        $options[] = [
            'source_output_key' => (string) ($sourceOutput['source_output_key'] ?? ''),
            'caption' => $caption,
        ];
    }

    usort(
        $options,
        static fn (array $left, array $right): int
            => strnatcasecmp($left['caption'], $right['caption']),
    );

    if ($selectedSourceOutputKey !== '') {
        $found = false;
        foreach ($options as $option) {
            if ($option['source_output_key'] === $selectedSourceOutputKey) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $options[] = [
                'source_output_key' => $selectedSourceOutputKey,
                'caption' => $selectedSourceOutputKey . ' (unmapped)',
            ];
        }
    }

    return $options;
}

/**
 * @param list<array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string
 * }> $templateCatalog
 * @return list<array{legacy_pid:string,caption:string}>
 */
function app_project_html_template_options(array $templateCatalog, string $selectedLegacyPid): array
{
    $options = [];
    $templateByPid = app_project_html_template_catalog_by_pid($templateCatalog);

    foreach ($templateCatalog as $template) {
        if (strcasecmp((string) ($template['target_type'] ?? ''), 'html') !== 0) {
            continue;
        }
        if ((int) ($template['parent_html_template_pid'] ?? 0) !== 0) {
            continue;
        }

        $caption = (string) ($template['name'] ?? '');
        $fileName = trim((string) ($template['file_name'] ?? ''));
        if ($fileName !== '') {
            $caption .= ' / ' . $fileName;
        }

        $options[] = [
            'legacy_pid' => (string) ($template['legacy_html_template_pid'] ?? 0),
            'caption' => $caption,
        ];
    }

    usort(
        $options,
        static fn (array $left, array $right): int
            => strnatcasecmp($left['caption'], $right['caption']),
    );

    if ($selectedLegacyPid !== '' && $selectedLegacyPid !== '0') {
        $found = false;
        foreach ($options as $option) {
            if ($option['legacy_pid'] === $selectedLegacyPid) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $fallbackTemplate = $templateByPid[$selectedLegacyPid] ?? null;
            $options[] = [
                'legacy_pid' => $selectedLegacyPid,
                'caption' => $fallbackTemplate !== null
                    ? ((string) ($fallbackTemplate['name'] ?? '') . ' / current selection')
                    : ('Legacy htmlTemplatePID ' . $selectedLegacyPid),
            ];
        }
    }

    return $options;
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_project_htmls_page(array $app, array $request): void
{
    $bootstrap = app_project_html_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $project = $bootstrap['project'];
    $projectKey = $bootstrap['project_key'];
    $reference = $bootstrap['reference'];
    $generatedRuntime = $bootstrap['generated_runtime'];

    $errors = app_project_html_bridge_errors_from_request();
    $input = app_project_html_form_defaults();

    $htmlCatalogResult = app_fetch_project_html_catalog($app, $projectKey, (int) $reference['project_pid']);
    if (!$htmlCatalogResult['ok']) {
        $errors[] = $htmlCatalogResult['error'];
    }
    $htmlCatalog = $htmlCatalogResult['items'];

    $parameterCatalogResult = app_fetch_project_html_parameter_catalog_for_project(
        $app,
        $projectKey,
        (int) $reference['project_pid'],
    );
    if (!$parameterCatalogResult['ok']) {
        $errors[] = $parameterCatalogResult['error'];
    }
    $parameterCatalog = $parameterCatalogResult['items'];
    $parametersByHtmlPid = app_project_html_parameter_catalog_by_html_pid($parameterCatalog);

    $templateCatalogResult = app_fetch_project_html_template_catalog($app, $projectKey);
    if (!$templateCatalogResult['ok']) {
        $errors[] = $templateCatalogResult['error'];
    }
    $templateCatalog = $templateCatalogResult['items'];
    $templateByPid = app_project_html_template_catalog_by_pid($templateCatalog);

    $sourceOutputCatalogResult = app_fetch_project_source_output_catalog($app, $projectKey);
    if (!$sourceOutputCatalogResult['ok']) {
        $errors[] = $sourceOutputCatalogResult['error'];
    }
    $sourceOutputCatalog = $sourceOutputCatalogResult['items'];
    $sourceOutputByKey = app_project_html_source_output_catalog_by_key($sourceOutputCatalog);

    $bootstrapSourceOutputCatalogResult = app_project_html_source_output_catalog_by_legacy_pid($app, $projectKey);
    if (!$bootstrapSourceOutputCatalogResult['ok']) {
        $errors[] = $bootstrapSourceOutputCatalogResult['error'];
    }
    $bootstrapSourceOutputByLegacyPid = $bootstrapSourceOutputCatalogResult['items'];

    $bindingResult = app_fetch_project_html_source_bindings($app, $projectKey);
    if (!$bindingResult['ok']) {
        $errors[] = $bindingResult['error'];
    }
    $persistedBindings = $bindingResult['items'];
    $persistedBindingsByLegacyPid = [];
    foreach ($persistedBindings as $binding) {
        $legacyProjectSourceOutputPid = (string) ($binding['legacy_project_source_output_pid'] ?? 0);
        if ($legacyProjectSourceOutputPid === '0') {
            continue;
        }

        $persistedBindingsByLegacyPid[$legacyProjectSourceOutputPid] = $binding;
    }

    $bindingCatalog = app_project_html_source_binding_catalog(
        $projectKey,
        $persistedBindings,
        $bootstrapSourceOutputByLegacyPid,
        $sourceOutputByKey,
    );

    $created = app_query_param('created') === '1';
    $deleted = app_query_param('deleted') === '1';
    $bindingCreated = app_query_param('binding_created') === '1';
    $bindingUpdated = app_query_param('binding_updated') === '1';
    $bindingDeleted = app_query_param('binding_deleted') === '1';
    $intent = trim(app_query_param('intent'));
    $selectedBindingLegacyPid = max(0, (int) app_query_param('binding_pid', '0'));
    $bindingInput = app_project_html_binding_form_defaults();
    $selectedBinding = $persistedBindingsByLegacyPid[(string) $selectedBindingLegacyPid] ?? null;
    if ($selectedBinding !== null) {
        $bindingInput = app_project_html_binding_form_from_item($selectedBinding);
    } elseif ($selectedBindingLegacyPid > 0) {
        $candidateBinding = $bindingCatalog[(string) $selectedBindingLegacyPid] ?? null;
        if (is_array($candidateBinding)) {
            $bindingInput = app_project_html_binding_form_from_item($candidateBinding);
        } else {
            $bindingInput['legacy_project_source_output_pid'] = (string) $selectedBindingLegacyPid;
        }
    }

    $action = trim(app_post_param('action'));

    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif ($action === 'save-source-binding' || $action === 'delete-source-binding') {
            $postedProjectKey = app_normalize_project_key(app_post_param('project_key'));
            if ($postedProjectKey !== $projectKey) {
                $errors[] = '更新対象の project key が route と一致しません。';
            }

            $bindingInput = app_project_html_binding_form_from_post($bindingInput);
            $selectedBindingLegacyPid = max(0, (int) $bindingInput['legacy_project_source_output_pid']);

            if ($action === 'delete-source-binding') {
                if ($selectedBindingLegacyPid <= 0) {
                    $errors[] = '削除対象の legacy ProjectSourceOutputPID を選択してください。';
                } else {
                    $deleteResult = app_delete_project_html_source_binding(
                        $app,
                        $projectKey,
                        $selectedBindingLegacyPid,
                    );
                    if ($deleteResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_htmls_path($projectKey) . '?binding_deleted=1',
                        );
                        return;
                    }

                    $errors[] = $deleteResult['error'];
                }
            } else {
                $validation = app_project_html_binding_validate_form_input(
                    $projectKey,
                    $bindingInput,
                    $sourceOutputByKey,
                );
                $bindingInput = $validation['input'];
                $errors = array_merge($errors, $validation['errors']);

                if ($errors === []) {
                    $wasPersisted = array_key_exists(
                        $bindingInput['legacy_project_source_output_pid'],
                        $persistedBindingsByLegacyPid,
                    );
                    $saveResult = app_upsert_project_html_source_binding(
                        $app,
                        $projectKey,
                        [
                            'legacy_project_source_output_pid' => (int) $bindingInput['legacy_project_source_output_pid'],
                            'source_output_key' => $bindingInput['source_output_key'],
                            'module_source_ref' => $bindingInput['module_source_ref'],
                            'refresh_policy' => $bindingInput['refresh_policy'],
                            'notes' => $bindingInput['notes'],
                            'source_of_truth' => 'manual',
                        ],
                    );
                    if ($saveResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_htmls_path($projectKey)
                            . '?binding_pid=' . rawurlencode($bindingInput['legacy_project_source_output_pid'])
                            . ($wasPersisted ? '&binding_updated=1' : '&binding_created=1'),
                        );
                        return;
                    }

                    $errors[] = $saveResult['error'];
                }
            }
        } elseif ($action === 'create-html') {
            $input = app_project_html_form_from_post($input);
            $createResult = app_create_project_html(
                $app,
                $projectKey,
                [
                    'project_pid' => (int) $reference['project_pid'],
                    'name' => $input['name'],
                    'legacy_project_source_output_pid' => (int) $input['legacy_project_source_output_pid'],
                    'legacy_html_template_pid' => (int) $input['legacy_html_template_pid'],
                ],
            );

            if ($createResult['ok'] && is_array($createResult['item'])) {
                app_send_redirect_response(
                    $request,
                    app_project_html_detail_path($projectKey, $createResult['item']['html_key']) . '?created=1',
                );
                return;
            }

            $errors[] = $createResult['error'] !== ''
                ? $createResult['error']
                : 'html の追加に失敗しました。';
        } elseif ($action !== '' || $errors === []) {
            $input = app_project_html_form_from_post($input);
            $errors[] = '未対応の操作です。';
        }
    }

    $sourceOutputOptions = app_project_html_source_output_options(
        $bindingCatalog,
        $input['legacy_project_source_output_pid'],
    );
    $bindingLegacyPidOptions = app_project_html_binding_legacy_pid_options(
        $htmlCatalog,
        $bindingCatalog,
        $bindingInput['legacy_project_source_output_pid'],
    );
    $bindingSourceOutputOptions = app_project_html_binding_source_output_options(
        $sourceOutputCatalog,
        $bindingInput['source_output_key'],
    );
    $templateOptions = app_project_html_template_options(
        $templateCatalog,
        $input['legacy_html_template_pid'],
    );

    $groupedItems = [];
    $observedLegacySourceOutputPids = [];
    $canonicalBindingCount = 0;
    $bootstrapBindingCount = 0;
    $bindingResolutionErrorCount = 0;
    foreach ($bindingCatalog as $binding) {
        if (($binding['binding_state'] ?? '') === 'canonical') {
            $canonicalBindingCount++;
        } else {
            $bootstrapBindingCount++;
        }
        if (!($binding['source_root_ok'] ?? false)) {
            $bindingResolutionErrorCount++;
        }
    }

    $unmappedSourceOutputPidCount = 0;
    foreach ($htmlCatalog as $html) {
        $audit = app_legacy_html_reference_parameter_audit_with_actual_items(
            $reference,
            $html,
            $parametersByHtmlPid[(string) $html['legacy_html_pid']] ?? [],
        );
        $template = $templateByPid[(string) $html['legacy_html_template_pid']] ?? null;
        $legacyProjectSourceOutputPid = (int) ($html['legacy_project_source_output_pid'] ?? 0);
        if ($legacyProjectSourceOutputPid > 0) {
            $observedLegacySourceOutputPids[(string) $legacyProjectSourceOutputPid] = true;
        }
        $legacySourceOutputPid = (string) $legacyProjectSourceOutputPid;
        $binding = $bindingCatalog[$legacySourceOutputPid] ?? null;

        $bucket = [
            'bucket_key' => 'legacy:' . $legacySourceOutputPid,
            'title' => $legacyProjectSourceOutputPid > 0
                ? 'Legacy ProjectSourceOutputPID ' . $legacyProjectSourceOutputPid
                : 'Project Source Output not assigned yet',
            'current_link' => '',
            'binding' => null,
            'items' => [],
        ];
        if (is_array($binding)) {
            $bucketTitle = 'Legacy ProjectSourceOutputPID ' . $legacyProjectSourceOutputPid;
            if ($binding['source_output_key'] !== '') {
                $bucketTitle .= ' -> ' . $binding['source_output_key'];
                if ($binding['source_output_name'] !== '') {
                    $bucketTitle .= ' / ' . $binding['source_output_name'];
                }
            }

            $bucket = [
                'bucket_key' => 'legacy:' . $legacySourceOutputPid,
                'title' => $bucketTitle,
                'current_link' => $binding['source_output_key'] !== ''
                    ? (
                        '/projects/' . rawurlencode($projectKey)
                        . '/source-outputs/' . rawurlencode($binding['source_output_key'])
                    )
                    : '',
                'binding' => $binding,
                'items' => [],
            ];
        } elseif ($legacyProjectSourceOutputPid > 0) {
            $unmappedSourceOutputPidCount++;
        }

        if (!array_key_exists($bucket['bucket_key'], $groupedItems)) {
            $groupedItems[$bucket['bucket_key']] = $bucket;
        }

        $groupedItems[$bucket['bucket_key']]['items'][] = [
            'html' => $html,
            'template' => $template,
            'audit' => $audit,
        ];
    }

    uasort(
        $groupedItems,
        static fn (array $left, array $right): int
            => strnatcasecmp($left['title'], $right['title']),
    );

    $showCreateForm = $intent === 'create'
        || $action === 'create-html'
        || (
            $errors !== []
            && !app_request_method_is($request, 'GET')
            && !in_array($action, ['save-source-binding', 'delete-source-binding'], true)
        );

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project HTML</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 96rem;
            background: #ffffff;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 2rem;
        }
        code {
            background: #edf2f7;
            padding: 0.1rem 0.3rem;
            border-radius: 4px;
        }
        .breadcrumbs {
            margin-bottom: 1rem;
        }
        .summary-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            margin: 1.5rem 0;
        }
        .summary-card, .note-card, .warning-card, .error-card, .success-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
        }
        .summary-card {
            background: #f8fafc;
        }
        .note-card {
            background: #eff6ff;
            border-color: #93c5fd;
        }
        .warning-card {
            background: #fefce8;
            border-color: #facc15;
        }
        .error-card {
            background: #fef2f2;
            border-color: #fca5a5;
        }
        .success-card {
            background: #dcfce7;
            border-color: #86efac;
        }
        form {
            margin-top: 1.5rem;
            padding: 1.5rem;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            background: #f8fafc;
        }
        label {
            display: block;
            font-weight: 600;
            margin-top: 1rem;
        }
        input, select, textarea, button {
            font: inherit;
        }
        input, select, textarea {
            width: 100%;
            box-sizing: border-box;
            margin-top: 0.35rem;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
        }
        textarea {
            min-height: 7rem;
            resize: vertical;
        }
        .button-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        button {
            margin-top: 1rem;
            padding: 0.7rem 1rem;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
        }
        button.danger {
            background: #991b1b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            border-bottom: 1px solid #d7dde5;
            padding: 0.75rem;
            vertical-align: top;
            text-align: left;
        }
        .bucket {
            margin-top: 2rem;
        }
        .muted {
            color: #475569;
        }
        .status-ok {
            color: #166534;
            font-weight: 600;
        }
        .status-warn {
            color: #b45309;
            font-weight: 600;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / html</p>

    <h1><?php echo app_h($project['name']); ?> HTML Authoring</h1>
    <p>current route で canonical <code>project_html_definitions</code> / <code>project_html_parameters</code> を扱い、template metadata も canonical <code>html_templates</code> / <code>html_template_parameters</code> を優先します。legacy <code>html_edit.php</code> の add flow はこの画面に吸収しました。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Live Rows</h2>
            <ul>
                <li>html rows: <code><?php echo app_h((string) count($htmlCatalog)); ?></code></li>
                <li>parameter rows: <code><?php echo app_h((string) count($parameterCatalog)); ?></code></li>
                <li>template rows: <code><?php echo app_h((string) count($templateCatalog)); ?></code></li>
                <li>dbclasses mode: <code><?php echo app_h($generatedRuntime['dbclasses_mode']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Reference</h2>
            <ul>
                <li>generated at: <code><?php echo app_h($reference['generated_at']); ?></code></li>
                <li>reference html rows: <code><?php echo app_h((string) $reference['html_count']); ?></code></li>
                <li>reference parameter rows: <code><?php echo app_h((string) $reference['parameter_count']); ?></code></li>
                <li>reference templates: <code><?php echo app_h((string) $reference['template_count']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Source Output Mapping</h2>
            <ul>
                <li>observed legacy buckets: <code><?php echo app_h((string) count($observedLegacySourceOutputPids)); ?></code></li>
                <li>canonical bindings: <code><?php echo app_h((string) $canonicalBindingCount); ?></code></li>
                <li>bootstrap-only bindings: <code><?php echo app_h((string) $bootstrapBindingCount); ?></code></li>
                <li>unmapped legacy pid count: <code><?php echo app_h((string) $unmappedSourceOutputPidCount); ?></code></li>
                <li>source root resolve errors: <code><?php echo app_h((string) $bindingResolutionErrorCount); ?></code></li>
                <li>db name: <code><?php echo app_h($app['db']['name']); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>Actions</h2>
            <ul>
                <li><a href="<?php echo app_h(app_project_htmls_path($projectKey) . '?intent=create'); ?>">add html</a></li>
                <li><a href="<?php echo app_h(app_html_templates_path()); ?>">global template settings</a></li>
                <li>HTML source binding はこの画面の binding editor で管理します</li>
                <li>existing html の basic info は detail 画面で更新します</li>
                <li>parameter edit は parameter list 画面で行います</li>
            </ul>
        </section>
    </div>

    <?php if ($created): ?>
        <section class="success-card">
            <h2>Created</h2>
            <p>html row を追加しました。</p>
        </section>
    <?php endif; ?>

    <?php if ($deleted): ?>
        <section class="success-card">
            <h2>Deleted</h2>
            <p>html row を削除しました。</p>
        </section>
    <?php endif; ?>

    <?php if ($bindingCreated): ?>
        <section class="success-card">
            <h2>Binding Created</h2>
            <p>HTML source binding を追加しました。</p>
        </section>
    <?php endif; ?>

    <?php if ($bindingUpdated): ?>
        <section class="success-card">
            <h2>Binding Updated</h2>
            <p>HTML source binding を更新しました。</p>
        </section>
    <?php endif; ?>

    <?php if ($bindingDeleted): ?>
        <section class="success-card">
            <h2>Binding Deleted</h2>
            <p>HTML source binding を削除しました。</p>
        </section>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <section class="error-card">
            <h2>Errors</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <section class="bucket">
        <h2>HTML Source Bindings</h2>
        <p class="muted">legacy <code>ProjectSourceOutputPID</code> ごとに、current <code>source_output_key</code> と effective html module source ref を保持します。persist 済み row がない bucket は bootstrap candidate として表示します。</p>

        <?php if ($bindingCatalog !== []): ?>
            <table>
                <thead>
                <tr>
                    <th>legacy pid</th>
                    <th>current source output</th>
                    <th>effective source ref</th>
                    <th>source root</th>
                    <th>status</th>
                    <th>action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($bindingCatalog as $binding): ?>
                    <?php
                    $editBindingPath = app_project_htmls_path($projectKey)
                        . '?binding_pid=' . rawurlencode((string) $binding['legacy_project_source_output_pid']);
                    $sourceOutputLink = $binding['source_output_key'] !== ''
                        ? (
                            '/projects/' . rawurlencode($projectKey)
                            . '/source-outputs/' . rawurlencode($binding['source_output_key'])
                        )
                        : '';
                    ?>
                    <tr>
                        <td><code><?php echo app_h((string) $binding['legacy_project_source_output_pid']); ?></code></td>
                        <td>
                            <?php if ($sourceOutputLink !== ''): ?>
                                <a href="<?php echo app_h($sourceOutputLink); ?>"><code><?php echo app_h($binding['source_output_key']); ?></code></a>
                            <?php elseif ($binding['source_output_key'] !== ''): ?>
                                <code><?php echo app_h($binding['source_output_key']); ?></code>
                            <?php else: ?>
                                <span class="muted">unmapped</span>
                            <?php endif; ?>
                            <?php if ($binding['source_output_name'] !== ''): ?>
                                <br><span class="muted"><?php echo app_h($binding['source_output_name']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($binding['effective_source_ref'] !== ''): ?>
                                <code><?php echo app_h($binding['effective_source_ref']); ?></code>
                            <?php else: ?>
                                <span class="muted">not set</span>
                            <?php endif; ?>
                            <?php if (
                                $binding['effective_source_output_key'] !== ''
                                && $binding['effective_source_output_key'] !== $binding['source_output_key']
                            ): ?>
                                <br><span class="muted">effective key: <code><?php echo app_h($binding['effective_source_output_key']); ?></code></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($binding['source_root_ok']): ?>
                                <code><?php echo app_h($binding['source_root_relative_path']); ?></code><br>
                                <span class="muted"><?php echo app_h(app_project_output_html_module_source_kind_caption($binding['source_kind'])); ?></span>
                            <?php else: ?>
                                <span class="status-warn">unresolved</span><br>
                                <span class="muted"><?php echo app_h($binding['source_root_error']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code><?php echo app_h($binding['binding_state']); ?></code><br>
                            <span class="muted">refresh: <?php echo app_h($binding['refresh_policy']); ?></span><br>
                            <span class="muted">source: <?php echo app_h($binding['source_of_truth']); ?></span>
                        </td>
                        <td><a href="<?php echo app_h($editBindingPath); ?>">edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="muted">binding row はまだありません。</p>
        <?php endif; ?>

        <form method="post" action="<?php echo app_h(app_project_htmls_path($projectKey)); ?>">
            <h3>Binding Editor</h3>
            <p class="muted">
                <?php
                $selectedBindingCatalogItem = $bindingCatalog[$bindingInput['legacy_project_source_output_pid']] ?? null;
                echo app_h(
                    is_array($selectedBindingCatalogItem) && !($selectedBindingCatalogItem['is_persisted'] ?? false)
                        ? 'bootstrap candidate を current binding へ昇格できます。'
                        : 'legacy bucket ごとの current binding を更新します。'
                );
                ?>
            </p>
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="project_key" value="<?php echo app_h($projectKey); ?>">

            <label for="binding-legacy-pid">Legacy ProjectSourceOutputPID</label>
            <select id="binding-legacy-pid" name="binding_legacy_project_source_output_pid">
                <option value="0">(select legacy pid)</option>
                <?php foreach ($bindingLegacyPidOptions as $option): ?>
                    <option value="<?php echo app_h($option['legacy_pid']); ?>"<?php echo $bindingInput['legacy_project_source_output_pid'] === $option['legacy_pid'] ? ' selected' : ''; ?>>
                        <?php echo app_h($option['caption']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="binding-source-output-key">Current Source Output</label>
            <select id="binding-source-output-key" name="binding_source_output_key">
                <option value="">(select source output)</option>
                <?php foreach ($bindingSourceOutputOptions as $option): ?>
                    <option value="<?php echo app_h($option['source_output_key']); ?>"<?php echo $bindingInput['source_output_key'] === $option['source_output_key'] ? ' selected' : ''; ?>>
                        <?php echo app_h($option['caption']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="binding-refresh-policy">Refresh Policy</label>
            <select id="binding-refresh-policy" name="binding_refresh_policy">
                <?php foreach (app_allowed_project_html_source_binding_refresh_policies() as $refreshPolicy): ?>
                    <option value="<?php echo app_h($refreshPolicy); ?>"<?php echo $bindingInput['refresh_policy'] === $refreshPolicy ? ' selected' : ''; ?>>
                        <?php echo app_h($refreshPolicy); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="binding-module-source-ref">module_source_ref</label>
            <input
                id="binding-module-source-ref"
                type="text"
                name="binding_module_source_ref"
                value="<?php echo app_h($bindingInput['module_source_ref']); ?>"
                placeholder="catalog://html-module/MTOOL/HTML-DB"
            >
            <p class="muted"><code>follow-source-output</code> は selected <code>source_output_key</code> から effective ref を再計算し、<code>manual</code> は入力した ref を固定します。</p>

            <label for="binding-notes">notes</label>
            <textarea id="binding-notes" name="binding_notes" placeholder="binding memo / refresh policy note"><?php echo app_h($bindingInput['notes']); ?></textarea>

            <div class="button-row">
                <button type="submit" name="action" value="save-source-binding">Save Binding</button>
                <?php if (array_key_exists($bindingInput['legacy_project_source_output_pid'], $persistedBindingsByLegacyPid)): ?>
                    <button class="danger" type="submit" name="action" value="delete-source-binding" onclick="return confirm('Delete this HTML source binding?');">Delete Binding</button>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <?php if ($showCreateForm): ?>
        <form method="post" action="<?php echo app_h(app_project_htmls_path($projectKey)); ?>">
            <h2>Add HTML</h2>
            <p class="muted">legacy <code>html_edit.php</code> の add flow を current route で受けます。</p>
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="action" value="create-html">

            <label for="html-name">Name</label>
            <input id="html-name" type="text" name="name" value="<?php echo app_h($input['name']); ?>">

            <label for="html-source-output">Source Output</label>
            <select id="html-source-output" name="legacy_project_source_output_pid">
                <option value="0">(none)</option>
                <?php foreach ($sourceOutputOptions as $option): ?>
                    <option value="<?php echo app_h($option['legacy_pid']); ?>"<?php echo $input['legacy_project_source_output_pid'] === $option['legacy_pid'] ? ' selected' : ''; ?>>
                        <?php echo app_h($option['caption']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="html-template">HTML Template</label>
            <select id="html-template" name="legacy_html_template_pid">
                <option value="0">(none)</option>
                <?php foreach ($templateOptions as $option): ?>
                    <option value="<?php echo app_h($option['legacy_pid']); ?>"<?php echo $input['legacy_html_template_pid'] === $option['legacy_pid'] ? ' selected' : ''; ?>>
                        <?php echo app_h($option['caption']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Add HTML</button>
        </form>
    <?php endif; ?>

    <?php foreach ($groupedItems as $group): ?>
        <section class="bucket">
            <h2><?php echo app_h($group['title']); ?></h2>
            <?php if ($group['current_link'] !== ''): ?>
                <p class="muted"><a href="<?php echo app_h($group['current_link']); ?>">source output detail</a></p>
            <?php endif; ?>
            <?php if (is_array($group['binding'])): ?>
                <p class="muted">
                    <?php echo app_h($group['binding']['binding_state'] === 'canonical' ? 'current binding' : 'bootstrap candidate'); ?>
                    /
                    refresh: <code><?php echo app_h($group['binding']['refresh_policy']); ?></code>
                    /
                    effective ref: <code><?php echo app_h($group['binding']['effective_source_ref']); ?></code>
                    <?php if (
                        $group['binding']['effective_source_output_key'] !== ''
                        && $group['binding']['effective_source_output_key'] !== $group['binding']['source_output_key']
                    ): ?>
                        /
                        effective key: <code><?php echo app_h($group['binding']['effective_source_output_key']); ?></code>
                    <?php endif; ?>
                    <?php if ($group['binding']['source_root_ok']): ?>
                        /
                        root: <code><?php echo app_h($group['binding']['source_root_relative_path']); ?></code>
                    <?php else: ?>
                        /
                        root unresolved: <?php echo app_h($group['binding']['source_root_error']); ?>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <table>
                <thead>
                <tr>
                    <th>html</th>
                    <th>template</th>
                    <th>parameter status</th>
                    <th>action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($group['items'] as $row): ?>
                    <?php
                    $html = $row['html'];
                    $template = $row['template'];
                    $audit = $row['audit'];
                    $detailPath = app_project_html_detail_path($projectKey, $html['html_key']);
                    $parametersPath = app_project_html_parameters_path($projectKey, $html['html_key']);
                    $statusClass = $audit['is_complete'] ? 'status-ok' : 'status-warn';
                    $statusText = $audit['is_complete']
                        ? 'All parameter set'
                        : ($audit['has_duplicate_matches']
                            ? 'Warning: duplicated parameter setting'
                            : 'Warning: not all parameter set');
                    ?>
                    <tr>
                        <td>
                            <code><?php echo app_h($html['html_key']); ?></code><br>
                            <?php echo app_h($html['name']); ?><br>
                            <span class="muted">legacy PID: <code><?php echo app_h((string) $html['legacy_html_pid']); ?></code></span><br>
                            <span class="muted">last modified: <code><?php echo app_h($html['last_modified_dt']); ?></code></span>
                        </td>
                        <td>
                            <?php if ($template !== null): ?>
                                <code><?php echo app_h($template['name']); ?></code><br>
                                <span class="muted">file: <code><?php echo app_h($template['file_name']); ?></code></span>
                                <?php if ($template['comment'] !== ''): ?>
                                    <br><span class="muted"><?php echo app_h($template['comment']); ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="muted">template not found</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="<?php echo app_h($statusClass); ?>"><?php echo app_h($statusText); ?></span><br>
                            <span class="muted">
                                expected <code><?php echo app_h((string) $audit['expected_count']); ?></code>,
                                actual <code><?php echo app_h((string) $audit['actual_count']); ?></code>
                            </span>
                            <?php if ($audit['missing_parameter_names'] !== []): ?>
                                <br><span class="muted">missing: <?php echo app_h(implode(', ', $audit['missing_parameter_names'])); ?></span>
                            <?php endif; ?>
                            <?php if ($audit['duplicate_parameter_names'] !== []): ?>
                                <br><span class="muted">duplicate: <?php echo app_h(implode(', ', $audit['duplicate_parameter_names'])); ?></span>
                            <?php endif; ?>
                            <?php if ($audit['unexpected_items'] !== []): ?>
                                <br><span class="muted">unused actual rows: <code><?php echo app_h((string) count($audit['unexpected_items'])); ?></code></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo app_h($detailPath); ?>">detail</a><br>
                            <a href="<?php echo app_h($detailPath . '?intent=edit'); ?>">edit</a><br>
                            <a href="<?php echo app_h($parametersPath); ?>">parameters</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endforeach; ?>
</main>
</body>
</html>
    <?php
}
