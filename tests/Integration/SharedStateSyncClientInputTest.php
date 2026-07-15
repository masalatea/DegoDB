<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/shared_state_sync_client_input.php';
require_once dirname(__DIR__, 2) . '/mtool/scripts/create_shared_state_sync_client_input.php';

final class SharedStateSyncClientInputTest extends TestCase
{
    public function testBuildsClientInputPacketWithoutSourceOrSdkClaim(): void
    {
        $packet = app_shared_state_sync_client_input_build([
            'project_key' => 'SAMPLE37',
            'api_base_url_env' => 'SAMPLE_BACKEND_URL',
        ]);

        self::assertSame(APP_SHARED_STATE_SYNC_CLIENT_INPUT_SCHEMA_VERSION, $packet['schema_version']);
        self::assertSame('shared_state_sync_client_input', $packet['bundle_manifest_key']);
        self::assertSame('SAMPLE37', $packet['project']['project_key']);
        self::assertSame('external_app_client_owner', $packet['client']['ownership']);
        self::assertFalse($packet['client']['source_generation']);
        self::assertFalse($packet['client']['sdk_generation']);
        self::assertSame('SAMPLE_BACKEND_URL', $packet['backend']['api_base_url_env']);
        self::assertTrue($packet['backend']['auth']['do_not_store_tokens_in_packet']);
        self::assertSame('do_not_persist_after_join', $packet['room_flow']['join_by_invite']['raw_invite_token_storage']);
        self::assertTrue($packet['state_flow']['update_state']['expected_revision_required']);
        self::assertSame('websocket', $packet['realtime_flow']['primary_transport']);
        self::assertFalse($packet['fallbacks']['polling']['realtime_claim_allowed']);
        self::assertFalse($packet['reconnect']['event_replay_required']);
        self::assertContains('generate_client_sdk', $packet['forbidden_actions']);
        self::assertContains('generate_react_source', $packet['forbidden_actions']);
        self::assertContains('choose_token_storage', $packet['forbidden_actions']);
        self::assertSame([], app_shared_state_sync_client_input_contract_errors($packet));
    }

    public function testContractErrorsRejectSourceSdkAndTokenBoundaryDrift(): void
    {
        $packet = app_shared_state_sync_client_input_build();
        $packet['client']['source_generation'] = true;
        $packet['client']['sdk_generation'] = true;
        $packet['backend']['auth']['do_not_store_tokens_in_packet'] = false;
        $packet['room_flow']['join_by_invite']['raw_invite_token_storage'] = 'persist';
        $packet['forbidden_actions'] = [];
        $packet['mutation_performed'] = true;

        self::assertSame(
            [
                'source_generation',
                'sdk_generation',
                'token_in_packet_boundary',
                'raw_invite_token_storage',
                'forbidden_generate_client_sdk',
                'forbidden_generate_react_source',
                'forbidden_install_dependencies',
                'forbidden_choose_token_storage',
                'forbidden_enable_offline_sync',
                'forbidden_claim_realtime_when_polling',
                'mutation_performed',
            ],
            app_shared_state_sync_client_input_contract_errors($packet),
        );
    }

    public function testEmitWritesOnlyClientInputFilesAndRefusesOverwrite(): void
    {
        $targetDir = $this->tempDir('sync-client-input');

        $result = app_shared_state_sync_client_input_emit(['project_key' => 'SAMPLE37'], $targetDir);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame(['SYNC-CLIENT-INPUT.md', 'sync-client-input.json'], $result['files']);
        self::assertFileExists($targetDir . '/sync-client-input.json');
        self::assertFileExists($targetDir . '/SYNC-CLIENT-INPUT.md');
        self::assertFileDoesNotExist($targetDir . '/package.json');
        self::assertFileDoesNotExist($targetDir . '/src');
        self::assertFileDoesNotExist($targetDir . '/node_modules');

        $packet = json_decode((string) file_get_contents($targetDir . '/sync-client-input.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('SAMPLE37', $packet['project']['project_key']);
        self::assertSame([], app_shared_state_sync_client_input_contract_errors($packet));

        $second = app_shared_state_sync_client_input_emit(['project_key' => 'SAMPLE37'], $targetDir);
        self::assertFalse($second['ok']);
        self::assertStringContainsString('package file already exists', $second['error']);
    }

    public function testCliParserAndEmitter(): void
    {
        $targetDir = $this->tempDir('cli-sync-client-input');
        $parsed = app_cli_shared_state_sync_client_input_parse_args([
            'create_shared_state_sync_client_input.php',
            '--project-key=SAMPLE37',
            '--api-base-url-env=SAMPLE_BACKEND_URL',
            '--target-dir=' . $targetDir,
        ]);

        self::assertTrue($parsed['ok'], $parsed['error']);
        self::assertFalse($parsed['help']);
        self::assertSame('SAMPLE37', $parsed['project_key']);
        self::assertSame('SAMPLE_BACKEND_URL', $parsed['api_base_url_env']);

        $result = app_cli_shared_state_sync_client_input_emit_from_parsed($parsed);
        self::assertTrue($result['ok'], $result['error']);
        self::assertSame(['SYNC-CLIENT-INPUT.md', 'sync-client-input.json'], $result['files']);
    }

    public function testCliParserRejectsMissingTargetDir(): void
    {
        $parsed = app_cli_shared_state_sync_client_input_parse_args([
            'create_shared_state_sync_client_input.php',
            '--project-key=SAMPLE37',
        ]);

        self::assertFalse($parsed['ok']);
        self::assertSame('valid --target-dir is required', $parsed['error']);
    }

    private function tempDir(string $name): string
    {
        $base = sys_get_temp_dir() . '/mtool-shared-state-sync-client-input-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $path = $base . '/' . $name;
        if (!mkdir($path, 0777, true) && !is_dir($path)) {
            self::fail('failed to create temp dir: ' . $path);
        }
        return $path;
    }
}
