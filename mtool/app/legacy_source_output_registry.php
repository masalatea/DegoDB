<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';

/**
 * @return array<string,array<string,string>>
 */
function app_legacy_project_source_output_key_fallback_registry(): array
{
    return [
        'MTOOL' => [
            '1' => 'RUNTIME-DBCLASSES',
            '13' => 'HTML-DB',
            '14' => 'HTML-CHAT',
            '15' => 'HTML-MINUTES',
            '16' => 'HTML-REQ',
            '17' => 'HTML-SPEC',
            '18' => 'HTML-TEST',
            '19' => 'HTML-SETTINGS-UPLOADER',
            '20' => 'HTML-SETTINGS-APACHE',
            '21' => 'HTML-SYSTEMSETTINGS-DROPBOX',
            '27' => 'HTML-SYSTEMSETTINGS-SPECIALHOLIDAY',
            '28' => 'PAYPAL-PROXY-SERVER',
            '31' => 'HTML-SETTINGS-SERVER',
            '32' => 'HTML-SETTINGS-DBUSER',
            '33' => 'HTML-SETTINGS-DBCONNECTION',
            '34' => 'HTML-SYSTEMSETTINGS-SECURITY',
            '35' => 'HTML-SYSTEMSETTINGS-INTERNALUSER',
            '36' => 'HTML-SETTINGS-TOP',
            '38' => 'HTML-SYSTEMSETTINGS-HTMLTEMPLATE',
            '83' => 'HTML-SETTINGS-DROPBOX',
            '84' => 'HTML-SYSTEMSETTINGS-APACHE',
            '117' => 'UPLOADER-PROXY-SERVER',
            '150' => 'HTML-SYSTEMSETTINGS-PROJECTGROUP',
            '265' => 'LANGRES-PHP-DEV-LIB',
            '269' => 'LANGRES-PHP-MTOOL-LIB',
            '274' => 'LANGRES-PHP-MATSUESOFT-LIB',
            '279' => 'LANGRES-PHP-JA-WEB-LIB',
            '280' => 'LANGRES-PHP-PUBLIC-WEB-LIB',
            '300' => 'DBIMPORT-PROXY-SERVER',
            '301' => 'DBIMPORT-PROXY-CLIENT',
            '329' => 'LANGRES-JAVA-MATSUESOFT-COMMON',
            '353' => 'LANGRES-SWIFT-IOS',
            '355' => 'LANGRES-CS-UWP-COMMONSTRINGS',
            '356' => 'HTML-SETTINGS-DBBACKUP',
            '361' => 'LANGRES-CS-DEGODB-RESOURCES',
            '369' => 'LANGRES-PHP-JA-WEB-LIB-ALT',
        ],
    ];
}

/**
 * @return array<string,string>
 */
function app_legacy_project_source_output_key_fallback_map(string $projectKey): array
{
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $registry = app_legacy_project_source_output_key_fallback_registry();

    return $registry[$normalizedProjectKey] ?? [];
}

function app_legacy_project_language_resource_source_output_name(
    string $projectKey,
    string $sourceOutputKey,
): string {
    $words = [ucfirst(strtolower(app_normalize_project_key($projectKey))), 'Language', 'Resource'];
    $tokens = explode('-', trim($sourceOutputKey));
    $tokenLabels = [
        'PHP' => 'PHP',
        'JAVA' => 'Java',
        'SWIFT' => 'Swift',
        'CS' => 'CS',
        'UWP' => 'UWP',
        'IOS' => 'iOS',
        'JA' => 'JA',
        'DEGODB' => 'DegoDB',
        'COMMONSTRINGS' => 'Common Strings',
    ];

    foreach (array_slice($tokens, 1) as $token) {
        $normalizedToken = strtoupper(trim($token));
        if ($normalizedToken === '') {
            continue;
        }

        if (array_key_exists($normalizedToken, $tokenLabels)) {
            $words[] = $tokenLabels[$normalizedToken];
            continue;
        }

        $words[] = ucfirst(strtolower($normalizedToken));
    }

    return implode(' ', $words);
}

function app_legacy_project_language_resource_source_output_program_language(string $sourceOutputKey): string
{
    $tokens = explode('-', trim($sourceOutputKey));
    $languageToken = strtoupper(trim((string) ($tokens[1] ?? '')));

    return match ($languageToken) {
        'PHP' => 'php',
        'JAVA' => 'java',
        'SWIFT' => 'swift',
        'CS' => 'cs',
        default => strtolower($languageToken),
    };
}

function app_legacy_project_language_resource_source_template_dir(
    string $projectKey,
    string $sourceOutputKey,
): string {
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey !== 'MTOOL') {
        return '';
    }

    $projectSlug = strtolower($normalizedProjectKey);
    $relativeCandidates = [
        'mtool/reference/legacy-source-snapshots/' . $projectSlug . '/language-resource/' . $sourceOutputKey,
        'mtool/reference/legacy-source-placeholders/' . $projectSlug . '/language-resource/' . $sourceOutputKey,
    ];
    $repoRoot = dirname(__DIR__, 2);

    foreach ($relativeCandidates as $relativePath) {
        if (is_dir($repoRoot . '/' . $relativePath)) {
            return $relativePath;
        }
    }

    return '';
}

/**
 * @return array<string,array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     source_template_dir:string,
 *     source_output_list_order:int,
 *     notes:string
 * }>
 */
function app_legacy_project_language_resource_source_output_binding_map(string $projectKey): array
{
    $bindings = [];
    $order = 10;

    foreach (app_legacy_project_source_output_key_fallback_map($projectKey) as $legacyPid => $sourceOutputKey) {
        if (!str_starts_with($sourceOutputKey, 'LANGRES-')) {
            continue;
        }

        $bindings[(string) $legacyPid] = [
            'legacy_project_source_output_pid' => (int) $legacyPid,
            'source_output_key' => $sourceOutputKey,
            'name' => app_legacy_project_language_resource_source_output_name($projectKey, $sourceOutputKey),
            'program_language' => app_legacy_project_language_resource_source_output_program_language($sourceOutputKey),
            'source_template_dir' => app_legacy_project_language_resource_source_template_dir(
                $projectKey,
                $sourceOutputKey,
            ),
            'source_output_list_order' => $order,
            'notes' => 'Stable fallback mapping for legacy ProjectSourceOutput.PID=' . (int) $legacyPid . '.',
        ];
        $order += 10;
    }

    return $bindings;
}
