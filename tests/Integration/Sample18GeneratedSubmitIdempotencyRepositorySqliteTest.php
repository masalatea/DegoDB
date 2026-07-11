<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/lab_sample18_generated_submit_idempotency_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/lab_sample18_task_board_page.php';

use PHPUnit\Framework\TestCase;

final class Sample18GeneratedSubmitIdempotencyRepositorySqliteTest extends TestCase
{
    public function testGeneratedSubmitIdempotencyRecordCanBeCreatedAndReused(): void
    {
        $app = $this->sqliteApp();
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);

        $input = $this->recordInput();
        $create = app_lab_sample18_generated_submit_idempotency_create_or_reuse_record($app, $input);
        self::assertTrue($create['ok'], $create['error']);
        self::assertTrue($create['created']);
        self::assertSame('recorded', $create['result']);
        self::assertSame($input['dedupe_key'], $create['item']['dedupe_key'] ?? '');
        self::assertSame('SAMPLE18', $create['item']['project_key'] ?? '');
        self::assertSame('create_task_card', $create['item']['operation_key'] ?? '');
        self::assertSame('blocked', $create['item']['result'] ?? '');
        self::assertSame('generated_submit_disabled', $create['item']['failure_code'] ?? '');
        self::assertSame('audit_sample18_first', $create['item']['first_audit_event_key'] ?? '');
        self::assertSame(0, $create['item']['duplicate_count'] ?? -1);
        self::assertSame(
            $input['metadata']['dispatcher_bound_fields'] ?? [],
            $create['item']['metadata']['dispatcher_bound_fields'] ?? [],
        );

        $duplicate = app_lab_sample18_generated_submit_idempotency_create_or_reuse_record($app, $input);
        self::assertTrue($duplicate['ok'], $duplicate['error']);
        self::assertFalse($duplicate['created']);
        self::assertSame('duplicate', $duplicate['result']);
        self::assertSame($input['dedupe_key'], $duplicate['item']['dedupe_key'] ?? '');
        self::assertSame(1, $duplicate['item']['duplicate_count'] ?? -1);

        $latest = app_lab_sample18_generated_submit_idempotency_fetch_latest_records($app, [
            'project_key' => 'SAMPLE18',
            'operation_key' => 'create_task_card',
            'limit' => 10,
        ]);
        self::assertTrue($latest['ok'], $latest['error']);
        self::assertCount(1, $latest['items']);
        self::assertSame($input['dedupe_key'], $latest['items'][0]['dedupe_key'] ?? '');
    }

    public function testGeneratedSubmitIdempotencyRecordCreatesNewRowForDifferentDedupeKey(): void
    {
        $app = $this->sqliteApp();
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);

        $firstInput = $this->recordInput([
            'dedupe_key' => 'sample18.generated_submit.create_task_card.first',
            'payload_fingerprint' => str_repeat('a', 64),
        ]);
        $first = app_lab_sample18_generated_submit_idempotency_create_or_reuse_record($app, $firstInput);
        self::assertTrue($first['ok'], $first['error']);
        self::assertTrue($first['created']);

        $secondInput = $this->recordInput([
            'dedupe_key' => 'sample18.generated_submit.create_task_card.second',
            'payload_fingerprint' => str_repeat('b', 64),
        ]);
        $second = app_lab_sample18_generated_submit_idempotency_create_or_reuse_record($app, $secondInput);
        self::assertTrue($second['ok'], $second['error']);
        self::assertTrue($second['created']);
        self::assertNotSame($first['item']['dedupe_key'] ?? '', $second['item']['dedupe_key'] ?? '');

        $latest = app_lab_sample18_generated_submit_idempotency_fetch_latest_records($app, [
            'project_key' => 'SAMPLE18',
            'operation_key' => 'create_task_card',
            'limit' => 10,
        ]);
        self::assertTrue($latest['ok'], $latest['error']);
        self::assertCount(2, $latest['items']);
    }

    public function testGeneratedSubmitIdempotencyRepositoryRejectsInvalidInputWithoutCreatingRows(): void
    {
        $app = $this->sqliteApp();
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);

        $missingDedupe = app_lab_sample18_generated_submit_idempotency_create_or_reuse_record(
            $app,
            $this->recordInput(['dedupe_key' => " \t "]),
        );
        self::assertFalse($missingDedupe['ok']);
        self::assertSame('failed', $missingDedupe['result']);
        self::assertStringContainsString('dedupe_key', $missingDedupe['error']);

        $invalidMetadata = app_lab_sample18_generated_submit_idempotency_create_or_reuse_record(
            $app,
            $this->recordInput(['metadata' => 'not-an-array']),
        );
        self::assertFalse($invalidMetadata['ok']);
        self::assertSame('failed', $invalidMetadata['result']);
        self::assertStringContainsString('metadata must be an array', $invalidMetadata['error']);

        $unsupportedResult = app_lab_sample18_generated_submit_idempotency_create_or_reuse_record(
            $app,
            $this->recordInput(['result' => 'accepted']),
        );
        self::assertFalse($unsupportedResult['ok']);
        self::assertSame('failed', $unsupportedResult['result']);
        self::assertStringContainsString('result is not supported', $unsupportedResult['error']);

        $latest = app_lab_sample18_generated_submit_idempotency_fetch_latest_records($app, [
            'limit' => 10,
        ]);
        self::assertTrue($latest['ok'], $latest['error']);
        self::assertSame([], $latest['items']);
    }

    public function testGeneratedSubmitIdempotencyRepositoryFailureIsReturned(): void
    {
        $result = app_lab_sample18_generated_submit_idempotency_create_or_reuse_record([
            'site' => 'lab',
            'db' => ['driver' => 'sqlite', 'dsn' => 'sqlite:/path/that/does/not/exist/sample18-idempotency.sqlite'],
            'config_db' => ['driver' => 'sqlite', 'dsn' => 'sqlite:/path/that/does/not/exist/sample18-idempotency.sqlite'],
        ], $this->recordInput());

        self::assertFalse($result['ok']);
        self::assertFalse($result['created']);
        self::assertSame('failed', $result['result']);
        self::assertSame([], $result['item']);
        self::assertNotSame('', $result['error']);
    }

    /**
     * @param array<string,mixed> $overrides
     * @return array<string,mixed>
     */
    private function recordInput(array $overrides = []): array
    {
        $normalized = app_lab_sample18_task_board_normalize_generated_submit_request(
            'create_task_card',
            [
                'title' => 'Idempotency repository test',
                'body' => 'Blocked generated submit should be deduped.',
                'assigned_to' => 'PHPUnit',
                'priority' => '12',
                'due_date' => '2026-07-10',
            ],
            '2026-07-10 04:00:00',
        );
        self::assertTrue($normalized['ok']);
        $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);
        $preview = app_lab_sample18_task_board_generated_submit_idempotency_audit_preview(
            $normalized,
            $dispatcher,
            'blocked',
            'generated_submit_disabled',
        );

        return array_merge([
            'dedupe_key' => $preview['dedupe_key_preview'] ?? '',
            'project_key' => 'SAMPLE18',
            'operation_key' => 'create_task_card',
            'payload_fingerprint' => $preview['payload_fingerprint'] ?? '',
            'result' => 'blocked',
            'failure_code' => 'generated_submit_disabled',
            'first_audit_event_key' => 'audit_sample18_first',
            'metadata' => [
                'route_boundary' => 'sample18_generated_submit',
                'normalized_payload' => $normalized['payload'],
                'dispatcher_bound_fields' => $dispatcher['bound_fields'] ?? [],
            ],
        ], $overrides);
    }

    /**
     * @return array<string,mixed>
     */
    private function sqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-sample18-idempotency-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
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
            'site' => 'lab',
            'db' => $configDb,
            'config_db' => $configDb,
        ];
    }
}
