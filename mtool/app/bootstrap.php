<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

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
    return app_load_config();
}

function app_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
