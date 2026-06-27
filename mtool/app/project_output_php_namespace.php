<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/project_repository.php';

function app_project_output_php_namespace_from_project(array $app, string $projectKey): string
{
    $projectResult = app_fetch_project_by_key($app, $projectKey);
    if (!$projectResult['ok'] || !is_array($projectResult['item'])) {
        return '';
    }

    $namespace = app_normalize_php_namespace((string) ($projectResult['item']['php_namespace'] ?? ''));

    return app_php_namespace_is_valid($namespace) ? $namespace : '';
}

function app_project_output_php_namespace_section(string $namespace): string
{
    $normalized = app_normalize_php_namespace($namespace);
    if ($normalized === '') {
        return '';
    }
    if (!app_php_namespace_is_valid($normalized)) {
        throw new InvalidArgumentException('PHP namespace の形式が不正です。');
    }

    return 'namespace ' . $normalized . ";\n\n";
}

