<?php

declare(strict_types=1);

/**
 * @param array{
 *     generated:array{
 *         root:string,
 *         dbclasses_root:string,
 *         dbclasses_loader:string,
 *         dbclasses_mode:string
 *     },
 *     repositories:array{
 *         project_driver:string,
 *         experiment_driver:string
 *     }
 * } $app
 * @return array{
 *     root:string,
 *     dbclasses_root:string,
 *     dbclasses_loader:string,
 *     dbclasses_mode:string,
 *     root_exists:bool,
 *     dbclasses_root_exists:bool,
 *     dbclasses_loader_exists:bool,
 *     data_file_count:int,
 *     dbaccess_file_count:int,
 *     total_file_count:int,
 *     index_file_exists:bool,
 *     project_repository_driver:string,
 *     experiment_repository_driver:string
 * }
 */
function app_generated_runtime_summary(array $app): array
{
    $root = $app['generated']['root'];
    $dbclassesRoot = $app['generated']['dbclasses_root'];
    $dbclassesLoader = $app['generated']['dbclasses_loader'];

    $dataFiles = glob($dbclassesRoot . '/data-*.php');
    $dbaccessFiles = glob($dbclassesRoot . '/dbaccess-*.php');
    $allFiles = glob($dbclassesRoot . '/*');

    return [
        'root' => $root,
        'dbclasses_root' => $dbclassesRoot,
        'dbclasses_loader' => $dbclassesLoader,
        'dbclasses_mode' => $app['generated']['dbclasses_mode'],
        'root_exists' => is_dir($root),
        'dbclasses_root_exists' => is_dir($dbclassesRoot),
        'dbclasses_loader_exists' => is_file($dbclassesLoader),
        'data_file_count' => is_array($dataFiles) ? count($dataFiles) : 0,
        'dbaccess_file_count' => is_array($dbaccessFiles) ? count($dbaccessFiles) : 0,
        'total_file_count' => is_array($allFiles) ? count($allFiles) : 0,
        'index_file_exists' => is_file($dbclassesRoot . '/index.html'),
        'project_repository_driver' => $app['repositories']['project_driver'] ?? 'pdo',
        'experiment_repository_driver' => $app['repositories']['experiment_driver'] ?? 'pdo',
    ];
}
