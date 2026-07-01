<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample30NoCodeAppLocalSyncDemoTest extends TestCase
{
    public function testNoCodeActionIntentCanHandoffToAppLocalSyncProcessing(): void
    {
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');

        try {
            $result = app_sample30_no_code_app_local_sync_demo_run(
                app_bootstrap(),
                'phpunit-sample30',
            );
        } finally {
            if ($previousPolicy === false) {
                putenv('MTOOL_GENERATED_NAME_POLICY');
            } else {
                putenv('MTOOL_GENERATED_NAME_POLICY=' . $previousPolicy);
            }
        }

        if (!$result['ok']) {
            fwrite(
                STDERR,
                json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            );
        }

        self::assertTrue(
            $result['ok'],
            is_string($result['error'] ?? null) && $result['error'] !== ''
                ? $result['error']
                : 'sample30 no-code App-local sync demo verification returned ok=false',
        );
        self::assertSame([], $result['assertion_errors']);
        self::assertSame('sync_task', $result['steps']['screen_definition']['contract_key'] ?? '');
        self::assertSame('update_sync_task', $result['steps']['screen_definition']['action_key'] ?? '');
        self::assertSame('managed-operation-sync-intent-v0', $result['steps']['dispatch']['result']['sync_intent']['intent_version'] ?? '');
        self::assertSame('done', $result['steps']['outbox_process']['outcome'] ?? '');
        self::assertSame('ready_for_sync', $result['steps']['local_read_after_sync']['dto']['status'] ?? '');
        self::assertSame('SyncTaskDBAccess', $result['steps']['server_binding']['binding']['dbaccess_class'] ?? '');
        self::assertSame('done', $result['steps']['server_outbox_process']['outcome'] ?? '');
        self::assertSame('Updatesync_task', $result['steps']['server_outbox_process']['handler_result']['method_name'] ?? '');
        self::assertSame('synced_to_server', $result['steps']['server_read_after_sync']['row']['status'] ?? '');
    }
}
