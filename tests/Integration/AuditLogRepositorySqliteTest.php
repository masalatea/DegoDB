<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/audit_log_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_source_output_operation_page.php';

use PHPUnit\Framework\TestCase;

final class AuditLogRepositorySqliteTest extends TestCase
{
    public function testAuditLogRepositoryCanAppendAndFilterWithSqliteConfigStore(): void
    {
        $app = $this->sqliteApp();
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);

        $append = app_audit_log_append($app, [
            'event_key' => 'audit_test_' . bin2hex(random_bytes(4)),
            'actor_login_id' => 'auditor@example.test',
            'actor_source' => 'phpunit',
            'project_key' => 'AUDIT_TEST',
            'event_type' => 'project_metadata_bundle.export',
            'target_type' => 'project',
            'target_key' => 'AUDIT_TEST',
            'result' => 'success',
            'message' => 'exported metadata bundle',
            'metadata' => [
                'scope' => 'project-core',
                'password' => 'do-not-store',
                'nested' => [
                    'api_token' => 'also-secret',
                    'count' => 1,
                ],
            ],
        ]);
        self::assertTrue($append['ok'], $append['error']);
        self::assertSame('auditor@example.test', $append['item']['actor_login_id'] ?? '');
        self::assertSame('[redacted]', $append['item']['metadata']['password'] ?? '');
        self::assertSame('[redacted]', $append['item']['metadata']['nested']['api_token'] ?? '');
        self::assertSame(1, $append['item']['metadata']['nested']['count'] ?? null);

        $latest = app_audit_log_fetch_latest($app, [
            'project_key' => 'AUDIT_TEST',
            'event_type' => 'project_metadata_bundle.export',
            'target_key' => 'AUDIT_TEST',
            'limit' => 10,
        ]);
        self::assertTrue($latest['ok'], $latest['error']);
        self::assertCount(1, $latest['items']);
        self::assertSame('project', $latest['items'][0]['target_type'] ?? '');

        $miss = app_audit_log_fetch_latest($app, [
            'project_key' => 'AUDIT_TEST',
            'event_type' => 'project_metadata_bundle.export',
            'target_key' => 'OTHER_TARGET',
            'limit' => 10,
        ]);
        self::assertTrue($miss['ok'], $miss['error']);
        self::assertSame([], $miss['items']);
    }

    public function testSourceOutputOperationBlockedResultAppendsAuditEvent(): void
    {
        $app = $this->sqliteApp();
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);

        $append = app_project_source_output_operation_append_audit_event($app, [
            'audit_event' => [
                'actor_login_id' => 'operator@example.test',
                'actor_source' => 'phpunit',
                'project_key' => 'MTOOL',
                'event_type' => 'mtool.source_output.artifact_review_requested',
                'target_type' => 'artifact',
                'target_key' => 'NO-CODE-RUNTIME',
                'result' => 'blocked',
                'message' => 'deferred_availability',
                'metadata' => [
                    'operation_key' => 'review_source_output_artifact',
                    'source_output_key' => 'NO-CODE-RUNTIME',
                    'artifact_key' => 'artifact-current',
                    'failure_code' => 'deferred_availability',
                ],
            ],
        ]);

        self::assertTrue($append['ok'], $append['error']);
        self::assertFalse($append['skipped']);
        self::assertSame('operator@example.test', $append['item']['actor_login_id'] ?? '');
        self::assertSame('blocked', $append['item']['result'] ?? '');
        self::assertSame('deferred_availability', $append['item']['message'] ?? '');
        self::assertSame(
            'review_source_output_artifact',
            $append['item']['metadata']['operation_key'] ?? '',
        );
    }

    public function testSourceOutputOperationAuditAppendFailureIsReturned(): void
    {
        $append = app_project_source_output_operation_append_audit_event([
            'site' => 'admin',
            'db' => ['driver' => 'sqlite', 'dsn' => 'sqlite:/path/that/does/not/exist/audit.sqlite'],
            'config_db' => ['driver' => 'sqlite', 'dsn' => 'sqlite:/path/that/does/not/exist/audit.sqlite'],
        ], [
            'audit_event' => [
                'actor_login_id' => 'operator@example.test',
                'actor_source' => 'phpunit',
                'project_key' => 'MTOOL',
                'event_type' => 'mtool.source_output.artifact_review_requested',
                'target_type' => 'artifact',
                'target_key' => 'NO-CODE-RUNTIME',
                'result' => 'blocked',
                'message' => 'deferred_availability',
                'metadata' => [
                    'operation_key' => 'review_source_output_artifact',
                    'source_output_key' => 'NO-CODE-RUNTIME',
                    'failure_code' => 'deferred_availability',
                ],
            ],
        ]);

        self::assertFalse($append['ok']);
        self::assertFalse($append['skipped']);
        self::assertSame([], $append['item']);
        self::assertNotSame('', $append['error']);
        self::assertSame('failed', app_project_source_output_operation_audit_append_status($append));
    }

    private function sqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-audit-log-sqlite-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $configDb = app_config_store_config(
            'sqlite',
            'db-config',
            '3306',
            'config_app',
            'config_app',
            'secret',
            '/var/www/work',
            $storeDir,
        );

        return [
            'site' => 'admin',
            'db' => $configDb,
            'config_db' => $configDb,
        ];
    }
}
