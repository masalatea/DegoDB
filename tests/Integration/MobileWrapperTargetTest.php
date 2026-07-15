<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/mobile_wrapper_target.php';
require_once dirname(__DIR__, 2) . '/mtool/app/mobile_wrapper_artifact_page.php';
require_once dirname(__DIR__, 2) . '/mtool/app/router.php';
require_once dirname(__DIR__, 2) . '/mtool/scripts/create_mobile_wrapper_target.php';

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

    public function testBuildsReactWrapperAppHandoffProofWithoutNativeProjectOwnership(): void
    {
        $result = app_mobile_wrapper_target_build_react_app_handoff_proof($this->packet());

        self::assertTrue($result['ok'], $result['error']);
        self::assertIsArray($result['package']);
        self::assertFalse($result['package']['mutation_performed']);
        self::assertArrayHasKey('react-wrapper-app-handoff.json', $result['package']['files']);
        self::assertArrayHasKey('REACT-WRAPPER-APP-HANDOFF.md', $result['package']['files']);

        $proof = $result['package']['files']['react-wrapper-app-handoff.json'];
        self::assertSame('mobile-react-wrapper-app-handoff-v1', $proof['schema_version'] ?? '');
        self::assertSame('react_web_capacitor_ios_android', $proof['target_key'] ?? '');
        self::assertSame('MW2_REACT_WRAPPER_APP_HANDOFF', $proof['proof_stage'] ?? '');
        self::assertFalse($proof['mutation_performed'] ?? true);
        self::assertTrue($proof['react_app_boundary']['not_a_generated_production_app'] ?? false);
        self::assertTrue($proof['capacitor_preparation_boundary']['mtool_does_not_create_native_project_files'] ?? false);
        self::assertContains('flutter_input_packet', $proof['later_targets'] ?? []);
        self::assertContains('react_native_input_packet', $proof['later_targets'] ?? []);
        self::assertContains('make sample28-no-code-react-bridge-build-smoke', $proof['verification']['required_before_capacitor'] ?? []);
    }

    public function testBuildsExternalOptionalOutputPacketWithoutReplacingMtoolNoCode(): void
    {
        $result = app_mobile_wrapper_target_build_external_optional_output_packet($this->packet());

        self::assertTrue($result['ok'], $result['error']);
        self::assertIsArray($result['package']);
        self::assertFalse($result['package']['mutation_performed']);
        self::assertArrayHasKey('external-output.json', $result['package']['files']);
        self::assertArrayHasKey('EXTERNAL-OUTPUT.md', $result['package']['files']);

        $packet = $result['package']['files']['external-output.json'];
        self::assertSame('mobile-external-optional-output-v1', $packet['schema_version'] ?? '');
        self::assertSame('external_no_code', $packet['mode'] ?? '');
        self::assertSame('react_web_capacitor', $packet['target'] ?? '');
        self::assertTrue($packet['baseline']['keeps_mtool_no_code'] ?? false);
        self::assertFalse($packet['baseline']['replacement_claim'] ?? true);
        self::assertSame('Mtool/server-owned', $packet['server_authority']['authorization'] ?? '');
        self::assertContains('React app shell', $packet['ownership_boundary']['external_custom_extension_owned'] ?? []);
        self::assertContains('initialize Capacitor', $packet['requires_user_confirmation'] ?? []);
        self::assertContains('automatic dependency installation', $packet['forbidden_without_artifact'] ?? []);
        self::assertContains('replace_mtool_no_code_runtime', $packet['non_goals'] ?? []);
    }

    public function testSample28ExternalOptionalOutputEmitsOnlyPacketFiles(): void
    {
        $targetDir = $this->tempDir('sample28-external-output');

        $result = app_mobile_wrapper_target_emit_sample28_external_optional_output_packet($targetDir);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame(['EXTERNAL-OUTPUT.md', 'external-output.json'], $result['files']);
        self::assertFileExists($targetDir . '/external-output.json');
        self::assertFileExists($targetDir . '/EXTERNAL-OUTPUT.md');
        self::assertFileDoesNotExist($targetDir . '/package.json');
        self::assertFileDoesNotExist($targetDir . '/capacitor.config.ts');
        self::assertFileDoesNotExist($targetDir . '/ios');
        self::assertFileDoesNotExist($targetDir . '/android');
    }

    public function testBuildsExternalAiTaskPacketForConfirmationDrivenAppCreation(): void
    {
        $result = app_mobile_wrapper_target_build_external_ai_task_packet($this->packet());

        self::assertTrue($result['ok'], $result['error']);
        self::assertIsArray($result['package']);
        self::assertFalse($result['package']['mutation_performed']);
        self::assertArrayHasKey('task.json', $result['package']['files']);
        self::assertArrayHasKey('TASK.md', $result['package']['files']);
        self::assertArrayHasKey('input/external-output.json', $result['package']['files']);
        self::assertArrayHasKey('input/mobile-app-handoff.json', $result['package']['files']);

        $task = $result['package']['files']['task.json'];
        self::assertSame('mobile-external-ai-task-packet-v1', $task['task_version'] ?? '');
        self::assertSame('pending_user_confirmation', $task['state'] ?? '');
        self::assertSame('external_no_code_app_builder_task', $task['operation'] ?? '');
        self::assertSame('react_web_capacitor', $task['target'] ?? '');
        self::assertTrue($task['confirmation']['required'] ?? false);
        self::assertSame([], $task['allowed_writes_before_confirmation'] ?? ['not-empty']);
        self::assertContains('dependency_install', $task['prohibitions_without_explicit_user_confirmation'] ?? []);
        self::assertContains('capacitor_init', $task['prohibitions_without_explicit_user_confirmation'] ?? []);
        self::assertContains('database_write', $task['prohibitions_without_explicit_user_confirmation'] ?? []);
        self::assertTrue($task['agent_instructions']['keep_mtool_no_code_as_baseline'] ?? false);
        self::assertTrue($task['agent_instructions']['server_authority_remains_mtool_owned'] ?? false);
        self::assertSame('mtool_external_output_contract', $task['inputs']['external_output']['authority'] ?? '');

        $markdown = $result['package']['files']['TASK.md'];
        self::assertStringContainsString('Do not continue until the user answers affirmatively', $markdown);
        self::assertStringContainsString('external_no_code` is optional and additive', $markdown);
        self::assertStringContainsString('`cap_sync`', $markdown);
    }

    public function testSample28ExternalAiTaskPacketEmitsOnlyTaskFiles(): void
    {
        $targetDir = $this->tempDir('sample28-ai-task-packet');

        $result = app_mobile_wrapper_target_emit_sample28_external_ai_task_packet($targetDir);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame(['TASK.md', 'input/external-output.json', 'input/mobile-app-handoff.json', 'task.json'], $result['files']);
        self::assertFileExists($targetDir . '/task.json');
        self::assertFileExists($targetDir . '/TASK.md');
        self::assertFileExists($targetDir . '/input/external-output.json');
        self::assertFileExists($targetDir . '/input/mobile-app-handoff.json');
        self::assertFileDoesNotExist($targetDir . '/package.json');
        self::assertFileDoesNotExist($targetDir . '/capacitor.config.ts');
        self::assertFileDoesNotExist($targetDir . '/ios');
        self::assertFileDoesNotExist($targetDir . '/android');
    }

    public function testBuildsOutputModeConfigForHybridMode(): void
    {
        $result = app_mobile_wrapper_target_build_output_mode_config($this->packet(), 'hybrid');

        self::assertTrue($result['ok'], $result['error']);
        self::assertIsArray($result['package']);
        self::assertArrayHasKey('output-mode-config.json', $result['package']['files']);
        self::assertArrayHasKey('OUTPUT-MODE-CONFIG.md', $result['package']['files']);

        $config = $result['package']['files']['output-mode-config.json'];
        self::assertSame('mobile-output-mode-config-v1', $config['schema_version'] ?? '');
        self::assertSame('hybrid', $config['selected_mode'] ?? '');
        self::assertSame(['mtool_no_code', 'external_no_code', 'hybrid'], $config['supported_modes'] ?? []);
        self::assertSame(['pwa', 'flutter_webview', 'react_web_capacitor'], $config['supported_app_surfaces'] ?? []);
        self::assertContains('c1_wrapper_readiness', $config['selected_artifact_keys'] ?? []);
        self::assertContains('external_optional_output', $config['selected_artifact_keys'] ?? []);
        self::assertContains('ai_task_packet', $config['selected_artifact_keys'] ?? []);
        self::assertContains('pwa_readiness', $config['selected_artifact_keys'] ?? []);
        self::assertSame('mobile-app-surface-config-v1', $config['app_surface_config']['schema_version'] ?? '');
        self::assertSame(['pwa', 'flutter_webview', 'react_web_capacitor'], $config['app_surface_config']['selected_surfaces'] ?? []);
        self::assertSame('shared_by_default', $config['app_surface_config']['backend_endpoint']['sharing_policy'] ?? '');
        self::assertSame('runtime configurable per environment', $config['app_surface_config']['backend_endpoint']['api_base_url_policy'] ?? '');
        self::assertTrue($config['app_surface_config']['backend_endpoint']['server_authority'] ?? false);
        self::assertTrue($config['app_surface_config']['surfaces']['pwa']['enabled'] ?? false);
        self::assertTrue($config['app_surface_config']['surfaces']['flutter_webview']['enabled'] ?? false);
        self::assertSame('same_app_url', $config['app_surface_config']['surfaces']['flutter_webview']['default_source_mode'] ?? '');
        self::assertSame('disabled_by_default', $config['app_surface_config']['surfaces']['flutter_webview']['native_bridge'] ?? '');
        self::assertContains('separate_endpoint_allowed_only_with_explicit_reason', array_keys($config['app_surface_config']['backend_endpoint'] ?? []));
        self::assertContains('external_outputs_are_additive_to_mtool_no_code', $config['warnings'] ?? []);
        self::assertContains('pwa_and_flutter_webview_share_backend_by_default_but_not_runtime_behavior', $config['warnings'] ?? []);
        self::assertContains('cap_sync', $config['forbidden_without_explicit_confirmation'] ?? []);

        $markdown = $result['package']['files']['OUTPUT-MODE-CONFIG.md'];
        self::assertStringContainsString('Selected mode: `hybrid`', $markdown);
        self::assertStringContainsString('`ai_task_packet`', $markdown);
        self::assertStringContainsString('`flutter_webview`', $markdown);
    }

    public function testOutputModeConfigCanSelectExternalNoCodeOnly(): void
    {
        $result = app_mobile_wrapper_target_build_output_mode_config($this->packet(), 'external_no_code');

        self::assertTrue($result['ok'], $result['error']);
        $config = $result['package']['files']['output-mode-config.json'];
        self::assertSame('external_no_code', $config['selected_mode'] ?? '');
        self::assertSame(['external_optional_output', 'ai_task_packet', 'pwa_readiness'], $config['selected_artifact_keys'] ?? []);
        self::assertSame(['pwa', 'flutter_webview', 'react_web_capacitor'], $config['app_surface_config']['selected_surfaces'] ?? []);
    }

    public function testSample28OutputModeConfigEmitsOnlyConfigFiles(): void
    {
        $targetDir = $this->tempDir('sample28-output-mode-config');

        $result = app_mobile_wrapper_target_emit_sample28_output_mode_config($targetDir, 'external_no_code');

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame(['OUTPUT-MODE-CONFIG.md', 'output-mode-config.json'], $result['files']);
        self::assertFileExists($targetDir . '/output-mode-config.json');
        self::assertFileExists($targetDir . '/OUTPUT-MODE-CONFIG.md');
        self::assertFileDoesNotExist($targetDir . '/package.json');
        self::assertFileDoesNotExist($targetDir . '/capacitor.config.ts');
        self::assertFileDoesNotExist($targetDir . '/ios');
        self::assertFileDoesNotExist($targetDir . '/android');

        $config = json_decode((string) file_get_contents($targetDir . '/output-mode-config.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('external_no_code', $config['selected_mode'] ?? '');
    }

    public function testBuildsPwaReadinessMetadataWithoutGeneratingPwaFiles(): void
    {
        $result = app_mobile_wrapper_target_build_pwa_readiness($this->packet());

        self::assertTrue($result['ok'], $result['error']);
        self::assertIsArray($result['package']);
        self::assertArrayHasKey('pwa-readiness.json', $result['package']['files']);
        self::assertArrayHasKey('PWA-READINESS.md', $result['package']['files']);

        $packet = $result['package']['files']['pwa-readiness.json'];
        self::assertSame('mobile-pwa-readiness-v1', $packet['schema_version'] ?? '');
        self::assertSame('pwa_static_cache_only', $packet['recommended_mode'] ?? '');
        self::assertContains('pwa_installable_online_only', $packet['readiness_modes'] ?? []);
        self::assertFalse($packet['service_worker_policy']['generated_by_mtool'] ?? true);
        self::assertSame('not_cacheable', $packet['service_worker_policy']['api_cache_default'] ?? '');
        self::assertFalse($packet['storage_policy']['browser_persistent_token_storage_allowed'] ?? true);
        self::assertFalse($packet['offline_policy']['offline_sync_default'] ?? true);
        self::assertTrue($packet['offline_policy']['sync_contract_required_for_offline_data'] ?? false);
        self::assertContains('service_worker_generation', $packet['forbidden_without_explicit_artifact'] ?? []);
        self::assertContains('offline_sync', $packet['forbidden_without_explicit_artifact'] ?? []);

        $markdown = $result['package']['files']['PWA-READINESS.md'];
        self::assertStringContainsString('Recommended mode: `pwa_static_cache_only`', $markdown);
        self::assertStringContainsString('`offline_sync`', $markdown);
    }

    public function testSample28PwaReadinessEmitsOnlyReadinessFiles(): void
    {
        $targetDir = $this->tempDir('sample28-pwa-readiness');

        $result = app_mobile_wrapper_target_emit_sample28_pwa_readiness($targetDir);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame(['PWA-READINESS.md', 'pwa-readiness.json'], $result['files']);
        self::assertFileExists($targetDir . '/pwa-readiness.json');
        self::assertFileExists($targetDir . '/PWA-READINESS.md');
        self::assertFileDoesNotExist($targetDir . '/manifest.webmanifest');
        self::assertFileDoesNotExist($targetDir . '/service-worker.js');
        self::assertFileDoesNotExist($targetDir . '/package.json');
        self::assertFileDoesNotExist($targetDir . '/ios');
        self::assertFileDoesNotExist($targetDir . '/android');
    }

    public function testSample28ReactWrapperAppHandoffEmitsOnlyProofFiles(): void
    {
        $targetDir = $this->tempDir('sample28-react-wrapper-app-handoff');

        $result = app_mobile_wrapper_target_emit_sample28_react_app_handoff_proof($targetDir);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame(['REACT-WRAPPER-APP-HANDOFF.md', 'react-wrapper-app-handoff.json'], $result['files']);
        self::assertFileExists($targetDir . '/react-wrapper-app-handoff.json');
        self::assertFileExists($targetDir . '/REACT-WRAPPER-APP-HANDOFF.md');
        self::assertFileDoesNotExist($targetDir . '/package.json');
        self::assertFileDoesNotExist($targetDir . '/capacitor.config.ts');
        self::assertFileDoesNotExist($targetDir . '/ios');
        self::assertFileDoesNotExist($targetDir . '/android');
    }

    public function testCliParserAcceptsSample28TargetDirectory(): void
    {
        $result = app_cli_mobile_wrapper_target_parse_args([
            'create_mobile_wrapper_target.php',
            '--sample=sample28',
            '--target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/mobile-wrapper-target',
        ]);

        self::assertTrue($result['ok'], $result['error']);
        self::assertFalse($result['help']);
        self::assertSame('sample28', $result['sample']);
        self::assertSame('', $result['handoff_file']);
        self::assertSame('', $result['project_key']);
        self::assertSame('', $result['source_output_key']);
        self::assertSame('c1', $result['artifact']);
        self::assertSame(
            'work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/mobile-wrapper-target',
            $result['target_dir'],
        );
    }

    public function testCliParserAcceptsReactWrapperAppArtifact(): void
    {
        $result = app_cli_mobile_wrapper_target_parse_args([
            'create_mobile_wrapper_target.php',
            '--sample=sample28',
            '--artifact=react-wrapper-app',
            '--target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/react-wrapper-app-handoff',
        ]);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame('react-wrapper-app', $result['artifact']);
        self::assertSame(
            'work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/react-wrapper-app-handoff',
            $result['target_dir'],
        );
    }

    public function testCliParserAcceptsExternalOutputArtifact(): void
    {
        $result = app_cli_mobile_wrapper_target_parse_args([
            'create_mobile_wrapper_target.php',
            '--sample=sample28',
            '--artifact=external-output',
            '--target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/react-web-capacitor-output',
        ]);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame('external-output', $result['artifact']);
        self::assertSame(
            'work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/react-web-capacitor-output',
            $result['target_dir'],
        );
    }

    public function testCliParserAcceptsAiTaskPacketArtifact(): void
    {
        $result = app_cli_mobile_wrapper_target_parse_args([
            'create_mobile_wrapper_target.php',
            '--sample=sample28',
            '--artifact=ai-task-packet',
            '--target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/ai-task-packet',
        ]);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame('ai-task-packet', $result['artifact']);
        self::assertSame(
            'work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/ai-task-packet',
            $result['target_dir'],
        );
    }

    public function testCliParserAcceptsOutputModeConfigArtifactAndMode(): void
    {
        $result = app_cli_mobile_wrapper_target_parse_args([
            'create_mobile_wrapper_target.php',
            '--sample=sample28',
            '--artifact=output-mode-config',
            '--output-mode=external_no_code',
            '--target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/output-mode-config',
        ]);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame('output-mode-config', $result['artifact']);
        self::assertSame('external_no_code', $result['output_mode']);
        self::assertSame(
            'work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/output-mode-config',
            $result['target_dir'],
        );
    }

    public function testCliParserAcceptsPwaReadinessArtifact(): void
    {
        $result = app_cli_mobile_wrapper_target_parse_args([
            'create_mobile_wrapper_target.php',
            '--sample=sample28',
            '--artifact=pwa-readiness',
            '--target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/pwa-readiness',
        ]);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame('pwa-readiness', $result['artifact']);
        self::assertSame(
            'work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/pwa-readiness',
            $result['target_dir'],
        );
    }

    public function testCliParserRejectsUnknownOutputMode(): void
    {
        $result = app_cli_mobile_wrapper_target_parse_args([
            'create_mobile_wrapper_target.php',
            '--sample=sample28',
            '--artifact=output-mode-config',
            '--output-mode=native_magic',
            '--target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/output-mode-config',
        ]);

        self::assertFalse($result['ok']);
        self::assertSame('supported --output-mode values are mtool_no_code, external_no_code, and hybrid', $result['error']);
    }

    public function testCliParserAcceptsHandoffFileInsteadOfSample(): void
    {
        $result = app_cli_mobile_wrapper_target_parse_args([
            'create_mobile_wrapper_target.php',
            '--handoff-file=work/mobile-app-handoff.json',
            '--artifact=react-wrapper-app',
            '--target-dir=work/mobile-wrapper-target/react-wrapper-app-handoff',
        ]);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame('', $result['sample']);
        self::assertSame('work/mobile-app-handoff.json', $result['handoff_file']);
        self::assertSame('react-wrapper-app', $result['artifact']);
    }

    public function testCliParserRejectsSampleAndHandoffFileTogether(): void
    {
        $result = app_cli_mobile_wrapper_target_parse_args([
            'create_mobile_wrapper_target.php',
            '--sample=sample28',
            '--handoff-file=work/mobile-app-handoff.json',
            '--target-dir=work/mobile-wrapper-target',
        ]);

        self::assertFalse($result['ok']);
        self::assertSame('specify exactly one source: --sample=sample28, --handoff-file=PATH, or --project-key=KEY --source-output-key=KEY', $result['error']);
    }

    public function testCliCanEmitReactWrapperAppHandoffFromHandoffFile(): void
    {
        $root = $this->tempDir('generic-handoff-file-root');
        $handoffPath = $root . '/mobile-app-handoff.json';
        file_put_contents(
            $handoffPath,
            json_encode(app_mobile_wrapper_target_sample28_c1_handoff(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n",
        );
        $targetDir = $root . '/react-wrapper-app-handoff';

        $parsed = app_cli_mobile_wrapper_target_parse_args([
            'create_mobile_wrapper_target.php',
            '--handoff-file=' . $handoffPath,
            '--artifact=react-wrapper-app',
            '--target-dir=' . $targetDir,
        ]);
        self::assertTrue($parsed['ok'], $parsed['error']);

        $result = app_cli_mobile_wrapper_target_emit_from_parsed($parsed);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame('handoff-file', $result['source']);
        self::assertSame(['REACT-WRAPPER-APP-HANDOFF.md', 'react-wrapper-app-handoff.json'], $result['files']);
        self::assertFileExists($targetDir . '/react-wrapper-app-handoff.json');
    }

    public function testCliCanEmitBundleManifestFromProjectSourceOutputLookup(): void
    {
        $root = $this->tempDir('project-source-output-root');
        $handoffDir = $root . '/PROJECT1/MOBILE-HANDOFF';
        self::assertTrue(mkdir($handoffDir, 0777, true));
        file_put_contents(
            $handoffDir . '/mobile-app-handoff.json',
            json_encode(app_mobile_wrapper_target_sample28_c1_handoff(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n",
        );
        $targetDir = $root . '/PROJECT1/MOBILE-WRAPPER-BUNDLE';

        $parsed = app_cli_mobile_wrapper_target_parse_args([
            'create_mobile_wrapper_target.php',
            '--project-key=PROJECT1',
            '--source-output-key=MOBILE-HANDOFF',
            '--source-output-root=' . $root,
            '--artifact=bundle-manifest',
            '--target-dir=' . $targetDir,
        ]);
        self::assertTrue($parsed['ok'], $parsed['error']);

        $result = app_cli_mobile_wrapper_target_emit_from_parsed($parsed);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame('project-source-output', $result['source']);
        self::assertSame(['MOBILE-WRAPPER-BUNDLE.md', 'mobile-wrapper-bundle-manifest.json'], $result['files']);
        self::assertFileExists($targetDir . '/mobile-wrapper-bundle-manifest.json');
    }

    public function testBuildsLaterPlatformInputPacketsWithoutOwningPlatformProjects(): void
    {
        $result = app_mobile_wrapper_target_build_later_platform_input_packets($this->packet());

        self::assertTrue($result['ok'], $result['error']);
        self::assertIsArray($result['package']);
        self::assertFalse($result['package']['mutation_performed']);
        self::assertArrayHasKey('flutter-input-packet.json', $result['package']['files']);
        self::assertArrayHasKey('react-native-input-packet.json', $result['package']['files']);
        self::assertArrayHasKey('LATER-PLATFORM-INPUT-PACKETS.md', $result['package']['files']);

        $flutter = $result['package']['files']['flutter-input-packet.json'];
        $reactNative = $result['package']['files']['react-native-input-packet.json'];
        self::assertSame('mobile-later-platform-input-packet-v1', $flutter['schema_version'] ?? '');
        self::assertSame('flutter_input_packet', $flutter['platform_key'] ?? '');
        self::assertSame('react_native_input_packet', $reactNative['platform_key'] ?? '');
        self::assertSame('structured_input_packet_only', $flutter['mtool_role'] ?? '');
        self::assertContains('Dart source code', $flutter['not_generated_by_mtool'] ?? []);
        self::assertContains('React Native source code', $reactNative['not_generated_by_mtool'] ?? []);
        self::assertSame('react-native-extension-v1', $reactNative['react_native_extension']['extension_version'] ?? '');
        self::assertSame('metadata_only', $reactNative['react_native_extension']['scope'] ?? '');
        self::assertSame('external_owner_choice', $reactNative['react_native_extension']['navigation']['library_selection'] ?? '');
        self::assertTrue($reactNative['react_native_extension']['api_client']['idempotency_for_mutations_required'] ?? false);
        self::assertFalse($reactNative['react_native_extension']['secure_storage']['browser_like_persistent_token_storage_allowed'] ?? true);
        self::assertSame('must_be_chosen_by_external_owner', $reactNative['react_native_extension']['native_modules']['expo_vs_bare_boundary'] ?? '');
        self::assertContains('react_native_project_init', $reactNative['react_native_extension']['forbidden_without_explicit_confirmation'] ?? []);
        self::assertContains('ios_android_project_write', $reactNative['react_native_extension']['forbidden_without_explicit_confirmation'] ?? []);
    }

    public function testSample28LaterPlatformInputPacketsEmitOnlyPacketFiles(): void
    {
        $targetDir = $this->tempDir('sample28-later-platform-input-packets');

        $result = app_mobile_wrapper_target_emit_sample28_later_platform_input_packets($targetDir);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame(
            ['LATER-PLATFORM-INPUT-PACKETS.md', 'flutter-input-packet.json', 'react-native-input-packet.json'],
            $result['files'],
        );
        self::assertFileExists($targetDir . '/flutter-input-packet.json');
        self::assertFileExists($targetDir . '/react-native-input-packet.json');
        self::assertFileDoesNotExist($targetDir . '/lib/main.dart');
        self::assertFileDoesNotExist($targetDir . '/package.json');
        self::assertFileDoesNotExist($targetDir . '/ios');
        self::assertFileDoesNotExist($targetDir . '/android');
    }

    public function testBuildsBundleManifestWithoutGeneratingProjects(): void
    {
        $result = app_mobile_wrapper_target_build_bundle_manifest($this->packet());

        self::assertTrue($result['ok'], $result['error']);
        self::assertIsArray($result['package']);
        self::assertArrayHasKey('mobile-wrapper-bundle-manifest.json', $result['package']['files']);
        self::assertArrayHasKey('MOBILE-WRAPPER-BUNDLE.md', $result['package']['files']);

        $manifest = $result['package']['files']['mobile-wrapper-bundle-manifest.json'];
        self::assertSame('mobile-wrapper-bundle-manifest-v1', $manifest['schema_version'] ?? '');
        self::assertSame(
            ['c1_wrapper_readiness', 'react_wrapper_app_handoff', 'external_optional_output', 'ai_task_packet', 'output_mode_config', 'pwa_readiness', 'later_platform_input_packets'],
            $manifest['artifact_order'] ?? [],
        );
        self::assertSame('external_optional_output', $manifest['artifacts'][2]['artifact_key'] ?? '');
        self::assertContains('external-output.json', $manifest['artifacts'][2]['files'] ?? []);
        self::assertSame('ai_task_packet', $manifest['artifacts'][3]['artifact_key'] ?? '');
        self::assertContains('task.json', $manifest['artifacts'][3]['files'] ?? []);
        self::assertContains('input/external-output.json', $manifest['artifacts'][3]['files'] ?? []);
        self::assertSame('output_mode_config', $manifest['artifacts'][4]['artifact_key'] ?? '');
        self::assertContains('output-mode-config.json', $manifest['artifacts'][4]['files'] ?? []);
        self::assertSame('pwa_readiness', $manifest['artifacts'][5]['artifact_key'] ?? '');
        self::assertContains('pwa-readiness.json', $manifest['artifacts'][5]['files'] ?? []);
        self::assertContains('Flutter project', $manifest['not_generated_by_mtool'] ?? []);
        self::assertContains('React Native project', $manifest['not_generated_by_mtool'] ?? []);
        self::assertContains('Capacitor project', $manifest['not_generated_by_mtool'] ?? []);
    }

    public function testSample28BundleManifestEmitsOnlyManifestFiles(): void
    {
        $targetDir = $this->tempDir('sample28-mobile-wrapper-bundle');

        $result = app_mobile_wrapper_target_emit_sample28_bundle_manifest($targetDir);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame(['MOBILE-WRAPPER-BUNDLE.md', 'mobile-wrapper-bundle-manifest.json'], $result['files']);
        self::assertFileExists($targetDir . '/mobile-wrapper-bundle-manifest.json');
        self::assertFileExists($targetDir . '/MOBILE-WRAPPER-BUNDLE.md');
        self::assertFileDoesNotExist($targetDir . '/package.json');
        self::assertFileDoesNotExist($targetDir . '/capacitor.config.ts');
        self::assertFileDoesNotExist($targetDir . '/ios');
        self::assertFileDoesNotExist($targetDir . '/android');
    }

    public function testMobileWrapperArtifactPageContractDocumentsReadOnlyBoundary(): void
    {
        $contract = app_mobile_wrapper_artifact_page_contract();

        self::assertSame('mobile-wrapper-artifact-page-v1', $contract['contract_version'] ?? '');
        self::assertFalse($contract['route_candidate']['mutation_performed'] ?? true);
        self::assertContains('bundle-manifest', $contract['supported_artifacts'] ?? []);
        self::assertContains('native project creation', $contract['excluded_operations'] ?? []);
        self::assertContains('React app shell', $contract['ownership_boundary']['external_owner_owns'] ?? []);
    }

    public function testMobileWrapperArtifactPageHtmlShowsCommandAndNoExecutionBoundary(): void
    {
        $html = app_mobile_wrapper_artifact_page_html(
            app_mobile_wrapper_artifact_page_contract(),
            'PROJECT1',
            'MOBILE-HANDOFF',
        );

        self::assertStringContainsString('data-mtool-mobile-wrapper-artifact-page="v1"', $html);
        self::assertStringContainsString('--project-key=PROJECT1 --source-output-key=MOBILE-HANDOFF', $html);
        self::assertStringContainsString('--artifact=bundle-manifest', $html);
        self::assertStringContainsString('does not generate artifacts or create app projects', $html);
        self::assertStringContainsString('native project creation', $html);
    }

    public function testMobileWrapperArtifactRouteIsAuthenticatedReadOnlyGuide(): void
    {
        $route = app_route_match(['path' => '/projects/PROJECT1/mobile-wrapper-artifacts']);

        self::assertSame('project_mobile_wrapper_artifacts', $route['name']);
        self::assertSame('PROJECT1', $route['params']['project_key'] ?? '');
        self::assertTrue(app_route_requires_auth($route['name']));
    }

    public function testCliParserRejectsUnsupportedSamples(): void
    {
        $result = app_cli_mobile_wrapper_target_parse_args([
            'create_mobile_wrapper_target.php',
            '--sample=sample99',
            '--target-dir=work/source-outputs/SAMPLE99/MOBILE-WRAPPER-TARGET/mobile-wrapper-target',
        ]);

        self::assertFalse($result['ok']);
        self::assertSame('supported --sample is currently sample28', $result['error']);
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
