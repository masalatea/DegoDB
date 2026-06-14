<?php

declare(strict_types=1);

require_once __DIR__ . '/runtime_storage_paths.php';

/**
 * @return array<string,list<string>>
 */
function app_sample_pack_category_map(): array
{
    return [
        'tutorials' => [
            'sample01-simple-table-runtime',
            'sample02-dataclass-nullable-default-status',
            'sample03-dataclass-lookup-and-helper',
            'sample04-dataclass-parent-child-basic',
            'sample05-dbaccess-select-basic',
            'sample06-dbaccess-filter-sort-page',
            'sample07-dbaccess-crud-basic',
            'sample08-dbaccess-join-read-model',
            'sample09-dbaccess-aggregate-report',
            'sample10-dbaccess-mini-crud-flow',
        ],
        'internal-patterns' => [
            'pattern01-default-property-split',
            'pattern02-wrapper-property-helper',
            'pattern03-method-only-split',
            'pattern04-method-and-enum-basic',
            'pattern05-companion-declarations-basic',
            'pattern06-companion-declarations-no-top-level',
            'pattern07-companion-declarations-multiclass',
            'pattern08-companion-declarations-multi-helper',
            'pattern09-top-level-declaration-single',
            'pattern10-top-level-declaration-multiclass',
            'pattern11-top-level-declaration-html-template',
            'pattern12-method-and-enum-no-top-level',
            'pattern13-method-and-enum-multimethod',
            'pattern14-method-and-enum-heavy-multimethod',
        ],
        'legacy-projects' => [
            'sample51-runtime-sql-server',
            'sample53-runtime-whiteboard',
            'sample56-runtime-misc-proxy',
        ],
    ];
}

function app_sample_pack_root(): string
{
    return dirname(__DIR__, 2) . '/sample';
}

function app_sample_pack_support_root(): string
{
    return app_sample_pack_root() . '/_pack-support';
}

function app_sample_pack_runner_path(): string
{
    return app_sample_pack_support_root() . '/sample-pack-runner.sh';
}

function app_sample_pack_archive_root(): string
{
    return app_sample_pack_root() . '/archive';
}

function app_sample_pack_old_root(): string
{
    return app_sample_pack_archive_root();
}

/**
 * @return list<string>
 */
function app_sample_pack_runtime_pack_names(): array
{
    return [
        'sample01-simple-table-runtime',
        'sample02-dataclass-nullable-default-status',
        'sample03-dataclass-lookup-and-helper',
        'sample04-dataclass-parent-child-basic',
        'sample05-dbaccess-select-basic',
        'sample06-dbaccess-filter-sort-page',
        'sample07-dbaccess-crud-basic',
        'sample08-dbaccess-join-read-model',
        'sample09-dbaccess-aggregate-report',
        'sample10-dbaccess-mini-crud-flow',
        'sample51-runtime-sql-server',
        'sample53-runtime-whiteboard',
        'sample56-runtime-misc-proxy',
    ];
}

/**
 * @return array<string,string>
 */
function app_sample_pack_project_key_map(): array
{
    return [
        'sample01-simple-table-runtime' => 'SAMPLE1',
        'sample02-dataclass-nullable-default-status' => 'SAMPLE02',
        'sample03-dataclass-lookup-and-helper' => 'SAMPLE03',
        'sample04-dataclass-parent-child-basic' => 'SAMPLE04',
        'sample05-dbaccess-select-basic' => 'SAMPLE05',
        'sample06-dbaccess-filter-sort-page' => 'SAMPLE06',
        'sample07-dbaccess-crud-basic' => 'SAMPLE07',
        'sample08-dbaccess-join-read-model' => 'SAMPLE08',
        'sample09-dbaccess-aggregate-report' => 'SAMPLE09',
        'sample10-dbaccess-mini-crud-flow' => 'SAMPLE10',
        'sample51-runtime-sql-server' => 'SAMPLE2',
        'sample53-runtime-whiteboard' => 'SAMPLE4',
        'sample56-runtime-misc-proxy' => 'SAMPLE8',
    ];
}

function app_sample_pack_project_key(string $packName): string
{
    $map = app_sample_pack_project_key_map();

    return $map[$packName] ?? '';
}

/**
 * @return list<string>
 */
function app_sample_pack_reference_only_sample_names(): array
{
    return [
        'pattern01-default-property-split',
        'pattern02-wrapper-property-helper',
        'pattern03-method-only-split',
        'pattern04-method-and-enum-basic',
        'pattern05-companion-declarations-basic',
        'pattern06-companion-declarations-no-top-level',
        'pattern07-companion-declarations-multiclass',
        'pattern08-companion-declarations-multi-helper',
        'pattern09-top-level-declaration-single',
        'pattern10-top-level-declaration-multiclass',
        'pattern11-top-level-declaration-html-template',
        'pattern12-method-and-enum-no-top-level',
        'pattern13-method-and-enum-multimethod',
        'pattern14-method-and-enum-heavy-multimethod',
    ];
}

function app_sample_pack_structure_type(string $packName): string
{
    if (in_array($packName, app_sample_pack_runtime_pack_names(), true)) {
        return 'runtime-pack';
    }

    if (in_array($packName, app_sample_pack_reference_only_sample_names(), true)) {
        return 'file-reference-sample';
    }

    return '';
}

/**
 * @return array<string,string>
 */
function app_sample_pack_active_relative_path_map(): array
{
    $map = [];

    foreach (app_sample_pack_category_map() as $category => $packNames) {
        foreach ($packNames as $packName) {
            $map[$packName] = $category . '/' . $packName;
        }
    }

    return $map;
}

function app_sample_pack_relative_path(string $packName): string
{
    $map = app_sample_pack_active_relative_path_map();

    return $map[$packName] ?? '';
}

function app_sample_pack_absolute_path(string $packName): string
{
    $relativePath = app_sample_pack_relative_path($packName);
    if ($relativePath === '') {
        return '';
    }

    return app_sample_pack_root() . '/' . $relativePath;
}

function app_sample_pack_seed_root(string $packName): string
{
    $packRoot = app_sample_pack_absolute_path($packName);
    if ($packRoot === '') {
        return '';
    }

    return $packRoot . '/seed';
}

function app_sample_pack_reference_root(string $packName): string
{
    $packRoot = app_sample_pack_absolute_path($packName);
    if ($packRoot === '') {
        return '';
    }

    return $packRoot . '/reference';
}

/**
 * @return array<string,list<string>>
 */
function app_sample_pack_fixture_relative_path_map(): array
{
    return [
        'pattern01-default-property-split' => [
            'data-TestPattern.php',
        ],
        'pattern02-wrapper-property-helper' => [
            'data-dbtablecolumns.php',
        ],
        'pattern03-method-only-split' => [
            'data-da.php',
            'data-dataclass.php',
        ],
        'pattern04-method-and-enum-basic' => [
            'data-Req.php',
        ],
        'pattern05-companion-declarations-basic' => [
            'data-CompareOutput.php',
        ],
        'pattern06-companion-declarations-no-top-level' => [
            'data-BuildLog.php',
        ],
        'pattern07-companion-declarations-multiclass' => [
            'data-LiveCheckResult.php',
        ],
        'pattern08-companion-declarations-multi-helper' => [
            'data-BuildSourceFuncCache.php',
        ],
        'pattern09-top-level-declaration-single' => [
            'data-SpecContent.php',
        ],
        'pattern10-top-level-declaration-multiclass' => [
            'data-ProjectUser.php',
        ],
        'pattern11-top-level-declaration-html-template' => [
            'data-htmlTemplate.php',
        ],
        'pattern12-method-and-enum-no-top-level' => [
            'data-daCustomProxy.php',
        ],
        'pattern13-method-and-enum-multimethod' => [
            'data-Project.php',
        ],
        'pattern14-method-and-enum-heavy-multimethod' => [
            'data-ProjectSourceOutput.php',
        ],
    ];
}

/**
 * @return list<string>
 */
function app_sample_pack_fixture_relative_paths(string $packName): array
{
    $map = app_sample_pack_fixture_relative_path_map();

    return $map[$packName] ?? [];
}

/**
 * @return list<string>
 */
function app_sample_pack_fixture_absolute_paths(string $packName): array
{
    $paths = [];

    foreach (app_sample_pack_fixture_relative_paths($packName) as $relativePath) {
        $paths[] = app_runtime_storage_legacy_dbclasses_fixture_path($relativePath);
    }

    return $paths;
}

function app_sample_pack_resource_root(string $packName): string
{
    $packRoot = app_sample_pack_absolute_path($packName);
    if ($packRoot === '') {
        return '';
    }

    return $packRoot . '/resources';
}
