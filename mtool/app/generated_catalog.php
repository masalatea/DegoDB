<?php

declare(strict_types=1);

/**
 * @param array{
 *     generated:array{
 *         dbclasses_root:string
 *     }
 * } $app
 * @return array{
 *     entities:list<array{
 *         source_name:string,
 *         data_file:string,
 *         dbaccess_file:string,
 *         data_path:string,
 *         dbaccess_path:string,
 *         has_data_file:bool,
 *         has_dbaccess_file:bool
 *     }>,
 *     total_entities:int,
 *     paired_count:int,
 *     data_only_count:int,
 *     dbaccess_only_count:int
 * }
 */
function app_generated_entity_catalog(array $app): array
{
    $dbclassesRoot = $app['generated']['dbclasses_root'] ?? '';

    $dataFiles = glob($dbclassesRoot . '/data-*.php');
    $dbaccessFiles = glob($dbclassesRoot . '/dbaccess-*.php');

    $dataMap = [];
    if (is_array($dataFiles)) {
        foreach ($dataFiles as $file) {
            $sourceName = app_generated_catalog_source_name($file, 'data-');
            if ($sourceName === '') {
                continue;
            }

            $dataMap[$sourceName] = basename($file);
        }
    }

    $dbaccessMap = [];
    if (is_array($dbaccessFiles)) {
        foreach ($dbaccessFiles as $file) {
            $sourceName = app_generated_catalog_source_name($file, 'dbaccess-');
            if ($sourceName === '') {
                continue;
            }

            $dbaccessMap[$sourceName] = basename($file);
        }
    }

    $sourceNames = array_values(
        array_unique(
            array_merge(array_keys($dataMap), array_keys($dbaccessMap)),
        ),
    );
    natcasesort($sourceNames);

    $entities = [];
    $pairedCount = 0;
    $dataOnlyCount = 0;
    $dbaccessOnlyCount = 0;

    foreach ($sourceNames as $sourceName) {
        $hasDataFile = array_key_exists($sourceName, $dataMap);
        $hasDbaccessFile = array_key_exists($sourceName, $dbaccessMap);

        if ($hasDataFile && $hasDbaccessFile) {
            $pairedCount++;
        } elseif ($hasDataFile) {
            $dataOnlyCount++;
        } elseif ($hasDbaccessFile) {
            $dbaccessOnlyCount++;
        }

        $entities[] = [
            'source_name' => $sourceName,
            'data_file' => $dataMap[$sourceName] ?? '',
            'dbaccess_file' => $dbaccessMap[$sourceName] ?? '',
            'data_path' => $hasDataFile ? ($dbclassesRoot . '/' . $dataMap[$sourceName]) : '',
            'dbaccess_path' => $hasDbaccessFile ? ($dbclassesRoot . '/' . $dbaccessMap[$sourceName]) : '',
            'has_data_file' => $hasDataFile,
            'has_dbaccess_file' => $hasDbaccessFile,
        ];
    }

    return [
        'entities' => $entities,
        'total_entities' => count($entities),
        'paired_count' => $pairedCount,
        'data_only_count' => $dataOnlyCount,
        'dbaccess_only_count' => $dbaccessOnlyCount,
    ];
}

function app_generated_catalog_source_name(string $file, string $prefix): string
{
    $basename = basename($file);
    if (!str_starts_with($basename, $prefix) || !str_ends_with($basename, '.php')) {
        return '';
    }

    $sourceName = substr($basename, strlen($prefix), -4);
    if (!is_string($sourceName)) {
        return '';
    }

    return trim($sourceName);
}

/**
 * @param array{
 *     entities:list<array{
 *         source_name:string,
 *         data_file:string,
 *         dbaccess_file:string,
 *         data_path:string,
 *         dbaccess_path:string,
 *         has_data_file:bool,
 *         has_dbaccess_file:bool
 *     }>
 * } $catalog
 * @return array{
 *     source_name:string,
 *     data_file:string,
 *     dbaccess_file:string,
 *     data_path:string,
 *     dbaccess_path:string,
 *     has_data_file:bool,
 *     has_dbaccess_file:bool
 * }|null
 */
function app_generated_catalog_find_entity(array $catalog, string $sourceName): ?array
{
    foreach ($catalog['entities'] as $entity) {
        if (strcasecmp($entity['source_name'], $sourceName) === 0) {
            return $entity;
        }
    }

    return null;
}

/**
 * @return list<string>
 */
function app_generated_file_class_names(string $filePath): array
{
    $kind = app_generated_runtime_top_level_kind($filePath);
    if ($kind === '') {
        return app_generated_file_class_names_raw($filePath);
    }

    $classNames = [];
    $seen = [];
    $primaryClassPath = app_generated_runtime_primary_class_path($filePath);
    foreach (app_generated_file_class_names_raw($primaryClassPath) as $className) {
        app_generated_append_unique_string($classNames, $seen, $className);
    }

    if ($kind === 'data') {
        $expectedBaseClassName = app_generated_runtime_expected_base_class_name($filePath);
        foreach (app_generated_runtime_data_base_paths($filePath) as $basePath) {
            foreach (app_generated_file_class_names_raw($basePath) as $className) {
                if ($className === $expectedBaseClassName) {
                    continue;
                }

                app_generated_append_unique_string($classNames, $seen, $className);
            }
        }
    }

    return $classNames;
}

/**
 * @return list<string>
 */
function app_generated_file_property_names(string $filePath): array
{
    $kind = app_generated_runtime_top_level_kind($filePath);
    if ($kind === '') {
        return app_generated_file_property_names_raw($filePath);
    }

    $propertyNames = [];
    $seen = [];

    foreach (app_generated_runtime_property_analysis_paths($filePath) as $analysisPath) {
        foreach (app_generated_file_primary_class_property_names_raw($analysisPath) as $propertyName) {
            app_generated_append_unique_string($propertyNames, $seen, $propertyName);
        }
    }

    return $propertyNames;
}

/**
 * @return list<string>
 */
function app_generated_file_method_names(string $filePath): array
{
    $catalog = app_generated_file_method_catalog($filePath);

    return array_values(
        array_map(
            static fn (array $method): string => $method['name'],
            $catalog,
        ),
    );
}

function app_generated_file_excerpt(string $filePath, int $lineLimit = 24): string
{
    $filePath = app_generated_runtime_preferred_excerpt_source_path($filePath);
    if ($filePath === '' || !is_file($filePath) || !is_readable($filePath)) {
        return '';
    }

    $content = file($filePath, FILE_IGNORE_NEW_LINES);
    if (!is_array($content)) {
        return '';
    }

    return implode("\n", array_slice($content, 0, $lineLimit));
}

/**
 * @return list<array{
 *     name:string,
 *     line:int,
 *     end_line:int,
 *     signature:string
 * }>
 */
function app_generated_file_method_catalog(string $filePath): array
{
    $kind = app_generated_runtime_top_level_kind($filePath);
    if ($kind === '') {
        return app_generated_file_method_catalog_raw($filePath);
    }

    $catalog = [];
    $seen = [];
    foreach (app_generated_runtime_method_analysis_paths($filePath) as $analysisPath) {
        foreach (app_generated_file_method_catalog_raw($analysisPath) as $method) {
            $normalizedMethodName = trim((string) ($method['name'] ?? ''));
            if ($normalizedMethodName === '' || isset($seen[$normalizedMethodName])) {
                continue;
            }

            $seen[$normalizedMethodName] = true;
            $catalog[] = $method;
        }
    }

    return $catalog;
}

/**
 * @param list<array{
 *     name:string,
 *     line:int,
 *     end_line:int,
 *     signature:string
 * }> $catalog
 * @return array{
 *     name:string,
 *     line:int,
 *     end_line:int,
 *     signature:string
 * }|null
 */
function app_generated_file_find_method(array $catalog, string $methodName): ?array
{
    foreach ($catalog as $method) {
        if (strcasecmp($method['name'], $methodName) === 0) {
            return $method;
        }
    }

    return null;
}

function app_generated_file_method_excerpt(string $filePath, string $methodName, int $lineLimit = 40): string
{
    return app_generated_file_method_slice($filePath, $methodName, $lineLimit);
}

function app_generated_file_method_source(string $filePath, string $methodName): string
{
    return app_generated_file_method_slice($filePath, $methodName, 0);
}

function app_generated_file_method_has_blob_streaming_contract(string $filePath, string $methodName): bool
{
    foreach (app_generated_file_blob_contract_analysis_paths($filePath) as $analysisPath) {
        $source = app_generated_file_method_slice_from_analysis_path($analysisPath, $methodName, 0);
        if ($source === '') {
            continue;
        }

        if (
            preg_match('/\bprepare\s*\(/', $source) === 1
            && preg_match('/\bbind_param\s*\(\s*[\'"]b[\'"]/', $source) === 1
            && preg_match('/\bsend_long_data\s*\(/', $source) === 1
        ) {
            return true;
        }
    }

    return false;
}

function app_generated_file_method_slice(string $filePath, string $methodName, int $lineLimit = 0): string
{
    foreach (app_generated_runtime_method_analysis_paths($filePath) as $analysisPath) {
        $slice = app_generated_file_method_slice_from_analysis_path($analysisPath, $methodName, $lineLimit);
        if ($slice !== '') {
            return $slice;
        }
    }

    return '';
}

function app_generated_file_method_slice_from_analysis_path(
    string $analysisPath,
    string $methodName,
    int $lineLimit = 0,
): string {
    $catalog = app_generated_file_method_catalog_raw($analysisPath);
    $method = app_generated_file_find_method($catalog, $methodName);
    if ($method === null) {
        return '';
    }

    $lines = file($analysisPath, FILE_IGNORE_NEW_LINES);
    if (!is_array($lines)) {
        return '';
    }

    $methodLength = max(1, $method['end_line'] - $method['line'] + 1);
    $length = $lineLimit > 0 ? min($lineLimit, $methodLength) : $methodLength;

    return implode(
        "\n",
        array_slice($lines, $method['line'] - 1, $length),
    );
}

/**
 * @return list<string>
 */
function app_generated_file_blob_contract_analysis_paths(string $filePath): array
{
    $paths = app_generated_runtime_method_analysis_paths($filePath);
    if (app_generated_runtime_top_level_kind($filePath) === 'dbaccess') {
        $legacySupportPath = dirname($filePath) . '/_support/legacy-dbaccess/' . basename($filePath);
        if (app_generated_file_exists_and_readable($legacySupportPath)) {
            $paths[] = $legacySupportPath;
        }
    }

    return array_values(array_unique($paths));
}

function app_generated_file_exists_and_readable(string $filePath): bool
{
    return $filePath !== '' && is_file($filePath) && is_readable($filePath);
}

function app_generated_runtime_top_level_kind(string $filePath): string
{
    $basename = basename($filePath);
    $parentDir = basename(dirname($filePath));
    if (in_array($parentDir, ['base', '_base', '_wrappers'], true)) {
        return '';
    }

    if (str_starts_with($basename, 'data-') && str_ends_with($basename, '.php')) {
        return 'data';
    }
    if (str_starts_with($basename, 'dbaccess-') && str_ends_with($basename, '.php')) {
        return 'dbaccess';
    }

    return '';
}

function app_generated_runtime_base_companion_path(string $filePath): string
{
    $kind = app_generated_runtime_top_level_kind($filePath);
    if ($kind === '') {
        return '';
    }

    $basename = preg_replace('/\.php$/', 'Base.php', basename($filePath));
    if (!is_string($basename) || $basename === '') {
        return '';
    }

    return dirname($filePath) . '/base/' . $basename;
}

function app_generated_runtime_layered_base_companion_path(string $filePath): string
{
    if (app_generated_runtime_top_level_kind($filePath) !== 'data') {
        return '';
    }

    return dirname($filePath) . '/_base/' . basename($filePath);
}

function app_generated_runtime_layered_wrapper_companion_path(string $filePath): string
{
    if (app_generated_runtime_top_level_kind($filePath) !== 'data') {
        return '';
    }

    return dirname($filePath) . '/_wrappers/' . basename($filePath);
}

function app_generated_runtime_expected_base_class_name(string $filePath): string
{
    $kind = app_generated_runtime_top_level_kind($filePath);
    if ($kind === '') {
        return '';
    }

    $prefix = $kind === 'data' ? 'data-' : 'dbaccess-';
    $sourceName = app_generated_catalog_source_name($filePath, $prefix);
    if ($sourceName === '') {
        return '';
    }

    return $sourceName . ($kind === 'data' ? 'DataBase' : 'DBAccessBase');
}

function app_generated_runtime_primary_class_path(string $filePath): string
{
    $layeredWrapperPath = app_generated_runtime_layered_wrapper_companion_path($filePath);
    if (
        app_generated_file_exists_and_readable($layeredWrapperPath)
        && app_generated_file_class_names_raw($filePath) === []
    ) {
        return $layeredWrapperPath;
    }

    return $filePath;
}

/**
 * @return list<string>
 */
function app_generated_runtime_data_base_paths(string $filePath): array
{
    $paths = [];
    foreach (
        [
            app_generated_runtime_layered_base_companion_path($filePath),
            app_generated_runtime_base_companion_path($filePath),
        ] as $candidatePath
    ) {
        if (!app_generated_file_exists_and_readable($candidatePath)) {
            continue;
        }

        $paths[] = $candidatePath;
    }

    return $paths;
}

/**
 * @return list<string>
 */
function app_generated_runtime_property_analysis_paths(string $filePath): array
{
    $kind = app_generated_runtime_top_level_kind($filePath);
    if ($kind !== 'data') {
        return [$filePath];
    }

    $layeredBasePath = app_generated_runtime_layered_base_companion_path($filePath);
    if (app_generated_file_exists_and_readable($layeredBasePath)) {
        return [$layeredBasePath];
    }

    $paths = [];
    $basePath = app_generated_runtime_base_companion_path($filePath);
    if (app_generated_file_exists_and_readable($basePath)) {
        $paths[] = $basePath;
    }
    if (app_generated_file_exists_and_readable($filePath)) {
        $paths[] = $filePath;
    }

    return $paths === [] ? [$filePath] : $paths;
}

/**
 * @return list<string>
 */
function app_generated_runtime_method_analysis_paths(string $filePath): array
{
    $kind = app_generated_runtime_top_level_kind($filePath);
    if ($kind === '') {
        return [$filePath];
    }

    if ($kind === 'dbaccess') {
        $basePath = app_generated_runtime_base_companion_path($filePath);
        if (app_generated_file_exists_and_readable($basePath)) {
            return [$basePath];
        }

        return [$filePath];
    }

    $layeredBasePath = app_generated_runtime_layered_base_companion_path($filePath);
    if (app_generated_file_exists_and_readable($layeredBasePath)) {
        return [$layeredBasePath];
    }

    $paths = [];
    if (app_generated_file_exists_and_readable($filePath)) {
        $paths[] = $filePath;
    }

    $basePath = app_generated_runtime_base_companion_path($filePath);
    if (app_generated_file_exists_and_readable($basePath)) {
        $paths[] = $basePath;
    }

    return $paths === [] ? [$filePath] : $paths;
}

function app_generated_runtime_file_is_generated_entry(string $filePath): bool
{
    if (!app_generated_file_exists_and_readable($filePath)) {
        return false;
    }

    $content = file_get_contents($filePath);
    if (!is_string($content) || $content === '') {
        return false;
    }

    return str_contains($content, 'mtool_runtime_bundle_load_layered_file(')
        || str_contains($content, 'mtool_runtime_bundle_load_custom_wrapper(');
}

function app_generated_runtime_preferred_excerpt_source_path(string $filePath): string
{
    $kind = app_generated_runtime_top_level_kind($filePath);
    if ($kind === '') {
        return $filePath;
    }

    if ($kind === 'dbaccess') {
        $basePath = app_generated_runtime_base_companion_path($filePath);
        if (app_generated_file_exists_and_readable($basePath)) {
            return $basePath;
        }

        return $filePath;
    }

    if (!app_generated_runtime_file_is_generated_entry($filePath)) {
        return $filePath;
    }

    $layeredBasePath = app_generated_runtime_layered_base_companion_path($filePath);
    if (app_generated_file_exists_and_readable($layeredBasePath)) {
        return $layeredBasePath;
    }

    $basePath = app_generated_runtime_base_companion_path($filePath);
    if (app_generated_file_exists_and_readable($basePath)) {
        return $basePath;
    }

    $layeredWrapperPath = app_generated_runtime_layered_wrapper_companion_path($filePath);
    if (app_generated_file_exists_and_readable($layeredWrapperPath)) {
        return $layeredWrapperPath;
    }

    return $filePath;
}

/**
 * @param array<string,bool> $seen
 * @param list<string> $values
 */
function app_generated_append_unique_string(array &$values, array &$seen, string $value): void
{
    $normalizedValue = trim($value);
    if ($normalizedValue === '' || isset($seen[$normalizedValue])) {
        return;
    }

    $seen[$normalizedValue] = true;
    $values[] = $normalizedValue;
}

/**
 * @return list<string>
 */
function app_generated_file_class_names_raw(string $filePath): array
{
    if ($filePath === '' || !is_file($filePath) || !is_readable($filePath)) {
        return [];
    }

    $content = file_get_contents($filePath);
    if (!is_string($content) || $content === '') {
        return [];
    }

    $tokens = token_get_all($content);
    $classNames = [];
    $seen = [];
    $classLikeTokenIds = [T_CLASS, T_INTERFACE, T_TRAIT];
    if (defined('T_ENUM')) {
        $classLikeTokenIds[] = constant('T_ENUM');
    }

    $tokenCount = count($tokens);
    for ($index = 0; $index < $tokenCount; $index++) {
        $token = $tokens[$index];
        if (!is_array($token) || !in_array($token[0], $classLikeTokenIds, true)) {
            continue;
        }

        for ($lookahead = $index + 1; $lookahead < $tokenCount; $lookahead++) {
            $nextToken = $tokens[$lookahead];
            if (is_array($nextToken)) {
                if (in_array($nextToken[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                    continue;
                }

                if ($nextToken[0] === T_STRING) {
                    app_generated_append_unique_string($classNames, $seen, $nextToken[1]);
                    break;
                }

                continue;
            }

            if ($nextToken === '{' || $nextToken === ';') {
                break;
            }
        }
    }

    return $classNames;
}

/**
 * @return list<string>
 */
function app_generated_file_property_names_raw(string $filePath): array
{
    return app_generated_file_regex_list($filePath, '/public\s+\$([A-Za-z0-9_]+)/');
}

/**
 * @return list<string>
 */
function app_generated_file_primary_class_property_names_raw(string $filePath): array
{
    if ($filePath === '' || !is_file($filePath) || !is_readable($filePath)) {
        return [];
    }

    $content = file_get_contents($filePath);
    if (!is_string($content) || $content === '') {
        return [];
    }

    $tokens = token_get_all($content);
    $properties = [];
    $seen = [];
    $classLikeTokenIds = [T_CLASS, T_INTERFACE, T_TRAIT];
    if (defined('T_ENUM')) {
        $classLikeTokenIds[] = constant('T_ENUM');
    }

    $tokenCount = count($tokens);
    $braceDepth = 0;
    $pendingClassLike = false;
    $targetClassBraceDepth = null;
    $memberIsPublic = false;
    $memberIsStatic = false;

    for ($index = 0; $index < $tokenCount; $index++) {
        $token = $tokens[$index];
        if (is_string($token)) {
            if ($token === '{') {
                $braceDepth++;
                if ($pendingClassLike && $targetClassBraceDepth === null) {
                    $targetClassBraceDepth = $braceDepth;
                }
                $pendingClassLike = false;
                continue;
            }

            if ($token === '}') {
                if ($targetClassBraceDepth !== null && $braceDepth === $targetClassBraceDepth) {
                    break;
                }

                if ($braceDepth > 0) {
                    $braceDepth--;
                }
                $pendingClassLike = false;
                if ($targetClassBraceDepth !== null && $braceDepth === $targetClassBraceDepth) {
                    $memberIsPublic = false;
                    $memberIsStatic = false;
                }
                continue;
            }

            if ($token === ';') {
                $pendingClassLike = false;
                if ($targetClassBraceDepth !== null && $braceDepth === $targetClassBraceDepth) {
                    $memberIsPublic = false;
                    $memberIsStatic = false;
                }
            }
            continue;
        }

        if (in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        if (in_array($token[0], $classLikeTokenIds, true)) {
            $pendingClassLike = true;
            $memberIsPublic = false;
            $memberIsStatic = false;
            continue;
        }

        if ($pendingClassLike && $targetClassBraceDepth === null) {
            continue;
        }

        if ($targetClassBraceDepth === null || $braceDepth !== $targetClassBraceDepth) {
            continue;
        }

        if ($token[0] === T_PUBLIC) {
            $memberIsPublic = true;
            continue;
        }

        if (in_array($token[0], [T_PRIVATE, T_PROTECTED], true) || (defined('T_VAR') && $token[0] === T_VAR)) {
            $memberIsPublic = false;
            $memberIsStatic = false;
            continue;
        }

        if ($token[0] === T_STATIC) {
            $memberIsStatic = true;
            continue;
        }

        if (in_array($token[0], [T_CONST, T_FUNCTION], true)) {
            $memberIsPublic = false;
            $memberIsStatic = false;
            continue;
        }

        if ($token[0] === T_VARIABLE && $memberIsPublic && !$memberIsStatic) {
            app_generated_append_unique_string(
                $properties,
                $seen,
                ltrim((string) $token[1], '$'),
            );
        }
    }

    return $properties;
}

/**
 * @return list<array{
 *     name:string,
 *     line:int,
 *     end_line:int,
 *     signature:string
 * }>
 */
function app_generated_file_method_catalog_raw(string $filePath): array
{
    if ($filePath === '' || !is_file($filePath) || !is_readable($filePath)) {
        return [];
    }

    $content = file_get_contents($filePath);
    if (!is_string($content) || $content === '') {
        return [];
    }

    $tokens = token_get_all($content);
    $methods = [];
    $tokenCount = count($tokens);
    $braceDepth = 0;
    $pendingClassLike = false;
    $classLikeBraceDepth = null;

    for ($index = 0; $index < $tokenCount; $index++) {
        $token = $tokens[$index];
        if (is_string($token)) {
            if ($token === '{') {
                $braceDepth++;
                if ($pendingClassLike && $classLikeBraceDepth === null) {
                    $classLikeBraceDepth = $braceDepth;
                }
                $pendingClassLike = false;
                continue;
            }

            if ($token === '}') {
                if ($classLikeBraceDepth !== null && $braceDepth === $classLikeBraceDepth) {
                    $classLikeBraceDepth = null;
                }
                if ($braceDepth > 0) {
                    $braceDepth--;
                }
                $pendingClassLike = false;
                continue;
            }

            if ($token === ';') {
                $pendingClassLike = false;
            }
            continue;
        }

        if (in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        if (defined('T_ENUM') && $token[0] === T_ENUM) {
            $pendingClassLike = true;
            continue;
        }

        if (in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT], true)) {
            $pendingClassLike = true;
            continue;
        }

        if ($pendingClassLike && $classLikeBraceDepth === null) {
            continue;
        }

        if ($token[0] !== T_FUNCTION || $classLikeBraceDepth === null) {
            $pendingClassLike = false;
            continue;
        }

        $methodName = '';
        $methodLine = (int) $token[2];

        for ($lookahead = $index + 1; $lookahead < $tokenCount; $lookahead++) {
            $nextToken = $tokens[$lookahead];
            if (is_string($nextToken)) {
                if ($nextToken === '(') {
                    break;
                }
                continue;
            }

            if ($nextToken[0] === T_STRING) {
                $methodName = trim($nextToken[1]);
                break;
            }
        }

        if ($methodName === '') {
            continue;
        }

        $methods[] = [
            'name' => $methodName,
            'line' => $methodLine,
            'end_line' => $methodLine,
            'signature' => '',
        ];
    }

    if ($methods === []) {
        return [];
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES);
    if (!is_array($lines)) {
        return $methods;
    }

    $lineCount = count($lines);

    foreach ($methods as $index => $method) {
        $startLine = max(1, min($lineCount, $method['line']));
        $endLine = $index + 1 < count($methods)
            ? max($startLine, $methods[$index + 1]['line'] - 1)
            : $lineCount;
        $signature = trim($lines[$startLine - 1] ?? '');

        $methods[$index]['line'] = $startLine;
        $methods[$index]['end_line'] = $endLine;
        $methods[$index]['signature'] = $signature !== ''
            ? $signature
            : ('function ' . $method['name'] . '(...)');
    }

    return $methods;
}

/**
 * @return list<string>
 */
function app_generated_file_regex_list(string $filePath, string $pattern): array
{
    if ($filePath === '' || !is_file($filePath) || !is_readable($filePath)) {
        return [];
    }

    $content = file_get_contents($filePath);
    if (!is_string($content) || $content === '') {
        return [];
    }

    $matches = [];
    preg_match_all($pattern, $content, $matches);
    $values = $matches[1] ?? [];
    if (!is_array($values)) {
        return [];
    }

    $normalized = array_values(
        array_unique(
            array_filter(
                array_map(
                    static fn ($value): string => is_string($value) ? trim($value) : '',
                    $values,
                ),
                static fn (string $value): bool => $value !== '',
            ),
        ),
    );

    return $normalized;
}
