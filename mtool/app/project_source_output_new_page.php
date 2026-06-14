<?php

declare(strict_types=1);

require_once __DIR__ . '/project_source_output_edit_page.php';

/**
 * @return array{
 *     ok:bool,
 *     value:string,
 *     error:string
 * }
 */
function app_project_source_output_next_order(array $app, string $projectKey): array
{
    $catalogResult = app_fetch_project_source_output_catalog($app, $projectKey);
    if (!$catalogResult['ok']) {
        return [
            'ok' => false,
            'value' => '100',
            'error' => $catalogResult['error'],
        ];
    }

    $nextOrder = 10;
    foreach ($catalogResult['items'] as $item) {
        $itemOrder = (int) ($item['source_output_list_order'] ?? 0);
        if ($itemOrder >= $nextOrder) {
            $nextOrder = $itemOrder + 10;
        }
    }

    return [
        'ok' => true,
        'value' => (string) $nextOrder,
        'error' => '',
    ];
}

/**
 * @return array{
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
 *     source_of_truth:string
 * }
 */
function app_project_source_output_new_defaults(array $app, string $projectKey): array
{
    $defaults = app_source_output_form_defaults();
    $nextOrder = app_project_source_output_next_order($app, $projectKey);
    $defaults['source_output_list_order'] = $nextOrder['value'];
    return $defaults;
}

/**
 * @return array{
 *     legacy_target_server_source_output_key:string,
 *     legacy_source_output_dir:string,
 *     legacy_source_template_dir:string
 * }
 */
function app_project_source_output_new_bridge_hints_from_request(): array
{
    $hints = [
        'legacy_target_server_source_output_key' => '',
        'legacy_source_output_dir' => '',
        'legacy_source_template_dir' => '',
    ];

    foreach (array_keys($hints) as $field) {
        $value = $_POST[$field] ?? ($_GET[$field] ?? null);
        if (is_array($value)) {
            continue;
        }

        if (is_string($value) || is_numeric($value)) {
            $hints[$field] = trim((string) $value);
        }
    }

    $hints['legacy_target_server_source_output_key'] = app_normalize_source_output_key(
        $hints['legacy_target_server_source_output_key'],
    );

    return $hints;
}

/**
 * @param array{
 *     legacy_target_server_source_output_key:string,
 *     legacy_source_output_dir:string,
 *     legacy_source_template_dir:string
 * } $bridgeHints
 * @return array{
 *     target_binding_type:string,
 *     artifact_strategy:string,
 *     proxy_base_url:string
 * }
 */
function app_project_source_output_new_bridge_target_server_context(
    array $app,
    string $projectKey,
    array $bridgeHints,
): array {
    $context = [
        'target_binding_type' => '',
        'artifact_strategy' => '',
        'proxy_base_url' => '',
    ];

    $sourceOutputKey = $bridgeHints['legacy_target_server_source_output_key'];
    if ($sourceOutputKey === '') {
        return $context;
    }

    $itemResult = app_fetch_project_source_output_item($app, $projectKey, $sourceOutputKey);
    if (!$itemResult['ok'] || $itemResult['item'] === null) {
        return $context;
    }

    $item = $itemResult['item'];

    return [
        'target_binding_type' => trim((string) ($item['target_binding_type'] ?? '')),
        'artifact_strategy' => trim((string) ($item['artifact_strategy'] ?? '')),
        'proxy_base_url' => trim((string) ($item['proxy_base_url'] ?? '')),
    ];
}

/**
 * @return array<string,bool>
 */
function app_project_source_output_new_existing_keys(array $app, string $projectKey): array
{
    $catalogResult = app_fetch_project_source_output_catalog($app, $projectKey);
    if (!$catalogResult['ok']) {
        return [];
    }

    $keys = [];
    foreach ($catalogResult['items'] as $item) {
        $sourceOutputKey = app_normalize_source_output_key((string) ($item['source_output_key'] ?? ''));
        if ($sourceOutputKey === '') {
            continue;
        }

        $keys[$sourceOutputKey] = true;
    }

    return $keys;
}

function app_project_source_output_new_bridge_project_display_name(string $projectName, string $projectKey): string
{
    $normalizedProjectName = trim($projectName);
    if ($normalizedProjectName !== '') {
        return $normalizedProjectName;
    }

    $normalizedProjectKey = strtolower(trim($projectKey));
    if ($normalizedProjectKey === '') {
        return 'Project';
    }

    return ucfirst($normalizedProjectKey);
}

function app_project_source_output_new_candidate_key_segment(string $value): string
{
    $normalized = preg_replace('/[^A-Za-z0-9]+/', '', strtoupper(trim($value)));
    return is_string($normalized) ? $normalized : '';
}

function app_project_source_output_new_candidate_display_phrase(string $token): string
{
    return match ($token) {
        'APACHE' => 'Apache',
        'CHAT' => 'Chat',
        'CLIENT' => 'Client',
        'COMMON' => 'Common',
        'COMMONSTRINGS' => 'Common Strings',
        'CS' => 'CSharp',
        'DB' => 'DB',
        'DBBACKUP' => 'DBBackup',
        'DBCONNECTION' => 'DBConnection',
        'DBIMPORT' => 'DB Import',
        'DBUSER' => 'DBUser',
        'DEGODB' => 'DegoDB',
        'DEV' => 'Dev',
        'DROPBOX' => 'Dropbox',
        'HTMLTEMPLATE' => 'HtmlTemplate',
        'INTERNALUSER' => 'Internal User',
        'IOS' => 'iOS',
        'JAVA' => 'Java',
        'JSON' => 'JSON',
        'LIB' => 'Lib',
        'MATSUESOFT' => 'Matsuesoft',
        'MINUTES' => 'Minutes',
        'MTOOL' => 'Mtool',
        'OPENAPI' => 'OpenAPI',
        'PAYPAL' => 'Paypal',
        'PHP' => 'PHP',
        'PROJECTGROUP' => 'ProjectGroup',
        'PUBLIC' => 'Public',
        'REQ' => 'Req',
        'RESOURCES' => 'Resources',
        'SECURITY' => 'Security',
        'SERVER' => 'Server',
        'SETTINGS' => 'Settings',
        'SPECIALHOLIDAY' => 'Special Holiday',
        'SPEC' => 'Spec',
        'SWIFT' => 'Swift',
        'SYSTEMSETTINGS' => 'System Settings',
        'TEST' => 'Test',
        'TOP' => 'Top',
        'UPLOADER' => 'Uploader',
        'UWP' => 'UWP',
        'WEB' => 'Web',
        default => ucfirst(strtolower($token)),
    };
}

function app_project_source_output_new_candidate_display_label(string $token): string
{
    $normalizedToken = app_normalize_source_output_key($token);
    if ($normalizedToken === '') {
        $normalizedToken = app_project_source_output_new_candidate_key_segment($token);
    }
    if ($normalizedToken === '') {
        return '';
    }

    $segments = array_values(
        array_filter(
            explode('-', $normalizedToken),
            static fn (string $segment): bool => $segment !== '',
        ),
    );
    if ($segments === []) {
        return '';
    }

    return implode(
        ' ',
        array_map(
            static fn (string $segment): string => app_project_source_output_new_candidate_display_phrase($segment),
            $segments,
        ),
    );
}

function app_project_source_output_new_has_bridge_hints(array $bridgeHints): bool
{
    foreach ($bridgeHints as $value) {
        if (trim((string) $value) !== '') {
            return true;
        }
    }

    return false;
}

/**
 * @param array{
 *     source_output_key:string,
 *     name:string,
 *     reason:string
 * } $candidate
 * @return array{
 *     mode:string,
 *     warnings:list<string>,
 *     candidates:list<array{
 *         source_output_key:string,
 *         name:string,
 *         reason:string
 *     }>,
 *     prefill:array{
 *         source_output_key:string,
 *         name:string
 *     }
 * }
 */
function app_project_source_output_new_bridge_identity_policy_with_prefill(array $candidate): array
{
    return [
        'mode' => 'safe-prefill',
        'warnings' => [],
        'candidates' => [$candidate],
        'prefill' => [
            'source_output_key' => $candidate['source_output_key'],
            'name' => $candidate['name'],
        ],
    ];
}

/**
 * @return array{
 *     mode:string,
 *     warnings:list<string>,
 *     candidates:list<array{
 *         source_output_key:string,
 *         name:string,
 *         reason:string
 *     }>,
 *     prefill:array{
 *         source_output_key:string,
 *         name:string
 *     }
 * }
 */
function app_project_source_output_new_bridge_identity_policy_empty(): array
{
    return [
        'mode' => 'none',
        'warnings' => [],
        'candidates' => [],
        'prefill' => [
            'source_output_key' => '',
            'name' => '',
        ],
    ];
}

/**
 * @param array{
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
 *     source_of_truth:string
 * } $input
 * @param array{
 *     legacy_target_server_source_output_key:string,
 *     legacy_source_output_dir:string,
 *     legacy_source_template_dir:string
 * } $bridgeHints
 * @return list<array{
 *     label:string,
 *     value:string
 * }>
 */
function app_project_source_output_new_bridge_candidate_sources(array $input, array $bridgeHints): array
{
    $items = [];
    foreach (
        [
            ['label' => 'proxy_base_url', 'value' => $input['proxy_base_url']],
            ['label' => 'legacy SourceOutputDir', 'value' => $bridgeHints['legacy_source_output_dir']],
            ['label' => 'source_output_dir', 'value' => $input['source_output_dir']],
            ['label' => 'legacy SourceTemplateDir', 'value' => $bridgeHints['legacy_source_template_dir']],
            ['label' => 'source_template_dir', 'value' => $input['source_template_dir']],
        ] as $item
    ) {
        $normalizedValue = trim(str_replace('\\', '/', (string) $item['value']));
        if ($normalizedValue === '') {
            continue;
        }

        if (!isset($items[$normalizedValue])) {
            $items[$normalizedValue] = [
                'value' => $normalizedValue,
                'labels' => [],
            ];
        }

        if (!in_array($item['label'], $items[$normalizedValue]['labels'], true)) {
            $items[$normalizedValue]['labels'][] = $item['label'];
        }
    }

    $sources = [];
    foreach ($items as $item) {
        $sources[] = [
            'label' => implode(' / ', $item['labels']),
            'value' => $item['value'],
        ];
    }

    return $sources;
}

/**
 * @param array{
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
 *     source_of_truth:string
 * } $input
 * @param array{
 *     legacy_target_server_source_output_key:string,
 *     legacy_source_output_dir:string,
 *     legacy_source_template_dir:string
 * } $bridgeHints
 * @return list<string>
 */
function app_project_source_output_new_bridge_candidate_texts(array $input, array $bridgeHints): array
{
    return array_values(
        array_map(
            static fn (array $candidateSource): string => $candidateSource['value'],
            app_project_source_output_new_bridge_candidate_sources($input, $bridgeHints),
        ),
    );
}

function app_project_source_output_new_bridge_html_relative_path(string $candidate): string
{
    $normalized = strtolower(trim(str_replace('\\', '/', $candidate)));
    if ($normalized === '') {
        return '';
    }

    if (preg_match('~(?:^|/)(?:dev|ja|www)\.matsuesoft\.com/([^?]+)$~', $normalized, $matches) === 1) {
        return trim((string) ($matches[1] ?? ''), '/');
    }

    if (
        preg_match(
            '~(?:^|/)((?:settings|systemsettings)(?:/[^/]+)?|db|chat|minutes|req|spec|test)$~',
            $normalized,
            $matches,
        ) === 1
    ) {
        return trim((string) ($matches[1] ?? ''), '/');
    }

    return '';
}

/**
 * @return array{
 *     source_output_key:string,
 *     name:string
 * }
 */
function app_project_source_output_new_bridge_html_identity_from_relative_path(
    string $projectDisplayName,
    string $relativePath,
): array {
    $segments = array_values(
        array_filter(
            array_map(
                static fn (string $segment): string => app_project_source_output_new_candidate_key_segment($segment),
                explode('/', $relativePath),
            ),
            static fn (string $segment): bool => $segment !== '',
        ),
    );
    if ($segments === []) {
        return ['source_output_key' => '', 'name' => ''];
    }

    if ($segments === ['SETTINGS']) {
        $segments[] = 'TOP';
    }

    return [
        'source_output_key' => 'HTML-' . implode('-', $segments),
        'name' => $projectDisplayName
            . ' HTML '
            . implode(
                ' ',
                array_map(
                    static fn (string $segment): string => app_project_source_output_new_candidate_display_phrase($segment),
                    $segments,
                ),
            )
            . ' Module',
    ];
}

/**
 * @param list<array{
 *     source_output_key:string,
 *     name:string,
 *     reason:string
 * }> $candidates
 * @return list<array{
 *     source_output_key:string,
 *     name:string,
 *     reason:string
 * }>
 */
function app_project_source_output_new_bridge_merge_identity_candidates(array $candidates): array
{
    $merged = [];
    foreach ($candidates as $candidate) {
        $sourceOutputKey = app_normalize_source_output_key($candidate['source_output_key']);
        $name = trim($candidate['name']);
        $reason = trim($candidate['reason']);
        if ($sourceOutputKey === '' || $name === '') {
            continue;
        }

        $candidateId = $sourceOutputKey . "\n" . $name;
        if (!isset($merged[$candidateId])) {
            $merged[$candidateId] = [
                'source_output_key' => $sourceOutputKey,
                'name' => $name,
                'reason' => $reason,
            ];
            continue;
        }

        if ($reason !== '' && !str_contains($merged[$candidateId]['reason'], $reason)) {
            $merged[$candidateId]['reason'] = $merged[$candidateId]['reason'] === ''
                ? $reason
                : $merged[$candidateId]['reason'] . ' / ' . $reason;
        }
    }

    return array_values($merged);
}

/**
 * @param list<array{
 *     source_output_key:string,
 *     name:string,
 *     reason:string
 * }> $candidates
 * @param array<string,bool> $existingKeys
 * @return list<array{
 *     source_output_key:string,
 *     name:string,
 *     reason:string
 * }>
 */
function app_project_source_output_new_bridge_available_candidates(array $candidates, array $existingKeys): array
{
    return array_values(
        array_filter(
            $candidates,
            static fn (array $candidate): bool => !array_key_exists($candidate['source_output_key'], $existingKeys),
        ),
    );
}

/**
 * @param list<array{
 *     source_output_key:string,
 *     name:string,
 *     reason:string
 * }> $candidates
 * @param array<string,bool> $existingKeys
 * @return array{
 *     mode:string,
 *     warnings:list<string>,
 *     candidates:list<array{
 *         source_output_key:string,
 *         name:string,
 *         reason:string
 *     }>,
 *     prefill:array{
 *         source_output_key:string,
 *         name:string
 *     }
 * }
 */
function app_project_source_output_new_bridge_identity_policy_from_candidates(
    array $candidates,
    array $existingKeys,
    string $noCandidateWarning,
    string $multiCandidateWarning,
    string $collisionWarning,
): array {
    if ($candidates === []) {
        $policy = app_project_source_output_new_bridge_identity_policy_empty();
        $policy['mode'] = 'manual-only';
        $policy['warnings'][] = $noCandidateWarning;
        return $policy;
    }

    $availableCandidates = app_project_source_output_new_bridge_available_candidates($candidates, $existingKeys);
    if (count($candidates) === 1 && count($availableCandidates) === 1) {
        return app_project_source_output_new_bridge_identity_policy_with_prefill($availableCandidates[0]);
    }

    if ($availableCandidates === []) {
        $policy = app_project_source_output_new_bridge_identity_policy_empty();
        $policy['mode'] = 'manual-only';
        $policy['warnings'][] = $collisionWarning;
        if (count($candidates) > 1) {
            $policy['warnings'][] = '複数候補はありましたが、すべて既存 key と衝突しました。';
        }
        return $policy;
    }

    $policy = app_project_source_output_new_bridge_identity_policy_empty();
    $policy['mode'] = 'warning-candidate';
    $policy['warnings'][] = $multiCandidateWarning;
    if (count($availableCandidates) < count($candidates)) {
        $policy['warnings'][] = '既存 key と衝突した候補は一覧から除外しています。';
    }
    $policy['candidates'] = $availableCandidates;
    return $policy;
}

/**
 * @param array{
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
 *     source_of_truth:string
 * } $input
 * @param array{
 *     legacy_target_server_source_output_key:string,
 *     legacy_source_output_dir:string,
 *     legacy_source_template_dir:string
 * } $bridgeHints
 * @return list<array{
 *     source_output_key:string,
 *     name:string,
 *     reason:string
 * }>
 */
function app_project_source_output_new_bridge_html_candidates(
    string $projectDisplayName,
    array $input,
    array $bridgeHints,
): array {
    $candidates = [];
    foreach (app_project_source_output_new_bridge_candidate_sources($input, $bridgeHints) as $candidateSource) {
        $relativePath = app_project_source_output_new_bridge_html_relative_path($candidateSource['value']);
        if ($relativePath === '') {
            continue;
        }

        $identity = app_project_source_output_new_bridge_html_identity_from_relative_path(
            $projectDisplayName,
            $relativePath,
        );
        if ($identity['source_output_key'] === '' || $identity['name'] === '') {
            continue;
        }

        $candidates[] = [
            'source_output_key' => $identity['source_output_key'],
            'name' => $identity['name'],
            'reason' => 'matched ' . $candidateSource['label'],
        ];
    }

    return app_project_source_output_new_bridge_merge_identity_candidates($candidates);
}

/**
 * @param array{
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
 *     source_of_truth:string
 * } $input
 * @param array{
 *     legacy_target_server_source_output_key:string,
 *     legacy_source_output_dir:string,
 *     legacy_source_template_dir:string
 * } $bridgeHints
 * @return array{
 *     source_output_key:string,
 *     name:string
 * }
 */
function app_project_source_output_new_bridge_html_identity(
    string $projectDisplayName,
    array $input,
    array $bridgeHints,
): array {
    $candidates = app_project_source_output_new_bridge_html_candidates($projectDisplayName, $input, $bridgeHints);
    if (count($candidates) !== 1) {
        return ['source_output_key' => '', 'name' => ''];
    }

    return [
        'source_output_key' => $candidates[0]['source_output_key'],
        'name' => $candidates[0]['name'],
    ];
}

function app_project_source_output_new_bridge_proxy_transport(string $classType): string
{
    return in_array(trim($classType), ['ProxyClient', 'DBaaSProxyClient'], true) ? 'CLIENT' : 'SERVER';
}

function app_project_source_output_new_bridge_proxy_token_from_source_value(string $value): string
{
    $normalized = trim($value);
    if ($normalized === '') {
        return '';
    }

    if (preg_match('~^[a-z][a-z0-9+.-]*://~i', $normalized) === 1) {
        $parsedPath = parse_url($normalized, PHP_URL_PATH);
        if (is_string($parsedPath)) {
            $normalized = $parsedPath;
        }
    }

    $normalized = str_replace('\\', '/', $normalized);
    $basename = basename(trim($normalized, '/'));
    $basename = preg_replace('/^proxy(?:[_-]?)/i', '', $basename);
    if (!is_string($basename) || trim($basename) === '') {
        return '';
    }

    $proxyToken = app_project_source_output_new_candidate_key_segment($basename);
    if (in_array($proxyToken, ['COMMON', 'LIB', 'TMP', 'OUTPUT', 'SOURCE', 'TOOLS'], true)) {
        return '';
    }

    return $proxyToken;
}

function app_project_source_output_new_bridge_proxy_token_from_target_source_output_key(string $sourceOutputKey): string
{
    $normalizedSourceOutputKey = app_normalize_source_output_key($sourceOutputKey);
    if ($normalizedSourceOutputKey === '') {
        return '';
    }

    if (preg_match('/^([A-Z0-9-]+)-PROXY-(?:SERVER|CLIENT)$/', $normalizedSourceOutputKey, $matches) !== 1) {
        return '';
    }

    return app_normalize_source_output_key((string) ($matches[1] ?? ''));
}

/**
 * @return array{
 *     source_output_key:string,
 *     name:string
 * }
 */
function app_project_source_output_new_bridge_proxy_identity_from_token(
    string $projectDisplayName,
    string $proxyToken,
    string $transport,
): array {
    $normalizedProxyToken = app_normalize_source_output_key($proxyToken);
    $normalizedTransport = app_normalize_source_output_key($transport);
    if ($normalizedProxyToken === '' || !in_array($normalizedTransport, ['CLIENT', 'SERVER'], true)) {
        return ['source_output_key' => '', 'name' => ''];
    }

    $proxyLabel = app_project_source_output_new_candidate_display_label($normalizedProxyToken);
    if ($proxyLabel === '') {
        return ['source_output_key' => '', 'name' => ''];
    }

    return [
        'source_output_key' => $normalizedProxyToken . '-PROXY-' . $normalizedTransport,
        'name' => $projectDisplayName
            . ' '
            . $proxyLabel
            . ' Proxy '
            . app_project_source_output_new_candidate_display_phrase($normalizedTransport),
    ];
}

/**
 * @param array{
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
 *     source_of_truth:string
 * } $input
 * @param array{
 *     legacy_target_server_source_output_key:string,
 *     legacy_source_output_dir:string,
 *     legacy_source_template_dir:string
 * } $bridgeHints
 * @return list<array{
 *     source_output_key:string,
 *     name:string,
 *     reason:string
 * }>
 */
function app_project_source_output_new_bridge_proxy_candidates(
    string $projectDisplayName,
    array $input,
    array $bridgeHints,
): array {
    $transport = app_project_source_output_new_bridge_proxy_transport($input['class_type']);
    $candidates = [];

    $targetProxyToken = app_project_source_output_new_bridge_proxy_token_from_target_source_output_key(
        $bridgeHints['legacy_target_server_source_output_key'],
    );
    if ($targetProxyToken !== '') {
        $identity = app_project_source_output_new_bridge_proxy_identity_from_token(
            $projectDisplayName,
            $targetProxyToken,
            $transport,
        );
        if ($identity['source_output_key'] !== '' && $identity['name'] !== '') {
            $candidates[] = [
                'source_output_key' => $identity['source_output_key'],
                'name' => $identity['name'],
                'reason' => 'matched mapped target server source output',
            ];
        }
    }

    foreach (app_project_source_output_new_bridge_candidate_sources($input, $bridgeHints) as $candidateSource) {
        $proxyToken = app_project_source_output_new_bridge_proxy_token_from_source_value($candidateSource['value']);
        if ($proxyToken === '') {
            continue;
        }

        $identity = app_project_source_output_new_bridge_proxy_identity_from_token(
            $projectDisplayName,
            $proxyToken,
            $transport,
        );
        if ($identity['source_output_key'] === '' || $identity['name'] === '') {
            continue;
        }

        $candidates[] = [
            'source_output_key' => $identity['source_output_key'],
            'name' => $identity['name'],
            'reason' => 'matched ' . $candidateSource['label'],
        ];
    }

    return app_project_source_output_new_bridge_merge_identity_candidates($candidates);
}

/**
 * @param array{
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
 *     source_of_truth:string
 * } $input
 * @param array{
 *     legacy_target_server_source_output_key:string,
 *     legacy_source_output_dir:string,
 *     legacy_source_template_dir:string
 * } $bridgeHints
 * @return array{
 *     source_output_key:string,
 *     name:string
 * }
 */
function app_project_source_output_new_bridge_proxy_identity(
    string $projectDisplayName,
    array $input,
    array $bridgeHints,
): array {
    $candidates = app_project_source_output_new_bridge_proxy_candidates($projectDisplayName, $input, $bridgeHints);
    if (count($candidates) !== 1) {
        return ['source_output_key' => '', 'name' => ''];
    }

    return [
        'source_output_key' => $candidates[0]['source_output_key'],
        'name' => $candidates[0]['name'],
    ];
}

/**
 * @param array{
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
 *     source_of_truth:string
 * } $input
 * @param array{
 *     legacy_target_server_source_output_key:string,
 *     legacy_source_output_dir:string,
 *     legacy_source_template_dir:string
 * } $bridgeHints
 * @return array{
 *     source_output_key:string,
 *     name:string
 * }
 */
function app_project_source_output_new_bridge_language_resource_identity_base(
    string $projectDisplayName,
    array $input,
    array $bridgeHints,
): array {
    $candidateTexts = app_project_source_output_new_bridge_candidate_texts($input, $bridgeHints);
    $combined = strtolower(implode("\n", $candidateTexts));

    $tailTokens = [];
    $tailLabel = '';

    if (str_contains($combined, 'dev.matsuesoft.com/lib')) {
        $tailTokens = ['DEV', 'LIB'];
        $tailLabel = 'Dev Web Lib';
    } elseif (str_contains($combined, 'ja.matsuesoft.com/lib')) {
        $tailTokens = ['JA', 'WEB', 'LIB'];
        $tailLabel = 'Ja Web Lib';
    } elseif (str_contains($combined, 'www.matsuesoft.com/lib')) {
        $tailTokens = ['PUBLIC', 'WEB', 'LIB'];
        $tailLabel = 'Public Web Lib';
    } elseif (str_contains($combined, 'mtool_lib')) {
        $tailTokens = ['MTOOL', 'LIB'];
        $tailLabel = 'Mtool Lib';
    } elseif (str_contains($combined, 'matsuesoft_lib')) {
        $tailTokens = ['MATSUESOFT', 'LIB'];
        $tailLabel = 'Matsuesoft Lib';
    } elseif (str_contains($combined, 'matsuesoftcommonlib')) {
        $tailTokens = ['MATSUESOFT', 'COMMON'];
        $tailLabel = 'Matsuesoft Common';
    } elseif (str_contains($combined, 'degodbcommonlib') && str_contains($combined, 'resources')) {
        $tailTokens = ['DEGODB', 'RESOURCES'];
        $tailLabel = 'DegoDB Resources';
    } elseif ((str_contains($combined, 'common strings') || str_contains($combined, 'commonstrings')) && str_contains($combined, 'uwp')) {
        $tailTokens = ['UWP', 'COMMONSTRINGS'];
        $tailLabel = 'UWP Common Strings';
    } elseif (str_contains($combined, 'swift') && str_contains($combined, 'language resource')) {
        $tailTokens = ['IOS'];
        $tailLabel = 'iOS';
    } else {
        foreach ($candidateTexts as $candidateText) {
            $basename = basename(trim(strtolower($candidateText), '/'));
            $fallbackToken = app_project_source_output_new_candidate_key_segment($basename);
            if ($fallbackToken === '' || in_array($fallbackToken, ['COMMON', 'LIB', 'TMP', 'OUTPUT'], true)) {
                continue;
            }

            $tailTokens = [$fallbackToken];
            $tailLabel = app_project_source_output_new_candidate_display_phrase($fallbackToken);
            break;
        }
    }

    $languageToken = match (trim($input['program_language'])) {
        'php' => 'PHP',
        'java' => 'JAVA',
        'swift' => 'SWIFT',
        'cs' => 'CS',
        default => app_project_source_output_new_candidate_key_segment($input['program_language']),
    };
    $languageLabel = match (trim($input['program_language'])) {
        'php' => 'PHP',
        'java' => 'Java',
        'swift' => 'Swift',
        'cs' => 'CSharp',
        default => app_project_source_output_new_candidate_display_phrase($languageToken),
    };

    if ($languageToken === '' || $tailTokens === [] || $tailLabel === '') {
        return ['source_output_key' => '', 'name' => ''];
    }

    $sourceOutputKey = 'LANGRES-' . $languageToken . '-' . implode('-', $tailTokens);
    $name = $projectDisplayName . ' Language Resource ' . $languageLabel . ' ' . $tailLabel;

    return [
        'source_output_key' => $sourceOutputKey,
        'name' => $name,
    ];
}

/**
 * @param array{
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
 *     source_of_truth:string
 * } $input
 * @param array{
 *     legacy_target_server_source_output_key:string,
 *     legacy_source_output_dir:string,
 *     legacy_source_template_dir:string
 * } $bridgeHints
 * @param array<string,bool> $existingKeys
 * @return array{
 *     source_output_key:string,
 *     name:string
 * }
 */
function app_project_source_output_new_bridge_language_resource_identity(
    string $projectDisplayName,
    array $input,
    array $bridgeHints,
    array $existingKeys,
): array {
    $identity = app_project_source_output_new_bridge_language_resource_identity_base(
        $projectDisplayName,
        $input,
        $bridgeHints,
    );
    $sourceOutputKey = app_normalize_source_output_key($identity['source_output_key']);
    $name = trim($identity['name']);
    if ($sourceOutputKey === '' || $name === '') {
        return ['source_output_key' => '', 'name' => ''];
    }

    if (array_key_exists($sourceOutputKey, $existingKeys)) {
        $altKey = $sourceOutputKey . '-ALT';
        if (array_key_exists($altKey, $existingKeys)) {
            return ['source_output_key' => '', 'name' => ''];
        }

        $sourceOutputKey = $altKey;
        $name .= ' Alt';
    }

    return [
        'source_output_key' => $sourceOutputKey,
        'name' => $name,
    ];
}

/**
 * @param array{
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
 *     source_of_truth:string
 * } $input
 * @param array{
 *     legacy_target_server_source_output_key:string,
 *     legacy_source_output_dir:string,
 *     legacy_source_template_dir:string
 * } $bridgeHints
 * @return array{
 *     mode:string,
 *     warnings:list<string>,
 *     candidates:list<array{
 *         source_output_key:string,
 *         name:string,
 *         reason:string
 *     }>,
 *     prefill:array{
 *         source_output_key:string,
 *         name:string
 *     }
 * }
 */
function app_project_source_output_new_bridge_identity_policy(
    array $app,
    string $projectKey,
    string $projectName,
    array $input,
    array $bridgeHints,
): array {
    $existingKeys = app_project_source_output_new_existing_keys($app, $projectKey);
    $projectDisplayName = app_project_source_output_new_bridge_project_display_name($projectName, $projectKey);

    return match (trim($input['class_type'])) {
        'DBAccess' => array_key_exists('RUNTIME-DBCLASSES', $existingKeys)
            ? array_merge(
                app_project_source_output_new_bridge_identity_policy_empty(),
                [
                    'mode' => 'manual-only',
                    'warnings' => [
                        'RUNTIME-DBCLASSES は既に存在するため、新規 DBAccess key は自動確定しません。',
                    ],
                ],
            )
            : app_project_source_output_new_bridge_identity_policy_with_prefill([
                'source_output_key' => 'RUNTIME-DBCLASSES',
                'name' => $projectDisplayName . ' Runtime DBClasses',
                'reason' => 'canonical runtime DBClasses definition',
            ]),
        'DataClass' => array_key_exists('DATACLASS-PHP', $existingKeys)
            ? array_merge(
                app_project_source_output_new_bridge_identity_policy_empty(),
                [
                    'mode' => 'manual-only',
                    'warnings' => [
                        'DATACLASS-PHP は既に存在するため、新規 DataClass key は自動確定しません。',
                    ],
                ],
            )
            : app_project_source_output_new_bridge_identity_policy_with_prefill([
                'source_output_key' => 'DATACLASS-PHP',
                'name' => $projectDisplayName . ' Data Class (PHP)',
                'reason' => 'canonical dataclass PHP definition',
            ]),
        'OpenAPI' => array_key_exists('OPENAPI-JSON', $existingKeys)
            ? array_merge(
                app_project_source_output_new_bridge_identity_policy_empty(),
                [
                    'mode' => 'manual-only',
                    'warnings' => [
                        'OPENAPI-JSON は既に存在するため、新規 OpenAPI key は自動確定しません。',
                    ],
                ],
            )
            : app_project_source_output_new_bridge_identity_policy_with_prefill([
                'source_output_key' => 'OPENAPI-JSON',
                'name' => $projectDisplayName . ' OpenAPI JSON',
                'reason' => 'canonical openapi JSON definition',
            ]),
        'html' => app_project_source_output_new_bridge_identity_policy_from_candidates(
            app_project_source_output_new_bridge_html_candidates($projectDisplayName, $input, $bridgeHints),
            $existingKeys,
            'legacy dir / URL から canonical html module key を一意に決められませんでした。source_output_key / name は manual で入力してください。',
            'legacy dir / URL が複数の html module 候補を示したため、自動確定せず candidate 表示に留めます。',
            'legacy dir / URL から読めた html candidate は既存 definition と衝突するため、自動確定しません。',
        ),
        'LanguageResource' => (function () use ($projectDisplayName, $input, $bridgeHints, $existingKeys): array {
            $baseIdentity = app_project_source_output_new_bridge_language_resource_identity_base(
                $projectDisplayName,
                $input,
                $bridgeHints,
            );
            $sourceOutputKey = app_normalize_source_output_key($baseIdentity['source_output_key']);
            $name = trim($baseIdentity['name']);
            if ($sourceOutputKey === '' || $name === '') {
                $policy = app_project_source_output_new_bridge_identity_policy_empty();
                $policy['mode'] = 'manual-only';
                $policy['warnings'][] = 'legacy dir / URL から stable language resource key を決められませんでした。source_output_key / name は manual で入力してください。';
                return $policy;
            }

            if (!array_key_exists($sourceOutputKey, $existingKeys)) {
                return app_project_source_output_new_bridge_identity_policy_with_prefill([
                    'source_output_key' => $sourceOutputKey,
                    'name' => $name,
                    'reason' => 'matched legacy language resource path',
                ]);
            }

            $altKey = $sourceOutputKey . '-ALT';
            if (!array_key_exists($altKey, $existingKeys)) {
                $policy = app_project_source_output_new_bridge_identity_policy_empty();
                $policy['mode'] = 'warning-candidate';
                $policy['warnings'][] = 'legacy dir が既存 language resource と重複するため、duplicate dir policy に従って ALT candidate を提案します。';
                $policy['candidates'][] = [
                    'source_output_key' => $altKey,
                    'name' => $name . ' Alt',
                    'reason' => 'duplicate language resource dir (ALT fallback)',
                ];
                return $policy;
            }

            $policy = app_project_source_output_new_bridge_identity_policy_empty();
            $policy['mode'] = 'manual-only';
            $policy['warnings'][] = 'legacy dir が既存 language resource と重複し、ALT candidate も既に使用中です。source_output_key / name は manual で確定してください。';
            return $policy;
        })(),
        'ProxyServer', 'DBaaSProxyServer', 'ProxyClient', 'DBaaSProxyClient'
            => app_project_source_output_new_bridge_identity_policy_from_candidates(
                app_project_source_output_new_bridge_proxy_candidates($projectDisplayName, $input, $bridgeHints),
                $existingKeys,
                'proxy_base_url / legacy dir / target server hint から stable proxy key を決められませんでした。source_output_key / name は manual で入力してください。',
                'proxy_base_url / legacy dir / target server hint が複数の proxy basename を示したため、自動確定せず candidate 表示に留めます。',
                'proxy candidate は既存 definition と衝突するため、自動確定しません。',
            ),
        default => array_merge(
            app_project_source_output_new_bridge_identity_policy_empty(),
            [
                'mode' => 'manual-only',
                'warnings' => [
                    'この ClassType の legacy add-flow candidate policy は未実装です。source_output_key / name は manual で入力してください。',
                ],
            ],
        ),
    };
}

/**
 * @param array{
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
 *     source_of_truth:string
 * } $input
 * @param array{
 *     legacy_target_server_source_output_key:string,
 *     legacy_source_output_dir:string,
 *     legacy_source_template_dir:string
 * } $bridgeHints
 * @return array{
 *     source_output_key:string,
 *     name:string
 * }
 */
function app_project_source_output_new_bridge_tentative_identity(
    array $app,
    string $projectKey,
    string $projectName,
    array $input,
    array $bridgeHints,
): array {
    $policy = app_project_source_output_new_bridge_identity_policy(
        $app,
        $projectKey,
        $projectName,
        $input,
        $bridgeHints,
    );

    return $policy['prefill'];
}

/**
 * @param array{
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
 *     source_of_truth:string
 * } $input
 * @param array{
 *     legacy_target_server_source_output_key:string,
 *     legacy_source_output_dir:string,
 *     legacy_source_template_dir:string
 * } $bridgeHints
 * @return array{
 *     input:array{
 *         source_output_key:string,
 *         name:string,
 *         program_language:string,
 *         class_type:string,
 *         release_target_type:string,
 *         source_template_dir:string,
 *         source_output_dir:string,
 *         source_temp_output_dir:string,
 *         proxy_base_url:string,
 *         autoload_filename_suffix:string,
 *         source_text_char_code:string,
 *         runtime_source_relative_path:string,
 *         artifact_strategy:string,
 *         target_binding_type:string,
 *         output_archive_format:string,
 *         source_output_list_order:string,
 *         notes:string,
 *         source_of_truth:string
 *     },
 *     policy:array{
 *         mode:string,
 *         warnings:list<string>,
 *         candidates:list<array{
 *             source_output_key:string,
 *             name:string,
 *             reason:string
 *         }>,
 *         prefill:array{
 *             source_output_key:string,
 *             name:string
 *         }
 *     }
 * }
 */
function app_project_source_output_new_prefill_from_legacy_bridge(
    array $app,
    string $projectKey,
    string $projectName,
    array $input,
    array $bridgeHints,
): array {
    $classType = trim($input['class_type']);
    $targetServerContext = app_project_source_output_new_bridge_target_server_context(
        $app,
        $projectKey,
        $bridgeHints,
    );

    if (
        $input['proxy_base_url'] === ''
        && $targetServerContext['proxy_base_url'] !== ''
        && in_array($classType, ['ProxyClient', 'DBaaSProxyClient'], true)
    ) {
        $input['proxy_base_url'] = $targetServerContext['proxy_base_url'];
    }

    switch ($classType) {
        case 'DBAccess':
            $input['artifact_strategy'] = 'generated-bootstrap-dbclasses';
            $input['target_binding_type'] = 'runtime';
            $input['output_archive_format'] = 'tar.gz';
            $input['runtime_source_relative_path'] = app_project_output_runtime_source_relative_path();
            break;

        case 'DataClass':
            $input['artifact_strategy'] = 'canonical-dataclass-php';
            $input['target_binding_type'] = 'runtime';
            $input['output_archive_format'] = 'tar.gz';
            $input['runtime_source_relative_path'] = app_project_output_data_class_default_runtime_source_relative_path(
                $projectKey,
                'DATACLASS-PHP',
            );
            break;

        case 'OpenAPI':
            $input['artifact_strategy'] = 'openapi-json';
            $input['target_binding_type'] = 'single-function-proxy';
            $input['output_archive_format'] = 'tar.gz';
            $input['runtime_source_relative_path'] = app_project_output_openapi_default_runtime_source_relative_path(
                $projectKey,
                'OPENAPI-JSON',
            );
            break;

        case 'html':
            $input['artifact_strategy'] = 'html-module-catalog';
            $input['target_binding_type'] = 'metadata-only';
            $input['output_archive_format'] = 'tar.gz';
            $input['runtime_source_relative_path'] = '';
            break;

        case 'LanguageResource':
            $input['artifact_strategy'] = 'legacy-directory-mirror';
            $input['target_binding_type'] = 'metadata-only';
            $input['output_archive_format'] = 'tar.gz';
            $input['runtime_source_relative_path'] = '';
            break;

        case 'ProxyServer':
        case 'DBaaSProxyServer':
        case 'ProxyClient':
        case 'DBaaSProxyClient':
            $isClient = in_array($classType, ['ProxyClient', 'DBaaSProxyClient'], true);
            if (in_array($targetServerContext['target_binding_type'], ['custom-proxy', 'single-function-proxy'], true)) {
                $bindingType = $targetServerContext['target_binding_type'];
            } elseif (in_array($targetServerContext['artifact_strategy'], ['custom-proxy-server', 'custom-proxy-client'], true)) {
                $bindingType = 'custom-proxy';
            } elseif (in_array($targetServerContext['artifact_strategy'], ['single-proxy-server', 'single-proxy-client'], true)) {
                $bindingType = 'single-function-proxy';
            } elseif ($input['proxy_base_url'] !== '') {
                $bindingType = 'custom-proxy';
            } else {
                $bindingType = 'single-function-proxy';
            }

            $input['target_binding_type'] = $bindingType;
            $input['artifact_strategy'] = $bindingType === 'custom-proxy'
                ? ($isClient ? 'custom-proxy-client' : 'custom-proxy-server')
                : ($isClient ? 'single-proxy-client' : 'single-proxy-server');
            $input['output_archive_format'] = 'tar.gz';
            $input['runtime_source_relative_path'] = '';
            break;
    }

    $policy = app_project_source_output_new_bridge_identity_policy(
        $app,
        $projectKey,
        $projectName,
        $input,
        $bridgeHints,
    );
    if ($input['source_output_key'] === '' && $policy['prefill']['source_output_key'] !== '') {
        $input['source_output_key'] = $policy['prefill']['source_output_key'];
    }
    if ($input['name'] === '' && $policy['prefill']['name'] !== '') {
        $input['name'] = $policy['prefill']['name'];
    }

    return [
        'input' => app_project_source_output_complete_new_defaults($projectKey, $input, $bridgeHints),
        'policy' => $policy,
    ];
}

/**
 * @param array{
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
 *     source_of_truth:string
 * } $input
 * @param array{
 *     legacy_target_server_source_output_key:string,
 *     legacy_source_output_dir:string,
 *     legacy_source_template_dir:string
 * } $bridgeHints
 * @return array{
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
 *     source_of_truth:string
 * }
 */
function app_project_source_output_complete_new_defaults(
    string $projectKey,
    array $input,
    array $bridgeHints,
): array
{
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $sourceOutputKey = app_normalize_source_output_key($input['source_output_key']);
    $legacySourceOutputDir = $bridgeHints['legacy_source_output_dir'];
    $legacySourceTemplateDir = $bridgeHints['legacy_source_template_dir'];

    if (
        $sourceOutputKey !== ''
        && (
            $input['source_output_dir'] === ''
            || $input['source_output_dir'] === $legacySourceOutputDir
        )
    ) {
        $input['source_output_dir'] = app_project_output_default_relative_path(
            $projectKey,
            $sourceOutputKey,
        );
    }

    if ($sourceOutputKey !== '' && $input['source_temp_output_dir'] === '') {
        $input['source_temp_output_dir'] = app_project_output_default_temp_relative_path(
            $projectKey,
            $sourceOutputKey,
        );
    }

    switch ($input['artifact_strategy']) {
        case 'generated-bootstrap-dbclasses':
            if ($input['runtime_source_relative_path'] === '') {
                $input['runtime_source_relative_path'] = app_project_output_runtime_source_relative_path();
            }
            break;

        case 'canonical-dbaccess-php':
            if ($sourceOutputKey !== '' && $input['runtime_source_relative_path'] === '') {
                $input['runtime_source_relative_path'] = app_project_output_db_access_default_runtime_source_relative_path(
                    $projectKey,
                    $sourceOutputKey,
                );
            }
            break;

        case 'canonical-dataclass-php':
            if ($sourceOutputKey !== '' && $input['runtime_source_relative_path'] === '') {
                $input['runtime_source_relative_path'] = app_project_output_data_class_default_runtime_source_relative_path(
                    $projectKey,
                    $sourceOutputKey,
                );
            }
            break;

        case 'openapi-json':
            if ($sourceOutputKey !== '' && $input['runtime_source_relative_path'] === '') {
                $input['runtime_source_relative_path'] = app_project_output_openapi_default_runtime_source_relative_path(
                    $projectKey,
                    $sourceOutputKey,
                );
            }
            break;

        case 'html-module-catalog':
            if (
                $sourceOutputKey !== ''
                && (
                    $input['source_template_dir'] === ''
                    || $input['source_template_dir'] === $legacySourceTemplateDir
                )
            ) {
                $input['source_template_dir'] = 'catalog://html-module/'
                    . $normalizedProjectKey
                    . '/'
                    . $sourceOutputKey;
            }
            if ($sourceOutputKey !== '' && $input['runtime_source_relative_path'] === '') {
                $input['runtime_source_relative_path'] = app_project_output_html_module_default_runtime_source_relative_path(
                    $projectKey,
                    $sourceOutputKey,
                );
            }
            break;

        case 'legacy-directory-mirror':
            if ($sourceOutputKey !== '' && $input['runtime_source_relative_path'] === '') {
                $input['runtime_source_relative_path'] = app_project_output_legacy_source_default_runtime_source_relative_path(
                    $projectKey,
                    $sourceOutputKey,
                );
            }
            break;

        case 'single-proxy-server':
        case 'single-proxy-client':
        case 'custom-proxy-server':
        case 'custom-proxy-client':
            if ($sourceOutputKey !== '' && $input['runtime_source_relative_path'] === '') {
                $input['runtime_source_relative_path'] = app_project_output_proxy_default_runtime_source_relative_path(
                    $projectKey,
                    $sourceOutputKey,
                );
            }
            break;
    }

    if ($input['target_binding_type'] === '') {
        $input['target_binding_type'] = app_source_output_target_binding_scope($input);
    }

    return $input;
}

/**
 * @param array{
 *     legacy_target_server_source_output_key:string,
 *     legacy_source_output_dir:string,
 *     legacy_source_template_dir:string
 * } $bridgeHints
 */
function app_project_source_output_new_render_bridge_hidden_inputs(array $bridgeHints): void
{
    foreach ($bridgeHints as $field => $value) {
        if ($value === '') {
            continue;
        }
        ?>
        <input type="hidden" name="<?php echo app_h($field); ?>" value="<?php echo app_h($value); ?>">
        <?php
    }
}

/**
 * @param array{
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
 *     source_of_truth:string
 * } $input
 * @param list<string> $excludeFields
 */
function app_project_source_output_new_render_input_hidden_inputs(array $input, array $excludeFields = []): void
{
    foreach ($input as $field => $value) {
        if (in_array($field, $excludeFields, true)) {
            continue;
        }
        ?>
        <input type="hidden" name="<?php echo app_h((string) $field); ?>" value="<?php echo app_h((string) $value); ?>">
        <?php
    }
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     },
 *     generated:array{
 *         root:string,
 *         dbclasses_root:string,
 *         dbclasses_loader:string,
 *         dbclasses_mode:string
 *     }
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_project_source_output_new_page(array $app, array $request): void
{
    $bootstrap = app_project_source_output_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($bootstrap === null) {
        return;
    }

    $projectKey = $bootstrap['project_key'];
    $project = $bootstrap['project'];
    $generatedRuntime = $bootstrap['generated_runtime'];

    $defaults = app_project_source_output_new_defaults($app, $projectKey);
    $input = $defaults;
    $errors = app_project_source_output_bridge_errors_from_request();
    $bridgePrefilled = false;
    $bridgeHints = app_project_source_output_new_bridge_hints_from_request();
    $legacyMetadata = app_project_source_output_legacy_metadata_from_request();
    $legacyMetadataRows = app_project_source_output_legacy_metadata_rows($legacyMetadata);
    $bridgeIdentityPolicy = app_project_source_output_new_bridge_identity_policy_empty();
    $bridgeCandidateApplied = false;

    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $action = trim(app_post_param('action', 'save-source-output'));
            $input = app_project_source_output_form_from_post($input);

            if ($action === 'bridge-prefill-source-output') {
                $bridgePrefillResult = app_project_source_output_new_prefill_from_legacy_bridge(
                    $app,
                    $projectKey,
                    (string) ($project['name'] ?? ''),
                    $input,
                    $bridgeHints,
                );
                $input = $bridgePrefillResult['input'];
                $bridgeIdentityPolicy = $bridgePrefillResult['policy'];
                $bridgePrefilled = true;
            } elseif ($action === 'apply-bridge-candidate') {
                $input = app_project_source_output_complete_new_defaults(
                    $projectKey,
                    $input,
                    $bridgeHints,
                );
                if (app_project_source_output_new_has_bridge_hints($bridgeHints)) {
                    $bridgeIdentityPolicy = app_project_source_output_new_bridge_identity_policy(
                        $app,
                        $projectKey,
                        (string) ($project['name'] ?? ''),
                        $input,
                        $bridgeHints,
                    );
                }
                $bridgePrefilled = true;
                $bridgeCandidateApplied = true;
            } elseif ($action === '' || $action === 'save-source-output') {
                $input = app_project_source_output_complete_new_defaults(
                    $projectKey,
                    $input,
                    $bridgeHints,
                );
                if (app_project_source_output_new_has_bridge_hints($bridgeHints)) {
                    $bridgeIdentityPolicy = app_project_source_output_new_bridge_identity_policy(
                        $app,
                        $projectKey,
                        (string) ($project['name'] ?? ''),
                        $input,
                        $bridgeHints,
                    );
                }
                $validation = app_validate_source_output_form($input);
                $input = $validation['input'];
                $errors = array_merge($errors, $validation['errors']);

                if ($errors === []) {
                    $persistInput = $input;
                    $persistInput['notes'] = app_project_source_output_notes_with_legacy_metadata(
                        $input['notes'],
                        $legacyMetadata,
                    );
                    $createResult = app_create_project_source_output($app, array_merge(
                        ['project_key' => $projectKey],
                        $persistInput,
                    ));

                    if ($createResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_source_output_edit_path($projectKey, $input['source_output_key']) . '?created=1',
                        );
                        return;
                    }

                    $errors[] = $createResult['error'];
                }
            } else {
                $errors[] = '未対応の操作です。';
            }
        }
    }

    if ($bridgeIdentityPolicy['mode'] === 'none' && app_project_source_output_new_has_bridge_hints($bridgeHints)) {
        $bridgeIdentityPolicy = app_project_source_output_new_bridge_identity_policy(
            $app,
            $projectKey,
            (string) ($project['name'] ?? ''),
            $input,
            $bridgeHints,
        );
    }

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();
    $bridgePolicyCaption = match ($bridgeIdentityPolicy['mode']) {
        'safe-prefill' => 'safe prefill',
        'warning-candidate' => 'warning candidate',
        'manual-only' => 'manual only',
        default => 'none',
    };

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Source Output Create</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 88rem;
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
            background: #fff7ed;
            border-color: #fdba74;
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
        input, select, textarea {
            width: 100%;
            box-sizing: border-box;
            margin-top: 0.35rem;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            font: inherit;
        }
        textarea {
            min-height: 8rem;
            resize: vertical;
        }
        .form-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }
        .button-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.25rem;
        }
        .button {
            display: inline-block;
            border: 0;
            border-radius: 8px;
            background: #0f172a;
            color: #ffffff;
            padding: 0.75rem 1rem;
            font: inherit;
            cursor: pointer;
            text-decoration: none;
        }
        .button-secondary {
            background: #475569;
        }
        .muted {
            color: #475569;
        }
        .candidate-list {
            margin: 1rem 0 0;
            padding-left: 1.25rem;
        }
        .candidate-list li + li {
            margin-top: 0.5rem;
        }
        .candidate-action-form {
            margin-top: 0.5rem;
        }
        .button-small {
            padding: 0.5rem 0.8rem;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / <a href="<?php echo app_h(app_project_source_outputs_path($projectKey)); ?>">source-outputs</a> / new</p>

    <h1><?php echo app_h($project['name']); ?> Source Output Create</h1>
    <p><code>ProjectSourceOutput</code> definition を current canonical schema へ新規作成する画面です。legacy add flow の handoff でもこの form を使い、current 側で不足する stable key / strategy を確定します。</p>

    <?php if ($bridgePrefilled): ?>
        <section class="success-card">
            <h2>Legacy Handoff</h2>
            <?php if ($bridgeCandidateApplied): ?>
                <p>candidate を form に反映しました。strategy / binding とあわせて内容を確認し、そのまま保存できます。</p>
            <?php elseif ($bridgeIdentityPolicy['mode'] === 'warning-candidate'): ?>
                <p>legacy add request の current handoff を受けました。class type / proxy hints から strategy と binding は初期推定済みですが、<code>source_output_key</code> / <code>name</code> は warning 付き candidate 表示に留めています。候補を確認し、保存前に手動で確定してください。</p>
            <?php elseif ($bridgeIdentityPolicy['mode'] === 'manual-only'): ?>
                <p>legacy add request の current handoff を受けました。class type / proxy hints から strategy と binding は初期推定済みですが、<code>source_output_key</code> / <code>name</code> は safe に確定できなかったため blank/manual に戻しています。current policy に合わせて入力してください。</p>
            <?php else: ?>
                <p>legacy add request の current handoff を受けました。class type / proxy hints から strategy と binding を初期推定済みで、legacy dir / URL から一意に読めた場合だけ <code>source_output_key</code> と <code>name</code> も prefill しています。prefill 値を確認して保存してください。</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <section class="error-card">
            <h2>エラー</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if ($bridgeIdentityPolicy['mode'] !== 'none'): ?>
        <section class="warning-card">
            <h2>Legacy Key/Name Policy</h2>
            <p class="muted">detected mode: <code><?php echo app_h($bridgePolicyCaption); ?></code></p>
            <?php if ($bridgeIdentityPolicy['warnings'] !== []): ?>
                <ul>
                    <?php foreach ($bridgeIdentityPolicy['warnings'] as $warning): ?>
                        <li><?php echo app_h($warning); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if ($bridgeIdentityPolicy['candidates'] !== []): ?>
                <ul class="candidate-list">
                    <?php foreach ($bridgeIdentityPolicy['candidates'] as $candidate): ?>
                        <li>
                            <code><?php echo app_h($candidate['source_output_key']); ?></code>
                            /
                            <?php echo app_h($candidate['name']); ?>
                            <?php if ($candidate['reason'] !== ''): ?>
                                <span class="muted">(<?php echo app_h($candidate['reason']); ?>)</span>
                            <?php endif; ?>
                            <form class="candidate-action-form" method="post">
                                <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
                                <input type="hidden" name="action" value="apply-bridge-candidate">
                                <?php app_project_source_output_new_render_input_hidden_inputs($input, ['source_output_key', 'name']); ?>
                                <?php app_project_source_output_new_render_bridge_hidden_inputs($bridgeHints); ?>
                                <?php app_project_source_output_render_legacy_metadata_hidden_inputs($legacyMetadata); ?>
                                <input type="hidden" name="source_output_key" value="<?php echo app_h($candidate['source_output_key']); ?>">
                                <input type="hidden" name="name" value="<?php echo app_h($candidate['name']); ?>">
                                <button class="button button-secondary button-small" type="submit">Use This Candidate</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Create Policy</h2>
            <ul>
                <li>source of truth: <code>manual</code></li>
                <li>default list order: <code><?php echo app_h($defaults['source_output_list_order']); ?></code></li>
                <li>default output base: <code><?php echo app_h(app_project_output_default_relative_path($projectKey)); ?></code></li>
                <li>default temp base: <code><?php echo app_h(app_project_output_default_temp_relative_path($projectKey)); ?></code></li>
                <li>custom layer base: <code><?php echo app_h(app_project_output_custom_layer_relative_path($projectKey, 'EXAMPLE')); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Current Runtime</h2>
            <ul>
                <li>mode: <code><?php echo app_h($generatedRuntime['dbclasses_mode']); ?></code></li>
                <li>source root: <code><?php echo app_h($generatedRuntime['dbclasses_root']); ?></code></li>
                <li>loader exists: <code><?php echo app_h($generatedRuntime['dbclasses_loader_exists'] ? 'yes' : 'no'); ?></code></li>
                <li>file count: <code><?php echo app_h((string) $generatedRuntime['total_file_count']); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>作成時の注意</h2>
            <p class="muted">html は <code>artifact_strategy=html-module-catalog</code> を使い、<code>source_template_dir</code> に <code>catalog://html-module/{project_key}/{source_output_key}</code> を入れます。</p>
            <p class="muted">legacy handoff の proxy は <code>ClassType</code>、<code>ProxyBaseURL</code>、mapped target server source output を見て strategy / binding を初期推定します。保存前に current policy と一致しているか確認してください。</p>
            <p class="muted">OpenAPI spec は internal filename を固定したまま扱い、public raw route や public alias key route はまだ持ちません。共有が必要な時は authenticated viewer または admin artifact download を使い、必要なら <code>spec_visibility</code> を <code>disabled</code> にして viewer からも隠せます。</p>
            <p class="muted">current raw output は全 project で <code>work/source-outputs/{project_key}/{source_output_key}</code> を既定にします。repo に残す sample asset が必要な場合だけ、対応する pack の <code>sample/&lt;category&gt;/&lt;pack&gt;/reference/&lt;source_output_key&gt;/</code> を使います。</p>
            <p class="muted">legacy dir / URL から key/name を一意に読める場合だけ prefill します。duplicate / ambiguous case は warning candidate か manual only に落とし、candidate card で明示します。</p>
            <p class="muted">legacy-only field は current schema へ未移植です。値が渡された場合は <code>notes</code> に structured block として退避し、detail/edit から確認できるようにします。</p>
        </section>

        <?php if ($legacyMetadataRows !== []): ?>
            <section class="warning-card">
                <h2>Legacy Metadata</h2>
                <p class="muted">この handoff には current schema 未移植の legacy field が含まれています。保存時は <code>notes</code> へ構造化して退避します。</p>
                <ul>
                    <?php foreach ($legacyMetadataRows as $row): ?>
                        <li><code><?php echo app_h($row['field']); ?></code>: <?php echo app_h($row['value']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>
    </div>

    <form method="post">
        <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
        <input type="hidden" name="action" value="save-source-output">
        <input type="hidden" name="source_of_truth" value="manual">
        <?php app_project_source_output_new_render_bridge_hidden_inputs($bridgeHints); ?>
        <?php app_project_source_output_render_legacy_metadata_hidden_inputs($legacyMetadata); ?>

        <div class="form-grid">
            <label>
                source output key
                <input name="source_output_key" value="<?php echo app_h($input['source_output_key']); ?>" placeholder="HTML-DB">
            </label>

            <label>
                name
                <input name="name" value="<?php echo app_h($input['name']); ?>" placeholder="Mtool HTML DB">
            </label>

            <label>
                ProgramLanguage
                <select name="program_language">
                    <?php foreach (app_allowed_source_output_program_languages() as $programLanguage): ?>
                        <option value="<?php echo app_h($programLanguage); ?>"<?php echo $input['program_language'] === $programLanguage ? ' selected' : ''; ?>><?php echo app_h(app_source_output_program_language_caption($programLanguage)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                ClassType
                <select name="class_type">
                    <?php foreach (app_allowed_source_output_class_types() as $classType): ?>
                        <option value="<?php echo app_h($classType); ?>"<?php echo $input['class_type'] === $classType ? ' selected' : ''; ?>><?php echo app_h(app_source_output_class_type_caption($classType)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                ReleaseTargetType
                <select name="release_target_type">
                    <?php foreach (app_allowed_source_output_release_target_types() as $releaseTargetType): ?>
                        <option value="<?php echo app_h($releaseTargetType); ?>"<?php echo $input['release_target_type'] === $releaseTargetType ? ' selected' : ''; ?>><?php echo app_h(app_source_output_release_target_type_caption($releaseTargetType)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                source_template_dir
                <input name="source_template_dir" value="<?php echo app_h($input['source_template_dir']); ?>" placeholder="catalog://html-module/MTOOL/HTML-DB">
            </label>

            <label>
                source_output_dir
                <input name="source_output_dir" value="<?php echo app_h($input['source_output_dir']); ?>" placeholder="<?php echo app_h(app_project_output_default_relative_path('MTOOL', 'HTML-DB')); ?>">
            </label>

            <label>
                source_temp_output_dir
                <input name="source_temp_output_dir" value="<?php echo app_h($input['source_temp_output_dir']); ?>" placeholder="<?php echo app_h(app_project_output_default_temp_relative_path('MTOOL', 'HTML-DB')); ?>">
            </label>

            <label>
                proxy_base_url
                <input name="proxy_base_url" value="<?php echo app_h($input['proxy_base_url']); ?>" placeholder="https://example.invalid/api">
            </label>

            <label>
                autoload_filename_suffix
                <input name="autoload_filename_suffix" value="<?php echo app_h($input['autoload_filename_suffix']); ?>" placeholder="mtool">
            </label>

            <label>
                source_text_char_code
                <input name="source_text_char_code" value="<?php echo app_h($input['source_text_char_code']); ?>" placeholder="UTF-8">
            </label>

            <label>
                runtime_source_relative_path
                <input name="runtime_source_relative_path" value="<?php echo app_h($input['runtime_source_relative_path']); ?>" placeholder="<?php echo app_h(app_project_output_runtime_source_relative_path()); ?>">
            </label>

            <label>
                artifact_strategy
                <select name="artifact_strategy">
                    <?php foreach (app_allowed_source_output_artifact_strategies() as $artifactStrategy): ?>
                        <option value="<?php echo app_h($artifactStrategy); ?>"<?php echo $input['artifact_strategy'] === $artifactStrategy ? ' selected' : ''; ?>><?php echo app_h(app_source_output_artifact_strategy_caption($artifactStrategy)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                target_binding_type
                <select name="target_binding_type">
                    <?php foreach (app_allowed_source_output_target_binding_types() as $targetBindingType): ?>
                        <option value="<?php echo app_h($targetBindingType); ?>"<?php echo $input['target_binding_type'] === $targetBindingType ? ' selected' : ''; ?>><?php echo app_h(app_source_output_target_binding_type_caption($targetBindingType)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                spec_visibility
                <select name="spec_visibility">
                    <?php foreach (app_allowed_source_output_spec_visibilities() as $specVisibility): ?>
                        <option value="<?php echo app_h($specVisibility); ?>"<?php echo ($input['spec_visibility'] ?? '') === $specVisibility ? ' selected' : ''; ?>><?php echo app_h(app_source_output_spec_visibility_caption($specVisibility)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                output_archive_format
                <select name="output_archive_format">
                    <?php foreach (app_allowed_source_output_archive_formats() as $archiveFormat): ?>
                        <option value="<?php echo app_h($archiveFormat); ?>"<?php echo $input['output_archive_format'] === $archiveFormat ? ' selected' : ''; ?>><?php echo app_h($archiveFormat); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                source_output_list_order
                <input name="source_output_list_order" value="<?php echo app_h($input['source_output_list_order']); ?>" inputmode="numeric" pattern="[0-9]*">
            </label>
        </div>

        <label>
            notes
            <textarea name="notes"><?php echo app_h($input['notes']); ?></textarea>
        </label>

        <div class="button-row">
            <button class="button" type="submit">Create Definition</button>
            <a class="button button-secondary" href="<?php echo app_h(app_project_source_outputs_path($projectKey)); ?>">Back To Source Outputs</a>
        </div>
    </form>
</main>
</body>
</html>
    <?php
}
