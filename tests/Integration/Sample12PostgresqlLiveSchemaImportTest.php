<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/database.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_table_import_source.php';

use PHPUnit\Framework\TestCase;

final class Sample12PostgresqlLiveSchemaImportTest extends TestCase
{
    public function testPostgresqlLiveSchemaImportKeepsPhysicalLogicalAndGeneratedNames(): void
    {
        $dbConfig = $this->pgsqlConfigOrSkip();
        $pdo = app_create_pdo_from_db_config($dbConfig);
        $this->prepareExternalArticleFixture($pdo);

        $result = app_project_table_import_source_named_live_schema(
            [
                'config_db' => [
                    'name' => '__not_the_pg_live_schema__',
                ],
                'database_sources' => [
                    'sample12_pgsql_lab' => [
                        'key' => 'sample12_pgsql_lab',
                        'label' => 'Sample12 PostgreSQL lab DB',
                        'description' => 'Sample12 PostgreSQL live schema import contract source.',
                        'source_of_truth' => 'test',
                        'db_config_key' => 'sample12_pgsql_lab',
                        'supports_live_schema_import' => true,
                        'supports_proxy_runtime_read' => false,
                        'proxy_runtime_priority' => 500,
                        'is_canonical_store' => false,
                        'host' => $dbConfig['host'],
                        'port' => $dbConfig['port'],
                        'name' => $dbConfig['name'],
                        'user' => $dbConfig['user'],
                        'password' => $dbConfig['password'],
                        'dsn' => $dbConfig['dsn'],
                    ],
                ],
            ],
            'SAMPLE12',
            [
                'key' => 'named-live-source:sample12_pgsql_lab',
                'label' => 'Sample12 PostgreSQL lab DB',
                'description' => 'Sample12 PostgreSQL live schema import contract source.',
                'database_source_key' => 'sample12_pgsql_lab',
                'apply_supported' => true,
            ],
        );

        self::assertTrue($result['ok'], $result['error']);

        $tablesByPhysicalName = [];
        foreach ($result['tables'] as $table) {
            $tablesByPhysicalName[(string) ($table['physical_name'] ?? $table['name'] ?? '')] = $table;
        }

        self::assertArrayHasKey('external_article', $tablesByPhysicalName);
        $table = $tablesByPhysicalName['external_article'];
        self::assertSame('external_article', $table['physical_name']);
        self::assertSame('ExternalArticle', $table['logical_name']);
        self::assertSame('ExternalArticle', $table['generated_name']);

        $columnsByPhysicalName = [];
        foreach ($table['columns'] as $column) {
            $columnsByPhysicalName[(string) ($column['physical_name'] ?? $column['name'] ?? '')] = $column;
        }

        self::assertSame('id', $columnsByPhysicalName['id']['physical_name']);
        self::assertSame('Id', $columnsByPhysicalName['id']['logical_name']);
        self::assertSame('id', $columnsByPhysicalName['id']['generated_name']);
        self::assertSame('PRI', $columnsByPhysicalName['id']['is_key']);
        self::assertSame('auto_increment', $columnsByPhysicalName['id']['extra']);

        self::assertSame('published_at', $columnsByPhysicalName['published_at']['physical_name']);
        self::assertSame('PublishedAt', $columnsByPhysicalName['published_at']['logical_name']);
        self::assertSame('publishedAt', $columnsByPhysicalName['published_at']['generated_name']);
        self::assertSame('timestamp without time zone', $columnsByPhysicalName['published_at']['datatype']);

        self::assertContains('external_article', $result['managed_target_table_names']);
    }

    /**
     * @return array{host:string,port:string,name:string,user:string,password:string,dsn:string}
     */
    private function pgsqlConfigOrSkip(): array
    {
        $dsn = trim((string) getenv('MTOOL_RUNTIME_PGSQL_DSN'));
        if ($dsn === '') {
            self::markTestSkipped('MTOOL_RUNTIME_PGSQL_DSN is not set.');
        }

        $user = (string) (getenv('MTOOL_RUNTIME_PGSQL_USER') ?: getenv('MTOOL_RUNTIME_DB_USER') ?: 'lab_app');
        $password = (string) (getenv('MTOOL_RUNTIME_PGSQL_PASSWORD') ?: getenv('MTOOL_RUNTIME_DB_PASSWORD') ?: '');

        return [
            'host' => (string) (getenv('MTOOL_RUNTIME_PGSQL_HOST') ?: getenv('MTOOL_RUNTIME_DB_HOST') ?: '127.0.0.1'),
            'port' => (string) (getenv('MTOOL_RUNTIME_PGSQL_PORT') ?: getenv('MTOOL_RUNTIME_DB_PORT') ?: '5432'),
            'name' => (string) (getenv('MTOOL_RUNTIME_PGSQL_DB') ?: getenv('MTOOL_RUNTIME_DB_NAME') ?: 'lab_app'),
            'user' => $user,
            'password' => $password,
            'dsn' => $dsn,
        ];
    }

    private function prepareExternalArticleFixture(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS external_article');
        $pdo->exec(
            "CREATE TABLE external_article (
                id BIGINT GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(191) NOT NULL UNIQUE,
                status VARCHAR(32) NOT NULL DEFAULT 'draft',
                published_at TIMESTAMP NULL,
                body TEXT NOT NULL
            )",
        );
    }
}
