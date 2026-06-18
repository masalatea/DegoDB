<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/config_db_bootstrap.php';

/**
 * @return array{
 *     site:string,
 *     site_name:string,
 *     site_role_summary:string,
 *     session:array{
 *         name:string
 *     },
 *     auth:array{
 *         mode:string,
 *         stub:array{
 *             username:string,
 *             password:string,
 *             display_name:string,
 *             roles:list<string>
 *         }
 *     },
 *     db:array{
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     },
 *     config_db:array{
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     },
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
 * }
 */
function app_bootstrap(): array
{
    $app = app_load_config();
    app_bootstrap_sqlite_config_store($app);

    return $app;
}

function app_bootstrap_sqlite_config_store(array $app): void
{
    $configDb = $app['config_db'] ?? [];
    if (!is_array($configDb) || ($configDb['driver'] ?? '') !== 'sqlite') {
        return;
    }

    $sqlitePath = trim((string) ($configDb['name'] ?? ''));
    if ($sqlitePath === '' || $sqlitePath === ':memory:') {
        return;
    }

    clearstatcache(true, $sqlitePath);
    if (is_file($sqlitePath) && filesize($sqlitePath) !== 0) {
        return;
    }

    $result = app_config_db_bootstrap_apply($app);
    if (!$result['ok']) {
        throw new RuntimeException($result['error'] !== '' ? $result['error'] : 'SQLite config store bootstrap failed.');
    }
}

function app_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
