<?php

declare(strict_types=1);

function app_generated_bootstrap_experiment_repository_error(array $app): string
{
    $loader = $app['generated']['dbclasses_loader'] ?? '';
    $mode = $app['generated']['dbclasses_mode'] ?? '';

    return sprintf(
        'experiment repository driver=legacy-dbclasses-bootstrap はまだ利用できません。現在の lab_experiments は新 bootstrap schema であり、旧 dbclasses と直接互換ではありません。generated mode=%s, loader=%s',
        $mode,
        $loader,
    );
}

function app_generated_bootstrap_fetch_lab_experiment_catalog(array $app): array
{
    return [
        'ok' => false,
        'items' => [],
        'error' => app_generated_bootstrap_experiment_repository_error($app),
    ];
}

function app_generated_bootstrap_fetch_lab_experiment_by_key(array $app, string $experimentKey): array
{
    return [
        'ok' => false,
        'item' => null,
        'error' => app_generated_bootstrap_experiment_repository_error($app),
    ];
}

function app_generated_bootstrap_insert_lab_experiment(array $app, array $input): array
{
    return [
        'ok' => false,
        'error' => app_generated_bootstrap_experiment_repository_error($app),
    ];
}

function app_generated_bootstrap_update_lab_experiment(array $app, array $input): array
{
    return [
        'ok' => false,
        'error' => app_generated_bootstrap_experiment_repository_error($app),
    ];
}
