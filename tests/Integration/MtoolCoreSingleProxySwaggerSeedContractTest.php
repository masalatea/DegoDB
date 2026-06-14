<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MtoolCoreSingleProxySwaggerSeedContractTest extends TestCase
{
    public function testCoreSeedContainsGenericSingleProxyAndOpenApiSourceOutputs(): void
    {
        $seedPath = dirname(__DIR__, 2) . '/mtool/docker/mariadb/config-seed/034_single_proxy_swagger_source_output_seed.sql';
        $contents = file_get_contents($seedPath);

        self::assertIsString($contents, 'failed to read: ' . $seedPath);
        self::assertSame(2, substr_count($contents, 'INSERT IGNORE INTO project_source_outputs'));
        self::assertStringContainsString("'DBTABLE-PROXY-SERVER'", $contents);
        self::assertStringContainsString("'OPENAPI-JSON'", $contents);
        self::assertStringContainsString("'single-proxy-server'", $contents);
        self::assertStringContainsString("'openapi-json'", $contents);
        self::assertStringContainsString("'single-function-proxy'", $contents);
        self::assertStringContainsString("'http://127.0.0.1:8082/runs/proxy/MTOOL/DBTABLE-PROXY-SERVER'", $contents);
        self::assertStringContainsString("    41,", $contents);
        self::assertStringContainsString("    42,", $contents);
    }

    public function testCoreSeedBackfillsDbtableTargetsToGenericSwaggerOutputs(): void
    {
        $seedPath = dirname(__DIR__, 2) . '/mtool/docker/mariadb/config-seed/034_single_proxy_swagger_source_output_seed.sql';
        $contents = file_get_contents($seedPath);

        self::assertIsString($contents, 'failed to read: ' . $seedPath);
        self::assertStringContainsString(
            'INSERT IGNORE INTO project_db_access_function_source_output_targets',
            $contents,
        );
        self::assertStringContainsString("c.source_name = 'dbtable'", $contents);
        self::assertStringContainsString("'GetdbtableList'", $contents);
        self::assertStringContainsString("'GetdbtableByName'", $contents);
        self::assertStringContainsString("SELECT 'DBTABLE-PROXY-SERVER' AS source_output_key", $contents);
        self::assertStringContainsString("SELECT 'OPENAPI-JSON'", $contents);
    }
}
