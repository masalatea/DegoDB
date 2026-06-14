<?php

declare(strict_types=1);

require_once __DIR__ . '/project_repository_pdo.php';
require_once __DIR__ . '/project_repository_generated_bootstrap.php';

function app_project_repository_driver(array $app): string
{
    return $app['repositories']['project_driver'] ?? 'pdo';
}

function app_fetch_project_catalog(array $app): array
{
    return match (app_project_repository_driver($app)) {
        'pdo' => app_pdo_fetch_project_catalog($app),
        'legacy-dbclasses-bootstrap' => app_generated_bootstrap_fetch_project_catalog($app),
        default => [
            'ok' => false,
            'items' => [],
            'error' => '未対応の project repository driver です。',
        ],
    };
}

function app_fetch_project_by_key(array $app, string $projectKey): array
{
    return match (app_project_repository_driver($app)) {
        'pdo' => app_pdo_fetch_project_by_key($app, $projectKey),
        'legacy-dbclasses-bootstrap' => app_generated_bootstrap_fetch_project_by_key($app, $projectKey),
        default => [
            'ok' => false,
            'item' => null,
            'error' => '未対応の project repository driver です。',
        ],
    };
}

function app_insert_project(array $app, array $input): array
{
    return match (app_project_repository_driver($app)) {
        'pdo' => app_pdo_insert_project($app, $input),
        'legacy-dbclasses-bootstrap' => app_generated_bootstrap_insert_project($app, $input),
        default => [
            'ok' => false,
            'error' => '未対応の project repository driver です。',
        ],
    };
}

function app_update_project(array $app, array $input): array
{
    return match (app_project_repository_driver($app)) {
        'pdo' => app_pdo_update_project($app, $input),
        'legacy-dbclasses-bootstrap' => app_generated_bootstrap_update_project($app, $input),
        default => [
            'ok' => false,
            'error' => '未対応の project repository driver です。',
        ],
    };
}
