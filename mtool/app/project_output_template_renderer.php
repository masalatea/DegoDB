<?php

declare(strict_types=1);

/**
 * @return list<string>
 */
function app_project_output_template_relative_segments(string $relativePath): array
{
    $normalizedPath = trim(str_replace('\\', '/', $relativePath), '/');
    if ($normalizedPath === '' || $normalizedPath === '.') {
        return [];
    }

    $segments = array_values(
        array_filter(
            explode('/', $normalizedPath),
            static fn ($segment): bool => is_string($segment) && $segment !== '' && $segment !== '.',
        ),
    );

    foreach ($segments as $segment) {
        if ($segment === '..') {
            throw new RuntimeException('template relative path must not contain "..": ' . $relativePath);
        }
    }

    return $segments;
}

function app_project_output_reference_template_root(): string
{
    return dirname(__DIR__) . '/reference/source-templates';
}

function app_project_output_reference_template_path(string $relativePath): string
{
    $segments = app_project_output_template_relative_segments($relativePath);
    if ($segments === []) {
        throw new RuntimeException('template relative path is empty.');
    }

    return app_project_output_reference_template_root() . '/' . implode('/', $segments);
}

/**
 * @return list<string>
 */
function app_project_output_reference_template_placeholders(string $templateContents): array
{
    $matches = [];
    $result = preg_match_all('/{{([A-Z0-9_]+)}}/', $templateContents, $matches);
    if ($result === false || !isset($matches[1]) || !is_array($matches[1])) {
        return [];
    }

    return array_values(
        array_unique(
            array_map(
                static fn ($placeholder): string => (string) $placeholder,
                $matches[1],
            ),
        ),
    );
}

/**
 * @param array<string,string> $parameters
 */
function app_project_output_render_reference_template(string $relativePath, array $parameters): string
{
    $templatePath = app_project_output_reference_template_path($relativePath);
    if (!is_file($templatePath)) {
        throw new RuntimeException('template file does not exist: ' . $relativePath);
    }

    $templateContents = file_get_contents($templatePath);
    if (!is_string($templateContents)) {
        throw new RuntimeException('failed to load template file: ' . $relativePath);
    }

    foreach (app_project_output_reference_template_placeholders($templateContents) as $placeholder) {
        if (!array_key_exists($placeholder, $parameters)) {
            throw new RuntimeException(
                'template placeholder is missing: '
                . $relativePath
                . ' -> '
                . $placeholder
            );
        }
    }

    $replace = [];
    foreach ($parameters as $name => $value) {
        $replace['{{' . $name . '}}'] = $value;
    }

    return strtr($templateContents, $replace);
}
