<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/mobile_wrapper_target.php';

final class MobileWrapperTargetTest extends TestCase
{
    public function testBuildsC1WrapperReadinessPackageFromValidatedHandoff(): void
    {
        $result = app_mobile_wrapper_target_build_c1_package($this->packet());

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame('', $result['error']);
        self::assertTrue($result['validation']['ready']);
        self::assertIsArray($result['package']);
        self::assertFalse($result['package']['mutation_performed']);
        self::assertArrayHasKey('wrapper-target-contract.json', $result['package']['files']);
        self::assertArrayHasKey('WRAPPER-CONSUMER-NOTES.md', $result['package']['files']);

        $contract = $result['package']['files']['wrapper-target-contract.json'];
        self::assertSame('mobile-react-wrapper-target-v1', $contract['contract_schema_version'] ?? '');
        self::assertSame('react_web_capacitor_ios_android', $contract['target_key'] ?? '');
        self::assertSame('C1_WRAPPER_READINESS', $contract['proof_stage'] ?? '');
        self::assertSame('mobile-app-handoff-v1', $contract['input_handoff_schema_version'] ?? '');
        self::assertFalse($contract['mutation_performed'] ?? true);
        self::assertTrue($contract['web_runtime']['react_bridge_available'] ?? false);
        self::assertSame('work/artifacts/SAMPLE28/react-bridge/bridge-contract.json', $contract['web_runtime']['react_bridge_ref'] ?? '');
        self::assertSame(3, $contract['screen_flow_boundary']['screen_count'] ?? 0);
        self::assertSame(1, $contract['action_boundary']['action_count'] ?? 0);
        self::assertContains('make sample28-no-code-react-bridge-build-smoke', $contract['verification']['gates'] ?? []);
    }

    public function testInvalidHandoffFailsBeforePackageBuild(): void
    {
        $packet = $this->packet();
        unset($packet['screens']);

        $result = app_mobile_wrapper_target_build_c1_package($packet);

        self::assertFalse($result['ok']);
        self::assertSame('mobile app handoff packet is not ready', $result['error']);
        self::assertNull($result['package']);
        self::assertFalse($result['validation']['ready']);
    }

    public function testC1ContractDoesNotClaimNativeBuildOwnership(): void
    {
        $result = app_mobile_wrapper_target_build_c1_package($this->packet());
        $contract = $result['package']['files']['wrapper-target-contract.json'];

        self::assertSame('C1 only', $contract['capacitor_boundary']['mtool_stage'] ?? '');
        self::assertSame('C2/C3', $contract['capacitor_boundary']['external_owner_stage'] ?? '');
        self::assertTrue($contract['capacitor_boundary']['mtool_does_not_initialize_capacitor_project'] ?? false);
        self::assertTrue($contract['capacitor_boundary']['mtool_does_not_build_native_targets'] ?? false);
        self::assertTrue($contract['capacitor_boundary']['mtool_does_not_manage_signing_or_store_submission'] ?? false);
    }

    public function testConsumerNotesCarryBoundaryGatesAndNonGoals(): void
    {
        $result = app_mobile_wrapper_target_build_c1_package($this->packet());
        $notes = $result['package']['files']['WRAPPER-CONSUMER-NOTES.md'];

        self::assertStringContainsString('# Mobile Wrapper Target Consumer Notes', $notes);
        self::assertStringContainsString('The external wrapper owner owns React app shell, Capacitor setup, native build, signing, device QA, and store distribution.', $notes);
        self::assertStringContainsString('`make sample28-no-code-react-bridge-browser-smoke`', $notes);
        self::assertStringContainsString('`app_store_signing`', $notes);
        self::assertStringContainsString('`production_user_data_in_packet`', $notes);
    }

    public function testEmitC1PackageWritesOnlyWrapperReadinessFiles(): void
    {
        $targetDir = $this->tempDir('mobile-wrapper-target');

        $result = app_mobile_wrapper_target_emit_c1_package($this->packet(), $targetDir);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame('', $result['error']);
        self::assertSame(['WRAPPER-CONSUMER-NOTES.md', 'wrapper-target-contract.json'], $result['files']);
        self::assertFileExists($targetDir . '/wrapper-target-contract.json');
        self::assertFileExists($targetDir . '/WRAPPER-CONSUMER-NOTES.md');
        self::assertFileDoesNotExist($targetDir . '/package.json');
        self::assertFileDoesNotExist($targetDir . '/capacitor.config.ts');
        self::assertFileDoesNotExist($targetDir . '/ios');
        self::assertFileDoesNotExist($targetDir . '/android');

        $contract = json_decode((string) file_get_contents($targetDir . '/wrapper-target-contract.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('mobile-react-wrapper-target-v1', $contract['contract_schema_version'] ?? '');
        self::assertSame('C1_WRAPPER_READINESS', $contract['proof_stage'] ?? '');
    }

    public function testEmitC1PackageRefusesToOverwriteExistingFiles(): void
    {
        $targetDir = $this->tempDir('mobile-wrapper-target-overwrite');
        file_put_contents($targetDir . '/wrapper-target-contract.json', '{}');

        $result = app_mobile_wrapper_target_emit_c1_package($this->packet(), $targetDir);

        self::assertFalse($result['ok']);
        self::assertStringContainsString('package file already exists', $result['error']);
    }

    public function testEmitC1PackageRefusesInvalidHandoffBeforeWriting(): void
    {
        $targetDir = sys_get_temp_dir() . '/mtool-mobile-wrapper-target-invalid-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $packet = $this->packet();
        $packet['mutation_performed'] = true;

        $result = app_mobile_wrapper_target_emit_c1_package($packet, $targetDir);

        self::assertFalse($result['ok']);
        self::assertSame('mobile app handoff packet is not ready', $result['error']);
        self::assertFileDoesNotExist($targetDir);
    }

    public function testSample28C1HandoffIsReadyAndReferencesExistingNoCodeArtifacts(): void
    {
        $handoff = app_mobile_wrapper_target_sample28_c1_handoff();

        $validation = app_mobile_app_handoff_validate($handoff);

        self::assertTrue($validation['ready'], json_encode($validation['blockers'], JSON_THROW_ON_ERROR));
        self::assertSame('SAMPLE28', $handoff['project']['project_key'] ?? '');
        self::assertSame('react_web_capacitor_ios_android', $handoff['platform_targets'][0]['target_key'] ?? '');
        self::assertSame(
            'work/source-outputs/SAMPLE28/NO-CODE-RUNTIME/runtime-preview.json',
            $handoff['source_artifacts']['no_code_runtime']['ref'] ?? '',
        );
        self::assertSame(
            'work/source-outputs/SAMPLE28/NO-CODE-REACT-BRIDGE/bridge-contract.json',
            $handoff['source_artifacts']['react_bridge']['ref'] ?? '',
        );
    }

    public function testSample28C1PackageEmitsReviewableArtifactWithoutNativeProjectFiles(): void
    {
        $targetDir = $this->tempDir('sample28-mobile-wrapper-target');

        $result = app_mobile_wrapper_target_emit_sample28_c1_package($targetDir);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame(['WRAPPER-CONSUMER-NOTES.md', 'wrapper-target-contract.json'], $result['files']);
        self::assertFileExists($targetDir . '/wrapper-target-contract.json');
        self::assertFileExists($targetDir . '/WRAPPER-CONSUMER-NOTES.md');
        self::assertFileDoesNotExist($targetDir . '/package.json');
        self::assertFileDoesNotExist($targetDir . '/capacitor.config.ts');
        self::assertFileDoesNotExist($targetDir . '/ios');
        self::assertFileDoesNotExist($targetDir . '/android');

        $contract = json_decode((string) file_get_contents($targetDir . '/wrapper-target-contract.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('mobile-react-wrapper-target-v1', $contract['contract_schema_version'] ?? '');
        self::assertSame('C1_WRAPPER_READINESS', $contract['proof_stage'] ?? '');
        self::assertSame(
            'work/source-outputs/SAMPLE28/NO-CODE-RUNTIME/runtime-preview.json',
            $contract['source_artifacts']['no_code_runtime']['ref'] ?? '',
        );
        self::assertTrue($contract['web_runtime']['react_bridge_available'] ?? false);
        self::assertSame('C1 only', $contract['capacitor_boundary']['mtool_stage'] ?? '');
    }

    /** @return array<string,mixed> */
    private function packet(): array
    {
        return [
            'schema_version' => 'mobile-app-handoff-v1',
            'mutation_performed' => false,
            'project' => [
                'project_key' => 'SAMPLE28',
                'name' => 'Sample28 No-Code Data App',
                'title' => 'Sample28 Mobile Wrapper Readiness',
            ],
            'source_artifacts' => [
                'openapi' => ['ref' => 'work/artifacts/SAMPLE28/openapi.json', 'sha256' => str_repeat('a', 64)],
                'no_code_runtime' => ['ref' => 'work/artifacts/SAMPLE28/no-code-runtime.json', 'sha256' => str_repeat('b', 64)],
                'screen_metadata' => ['ref' => 'work/artifacts/SAMPLE28/screen-definition.json', 'sha256' => str_repeat('c', 64)],
                'auth_policy' => ['ref' => 'work/artifacts/SAMPLE28/auth-policy.json', 'sha256' => str_repeat('d', 64)],
                'react_bridge' => ['ref' => 'work/artifacts/SAMPLE28/react-bridge/bridge-contract.json', 'sha256' => str_repeat('e', 64)],
            ],
            'platform_targets' => [
                ['target_key' => 'react_web_capacitor_ios_android', 'required_now' => true, 'role' => 'first proof target'],
                ['target_key' => 'pwa', 'required_now' => false, 'role' => 'optional shared web target'],
                ['target_key' => 'flutter_input_packet', 'required_now' => false, 'role' => 'later input packet'],
                ['target_key' => 'react_native_input_packet', 'required_now' => false, 'role' => 'later input packet'],
                ['target_key' => 'direct_native_generation', 'required_now' => false, 'role' => 'non-goal'],
            ],
            'app_identity' => [
                'display_name' => 'Sample28',
                'bundle_id_placeholder' => 'com.example.sample28',
                'package_id_placeholder' => 'com.example.sample28',
                'environment' => 'local-proof',
            ],
            'auth' => [
                'mode' => 'oidc',
                'login_route' => '/login',
                'logout_route' => '/logout',
                'token_storage_policy' => 'do_not_store_tokens_in_handoff_packet',
                'redirect_or_deep_link_policy' => 'external owner configures callback/deep link',
            ],
            'api' => [
                'base_url_policy' => 'runtime configurable per environment',
                'error_envelope' => 'standard JSON error envelope',
                'endpoints' => [
                    ['endpoint_key' => 'tickets.list', 'method' => 'GET', 'path' => '/api/tickets', 'response_ref' => '#/components/schemas/TicketList'],
                    ['endpoint_key' => 'tickets.update', 'method' => 'POST', 'path' => '/api/tickets/{id}', 'response_ref' => '#/components/schemas/TicketUpdateResult'],
                ],
            ],
            'screens' => [
                ['screen_key' => 'ticket_list', 'screen_type' => 'list', 'title' => 'Tickets', 'states' => ['loading', 'empty', 'error']],
                ['screen_key' => 'ticket_detail', 'screen_type' => 'detail', 'title' => 'Ticket Detail', 'states' => ['loading', 'not_found', 'error']],
                ['screen_key' => 'ticket_form', 'screen_type' => 'form', 'title' => 'Edit Ticket', 'states' => ['draft', 'submitting', 'validation_error', 'submitted']],
            ],
            'navigation' => [
                ['from' => 'ticket_list', 'to' => 'ticket_detail', 'trigger' => 'select_row'],
                ['from' => 'ticket_detail', 'to' => 'ticket_form', 'trigger' => 'edit'],
            ],
            'actions' => [
                [
                    'action_key' => 'update_ticket',
                    'kind' => 'submit',
                    'endpoint_key' => 'tickets.update',
                    'availability' => 'enabled_after_validation',
                    'safety' => 'safe_submit',
                    'mutates' => true,
                    'idempotency' => 'client_generated_request_key',
                    'success_state' => 'success',
                    'failure_state' => 'validation_failure',
                ],
            ],
            'validation' => [
                'field_rules' => [['field_key' => 'title', 'required' => true]],
                'action_rules' => [['action_key' => 'update_ticket', 'rule' => 'title_required']],
                'enforcement' => 'client displays server-authoritative validation errors',
            ],
            'error_states' => [
                ['state_key' => 'success', 'user_message' => 'Saved.'],
                ['state_key' => 'validation_failure', 'user_message' => 'Please fix the highlighted fields.'],
                ['state_key' => 'auth_failure', 'user_message' => 'Please sign in again.'],
                ['state_key' => 'network_failure', 'user_message' => 'Network unavailable. Try again.'],
                ['state_key' => 'unavailable_action', 'user_message' => 'This action is not available.'],
            ],
            'native_capabilities' => [
                ['capability_key' => 'none', 'required' => false, 'reason' => 'First wrapper proof uses web/API behavior only.'],
            ],
            'offline_and_local_storage' => [
                'offline_sync' => false,
                'local_draft_policy' => 'browser-local draft allowed for form only',
                'cache_policy' => 'short-lived endpoint cache only',
            ],
            'security_and_privacy' => [
                'secret_policy' => 'no secrets in packet',
                'pii_policy' => 'no production user data in packet',
                'token_persistence_policy' => 'builder must choose secure token storage',
                'logging_policy' => 'do not log tokens or personal data',
            ],
            'build_handoff' => [
                'owned_by' => 'external mobile builder',
                'capacitor_setup_owner' => 'external mobile builder',
                'ios_build_owner' => 'external mobile builder',
                'android_build_owner' => 'external mobile builder',
                'signing_owner' => 'app owner',
                'store_submission_owner' => 'app owner',
            ],
            'verification_checklist' => [
                'source artifact hashes match',
                'login flow understood',
                'list/detail/form routes mapped',
                'submit action validation mapped',
                'native capability declaration reviewed',
            ],
            'non_goals' => [
                'direct_native_generation',
                'app_store_signing',
                'offline_sync_by_default',
                'production_user_data_in_packet',
            ],
        ];
    }

    private function tempDir(string $name): string
    {
        $base = sys_get_temp_dir() . '/mtool-mobile-wrapper-target-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $path = $base . '/' . $name;
        if (!mkdir($path, 0777, true) && !is_dir($path)) {
            self::fail('failed to create temp dir: ' . $path);
        }
        return $path;
    }
}
