<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';

use PHPUnit\Framework\TestCase;

final class ConfigDbExternalizationContractTest extends TestCase
{
    public function testComposeAndEnvExposeConfigDbOverrideLane(): void
    {
        $compose = $this->readRepoFile('compose.yaml');
        $localCompose = $this->readRepoFile('compose.local-db-config.yaml');
        $envExample = $this->readRepoFile('.env.example');
        $makefile = $this->readRepoFile('Makefile');

        self::assertStringContainsString('APP_DB_HOST: ${APP_CONFIG_DB_HOST:-db-config}', $compose);
        self::assertStringContainsString('APP_CONFIG_DB_HOST: ${APP_CONFIG_DB_HOST:-db-config}', $compose);
        self::assertStringContainsString('APP_CONFIG_DB_PORT: ${APP_CONFIG_DB_PORT:-3306}', $compose);
        self::assertStringContainsString('db-config:', $localCompose);
        self::assertStringContainsString('./docker/mariadb/config-initdb:/docker-entrypoint-initdb.d:ro', $localCompose);
        self::assertStringContainsString('APP_CONFIG_DB_NAME=', $envExample);
        self::assertStringContainsString('APP_CONFIG_DB_USER=', $envExample);
        self::assertStringContainsString('APP_CONFIG_DB_PASSWORD=', $envExample);
        self::assertStringContainsString('compose.local-db-config.yaml', $makefile);
        self::assertStringContainsString('up-external-config-db', $makefile);
        self::assertStringContainsString('down-external-config-db', $makefile);
        self::assertStringContainsString('ps-external-config-db', $makefile);
        self::assertStringContainsString('health-external-config-db', $makefile);
        self::assertStringContainsString('config-db-preflight', $makefile);
        self::assertStringContainsString('config-db-preflight-external-config-db', $makefile);
        self::assertStringContainsString('check_config_db_bootstrap.php', $makefile);
        self::assertStringContainsString('migrate_config_db.php', $makefile);
        self::assertStringContainsString('db-config-migrate-external-config-db', $makefile);
    }

    public function testComposeStartupLaneCanSkipLocalDbConfigService(): void
    {
        $compose = $this->readRepoFile('compose.yaml');
        $localCompose = $this->readRepoFile('compose.local-db-config.yaml');

        $webAdminDependsOn = $this->readComposeServiceFieldBlock($compose, 'web-admin', 'depends_on');
        $webLabDependsOn = $this->readComposeServiceFieldBlock($compose, 'web-lab', 'depends_on');

        self::assertStringNotContainsString("\n  db-config:\n", $compose);
        self::assertStringContainsString("\n  db-config:\n", $localCompose);
        self::assertSame('', $webAdminDependsOn);
        self::assertStringContainsString('db-lab:', $webLabDependsOn);
        self::assertStringNotContainsString('db-config:', $webLabDependsOn);
    }

    public function testAdminMetadataRoutingCanSplitDefaultDbFromConfigDb(): void
    {
        $app = $this->createMismatchedAdminApp();

        self::assertFalse(app_config_db_bootstrap_admin_db_matches_config_db($app));

        $pdo = app_create_metadata_pdo($app);
        $databaseName = $pdo->query('SELECT DATABASE()')->fetchColumn();

        self::assertSame('config_app', $databaseName);
    }

    public function testConfigDbPreflightAllowsAdminDbMismatchWhenSchemaIsCurrent(): void
    {
        $app = $this->createMismatchedAdminApp();

        $result = app_config_db_bootstrap_preflight($app);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame('admin', $result['target']['site']);
        self::assertFalse($result['target']['admin_db_matches_config_db']);
        self::assertSame('config_app', $result['summary']['resolved_database_name']);
        self::assertTrue($result['summary']['schema_current']);
        self::assertContains(
            app_config_db_bootstrap_admin_metadata_routing_warning(),
            $result['warnings'],
        );
    }

    public function testConfigDbPreflightPassesAgainstCurrentSchema(): void
    {
        $app = app_bootstrap();

        $result = app_config_db_bootstrap_preflight($app);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame('admin', $result['target']['site']);
        self::assertTrue($result['target']['admin_db_matches_config_db']);
        self::assertNotSame('', $result['summary']['version']);
        self::assertSame('config_app', $result['summary']['resolved_database_name']);
        self::assertGreaterThan(0, $result['summary']['sql_file_count']);
        self::assertSame([], $result['missing_tables']);
        self::assertSame([], $result['missing_columns']);
        self::assertSame([], $result['unexpected_legacy_columns']);
        self::assertTrue($result['summary']['schema_current']);
    }

    public function testFirebirdBootstrapConvertsCreateTableShape(): void
    {
        $statement = <<<SQL
CREATE TABLE IF NOT EXISTS projects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_key VARCHAR(64) NOT NULL,
    description TEXT NOT NULL,
    can_administer TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_projects_project_key (project_key),
    KEY idx_projects_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;

        $converted = app_config_db_bootstrap_firebird_convert_create_table_statement($statement);

        self::assertStringStartsWith("CREATE TABLE PROJECTS (\n", $converted);
        self::assertStringContainsString('ID BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL', $converted);
        self::assertStringContainsString('PROJECT_KEY VARCHAR(64) NOT NULL', $converted);
        self::assertStringContainsString('DESCRIPTION BLOB SUB_TYPE TEXT NOT NULL', $converted);
        self::assertStringContainsString('CAN_ADMINISTER SMALLINT DEFAULT 0 NOT NULL', $converted);
        self::assertStringContainsString('CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL', $converted);
        self::assertStringContainsString('UPDATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL', $converted);
        self::assertStringContainsString('PRIMARY KEY (ID)', $converted);
        self::assertStringNotContainsString('AUTO_INCREMENT', $converted);
        self::assertStringNotContainsString('UNSIGNED', $converted);
        self::assertStringNotContainsString('ENGINE=', $converted);
        self::assertStringNotContainsString('UNIQUE KEY', $converted);
    }

    public function testFirebirdBootstrapPreparesAlterAddColumnWithMetadataGuard(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $statement = "ALTER TABLE project_source_outputs\n"
            . "    ADD COLUMN IF NOT EXISTS spec_visibility VARCHAR(64) NOT NULL DEFAULT 'internal-only' AFTER target_binding_type";

        $prepared = app_config_db_bootstrap_firebird_prepare_statement($pdo, $statement);

        self::assertSame([
            "ALTER TABLE PROJECT_SOURCE_OUTPUTS ADD SPEC_VISIBILITY VARCHAR(64) DEFAULT 'internal-only' NOT NULL",
        ], $prepared);
    }

    public function testFirebirdBootstrapSkipsLegacyDropCleanupStatements(): void
    {
        $pdo = new PDO('sqlite::memory:');

        self::assertSame(
            [],
            app_config_db_bootstrap_firebird_prepare_statement($pdo, 'DROP INDEX IF EXISTS idx_legacy_source_pid'),
        );
        self::assertSame(
            [],
            app_config_db_bootstrap_firebird_prepare_statement(
                $pdo,
                'ALTER TABLE project_db_access_function_select_wheres DROP COLUMN IF EXISTS legacy_source_pid',
            ),
        );
    }

    public function testFirebirdBootstrapDoesNotDoubleConvertMediumText(): void
    {
        self::assertSame(
            'PARAMETER_VALUE BLOB SUB_TYPE TEXT NOT NULL',
            app_config_db_bootstrap_firebird_convert_column_definition('parameter_value MEDIUMTEXT NOT NULL'),
        );
    }

    public function testFirebirdBootstrapDropsExplicitNullableMarker(): void
    {
        self::assertSame(
            'CANDIDATE_REVISION_ID BIGINT',
            app_config_db_bootstrap_firebird_convert_column_definition('candidate_revision_id BIGINT UNSIGNED NULL'),
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function createMismatchedAdminApp(): array
    {
        $app = app_bootstrap();
        $app['site'] = 'admin';
        $app['db'] = [
            'host' => 'legacy-db-host',
            'port' => '3306',
            'name' => 'legacy_runtime',
            'user' => 'legacy_user',
            'password' => 'legacy_password',
            'dsn' => 'mysql:host=legacy-db-host;port=3306;dbname=legacy_runtime;charset=utf8mb4',
        ];

        return $app;
    }

    private function readRepoFile(string $relativePath): string
    {
        $absolutePath = dirname(__DIR__, 2) . '/' . $relativePath;
        self::assertFileExists($absolutePath, 'missing file: ' . $relativePath);

        $content = file_get_contents($absolutePath);
        self::assertIsString($content, 'failed to read: ' . $relativePath);

        return $content;
    }

    private function readComposeServiceFieldBlock(string $compose, string $serviceName, string $fieldName): string
    {
        $serviceBlock = $this->readComposeServiceBlock($compose, $serviceName);
        $lines = preg_split('/\R/', $serviceBlock);
        self::assertIsArray($lines);

        $capture = false;
        $captured = [];
        $fieldPrefix = '    ' . $fieldName . ':';

        foreach ($lines as $line) {
            if (!$capture) {
                if ($line === $fieldPrefix) {
                    $capture = true;
                }
                continue;
            }

            if ($line !== '' && strspn($line, ' ') < 6) {
                break;
            }

            $captured[] = $line;
        }

        return trim(implode("\n", $captured));
    }

    private function readComposeServiceBlock(string $compose, string $serviceName): string
    {
        $lines = preg_split('/\R/', $compose);
        self::assertIsArray($lines);

        $capture = false;
        $captured = [];
        $servicePrefix = '  ' . $serviceName . ':';

        foreach ($lines as $line) {
            if (!$capture) {
                if ($line === $servicePrefix) {
                    $capture = true;
                    $captured[] = $line;
                }
                continue;
            }

            if (preg_match('/^  [A-Za-z0-9][A-Za-z0-9_-]*:\s*$/', $line) === 1) {
                break;
            }

            $captured[] = $line;
        }

        self::assertNotSame([], $captured, 'missing compose service: ' . $serviceName);

        return implode("\n", $captured);
    }
}
