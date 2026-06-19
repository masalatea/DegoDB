<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample16AuthenticatedProxyTest extends TestCase
{
    public function testAuthenticatedProxyFailsClosedAndAcceptsMatchingBearerToken(): void
    {
        $result = app_sample16_auth_proxy_run(
            app_bootstrap(),
            'phpunit',
            app_sample16_auth_proxy_default_reference_root(),
        );

        self::assertTrue($result['ok'], $this->failureMessageFromResult($result));
        self::assertSame(
            'single-proxy-server',
            (string) ($result['steps']['source_output']['item']['artifact_strategy'] ?? ''),
        );

        $authCases = [];
        foreach ($result['steps']['auth_cases'] ?? [] as $case) {
            self::assertIsArray($case);
            $authCases[(string) ($case['case'] ?? '')] = $case;
        }

        self::assertFalse((bool) ($authCases['missing_authorization']['ok'] ?? true));
        self::assertFalse((bool) ($authCases['malformed_authorization']['ok'] ?? true));
        self::assertFalse((bool) ($authCases['missing_env']['ok'] ?? true));
        self::assertFalse((bool) ($authCases['wrong_token']['ok'] ?? true));
        self::assertTrue((bool) ($authCases['matching_token']['ok'] ?? false));

        $fileChecks = [];
        foreach ($result['steps']['output']['file_checks'] ?? [] as $fileCheck) {
            self::assertIsArray($fileCheck);
            $fileChecks[(string) ($fileCheck['relative_path'] ?? '')] = $fileCheck;
        }

        self::assertTrue((bool) ($fileChecks['_support/runtime_dbclasses/_support/mtool_runtime_db.php']['ok'] ?? false));
        self::assertTrue((bool) ($fileChecks['_support/runtime_dbclasses/base/dbaccess-AuthTaskBase.php']['ok'] ?? false));

        $dbAccessBasePath = (string) ($result['steps']['output']['published']['published_root'] ?? '')
            . '/_support/runtime_dbclasses/base/dbaccess-AuthTaskBase.php';
        self::assertFileExists($dbAccessBasePath);
        $dbAccessBase = file_get_contents($dbAccessBasePath);
        self::assertIsString($dbAccessBase);
        self::assertStringContainsString('$mtooldb->execute($last_sql_command_for_mtooldb, [', $dbAccessBase);
        self::assertStringContainsString('where `AuthTask`.`Id` = ?', $dbAccessBase);

        $autoloadPath = (string) ($result['steps']['output']['published']['published_root'] ?? '')
            . '/_support/runtime_dbclasses/autoload_proxy_runtime.php';
        self::assertFileExists($autoloadPath);
        $autoloadText = file_get_contents($autoloadPath);
        self::assertIsString($autoloadText);
        self::assertStringContainsString("require_once __DIR__ . '/_support/mtool_runtime_db.php';", $autoloadText);
        self::assertStringNotContainsString('function connect_mtooldb_if_not_yet(): void', $autoloadText);
    }

    private function failureMessageFromResult(array $result): string
    {
        $encoded = json_encode(
            [
                'error' => $result['error'] ?? '',
                'assertion_errors' => $result['assertion_errors'] ?? [],
                'table_import' => $result['steps']['table_import'] ?? null,
                'data_class_sync' => $result['steps']['data_class_sync'] ?? null,
                'source_output' => $result['steps']['source_output'] ?? null,
                'output' => $result['steps']['output'] ?? null,
                'auth_cases' => $result['steps']['auth_cases'] ?? null,
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        );

        return is_string($encoded) && $encoded !== ''
            ? $encoded
            : 'sample16 authenticated proxy verification returned ok=false';
    }
}
