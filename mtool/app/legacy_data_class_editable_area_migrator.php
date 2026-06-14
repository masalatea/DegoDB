<?php

declare(strict_types=1);

/**
 * @return array{
 *     ok:bool,
 *     content:string,
 *     full_match:string,
 *     offset:int,
 *     end_offset:int,
 *     error:string
 * }
 */
function app_legacy_data_class_extract_editable_area(string $contents, string $areaName): array
{
    $pattern = '/\/\/ == START OF EDITABLE AREA FOR '
        . preg_quote($areaName, '/')
        . ' ==\R?(.*?)\R?\/\/ == END OF EDITABLE AREA FOR '
        . preg_quote($areaName, '/')
        . ' ==/s';

    $matches = [];
    $matched = preg_match($pattern, $contents, $matches, PREG_OFFSET_CAPTURE);
    if ($matched !== 1) {
        return [
            'ok' => false,
            'content' => '',
            'full_match' => '',
            'offset' => -1,
            'end_offset' => -1,
            'error' => 'editable area が見つかりません: ' . $areaName,
        ];
    }

    $fullMatch = (
        isset($matches[0][0]) && is_string($matches[0][0])
            ? $matches[0][0]
            : ''
    );
    $content = (
        isset($matches[1][0]) && is_string($matches[1][0])
            ? $matches[1][0]
            : ''
    );
    $offset = isset($matches[0][1]) ? (int) $matches[0][1] : -1;

    return [
        'ok' => true,
        'content' => $content,
        'full_match' => $fullMatch,
        'offset' => $offset,
        'end_offset' => $offset >= 0 ? ($offset + strlen($fullMatch)) : -1,
        'error' => '',
    ];
}

function app_legacy_data_class_normalize_newlines(string $contents): string
{
    return str_replace(["\r\n", "\r"], "\n", $contents);
}

function app_legacy_data_class_trim_outer_blank_lines(string $contents): string
{
    $normalized = app_legacy_data_class_normalize_newlines($contents);

    $trimmed = preg_replace('/\A(?:[ \t]*\n)+/', '', $normalized);
    if (!is_string($trimmed)) {
        $trimmed = $normalized;
    }

    $trimmed = preg_replace('/(?:\n[ \t]*)+\z/', '', $trimmed);
    if (!is_string($trimmed)) {
        return $normalized;
    }

    return $trimmed;
}

/**
 * @return list<string>
 */
function app_legacy_data_class_property_names_from_string(string $contents): array
{
    $matches = [];
    $matched = preg_match_all('/public\s+\$([A-Za-z0-9_]+)/', $contents, $matches);
    if ($matched === false || !isset($matches[1]) || !is_array($matches[1])) {
        return [];
    }

    $properties = [];
    $seen = [];
    foreach ($matches[1] as $propertyName) {
        $normalizedPropertyName = trim((string) $propertyName);
        if ($normalizedPropertyName === '' || isset($seen[$normalizedPropertyName])) {
            continue;
        }

        $seen[$normalizedPropertyName] = true;
        $properties[] = $normalizedPropertyName;
    }

    return $properties;
}

/**
 * @return list<string>
 */
function app_legacy_data_class_primary_class_property_names_from_string(string $contents): array
{
    $tokens = token_get_all($contents);
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
            $propertyName = trim(ltrim((string) $token[1], '$'));
            if ($propertyName === '' || isset($seen[$propertyName])) {
                continue;
            }

            $seen[$propertyName] = true;
            $properties[] = $propertyName;
        }
    }

    return $properties;
}

function app_legacy_data_class_wrapper_top_level_section(string $contents): string
{
    $trimmed = app_legacy_data_class_trim_outer_blank_lines($contents);
    if ($trimmed === '' || trim($trimmed) === '') {
        return '';
    }

    return $trimmed . "\n\n";
}

function app_legacy_data_class_wrapper_class_body_section(string $contents): string
{
    $trimmed = app_legacy_data_class_trim_outer_blank_lines($contents);
    if ($trimmed === '' || trim($trimmed) === '') {
        return '';
    }

    return $trimmed . "\n";
}

function app_legacy_data_class_base_trailing_section(string $contents): string
{
    $trimmed = app_legacy_data_class_trim_outer_blank_lines($contents);
    if ($trimmed === '' || trim($trimmed) === '') {
        return '';
    }

    return "\n" . $trimmed . "\n\n";
}

function app_legacy_data_class_source_name_from_class_name(string $className): string
{
    if (!str_ends_with($className, 'Data')) {
        return '';
    }

    return substr($className, 0, -4);
}

/**
 * @return list<string>
 */
function app_legacy_data_class_class_names_from_section(string $contents): array
{
    $matches = [];
    $matched = preg_match_all('/^class\s+([A-Za-z0-9_]+)/m', $contents, $matches);
    if ($matched === false || !isset($matches[1]) || !is_array($matches[1])) {
        return [];
    }

    $classNames = [];
    $seen = [];
    foreach ($matches[1] as $className) {
        $normalizedClassName = trim((string) $className);
        if ($normalizedClassName === '' || isset($seen[$normalizedClassName])) {
            continue;
        }

        $seen[$normalizedClassName] = true;
        $classNames[] = $normalizedClassName;
    }

    return $classNames;
}

/**
 * @return array{
 *     property_names:list<string>,
 *     method_names:list<string>,
 *     has_non_method_code:bool,
 *     has_unsupported_code:bool
 * }
 */
function app_legacy_data_class_additional_class_definition_analysis(string $contents): array
{
    $syntheticPhp = "<?php\nclass __LegacyAdditionalClassDefinition__\n{\n"
        . $contents
        . "\n}\n";
    $tokens = token_get_all($syntheticPhp);
    $tokenCount = count($tokens);
    $braceDepth = 0;
    $pendingClassLike = false;
    $classLikeBraceDepth = null;
    $pendingMethod = false;
    $propertyNames = [];
    $seenPropertyNames = [];
    $methodNames = [];
    $seenMethodNames = [];
    $hasNonMethodCode = false;
    $hasUnsupportedCode = false;
    $ignoredTokenIds = [
        T_WHITESPACE,
        T_COMMENT,
        T_DOC_COMMENT,
        T_PUBLIC,
        T_PROTECTED,
        T_PRIVATE,
        T_STATIC,
        T_ABSTRACT,
        T_FINAL,
        T_VAR,
    ];
    if (defined('T_READONLY')) {
        $ignoredTokenIds[] = constant('T_READONLY');
    }
    if (defined('T_ATTRIBUTE')) {
        $ignoredTokenIds[] = constant('T_ATTRIBUTE');
    }

    for ($index = 0; $index < $tokenCount; $index++) {
        $token = $tokens[$index];
        if (is_string($token)) {
            if ($token === '{') {
                $braceDepth++;
                if ($pendingClassLike && $classLikeBraceDepth === null) {
                    $classLikeBraceDepth = $braceDepth;
                    $pendingClassLike = false;
                    continue;
                }
                if (
                    $pendingMethod
                    && $classLikeBraceDepth !== null
                    && $braceDepth === ($classLikeBraceDepth + 1)
                ) {
                    $pendingMethod = false;
                }
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

            if (
                $token === ';'
                && $pendingMethod
                && $classLikeBraceDepth !== null
                && $braceDepth === $classLikeBraceDepth
            ) {
                $pendingMethod = false;
                continue;
            }

            if (
                $classLikeBraceDepth !== null
                && $braceDepth === $classLikeBraceDepth
                && !$pendingMethod
                && trim($token) !== ''
            ) {
                $hasNonMethodCode = true;
            }
            continue;
        }

        if ($token[0] === T_CLASS) {
            $pendingClassLike = true;
            continue;
        }

        if ($pendingClassLike && $classLikeBraceDepth === null) {
            continue;
        }

        if ($classLikeBraceDepth === null || $braceDepth !== $classLikeBraceDepth) {
            continue;
        }

        if (in_array($token[0], $ignoredTokenIds, true)) {
            continue;
        }

        if ($pendingMethod) {
            continue;
        }

        if ($token[0] === T_VARIABLE) {
            $propertyName = ltrim(trim($token[1]), '$');
            if ($propertyName !== '' && !isset($seenPropertyNames[$propertyName])) {
                $seenPropertyNames[$propertyName] = true;
                $propertyNames[] = $propertyName;
            }

            $hasNonMethodCode = true;
            $propertyTerminated = false;

            for ($lookahead = $index + 1; $lookahead < $tokenCount; $lookahead++) {
                $nextToken = $tokens[$lookahead];
                if (is_string($nextToken)) {
                    if ($nextToken === ';') {
                        $propertyTerminated = true;
                        $index = $lookahead;
                        break;
                    }

                    if ($nextToken === '{' || $nextToken === '}') {
                        break;
                    }

                    continue;
                }

                if ($nextToken[0] === T_VARIABLE) {
                    $nextPropertyName = ltrim(trim($nextToken[1]), '$');
                    if ($nextPropertyName !== '' && !isset($seenPropertyNames[$nextPropertyName])) {
                        $seenPropertyNames[$nextPropertyName] = true;
                        $propertyNames[] = $nextPropertyName;
                    }
                }
            }

            if (!$propertyTerminated) {
                $hasUnsupportedCode = true;
            }

            continue;
        }

        if ($token[0] === T_FUNCTION) {
            $methodName = '';
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
                $hasNonMethodCode = true;
                $hasUnsupportedCode = true;
                continue;
            }

            if (!isset($seenMethodNames[$methodName])) {
                $seenMethodNames[$methodName] = true;
                $methodNames[] = $methodName;
            }
            $pendingMethod = true;
            continue;
        }

        $hasNonMethodCode = true;
        $hasUnsupportedCode = true;
    }

    return [
        'property_names' => $propertyNames,
        'method_names' => $methodNames,
        'has_non_method_code' => $hasNonMethodCode,
        'has_unsupported_code' => $hasUnsupportedCode,
    ];
}

function app_legacy_data_class_generated_trailing_section(string $contents, array $bottomArea): string
{
    $bottomEndOffset = (int) ($bottomArea['end_offset'] ?? -1);
    if ($bottomEndOffset < 0) {
        return '';
    }

    $section = substr($contents, $bottomEndOffset);
    if (!is_string($section) || $section === '') {
        return '';
    }

    $sectionWithoutClosingTag = preg_replace('/\s*\?>\s*\z/s', '', $section);
    if (is_string($sectionWithoutClosingTag)) {
        $section = $sectionWithoutClosingTag;
    }

    return app_legacy_data_class_trim_outer_blank_lines($section);
}

/**
 * @param list<string> $sections
 */
function app_legacy_data_class_join_sections(array $sections): string
{
    $normalizedSections = [];

    foreach ($sections as $section) {
        $trimmed = app_legacy_data_class_trim_outer_blank_lines($section);
        if ($trimmed === '' || trim($trimmed) === '') {
            continue;
        }

        $normalizedSections[] = $trimmed;
    }

    return implode("\n\n", $normalizedSections);
}

/**
 * @return array{
 *     class_section:string,
 *     class_names:list<string>,
 *     remainder_section:string,
 *     has_unclosed_class:bool
 * }
 */
function app_legacy_data_class_split_top_level_class_declarations(string $contents): array
{
    $tokens = token_get_all("<?php\n" . $contents);
    $braceDepth = 0;
    $classBraceDepth = null;
    $inClass = false;
    $pendingClass = false;
    $captureClassName = false;
    $classNames = [];
    $classChunks = [];
    $currentClassText = '';
    $currentOtherText = '';
    $started = false;

    foreach ($tokens as $token) {
        if (!$started && is_array($token) && $token[0] === T_OPEN_TAG) {
            $started = true;
            continue;
        }
        $started = true;

        $text = is_array($token) ? (string) $token[1] : $token;

        if (
            !$inClass
            && is_array($token)
            && $token[0] === T_CLASS
            && $braceDepth === 0
        ) {
            $inClass = true;
            $pendingClass = true;
            $captureClassName = true;
            $classBraceDepth = null;
        }

        if ($inClass) {
            $currentClassText .= $text;
        } else {
            $currentOtherText .= $text;
        }

        if ($captureClassName && is_array($token) && $token[0] === T_STRING) {
            $classNames[] = trim((string) $token[1]);
            $captureClassName = false;
        }

        if (!is_string($token)) {
            continue;
        }

        if ($token === '{') {
            $braceDepth++;
            if ($inClass && $pendingClass && $classBraceDepth === null) {
                $classBraceDepth = $braceDepth;
                $pendingClass = false;
            }
            continue;
        }

        if ($token !== '}') {
            continue;
        }

        if ($inClass && $classBraceDepth !== null && $braceDepth === $classBraceDepth) {
            if ($braceDepth > 0) {
                $braceDepth--;
            }
            $classChunks[] = $currentClassText;
            $currentClassText = '';
            $inClass = false;
            $pendingClass = false;
            $captureClassName = false;
            $classBraceDepth = null;
            continue;
        }

        if ($braceDepth > 0) {
            $braceDepth--;
        }
    }

    return [
        'class_section' => app_legacy_data_class_join_sections($classChunks),
        'class_names' => array_values(
            array_filter(
                $classNames,
                static fn (string $className): bool => trim($className) !== '',
            ),
        ),
        'remainder_section' => app_legacy_data_class_trim_outer_blank_lines($currentOtherText),
        'has_unclosed_class' => $inClass,
    ];
}

/**
 * @return array{
 *     function_names:list<string>,
 *     has_unsupported_code:bool
 * }
 */
function app_legacy_data_class_top_level_function_section_analysis(string $contents): array
{
    $trimmedContents = app_legacy_data_class_trim_outer_blank_lines($contents);
    if ($trimmedContents === '' || trim($trimmedContents) === '') {
        return [
            'function_names' => [],
            'has_unsupported_code' => false,
        ];
    }

    $tokens = token_get_all("<?php\n" . $trimmedContents);
    $braceDepth = 0;
    $pendingFunction = false;
    $functionNames = [];
    $seenFunctionNames = [];
    $hasUnsupportedCode = false;
    $started = false;
    $ignoredTokenIds = [
        T_WHITESPACE,
        T_COMMENT,
        T_DOC_COMMENT,
    ];

    foreach ($tokens as $index => $token) {
        if (!$started && is_array($token) && $token[0] === T_OPEN_TAG) {
            $started = true;
            continue;
        }
        $started = true;

        if (is_string($token)) {
            if ($token === '{') {
                $braceDepth++;
                $pendingFunction = false;
                continue;
            }

            if ($token === '}') {
                if ($braceDepth > 0) {
                    $braceDepth--;
                }
                continue;
            }

            if ($token === ';' && $pendingFunction && $braceDepth === 0) {
                $pendingFunction = false;
                continue;
            }

            if ($pendingFunction) {
                continue;
            }

            if ($braceDepth === 0 && trim($token) !== '') {
                $hasUnsupportedCode = true;
            }
            continue;
        }

        if ($braceDepth !== 0) {
            continue;
        }

        if (in_array($token[0], $ignoredTokenIds, true)) {
            continue;
        }

        if ($pendingFunction) {
            continue;
        }

        if ($token[0] === T_FUNCTION) {
            $functionName = '';
            for ($lookahead = $index + 1, $tokenCount = count($tokens); $lookahead < $tokenCount; $lookahead++) {
                $nextToken = $tokens[$lookahead];
                if (is_string($nextToken)) {
                    if ($nextToken === '(') {
                        break;
                    }
                    continue;
                }

                if ($nextToken[0] === T_STRING) {
                    $functionName = trim((string) $nextToken[1]);
                    break;
                }
            }

            if ($functionName === '') {
                $hasUnsupportedCode = true;
                continue;
            }

            if (!isset($seenFunctionNames[$functionName])) {
                $seenFunctionNames[$functionName] = true;
                $functionNames[] = $functionName;
            }
            $pendingFunction = true;
            continue;
        }

        $hasUnsupportedCode = true;
    }

    return [
        'function_names' => $functionNames,
        'has_unsupported_code' => $hasUnsupportedCode,
    ];
}

/**
 * @param array{
 *     ok:bool,
 *     class_name:string,
 *     parent_class:string,
 *     class_count:int,
 *     extra_method_names:list<string>,
 *     has_top_level_function:bool,
 *     has_default_property_value:bool
 * } $bootstrapInfo
 * @param array{
 *     ok:bool,
 *     class_name:string,
 *     parent_class:string,
 *     wrapper_property_names:list<string>,
 *     generated_trailing_class_names:list<string>,
 *     has_default_property_value_outside_additional_class_definition:bool
 * } $migrationInfo
 */
function app_legacy_data_class_supports_generated_enum_wrapper_base_migration(
    array $bootstrapInfo,
    array $migrationInfo,
): bool {
    if (!(bool) ($bootstrapInfo['ok'] ?? false)) {
        return false;
    }
    if (!(bool) ($migrationInfo['ok'] ?? false)) {
        return false;
    }
    if (trim((string) ($migrationInfo['class_name'] ?? '')) === '') {
        return false;
    }
    if ((bool) ($bootstrapInfo['has_default_property_value'] ?? false)) {
        return false;
    }
    if (($bootstrapInfo['extra_method_names'] ?? []) !== []) {
        return false;
    }
    if (($migrationInfo['wrapper_property_names'] ?? []) !== []) {
        return false;
    }
    if ((bool) ($migrationInfo['has_default_property_value_outside_additional_class_definition'] ?? true)) {
        return false;
    }

    $classCount = (int) ($bootstrapInfo['class_count'] ?? 0);
    $generatedTrailingClassNames = $migrationInfo['generated_trailing_class_names'] ?? [];
    if (!is_array($generatedTrailingClassNames)) {
        return false;
    }
    if ($generatedTrailingClassNames === []) {
        return false;
    }
    foreach ($generatedTrailingClassNames as $className) {
        if (!is_string($className) || preg_match('/Enum$/', $className) !== 1) {
            return false;
        }
    }

    return $classCount === (1 + count($generatedTrailingClassNames));
}

/**
 * @param array{
 *     ok:bool,
 *     class_count:int,
 *     extra_method_names:list<string>,
 *     has_top_level_function:bool,
 *     has_default_property_value:bool
 * } $bootstrapInfo
 * @param array{
 *     ok:bool,
 *     class_name:string,
 *     wrapper_property_names:list<string>,
 *     has_default_property_value_outside_additional_class_definition:bool
 * } $migrationInfo
 */
function app_legacy_data_class_supports_default_property_wrapper_base_migration(
    array $bootstrapInfo,
    array $migrationInfo,
): bool {
    if (!(bool) ($bootstrapInfo['ok'] ?? false)) {
        return false;
    }
    if ((int) ($bootstrapInfo['class_count'] ?? 0) !== 1) {
        return false;
    }
    if (($bootstrapInfo['extra_method_names'] ?? []) !== []) {
        return false;
    }
    if ((bool) ($bootstrapInfo['has_top_level_function'] ?? false)) {
        return false;
    }
    if (!(bool) ($bootstrapInfo['has_default_property_value'] ?? false)) {
        return false;
    }
    if (!(bool) ($migrationInfo['ok'] ?? false)) {
        return false;
    }
    if (trim((string) ($migrationInfo['class_name'] ?? '')) === '') {
        return false;
    }
    if (($migrationInfo['wrapper_property_names'] ?? []) === []) {
        return false;
    }

    return !(bool) ($migrationInfo['has_default_property_value_outside_additional_class_definition'] ?? true);
}

/**
 * @param array{
 *     ok:bool,
 *     class_count:int,
 *     extra_method_names:list<string>,
 *     has_top_level_function:bool,
 *     has_default_property_value:bool
 * } $bootstrapInfo
 * @param array{
 *     ok:bool,
 *     class_name:string,
 *     wrapper_property_names:list<string>,
 *     wrapper_method_names:list<string>,
 *     generated_trailing_class_names:list<string>,
 *     additional_class_definition_has_non_method_code:bool,
 *     has_default_property_value_outside_additional_class_definition:bool
 * } $migrationInfo
 */
function app_legacy_data_class_supports_method_only_wrapper_base_migration(
    array $bootstrapInfo,
    array $migrationInfo,
): bool {
    if (!(bool) ($bootstrapInfo['ok'] ?? false)) {
        return false;
    }
    if ((int) ($bootstrapInfo['class_count'] ?? 0) !== 1) {
        return false;
    }
    if ((bool) ($bootstrapInfo['has_top_level_function'] ?? false)) {
        return false;
    }
    if ((bool) ($bootstrapInfo['has_default_property_value'] ?? false)) {
        return false;
    }
    if (!(bool) ($migrationInfo['ok'] ?? false)) {
        return false;
    }
    if (trim((string) ($migrationInfo['class_name'] ?? '')) === '') {
        return false;
    }
    $extraMethodNames = $bootstrapInfo['extra_method_names'] ?? [];
    if (!is_array($extraMethodNames) || $extraMethodNames === []) {
        return false;
    }
    if (($migrationInfo['wrapper_property_names'] ?? []) !== []) {
        return false;
    }
    if (($migrationInfo['generated_trailing_class_names'] ?? []) !== []) {
        return false;
    }
    if ((bool) ($migrationInfo['additional_class_definition_has_non_method_code'] ?? true)) {
        return false;
    }
    if ((bool) ($migrationInfo['has_default_property_value_outside_additional_class_definition'] ?? true)) {
        return false;
    }

    return ($migrationInfo['wrapper_method_names'] ?? []) === $extraMethodNames;
}

/**
 * @param array{
 *     ok:bool,
 *     class_count:int,
 *     extra_method_names:list<string>,
 *     has_top_level_function:bool,
 *     has_default_property_value:bool
 * } $bootstrapInfo
 * @param array{
 *     ok:bool,
 *     class_name:string,
 *     wrapper_property_names:list<string>,
 *     wrapper_method_names:list<string>,
 *     generated_trailing_class_names:list<string>,
 *     additional_class_definition_has_unsupported_code:bool,
 *     has_default_property_value_outside_additional_class_definition:bool
 * } $migrationInfo
 */
function app_legacy_data_class_supports_wrapper_property_method_wrapper_base_migration(
    array $bootstrapInfo,
    array $migrationInfo,
): bool {
    if (!(bool) ($bootstrapInfo['ok'] ?? false)) {
        return false;
    }
    if ((int) ($bootstrapInfo['class_count'] ?? 0) !== 1) {
        return false;
    }
    if ((bool) ($bootstrapInfo['has_top_level_function'] ?? false)) {
        return false;
    }
    if ((bool) ($bootstrapInfo['has_default_property_value'] ?? false)) {
        return false;
    }
    if (!(bool) ($migrationInfo['ok'] ?? false)) {
        return false;
    }
    if (trim((string) ($migrationInfo['class_name'] ?? '')) === '') {
        return false;
    }
    $extraMethodNames = $bootstrapInfo['extra_method_names'] ?? [];
    if (!is_array($extraMethodNames) || $extraMethodNames === []) {
        return false;
    }
    if (($migrationInfo['wrapper_property_names'] ?? []) === []) {
        return false;
    }
    if (($migrationInfo['generated_trailing_class_names'] ?? []) !== []) {
        return false;
    }
    if ((bool) ($migrationInfo['additional_class_definition_has_unsupported_code'] ?? true)) {
        return false;
    }
    if ((bool) ($migrationInfo['has_default_property_value_outside_additional_class_definition'] ?? true)) {
        return false;
    }

    return ($migrationInfo['wrapper_method_names'] ?? []) === $extraMethodNames;
}

/**
 * @param array{
 *     ok:bool,
 *     class_count:int,
 *     extra_method_names:list<string>,
 *     has_top_level_function:bool,
 *     has_default_property_value:bool
 * } $bootstrapInfo
 * @param array{
 *     ok:bool,
 *     class_name:string,
 *     wrapper_property_names:list<string>,
 *     wrapper_method_names:list<string>,
 *     generated_trailing_class_names:list<string>,
 *     editable_areas:array{
 *         above:string,
 *         additional_class_definition:string,
 *         bottom:string
 *     },
 *     additional_class_definition_has_non_method_code:bool,
 *     has_default_property_value_outside_additional_class_definition:bool
 * } $migrationInfo
 */
function app_legacy_data_class_supports_method_and_enum_wrapper_base_migration(
    array $bootstrapInfo,
    array $migrationInfo,
): bool {
    if (!(bool) ($bootstrapInfo['ok'] ?? false)) {
        return false;
    }
    if ((bool) ($bootstrapInfo['has_default_property_value'] ?? false)) {
        return false;
    }
    if (!(bool) ($migrationInfo['ok'] ?? false)) {
        return false;
    }
    if (trim((string) ($migrationInfo['class_name'] ?? '')) === '') {
        return false;
    }

    $extraMethodNames = $bootstrapInfo['extra_method_names'] ?? [];
    if (!is_array($extraMethodNames) || $extraMethodNames === []) {
        return false;
    }
    if (($migrationInfo['wrapper_property_names'] ?? []) !== []) {
        return false;
    }
    if ((bool) ($migrationInfo['additional_class_definition_has_non_method_code'] ?? true)) {
        return false;
    }
    if ((bool) ($migrationInfo['has_default_property_value_outside_additional_class_definition'] ?? true)) {
        return false;
    }

    $generatedTrailingClassNames = $migrationInfo['generated_trailing_class_names'] ?? [];
    if (!is_array($generatedTrailingClassNames) || $generatedTrailingClassNames === []) {
        return false;
    }
    foreach ($generatedTrailingClassNames as $className) {
        if (!is_string($className) || preg_match('/Enum$/', $className) !== 1) {
            return false;
        }
    }

    $bottomClassNames = app_legacy_data_class_class_names_from_section(
        (string) (($migrationInfo['editable_areas']['bottom'] ?? '') ?: ''),
    );
    if ($bottomClassNames !== []) {
        return false;
    }

    return (int) ($bootstrapInfo['class_count'] ?? 0) === (1 + count($generatedTrailingClassNames))
        && ($migrationInfo['wrapper_method_names'] ?? []) === $extraMethodNames;
}

/**
 * @param array{
 *     ok:bool,
 *     class_count:int,
 *     extra_method_names:list<string>,
 *     has_top_level_function:bool,
 *     has_default_property_value:bool
 * } $bootstrapInfo
 * @param array{
 *     ok:bool,
 *     class_name:string,
 *     wrapper_property_names:list<string>,
 *     wrapper_method_names:list<string>,
 *     generated_trailing_class_names:list<string>,
 *     generated_bottom_class_names:list<string>,
 *     generated_wrapper_bottom_section:string,
 *     additional_class_definition_has_non_method_code:bool,
 *     bottom_has_unsupported_code:bool,
 *     has_default_property_value_outside_additional_class_definition:bool
 * } $migrationInfo
 */
function app_legacy_data_class_supports_top_level_declaration_wrapper_base_migration(
    array $bootstrapInfo,
    array $migrationInfo,
): bool {
    if (!(bool) ($bootstrapInfo['ok'] ?? false)) {
        return false;
    }
    if ((bool) ($bootstrapInfo['has_default_property_value'] ?? false)) {
        return false;
    }
    if (!(bool) ($migrationInfo['ok'] ?? false)) {
        return false;
    }
    if (trim((string) ($migrationInfo['class_name'] ?? '')) === '') {
        return false;
    }
    if (($migrationInfo['wrapper_property_names'] ?? []) !== []) {
        return false;
    }
    if ((bool) ($migrationInfo['additional_class_definition_has_non_method_code'] ?? true)) {
        return false;
    }
    if ((bool) ($migrationInfo['bottom_has_unsupported_code'] ?? true)) {
        return false;
    }
    if ((bool) ($migrationInfo['has_default_property_value_outside_additional_class_definition'] ?? true)) {
        return false;
    }

    $extraMethodNames = $bootstrapInfo['extra_method_names'] ?? [];
    if (!is_array($extraMethodNames)) {
        return false;
    }
    if (($migrationInfo['wrapper_method_names'] ?? []) !== $extraMethodNames) {
        return false;
    }

    $generatedBottomClassNames = $migrationInfo['generated_bottom_class_names'] ?? [];
    $generatedTrailingClassNames = $migrationInfo['generated_trailing_class_names'] ?? [];
    if (!is_array($generatedBottomClassNames) || !is_array($generatedTrailingClassNames)) {
        return false;
    }

    $hasBaseDeclarations = $generatedBottomClassNames !== [] || $generatedTrailingClassNames !== [];
    $hasWrapperBottomDeclarations = trim((string) ($migrationInfo['generated_wrapper_bottom_section'] ?? '')) !== '';
    if ($extraMethodNames === [] && !$hasBaseDeclarations && !$hasWrapperBottomDeclarations) {
        return false;
    }

    $wrapperBottomFunctionAnalysis = app_legacy_data_class_top_level_function_section_analysis(
        (string) ($migrationInfo['generated_wrapper_bottom_section'] ?? ''),
    );
    if ((bool) ($wrapperBottomFunctionAnalysis['has_unsupported_code'] ?? true)) {
        return false;
    }
    if ((bool) ($bootstrapInfo['has_top_level_function'] ?? false) !== (($wrapperBottomFunctionAnalysis['function_names'] ?? []) !== [])) {
        return false;
    }

    return (int) ($bootstrapInfo['class_count'] ?? 0) === (
        1
        + count($generatedBottomClassNames)
        + count($generatedTrailingClassNames)
    );
}

/**
 * @return array{
 *     ok:bool,
 *     class_name:string,
 *     parent_class:string,
 *     all_property_names:list<string>,
 *     generated_property_names:list<string>,
 *     wrapper_property_names:list<string>,
 *     wrapper_method_names:list<string>,
 *     generated_trailing_section:string,
 *     generated_trailing_class_names:list<string>,
 *     editable_areas:array{
 *         above:string,
 *         additional_class_definition:string,
 *         bottom:string
 *     },
 *     generated_base_additional_section:string,
 *     generated_bottom_class_names:list<string>,
 *     generated_wrapper_bottom_section:string,
 *     additional_class_definition_has_non_method_code:bool,
 *     additional_class_definition_has_unsupported_code:bool,
 *     bottom_has_unsupported_code:bool,
 *     has_default_property_value_outside_additional_class_definition:bool,
 *     error:string
 * }
 */
function app_legacy_data_class_migration_info(string $filePath): array
{
    $contents = file_get_contents($filePath);
    if (!is_string($contents) || $contents === '') {
        return [
            'ok' => false,
            'class_name' => '',
            'parent_class' => '',
            'all_property_names' => [],
            'generated_property_names' => [],
            'wrapper_property_names' => [],
            'wrapper_method_names' => [],
            'generated_trailing_section' => '',
            'generated_trailing_class_names' => [],
            'editable_areas' => [
                'above' => '',
                'additional_class_definition' => '',
                'bottom' => '',
            ],
            'generated_base_additional_section' => '',
            'generated_bottom_class_names' => [],
            'generated_wrapper_bottom_section' => '',
            'additional_class_definition_has_non_method_code' => false,
            'additional_class_definition_has_unsupported_code' => false,
            'bottom_has_unsupported_code' => false,
            'has_default_property_value_outside_additional_class_definition' => false,
            'error' => 'legacy data class file の読み込みに失敗しました。',
        ];
    }

    $contents = app_legacy_data_class_normalize_newlines($contents);

    $classMatches = [];
    if (!preg_match('/^class\s+([A-Za-z0-9_]+)(?:\s+extends\s+([A-Za-z0-9_]+))?/m', $contents, $classMatches)) {
        return [
            'ok' => false,
            'class_name' => '',
            'parent_class' => '',
            'all_property_names' => [],
            'generated_property_names' => [],
            'wrapper_property_names' => [],
            'wrapper_method_names' => [],
            'generated_trailing_section' => '',
            'generated_trailing_class_names' => [],
            'editable_areas' => [
                'above' => '',
                'additional_class_definition' => '',
                'bottom' => '',
            ],
            'generated_base_additional_section' => '',
            'generated_bottom_class_names' => [],
            'generated_wrapper_bottom_section' => '',
            'additional_class_definition_has_non_method_code' => false,
            'additional_class_definition_has_unsupported_code' => false,
            'bottom_has_unsupported_code' => false,
            'has_default_property_value_outside_additional_class_definition' => false,
            'error' => 'legacy data class 定義を解析できません。',
        ];
    }

    $areas = [];
    foreach (
        [
            'above' => 'ABOVE',
            'additional_class_definition' => 'ADDITIONAL CLASS DEFINITION',
            'bottom' => 'BOTTOM',
        ] as $areaKey => $legacyAreaName
    ) {
        $areaResult = app_legacy_data_class_extract_editable_area($contents, $legacyAreaName);
        if (!$areaResult['ok']) {
            return [
                'ok' => false,
                'class_name' => '',
                'parent_class' => '',
                'all_property_names' => [],
                'generated_property_names' => [],
                'wrapper_property_names' => [],
                'wrapper_method_names' => [],
                'generated_trailing_section' => '',
                'generated_trailing_class_names' => [],
                'editable_areas' => [
                    'above' => '',
                    'additional_class_definition' => '',
                    'bottom' => '',
                ],
                'generated_base_additional_section' => '',
                'generated_bottom_class_names' => [],
                'generated_wrapper_bottom_section' => '',
                'additional_class_definition_has_non_method_code' => false,
                'additional_class_definition_has_unsupported_code' => false,
                'bottom_has_unsupported_code' => false,
                'has_default_property_value_outside_additional_class_definition' => false,
                'error' => $areaResult['error'],
            ];
        }

        $areas[$areaKey] = $areaResult;
    }

    $allPropertyNames = app_legacy_data_class_primary_class_property_names_from_string($contents);
    $wrapperPropertyNames = app_legacy_data_class_property_names_from_string(
        $areas['additional_class_definition']['content'],
    );
    $wrapperMethodAnalysis = app_legacy_data_class_additional_class_definition_analysis(
        $areas['additional_class_definition']['content'],
    );
    $wrapperPropertySet = array_fill_keys($wrapperPropertyNames, true);
    $generatedPropertyNames = [];
    foreach ($allPropertyNames as $propertyName) {
        if (isset($wrapperPropertySet[$propertyName])) {
            continue;
        }

        $generatedPropertyNames[] = $propertyName;
    }

    $contentsWithoutAdditionalClassDefinition = str_replace(
        $areas['additional_class_definition']['full_match'],
        '',
        $contents,
    );
    $hasDefaultPropertyValueOutsideAdditionalClassDefinition = (
        preg_match(
            '/^\s*public\s+\$[A-Za-z0-9_]+\s*=/m',
            $contentsWithoutAdditionalClassDefinition,
        ) === 1
    );
    $generatedTrailingSection = app_legacy_data_class_generated_trailing_section(
        $contents,
        $areas['bottom'],
    );
    $generatedTrailingClassNames = app_legacy_data_class_class_names_from_section(
        $generatedTrailingSection,
    );
    $bottomClassSplit = app_legacy_data_class_split_top_level_class_declarations(
        $areas['bottom']['content'],
    );
    if ($bottomClassSplit['has_unclosed_class']) {
        return [
            'ok' => false,
            'class_name' => '',
            'parent_class' => '',
            'all_property_names' => [],
            'generated_property_names' => [],
            'wrapper_property_names' => [],
            'wrapper_method_names' => [],
            'generated_trailing_section' => '',
            'generated_trailing_class_names' => [],
            'editable_areas' => [
                'above' => '',
                'additional_class_definition' => '',
                'bottom' => '',
            ],
            'generated_base_additional_section' => '',
            'generated_bottom_class_names' => [],
            'generated_wrapper_bottom_section' => '',
            'additional_class_definition_has_non_method_code' => false,
            'additional_class_definition_has_unsupported_code' => false,
            'bottom_has_unsupported_code' => true,
            'has_default_property_value_outside_additional_class_definition' => false,
            'error' => 'legacy data class bottom class 定義を解析できません。',
        ];
    }
    $bottomTopLevelFunctionAnalysis = app_legacy_data_class_top_level_function_section_analysis(
        $bottomClassSplit['remainder_section'],
    );
    $generatedBaseAdditionalSection = app_legacy_data_class_join_sections(
        [
            $bottomClassSplit['class_section'],
            $generatedTrailingSection,
        ],
    );

    return [
        'ok' => true,
        'class_name' => (string) $classMatches[1],
        'parent_class' => isset($classMatches[2]) && is_string($classMatches[2]) ? $classMatches[2] : '',
        'all_property_names' => $allPropertyNames,
        'generated_property_names' => $generatedPropertyNames,
        'wrapper_property_names' => $wrapperPropertyNames,
        'wrapper_method_names' => $wrapperMethodAnalysis['method_names'],
        'generated_trailing_section' => $generatedTrailingSection,
        'generated_trailing_class_names' => $generatedTrailingClassNames,
        'editable_areas' => [
            'above' => $areas['above']['content'],
            'additional_class_definition' => $areas['additional_class_definition']['content'],
            'bottom' => $areas['bottom']['content'],
        ],
        'generated_base_additional_section' => $generatedBaseAdditionalSection,
        'generated_bottom_class_names' => $bottomClassSplit['class_names'],
        'generated_wrapper_bottom_section' => $bottomClassSplit['remainder_section'],
        'additional_class_definition_has_non_method_code' => $wrapperMethodAnalysis['has_non_method_code'],
        'additional_class_definition_has_unsupported_code' => $wrapperMethodAnalysis['has_unsupported_code'],
        'bottom_has_unsupported_code' => $bottomTopLevelFunctionAnalysis['has_unsupported_code'],
        'has_default_property_value_outside_additional_class_definition'
            => $hasDefaultPropertyValueOutsideAdditionalClassDefinition,
        'error' => '',
    ];
}
