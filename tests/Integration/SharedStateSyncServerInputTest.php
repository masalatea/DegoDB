<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/shared_state_sync_server_input.php';
require_once dirname(__DIR__, 2) . '/mtool/scripts/create_shared_state_sync_server_input.php';

final class SharedStateSyncServerInputTest extends TestCase
{
    public function testBuildsExternalNodeServerInputPacketWithoutRuntimeClaim(): void
    {
        $packet = app_shared_state_sync_server_input_build([
            'project_key' => 'SAMPLE36',
            'backend_base_url_env' => 'SAMPLE_BACKEND_URL',
        ]);

        self::assertSame(APP_SHARED_STATE_SYNC_SERVER_INPUT_SCHEMA_VERSION, $packet['schema_version']);
        self::assertSame('shared_state_sync_server_input', $packet['bundle_manifest_key']);
        self::assertSame('SAMPLE36', $packet['project']['project_key']);
        self::assertSame('nodejs', $packet['server']['runtime']);
        self::assertSame('external_runtime_owner', $packet['server']['ownership']);
        self::assertFalse($packet['server']['production_runtime_generated']);
        self::assertSame('app_backend', $packet['backend_integration']['authority']);
        self::assertSame('SAMPLE_BACKEND_URL', $packet['backend_integration']['base_url_env']);
        self::assertFalse($packet['backend_integration']['auth_context']['sso_token_broadcast_allowed']);
        self::assertSame('/sync/ws', $packet['routes']['websocket']['path']);
        self::assertTrue($packet['routes']['sse']['server_to_client_only']);
        self::assertSame('active_membership_required', $packet['auth']['room_authorization']);
        self::assertFalse($packet['rooms']['cross_room_broadcast_allowed']);
        self::assertFalse($packet['events']['replay_required']);
        self::assertFalse($packet['fallbacks']['polling']['realtime_claim_allowed']);
        self::assertContains('install_node_dependencies', $packet['forbidden_actions']);
        self::assertContains('start_production_server', $packet['forbidden_actions']);
        self::assertContains('open_public_port', $packet['forbidden_actions']);
        self::assertSame([], app_shared_state_sync_server_input_contract_errors($packet));
    }

    public function testContractErrorsRejectRuntimeAndSecurityBoundaryDrift(): void
    {
        $packet = app_shared_state_sync_server_input_build();
        $packet['server']['production_runtime_generated'] = true;
        $packet['backend_integration']['auth_context']['sso_token_broadcast_allowed'] = true;
        $packet['rooms']['cross_room_broadcast_allowed'] = true;
        $packet['forbidden_actions'] = [];
        $packet['mutation_performed'] = true;

        self::assertSame(
            [
                'production_runtime_generated',
                'sso_token_broadcast',
                'cross_room_broadcast',
                'forbidden_install_node_dependencies',
                'forbidden_start_production_server',
                'forbidden_open_public_port',
                'forbidden_broadcast_sso_token',
                'forbidden_claim_crdt_or_game_loop_support',
                'mutation_performed',
            ],
            app_shared_state_sync_server_input_contract_errors($packet),
        );
    }

    public function testEmitWritesOnlyInputPacketFilesAndRefusesOverwrite(): void
    {
        $targetDir = $this->tempDir('sync-server-input');

        $result = app_shared_state_sync_server_input_emit(['project_key' => 'SAMPLE36'], $targetDir);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame(['SYNC-SERVER-INPUT.md', 'sync-server-input.json'], $result['files']);
        self::assertFileExists($targetDir . '/sync-server-input.json');
        self::assertFileExists($targetDir . '/SYNC-SERVER-INPUT.md');
        self::assertFileDoesNotExist($targetDir . '/package.json');
        self::assertFileDoesNotExist($targetDir . '/server.js');
        self::assertFileDoesNotExist($targetDir . '/node_modules');

        $packet = json_decode((string) file_get_contents($targetDir . '/sync-server-input.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('SAMPLE36', $packet['project']['project_key']);
        self::assertSame([], app_shared_state_sync_server_input_contract_errors($packet));

        $second = app_shared_state_sync_server_input_emit(['project_key' => 'SAMPLE36'], $targetDir);
        self::assertFalse($second['ok']);
        self::assertStringContainsString('package file already exists', $second['error']);
    }

    public function testCliParserAndEmitter(): void
    {
        $targetDir = $this->tempDir('cli-sync-server-input');
        $parsed = app_cli_shared_state_sync_server_input_parse_args([
            'create_shared_state_sync_server_input.php',
            '--project-key=SAMPLE36',
            '--backend-base-url-env=SAMPLE_BACKEND_URL',
            '--target-dir=' . $targetDir,
        ]);

        self::assertTrue($parsed['ok'], $parsed['error']);
        self::assertFalse($parsed['help']);
        self::assertSame('SAMPLE36', $parsed['project_key']);
        self::assertSame('SAMPLE_BACKEND_URL', $parsed['backend_base_url_env']);

        $result = app_cli_shared_state_sync_server_input_emit_from_parsed($parsed);
        self::assertTrue($result['ok'], $result['error']);
        self::assertSame(['SYNC-SERVER-INPUT.md', 'sync-server-input.json'], $result['files']);
    }

    public function testCliParserRejectsMissingTargetDir(): void
    {
        $parsed = app_cli_shared_state_sync_server_input_parse_args([
            'create_shared_state_sync_server_input.php',
            '--project-key=SAMPLE36',
        ]);

        self::assertFalse($parsed['ok']);
        self::assertSame('valid --target-dir is required', $parsed['error']);
    }

    private function tempDir(string $name): string
    {
        $base = sys_get_temp_dir() . '/mtool-shared-state-sync-server-input-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $path = $base . '/' . $name;
        if (!mkdir($path, 0777, true) && !is_dir($path)) {
            self::fail('failed to create temp dir: ' . $path);
        }
        return $path;
    }
}
