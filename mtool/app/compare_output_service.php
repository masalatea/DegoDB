<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/compare_output_asset_service.php';
require_once __DIR__ . '/runtime_storage_paths.php';

function app_compare_output_workspace_root(): string
{
    return app_runtime_storage_repo_root();
}

function app_compare_output_normalize_requested_by(string $requestedBy): string
{
    $normalized = preg_replace('/\s+/', ' ', trim($requestedBy));
    if (!is_string($normalized) || $normalized === '') {
        return 'system';
    }

    if (strlen($normalized) > 128) {
        return substr($normalized, 0, 128);
    }

    return $normalized;
}

function app_compare_output_tmp_output_suffix_pattern(): string
{
    return '/\s*-\s*tmp\s*output\s*$/i';
}

function app_compare_output_normalize_path_fragment(string $value): string
{
    return str_replace('\\', '/', trim($value));
}

function app_compare_output_path_is_absolute(string $value): bool
{
    return $value !== ''
        && ($value[0] === '/' || preg_match('/^[A-Za-z]:[\\\\\\/]/', $value) === 1);
}

function app_compare_output_relative_path_is_safe(string $value): bool
{
    if ($value === '' || app_compare_output_path_is_absolute($value) || str_contains($value, "\0")) {
        return false;
    }

    foreach (explode('/', str_replace('\\', '/', $value)) as $segment) {
        if ($segment === '..') {
            return false;
        }
    }

    return true;
}

/**
 * @return array{
 *     ok:bool,
 *     path:string,
 *     error:string
 * }
 */
function app_compare_output_resolve_base_path(string $basePath): array
{
    $normalized = app_compare_output_normalize_path_fragment($basePath);
    if ($normalized === '') {
        return [
            'ok' => true,
            'path' => app_compare_output_workspace_root(),
            'error' => '',
        ];
    }

    if (str_contains($normalized, "\0")) {
        return [
            'ok' => false,
            'path' => '',
            'error' => 'storage base path に不正な文字が含まれています。',
        ];
    }

    if (app_compare_output_path_is_absolute($normalized)) {
        return [
            'ok' => true,
            'path' => rtrim($normalized, '/'),
            'error' => '',
        ];
    }

    if (!app_compare_output_relative_path_is_safe($normalized)) {
        return [
            'ok' => false,
            'path' => '',
            'error' => 'storage base path は相対 path として安全な形式で指定してください。',
        ];
    }

    $canonicalRelativePath = app_runtime_storage_canonical_repo_relative_path($normalized);

    return [
        'ok' => true,
        'path' => rtrim(app_compare_output_workspace_root(), '/') . '/' . ltrim($canonicalRelativePath, '/'),
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     path:string,
 *     error:string
 * }
 */
function app_compare_output_resolve_relative_path(string $basePath, string $relativePath, string $fieldName): array
{
    $normalized = app_compare_output_normalize_path_fragment($relativePath);
    if ($normalized === '') {
        return [
            'ok' => false,
            'path' => '',
            'error' => $fieldName . ' は必須です。',
        ];
    }

    if (!app_compare_output_relative_path_is_safe($normalized)) {
        return [
            'ok' => false,
            'path' => '',
            'error' => $fieldName . ' は storage base path 配下の相対 path で指定してください。',
        ];
    }

    return [
        'ok' => true,
        'path' => rtrim($basePath, '/') . '/' . ltrim($normalized, '/'),
        'error' => '',
    ];
}

/**
 * @param array{
 *     storage_base_path:string,
 *     output_file_path:string,
 *     compare_path:string
 * } $compareOutput
 * @return array{
 *     ok:bool,
 *     resolved_storage_base_path:string,
 *     compare_root_absolute_path:string,
 *     output_file_absolute_path:string,
 *     output_directory_absolute_path:string,
 *     error:string
 * }
 */
function app_compare_output_resolve_definition_paths(array $compareOutput): array
{
    $basePathResult = app_compare_output_resolve_base_path($compareOutput['storage_base_path']);
    if (!$basePathResult['ok']) {
        return [
            'ok' => false,
            'resolved_storage_base_path' => '',
            'compare_root_absolute_path' => '',
            'output_file_absolute_path' => '',
            'output_directory_absolute_path' => '',
            'error' => $basePathResult['error'],
        ];
    }

    $compareRootResult = app_compare_output_resolve_relative_path(
        $basePathResult['path'],
        $compareOutput['compare_path'],
        'compare_path',
    );
    if (!$compareRootResult['ok']) {
        return [
            'ok' => false,
            'resolved_storage_base_path' => $basePathResult['path'],
            'compare_root_absolute_path' => '',
            'output_file_absolute_path' => '',
            'output_directory_absolute_path' => '',
            'error' => $compareRootResult['error'],
        ];
    }

    $outputFileResult = app_compare_output_resolve_relative_path(
        $basePathResult['path'],
        $compareOutput['output_file_path'],
        'output_file_path',
    );
    if (!$outputFileResult['ok']) {
        return [
            'ok' => false,
            'resolved_storage_base_path' => $basePathResult['path'],
            'compare_root_absolute_path' => $compareRootResult['path'],
            'output_file_absolute_path' => '',
            'output_directory_absolute_path' => '',
            'error' => $outputFileResult['error'],
        ];
    }

    return [
        'ok' => true,
        'resolved_storage_base_path' => $basePathResult['path'],
        'compare_root_absolute_path' => $compareRootResult['path'],
        'output_file_absolute_path' => $outputFileResult['path'],
        'output_directory_absolute_path' => dirname($outputFileResult['path']),
        'error' => '',
    ];
}

/**
 * @return list<string>
 */
function app_compare_output_default_ignore_patterns(): array
{
    return app_compare_output_default_ignore_pattern_list();
}

/**
 * @param list<string> $ignorePatterns
 */
function app_compare_output_path_is_ignored(string $relativePath, array $ignorePatterns): bool
{
    foreach ($ignorePatterns as $pattern) {
        if (preg_match($pattern, $relativePath) === 1) {
            return true;
        }
    }

    return false;
}

function app_compare_output_ensure_directory(string $directory): void
{
    if (is_dir($directory)) {
        return;
    }

    if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('directory を作成できませんでした: ' . $directory);
    }
}

/**
 * @param list<string> $ignorePatterns
 * @return array{
 *     ok:bool,
 *     directories:list<string>,
 *     error:string
 * }
 */
function app_compare_output_collect_directories(string $root, array $ignorePatterns): array
{
    if (!is_dir($root)) {
        return [
            'ok' => false,
            'directories' => [],
            'error' => 'compare root directory が見つかりません: ' . $root,
        ];
    }

    $directories = [];

    try {
        $directoryIterator = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
        $filter = new RecursiveCallbackFilterIterator(
            $directoryIterator,
            static function (SplFileInfo $current, string $key, RecursiveIterator $iterator) use ($root, $ignorePatterns): bool {
                $relative = substr($current->getPathname(), strlen($root));
                $normalized = str_replace(DIRECTORY_SEPARATOR, '/', (string) $relative);
                if ($normalized === '') {
                    return true;
                }

                $normalized = '/' . ltrim($normalized, '/');
                if ($current->isDir()) {
                    return !app_compare_output_path_is_ignored($normalized, $ignorePatterns);
                }

                return true;
            },
        );
        $iterator = new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::SELF_FIRST);

        /** @var SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isDir()) {
                continue;
            }

            $directories[] = str_replace(DIRECTORY_SEPARATOR, '/', $fileInfo->getPathname());
        }
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'directories' => [],
            'error' => 'compare root directory の走査に失敗しました: ' . $throwable->getMessage(),
        ];
    }

    usort($directories, 'strcmp');

    return [
        'ok' => true,
        'directories' => $directories,
        'error' => '',
    ];
}

/**
 * @param list<string> $ignorePatterns
 * @return array{
 *     ok:bool,
 *     files:array<string,string>,
 *     error:string
 * }
 */
function app_compare_output_collect_file_hashes(string $root, array $ignorePatterns): array
{
    if (!is_dir($root)) {
        return [
            'ok' => false,
            'files' => [],
            'error' => '比較対象 directory が見つかりません: ' . $root,
        ];
    }

    $files = [];

    try {
        $directoryIterator = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
        $filter = new RecursiveCallbackFilterIterator(
            $directoryIterator,
            static function (SplFileInfo $current, string $key, RecursiveIterator $iterator) use ($root, $ignorePatterns): bool {
                $relative = substr($current->getPathname(), strlen($root));
                $normalized = str_replace(DIRECTORY_SEPARATOR, '/', (string) $relative);
                if ($normalized === '') {
                    return true;
                }

                $normalized = '/' . ltrim($normalized, '/');
                if ($current->isDir()) {
                    return !app_compare_output_path_is_ignored($normalized, $ignorePatterns);
                }

                return true;
            },
        );
        $iterator = new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::SELF_FIRST);

        /** @var SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            $relative = substr($fileInfo->getPathname(), strlen($root) + 1);
            if (!is_string($relative) || $relative === '') {
                continue;
            }

            $normalizedRelative = str_replace(DIRECTORY_SEPARATOR, '/', $relative);
            if (app_compare_output_path_is_ignored('/' . ltrim($normalizedRelative, '/'), $ignorePatterns)) {
                continue;
            }

            $hash = hash_file('sha256', $fileInfo->getPathname());
            if (!is_string($hash) || $hash === '') {
                return [
                    'ok' => false,
                    'files' => [],
                    'error' => 'file hash の計算に失敗しました: ' . $fileInfo->getPathname(),
                ];
            }

            $files[$normalizedRelative] = $hash;
        }
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'files' => [],
            'error' => '比較対象 directory の走査に失敗しました: ' . $throwable->getMessage(),
        ];
    }

    ksort($files);

    return [
        'ok' => true,
        'files' => $files,
        'error' => '',
    ];
}

/**
 * @param list<string> $ignorePatterns
 * @return array{
 *     ok:bool,
 *     deviation:bool,
 *     error:string
 * }
 */
function app_compare_output_directory_has_deviation(
    string $pathA,
    string $pathB,
    bool $isSameFilenameOnly,
    array $ignorePatterns,
): array {
    $filesAResult = app_compare_output_collect_file_hashes($pathA, $ignorePatterns);
    if (!$filesAResult['ok']) {
        return [
            'ok' => false,
            'deviation' => false,
            'error' => $filesAResult['error'],
        ];
    }

    $filesBResult = app_compare_output_collect_file_hashes($pathB, $ignorePatterns);
    if (!$filesBResult['ok']) {
        return [
            'ok' => false,
            'deviation' => false,
            'error' => $filesBResult['error'],
        ];
    }

    foreach ($filesAResult['files'] as $relativePath => $hashA) {
        if (!array_key_exists($relativePath, $filesBResult['files'])) {
            if (!$isSameFilenameOnly) {
                return [
                    'ok' => true,
                    'deviation' => true,
                    'error' => '',
                ];
            }

            continue;
        }

        if ($hashA !== $filesBResult['files'][$relativePath]) {
            return [
                'ok' => true,
                'deviation' => true,
                'error' => '',
            ];
        }
    }

    return [
        'ok' => true,
        'deviation' => false,
        'error' => '',
    ];
}

/**
 * @param array{
 *     compare_root_absolute_path:string
 * } $resolvedPaths
 * @param list<string> $ignorePatterns
 * @return array{
 *     ok:bool,
 *     pairs:list<array{
 *         pair_source:string,
 *         pair_key:string,
 *         path_a_absolute:string,
 *         path_b_absolute:string
 *     }>,
 *     checked_pair_count:int,
 *     warnings:list<string>,
 *     error:string
 * }
 */
function app_compare_output_collect_compare_root_pairs(array $resolvedPaths, array $ignorePatterns): array
{
    $directoryResult = app_compare_output_collect_directories(
        $resolvedPaths['compare_root_absolute_path'],
        $ignorePatterns,
    );
    if (!$directoryResult['ok']) {
        return [
            'ok' => false,
            'pairs' => [],
            'checked_pair_count' => 0,
            'warnings' => [],
            'error' => $directoryResult['error'],
        ];
    }

    $pairs = [];
    $checkedPairCount = 0;

    foreach ($directoryResult['directories'] as $directory) {
        $relative = substr($directory, strlen($resolvedPaths['compare_root_absolute_path']));
        if (!is_string($relative) || $relative === '') {
            continue;
        }

        $normalizedRelative = '/' . ltrim(str_replace(DIRECTORY_SEPARATOR, '/', $relative), '/');
        if (preg_match(app_compare_output_tmp_output_suffix_pattern(), $normalizedRelative) !== 1) {
            continue;
        }

        $counterpartRelative = preg_replace(
            app_compare_output_tmp_output_suffix_pattern(),
            '',
            $normalizedRelative,
        );
        if (!is_string($counterpartRelative) || $counterpartRelative === $normalizedRelative || $counterpartRelative === '') {
            continue;
        }

        $counterpartAbsolute = rtrim($resolvedPaths['compare_root_absolute_path'], '/') . $counterpartRelative;
        if (!is_dir($counterpartAbsolute)) {
            continue;
        }

        $checkedPairCount++;

        $comparisonResult = app_compare_output_directory_has_deviation(
            $directory,
            $counterpartAbsolute,
            false,
            $ignorePatterns,
        );
        if (!$comparisonResult['ok']) {
            return [
                'ok' => false,
                'pairs' => [],
                'checked_pair_count' => $checkedPairCount,
                'warnings' => [],
                'error' => $comparisonResult['error'],
            ];
        }

        if ($comparisonResult['deviation']) {
            $pairs[] = [
                'pair_source' => 'compare_path',
                'pair_key' => $normalizedRelative,
                'path_a_absolute' => $directory,
                'path_b_absolute' => $counterpartAbsolute,
            ];
        }
    }

    return [
        'ok' => true,
        'pairs' => $pairs,
        'checked_pair_count' => $checkedPairCount,
        'warnings' => [],
        'error' => '',
    ];
}

/**
 * @param array{
 *     storage_base_path:string
 * } $compareOutput
 * @param list<array{
 *     additional_path_key:string,
 *     path_a_base_path:string,
 *     path_a:string,
 *     path_b_base_path:string,
 *     path_b:string,
 *     is_same_filename_only:string,
 *     additional_path_list_order:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at:string
 * }> $additionalPaths
 * @param list<string> $ignorePatterns
 * @return array{
 *     ok:bool,
 *     pairs:list<array{
 *         pair_source:string,
 *         pair_key:string,
 *         path_a_absolute:string,
 *         path_b_absolute:string
 *     }>,
 *     checked_pair_count:int,
 *     warnings:list<string>,
 *     error:string
 * }
 */
function app_compare_output_collect_additional_path_pairs(
    array $compareOutput,
    array $additionalPaths,
    array $ignorePatterns,
): array {
    $pairs = [];
    $warnings = [];
    $checkedPairCount = 0;

    foreach ($additionalPaths as $additionalPath) {
        $basePathAResult = app_compare_output_resolve_base_path(
            $additionalPath['path_a_base_path'] !== ''
                ? $additionalPath['path_a_base_path']
                : $compareOutput['storage_base_path'],
        );
        if (!$basePathAResult['ok']) {
            $warnings[] = $additionalPath['additional_path_key'] . ': ' . $basePathAResult['error'];
            continue;
        }

        $basePathBResult = app_compare_output_resolve_base_path(
            $additionalPath['path_b_base_path'] !== ''
                ? $additionalPath['path_b_base_path']
                : $compareOutput['storage_base_path'],
        );
        if (!$basePathBResult['ok']) {
            $warnings[] = $additionalPath['additional_path_key'] . ': ' . $basePathBResult['error'];
            continue;
        }

        $pathAResult = app_compare_output_resolve_relative_path(
            $basePathAResult['path'],
            $additionalPath['path_a'],
            'path_a',
        );
        if (!$pathAResult['ok']) {
            $warnings[] = $additionalPath['additional_path_key'] . ': ' . $pathAResult['error'];
            continue;
        }

        $pathBResult = app_compare_output_resolve_relative_path(
            $basePathBResult['path'],
            $additionalPath['path_b'],
            'path_b',
        );
        if (!$pathBResult['ok']) {
            $warnings[] = $additionalPath['additional_path_key'] . ': ' . $pathBResult['error'];
            continue;
        }

        if (!is_dir($pathAResult['path'])) {
            $warnings[] = $additionalPath['additional_path_key'] . ': path_a directory が見つかりません: ' . $pathAResult['path'];
            continue;
        }

        if (!is_dir($pathBResult['path'])) {
            $warnings[] = $additionalPath['additional_path_key'] . ': path_b directory が見つかりません: ' . $pathBResult['path'];
            continue;
        }

        $checkedPairCount++;

        $comparisonResult = app_compare_output_directory_has_deviation(
            $pathAResult['path'],
            $pathBResult['path'],
            $additionalPath['is_same_filename_only'] === '1',
            $ignorePatterns,
        );
        if (!$comparisonResult['ok']) {
            return [
                'ok' => false,
                'pairs' => [],
                'checked_pair_count' => $checkedPairCount,
                'warnings' => $warnings,
                'error' => $comparisonResult['error'],
            ];
        }

        if ($comparisonResult['deviation']) {
            $pairs[] = [
                'pair_source' => 'additional_path',
                'pair_key' => $additionalPath['additional_path_key'],
                'path_a_absolute' => $pathAResult['path'],
                'path_b_absolute' => $pathBResult['path'],
            ];
        }
    }

    return [
        'ok' => true,
        'pairs' => $pairs,
        'checked_pair_count' => $checkedPairCount,
        'warnings' => $warnings,
        'error' => '',
    ];
}

/**
 * @return array{
 *     template:string,
 *     line_template:string
 * }
 */
function app_compare_output_template_spec(string $outputFileType): array
{
    return app_compare_output_builtin_template_spec($outputFileType);
}

function app_compare_output_render_template(string $template, array $replaceMap): string
{
    return str_replace(array_keys($replaceMap), array_values($replaceMap), $template);
}

function app_compare_output_normalize_absolute_path(string $path): string
{
    $normalized = str_replace('\\', '/', $path);
    if ($normalized !== '/' && str_ends_with($normalized, '/')) {
        $normalized = rtrim($normalized, '/');
    }

    return $normalized;
}

function app_compare_output_make_relative_path(string $fromDirectory, string $toPath): string
{
    $from = app_compare_output_normalize_absolute_path((string) (realpath($fromDirectory) ?: $fromDirectory));
    $to = app_compare_output_normalize_absolute_path((string) (realpath($toPath) ?: $toPath));

    if (preg_match('/^[A-Za-z]:/', $from) === 1 || preg_match('/^[A-Za-z]:/', $to) === 1) {
        if (substr($from, 0, 2) !== substr($to, 0, 2)) {
            return $to;
        }
    }

    $fromParts = array_values(array_filter(explode('/', trim($from, '/')), static fn (string $part): bool => $part !== ''));
    $toParts = array_values(array_filter(explode('/', trim($to, '/')), static fn (string $part): bool => $part !== ''));

    while ($fromParts !== [] && $toParts !== [] && $fromParts[0] === $toParts[0]) {
        array_shift($fromParts);
        array_shift($toParts);
    }

    $relativeParts = array_merge(array_fill(0, count($fromParts), '..'), $toParts);
    $relativePath = implode('/', $relativeParts);
    if ($relativePath === '') {
        return '.';
    }

    if (!str_starts_with($relativePath, '../') && !str_starts_with($relativePath, './')) {
        $relativePath = './' . $relativePath;
    }

    return $relativePath;
}

/**
 * @param list<array{
 *     pair_source:string,
 *     pair_key:string,
 *     path_a_absolute:string,
 *     path_b_absolute:string
 * }> $pairs
 * @return list<array{
 *     pair_source:string,
 *     pair_key:string,
 *     path_a:string,
 *     path_b:string
 * }>
 */
function app_compare_output_renderable_pairs(string $outputDirectory, array $pairs): array
{
    $rendered = [];

    foreach ($pairs as $pair) {
        $rendered[] = [
            'pair_source' => $pair['pair_source'],
            'pair_key' => $pair['pair_key'],
            'path_a' => app_compare_output_make_relative_path($outputDirectory, $pair['path_a_absolute']),
            'path_b' => app_compare_output_make_relative_path($outputDirectory, $pair['path_b_absolute']),
        ];
    }

    return $rendered;
}

/**
 * @param list<array{
 *     pair_source:string,
 *     pair_key:string,
 *     path_a:string,
 *     path_b:string
 * }> $pairs
 */
function app_compare_output_render_output_content(
    string $template,
    string $lineTemplate,
    string $compareToolFilePath,
    array $pairs,
): string {
    if ($template === '' || $lineTemplate === '') {
        throw new RuntimeException('未対応の output file type です。');
    }

    $lines = '';
    foreach ($pairs as $pair) {
        $lines .= app_compare_output_render_template(
            $lineTemplate,
            [
                '__COMPARE_COMMAND__' => $compareToolFilePath,
                '__PATH_A__' => $pair['path_a'],
                '__PATH_B__' => $pair['path_b'],
            ],
        );
    }

    return app_compare_output_render_template(
        $template,
        [
            '__LINES__' => $lines,
        ],
    );
}

/**
 * @param array{
 *     compare_output_key:string,
 *     name:string,
 *     storage_base_path:string,
 *     output_file_path:string,
 *     output_file_type:string,
 *     compare_path:string,
 *     compare_tool_file_path:string,
 *     compare_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at?:string
 * } $compareOutput
 * @param list<array{
 *     additional_path_key:string,
 *     path_a_base_path:string,
 *     path_a:string,
 *     path_b_base_path:string,
 *     path_b:string,
 *     is_same_filename_only:string,
 *     additional_path_list_order:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at?:string
 * }> $additionalPaths
 * @return array{
 *     ok:bool,
 *     output:array{
 *         project_key:string,
 *         compare_output_key:string,
 *         compare_output_name:string,
 *         output_file_type:string,
 *         requested_by:string,
 *         created_at:string,
 *         resolved_storage_base_path:string,
 *         compare_root_absolute_path:string,
 *         output_file_absolute_path:string,
 *         output_directory_absolute_path:string,
 *         deviation_pair_count:int,
 *         checked_pair_count:int,
 *         output_bytes:int,
 *         warnings:list<string>,
 *         pairs:list<array{
 *             pair_source:string,
 *             pair_key:string,
 *             path_a:string,
 *             path_b:string
 *         }>,
 *         rendered_content:string
 *     }|null,
 *     error:string
 * }
 */
function app_compare_output_generate_output_file(
    array $app,
    string $projectKey,
    array $compareOutput,
    array $additionalPaths,
    string $requestedBy = 'system',
): array {
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        return [
            'ok' => false,
            'output' => null,
            'error' => 'project key の形式が不正です。',
        ];
    }

    if (
        $compareOutput['compare_output_key'] === ''
        || !app_compare_output_key_is_valid(app_normalize_compare_output_key($compareOutput['compare_output_key']))
    ) {
        return [
            'ok' => false,
            'output' => null,
            'error' => 'compare output key の形式が不正です。',
        ];
    }

    $resolvedPaths = app_compare_output_resolve_definition_paths($compareOutput);
    if (!$resolvedPaths['ok']) {
        return [
            'ok' => false,
            'output' => null,
            'error' => $resolvedPaths['error'],
        ];
    }

    if (!is_dir($resolvedPaths['compare_root_absolute_path'])) {
        return [
            'ok' => false,
            'output' => null,
            'error' => 'compare root directory が見つかりません: ' . $resolvedPaths['compare_root_absolute_path'],
        ];
    }

    $ignorePatternsResult = app_compare_output_ignore_patterns_for_project($app, $normalizedProjectKey);
    if (!$ignorePatternsResult['ok']) {
        return [
            'ok' => false,
            'output' => null,
            'error' => $ignorePatternsResult['error'],
        ];
    }

    $ignorePatterns = $ignorePatternsResult['patterns'];
    $compareRootPairResult = app_compare_output_collect_compare_root_pairs($resolvedPaths, $ignorePatterns);
    if (!$compareRootPairResult['ok']) {
        return [
            'ok' => false,
            'output' => null,
            'error' => $compareRootPairResult['error'],
        ];
    }

    $additionalPairResult = app_compare_output_collect_additional_path_pairs(
        $compareOutput,
        $additionalPaths,
        $ignorePatterns,
    );
    if (!$additionalPairResult['ok']) {
        return [
            'ok' => false,
            'output' => null,
            'error' => $additionalPairResult['error'],
        ];
    }

    $pairs = array_merge($compareRootPairResult['pairs'], $additionalPairResult['pairs']);
    $warnings = array_merge($compareRootPairResult['warnings'], $additionalPairResult['warnings']);

    try {
        app_compare_output_ensure_directory($resolvedPaths['output_directory_absolute_path']);
        $renderedPairs = app_compare_output_renderable_pairs(
            $resolvedPaths['output_directory_absolute_path'],
            $pairs,
        );
        $templateSpecResult = app_compare_output_template_spec_for_project(
            $app,
            $normalizedProjectKey,
            $compareOutput['output_file_type'],
        );
        if (!$templateSpecResult['ok']) {
            throw new RuntimeException($templateSpecResult['error']);
        }
        $renderedContent = app_compare_output_render_output_content(
            $templateSpecResult['template'],
            $templateSpecResult['line_template'],
            $compareOutput['compare_tool_file_path'],
            $renderedPairs,
        );

        if (file_put_contents($resolvedPaths['output_file_absolute_path'], $renderedContent) === false) {
            throw new RuntimeException('compare output file の保存に失敗しました。');
        }

        return [
            'ok' => true,
            'output' => [
                'project_key' => $normalizedProjectKey,
                'compare_output_key' => app_normalize_compare_output_key($compareOutput['compare_output_key']),
                'compare_output_name' => $compareOutput['name'],
                'output_file_type' => $compareOutput['output_file_type'],
                'requested_by' => app_compare_output_normalize_requested_by($requestedBy),
                'created_at' => date(DATE_ATOM),
                'resolved_storage_base_path' => $resolvedPaths['resolved_storage_base_path'],
                'compare_root_absolute_path' => $resolvedPaths['compare_root_absolute_path'],
                'output_file_absolute_path' => $resolvedPaths['output_file_absolute_path'],
                'output_directory_absolute_path' => $resolvedPaths['output_directory_absolute_path'],
                'deviation_pair_count' => count($renderedPairs),
                'checked_pair_count' => $compareRootPairResult['checked_pair_count'] + $additionalPairResult['checked_pair_count'],
                'output_bytes' => strlen($renderedContent),
                'warnings' => $warnings,
                'pairs' => $renderedPairs,
                'rendered_content' => $renderedContent,
            ],
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'output' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}
