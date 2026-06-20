<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/generated_name.php';
require_once __DIR__ . '/legacy_table_schema_reference.php';
require_once __DIR__ . '/project_scope_policy.php';

/**
 * @return list<array{
 *     key:string,
 *     label:string,
 *     description:string,
 *     apply_supported:bool,
 *     database_source_key:string
 * }>
 */
function app_project_table_import_live_source_definitions(array $app = []): array
{
    $definitions = [
        [
            'key' => 'live-schema',
            'label' => 'live schema',
            'description' => '現在の接続先 DB schema を import source として使います。',
            'apply_supported' => true,
            'database_source_key' => 'db',
        ],
        [
            'key' => 'lab-live-schema',
            'label' => 'lab live schema',
            'description' => 'Lab DB schema を import source として使います。',
            'apply_supported' => true,
            'database_source_key' => 'lab_db',
        ],
    ];

    foreach (app_project_table_import_external_live_source_definitions($app) as $definition) {
        $definitions[] = $definition;
    }

    return $definitions;
}

function app_project_table_import_named_live_source_option_prefix(): string
{
    return 'named-live-schema:';
}

function app_project_table_import_named_live_source_option_key(string $databaseSourceKey): string
{
    return app_project_table_import_named_live_source_option_prefix() . trim($databaseSourceKey);
}

function app_project_table_import_named_live_source_database_source_key(string $sourceKey): string
{
    $normalizedSourceKey = trim($sourceKey);
    $prefix = app_project_table_import_named_live_source_option_prefix();
    if (!str_starts_with($normalizedSourceKey, $prefix)) {
        return '';
    }

    return trim(substr($normalizedSourceKey, strlen($prefix)));
}

/**
 * @return list<array{
 *     key:string,
 *     label:string,
 *     description:string,
 *     apply_supported:bool,
 *     database_source_key:string
 * }>
 */
function app_project_table_import_external_live_source_definitions(array $app): array
{
    if ($app === []) {
        return [];
    }

    $definitions = [];
    foreach (app_database_source_catalog($app) as $source) {
        $databaseSourceKey = trim((string) ($source['key'] ?? ''));
        if (
            $databaseSourceKey === ''
            || app_database_source_is_builtin_key($databaseSourceKey)
            || !(bool) ($source['supports_live_schema_import'] ?? false)
        ) {
            continue;
        }

        $label = trim((string) ($source['label'] ?? $databaseSourceKey));
        $description = trim((string) ($source['description'] ?? ''));
        if ($description === '') {
            $description = 'admin-managed named database source を import source として使います。';
        }

        $definitions[] = [
            'key' => app_project_table_import_named_live_source_option_key($databaseSourceKey),
            'label' => 'named live schema / ' . $label,
            'description' => $description,
            'apply_supported' => true,
            'database_source_key' => $databaseSourceKey,
        ];
    }

    usort(
        $definitions,
        static fn (array $left, array $right): int => strcmp(
            (string) ($left['database_source_key'] ?? ''),
            (string) ($right['database_source_key'] ?? ''),
        ),
    );

    return $definitions;
}

/**
 * @return array{
 *     key:string,
 *     label:string,
 *     description:string,
 *     apply_supported:bool,
 *     database_source_key:string
 * }|null
 */
function app_project_table_import_live_source_definition(string $sourceKey, array $app = []): ?array
{
    $normalizedSourceKey = trim($sourceKey);
    if ($normalizedSourceKey === '') {
        return null;
    }

    foreach (app_project_table_import_live_source_definitions($app) as $definition) {
        if ($definition['key'] === $normalizedSourceKey) {
            return $definition;
        }
    }

    return null;
}

function app_project_table_import_source_normalize(string $projectKey, ?string $sourceKey, array $app = []): string
{
    $candidate = trim((string) $sourceKey);
    if ($candidate === '') {
        return 'live-schema';
    }

    foreach (app_project_table_import_source_options($projectKey, $app) as $option) {
        if ($option['key'] === $candidate) {
            return $candidate;
        }
    }

    return 'live-schema';
}

/**
 * @return array{
 *     key:string,
 *     label:string,
 *     description:string,
 *     apply_supported:bool
 * }|null
 */
function app_project_table_import_source_option(string $projectKey, string $sourceKey, array $app = []): ?array
{
    $normalizedSourceKey = app_project_table_import_source_normalize($projectKey, $sourceKey, $app);
    foreach (app_project_table_import_source_options($projectKey, $app) as $option) {
        if ($option['key'] === $normalizedSourceKey) {
            return $option;
        }
    }

    return null;
}

/**
 * @return list<array{
 *     key:string,
 *     label:string,
 *     description:string,
 *     apply_supported:bool
 * }>
 */
function app_project_table_import_source_options(string $projectKey, array $app = []): array
{
    $options = array_map(
        static fn (array $definition): array => [
            'key' => $definition['key'],
            'label' => $definition['label'],
            'description' => $definition['description'],
            'apply_supported' => $definition['apply_supported'],
        ],
        app_project_table_import_live_source_definitions($app),
    );

    if (app_legacy_table_schema_reference_path($projectKey) !== '') {
        $options[] = [
            'key' => 'legacy-reference',
            'label' => 'legacy reference',
            'description' => '旧 Mtool schema snapshot を比較用 baseline として使います。',
            'apply_supported' => false,
        ];

        foreach (app_project_table_import_legacy_scope_definitions($projectKey) as $scopeDefinition) {
            $options[] = [
                'key' => 'legacy-reference-' . $scopeDefinition['key'],
                'label' => 'legacy reference / ' . $scopeDefinition['label'],
                'description' => $scopeDefinition['description'],
                'apply_supported' => $scopeDefinition['apply_supported'],
            ];
        }
    }

    return $options;
}

/**
 * @return list<array{
 *     key:string,
 *     label:string,
 *     description:string,
 *     apply_supported:bool,
 *     table_names:list<string>
 * }>
 */
function app_project_table_import_legacy_scope_definitions(string $projectKey): array
{
    if (strtoupper(trim($projectKey)) !== 'MTOOL') {
        return [];
    }

    return [
        [
            'key' => 'test-module',
            'label' => 'test module',
            'description' => 'legacy Test module の physical table 群だけを段階 import します。',
            'apply_supported' => true,
            'table_names' => [
                'Test',
                'TestCondition',
                'TestConditionSelection',
                'TestGroup',
                'TestPattern',
                'TestPatternExecuteResult',
                'TestPatternSelection',
            ],
        ],
        [
            'key' => 'build-run-state',
            'label' => 'build / run state',
            'description' => 'legacy build token / cache / saved-file table 群だけを段階 import します。',
            'apply_supported' => true,
            'table_names' => [
                'BuildLog',
                'BuildSourceCache',
                'BuildSourceFuncCache',
                'BuildToken',
                'BuildTokenCompletedItem',
                'BuildTokenProjectSourceOutput',
                'BuildTokenTemplateCache',
                'CompareOutputSearchCache',
                'CompareOutputSearchCacheHint',
                'ProjectSourceOutputSavedFiles',
                'UploadDropboxPathCache',
                'UploadDropboxPathCacheItems',
            ],
        ],
    ];
}

/**
 * @return array{
 *     key:string,
 *     label:string,
 *     description:string,
 *     apply_supported:bool,
 *     table_names:list<string>
 * }|null
 */
function app_project_table_import_legacy_scope_definition(string $projectKey, string $scopeKey): ?array
{
    foreach (app_project_table_import_legacy_scope_definitions($projectKey) as $scopeDefinition) {
        if ($scopeDefinition['key'] === $scopeKey) {
            return $scopeDefinition;
        }
    }

    return null;
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     source_key:string,
 *     source_label:string,
 *     source_description:string,
 *     source_schema_name:string,
 *     apply_supported:bool,
 *     managed_target_table_names:list<string>,
 *     compare_against_all_canonical:bool,
 *     tables:list<array{
 *         name:string,
 *         columns:list<array{
 *             name:string,
 *             datatype:string,
 *             is_null:string,
 *             is_key:string,
 *             is_default:string,
 *             extra:string,
 *             column_list_order:int
 *         }>,
 *         columns_by_name:array<string,array{
 *             name:string,
 *             datatype:string,
 *             is_null:string,
 *             is_key:string,
 *             is_default:string,
 *             extra:string,
 *             column_list_order:int
 *         }>
 *     }>,
 *     error:string
 * }
 */
function app_project_table_import_source_resolve(array $app, string $projectKey, string $sourceKey): array
{
    $normalizedSourceKey = app_project_table_import_source_normalize($projectKey, $sourceKey, $app);

    $liveSourceDefinition = app_project_table_import_live_source_definition($normalizedSourceKey, $app);
    if ($liveSourceDefinition !== null) {
        return app_project_table_import_source_named_live_schema(
            $app,
            $projectKey,
            $liveSourceDefinition,
        );
    }

    return match ($normalizedSourceKey) {
        'legacy-reference' => app_project_table_import_source_legacy_reference($projectKey),
        'legacy-reference-test-module' => app_project_table_import_source_legacy_reference_scope($projectKey, 'test-module'),
        'legacy-reference-build-run-state' => app_project_table_import_source_legacy_reference_scope($projectKey, 'build-run-state'),
        default => app_project_table_import_source_live_schema($app, $projectKey),
    };
}

/**
 * @param array{
 *     key:string,
 *     label:string,
 *     description:string,
 *     apply_supported:bool,
 *     database_source_key:string
 * } $sourceDefinition
 * @return array{
 *     ok:bool,
 *     source_key:string,
 *     source_label:string,
 *     source_description:string,
 *     source_schema_name:string,
 *     apply_supported:bool,
 *     managed_target_table_names:list<string>,
 *     compare_against_all_canonical:bool,
 *     tables:list<array{
 *         name:string,
 *         columns:list<array{
 *             name:string,
 *             datatype:string,
 *             is_null:string,
 *             is_key:string,
 *             is_default:string,
 *             extra:string,
 *             column_list_order:int
 *         }>,
 *         columns_by_name:array<string,array{
 *             name:string,
 *             datatype:string,
 *             is_null:string,
 *             is_key:string,
 *             is_default:string,
 *             extra:string,
 *             column_list_order:int
 *         }>
 *     }>,
 *     error:string
 * }
 */
function app_project_table_import_source_named_live_schema(
    array $app,
    string $projectKey,
    array $sourceDefinition,
): array {
    try {
        $normalizedProjectKey = app_normalize_project_key($projectKey);
        $dbConfig = app_database_source_config($app, $sourceDefinition['database_source_key']);
        $schemaName = trim((string) ($dbConfig['name'] ?? ''));
        if ($schemaName === '') {
            throw new RuntimeException('import source schema name が未設定です。');
        }

        $pdo = app_create_pdo_from_db_config($dbConfig);
        $dialect = app_sql_dialect_from_db_config($dbConfig);
        if ($dialect === 'sqlite') {
            $tables = app_project_table_import_source_tables_from_sqlite($pdo);
            $schemaName = app_sql_current_database_name($pdo);

            return [
                'ok' => true,
                'source_key' => $sourceDefinition['key'],
                'source_label' => $sourceDefinition['label'],
                'source_description' => $sourceDefinition['description'],
                'source_schema_name' => $schemaName,
                'apply_supported' => $sourceDefinition['apply_supported'],
                'managed_target_table_names' => app_project_table_import_live_schema_managed_target_table_names(
                    $app,
                    $normalizedProjectKey,
                    $sourceDefinition['key'],
                    $schemaName,
                    $tables,
                ),
                'compare_against_all_canonical' => false,
                'tables' => $tables,
                'error' => '',
            ];
        }

        if ($dialect === 'pgsql') {
            $schemaName = app_project_table_import_source_pgsql_current_schema($pdo);
            $tables = app_project_table_import_source_tables_from_pgsql($pdo);

            return [
                'ok' => true,
                'source_key' => $sourceDefinition['key'],
                'source_label' => $sourceDefinition['label'],
                'source_description' => $sourceDefinition['description'],
                'source_schema_name' => $schemaName,
                'apply_supported' => $sourceDefinition['apply_supported'],
                'managed_target_table_names' => app_project_table_import_live_schema_managed_target_table_names(
                    $app,
                    $normalizedProjectKey,
                    $sourceDefinition['key'],
                    $schemaName,
                    $tables,
                ),
                'compare_against_all_canonical' => false,
                'tables' => $tables,
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'SELECT
                c.TABLE_NAME,
                c.COLUMN_NAME,
                c.COLUMN_TYPE,
                c.IS_NULLABLE,
                c.COLUMN_KEY,
                c.COLUMN_DEFAULT,
                c.EXTRA,
                c.ORDINAL_POSITION
            FROM information_schema.COLUMNS AS c
            INNER JOIN information_schema.TABLES AS t
                ON t.TABLE_SCHEMA = c.TABLE_SCHEMA
               AND t.TABLE_NAME = c.TABLE_NAME
            WHERE c.TABLE_SCHEMA = :schema_name
              AND t.TABLE_TYPE = "BASE TABLE"
            ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION'
        );
        $statement->execute([
            ':schema_name' => $schemaName,
        ]);

        $rows = $statement->fetchAll();
        $tables = app_project_table_import_source_tables_from_information_schema_rows($rows);

        return [
            'ok' => true,
            'source_key' => $sourceDefinition['key'],
            'source_label' => $sourceDefinition['label'],
            'source_description' => $sourceDefinition['description'],
            'source_schema_name' => $schemaName,
            'apply_supported' => $sourceDefinition['apply_supported'],
            'managed_target_table_names' => app_project_table_import_live_schema_managed_target_table_names(
                $app,
                $normalizedProjectKey,
                $sourceDefinition['key'],
                $schemaName,
                $tables,
            ),
            'compare_against_all_canonical' => false,
            'tables' => $tables,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'source_key' => $sourceDefinition['key'],
            'source_label' => $sourceDefinition['label'],
            'source_description' => $sourceDefinition['description'],
            'source_schema_name' => '',
            'apply_supported' => $sourceDefinition['apply_supported'],
            'managed_target_table_names' => [],
            'compare_against_all_canonical' => false,
            'tables' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return list<array{
 *     name:string,
 *     columns:list<array{
 *         name:string,
 *         datatype:string,
 *         is_null:string,
 *         is_key:string,
 *         is_default:string,
 *         extra:string,
 *         column_list_order:int
 *     }>,
 *     columns_by_name:array<string,array{
 *         name:string,
 *         datatype:string,
 *         is_null:string,
 *         is_key:string,
 *         is_default:string,
 *         extra:string,
 *         column_list_order:int
 *     }>
 * }>
 */
function app_project_table_import_source_tables_from_sqlite(PDO $pdo): array
{
    $tableRows = $pdo->query(
        "SELECT name
         FROM sqlite_schema
         WHERE type = 'table'
           AND name NOT LIKE 'sqlite_%'
         ORDER BY name",
    )->fetchAll();
    $tables = [];

    foreach ($tableRows as $tableRow) {
        if (!is_array($tableRow)) {
            continue;
        }

        $tableName = trim((string) ($tableRow['name'] ?? ''));
        if ($tableName === '') {
            continue;
        }

        $columns = [];
        $columnsByName = [];
        $columnRows = $pdo->query('PRAGMA table_info(' . app_sql_identifier('sqlite', $tableName) . ')')->fetchAll();
        foreach ($columnRows as $columnRow) {
            if (!is_array($columnRow)) {
                continue;
            }

            $columnName = trim((string) ($columnRow['name'] ?? ''));
            if ($columnName === '') {
                continue;
            }

            $type = trim((string) ($columnRow['type'] ?? ''));
            $isPrimary = (int) ($columnRow['pk'] ?? 0) > 0;
            $isNotNull = (int) ($columnRow['notnull'] ?? 0) > 0 || $isPrimary;
            $column = [
                'name' => $columnName,
                'datatype' => $type !== '' ? $type : 'TEXT',
                'is_null' => $isNotNull ? 'NO' : 'YES',
                'is_key' => $isPrimary ? 'PRI' : '',
                'is_default' => app_project_table_import_source_default_string($columnRow['dflt_value'] ?? null),
                'extra' => $isPrimary && strtoupper($type) === 'INTEGER' ? 'auto_increment' : '',
                'column_list_order' => ((int) ($columnRow['cid'] ?? 0)) + 1,
            ];
            $columns[] = $column;
            $columnsByName[$columnName] = $column;
        }

        $tables[] = [
            'name' => $tableName,
            'columns' => $columns,
            'columns_by_name' => $columnsByName,
        ];
    }

    return $tables;
}

function app_project_table_import_source_pgsql_current_schema(PDO $pdo): string
{
    $schemaName = $pdo->query('SELECT current_schema()')->fetchColumn();

    return is_string($schemaName) && trim($schemaName) !== '' ? trim($schemaName) : 'public';
}

/**
 * @return list<array{
 *     name:string,
 *     columns:list<array{
 *         name:string,
 *         datatype:string,
 *         is_null:string,
 *         is_key:string,
 *         is_default:string,
 *         extra:string,
 *         column_list_order:int
 *     }>,
 *     columns_by_name:array<string,array{
 *         name:string,
 *         datatype:string,
 *         is_null:string,
 *         is_key:string,
 *         is_default:string,
 *         extra:string,
 *         column_list_order:int
 *     }>
 * }>
 */
function app_project_table_import_source_tables_from_pgsql(PDO $pdo): array
{
    $statement = $pdo->query(
        "SELECT
            c.relname AS table_name,
            a.attname AS column_name,
            format_type(a.atttypid, a.atttypmod) AS column_type,
            CASE WHEN a.attnotnull THEN 'NO' ELSE 'YES' END AS is_nullable,
            CASE WHEN pk.attname IS NOT NULL THEN 'PRI' ELSE '' END AS column_key,
            pg_get_expr(ad.adbin, ad.adrelid) AS column_default,
            CASE
                WHEN a.attidentity <> '' THEN 'auto_increment'
                WHEN pg_get_expr(ad.adbin, ad.adrelid) LIKE 'nextval(%' THEN 'auto_increment'
                ELSE ''
            END AS extra,
            a.attnum AS ordinal_position
        FROM pg_class AS c
        INNER JOIN pg_namespace AS n
            ON n.oid = c.relnamespace
        INNER JOIN pg_attribute AS a
            ON a.attrelid = c.oid
        LEFT JOIN pg_attrdef AS ad
            ON ad.adrelid = c.oid
           AND ad.adnum = a.attnum
        LEFT JOIN (
            SELECT
                i.indrelid,
                unnest(i.indkey) AS attnum,
                a2.attname
            FROM pg_index AS i
            INNER JOIN pg_attribute AS a2
                ON a2.attrelid = i.indrelid
               AND a2.attnum = ANY(i.indkey)
            WHERE i.indisprimary
        ) AS pk
            ON pk.indrelid = c.oid
           AND pk.attnum = a.attnum
        WHERE n.nspname = current_schema()
          AND c.relkind IN ('r', 'p')
          AND a.attnum > 0
          AND NOT a.attisdropped
        ORDER BY c.relname, a.attnum",
    );

    return app_project_table_import_source_tables_from_information_schema_rows($statement->fetchAll());
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     source_key:string,
 *     source_label:string,
 *     source_description:string,
 *     source_schema_name:string,
 *     apply_supported:bool,
 *     managed_target_table_names:list<string>,
 *     compare_against_all_canonical:bool,
 *     tables:list<array{
 *         name:string,
 *         columns:list<array{
 *             name:string,
 *             datatype:string,
 *             is_null:string,
 *             is_key:string,
 *             is_default:string,
 *             extra:string,
 *             column_list_order:int
 *         }>,
 *         columns_by_name:array<string,array{
 *             name:string,
 *             datatype:string,
 *             is_null:string,
 *             is_key:string,
 *             is_default:string,
 *             extra:string,
 *             column_list_order:int
 *         }>
 *     }>,
 *     error:string
 * }
 */
function app_project_table_import_source_live_schema(array $app, string $projectKey): array
{
    $sourceDefinition = app_project_table_import_live_source_definition('live-schema');
    if ($sourceDefinition === null) {
        return [
            'ok' => false,
            'source_key' => 'live-schema',
            'source_label' => 'live schema',
            'source_description' => '現在の接続先 DB schema を import source として使います。',
            'source_schema_name' => '',
            'apply_supported' => true,
            'managed_target_table_names' => [],
            'compare_against_all_canonical' => false,
            'tables' => [],
            'error' => 'live schema import source 定義が見つかりません。',
        ];
    }

    return app_project_table_import_source_named_live_schema($app, $projectKey, $sourceDefinition);
}

/**
 * @return array{
 *     ok:bool,
 *     source_key:string,
 *     source_label:string,
 *     source_description:string,
 *     source_schema_name:string,
 *     apply_supported:bool,
 *     managed_target_table_names:list<string>,
 *     compare_against_all_canonical:bool,
 *     tables:list<array{
 *         name:string,
 *         columns:list<array{
 *             name:string,
 *             datatype:string,
 *             is_null:string,
 *             is_key:string,
 *             is_default:string,
 *             extra:string,
 *             column_list_order:int
 *         }>,
 *         columns_by_name:array<string,array{
 *             name:string,
 *             datatype:string,
 *             is_null:string,
 *             is_key:string,
 *             is_default:string,
 *             extra:string,
 *             column_list_order:int
 *         }>
 *     }>,
 *     error:string
 * }
 */
function app_project_table_import_source_legacy_reference(string $projectKey): array
{
    $reference = app_load_legacy_table_schema_reference($projectKey);
    if (!$reference['ok'] || $reference['item'] === null) {
        return [
            'ok' => false,
            'source_key' => 'legacy-reference',
            'source_label' => 'legacy reference',
            'source_description' => '旧 Mtool schema snapshot を比較用 baseline として使います。',
            'source_schema_name' => '',
            'apply_supported' => false,
            'managed_target_table_names' => [],
            'compare_against_all_canonical' => true,
            'tables' => [],
            'error' => $reference['error'],
        ];
    }

    return [
        'ok' => true,
        'source_key' => 'legacy-reference',
        'source_label' => 'legacy reference',
        'source_description' => '旧 Mtool schema snapshot を比較用 baseline として使います。',
        'source_schema_name' => $reference['item']['source_schema_name'],
        'apply_supported' => false,
        'managed_target_table_names' => [],
        'compare_against_all_canonical' => true,
        'tables' => app_project_table_import_source_tables_from_reference($reference['item']['tables']),
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     source_key:string,
 *     source_label:string,
 *     source_description:string,
 *     source_schema_name:string,
 *     apply_supported:bool,
 *     managed_target_table_names:list<string>,
 *     compare_against_all_canonical:bool,
 *     tables:list<array{
 *         name:string,
 *         columns:list<array{
 *             name:string,
 *             datatype:string,
 *             is_null:string,
 *             is_key:string,
 *             is_default:string,
 *             extra:string,
 *             column_list_order:int
 *         }>,
 *         columns_by_name:array<string,array{
 *             name:string,
 *             datatype:string,
 *             is_null:string,
 *             is_key:string,
 *             is_default:string,
 *             extra:string,
 *             column_list_order:int
 *         }>
 *     }>,
 *     error:string
 * }
 */
function app_project_table_import_source_legacy_reference_scope(string $projectKey, string $scopeKey): array
{
    $scopeDefinition = app_project_table_import_legacy_scope_definition($projectKey, $scopeKey);
    if ($scopeDefinition === null) {
        return [
            'ok' => false,
            'source_key' => 'legacy-reference-' . $scopeKey,
            'source_label' => 'legacy reference / ' . $scopeKey,
            'source_description' => '',
            'source_schema_name' => '',
            'apply_supported' => false,
            'managed_target_table_names' => [],
            'compare_against_all_canonical' => false,
            'tables' => [],
            'error' => 'legacy import scope が見つかりません: ' . $scopeKey,
        ];
    }

    $reference = app_load_legacy_table_schema_reference($projectKey);
    if (!$reference['ok'] || $reference['item'] === null) {
        return [
            'ok' => false,
            'source_key' => 'legacy-reference-' . $scopeKey,
            'source_label' => 'legacy reference / ' . $scopeDefinition['label'],
            'source_description' => $scopeDefinition['description'],
            'source_schema_name' => '',
            'apply_supported' => $scopeDefinition['apply_supported'],
            'managed_target_table_names' => $scopeDefinition['table_names'],
            'compare_against_all_canonical' => false,
            'tables' => [],
            'error' => $reference['error'],
        ];
    }

    $filteredReferenceTables = app_project_table_import_filter_reference_tables(
        $reference['item']['tables'],
        $scopeDefinition['table_names'],
    );
    if ($filteredReferenceTables === []) {
        return [
            'ok' => false,
            'source_key' => 'legacy-reference-' . $scopeKey,
            'source_label' => 'legacy reference / ' . $scopeDefinition['label'],
            'source_description' => $scopeDefinition['description'],
            'source_schema_name' => $reference['item']['source_schema_name'],
            'apply_supported' => $scopeDefinition['apply_supported'],
            'managed_target_table_names' => $scopeDefinition['table_names'],
            'compare_against_all_canonical' => false,
            'tables' => [],
            'error' => 'legacy reference に scope 対象 table が見つかりませんでした。',
        ];
    }

    return [
        'ok' => true,
        'source_key' => 'legacy-reference-' . $scopeKey,
        'source_label' => 'legacy reference / ' . $scopeDefinition['label'],
        'source_description' => $scopeDefinition['description'],
        'source_schema_name' => $reference['item']['source_schema_name'],
        'apply_supported' => $scopeDefinition['apply_supported'],
        'managed_target_table_names' => $scopeDefinition['table_names'],
        'compare_against_all_canonical' => false,
        'tables' => app_project_table_import_source_tables_from_reference($filteredReferenceTables),
        'error' => '',
    ];
}

/**
 * @param list<array{
 *     name:string,
 *     columns:list<array<string,mixed>>,
 *     columns_by_name?:array<string,array<string,mixed>>
 * }> $tables
 * @return list<string>
 */
function app_project_table_import_live_schema_managed_target_table_names(
    array $app,
    string $projectKey,
    string $sourceKey,
    string $schemaName,
    array $tables,
): array
{
    if (
        strtoupper(trim($projectKey)) === 'MTOOL'
        && trim($sourceKey) === 'live-schema'
    ) {
        $tableNames = array_keys(app_mtool_self_host_legacy_table_alias_map());
        sort($tableNames, SORT_NATURAL | SORT_FLAG_CASE);

        return array_values($tableNames);
    }

    $filterInternalConfigTables = app_project_table_import_live_schema_should_filter_internal_config_tables(
        $app,
        $schemaName,
    );
    $tableNames = [];
    foreach ($tables as $table) {
        $tableName = trim((string) ($table['name'] ?? ''));
        if ($tableName === '') {
            continue;
        }
        if (
            $filterInternalConfigTables
            && app_project_table_import_live_schema_is_internal_config_table_name($tableName)
        ) {
            continue;
        }
        $tableNames[] = $tableName;
    }

    sort($tableNames, SORT_NATURAL | SORT_FLAG_CASE);

    return array_values(array_unique($tableNames));
}

function app_project_table_import_live_schema_should_filter_internal_config_tables(
    array $app,
    string $schemaName,
): bool {
    $normalizedSchemaName = strtolower(trim($schemaName));
    $normalizedConfigSchemaName = strtolower(trim((string) ($app['config_db']['name'] ?? '')));

    return $normalizedSchemaName !== ''
        && $normalizedConfigSchemaName !== ''
        && $normalizedSchemaName === $normalizedConfigSchemaName;
}

function app_project_table_import_live_schema_is_internal_config_table_name(string $tableName): bool
{
    $normalized = strtolower(trim($tableName));
    if ($normalized === '') {
        return false;
    }

    if (str_starts_with($normalized, 'project_')) {
        return true;
    }

    return in_array($normalized, [
        'projects',
        'dbtable',
        'dbtablecolumns',
        'dataclass',
        'dataclassfields',
        'html_templates',
        'html_template_parameters',
    ], true);
}

/**
 * @param list<array{
 *     name:string,
 *     column_count:int,
 *     columns:list<array{
 *         name:string,
 *         datatype:string,
 *         is_null:string,
 *         is_key:string,
 *         is_default:string,
 *         extra:string,
 *         column_list_order:int
 *     }>
 * }> $referenceTables
 * @param list<string> $tableNames
 * @return list<array{
 *     name:string,
 *     column_count:int,
 *     columns:list<array{
 *         name:string,
 *         datatype:string,
 *         is_null:string,
 *         is_key:string,
 *         is_default:string,
 *         extra:string,
 *         column_list_order:int
 *     }>
 * }>
 */
function app_project_table_import_filter_reference_tables(array $referenceTables, array $tableNames): array
{
    if ($tableNames === []) {
        return [];
    }

    $allowedNames = [];
    foreach ($tableNames as $tableName) {
        $normalizedTableName = trim((string) $tableName);
        if ($normalizedTableName === '') {
            continue;
        }
        $allowedNames[$normalizedTableName] = true;
    }

    $filteredTables = [];
    foreach ($referenceTables as $referenceTable) {
        if (!is_array($referenceTable)) {
            continue;
        }

        $tableName = trim((string) ($referenceTable['name'] ?? ''));
        if ($tableName === '' || !isset($allowedNames[$tableName])) {
            continue;
        }

        $filteredTables[] = $referenceTable;
    }

    usort(
        $filteredTables,
        static fn (array $left, array $right): int => strcasecmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? '')),
    );

    return $filteredTables;
}

/**
 * @param list<mixed> $rows
 * @return list<array{
 *     name:string,
 *     columns:list<array{
 *         name:string,
 *         datatype:string,
 *         is_null:string,
 *         is_key:string,
 *         is_default:string,
 *         extra:string,
 *         column_list_order:int
 *     }>,
 *     columns_by_name:array<string,array{
 *         name:string,
 *         datatype:string,
 *         is_null:string,
 *         is_key:string,
 *         is_default:string,
 *         extra:string,
 *         column_list_order:int
 *     }>
 * }>
 */
function app_project_table_import_source_tables_from_information_schema_rows(array $rows): array
{
    $tables = [];
    $indexByTableName = [];

    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }

        $tableName = app_project_table_import_source_row_value($row, 'TABLE_NAME', 'table_name');
        if ($tableName === '') {
            continue;
        }

        if (!array_key_exists($tableName, $indexByTableName)) {
            $tableNameMap = app_generated_name_map_for_physical_name($tableName, 'class');
            $indexByTableName[$tableName] = count($tables);
            $tables[] = [
                'name' => $tableName,
                'physical_name' => $tableNameMap['physical_name'],
                'logical_name' => $tableNameMap['logical_name'],
                'generated_name' => $tableNameMap['generated_name'],
                'columns' => [],
                'columns_by_name' => [],
            ];
        }

        $columnName = app_project_table_import_source_row_value($row, 'COLUMN_NAME', 'column_name');
        $columnNameMap = app_generated_name_map_for_physical_name($columnName, 'php-property');
        $column = [
            'name' => $columnName,
            'physical_name' => $columnNameMap['physical_name'],
            'logical_name' => $columnNameMap['logical_name'],
            'generated_name' => $columnNameMap['generated_name'],
            'datatype' => app_project_table_import_source_row_value($row, 'COLUMN_TYPE', 'column_type'),
            'is_null' => app_project_table_import_source_row_value($row, 'IS_NULLABLE', 'is_nullable'),
            'is_key' => app_project_table_import_source_row_value($row, 'COLUMN_KEY', 'column_key'),
            'is_default' => app_project_table_import_source_default_string(
                app_project_table_import_source_row_raw_value($row, 'COLUMN_DEFAULT', 'column_default'),
            ),
            'extra' => app_project_table_import_source_row_value($row, 'EXTRA', 'extra'),
            'column_list_order' => (int) app_project_table_import_source_row_value(
                $row,
                'ORDINAL_POSITION',
                'ordinal_position',
            ),
        ];

        $tableIndex = $indexByTableName[$tableName];
        $tables[$tableIndex]['columns'][] = $column;
        $tables[$tableIndex]['columns_by_name'][$column['name']] = $column;
    }

    return $tables;
}

function app_project_table_import_source_row_raw_value(array $row, string $upperKey, string $lowerKey): mixed
{
    if (array_key_exists($upperKey, $row)) {
        return $row[$upperKey];
    }

    if (array_key_exists($lowerKey, $row)) {
        return $row[$lowerKey];
    }

    return null;
}

function app_project_table_import_source_row_value(array $row, string $upperKey, string $lowerKey): string
{
    return (string) app_project_table_import_source_row_raw_value($row, $upperKey, $lowerKey);
}

/**
 * @param list<array{
 *     name:string,
 *     column_count:int,
 *     columns:list<array{
 *         name:string,
 *         datatype:string,
 *         is_null:string,
 *         is_key:string,
 *         is_default:string,
 *         extra:string,
 *         column_list_order:int
 *     }>
 * }> $referenceTables
 * @return list<array{
 *     name:string,
 *     columns:list<array{
 *         name:string,
 *         datatype:string,
 *         is_null:string,
 *         is_key:string,
 *         is_default:string,
 *         extra:string,
 *         column_list_order:int
 *     }>,
 *     columns_by_name:array<string,array{
 *         name:string,
 *         datatype:string,
 *         is_null:string,
 *         is_key:string,
 *         is_default:string,
 *         extra:string,
 *         column_list_order:int
 *     }>
 * }>
 */
function app_project_table_import_source_tables_from_reference(array $referenceTables): array
{
    $tables = [];

    foreach ($referenceTables as $referenceTable) {
        if (!is_array($referenceTable)) {
            continue;
        }

        $tableName = (string) ($referenceTable['name'] ?? '');
        if ($tableName === '') {
            continue;
        }
        $tableNameMap = app_generated_name_map_for_physical_name($tableName, 'class');

        $columns = [];
        $columnsByName = [];
        $referenceColumns = $referenceTable['columns'] ?? [];
        if (!is_array($referenceColumns)) {
            $referenceColumns = [];
        }

        foreach ($referenceColumns as $referenceColumn) {
            if (!is_array($referenceColumn)) {
                continue;
            }

            $columnName = (string) ($referenceColumn['name'] ?? '');
            $columnNameMap = app_generated_name_map_for_physical_name($columnName, 'php-property');
            $column = [
                'name' => $columnName,
                'physical_name' => $columnNameMap['physical_name'],
                'logical_name' => $columnNameMap['logical_name'],
                'generated_name' => $columnNameMap['generated_name'],
                'datatype' => (string) ($referenceColumn['datatype'] ?? ''),
                'is_null' => (string) ($referenceColumn['is_null'] ?? ''),
                'is_key' => (string) ($referenceColumn['is_key'] ?? ''),
                'is_default' => (string) ($referenceColumn['is_default'] ?? ''),
                'extra' => (string) ($referenceColumn['extra'] ?? ''),
                'column_list_order' => (int) ($referenceColumn['column_list_order'] ?? 0),
            ];
            if ($column['name'] === '') {
                continue;
            }

            $columns[] = $column;
            $columnsByName[$column['name']] = $column;
        }

        $tables[] = [
            'name' => $tableName,
            'physical_name' => $tableNameMap['physical_name'],
            'logical_name' => $tableNameMap['logical_name'],
            'generated_name' => $tableNameMap['generated_name'],
            'columns' => $columns,
            'columns_by_name' => $columnsByName,
        ];
    }

    return $tables;
}

function app_project_table_import_source_default_string(mixed $value): string
{
    if ($value === null) {
        return '';
    }

    return (string) $value;
}
