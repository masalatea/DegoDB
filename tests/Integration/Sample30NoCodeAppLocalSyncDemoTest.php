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
        self::assertSame('sample30-editor-subject', $result['steps']['app_local_identity']['identity']['subject'] ?? '');
        self::assertSame('sample30-local-device', $result['steps']['app_local_identity']['identity']['device_id'] ?? '');
        self::assertTrue($result['steps']['app_local_identity']['credentials_excluded'] ?? false);
        self::assertSame('managed-operation-sync-intent-v0', $result['steps']['dispatch']['result']['sync_intent']['intent_version'] ?? '');
        self::assertSame('sample30-editor-subject', $result['steps']['dispatch']['result']['sync_intent']['actor']['subject'] ?? '');
        self::assertSame('done', $result['steps']['outbox_process']['outcome'] ?? '');
        self::assertSame('ready_for_sync', $result['steps']['local_read_after_sync']['dto']['status'] ?? '');
        self::assertSame('processed', $result['steps']['sync_handoff_visibility']['app_local']['handoff_state'] ?? '');
        self::assertSame('dirty', $result['steps']['sync_handoff_visibility']['app_local']['row_sync_status'] ?? '');
        self::assertSame('SyncTaskDBAccess', $result['steps']['server_binding']['binding']['dbaccess_class'] ?? '');
        self::assertSame('done', $result['steps']['server_outbox_process']['outcome'] ?? '');
        self::assertSame('Updatesync_task', $result['steps']['server_outbox_process']['handler_result']['method_name'] ?? '');
        self::assertSame('synced_to_server', $result['steps']['server_read_after_sync']['row']['status'] ?? '');
        self::assertSame('processed', $result['steps']['sync_handoff_visibility']['server']['handoff_state'] ?? '');
        self::assertSame('sample30-editor-subject', $result['steps']['sync_handoff_visibility']['server']['actor_subject'] ?? '');
        self::assertSame(
            $result['steps']['app_local_identity']['local_user_id'] ?? '',
            $result['steps']['sync_handoff_visibility']['server']['actor_local_user_id'] ?? '',
        );
        self::assertTrue($result['steps']['sync_handoff_visibility']['server']['title_preserved'] ?? false);
        self::assertTrue($result['steps']['sync_handoff_visibility']['runtime_artifact']['list_sync_status_hint'] ?? false);
        self::assertTrue($result['steps']['sync_handoff_visibility']['runtime_artifact']['detail_sync_status_hint'] ?? false);
        self::assertFalse($result['steps']['sync_handoff_visibility']['runtime_artifact']['form_sync_status_hint'] ?? true);
        self::assertSame('failed', $result['steps']['sync_error_state_process']['outcome'] ?? '');
        self::assertSame('failed', $result['steps']['sync_error_state_process']['item']['status'] ?? '');
        self::assertSame(1, $result['steps']['sync_error_state_process']['item']['attempts'] ?? 0);
        self::assertSame(
            'sample30 deterministic sync failure for visibility.',
            $result['steps']['sync_error_state_process']['item']['last_error'] ?? '',
        );
        self::assertSame('failed', $result['steps']['sync_handoff_visibility']['error_state']['handoff_state'] ?? '');
    }
}
