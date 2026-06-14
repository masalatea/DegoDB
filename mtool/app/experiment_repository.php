<?php

declare(strict_types=1);

require_once __DIR__ . '/experiment_repository_pdo.php';
require_once __DIR__ . '/experiment_repository_generated_bootstrap.php';

function app_experiment_repository_driver(array $app): string
{
    return $app['repositories']['experiment_driver'] ?? 'pdo';
}

function app_fetch_lab_experiment_catalog(array $app): array
{
    return match (app_experiment_repository_driver($app)) {
        'pdo' => app_pdo_fetch_lab_experiment_catalog($app),
        'legacy-dbclasses-bootstrap' => app_generated_bootstrap_fetch_lab_experiment_catalog($app),
        default => [
            'ok' => false,
            'items' => [],
            'error' => '未対応の experiment repository driver です。',
        ],
    };
}

function app_fetch_lab_experiment_by_key(array $app, string $experimentKey): array
{
    return match (app_experiment_repository_driver($app)) {
        'pdo' => app_pdo_fetch_lab_experiment_by_key($app, $experimentKey),
        'legacy-dbclasses-bootstrap' => app_generated_bootstrap_fetch_lab_experiment_by_key($app, $experimentKey),
        default => [
            'ok' => false,
            'item' => null,
            'error' => '未対応の experiment repository driver です。',
        ],
    };
}

function app_insert_lab_experiment(array $app, array $input): array
{
    return match (app_experiment_repository_driver($app)) {
        'pdo' => app_pdo_insert_lab_experiment($app, $input),
        'legacy-dbclasses-bootstrap' => app_generated_bootstrap_insert_lab_experiment($app, $input),
        default => [
            'ok' => false,
            'error' => '未対応の experiment repository driver です。',
        ],
    };
}

function app_update_lab_experiment(array $app, array $input): array
{
    return match (app_experiment_repository_driver($app)) {
        'pdo' => app_pdo_update_lab_experiment($app, $input),
        'legacy-dbclasses-bootstrap' => app_generated_bootstrap_update_lab_experiment($app, $input),
        default => [
            'ok' => false,
            'error' => '未対応の experiment repository driver です。',
        ],
    };
}
