<?php

declare(strict_types=1);

function app_generated_bootstrap_project_repository_error(array $app): string
{
    $loader = $app['generated']['dbclasses_loader'] ?? '';
    $mode = $app['generated']['dbclasses_mode'] ?? '';

    return sprintf(
        'project repository driver=legacy-dbclasses-bootstrap はまだ利用できません。現在の projects / project_memberships は新 bootstrap schema であり、旧 dbclasses と直接互換ではありません。generated mode=%s, loader=%s',
        $mode,
        $loader,
    );
}

function app_generated_bootstrap_fetch_project_catalog(array $app): array
{
    return [
        'ok' => false,
        'items' => [],
        'error' => app_generated_bootstrap_project_repository_error($app),
    ];
}

function app_generated_bootstrap_fetch_project_by_key(array $app, string $projectKey): array
{
    return [
        'ok' => false,
        'item' => null,
        'error' => app_generated_bootstrap_project_repository_error($app),
    ];
}

function app_generated_bootstrap_insert_project(array $app, array $input): array
{
    return [
        'ok' => false,
        'error' => app_generated_bootstrap_project_repository_error($app),
    ];
}

function app_generated_bootstrap_update_project(array $app, array $input): array
{
    return [
        'ok' => false,
        'error' => app_generated_bootstrap_project_repository_error($app),
    ];
}
