<?php

declare(strict_types=1);

require_once __DIR__ . '/database_source_repository_pdo.php';

function app_fetch_database_sources(array $app): array
{
    return app_pdo_fetch_database_source_catalog($app);
}

function app_fetch_database_source(array $app, int $sourceId): array
{
    return app_pdo_fetch_database_source_item($app, $sourceId);
}

function app_create_database_source(array $app, array $input): array
{
    return app_pdo_create_database_source($app, $input);
}

function app_update_database_source(array $app, int $sourceId, array $input): array
{
    return app_pdo_update_database_source($app, $sourceId, $input);
}

function app_delete_database_source(array $app, int $sourceId): array
{
    return app_pdo_delete_database_source($app, $sourceId);
}
