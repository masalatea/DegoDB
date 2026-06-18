<?php

declare(strict_types=1);

require_once __DIR__ . '/database_source_repository.php';
require_once __DIR__ . '/sql_dialect.php';

/**
 * @param array{
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     dsn:string
 * } $dbConfig
 */
function app_create_pdo_from_db_config(array $dbConfig): PDO
{
    $dsn = $dbConfig['dsn'];
    if (str_starts_with(strtolower(trim($dsn)), 'sqlite:')) {
        $sqlitePath = substr($dsn, strlen('sqlite:'));
        if ($sqlitePath !== '' && $sqlitePath !== ':memory:') {
            $sqliteDir = dirname($sqlitePath);
            if ($sqliteDir !== '' && $sqliteDir !== '.' && !is_dir($sqliteDir)) {
                mkdir($sqliteDir, 0775, true);
            }
        }
    }

    return new PDO(
        $dsn,
        $dbConfig['user'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    );
}

/**
 * @return array<string,array{
 *     key:string,
 *     label:string,
 *     description:string,
 *     source_of_truth:string,
 *     db_config_key:string,
 *     supports_live_schema_import:bool,
 *     supports_proxy_runtime_read:bool,
 *     proxy_runtime_priority:int,
 *     is_canonical_store:bool,
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     dsn:string
 * }>
 */
function app_database_source_builtin_keys(): array
{
    return ['db', 'config_db', 'lab_db'];
}

function app_database_source_is_builtin_key(string $key): bool
{
    return in_array(trim($key), app_database_source_builtin_keys(), true);
}

/**
 * @param array<string,mixed> $candidate
 * @return array{
 *     key:string,
 *     label:string,
 *     description:string,
 *     source_of_truth:string,
 *     db_config_key:string,
 *     supports_live_schema_import:bool,
 *     supports_proxy_runtime_read:bool,
 *     proxy_runtime_priority:int,
 *     is_canonical_store:bool,
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     dsn:string
 * }|null
 */
function app_database_source_normalize_catalog_candidate(
    array $app,
    string $candidateKey,
    array $candidate,
    string $defaultSourceOfTruth = 'app-config',
): ?array {
    $normalizedKey = trim((string) ($candidate['key'] ?? $candidateKey));
    if ($normalizedKey === '') {
        return null;
    }

    $dbConfigKey = trim((string) ($candidate['db_config_key'] ?? $normalizedKey));
    $fallbackConfig = app_database_config_exists($app, $dbConfigKey)
        ? app_database_config($app, $dbConfigKey)
        : [
            'host' => '',
            'port' => '',
            'name' => '',
            'user' => '',
            'password' => '',
            'dsn' => '',
        ];

    $host = (string) ($candidate['host'] ?? $fallbackConfig['host']);
    $port = (string) ($candidate['port'] ?? $fallbackConfig['port']);
    $name = (string) ($candidate['name'] ?? $fallbackConfig['name']);
    $user = (string) ($candidate['user'] ?? $fallbackConfig['user']);
    $password = (string) ($candidate['password'] ?? $fallbackConfig['password']);
    $dsn = (string) ($candidate['dsn'] ?? '');
    if ($dsn === '' && $host !== '' && $port !== '' && $name !== '') {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $host,
            $port,
            $name,
        );
    }

    return [
        'key' => $normalizedKey,
        'label' => trim((string) ($candidate['label'] ?? $normalizedKey)),
        'description' => trim((string) ($candidate['description'] ?? '')),
        'source_of_truth' => trim((string) ($candidate['source_of_truth'] ?? $defaultSourceOfTruth)),
        'db_config_key' => $dbConfigKey,
        'supports_live_schema_import' => (bool) ($candidate['supports_live_schema_import'] ?? false),
        'supports_proxy_runtime_read' => (bool) ($candidate['supports_proxy_runtime_read'] ?? false),
        'proxy_runtime_priority' => (int) ($candidate['proxy_runtime_priority'] ?? 1000),
        'is_canonical_store' => (bool) ($candidate['is_canonical_store'] ?? false),
        'host' => $host,
        'port' => $port,
        'name' => $name,
        'user' => $user,
        'password' => $password,
        'dsn' => $dsn !== '' ? $dsn : (string) $fallbackConfig['dsn'],
    ];
}

function app_database_source_catalog(array $app): array
{
    $catalog = $app['database_sources'] ?? null;
    if (!is_array($catalog)) {
        $catalog = [];
    }

    $normalizedCatalog = [];
    foreach ($catalog as $candidateKey => $candidate) {
        if (!is_string($candidateKey) || !is_array($candidate)) {
            continue;
        }

        $normalizedCandidate = app_database_source_normalize_catalog_candidate($app, $candidateKey, $candidate);
        if ($normalizedCandidate === null) {
            continue;
        }

        $normalizedCatalog[$normalizedCandidate['key']] = $normalizedCandidate;
    }

    foreach (app_database_source_builtin_keys() as $fallbackKey) {
        if (isset($normalizedCatalog[$fallbackKey]) || !app_database_config_exists($app, $fallbackKey)) {
            continue;
        }

        $fallbackConfig = app_database_config($app, $fallbackKey);
        $normalizedCatalog[$fallbackKey] = [
            'key' => $fallbackKey,
            'label' => $fallbackKey,
            'description' => '',
            'source_of_truth' => 'legacy-fallback',
            'db_config_key' => $fallbackKey,
            'supports_live_schema_import' => true,
            'supports_proxy_runtime_read' => in_array($fallbackKey, ['config_db', 'lab_db'], true),
            'proxy_runtime_priority' => $fallbackKey === 'lab_db' ? 100 : 200,
            'is_canonical_store' => $fallbackKey === 'config_db',
            'host' => $fallbackConfig['host'],
            'port' => $fallbackConfig['port'],
            'name' => $fallbackConfig['name'],
            'user' => $fallbackConfig['user'],
            'password' => $fallbackConfig['password'],
            'dsn' => $fallbackConfig['dsn'],
        ];
    }

    $persistedCatalogResult = app_fetch_database_sources($app);
    if ($persistedCatalogResult['ok']) {
        foreach ($persistedCatalogResult['items'] as $candidate) {
            $normalizedCandidate = app_database_source_normalize_catalog_candidate(
                $app,
                (string) ($candidate['source_key'] ?? ''),
                $candidate,
                'manual',
            );
            if ($normalizedCandidate === null || isset($normalizedCatalog[$normalizedCandidate['key']])) {
                continue;
            }

            $normalizedCatalog[$normalizedCandidate['key']] = $normalizedCandidate;
        }
    }

    return $normalizedCatalog;
}

function app_database_source_exists(array $app, string $key): bool
{
    $normalizedKey = trim($key);
    if ($normalizedKey === '') {
        return false;
    }

    return isset(app_database_source_catalog($app)[$normalizedKey]);
}

function app_database_source_supports_proxy_runtime_read(array $app, string $key): bool
{
    if (!app_database_source_exists($app, $key)) {
        return false;
    }

    return (bool) (app_database_source($app, $key)['supports_proxy_runtime_read'] ?? false);
}

/**
 * @return array{
 *     key:string,
 *     label:string,
 *     description:string,
 *     source_of_truth:string,
 *     db_config_key:string,
 *     supports_live_schema_import:bool,
 *     supports_proxy_runtime_read:bool,
 *     proxy_runtime_priority:int,
 *     is_canonical_store:bool,
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     dsn:string
 * }
 */
function app_database_source(array $app, string $key): array
{
    $normalizedKey = trim($key);
    $catalog = app_database_source_catalog($app);
    if (isset($catalog[$normalizedKey])) {
        return $catalog[$normalizedKey];
    }

    throw new RuntimeException($normalizedKey . ' database source が見つかりません。');
}

/**
 * @return array{
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     dsn:string
 * }
 */
function app_database_source_config(array $app, string $key): array
{
    $source = app_database_source($app, $key);

    return [
        'host' => $source['host'],
        'port' => $source['port'],
        'name' => $source['name'],
        'user' => $source['user'],
        'password' => $source['password'],
        'dsn' => $source['dsn'],
    ];
}

/**
 * @return list<array{
 *     key:string,
 *     label:string,
 *     description:string,
 *     source_of_truth:string,
 *     db_config_key:string,
 *     supports_live_schema_import:bool,
 *     supports_proxy_runtime_read:bool,
 *     proxy_runtime_priority:int,
 *     is_canonical_store:bool,
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     dsn:string
 * }>
 */
function app_database_source_proxy_runtime_candidates(array $app): array
{
    $candidates = array_values(array_filter(
        app_database_source_catalog($app),
        static fn (array $source): bool => (bool) ($source['supports_proxy_runtime_read'] ?? false),
    ));

    usort(
        $candidates,
        static function (array $left, array $right): int {
            $priorityCompare = ((int) ($left['proxy_runtime_priority'] ?? 1000))
                <=> ((int) ($right['proxy_runtime_priority'] ?? 1000));
            if ($priorityCompare !== 0) {
                return $priorityCompare;
            }

            return strcmp(
                trim((string) ($left['key'] ?? '')),
                trim((string) ($right['key'] ?? '')),
            );
        },
    );

    return $candidates;
}

function app_database_source_canonical_store_key(array $app): string
{
    foreach (app_database_source_catalog($app) as $source) {
        if ((bool) ($source['is_canonical_store'] ?? false)) {
            return (string) ($source['key'] ?? 'config_db');
        }
    }

    if (app_database_source_exists($app, 'config_db')) {
        return 'config_db';
    }

    return app_database_source_exists($app, 'db') ? 'db' : '';
}

/**
 * @return array{
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     dsn:string
 * }
 */
function app_database_config_exists(array $app, string $key = 'db'): bool
{
    $candidate = $app[$key] ?? null;

    return is_array($candidate)
        && isset(
            $candidate['host'],
            $candidate['port'],
            $candidate['name'],
            $candidate['user'],
            $candidate['password'],
            $candidate['dsn'],
        );
}

/**
 * @return array{
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     dsn:string
 * }
 */
function app_database_config(array $app, string $key = 'db'): array
{
    if (app_database_config_exists($app, $key)) {
        $candidate = $app[$key];

        return [
            'host' => (string) $candidate['host'],
            'port' => (string) $candidate['port'],
            'name' => (string) $candidate['name'],
            'user' => (string) $candidate['user'],
            'password' => (string) $candidate['password'],
            'dsn' => (string) $candidate['dsn'],
        ];
    }

    if ($key !== 'db') {
        throw new RuntimeException($key . ' config が見つかりません。');
    }

    throw new RuntimeException('db config が見つかりません。');
}

/**
 * @param array{
 *     db:array{
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     }
 * } $app
 */
function app_create_pdo(array $app): PDO
{
    return app_create_pdo_from_db_config(app_database_config($app, 'db'));
}

function app_create_config_pdo(array $app): PDO
{
    return app_create_pdo_from_db_config(app_database_config($app, 'config_db'));
}

/**
 * Canonical metadata stays on config_db even when the site default DB points at
 * a live schema source or runtime database.
 */
function app_create_metadata_pdo(array $app): PDO
{
    return app_create_config_pdo($app);
}

/**
 * @param array{
 *     db:array{
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     label:string,
 *     detail:string
 * }
 */
function app_probe_database(array $app): array
{
    return app_probe_database_config($app, 'db');
}

/**
 * @return array{
 *     ok:bool,
 *     label:string,
 *     detail:string
 * }
 */
function app_probe_config_database(array $app): array
{
    return app_probe_database_config($app, 'config_db');
}

function app_probe_database_source(array $app, string $key): array
{
    try {
        $pdo = app_create_pdo_from_db_config(app_database_source_config($app, $key));
        $version = app_sql_server_version($pdo);

        return [
            'ok' => true,
            'label' => 'connected',
            'detail' => $version !== '' ? $version : 'version unavailable',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'label' => 'connection failed',
            'detail' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{
 *     ok:bool,
 *     label:string,
 *     detail:string
 * }
 */
function app_probe_database_config(array $app, string $key): array
{
    try {
        $pdo = app_create_pdo_from_db_config(app_database_config($app, $key));
        $version = app_sql_server_version($pdo);

        return [
            'ok' => true,
            'label' => 'connected',
            'detail' => $version !== '' ? $version : 'version unavailable',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'label' => 'connection failed',
            'detail' => $throwable->getMessage(),
        ];
    }
}
