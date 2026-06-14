<?php

declare(strict_types=1);

require_once __DIR__ . '/generated_catalog.php';
require_once __DIR__ . '/domain_validation.php';

function app_custom_proxy_display_name(string $basename, string $name): string
{
    $basename = trim($basename);
    $name = trim($name);

    if ($basename === '') {
        return $name;
    }

    if ($name === '') {
        return $basename;
    }

    return $basename . '::' . $name;
}

function app_custom_proxy_generated_function_catalog(array $generatedCatalog): array
{
    $items = [];

    foreach ($generatedCatalog['entities'] ?? [] as $entity) {
        if (!is_array($entity)) {
            continue;
        }

        $sourceName = (string) ($entity['source_name'] ?? '');
        $dbaccessPath = (string) ($entity['dbaccess_path'] ?? '');
        if ($sourceName === '' || $dbaccessPath === '') {
            continue;
        }

        $methods = app_generated_file_method_catalog($dbaccessPath);
        foreach ($methods as $method) {
            if (!is_array($method)) {
                continue;
            }

            $functionName = (string) ($method['name'] ?? '');
            if ($functionName === '') {
                continue;
            }

            $items[] = [
                'source_name' => $sourceName,
                'function_name' => $functionName,
                'signature' => (string) ($method['signature'] ?? ''),
                'line' => (int) ($method['line'] ?? 0),
            ];
        }
    }

    usort(
        $items,
        static function (array $left, array $right): int {
            $sourceComparison = strcmp($left['source_name'], $right['source_name']);
            if ($sourceComparison !== 0) {
                return $sourceComparison;
            }

            return strcmp($left['function_name'], $right['function_name']);
        },
    );

    return $items;
}

function app_custom_proxy_find_generated_function(
    array $generatedCatalog,
    string $sourceName,
    string $functionName,
): ?array {
    $sourceName = trim($sourceName);
    $functionName = trim($functionName);
    if ($sourceName === '' || $functionName === '') {
        return null;
    }

    $entity = app_generated_catalog_find_entity($generatedCatalog, $sourceName);
    if ($entity === null) {
        return null;
    }

    $methodCatalog = app_generated_file_method_catalog($entity['dbaccess_path']);
    $method = app_generated_file_find_method($methodCatalog, $functionName);
    if ($method === null) {
        return null;
    }

    return [
        'source_name' => $sourceName,
        'function_name' => $functionName,
        'signature' => (string) ($method['signature'] ?? ''),
        'line' => (int) ($method['line'] ?? 0),
    ];
}

function app_custom_proxy_normalize_target_source_output_keys(array $requestedKeys, array $availableSourceOutputs): array
{
    $allowed = [];
    foreach ($availableSourceOutputs as $sourceOutput) {
        if (!is_array($sourceOutput)) {
            continue;
        }

        if (!app_source_output_supports_custom_proxy_targets($sourceOutput)) {
            continue;
        }

        $sourceOutputKey = (string) ($sourceOutput['source_output_key'] ?? '');
        if ($sourceOutputKey !== '') {
            $allowed[$sourceOutputKey] = true;
        }
    }

    $normalized = [];
    foreach ($requestedKeys as $requestedKey) {
        if (!is_string($requestedKey)) {
            continue;
        }

        $key = app_normalize_source_output_key($requestedKey);
        if ($key !== '' && isset($allowed[$key])) {
            $normalized[$key] = $key;
        }
    }

    return array_values($normalized);
}
