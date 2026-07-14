<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/sql_dialect.php';

use PHPUnit\Framework\TestCase;

final class SqlDialectTest extends TestCase
{
    public function testDialectIsDetectedFromDsn(): void
    {
        self::assertSame(
            'mysql',
            app_sql_dialect_from_dsn('mysql:host=db-config;port=3306;dbname=config_app;charset=utf8mb4'),
        );
        self::assertSame(
            'sqlite',
            app_sql_dialect_from_dsn('sqlite:/var/www/work/config-store/config.sqlite'),
        );
        self::assertSame(
            'pgsql',
            app_sql_dialect_from_dsn('pgsql:host=127.0.0.1;port=5432;dbname=lab_app'),
        );
        self::assertSame(
            'firebird',
            app_sql_dialect_from_dsn('firebird:dbname=user-db-firebird/3050:/var/lib/firebird/data/config_app.fdb;charset=UTF8'),
        );
        self::assertSame(
            'mysql',
            app_sql_dialect_from_dsn(''),
        );
    }

    public function testDialectIsDetectedFromDbConfig(): void
    {
        self::assertSame(
            'sqlite',
            app_sql_dialect_from_db_config([
                'dsn' => 'sqlite:/tmp/config.sqlite',
            ]),
        );
    }

    public function testDialectIsDetectedFromPdo(): void
    {
        $pdo = new PDO('sqlite::memory:');

        self::assertSame('sqlite', app_sql_dialect_from_pdo($pdo));
    }

    public function testDatetimeSelectExpressionKeepsCurrentMysqlShape(): void
    {
        self::assertSame(
            'DATE_FORMAT(p.updated_at, "%Y-%m-%d %H:%i:%s") AS updated_at',
            app_sql_datetime_select_expr('mysql', 'p.updated_at', 'updated_at'),
        );
    }

    public function testDatetimeSelectExpressionSupportsSqliteShape(): void
    {
        self::assertSame(
            "strftime('%Y-%m-%d %H:%M:%S', p.updated_at) AS updated_at",
            app_sql_datetime_select_expr('sqlite', 'p.updated_at', 'updated_at'),
        );
    }

    public function testDatetimeSelectExpressionSupportsPostgresqlShape(): void
    {
        self::assertSame(
            "to_char(p.updated_at, 'YYYY-MM-DD HH24:MI:SS') AS updated_at",
            app_sql_datetime_select_expr('pgsql', 'p.updated_at', 'updated_at'),
        );
    }

    public function testDatetimeSelectExpressionSupportsFirebirdShape(): void
    {
        self::assertSame(
            'CAST(p.updated_at AS VARCHAR(32)) AS updated_at',
            app_sql_datetime_select_expr('firebird', 'p.updated_at', 'updated_at'),
        );
    }

    public function testFirebirdIdentifierUsesQuotedShape(): void
    {
        self::assertSame('"project_source_output"', app_sql_identifier('firebird', 'project_source_output'));
    }

    public function testLimitClauseSupportsFirebirdRowsShape(): void
    {
        self::assertSame('LIMIT 1', app_sql_limit_clause('mysql', 1));
        self::assertSame('LIMIT 1', app_sql_limit_clause('sqlite', 0));
        self::assertSame('ROWS 5', app_sql_limit_clause('firebird', 5));
    }

    public function testRowKeyNormalizationSupportsUppercasePdoDrivers(): void
    {
        self::assertSame(
            [
                'event_key' => 'evt_1',
                0 => 'first-column',
            ],
            app_sql_normalize_row_keys([
                'EVENT_KEY' => 'evt_1',
                0 => 'first-column',
            ]),
        );
    }

    public function testPostgresqlIdentifierKeepsUnquotedShape(): void
    {
        self::assertSame('project_source_output', app_sql_identifier('pgsql', 'project_source_output'));
    }

    public function testSqliteTableExistsHelper(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE database_sources (id INTEGER PRIMARY KEY, source_key TEXT)');

        self::assertTrue(app_sql_table_exists($pdo, 'database_sources'));
        self::assertFalse(app_sql_table_exists($pdo, 'missing_table'));
        self::assertFalse(app_sql_table_exists($pdo, ''));

        self::assertTrue(app_sql_column_exists($pdo, 'database_sources', 'source_key'));
        self::assertFalse(app_sql_column_exists($pdo, 'database_sources', 'missing_column'));
        self::assertFalse(app_sql_column_exists($pdo, 'missing_table', 'source_key'));
        self::assertFalse(app_sql_column_exists($pdo, '', 'source_key'));
        self::assertFalse(app_sql_column_exists($pdo, 'database_sources', ''));
    }

    public function testSqliteVersionAndDatabaseNameHelpers(): void
    {
        $pdo = new PDO('sqlite::memory:');

        self::assertNotSame('', app_sql_server_version($pdo));
        self::assertSame('main', app_sql_current_database_name($pdo));
    }
}
